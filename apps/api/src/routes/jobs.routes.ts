import { Router } from 'express';
import { z } from 'zod';
import { PrismaClient } from '@prisma/client';
import { 
  NotFoundError, 
  QuotaExceededError,
  ValidationError,
  logger 
} from '@core/shared';
import type { JobInput, JobResponse } from '@core/types';
import { authenticate } from '../middleware/auth';
import { defaultRateLimiter } from '../middleware/rateLimit';
import { JobService } from '../services/JobService';

const router = Router();
const prisma = new PrismaClient();
const jobService = new JobService(prisma);

// All routes require authentication
router.use(authenticate);

// Validation schemas
const createJobSchema = z.object({
  type: z.string().min(1),
  inputParams: z.record(z.any()),
  priority: z.number().int().min(0).max(20).optional(),
});

// Create a new job
router.post('/', defaultRateLimiter, async (req, res, next) => {
  try {
    const data = createJobSchema.parse(req.body);

    // Check user quota
    const user = await prisma.user.findUnique({
      where: { id: req.user!.id },
    });

    if (!user) {
      throw new NotFoundError('User not found');
    }

    // Check if quota is exceeded
    if (user.monthlyJobsUsed >= user.monthlyJobQuota) {
      throw new QuotaExceededError(
        `Monthly job quota exceeded (${user.monthlyJobsUsed}/${user.monthlyJobQuota})`
      );
    }

    // Create job
    const job = await jobService.createJob({
      userId: req.user!.id,
      type: data.type,
      inputParams: data.inputParams,
      priority: data.priority || 0,
    });

    // Increment user's monthly jobs counter
    await prisma.user.update({
      where: { id: req.user!.id },
      data: { monthlyJobsUsed: { increment: 1 } },
    });

    logger.info('Job created', { jobId: job.id, userId: req.user!.id, type: data.type });

    res.status(201).json({
      success: true,
      data: { job },
    });
  } catch (error) {
    next(error);
  }
});

// List jobs (with pagination and filters)
router.get('/', async (req, res, next) => {
  try {
    const page = parseInt(req.query.page as string) || 1;
    const limit = Math.min(parseInt(req.query.limit as string) || 20, 100);
    const status = req.query.status as string;
    const type = req.query.type as string;

    const where: any = {
      userId: req.user!.id,
    };

    if (status) {
      where.status = status;
    }

    if (type) {
      where.type = type;
    }

    const [jobs, total] = await Promise.all([
      prisma.job.findMany({
        where,
        skip: (page - 1) * limit,
        take: limit,
        orderBy: { createdAt: 'desc' },
      }),
      prisma.job.count({ where }),
    ]);

    res.json({
      success: true,
      data: jobs,
      meta: {
        page,
        limit,
        total,
        totalPages: Math.ceil(total / limit),
      },
    });
  } catch (error) {
    next(error);
  }
});

// Get a specific job
router.get('/:id', async (req, res, next) => {
  try {
    const job = await prisma.job.findFirst({
      where: {
        id: req.params.id,
        userId: req.user!.id,
      },
    });

    if (!job) {
      throw new NotFoundError('Job not found');
    }

    res.json({
      success: true,
      data: { job },
    });
  } catch (error) {
    next(error);
  }
});

// Get job status (lightweight for polling)
router.get('/:id/status', async (req, res, next) => {
  try {
    const job = await prisma.job.findFirst({
      where: {
        id: req.params.id,
        userId: req.user!.id,
      },
      select: {
        id: true,
        status: true,
        progressPercent: true,
        currentStep: true,
        stepsCompleted: true,
        stepsTotal: true,
        errorMessage: true,
      },
    });

    if (!job) {
      throw new NotFoundError('Job not found');
    }

    res.json({
      success: true,
      data: { status: job },
    });
  } catch (error) {
    next(error);
  }
});

// Get job progress (detailed)
router.get('/:id/progress', async (req, res, next) => {
  try {
    const job = await prisma.job.findFirst({
      where: {
        id: req.params.id,
        userId: req.user!.id,
      },
      include: {
        jobSteps: {
          orderBy: { stepOrder: 'asc' },
        },
      },
    });

    if (!job) {
      throw new NotFoundError('Job not found');
    }

    res.json({
      success: true,
      data: {
        job: {
          id: job.id,
          status: job.status,
          progressPercent: job.progressPercent,
          currentStep: job.currentStep,
          stepsCompleted: job.stepsCompleted,
          stepsTotal: job.stepsTotal,
        },
        steps: job.jobSteps,
      },
    });
  } catch (error) {
    next(error);
  }
});

// Get job steps
router.get('/:id/steps', async (req, res, next) => {
  try {
    const job = await prisma.job.findFirst({
      where: {
        id: req.params.id,
        userId: req.user!.id,
      },
    });

    if (!job) {
      throw new NotFoundError('Job not found');
    }

    const steps = await prisma.jobStep.findMany({
      where: { jobId: job.id },
      orderBy: { stepOrder: 'asc' },
    });

    res.json({
      success: true,
      data: { steps },
    });
  } catch (error) {
    next(error);
  }
});

// Cancel a job
router.delete('/:id', async (req, res, next) => {
  try {
    const job = await prisma.job.findFirst({
      where: {
        id: req.params.id,
        userId: req.user!.id,
      },
    });

    if (!job) {
      throw new NotFoundError('Job not found');
    }

    // Can only cancel pending, queued, or processing jobs
    if (!['PENDING', 'QUEUED', 'PROCESSING'].includes(job.status)) {
      throw new ValidationError('Job cannot be cancelled in current status');
    }

    await prisma.job.update({
      where: { id: job.id },
      data: { status: 'CANCELLED' },
    });

    logger.info('Job cancelled', { jobId: job.id, userId: req.user!.id });

    res.json({
      success: true,
      data: { message: 'Job cancelled successfully' },
    });
  } catch (error) {
    next(error);
  }
});

// Retry a failed job
router.post('/:id/retry', async (req, res, next) => {
  try {
    const job = await prisma.job.findFirst({
      where: {
        id: req.params.id,
        userId: req.user!.id,
      },
    });

    if (!job) {
      throw new NotFoundError('Job not found');
    }

    // Can only retry failed jobs
    if (job.status !== 'FAILED') {
      throw new ValidationError('Only failed jobs can be retried');
    }

    // Create a new job with the same parameters
    const newJob = await jobService.createJob({
      userId: req.user!.id,
      type: job.type,
      inputParams: job.inputParams as any,
      priority: job.priority,
    });

    logger.info('Job retried', { 
      originalJobId: job.id, 
      newJobId: newJob.id, 
      userId: req.user!.id 
    });

    res.json({
      success: true,
      data: { job: newJob },
    });
  } catch (error) {
    next(error);
  }
});

export { router as jobsRouter };
