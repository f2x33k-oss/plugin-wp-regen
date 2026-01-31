<?php
/**
 * Page Debug
 *
 * @package AI_Content_Factory_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class AICFP_Debug_Page {
    
    public static function render() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.', 'ai-content-factory-pro'));
        }
        
        // Collecter les informations
        $debug_info = self::collect_debug_info();
        
        ?>
        <div class="wrap aicfp-modern-page">
            <div class="aicfp-page-header" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);">
                <div class="aicfp-header-content">
                    <h1>
                        <span class="dashicons dashicons-admin-tools"></span>
                        <?php echo esc_html__('Mode Debug', 'ai-content-factory-pro'); ?>
                    </h1>
                    <p class="aicfp-subtitle"><?php echo esc_html__('Informations système pour diagnostic et reporting de bugs', 'ai-content-factory-pro'); ?></p>
                </div>
            </div>
            
            <div class="aicfp-container">
                <!-- Actions rapides -->
                <div class="aicfp-card aicfp-card-modern" style="margin-bottom: 20px;">
                    <div class="aicfp-card-header">
                        <h2>🚀 <?php echo esc_html__('Actions Rapides', 'ai-content-factory-pro'); ?></h2>
                    </div>
                    <div class="aicfp-card-body">
                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <button type="button" id="aicfp-copy-debug-info" class="aicfp-btn aicfp-btn-primary">
                                <span class="dashicons dashicons-clipboard"></span>
                                <span class="aicfp-btn-text"><?php echo esc_html__('Copier toutes les infos', 'ai-content-factory-pro'); ?></span>
                            </button>
                            <button type="button" id="aicfp-download-debug" class="aicfp-btn aicfp-btn-secondary">
                                <span class="dashicons dashicons-download"></span>
                                <span class="aicfp-btn-text"><?php echo esc_html__('Télécharger rapport', 'ai-content-factory-pro'); ?></span>
                            </button>
                            <button type="button" id="aicfp-refresh-debug" class="aicfp-btn aicfp-btn-secondary">
                                <span class="dashicons dashicons-update"></span>
                                <span class="aicfp-btn-text"><?php echo esc_html__('Actualiser', 'ai-content-factory-pro'); ?></span>
                            </button>
                            <button type="button" id="aicfp-clear-logs" class="aicfp-btn aicfp-btn-danger">
                                <span class="dashicons dashicons-trash"></span>
                                <span class="aicfp-btn-text"><?php echo esc_html__('Vider les logs', 'ai-content-factory-pro'); ?></span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Informations système -->
                <div class="aicfp-card aicfp-card-modern">
                    <div class="aicfp-card-header">
                        <h2>💻 <?php echo esc_html__('Informations Système', 'ai-content-factory-pro'); ?></h2>
                    </div>
                    <div class="aicfp-card-body">
                        <div id="aicfp-debug-content" class="aicfp-debug-content">
                            <?php echo $debug_info; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Logs récents -->
                <div class="aicfp-card aicfp-card-modern">
                    <div class="aicfp-card-header">
                        <h2>📋 <?php echo esc_html__('Logs Récents (50 dernières lignes)', 'ai-content-factory-pro'); ?></h2>
                    </div>
                    <div class="aicfp-card-body">
                        <pre class="aicfp-logs-viewer"><?php echo self::get_recent_logs(); ?></pre>
                    </div>
                </div>
                
                <!-- Tests automatiques -->
                <div class="aicfp-card aicfp-card-modern">
                    <div class="aicfp-card-header">
                        <h2>🧪 <?php echo esc_html__('Tests Automatiques', 'ai-content-factory-pro'); ?></h2>
                    </div>
                    <div class="aicfp-card-body">
                        <button type="button" id="aicfp-run-tests" class="aicfp-btn aicfp-btn-primary">
                            <span class="dashicons dashicons-yes"></span>
                            <span class="aicfp-btn-text"><?php echo esc_html__('Exécuter les tests', 'ai-content-factory-pro'); ?></span>
                        </button>
                        <div id="aicfp-test-results" style="margin-top: 20px;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .aicfp-debug-content {
                background: #1e1e1e;
                color: #d4d4d4;
                padding: 20px;
                border-radius: 8px;
                font-family: 'Courier New', monospace;
                font-size: 13px;
                line-height: 1.8;
                max-height: 600px;
                overflow-y: auto;
            }
            
            .aicfp-debug-content h3 {
                color: #4ec9b0;
                margin: 20px 0 10px 0;
                font-size: 16px;
            }
            
            .aicfp-debug-content .debug-item {
                margin: 8px 0;
                padding-left: 20px;
            }
            
            .aicfp-debug-content .debug-label {
                color: #9cdcfe;
                font-weight: bold;
            }
            
            .aicfp-debug-content .debug-value {
                color: #ce9178;
            }
            
            .aicfp-debug-content .debug-ok {
                color: #4ec9b0;
            }
            
            .aicfp-debug-content .debug-warning {
                color: #dcdcaa;
            }
            
            .aicfp-debug-content .debug-error {
                color: #f48771;
            }
            
            .aicfp-logs-viewer {
                background: #1e1e1e;
                color: #d4d4d4;
                padding: 20px;
                border-radius: 8px;
                font-family: 'Courier New', monospace;
                font-size: 12px;
                line-height: 1.6;
                max-height: 400px;
                overflow-y: auto;
                white-space: pre-wrap;
                word-wrap: break-word;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Copier les infos
            $('#aicfp-copy-debug-info').on('click', function() {
                const text = $('#aicfp-debug-content').text();
                
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(function() {
                        alert('✅ Informations copiées dans le presse-papier !');
                    });
                } else {
                    // Fallback
                    const $temp = $('<textarea>');
                    $('body').append($temp);
                    $temp.val(text).select();
                    document.execCommand('copy');
                    $temp.remove();
                    alert('✅ Informations copiées !');
                }
            });
            
            // Télécharger rapport
            $('#aicfp-download-debug').on('click', function() {
                const text = $('#aicfp-debug-content').text();
                const blob = new Blob([text], { type: 'text/plain' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'aicfp-debug-report-' + Date.now() + '.txt';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
                alert('✅ Rapport téléchargé !');
            });
            
            // Actualiser
            $('#aicfp-refresh-debug').on('click', function() {
                location.reload();
            });
            
            // Vider les logs
            $('#aicfp-clear-logs').on('click', function() {
                if (confirm('⚠️ Êtes-vous sûr de vouloir vider tous les logs ?')) {
                    $.post(ajaxurl, {
                        action: 'aicfp_clear_logs',
                        nonce: aicfp_ajax.nonce
                    }, function(response) {
                        if (response.success) {
                            alert('✅ Logs vidés avec succès');
                            location.reload();
                        }
                    });
                }
            });
            
            // Exécuter tests
            $('#aicfp-run-tests').on('click', function() {
                const $btn = $(this);
                const $results = $('#aicfp-test-results');
                
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt aicfp-spin"></span> Tests en cours...');
                $results.html('<p>⏳ Exécution des tests automatiques...</p>');
                
                $.post(ajaxurl, {
                    action: 'aicfp_run_tests',
                    nonce: aicfp_ajax.nonce
                }, function(response) {
                    if (response.success) {
                        $results.html('<div style="background: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 8px; font-family: monospace; white-space: pre-wrap;">' + response.data.results + '</div>');
                    } else {
                        $results.html('<p style="color: red;">❌ ' + response.data.message + '</p>');
                    }
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> Exécuter les tests');
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Collecter les informations de debug
     */
    private static function collect_debug_info() {
        global $wpdb;
        
        ob_start();
        ?>
        
        <h3>🔧 Plugin</h3>
        <div class="debug-item">
            <span class="debug-label">Version:</span>
            <span class="debug-value"><?php echo esc_html(AICFP_VERSION); ?></span>
        </div>
        <div class="debug-item">
            <span class="debug-label">Chemin:</span>
            <span class="debug-value"><?php echo esc_html(AICFP_PLUGIN_DIR); ?></span>
        </div>
        
        <h3>💻 Serveur</h3>
        <div class="debug-item">
            <span class="debug-label">PHP Version:</span>
            <span class="debug-value <?php echo version_compare(PHP_VERSION, '7.4', '>=') ? 'debug-ok' : 'debug-error'; ?>">
                <?php echo esc_html(PHP_VERSION); ?>
                <?php echo version_compare(PHP_VERSION, '7.4', '>=') ? ' ✅' : ' ❌ (Minimum 7.4)'; ?>
            </span>
        </div>
        <div class="debug-item">
            <span class="debug-label">MySQL Version:</span>
            <span class="debug-value"><?php echo esc_html($wpdb->db_version()); ?></span>
        </div>
        <div class="debug-item">
            <span class="debug-label">WordPress Version:</span>
            <span class="debug-value"><?php echo esc_html(get_bloginfo('version')); ?></span>
        </div>
        <div class="debug-item">
            <span class="debug-label">Memory Limit:</span>
            <span class="debug-value"><?php echo esc_html(ini_get('memory_limit')); ?></span>
        </div>
        <div class="debug-item">
            <span class="debug-label">Max Execution Time:</span>
            <span class="debug-value"><?php echo esc_html(ini_get('max_execution_time')); ?>s</span>
        </div>
        <div class="debug-item">
            <span class="debug-label">allow_url_fopen:</span>
            <span class="debug-value <?php echo ini_get('allow_url_fopen') ? 'debug-ok' : 'debug-error'; ?>">
                <?php echo ini_get('allow_url_fopen') ? '✅ Activé' : '❌ Désactivé'; ?>
            </span>
        </div>
        <div class="debug-item">
            <span class="debug-label">cURL:</span>
            <span class="debug-value <?php echo function_exists('curl_version') ? 'debug-ok' : 'debug-error'; ?>">
                <?php echo function_exists('curl_version') ? '✅ Disponible' : '❌ Non disponible'; ?>
            </span>
        </div>
        <div class="debug-item">
            <span class="debug-label">ZipArchive:</span>
            <span class="debug-value <?php echo class_exists('ZipArchive') ? 'debug-ok' : 'debug-error'; ?>">
                <?php echo class_exists('ZipArchive') ? '✅ Disponible' : '❌ Non disponible'; ?>
            </span>
        </div>
        
        <h3>🗄️ Base de Données</h3>
        <?php
        $table_name = $wpdb->prefix . 'ai_queue';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        ?>
        <div class="debug-item">
            <span class="debug-label">Table wp_ai_queue:</span>
            <span class="debug-value <?php echo $table_exists ? 'debug-ok' : 'debug-error'; ?>">
                <?php echo $table_exists ? '✅ Existe' : '❌ N\'existe pas'; ?>
            </span>
        </div>
        <?php if ($table_exists): ?>
            <?php
            $columns = $wpdb->get_results("DESCRIBE $table_name");
            $total_tasks = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            $pending = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'pending'");
            $processing = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'processing'");
            $completed = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'completed'");
            ?>
            <div class="debug-item">
                <span class="debug-label">Colonnes:</span>
                <span class="debug-value"><?php echo count($columns); ?></span>
            </div>
            <div class="debug-item">
                <span class="debug-label">Tâches totales:</span>
                <span class="debug-value"><?php echo $total_tasks; ?></span>
            </div>
            <div class="debug-item">
                <span class="debug-label">En attente:</span>
                <span class="debug-value"><?php echo $pending; ?></span>
            </div>
            <div class="debug-item">
                <span class="debug-label">En cours:</span>
                <span class="debug-value"><?php echo $processing; ?></span>
            </div>
            <div class="debug-item">
                <span class="debug-label">Terminées:</span>
                <span class="debug-value"><?php echo $completed; ?></span>
            </div>
        <?php endif; ?>
        
        <h3>🔑 Clés API</h3>
        <?php
        $apis = array(
            'openai' => 'OpenAI',
            'rapidapi' => 'RapidAPI (Midjourney)',
            'pinterest_rapidapi' => 'RapidAPI (Pinterest)',
            'sdxl' => 'SDXL',
            'sdxl_food' => 'SDXL Food LoRA',
            'dalle' => 'DALL-E 3',
            'replicate' => 'Replicate',
            'flux' => 'Flux Pro'
        );
        
        foreach ($apis as $key => $name) {
            $api_key = get_option('aicfp_' . $key . '_api_key');
            $configured = !empty($api_key);
            ?>
            <div class="debug-item">
                <span class="debug-label"><?php echo esc_html($name); ?>:</span>
                <span class="debug-value <?php echo $configured ? 'debug-ok' : 'debug-warning'; ?>">
                    <?php if ($configured): ?>
                        ✅ Configurée (<?php echo strlen($api_key); ?> caractères)
                    <?php else: ?>
                        ⚠️ Non configurée
                    <?php endif; ?>
                </span>
            </div>
            <?php
        }
        ?>
        
        <h3>⏱️ WP-Cron</h3>
        <?php
        $next_cron = wp_next_scheduled('aicfp_process_queue');
        $cron_disabled = defined('DISABLE_WP_CRON') && DISABLE_WP_CRON;
        ?>
        <div class="debug-item">
            <span class="debug-label">Status:</span>
            <span class="debug-value <?php echo $cron_disabled ? 'debug-error' : 'debug-ok'; ?>">
                <?php echo $cron_disabled ? '❌ Désactivé (DISABLE_WP_CRON = true)' : '✅ Activé'; ?>
            </span>
        </div>
        <?php if ($next_cron): ?>
            <div class="debug-item">
                <span class="debug-label">Prochaine exécution:</span>
                <span class="debug-value"><?php echo date('Y-m-d H:i:s', $next_cron); ?></span>
                <span class="debug-value"> (dans <?php echo human_time_diff($next_cron); ?>)</span>
            </div>
        <?php else: ?>
            <div class="debug-item">
                <span class="debug-label">Prochaine exécution:</span>
                <span class="debug-value debug-error">❌ Non planifié</span>
            </div>
        <?php endif; ?>
        
        <h3>📂 Dossiers</h3>
        <?php
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/aicfp-temp/';
        ?>
        <div class="debug-item">
            <span class="debug-label">Uploads:</span>
            <span class="debug-value <?php echo is_writable($upload_dir['basedir']) ? 'debug-ok' : 'debug-error'; ?>">
                <?php echo is_writable($upload_dir['basedir']) ? '✅ Accessible en écriture' : '❌ Non accessible'; ?>
            </span>
        </div>
        <div class="debug-item">
            <span class="debug-label">Temp:</span>
            <span class="debug-value <?php echo file_exists($temp_dir) ? 'debug-ok' : 'debug-warning'; ?>">
                <?php echo file_exists($temp_dir) ? '✅ Existe' : '⚠️ N\'existe pas (sera créé)'; ?>
            </span>
        </div>
        <?php if (file_exists($temp_dir)): ?>
            <div class="debug-item">
                <span class="debug-label">Fichiers temp:</span>
                <span class="debug-value"><?php echo count(glob($temp_dir . '*')); ?></span>
            </div>
        <?php endif; ?>
        
        <h3>🔌 Classes Chargées</h3>
        <?php
        $classes = array(
            'AI_Content_Factory_Pro',
            'AICFP_Database',
            'AICFP_Queue_Manager',
            'AICFP_API_Handler',
            'AICFP_Image_API_Manager',
            'AICFP_Email_Handler',
            'AICFP_File_Handler',
            'AICFP_Ajax_Handler'
        );
        
        foreach ($classes as $class) {
            $exists = class_exists($class);
            ?>
            <div class="debug-item">
                <span class="debug-label"><?php echo esc_html($class); ?>:</span>
                <span class="debug-value <?php echo $exists ? 'debug-ok' : 'debug-error'; ?>">
                    <?php echo $exists ? '✅' : '❌'; ?>
                </span>
            </div>
            <?php
        }
        ?>
        
        <h3>🔗 Hooks AJAX</h3>
        <?php
        global $wp_filter;
        $ajax_actions = array(
            'aicfp_submit_generation',
            'aicfp_submit_album_idees',
            'aicfp_calculate_estimate',
            'aicfp_suggest_titles',
            'aicfp_search_pinterest',
            'aicfp_get_queue_status',
            'aicfp_start_task',
            'aicfp_pause_task',
            'aicfp_resume_task',
            'aicfp_cancel_task',
            'aicfp_delete_task'
        );
        
        foreach ($ajax_actions as $action) {
            $registered = isset($wp_filter['wp_ajax_' . $action]);
            ?>
            <div class="debug-item">
                <span class="debug-label"><?php echo esc_html($action); ?>:</span>
                <span class="debug-value <?php echo $registered ? 'debug-ok' : 'debug-error'; ?>">
                    <?php echo $registered ? '✅' : '❌'; ?>
                </span>
            </div>
            <?php
        }
        ?>
        
        <h3>📊 Statistiques</h3>
        <?php if ($table_exists): ?>
            <?php
            $recent_errors = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE error_log IS NOT NULL AND error_log != ''");
            $avg_time = $wpdb->get_var("SELECT AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) FROM $table_name WHERE status = 'completed' AND started_at IS NOT NULL AND completed_at IS NOT NULL");
            ?>
            <div class="debug-item">
                <span class="debug-label">Tâches avec erreurs:</span>
                <span class="debug-value <?php echo $recent_errors > 0 ? 'debug-warning' : 'debug-ok'; ?>">
                    <?php echo $recent_errors; ?>
                </span>
            </div>
            <div class="debug-item">
                <span class="debug-label">Temps moyen génération:</span>
                <span class="debug-value"><?php echo $avg_time ? round($avg_time, 1) . ' min' : 'N/A'; ?></span>
            </div>
        <?php endif; ?>
        
        <h3>🌐 URLs</h3>
        <div class="debug-item">
            <span class="debug-label">Site URL:</span>
            <span class="debug-value"><?php echo esc_html(get_site_url()); ?></span>
        </div>
        <div class="debug-item">
            <span class="debug-label">Admin URL:</span>
            <span class="debug-value"><?php echo esc_html(admin_url()); ?></span>
        </div>
        <div class="debug-item">
            <span class="debug-label">AJAX URL:</span>
            <span class="debug-value"><?php echo esc_html(admin_url('admin-ajax.php')); ?></span>
        </div>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Obtenir les logs récents
     */
    private static function get_recent_logs() {
        $log_file = WP_CONTENT_DIR . '/debug.log';
        
        if (!file_exists($log_file)) {
            return '📝 Aucun fichier de log trouvé.\n\nPour activer les logs:\n1. Ouvrir wp-config.php\n2. Ajouter:\n   define(\'WP_DEBUG\', true);\n   define(\'WP_DEBUG_LOG\', true);\n   define(\'WP_DEBUG_DISPLAY\', false);';
        }
        
        // Lire les 50 dernières lignes
        $lines = file($log_file);
        $recent = array_slice($lines, -50);
        
        // Filtrer pour garder uniquement les lignes AICFP
        $filtered = array_filter($recent, function($line) {
            return strpos($line, 'AICFP') !== false || 
                   strpos($line, 'PHP Fatal') !== false ||
                   strpos($line, 'PHP Warning') !== false;
        });
        
        if (empty($filtered)) {
            return '✅ Aucune erreur récente trouvée (50 dernières lignes).\n\n' . 
                   'Logs généraux disponibles dans: ' . $log_file . '\n' .
                   'Taille du fichier: ' . size_format(filesize($log_file));
        }
        
        return implode('', $filtered);
    }
}
