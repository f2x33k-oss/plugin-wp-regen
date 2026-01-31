<?php
/**
 * Page Albums Recettes
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de la page Albums Recettes
 */
class AICFP_Albums_Recettes_Page {
    
    /**
     * Render la page
     */
    public static function render() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'ai-content-factory-pro'));
        }
        
        ?>
        <div class="wrap aicfp-albums-recettes-page">
            <h1><?php echo esc_html__('Albums Recettes - AI Content Factory Pro', 'ai-content-factory-pro'); ?></h1>
            
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
                                <label>
                                    <?php echo esc_html__('Images de référence', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <div class="aicfp-reference-images-container">
                                    <!-- Option 1 : Upload ZIP -->
                                    <div class="aicfp-upload-option">
                                        <h4><?php echo esc_html__('Option 1 : Upload ZIP', 'ai-content-factory-pro'); ?></h4>
                                        <input type="file" 
                                               id="aicfp_reference_zip" 
                                               name="reference_zip" 
                                               accept=".zip"
                                               class="aicfp-zip-upload">
                                        <p class="description">
                                            <?php echo esc_html__('Uploadez un fichier ZIP contenant toutes les images de référence.', 'ai-content-factory-pro'); ?>
                                        </p>
                                    </div>
                                    
                                    <!-- Option 2 : Images individuelles -->
                                    <div class="aicfp-upload-option">
                                        <h4><?php echo esc_html__('Option 2 : Images individuelles', 'ai-content-factory-pro'); ?></h4>
                                        <div id="aicfp-individual-images">
                                            <div class="aicfp-image-upload-row">
                                                <input type="file" 
                                                       name="reference_images[]" 
                                                       accept="image/*"
                                                       class="aicfp-single-image">
                                                <div class="aicfp-image-preview"></div>
                                                <button type="button" class="button aicfp-remove-image" style="display:none;">×</button>
                                            </div>
                                        </div>
                                        <button type="button" id="aicfp-add-image-field" class="button button-secondary">
                                            <span class="dashicons dashicons-plus-alt"></span>
                                            <?php echo esc_html__('Ajouter une image', 'ai-content-factory-pro'); ?>
                                        </button>
                                        <p class="description">
                                            <?php echo esc_html__('Ajoutez jusqu\'à 10 images de référence pour le style Midjourney (--sref).', 'ai-content-factory-pro'); ?>
                                        </p>
                                    </div>
                                </div>
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
            
            /* Images de référence */
            .aicfp-reference-images-container {
                display: flex;
                flex-direction: column;
                gap: 30px;
            }
            
            .aicfp-upload-option {
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background: #f9f9f9;
            }
            
            .aicfp-upload-option h4 {
                margin-top: 0;
                color: #1d2327;
            }
            
            #aicfp-individual-images {
                display: flex;
                flex-direction: column;
                gap: 15px;
                margin-bottom: 15px;
            }
            
            .aicfp-image-upload-row {
                display: flex;
                align-items: center;
                gap: 15px;
                padding: 15px;
                background: white;
                border: 1px solid #ddd;
                border-radius: 5px;
            }
            
            .aicfp-single-image {
                flex: 1;
            }
            
            .aicfp-image-preview {
                width: 100px;
                height: 100px;
                border: 2px dashed #ddd;
                border-radius: 5px;
                overflow: hidden;
                display: none;
                background-size: cover;
                background-position: center;
                background-color: #f5f5f5;
            }
            
            .aicfp-image-preview.has-image {
                display: block;
                border-style: solid;
                border-color: #2271b1;
            }
            
            .aicfp-remove-image {
                background: #d63638;
                color: white;
                border: none;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                cursor: pointer;
                font-size: 20px;
                line-height: 1;
            }
            
            .aicfp-remove-image:hover {
                background: #a02020;
            }
            
            #aicfp-add-image-field .dashicons {
                line-height: 28px;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            let imageCount = 1;
            const maxImages = 10;
            
            // Ajouter un nouveau champ d'image
            $('#aicfp-add-image-field').on('click', function() {
                if ($('.aicfp-image-upload-row').length >= maxImages) {
                    alert('<?php echo esc_js(__("Vous pouvez ajouter maximum 10 images de référence.", "ai-content-factory-pro")); ?>');
                    return;
                }
                
                const newRow = `
                    <div class="aicfp-image-upload-row">
                        <input type="file" 
                               name="reference_images[]" 
                               accept="image/*"
                               class="aicfp-single-image">
                        <div class="aicfp-image-preview"></div>
                        <button type="button" class="button aicfp-remove-image">×</button>
                    </div>
                `;
                
                $('#aicfp-individual-images').append(newRow);
                imageCount++;
            });
            
            // Supprimer une ligne d'image
            $(document).on('click', '.aicfp-remove-image', function() {
                if ($('.aicfp-image-upload-row').length > 1) {
                    $(this).closest('.aicfp-image-upload-row').remove();
                    imageCount--;
                } else {
                    // Réinitialiser la première ligne
                    $(this).closest('.aicfp-image-upload-row').find('.aicfp-single-image').val('');
                    $(this).closest('.aicfp-image-upload-row').find('.aicfp-image-preview').removeClass('has-image').css('background-image', '');
                    $(this).hide();
                }
            });
            
            // Prévisualisation des images
            $(document).on('change', '.aicfp-single-image', function() {
                const file = this.files[0];
                const $preview = $(this).siblings('.aicfp-image-preview');
                const $removeBtn = $(this).siblings('.aicfp-remove-image');
                
                if (file) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        $preview.css('background-image', 'url(' + e.target.result + ')');
                        $preview.addClass('has-image');
                        $removeBtn.show();
                    }
                    
                    reader.readAsDataURL(file);
                } else {
                    $preview.removeClass('has-image').css('background-image', '');
                    $removeBtn.hide();
                }
            });
        });
        </script>
        <?php
    }
}
