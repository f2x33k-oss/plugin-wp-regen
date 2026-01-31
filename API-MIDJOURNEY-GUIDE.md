# Guide d'utilisation de l'API Midjourney (RapidAPI)

## Configuration de l'API

### Informations de base
- **Service**: Midjourney Best Experience
- **Host**: `midjourney-best-experience.p.rapidapi.com`
- **URL Base**: `https://midjourney-best-experience.p.rapidapi.com`
- **Clé API**: `60bcbb5fe7mshd88f23d138be003p1be084jsnc1e30b0bb6d3`

## Endpoints disponibles

### 1. Créer une nouvelle image (Imagine)

**Endpoint**: `POST /mj/imagine`

**Headers**:
```
Content-Type: application/json
x-rapidapi-host: midjourney-best-experience.p.rapidapi.com
x-rapidapi-key: 60bcbb5fe7mshd88f23d138be003p1be084jsnc1e30b0bb6d3
```

**Body (JSON)**:
```json
{
  "prompt": "une recette de gratin",
  "aspect_ratio": "16:9",
  "process_mode": "relax"
}
```

**Paramètres**:
- `prompt` (string, requis): Description de l'image à générer
- `aspect_ratio` (string, optionnel): Ratio de l'image (ex: "16:9", "1:1", "4:3")
- `process_mode` (string, optionnel): Mode de traitement ("relax", "fast", "turbo")
- `ref_urls` (array, optionnel): URLs d'images de référence pour --sref

**Réponse**:
```json
{
  "task_id": "abc123",
  "messageId": "abc123",
  "status": "pending"
}
```

### 2. Vérifier le statut d'une image

**Endpoint**: `GET /mj/message/{task_id}`

**Headers**:
```
x-rapidapi-host: midjourney-best-experience.p.rapidapi.com
x-rapidapi-key: 60bcbb5fe7mshd88f23d138be003p1be084jsnc1e30b0bb6d3
```

**Réponse (en cours)**:
```json
{
  "status": "processing",
  "progress": 50
}
```

**Réponse (terminée)**:
```json
{
  "status": "completed",
  "progress": 100,
  "image_url": "https://cdn.midjourney.com/...",
  "uri": "https://cdn.midjourney.com/..."
}
```

### 3. Créer des variations

**Endpoint**: `POST /mj/action-relax`

**Query Parameters**:
- `action`: Type de variation (ex: "variation1", "variation2", etc.)
- `image_id`: ID de l'image source
- `hook_url`: URL de webhook pour notification

**Exemple**:
```bash
curl --request POST \
  --url 'https://midjourney-best-experience.p.rapidapi.com/mj/action-relax?action=variation1&image_id=11209086137713295861&hook_url=https%3A%2F%2Fwww.google.com' \
  --header 'Content-Type: application/x-www-form-urlencoded' \
  --header 'x-rapidapi-host: midjourney-best-experience.p.rapidapi.com' \
  --header 'x-rapidapi-key: 60bcbb5fe7mshd88f23d138be003p1be084jsnc1e30b0bb6d3'
```

## Workflow d'utilisation dans le plugin

### Étape 1: Générer l'image
```php
$response = wp_remote_post('https://midjourney-best-experience.p.rapidapi.com/mj/imagine', [
    'headers' => [
        'Content-Type' => 'application/json',
        'x-rapidapi-host' => 'midjourney-best-experience.p.rapidapi.com',
        'x-rapidapi-key' => '60bcbb5fe7mshd88f23d138be003p1be084jsnc1e30b0bb6d3'
    ],
    'body' => json_encode([
        'prompt' => 'une belle recette de gratin dauphinois',
        'aspect_ratio' => '16:9',
        'process_mode' => 'relax'
    ])
]);

$data = json_decode(wp_remote_retrieve_body($response), true);
$task_id = $data['task_id'] ?? $data['messageId'];
```

### Étape 2: Polling (vérifier le statut)
```php
$max_attempts = 40;
$attempt = 0;

while ($attempt < $max_attempts) {
    sleep(15); // Attendre 15 secondes
    
    $response = wp_remote_get(
        'https://midjourney-best-experience.p.rapidapi.com/mj/message/' . $task_id,
        [
            'headers' => [
                'x-rapidapi-host' => 'midjourney-best-experience.p.rapidapi.com',
                'x-rapidapi-key' => '60bcbb5fe7mshd88f23d138be003p1be084jsnc1e30b0bb6d3'
            ]
        ]
    );
    
    $data = json_decode(wp_remote_retrieve_body($response), true);
    
    if ($data['status'] === 'completed') {
        $image_url = $data['image_url'] ?? $data['uri'];
        break;
    }
    
    $attempt++;
}
```

### Étape 3: Générer le texte avec GPT-4o Vision
```php
// Une fois l'image générée, analyser avec GPT-4o Vision
$content = AICFP_API_Handler::generate_text($prompt, $image_url);
```

## Utilisation des images de référence (--sref)

Pour utiliser des images de référence avec Midjourney, ajoutez-les directement dans le prompt:

```php
$prompt = "une recette de gratin --sref https://example.com/image1.jpg https://example.com/image2.jpg";
```

Ou en utilisant le paramètre `ref_urls`:

```php
[
    'prompt' => 'une recette de gratin',
    'aspect_ratio' => '16:9',
    'ref_urls' => [
        'https://example.com/image1.jpg',
        'https://example.com/image2.jpg'
    ]
]
```

## Modes de traitement

### Relax Mode (par défaut)
- Moins cher
- Plus lent (~2-5 minutes)
- Idéal pour les tâches batch

### Fast Mode
- Plus cher
- Plus rapide (~1-2 minutes)
- Priorité dans la file d'attente

### Turbo Mode
- Le plus cher
- Le plus rapide (~30-60 secondes)
- Priorité maximale

## Formats de réponse possibles

L'API peut retourner différents formats selon la version:

```json
// Format 1
{
  "task_id": "abc123",
  "status": "completed",
  "image_url": "https://..."
}

// Format 2
{
  "messageId": "abc123",
  "status": "done",
  "uri": "https://..."
}

// Format 3
{
  "id": "abc123",
  "progress": 100,
  "imageUrl": "https://..."
}
```

Le plugin gère tous ces formats automatiquement.

## Gestion des erreurs

### Erreurs courantes

**1. Quota dépassé**
```json
{
  "error": "Rate limit exceeded",
  "message": "You have exceeded your API quota"
}
```

**2. Prompt invalide**
```json
{
  "error": "Invalid prompt",
  "message": "The prompt contains banned words"
}
```

**3. Timeout**
```json
{
  "error": "Timeout",
  "message": "Image generation took too long"
}
```

### Stratégies de récupération

1. **Rate limiting**: Implémenter un délai entre les requêtes
2. **Retry logic**: Réessayer 2-3 fois en cas d'erreur réseau
3. **Fallback**: Passer à l'item suivant si échec persistant
4. **Logging**: Enregistrer toutes les erreurs pour analyse

## Coûts estimés

Basé sur l'abonnement RapidAPI "Midjourney Best Experience":

- **Relax Mode**: ~$0.05 par image
- **Fast Mode**: ~$0.10 par image
- **Turbo Mode**: ~$0.20 par image

Les coûts peuvent varier selon votre plan RapidAPI.

## Optimisations

### 1. Batch Processing
Traiter les images par lots avec des intervalles pour éviter le rate limiting.

### 2. Caching
Stocker les URLs d'images générées pour éviter les régénérations.

### 3. Retry avec backoff exponentiel
```php
$delays = [5, 10, 20, 40]; // secondes
foreach ($delays as $delay) {
    $result = try_generate_image();
    if (!is_error($result)) break;
    sleep($delay);
}
```

### 4. Monitoring
- Logger toutes les requêtes API
- Tracker le temps de génération
- Surveiller le taux d'échec

## Limites connues

1. **Rate limits**: Variable selon le plan RapidAPI
2. **Temps de génération**: 1-10 minutes selon le mode et la charge
3. **Taille des prompts**: Maximum ~1000 caractères
4. **Images de référence**: Maximum 5 URLs par requête
5. **Concurrence**: 1 tâche à la fois recommandé

## Support et documentation

- **RapidAPI Hub**: https://rapidapi.com/hub
- **Documentation Midjourney**: https://docs.midjourney.com/
- **Support**: Via le dashboard RapidAPI

## Notes importantes

⚠️ **Sécurité**: Ne jamais exposer la clé API côté client  
⚠️ **Quotas**: Surveiller l'utilisation pour éviter les surcoûts  
⚠️ **Conformité**: Respecter les conditions d'utilisation de Midjourney  
⚠️ **Contenu**: Éviter les prompts inappropriés ou interdits
