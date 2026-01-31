<?php
/**
 * Page Albums Recettes - Version moderne
 *
 * @package AI_Content_Factory_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class AICFP_Albums_Recettes_Page {
    
    public static function render() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.', 'ai-content-factory-pro'));
        }
        
        // Enregistrer le script spécifique à cette page
        wp_enqueue_script(
            'aicfp-albums-recettes',
            AICFP_PLUGIN_URL . 'assets/js/albums-recettes.js',
            array('jquery'),
            AICFP_VERSION,
            true
        );
        
        ?>
        <div class="wrap aicfp-modern-page">
            <!-- Header avec gradient -->
            <div class="aicfp-page-header">
                <div class="aicfp-header-content">
                    <h1>
                        <span class="dashicons dashicons-food"></span>
                        <?php echo esc_html__('Albums Recettes', 'ai-content-factory-pro'); ?>
                    </h1>
                    <p class="aicfp-subtitle"><?php echo esc_html__('Créez des albums de recettes avec textes et images générés par IA', 'ai-content-factory-pro'); ?></p>
                </div>
            </div>
            
            <!-- Contenu principal -->
            <div class="aicfp-container">
                <div class="aicfp-grid">
                    <!-- Colonne formulaire -->
                    <div class="aicfp-col-main">
                        <div class="aicfp-card aicfp-card-modern">
                            <div class="aicfp-card-header">
                                <h2><?php echo esc_html__('Configuration de l\'album', 'ai-content-factory-pro'); ?></h2>
                            </div>
                            
                            <form id="aicfp-albums-recettes-form" enctype="multipart/form-data">
                                <?php wp_nonce_field('aicfp_nonce', 'aicfp_nonce'); ?>
                                
                                <div class="aicfp-form-section">
                                    <!-- Titre -->
                                    <div class="aicfp-form-group">
                                        <label class="aicfp-label">
                                            <?php echo esc_html__('Titre de l\'album', 'ai-content-factory-pro'); ?>
                                            <span class="aicfp-required">*</span>
                                        </label>
                                        <div class="aicfp-input-group">
                                            <input type="text" 
                                                   id="aicfp_title" 
                                                   name="title" 
                                                   class="aicfp-input" 
                                                   required
                                                   placeholder="Ex: 20 recettes de gratins savoureux">
                                            <button type="button" id="aicfp-suggest-title" class="aicfp-btn aicfp-btn-secondary">
                                                <span class="dashicons dashicons-lightbulb"></span>
                                                <span class="aicfp-btn-text"><?php echo esc_html__('Suggérer', 'ai-content-factory-pro'); ?></span>
                                            </button>
                                        </div>
                                        <p class="aicfp-hint"><?php echo esc_html__('Le nombre d\'items sera détecté automatiquement (ex: "20 recettes" → 20 items)', 'ai-content-factory-pro'); ?></p>
                                        
                                        <!-- Zone suggestions -->
                                        <div id="aicfp-title-suggestions" class="aicfp-suggestions-panel" style="display:none;">
                                            <div class="aicfp-suggestions-header">
                                                <span><?php echo esc_html__('💡 Suggestions', 'ai-content-factory-pro'); ?></span>
                                                <button type="button" id="aicfp-reload-suggestions" class="aicfp-btn-link">
                                                    <span class="dashicons dashicons-update"></span>
                                                    <?php echo esc_html__('Recharger', 'ai-content-factory-pro'); ?>
                                                </button>
                                            </div>
                                            <div id="aicfp-suggestions-list"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Options de génération -->
                                    <div class="aicfp-form-group">
                                        <label class="aicfp-label"><?php echo esc_html__('Options de génération', 'ai-content-factory-pro'); ?></label>
                                        
                                        <div class="aicfp-toggle-group">
                                            <label class="aicfp-toggle-modern">
                                                <input type="checkbox" id="aicfp_generate_text" name="generate_text" value="1" checked>
                                                <span class="aicfp-toggle-slider"></span>
                                            </label>
                                            <span class="aicfp-toggle-label-text">
                                                <?php echo esc_html__('Générer les textes via ChatGPT', 'ai-content-factory-pro'); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Option publication (visible seulement si texte activé) -->
                                    <div class="aicfp-form-group" id="aicfp-publish-option-row" style="display:none;">
                                        <div class="aicfp-checkbox-group">
                                            <label class="aicfp-checkbox-modern">
                                                <input type="checkbox" id="aicfp_publish_article" name="publish_article" value="1">
                                                <span class="aicfp-checkbox-icon"></span>
                                                <span class="aicfp-checkbox-label">
                                                    <?php echo esc_html__('Publier l\'article directement', 'ai-content-factory-pro'); ?>
                                                </span>
                                            </label>
                                        </div>
                                        <p class="aicfp-hint aicfp-hint-info">
                                            <?php echo esc_html__('L\'article contiendra : titre, intro de 30 mots, et pour chaque recette → titre + image + texte. La première image sera l\'image à la une.', 'ai-content-factory-pro'); ?>
                                        </p>
                                    </div>
                                    
                                    <!-- Sélection API de génération d'images -->
                                    <div class="aicfp-form-group">
                                        <label for="aicfp_image_api" class="aicfp-label">
                                            <span class="dashicons dashicons-admin-settings"></span>
                                            <?php echo esc_html__('Moteur de génération d\'images', 'ai-content-factory-pro'); ?>
                                        </label>
                                        <select id="aicfp_image_api" name="image_api" class="aicfp-input aicfp-api-selector">
                                            <option value="midjourney" selected><?php echo esc_html__('🎨 Midjourney (RapidAPI)', 'ai-content-factory-pro'); ?></option>
                                            <option value="sdxl"><?php echo esc_html__('🖼️ Stable Diffusion XL', 'ai-content-factory-pro'); ?></option>
                                            <option value="sdxl-food"><?php echo esc_html__('🍽️ SDXL Food LoRA (Spécialisé recettes)', 'ai-content-factory-pro'); ?></option>
                                            <option value="sdxl-finetuned"><?php echo esc_html__('⚡ Fine-tuned SDXL', 'ai-content-factory-pro'); ?></option>
                                            <option value="dalle"><?php echo esc_html__('🤖 DALL-E 3 (ChatGPT)', 'ai-content-factory-pro'); ?></option>
                                            <option value="nanobanana"><?php echo esc_html__('🍌 Nanobanana', 'ai-content-factory-pro'); ?></option>
                                            <option value="replicate"><?php echo esc_html__('🔄 Replicate', 'ai-content-factory-pro'); ?></option>
                                            <option value="flux-pro"><?php echo esc_html__('⚡ Flux Pro', 'ai-content-factory-pro'); ?></option>
                                        </select>
                                        <p class="aicfp-hint">
                                            <?php echo esc_html__('Choisissez le moteur IA pour générer les images. SDXL Food LoRA est optimisé pour les recettes.', 'ai-content-factory-pro'); ?>
                                        </p>
                                        
                                        <!-- Info API sélectionnée -->
                                        <div id="aicfp-api-info" class="aicfp-api-info-panel">
                                            <div class="aicfp-api-info-content" data-api="midjourney">
                                                <strong>Midjourney</strong> - Qualité premium, style artistique
                                                <br><small>Coût: ~$0.05/image • Temps: ~2 min/image</small>
                                            </div>
                                            <div class="aicfp-api-info-content" data-api="sdxl" style="display:none;">
                                                <strong>Stable Diffusion XL</strong> - Polyvalent, rapide, économique
                                                <br><small>Coût: ~$0.01/image • Temps: ~30s/image</small>
                                            </div>
                                            <div class="aicfp-api-info-content" data-api="sdxl-food" style="display:none;">
                                                <strong>SDXL Food LoRA</strong> - Spécialisé recettes, ultra-réaliste
                                                <br><small>Coût: ~$0.02/image • Temps: ~45s/image • ⭐ RECOMMANDÉ</small>
                                            </div>
                                            <div class="aicfp-api-info-content" data-api="sdxl-finetuned" style="display:none;">
                                                <strong>Fine-tuned SDXL</strong> - Modèle optimisé personnalisé
                                                <br><small>Coût: ~$0.02/image • Temps: ~40s/image</small>
                                            </div>
                                            <div class="aicfp-api-info-content" data-api="dalle" style="display:none;">
                                                <strong>DALL-E 3</strong> - Par OpenAI, haute qualité
                                                <br><small>Coût: ~$0.04/image • Temps: ~20s/image</small>
                                            </div>
                                            <div class="aicfp-api-info-content" data-api="nanobanana" style="display:none;">
                                                <strong>Nanobanana</strong> - Rapide et créatif
                                                <br><small>Coût: ~$0.02/image • Temps: ~30s/image</small>
                                            </div>
                                            <div class="aicfp-api-info-content" data-api="replicate" style="display:none;">
                                                <strong>Replicate</strong> - Accès à multiples modèles
                                                <br><small>Coût: Variable • Temps: ~1 min/image</small>
                                            </div>
                                            <div class="aicfp-api-info-content" data-api="flux-pro" style="display:none;">
                                                <strong>Flux Pro</strong> - Nouvelle génération, ultra-rapide
                                                <br><small>Coût: ~$0.03/image • Temps: ~15s/image</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Pinterest Search -->
                                    <div class="aicfp-form-group">
                                        <label class="aicfp-label">
                                            <span class="dashicons dashicons-pinterest" style="color: #e60023;"></span>
                                            <?php echo esc_html__('Recherche Pinterest', 'ai-content-factory-pro'); ?>
                                        </label>
                                        <div class="aicfp-search-bar-modern">
                                            <input type="text" 
                                                   id="aicfp_pinterest_search" 
                                                   class="aicfp-input" 
                                                   placeholder="Ex: Recettes de gratins">
                                            <button type="button" id="aicfp-search-pinterest" class="aicfp-btn aicfp-btn-primary">
                                                <span class="dashicons dashicons-search"></span>
                                                <span class="aicfp-btn-text"><?php echo esc_html__('Rechercher', 'ai-content-factory-pro'); ?></span>
                                            </button>
                                        </div>
                                        
                                        <div id="aicfp-pinterest-results" class="aicfp-pinterest-panel" style="display:none;">
                                            <div class="aicfp-pinterest-toolbar">
                                                <span class="aicfp-pinterest-count">
                                                    <strong id="aicfp-selected-count">0</strong> <?php echo esc_html__('sélectionnée(s)', 'ai-content-factory-pro'); ?>
                                                </span>
                                                <div class="aicfp-pinterest-actions">
                                                    <button type="button" id="aicfp-clear-pinterest" class="aicfp-btn aicfp-btn-text">
                                                        <?php echo esc_html__('Tout désélectionner', 'ai-content-factory-pro'); ?>
                                                    </button>
                                                    <button type="button" id="aicfp-import-pinterest" class="aicfp-btn aicfp-btn-success">
                                                        <span class="dashicons dashicons-download"></span>
                                                        <?php echo esc_html__('Importer', 'ai-content-factory-pro'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="aicfp-pinterest-grid" class="aicfp-pinterest-grid-modern"></div>
                                        </div>
                                        
                                        <p class="aicfp-hint"><?php echo esc_html__('Recherchez et sélectionnez des images sur Pinterest comme sources pour vos recettes', 'ai-content-factory-pro'); ?></p>
                                    </div>
                                    
                                    <!-- Images de référence alternatives -->
                                    <div class="aicfp-form-group">
                                        <label class="aicfp-label"><?php echo esc_html__('OU uploader vos propres images', 'ai-content-factory-pro'); ?></label>
                                        
                                        <div class="aicfp-tabs-minimal">
                                            <button type="button" class="aicfp-tab-btn active" data-tab="zip">
                                                <span class="dashicons dashicons-media-archive"></span>
                                                <?php echo esc_html__('ZIP', 'ai-content-factory-pro'); ?>
                                            </button>
                                            <button type="button" class="aicfp-tab-btn" data-tab="individual">
                                                <span class="dashicons dashicons-images-alt2"></span>
                                                <?php echo esc_html__('Images', 'ai-content-factory-pro'); ?>
                                            </button>
                                        </div>
                                        
                                        <div class="aicfp-tab-content active" id="tab-zip">
                                            <div class="aicfp-upload-zone">
                                                <input type="file" id="aicfp_reference_zip" name="reference_zip" accept=".zip" class="aicfp-file-input">
                                                <label for="aicfp_reference_zip" class="aicfp-upload-label">
                                                    <span class="dashicons dashicons-upload"></span>
                                                    <span><?php echo esc_html__('Choisir un fichier ZIP', 'ai-content-factory-pro'); ?></span>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="aicfp-tab-content" id="tab-individual">
                                            <div id="aicfp-individual-images">
                                                <div class="aicfp-image-row">
                                                    <input type="file" name="reference_images[]" accept="image/*" class="aicfp-single-image aicfp-file-input">
                                                    <div class="aicfp-preview"></div>
                                                    <button type="button" class="aicfp-btn-remove" style="display:none;">×</button>
                                                </div>
                                            </div>
                                            <button type="button" id="aicfp-add-image-field" class="aicfp-btn aicfp-btn-text">
                                                <span class="dashicons dashicons-plus"></span>
                                                <?php echo esc_html__('Ajouter une image', 'ai-content-factory-pro'); ?>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Email -->
                                    <div class="aicfp-form-group">
                                        <label for="aicfp_email" class="aicfp-label">
                                            <?php echo esc_html__('Email de livraison', 'ai-content-factory-pro'); ?>
                                            <span class="aicfp-required">*</span>
                                        </label>
                                        <input type="email" 
                                               id="aicfp_email" 
                                               name="email" 
                                               class="aicfp-input" 
                                               required
                                               value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>">
                                        <p class="aicfp-hint"><?php echo esc_html__('Vous recevrez un email avec les résultats', 'ai-content-factory-pro'); ?></p>
                                    </div>
                                </div>
                                
                                <!-- Bouton Submit -->
                                <div class="aicfp-form-footer">
                                    <button type="submit" id="aicfp-submit-btn" class="aicfp-btn aicfp-btn-primary aicfp-btn-large">
                                        <span class="dashicons dashicons-hammer"></span>
                                        <span class="aicfp-btn-text"><?php echo esc_html__('Lancer la génération', 'ai-content-factory-pro'); ?></span>
                                    </button>
                                </div>
                            </form>
                            
                            <div id="aicfp-result-message"></div>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="aicfp-col-sidebar">
                        <!-- Estimateur -->
                        <div class="aicfp-card aicfp-card-estimator">
                            <div class="aicfp-card-header">
                                <h3><?php echo esc_html__('Estimation', 'ai-content-factory-pro'); ?></h3>
                            </div>
                            <div class="aicfp-card-body">
                                <div class="aicfp-stat-box">
                                    <span class="aicfp-stat-icon">🍽️</span>
                                    <div class="aicfp-stat-content">
                                        <span class="aicfp-stat-label"><?php echo esc_html__('Recettes', 'ai-content-factory-pro'); ?></span>
                                        <span class="aicfp-stat-value" id="aicfp-estimated-items">-</span>
                                    </div>
                                </div>
                                
                                <div class="aicfp-stat-box">
                                    <span class="aicfp-stat-icon">💰</span>
                                    <div class="aicfp-stat-content">
                                        <span class="aicfp-stat-label"><?php echo esc_html__('Coût estimé', 'ai-content-factory-pro'); ?></span>
                                        <span class="aicfp-stat-value" id="aicfp-estimated-cost">$0.00</span>
                                    </div>
                                </div>
                                
                                <div class="aicfp-stat-box">
                                    <span class="aicfp-stat-icon">⏱️</span>
                                    <div class="aicfp-stat-content">
                                        <span class="aicfp-stat-label"><?php echo esc_html__('Temps estimé', 'ai-content-factory-pro'); ?></span>
                                        <span class="aicfp-stat-value" id="aicfp-estimated-time">0 min</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Aide rapide -->
                        <div class="aicfp-card aicfp-card-help">
                            <div class="aicfp-card-header">
                                <h3><?php echo esc_html__('Aide rapide', 'ai-content-factory-pro'); ?></h3>
                            </div>
                            <div class="aicfp-card-body">
                                <div class="aicfp-help-item">
                                    <span class="dashicons dashicons-info"></span>
                                    <p><?php echo esc_html__('Utilisez le bouton "Suggérer" pour des idées de titres basées sur votre historique', 'ai-content-factory-pro'); ?></p>
                                </div>
                                <div class="aicfp-help-item">
                                    <span class="dashicons dashicons-pinterest"></span>
                                    <p><?php echo esc_html__('Recherchez sur Pinterest pour trouver des images inspirantes', 'ai-content-factory-pro'); ?></p>
                                </div>
                                <div class="aicfp-help-item">
                                    <span class="dashicons dashicons-yes"></span>
                                    <p><?php echo esc_html__('Activez la publication directe pour publier automatiquement l\'article', 'ai-content-factory-pro'); ?></p>
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
