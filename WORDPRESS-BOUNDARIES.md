# WORDPRESS BOUNDARIES - Règles strictes de séparation

## ⚠️ PRINCIPE FONDAMENTAL

**WordPress ne contient AUCUNE logique métier. Point final.**

WordPress est un **CLIENT DE PUBLICATION UNIQUEMENT**.

---

## ✅ CE QUI EST AUTORISÉ DANS WORDPRESS

### 1. Interface utilisateur (UI)
- Pages d'administration (settings, dashboard)
- Formulaires de saisie (paramètres de job uniquement)
- Affichage de la liste des jobs
- Boutons d'action (créer, publier, annuler)
- Notifications utilisateur (succès, erreurs)

### 2. Communication HTTP
- Appels REST API vers le Core App
- Envoi de requêtes authentifiées (JWT ou API Key)
- Parsing des réponses JSON
- Gestion des erreurs HTTP (timeouts, 4xx, 5xx)

### 3. Publication WordPress native
- Création de posts WP (`wp_insert_post`)
- Import d'images dans media library (`media_sideload_image`)
- Assignation de taxonomies (`wp_set_post_terms`)
- Configuration de métadonnées (`update_post_meta`)
- Génération de HTML/Gutenberg blocks pour affichage

### 4. Authentification locale
- Stockage sécurisé de l'API Key (options WP)
- Vérification des capacités WordPress (`current_user_can`)
- Protection des formulaires (nonces WP)

### 5. Polling léger (optionnel)
- AJAX pour rafraîchir le status d'un job
- Appel GET /api/v1/jobs/:id/status toutes les 10-30s
- Mise à jour UI avec le status reçu

### 6. Templating et affichage
- Templates PHP pour afficher le contenu publié
- CSS pour styliser les formats (Recipe Album, Idea Carousel)
- JavaScript pour interactions frontend (carousel, accordéon, etc.)

---

## ❌ CE QUI EST **INTERDIT** DANS WORDPRESS

### 🚫 Logique métier

#### Génération de contenu
- ❌ Appels directs aux APIs d'IA (OpenAI, Anthropic, etc.)
- ❌ Génération de texte, titres, descriptions
- ❌ Prompts engineering
- ❌ Traitement de réponses IA
- ❌ Sélection de modèles IA

**Pourquoi ?**
- Couplage fort avec des APIs externes
- Impossible à tester et monitorer centralement
- Pas de tracking des coûts
- Pas de retry logic centralisée
- Pas de scalabilité
- Migration cauchemardesque

#### Scraping et collecte de données
- ❌ Web scraping (Puppeteer, Cheerio, etc.)
- ❌ Parsing de pages HTML externes
- ❌ Extraction d'ingrédients, recettes, données
- ❌ Appels à des APIs de scraping tierces

**Pourquoi ?**
- Opérations lentes (timeouts WordPress)
- Consommation mémoire élevée
- Pas de gestion de concurrence
- Rate limiting complexe
- Erreurs difficiles à gérer

#### Orchestration multi-API
- ❌ Séquençage d'appels (OpenAI → Scraping → Images → Social)
- ❌ Gestion de workflows complexes
- ❌ Retry logic inter-API
- ❌ Agrégation de résultats multiples

**Pourquoi ?**
- WordPress n'est pas conçu pour orchestration asynchrone
- Timeouts PHP (30-60s max)
- Pas de queue management
- Pas de state persistence entre étapes
- Débug cauchemardesque

#### Traitement d'images
- ❌ Génération d'images (DALL-E, Midjourney, etc.)
- ❌ Manipulation d'images (resize, crop, filters)
- ❌ Optimisation d'images lourdes
- ❌ Upload vers S3/CDN

**Pourquoi ?**
- Opérations intensives en ressources
- Timeouts
- Pas de gestion de jobs longue durée
- Coûts API non trackés

### 🚫 Gestion de jobs

- ❌ Création de systèmes de queue custom
- ❌ Workers custom en background
- ❌ Cron jobs pour tâches longues
- ❌ State management de jobs (pending, processing, etc.)
- ❌ Retry logic sur jobs échoués
- ❌ Calcul de progression (%)

**Pourquoi ?**
- WordPress n'est pas un job runner
- WP Cron n'est pas fiable (dépend du trafic)
- Pas de vraie concurrence
- Pas de monitoring centralisé
- Scalabilité impossible

### 🚫 Secrets et API Keys

- ❌ Stockage de clés API tierces (OpenAI, etc.)
- ❌ Gestion de OAuth tokens externes
- ❌ Rotation de secrets
- ❌ Chiffrement de données sensibles

**Pourquoi ?**
- WordPress DB accessible via SQL injection potentielle
- Options WP pas conçues pour secrets sensibles
- Pas d'audit trail
- Rotation manuelle → erreur humaine

### 🚫 Analytics et coûts

- ❌ Calcul de coûts API
- ❌ Tracking de tokens (GPT-4, etc.)
- ❌ Agrégation de métriques
- ❌ Génération de rapports de coûts

**Pourquoi ?**
- Données fragmentées (impossible consolidation multi-client)
- Pas de granularité (par user, par job, par API)
- Pas de dashboard centralisé

### 🚫 Règles métier

- ❌ Validation métier complexe (ex: "recette valide = 3+ ingrédients")
- ❌ Logique de quotas (ex: max 10 jobs/mois)
- ❌ Calcul de pricing
- ❌ Règles d'éligibilité

**Pourquoi ?**
- Duplication de code si multi-client
- Évolution des règles = mise à jour tous les sites WP
- Pas de test centralisé
- Incohérence garantie

---

## 📋 EXEMPLES CONCRETS

### ❌ MAUVAIS EXEMPLE : Logique métier dans WordPress

```php
// ❌ NE JAMAIS FAIRE CECI
function generate_recipe_album($topic) {
    // Appel direct OpenAI
    $openai_key = get_option('openai_api_key');
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => ['Authorization' => 'Bearer ' . $openai_key],
        'body' => json_encode([
            'model' => 'gpt-4',
            'messages' => [['role' => 'user', 'content' => "Generate recipes for $topic"]]
        ])
    ]);
    
    $recipes = json_decode($response['body']);
    
    // Génération d'images
    foreach ($recipes as $recipe) {
        $image_response = wp_remote_post('https://api.openai.com/v1/images/generations', ...);
        $recipe->image = $image_response['url'];
    }
    
    // Création de posts
    foreach ($recipes as $recipe) {
        wp_insert_post(['post_title' => $recipe->title, ...]);
    }
    
    return $recipes;
}
```

**Pourquoi c'est terrible :**
- Timeout garanti si OpenAI lent (>30s)
- API keys exposées dans WordPress
- Coûts non trackés
- Impossible à retry si erreur
- Pas de monitoring
- Pas de logs centralisés
- Migration impossible

### ✅ BON EXEMPLE : Client léger

```php
// ✅ CORRECT : Simple client HTTP
function create_recipe_album_job($topic) {
    $api_client = new CoreAppAPIClient();
    
    // 1. Appel simple à l'API Core
    $response = $api_client->post('/api/v1/jobs', [
        'type' => 'recipe_album',
        'input_params' => [
            'topic' => $topic,
            'num_recipes' => 5,
            'style' => 'moderne'
        ]
    ]);
    
    if ($response['success']) {
        // 2. Stocke job_id pour polling
        update_option('current_job_id', $response['job_id']);
        
        // 3. Retourne pour affichage UI
        return [
            'success' => true,
            'job_id' => $response['job_id'],
            'message' => 'Job créé avec succès'
        ];
    } else {
        return ['success' => false, 'error' => $response['error']];
    }
}

function publish_completed_job($job_id) {
    $api_client = new CoreAppAPIClient();
    
    // 1. Récupère le contenu finalisé
    $content = $api_client->get("/api/v1/content/job/$job_id");
    
    if (!$content) {
        return ['success' => false, 'error' => 'Content not found'];
    }
    
    // 2. Publie via le publisher approprié
    $publisher = PublisherFactory::get($content['content_type']);
    $result = $publisher->publish($content);
    
    // 3. Notifie le Core App
    if ($result['success']) {
        $api_client->post("/api/v1/content/{$content['id']}/publish", [
            'platform' => 'wordpress',
            'url' => $result['album_url'],
            'published_at' => current_time('mysql')
        ]);
    }
    
    return $result;
}
```

**Pourquoi c'est excellent :**
- Aucune logique métier
- Appels HTTP simples
- Pas de secrets sensibles
- Rapide (< 1s)
- Facile à tester
- Facile à migrer

---

## 🎯 RÈGLE D'OR

**Si vous hésitez, demandez-vous :**

> "Si je devais supporter 1000 clients WordPress différents,
> est-ce que je voudrais cette logique dupliquée 1000 fois ?"

- **NON** → La logique va dans le Core App
- **OUI** (ex: affichage UI) → Peut rester dans WordPress

---

## 🚨 SIGNAUX D'ALARME

Si vous écrivez du code WordPress et que vous voyez :

- ❌ `wp_remote_post('https://api.openai.com', ...)`
- ❌ `require 'vendor/openai/openai-php-client'`
- ❌ `function generate_content_with_ai(...)`
- ❌ `function scrape_website(...)`
- ❌ `wp_schedule_event(..., 'process_long_job')`
- ❌ `set_time_limit(600)` // augmenter timeout
- ❌ `ini_set('memory_limit', '512M')` // augmenter mémoire
- ❌ Boucles lourdes (>1000 itérations)
- ❌ Traitement récursif profond
- ❌ Calculs complexes

**STOP immédiatement et refactorez vers le Core App.**

---

## 📊 MATRICE DE DÉCISION

| Fonctionnalité | WordPress | Core App | Justification |
|----------------|-----------|----------|---------------|
| Formulaire "Créer un job" | ✅ | ❌ | UI pure |
| Appel POST /api/v1/jobs | ✅ | ❌ | HTTP client |
| Validation "topic non vide" | ✅ | ✅ | Simple (WP) + Business (Core) |
| Génération de texte IA | ❌ | ✅ | Logique métier |
| Retry si API error | ❌ | ✅ | Orchestration |
| Polling status toutes les 10s | ✅ | ❌ | UI interaction |
| Calcul de coût API | ❌ | ✅ | Analytics |
| Création de post WP | ✅ | ❌ | Publication WP native |
| Upload image vers S3 | ❌ | ✅ | Storage externe |
| Template HTML carousel | ✅ | ❌ | Affichage frontend |
| Gestion OAuth Google | ❌ | ✅ | Secrets management |
| Dashboard admin stats | ✅ | ❌ | UI (data depuis Core) |

---

## 🔒 CONTRAT D'INTERFACE

### WordPress peut uniquement :

1. **Envoyer** : Paramètres de job (JSON simple)
2. **Recevoir** : Status de job (pending/processing/completed/failed)
3. **Recevoir** : Contenu finalisé (JSON structuré)
4. **Publier** : Post WordPress avec contenu reçu

### Core App garantit :

1. **Traiter** : Tous les jobs de manière asynchrone
2. **Retourner** : Contenu structuré prêt à publier
3. **Fournir** : APIs claires et documentées
4. **Gérer** : Tous les secrets, coûts, erreurs, retry

---

## ✅ CHECKLIST AVANT MERGE

Avant de merger du code WordPress, vérifiez :

- [ ] Aucun appel direct à OpenAI, Anthropic, etc.
- [ ] Aucune librairie d'IA dans composer.json
- [ ] Aucun secret API tiers stocké
- [ ] Aucun job longue durée (>5s)
- [ ] Aucune logique de retry complexe
- [ ] Aucun calcul de coût
- [ ] Aucune orchestration multi-API
- [ ] Tout est fait via appels REST au Core App
- [ ] Les publishers ne font QUE de la publication WP

---

## 💡 CONCLUSION

**WordPress est un cadre photo, pas un studio de photographie.**

- Le Core App crée la photo (logique métier)
- WordPress l'affiche joliment (publication)

**Maintenez cette séparation stricte pour garantir :**
- Scalabilité
- Maintenabilité
- Testabilité
- Sécurité
- Évolutivité

**Si vous êtes tenté de contourner cette règle, c'est que l'API du Core App manque un endpoint.**
→ Ajoutez l'endpoint au Core App, ne contournez pas vers WordPress.
