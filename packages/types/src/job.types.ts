// Job Types

export enum JobStatus {
  PENDING = 'PENDING',
  QUEUED = 'QUEUED',
  PROCESSING = 'PROCESSING',
  COMPLETED = 'COMPLETED',
  FAILED = 'FAILED',
  CANCELLED = 'CANCELLED',
  RETRYING = 'RETRYING',
}

export enum JobStepStatus {
  PENDING = 'PENDING',
  PROCESSING = 'PROCESSING',
  COMPLETED = 'COMPLETED',
  FAILED = 'FAILED',
}

export interface JobInput {
  type: string;
  inputParams: Record<string, any>;
  priority?: number;
}

export interface JobResponse {
  id: string;
  userId: string;
  type: string;
  status: JobStatus;
  priority: number;
  createdAt: string;
  startedAt?: string;
  completedAt?: string;
  estimatedDurationSeconds?: number;
  inputParams: Record<string, any>;
  resultData?: Record<string, any>;
  errorMessage?: string;
  progressPercent: number;
  currentStep?: string;
  stepsTotal: number;
  stepsCompleted: number;
  totalCostUsd: string;
}

export interface JobStepResponse {
  id: string;
  jobId: string;
  stepName: string;
  stepOrder: number;
  status: JobStepStatus;
  startedAt?: string;
  completedAt?: string;
  inputData?: Record<string, any>;
  outputData?: Record<string, any>;
  errorMessage?: string;
  costUsd: string;
}

export interface JobStatusUpdate {
  jobId: string;
  status: JobStatus;
  progressPercent?: number;
  currentStep?: string;
  stepsCompleted?: number;
  errorMessage?: string;
}

export interface JobProgressUpdate {
  jobId: string;
  progressPercent: number;
  currentStep: string;
  stepsCompleted: number;
  stepsTotal: number;
}

// Specific job types

export interface RecipeAlbumJobParams {
  topic: string;
  numRecipes: number;
  style: string;
  imageStyle: string;
  targetAudience: string;
  language?: string;
}

export interface IdeaCarouselJobParams {
  topic: string;
  numSlides: number;
  theme: string;
  targetAudience: string;
  language?: string;
}
