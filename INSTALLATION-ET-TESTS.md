# Installation et Tests - AI Content Factory Pro

## 📦 Installation sur WordPress

### Méthode 1 : Installation via ZIP

1. **Télécharger le plugin**
   ```bash
   # Depuis votre machine locale, créer un ZIP
   cd /workspace
   zip -r ai-content-factory-pro.zip . -x "*.git*" -x "*.md"
   ```

2. **Installer sur WordPress**
   - Aller dans WordPress Admin → Extensions → Ajouter
   - Cliquer sur "Téléverser une extension"
   - Choisir le fichier `ai-content-factory-pro.zip`
   - Cliquer sur "Installer maintenant"
   - Activer le plugin

### Méthode 2 : Installation manuelle

1. **Télécharger les fichiers**
   ```bash
   git clone https://github.com/f2x33k-oss/plugin-wp-regen.git
   cd plugin-wp-regen
   git checkout cursor/plugin-structure-et-file-fd63
   ```

2. **Copier dans WordPress**
   ```bash
   cp -r . /path/to/wordpress/wp-content/plugins/ai-content-factory-pro/
   ```

3. **Activer le plugin**
   - Aller dans WordPress Admin → Extensions
   - Activer "AI Content Factory Pro"

## ⚙️ Configuration initiale

### 1. Configurer les clés API

**Aller dans** : AI Content Factory → Réglages

#### OpenAI (Obligatoire pour génération de texte)
```
Clé API OpenAI: [VOTRE_CLÉ_OPENAI]
```
Obtenir une clé : https://platform.openai.com/api-keys

#### RapidAPI Midjourney (Pré-configurée)
```
Clé API RapidAPI: 60bcbb5fe7mshd88f23d138be003p1be084jsnc1e30b0bb6d3
```
✅ Cette clé est déjà configurée par défaut et fonctionnelle.

### 2. Configurer SMTP (Optionnel mais recommandé)

**Exemple avec Gmail** :
```
✅ Activer SMTP
Hôte SMTP: smtp.gmail.com
Port SMTP: 587
Chiffrement: TLS
Nom d'utilisateur: votre.email@gmail.com
Mot de passe: [Mot de passe d'application Gmail]
Email expéditeur: votre.email@gmail.com
Nom expéditeur: AI Content Factory
```

**Note** : Pour Gmail, créer un mot de passe d'application :
1. Compte Google → Sécurité
2. Validation en deux étapes → Activer
3. Mots de passe d'application → Créer

### 3. Activer le logging WordPress

Dans `wp-config.php`, ajouter :
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Les logs seront dans `/wp-content/debug.log`

## 🧪 Tests recommandés

### Test 1 : Génération simple (1 recette)

1. **Aller dans** : AI Content Factory → Générer

2. **Remplir le formulaire** :
   ```
   Titre: 1 recette de gratin dauphinois
   ✅ Générer les textes (activé)
   Images de référence: [Laisser vide]
   Email: votre@email.com
   ```

3. **Vérifier l'estimation** :
   ```
   Items: 1
   Coût estimé: $0.07
   Temps estimé: 2 min
   ```

4. **Cliquer sur** "Lancer la génération"

5. **Suivre la progression** : AI Content Factory → Instances

### Test 2 : Génération avec images de référence

1. **Créer un ZIP avec 2-3 images** de gratins
   ```bash
   # Exemple
   zip references.zip image1.jpg image2.jpg image3.jpg
   ```

2. **Générer** :
   ```
   Titre: 3 recettes de gratins variés
   ✅ Générer les textes (activé)
   Images de référence: [Uploader references.zip]
   Email: votre@email.com
   ```

3. **Résultat attendu** :
   - 3 images générées avec style des références
   - 3 recettes détaillées analysant chaque image
   - 1 article WordPress avec tout le contenu
   - 1 email avec liens et aperçus

### Test 3 : Génération sans texte (images uniquement)

1. **Générer** :
   ```
   Titre: 5 plats de pâtes
   ❌ Générer les textes (désactivé)
   Images de référence: [Optionnel]
   Email: votre@email.com
   ```

2. **Résultat attendu** :
   - 5 images générées
   - Aucun article WordPress créé
   - Email avec uniquement les URLs d'images et prompts

## 🔍 Vérifications après génération

### Vérifier dans WordPress

1. **Articles** :
   - Aller dans Articles → Tous les articles
   - Vérifier que l'article est en mode "Brouillon"
   - Ouvrir l'article pour voir le contenu

2. **Meta Box** :
   - Dans l'éditeur d'article
   - Voir la sidebar "AI Content Factory - Informations de génération"
   - Vérifier les images générées et les logs

3. **Médiathèque** :
   - Aller dans Médias
   - Vérifier que les images sont bien importées

### Vérifier les emails

1. **Email de complétion** :
   - Vérifier votre boîte mail
   - Ouvrir l'email "Tâche terminée"
   - Vérifier les liens et images

2. **Contenu de l'email** :
   - ✅ Informations de la tâche
   - ✅ Lien vers l'article (si texte activé)
   - ✅ Galerie d'images générées
   - ✅ Liste des prompts utilisés

### Vérifier les logs

```bash
tail -f /path/to/wordpress/wp-content/debug.log
```

**Rechercher** :
- `Midjourney API Response:` - Réponse de l'API
- `Midjourney polling response:` - Statut du polling
- `AICFP:` - Messages du plugin

## 📊 Temps de génération attendus

### Pour 1 recette
- **Génération image** : ~2-5 minutes (mode relax)
- **Génération texte** : ~30 secondes
- **Total** : ~3 minutes

### Pour 10 recettes
- **Génération images** : ~20-50 minutes
- **Génération textes** : ~5 minutes
- **Total** : ~30 minutes

### Pour 20 recettes
- **Génération images** : ~40-100 minutes
- **Génération textes** : ~10 minutes
- **Total** : ~60 minutes

**Note** : Le temps varie selon la charge de l'API Midjourney.

## 🐛 Dépannage

### Problème 1 : Les tâches restent en "pending"

**Cause** : WP-Cron ne fonctionne pas

**Solution** :
```bash
# Vérifier si WP-Cron est désactivé
grep DISABLE_WP_CRON wp-config.php

# Si désactivé, configurer un vrai cron
crontab -e
# Ajouter :
*/1 * * * * curl https://votre-site.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1
```

### Problème 2 : Erreur "Clé API OpenAI non configurée"

**Solution** :
1. Aller dans AI Content Factory → Réglages
2. Entrer votre clé API OpenAI
3. Sauvegarder

### Problème 3 : Erreur "Rate limit exceeded"

**Cause** : Quota API dépassé

**Solution** :
1. Vérifier votre quota RapidAPI
2. Upgrader votre plan si nécessaire
3. Attendre le reset du quota (généralement mensuel)

### Problème 4 : Images générées mais pas de texte

**Cause** : Clé OpenAI invalide ou quota dépassé

**Solution** :
1. Vérifier la clé OpenAI dans les réglages
2. Vérifier le quota OpenAI : https://platform.openai.com/usage
3. Consulter les logs pour l'erreur exacte

### Problème 5 : Timeout lors de la génération

**Cause** : API Midjourney saturée ou en maintenance

**Solution** :
1. Vérifier le statut de RapidAPI
2. La tâche reprendra automatiquement
3. Si timeout persistant, annuler et relancer

### Problème 6 : Email non reçu

**Causes possibles** :
- SMTP mal configuré
- Email dans les spams
- WP Mail désactivé

**Solutions** :
1. Configurer SMTP dans les réglages
2. Vérifier le dossier spam
3. Tester avec un plugin comme WP Mail SMTP

## 📈 Monitoring

### Surveiller les tâches

**Dashboard** : AI Content Factory → Instances

**Statistiques** :
- En attente
- En cours
- Terminées
- En pause

**Actions disponibles** :
- Pause : Mettre en pause
- Reprendre : Relancer une tâche en pause
- Annuler : Annuler une tâche
- Supprimer : Supprimer une tâche terminée

### Analyser les performances

**Temps moyen par image** :
```sql
SELECT AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_minutes
FROM wp_ai_queue
WHERE status = 'completed';
```

**Taux de succès** :
```sql
SELECT 
  status,
  COUNT(*) as count,
  (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM wp_ai_queue)) as percentage
FROM wp_ai_queue
GROUP BY status;
```

## 🎯 Scénarios d'utilisation

### Scénario 1 : Blog de recettes

**Objectif** : Générer 30 recettes par semaine

**Configuration** :
```
Lundi : 10 recettes de gratins
Mercredi : 10 recettes de soupes
Vendredi : 10 recettes de desserts
```

**Temps total par semaine** : ~3 heures
**Coût estimé par semaine** : ~$2.10

### Scénario 2 : Livre de cuisine numérique

**Objectif** : Créer un ebook avec 100 recettes

**Configuration** :
```
Lancer 5 tâches de 20 recettes chacune
Espacer de 2-3 heures entre chaque tâche
```

**Temps total** : ~10 heures réparties sur 2 jours
**Coût estimé total** : ~$7.00

### Scénario 3 : Contenu pour réseaux sociaux

**Objectif** : Images de plats pour Instagram

**Configuration** :
```
Titre : 30 plats photogéniques
❌ Générer les textes (désactivé)
✅ Images de référence (pour le style)
```

**Temps total** : ~60 minutes
**Coût estimé** : ~$1.50

## 🔐 Sécurité et sauvegarde

### Sauvegarder la configuration

```bash
# Exporter les options WordPress
wp option get aicfp_openai_api_key > backup-config.txt
wp option get aicfp_rapidapi_key >> backup-config.txt
wp option get aicfp_smtp_enabled >> backup-config.txt
# etc.
```

### Sauvegarder la base de données

```bash
# Exporter la table de file d'attente
mysqldump -u user -p wordpress wp_ai_queue > backup-queue.sql
```

### Nettoyer les anciennes tâches

```php
// Dans WordPress Admin → Outils → PHP
AICFP_Database::cleanup_old_tasks(); // Supprime les tâches > 30 jours
AICFP_File_Handler::cleanup_temp_files(); // Supprime les fichiers > 7 jours
```

## 📞 Support

### Ressources
- **Documentation API** : `API-MIDJOURNEY-GUIDE.md`
- **Guide Prompts** : `PROMPT-CHATGPT-RECETTES.md`
- **Changelog** : `CHANGELOG.md`
- **Logs WordPress** : `/wp-content/debug.log`

### Contacts
- **GitHub Issues** : https://github.com/f2x33k-oss/plugin-wp-regen/issues
- **RapidAPI Support** : https://rapidapi.com/support
- **OpenAI Support** : https://help.openai.com/

## ✅ Checklist pré-production

Avant de lancer en production :

- [ ] Clé API OpenAI configurée et validée
- [ ] Clé API RapidAPI configurée (par défaut OK)
- [ ] SMTP configuré et testé
- [ ] WP-Cron fonctionnel
- [ ] Logging WordPress activé
- [ ] Test de génération simple réussi
- [ ] Test avec images de référence réussi
- [ ] Email de notification reçu
- [ ] Article WordPress créé correctement
- [ ] Meta Box visible dans l'éditeur
- [ ] Médiathèque contient les images
- [ ] Dashboard Instances fonctionne
- [ ] Actions Pause/Reprendre/Annuler testées
- [ ] Sauvegarde de configuration effectuée

## 🎉 Prêt à l'utilisation !

Une fois tous les tests validés, le plugin est prêt pour une utilisation en production.

**Bon succès avec AI Content Factory Pro !** 🚀
