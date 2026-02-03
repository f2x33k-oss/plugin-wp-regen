import { Request, Response, NextFunction } from 'express';

// MVP: Hardcoded user authentication
// In production, this would verify JWT tokens
export const authenticate = (req: Request, res: Response, next: NextFunction) => {
  // For MVP, we always authenticate as user_id: 1
  // This bypasses real authentication
  req.user = { id: 1 };
  next();
};

// Extend Express Request type
declare global {
  namespace Express {
    interface Request {
      user?: {
        id: number;
      };
    }
  }
}
