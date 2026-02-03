import { ERROR_CODES, HTTP_STATUS } from './constants';

export class AppError extends Error {
  constructor(
    public code: string,
    public message: string,
    public statusCode: number = HTTP_STATUS.INTERNAL_SERVER_ERROR,
    public details?: any
  ) {
    super(message);
    this.name = this.constructor.name;
    Error.captureStackTrace(this, this.constructor);
  }
}

// Authentication Errors
export class UnauthorizedError extends AppError {
  constructor(message = 'Unauthorized', details?: any) {
    super(ERROR_CODES.UNAUTHORIZED, message, HTTP_STATUS.UNAUTHORIZED, details);
  }
}

export class InvalidCredentialsError extends AppError {
  constructor(message = 'Invalid credentials', details?: any) {
    super(ERROR_CODES.INVALID_CREDENTIALS, message, HTTP_STATUS.UNAUTHORIZED, details);
  }
}

export class InvalidApiKeyError extends AppError {
  constructor(message = 'Invalid API key', details?: any) {
    super(ERROR_CODES.INVALID_API_KEY, message, HTTP_STATUS.UNAUTHORIZED, details);
  }
}

export class TokenExpiredError extends AppError {
  constructor(message = 'Token expired', details?: any) {
    super(ERROR_CODES.TOKEN_EXPIRED, message, HTTP_STATUS.UNAUTHORIZED, details);
  }
}

// Authorization Errors
export class ForbiddenError extends AppError {
  constructor(message = 'Forbidden', details?: any) {
    super(ERROR_CODES.FORBIDDEN, message, HTTP_STATUS.FORBIDDEN, details);
  }
}

export class InsufficientPermissionsError extends AppError {
  constructor(message = 'Insufficient permissions', details?: any) {
    super(ERROR_CODES.INSUFFICIENT_PERMISSIONS, message, HTTP_STATUS.FORBIDDEN, details);
  }
}

// Validation Errors
export class ValidationError extends AppError {
  constructor(message = 'Validation error', details?: any) {
    super(ERROR_CODES.VALIDATION_ERROR, message, HTTP_STATUS.BAD_REQUEST, details);
  }
}

export class InvalidInputError extends AppError {
  constructor(message = 'Invalid input', details?: any) {
    super(ERROR_CODES.INVALID_INPUT, message, HTTP_STATUS.BAD_REQUEST, details);
  }
}

// Resource Errors
export class NotFoundError extends AppError {
  constructor(message = 'Resource not found', details?: any) {
    super(ERROR_CODES.NOT_FOUND, message, HTTP_STATUS.NOT_FOUND, details);
  }
}

export class AlreadyExistsError extends AppError {
  constructor(message = 'Resource already exists', details?: any) {
    super(ERROR_CODES.ALREADY_EXISTS, message, HTTP_STATUS.CONFLICT, details);
  }
}

// Job Errors
export class JobNotFoundError extends AppError {
  constructor(jobId: string) {
    super(ERROR_CODES.JOB_NOT_FOUND, `Job ${jobId} not found`, HTTP_STATUS.NOT_FOUND, { jobId });
  }
}

export class JobFailedError extends AppError {
  constructor(message: string, details?: any) {
    super(ERROR_CODES.JOB_FAILED, message, HTTP_STATUS.UNPROCESSABLE_ENTITY, details);
  }
}

// Quota Errors
export class QuotaExceededError extends AppError {
  constructor(message = 'Quota exceeded', details?: any) {
    super(ERROR_CODES.QUOTA_EXCEEDED, message, HTTP_STATUS.TOO_MANY_REQUESTS, details);
  }
}

export class RateLimitExceededError extends AppError {
  constructor(message = 'Rate limit exceeded', details?: any) {
    super(ERROR_CODES.RATE_LIMIT_EXCEEDED, message, HTTP_STATUS.TOO_MANY_REQUESTS, details);
  }
}

// External API Errors
export class ExternalApiError extends AppError {
  constructor(message: string, details?: any) {
    super(ERROR_CODES.EXTERNAL_API_ERROR, message, HTTP_STATUS.SERVICE_UNAVAILABLE, details);
  }
}

export class ExternalApiTimeoutError extends AppError {
  constructor(message = 'External API timeout', details?: any) {
    super(ERROR_CODES.EXTERNAL_API_TIMEOUT, message, HTTP_STATUS.SERVICE_UNAVAILABLE, details);
  }
}

export class ExternalApiRateLimitedError extends AppError {
  constructor(message = 'External API rate limited', details?: any) {
    super(ERROR_CODES.EXTERNAL_API_RATE_LIMITED, message, HTTP_STATUS.TOO_MANY_REQUESTS, details);
  }
}

// System Errors
export class InternalError extends AppError {
  constructor(message = 'Internal server error', details?: any) {
    super(ERROR_CODES.INTERNAL_ERROR, message, HTTP_STATUS.INTERNAL_SERVER_ERROR, details);
  }
}

export class ServiceUnavailableError extends AppError {
  constructor(message = 'Service unavailable', details?: any) {
    super(ERROR_CODES.SERVICE_UNAVAILABLE, message, HTTP_STATUS.SERVICE_UNAVAILABLE, details);
  }
}

export class DatabaseError extends AppError {
  constructor(message = 'Database error', details?: any) {
    super(ERROR_CODES.DATABASE_ERROR, message, HTTP_STATUS.INTERNAL_SERVER_ERROR, details);
  }
}
