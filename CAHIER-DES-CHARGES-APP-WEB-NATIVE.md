# 📘 CAHIER DES CHARGES - App Web Native Albums Recettes + Idées

**Basé sur** : AI Content Factory Pro v5.1.1 (WordPress)  
**Modules** : Albums Recettes + Albums Idées uniquement  
**Objectif** : App web standalone (React, Vue ou Vanilla JS)  
**Date** : Février 2026

---

## 🎯 OBJECTIF

Créer une **application web native** (SPA) permettant de générer automatiquement :
- **Albums recettes** avec textes et images via IA
- **Albums idées** visuels pour carrousels sociaux

**Sans WordPress** - Fonctionnement autonome

---

## 📦 MODULES À IMPLÉMENTER (2)

### 1. Albums Recettes 🍽️

**Description** :  
Application de génération d'albums de recettes avec textes et images via IA

**Fonctionnalités principales** :
1. Formulaire de création
2. Suggestions titres intelligentes
3. Recherche images Pinterest/Instagram
4. Sélection moteurs IA (texte + images)
5. Génération asynchrone
6. Suivi temps réel
7. Téléchargements (texte + images)
8. Emails de notification

---

### 2. Albums Idées 💡

**Description** :  
Génération d'albums visuels pour carrousels sociaux

**Fonctionnalités principales** :
1. Formulaire simplifié
2. Sélection style visuel
3. Sélection format image
4. Upload images référence
5. Génération images uniquement
6. Téléchargement ZIP

---

## 🔧 ARCHITECTURE TECHNIQUE

### Stack Recommandé

**Frontend** :
- React 18+ avec Vite ou Next.js
- TypeScript pour type safety
- TailwindCSS pour styling
- Zustand ou Redux pour state management
- React Query pour API calls
- Axios pour HTTP

**Backend** :
- Node.js + Express
- PostgreSQL ou MongoDB
- Bull Queue pour file d'attente asynchrone
- Socket.io pour temps réel
- JWT pour authentification

**Infrastructure** :
- Docker pour déploiement
- Redis pour cache et queue
- S3 ou CloudFlare R2 pour stockage fichiers

---

## 📊 BASE DE DONNÉES

### Table `tasks` (Tâches génération)

```sql
CREATE TABLE tasks (
  id SERIAL PRIMARY KEY,
  user_id INTEGER REFERENCES users(id),
  type VARCHAR(50) NOT NULL, -- 'recettes' ou 'idees'
  title VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  
  -- Configuration
  generate_text BOOLEAN DEFAULT true,
  text_engine VARCHAR(50), -- 'chatgpt', 'gemini', 'claude'
  image_engine VARCHAR(50), -- 'sdxl-lightning', 'midjourney', etc.
  article_format VARCHAR(50), -- 'global' ou 'individual'
  generation_order VARCHAR(50), -- 'image_first' ou 'text_first'
  
  -- Pinterest/Instagram
  reference_images JSONB,
  pinterest_images JSONB,
  
  -- Progression
  status VARCHAR(50) DEFAULT 'pending', -- pending, processing, paused, completed, failed
  progress INTEGER DEFAULT 0,
  total_items INTEGER,
  current_item INTEGER DEFAULT 0,
  
  -- Résultats
  generated_images JSONB,
  generated_content JSONB,
  article_url TEXT,
  download_text_url TEXT,
  download_images_url TEXT,
  
  -- Erreurs et logs
  prompts_log JSONB,
  error_log JSONB,
  
  -- Coûts et temps
  cost_estimate DECIMAL(10,2),
  time_estimate INTEGER,
  
  -- Timestamps
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW(),
  started_at TIMESTAMP,
  completed_at TIMESTAMP
);

CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_user ON tasks(user_id);
CREATE INDEX idx_tasks_created ON tasks(created_at DESC);
```

### Table `users`

```sql
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  name VARCHAR(255),
  api_keys JSONB, -- Stockage chiffré des clés API
  preferences JSONB, -- Préférences utilisateur
  created_at TIMESTAMP DEFAULT NOW()
);
```

### Table `api_usage` (Tracking usage)

```sql
CREATE TABLE api_usage (
  id SERIAL PRIMARY KEY,
  user_id INTEGER REFERENCES users(id),
  api_name VARCHAR(100),
  api_type VARCHAR(50), -- 'text' ou 'image'
  calls_count INTEGER DEFAULT 0,
  tokens_used INTEGER,
  cost DECIMAL(10,4),
  date DATE DEFAULT CURRENT_DATE
);
```

---

## 🔗 APIS À INTÉGRER

### APIs Texte (3)

#### 1. OpenAI ChatGPT GPT-4o

**Endpoint** : `https://api.openai.com/v1/chat/completions`

**Headers** :
```json
{
  "Content-Type": "application/json",
  "Authorization": "Bearer {api_key}"
}
```

**Body** :
```json
{
  "model": "gpt-4o",
  "messages": [
    {
      "role": "system",
      "content": "Tu es un chef cuisinier expert..."
    },
    {
      "role": "user",
      "content": "Écris une recette de {titre}..."
    }
  ],
  "max_tokens": 2000,
  "temperature": 0.7
}
```

**Response** :
```json
{
  "choices": [{
    "message": {
      "content": "Texte de la recette..."
    }
  }]
}
```

**Coût** : ~$0.02 par recette

---

#### 2. Google Gemini Pro

**Endpoint** : `https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={api_key}`

**Body** :
```json
{
  "contents": [{
    "parts": [{
      "text": "Écris une recette de {titre}..."
    }]
  }]
}
```

**Response** :
```json
{
  "candidates": [{
    "content": {
      "parts": [{
        "text": "Texte de la recette..."
      }]
    }
  }]
}
```

**Coût** : Gratuit (quota généreux)

---

#### 3. Anthropic Claude 3 Sonnet

**Endpoint** : `https://api.anthropic.com/v1/messages`

**Headers** :
```json
{
  "Content-Type": "application/json",
  "x-api-key": "{api_key}",
  "anthropic-version": "2023-06-01"
}
```

**Body** :
```json
{
  "model": "claude-3-sonnet-20240229",
  "max_tokens": 2000,
  "messages": [{
    "role": "user",
    "content": "Écris une recette de {titre}..."
  }]
}
```

**Coût** : ~$0.03 par recette

---

### APIs Images (10)

#### 1. SDXL Lightning 4-Step (Replicate) ⚡ RECOMMANDÉ

**Endpoint** : `https://api.replicate.com/v1/predictions`

**Headers** :
```json
{
  "Content-Type": "application/json",
  "Authorization": "Token {api_key}"
}
```

**Body** :
```json
{
  "version": "bytedance/sdxl-lightning-4step",
  "input": {
    "prompt": "professional food photography...",
    "num_outputs": 1,
    "width": 1024,
    "height": 1024
  }
}
```

**Polling** : GET `/v1/predictions/{id}` toutes les 2s jusqu'à status='succeeded'

**Coût** : $0.005 par image  
**Temps** : ~5 secondes

---

#### 2. SDXL Fast (RapidAPI)

**Endpoint** : `https://sdxl-stable-diffusion-xl-fast-text-to-image-api1.p.rapidapi.com/v2/woiipru2dawbnt/run`

**Headers** :
```json
{
  "Content-Type": "application/json",
  "x-rapidapi-host": "sdxl-stable-diffusion-xl-fast-text-to-image-api1.p.rapidapi.com",
  "x-rapidapi-key": "{key}"
}
```

**Body** :
```json
{
  "input": {
    "prompt": "food photography...",
    "negative_prompt": "blurry, low quality",
    "width": 1024,
    "height": 1024,
    "num_inference_steps": 25,
    "guidance_scale": 7.5
  }
}
```

**Coût** : $0.01 par image  
**Temps** : ~20 secondes

---

#### 3. Midjourney (RapidAPI)

**Endpoint POST** : `https://midjourney-best-experience.p.rapidapi.com/api/imagine`

**Headers** :
```json
{
  "Content-Type": "application/json",
  "x-rapidapi-host": "midjourney-best-experience.p.rapidapi.com",
  "x-rapidapi-key": "{key}"
}
```

**Body** :
```json
{
  "prompt": "food photography...",
  "aspect_ratio": "16:9",
  "process_mode": "relax"
}
```

**Polling** : GET `/mj/message/{messageId}` toutes les 15s

**Coût** : $0.05 par image  
**Temps** : ~2 minutes

---

#### 4. DALL-E 3 (OpenAI)

**Endpoint** : `https://api.openai.com/v1/images/generations`

**Body** :
```json
{
  "model": "dall-e-3",
  "prompt": "food photography...",
  "n": 1,
  "size": "1024x1024",
  "quality": "standard"
}
```

**Coût** : $0.04 par image  
**Temps** : ~20 secondes

---

#### Autres APIs (6)

- SDXL ($0.01)
- SDXL Food LoRA ($0.02)
- Flux Pro ($0.03)
- Nanobanana ($0.02)
- Replicate ($0.03)
- Fine-tuned SDXL ($0.02)

**Détails techniques** : Voir `GUIDE-APIS-IMAGES.md` (inclus)

---

### APIs Pinterest (4)

#### Pinterest Search API (Recommandé)

**Endpoint** : `https://pinterest-search-api.p.rapidapi.com/search`

**Params** :
```
?limit=50&filter=all&query={keyword}
```

**Response** :
```json
{
  "results": [{
    "images": {
      "orig": { "url": "..." },
      "236x": { "url": "..." }
    },
    "title": "...",
    "id": "..."
  }]
}
```

**Autres Pinterest** : Pin Search, Image API, Unofficial

---

### Instagram

**Endpoint** : `https://instagram120.p.rapidapi.com/api/instagram/posts`

**Body** :
```json
{
  "username": "account_name",
  "maxId": ""
}
```

---

## 🎨 INTERFACE UTILISATEUR

### Pages Principales

#### 1. Page Albums Recettes

**Layout** :
```
┌─────────────────────────────────────────┐
│ 🍽️ ALBUMS RECETTES                      │
│ Créez des albums de recettes avec IA   │
├─────────────────────────────────────────┤
│ ┌────────────────┬──────────────┐      │
│ │ FORMULAIRE     │ ESTIMATEUR   │      │
│ │                │              │      │
│ │ Titre          │ 📊 Items: 20 │      │
│ │ [Suggérer]     │ 💰 $0.10     │      │
│ │                │ ⏱️ 15 min    │      │
│ │ Destinataire   │              │      │
│ │ [Select user]  │ 📘 AIDE     │      │
│ │                │              │      │
│ │ Moteur texte   │              │      │
│ │ [Gemini ▼]     │              │      │
│ │                │              │      │
│ │ Moteur images  │              │      │
│ │ [SDXL Light ▼] │              │      │
│ │                │              │      │
│ │ Pinterest      │              │      │
│ │ [Rechercher]   │              │      │
│ │ [Grid images]  │              │      │
│ │                │              │      │
│ │ Format         │              │      │
│ │ ○ Global       │              │      │
│ │ ○ Individuel   │              │      │
│ │                │              │      │
│ │ [🔨 GÉNÉRER]   │              │      │
│ └────────────────┴──────────────┘      │
└─────────────────────────────────────────┘
```

**Composants** :

**TitleInput** :
```tsx
interface TitleInputProps {
  value: string;
  onChange: (value: string) => void;
  onSuggest: () => void;
}

// État: titre, suggestions (array)
// Actions: onChange, suggérer, sélectionner suggestion
```

**PinterestSearch** :
```tsx
interface PinterestSearchProps {
  onImagesSelected: (images: Image[]) => void;
}

interface Image {
  url: string;
  thumbnail: string;
  title: string;
  id: string;
  selected: boolean;
}

// État: query, results (array), selected (array)
// Actions: search, select, import
// API: 4 endpoints Pinterest en cascade
```

**ApiSelector** :
```tsx
interface ApiSelectorProps {
  type: 'text' | 'images';
  value: string;
  onChange: (api: string) => void;
}

// Dropdown avec 3 moteurs texte ou 10 moteurs images
// Affiche coût et temps par API
```

**Estimator** :
```tsx
interface EstimatorProps {
  itemCount: number;
  costPerItem: number;
  timePerItem: number;
}

// Calcul temps réel:
// items * (text_cost + image_cost)
// items * (text_time + image_time) / parallel_count
```

**FormSubmit** :
```tsx
const handleSubmit = async (data) => {
  // Validation
  if (!data.title || !data.email) return;
  if (!validateApiKeys(data.text_engine, data.image_engine)) return;
  
  // POST /api/tasks
  const response = await axios.post('/api/tasks', data);
  
  // Redirection vers Instances
  navigate('/instances?highlight=' + response.data.task_id);
};
```

---

#### 2. Page Instances (Suivi)

**Layout** :
```
┌─────────────────────────────────────────┐
│ 📊 SUIVI DES GÉNÉRATIONS                │
├─────────────────────────────────────────┤
│ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐   │
│ │ 🕐 2  │ │ ⚙️ 1  │ │ ✅ 5  │ │ ⏸️ 0  │   │
│ └──────┘ └──────┘ └──────┘ └──────┘   │
├─────────────────────────────────────────┤
│ ⚡ GÉNÉRATIONS EN COURS                 │
│                                         │
│ ┌───────────────────────────────────┐  │
│ │ 20 recettes...     [EN COURS]     │  │
│ │ #12 • 15/20 • email@...           │  │
│ │ ▓▓▓▓▓▓▓▓░░░░ 75%                 │  │
│ │ 📊 Items: 15/20                   │  │
│ │ 📝 Texte: Gemini                  │  │
│ │ 🖼️ Images: SDXL Lightning         │  │
│ │ ⏳ Reste: 5 min                   │  │
│ │ [⏸ Pause] [⏹ Arrêter]            │  │
│ └───────────────────────────────────┘  │
│                                         │
│ 📚 HISTORIQUE                           │
│                                         │
│ ┌───────────────────────────────────┐  │
│ │ 5 recettes...     [TERMINÉE] ✅   │  │
│ │ Coût: $0.05 • Temps: 5 min        │  │
│ │ [📝 Texte] [💾 ZIP] [📄 Article]  │  │
│ └───────────────────────────────────┘  │
└─────────────────────────────────────────┘
```

**Composants** :

**StatsCards** :
```tsx
interface StatsProps {
  pending: number;
  processing: number;
  completed: number;
  paused: number;
}

// 4 cards avec chiffres
// Auto-update via WebSocket ou polling
```

**TaskCard** :
```tsx
interface TaskCardProps {
  task: Task;
  onAction: (action: string, taskId: number) => void;
}

interface Task {
  id: number;
  title: string;
  status: string;
  progress: number;
  current_item: number;
  total_items: number;
  api_text: string;
  api_images: string;
  time_remaining: number;
  cost_estimate: number;
  // ...
}

// Affichage selon status
// Boutons contextuels
// Barre progression
// Détails enrichis
```

**ProgressBar** :
```tsx
interface ProgressBarProps {
  progress: number; // 0-100
  status: string;
}

// Barre verte animée
// Stripes si en cours
// Couleur selon status
```

**DownloadButtons** :
```tsx
const handleDownloadText = async (taskId) => {
  const response = await axios.post('/api/tasks/download-text', { taskId });
  // Créer Blob et télécharger
  const blob = new Blob([response.data.content], { type: 'text/plain' });
  downloadBlob(blob, response.data.filename);
};

const handleDownloadZip = async (taskId) => {
  const response = await axios.post('/api/tasks/download-zip', { taskId });
  // Ouvrir URL
  window.open(response.data.url, '_blank');
};
```

---

#### 3. Page Albums Idées

**Layout** :
```
┌─────────────────────────────────────────┐
│ 💡 ALBUMS IDÉES                         │
│ Carrousels Facebook/Instagram           │
├─────────────────────────────────────────┤
│ ┌────────────────┬──────────────┐      │
│ │ FORMULAIRE     │ ESTIMATEUR   │      │
│ │                │              │      │
│ │ Titre          │ 💡 15 idées  │      │
│ │ [15 idées...] │ 💰 $0.08     │      │
│ │                │ ⏱️ 8 min     │      │
│ │ Style visuel   │              │      │
│ │ [Moderne ▼]    │ 📱 INFO     │      │
│ │                │ CARROUSELS   │      │
│ │ Format image   │              │      │
│ │ [Carré 1:1 ▼]  │              │      │
│ │                │              │      │
│ │ Moteur IA      │              │      │
│ │ [SDXL Light ▼] │              │      │
│ │                │              │      │
│ │ [🎨 GÉNÉRER]   │              │      │
│ └────────────────┴──────────────┘      │
└─────────────────────────────────────────┘
```

**Spécificités** :
- Pas de génération texte
- Seulement images
- Styles : réaliste, moderne, minimaliste, artistique, vintage
- Formats : carré 1:1, portrait 4:5, paysage 16:9
- Optimisé pour réseaux sociaux

---

## 🔄 WORKFLOW BACKEND

### Génération Albums Recettes

**Endpoint** : POST `/api/tasks/create`

**Flow** :
```
1. Validation entrées
   → Titre, email, APIs sélectionnées
   → Vérifier clés API configurées

2. Créer tâche en DB
   → Status: pending
   → Calculer total_items depuis titre
   → Estimer coût et temps

3. Ajouter à queue (Bull)
   → Job ID retourné
   → Envoyer email démarrage

4. Traitement asynchrone (Worker)
   → Multi-threading (3 parallèles)
   
   Pour chaque lot de 3:
   a) Si image_first:
      - Générer 3 images (parallèle)
      - Générer 3 textes analysant images (parallèle)
   
   b) Si text_first:
      - Générer 3 textes (parallèle)
      - Créer prompts images basés sur textes
      - Générer 3 images (parallèle)
   
   c) Sauvegarder résultats
   d) Mettre à jour progress
   e) Émettre Socket.io pour temps réel

5. Finalisation
   → Créer fichier texte (.txt)
   → Créer ZIP images (renommées)
   → Upload S3/CloudFlare
   → Upload Google Drive (optionnel)
   → Créer Google Doc (optionnel)
   → Envoyer email complétion
   → Status: completed

6. Response temps réel
   → Via Socket.io
   → Événements: progress, item_completed, completed, error
```

**Worker Queue** (Bull) :
```javascript
const queue = new Bull('generation', {
  redis: redisConfig
});

queue.process(3, async (job) => {
  const { taskId, config } = job.data;
  
  // Mettre à jour status
  await updateTask(taskId, { status: 'processing' });
  
  // Générer items en parallèle (3 par lot)
  const parallelCount = config.parallel || 3;
  
  for (let i = 0; i < config.total_items; i += parallelCount) {
    const batch = [];
    
    for (let j = 0; j < parallelCount && (i + j) < config.total_items; j++) {
      batch.push(generateItem(taskId, i + j + 1, config));
    }
    
    await Promise.all(batch);
    
    // Update progress
    const progress = Math.round(((i + parallelCount) / config.total_items) * 100);
    await updateTask(taskId, { 
      current_item: i + parallelCount, 
      progress 
    });
    
    // Socket emit
    io.emit('task_progress', { taskId, progress });
  }
  
  // Finaliser
  await finalizeTask(taskId);
});
```

---

## 📧 EMAILS

### Template Email Démarrage

```html
<!DOCTYPE html>
<html>
<head>
  <style>
    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; text-align: center; }
    .content { padding: 30px; }
    .info-box { background: #f0f6fc; padding: 20px; margin: 20px 0; }
  </style>
</head>
<body>
  <div class="header">
    <h1>🚀 Génération Lancée !</h1>
  </div>
  <div class="content">
    <p>Votre génération a été ajoutée à la file d'attente.</p>
    <div class="info-box">
      <strong>Titre :</strong> {titre}<br>
      <strong>API Images :</strong> {api_images}<br>
      <strong>Coût estimé :</strong> ${cost}<br>
      <strong>Temps estimé :</strong> {time} min
    </div>
    <a href="{instances_url}">📊 Voir le suivi en temps réel</a>
  </div>
</body>
</html>
```

### Template Email Complétion

```html
<!DOCTYPE html>
<html>
<head>
  <style>
    .header { background: linear-gradient(135deg, #00a32a 0%, #008a24 100%); color: white; }
    .downloads { background: #e7f5fe; padding: 25px; }
    .button { background: #2271b1; color: white; padding: 12px 24px; text-decoration: none; }
  </style>
</head>
<body>
  <div class="header">
    <h1>🎉 Génération Terminée !</h1>
    <p>Votre album recettes est prêt !</p>
  </div>
  
  <div class="content">
    <h2>📦 Téléchargements</h2>
    <div class="downloads">
      <a href="{text_url}" class="button">📝 Textes (.txt)</a>
      <a href="{zip_url}" class="button">💾 Images (ZIP)</a>
      <a href="{drive_url}" class="button">📁 Google Drive</a>
    </div>
    
    <h3>📚 Historique récent</h3>
    {historique_html}
  </div>
</body>
</html>
```

---

## 🔐 SÉCURITÉ

**Authentification** :
- JWT tokens
- Refresh tokens
- httpOnly cookies

**API Keys** :
- Chiffrement AES-256 en DB
- Variables d'environnement (.env)
- Jamais exposées côté client

**CORS** :
```javascript
app.use(cors({
  origin: process.env.FRONTEND_URL,
  credentials: true
}));
```

**Rate Limiting** :
```javascript
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 min
  max: 100 // 100 requêtes max
});
```

**Validation** :
- Joi ou Zod pour validation données
- Sanitization entrées
- Échappement sorties

---

## ⚡ PERFORMANCE

### Multi-threading

**Configuration** :
```javascript
const PARALLEL_GENERATIONS = 3; // Configurable 1-10

// Génère 3 recettes en parallèle
await Promise.all([
  generateRecipe(1),
  generateRecipe(2),
  generateRecipe(3)
]);
```

**Gain** : 3x plus rapide

### Caching

**Redis** :
```javascript
// Cache suggestions titres (1h)
await redis.set(`suggestions:${query}`, JSON.stringify(suggestions), 'EX', 3600);

// Cache résultats Pinterest (30min)
await redis.set(`pinterest:${query}`, JSON.stringify(images), 'EX', 1800);
```

### WebSocket Temps Réel

**Socket.io** :
```javascript
// Serveur
io.on('connection', (socket) => {
  socket.on('subscribe_task', (taskId) => {
    socket.join(`task_${taskId}`);
  });
});

// Worker émet progression
io.to(`task_${taskId}`).emit('progress', { 
  taskId, 
  progress, 
  current_item,
  time_remaining 
});

// Client
socket.on('progress', (data) => {
  updateProgressBar(data.progress);
  updateDetails(data);
});
```

---

## 📁 STRUCTURE FICHIERS APP WEB

```
app-recettes/
├── frontend/
│   ├── src/
│   │   ├── pages/
│   │   │   ├── AlbumsRecettes.tsx
│   │   │   ├── AlbumsIdees.tsx
│   │   │   ├── Instances.tsx
│   │   │   └── Settings.tsx
│   │   ├── components/
│   │   │   ├── TitleInput.tsx
│   │   │   ├── PinterestSearch.tsx
│   │   │   ├── ApiSelector.tsx
│   │   │   ├── Estimator.tsx
│   │   │   ├── TaskCard.tsx
│   │   │   └── ProgressBar.tsx
│   │   ├── hooks/
│   │   │   ├── useGeneration.ts
│   │   │   ├── usePinterest.ts
│   │   │   └── useWebSocket.ts
│   │   ├── services/
│   │   │   ├── api.ts (axios config)
│   │   │   ├── openai.ts
│   │   │   ├── gemini.ts
│   │   │   ├── midjourney.ts
│   │   │   └── pinterest.ts
│   │   ├── store/
│   │   │   ├── taskStore.ts
│   │   │   └── userStore.ts
│   │   └── utils/
│   │       ├── prompts.ts
│   │       └── formatters.ts
│   ├── public/
│   └── package.json
│
├── backend/
│   ├── src/
│   │   ├── routes/
│   │   │   ├── tasks.ts
│   │   │   ├── auth.ts
│   │   │   └── apis.ts
│   │   ├── controllers/
│   │   │   ├── TaskController.ts
│   │   │   └── ApiController.ts
│   │   ├── services/
│   │   │   ├── GenerationService.ts
│   │   │   ├── OpenAIService.ts
│   │   │   ├── GeminiService.ts
│   │   │   ├── MidjourneyService.ts
│   │   │   ├── PinterestService.ts
│   │   │   └── EmailService.ts
│   │   ├── workers/
│   │   │   └── generationWorker.ts
│   │   ├── models/
│   │   │   ├── Task.ts
│   │   │   └── User.ts
│   │   └── utils/
│   │       ├── queue.ts
│   │       └── prompts.ts
│   ├── prisma/
│   │   └── schema.prisma
│   └── package.json
│
├── docker-compose.yml
├── .env.example
└── README.md
```

---

## 🚀 DÉPLOIEMENT

**Frontend** :
- Vercel ou Netlify
- Build optimisé
- CDN automatique

**Backend** :
- Railway, Render ou Fly.io
- PostgreSQL managé
- Redis managé
- Workers séparés

**Stockage** :
- CloudFlare R2 (gratuit 10GB)
- Ou AWS S3

---

## 💰 COÛTS ESTIMÉS

**Infrastructure mensuelle** :
- Frontend : $0 (Vercel free)
- Backend : $5-20 (Railway/Render)
- PostgreSQL : Inclus
- Redis : Inclus ou $5
- Stockage : $0-5

**APIs (100 albums/mois)** :
- Gemini : $0 (gratuit)
- SDXL Lightning : $5 (100 x 10 images x $0.005)
- Total : **$5-10/mois** (vs $140 avec Midjourney)

---

## 📋 FONCTIONNALITÉS DÉTAILLÉES

### Albums Recettes - Étapes Génération

**1. Input utilisateur** :
```json
{
  "title": "20 recettes de gratins",
  "email": "user@example.com",
  "text_engine": "gemini",
  "image_engine": "sdxl-lightning",
  "pinterest_images": [...],
  "article_format": "global",
  "generation_order": "text_first"
}
```

**2. Traitement** :
```javascript
// Détecter nombre items
const itemCount = extractNumber(title); // 20

// Pour chaque lot de 3:
for (let i = 0; i < 20; i += 3) {
  const batch = [
    generateRecipe(i+1, config),
    generateRecipe(i+2, config),
    generateRecipe(i+3, config)
  ];
  
  const results = await Promise.all(batch);
  
  // Sauvegarder
  await saveResults(taskId, results);
  
  // Progress
  const progress = Math.round(((i + 3) / 20) * 100);
  await updateProgress(taskId, progress);
  
  // Socket
  io.emit('progress', { taskId, progress });
}
```

**3. Génération 1 recette** :
```javascript
const generateRecipe = async (itemNumber, config) => {
  let text, image;
  
  if (config.generation_order === 'text_first') {
    // TEXTE d'abord
    text = await generateText(config.text_engine, config.title, itemNumber);
    
    // IMAGE basée sur texte
    const imagePrompt = await createImagePrompt(text);
    image = await generateImage(config.image_engine, imagePrompt);
  } else {
    // IMAGE d'abord (défaut)
    const prompt = `Recipe ${itemNumber} of ${config.title}`;
    image = await generateImage(config.image_engine, prompt);
    
    // TEXTE basé sur image
    text = await generateText(config.text_engine, prompt, image.url);
  }
  
  return { itemNumber, text, image };
};
```

**4. Finalisation** :
```javascript
// Créer fichier texte
const textContent = formatRecipesAsText(results);
const textUrl = await uploadToS3(textContent, 'text/plain');

// Créer ZIP images
const zipBuffer = await createImagesZip(results);
const zipUrl = await uploadToS3(zipBuffer, 'application/zip');

// Envoyer email
await sendCompletionEmail({
  email: task.email,
  title: task.title,
  textUrl,
  zipUrl,
  stats: { cost, time, api_text, api_images }
});
```

---

## 🧪 TESTS

**Jest + Testing Library** :
```javascript
describe('AlbumsRecettes', () => {
  test('Calcule estimateur correctement', () => {
    const items = 20;
    const textCost = 0; // Gemini gratuit
    const imageCost = 0.005 * items; // SDXL Lightning
    expect(calculateCost(items, 'gemini', 'sdxl-lightning')).toBe(0.10);
  });
  
  test('Valide APIs avant génération', () => {
    expect(() => validateApis('gemini', 'sdxl-lightning')).not.toThrow();
    expect(() => validateApis('chatgpt', null)).toThrow('API images requise');
  });
});
```

---

## 📚 DOCUMENTATION À FOURNIR

**Pour l'agent Cursor** :

1. ✅ Ce cahier des charges
2. ✅ GUIDE-APIS-IMAGES.md (détails 10 APIs)
3. ✅ PROMPT-CHATGPT-RECETTES.md (format recettes)
4. ✅ Exemples de code (ci-dessus)
5. ✅ Flow diagrams
6. ✅ Schéma base de données

---

## 🎯 RÉSUMÉ POUR AGENT CURSOR

**À créer** :

**App Web Native** avec 2 modules :
- Albums Recettes (génération texte + images)
- Albums Idées (génération images seulement)

**Stack** : React + Node.js + PostgreSQL + Redis + Bull Queue

**APIs** : 
- 3 texte (ChatGPT/Gemini/Claude)
- 10 images (SDXL Lightning recommandé)
- 4 Pinterest
- 1 Instagram

**Features** :
- Multi-threading (3x rapide)
- Temps réel (Socket.io)
- Téléchargements (texte + ZIP)
- Emails professionnels
- Interface moderne

**Coûts** :
- Infra : $10-20/mois
- APIs : $5-10/mois (100 albums)
- **Total : $15-30/mois**

---

**Version WordPress actuelle** : v5.1.1  
**Code source** : https://github.com/f2x33k-oss/regen  
**Branch** : cursor/plugin-structure-et-file-fd63

**Ce document + code WordPress = base complète pour app web** ✅
