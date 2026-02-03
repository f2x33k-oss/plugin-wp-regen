import 'dotenv/config';
import express from 'express';
import helmet from 'helmet';
import cors from 'cors';
import { logger } from '@core/shared';
import { errorHandler } from './middleware/errorHandler';
import { requestLogger } from './middleware/requestLogger';
import { authRouter } from './routes/auth.routes';
import { jobsRouter } from './routes/jobs.routes';
import { contentRouter } from './routes/content.routes';
import { usersRouter } from './routes/users.routes';
import { healthRouter } from './routes/health.routes';

const app = express();
const PORT = process.env.API_PORT || 3000;
const HOST = process.env.API_HOST || '0.0.0.0';

// Security middleware
app.use(helmet());

// CORS
const corsOrigins = process.env.CORS_ORIGINS?.split(',') || ['http://localhost:3000'];
app.use(cors({
  origin: corsOrigins,
  credentials: true,
}));

// Body parsing
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Request logging
app.use(requestLogger);

// Health check (no auth required)
app.use('/api/v1/health', healthRouter);

// API Routes
app.use('/api/v1/auth', authRouter);
app.use('/api/v1/jobs', jobsRouter);
app.use('/api/v1/content', contentRouter);
app.use('/api/v1/users', usersRouter);

// 404 handler
app.use('*', (req, res) => {
  res.status(404).json({
    success: false,
    error: {
      code: 'NOT_FOUND',
      message: 'Route not found',
    },
  });
});

// Error handler (must be last)
app.use(errorHandler);

// Start server
app.listen(Number(PORT), HOST, () => {
  logger.info(`🚀 API Server running on http://${HOST}:${PORT}`);
  logger.info(`Environment: ${process.env.NODE_ENV || 'development'}`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
  logger.info('SIGTERM signal received: closing HTTP server');
  process.exit(0);
});

process.on('SIGINT', () => {
  logger.info('SIGINT signal received: closing HTTP server');
  process.exit(0);
});
