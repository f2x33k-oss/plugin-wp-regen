import rateLimit from 'express-rate-limit';
import { RATE_LIMITS } from '@core/shared';

export const createRateLimiter = (options?: {
  windowMs?: number;
  max?: number;
  message?: string;
}) => {
  return rateLimit({
    windowMs: options?.windowMs || RATE_LIMITS.WINDOW_MS,
    max: options?.max || RATE_LIMITS.MAX_REQUESTS,
    message: {
      success: false,
      error: {
        code: 'RATE_LIMIT_EXCEEDED',
        message: options?.message || 'Too many requests, please try again later',
      },
    },
    standardHeaders: true,
    legacyHeaders: false,
  });
};

// Default rate limiter
export const defaultRateLimiter = createRateLimiter();

// Strict rate limiter for sensitive endpoints (e.g., login)
export const strictRateLimiter = createRateLimiter({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 5,
  message: 'Too many attempts, please try again later',
});

// Generous rate limiter for read operations
export const generousRateLimiter = createRateLimiter({
  windowMs: 60 * 1000, // 1 minute
  max: 200,
});
