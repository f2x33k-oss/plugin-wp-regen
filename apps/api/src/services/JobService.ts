import { PrismaClient, Job } from '@prisma/client';
import { Queue } from 'bullmq';
import { logger, QUEUE_NAMES } from '@core/shared';
import type { JobInput } from '@core/types';

export class JobService {
  private prisma: PrismaClient;
  private jobQueue: Queue;

  constructor(prisma: PrismaClient) {
    this.prisma = prisma;
    
    // Initialize BullMQ queue
    this.jobQueue = new Queue(QUEUE_NAMES.JOBS, {
      connection: {
        host: process.env.REDIS_HOST || 'localhost',
        port: parseInt(process.env.REDIS_PORT || '6379'),
        password: process.env.REDIS_PASSWORD || undefined,
      },
    });
  }

  async createJob(data: {
    userId: string;
    type: string;
    inputParams: Record<string, any>;
    priority: number;
  }): Promise<Job> {
    // Create job in database
    const job = await this.prisma.job.create({
      data: {
        userId: data.userId,
        type: data.type,
        inputParams: data.inputParams,
        priority: data.priority,
        status: 'PENDING',
        progressPercent: 0,
        stepsTotal: 1,
        stepsCompleted: 0,
      },
    });

    // Add job to queue
    try {
      await this.jobQueue.add(
        data.type,
        {
          jobId: job.id,
          userId: data.userId,
          type: data.type,
          inputParams: data.inputParams,
        },
        {
          jobId: job.id,
          priority: data.priority,
          attempts: parseInt(process.env.JOB_MAX_ATTEMPTS || '3'),
          backoff: {
            type: process.env.JOB_RETRY_BACKOFF === 'fixed' ? 'fixed' : 'exponential',
            delay: parseInt(process.env.JOB_RETRY_DELAY || '60000'),
          },
        }
      );

      // Update job status to QUEUED
      await this.prisma.job.update({
        where: { id: job.id },
        data: { status: 'QUEUED' },
      });

      logger.info('Job added to queue', { jobId: job.id, type: data.type });
    } catch (error) {
      logger.error('Failed to add job to queue', { jobId: job.id, error });
      
      // Update job status to FAILED
      await this.prisma.job.update({
        where: { id: job.id },
        data: {
          status: 'FAILED',
          errorMessage: 'Failed to add job to queue',
        },
      });

      throw error;
    }

    return job;
  }

  async updateJobProgress(
    jobId: string,
    progress: {
      progressPercent?: number;
      currentStep?: string;
      stepsCompleted?: number;
    }
  ): Promise<void> {
    await this.prisma.job.update({
      where: { id: jobId },
      data: progress,
    });
  }

  async completeJob(
    jobId: string,
    resultData: Record<string, any>,
    totalCost: number
  ): Promise<void> {
    await this.prisma.job.update({
      where: { id: jobId },
      data: {
        status: 'COMPLETED',
        completedAt: new Date(),
        resultData,
        totalCostUsd: totalCost,
        progressPercent: 100,
      },
    });

    logger.info('Job completed', { jobId, totalCost });
  }

  async failJob(
    jobId: string,
    errorMessage: string,
    errorStack?: string
  ): Promise<void> {
    await this.prisma.job.update({
      where: { id: jobId },
      data: {
        status: 'FAILED',
        completedAt: new Date(),
        errorMessage,
        errorStack,
      },
    });

    logger.error('Job failed', { jobId, errorMessage });
  }
}
