import { Router } from 'express';
import { TaskController } from '../controllers/task.controller';
import { validate } from '../middleware/validate';
import { authenticate } from '../middleware/auth';
import { createTaskSchema, updateTaskSchema } from '../validators/task.validator';

const router = Router();

// All routes use hardcoded authentication (MVP)
router.use(authenticate);

// GET /api/tasks/stats - Get statistics (must be before /:id)
router.get('/stats', TaskController.getStats);

// POST /api/tasks - Create new task
router.post('/', validate(createTaskSchema), TaskController.create);

// GET /api/tasks - Get all tasks
router.get('/', TaskController.getAll);

// GET /api/tasks/:id - Get single task
router.get('/:id', TaskController.getById);

// PATCH /api/tasks/:id - Update task
router.patch('/:id', validate(updateTaskSchema), TaskController.update);

// DELETE /api/tasks/:id - Delete task
router.delete('/:id', TaskController.delete);

export default router;
