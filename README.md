# AI Content Factory Pro

Plugin WordPress professionnel pour la génération automatique de contenu et d'images via OpenAI (ChatGPT) et Midjourney avec système de file d'attente avancé.

## 🚀 Fonctionnalités

- **Génération automatique de contenu** via OpenAI GPT-4o
- **Génération d'images** via l'API Midjourney (RapidAPI)
- **Système de file d'attente asynchrone** avec traitement en arrière-plan
- **Références d'images personnalisées** (--sref) via upload de fichiers ZIP
- **Dashboard de suivi** en temps réel avec barre de progression
- **Notifications par email** avec résultats détaillés
- **Création automatique d'articles WordPress** en mode brouillon
- **Calculateur de coûts et temps** estimés
- **Gestion avancée** : Pause, Reprendre, Annuler les tâches
- **Meta Box dans l'éditeur** avec journaux de génération
- **Interface moderne** et intuitive
- **Sécurité renforcée** avec nonces et vérifications de capacités

## 📋 Prérequis

- WordPress 5.8 ou supérieur
- PHP 7.4 ou supérieur
- Clé API OpenAI (pour la génération de texte)
- Clé RapidAPI (pour Midjourney)
- Configuration SMTP recommandée (pour les emails)

## 📦 Installation

1. **Téléchargez** le plugin ou clonez ce dépôt
2. **Compressez** le dossier en fichier ZIP (si nécessaire)
3. **Uploadez** le ZIP via WordPress Admin → Extensions → Ajouter
4. **Activez** le plugin

### Installation manuelle

```bash
cd wp-content/plugins/
git clone [url-du-repo] ai-content-factory-pro
```

Puis activez le plugin dans WordPress.

## ⚙️ Configuration

### 1. Configuration des clés API

Accédez à **AI Content Factory → Réglages** et configurez :

#### OpenAI
- Obtenez votre clé API sur [platform.openai.com/api-keys](https://platform.openai.com/api-keys)
- Entrez la clé dans le champ "Clé API OpenAI"
- Coût estimé : ~0.02$ par article avec GPT-4o

#### Midjourney (RapidAPI)
- Inscrivez-vous sur [rapidapi.com](https://rapidapi.com)
- Abonnez-vous à l'API Midjourney
- Copiez votre clé RapidAPI
- Entrez la clé dans le champ "Clé RapidAPI (Midjourney)"
- Coût estimé : ~0.05$ par image

### 2. Configuration SMTP (recommandé)

Pour des emails fiables, configurez SMTP :

- Activez "Utiliser SMTP pour l'envoi d'emails"
- Configurez votre serveur SMTP (ex: Gmail, SendGrid, Mailgun)
- Exemple pour Gmail :
  - Hôte : `smtp.gmail.com`
  - Port : `587`
  - Chiffrement : `TLS`
  - Utilisez un mot de passe d'application

## 🎯 Utilisation

### Générer du contenu

1. Accédez à **AI Content Factory → Générer**
2. Entrez un titre (ex: "20 recettes de gratins")
   - Le plugin détecte automatiquement le nombre (ex: 20)
3. Activez/désactivez la génération de texte
4. (Optionnel) Uploadez un ZIP d'images de référence
5. Entrez votre email pour recevoir les résultats
6. Vérifiez le coût et temps estimés
7. Cliquez sur "Lancer la génération"

### Suivre les tâches

1. Accédez à **AI Content Factory → Instances**
2. Visualisez toutes les tâches en cours
3. Actions disponibles :
   - **Pause** : Mettre en pause une tâche
   - **Reprendre** : Relancer une tâche en pause
   - **Annuler** : Annuler une tâche
   - **Supprimer** : Supprimer une tâche terminée
   - **Voir l'article** : Accéder à l'article créé

### Résultats

#### Si génération de texte activée :
- Un article WordPress est créé en mode brouillon
- Email avec lien vers l'article + images

#### Si génération de texte désactivée :
- Pas d'article créé
- Email avec liste des URLs d'images + prompts

## 📁 Structure du plugin

```
ai-content-factory-pro/
├── admin/
│   ├── class-admin-menu.php         # Menu administration
│   ├── class-settings-page.php      # Page de réglages
│   ├── class-generate-page.php      # Page de génération
│   ├── class-instances-page.php     # Page des instances
│   └── class-meta-box.php           # Meta Box éditeur
├── assets/
│   ├── css/
│   │   └── admin-style.css          # Styles admin
│   └── js/
│       └── admin-script.js          # Scripts admin
├── includes/
│   ├── class-database.php           # Gestion BDD
│   ├── class-queue-manager.php      # Gestionnaire de file
│   ├── class-api-handler.php        # Handlers API
│   ├── class-email-handler.php      # Gestion emails
│   ├── class-file-handler.php       # Gestion fichiers
│   └── class-ajax-handler.php       # Handlers AJAX
├── ai-content-factory-pro.php       # Fichier principal
└── README.md                         # Documentation
```

## 🗄️ Base de données

Le plugin crée une table `wp_ai_queue` avec les champs suivants :

- `id` : ID unique de la tâche
- `title` : Titre du projet
- `generate_text` : Activer/désactiver génération texte
- `email` : Email de livraison
- `zip_file_path` : Chemin du ZIP (images de référence)
- `reference_images` : URLs des images extraites
- `status` : Statut (pending, processing, completed, paused, cancelled, failed)
- `progress` : Progression (0-100%)
- `total_items` : Nombre total d'items
- `current_item` : Item en cours
- `post_id` : ID de l'article WordPress créé
- `generated_images` : Images générées (sérialisé)
- `generated_content` : Contenu généré (sérialisé)
- `prompts_log` : Journal des prompts (sérialisé)
- `error_log` : Journal des erreurs (sérialisé)
- `cost_estimate` : Coût estimé ($)
- `time_estimate` : Temps estimé (minutes)
- Horodatages : `created_at`, `updated_at`, `started_at`, `completed_at`

## 🔄 Workflow technique

### Processus de génération

1. **Soumission** : L'utilisateur soumet un formulaire
2. **Création de tâche** : Insertion dans la table `wp_ai_queue`
3. **Traitement cron** : WP-Cron traite la file toutes les minutes
4. **Extraction ZIP** : Si fourni, extraction des images de référence
5. **Génération** : Pour chaque item :
   - Génération du prompt
   - (Si activé) Génération du texte via OpenAI
   - Génération de l'image via Midjourney
   - Polling jusqu'à complétion (~20s d'intervalle)
   - Import dans la médiathèque WordPress
6. **Finalisation** :
   - (Si texte activé) Création de l'article WordPress
   - Envoi de l'email de notification
7. **Nettoyage** : Suppression des fichiers temporaires

### API Midjourney (Polling)

```
POST /imagine → {task_id}
↓ (attendre 20s)
GET /status/{task_id} → {status: "processing"}
↓ (attendre 20s)
GET /status/{task_id} → {status: "completed", image_url: "..."}
```

## 💰 Estimation des coûts

### Génération de texte (OpenAI GPT-4o)
- ~0.02$ par article
- Basé sur ~1000 tokens par article

### Génération d'image (Midjourney via RapidAPI)
- ~0.05$ par image
- Varie selon l'abonnement RapidAPI

### Exemple
Pour "20 recettes de gratins" avec texte :
- Texte : 20 × 0.02$ = 0.40$
- Images : 20 × 0.05$ = 1.00$
- **Total : 1.40$**

## ⏱️ Estimation du temps

- Génération texte : ~30 secondes par item
- Génération image : ~2 minutes par item
- Exemple pour 20 items avec texte : **~50 minutes**

## 🛡️ Sécurité

- **Nonces WordPress** sur tous les formulaires
- **Vérification des capacités** (`manage_options`)
- **Sanitisation** de toutes les entrées utilisateur
- **Échappement** de toutes les sorties
- **Validation des types de fichiers** (ZIP uniquement)
- **Protection contre l'accès direct** aux fichiers PHP

## 🔧 Hooks et filtres

### Actions disponibles

```php
// Exécuté avant le traitement d'une tâche
do_action('aicfp_before_process_task', $task_id);

// Exécuté après le traitement d'une tâche
do_action('aicfp_after_process_task', $task_id, $result);
```

### Filtres disponibles

```php
// Modifier le prompt généré
add_filter('aicfp_generate_prompt', function($prompt, $task, $item_number) {
    // Votre logique personnalisée
    return $prompt;
}, 10, 3);
```

## 📧 Format des emails

Les emails envoyés contiennent :

- Informations de la tâche (titre, dates, nombre d'items)
- Lien vers l'article WordPress (si texte activé)
- Galerie d'images générées avec liens
- Liste des prompts utilisés
- Journaux d'erreurs (si présents)

## 🐛 Dépannage

### Les tâches ne se lancent pas

Vérifiez que WP-Cron fonctionne :
```php
wp_get_schedules(); // Doit inclure 'every_minute'
```

### Erreurs de génération d'images

- Vérifiez votre clé RapidAPI
- Vérifiez votre quota/abonnement
- Consultez les logs d'erreurs dans la Meta Box

### Emails non reçus

- Configurez SMTP
- Vérifiez les spams
- Testez avec un plugin comme WP Mail SMTP

### Fichiers temporaires

Le plugin nettoie automatiquement les fichiers de plus de 7 jours dans `/wp-content/uploads/aicfp-temp/`

## 🔄 Mise à jour

1. Désactivez le plugin
2. Remplacez les fichiers
3. Réactivez le plugin
4. Les tables de BDD sont mises à jour automatiquement

## ❓ FAQ

**Q: Puis-je modifier les prompts générés ?**  
R: Oui, utilisez le filtre `aicfp_generate_prompt`.

**Q: Combien de tâches peuvent tourner en parallèle ?**  
R: Par défaut, 1 tâche à la fois pour éviter les surcharges. Modifiable dans `class-queue-manager.php`.

**Q: Les images sont-elles sauvegardées dans WordPress ?**  
R: Oui, elles sont importées dans la médiathèque WordPress.

**Q: Puis-je utiliser sans génération de texte ?**  
R: Oui, désactivez simplement le toggle "Générer les textes".

## 📝 Changelog

### Version 1.0.0 (2026-01-31)
- Version initiale
- Génération de contenu via OpenAI
- Génération d'images via Midjourney
- Système de file d'attente
- Dashboard de suivi
- Notifications par email
- Meta Box dans l'éditeur

## 📄 Licence

GPL v2 or later

## 👨‍💻 Développement

### Contribuer

Les contributions sont les bienvenues ! Veuillez suivre les standards de codage WordPress.

### Support

Pour obtenir de l'aide ou signaler un bug, ouvrez une issue sur GitHub.

---

**Développé avec ❤️ pour WordPress**
