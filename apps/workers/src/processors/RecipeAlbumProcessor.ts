import { PrismaClient } from '@prisma/client';
import { logger } from '@core/shared';
import { BaseProcessor } from './BaseProcessor';
import { OpenAIClient } from '../clients/OpenAIClient';
import type { WorkerJobResult, RecipeAlbumJobParams } from '@core/types';

export class RecipeAlbumProcessor extends BaseProcessor {
  private openaiClient: OpenAIClient;

  constructor(prisma: PrismaClient) {
    super(prisma);
    this.openaiClient = new OpenAIClient();
  }

  async process(jobId: string, inputParams: Record<string, any>): Promise<WorkerJobResult> {
    const params = inputParams as RecipeAlbumJobParams;
    let totalCost = 0;

    // Get job details for userId
    const job = await this.prisma.job.findUnique({
      where: { id: jobId },
      select: { userId: true },
    });

    if (!job) {
      throw new Error('Job not found');
    }

    try {
      // Define steps
      const stepsTotal = 4;
      await this.prisma.job.update({
        where: { id: jobId },
        data: { stepsTotal },
      });

      // Step 1: Generate recipe concepts
      const step1Id = await this.createJobStep(jobId, 'Generate recipe concepts', 1);
      await this.updateJobStep(step1Id, 'PROCESSING');
      await this.updateJobProgress(jobId, 10, 'Generating recipe concepts', 0);

      const conceptPrompt = `Generate ${params.numRecipes} ${params.style} ${params.topic} recipes suitable for ${params.targetAudience}. 
For each recipe, provide: title, description, prep time, cook time, servings, difficulty level, ingredients with quantities, and step-by-step instructions.
Format as JSON array.`;

      const conceptResult = await this.openaiClient.generateText({
        prompt: conceptPrompt,
        model: 'gpt-4-turbo-preview',
        maxTokens: 4000,
      });

      totalCost += conceptResult.cost;
      await this.trackApiCall(jobId, step1Id, job.userId, 'openai', '/chat/completions', {
        requestTokens: conceptResult.usage.promptTokens,
        responseTokens: conceptResult.usage.completionTokens,
        totalTokens: conceptResult.usage.totalTokens,
        costUsd: conceptResult.cost,
        success: true,
        durationMs: conceptResult.durationMs,
      });

      const recipes = JSON.parse(conceptResult.text);

      await this.updateJobStep(step1Id, 'COMPLETED', {
        outputData: { recipesCount: recipes.length },
        costUsd: conceptResult.cost,
      });
      await this.updateJobProgress(jobId, 30, 'Recipe concepts generated', 1);

      // Step 2: Generate images for each recipe
      const step2Id = await this.createJobStep(jobId, 'Generate recipe images', 2);
      await this.updateJobStep(step2Id, 'PROCESSING');
      await this.updateJobProgress(jobId, 40, 'Generating recipe images', 1);

      const recipeImages: string[] = [];
      for (const recipe of recipes) {
        const imagePrompt = `A professional food photography image of ${recipe.title}, ${params.imageStyle} style, appetizing presentation`;
        
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

        recipeImages.push(imageResult.imageUrl);
      }

      await this.updateJobStep(step2Id, 'COMPLETED', {
        outputData: { imagesGenerated: recipeImages.length },
        costUsd: totalCost,
      });
      await this.updateJobProgress(jobId, 70, 'Recipe images generated', 2);

      // Step 3: Generate album cover
      const step3Id = await this.createJobStep(jobId, 'Generate album cover', 3);
      await this.updateJobStep(step3Id, 'PROCESSING');
      await this.updateJobProgress(jobId, 80, 'Generating album cover', 2);

      const coverPrompt = `An attractive cookbook cover image for "${params.topic}", ${params.imageStyle} style`;
      const coverResult = await this.openaiClient.generateImage({
        prompt: coverPrompt,
        size: '1024x1024',
        quality: 'hd',
      });

      totalCost += coverResult.cost;
      await this.trackApiCall(jobId, step3Id, job.userId, 'openai', '/images/generations', {
        costUsd: coverResult.cost,
        success: true,
        durationMs: coverResult.durationMs,
      });

      await this.updateJobStep(step3Id, 'COMPLETED', {
        outputData: { coverImageUrl: coverResult.imageUrl },
        costUsd: coverResult.cost,
      });
      await this.updateJobProgress(jobId, 90, 'Album cover generated', 3);

      // Step 4: Create content item
      const step4Id = await this.createJobStep(jobId, 'Create content item', 4);
      await this.updateJobStep(step4Id, 'PROCESSING');
      await this.updateJobProgress(jobId, 95, 'Finalizing content', 3);

      // Format data for Recipe Album
      const formattedRecipes = recipes.map((recipe: any, index: number) => ({
        id: `recipe-${index + 1}`,
        title: recipe.title,
        description: recipe.description,
        prepTimeMinutes: recipe.prepTime || 15,
        cookTimeMinutes: recipe.cookTime || 30,
        servings: recipe.servings || 4,
        difficulty: recipe.difficulty || 'moyen',
        ingredients: recipe.ingredients || [],
        steps: (recipe.steps || []).map((step: string, stepIndex: number) => ({
          stepNumber: stepIndex + 1,
          instruction: step,
        })),
        featuredImage: recipeImages[index],
        tags: recipe.tags || [],
      }));

      const formatData = {
        albumTitle: `${params.numRecipes} ${params.topic}`,
        albumDescription: `Collection de ${params.numRecipes} recettes ${params.style} pour ${params.targetAudience}`,
        albumCoverImage: coverResult.imageUrl,
        recipes: formattedRecipes,
        seo: {
          metaDescription: `Découvrez ${params.numRecipes} délicieuses recettes ${params.topic}`,
          keywords: [params.topic, params.style, 'recettes', params.targetAudience],
        },
      };

      const contentId = await this.createContentItem(
        jobId,
        job.userId,
        'recipe_album',
        {
          title: formatData.albumTitle,
          subtitle: formatData.albumDescription,
          formatData,
          featuredImageUrl: coverResult.imageUrl,
          mediaUrls: recipeImages,
          seoTitle: formatData.albumTitle,
          seoDescription: formatData.seo.metaDescription,
          keywords: formatData.seo.keywords,
        }
      );

      await this.updateJobStep(step4Id, 'COMPLETED', {
        outputData: { contentId },
      });
      await this.updateJobProgress(jobId, 100, 'Content finalized', 4);

      logger.info('Recipe album job completed', { 
        jobId, 
        recipesCount: recipes.length,
        totalCost 
      });

      return {
        success: true,
        resultData: { contentId },
        totalCostUsd: totalCost,
      };

    } catch (error) {
      logger.error('Recipe album processing failed', { jobId, error });
      throw error;
    }
  }
}
