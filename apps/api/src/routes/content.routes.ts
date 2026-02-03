import { Router } from 'express';
import { z } from 'zod';
import { PrismaClient } from '@prisma/client';
import { NotFoundError, logger } from '@core/shared';
import type { PublishRequest } from '@core/types';
import { authenticate } from '../middleware/auth';
import { generousRateLimiter } from '../middleware/rateLimit';

const router = Router();
const prisma = new PrismaClient();

// All routes require authentication
router.use(authenticate);

// List content items
router.get('/', generousRateLimiter, async (req, res, next) => {
  try {
    const page = parseInt(req.query.page as string) || 1;
    const limit = Math.min(parseInt(req.query.limit as string) || 20, 100);
    const contentType = req.query.contentType as string;

    const where: any = {
      userId: req.user!.id,
    };

    if (contentType) {
      where.contentType = contentType;
    }

    const [contentItems, total] = await Promise.all([
      prisma.contentItem.findMany({
        where,
        skip: (page - 1) * limit,
        take: limit,
        orderBy: { createdAt: 'desc' },
      }),
      prisma.contentItem.count({ where }),
    ]);

    res.json({
      success: true,
      data: contentItems,
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

// Get a specific content item
router.get('/:id', async (req, res, next) => {
  try {
    const contentItem = await prisma.contentItem.findFirst({
      where: {
        id: req.params.id,
        userId: req.user!.id,
      },
    });

    if (!contentItem) {
      throw new NotFoundError('Content not found');
    }

    res.json({
      success: true,
      data: { content: contentItem },
    });
  } catch (error) {
    next(error);
  }
});

// Get content by job ID
router.get('/job/:jobId', async (req, res, next) => {
  try {
    const contentItem = await prisma.contentItem.findFirst({
      where: {
        jobId: req.params.jobId,
        userId: req.user!.id,
      },
    });

    if (!contentItem) {
      throw new NotFoundError('Content not found for this job');
    }

    res.json({
      success: true,
      data: { content: contentItem },
    });
  } catch (error) {
    next(error);
  }
});

// Mark content as published
router.post('/:id/publish', async (req, res, next) => {
  try {
    const publishData = req.body as PublishRequest;

    const contentItem = await prisma.contentItem.findFirst({
      where: {
        id: req.params.id,
        userId: req.user!.id,
      },
    });

    if (!contentItem) {
      throw new NotFoundError('Content not found');
    }

    // Add publication record
    const publishedTo = Array.isArray(contentItem.publishedTo) 
      ? contentItem.publishedTo 
      : [];
    
    publishedTo.push({
      platform: publishData.platform,
      url: publishData.url,
      publishedAt: publishData.publishedAt,
    });

    await prisma.contentItem.update({
      where: { id: contentItem.id },
      data: { publishedTo },
    });

    logger.info('Content marked as published', {
      contentId: contentItem.id,
      platform: publishData.platform,
      userId: req.user!.id,
    });

    res.json({
      success: true,
      data: { message: 'Content marked as published' },
    });
  } catch (error) {
    next(error);
  }
});

// Delete content
router.delete('/:id', async (req, res, next) => {
  try {
    const contentItem = await prisma.contentItem.findFirst({
      where: {
        id: req.params.id,
        userId: req.user!.id,
      },
    });

    if (!contentItem) {
      throw new NotFoundError('Content not found');
    }

    await prisma.contentItem.delete({
      where: { id: contentItem.id },
    });

    logger.info('Content deleted', { contentId: contentItem.id, userId: req.user!.id });

    res.json({
      success: true,
      data: { message: 'Content deleted successfully' },
    });
  } catch (error) {
    next(error);
  }
});

export { router as contentRouter };
