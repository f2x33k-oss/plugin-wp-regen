import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';
import crypto from 'crypto';
import { PrismaClient } from '@prisma/client';
import { 
  UnauthorizedError, 
  InvalidApiKeyError, 
  TokenExpiredError,
  logger 
} from '@core/shared';

const prisma = new PrismaClient();

// Extend Express Request to include user
declare global {
  namespace Express {
    interface Request {
      user?: {
        id: string;
        email: string;
        role: string;
      };
    }
  }
}

// JWT Authentication Middleware
export async function authenticateJWT(
  req: Request,
  res: Response,
  next: NextFunction
) {
  try {
    const authHeader = req.headers.authorization;

    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      throw new UnauthorizedError('Missing or invalid authorization header');
    }

    const token = authHeader.substring(7);

    try {
      const decoded = jwt.verify(
        token,
        process.env.JWT_SECRET!
      ) as jwt.JwtPayload;

      // Fetch user from database
      const user = await prisma.user.findUnique({
        where: { id: decoded.userId },
        select: { id: true, email: true, role: true, status: true },
      });

      if (!user || user.status !== 'ACTIVE') {
        throw new UnauthorizedError('User not found or inactive');
      }

      req.user = {
        id: user.id,
        email: user.email,
        role: user.role,
      };

      next();
    } catch (err) {
      if (err instanceof jwt.TokenExpiredError) {
        throw new TokenExpiredError();
      }
      throw new UnauthorizedError('Invalid token');
    }
  } catch (error) {
    next(error);
  }
}

// API Key Authentication Middleware
export async function authenticateApiKey(
  req: Request,
  res: Response,
  next: NextFunction
) {
  try {
    const apiKey = req.headers['x-api-key'] as string;

    if (!apiKey) {
      throw new InvalidApiKeyError('Missing API key');
    }

    // Hash the provided API key
    const keyHash = crypto
      .createHash('sha256')
      .update(apiKey)
      .digest('hex');

    // Find API key in database
    const apiKeyRecord = await prisma.apiKey.findUnique({
      where: { keyHash },
      include: { user: true },
    });

    if (!apiKeyRecord || apiKeyRecord.status !== 'ACTIVE') {
      throw new InvalidApiKeyError('Invalid or inactive API key');
    }

    // Check expiration
    if (apiKeyRecord.expiresAt && apiKeyRecord.expiresAt < new Date()) {
      throw new InvalidApiKeyError('API key has expired');
    }

    // Update last used timestamp
    await prisma.apiKey.update({
      where: { id: apiKeyRecord.id },
      data: { lastUsedAt: new Date() },
    });

    req.user = {
      id: apiKeyRecord.user.id,
      email: apiKeyRecord.user.email,
      role: apiKeyRecord.user.role,
    };

    next();
  } catch (error) {
    next(error);
  }
}

// Combined authentication (try JWT first, then API Key)
export async function authenticate(
  req: Request,
  res: Response,
  next: NextFunction
) {
  const hasJWT = req.headers.authorization?.startsWith('Bearer ');
  const hasApiKey = !!req.headers['x-api-key'];

  if (hasJWT) {
    return authenticateJWT(req, res, next);
  } else if (hasApiKey) {
    return authenticateApiKey(req, res, next);
  } else {
    return next(new UnauthorizedError('No authentication provided'));
  }
}

// Authorization middleware (check roles)
export function authorize(...allowedRoles: string[]) {
  return (req: Request, res: Response, next: NextFunction) => {
    if (!req.user) {
      return next(new UnauthorizedError('User not authenticated'));
    }

    if (!allowedRoles.includes(req.user.role)) {
      return next(new UnauthorizedError(`Insufficient permissions. Required: ${allowedRoles.join(', ')}`));
    }

    next();
  };
}
