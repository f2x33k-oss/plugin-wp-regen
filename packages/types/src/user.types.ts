// User Types

export enum UserRole {
  ADMIN = 'ADMIN',
  USER = 'USER',
  VIEWER = 'VIEWER',
}

export enum UserStatus {
  ACTIVE = 'ACTIVE',
  SUSPENDED = 'SUSPENDED',
  DELETED = 'DELETED',
}

export interface UserResponse {
  id: string;
  email: string;
  fullName?: string;
  role: UserRole;
  status: UserStatus;
  createdAt: string;
  lastLoginAt?: string;
  monthlyJobQuota: number;
  monthlyJobsUsed: number;
  quotaResetDate?: string;
}

export interface CreateUserRequest {
  email: string;
  password: string;
  fullName?: string;
  role?: UserRole;
}

export interface UpdateUserRequest {
  fullName?: string;
  monthlyJobQuota?: number;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  accessToken: string;
  refreshToken: string;
  user: UserResponse;
}

export interface ApiKeyResponse {
  id: string;
  userId: string;
  keyPrefix: string;
  name?: string;
  scopes: string[];
  status: string;
  lastUsedAt?: string;
  expiresAt?: string;
  createdAt: string;
}

export interface CreateApiKeyRequest {
  name?: string;
  scopes?: string[];
  expiresAt?: string;
}

export interface CreateApiKeyResponse {
  apiKey: ApiKeyResponse;
  key: string; // Only returned once
}

export interface QuotaResponse {
  monthlyJobQuota: number;
  monthlyJobsUsed: number;
  quotaResetDate?: string;
  remainingJobs: number;
}

export interface UsageStatsResponse {
  totalJobs: number;
  completedJobs: number;
  failedJobs: number;
  totalCostUsd: string;
  jobsByType: Record<string, number>;
  costByProvider: Record<string, string>;
}
