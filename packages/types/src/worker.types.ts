// Worker Types

export enum WorkerStatus {
  IDLE = 'IDLE',
  BUSY = 'BUSY',
  ERROR = 'ERROR',
  OFFLINE = 'OFFLINE',
}

export interface WorkerHealthResponse {
  id: string;
  workerId: string;
  workerType: string;
  status: WorkerStatus;
  currentJobId?: string;
  jobsProcessed: number;
  lastHeartbeatAt: string;
  startedAt: string;
  cpuPercent?: string;
  memoryMb?: number;
}

export interface WorkerJobData {
  jobId: string;
  type: string;
  inputParams: Record<string, any>;
  userId: string;
}

export interface WorkerJobResult {
  success: boolean;
  resultData?: Record<string, any>;
  errorMessage?: string;
  errorStack?: string;
  totalCostUsd: number;
}

// External API clients

export interface OpenAIRequest {
  model: string;
  messages: Array<{
    role: 'system' | 'user' | 'assistant';
    content: string;
  }>;
  temperature?: number;
  maxTokens?: number;
}

export interface OpenAIResponse {
  id: string;
  choices: Array<{
    message: {
      role: string;
      content: string;
    };
    finishReason: string;
  }>;
  usage: {
    promptTokens: number;
    completionTokens: number;
    totalTokens: number;
  };
}

export interface ImageGenerationRequest {
  prompt: string;
  n?: number;
  size?: '256x256' | '512x512' | '1024x1024' | '1792x1024' | '1024x1792';
  quality?: 'standard' | 'hd';
  style?: 'vivid' | 'natural';
}

export interface ImageGenerationResponse {
  created: number;
  data: Array<{
    url: string;
    revisedPrompt?: string;
  }>;
}
