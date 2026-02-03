import { Router } from 'express';
import { z } from 'zod';
import bcrypt from 'bcrypt';
import jwt from 'jsonwebtoken';
import crypto from 'crypto';
import { PrismaClient } from '@prisma/client';
import { 
  InvalidCredentialsError, 
  AlreadyExistsError,
  ValidationError,
  logger 
} from '@core/shared';
import type { LoginRequest, LoginResponse, CreateApiKeyRequest } from '@core/types';
import { authenticate } from '../middleware/auth';
import { strictRateLimiter } from '../middleware/rateLimit';

const router = Router();
const prisma = new PrismaClient();

// Validation schemas
const registerSchema = z.object({
  email: z.string().email(),
  password: z.string().min(8),
  fullName: z.string().optional(),
});

const loginSchema = z.object({
  email: z.string().email(),
  password: z.string(),
});

// Register a new user
router.post('/register', async (req, res, next) => {
  try {
    const data = registerSchema.parse(req.body);

    // Check if user already exists
    const existingUser = await prisma.user.findUnique({
      where: { email: data.email },
    });

    if (existingUser) {
      throw new AlreadyExistsError('User with this email already exists');
    }

    // Hash password
    const passwordHash = await bcrypt.hash(data.password, 10);

    // Create user
    const user = await prisma.user.create({
      data: {
        email: data.email,
        passwordHash,
        fullName: data.fullName,
        role: 'USER',
        status: 'ACTIVE',
      },
      select: {
        id: true,
        email: true,
        fullName: true,
        role: true,
        createdAt: true,
      },
    });

    logger.info('User registered', { userId: user.id, email: user.email });

    res.status(201).json({
      success: true,
      data: { user },
    });
  } catch (error) {
    next(error);
  }
});

// Login
router.post('/login', strictRateLimiter, async (req, res, next) => {
  try {
    const data = loginSchema.parse(req.body);

    // Find user
    const user = await prisma.user.findUnique({
      where: { email: data.email },
    });

    if (!user || user.status !== 'ACTIVE') {
      throw new InvalidCredentialsError('Invalid email or password');
    }

    // Verify password
    const isValidPassword = await bcrypt.compare(data.password, user.passwordHash);

    if (!isValidPassword) {
      throw new InvalidCredentialsError('Invalid email or password');
    }

    // Generate tokens
    const accessToken = jwt.sign(
      { userId: user.id, email: user.email, role: user.role },
      process.env.JWT_SECRET!,
      { expiresIn: process.env.JWT_ACCESS_TOKEN_EXPIRES_IN || '15m' }
    );

    const refreshToken = jwt.sign(
      { userId: user.id },
      process.env.JWT_SECRET!,
      { expiresIn: process.env.JWT_REFRESH_TOKEN_EXPIRES_IN || '7d' }
    );

    // Update last login
    await prisma.user.update({
      where: { id: user.id },
      data: { lastLoginAt: new Date() },
    });

    logger.info('User logged in', { userId: user.id, email: user.email });

    const response: LoginResponse = {
      accessToken,
      refreshToken,
      user: {
        id: user.id,
        email: user.email,
        fullName: user.fullName || undefined,
        role: user.role,
        status: user.status,
        createdAt: user.createdAt.toISOString(),
        monthlyJobQuota: user.monthlyJobQuota,
        monthlyJobsUsed: user.monthlyJobsUsed,
      },
    };

    res.json({
      success: true,
      data: response,
    });
  } catch (error) {
    next(error);
  }
});

// Get current user
router.get('/me', authenticate, async (req, res, next) => {
  try {
    const user = await prisma.user.findUnique({
      where: { id: req.user!.id },
      select: {
        id: true,
        email: true,
        fullName: true,
        role: true,
        status: true,
        createdAt: true,
        lastLoginAt: true,
        monthlyJobQuota: true,
        monthlyJobsUsed: true,
        quotaResetDate: true,
      },
    });

    res.json({
      success: true,
      data: { user },
    });
  } catch (error) {
    next(error);
  }
});

// Create API key
router.post('/api-keys', authenticate, async (req, res, next) => {
  try {
    const data = req.body as CreateApiKeyRequest;

    // Generate random API key
    const key = `${process.env.API_KEY_PREFIX || 'sk_live_'}${crypto.randomBytes(32).toString('hex')}`;
    const keyHash = crypto.createHash('sha256').update(key).digest('hex');
    const keyPrefix = key.substring(0, 12);

    // Create API key record
    const apiKey = await prisma.apiKey.create({
      data: {
        userId: req.user!.id,
        keyHash,
        keyPrefix,
        name: data.name,
        scopes: data.scopes || [],
        status: 'ACTIVE',
        expiresAt: data.expiresAt ? new Date(data.expiresAt) : null,
      },
    });

    logger.info('API key created', { userId: req.user!.id, keyId: apiKey.id });

    res.status(201).json({
      success: true,
      data: {
        apiKey: {
          id: apiKey.id,
          keyPrefix: apiKey.keyPrefix,
          name: apiKey.name,
          scopes: apiKey.scopes,
          createdAt: apiKey.createdAt.toISOString(),
        },
        key, // Only returned once!
      },
    });
  } catch (error) {
    next(error);
  }
});

// List API keys
router.get('/api-keys', authenticate, async (req, res, next) => {
  try {
    const apiKeys = await prisma.apiKey.findMany({
      where: { userId: req.user!.id },
      select: {
        id: true,
        keyPrefix: true,
        name: true,
        scopes: true,
        status: true,
        lastUsedAt: true,
        expiresAt: true,
        createdAt: true,
      },
      orderBy: { createdAt: 'desc' },
    });

    res.json({
      success: true,
      data: { apiKeys },
    });
  } catch (error) {
    next(error);
  }
});

// Revoke API key
router.delete('/api-keys/:id', authenticate, async (req, res, next) => {
  try {
    await prisma.apiKey.updateMany({
      where: {
        id: req.params.id,
        userId: req.user!.id,
      },
      data: {
        status: 'REVOKED',
      },
    });

    logger.info('API key revoked', { userId: req.user!.id, keyId: req.params.id });

    res.json({
      success: true,
      data: { message: 'API key revoked successfully' },
    });
  } catch (error) {
    next(error);
  }
});

export { router as authRouter };
