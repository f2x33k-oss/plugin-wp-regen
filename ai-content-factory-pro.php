<?php
/**
 * Plugin Name: AI Content Factory Pro
 * Plugin URI: https://example.com/ai-content-factory-pro
 * Description: Génération automatique de contenu et d'images via OpenAI et Midjourney avec système de file d'attente avancé.
 * Version: 1.0.0
 * Author: AI Content Factory Team
 * Author URI: https://example.com
 * Text Domain: ai-content-factory-pro
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Définir les constantes du plugin
define('AICFP_VERSION', '1.0.0');
define('AICFP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AICFP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AICFP_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Classe principale du plugin
 */
class AI_Content_Factory_Pro {
    
    /**
     * Instance unique du plugin (Singleton)
     */
    private static $instance = null;
    
    /**
     * Obtenir l'instance unique du plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructeur privé pour le pattern Singleton
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Charger les dépendances du plugin
     */
    private function load_dependencies() {
        // Classes principales
        require_once AICFP_PLUGIN_DIR . 'includes/class-database.php';
        require_once AICFP_PLUGIN_DIR . 'includes/class-queue-manager.php';
        require_once AICFP_PLUGIN_DIR . 'includes/class-api-handler.php';
        require_once AICFP_PLUGIN_DIR . 'includes/class-image-api-manager.php';
        require_once AICFP_PLUGIN_DIR . 'includes/class-email-handler.php';
        require_once AICFP_PLUGIN_DIR . 'includes/class-file-handler.php';
        
        // Pages d'administration
        require_once AICFP_PLUGIN_DIR . 'admin/class-admin-menu.php';
        require_once AICFP_PLUGIN_DIR . 'admin/class-settings-page.php';
        require_once AICFP_PLUGIN_DIR . 'admin/class-albums-recettes-page.php';
        require_once AICFP_PLUGIN_DIR . 'admin/class-albums-idees-page.php';
        require_once AICFP_PLUGIN_DIR . 'admin/class-videos-page.php';
        require_once AICFP_PLUGIN_DIR . 'admin/class-instances-page.php';
        require_once AICFP_PLUGIN_DIR . 'admin/class-meta-box.php';
        
        // AJAX Handlers
        require_once AICFP_PLUGIN_DIR . 'includes/class-ajax-handler.php';
    }
    
    /**
     * Initialiser les hooks WordPress
     */
    private function init_hooks() {
        // Activation et désactivation du plugin
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Charger les traductions
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Initialiser les composants du plugin
        add_action('plugins_loaded', array($this, 'init_components'));
        
        // Enregistrer les scripts et styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Tâche cron pour traiter la file d'attente
        add_action('aicfp_process_queue', array('AICFP_Queue_Manager', 'process_queue_cron'));
        
        // Ajouter le lien de paramètres dans la liste des plugins
        add_filter('plugin_action_links_' . AICFP_PLUGIN_BASENAME, array($this, 'add_action_links'));
    }
    
    /**
     * Activation du plugin
     */
    public function activate() {
        // Créer les tables de base de données
        AICFP_Database::create_tables();
        
        // Planifier la tâche cron
        if (!wp_next_scheduled('aicfp_process_queue')) {
            wp_schedule_event(time(), 'every_minute', 'aicfp_process_queue');
        }
        
        // Créer le dossier de stockage temporaire
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/aicfp-temp/';
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
        
        // Flush des règles de réécriture
        flush_rewrite_rules();
    }
    
    /**
     * Désactivation du plugin
     */
    public function deactivate() {
        // Supprimer la tâche cron
        $timestamp = wp_next_scheduled('aicfp_process_queue');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'aicfp_process_queue');
        }
        
        // Flush des règles de réécriture
        flush_rewrite_rules();
    }
    
    /**
     * Charger les traductions
     */
    public function load_textdomain() {
        load_plugin_textdomain('ai-content-factory-pro', false, dirname(AICFP_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Initialiser les composants du plugin
     */
    public function init_components() {
        // Initialiser le menu admin
        new AICFP_Admin_Menu();
        
        // Initialiser les handlers AJAX
        new AICFP_Ajax_Handler();
        
        // Initialiser la Meta Box
        new AICFP_Meta_Box();
    }
    
    /**
     * Enregistrer les scripts et styles pour l'admin
     */
    public function enqueue_admin_assets($hook) {
        // Charger uniquement sur les pages du plugin
        if (strpos($hook, 'aicfp') === false && $hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }
        
        // Styles principaux
        wp_enqueue_style(
            'aicfp-admin-style',
            AICFP_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            AICFP_VERSION
        );
        
        wp_enqueue_style(
            'aicfp-modern-ui',
            AICFP_PLUGIN_URL . 'assets/css/modern-ui.css',
            array(),
            AICFP_VERSION
        );
        
        // Scripts spécifiques par page
        if (strpos($hook, 'aicfp-albums-recettes') !== false) {
            wp_enqueue_script(
                'aicfp-albums-recettes',
                AICFP_PLUGIN_URL . 'assets/js/albums-recettes.js',
                array('jquery'),
                AICFP_VERSION,
                true
            );
        }
        
        if (strpos($hook, 'aicfp-albums-idees') !== false) {
            wp_enqueue_script(
                'aicfp-albums-idees',
                AICFP_PLUGIN_URL . 'assets/js/albums-idees.js',
                array('jquery'),
                AICFP_VERSION,
                true
            );
        }
        
        if (strpos($hook, 'aicfp-instances') !== false) {
            wp_enqueue_script(
                'aicfp-instances',
                AICFP_PLUGIN_URL . 'assets/js/instances.js',
                array('jquery'),
                AICFP_VERSION,
                true
            );
        }
        
        // Localiser les scripts pour AJAX
        $scripts = array('aicfp-albums-recettes', 'aicfp-albums-idees', 'aicfp-instances');
        foreach ($scripts as $script) {
            if (wp_script_is($script, 'enqueued')) {
                wp_localize_script($script, 'aicfp_ajax', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('aicfp_nonce'),
                    'strings' => array(
                        'confirm_cancel' => __('Êtes-vous sûr de vouloir annuler cette tâche ?', 'ai-content-factory-pro'),
                        'confirm_delete' => __('Êtes-vous sûr de vouloir supprimer cette tâche ?', 'ai-content-factory-pro'),
                        'error_occurred' => __('Une erreur est survenue. Veuillez réessayer.', 'ai-content-factory-pro'),
                    )
                ));
            }
        }
    }
    
    /**
     * Ajouter des liens d'action dans la liste des plugins
     */
    public function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=aicfp-settings') . '">' . __('Réglages', 'ai-content-factory-pro') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}

/**
 * Ajouter un intervalle de cron personnalisé
 */
add_filter('cron_schedules', function($schedules) {
    $schedules['every_minute'] = array(
        'interval' => 60,
        'display' => __('Chaque minute', 'ai-content-factory-pro')
    );
    return $schedules;
});

/**
 * Initialiser le plugin
 */
function aicfp_init() {
    return AI_Content_Factory_Pro::get_instance();
}

// Lancer le plugin
aicfp_init();
