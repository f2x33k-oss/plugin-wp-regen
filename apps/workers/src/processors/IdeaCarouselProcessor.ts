import { PrismaClient } from '@prisma/client';
import { logger } from '@core/shared';
import { BaseProcessor } from './BaseProcessor';
import { OpenAIClient } from '../clients/OpenAIClient';
import type { WorkerJobResult, IdeaCarouselJobParams } from '@core/types';

export class IdeaCarouselProcessor extends BaseProcessor {
  private openaiClient: OpenAIClient;

  constructor(prisma: PrismaClient) {
    super(prisma);
    this.openaiClient = new OpenAIClient();
  }

  async process(jobId: string, inputParams: Record<string, any>): Promise<WorkerJobResult> {
    const params = inputParams as IdeaCarouselJobParams;
    let totalCost = 0;

    const job = await this.prisma.job.findUnique({
      where: { id: jobId },
      select: { userId: true },
    });

    if (!job) {
      throw new Error('Job not found');
    }

    try {
      const stepsTotal = 3;
      await this.prisma.job.update({
        where: { id: jobId },
        data: { stepsTotal },
      });

      // Step 1: Generate carousel content
      const step1Id = await this.createJobStep(jobId, 'Generate carousel ideas', 1);
      await this.updateJobStep(step1Id, 'PROCESSING');
      await this.updateJobProgress(jobId, 10, 'Generating carousel ideas', 0);

      const contentPrompt = `Create ${params.numSlides} engaging carousel slides about "${params.topic}" for ${params.targetAudience}.
Each slide should have:
- A catchy title (max 50 characters)
- Concise content (max 200 characters)
- 2-3 relevant tags
- Optional CTA text and URL

Make it informative, actionable, and visually appealing. Theme: ${params.theme}.
Format as JSON array.`;

      const contentResult = await this.openaiClient.generateText({
        prompt: contentPrompt,
        model: 'gpt-4-turbo-preview',
        maxTokens: 2000,
      });

      totalCost += contentResult.cost;
      await this.trackApiCall(jobId, step1Id, job.userId, 'openai', '/chat/completions', {
        requestTokens: contentResult.usage.promptTokens,
        responseTokens: contentResult.usage.completionTokens,
        totalTokens: contentResult.usage.totalTokens,
        costUsd: contentResult.cost,
        success: true,
        durationMs: contentResult.durationMs,
      });

      const slides = JSON.parse(contentResult.text);

      await this.updateJobStep(step1Id, 'COMPLETED', {
        outputData: { slidesCount: slides.length },
        costUsd: contentResult.cost,
      });
      await this.updateJobProgress(jobId, 40, 'Carousel ideas generated', 1);

      // Step 2: Generate images for each slide
      const step2Id = await this.createJobStep(jobId, 'Generate slide images', 2);
      await this.updateJobStep(step2Id, 'PROCESSING');
      await this.updateJobProgress(jobId, 50, 'Generating slide images', 1);

      const slideImages: string[] = [];
      for (const slide of slides) {
        const imagePrompt = `Modern, minimalist illustration for a carousel slide about: ${slide.title}. Theme: ${params.theme}. Clean design, vibrant colors.`;
        
        const imageResult = await this.openaiClient.generateImage({
          prompt: imagePrompt,
          size: '1024x1024',
          quality: 'standard',
        });

        totalCost += imageResult.cost;
        await this.trackApiCall(jobId, step2Id, job.userId, 'openai', '/images/generations', {
          costUsd: imageResult.cost,
          success: true,
          durationMs: imageResult.durationMs,
        });

        slideImages.push(imageResult.imageUrl);
      }

      await this.updateJobStep(step2Id, 'COMPLETED', {
        outputData: { imagesGenerated: slideImages.length },
        costUsd: totalCost - contentResult.cost,
      });
      await this.updateJobProgress(jobId, 85, 'Slide images generated', 2);

      // Step 3: Create content item
      const step3Id = await this.createJobStep(jobId, 'Create content item', 3);
      await this.updateJobStep(step3Id, 'PROCESSING');
      await this.updateJobProgress(jobId, 95, 'Finalizing content', 2);

      const formattedSlides = slides.map((slide: any, index: number) => ({
        slideNumber: index + 1,
        title: slide.title,
        content: slide.content,
        imageUrl: slideImages[index],
        ctaText: slide.ctaText || null,
        ctaUrl: slide.ctaUrl || null,
        tags: slide.tags || [],
      }));

      const formatData = {
        carouselTitle: `${params.numSlides} ${params.topic}`,
        carouselDescription: `Carousel de ${params.numSlides} idées sur ${params.topic}`,
        slides: formattedSlides,
        carouselStyle: {
          theme: params.theme,
          primaryColor: '#4CAF50',
          font: 'Roboto',
        },
        seo: {
          metaDescription: `Découvrez ${params.numSlides} idées pratiques sur ${params.topic}`,
          keywords: [params.topic, params.theme, 'idées', params.targetAudience],
        },
      };

      const contentId = await this.createContentItem(
        jobId,
        job.userId,
        'idea_carousel',
        {
          title: formatData.carouselTitle,
          subtitle: formatData.carouselDescription,
          formatData,
          featuredImageUrl: slideImages[0],
          mediaUrls: slideImages,
          seoTitle: formatData.carouselTitle,
          seoDescription: formatData.seo.metaDescription,
          keywords: formatData.seo.keywords,
        }
      );

      await this.updateJobStep(step3Id, 'COMPLETED', {
        outputData: { contentId },
      });
      await this.updateJobProgress(jobId, 100, 'Content finalized', 3);

      logger.info('Idea carousel job completed', { 
        jobId, 
        slidesCount: slides.length,
        totalCost 
      });

      return {
        success: true,
        resultData: { contentId },
        totalCostUsd: totalCost,
      };

    } catch (error) {
      logger.error('Idea carousel processing failed', { jobId, error });
      throw error;
    }
  }
}
