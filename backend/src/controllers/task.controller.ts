import { Request, Response, NextFunction } from 'express';
import prisma from '../lib/prisma';
import { NotFoundError } from '../lib/errors';

export class TaskController {
  /**
   * Create a new task
   * POST /api/tasks
   */
  static async create(req: Request, res: Response, next: NextFunction) {
    try {
      const { title } = req.body;

      // Extract number from title (e.g., "20 recettes de gratins" -> 20)
      const match = title.match(/(\d+)/);
      const totalItems = match ? parseInt(match[1]) : 10;

      const task = await prisma.task.create({
        data: {
          title,
          total_items: totalItems,
          status: 'pending',
          progress: 0,
          current_item: 0
        }
      });

      res.status(201).json({
        success: true,
        data: task
      });
    } catch (error) {
      next(error);
    }
  }

  /**
   * Get all tasks
   * GET /api/tasks
   */
  static async getAll(req: Request, res: Response, next: NextFunction) {
    try {
      const { status } = req.query;

      const tasks = await prisma.task.findMany({
        where: status ? { status: status as string } : undefined,
        orderBy: {
          created_at: 'desc'
        }
      });

      res.json({
        success: true,
        data: tasks,
        count: tasks.length
      });
    } catch (error) {
      next(error);
    }
  }

  /**
   * Get single task by ID
   * GET /api/tasks/:id
   */
  static async getById(req: Request, res: Response, next: NextFunction) {
    try {
      const { id } = req.params;

      const task = await prisma.task.findUnique({
        where: { id: parseInt(id) }
      });

      if (!task) {
        throw new NotFoundError('Task not found');
      }

      res.json({
        success: true,
        data: task
      });
    } catch (error) {
      next(error);
    }
  }

  /**
   * Update task
   * PATCH /api/tasks/:id
   */
  static async update(req: Request, res: Response, next: NextFunction) {
    try {
      const { id } = req.params;
      const updateData = req.body;

      const task = await prisma.task.update({
        where: { id: parseInt(id) },
        data: {
          ...updateData,
          updated_at: new Date()
        }
      });

      res.json({
        success: true,
        data: task
      });
    } catch (error) {
      next(error);
    }
  }

  /**
   * Delete task (cancel)
   * DELETE /api/tasks/:id
   */
  static async delete(req: Request, res: Response, next: NextFunction) {
    try {
      const { id } = req.params;

      await prisma.task.delete({
        where: { id: parseInt(id) }
      });

      res.json({
        success: true,
        message: 'Task deleted successfully'
      });
    } catch (error) {
      next(error);
    }
  }

  /**
   * Get task statistics
   * GET /api/tasks/stats
   */
  static async getStats(req: Request, res: Response, next: NextFunction) {
    try {
      const [pending, processing, completed, failed] = await Promise.all([
        prisma.task.count({ where: { status: 'pending' } }),
        prisma.task.count({ where: { status: 'processing' } }),
        prisma.task.count({ where: { status: 'completed' } }),
        prisma.task.count({ where: { status: 'failed' } })
      ]);

      res.json({
        success: true,
        data: {
          pending,
          processing,
          completed,
          failed,
          total: pending + processing + completed + failed
        }
      });
    } catch (error) {
      next(error);
    }
  }
}
