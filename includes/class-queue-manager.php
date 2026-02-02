<?php
/**
 * Gestionnaire de file d'attente
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de gestion de la file d'attente
 */
class AICFP_Queue_Manager {
    
    /**
     * Traiter la file d'attente (appelé par le cron)
     */
    public static function process_queue_cron() {
        // Vérifier s'il y a une tâche en cours de traitement
        $running_tasks = AICFP_Database::get_running_tasks();
        
        // Limiter à 1 tâche en cours à la fois pour éviter la surcharge
        if (count($running_tasks) > 0) {
            // Continuer le traitement des tâches en cours
            foreach ($running_tasks as $task) {
                self::continue_task($task);
            }
            return;
        }
        
        // Obtenir la prochaine tâche en attente
        $task = AICFP_Database::get_next_pending_task();
        
        if (!$task) {
            return; // Aucune tâche en attente
        }
        
        // Démarrer le traitement de la tâche
        self::start_task($task);
    }
    
    /**
     * Démarrer le traitement d'une tâche
     */
    public static function start_task($task) {
        // Mettre à jour le statut
        AICFP_Database::update_task($task->id, array(
            'status' => 'processing',
            'started_at' => current_time('mysql')
        ));
        
        // Extraire le nombre d'items à partir du titre
        $total_items = self::extract_item_count($task->title);
        
        AICFP_Database::update_task($task->id, array(
            'total_items' => $total_items,
            'current_item' => 0
        ));
        
        // Traiter les images de référence si un ZIP est fourni
        if ($task->zip_file_path) {
            $reference_images = AICFP_File_Handler::extract_zip($task->zip_file_path);
            if (is_wp_error($reference_images)) {
                self::log_error($task->id, $reference_images->get_error_message());
            } else {
                AICFP_Database::update_task($task->id, array(
                    'reference_images' => maybe_serialize($reference_images)
                ));
            }
        }
        
        // Envoyer l'email de démarrage
        AICFP_Email_Handler::send_start_email($task);
        
        // Continuer le traitement
        self::process_task($task->id);
    }
    
    /**
     * Continuer le traitement d'une tâche
     */
    public static function continue_task($task) {
        self::process_task($task->id);
    }
    
    /**
     * Traiter une tâche avec multi-threading
     */
    public static function process_task($task_id) {
        $task = AICFP_Database::get_task($task_id);
        
        if (!$task || $task->status !== 'processing') {
            return;
        }
        
        // Vérifier si la tâche est terminée
        if ($task->current_item >= $task->total_items) {
            self::complete_task($task);
            return;
        }
        
        // Multi-threading: Traiter plusieurs items en parallèle
        $parallel_limit = get_option('aicfp_parallel_generations', 3); // 3 par défaut
        $items_to_process = min($parallel_limit, $task->total_items - $task->current_item);
        
        if (get_option('aicfp_verbose_logging', true)) {
            error_log('AICFP: Traitement parallèle de ' . $items_to_process . ' items (limit: ' . $parallel_limit . ')');
        }
        
        // Traiter plusieurs items en parallèle
        for ($i = 0; $i < $items_to_process; $i++) {
            $item_number = $task->current_item + $i + 1;
            
            if ($item_number > $task->total_items) {
                break;
            }
            
            // Générer en parallèle (via wp_remote_post avec timeout court)
            self::process_single_item($task_id, $item_number);
        }
        
        // Mettre à jour le current_item
        $new_current = $task->current_item + $items_to_process;
        $progress = round(($new_current / $task->total_items) * 100);
        
        AICFP_Database::update_task($task_id, array(
            'current_item' => $new_current,
            'progress' => $progress
        ));
    }
    
    /**
     * Traiter un seul item
     */
    private static function process_single_item($task_id, $item_number) {
        $task = AICFP_Database::get_task($task_id);
        
        // Générer le prompt pour l'item
        $prompt = self::generate_prompt($task, $item_number);
        
        // ÉTAPE 1: Générer l'image via l'API sélectionnée EN PREMIER
        $reference_images = maybe_unserialize($task->reference_images);
        $image_api = get_post_meta($task->id, '_aicfp_image_api', true) ?: 'midjourney';
        $image_url = AICFP_Image_API_Manager::generate_image($prompt, $image_api, $reference_images);
        
        if (is_wp_error($image_url)) {
            self::log_error($task_id, sprintf(
                __('Erreur génération image item %d: %s', 'ai-content-factory-pro'),
                $item_number,
                $image_url->get_error_message()
            ));
            // Continuer avec l'item suivant même si l'image a échoué
            $image_url = null;
        }
        
        // ÉTAPE 2: Générer le texte via ChatGPT en analysant l'image générée
        $content = '';
        if ($task->generate_text) {
            // Passer l'URL de l'image générée à GPT-4o Vision pour analyse
            $content = AICFP_API_Handler::generate_text($prompt, $image_url);
            if (is_wp_error($content)) {
                self::log_error($task_id, sprintf(
                    __('Erreur génération texte item %d: %s', 'ai-content-factory-pro'),
                    $item_number,
                    $content->get_error_message()
                ));
                $content = '';
            }
        }
        
        // Sauvegarder les résultats
        if ($image_url) {
            $generated_images = maybe_unserialize($task->generated_images) ?: array();
            $generated_images[] = array(
                'item' => $item_number,
                'url' => $image_url,
                'prompt' => $prompt
            );
            
            AICFP_Database::update_task($task_id, array(
                'generated_images' => maybe_serialize($generated_images)
            ));
        }
        
        if ($content) {
            $generated_content_array = maybe_unserialize($task->generated_content) ?: array();
            $generated_content_array[] = array(
                'item' => $item_number,
                'content' => $content
            );
            
            AICFP_Database::update_task($task_id, array(
                'generated_content' => maybe_serialize($generated_content_array)
            ));
        }
        
        // Mettre à jour la progression
        $progress = round(($item_number / $task->total_items) * 100);
        AICFP_Database::update_task($task_id, array(
            'current_item' => $item_number,
            'progress' => $progress
        ));
        
        // Logger le prompt
        self::log_prompt($task_id, $item_number, $prompt);
    }
    
    /**
     * Terminer une tâche
     */
    public static function complete_task($task) {
        // Créer l'article WP si génération de texte activée
        $post_id = null;
        if ($task->generate_text) {
            $generated_content = maybe_unserialize($task->generated_content);
            $generated_images = maybe_unserialize($task->generated_images);
            
            // Générer une intro de 30 mots
            $intro = self::generate_intro($task->title);
            
            // Sauvegarder l'intro en métadonnée pour téléchargement
            update_post_meta($task->id, '_aicfp_intro', $intro);
            
            // Construire le contenu avec intro + recettes
            $post_content = self::build_post_content_with_intro($intro, $generated_content, $generated_images);
            
            // Déterminer le statut de publication (depuis les options de la tâche)
            $publish_article = get_post_meta($task->id, '_aicfp_publish_article', true);
            $post_status = $publish_article ? 'publish' : 'draft';
            
            $post_data = array(
                'post_title' => $task->title,
                'post_content' => $post_content,
                'post_status' => $post_status,
                'post_type' => 'post'
            );
            
            $post_id = wp_insert_post($post_data);
            
            if (!is_wp_error($post_id)) {
                // Associer les métadonnées TOUJOURS (même si pas d'images)
                update_post_meta($post_id, '_aicfp_task_id', $task->id);
                
                if (get_option('aicfp_verbose_logging', true)) {
                    error_log('AICFP: Métadonnée _aicfp_task_id sauvegardée pour post ' . $post_id . ' = ' . $task->id);
                }
                
                if (!empty($generated_images)) {
                    // Définir la première image comme image à la une
                    $first_image_url = $generated_images[0]['url'];
                    self::set_featured_image_from_url($post_id, $first_image_url);
                    
                    // Sauvegarder images et prompts
                    update_post_meta($post_id, '_aicfp_generated_images', $generated_images);
                    update_post_meta($post_id, '_aicfp_prompts_log', maybe_unserialize($task->prompts_log));
                }
            }
        }
        
        // Upload vers Google Drive si activé
        $google_drive_url = null;
        $google_doc_url = null;
        
        if (get_option('aicfp_use_google_drive', false) && !empty($generated_images)) {
            // Renommer les images intelligemment avec titres de recettes
            $renamed_images = self::prepare_images_with_titles($generated_images, $generated_content);
            
            // Upload vers Google Drive
            $drive_result = AICFP_Google_Services::upload_images_to_drive($task->title, $renamed_images);
            
            if (!is_wp_error($drive_result)) {
                $google_drive_url = $drive_result['folder_url'];
                update_post_meta($task->id, '_aicfp_google_drive_url', $google_drive_url);
                update_post_meta($task->id, '_aicfp_google_drive_files', $drive_result['files']);
            }
        }
        
        // Créer Google Doc si activé
        if (get_option('aicfp_create_google_docs', true) && $task->generate_text && !empty($generated_content)) {
            $doc_url = AICFP_Google_Services::create_google_doc($task->title, $generated_content);
            
            if (!is_wp_error($doc_url)) {
                $google_doc_url = $doc_url;
                update_post_meta($task->id, '_aicfp_google_doc_url', $google_doc_url);
            }
        }
        
        // Mettre à jour la tâche
        AICFP_Database::update_task($task->id, array(
            'status' => 'completed',
            'progress' => 100,
            'post_id' => $post_id,
            'completed_at' => current_time('mysql')
        ));
        
        // Envoyer l'email de notification
        AICFP_Email_Handler::send_completion_email($task);
    }
    
    /**
     * Préparer les images avec titres de recettes pour renommage
     */
    private static function prepare_images_with_titles($generated_images, $generated_content) {
        $renamed_images = array();
        
        foreach ($generated_images as $index => $image) {
            $recipe_title = '';
            
            // Essayer d'extraire le titre de la recette correspondante
            if (isset($generated_content[$index])) {
                $content = $generated_content[$index]['content'];
                $lines = explode("\n", $content);
                
                // Chercher le titre dans les premières lignes
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line) && !str_starts_with($line, '👥') && !str_starts_with($line, '⏱️')) {
                        $recipe_title = preg_replace('/^[^\w\s]+\s*/', '', $line);
                        $recipe_title = trim(str_replace(['**', '__', '🍽️'], '', $recipe_title));
                        if (!empty($recipe_title) && strlen($recipe_title) > 3) {
                            break;
                        }
                    }
                }
            }
            
            // Ajouter le titre de la recette à l'image
            $renamed_images[] = array_merge($image, array(
                'recipe_title' => $recipe_title
            ));
        }
        
        return $renamed_images;
    }
    
    /**
     * Mettre en pause une tâche
     */
    public static function pause_task($task_id) {
        return AICFP_Database::update_task($task_id, array(
            'status' => 'paused'
        ));
    }
    
    /**
     * Reprendre une tâche
     */
    public static function resume_task($task_id) {
        return AICFP_Database::update_task($task_id, array(
            'status' => 'pending'
        ));
    }
    
    /**
     * Annuler une tâche
     */
    public static function cancel_task($task_id) {
        return AICFP_Database::update_task($task_id, array(
            'status' => 'cancelled',
            'completed_at' => current_time('mysql')
        ));
    }
    
    /**
     * Extraire le nombre d'items du titre
     */
    private static function extract_item_count($title) {
        // Rechercher un nombre dans le titre (ex: "20 recettes")
        if (preg_match('/(\d+)/', $title, $matches)) {
            return max(1, intval($matches[1]));
        }
        return 10; // Par défaut
    }
    
    /**
     * Générer un prompt pour un item
     */
    private static function generate_prompt($task, $item_number) {
        $base_title = $task->title;
        
        // Exemple: "20 recettes de gratins" -> "Recette de gratin #1"
        $prompt = sprintf(
            __('Item %d basé sur: %s', 'ai-content-factory-pro'),
            $item_number,
            $base_title
        );
        
        return apply_filters('aicfp_generate_prompt', $prompt, $task, $item_number);
    }
    
    /**
     * Générer une intro de 30 mots via ChatGPT
     */
    private static function generate_intro($title) {
        $api_key = get_option('aicfp_openai_api_key');
        
        if (empty($api_key)) {
            return "Découvrez notre sélection exceptionnelle de recettes délicieuses et faciles à réaliser pour régaler toute la famille.";
        }
        
        $prompt = "Écris une introduction accrocheuse de EXACTEMENT 30 mots pour un article intitulé : \"$title\". L'intro doit donner envie de lire les recettes. Réponds UNIQUEMENT avec l'introduction, sans guillemets.";
        
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => wp_json_encode(array(
                'model' => 'gpt-4o',
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'max_tokens' => 100,
                'temperature' => 0.7
            ))
        ));
        
        if (is_wp_error($response)) {
            return "Découvrez notre sélection exceptionnelle de recettes délicieuses et faciles à réaliser pour régaler toute la famille.";
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return trim($data['choices'][0]['message']['content']);
        }
        
        return "Découvrez notre sélection exceptionnelle de recettes délicieuses et faciles à réaliser pour régaler toute la famille.";
    }
    
    /**
     * Construire le contenu de l'article avec intro
     */
    private static function build_post_content_with_intro($intro, $generated_content, $generated_images) {
        // Logging pour debug
        if (get_option('aicfp_verbose_logging', true)) {
            error_log('AICFP: Construction article - Intro: ' . strlen($intro) . ' chars');
            error_log('AICFP: Contenu array: ' . (is_array($generated_content) ? count($generated_content) . ' items' : 'NON ARRAY'));
            error_log('AICFP: Images array: ' . (is_array($generated_images) ? count($generated_images) . ' items' : 'NON ARRAY'));
        }
        
        // Intro de 30 mots
        $content = '<p class="intro-paragraph"><strong>' . esc_html($intro) . '</strong></p>';
        $content .= "\n\n";
        
        // Vérifier que nous avons bien des arrays
        if (!is_array($generated_content)) {
            $generated_content = array();
        }
        
        if (!is_array($generated_images)) {
            $generated_images = array();
        }
        
        // Logging du contenu
        if (get_option('aicfp_verbose_logging', true)) {
            error_log('AICFP: Nombre de recettes: ' . count($generated_content));
            error_log('AICFP: Nombre d\'images: ' . count($generated_images));
        }
        
        if (!empty($generated_content) && !empty($generated_images)) {
            foreach ($generated_content as $index => $item) {
                // Extraire le titre de la recette du contenu
                $recipe_text = $item['content'];
                $lines = explode("\n", $recipe_text);
                $recipe_title = '';
                
                // Chercher le titre dans les premières lignes (souvent avec emoji 🍽️ ou en gras)
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line) && !str_starts_with($line, '👥') && !str_starts_with($line, '⏱️')) {
                        // Nettoyer le titre des émoticones en début de ligne
                        $recipe_title = preg_replace('/^[^\w\s]+\s*/', '', $line);
                        $recipe_title = trim(str_replace(['**', '__'], '', $recipe_title));
                        if (!empty($recipe_title)) {
                            break;
                        }
                    }
                }
                
                if (empty($recipe_title)) {
                    $recipe_title = sprintf(__('Recette %d', 'ai-content-factory-pro'), $item['item']);
                }
                
                // Ajouter le titre (H2)
                $content .= '<h2>' . esc_html($recipe_title) . '</h2>';
                $content .= "\n";
                
                // Ajouter l'image correspondante
                if (isset($generated_images[$index])) {
                    $content .= '<figure class="wp-block-image size-large">';
                    $content .= '<img src="' . esc_url($generated_images[$index]['url']) . '" alt="' . esc_attr($recipe_title) . '" />';
                    $content .= '</figure>';
                    $content .= "\n";
                }
                
                // Nettoyer le texte de la recette (retirer markdown)
                $clean_text = self::clean_recipe_text($recipe_text);
                
                // Ajouter le texte de la recette
                $formatted_text = nl2br(esc_html($clean_text));
                $content .= '<div class="recipe-content">' . $formatted_text . '</div>';
                $content .= "\n";
                
                // Séparateur entre recettes
                if ($index < count($generated_content) - 1) {
                    $content .= '<hr class="wp-block-separator" style="margin: 30px 0;" />';
                    $content .= "\n\n";
                }
            }
        }
        
        // Logging du contenu final
        if (get_option('aicfp_verbose_logging', true)) {
            error_log('AICFP: Contenu final article: ' . strlen($content) . ' caractères');
            error_log('AICFP: Contenu début: ' . substr($content, 0, 200));
        }
        
        return $content;
    }
    
    /**
     * Nettoyer le texte de recette (retirer markdown)
     */
    private static function clean_recipe_text($text) {
        // Retirer les ####, ###, ##
        $text = preg_replace('/^####\s+/m', '', $text);
        $text = preg_replace('/^###\s+/m', '', $text);
        $text = preg_replace('/^##\s+/m', '', $text);
        $text = preg_replace('/\n####\s+/', "\n", $text);
        $text = preg_replace('/\n###\s+/', "\n", $text);
        
        // Retirer les - devant les émojis des ingrédients
        $text = preg_replace('/^-\s+([🥔🧅🧄🥕🍅🥒🥬🥦🌽🍄🥛🧈🧀🥚🥩🍗🥓🐟🦐🧂])/m', '$1', $text);
        
        // Mettre les étapes en gras (avec emoji)
        $text = preg_replace('/^(\d️⃣\s+[🔪🧈🔥❄️🥄🍳🔄⏲️🎨]\s+[^:]+:)/m', '<strong>$1</strong>', $text);
        
        // Identifier et mettre Ingrédients et Préparation en H3
        $text = preg_replace('/^(INGRÉDIENTS|Ingrédients|INGREDIENTS|Ingredients)\s*:?\s*$/mi', '<h3>Ingrédients</h3>', $text);
        $text = preg_replace('/^(PRÉPARATION|Préparation|PREPARATION|Preparation|ÉTAPES|Étapes|ETAPES|Etapes)\s*:?\s*$/mi', '<h3>Préparation</h3>', $text);
        
        // Retirer les ** restants
        $text = str_replace('**', '', $text);
        
        // Retirer les __
        $text = str_replace('__', '', $text);
        
        // Retirer les ``` (code blocks)
        $text = str_replace('```', '', $text);
        
        return $text;
    }
    
    /**
     * Définir une image comme image à la une depuis une URL
     */
    private static function set_featured_image_from_url($post_id, $image_url) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        // Télécharger l'image
        $tmp = download_url($image_url);
        
        if (is_wp_error($tmp)) {
            return false;
        }
        
        $file_array = array(
            'name' => basename($image_url) . '.jpg',
            'tmp_name' => $tmp
        );
        
        // Importer comme attachement
        $attachment_id = media_handle_sideload($file_array, $post_id);
        
        if (is_wp_error($attachment_id)) {
            @unlink($file_array['tmp_name']);
            return false;
        }
        
        // Définir comme image à la une
        set_post_thumbnail($post_id, $attachment_id);
        
        return $attachment_id;
    }
    
    /**
     * Construire le contenu de l'article (ancienne version)
     */
    private static function build_post_content($generated_content, $generated_images) {
        $content = '';
        
        if (is_array($generated_content)) {
            foreach ($generated_content as $index => $item) {
                $content .= '<h2>' . sprintf(__('Section %d', 'ai-content-factory-pro'), $item['item']) . '</h2>';
                $content .= '<p>' . $item['content'] . '</p>';
                
                // Ajouter l'image correspondante
                if (isset($generated_images[$index])) {
                    $content .= '<img src="' . esc_url($generated_images[$index]['url']) . '" alt="" />';
                }
                
                $content .= '<hr />';
            }
        }
        
        return $content;
    }
    
    /**
     * Logger une erreur
     */
    private static function log_error($task_id, $error_message) {
        $task = AICFP_Database::get_task($task_id);
        $error_log = maybe_unserialize($task->error_log) ?: array();
        
        $error_log[] = array(
            'time' => current_time('mysql'),
            'message' => $error_message
        );
        
        AICFP_Database::update_task($task_id, array(
            'error_log' => maybe_serialize($error_log)
        ));
    }
    
    /**
     * Logger un prompt
     */
    private static function log_prompt($task_id, $item_number, $prompt) {
        $task = AICFP_Database::get_task($task_id);
        $prompts_log = maybe_unserialize($task->prompts_log) ?: array();
        
        $prompts_log[] = array(
            'item' => $item_number,
            'prompt' => $prompt,
            'time' => current_time('mysql')
        );
        
        AICFP_Database::update_task($task_id, array(
            'prompts_log' => maybe_serialize($prompts_log)
        ));
    }
}
