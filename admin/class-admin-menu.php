<?php
/**
 * Menu d'administration
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de gestion du menu admin
 */
class AICFP_Admin_Menu {
    
    /**
     * Constructeur
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_pages'));
    }
    
    /**
     * Ajouter les pages de menu
     */
    public function add_menu_pages() {
        // Menu principal avec icône personnalisée
        add_menu_page(
            __('AI Content Factory Pro', 'ai-content-factory-pro'),
            __('AI Content Factory', 'ai-content-factory-pro'),
            'manage_options',
            'aicfp-albums-recettes',
            array('AICFP_Albums_Recettes_Page', 'render'),
            'dashicons-superhero-alt',
            30
        );
        
        // Sous-menu: Albums Recettes (page par défaut)
        add_submenu_page(
            'aicfp-albums-recettes',
            __('Albums Recettes', 'ai-content-factory-pro'),
            '<span class="dashicons dashicons-food" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px;"></span>' . __('Albums Recettes', 'ai-content-factory-pro'),
            'manage_options',
            'aicfp-albums-recettes',
            array('AICFP_Albums_Recettes_Page', 'render')
        );
        
        // Sous-menu: Albums Idées
        add_submenu_page(
            'aicfp-albums-recettes',
            __('Albums Idées', 'ai-content-factory-pro'),
            '<span class="dashicons dashicons-format-gallery" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px;"></span>' . __('Albums Idées', 'ai-content-factory-pro'),
            'manage_options',
            'aicfp-albums-idees',
            array('AICFP_Albums_Idees_Page', 'render')
        );
        
        // Sous-menu: Vidéos
        add_submenu_page(
            'aicfp-albums-recettes',
            __('Génération de Vidéos IA', 'ai-content-factory-pro'),
            '<span class="dashicons dashicons-video-alt3" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px;"></span>' . __('Vidéos', 'ai-content-factory-pro'),
            'manage_options',
            'aicfp-videos',
            array('AICFP_Videos_Page', 'render')
        );
        
        // Sous-menu: Instances
        add_submenu_page(
            'aicfp-albums-recettes',
            __('File d\'attente', 'ai-content-factory-pro'),
            '<span class="dashicons dashicons-list-view" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px;"></span>' . __('Instances', 'ai-content-factory-pro'),
            'manage_options',
            'aicfp-instances',
            array('AICFP_Instances_Page', 'render')
        );
        
        // Sous-menu: Réglages
        add_submenu_page(
            'aicfp-albums-recettes',
            __('Réglages', 'ai-content-factory-pro'),
            '<span class="dashicons dashicons-admin-settings" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px;"></span>' . __('Réglages', 'ai-content-factory-pro'),
            'manage_options',
            'aicfp-settings',
            array('AICFP_Settings_Page', 'render')
        );
        
        // Sous-menu: Erreurs
        add_submenu_page(
            'aicfp-albums-recettes',
            __('Journal des Erreurs', 'ai-content-factory-pro'),
            '<span class="dashicons dashicons-warning" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px; color: #d63638;"></span>' . __('Erreurs', 'ai-content-factory-pro'),
            'manage_options',
            'aicfp-errors',
            array('AICFP_Errors_Page', 'render')
        );
        
        // Sous-menu: Debug (si activé)
        if (get_option('aicfp_debug_mode_enabled', false)) {
            add_submenu_page(
                'aicfp-albums-recettes',
                __('Mode Debug', 'ai-content-factory-pro'),
                '<span class="dashicons dashicons-admin-tools" style="font-size: 16px; width: 16px; height: 16px; margin-right: 5px; color: #ff6b6b;"></span>' . __('Debug', 'ai-content-factory-pro'),
                'manage_options',
                'aicfp-debug',
                array('AICFP_Debug_Page', 'render')
            );
        }
    }
}
