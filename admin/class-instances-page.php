<?php
/**
 * Page Instances - Version moderne
 *
 * @package AI_Content_Factory_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class AICFP_Instances_Page {
    
    public static function render() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.', 'ai-content-factory-pro'));
        }
        
        ?>
        <div class="wrap aicfp-modern-page aicfp-instances-modern">
            <!-- Header -->
            <div class="aicfp-page-header" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="aicfp-header-content">
                    <h1>
                        <span class="dashicons dashicons-list-view"></span>
                        <?php echo esc_html__('File d\'attente', 'ai-content-factory-pro'); ?>
                    </h1>
                    <p class="aicfp-subtitle"><?php echo esc_html__('Suivez la progression de vos générations en temps réel', 'ai-content-factory-pro'); ?></p>
                </div>
            </div>
            
            <div class="aicfp-container">
                <!-- Stats -->
                <div class="aicfp-stats-modern">
                    <div class="aicfp-stat-card-modern stat-pending">
                        <div class="aicfp-stat-label-modern"><?php echo esc_html__('En attente', 'ai-content-factory-pro'); ?></div>
                        <span class="aicfp-stat-number" id="stat-pending">0</span>
                    </div>
                    <div class="aicfp-stat-card-modern stat-processing">
                        <div class="aicfp-stat-label-modern"><?php echo esc_html__('En cours', 'ai-content-factory-pro'); ?></div>
                        <span class="aicfp-stat-number" id="stat-processing">0</span>
                    </div>
                    <div class="aicfp-stat-card-modern stat-completed">
                        <div class="aicfp-stat-label-modern"><?php echo esc_html__('Terminées', 'ai-content-factory-pro'); ?></div>
                        <span class="aicfp-stat-number" id="stat-completed">0</span>
                    </div>
                    <div class="aicfp-stat-card-modern stat-paused">
                        <div class="aicfp-stat-label-modern"><?php echo esc_html__('En pause', 'ai-content-factory-pro'); ?></div>
                        <span class="aicfp-stat-number" id="stat-paused">0</span>
                    </div>
                </div>
                
                <!-- Liste des tâches -->
                <div class="aicfp-card aicfp-card-modern">
                    <div class="aicfp-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h2><?php echo esc_html__('Tâches', 'ai-content-factory-pro'); ?></h2>
                        <button id="aicfp-refresh-tasks" class="aicfp-btn aicfp-btn-secondary">
                            <span class="dashicons dashicons-update"></span>
                            <span class="aicfp-btn-text"><?php echo esc_html__('Actualiser', 'ai-content-factory-pro'); ?></span>
                        </button>
                    </div>
                    <div class="aicfp-card-body">
                        <div id="aicfp-tasks-container">
                            <div class="aicfp-loading-overlay">
                                <div class="aicfp-spinner"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .aicfp-task-card {
                background: white;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 15px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                transition: all 0.3s;
                animation: fadeIn 0.5s ease;
            }
            
            .aicfp-task-card:hover {
                box-shadow: 0 4px 16px rgba(0,0,0,0.12);
                transform: translateY(-2px);
            }
            
            .aicfp-task-header-modern {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 15px;
            }
            
            .aicfp-task-title-modern {
                font-size: 18px;
                font-weight: 600;
                color: #1d2327;
                margin-bottom: 8px;
            }
            
            .aicfp-task-meta-modern {
                font-size: 13px;
                color: #50575e;
            }
            
            .aicfp-task-actions-modern {
                display: flex;
                gap: 10px;
                margin-top: 15px;
                flex-wrap: wrap;
            }
            
            .aicfp-empty-state {
                text-align: center;
                padding: 60px 20px;
                color: #666;
            }
            
            .aicfp-empty-state p {
                font-size: 18px;
                margin: 0;
            }
        </style>
        <?php
    }
}
