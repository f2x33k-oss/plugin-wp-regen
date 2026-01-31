# 🚀 Démarrage Rapide - 5 Minutes

**AI Content Factory Pro v1.6.0**

---

## ⚡ Installation Express (2 minutes)

### 1. Télécharger

```
https://github.com/f2x33k-oss/plugin-wp-regen/raw/cursor/plugin-structure-et-file-fd63/ai-content-factory-pro.zip
```

### 2. Installer

```
WordPress → Extensions → Ajouter
→ Téléverser
→ Choisir le ZIP
→ Installer
→ Activer
```

### 3. Configurer

```
AI Content Factory → Réglages
→ Clé API OpenAI: [VOTRE_CLÉ]
→ [Enregistrer]
```

**Obtenir clé OpenAI** : https://platform.openai.com/api-keys

---

## 🧪 Premier Test (3 minutes)

### Étape 1 : Ouvrir Console (IMPORTANT)

```
1. Appuyer sur F12
2. Onglet "Console"
3. Garder ouvert
```

**Vérifier** : "AICFP: Initialisation Albums Recettes OK"

### Étape 2 : Générer 1 Recette

```
AI Content Factory → Albums Recettes

Titre: 1 recette de gratin dauphinois
✅ Générer les textes
API: SDXL Food LoRA
Email: votre@email.com

[🔨 Lancer la génération]
```

**Estimateur doit afficher** :
- 🍽️ 1 recette
- 💰 $0.04
- ⏱️ 1 min

### Étape 3 : Observer

```
1. Console : Aucune erreur rouge ✅
2. Notification : "Album ajouté" ✅
3. Redirection : Vers Instances ✅
4. Tâche visible : Avec highlight bleu ✅
```

### Étape 4 : Attendre

```
⏱️ ~1-2 minutes

Status: En attente → En cours → Terminée

Quand terminée:
→ [📄 Voir l'article]
→ Article créé ✅
→ Email reçu ✅
```

---

## ❌ Si Problème

### Estimateur ne se met pas à jour

```
Solution:
1. Ctrl+F5 (vider cache)
2. Recharger la page
```

### Rien ne se passe au clic "Lancer"

```
Console doit montrer:
→ "aicfp_ajax is not defined" ❌

Solution:
1. Vider cache navigateur
2. Désactiver/Réactiver plugin
```

### Tâche reste en "En attente"

```
Attendre 60 secondes (WP-Cron)

Si toujours en attente:
→ Vérifier debug.log
→ Déclencher WP-Cron manuellement:
   https://yoursite.com/wp-cron.php
```

### Erreur "Clé API non configurée"

```
→ Réglages → Entrer clé OpenAI
→ Sauvegarder
→ Réessayer
```

---

## 📚 Documentation Complète

Si besoin de plus d'infos :

1. **GUIDE-INSTALLATION-COMPLETE.md** - Installation détaillée
2. **AUDIT-SECURITE-FONCTIONNEL.md** - Tests et diagnostics
3. **VERSION-1.4.0-GUIDE-COMPLET.md** - Features UI
4. **GUIDE-APIS-IMAGES.md** - APIs images
5. **SUIVI-GENERATIONS-v1.6.md** - Suivi avancé

---

## ✅ Checklist 30 Secondes

- [ ] Plugin installé et activé
- [ ] Clé OpenAI configurée
- [ ] Test 1 recette lancé
- [ ] Console sans erreur
- [ ] Estimateur fonctionne
- [ ] Redirection automatique
- [ ] Tâche visible dans Instances

**Si 7/7 cochés** : ✅ Vous êtes prêt !

---

## 🎯 Prochaines Étapes

Une fois le test réussi :

1. Tester avec 5 recettes
2. Tester Pinterest
3. Tester suggestions de titres
4. Tester Albums Idées
5. Configuration des APIs vidéo
6. Utilisation en production

---

**Support** : Consulter GUIDE-INSTALLATION-COMPLETE.md

**Version** : 1.6.0  
**Status** : ✅ Testé et validé
