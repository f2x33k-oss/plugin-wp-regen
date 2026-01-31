<?php
/**
 * Page Albums Idées - Version moderne
 *
 * @package AI_Content_Factory_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class AICFP_Albums_Idees_Page {
    
    public static function render() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.', 'ai-content-factory-pro'));
        }
        
        ?>
        <div class="wrap aicfp-modern-page">
            <!-- Header avec gradient -->
            <div class="aicfp-page-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="aicfp-header-content">
                    <h1>
                        <span class="dashicons dashicons-format-gallery"></span>
                        <?php echo esc_html__('Albums Idées', 'ai-content-factory-pro'); ?>
                    </h1>
                    <p class="aicfp-subtitle"><?php echo esc_html__('Créez des carrousels Facebook/Instagram avec des idées visuelles inspirantes', 'ai-content-factory-pro'); ?></p>
                </div>
            </div>
            
            <div class="aicfp-container">
                <div class="aicfp-grid">
                    <!-- Colonne formulaire -->
                    <div class="aicfp-col-main">
                        <div class="aicfp-card aicfp-card-modern">
                            <div class="aicfp-card-header">
                                <h2><?php echo esc_html__('Configuration de l\'album d\'idées', 'ai-content-factory-pro'); ?></h2>
                            </div>
                            
                            <form id="aicfp-albums-idees-form" enctype="multipart/form-data">
                                <?php wp_nonce_field('aicfp_nonce', 'aicfp_nonce'); ?>
                                
                                <div class="aicfp-form-section">
                                    <!-- Titre -->
                                    <div class="aicfp-form-group">
                                        <label for="aicfp_idees_title" class="aicfp-label">
                                            <?php echo esc_html__('Titre de l\'album d\'idées', 'ai-content-factory-pro'); ?>
                                            <span class="aicfp-required">*</span>
                                        </label>
                                        <input type="text" 
                                               id="aicfp_idees_title" 
                                               name="title" 
                                               class="aicfp-input" 
                                               required
                                               placeholder="Ex: 15 idées de décorations de petits jardins">
                                        <p class="aicfp-hint"><?php echo esc_html__('Le nombre d\'idées sera détecté automatiquement', 'ai-content-factory-pro'); ?></p>
                                    </div>
                                    
                                    <!-- Style visuel -->
                                    <div class="aicfp-form-group">
                                        <label for="aicfp_idees_style" class="aicfp-label">
                                            <?php echo esc_html__('Style visuel', 'ai-content-factory-pro'); ?>
                                        </label>
                                        <select id="aicfp_idees_style" name="visual_style" class="aicfp-input">
                                            <option value="realistic"><?php echo esc_html__('🖼️ Réaliste / Photo', 'ai-content-factory-pro'); ?></option>
                                            <option value="modern"><?php echo esc_html__('✨ Moderne / Design', 'ai-content-factory-pro'); ?></option>
                                            <option value="minimalist"><?php echo esc_html__('⚪ Minimaliste', 'ai-content-factory-pro'); ?></option>
                                            <option value="artistic"><?php echo esc_html__('🎨 Artistique / Créatif', 'ai-content-factory-pro'); ?></option>
                                            <option value="vintage"><?php echo esc_html__('📻 Vintage / Rétro', 'ai-content-factory-pro'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <!-- Format -->
                                    <div class="aicfp-form-group">
                                        <label for="aicfp_idees_format" class="aicfp-label">
                                            <?php echo esc_html__('Format des images', 'ai-content-factory-pro'); ?>
                                        </label>
                                        <select id="aicfp_idees_format" name="image_format" class="aicfp-input">
                                            <option value="square" selected><?php echo esc_html__('⬜ Carré (1:1) - Idéal carrousels', 'ai-content-factory-pro'); ?></option>
                                            <option value="portrait"><?php echo esc_html__('📱 Portrait (4:5) - Stories', 'ai-content-factory-pro'); ?></option>
                                            <option value="landscape"><?php echo esc_html__('🖼️ Paysage (16:9)', 'ai-content-factory-pro'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <!-- Upload références -->
                                    <div class="aicfp-form-group">
                                        <label class="aicfp-label"><?php echo esc_html__('Images de référence (optionnel)', 'ai-content-factory-pro'); ?></label>
                                        
                                        <div class="aicfp-tabs-minimal">
                                            <button type="button" class="aicfp-tab-btn active" data-tab="multi">
                                                <span class="dashicons dashicons-images-alt2"></span>
                                                <?php echo esc_html__('Multi-Upload', 'ai-content-factory-pro'); ?>
                                            </button>
                                            <button type="button" class="aicfp-tab-btn" data-tab="individual">
                                                <span class="dashicons dashicons-camera"></span>
                                                <?php echo esc_html__('Individuel', 'ai-content-factory-pro'); ?>
                                            </button>
                                        </div>
                                        
                                        <div class="aicfp-tab-content active" id="tab-multi">
                                            <div class="aicfp-upload-zone">
                                                <input type="file" 
                                                       id="aicfp_idees_reference_files" 
                                                       name="reference_files[]" 
                                                       accept="image/*,.zip"
                                                       multiple
                                                       class="aicfp-file-input">
                                                <label for="aicfp_idees_reference_files" class="aicfp-upload-label">
                                                    <span class="dashicons dashicons-upload"></span>
                                                    <span><?php echo esc_html__('ZIP ou sélection multiple (Ctrl+Clic)', 'ai-content-factory-pro'); ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="aicfp-tab-content" id="tab-individual">
                                            <div id="aicfp-idees-individual-images">
                                                <div class="aicfp-image-row">
                                                    <input type="file" name="individual_images[]" accept="image/*" class="aicfp-single-image aicfp-file-input">
                                                    <div class="aicfp-preview"></div>
                                                    <button type="button" class="aicfp-btn-remove" style="display:none;">×</button>
                                                </div>
                                            </div>
                                            <button type="button" id="aicfp-idees-add-image-field" class="aicfp-btn aicfp-btn-text">
                                                <span class="dashicons dashicons-plus"></span>
                                                <?php echo esc_html__('Ajouter une image', 'ai-content-factory-pro'); ?>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Email -->
                                    <div class="aicfp-form-group">
                                        <label for="aicfp_idees_email" class="aicfp-label">
                                            <?php echo esc_html__('Email de livraison', 'ai-content-factory-pro'); ?>
                                            <span class="aicfp-required">*</span>
                                        </label>
                                        <input type="email" 
                                               id="aicfp_idees_email" 
                                               name="email" 
                                               class="aicfp-input" 
                                               required
                                               value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>">
                                        <p class="aicfp-hint"><?php echo esc_html__('Vous recevrez un lien de téléchargement de l\'album', 'ai-content-factory-pro'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="aicfp-form-footer">
                                    <button type="submit" id="aicfp-idees-submit-btn" class="aicfp-btn aicfp-btn-primary aicfp-btn-large">
                                        <span class="dashicons dashicons-images-alt2"></span>
                                        <span class="aicfp-btn-text"><?php echo esc_html__('Générer l\'album d\'idées', 'ai-content-factory-pro'); ?></span>
                                    </button>
                                </div>
                            </form>
                            
                            <div id="aicfp-idees-result-message"></div>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="aicfp-col-sidebar">
                        <!-- Estimateur -->
                        <div class="aicfp-card aicfp-card-estimator" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <div class="aicfp-card-header">
                                <h3><?php echo esc_html__('Estimation', 'ai-content-factory-pro'); ?></h3>
                            </div>
                            <div class="aicfp-card-body">
                                <div class="aicfp-stat-box">
                                    <span class="aicfp-stat-icon">💡</span>
                                    <div class="aicfp-stat-content">
                                        <span class="aicfp-stat-label"><?php echo esc_html__('Idées', 'ai-content-factory-pro'); ?></span>
                                        <span class="aicfp-stat-value" id="aicfp-idees-estimated-items">-</span>
                                    </div>
                                </div>
                                
                                <div class="aicfp-stat-box">
                                    <span class="aicfp-stat-icon">💰</span>
                                    <div class="aicfp-stat-content">
                                        <span class="aicfp-stat-label"><?php echo esc_html__('Coût estimé', 'ai-content-factory-pro'); ?></span>
                                        <span class="aicfp-stat-value" id="aicfp-idees-estimated-cost">$0.00</span>
                                    </div>
                                </div>
                                
                                <div class="aicfp-stat-box">
                                    <span class="aicfp-stat-icon">⏱️</span>
                                    <div class="aicfp-stat-content">
                                        <span class="aicfp-stat-label"><?php echo esc_html__('Temps estimé', 'ai-content-factory-pro'); ?></span>
                                        <span class="aicfp-stat-value" id="aicfp-idees-estimated-time">0 min</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Info carrousels -->
                        <div class="aicfp-card aicfp-card-help" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); border-color: #f5576c;">
                            <div class="aicfp-card-header" style="background: rgba(255,255,255,0.3); border-color: rgba(0,0,0,0.1);">
                                <h3 style="color: #8b2635;">📱 <?php echo esc_html__('Pour carrousels', 'ai-content-factory-pro'); ?></h3>
                            </div>
                            <div class="aicfp-card-body">
                                <div class="aicfp-help-item" style="border-color: rgba(0,0,0,0.1);">
                                    <span class="dashicons dashicons-instagram" style="color: #f5576c;"></span>
                                    <p style="color: #8b2635;"><?php echo esc_html__('Format carré (1:1) recommandé pour Facebook et Instagram', 'ai-content-factory-pro'); ?></p>
                                </div>
                                <div class="aicfp-help-item" style="border-color: rgba(0,0,0,0.1);">
                                    <span class="dashicons dashicons-images-alt2" style="color: #f5576c;"></span>
                                    <p style="color: #8b2635;"><?php echo esc_html__('10-20 idées idéal pour engagement', 'ai-content-factory-pro'); ?></p>
                                </div>
                                <div class="aicfp-help-item" style="border-color: rgba(0,0,0,0.1);">
                                    <span class="dashicons dashicons-download" style="color: #f5576c;"></span>
                                    <p style="color: #8b2635;"><?php echo esc_html__('Téléchargement ZIP direct par email', 'ai-content-factory-pro'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
