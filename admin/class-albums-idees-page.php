<?php
/**
 * Page Albums Idées
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de la page Albums Idées
 */
class AICFP_Albums_Idees_Page {
    
    /**
     * Render la page
     */
    public static function render() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'ai-content-factory-pro'));
        }
        
        ?>
        <div class="wrap aicfp-albums-idees-page">
            <h1><?php echo esc_html__('Albums Idées - Carrousels Facebook', 'ai-content-factory-pro'); ?></h1>
            
            <div class="aicfp-card">
                <div class="aicfp-intro">
                    <p><?php echo esc_html__('Créez des albums d\'idées parfaits pour les carrousels Facebook/Instagram.', 'ai-content-factory-pro'); ?></p>
                    <p class="description"><?php echo esc_html__('Exemple : "15 idées de décorations de petits jardins" → génère 15 images d\'idées sur ce thème.', 'ai-content-factory-pro'); ?></p>
                </div>
                
                <form id="aicfp-albums-idees-form" enctype="multipart/form-data">
                    <?php wp_nonce_field('aicfp_nonce', 'aicfp_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aicfp_idees_title">
                                    <?php echo esc_html__('Titre de l\'album d\'idées', 'ai-content-factory-pro'); ?>
                                    <span class="required">*</span>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_idees_title" 
                                       name="title" 
                                       class="large-text" 
                                       required 
                                       placeholder="<?php echo esc_attr__('Ex: 15 idées de décorations de petits jardins', 'ai-content-factory-pro'); ?>">
                                <p class="description">
                                    <?php echo esc_html__('Le plugin détectera automatiquement le nombre d\'idées à générer (ex: "15 idées" → 15 images).', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_idees_style">
                                    <?php echo esc_html__('Style visuel', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <select id="aicfp_idees_style" name="visual_style" class="regular-text">
                                    <option value="realistic"><?php echo esc_html__('Réaliste / Photo', 'ai-content-factory-pro'); ?></option>
                                    <option value="modern"><?php echo esc_html__('Moderne / Design', 'ai-content-factory-pro'); ?></option>
                                    <option value="minimalist"><?php echo esc_html__('Minimaliste', 'ai-content-factory-pro'); ?></option>
                                    <option value="artistic"><?php echo esc_html__('Artistique / Créatif', 'ai-content-factory-pro'); ?></option>
                                    <option value="vintage"><?php echo esc_html__('Vintage / Rétro', 'ai-content-factory-pro'); ?></option>
                                </select>
                                <p class="description">
                                    <?php echo esc_html__('Le style appliqué à toutes les images de l\'album.', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_idees_format">
                                    <?php echo esc_html__('Format des images', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <select id="aicfp_idees_format" name="image_format" class="regular-text">
                                    <option value="square" selected><?php echo esc_html__('Carré (1:1) - Idéal pour carrousels', 'ai-content-factory-pro'); ?></option>
                                    <option value="portrait"><?php echo esc_html__('Portrait (4:5) - Stories/Feed', 'ai-content-factory-pro'); ?></option>
                                    <option value="landscape"><?php echo esc_html__('Paysage (16:9)', 'ai-content-factory-pro'); ?></option>
                                </select>
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
                                    <!-- Option 1 : Upload ZIP ou Dossier -->
                                    <div class="aicfp-upload-option">
                                        <h4><?php echo esc_html__('Upload ZIP ou sélection multiple', 'ai-content-factory-pro'); ?></h4>
                                        <input type="file" 
                                               id="aicfp_idees_reference_files" 
                                               name="reference_files[]" 
                                               accept="image/*,.zip"
                                               multiple
                                               class="aicfp-zip-upload">
                                        <p class="description">
                                            <?php echo esc_html__('Uploadez un ZIP ou sélectionnez plusieurs images à la fois (Ctrl+Clic).', 'ai-content-factory-pro'); ?>
                                        </p>
                                    </div>
                                    
                                    <!-- Option 2 : Images individuelles avec preview -->
                                    <div class="aicfp-upload-option">
                                        <h4><?php echo esc_html__('Images individuelles', 'ai-content-factory-pro'); ?></h4>
                                        <div id="aicfp-idees-individual-images">
                                            <div class="aicfp-image-upload-row">
                                                <input type="file" 
                                                       name="individual_images[]" 
                                                       accept="image/*"
                                                       class="aicfp-single-image">
                                                <div class="aicfp-image-preview"></div>
                                                <button type="button" class="button aicfp-remove-image" style="display:none;">×</button>
                                            </div>
                                        </div>
                                        <button type="button" id="aicfp-idees-add-image-field" class="button button-secondary">
                                            <span class="dashicons dashicons-plus-alt"></span>
                                            <?php echo esc_html__('Ajouter une image', 'ai-content-factory-pro'); ?>
                                        </button>
                                        <p class="description">
                                            <?php echo esc_html__('Images de référence pour définir le style visuel général.', 'ai-content-factory-pro'); ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_idees_email">
                                    <?php echo esc_html__('Email de livraison', 'ai-content-factory-pro'); ?>
                                    <span class="required">*</span>
                                </label>
                            </th>
                            <td>
                                <input type="email" 
                                       id="aicfp_idees_email" 
                                       name="email" 
                                       class="regular-text" 
                                       required 
                                       value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>">
                                <p class="description">
                                    <?php echo esc_html__('Vous recevrez un email avec le lien de téléchargement de l\'album une fois terminé.', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <!-- Calculateur -->
                    <div class="aicfp-estimator">
                        <h3><?php echo esc_html__('Estimation', 'ai-content-factory-pro'); ?></h3>
                        <div class="aicfp-estimator-content">
                            <div class="aicfp-estimator-item">
                                <span class="aicfp-estimator-label"><?php echo esc_html__('Idées:', 'ai-content-factory-pro'); ?></span>
                                <span class="aicfp-estimator-value" id="aicfp-idees-estimated-items">-</span>
                            </div>
                            <div class="aicfp-estimator-item">
                                <span class="aicfp-estimator-label"><?php echo esc_html__('Coût estimé:', 'ai-content-factory-pro'); ?></span>
                                <span class="aicfp-estimator-value" id="aicfp-idees-estimated-cost">$0.00</span>
                            </div>
                            <div class="aicfp-estimator-item">
                                <span class="aicfp-estimator-label"><?php echo esc_html__('Temps estimé:', 'ai-content-factory-pro'); ?></span>
                                <span class="aicfp-estimator-value" id="aicfp-idees-estimated-time">0 min</span>
                            </div>
                        </div>
                    </div>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary button-large" id="aicfp-idees-submit-btn">
                            <span class="dashicons dashicons-images-alt2"></span>
                            <?php echo esc_html__('Générer l\'album d\'idées', 'ai-content-factory-pro'); ?>
                        </button>
                    </p>
                </form>
                
                <div id="aicfp-idees-result-message" style="display: none;"></div>
            </div>
        </div>
        
        <style>
            .aicfp-intro {
                background: #e7f5fe;
                padding: 15px;
                border-left: 4px solid #2271b1;
                margin-bottom: 20px;
            }
            
            .aicfp-intro p {
                margin: 5px 0;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Calcul automatique de l'estimation
            $('#aicfp_idees_title').on('change keyup', function() {
                const title = $(this).val();
                const match = title.match(/(\d+)/);
                const itemCount = match ? parseInt(match[1]) : 10;
                
                $('#aicfp-idees-estimated-items').text(itemCount);
                $('#aicfp-idees-estimated-cost').text('$' + (itemCount * 0.05).toFixed(2));
                $('#aicfp-idees-estimated-time').text(Math.ceil(itemCount * 2) + ' min');
            });
            
            // Ajouter un nouveau champ d'image
            $('#aicfp-idees-add-image-field').on('click', function() {
                const newRow = `
                    <div class="aicfp-image-upload-row">
                        <input type="file" 
                               name="individual_images[]" 
                               accept="image/*"
                               class="aicfp-single-image">
                        <div class="aicfp-image-preview"></div>
                        <button type="button" class="button aicfp-remove-image">×</button>
                    </div>
                `;
                
                $('#aicfp-idees-individual-images').append(newRow);
            });
            
            // Supprimer une ligne d'image
            $(document).on('click', '.aicfp-remove-image', function() {
                const $row = $(this).closest('.aicfp-image-upload-row');
                if ($('#aicfp-idees-individual-images .aicfp-image-upload-row').length > 1) {
                    $row.remove();
                } else {
                    $row.find('.aicfp-single-image').val('');
                    $row.find('.aicfp-image-preview').removeClass('has-image').css('background-image', '');
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
            
            // Soumission du formulaire
            $('#aicfp-albums-idees-form').on('submit', function(e) {
                e.preventDefault();
                
                const $submitBtn = $('#aicfp-idees-submit-btn');
                const $resultMessage = $('#aicfp-idees-result-message');
                
                $submitBtn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> Traitement en cours...');
                
                const formData = new FormData(this);
                formData.append('action', 'aicfp_submit_album_idees');
                formData.append('nonce', aicfp_ajax.nonce);
                
                $.ajax({
                    url: aicfp_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $resultMessage
                                .removeClass('aicfp-result-error')
                                .addClass('aicfp-result-success')
                                .html('<strong>Succès !</strong> ' + response.data.message)
                                .show();
                            
                            setTimeout(function() {
                                window.location.href = 'admin.php?page=aicfp-instances';
                            }, 2000);
                        } else {
                            $resultMessage
                                .removeClass('aicfp-result-success')
                                .addClass('aicfp-result-error')
                                .html('<strong>Erreur !</strong> ' + response.data.message)
                                .show();
                        }
                    },
                    error: function() {
                        $resultMessage
                            .removeClass('aicfp-result-success')
                            .addClass('aicfp-result-error')
                            .html('<strong>Erreur !</strong> Une erreur est survenue.')
                            .show();
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).html('<span class="dashicons dashicons-images-alt2"></span> Générer l\'album d\'idées');
                    }
                });
            });
        });
        </script>
        <?php
    }
}
