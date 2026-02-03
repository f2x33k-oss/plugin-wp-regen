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
     * Générer du texte via ChatGPT, Gemini ou Claude
     */
    public static function generate_text($prompt, $image_url = null) {
        $text_engine = get_option('aicfp_text_engine', 'chatgpt');
        
        if ($text_engine === 'gemini') {
            return self::generate_text_with_gemini($prompt, $image_url);
        }
        
        if ($text_engine === 'claude') {
            return self::generate_text_with_claude($prompt, $image_url);
        }
        
        // ChatGPT par défaut
        $api_key = get_option('aicfp_openai_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Clé API OpenAI non configurée.', 'ai-content-factory-pro'));
        }
        
        // Prompt système pour les recettes
        $system_prompt = "Tu es un chef cuisinier expert qui crée des recettes détaillées et appétissantes. Tu respectes toujours le format demandé avec précision.";
        
        // Prompt utilisateur avec instructions détaillées
        $user_prompt = "Ecris-moi une recette à partir de ce titre : \"$prompt\" en la présentant de cette façon : un titre court et explicite, le nombre de personne pour la recette, le temps de préparation puis les ingrédients (Utilise des émoticones devant chaque ingrédient) avec le grammage, les étapes de préparation très détaillées avec des émoticones, (Numérote chaque étape (1️⃣, 2️⃣, 3️⃣...), commence chaque étape par un emoji correspondant à l'action ou l'ingrédient), une astuce pour faciliter la recette, un ingrédient à échanger, une astuce de cuisson. Ne mentionne jamais \"Comme sur la photo\" ou \"visible sur l'image\" dans la recette.";
        
        // Si une image est fournie, utiliser l'API vision de GPT-4o
        $messages = array(
            array(
                'role' => 'system',
                'content' => $system_prompt
            )
        );
        
        if ($image_url) {
            $messages[] = array(
                'role' => 'user',
                'content' => array(
                    array(
                        'type' => 'text',
                        'text' => $user_prompt
                    ),
                    array(
                        'type' => 'image_url',
                        'image_url' => array(
                            'url' => $image_url
                        )
                    )
                )
            );
        } else {
            $messages[] = array(
                'role' => 'user',
                'content' => $user_prompt
            );
        }
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'timeout' => 90,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => wp_json_encode(array(
                'model' => 'gpt-4o',
                'messages' => $messages,
                'max_tokens' => 2000,
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
     * Générer du texte via Google Gemini
     */
    private static function generate_text_with_gemini($prompt, $image_url = null) {
        $api_key = get_option('aicfp_gemini_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Clé API Gemini non configurée.', 'ai-content-factory-pro'));
        }
        
        // Prompt système pour Gemini
        $full_prompt = "Tu es un chef cuisinier expert qui crée des recettes détaillées.\n\n";
        $full_prompt .= "Ecris une recette à partir de : \"$prompt\" avec ce format:\n";
        $full_prompt .= "- Titre court\n- Personnes et temps\n- Ingrédients avec émojis\n";
        $full_prompt .= "- Étapes numérotées 1️⃣, 2️⃣ avec émojis\n- Astuces\n";
        
        $response = wp_remote_post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $api_key, array(
            'timeout' => 90,
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => wp_json_encode(array(
                'contents' => array(
                    array(
                        'parts' => array(
                            array('text' => $full_prompt)
                        )
                    )
                )
            ))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($data['candidates'][0]['content']['parts'][0]['text']);
        }
        
        return new WP_Error('gemini_error', __('Erreur avec Gemini. Vérifiez votre clé API Gemini dans Réglages.', 'ai-content-factory-pro'));
    }
    
    /**
     * Générer du texte via Claude (Anthropic)
     */
    private static function generate_text_with_claude($prompt, $image_url = null) {
        $api_key = get_option('aicfp_claude_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Clé API Claude non configurée.', 'ai-content-factory-pro'));
        }
        
        $full_prompt = "Tu es un chef cuisinier expert. Écris une recette à partir de : \"$prompt\".\n\n";
        $full_prompt .= "Format: Titre, personnes, temps, ingrédients avec émojis, étapes numérotées 1️⃣, 2️⃣ avec émojis, astuces.";
        
        $response = wp_remote_post('https://api.anthropic.com/v1/messages', array(
            'timeout' => 90,
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-api-key' => $api_key,
                'anthropic-version' => '2023-06-01'
            ),
            'body' => wp_json_encode(array(
                'model' => 'claude-3-sonnet-20240229',
                'max_tokens' => 2000,
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => $full_prompt
                    )
                )
            ))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($data['content'][0]['text'])) {
            return trim($data['content'][0]['text']);
        }
        
        return new WP_Error('claude_error', __('Erreur avec Claude. Vérifiez votre clé API Claude dans Réglages.', 'ai-content-factory-pro'));
    }
    
    /**
     * Générer une image via Midjourney (RapidAPI)
     */
    public static function generate_image($prompt, $reference_images = null) {
        $api_key = get_option('aicfp_rapidapi_key');
        
        // Clé API Midjourney pré-configurée et fonctionnelle
        if (empty($api_key)) {
            $api_key = '60bcbb5fe7mshd88f23d138be003p1be084jsnc1e30b0bb6d3';
        }
        
        // Logger pour debug
        if (get_option('aicfp_verbose_logging', true)) {
            error_log('AICFP: Génération Midjourney - Prompt: ' . substr($prompt, 0, 100));
        }
        
        // Construire le prompt avec les références d'images si disponibles
        $full_prompt = $prompt;
        if (!empty($reference_images) && is_array($reference_images)) {
            // Ajouter les URLs des images de référence au prompt avec --sref
            $ref_urls = implode(' ', array_slice($reference_images, 0, 5));
            $full_prompt .= ' --sref ' . $ref_urls;
        }
        
        // API Midjourney Best Experience (endpoint mis à jour)
        $api_url = 'https://midjourney-best-experience.p.rapidapi.com/api/imagine';
        
        // Envoyer la requête POST pour créer la tâche
        $response = wp_remote_post($api_url, array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => 'midjourney-best-experience.p.rapidapi.com',
                'x-rapidapi-key' => $api_key
            ),
            'body' => wp_json_encode(array(
                'prompt' => $full_prompt,
                'aspect_ratio' => '16:9',
                'process_mode' => 'relax'
            ))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Logger la réponse pour débogage
        error_log('Midjourney API Response: ' . print_r($data, true));
        
        if ($response_code !== 200 && $response_code !== 201) {
            $error_message = isset($data['message']) ? $data['message'] : __('Erreur lors de la création de la tâche Midjourney.', 'ai-content-factory-pro');
            return new WP_Error('midjourney_error', $error_message . ' (Code: ' . $response_code . ')');
        }
        
        // Vérifier si nous avons un task_id ou message_id
        $task_id = null;
        if (isset($data['task_id'])) {
            $task_id = $data['task_id'];
        } elseif (isset($data['messageId'])) {
            $task_id = $data['messageId'];
        } elseif (isset($data['id'])) {
            $task_id = $data['id'];
        }
        
        if (!$task_id) {
            return new WP_Error('midjourney_error', __('Impossible de créer la tâche Midjourney. Aucun ID retourné.', 'ai-content-factory-pro'));
        }
        
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
        $max_attempts = 40; // 40 tentatives * 15 secondes = 10 minutes max
        $attempt = 0;
        
        while ($attempt < $max_attempts) {
            sleep(15); // Attendre 15 secondes entre chaque tentative
            
            // Vérifier le statut de la tâche
            $response = wp_remote_get(
                'https://midjourney-best-experience.p.rapidapi.com/mj/message/' . $task_id,
                array(
                    'timeout' => 30,
                    'headers' => array(
                        'x-rapidapi-host' => 'midjourney-best-experience.p.rapidapi.com',
                        'x-rapidapi-key' => $api_key
                    )
                )
            );
            
            if (is_wp_error($response)) {
                error_log('Midjourney polling error: ' . $response->get_error_message());
                $attempt++;
                continue;
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            // Logger la réponse pour débogage
            error_log('Midjourney polling response (attempt ' . ($attempt + 1) . '): ' . print_r($data, true));
            
            // Vérifier différents formats de réponse possibles
            $status = null;
            $image_url = null;
            
            if (isset($data['status'])) {
                $status = $data['status'];
            } elseif (isset($data['progress'])) {
                $status = $data['progress'] >= 100 ? 'completed' : 'processing';
            }
            
            // Image URL peut être dans différents champs
            if (isset($data['image_url'])) {
                $image_url = $data['image_url'];
            } elseif (isset($data['uri'])) {
                $image_url = $data['uri'];
            } elseif (isset($data['url'])) {
                $image_url = $data['url'];
            } elseif (isset($data['imageUrl'])) {
                $image_url = $data['imageUrl'];
            }
            
            // Vérifier si la tâche est terminée
            if ($status === 'completed' || $status === 'done' || $status === 'success') {
                if ($image_url) {
                    return $image_url;
                }
            }
            
            // Vérifier si la tâche a échoué
            if ($status === 'failed' || $status === 'error') {
                $error_msg = isset($data['error']) ? $data['error'] : __('La génération de l\'image a échoué.', 'ai-content-factory-pro');
                return new WP_Error('midjourney_failed', $error_msg);
            }
            
            $attempt++;
        }
        
        return new WP_Error('midjourney_timeout', __('Timeout: L\'image n\'a pas été générée à temps après 10 minutes.', 'ai-content-factory-pro'));
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
