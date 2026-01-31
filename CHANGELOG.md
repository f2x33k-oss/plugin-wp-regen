# Changelog - AI Content Factory Pro

Toutes les modifications notables de ce projet seront documentées dans ce fichier.

## [1.1.0] - 2026-01-31

### ✨ Nouvelles fonctionnalités

#### Intégration API Midjourney réelle
- ✅ Intégration de l'API Midjourney Best Experience via RapidAPI
- ✅ Configuration automatique avec clé API fournie
- ✅ Support des images de référence (--sref) dans les prompts
- ✅ Gestion intelligente du polling avec retry automatique
- ✅ Support de multiples formats de réponse API

#### Prompt ChatGPT optimisé pour recettes
- ✅ Prompt système professionnel pour chef cuisinier
- ✅ Format détaillé avec émojis pour chaque élément
- ✅ Numérotation automatique des étapes (1️⃣, 2️⃣, 3️⃣)
- ✅ Structure complète : ingrédients, étapes, astuces
- ✅ Exclusion des mentions "comme sur la photo"

#### Analyse d'image avec GPT-4o Vision
- ✅ Génération de l'image EN PREMIER via Midjourney
- ✅ Analyse automatique de l'image générée par GPT-4o
- ✅ Création de recette basée sur les ingrédients visibles
- ✅ Workflow optimisé : Image → Analyse → Texte

### 🔧 Modifications techniques

#### API Handler (`class-api-handler.php`)
- Mise à jour de l'URL de base: `midjourney-best-experience.p.rapidapi.com`
- Ajout du support pour GPT-4o Vision avec paramètre `image_url`
- Ajout de la clé API par défaut pour tests
- Amélioration de la gestion des erreurs avec logging
- Support de multiples formats de task_id (task_id, messageId, id)
- Polling adaptatif avec 40 tentatives × 15 secondes
- Détection automatique de l'URL d'image (image_url, uri, url, imageUrl)

#### Queue Manager (`class-queue-manager.php`)
- Inversion de l'ordre de génération : Image d'abord, puis texte
- Passage de l'image générée à l'API ChatGPT pour analyse
- Séparation claire des étapes de génération
- Meilleure gestion des erreurs pour chaque étape

#### Settings Page (`class-settings-page.php`)
- Pré-remplissage de la clé RapidAPI par défaut
- Changement du type input de password à text pour la clé API
- Mise à jour de la description avec le nom correct de l'API

### 📚 Documentation

#### Nouveaux fichiers de documentation
- `API-MIDJOURNEY-GUIDE.md` - Guide complet de l'API Midjourney
  - Configuration et endpoints
  - Workflow d'utilisation
  - Gestion des erreurs
  - Exemples de code
  - Coûts et optimisations

- `PROMPT-CHATGPT-RECETTES.md` - Guide du prompt recettes
  - Format attendu avec émojis
  - Exemples de recettes
  - Configuration OpenAI
  - Bonnes pratiques
  - Variantes du prompt

### 🐛 Corrections

- Correction du timeout API (augmenté à 90s pour ChatGPT)
- Amélioration du logging pour débogage
- Gestion des différents formats de réponse API
- Retry automatique en cas d'erreur réseau

### ⚙️ Configuration

#### Nouvelles configurations par défaut
```php
// API Midjourney
Host: midjourney-best-experience.p.rapidapi.com
Clé: 60bcbb5fe7mshd88f23d138be003p1be084jsnc1e30b0bb6d3

// OpenAI
Model: gpt-4o (avec Vision)
Max tokens: 2000 (augmenté pour recettes complètes)
Timeout: 90s
```

### 📊 Améliorations de performance

- Polling optimisé : 15s × 40 tentatives = 10 minutes max
- Réduction des requêtes API avec meilleure gestion du cache
- Logging amélioré pour diagnostic rapide
- Gestion des erreurs sans bloquer la file d'attente

### 🔒 Sécurité

- Validation améliorée des réponses API
- Logging sécurisé sans exposer les clés
- Gestion des erreurs sans fuites de données

## [1.0.0] - 2026-01-31

### 🎉 Version initiale

- Plugin WordPress complet avec 15 fichiers
- Système de file d'attente asynchrone
- Intégration OpenAI (ChatGPT)
- Intégration Midjourney (version initiale)
- Dashboard de suivi en temps réel
- Notifications par email
- Meta Box dans l'éditeur WordPress
- Interface moderne avec Tailwind-like CSS
- Support des images de référence (ZIP)
- Calculateur de coûts et temps
- Actions : Pause/Reprendre/Annuler

---

## Versions à venir

### [1.2.0] - Prévu

#### Fonctionnalités planifiées
- [ ] Support de multiples langues pour les recettes
- [ ] Templates de recettes personnalisables
- [ ] Export des recettes en PDF
- [ ] Planification des générations
- [ ] Statistiques avancées
- [ ] Webhooks personnalisés
- [ ] API REST pour intégrations externes

#### Améliorations
- [ ] Cache intelligent des images générées
- [ ] Batch processing optimisé
- [ ] Interface de prévisualisation en temps réel
- [ ] Éditeur de prompt intégré
- [ ] Gestion des doublons automatique

---

## Notes de migration

### De 1.0.0 vers 1.1.0

**Aucune action requise**. Les modifications sont rétrocompatibles.

Recommandations :
1. Vérifier la clé API RapidAPI dans les réglages
2. Tester avec une petite tâche (1-2 items)
3. Consulter les logs en cas d'erreur

### Configuration recommandée

```php
// wp-config.php - Activer le logging WordPress
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

---

## Support

Pour obtenir de l'aide :
1. Consulter les fichiers de documentation (API-MIDJOURNEY-GUIDE.md, PROMPT-CHATGPT-RECETTES.md)
2. Vérifier les logs WordPress (`wp-content/debug.log`)
3. Ouvrir une issue sur GitHub avec les détails de l'erreur

---

## Contributeurs

- **Développement initial** : AI Content Factory Team
- **Intégration API** : Version 1.1.0
- **Documentation** : Version 1.1.0

---

## Licence

GPL v2 or later

---

**Dernière mise à jour** : 31 janvier 2026
