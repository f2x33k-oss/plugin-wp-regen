# Architecture de l'Application Web Native - CORE

## Vue d'ensemble exécutive

Cette application web native extrait toute la logique métier de WordPress pour créer un système API-first, scalable et sécurisé, capable de gérer l'orchestration multi-API, les tâches d'IA longue durée (15+ minutes), et le suivi des coûts par API.

**Principe fondamental** : WordPress devient un simple client de publication, la logique métier réside entièrement dans le CORE.

---

## 1. DIAGRAMME D'ARCHITECTURE DE HAUT NIVEAU

```
┌─────────────────────────────────────────────────────────────────────┐
│                           CLIENTS EXTERNES                           │
│                                                                       │
│  ┌─────────────────┐         ┌─────────────────┐                   │
│  │  WordPress Site │         │  Future SaaS    │                   │
│  │  (Plugin léger) │         │  Web Dashboard  │                   │
│  └────────┬────────┘         └────────┬────────┘                   │
│           │                            │                             │
└───────────┼────────────────────────────┼─────────────────────────────┘
            │                            │
            │  REST API (Auth: JWT/API Key)
            │                            │
┌───────────▼────────────────────────────▼─────────────────────────────┐
│                          API GATEWAY / LOAD BALANCER                  │
│                      (Rate limiting, Auth, Routing)                   │
└───────────┬───────────────────────────────────────────────────────────┘
            │
┌───────────▼───────────────────────────────────────────────────────────┐
│                           CORE APPLICATION                             │
│                                                                        │
│  ┌─────────────────────────────────────────────────────────────────┐ │
│  │                    API SERVER (Node.js/Python)                   │ │
│  │                                                                   │ │
│  │  • Authentification & Autorisation                               │ │
│  │  • Gestion des utilisateurs & permissions                        │ │
│  │  • Création et suivi des jobs                                    │ │
│  │  • Récupération du contenu finalisé                              │ │
│  │  • Endpoints de diagnostic et debug                              │ │
│  │  • Métriques et coûts par API                                    │ │
│  └───────────────────────────┬───────────────────────────────────────┘ │
│                              │                                         │
│  ┌───────────────────────────▼───────────────────────────────────────┐ │
│  │                    MESSAGE QUEUE (Redis/RabbitMQ)                 │ │
│  │                                                                    │ │
│  │  • Job Queue (priority, retry, dead-letter)                       │ │
│  │  • Task distribution                                              │ │
│  │  • Real-time status updates (pub/sub)                            │ │
│  └───────────────────────────┬───────────────────────────────────────┘ │
│                              │                                         │
│  ┌───────────────────────────▼───────────────────────────────────────┐ │
│  │                    WORKER POOL (Scalable)                         │ │
│  │                                                                    │ │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐           │ │
│  │  │ AI Worker 1  │  │ AI Worker 2  │  │ AI Worker N  │           │ │
│  │  │              │  │              │  │              │           │ │
│  │  │ • OpenAI     │  │ • Anthropic  │  │ • Custom AI  │           │ │
│  │  │ • Scraping   │  │ • Google API │  │ • Social API │           │ │
│  │  │ • Cost track │  │ • Cost track │  │ • Cost track │           │ │
│  │  └──────────────┘  └──────────────┘  └──────────────┘           │ │
│  │                                                                    │ │
│  └────────────────────────────────────────────────────────────────────┘ │
│                                                                        │
└────────────────────────────────────────────────────────────────────────┘
            │                            │
┌───────────▼────────────┐   ┌───────────▼────────────────────────┐
│   DATABASE (Primary)   │   │  SECRETS VAULT (HashiCorp Vault)   │
│   PostgreSQL/MySQL     │   │                                     │
│                        │   │  • API Keys (OpenAI, etc.)         │
│  • Users & Auth        │   │  • OAuth tokens                     │
│  • Jobs & Status       │   │  • Database credentials             │
│  • Content             │   │  • Encryption keys                  │
│  • API Usage & Costs   │   │                                     │
│  • Permissions         │   │  (ou AWS Secrets Manager/GCP SM)   │
└────────────────────────┘   └─────────────────────────────────────┘

            ┌────────────────────────────────────┐
            │   OBJECT STORAGE (S3/GCS/Minio)   │
            │                                    │
            │  • Generated images                │
            │  • Large content files             │
            │  • Job artifacts                   │
            └────────────────────────────────────┘

EXTERNAL APIs (orchestrés par les Workers)
┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│   OpenAI     │  │  Anthropic   │  │ Google APIs  │  │  Social APIs │
└──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘
```

---

## 2. STACK TECHNOLOGIQUE RECOMMANDÉ

### Backend API Server

**Option A (Recommandée) : Node.js + TypeScript**
- **Framework** : Express.js ou Fastify (haute performance)
- **Avantages** :
  - Excellent pour I/O asynchrone
  - Écosystème npm riche
  - Même langage que frontend potentiel
  - Excellente gestion des WebSockets pour temps réel
- **Librairies clés** :
  - `zod` ou `joi` pour validation
  - `prisma` ou `typeorm` pour ORM
  - `passport` ou `jsonwebtoken` pour auth
  - `bull` ou `bullmq` pour queue management

**Option B : Python + FastAPI**
- **Framework** : FastAPI
- **Avantages** :
  - Excellent pour IA/ML (si traitement local)
  - Type hints natifs
  - Auto-documentation OpenAPI
  - Performance élevée (async/await)
- **Librairies clés** :
  - `sqlalchemy` pour ORM
  - `celery` pour workers
  - `pydantic` pour validation
  - `python-jose` pour JWT

**Choix recommandé** : **Node.js + TypeScript** pour cohérence full-stack et performance I/O.

### Message Queue & Workers

- **Queue** : **Redis + BullMQ** (Node.js) ou **Redis + Celery** (Python)
  - Redis pour queue légère et pub/sub
  - Support natif des priorités, retry, dead-letter queue
  - Alternative : RabbitMQ pour cas complexes

- **Workers** :
  - Processus séparés, horizontalement scalables
  - Containerisés (Docker)
  - Auto-scaling basé sur la longueur de la queue

### Base de données

**Primary Database** : **PostgreSQL 15+**
- Relationnel robuste, ACID
- Excellent support JSON/JSONB pour métadonnées flexibles
- Extensions utiles : `pgcrypto`, `uuid-ossp`
- Réplication et backup natifs

**Cache Layer** : **Redis**
- Cache API responses
- Session storage
- Rate limiting
- Real-time pub/sub

### Secrets Management

**Production** : **HashiCorp Vault**
- Gestion centralisée des secrets
- Rotation automatique
- Audit complet
- Alternative cloud-native : AWS Secrets Manager, GCP Secret Manager, Azure Key Vault

**Development** : `.env` avec `dotenv` + `.env.example` template

### Storage d'objets

**Cloud** : AWS S3, Google Cloud Storage, ou Azure Blob
**Self-hosted** : MinIO (compatible S3)

### Frontend (Dashboard d'admin - optionnel pour v1)

- **Framework** : React + TypeScript ou Vue 3
- **UI Library** : Tailwind CSS + shadcn/ui ou Material-UI
- **State** : React Query ou Zustand
- Pour v1 : simple dashboard admin, v2 : full SaaS interface

### Infrastructure & Déploiement

- **Containerisation** : Docker + Docker Compose (dev), Kubernetes (prod scale)
- **CI/CD** : GitHub Actions, GitLab CI, ou CircleCI
- **Monitoring** :
  - **Logs** : Winston/Pino → ELK Stack ou Datadog
  - **Metrics** : Prometheus + Grafana
  - **APM** : New Relic, Datadog, ou Sentry
- **API Gateway** : Nginx, Traefik, ou Kong

---

## 3. SCHÉMA DE BASE DE DONNÉES (Entités principales)

```sql
-- ============================================================
-- USERS & AUTHENTICATION
-- ============================================================

CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL, -- bcrypt/argon2
    full_name VARCHAR(255),
    role VARCHAR(50) NOT NULL DEFAULT 'user', -- admin, user, viewer
    status VARCHAR(50) NOT NULL DEFAULT 'active', -- active, suspended, deleted
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    last_login_at TIMESTAMP,
    
    -- Quotas
    monthly_job_quota INTEGER DEFAULT 100,
    monthly_jobs_used INTEGER DEFAULT 0,
    quota_reset_date DATE,
    
    -- Metadata
    metadata JSONB DEFAULT '{}'::jsonb
);

CREATE TABLE api_keys (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    key_hash VARCHAR(255) NOT NULL UNIQUE, -- SHA-256 du key
    key_prefix VARCHAR(20) NOT NULL, -- ex: "sk_live_abc..." -> "sk_live_"
    name VARCHAR(255), -- nom descriptif
    scopes JSONB DEFAULT '[]'::jsonb, -- ["jobs:create", "jobs:read"]
    status VARCHAR(50) NOT NULL DEFAULT 'active',
    last_used_at TIMESTAMP,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    
    INDEX idx_api_keys_user (user_id),
    INDEX idx_api_keys_hash (key_hash)
);

CREATE TABLE oauth_tokens (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    provider VARCHAR(100) NOT NULL, -- 'google', 'facebook', etc.
    access_token_encrypted TEXT NOT NULL,
    refresh_token_encrypted TEXT,
    token_type VARCHAR(50),
    expires_at TIMESTAMP,
    scopes JSONB DEFAULT '[]'::jsonb,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    
    UNIQUE(user_id, provider),
    INDEX idx_oauth_user (user_id)
);

-- ============================================================
-- JOBS & WORKFLOW
-- ============================================================

CREATE TABLE jobs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id),
    
    -- Job info
    type VARCHAR(100) NOT NULL, -- 'recipe_album', 'idea_carousel', custom types
    status VARCHAR(50) NOT NULL DEFAULT 'pending', 
        -- pending, queued, processing, completed, failed, cancelled
    priority INTEGER DEFAULT 0, -- higher = plus prioritaire
    
    -- Timing
    created_at TIMESTAMP DEFAULT NOW(),
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    estimated_duration_seconds INTEGER,
    
    -- Input/Output
    input_params JSONB NOT NULL DEFAULT '{}'::jsonb, -- params fournis par client
    result_data JSONB, -- résultat finalisé
    error_message TEXT,
    error_stack TEXT,
    
    -- Progress tracking
    progress_percent INTEGER DEFAULT 0,
    current_step VARCHAR(255),
    steps_total INTEGER DEFAULT 1,
    steps_completed INTEGER DEFAULT 0,
    
    -- Cost tracking
    total_cost_usd DECIMAL(10, 4) DEFAULT 0,
    
    -- Metadata
    metadata JSONB DEFAULT '{}'::jsonb,
    
    INDEX idx_jobs_user (user_id),
    INDEX idx_jobs_status (status),
    INDEX idx_jobs_created (created_at DESC),
    INDEX idx_jobs_type (type)
);

CREATE TABLE job_steps (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    job_id UUID NOT NULL REFERENCES jobs(id) ON DELETE CASCADE,
    
    step_name VARCHAR(255) NOT NULL,
    step_order INTEGER NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    
    input_data JSONB,
    output_data JSONB,
    error_message TEXT,
    
    -- Cost for this step
    cost_usd DECIMAL(10, 4) DEFAULT 0,
    
    INDEX idx_job_steps_job (job_id),
    INDEX idx_job_steps_order (job_id, step_order)
);

-- ============================================================
-- CONTENT (résultats finalisés prêts à publier)
-- ============================================================

CREATE TABLE content_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    job_id UUID UNIQUE NOT NULL REFERENCES jobs(id),
    user_id UUID NOT NULL REFERENCES users(id),
    
    -- Type de contenu
    content_type VARCHAR(100) NOT NULL, -- 'recipe_album', 'idea_carousel'
    
    -- Données structurées
    title VARCHAR(500),
    subtitle VARCHAR(500),
    body_html TEXT,
    body_markdown TEXT,
    
    -- Données spécifiques au format
    format_data JSONB NOT NULL DEFAULT '{}'::jsonb,
        -- Pour recipe_album: ingredients[], steps[], images[], etc.
        -- Pour idea_carousel: slides[], images[], etc.
    
    -- Médias
    featured_image_url TEXT,
    media_urls JSONB DEFAULT '[]'::jsonb, -- liste d'URLs
    
    -- Publication
    published_to JSONB DEFAULT '[]'::jsonb, -- [{platform: 'wordpress', url: '...', published_at: '...'}]
    
    -- SEO
    seo_title VARCHAR(255),
    seo_description VARCHAR(500),
    keywords JSONB DEFAULT '[]'::jsonb,
    
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    
    INDEX idx_content_user (user_id),
    INDEX idx_content_type (content_type),
    INDEX idx_content_created (created_at DESC)
);

-- ============================================================
-- API USAGE & COST TRACKING
-- ============================================================

CREATE TABLE api_calls (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    job_id UUID REFERENCES jobs(id) ON DELETE SET NULL,
    job_step_id UUID REFERENCES job_steps(id) ON DELETE SET NULL,
    user_id UUID NOT NULL REFERENCES users(id),
    
    -- API info
    provider VARCHAR(100) NOT NULL, -- 'openai', 'anthropic', 'google', 'scraping_api'
    endpoint VARCHAR(255), -- ex: '/v1/chat/completions'
    method VARCHAR(10),
    
    -- Request/Response
    request_tokens INTEGER,
    response_tokens INTEGER,
    total_tokens INTEGER,
    
    -- Cost
    cost_usd DECIMAL(10, 6) NOT NULL,
    
    -- Status
    status_code INTEGER,
    success BOOLEAN DEFAULT true,
    error_message TEXT,
    
    -- Timing
    duration_ms INTEGER,
    called_at TIMESTAMP DEFAULT NOW(),
    
    -- Metadata
    metadata JSONB DEFAULT '{}'::jsonb,
    
    INDEX idx_api_calls_job (job_id),
    INDEX idx_api_calls_user (user_id),
    INDEX idx_api_calls_provider (provider),
    INDEX idx_api_calls_date (called_at DESC)
);

CREATE TABLE api_cost_summary (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id),
    provider VARCHAR(100) NOT NULL,
    
    -- Période
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    
    -- Métriques
    total_calls INTEGER DEFAULT 0,
    total_cost_usd DECIMAL(10, 2) DEFAULT 0,
    total_tokens INTEGER DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT NOW(),
    
    UNIQUE(user_id, provider, period_start, period_end),
    INDEX idx_cost_summary_user (user_id),
    INDEX idx_cost_summary_period (period_start, period_end)
);

-- ============================================================
-- PERMISSIONS & QUOTAS
-- ============================================================

CREATE TABLE permissions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    role VARCHAR(50) NOT NULL,
    resource VARCHAR(100) NOT NULL, -- 'jobs', 'content', 'users', etc.
    action VARCHAR(50) NOT NULL, -- 'create', 'read', 'update', 'delete'
    
    UNIQUE(role, resource, action)
);

CREATE TABLE user_permissions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    permission_id UUID NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    
    UNIQUE(user_id, permission_id)
);

-- ============================================================
-- SYSTEM & DIAGNOSTICS
-- ============================================================

CREATE TABLE system_events (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    event_type VARCHAR(100) NOT NULL, -- 'job_failed', 'api_error', 'quota_exceeded'
    severity VARCHAR(20) NOT NULL, -- 'info', 'warning', 'error', 'critical'
    
    user_id UUID REFERENCES users(id),
    job_id UUID REFERENCES jobs(id),
    
    message TEXT NOT NULL,
    stack_trace TEXT,
    metadata JSONB DEFAULT '{}'::jsonb,
    
    created_at TIMESTAMP DEFAULT NOW(),
    
    INDEX idx_events_type (event_type),
    INDEX idx_events_severity (severity),
    INDEX idx_events_created (created_at DESC)
);

CREATE TABLE worker_health (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    worker_id VARCHAR(255) UNIQUE NOT NULL,
    worker_type VARCHAR(100) NOT NULL, -- 'ai_worker', 'scraper_worker', etc.
    
    status VARCHAR(50) NOT NULL DEFAULT 'idle', -- idle, busy, error, offline
    current_job_id UUID REFERENCES jobs(id),
    
    -- Stats
    jobs_processed INTEGER DEFAULT 0,
    last_heartbeat_at TIMESTAMP DEFAULT NOW(),
    started_at TIMESTAMP DEFAULT NOW(),
    
    -- Resources
    cpu_percent DECIMAL(5, 2),
    memory_mb INTEGER,
    
    metadata JSONB DEFAULT '{}'::jsonb,
    
    INDEX idx_worker_status (status),
    INDEX idx_worker_heartbeat (last_heartbeat_at)
);
```

---

## 4. SPÉCIFICATION DES ENDPOINTS API

### 4.1 Authentification

```
POST   /api/v1/auth/register          - Créer un compte utilisateur
POST   /api/v1/auth/login             - Login (retourne JWT)
POST   /api/v1/auth/refresh           - Refresh JWT token
POST   /api/v1/auth/logout            - Logout
POST   /api/v1/auth/reset-password    - Demande reset password
GET    /api/v1/auth/me                - Infos utilisateur actuel

POST   /api/v1/api-keys               - Créer une API key
GET    /api/v1/api-keys               - Lister les API keys
DELETE /api/v1/api-keys/:id           - Révoquer une API key
```

### 4.2 Jobs

```
POST   /api/v1/jobs                   - Créer un nouveau job
GET    /api/v1/jobs                   - Lister les jobs (pagination, filtres)
GET    /api/v1/jobs/:id               - Détails d'un job
GET    /api/v1/jobs/:id/status        - Status d'un job (polling)
GET    /api/v1/jobs/:id/progress      - Progression détaillée
DELETE /api/v1/jobs/:id               - Annuler un job
POST   /api/v1/jobs/:id/retry         - Retry un job failed

GET    /api/v1/jobs/:id/steps         - Étapes d'un job
GET    /api/v1/jobs/:id/logs          - Logs d'exécution

# WebSocket (optionnel mais recommandé)
WS     /api/v1/jobs/:id/stream        - Stream real-time du status
```

**Exemple de création de job** :
```json
POST /api/v1/jobs
{
  "type": "recipe_album",
  "input_params": {
    "topic": "Recettes végétariennes italiennes",
    "num_recipes": 5,
    "style": "moderne",
    "image_style": "photographie",
    "target_audience": "débutants"
  },
  "priority": 0
}

Response 201:
{
  "job_id": "550e8400-e29b-41d4-a716-446655440000",
  "status": "queued",
  "estimated_duration_seconds": 900,
  "created_at": "2026-02-03T10:30:00Z"
}
```

### 4.3 Content (résultats finalisés)

```
GET    /api/v1/content                - Lister le contenu disponible
GET    /api/v1/content/:id            - Récupérer un contenu spécifique
GET    /api/v1/content/job/:job_id    - Récupérer contenu par job_id
POST   /api/v1/content/:id/publish    - Marquer comme publié
DELETE /api/v1/content/:id            - Supprimer un contenu
```

**Exemple de récupération de contenu** :
```json
GET /api/v1/content/:id

Response 200:
{
  "id": "content-uuid",
  "job_id": "job-uuid",
  "content_type": "recipe_album",
  "title": "5 Recettes Végétariennes Italiennes Faciles",
  "format_data": {
    "recipes": [
      {
        "title": "Pasta al Pomodoro",
        "prep_time_minutes": 15,
        "cook_time_minutes": 20,
        "servings": 4,
        "ingredients": [
          {"item": "Spaghetti", "quantity": "400g"},
          {"item": "Tomates San Marzano", "quantity": "800g"}
        ],
        "steps": [
          "Faire bouillir l'eau salée...",
          "Préparer la sauce..."
        ],
        "image_url": "https://storage.../pasta-pomodoro.jpg"
      }
      // ... autres recettes
    ],
    "album_cover_image": "https://storage.../album-cover.jpg"
  },
  "media_urls": ["url1", "url2"],
  "seo_title": "...",
  "created_at": "...",
  "published_to": []
}
```

### 4.4 Users & Permissions

```
GET    /api/v1/users/me               - Profil utilisateur actuel
PUT    /api/v1/users/me               - Mettre à jour le profil
GET    /api/v1/users/me/quota         - Consulter les quotas
GET    /api/v1/users/me/usage         - Statistiques d'utilisation

# Admin only
GET    /api/v1/users                  - Lister les utilisateurs
POST   /api/v1/users                  - Créer un utilisateur
PUT    /api/v1/users/:id              - Modifier un utilisateur
DELETE /api/v1/users/:id              - Supprimer un utilisateur
```

### 4.5 Coûts & Analytics

```
GET    /api/v1/costs                  - Résumé des coûts (filtres: date range, provider)
GET    /api/v1/costs/breakdown        - Répartition détaillée
GET    /api/v1/costs/export           - Export CSV

GET    /api/v1/analytics/jobs         - Stats jobs (success rate, avg duration)
GET    /api/v1/analytics/usage        - Usage trends
```

### 4.6 Diagnostics & Debug

```
GET    /api/v1/health                 - Health check (200 OK)
GET    /api/v1/health/detailed        - Status détaillé (DB, Redis, Workers)
GET    /api/v1/metrics                - Métriques Prometheus

GET    /api/v1/debug/jobs/:id         - Debug info pour un job
GET    /api/v1/debug/workers          - État des workers
GET    /api/v1/debug/queue            - État de la queue

# Admin only
GET    /api/v1/system/events          - Événements système
GET    /api/v1/system/logs            - Logs applicatifs
```

### 4.7 OAuth & External Services

```
GET    /api/v1/oauth/:provider/authorize  - Redirect vers OAuth provider
GET    /api/v1/oauth/:provider/callback   - Callback OAuth
DELETE /api/v1/oauth/:provider             - Révoquer connexion
GET    /api/v1/oauth                      - Liste des connexions OAuth
```

---

## 5. CYCLE DE VIE D'UN JOB / WORKER

### 5.1 États d'un Job

```
pending → queued → processing → completed
                  ↓
                failed ← → retrying
                  ↓
               cancelled
```

**États détaillés** :
1. **pending** : Job créé, pas encore dans la queue
2. **queued** : Dans la queue, en attente d'un worker
3. **processing** : Worker actif, job en cours
4. **completed** : Terminé avec succès
5. **failed** : Échec (avec error_message)
6. **retrying** : Retry automatique en cours
7. **cancelled** : Annulé par l'utilisateur ou le système

### 5.2 Workflow détaillé

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. CLIENT (WordPress plugin) envoie requête                     │
│    POST /api/v1/jobs                                             │
└───────────────────────────┬─────────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────────┐
│ 2. API SERVER                                                    │
│    • Valide input_params                                         │
│    • Vérifie quota utilisateur                                   │
│    • Crée job (status: pending)                                  │
│    • Ajoute job à la queue (status: queued)                      │
│    • Retourne job_id au client                                   │
└───────────────────────────┬─────────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────────┐
│ 3. MESSAGE QUEUE (BullMQ/Celery)                                │
│    • Job en attente dans priority queue                          │
│    • Retry logic: 3 tentatives, exponential backoff             │
│    • Dead-letter queue pour jobs échoués                         │
└───────────────────────────┬─────────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────────┐
│ 4. WORKER (disponible) récupère le job                          │
│    • Update job status: processing                               │
│    • Heartbeat régulier (toutes les 30s)                         │
│    • Charge les secrets nécessaires (API keys)                   │
└───────────────────────────┬─────────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────────┐
│ 5. EXÉCUTION (orchestration multi-étapes)                       │
│                                                                   │
│  Exemple: Recipe Album Job                                       │
│                                                                   │
│  Étape 1: Génération du concept (OpenAI)                         │
│    ├─ Appel API OpenAI                                           │
│    ├─ Enregistrement API call + cost                             │
│    ├─ Update progress: 20%                                       │
│    └─ Crée job_step (completed)                                  │
│                                                                   │
│  Étape 2: Recherche d'ingrédients (scraping API)                │
│    ├─ Appel scraping service                                     │
│    ├─ Enregistrement API call + cost                             │
│    ├─ Update progress: 40%                                       │
│    └─ Crée job_step (completed)                                  │
│                                                                   │
│  Étape 3: Génération des images (DALL-E)                         │
│    ├─ Appel OpenAI Images API                                    │
│    ├─ Upload vers S3                                             │
│    ├─ Enregistrement API call + cost                             │
│    ├─ Update progress: 70%                                       │
│    └─ Crée job_step (completed)                                  │
│                                                                   │
│  Étape 4: Formatage final (interne)                              │
│    ├─ Structure le contenu                                       │
│    ├─ Génère HTML/Markdown                                       │
│    ├─ Update progress: 90%                                       │
│    └─ Crée job_step (completed)                                  │
│                                                                   │
│  Étape 5: Sauvegarde du résultat                                 │
│    ├─ Crée content_item                                          │
│    ├─ Update job: status=completed, progress=100%               │
│    └─ Publie événement "job.completed" (pub/sub)                │
│                                                                   │
│  Total cost calculé et sauvegardé                                │
└───────────────────────────┬─────────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────────┐
│ 6. CLIENT (polling ou WebSocket)                                 │
│    • Détecte job completed                                       │
│    • Appelle GET /api/v1/content/job/:job_id                     │
│    • Récupère le contenu finalisé                                │
│    • Publie sur WordPress                                        │
└─────────────────────────────────────────────────────────────────┘
```

### 5.3 Gestion des erreurs et retry

**Stratégie de retry** :
- **Tentatives** : 3 maximum (configurable)
- **Backoff** : Exponentiel (1min, 5min, 15min)
- **Erreurs retryables** :
  - Timeouts réseau
  - Rate limiting (429)
  - Erreurs serveur temporaires (502, 503, 504)
  - Erreurs API tier-3 spécifiques
  
**Erreurs non-retryables** :
- Validation (400)
- Authentification (401, 403)
- Quotas dépassés
- Données invalides

**Dead-letter queue** :
- Jobs échoués après 3 tentatives
- Alerte admin
- Analyse manuelle possible

### 5.4 Timeouts

- **Job total** : 30 minutes (configurable par type)
- **Étape individuelle** : 5 minutes (avec exceptions pour IA lourde)
- **Heartbeat worker** : 30 secondes (si pas de heartbeat → job considéré échoué)

---

## 6. MODÈLE DE SÉCURITÉ

### 6.1 Authentification

#### Option 1: JWT (JSON Web Tokens)
```
Flow:
1. Client → POST /auth/login (email, password)
2. Server → Valide credentials
3. Server → Génère JWT (access token + refresh token)
4. Client → Stocke tokens
5. Client → Envoie access token dans header: Authorization: Bearer <token>
6. Server → Valide token (signature, expiration)

Configuration:
- Access token: 15 minutes
- Refresh token: 7 jours
- Algorithme: RS256 (asymétrique) ou HS256 (symétrique)
- Claims: user_id, email, role, iat, exp
```

#### Option 2: API Keys (pour WordPress plugin)
```
Format: sk_live_<32-char-random> ou sk_test_<32-char-random>
Storage: SHA-256 hash dans DB, jamais en clair
Usage: Header: X-API-Key: sk_live_...
Scopes: ["jobs:create", "jobs:read", "content:read"]
```

#### Option 3: OAuth 2.0 (pour services externes)
```
Providers: Google, Facebook, etc.
Flow: Authorization Code avec PKCE
Token storage: Encrypted dans DB (AES-256)
Rotation: Automatique via refresh token
```

### 6.2 Autorisation (RBAC)

**Rôles** :
- **admin** : Accès complet
- **user** : Création jobs, lecture contenu propre
- **viewer** : Lecture seule

**Permissions** :
```
jobs:create, jobs:read, jobs:update, jobs:delete
content:read, content:delete
users:manage
costs:view
system:manage
```

**Middleware d'autorisation** :
```javascript
// Pseudo-code
function authorize(requiredPermission) {
  return (req, res, next) => {
    const user = req.user; // depuis JWT ou API key
    if (!user.hasPermission(requiredPermission)) {
      return res.status(403).json({ error: "Forbidden" });
    }
    next();
  };
}
```

### 6.3 Gestion des Secrets

**Architecture** :
```
Environment → App → Vault Client → HashiCorp Vault
                                    ↓
                              [Encrypted Storage]
                              - OpenAI API keys
                              - DB passwords
                              - OAuth secrets
                              - Encryption keys
```

**Best practices** :
1. **Jamais en code** : Aucun secret hardcodé
2. **Rotation régulière** : Automatisée via Vault
3. **Least privilege** : Chaque worker n'accède qu'aux secrets nécessaires
4. **Audit** : Tous les accès aux secrets sont loggés
5. **Encryption at rest** : AES-256 pour DB, Vault encrypted backend

**Secrets par environnement** :
```
development: .env local (ignoré par git)
staging: AWS Secrets Manager / Vault
production: HashiCorp Vault cluster (HA)
```

### 6.4 Rate Limiting

**Par endpoint** :
```
/api/v1/jobs (POST): 10 requests/minute/user
/api/v1/jobs (GET): 100 requests/minute/user
/api/v1/content (GET): 200 requests/minute/user
```

**Implémentation** : Redis + sliding window ou token bucket

**Réponse** :
```
HTTP 429 Too Many Requests
{
  "error": "Rate limit exceeded",
  "retry_after_seconds": 42
}
Headers:
X-RateLimit-Limit: 10
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1738588200
```

### 6.5 Sécurité réseau

- **HTTPS obligatoire** : TLS 1.3
- **CORS** : Whitelist des origines autorisées
- **CSRF protection** : Token pour les sessions web
- **Input validation** : Zod/Joi sur tous les endpoints
- **SQL Injection** : ORM (Prisma/SQLAlchemy) + prepared statements
- **XSS** : Sanitization des inputs, CSP headers

### 6.6 Audit & Compliance

- **Logs d'audit** : Toutes les actions sensibles (login, création API key, accès secrets)
- **Retention** : 90 jours minimum (configurable)
- **GDPR** : Endpoints pour export/delete des données utilisateur
- **SOC2** : Framework pour scalabilité future

---

## 7. STRUCTURE DU PLUGIN WORDPRESS (MINIMALISTE)

```
wp-content/plugins/core-app-connector/
│
├── core-app-connector.php          # Main plugin file
│   
├── includes/
│   ├── class-api-client.php        # Client HTTP pour l'API core
│   ├── class-auth.php              # Gestion auth (API key storage)
│   ├── class-job-manager.php       # Création et suivi des jobs
│   ├── class-content-fetcher.php   # Récupération du contenu
│   └── class-publisher.php         # Publication WP (2 formats)
│
├── admin/
│   ├── class-admin-settings.php    # Page de settings (API key, endpoint URL)
│   ├── class-admin-dashboard.php   # Dashboard: liste jobs, status
│   ├── views/
│   │   ├── settings.php            # Vue settings
│   │   └── dashboard.php           # Vue dashboard
│   └── assets/
│       ├── css/admin-styles.css
│       └── js/admin-scripts.js     # AJAX pour polling status
│
├── publishers/
│   ├── class-recipe-album-publisher.php     # Format 1: Recipe Album
│   └── class-idea-carousel-publisher.php    # Format 2: Idea Carousel
│
├── assets/
│   └── js/
│       └── job-poller.js           # Polling status des jobs
│
├── templates/                      # Templates frontend WP (optionnel)
│   ├── recipe-album.php
│   └── idea-carousel.php
│
├── tests/                          # Tests unitaires/intégration
│   └── test-api-client.php
│
├── languages/                      # i18n
│   └── core-app-connector.pot
│
├── README.txt                      # WordPress.org readme
├── composer.json                   # Dependencies (Guzzle, etc.)
└── .env.example                    # Template config
```

### Fonctionnalités du plugin

**1. Settings (page admin)** :
- Champ : URL de l'API Core (ex: `https://core-app.example.com`)
- Champ : API Key (sécurisé, type password)
- Test de connexion (bouton)
- Sélection : Format par défaut (Recipe Album / Idea Carousel)

**2. Dashboard (page admin)** :
- Bouton : "Créer un nouveau job"
- Liste des jobs récents (status, progression, date)
- Actions : Voir détails, Annuler, Publier (si completed)

**3. Workflow "Créer un job"** :
```
1. Admin clique "Créer un job"
2. Modal/Page avec formulaire :
   - Type de job (Recipe Album / Idea Carousel)
   - Paramètres spécifiques (topic, nombre, style, etc.)
3. Soumission → Appel POST /api/v1/jobs
4. Redirection vers dashboard avec job en cours
5. Polling AJAX toutes les 10s pour status update
6. Notification quand completed
7. Bouton "Publier" apparaît
```

**4. Workflow "Publier"** :
```
1. Admin clique "Publier"
2. Plugin appelle GET /api/v1/content/job/:job_id
3. Récupère format_data (structuré)
4. Appelle le publisher approprié :
   - RecipeAlbumPublisher::publish($content)
   - IdeaCarouselPublisher::publish($content)
5. Publisher crée/met à jour un post WordPress :
   - Crée custom post type ou post standard
   - Insère titre, contenu, médias
   - Configure métadonnées
   - Publie ou sauvegarde en draft
6. Appelle POST /api/v1/content/:id/publish (marque comme publié)
7. Affiche succès + lien vers post WP
```

**5. Pas de logique métier** :
- **Zéro** appel direct aux APIs d'IA
- **Zéro** traitement de données complexe
- **Zéro** gestion de queue ou workers
- **Uniquement** : HTTP client, affichage UI, publication WP

**6. Sécurité** :
- API Key stockée en DB (option sécurisée WP)
- Capacités WP : `manage_options` pour accès settings
- Nonces pour toutes les soumissions de formulaires
- Sanitization des inputs

---

## 8. STRATÉGIE DE MIGRATION

### 8.1 Analyse de l'existant

**Phase 1 : Audit (1-2 semaines)**
1. Cartographier les 10k+ lignes actuelles :
   - Identifier tous les hooks, actions, filtres WP utilisés
   - Lister toutes les dépendances (librairies, APIs externes)
   - Documenter les flows de données
   - Identifier les custom post types, taxonomies, meta fields
   
2. Catégoriser le code :
   - **Logique métier pure** (→ Core App) : 60-70%
   - **Intégration WP** (→ Nouveau plugin) : 20-30%
   - **UI/UX spécifique WP** (→ Nouveau plugin) : 10%
   - **Code obsolète** (→ Supprimer) : ?%

3. Identifier les APIs tierces utilisées :
   - OpenAI, Anthropic, etc.
   - Scraping services
   - Google APIs
   - Social media APIs
   - Calculer les coûts actuels

### 8.2 Approche de migration

**Stratégie recommandée : INCREMENTAL (Strangler Fig Pattern)**

```
Phase actuelle:
┌─────────────────────────────────────┐
│    WordPress Plugin (monolithe)    │
│  • Logique métier                   │
│  • API calls                        │
│  • Jobs                             │
│  • Publication                      │
└─────────────────────────────────────┘

Phase transitoire (dual-run):
┌─────────────────────────┐     ┌──────────────────────┐
│  WordPress Plugin       │────→│   Core App (nouveau) │
│  (mode legacy)          │     │                      │
│                         │     │  • Jobs v2           │
│  • Jobs v1 (legacy)     │     │  • API orchestration │
│  • Publication          │     │  • Workers           │
│                         │     │                      │
│  Feature flag:          │     └──────────────────────┘
│    use_core_app = true  │
└─────────────────────────┘

Phase finale:
┌──────────────────┐           ┌───────────────────────┐
│ WP Plugin léger  │──────────→│  Core App (complet)   │
│ • Auth           │           │                       │
│ • UI             │           │  • Toute logique      │
│ • Publication    │           │  • Workers            │
└──────────────────┘           └───────────────────────┘
```

### 8.3 Plan de migration détaillé

**Étape 1 : Fondations du Core App (2-3 semaines)**
- Setup infrastructure (Docker, DB, Redis)
- API server de base
- Système d'authentification
- DB schema initial
- CI/CD pipeline

**Étape 2 : Migration d'un flow simple (1-2 semaines)**
- Choisir 1 type de job simple (ex: génération d'un article basique)
- Implémenter ce flow dans le Core App
- Créer l'endpoint API correspondant
- Feature flag dans WP plugin : essayer Core App, fallback sur legacy

**Étape 3 : Validation et monitoring (1 semaine)**
- Tester en production avec 10% du trafic
- Comparer résultats (qualité, coûts, temps)
- Ajuster
- Augmenter progressivement à 50%, puis 100%

**Étape 4 : Migration des autres flows (4-6 semaines)**
- Flow par flow, un à la fois
- Même approche : implémentation, feature flag, validation
- Prioriser par fréquence d'usage

**Étape 5 : Workers et optimisations (2-3 semaines)**
- Implémenter tous les workers spécialisés
- Optimiser les appels API (batching, caching)
- Fine-tuning du retry logic
- Monitoring et alerting avancés

**Étape 6 : Nouveau plugin WordPress (2 semaines)**
- Développer le plugin léger from scratch
- Implémenter les 2 publishers (Recipe Album, Idea Carousel)
- UI/UX admin
- Tests complets

**Étape 7 : Cutover final (1 semaine)**
- Désactiver tous les feature flags legacy
- Supprimer l'ancien code WP
- Activer le nouveau plugin
- Monitoring intensif pendant 1 semaine

**Étape 8 : Cleanup (1 semaine)**
- Supprimer le code legacy
- Documentation finale
- Formation utilisateurs
- Handoff

**TOTAL estimé : 14-19 semaines** (ajustable selon complexité)

### 8.4 Risques et mitigation

| Risque | Impact | Probabilité | Mitigation |
|--------|--------|-------------|------------|
| Breaking changes dans les formats de contenu | Élevé | Moyen | Tests A/B, validation côte-à-côte, rollback plan |
| Augmentation des coûts API (ré-architecture) | Élevé | Faible | Monitoring coûts en temps réel, alertes, budgets |
| Perte de données en migration | Critique | Faible | Backup complet avant migration, tests de restauration |
| Downtime pendant cutover | Moyen | Moyen | Déploiement blue-green, rollback automatique |
| Résistance utilisateurs (nouveau UI) | Faible | Moyen | Formation, documentation, support réactif |
| Bugs dans le nouveau code | Moyen | Élevé | Tests automatisés (>80% coverage), QA manuelle |

### 8.5 Rollback plan

À chaque étape :
1. **Garder l'ancien code actif** jusqu'à validation complète
2. **Feature flags** pour activer/désactiver nouvelle logique
3. **Monitoring** : alertes si taux d'erreur > 1%
4. **Rollback automatique** : si métriques critiques dégradées
5. **Plan de communication** : informer utilisateurs en cas d'incident

---

## 9. STRUCTURE DE DOSSIERS DU CORE APP

```
core-app/
│
├── .github/
│   └── workflows/
│       ├── ci.yml                  # Tests, lint, build
│       └── deploy.yml              # Déploiement automatisé
│
├── apps/
│   ├── api/                        # API Server principal
│   │   ├── src/
│   │   │   ├── index.ts            # Entry point
│   │   │   ├── config/
│   │   │   │   ├── database.ts
│   │   │   │   ├── redis.ts
│   │   │   │   └── secrets.ts
│   │   │   ├── middleware/
│   │   │   │   ├── auth.ts         # JWT/API key validation
│   │   │   │   ├── rateLimit.ts
│   │   │   │   ├── errorHandler.ts
│   │   │   │   └── logger.ts
│   │   │   ├── routes/
│   │   │   │   ├── auth.routes.ts
│   │   │   │   ├── jobs.routes.ts
│   │   │   │   ├── content.routes.ts
│   │   │   │   ├── users.routes.ts
│   │   │   │   ├── costs.routes.ts
│   │   │   │   └── health.routes.ts
│   │   │   ├── controllers/
│   │   │   │   ├── AuthController.ts
│   │   │   │   ├── JobsController.ts
│   │   │   │   ├── ContentController.ts
│   │   │   │   └── UsersController.ts
│   │   │   ├── services/
│   │   │   │   ├── AuthService.ts
│   │   │   │   ├── JobService.ts
│   │   │   │   ├── QueueService.ts
│   │   │   │   ├── StorageService.ts
│   │   │   │   └── CostTrackingService.ts
│   │   │   ├── models/              # DB models (Prisma/TypeORM)
│   │   │   │   ├── User.ts
│   │   │   │   ├── Job.ts
│   │   │   │   ├── Content.ts
│   │   │   │   └── ApiCall.ts
│   │   │   ├── validators/
│   │   │   │   └── schemas.ts       # Zod schemas
│   │   │   └── utils/
│   │   │       ├── jwt.ts
│   │   │       ├── hash.ts
│   │   │       └── logger.ts
│   │   ├── tests/
│   │   │   ├── unit/
│   │   │   ├── integration/
│   │   │   └── e2e/
│   │   ├── package.json
│   │   ├── tsconfig.json
│   │   ├── Dockerfile
│   │   └── .env.example
│   │
│   ├── workers/                     # Workers (job processing)
│   │   ├── src/
│   │   │   ├── index.ts             # Worker entry point
│   │   │   ├── processors/
│   │   │   │   ├── BaseProcessor.ts
│   │   │   │   ├── RecipeAlbumProcessor.ts
│   │   │   │   ├── IdeaCarouselProcessor.ts
│   │   │   │   └── CustomJobProcessor.ts
│   │   │   ├── orchestrators/
│   │   │   │   ├── AIOrchestrator.ts      # Gère OpenAI, Anthropic, etc.
│   │   │   │   ├── ScrapingOrchestrator.ts
│   │   │   │   ├── ImageOrchestrator.ts
│   │   │   │   └── SocialOrchestrator.ts
│   │   │   ├── clients/                    # API clients externes
│   │   │   │   ├── OpenAIClient.ts
│   │   │   │   ├── AnthropicClient.ts
│   │   │   │   ├── GoogleAPIClient.ts
│   │   │   │   └── ScrapingAPIClient.ts
│   │   │   ├── services/
│   │   │   │   ├── CostCalculator.ts
│   │   │   │   ├── StorageService.ts
│   │   │   │   └── ProgressTracker.ts
│   │   │   └── utils/
│   │   │       ├── retry.ts
│   │   │       └── timeout.ts
│   │   ├── tests/
│   │   ├── package.json
│   │   ├── Dockerfile
│   │   └── .env.example
│   │
│   └── admin-dashboard/             # Frontend admin (optionnel v1)
│       ├── src/
│       │   ├── pages/
│       │   ├── components/
│       │   ├── hooks/
│       │   └── api/
│       ├── public/
│       ├── package.json
│       └── vite.config.ts
│
├── packages/                        # Shared packages (monorepo)
│   ├── types/                       # Types TypeScript partagés
│   │   ├── src/
│   │   │   ├── Job.ts
│   │   │   ├── User.ts
│   │   │   ├── Content.ts
│   │   │   └── API.ts
│   │   └── package.json
│   │
│   ├── db/                          # DB client & migrations
│   │   ├── prisma/
│   │   │   ├── schema.prisma
│   │   │   └── migrations/
│   │   ├── seeds/
│   │   └── package.json
│   │
│   └── shared/                      # Utils partagés
│       ├── src/
│       │   ├── logger.ts
│       │   ├── constants.ts
│       │   └── errors.ts
│       └── package.json
│
├── infrastructure/
│   ├── docker/
│   │   ├── docker-compose.yml       # Dev environment
│   │   ├── docker-compose.prod.yml  # Prod stack
│   │   ├── api.Dockerfile
│   │   ├── worker.Dockerfile
│   │   └── nginx.conf
│   │
│   ├── kubernetes/                  # K8s manifests (pour scale)
│   │   ├── api-deployment.yaml
│   │   ├── worker-deployment.yaml
│   │   ├── redis-statefulset.yaml
│   │   ├── postgres-statefulset.yaml
│   │   ├── ingress.yaml
│   │   └── secrets.yaml
│   │
│   ├── terraform/                   # IaC (AWS/GCP/Azure)
│   │   ├── main.tf
│   │   ├── variables.tf
│   │   ├── outputs.tf
│   │   └── modules/
│   │
│   └── vault/                       # Vault configuration
│       └── policies/
│
├── scripts/
│   ├── setup-dev.sh                 # Setup local dev
│   ├── migrate-db.sh                # Run migrations
│   ├── seed-db.sh                   # Seed data
│   ├── generate-api-key.sh          # Generate API keys
│   └── deploy.sh                    # Deploy script
│
├── docs/
│   ├── API.md                       # API documentation
│   ├── ARCHITECTURE.md              # Ce document
│   ├── DEPLOYMENT.md                # Guide de déploiement
│   ├── DEVELOPMENT.md               # Guide développeur
│   ├── MIGRATION.md                 # Guide de migration
│   └── SECURITY.md                  # Security guidelines
│
├── tests/
│   ├── load/                        # Load tests (k6, JMeter)
│   └── e2e/                         # E2E tests (Playwright)
│
├── .gitignore
├── .env.example
├── package.json                     # Root package.json (monorepo)
├── turbo.json                       # Turborepo config (si monorepo)
├── pnpm-workspace.yaml              # pnpm workspaces (si monorepo)
├── README.md
└── LICENSE
```

### Justification de la structure

**Monorepo** :
- **Avantages** : Code sharing facile, versions synchronisées, refactoring simplifié
- **Outils** : Turborepo, pnpm workspaces, ou Nx
- **Alternative** : Multi-repos séparés (API, Workers, Dashboard) si équipes distinctes

**Séparation API / Workers** :
- **Scaling indépendant** : Workers peuvent être scalés sans impacter l'API
- **Isolation** : Crash d'un worker n'affecte pas l'API
- **Déploiement** : Mise à jour workers sans downtime API

**Packages partagés** :
- **types/** : Contrats d'interface partagés
- **db/** : Single source of truth pour le schéma DB
- **shared/** : Évite la duplication de code

---

## 10. PUBLICATION DES DEUX FORMATS WORDPRESS

### 10.1 Format 1 : Recipe Album

**Description** : Un album de recettes (3-10 recettes) avec images, ingrédients, étapes.

**Structure de données (format_data)** :
```json
{
  "album_title": "5 Recettes Végétariennes Italiennes",
  "album_description": "Des classiques revisités...",
  "album_cover_image": "https://storage.../cover.jpg",
  "recipes": [
    {
      "id": "recipe-1",
      "title": "Pasta al Pomodoro",
      "description": "Un classique simple et délicieux",
      "prep_time_minutes": 15,
      "cook_time_minutes": 20,
      "servings": 4,
      "difficulty": "facile",
      "ingredients": [
        {
          "item": "Spaghetti",
          "quantity": "400",
          "unit": "g",
          "notes": "ou autre pasta longue"
        },
        {
          "item": "Tomates San Marzano",
          "quantity": "800",
          "unit": "g",
          "notes": "en conserve"
        }
      ],
      "steps": [
        {
          "step_number": 1,
          "instruction": "Faire bouillir l'eau salée pour les pâtes.",
          "image_url": "https://storage.../step1.jpg"
        },
        {
          "step_number": 2,
          "instruction": "Préparer la sauce tomate...",
          "image_url": null
        }
      ],
      "featured_image": "https://storage.../pasta-pomodoro.jpg",
      "nutrition_info": {
        "calories": 450,
        "protein_g": 12,
        "carbs_g": 80,
        "fat_g": 8
      },
      "tags": ["végétarien", "italien", "facile", "comfort food"]
    }
    // ... autres recettes
  ],
  "seo": {
    "meta_description": "Découvrez 5 recettes...",
    "keywords": ["recettes végétariennes", "cuisine italienne"]
  }
}
```

**Publication WordPress** :

```php
// publishers/class-recipe-album-publisher.php

class RecipeAlbumPublisher {
    
    public function publish($content) {
        $format_data = $content['format_data'];
        
        // 1. Créer le post parent (l'album)
        $album_post_id = wp_insert_post([
            'post_title'   => $format_data['album_title'],
            'post_content' => $this->build_album_html($format_data),
            'post_type'    => 'recipe_album', // custom post type
            'post_status'  => 'publish',
            'meta_input'   => [
                'core_content_id'   => $content['id'],
                'core_job_id'       => $content['job_id'],
                'album_cover_image' => $format_data['album_cover_image'],
            ]
        ]);
        
        // 2. Créer chaque recette comme post enfant
        foreach ($format_data['recipes'] as $recipe) {
            $recipe_post_id = wp_insert_post([
                'post_title'   => $recipe['title'],
                'post_content' => $this->build_recipe_html($recipe),
                'post_type'    => 'recipe',
                'post_parent'  => $album_post_id,
                'post_status'  => 'publish',
                'meta_input'   => [
                    'prep_time'       => $recipe['prep_time_minutes'],
                    'cook_time'       => $recipe['cook_time_minutes'],
                    'servings'        => $recipe['servings'],
                    'difficulty'      => $recipe['difficulty'],
                    'ingredients'     => json_encode($recipe['ingredients']),
                    'steps'           => json_encode($recipe['steps']),
                    'nutrition_info'  => json_encode($recipe['nutrition_info']),
                ]
            ]);
            
            // 3. Attacher l'image featured
            $this->attach_featured_image($recipe_post_id, $recipe['featured_image']);
            
            // 4. Assigner les tags
            wp_set_post_terms($recipe_post_id, $recipe['tags'], 'post_tag');
        }
        
        return [
            'success'    => true,
            'album_id'   => $album_post_id,
            'album_url'  => get_permalink($album_post_id),
        ];
    }
    
    private function build_album_html($format_data) {
        // Génère HTML avec Gutenberg blocks ou HTML classique
        $html = '<div class="recipe-album">';
        $html .= '<div class="album-intro">' . esc_html($format_data['album_description']) . '</div>';
        $html .= '<div class="recipes-grid">';
        
        foreach ($format_data['recipes'] as $recipe) {
            $html .= '<div class="recipe-card">';
            $html .= '<img src="' . esc_url($recipe['featured_image']) . '" />';
            $html .= '<h3>' . esc_html($recipe['title']) . '</h3>';
            $html .= '<p>' . esc_html($recipe['description']) . '</p>';
            $html .= '</div>';
        }
        
        $html .= '</div></div>';
        return $html;
    }
    
    private function build_recipe_html($recipe) {
        // Template HTML pour une recette complète
        // Peut utiliser un template de thème ou générer HTML
    }
}
```

**Résultat WP** :
- 1 post type `recipe_album` (liste des recettes)
- N posts type `recipe` (chaque recette détaillée)
- Hiérarchie parent-enfant
- Images importées dans media library
- SEO optimisé

---

### 10.2 Format 2 : Idea Carousel

**Description** : Un carousel d'idées/conseils (5-15 slides) avec image et texte court par slide.

**Structure de données (format_data)** :
```json
{
  "carousel_title": "10 Astuces pour Jardiner en Appartement",
  "carousel_description": "Des idées pratiques...",
  "slides": [
    {
      "slide_number": 1,
      "title": "Utilisez des pots suspendus",
      "content": "Maximisez l'espace vertical en suspendant vos plantes...",
      "image_url": "https://storage.../slide1.jpg",
      "cta_text": "En savoir plus",
      "cta_url": "https://example.com/pots-suspendus",
      "tags": ["astuce", "espace"]
    },
    {
      "slide_number": 2,
      "title": "Choisissez des plantes adaptées",
      "content": "Privilégiez les herbes aromatiques qui supportent...",
      "image_url": "https://storage.../slide2.jpg",
      "cta_text": null,
      "cta_url": null,
      "tags": ["plantes", "choix"]
    }
    // ... 8 autres slides
  ],
  "carousel_style": {
    "theme": "modern",
    "primary_color": "#4CAF50",
    "font": "Roboto"
  },
  "seo": {
    "meta_description": "10 astuces pratiques...",
    "keywords": ["jardinage", "appartement", "astuces"]
  }
}
```

**Publication WordPress** :

```php
// publishers/class-idea-carousel-publisher.php

class IdeaCarouselPublisher {
    
    public function publish($content) {
        $format_data = $content['format_data'];
        
        // 1. Créer le post carousel
        $carousel_post_id = wp_insert_post([
            'post_title'   => $format_data['carousel_title'],
            'post_content' => $this->build_carousel_html($format_data),
            'post_type'    => 'idea_carousel', // custom post type
            'post_status'  => 'publish',
            'meta_input'   => [
                'core_content_id'    => $content['id'],
                'core_job_id'        => $content['job_id'],
                'carousel_style'     => json_encode($format_data['carousel_style']),
                'slides_data'        => json_encode($format_data['slides']),
                'num_slides'         => count($format_data['slides']),
            ]
        ]);
        
        // 2. Importer toutes les images dans media library
        $slide_image_ids = [];
        foreach ($format_data['slides'] as $slide) {
            $image_id = $this->import_image($slide['image_url'], $carousel_post_id);
            $slide_image_ids[$slide['slide_number']] = $image_id;
        }
        
        update_post_meta($carousel_post_id, 'slide_image_ids', $slide_image_ids);
        
        // 3. Assigner taxonomies/tags
        $all_tags = [];
        foreach ($format_data['slides'] as $slide) {
            $all_tags = array_merge($all_tags, $slide['tags']);
        }
        wp_set_post_terms($carousel_post_id, array_unique($all_tags), 'post_tag');
        
        return [
            'success'      => true,
            'carousel_id'  => $carousel_post_id,
            'carousel_url' => get_permalink($carousel_post_id),
        ];
    }
    
    private function build_carousel_html($format_data) {
        // Option 1: Générer des Gutenberg blocks (recommandé)
        return $this->build_gutenberg_carousel($format_data);
        
        // Option 2: HTML + JS carousel library (Slick, Swiper)
        // return $this->build_js_carousel($format_data);
    }
    
    private function build_gutenberg_carousel($format_data) {
        // Génère des blocks Gutenberg pour un carousel natif
        $blocks = '<!-- wp:group {"className":"idea-carousel"} -->';
        
        foreach ($format_data['slides'] as $slide) {
            $blocks .= '<!-- wp:media-text {"mediaUrl":"' . $slide['image_url'] . '"} -->';
            $blocks .= '<div class="wp-block-media-text">';
            $blocks .= '<h3>' . esc_html($slide['title']) . '</h3>';
            $blocks .= '<p>' . esc_html($slide['content']) . '</p>';
            if ($slide['cta_url']) {
                $blocks .= '<a href="' . esc_url($slide['cta_url']) . '" class="wp-button">';
                $blocks .= esc_html($slide['cta_text']) . '</a>';
            }
            $blocks .= '</div>';
            $blocks .= '<!-- /wp:media-text -->';
        }
        
        $blocks .= '<!-- /wp:group -->';
        return $blocks;
    }
    
    private function import_image($image_url, $post_id) {
        // Télécharge l'image et l'importe dans media library
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $image_id = media_sideload_image($image_url, $post_id, null, 'id');
        return $image_id;
    }
}
```

**Résultat WP** :
- 1 post type `idea_carousel`
- Images importées dans media library
- Carousel interactif (via blocks ou JS library)
- Navigation prev/next
- Responsive design

---

### 10.3 Architecture de publication commune

```
┌──────────────────────────────────────────────────────────────┐
│  WordPress Admin Dashboard                                   │
│                                                               │
│  [Job Status: Completed] ✓                                   │
│  [Bouton: Publier]                                           │
└───────────────────────┬──────────────────────────────────────┘
                        │ Click
                        ▼
┌──────────────────────────────────────────────────────────────┐
│  Plugin: JobManager::publish($job_id)                        │
│                                                               │
│  1. GET /api/v1/content/job/:job_id                          │
│  2. Détecte content_type                                     │
│  3. Route vers le bon publisher:                             │
│     - recipe_album → RecipeAlbumPublisher                    │
│     - idea_carousel → IdeaCarouselPublisher                  │
└───────────────────────┬──────────────────────────────────────┘
                        │
                        ▼
┌──────────────────────────────────────────────────────────────┐
│  Publisher spécifique                                         │
│                                                               │
│  • Parse format_data                                         │
│  • Crée/met à jour posts WP                                  │
│  • Importe médias                                            │
│  • Configure SEO                                             │
│  • Retourne URL du post publié                               │
└───────────────────────┬──────────────────────────────────────┘
                        │
                        ▼
┌──────────────────────────────────────────────────────────────┐
│  POST /api/v1/content/:id/publish                            │
│                                                               │
│  Body: {                                                      │
│    "platform": "wordpress",                                  │
│    "url": "https://mon-site.com/recipe-album/...",          │
│    "published_at": "2026-02-03T12:00:00Z"                   │
│  }                                                            │
│                                                               │
│  → Core App met à jour content_item.published_to             │
└──────────────────────────────────────────────────────────────┘
```

**Avantages de cette approche** :
1. **Séparation des responsabilités** : WP ne sait pas comment le contenu a été généré
2. **Flexibilité** : Facile d'ajouter d'autres formats (Video Reel, Infographic, etc.)
3. **Traçabilité** : Core App sait où le contenu a été publié
4. **Réutilisabilité** : Le même contenu peut être publié sur plusieurs plateformes

---

## CONCLUSION ET PROCHAINES ÉTAPES

### Résumé

Vous avez maintenant une architecture complète pour :
1. ✅ Extraire toute la logique métier de WordPress
2. ✅ Construire un Core App scalable, API-first
3. ✅ Gérer des jobs longue durée (15+ min) avec workers
4. ✅ Orchestrer multiples APIs (IA, scraping, social, Google)
5. ✅ Tracker les coûts par API
6. ✅ Sécuriser les secrets (Vault)
7. ✅ Réduire WordPress à un simple client de publication
8. ✅ Publier 2 formats : Recipe Album & Idea Carousel
9. ✅ Préparer la scalabilité v3.0 (SaaS multi-tenant)

### Validation de la conception

Avant de passer au code, je recommande de :

1. **Valider les besoins métier**
   - Les 2 formats (Recipe Album, Idea Carousel) couvrent-ils tous les cas d'usage v1 ?
   - Y a-t-il des workflows critiques non documentés dans les 10k lignes existantes ?

2. **Valider les choix techniques**
   - Stack : Node.js + TypeScript + PostgreSQL + Redis + BullMQ → OK ?
   - Déploiement : Docker Compose (dev) + Kubernetes (prod) → OK ?
   - Secrets : HashiCorp Vault ou cloud-native (AWS Secrets Manager) → OK ?

3. **Valider la stratégie de migration**
   - Migration incrémentale (Strangler Fig) → OK ?
   - Timeline 14-19 semaines → réaliste ?
   - Ressources : combien de développeurs ? DevOps disponible ?

### Prochaines étapes

Une fois l'architecture validée, je suis prêt à :

1. **Scaffolder le projet complet**
   - Structure de dossiers
   - Configuration Docker / Docker Compose
   - Setup Prisma + DB schema
   - API server initial (Express + TypeScript)
   - Workers initial (BullMQ)
   - CI/CD GitHub Actions

2. **Implémenter un flow complet end-to-end**
   - Exemple : Recipe Album
   - De la création du job à la publication WP
   - Démonstrateur fonctionnel

3. **Documentation développeur**
   - Guide de setup local
   - Guide de contribution
   - API documentation (OpenAPI/Swagger)

**Êtes-vous prêt à passer au scaffolding du code ?**

Si oui, je vais créer :
- La structure complète du monorepo
- Le schema Prisma
- L'API server de base
- Le système de workers
- Le Docker Compose dev
- Le plugin WordPress minimal
- Les scripts de setup

Confirmez-moi et je commence immédiatement ! 🚀
