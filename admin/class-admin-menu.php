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
        // Menu principal
        add_menu_page(
            __('AI Content Factory Pro', 'ai-content-factory-pro'),
            __('AI Content Factory', 'ai-content-factory-pro'),
            'manage_options',
            'aicfp-generate',
            array('AICFP_Generate_Page', 'render'),
            'dashicons-admin-generic',
            30
        );
        
        // Sous-menu: Générer (page par défaut)
        add_submenu_page(
            'aicfp-generate',
            __('Générer du contenu', 'ai-content-factory-pro'),
            __('Générer', 'ai-content-factory-pro'),
            'manage_options',
            'aicfp-generate',
            array('AICFP_Generate_Page', 'render')
        );
        
        // Sous-menu: Instances
        add_submenu_page(
            'aicfp-generate',
            __('File d\'attente', 'ai-content-factory-pro'),
            __('Instances', 'ai-content-factory-pro'),
            'manage_options',
            'aicfp-instances',
            array('AICFP_Instances_Page', 'render')
        );
        
        // Sous-menu: Réglages
        add_submenu_page(
            'aicfp-generate',
            __('Réglages', 'ai-content-factory-pro'),
            __('Réglages', 'ai-content-factory-pro'),
            'manage_options',
            'aicfp-settings',
            array('AICFP_Settings_Page', 'render')
        );
    }
}
