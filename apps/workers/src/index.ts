import 'dotenv/config';
import { Worker, Job as BullJob } from 'bullmq';
import { PrismaClient } from '@prisma/client';
import { logger, QUEUE_NAMES } from '@core/shared';
import { RecipeAlbumProcessor } from './processors/RecipeAlbumProcessor';
import { IdeaCarouselProcessor } from './processors/IdeaCarouselProcessor';
import type { WorkerJobData } from '@core/types';

const prisma = new PrismaClient();
const workerId = process.env.WORKER_ID || `worker-${process.pid}`;
const concurrency = parseInt(process.env.WORKER_CONCURRENCY || '5');

logger.info('🔧 Starting worker', { workerId, concurrency });

// Initialize processors
const recipeAlbumProcessor = new RecipeAlbumProcessor(prisma);
const ideaCarouselProcessor = new IdeaCarouselProcessor(prisma);

// Worker process function
async function processJob(job: BullJob<WorkerJobData>) {
  const { jobId, type, inputParams, userId } = job.data;

  logger.info('Processing job', { jobId, type, workerId });

  try {
    // Update job status to PROCESSING
    await prisma.job.update({
      where: { id: jobId },
      data: {
        status: 'PROCESSING',
        startedAt: new Date(),
      },
    });

    // Update worker health
    await updateWorkerHealth('BUSY', jobId);

    // Route to appropriate processor
    let result;
    switch (type) {
      case 'recipe_album':
        result = await recipeAlbumProcessor.process(jobId, inputParams);
        break;
      
      case 'idea_carousel':
        result = await ideaCarouselProcessor.process(jobId, inputParams);
        break;
      
      default:
        throw new Error(`Unknown job type: ${type}`);
    }

    // Mark job as completed
    await prisma.job.update({
      where: { id: jobId },
      data: {
        status: 'COMPLETED',
        completedAt: new Date(),
        resultData: result.resultData || {},
        totalCostUsd: result.totalCostUsd,
        progressPercent: 100,
      },
    });

    logger.info('Job completed successfully', { 
      jobId, 
      type, 
      totalCost: result.totalCostUsd 
    });

    // Update worker health
    await updateWorkerHealth('IDLE');

  } catch (error) {
    logger.error('Job processing failed', { 
      jobId, 
      type, 
      error: error instanceof Error ? error.message : String(error),
      stack: error instanceof Error ? error.stack : undefined,
    });

    // Mark job as failed
    await prisma.job.update({
      where: { id: jobId },
      data: {
        status: 'FAILED',
        completedAt: new Date(),
        errorMessage: error instanceof Error ? error.message : String(error),
        errorStack: error instanceof Error ? error.stack : undefined,
      },
    });

    // Update worker health
    await updateWorkerHealth('ERROR');

    throw error;
  }
}

// Initialize BullMQ worker
const worker = new Worker(QUEUE_NAMES.JOBS, processJob, {
  connection: {
    host: process.env.REDIS_HOST || 'localhost',
    port: parseInt(process.env.REDIS_PORT || '6379'),
    password: process.env.REDIS_PASSWORD || undefined,
  },
  concurrency,
  limiter: {
    max: parseInt(process.env.WORKER_MAX_JOBS_PER_WORKER || '10'),
    duration: 1000,
  },
});

// Worker event listeners
worker.on('completed', (job) => {
  logger.info('Job completed event', { jobId: job.id });
});

worker.on('failed', (job, err) => {
  logger.error('Job failed event', { 
    jobId: job?.id, 
    error: err.message 
  });
});

worker.on('error', (err) => {
  logger.error('Worker error', { error: err.message });
});

// Register worker in database
async function registerWorker() {
  try {
    await prisma.workerHealth.upsert({
      where: { workerId },
      create: {
        workerId,
        workerType: 'ai_worker',
        status: 'IDLE',
        jobsProcessed: 0,
      },
      update: {
        status: 'IDLE',
        lastHeartbeatAt: new Date(),
      },
    });
    logger.info('Worker registered', { workerId });
  } catch (error) {
    logger.error('Failed to register worker', { error });
  }
}

// Update worker health
async function updateWorkerHealth(status: string, currentJobId?: string) {
  try {
    await prisma.workerHealth.update({
      where: { workerId },
      data: {
        status: status as any,
        currentJobId,
        lastHeartbeatAt: new Date(),
        ...(status === 'IDLE' && currentJobId ? { jobsProcessed: { increment: 1 } } : {}),
      },
    });
  } catch (error) {
    logger.error('Failed to update worker health', { error });
  }
}

// Heartbeat interval (every 30 seconds)
setInterval(async () => {
  await updateWorkerHealth(worker.isRunning() ? 'IDLE' : 'OFFLINE');
}, 30000);

// Initial registration
registerWorker();

// Graceful shutdown
async function shutdown() {
  logger.info('Shutting down worker', { workerId });
  
  await worker.close();
  
  await prisma.workerHealth.update({
    where: { workerId },
    data: { status: 'OFFLINE' },
  });
  
  await prisma.$disconnect();
  
  process.exit(0);
}

process.on('SIGTERM', shutdown);
process.on('SIGINT', shutdown);

logger.info('✅ Worker started successfully', { workerId });
