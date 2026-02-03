import { Router } from 'express';
import { PrismaClient } from '@prisma/client';
import { NotFoundError } from '@core/shared';
import type { QuotaResponse, UsageStatsResponse } from '@core/types';
import { authenticate } from '../middleware/auth';

const router = Router();
const prisma = new PrismaClient();

// All routes require authentication
router.use(authenticate);

// Get user quota
router.get('/me/quota', async (req, res, next) => {
  try {
    const user = await prisma.user.findUnique({
      where: { id: req.user!.id },
      select: {
        monthlyJobQuota: true,
        monthlyJobsUsed: true,
        quotaResetDate: true,
      },
    });

    if (!user) {
      throw new NotFoundError('User not found');
    }

    const response: QuotaResponse = {
      monthlyJobQuota: user.monthlyJobQuota,
      monthlyJobsUsed: user.monthlyJobsUsed,
      quotaResetDate: user.quotaResetDate?.toISOString(),
      remainingJobs: Math.max(0, user.monthlyJobQuota - user.monthlyJobsUsed),
    };

    res.json({
      success: true,
      data: { quota: response },
    });
  } catch (error) {
    next(error);
  }
});

// Get usage statistics
router.get('/me/usage', async (req, res, next) => {
  try {
    // Get job statistics
    const [totalJobs, completedJobs, failedJobs, jobsByType, totalCost] = await Promise.all([
      prisma.job.count({ where: { userId: req.user!.id } }),
      prisma.job.count({ where: { userId: req.user!.id, status: 'COMPLETED' } }),
      prisma.job.count({ where: { userId: req.user!.id, status: 'FAILED' } }),
      prisma.job.groupBy({
        by: ['type'],
        where: { userId: req.user!.id },
        _count: true,
      }),
      prisma.job.aggregate({
        where: { userId: req.user!.id },
        _sum: { totalCostUsd: true },
      }),
    ]);

    // Get cost by provider
    const costByProvider = await prisma.apiCall.groupBy({
      by: ['provider'],
      where: { userId: req.user!.id },
      _sum: { costUsd: true },
    });

    const response: UsageStatsResponse = {
      totalJobs,
      completedJobs,
      failedJobs,
      totalCostUsd: totalCost._sum.totalCostUsd?.toString() || '0',
      jobsByType: Object.fromEntries(
        jobsByType.map(item => [item.type, item._count])
      ),
      costByProvider: Object.fromEntries(
        costByProvider.map(item => [item.provider, item._sum.costUsd?.toString() || '0'])
      ),
    };

    res.json({
      success: true,
      data: { usage: response },
    });
  } catch (error) {
    next(error);
  }
});

export { router as usersRouter };
