import OpenAI from 'openai';
import { logger, ExternalApiError, retryApiCall } from '@core/shared';

export class OpenAIClient {
  private client: OpenAI;

  constructor() {
    if (!process.env.OPENAI_API_KEY) {
      throw new Error('OPENAI_API_KEY is not configured');
    }

    this.client = new OpenAI({
      apiKey: process.env.OPENAI_API_KEY,
    });
  }

  async generateText(params: {
    prompt: string;
    model?: string;
    temperature?: number;
    maxTokens?: number;
  }): Promise<{
    text: string;
    usage: {
      promptTokens: number;
      completionTokens: number;
      totalTokens: number;
    };
    cost: number;
    durationMs: number;
  }> {
    const startTime = Date.now();
    
    try {
      const completion = await retryApiCall(
        () => this.client.chat.completions.create({
          model: params.model || 'gpt-4-turbo-preview',
          messages: [
            {
              role: 'user',
              content: params.prompt,
            },
          ],
          temperature: params.temperature || 0.7,
          max_tokens: params.maxTokens || 1000,
        }),
        'OpenAI Chat Completion'
      );

      const durationMs = Date.now() - startTime;

      if (!completion.choices[0]?.message?.content) {
        throw new ExternalApiError('OpenAI returned empty response');
      }

      const usage = {
        promptTokens: completion.usage?.prompt_tokens || 0,
        completionTokens: completion.usage?.completion_tokens || 0,
        totalTokens: completion.usage?.total_tokens || 0,
      };

      // Calculate cost (approximate pricing for GPT-4 Turbo)
      const cost = this.calculateCost(
        params.model || 'gpt-4-turbo-preview',
        usage.promptTokens,
        usage.completionTokens
      );

      logger.info('OpenAI text generation completed', {
        model: params.model,
        tokens: usage.totalTokens,
        cost,
        durationMs,
      });

      return {
        text: completion.choices[0].message.content,
        usage,
        cost,
        durationMs,
      };

    } catch (error) {
      logger.error('OpenAI text generation failed', { error });
      throw new ExternalApiError(
        `OpenAI API error: ${error instanceof Error ? error.message : String(error)}`
      );
    }
  }

  async generateImage(params: {
    prompt: string;
    size?: '256x256' | '512x512' | '1024x1024' | '1792x1024' | '1024x1792';
    quality?: 'standard' | 'hd';
  }): Promise<{
    imageUrl: string;
    cost: number;
    durationMs: number;
  }> {
    const startTime = Date.now();

    try {
      const response = await retryApiCall(
        () => this.client.images.generate({
          model: 'dall-e-3',
          prompt: params.prompt,
          n: 1,
          size: params.size || '1024x1024',
          quality: params.quality || 'standard',
        }),
        'OpenAI Image Generation'
      );

      const durationMs = Date.now() - startTime;

      if (!response.data[0]?.url) {
        throw new ExternalApiError('OpenAI returned no image URL');
      }

      // Calculate cost for DALL-E 3
      const cost = this.calculateImageCost(
        params.size || '1024x1024',
        params.quality || 'standard'
      );

      logger.info('OpenAI image generation completed', {
        size: params.size,
        quality: params.quality,
        cost,
        durationMs,
      });

      return {
        imageUrl: response.data[0].url,
        cost,
        durationMs,
      };

    } catch (error) {
      logger.error('OpenAI image generation failed', { error });
      throw new ExternalApiError(
        `OpenAI API error: ${error instanceof Error ? error.message : String(error)}`
      );
    }
  }

  private calculateCost(model: string, promptTokens: number, completionTokens: number): number {
    // Pricing per 1K tokens (as of 2024)
    const pricing: Record<string, { input: number; output: number }> = {
      'gpt-4-turbo-preview': { input: 0.01, output: 0.03 },
      'gpt-4': { input: 0.03, output: 0.06 },
      'gpt-3.5-turbo': { input: 0.0005, output: 0.0015 },
    };

    const modelPricing = pricing[model] || pricing['gpt-4-turbo-preview'];
    
    return (
      (promptTokens / 1000) * modelPricing.input +
      (completionTokens / 1000) * modelPricing.output
    );
  }

  private calculateImageCost(size: string, quality: string): number {
    // DALL-E 3 pricing (as of 2024)
    if (quality === 'hd') {
      return size === '1024x1024' ? 0.080 : 0.120;
    } else {
      return size === '1024x1024' ? 0.040 : 0.080;
    }
  }
}
