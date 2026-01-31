<?php
/**
 * Page de réglages
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de la page de réglages
 */
class AICFP_Settings_Page {
    
    /**
     * Render la page
     */
    public static function render() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'ai-content-factory-pro'));
        }
        
        // Enregistrer les paramètres
        if (isset($_POST['aicfp_save_settings'])) {
            check_admin_referer('aicfp_settings_nonce');
            self::save_settings();
        }
        
        ?>
        <div class="wrap aicfp-settings-page">
            <h1><?php echo esc_html__('Réglages - AI Content Factory Pro', 'ai-content-factory-pro'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('aicfp_settings_nonce'); ?>
                
                <!-- API Keys Section -->
                <div class="aicfp-card">
                    <h2><?php echo esc_html__('Clés API', 'ai-content-factory-pro'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aicfp_openai_api_key">
                                    <?php echo esc_html__('Clé API OpenAI', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="aicfp_openai_api_key" 
                                       name="aicfp_openai_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_openai_api_key', '')); ?>" 
                                       class="regular-text" 
                                       autocomplete="off">
                                <p class="description">
                                    <?php echo esc_html__('Votre clé API OpenAI pour la génération de texte (GPT-4o).', 'ai-content-factory-pro'); ?>
                                    <a href="https://platform.openai.com/api-keys" target="_blank"><?php echo esc_html__('Obtenir une clé', 'ai-content-factory-pro'); ?></a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_rapidapi_key">
                                    <?php echo esc_html__('Clé RapidAPI (Midjourney)', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="aicfp_rapidapi_key" 
                                       name="aicfp_rapidapi_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_rapidapi_key', '')); ?>" 
                                       class="regular-text" 
                                       autocomplete="off">
                                <p class="description">
                                    <?php echo esc_html__('Votre clé RapidAPI pour la génération d\'images via Midjourney.', 'ai-content-factory-pro'); ?>
                                    <a href="https://rapidapi.com/" target="_blank"><?php echo esc_html__('Obtenir une clé', 'ai-content-factory-pro'); ?></a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- SMTP Settings Section -->
                <div class="aicfp-card">
                    <h2><?php echo esc_html__('Paramètres SMTP', 'ai-content-factory-pro'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aicfp_smtp_enabled">
                                    <?php echo esc_html__('Activer SMTP', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           id="aicfp_smtp_enabled" 
                                           name="aicfp_smtp_enabled" 
                                           value="1" 
                                           <?php checked(get_option('aicfp_smtp_enabled', false), true); ?>>
                                    <?php echo esc_html__('Utiliser SMTP pour l\'envoi d\'emails', 'ai-content-factory-pro'); ?>
                                </label>
                            </td>
                        </tr>
                        
                        <tr class="aicfp-smtp-field">
                            <th scope="row">
                                <label for="aicfp_smtp_host">
                                    <?php echo esc_html__('Hôte SMTP', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_smtp_host" 
                                       name="aicfp_smtp_host" 
                                       value="<?php echo esc_attr(get_option('aicfp_smtp_host', '')); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    <?php echo esc_html__('Ex: smtp.gmail.com', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="aicfp-smtp-field">
                            <th scope="row">
                                <label for="aicfp_smtp_port">
                                    <?php echo esc_html__('Port SMTP', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="aicfp_smtp_port" 
                                       name="aicfp_smtp_port" 
                                       value="<?php echo esc_attr(get_option('aicfp_smtp_port', '587')); ?>" 
                                       class="small-text">
                                <p class="description">
                                    <?php echo esc_html__('Port commun: 587 (TLS) ou 465 (SSL)', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="aicfp-smtp-field">
                            <th scope="row">
                                <label for="aicfp_smtp_encryption">
                                    <?php echo esc_html__('Chiffrement', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <select id="aicfp_smtp_encryption" name="aicfp_smtp_encryption">
                                    <option value="tls" <?php selected(get_option('aicfp_smtp_encryption', 'tls'), 'tls'); ?>>TLS</option>
                                    <option value="ssl" <?php selected(get_option('aicfp_smtp_encryption', 'tls'), 'ssl'); ?>>SSL</option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr class="aicfp-smtp-field">
                            <th scope="row">
                                <label for="aicfp_smtp_username">
                                    <?php echo esc_html__('Nom d\'utilisateur', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_smtp_username" 
                                       name="aicfp_smtp_username" 
                                       value="<?php echo esc_attr(get_option('aicfp_smtp_username', '')); ?>" 
                                       class="regular-text" 
                                       autocomplete="off">
                            </td>
                        </tr>
                        
                        <tr class="aicfp-smtp-field">
                            <th scope="row">
                                <label for="aicfp_smtp_password">
                                    <?php echo esc_html__('Mot de passe', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="aicfp_smtp_password" 
                                       name="aicfp_smtp_password" 
                                       value="<?php echo esc_attr(get_option('aicfp_smtp_password', '')); ?>" 
                                       class="regular-text" 
                                       autocomplete="off">
                            </td>
                        </tr>
                        
                        <tr class="aicfp-smtp-field">
                            <th scope="row">
                                <label for="aicfp_smtp_from_email">
                                    <?php echo esc_html__('Email expéditeur', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="email" 
                                       id="aicfp_smtp_from_email" 
                                       name="aicfp_smtp_from_email" 
                                       value="<?php echo esc_attr(get_option('aicfp_smtp_from_email', get_option('admin_email'))); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                        
                        <tr class="aicfp-smtp-field">
                            <th scope="row">
                                <label for="aicfp_smtp_from_name">
                                    <?php echo esc_html__('Nom expéditeur', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_smtp_from_name" 
                                       name="aicfp_smtp_from_name" 
                                       value="<?php echo esc_attr(get_option('aicfp_smtp_from_name', get_bloginfo('name'))); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                    </table>
                </div>
                
                <p class="submit">
                    <button type="submit" name="aicfp_save_settings" class="button button-primary">
                        <?php echo esc_html__('Enregistrer les modifications', 'ai-content-factory-pro'); ?>
                    </button>
                </p>
            </form>
        </div>
        
        <style>
            .aicfp-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
                margin-bottom: 20px;
                padding: 20px;
            }
            .aicfp-card h2 {
                margin-top: 0;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            function toggleSMTPFields() {
                if ($('#aicfp_smtp_enabled').is(':checked')) {
                    $('.aicfp-smtp-field').show();
                } else {
                    $('.aicfp-smtp-field').hide();
                }
            }
            
            toggleSMTPFields();
            $('#aicfp_smtp_enabled').on('change', toggleSMTPFields);
        });
        </script>
        <?php
    }
    
    /**
     * Enregistrer les paramètres
     */
    private static function save_settings() {
        // Sauvegarder les clés API
        if (isset($_POST['aicfp_openai_api_key'])) {
            update_option('aicfp_openai_api_key', sanitize_text_field($_POST['aicfp_openai_api_key']));
        }
        
        if (isset($_POST['aicfp_rapidapi_key'])) {
            update_option('aicfp_rapidapi_key', sanitize_text_field($_POST['aicfp_rapidapi_key']));
        }
        
        // Sauvegarder les paramètres SMTP
        update_option('aicfp_smtp_enabled', isset($_POST['aicfp_smtp_enabled']));
        
        if (isset($_POST['aicfp_smtp_host'])) {
            update_option('aicfp_smtp_host', sanitize_text_field($_POST['aicfp_smtp_host']));
        }
        
        if (isset($_POST['aicfp_smtp_port'])) {
            update_option('aicfp_smtp_port', intval($_POST['aicfp_smtp_port']));
        }
        
        if (isset($_POST['aicfp_smtp_encryption'])) {
            update_option('aicfp_smtp_encryption', sanitize_text_field($_POST['aicfp_smtp_encryption']));
        }
        
        if (isset($_POST['aicfp_smtp_username'])) {
            update_option('aicfp_smtp_username', sanitize_text_field($_POST['aicfp_smtp_username']));
        }
        
        if (isset($_POST['aicfp_smtp_password'])) {
            update_option('aicfp_smtp_password', $_POST['aicfp_smtp_password']); // Ne pas sanitize le mot de passe
        }
        
        if (isset($_POST['aicfp_smtp_from_email'])) {
            update_option('aicfp_smtp_from_email', sanitize_email($_POST['aicfp_smtp_from_email']));
        }
        
        if (isset($_POST['aicfp_smtp_from_name'])) {
            update_option('aicfp_smtp_from_name', sanitize_text_field($_POST['aicfp_smtp_from_name']));
        }
        
        // Afficher un message de succès
        add_settings_error(
            'aicfp_messages',
            'aicfp_message',
            __('Paramètres enregistrés avec succès.', 'ai-content-factory-pro'),
            'updated'
        );
        
        settings_errors('aicfp_messages');
    }
}
