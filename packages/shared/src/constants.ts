// ============================================
// CORE APP - Constants
// ============================================

// Job Types
export const JOB_TYPES = {
  RECIPE_ALBUM: 'recipe_album',
  IDEA_CAROUSEL: 'idea_carousel',
} as const;

// Content Types
export const CONTENT_TYPES = {
  RECIPE_ALBUM: 'recipe_album',
  IDEA_CAROUSEL: 'idea_carousel',
} as const;

// API Providers
export const API_PROVIDERS = {
  OPENAI: 'openai',
  ANTHROPIC: 'anthropic',
  GOOGLE: 'google',
  SCRAPING: 'scraping',
  FACEBOOK: 'facebook',
  TWITTER: 'twitter',
} as const;

// Queue Names
export const QUEUE_NAMES = {
  JOBS: 'jobs',
  NOTIFICATIONS: 'notifications',
} as const;

// Job Priorities
export const JOB_PRIORITY = {
  LOW: 0,
  NORMAL: 5,
  HIGH: 10,
  URGENT: 20,
} as const;

// Timeouts (milliseconds)
export const TIMEOUTS = {
  JOB_DEFAULT: 30 * 60 * 1000, // 30 minutes
  JOB_AI_HEAVY: 60 * 60 * 1000, // 60 minutes
  API_REQUEST: 30 * 1000, // 30 seconds
  WORKER_HEARTBEAT: 30 * 1000, // 30 seconds
} as const;

// Retry Configuration
export const RETRY_CONFIG = {
  MAX_ATTEMPTS: 3,
  INITIAL_DELAY: 60 * 1000, // 1 minute
  MAX_DELAY: 15 * 60 * 1000, // 15 minutes
  BACKOFF_MULTIPLIER: 2,
} as const;

// Pagination
export const PAGINATION = {
  DEFAULT_PAGE: 1,
  DEFAULT_LIMIT: 20,
  MAX_LIMIT: 100,
} as const;

// Rate Limiting
export const RATE_LIMITS = {
  WINDOW_MS: 60 * 1000, // 1 minute
  MAX_REQUESTS: 100,
} as const;

// API Key
export const API_KEY = {
  PREFIX_LIVE: 'sk_live_',
  PREFIX_TEST: 'sk_test_',
  LENGTH: 32,
} as const;

// Quotas
export const DEFAULT_QUOTAS = {
  MONTHLY_JOBS: 100,
  MONTHLY_COST_USD: 50,
} as const;

// Error Codes
export const ERROR_CODES = {
  // Authentication
  UNAUTHORIZED: 'UNAUTHORIZED',
  INVALID_CREDENTIALS: 'INVALID_CREDENTIALS',
  INVALID_API_KEY: 'INVALID_API_KEY',
  TOKEN_EXPIRED: 'TOKEN_EXPIRED',
  
  // Authorization
  FORBIDDEN: 'FORBIDDEN',
  INSUFFICIENT_PERMISSIONS: 'INSUFFICIENT_PERMISSIONS',
  
  // Validation
  VALIDATION_ERROR: 'VALIDATION_ERROR',
  INVALID_INPUT: 'INVALID_INPUT',
  MISSING_REQUIRED_FIELD: 'MISSING_REQUIRED_FIELD',
  
  // Resources
  NOT_FOUND: 'NOT_FOUND',
  ALREADY_EXISTS: 'ALREADY_EXISTS',
  
  // Jobs
  JOB_NOT_FOUND: 'JOB_NOT_FOUND',
  JOB_ALREADY_COMPLETED: 'JOB_ALREADY_COMPLETED',
  JOB_FAILED: 'JOB_FAILED',
  
  // Quotas
  QUOTA_EXCEEDED: 'QUOTA_EXCEEDED',
  RATE_LIMIT_EXCEEDED: 'RATE_LIMIT_EXCEEDED',
  
  // External APIs
  EXTERNAL_API_ERROR: 'EXTERNAL_API_ERROR',
  EXTERNAL_API_TIMEOUT: 'EXTERNAL_API_TIMEOUT',
  EXTERNAL_API_RATE_LIMITED: 'EXTERNAL_API_RATE_LIMITED',
  
  // System
  INTERNAL_ERROR: 'INTERNAL_ERROR',
  SERVICE_UNAVAILABLE: 'SERVICE_UNAVAILABLE',
  DATABASE_ERROR: 'DATABASE_ERROR',
} as const;

// HTTP Status Codes
export const HTTP_STATUS = {
  OK: 200,
  CREATED: 201,
  NO_CONTENT: 204,
  BAD_REQUEST: 400,
  UNAUTHORIZED: 401,
  FORBIDDEN: 403,
  NOT_FOUND: 404,
  CONFLICT: 409,
  UNPROCESSABLE_ENTITY: 422,
  TOO_MANY_REQUESTS: 429,
  INTERNAL_SERVER_ERROR: 500,
  SERVICE_UNAVAILABLE: 503,
} as const;
