<?php
/**
 * Page Tests Automatiques
 *
 * @package AI_Content_Factory_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class AICFP_Tests_Page {
    
    public static function render() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes.'));
        }
        
        ?>
        <div class="wrap aicfp-modern-page">
            <div class="aicfp-page-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="aicfp-header-content">
                    <h1>
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php echo esc_html__('Tests Automatiques', 'ai-content-factory-pro'); ?>
                    </h1>
                    <p class="aicfp-subtitle"><?php echo esc_html__('Testez toutes les APIs et configurations automatiquement', 'ai-content-factory-pro'); ?></p>
                </div>
            </div>
            
            <div class="aicfp-container">
                <div class="aicfp-card aicfp-card-modern">
                    <div class="aicfp-card-header">
                        <h2>🧪 <?php echo esc_html__('Configuration du Test', 'ai-content-factory-pro'); ?></h2>
                    </div>
                    <div class="aicfp-card-body">
                        <form id="aicfp-test-form">
                            <?php wp_nonce_field('aicfp_test_nonce', 'aicfp_test_nonce'); ?>
                            
                            <div class="aicfp-form-group">
                                <label class="aicfp-label"><?php echo esc_html__('APIs Texte à tester', 'ai-content-factory-pro'); ?></label>
                                <div style="display: flex; gap: 15px;">
                                    <label><input type="checkbox" name="test_text[]" value="chatgpt" checked> ChatGPT</label>
                                    <label><input type="checkbox" name="test_text[]" value="gemini" checked> Gemini</label>
                                    <label><input type="checkbox" name="test_text[]" value="claude"> Claude</label>
                                </div>
                            </div>
                            
                            <div class="aicfp-form-group">
                                <label class="aicfp-label"><?php echo esc_html__('APIs Images à tester', 'ai-content-factory-pro'); ?></label>
                                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                    <label><input type="checkbox" name="test_images[]" value="sdxl-lightning" checked> SDXL Lightning</label>
                                    <label><input type="checkbox" name="test_images[]" value="sdxl-fast" checked> SDXL Fast</label>
                                    <label><input type="checkbox" name="test_images[]" value="dalle"> DALL-E 3</label>
                                    <label><input type="checkbox" name="test_images[]" value="midjourney"> Midjourney</label>
                                </div>
                            </div>
                            
                            <div class="aicfp-form-group">
                                <label class="aicfp-label"><?php echo esc_html__('Nombre de recettes test', 'ai-content-factory-pro'); ?></label>
                                <input type="number" name="test_recipes" value="1" min="1" max="3" class="aicfp-input" style="max-width: 100px;">
                                <p class="aicfp-hint">Recommandé : 1 recette pour économiser les crédits</p>
                            </div>
                            
                            <div class="aicfp-form-group">
                                <button type="submit" class="aicfp-btn aicfp-btn-primary aicfp-btn-large">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    <span class="aicfp-btn-text"><?php echo esc_html__('🚀 Lancer les Tests', 'ai-content-factory-pro'); ?></span>
                                </button>
                            </div>
                        </form>
                        
                        <div id="aicfp-test-results" style="margin-top: 30px;"></div>
                    </div>
                </div>
                
                <div class="aicfp-card aicfp-card-modern">
                    <div class="aicfp-card-header">
                        <h2>📊 <?php echo esc_html__('Historique des Tests', 'ai-content-factory-pro'); ?></h2>
                    </div>
                    <div class="aicfp-card-body">
                        <p style="text-align: center; color: #6b7280;">
                            Les résultats des tests apparaîtront ici après exécution
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#aicfp-test-form').on('submit', function(e) {
                e.preventDefault();
                
                const $results = $('#aicfp-test-results');
                const formData = new FormData(this);
                formData.append('action', 'aicfp_run_automatic_tests');
                
                $results.html('<div style="text-align: center; padding: 40px;"><div class="aicfp-spinner"></div><p>Tests en cours...</p></div>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $results.html(response.data.html);
                        } else {
                            $results.html('<div style="color: red;">Erreur: ' + response.data.message + '</div>');
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
}
