# 🗺️ ROADMAP v3.0.0 - 100% COMPLET

**Objectif** : Implémenter les 10% de fonctionnalités restantes

---

## ✅ FONCTIONNALITÉS À IMPLÉMENTER

### 1. 2 Formats Articles WordPress

**Format 1 - Actuel** : 1 article global
- Titre album
- Intro 30 mots
- 6x (H2 + Image + Texte)
- ✅ Déjà implémenté

**Format 2 - Nouveau** : 1 article par recette + tag
- Créer tag : "recette-gratin"
- Créer 6 articles séparés
- Chaque article :
  * Titre de la recette
  * Image
  * Intro 30-50 mots
  * Ingrédients (H3)
  * Instructions (H3)
  * Lien fin article → autres recettes du tag
- ⏳ À implémenter

**Implémentation** :
- Ajouter option formulaire : "Format article"
  * Radio : "1 article global" (défaut)
  * Radio : "1 article par recette + tag"
- Modifier complete_task() pour détecter format
- Créer fonction create_individual_articles()
- Créer tag automatiquement
- Générer liens croisés

---

### 2. Alertes Crédits API

**Fonctionnalité** :
- Vérifier crédits restants RapidAPI
- Alert si < 20% : "⚠️ Attention ! Il vous reste moins de 20% de crédits sur [API]"
- Alert si 0% : "❌ Crédits épuisés sur [API] ! Veuillez recharger"
- Afficher dans Dashboard Instances

**Implémentation** :
- Appel API RapidAPI pour vérifier quotas
- Endpoint : GET /quota
- Parser réponse
- Afficher bandeau warning si <20%
- Afficher bandeau error si 0%
- Cache résultat 1h

---

### 3. Boutons Test Connexion API

**Fonctionnalité** :
- Bouton "Tester" à côté de chaque champ clé API
- Vérifie connexion en temps réel
- Affiche ✅ ou ❌
- Message détaillé

**Implémentation** :
- Ajouter boutons dans Réglages
- Handler AJAX test_api_connection
- Fonction test par API (appel ping)
- Notification résultat
- Logging tentative

---

### 4. Toggle Activer/Désactiver API

**Fonctionnalité** :
- Case à cocher à côté de chaque API
- Désactiver = ne pas proposer dans sélecteur
- Sauvegarder état actif/inactif
- Filtrer dropdown selon état

**Implémentation** :
- Ajouter checkboxes Réglages
- Options : aicfp_[api]_enabled
- Filtrer get_available_apis()
- Masquer dans dropdown si désactivé

---

### 5. Barre Progression Temps Réel

**Fonctionnalité** :
- Mise à jour via WebSocket ou long polling
- Pas de refresh page
- Animation fluide 0-100%
- Pourcentage live

**Implémentation** :
- AJAX polling 2s au lieu refresh page
- Endpoint get_task_progress
- Update DOM directement
- Animation CSS transition

---

### 6. Détails Erreurs Enrichis

**Fonctionnalité** :
- Page Erreurs avec plus d'infos :
  * API concernée
  * Code erreur HTTP
  * Message complet
  * Stacktrace si dispo
  * Solution suggérée

**Implémentation** :
- Enrichir error_log array
- Ajouter champs : api, code, stack, solution
- Afficher dans cards
- Bouton "Voir solution"

---

### 7. Option Mise à Jour Plugin

**Fonctionnalité** :
- Page dédiée ou section Réglages
- Upload ZIP nouvelle version
- Backup automatique
- Mise à jour 1 clic
- Préservation réglages

**Implémentation** :
- Créer page Update
- Form upload ZIP
- Handler update_plugin
- Vérifier version
- Extraire et remplacer fichiers
- Garder wp-options

---

### 8. Menu Vidéos Backend Complet

**Fonctionnalité** :
- Génération vraiment fonctionnelle
- Agents IA (Scénariste, Réalisateur, Optimisation)
- Intégration Google VEO / Sora
- Upload vers stockage
- 2 formats :
  * Vidéos avec montage + sous-titres + avatar IA
  * Vidéos compilation clips + emojis + textes
- Sélection durée intelligente

**Implémentation** :
- Créer class-video-generator.php
- Multi-agents avec ChatGPT
- Intégration VEO API
- Intégration Sora API
- Système montage (FFmpeg)
- Génération sous-titres
- Avatar IA (D-ID ou similaire)
- Upload Google Drive
- Notification complétion

---

### 9. Prompts Images Optimisés

**Fonctionnalité** :
- Prompt basé sur titre recette + ingrédients
- Format simple : "photo de [plat]"
- Extraction auto ingrédients du texte généré
- Prompt avant génération image

**Implémentation** :
- Modifier generate_prompt()
- Analyser texte recette généré
- Extraire ingrédients principaux
- Créer prompt : "professional food photography of [dish] with [ingredients]"
- Passer à API images

---

### 10. Sélecteur API sur Idées et Vidéos

**Fonctionnalité** :
- Dropdown API comme Albums Recettes
- Sur Albums Idées
- Sur page Vidéos
- Info API dynamique
- Calcul coût temps réel

**Implémentation** :
- Copier système sélecteur de Recettes
- Ajouter à formulaire Idées
- Ajouter à formulaire Vidéos
- Adapter calculateur
- Sauvegarder choix en métadonnée

---

## 📊 PRIORITÉS

### Phase 1 (Critique) - v3.0.0
1. ⚡ **2 formats articles** (très demandé)
2. ⚡ **Boutons test API** (debug utile)
3. ⚡ **Prompts optimisés** (qualité images)
4. ⚡ **Sélecteur API Idées** (cohérence)

### Phase 2 (Important) - v3.1.0
5. **Alertes crédits** (monitoring)
6. **Toggle API** (flexibilité)
7. **Détails erreurs** (diagnostic)
8. **Mise à jour plugin** (facilité)

### Phase 3 (Nice to have) - v3.2.0
9. **Barre temps réel** (UX)
10. **Menu Vidéos backend** (grosse feature)

---

## ⏱️ TEMPS ESTIMÉ

**Phase 1** : ~2-3 heures  
**Phase 2** : ~1-2 heures  
**Phase 3** : ~4-6 heures  

**Total** : ~8-10 heures pour 100% complet

---

## 🎯 QUESTION

**Voulez-vous** :

**Option A** : Tout maintenant (8-10h, v3.0.0 100% complet)  
**Option B** : Phase 1 maintenant (2-3h, v3.0.0 90% + essentiels)  
**Option C** : Tester v2.7.0 actuelle d'abord, puis continuer

**Quelle option choisissez-vous ?**

---

**Version actuelle** : v2.7.0 (90% fonctionnel)  
**Prochaine version** : v3.0.0 (100% complet)
