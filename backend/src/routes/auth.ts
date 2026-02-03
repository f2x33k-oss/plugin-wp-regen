import { Router } from 'express';
import { AuthController } from '../controllers/auth.controller';
import { authenticate } from '../middleware/auth';

const router = Router();

// GET /api/auth/me - Get current user (hardcoded for MVP)
router.get('/me', authenticate, AuthController.me);

export default router;
