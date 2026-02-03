// API Types

export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  error?: ApiError;
  meta?: ResponseMeta;
}

export interface ApiError {
  code: string;
  message: string;
  details?: any;
}

export interface ResponseMeta {
  page?: number;
  limit?: number;
  total?: number;
  timestamp?: string;
}

export interface PaginationParams {
  page?: number;
  limit?: number;
  sortBy?: string;
  sortOrder?: 'asc' | 'desc';
}

export interface PaginatedResponse<T> {
  success: boolean;
  data: T[];
  meta: {
    page: number;
    limit: number;
    total: number;
    totalPages: number;
  };
}

export interface HealthCheckResponse {
  status: 'healthy' | 'unhealthy';
  timestamp: string;
  version: string;
  uptime: number;
}

export interface DetailedHealthResponse extends HealthCheckResponse {
  services: {
    database: ServiceHealth;
    redis: ServiceHealth;
    workers: ServiceHealth;
  };
}

export interface ServiceHealth {
  status: 'healthy' | 'unhealthy' | 'degraded';
  responseTime?: number;
  message?: string;
}

// Cost & Analytics

export interface CostSummaryResponse {
  totalCostUsd: string;
  breakdown: CostBreakdown[];
  period: {
    start: string;
    end: string;
  };
}

export interface CostBreakdown {
  provider: string;
  totalCalls: number;
  totalCostUsd: string;
  totalTokens?: number;
}

export interface JobAnalyticsResponse {
  totalJobs: number;
  completedJobs: number;
  failedJobs: number;
  successRate: number;
  averageDurationSeconds: number;
  jobsByType: Record<string, number>;
  jobsByStatus: Record<string, number>;
}
