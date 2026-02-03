import { Request, Response, NextFunction } from 'express';

export class AuthController {
  /**
   * Get current user (hardcoded for MVP)
   * GET /api/auth/me
   */
  static async me(req: Request, res: Response, next: NextFunction) {
    try {
      // MVP: Return hardcoded user
      res.json({
        success: true,
        data: {
          id: 1,
          name: 'Demo User',
          email: 'demo@example.com'
        }
      });
    } catch (error) {
      next(error);
    }
  }
}
