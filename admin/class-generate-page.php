<?php
/**
 * Page de génération
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de la page de génération
 */
class AICFP_Generate_Page {
    
    /**
     * Render la page
     */
    public static function render() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'ai-content-factory-pro'));
        }
        
        ?>
        <div class="wrap aicfp-generate-page">
            <h1><?php echo esc_html__('Générer du Contenu - AI Content Factory Pro', 'ai-content-factory-pro'); ?></h1>
            
            <div class="aicfp-card">
                <form id="aicfp-generate-form" enctype="multipart/form-data">
                    <?php wp_nonce_field('aicfp_nonce', 'aicfp_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aicfp_title">
                                    <?php echo esc_html__('Titre du projet', 'ai-content-factory-pro'); ?>
                                    <span class="required">*</span>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_title" 
                                       name="title" 
                                       class="regular-text" 
                                       required 
                                       placeholder="<?php echo esc_attr__('Ex: 20 recettes de gratins', 'ai-content-factory-pro'); ?>">
                                <p class="description">
                                    <?php echo esc_html__('Le plugin détectera automatiquement le nombre d\'items à générer (ex: "20 recettes" → 20 items).', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_generate_text">
                                    <?php echo esc_html__('Génération de texte', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <label class="aicfp-toggle">
                                    <input type="checkbox" 
                                           id="aicfp_generate_text" 
                                           name="generate_text" 
                                           value="1" 
                                           checked>
                                    <span class="aicfp-toggle-slider"></span>
                                    <span class="aicfp-toggle-label">
                                        <?php echo esc_html__('Générer les textes via ChatGPT', 'ai-content-factory-pro'); ?>
                                    </span>
                                </label>
                                <p class="description">
                                    <?php echo esc_html__('Si activé, créera un article WordPress avec le contenu généré.', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_reference_zip">
                                    <?php echo esc_html__('Images de référence', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="file" 
                                       id="aicfp_reference_zip" 
                                       name="reference_zip" 
                                       accept=".zip">
                                <p class="description">
                                    <?php echo esc_html__('Uploadez un fichier ZIP contenant des images à utiliser comme références (--sref) pour Midjourney.', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_email">
                                    <?php echo esc_html__('Email de livraison', 'ai-content-factory-pro'); ?>
                                    <span class="required">*</span>
                                </label>
                            </th>
                            <td>
                                <input type="email" 
                                       id="aicfp_email" 
                                       name="email" 
                                       class="regular-text" 
                                       required 
                                       value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>">
                                <p class="description">
                                    <?php echo esc_html__('Vous recevrez un email avec les résultats une fois la génération terminée.', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <!-- Calculateur -->
                    <div class="aicfp-estimator">
                        <h3><?php echo esc_html__('Estimation', 'ai-content-factory-pro'); ?></h3>
                        <div class="aicfp-estimator-content">
                            <div class="aicfp-estimator-item">
                                <span class="aicfp-estimator-label"><?php echo esc_html__('Items:', 'ai-content-factory-pro'); ?></span>
                                <span class="aicfp-estimator-value" id="aicfp-estimated-items">-</span>
                            </div>
                            <div class="aicfp-estimator-item">
                                <span class="aicfp-estimator-label"><?php echo esc_html__('Coût estimé:', 'ai-content-factory-pro'); ?></span>
                                <span class="aicfp-estimator-value" id="aicfp-estimated-cost">$0.00</span>
                            </div>
                            <div class="aicfp-estimator-item">
                                <span class="aicfp-estimator-label"><?php echo esc_html__('Temps estimé:', 'ai-content-factory-pro'); ?></span>
                                <span class="aicfp-estimator-value" id="aicfp-estimated-time">0 min</span>
                            </div>
                        </div>
                    </div>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary button-large" id="aicfp-submit-btn">
                            <span class="dashicons dashicons-hammer"></span>
                            <?php echo esc_html__('Lancer la génération', 'ai-content-factory-pro'); ?>
                        </button>
                    </p>
                </form>
                
                <div id="aicfp-result-message" style="display: none;"></div>
            </div>
        </div>
        
        <style>
            .aicfp-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
                margin-top: 20px;
                padding: 20px;
            }
            
            .required {
                color: #d63638;
            }
            
            .aicfp-toggle {
                position: relative;
                display: inline-flex;
                align-items: center;
                cursor: pointer;
            }
            
            .aicfp-toggle input[type="checkbox"] {
                position: absolute;
                opacity: 0;
                width: 0;
                height: 0;
            }
            
            .aicfp-toggle-slider {
                position: relative;
                display: inline-block;
                width: 50px;
                height: 26px;
                background-color: #ccc;
                border-radius: 26px;
                transition: 0.3s;
                margin-right: 10px;
            }
            
            .aicfp-toggle-slider:before {
                content: "";
                position: absolute;
                height: 20px;
                width: 20px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                border-radius: 50%;
                transition: 0.3s;
            }
            
            .aicfp-toggle input:checked + .aicfp-toggle-slider {
                background-color: #2271b1;
            }
            
            .aicfp-toggle input:checked + .aicfp-toggle-slider:before {
                transform: translateX(24px);
            }
            
            .aicfp-estimator {
                background: #f0f6fc;
                border: 1px solid #c3e4ff;
                border-radius: 5px;
                padding: 20px;
                margin: 20px 0;
            }
            
            .aicfp-estimator h3 {
                margin-top: 0;
                color: #0c5d8c;
            }
            
            .aicfp-estimator-content {
                display: flex;
                gap: 30px;
                flex-wrap: wrap;
            }
            
            .aicfp-estimator-item {
                display: flex;
                flex-direction: column;
            }
            
            .aicfp-estimator-label {
                font-size: 12px;
                color: #666;
                text-transform: uppercase;
                margin-bottom: 5px;
            }
            
            .aicfp-estimator-value {
                font-size: 24px;
                font-weight: bold;
                color: #0c5d8c;
            }
            
            #aicfp-submit-btn .dashicons {
                line-height: 28px;
                margin-right: 5px;
            }
            
            .aicfp-result-success {
                background: #d7f0db;
                border-left: 4px solid #00a32a;
                padding: 15px;
                margin-top: 20px;
            }
            
            .aicfp-result-error {
                background: #fcf0f1;
                border-left: 4px solid #d63638;
                padding: 15px;
                margin-top: 20px;
            }
        </style>
        <?php
    }
}
