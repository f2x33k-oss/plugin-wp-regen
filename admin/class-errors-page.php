<?php
/**
 * Page Erreurs
 *
 * @package AI_Content_Factory_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class AICFP_Errors_Page {
    
    public static function render() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes.'));
        }
        
        global $wpdb;
        $table_name = AICFP_Database::get_table_name();
        
        // Récupérer toutes les tâches avec erreurs
        $tasks_with_errors = $wpdb->get_results(
            "SELECT * FROM $table_name 
             WHERE error_log IS NOT NULL AND error_log != '' 
             ORDER BY updated_at DESC 
             LIMIT 50"
        );
        
        ?>
        <div class="wrap aicfp-modern-page">
            <div class="aicfp-page-header" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);">
                <div class="aicfp-header-content">
                    <h1>
                        <span class="dashicons dashicons-warning"></span>
                        <?php echo esc_html__('Journal des Erreurs', 'ai-content-factory-pro'); ?>
                    </h1>
                    <p class="aicfp-subtitle"><?php echo esc_html__('Toutes les erreurs de génération en un seul endroit', 'ai-content-factory-pro'); ?></p>
                </div>
            </div>
            
            <div class="aicfp-container">
                <div class="aicfp-card aicfp-card-modern">
                    <div class="aicfp-card-header">
                        <h2>⚠️ <?php echo esc_html__('Erreurs Récentes', 'ai-content-factory-pro'); ?></h2>
                        <span style="color: #d63638; font-weight: 600;">
                            <?php echo count($tasks_with_errors); ?> tâche(s) avec erreurs
                        </span>
                    </div>
                    <div class="aicfp-card-body">
                        <?php if (empty($tasks_with_errors)): ?>
                            <div style="text-align: center; padding: 60px 20px;">
                                <p style="font-size: 18px; color: #00a32a;">✅ Aucune erreur enregistrée</p>
                                <p style="color: #6b7280;">Le plugin fonctionne parfaitement !</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($tasks_with_errors as $task): ?>
                                <?php
                                $error_log = maybe_unserialize($task->error_log);
                                if (!is_array($error_log)) continue;
                                ?>
                                <div class="aicfp-error-card" style="background: #fff; border-left: 4px solid #d63638; padding: 20px; margin-bottom: 15px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                                        <div>
                                            <h3 style="margin: 0; font-size: 16px; color: #1d2327;">
                                                <?php echo esc_html($task->title); ?>
                                            </h3>
                                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #6b7280;">
                                                Tâche #<?php echo $task->id; ?> • <?php echo $task->updated_at; ?>
                                            </p>
                                        </div>
                                        <span style="background: #fcf0f1; color: #d63638; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                            <?php echo count($error_log); ?> erreur(s)
                                        </span>
                                    </div>
                                    
                                    <div style="background: #fcf0f1; padding: 15px; border-radius: 6px;">
                                        <?php foreach ($error_log as $error): ?>
                                            <div style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #f5c2c7;">
                                                <small style="color: #6b7280; font-weight: 600;">
                                                    <?php echo esc_html($error['time']); ?>
                                                </small>
                                                <p style="margin: 5px 0 0 0; color: #d63638; font-weight: 500;">
                                                    <?php echo esc_html($error['message']); ?>
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
