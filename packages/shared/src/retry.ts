import { RETRY_CONFIG } from './constants';
import { logger } from './logger';

export interface RetryOptions {
  maxAttempts?: number;
  initialDelay?: number;
  maxDelay?: number;
  backoffMultiplier?: number;
  shouldRetry?: (error: any) => boolean;
  onRetry?: (attempt: number, error: any) => void;
}

const defaultOptions: Required<RetryOptions> = {
  maxAttempts: RETRY_CONFIG.MAX_ATTEMPTS,
  initialDelay: RETRY_CONFIG.INITIAL_DELAY,
  maxDelay: RETRY_CONFIG.MAX_DELAY,
  backoffMultiplier: RETRY_CONFIG.BACKOFF_MULTIPLIER,
  shouldRetry: () => true,
  onRetry: () => {},
};

export async function retry<T>(
  fn: () => Promise<T>,
  options: RetryOptions = {}
): Promise<T> {
  const opts = { ...defaultOptions, ...options };
  let lastError: any;
  
  for (let attempt = 1; attempt <= opts.maxAttempts; attempt++) {
    try {
      return await fn();
    } catch (error) {
      lastError = error;
      
      // Check if we should retry
      if (attempt >= opts.maxAttempts || !opts.shouldRetry(error)) {
        throw error;
      }
      
      // Calculate delay with exponential backoff
      const delay = Math.min(
        opts.initialDelay * Math.pow(opts.backoffMultiplier, attempt - 1),
        opts.maxDelay
      );
      
      logger.warn(`Retry attempt ${attempt}/${opts.maxAttempts} after ${delay}ms`, {
        error: error instanceof Error ? error.message : String(error),
      });
      
      opts.onRetry(attempt, error);
      
      await sleep(delay);
    }
  }
  
  throw lastError;
}

export function sleep(ms: number): Promise<void> {
  return new Promise(resolve => setTimeout(resolve, ms));
}

// Helper to determine if an error is retryable
export function isRetryableError(error: any): boolean {
  // Network errors
  if (error.code === 'ECONNRESET' || error.code === 'ETIMEDOUT') {
    return true;
  }
  
  // HTTP status codes that are retryable
  if (error.response?.status) {
    const status = error.response.status;
    return status === 429 || status === 502 || status === 503 || status === 504;
  }
  
  return false;
}

// Helper for retrying API calls with exponential backoff
export async function retryApiCall<T>(
  apiCall: () => Promise<T>,
  context: string
): Promise<T> {
  return retry(apiCall, {
    shouldRetry: isRetryableError,
    onRetry: (attempt, error) => {
      logger.warn(`Retrying API call: ${context}`, {
        attempt,
        error: error instanceof Error ? error.message : String(error),
      });
    },
  });
}
