# Recipe Album Generator - Backend API

## Phase 1: Core API (COMPLETE)

API REST pour créer et gérer des tâches de génération d'albums recettes.

---

## 🚀 Setup

### 1. Démarrer PostgreSQL

```bash
# Depuis la racine du projet
docker-compose up -d
```

### 2. Installer les dépendances

```bash
cd backend
npm install
```

### 3. Générer Prisma Client & Migrations

```bash
npm run db:generate
npm run db:migrate
```

### 4. Démarrer le serveur

```bash
npm run dev
```

Le serveur démarre sur **http://localhost:3000**

---

## 📡 Endpoints Disponibles

### Health Check

```bash
GET /health
```

**Response:**
```json
{
  "status": "ok",
  "timestamp": "2026-02-03T10:30:00.000Z",
  "service": "recipe-album-api"
}
```

---

### Auth

#### Get Current User (hardcoded)

```bash
GET /api/auth/me
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Demo User",
    "email": "demo@example.com"
  }
}
```

---

### Tasks

#### Create Task

```bash
POST /api/tasks
Content-Type: application/json

{
  "title": "20 recettes de gratins"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "20 recettes de gratins",
    "status": "pending",
    "progress": 0,
    "total_items": 20,
    "current_item": 0,
    "generated_text": null,
    "generated_images": null,
    "text_url": null,
    "zip_url": null,
    "error_message": null,
    "created_at": "2026-02-03T10:30:00.000Z",
    "updated_at": "2026-02-03T10:30:00.000Z",
    "started_at": null,
    "completed_at": null
  }
}
```

#### Get All Tasks

```bash
GET /api/tasks
```

**Optional query params:**
- `?status=pending`
- `?status=processing`
- `?status=completed`
- `?status=failed`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "20 recettes de gratins",
      "status": "pending",
      "progress": 0,
      "total_items": 20,
      "current_item": 0,
      "created_at": "2026-02-03T10:30:00.000Z",
      ...
    }
  ],
  "count": 1
}
```

#### Get Single Task

```bash
GET /api/tasks/1
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "20 recettes de gratins",
    "status": "pending",
    ...
  }
}
```

#### Update Task

```bash
PATCH /api/tasks/1
Content-Type: application/json

{
  "status": "processing",
  "progress": 50,
  "current_item": 10
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "status": "processing",
    "progress": 50,
    "current_item": 10,
    ...
  }
}
```

#### Delete Task

```bash
DELETE /api/tasks/1
```

**Response:**
```json
{
  "success": true,
  "message": "Task deleted successfully"
}
```

#### Get Statistics

```bash
GET /api/tasks/stats
```

**Response:**
```json
{
  "success": true,
  "data": {
    "pending": 2,
    "processing": 1,
    "completed": 5,
    "failed": 0,
    "total": 8
  }
}
```

---

## 🧪 Test avec curl

```bash
# Health check
curl http://localhost:3000/health

# Create task
curl -X POST http://localhost:3000/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"title": "20 recettes de gratins"}'

# Get all tasks
curl http://localhost:3000/api/tasks

# Get task by ID
curl http://localhost:3000/api/tasks/1

# Update task
curl -X PATCH http://localhost:3000/api/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"status": "processing", "progress": 25}'

# Get stats
curl http://localhost:3000/api/tasks/stats
```

---

## ❌ Erreurs

### Validation Error

```json
{
  "error": "Validation error",
  "details": [
    {
      "field": "title",
      "message": "Title must contain a number (e.g., \"20 recettes de gratins\")"
    }
  ]
}
```

### Not Found

```json
{
  "error": "Not found",
  "message": "Task not found"
}
```

### Internal Error

```json
{
  "error": "Internal server error",
  "message": "Something went wrong"
}
```

---

## 📂 Structure

```
backend/
├── src/
│   ├── controllers/
│   │   ├── auth.controller.ts
│   │   └── task.controller.ts
│   ├── routes/
│   │   ├── auth.ts
│   │   └── tasks.ts
│   ├── middleware/
│   │   ├── auth.ts
│   │   ├── errorHandler.ts
│   │   ├── notFoundHandler.ts
│   │   └── validate.ts
│   ├── validators/
│   │   └── task.validator.ts
│   ├── lib/
│   │   ├── errors.ts
│   │   └── prisma.ts
│   └── index.ts
├── prisma/
│   └── schema.prisma
├── package.json
├── tsconfig.json
└── .env
```

---

## ✅ Phase 1 Complete

**Ce qui fonctionne:**
- ✅ API REST avec Express + TypeScript
- ✅ Base de données PostgreSQL (via Prisma)
- ✅ CRUD complet pour les tasks
- ✅ Validation des entrées (Zod)
- ✅ Gestion d'erreurs normalisée
- ✅ Auth basique (hardcoded user)

**Ce qui N'est PAS fait:**
- ❌ Génération IA (Gemini, SDXL)
- ❌ Workers asynchrones
- ❌ File d'attente (Bull)
- ❌ WebSockets temps réel
- ❌ Emails
- ❌ Téléchargements fichiers
- ❌ Frontend
