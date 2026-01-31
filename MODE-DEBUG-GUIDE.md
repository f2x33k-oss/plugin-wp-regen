# 🔧 Guide du Mode Debug

**AI Content Factory Pro v1.7.0**

---

## 🎯 Qu'est-ce que le Mode Debug ?

Un **outil de diagnostic intégré** qui vous permet de :

✅ Voir toutes les informations système  
✅ Consulter les logs filtrés  
✅ Exécuter des tests automatiques  
✅ Copier/Télécharger un rapport complet  
✅ **Reporter facilement les bugs au développeur**  

---

## 🚀 Activation

### Étape 1 : Activer dans Réglages

```
AI Content Factory → Réglages
→ Scroll vers "🔧 Mode Debug" (section rouge)
→ ✅ Activer le mode Debug
→ ✅ Logging détaillé (recommandé)
→ [Enregistrer les modifications]
```

### Étape 2 : Accéder au Menu

```
AI Content Factory → 🔧 Debug
```

**Le menu apparaît seulement si activé !**

---

## 📊 Page Debug

### Actions Rapides

**4 boutons en haut** :

1. **📋 Copier toutes les infos**
   - Copie tout dans le presse-papier
   - À coller dans un email/ticket

2. **💾 Télécharger rapport**
   - Télécharge fichier .txt
   - Nom : `aicfp-debug-report-[timestamp].txt`

3. **🔄 Actualiser**
   - Recharge les informations
   - Rafraîchit les stats

4. **🗑 Vider les logs**
   - Efface debug.log
   - Confirmation requise
   - Recommence à zéro

---

### Informations Affichées

#### 🔧 Plugin
```
Version: 1.7.0
Chemin: /wp-content/plugins/ai-content-factory-pro/
```

#### 💻 Serveur
```
PHP Version: 8.1.0 ✅
MySQL Version: 8.0.32
WordPress Version: 6.4
Memory Limit: 256M
Max Execution Time: 300s
allow_url_fopen: ✅ Activé
cURL: ✅ Disponible
ZipArchive: ✅ Disponible
```

#### 🗄️ Base de Données
```
Table wp_ai_queue: ✅ Existe
Colonnes: 21
Tâches totales: 15
En attente: 2
En cours: 1
Terminées: 12
```

#### 🔑 Clés API
```
OpenAI: ✅ Configurée (51 caractères)
RapidAPI (Midjourney): ✅ Configurée
RapidAPI (Pinterest): ✅ Configurée
SDXL: ⚠️ Non configurée
SDXL Food LoRA: ⚠️ Non configurée
DALL-E 3: ✅ (utilise OpenAI)
Replicate: ⚠️ Non configurée
Flux Pro: ⚠️ Non configurée
```

#### ⏱️ WP-Cron
```
Status: ✅ Activé
Prochaine exécution: 2026-01-31 14:35:00 (dans 2 minutes)
```

#### 📂 Dossiers
```
Uploads: ✅ Accessible en écriture
Temp: ✅ Existe
Fichiers temp: 3
```

#### 🔌 Classes Chargées
```
AI_Content_Factory_Pro: ✅
AICFP_Database: ✅
AICFP_Queue_Manager: ✅
AICFP_API_Handler: ✅
AICFP_Image_API_Manager: ✅
AICFP_Email_Handler: ✅
AICFP_File_Handler: ✅
AICFP_Ajax_Handler: ✅
```

#### 🔗 Hooks AJAX
```
aicfp_submit_generation: ✅
aicfp_calculate_estimate: ✅
aicfp_suggest_titles: ✅
aicfp_search_pinterest: ✅
aicfp_get_queue_status: ✅
aicfp_start_task: ✅
aicfp_pause_task: ✅
aicfp_resume_task: ✅
aicfp_cancel_task: ✅
aicfp_delete_task: ✅
aicfp_submit_album_idees: ✅
```

#### 📊 Statistiques
```
Tâches avec erreurs: 2
Temps moyen génération: 2.3 min
```

#### 🌐 URLs
```
Site URL: https://example.com
Admin URL: https://example.com/wp-admin/
AJAX URL: https://example.com/wp-admin/admin-ajax.php
```

---

### 📋 Logs Récents

**50 dernières lignes** filtrées sur :
- Lignes contenant "AICFP"
- Erreurs PHP (Fatal, Warning)

**Format** :
```
[31-Jan-2026 14:30:15 UTC] AICFP: Tâche créée - ID=12
[31-Jan-2026 14:30:45 UTC] AICFP: Génération Midjourney - Prompt: ...
[31-Jan-2026 14:32:10 UTC] AICFP: Tâche terminée - ID=12
```

**Si aucun log** :
```
Instructions pour activer:
1. wp-config.php
2. Ajouter define('WP_DEBUG', true);
3. Ajouter define('WP_DEBUG_LOG', true);
```

---

### 🧪 Tests Automatiques

**Bouton** : [🧪 Exécuter les tests]

**Tests effectués** :
1. Plugin activé
2. Tables DB créées
3. Clés API configurées
4. WP-Cron planifié
5. Classes chargées (4 principales)
6. Hooks AJAX enregistrés (3 principaux)

**Résultat** :
```
✅ Plugin opérationnel
→ Prêt pour génération

OU

⚠️ Configuration incomplète
→ Réactiver plugin
→ Configurer clé OpenAI
```

---

## 🐛 Reporter un Bug

### Méthode Rapide

**Étape 1** : Activer Mode Debug
```
Réglages → ✅ Mode Debug
```

**Étape 2** : Reproduire le bug
```
Faire l'action qui cause le problème
```

**Étape 3** : Collecter les infos
```
Debug → [📋 Copier toutes les infos]
```

**Étape 4** : Envoyer au développeur
```
Email/Ticket avec:
- Infos copiées (Ctrl+V)
- Description du problème
- Screenshot si possible
```

### Informations Automatiquement Incluses

✅ Version du plugin  
✅ Version PHP/WordPress/MySQL  
✅ Configuration serveur  
✅ État des tables  
✅ Clés API configurées  
✅ Status WP-Cron  
✅ Classes et hooks  
✅ Logs récents  
✅ Statistiques  

**Le développeur aura TOUT pour diagnostiquer !**

---

## 📝 Logging Détaillé

### Quand Activé

**Logs de** :
- Soumission de formulaires
- Appels API (Midjourney, Pinterest, OpenAI)
- Création de tâches
- Traitement de la queue
- Génération d'images
- Génération de textes
- Erreurs

**Exemple** :
```
[31-Jan-2026 14:30:00] AICFP: submit_generation appelé
[31-Jan-2026 14:30:01] AICFP: Titre=20 recettes, Email=test@..., GenText=oui
[31-Jan-2026 14:30:02] AICFP: Tâche créée - ID=12, API=sdxl-food
[31-Jan-2026 14:30:15] AICFP: Génération Midjourney - Prompt: recette...
[31-Jan-2026 14:32:00] AICFP: Recherche Pinterest - Query: recettes
```

### Quand Désactivé

**Logs minimaux** :
- Erreurs critiques seulement
- Moins de bruit dans debug.log

---

## 🔑 Clarification Clés API

### Clé RapidAPI Fournie

**Cette clé fonctionne pour** :
```
✅ Midjourney (génération images)
✅ Pinterest (recherche images)
```

**Cette clé NE FONCTIONNE PAS pour** :
```
❌ SDXL
❌ SDXL Food LoRA
❌ Fine-tuned SDXL
❌ Nanobanana
❌ Flux Pro
❌ Autres APIs RapidAPI
```

### Pourquoi ?

Chaque API sur RapidAPI nécessite un **abonnement séparé**.

La clé fournie a un abonnement actif pour :
- Midjourney Best Experience
- Pinterest Scraper

Pour les autres, vous devez :
1. Aller sur https://rapidapi.com/
2. S'abonner à l'API souhaitée
3. Utiliser VOTRE clé RapidAPI
4. Configurer dans Réglages

---

## 🎯 Cas d'Usage

### Cas 1 : "Rien ne se passe"

**Actions** :
1. Mode Debug activé
2. Debug → Copier infos
3. Vérifier section "🔗 Hooks AJAX"
4. Si ❌ → Désactiver/Réactiver plugin
5. Envoyer rapport au support

### Cas 2 : "Erreur lors de la génération"

**Actions** :
1. Debug → Logs récents
2. Chercher ligne d'erreur
3. Copier l'erreur exacte
4. Télécharger rapport complet
5. Envoyer au support

### Cas 3 : "Calculateur ne fonctionne pas"

**Actions** :
1. Console F12 ouverte
2. Reproduire le problème
3. Copier erreurs console
4. Debug → Télécharger rapport
5. Envoyer les deux au support

### Cas 4 : "WP-Cron ne traite pas"

**Actions** :
1. Debug → Voir section "⏱️ WP-Cron"
2. Vérifier "Status: ✅ Activé"
3. Vérifier "Prochaine exécution"
4. Si ❌ Désactivé → Contacter hébergeur
5. Si timing bizarre → Envoyer rapport

---

## 💡 Conseils

### Activer Mode Debug Quand

✅ Vous rencontrez un problème  
✅ Vous voulez comprendre le système  
✅ Vous testez une nouvelle fonctionnalité  
✅ Vous allez reporter un bug  

### Désactiver Mode Debug Quand

✅ Tout fonctionne parfaitement  
✅ En production stable  
✅ Pas besoin de diagnostic  

**Note** : Le logging détaillé peut être gardé activé sans impact.

---

## 📞 Template de Report de Bug

```
Sujet: [Bug] Description courte

Description:
1. Ce que je faisais:
   [Action effectuée]

2. Ce qui s'est passé:
   [Comportement observé]

3. Ce qui devrait se passer:
   [Comportement attendu]

4. Comment reproduire:
   - Étape 1
   - Étape 2
   - Étape 3

5. Informations système:
   [Coller le contenu de Debug → Copier infos]

6. Screenshot:
   [Joindre si possible]
```

---

## 🔧 Maintenance

### Vider les Logs Régulièrement

Si debug.log devient très gros (>10 MB) :
```
Debug → [🗑 Vider les logs]
```

### Exécuter Tests Après Mise à Jour

```
Debug → [🧪 Exécuter les tests]
→ Vérifier que tout est ✅
```

### Désactiver en Production

Une fois stable :
```
Réglages → ❌ Désactiver Mode Debug
```

Le menu disparaît, mais les logs restent.

---

## 🎉 Avantages

### Pour Vous

- ✅ Comprenez ce qui se passe
- ✅ Diagnostiquez rapidement
- ✅ Exportez facilement les infos
- ✅ Support plus rapide

### Pour le Développeur

- ✅ Rapport structuré
- ✅ Toutes infos nécessaires
- ✅ Logs déjà filtrés
- ✅ Tests déjà exécutés
- ✅ Résolution 10x plus rapide

**Win-Win !** 🚀

---

**Version** : 1.7.0  
**Date** : 31 janvier 2026
