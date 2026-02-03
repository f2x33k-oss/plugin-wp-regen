import { Router } from 'express';
import { PrismaClient } from '@prisma/client';
import { logger } from '@core/shared';
import type { HealthCheckResponse, DetailedHealthResponse } from '@core/types';

const router = Router();
const prisma = new PrismaClient();

// Basic health check
router.get('/', async (req, res) => {
  const response: HealthCheckResponse = {
    status: 'healthy',
    timestamp: new Date().toISOString(),
    version: '1.0.0',
    uptime: process.uptime(),
  };

  res.json(response);
});

// Detailed health check (with service status)
router.get('/detailed', async (req, res) => {
  const startTime = Date.now();
  const response: DetailedHealthResponse = {
    status: 'healthy',
    timestamp: new Date().toISOString(),
    version: '1.0.0',
    uptime: process.uptime(),
    services: {
      database: { status: 'healthy' },
      redis: { status: 'healthy' },
      workers: { status: 'healthy' },
    },
  };

  // Check database
  try {
    const dbStart = Date.now();
    await prisma.$queryRaw`SELECT 1`;
    response.services.database = {
      status: 'healthy',
      responseTime: Date.now() - dbStart,
    };
  } catch (error) {
    logger.error('Database health check failed', { error });
    response.services.database = {
      status: 'unhealthy',
      message: 'Database connection failed',
    };
    response.status = 'unhealthy';
  }

  // Check Redis (TODO: implement when Redis client is set up)
  // For now, assume healthy
  response.services.redis = {
    status: 'healthy',
    message: 'Redis check not implemented',
  };

  // Check workers (TODO: implement by querying worker_health table)
  try {
    const activeWorkers = await prisma.workerHealth.count({
      where: {
        status: 'BUSY',
        lastHeartbeatAt: {
          gte: new Date(Date.now() - 60000), // Active in last minute
        },
      },
    });

    response.services.workers = {
      status: activeWorkers > 0 ? 'healthy' : 'degraded',
      message: `${activeWorkers} active workers`,
    };
  } catch (error) {
    logger.error('Worker health check failed', { error });
    response.services.workers = {
      status: 'unhealthy',
      message: 'Worker health check failed',
    };
  }

  const statusCode = response.status === 'healthy' ? 200 : 503;
  res.status(statusCode).json(response);
});

export { router as healthRouter };
