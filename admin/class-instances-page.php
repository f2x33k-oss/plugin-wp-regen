<?php
/**
 * Page des instances (file d'attente)
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de la page des instances
 */
class AICFP_Instances_Page {
    
    /**
     * Render la page
     */
    public static function render() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'ai-content-factory-pro'));
        }
        
        ?>
        <div class="wrap aicfp-instances-page">
            <h1><?php echo esc_html__('File d\'attente - AI Content Factory Pro', 'ai-content-factory-pro'); ?></h1>
            
            <!-- Statistiques -->
            <div class="aicfp-stats-grid" id="aicfp-stats-grid">
                <div class="aicfp-stat-card">
                    <div class="aicfp-stat-label"><?php echo esc_html__('En attente', 'ai-content-factory-pro'); ?></div>
                    <div class="aicfp-stat-value" id="stat-pending">0</div>
                </div>
                <div class="aicfp-stat-card aicfp-stat-processing">
                    <div class="aicfp-stat-label"><?php echo esc_html__('En cours', 'ai-content-factory-pro'); ?></div>
                    <div class="aicfp-stat-value" id="stat-processing">0</div>
                </div>
                <div class="aicfp-stat-card aicfp-stat-completed">
                    <div class="aicfp-stat-label"><?php echo esc_html__('Terminées', 'ai-content-factory-pro'); ?></div>
                    <div class="aicfp-stat-value" id="stat-completed">0</div>
                </div>
                <div class="aicfp-stat-card aicfp-stat-paused">
                    <div class="aicfp-stat-label"><?php echo esc_html__('En pause', 'ai-content-factory-pro'); ?></div>
                    <div class="aicfp-stat-value" id="stat-paused">0</div>
                </div>
            </div>
            
            <!-- Liste des tâches -->
            <div class="aicfp-card">
                <div class="aicfp-card-header">
                    <h2><?php echo esc_html__('Tâches', 'ai-content-factory-pro'); ?></h2>
                    <button class="button" id="aicfp-refresh-tasks">
                        <span class="dashicons dashicons-update"></span>
                        <?php echo esc_html__('Actualiser', 'ai-content-factory-pro'); ?>
                    </button>
                </div>
                
                <div id="aicfp-tasks-container">
                    <p class="aicfp-loading"><?php echo esc_html__('Chargement...', 'ai-content-factory-pro'); ?></p>
                </div>
            </div>
        </div>
        
        <style>
            .aicfp-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            
            .aicfp-stat-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 5px;
                padding: 20px;
                text-align: center;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            
            .aicfp-stat-label {
                font-size: 12px;
                color: #666;
                text-transform: uppercase;
                margin-bottom: 10px;
            }
            
            .aicfp-stat-value {
                font-size: 32px;
                font-weight: bold;
                color: #1d2327;
            }
            
            .aicfp-stat-processing {
                border-left: 4px solid #2271b1;
            }
            
            .aicfp-stat-completed {
                border-left: 4px solid #00a32a;
            }
            
            .aicfp-stat-paused {
                border-left: 4px solid #dba617;
            }
            
            .aicfp-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
                margin-bottom: 20px;
            }
            
            .aicfp-card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px;
                border-bottom: 1px solid #eee;
            }
            
            .aicfp-card-header h2 {
                margin: 0;
            }
            
            .aicfp-task-item {
                padding: 20px;
                border-bottom: 1px solid #eee;
            }
            
            .aicfp-task-item:last-child {
                border-bottom: none;
            }
            
            .aicfp-task-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 15px;
            }
            
            .aicfp-task-title {
                font-size: 16px;
                font-weight: 600;
                color: #1d2327;
                margin-bottom: 5px;
            }
            
            .aicfp-task-meta {
                font-size: 12px;
                color: #666;
            }
            
            .aicfp-task-status {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
            }
            
            .aicfp-status-pending {
                background: #f0f0f1;
                color: #50575e;
            }
            
            .aicfp-status-processing {
                background: #d7f0ff;
                color: #0c5d8c;
            }
            
            .aicfp-status-completed {
                background: #d7f0db;
                color: #006700;
            }
            
            .aicfp-status-paused {
                background: #fcf0c3;
                color: #8a6d00;
            }
            
            .aicfp-status-cancelled {
                background: #fcf0f1;
                color: #8c0000;
            }
            
            .aicfp-progress-bar {
                height: 8px;
                background: #f0f0f1;
                border-radius: 4px;
                overflow: hidden;
                margin: 10px 0;
            }
            
            .aicfp-progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #2271b1 0%, #135e96 100%);
                transition: width 0.3s ease;
            }
            
            .aicfp-progress-text {
                font-size: 12px;
                color: #666;
                margin-top: 5px;
            }
            
            .aicfp-task-actions {
                display: flex;
                gap: 8px;
                margin-top: 15px;
            }
            
            .aicfp-task-actions .button {
                font-size: 12px;
                height: auto;
                padding: 4px 12px;
            }
            
            .aicfp-loading {
                text-align: center;
                padding: 40px;
                color: #666;
            }
            
            .aicfp-no-tasks {
                text-align: center;
                padding: 40px;
                color: #666;
            }
            
            #aicfp-refresh-tasks .dashicons {
                line-height: 28px;
            }
        </style>
        <?php
    }
}
