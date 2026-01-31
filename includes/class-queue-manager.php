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
     * Traiter une tâche
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
        
        // Traiter l'item suivant
        $item_number = $task->current_item + 1;
        
        // Générer le prompt pour l'item
        $prompt = self::generate_prompt($task, $item_number);
        
        // Si génération de texte activée, générer via ChatGPT
        $content = '';
        if ($task->generate_text) {
            $content = AICFP_API_Handler::generate_text($prompt);
            if (is_wp_error($content)) {
                self::log_error($task_id, sprintf(
                    __('Erreur génération texte item %d: %s', 'ai-content-factory-pro'),
                    $item_number,
                    $content->get_error_message()
                ));
                $content = '';
            }
        }
        
        // Générer l'image via Midjourney
        $reference_images = maybe_unserialize($task->reference_images);
        $image_url = AICFP_API_Handler::generate_image($prompt, $reference_images);
        
        if (is_wp_error($image_url)) {
            self::log_error($task_id, sprintf(
                __('Erreur génération image item %d: %s', 'ai-content-factory-pro'),
                $item_number,
                $image_url->get_error_message()
            ));
            // Continuer avec l'item suivant
        } else {
            // Sauvegarder l'image générée
            $generated_images = maybe_unserialize($task->generated_images) ?: array();
            $generated_images[] = array(
                'item' => $item_number,
                'url' => $image_url,
                'prompt' => $prompt
            );
            
            $generated_content_array = maybe_unserialize($task->generated_content) ?: array();
            if ($content) {
                $generated_content_array[] = array(
                    'item' => $item_number,
                    'content' => $content
                );
            }
            
            AICFP_Database::update_task($task_id, array(
                'generated_images' => maybe_serialize($generated_images),
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
            
            $post_content = self::build_post_content($generated_content, $generated_images);
            
            $post_data = array(
                'post_title' => $task->title,
                'post_content' => $post_content,
                'post_status' => 'draft',
                'post_type' => 'post'
            );
            
            $post_id = wp_insert_post($post_data);
            
            if (!is_wp_error($post_id)) {
                // Associer les métadonnées
                update_post_meta($post_id, '_aicfp_task_id', $task->id);
                update_post_meta($post_id, '_aicfp_generated_images', $generated_images);
                update_post_meta($post_id, '_aicfp_prompts_log', maybe_unserialize($task->prompts_log));
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
     * Construire le contenu de l'article
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
