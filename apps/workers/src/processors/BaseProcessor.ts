import { PrismaClient } from '@prisma/client';
import { logger } from '@core/shared';
import type { WorkerJobResult } from '@core/types';

export abstract class BaseProcessor {
  protected prisma: PrismaClient;

  constructor(prisma: PrismaClient) {
    this.prisma = prisma;
  }

  abstract process(jobId: string, inputParams: Record<string, any>): Promise<WorkerJobResult>;

  // Helper: Create job step
  protected async createJobStep(
    jobId: string,
    stepName: string,
    stepOrder: number
  ): Promise<string> {
    const step = await this.prisma.jobStep.create({
      data: {
        jobId,
        stepName,
        stepOrder,
        status: 'PENDING',
      },
    });

    return step.id;
  }

  // Helper: Update job step
  protected async updateJobStep(
    stepId: string,
    status: string,
    data?: {
      outputData?: Record<string, any>;
      errorMessage?: string;
      costUsd?: number;
    }
  ): Promise<void> {
    await this.prisma.jobStep.update({
      where: { id: stepId },
      data: {
        status: status as any,
        ...(status === 'PROCESSING' && { startedAt: new Date() }),
        ...(status === 'COMPLETED' && { completedAt: new Date() }),
        ...(status === 'FAILED' && { completedAt: new Date() }),
        ...data,
      },
    });
  }

  // Helper: Update job progress
  protected async updateJobProgress(
    jobId: string,
    progressPercent: number,
    currentStep: string,
    stepsCompleted: number
  ): Promise<void> {
    await this.prisma.job.update({
      where: { id: jobId },
      data: {
        progressPercent,
        currentStep,
        stepsCompleted,
      },
    });
  }

  // Helper: Track API call
  protected async trackApiCall(
    jobId: string,
    jobStepId: string,
    userId: string,
    provider: string,
    endpoint: string,
    data: {
      requestTokens?: number;
      responseTokens?: number;
      totalTokens?: number;
      costUsd: number;
      statusCode?: number;
      success: boolean;
      errorMessage?: string;
      durationMs?: number;
    }
  ): Promise<void> {
    await this.prisma.apiCall.create({
      data: {
        jobId,
        jobStepId,
        userId,
        provider,
        endpoint,
        ...data,
      },
    });
  }

  // Helper: Create content item
  protected async createContentItem(
    jobId: string,
    userId: string,
    contentType: string,
    data: {
      title?: string;
      subtitle?: string;
      formatData: Record<string, any>;
      featuredImageUrl?: string;
      mediaUrls?: string[];
      seoTitle?: string;
      seoDescription?: string;
      keywords?: string[];
    }
  ): Promise<string> {
    const contentItem = await this.prisma.contentItem.create({
      data: {
        jobId,
        userId,
        contentType,
        ...data,
        mediaUrls: data.mediaUrls || [],
        keywords: data.keywords || [],
      },
    });

    return contentItem.id;
  }
}
