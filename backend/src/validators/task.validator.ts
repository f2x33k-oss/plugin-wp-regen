import { z } from 'zod';

export const createTaskSchema = z.object({
  title: z.string()
    .min(5, 'Title must be at least 5 characters')
    .max(255, 'Title must be less than 255 characters')
    .regex(/\d+/, 'Title must contain a number (e.g., "20 recettes de gratins")')
});

export const updateTaskSchema = z.object({
  status: z.enum(['pending', 'processing', 'completed', 'failed']).optional(),
  progress: z.number().min(0).max(100).optional(),
  current_item: z.number().min(0).optional(),
  text_url: z.string().url().optional().nullable(),
  zip_url: z.string().url().optional().nullable(),
  error_message: z.string().optional().nullable()
});

export type CreateTaskInput = z.infer<typeof createTaskSchema>;
export type UpdateTaskInput = z.infer<typeof updateTaskSchema>;
