<?php
/**
 * Gestionnaire des API (OpenAI et Midjourney)
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de gestion des API
 */
class AICFP_API_Handler {
    
    /**
     * Générer du texte via OpenAI ChatGPT
     */
    public static function generate_text($prompt) {
        $api_key = get_option('aicfp_openai_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Clé API OpenAI non configurée.', 'ai-content-factory-pro'));
        }
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'timeout' => 60,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => wp_json_encode(array(
                'model' => 'gpt-4o',
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => 'Tu es un assistant qui génère du contenu de qualité pour des articles de blog.'
                    ),
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'max_tokens' => 1000,
                'temperature' => 0.7
            ))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            return new WP_Error('openai_error', $data['error']['message']);
        }
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('openai_error', __('Réponse invalide de l\'API OpenAI.', 'ai-content-factory-pro'));
        }
        
        return $data['choices'][0]['message']['content'];
    }
    
    /**
     * Générer une image via Midjourney (RapidAPI)
     */
    public static function generate_image($prompt, $reference_images = null) {
        $api_key = get_option('aicfp_rapidapi_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Clé API RapidAPI non configurée.', 'ai-content-factory-pro'));
        }
        
        // Construire le payload
        $payload = array(
            'prompt' => $prompt,
            'aspect_ratio' => '16:9'
        );
        
        // Ajouter les URLs de référence si disponibles
        if (!empty($reference_images) && is_array($reference_images)) {
            $payload['ref_urls'] = array_slice($reference_images, 0, 5); // Max 5 images
        }
        
        // Envoyer la requête POST pour créer la tâche
        $response = wp_remote_post('https://midjourney-api-ai.p.rapidapi.com/imagine', array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-RapidAPI-Key' => $api_key,
                'X-RapidAPI-Host' => 'midjourney-api-ai.p.rapidapi.com'
            ),
            'body' => wp_json_encode($payload)
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['task_id'])) {
            return new WP_Error('midjourney_error', __('Impossible de créer la tâche Midjourney.', 'ai-content-factory-pro'));
        }
        
        $task_id = $data['task_id'];
        
        // Polling pour attendre la complétion
        $image_url = self::poll_midjourney_task($task_id, $api_key);
        
        if (is_wp_error($image_url)) {
            return $image_url;
        }
        
        // Télécharger l'image dans la médiathèque WordPress
        $attachment_id = self::download_image_to_media_library($image_url, $prompt);
        
        if (is_wp_error($attachment_id)) {
            // Retourner l'URL même si l'import échoue
            return $image_url;
        }
        
        return wp_get_attachment_url($attachment_id);
    }
    
    /**
     * Polling de la tâche Midjourney
     */
    private static function poll_midjourney_task($task_id, $api_key) {
        $max_attempts = 30; // 30 tentatives * 20 secondes = 10 minutes max
        $attempt = 0;
        
        while ($attempt < $max_attempts) {
            sleep(20); // Attendre 20 secondes entre chaque tentative
            
            $response = wp_remote_get(
                'https://midjourney-api-ai.p.rapidapi.com/status/' . $task_id,
                array(
                    'timeout' => 30,
                    'headers' => array(
                        'X-RapidAPI-Key' => $api_key,
                        'X-RapidAPI-Host' => 'midjourney-api-ai.p.rapidapi.com'
                    )
                )
            );
            
            if (is_wp_error($response)) {
                $attempt++;
                continue;
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (isset($data['status']) && $data['status'] === 'completed') {
                if (isset($data['image_url'])) {
                    return $data['image_url'];
                }
            }
            
            if (isset($data['status']) && $data['status'] === 'failed') {
                return new WP_Error('midjourney_failed', __('La génération de l\'image a échoué.', 'ai-content-factory-pro'));
            }
            
            $attempt++;
        }
        
        return new WP_Error('midjourney_timeout', __('Timeout: L\'image n\'a pas été générée à temps.', 'ai-content-factory-pro'));
    }
    
    /**
     * Télécharger une image dans la médiathèque WordPress
     */
    private static function download_image_to_media_library($image_url, $description = '') {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $tmp = download_url($image_url);
        
        if (is_wp_error($tmp)) {
            return $tmp;
        }
        
        $file_array = array(
            'name' => basename($image_url) . '.jpg',
            'tmp_name' => $tmp
        );
        
        // Si le fichier téléchargé n'a pas d'extension, en ajouter une
        if (!preg_match('/\.(jpg|jpeg|png|gif)$/i', $file_array['name'])) {
            $file_array['name'] = 'midjourney-' . time() . '.jpg';
        }
        
        $id = media_handle_sideload($file_array, 0, $description);
        
        if (is_wp_error($id)) {
            @unlink($file_array['tmp_name']);
            return $id;
        }
        
        return $id;
    }
    
    /**
     * Calculer le coût estimé
     */
    public static function calculate_cost($item_count, $generate_text) {
        $text_cost = $generate_text ? ($item_count * 0.02) : 0;
        $image_cost = $item_count * 0.05;
        
        return $text_cost + $image_cost;
    }
    
    /**
     * Calculer le temps estimé (en minutes)
     */
    public static function calculate_time($item_count, $generate_text) {
        // Temps pour génération texte: ~30 secondes par item
        // Temps pour génération image: ~2 minutes par item
        $text_time = $generate_text ? ($item_count * 0.5) : 0;
        $image_time = $item_count * 2;
        
        return ceil($text_time + $image_time);
    }
}
