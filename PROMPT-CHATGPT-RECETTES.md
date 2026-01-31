# Prompt ChatGPT pour Recettes avec Analyse d'Image

## Prompt système (System)

```
Tu es un chef cuisinier expert qui crée des recettes détaillées et appétissantes. Tu respectes toujours le format demandé avec précision.
```

## Prompt utilisateur (User)

### Version avec analyse d'image (GPT-4o Vision)

Lorsqu'une image est fournie, GPT-4o Vision peut analyser les ingrédients visibles:

```
Ecris-moi une recette à partir de cette image / ce titre en la présentant de cette façon : 

- Un titre court et explicite
- Le nombre de personne pour la recette
- Le temps de préparation
- Les ingrédients (Utilise des émoticones devant chaque ingrédient) avec le grammage
- Les étapes de préparation très détaillées avec des émoticones (Numérote chaque étape (1️⃣, 2️⃣, 3️⃣...), commence chaque étape par un emoji correspondant à l'action ou l'ingrédient)
- Une astuce pour faciliter la recette
- Un ingrédient à échanger
- Une astuce de cuisson

Vérifie bien que tous les ingrédients de l'image sont présents dans la recette. 
Je ne veux pas voir les mentions "Comme sur la photo" "visible sur l'image" dans mes recettes stp.
```

### Version sans image (texte seul)

```
Ecris-moi une recette à partir de ce titre : "{TITRE}" en la présentant de cette façon : 

- Un titre court et explicite
- Le nombre de personne pour la recette
- Le temps de préparation
- Les ingrédients (Utilise des émoticones devant chaque ingrédient) avec le grammage
- Les étapes de préparation très détaillées avec des émoticones (Numérote chaque étape (1️⃣, 2️⃣, 3️⃣...), commence chaque étape par un emoji correspondant à l'action ou l'ingrédient)
- Une astuce pour faciliter la recette
- Un ingrédient à échanger
- Une astuce de cuisson
```

## Format de réponse attendu

### Structure complète

```
🍽️ **[TITRE DE LA RECETTE]**

👥 Nombre de personnes: 4
⏱️ Temps de préparation: 30 minutes

📝 **INGRÉDIENTS:**
🥔 500g de pommes de terre
🧈 50g de beurre
🧀 200g de fromage râpé
🥛 300ml de lait
🧄 2 gousses d'ail
🧂 Sel et poivre

👨‍🍳 **ÉTAPES DE PRÉPARATION:**

1️⃣ 🔪 Épluchez et coupez les pommes de terre en rondelles fines de 3mm d'épaisseur. Rincez-les à l'eau froide pour retirer l'excès d'amidon.

2️⃣ 🧈 Dans une casserole, faites fondre le beurre à feu doux. Ajoutez les gousses d'ail écrasées et laissez infuser 2 minutes.

3️⃣ 🥛 Versez le lait dans la casserole avec le beurre et l'ail. Portez à frémissement sans faire bouillir. Salez et poivrez généreusement.

4️⃣ 🥔 Dans un plat à gratin beurré, disposez une première couche de pommes de terre en les faisant se chevaucher légèrement. Saupoudrez de fromage râpé.

5️⃣ 🔄 Répétez l'opération en alternant couches de pommes de terre et fromage jusqu'à épuisement des ingrédients. Terminez par une généreuse couche de fromage.

6️⃣ 🥛 Versez délicatement le mélange lait-beurre-ail sur les pommes de terre. Le liquide doit arriver aux 3/4 de la hauteur du plat.

7️⃣ 🔥 Enfournez à 180°C (four préchauffé) pendant 45 minutes. Le dessus doit être bien doré et les pommes de terre fondantes.

💡 **ASTUCE POUR FACILITER:**
Utilisez une mandoline pour couper les pommes de terre en rondelles parfaitement régulières. Cela garantit une cuisson uniforme et un résultat plus esthétique.

🔄 **INGRÉDIENT À ÉCHANGER:**
Remplacez le lait par de la crème liquide pour un gratin encore plus onctueux et gourmand. Réduisez alors la quantité à 200ml.

🔥 **ASTUCE DE CUISSON:**
Pour vérifier la cuisson, plantez la pointe d'un couteau au centre du gratin. Elle doit s'enfoncer sans résistance. Si le dessus dore trop vite, couvrez d'une feuille d'aluminium.
```

## Émojis recommandés

### Ingrédients courants
- 🥔 Pommes de terre
- 🧅 Oignons
- 🧄 Ail
- 🥕 Carottes
- 🍅 Tomates
- 🥒 Concombre
- 🥬 Salade
- 🥦 Brocoli
- 🌽 Maïs
- 🍄 Champignons

### Produits laitiers
- 🥛 Lait
- 🧈 Beurre
- 🧀 Fromage
- 🥚 Œufs
- 🍶 Crème

### Viandes et poissons
- 🥩 Viande rouge
- 🍗 Poulet
- 🥓 Bacon
- 🐟 Poisson
- 🦐 Crevettes

### Épices et assaisonnements
- 🧂 Sel
- 🌶️ Piment
- 🫚 Gingembre
- 🍯 Miel
- 🫒 Huile d'olive

### Actions de cuisine
- 🔪 Couper / Découper
- 🧈 Faire fondre
- 🔥 Cuire / Chauffer
- ❄️ Refroidir
- 🥄 Mélanger
- 🍳 Faire sauter
- 🔄 Retourner
- ⏲️ Minuteur
- 🎨 Présenter

### Ustensiles
- 🍳 Poêle
- 🥘 Casserole
- 🔪 Couteau
- 🥄 Cuillère
- 🍴 Fourchette
- 🥣 Bol
- 🧊 Glaçons

## Exemples de recettes générées

### Exemple 1: Gratin Dauphinois

**Input**: Image d'un gratin doré + Titre "Gratin dauphinois traditionnel"

**Output**: [Voir format ci-dessus]

### Exemple 2: Tarte aux pommes

**Input**: Titre "Tarte aux pommes maison"

**Output**:
```
🥧 **TARTE AUX POMMES MAISON**

👥 Nombre de personnes: 6
⏱️ Temps de préparation: 45 minutes

📝 **INGRÉDIENTS:**
🌾 250g de farine
🧈 125g de beurre froid
💧 50ml d'eau froide
🍎 5 pommes Golden
🍯 50g de sucre
🥚 1 jaune d'œuf
🧂 1 pincée de sel

[...suite de la recette...]
```

## Configuration dans le code

### Appel API OpenAI avec image

```php
$messages = [
    [
        'role' => 'system',
        'content' => 'Tu es un chef cuisinier expert qui crée des recettes détaillées et appétissantes.'
    ],
    [
        'role' => 'user',
        'content' => [
            [
                'type' => 'text',
                'text' => $prompt_recette
            ],
            [
                'type' => 'image_url',
                'image_url' => [
                    'url' => $image_url
                ]
            ]
        ]
    ]
];

$response = openai_api_call([
    'model' => 'gpt-4o',
    'messages' => $messages,
    'max_tokens' => 2000,
    'temperature' => 0.7
]);
```

### Appel API OpenAI sans image

```php
$messages = [
    [
        'role' => 'system',
        'content' => 'Tu es un chef cuisinier expert qui crée des recettes détaillées et appétissantes.'
    ],
    [
        'role' => 'user',
        'content' => $prompt_recette
    ]
];

$response = openai_api_call([
    'model' => 'gpt-4o',
    'messages' => $messages,
    'max_tokens' => 2000,
    'temperature' => 0.7
]);
```

## Variantes du prompt

### Pour recettes végétariennes

Ajouter: "La recette doit être 100% végétarienne, sans viande ni poisson."

### Pour recettes rapides

Ajouter: "Le temps de préparation total ne doit pas dépasser 20 minutes."

### Pour recettes healthy

Ajouter: "Privilégie des ingrédients sains et équilibrés, évite les matières grasses excessives."

### Pour recettes économiques

Ajouter: "Utilise des ingrédients économiques et facilement trouvables."

## Bonnes pratiques

### ✅ À FAIRE

- Toujours inclure les émojis demandés
- Numéroter toutes les étapes (1️⃣, 2️⃣, etc.)
- Détailler précisément les quantités
- Expliquer chaque étape en détail
- Fournir des astuces pratiques
- Proposer des alternatives d'ingrédients

### ❌ À ÉVITER

- Ne JAMAIS mentionner "comme sur la photo"
- Ne JAMAIS dire "visible sur l'image"
- Ne pas utiliser de mesures imprécises ("un peu", "quelques")
- Ne pas omettre d'ingrédients visibles sur l'image
- Ne pas créer d'étapes floues ou incomplètes

## Qualité de la recette

Une bonne recette générée doit:

1. ✅ Être **complète** et réalisable
2. ✅ Avoir des **quantités précises**
3. ✅ Contenir des **étapes détaillées**
4. ✅ Inclure des **astuces utiles**
5. ✅ Être **bien formatée** avec émojis
6. ✅ Respecter les **ingrédients de l'image** (si fournie)
7. ✅ Être **cohérente** du début à la fin
8. ✅ Avoir un **temps de cuisson réaliste**

## Paramètres OpenAI recommandés

```json
{
  "model": "gpt-4o",
  "max_tokens": 2000,
  "temperature": 0.7,
  "top_p": 1,
  "frequency_penalty": 0,
  "presence_penalty": 0
}
```

- **model**: `gpt-4o` pour l'analyse d'images
- **max_tokens**: 2000 pour des recettes complètes
- **temperature**: 0.7 pour un bon équilibre créativité/cohérence

## Coût estimé

- **GPT-4o**: ~$0.02 par recette (avec image)
- **GPT-4o**: ~$0.01 par recette (sans image)

## Support et optimisation

Pour améliorer les résultats:

1. **Tester différentes températures** (0.5 à 0.9)
2. **Ajuster max_tokens** selon la longueur désirée
3. **Affiner le prompt système** pour le style souhaité
4. **Ajouter des exemples** dans le prompt (few-shot learning)
5. **Filtrer les réponses** pour vérifier la qualité

---

**Note**: Ce format est optimisé pour WordPress et peut être directement inséré dans un article sans traitement supplémentaire.
