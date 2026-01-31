<?php
/**
 * Gestionnaire AJAX
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de gestion des requêtes AJAX
 */
class AICFP_Ajax_Handler {
    
    /**
     * Constructeur
     */
    public function __construct() {
        // Actions AJAX pour les utilisateurs connectés
        add_action('wp_ajax_aicfp_submit_generation', array($this, 'submit_generation'));
        add_action('wp_ajax_aicfp_get_queue_status', array($this, 'get_queue_status'));
        add_action('wp_ajax_aicfp_pause_task', array($this, 'pause_task'));
        add_action('wp_ajax_aicfp_resume_task', array($this, 'resume_task'));
        add_action('wp_ajax_aicfp_cancel_task', array($this, 'cancel_task'));
        add_action('wp_ajax_aicfp_delete_task', array($this, 'delete_task'));
        add_action('wp_ajax_aicfp_calculate_estimate', array($this, 'calculate_estimate'));
    }
    
    /**
     * Vérifier le nonce et les permissions
     */
    private function verify_request() {
        if (!check_ajax_referer('aicfp_nonce', 'nonce', false)) {
            wp_send_json_error(array(
                'message' => __('Vérification de sécurité échouée.', 'ai-content-factory-pro')
            ));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('Vous n\'avez pas les permissions nécessaires.', 'ai-content-factory-pro')
            ));
        }
    }
    
    /**
     * Soumettre une nouvelle génération
     */
    public function submit_generation() {
        $this->verify_request();
        
        // Récupérer les données du formulaire
        $title = sanitize_text_field($_POST['title'] ?? '');
        $generate_text = isset($_POST['generate_text']) ? (bool) $_POST['generate_text'] : true;
        $email = sanitize_email($_POST['email'] ?? '');
        
        // Validation
        if (empty($title)) {
            wp_send_json_error(array(
                'message' => __('Le titre est requis.', 'ai-content-factory-pro')
            ));
        }
        
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array(
                'message' => __('Une adresse email valide est requise.', 'ai-content-factory-pro')
            ));
        }
        
        // Gérer l'upload du ZIP
        $zip_file_path = null;
        if (isset($_FILES['reference_zip']) && $_FILES['reference_zip']['size'] > 0) {
            $upload = $this->handle_zip_upload($_FILES['reference_zip']);
            
            if (is_wp_error($upload)) {
                wp_send_json_error(array(
                    'message' => $upload->get_error_message()
                ));
            }
            
            $zip_file_path = $upload;
        }
        
        // Calculer les estimations
        $item_count = $this->extract_item_count($title);
        $cost_estimate = AICFP_API_Handler::calculate_cost($item_count, $generate_text);
        $time_estimate = AICFP_API_Handler::calculate_time($item_count, $generate_text);
        
        // Créer la tâche
        $task_id = AICFP_Database::insert_task(array(
            'title' => $title,
            'generate_text' => $generate_text ? 1 : 0,
            'email' => $email,
            'zip_file_path' => $zip_file_path,
            'cost_estimate' => $cost_estimate,
            'time_estimate' => $time_estimate
        ));
        
        if (is_wp_error($task_id)) {
            wp_send_json_error(array(
                'message' => $task_id->get_error_message()
            ));
        }
        
        wp_send_json_success(array(
            'message' => __('Tâche ajoutée à la file d\'attente avec succès.', 'ai-content-factory-pro'),
            'task_id' => $task_id
        ));
    }
    
    /**
     * Obtenir le statut de la file d'attente
     */
    public function get_queue_status() {
        $this->verify_request();
        
        $tasks = AICFP_Database::get_all_tasks(50);
        
        $formatted_tasks = array();
        foreach ($tasks as $task) {
            $formatted_tasks[] = array(
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'progress' => $task->progress,
                'total_items' => $task->total_items,
                'current_item' => $task->current_item,
                'generate_text' => (bool) $task->generate_text,
                'email' => $task->email,
                'cost_estimate' => number_format($task->cost_estimate, 2),
                'time_estimate' => $task->time_estimate,
                'created_at' => $task->created_at,
                'started_at' => $task->started_at,
                'completed_at' => $task->completed_at,
                'post_id' => $task->post_id,
                'error_count' => $task->error_log ? count(maybe_unserialize($task->error_log)) : 0
            );
        }
        
        $stats = array(
            'pending' => AICFP_Database::count_tasks_by_status('pending'),
            'processing' => AICFP_Database::count_tasks_by_status('processing'),
            'completed' => AICFP_Database::count_tasks_by_status('completed'),
            'paused' => AICFP_Database::count_tasks_by_status('paused'),
            'cancelled' => AICFP_Database::count_tasks_by_status('cancelled'),
            'failed' => AICFP_Database::count_tasks_by_status('failed')
        );
        
        wp_send_json_success(array(
            'tasks' => $formatted_tasks,
            'stats' => $stats
        ));
    }
    
    /**
     * Mettre en pause une tâche
     */
    public function pause_task() {
        $this->verify_request();
        
        $task_id = intval($_POST['task_id'] ?? 0);
        
        if (!$task_id) {
            wp_send_json_error(array(
                'message' => __('ID de tâche invalide.', 'ai-content-factory-pro')
            ));
        }
        
        $result = AICFP_Queue_Manager::pause_task($task_id);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Tâche mise en pause.', 'ai-content-factory-pro')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Impossible de mettre en pause la tâche.', 'ai-content-factory-pro')
            ));
        }
    }
    
    /**
     * Reprendre une tâche
     */
    public function resume_task() {
        $this->verify_request();
        
        $task_id = intval($_POST['task_id'] ?? 0);
        
        if (!$task_id) {
            wp_send_json_error(array(
                'message' => __('ID de tâche invalide.', 'ai-content-factory-pro')
            ));
        }
        
        $result = AICFP_Queue_Manager::resume_task($task_id);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Tâche reprise.', 'ai-content-factory-pro')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Impossible de reprendre la tâche.', 'ai-content-factory-pro')
            ));
        }
    }
    
    /**
     * Annuler une tâche
     */
    public function cancel_task() {
        $this->verify_request();
        
        $task_id = intval($_POST['task_id'] ?? 0);
        
        if (!$task_id) {
            wp_send_json_error(array(
                'message' => __('ID de tâche invalide.', 'ai-content-factory-pro')
            ));
        }
        
        $result = AICFP_Queue_Manager::cancel_task($task_id);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Tâche annulée.', 'ai-content-factory-pro')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Impossible d\'annuler la tâche.', 'ai-content-factory-pro')
            ));
        }
    }
    
    /**
     * Supprimer une tâche
     */
    public function delete_task() {
        $this->verify_request();
        
        $task_id = intval($_POST['task_id'] ?? 0);
        
        if (!$task_id) {
            wp_send_json_error(array(
                'message' => __('ID de tâche invalide.', 'ai-content-factory-pro')
            ));
        }
        
        $result = AICFP_Database::delete_task($task_id);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Tâche supprimée.', 'ai-content-factory-pro')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Impossible de supprimer la tâche.', 'ai-content-factory-pro')
            ));
        }
    }
    
    /**
     * Calculer l'estimation de coût et temps
     */
    public function calculate_estimate() {
        $this->verify_request();
        
        $title = sanitize_text_field($_POST['title'] ?? '');
        $generate_text = isset($_POST['generate_text']) ? (bool) $_POST['generate_text'] : true;
        
        $item_count = $this->extract_item_count($title);
        $cost = AICFP_API_Handler::calculate_cost($item_count, $generate_text);
        $time = AICFP_API_Handler::calculate_time($item_count, $generate_text);
        
        wp_send_json_success(array(
            'cost' => number_format($cost, 2),
            'time' => $time,
            'item_count' => $item_count
        ));
    }
    
    /**
     * Gérer l'upload du ZIP
     */
    private function handle_zip_upload($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', __('Erreur lors de l\'upload du fichier.', 'ai-content-factory-pro'));
        }
        
        // Vérifier le type de fichier
        $file_type = wp_check_filetype($file['name']);
        if ($file_type['ext'] !== 'zip') {
            return new WP_Error('invalid_file', __('Le fichier doit être un ZIP.', 'ai-content-factory-pro'));
        }
        
        // Créer le dossier de destination
        $upload_dir = wp_upload_dir();
        $dest_dir = $upload_dir['basedir'] . '/aicfp-temp/';
        wp_mkdir_p($dest_dir);
        
        // Déplacer le fichier
        $dest_path = $dest_dir . uniqid() . '.zip';
        if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
            return new WP_Error('move_error', __('Impossible de déplacer le fichier uploadé.', 'ai-content-factory-pro'));
        }
        
        return $dest_path;
    }
    
    /**
     * Extraire le nombre d'items du titre
     */
    private function extract_item_count($title) {
        if (preg_match('/(\d+)/', $title, $matches)) {
            return max(1, intval($matches[1]));
        }
        return 10;
    }
}
