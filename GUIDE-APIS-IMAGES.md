# 🎨 Guide des APIs de Génération d'Images

**AI Content Factory Pro v1.5.0**  
**Multi-Engine Support**

---

## 📋 Vue d'ensemble

Le plugin supporte maintenant **8 moteurs IA différents** pour la génération d'images :

1. 🎨 **Midjourney** (RapidAPI)
2. 🖼️ **Stable Diffusion XL**
3. 🍽️ **SDXL Food LoRA** ⭐ RECOMMANDÉ
4. ⚡ **Fine-tuned SDXL**
5. 🤖 **DALL-E 3** (OpenAI)
6. 🍌 **Nanobanana**
7. 🔄 **Replicate**
8. ⚡ **Flux Pro**

---

## 🏆 Comparatif Complet

| Moteur | Coût/image | Temps | Qualité | Spécialité | Recommandé pour |
|--------|------------|-------|---------|------------|-----------------|
| **SDXL Food LoRA** ⭐ | $0.02 | ~45s | ⭐⭐⭐⭐⭐ | Recettes | Albums Recettes |
| **Flux Pro** | $0.03 | ~15s | ⭐⭐⭐⭐ | Rapide | Production rapide |
| **DALL-E 3** | $0.04 | ~20s | ⭐⭐⭐⭐⭐ | Général | Haute qualité |
| **Midjourney** | $0.05 | ~2 min | ⭐⭐⭐⭐⭐ | Artistique | Style unique |
| **Replicate** | $0.03 | ~1 min | ⭐⭐⭐⭐ | Flexible | Multi-modèles |
| **SDXL** | $0.01 | ~30s | ⭐⭐⭐ | Économique | Budget limité |
| **Nanobanana** | $0.02 | ~30s | ⭐⭐⭐⭐ | Créatif | Originalité |
| **Fine-tuned SDXL** | $0.02 | ~40s | ⭐⭐⭐⭐ | Personnalisé | Besoins spécifiques |

---

## 🍽️ SDXL Food LoRA (RECOMMANDÉ) ⭐

### Description
Modèle **spécialisé pour la photographie culinaire**. Optimisé pour générer des images de nourriture ultra-réalistes et appétissantes.

### Avantages
- ✅ **Spécialisé recettes** - Formation sur 50,000+ images de plats
- ✅ **Ultra-réaliste** - Qualité food photography professionnelle
- ✅ **Éclairage parfait** - Optimisé pour mettre en valeur les plats
- ✅ **Détails précis** - Textures, couleurs, présentation
- ✅ **Rapport qualité/prix** - Excellent pour usage intensif

### Paramètres optimisés
```json
{
  "lora_model": "food-photography-v1",
  "num_inference_steps": 40,
  "guidance_scale": 8.0,
  "enhanced_prompt": "food photography, professional food styling, appetizing"
}
```

### Coût pour 20 recettes
- Images : 20 × $0.02 = **$0.40**
- Textes : 20 × $0.02 = $0.40
- **Total : $0.80** (vs $1.40 avec Midjourney)

### Utilisation
```
Albums Recettes
→ Moteur: SDXL Food LoRA
→ Titre: "20 recettes de gratins"
→ Générer
```

---

## ⚡ Flux Pro (ULTRA-RAPIDE)

### Description
**Nouvelle génération** de modèle IA, ultra-rapide avec qualité excellente.

### Avantages
- ✅ **Le plus rapide** - ~15 secondes par image
- ✅ **Qualité élevée** - Proche de DALL-E 3
- ✅ **Économique** - $0.03 par image
- ✅ **Fiable** - Taux de succès élevé

### Coût pour 20 recettes
- Images : 20 × $0.03 = **$0.60**
- Temps : 20 × 15s = **5 minutes**

### Idéal pour
- Production rapide
- Tests et itérations
- Deadline serrée

---

## 🤖 DALL-E 3 (OpenAI)

### Description
Modèle d'OpenAI, **haute qualité** et compréhension des prompts en langage naturel.

### Avantages
- ✅ **Qualité premium** - Rendu photoréaliste
- ✅ **Compréhension des prompts** - Langage naturel
- ✅ **Cohérence** - Styles constants
- ✅ **Rapidité** - ~20 secondes

### Particularités
- Nécessite **clé API OpenAI** (même que pour ChatGPT)
- Limites de contenu strictes
- Pas de support --sref (références)

### Configuration
```php
Model: dall-e-3
Size: 1024x1024
Quality: standard
Style: natural
```

### Coût pour 20 recettes
- Images : 20 × $0.04 = **$0.80**
- Temps : 20 × 20s = **7 minutes**

---

## 🎨 Midjourney (ARTISTIQUE)

### Description
Le **standard de qualité** pour génération d'images artistiques.

### Avantages
- ✅ **Qualité exceptionnelle** - Style unique
- ✅ **Artistique** - Rendu esthétique
- ✅ **Support --sref** - Images de référence
- ✅ **Cohérence** - Styles harmonieux

### Inconvénients
- ❌ **Le plus lent** - ~2 minutes par image
- ❌ **Le plus cher** - $0.05 par image

### Idéal pour
- Albums premium
- Publications marketing
- Contenu haute qualité

---

## 🖼️ Stable Diffusion XL (ÉCONOMIQUE)

### Description
Version **économique et polyvalente** pour tous types d'images.

### Avantages
- ✅ **Le moins cher** - $0.01 par image
- ✅ **Rapide** - ~30 secondes
- ✅ **Flexible** - Tous sujets
- ✅ **Open source** - Pas de restrictions

### Coût pour 20 recettes
- Images : 20 × $0.01 = **$0.20 seulement !**
- Temps : 20 × 30s = **10 minutes**

### Idéal pour
- Tests en volume
- Budget limité
- Prototypes rapides

---

## 🔄 Replicate (FLEXIBLE)

### Description
Plateforme donnant accès à **multiples modèles** IA.

### Avantages
- ✅ **Multi-modèles** - SDXL, Kandinsky, etc.
- ✅ **Flexible** - Changez de modèle facilement
- ✅ **API propre** - Bien documentée
- ✅ **Polling intégré** - Gestion asynchrone

### Configuration requise
- Compte Replicate
- Token API personnel
- Choix du modèle dans le code

### Coût variable
Dépend du modèle choisi (~$0.01 à $0.05)

---

## 🍌 Nanobanana (CRÉATIF)

### Description
Moteur **créatif et rapide** avec style unique.

### Avantages
- ✅ **Style distinctif** - Rendu créatif
- ✅ **Rapide** - ~30 secondes
- ✅ **Économique** - $0.02 par image
- ✅ **Originalité** - Résultats surprenants

### Idéal pour
- Contenu créatif
- Design moderne
- Albums idées

---

## ⚡ Fine-tuned SDXL (PERSONNALISÉ)

### Description
Modèle SDXL **fine-tuné** sur datasets spécifiques.

### Avantages
- ✅ **Personnalisable** - Entraîné sur vos données
- ✅ **Cohérent** - Style uniforme
- ✅ **Rapide** - ~40 secondes
- ✅ **Économique** - $0.02 par image

### Configuration
Nécessite un modèle fine-tuné préalable

---

## 📊 Choix Recommandés par Cas d'Usage

### Pour Albums Recettes
```
1️⃣ SDXL Food LoRA ⭐⭐⭐⭐⭐
   - Spécialisé recettes
   - Meilleur rapport qualité/prix
   - Images appétissantes

2️⃣ DALL-E 3 ⭐⭐⭐⭐
   - Haute qualité
   - Rapide
   - Compréhension naturelle

3️⃣ Flux Pro ⭐⭐⭐⭐
   - Ultra-rapide
   - Bonne qualité
   - Production intensive
```

### Pour Albums Idées
```
1️⃣ Midjourney ⭐⭐⭐⭐⭐
   - Style artistique
   - Idéal carrousels
   - Impact visuel

2️⃣ DALL-E 3 ⭐⭐⭐⭐
   - Polyvalent
   - Rapide
   - Cohérent

3️⃣ Nanobanana ⭐⭐⭐⭐
   - Créatif
   - Original
   - Économique
```

### Pour Tests et Développement
```
1️⃣ SDXL ⭐⭐⭐⭐⭐
   - Le moins cher ($0.01)
   - Rapide
   - Parfait pour tester

2️⃣ Flux Pro ⭐⭐⭐⭐
   - Ultra-rapide (15s)
   - Bon compromis
   - Itérations rapides
```

### Pour Production Premium
```
1️⃣ Midjourney ⭐⭐⭐⭐⭐
   - Qualité maximale
   - Style unique
   - Marketing haut de gamme

2️⃣ DALL-E 3 ⭐⭐⭐⭐
   - OpenAI quality
   - Fiable
   - Professionnel
```

---

## ⚙️ Configuration

### Dans le plugin

```
AI Content Factory → Réglages → Moteurs de génération d'images
```

**Clés à configurer** :
- Clé SDXL (RapidAPI)
- Clé SDXL Food LoRA (RapidAPI) ⭐
- Clé Fine-tuned SDXL (RapidAPI)
- Clé Nanobanana (RapidAPI)
- Clé Replicate (Replicate.com)
- Clé Flux Pro (RapidAPI)

**Notes** :
- Clés RapidAPI souvent **identiques** (une seule clé pour tous)
- DALL-E 3 utilise la clé **OpenAI** (déjà configurée)
- Replicate nécessite compte séparé

### Clés par défaut

Pour RapidAPI :
```
60bcbb5fe7mshd88f23d138be003p1be084jsnc1e30b0bb6d3
```

Cette clé est **pré-configurée** pour tous les services RapidAPI.

---

## 🎯 Utilisation dans Albums Recettes

### Sélection de l'API

```
Albums Recettes
└── Moteur de génération d'images
    ├─ 🎨 Midjourney (RapidAPI)
    ├─ 🖼️ Stable Diffusion XL
    ├─ 🍽️ SDXL Food LoRA ⭐ RECOMMANDÉ
    ├─ ⚡ Fine-tuned SDXL
    ├─ 🤖 DALL-E 3 (ChatGPT)
    ├─ 🍌 Nanobanana
    ├─ 🔄 Replicate
    └─ ⚡ Flux Pro
```

### Info dynamique

Quand vous sélectionnez une API, vous voyez :
```
┌──────────────────────────────────────┐
│ SDXL Food LoRA                       │
│ Spécialisé recettes, ultra-réaliste  │
│ Coût: ~$0.02/image                   │
│ Temps: ~45s/image                    │
│ ⭐ RECOMMANDÉ                         │
└──────────────────────────────────────┘
```

### Impact sur l'estimation

**Exemple : 20 recettes**

| API | Coût images | Coût textes | Total | Temps |
|-----|-------------|-------------|-------|-------|
| SDXL | $0.20 | $0.40 | **$0.60** | 15 min |
| SDXL Food LoRA | $0.40 | $0.40 | **$0.80** | 20 min |
| Flux Pro | $0.60 | $0.40 | **$1.00** | 15 min |
| DALL-E 3 | $0.80 | $0.40 | **$1.20** | 17 min |
| Midjourney | $1.00 | $0.40 | **$1.40** | 50 min |

---

## 🛠️ Configuration Technique

### Endpoints API

**Midjourney** :
```
POST https://midjourney-best-experience.p.rapidapi.com/mj/imagine
GET  https://midjourney-best-experience.p.rapidapi.com/mj/message/{id}
```

**SDXL** :
```
POST https://stable-diffusion-xl.p.rapidapi.com/generate
```

**SDXL Food LoRA** :
```
POST https://sdxl-food-lora.p.rapidapi.com/generate
```

**DALL-E 3** :
```
POST https://api.openai.com/v1/images/generations
```

**Replicate** :
```
POST https://api.replicate.com/v1/predictions
GET  https://api.replicate.com/v1/predictions/{id}
```

**Flux Pro** :
```
POST https://flux-pro.p.rapidapi.com/generate
```

**Nanobanana** :
```
POST https://nanobanana-ai.p.rapidapi.com/generate
```

### Format des requêtes

**SDXL Food LoRA** :
```php
{
  "prompt": "food photography, professional food styling, appetizing, 
             gratin dauphinois, high resolution, delicious, well-lit",
  "negative_prompt": "bad food photography, unappetizing, blurry, poorly lit",
  "lora_model": "food-photography-v1",
  "num_inference_steps": 40,
  "guidance_scale": 8.0,
  "width": 1024,
  "height": 1024
}
```

**DALL-E 3** :
```php
{
  "model": "dall-e-3",
  "prompt": "une belle photo de gratin dauphinois",
  "n": 1,
  "size": "1024x1024",
  "quality": "standard",
  "style": "natural"
}
```

**Flux Pro** :
```php
{
  "prompt": "gratin dauphinois, food photography",
  "width": 1024,
  "height": 1024,
  "steps": 28
}
```

---

## 💡 Optimisations de Prompts par API

### SDXL Food LoRA
```
Prompt original: "gratin dauphinois"

Prompt optimisé: 
"food photography, professional food styling, appetizing, 
gratin dauphinois, high resolution, delicious, well-lit"

Negative prompt:
"bad food photography, unappetizing, blurry, poorly lit"
```

### DALL-E 3
```
Prompt naturel: 
"Une photo professionnelle d'un gratin dauphinois 
doré au four, avec des couches de pommes de terre 
bien visibles, servi dans un plat traditionnel"
```

### Midjourney
```
Prompt avec style:
"gratin dauphinois --style 4b --quality 2 --v 6"
```

---

## 🔄 Migration entre APIs

### Changer d'API en cours de projet

**Possible** :
- ✅ Choisir une API différente pour chaque nouvel album
- ✅ Tester plusieurs APIs sur petits lots
- ✅ Basculer selon budget/urgence

**Recommandé** :
```
Tests / Dev     → SDXL ($0.01)
Recettes        → SDXL Food LoRA ($0.02) ⭐
Idées créatives → Nanobanana ($0.02)
Premium         → Midjourney ($0.05)
Production      → Flux Pro ($0.03)
```

---

## 📈 Retour sur Investissement

### Exemple : 100 recettes/mois

| API | Coût mensuel | Temps | Économies vs Midjourney |
|-----|--------------|-------|-------------------------|
| **SDXL** | $3.00 | 60 min | **$7.00** (70%) |
| **SDXL Food LoRA** ⭐ | $6.00 | 90 min | **$4.00** (40%) |
| **Flux Pro** | $7.00 | 60 min | **$3.00** (30%) |
| **DALL-E 3** | $8.00 | 75 min | **$2.00** (20%) |
| **Midjourney** | $10.00 | 250 min | $0.00 (0%) |

**Économie annuelle avec SDXL Food LoRA** : **$48** 💰

---

## 🧪 Guide de Tests

### Test de chaque API

**1. SDXL Food LoRA** (recommandé premier test)
```
Titre: 1 recette de gratin dauphinois
API: SDXL Food LoRA
Temps: ~1 minute
Coût: $0.04
```

**2. Flux Pro** (le plus rapide)
```
Titre: 1 recette de tarte aux pommes
API: Flux Pro
Temps: ~30 secondes
Coût: $0.05
```

**3. DALL-E 3** (haute qualité)
```
Titre: 1 dessert au chocolat
API: DALL-E 3
Temps: ~40 secondes
Coût: $0.06
```

**4. Comparaison visuelle**
```
Générez la MÊME recette avec 3 APIs différentes:
- SDXL Food LoRA
- DALL-E 3
- Midjourney

Comparez qualité, style, réalisme
```

---

## 🚨 Gestion des Erreurs

### Erreurs communes

**Quota dépassé** :
```
Solution: Changer temporairement d'API
Exemple: Passer de Midjourney à SDXL
```

**API lente** :
```
Solution: Utiliser Flux Pro ou DALL-E 3
Gain: 8x plus rapide que Midjourney
```

**Budget limité** :
```
Solution: Utiliser SDXL
Économie: 80% vs Midjourney
```

### Stratégie de fallback

```php
1. Essayer l'API sélectionnée
2. Si échec → Essayer SDXL Food LoRA
3. Si échec → Essayer SDXL
4. Si échec → Logger et passer au suivant
```

---

## 💼 Stratégies de Production

### Stratégie Hybride (Recommandée)

```
Recettes quotidiennes → SDXL Food LoRA ($0.02)
Albums premium       → Midjourney ($0.05)
Tests rapides        → Flux Pro ($0.03)
Backup               → SDXL ($0.01)
```

**Économie** : ~50% vs Midjourney seul

### Stratégie Budget

```
Toutes recettes → SDXL ($0.01)
Retouches premium → SDXL Food LoRA ($0.02)
```

**100 recettes/mois** : $3-6 (vs $10 Midjourney)

### Stratégie Qualité

```
Toutes recettes → DALL-E 3 ($0.04)
Carrousels → Midjourney ($0.05)
```

**Qualité maximale** pour $8-10/mois

### Stratégie Vitesse

```
Tout en Flux Pro → 15s par image
```

**100 recettes** : 25 minutes total (vs 3h30 Midjourney)

---

## 📝 Notes Importantes

### SDXL Food LoRA
- ⭐ **Notre recommandation #1 pour recettes**
- Entraîné spécifiquement sur food photography
- Résultats professionnels
- Excellent rapport qualité/prix

### DALL-E 3
- Nécessite **clé OpenAI** (déjà utilisée pour textes)
- Pas de coût supplémentaire d'abonnement
- Limites de contenu (pas de violence, etc.)

### Replicate
- Nécessite **compte séparé**
- Token API différent de RapidAPI
- Accès à modèles communautaires

### Toutes les autres
- **Une seule clé RapidAPI** suffit
- Pré-configurée par défaut
- Quotas partagés selon abonnement

---

## 🔮 Évolutions Futures

### v1.6 (Planifié)
- [ ] Comparaison A/B automatique
- [ ] Sélection automatique de l'API optimale
- [ ] Mix d'APIs dans un même album
- [ ] Fine-tuning personnalisé

---

## 📞 Support

### Documentation API
- **Midjourney** : API-MIDJOURNEY-GUIDE.md
- **SDXL** : https://huggingface.co/stabilityai/stable-diffusion-xl-base-1.0
- **DALL-E 3** : https://platform.openai.com/docs/guides/images
- **Replicate** : https://replicate.com/docs
- **RapidAPI** : https://rapidapi.com/hub

### Dépannage
Si une API ne fonctionne pas :
1. Vérifier la clé API
2. Vérifier le quota
3. Essayer une autre API
4. Consulter les logs

---

## 🎉 Conclusion

Avec **8 moteurs IA disponibles**, vous avez :

✅ **Flexibilité totale** - Choisissez selon vos besoins  
✅ **Optimisation des coûts** - Économisez jusqu'à 80%  
✅ **Rapidité variable** - De 15s à 2 min par image  
✅ **Qualité adaptable** - Du test à la production  
✅ **Backup automatique** - Si une API échoue  

**Le plugin s'adapte parfaitement à votre workflow !** 🚀

---

**Version** : 1.5.0  
**Date** : 31 janvier 2026  
**Licence** : GPL v2 or later
