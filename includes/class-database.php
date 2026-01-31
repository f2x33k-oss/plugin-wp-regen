<?php
/**
 * Gestion de la base de données
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de gestion de la base de données
 */
class AICFP_Database {
    
    /**
     * Nom de la table de file d'attente
     */
    const TABLE_QUEUE = 'ai_queue';
    
    /**
     * Créer les tables de base de données
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . self::TABLE_QUEUE;
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            generate_text tinyint(1) NOT NULL DEFAULT 1,
            email varchar(255) NOT NULL,
            zip_file_path varchar(500) DEFAULT NULL,
            reference_images text DEFAULT NULL,
            status varchar(50) NOT NULL DEFAULT 'pending',
            progress int(11) NOT NULL DEFAULT 0,
            total_items int(11) NOT NULL DEFAULT 0,
            current_item int(11) NOT NULL DEFAULT 0,
            post_id bigint(20) UNSIGNED DEFAULT NULL,
            generated_images text DEFAULT NULL,
            generated_content longtext DEFAULT NULL,
            prompts_log longtext DEFAULT NULL,
            error_log longtext DEFAULT NULL,
            cost_estimate decimal(10,2) DEFAULT 0.00,
            time_estimate int(11) DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            started_at datetime DEFAULT NULL,
            completed_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Obtenir la table de file d'attente
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_QUEUE;
    }
    
    /**
     * Insérer une nouvelle tâche dans la file d'attente
     */
    public static function insert_task($data) {
        global $wpdb;
        
        $defaults = array(
            'title' => '',
            'generate_text' => 1,
            'email' => '',
            'zip_file_path' => null,
            'reference_images' => null,
            'status' => 'pending',
            'progress' => 0,
            'total_items' => 0,
            'current_item' => 0,
            'post_id' => null,
            'generated_images' => null,
            'generated_content' => null,
            'prompts_log' => null,
            'error_log' => null,
            'cost_estimate' => 0.00,
            'time_estimate' => 0,
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert(
            self::get_table_name(),
            $data,
            array(
                '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d',
                '%d', '%s', '%s', '%s', '%s', '%f', '%d'
            )
        );
        
        if ($result === false) {
            return new WP_Error('db_insert_error', __('Erreur lors de l\'insertion de la tâche.', 'ai-content-factory-pro'));
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Mettre à jour une tâche
     */
    public static function update_task($task_id, $data) {
        global $wpdb;
        
        $result = $wpdb->update(
            self::get_table_name(),
            $data,
            array('id' => $task_id),
            null,
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Obtenir une tâche par ID
     */
    public static function get_task($task_id) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM " . self::get_table_name() . " WHERE id = %d",
            $task_id
        );
        
        return $wpdb->get_row($sql);
    }
    
    /**
     * Obtenir toutes les tâches
     */
    public static function get_all_tasks($limit = 50, $offset = 0, $status = null) {
        global $wpdb;
        
        $sql = "SELECT * FROM " . self::get_table_name();
        
        if ($status) {
            $sql .= $wpdb->prepare(" WHERE status = %s", $status);
        }
        
        $sql .= " ORDER BY created_at DESC";
        $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $limit, $offset);
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Obtenir la prochaine tâche en attente
     */
    public static function get_next_pending_task() {
        global $wpdb;
        
        $sql = "SELECT * FROM " . self::get_table_name() . " 
                WHERE status = 'pending' 
                ORDER BY created_at ASC 
                LIMIT 1";
        
        return $wpdb->get_row($sql);
    }
    
    /**
     * Obtenir les tâches en cours
     */
    public static function get_running_tasks() {
        global $wpdb;
        
        $sql = "SELECT * FROM " . self::get_table_name() . " 
                WHERE status = 'processing' 
                ORDER BY created_at ASC";
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Compter les tâches par statut
     */
    public static function count_tasks_by_status($status = null) {
        global $wpdb;
        
        if ($status) {
            $sql = $wpdb->prepare(
                "SELECT COUNT(*) FROM " . self::get_table_name() . " WHERE status = %s",
                $status
            );
        } else {
            $sql = "SELECT COUNT(*) FROM " . self::get_table_name();
        }
        
        return (int) $wpdb->get_var($sql);
    }
    
    /**
     * Supprimer une tâche
     */
    public static function delete_task($task_id) {
        global $wpdb;
        
        return $wpdb->delete(
            self::get_table_name(),
            array('id' => $task_id),
            array('%d')
        );
    }
    
    /**
     * Nettoyer les anciennes tâches terminées (plus de 30 jours)
     */
    public static function cleanup_old_tasks() {
        global $wpdb;
        
        $sql = "DELETE FROM " . self::get_table_name() . " 
                WHERE status IN ('completed', 'failed', 'cancelled') 
                AND completed_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        return $wpdb->query($sql);
    }
}
