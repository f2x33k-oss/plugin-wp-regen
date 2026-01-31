<?php
/**
 * Gestionnaire d'emails
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de gestion des emails
 */
class AICFP_Email_Handler {
    
    /**
     * Envoyer l'email de démarrage
     */
    public static function send_start_email($task) {
        if (!get_option('aicfp_email_notifications_enabled', true)) {
            return;
        }
        
        $to = $task->email;
        $album_type = get_post_meta($task->id, '_aicfp_album_type', true) ?: 'recettes';
        $image_api = get_post_meta($task->id, '_aicfp_image_api', true) ?: 'midjourney';
        
        $subject = '🚀 Génération lancée - ' . $task->title;
        
        $message = self::build_start_email_template($task, $album_type, $image_api);
        
        self::configure_smtp();
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Envoyer l'email de complétion
     */
    public static function send_completion_email($task) {
        // Récupérer les paramètres SMTP
        self::configure_smtp();
        
        $to = $task->email;
        
        // Déterminer le type d'album
        $album_type = get_post_meta($task->id, '_aicfp_album_type', true) ?: 'recettes';
        $type_label = array(
            'recettes' => '🍽️ Album Recettes',
            'idees' => '💡 Album Idées',
            'videos' => '🎬 Vidéos'
        );
        
        $subject = '🎉 Génération terminée ! Votre ' . ($type_label[$album_type] ?? 'album') . ' est prêt !';
        
        // Construire le contenu de l'email
        $message = self::build_completion_email_template($task, $album_type);
        
        // Headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8'
        );
        
        // Envoyer via Gmail API si configuré, sinon wp_mail
        if (get_option('aicfp_use_gmail_api', false)) {
            $sent = AICFP_Google_Services::send_via_gmail($to, $subject, $message);
        } else {
            $sent = wp_mail($to, $subject, $message, $headers);
        }
        
        if (!$sent) {
            error_log('AICFP: Échec d\'envoi de l\'email pour la tâche #' . $task->id);
        }
        
        return $sent;
    }
    
    /**
     * Configurer SMTP
     */
    private static function configure_smtp() {
        $smtp_enabled = get_option('aicfp_smtp_enabled', false);
        
        if (!$smtp_enabled) {
            return;
        }
        
        add_action('phpmailer_init', function($phpmailer) {
            $phpmailer->isSMTP();
            $phpmailer->Host = get_option('aicfp_smtp_host', '');
            $phpmailer->SMTPAuth = true;
            $phpmailer->Port = get_option('aicfp_smtp_port', 587);
            $phpmailer->Username = get_option('aicfp_smtp_username', '');
            $phpmailer->Password = get_option('aicfp_smtp_password', '');
            $phpmailer->SMTPSecure = get_option('aicfp_smtp_encryption', 'tls');
            $phpmailer->From = get_option('aicfp_smtp_from_email', get_option('admin_email'));
            $phpmailer->FromName = get_option('aicfp_smtp_from_name', get_bloginfo('name'));
        });
    }
    
    /**
     * Template email de démarrage
     */
    private static function build_start_email_template($task, $album_type, $image_api) {
        $instances_url = admin_url('admin.php?page=aicfp-instances&highlight=' . $task->id);
        $api_info = AICFP_Image_API_Manager::get_api_info($image_api);
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { background: white; padding: 30px; border: 1px solid #ddd; border-top: none; }
                .info-box { background: #f0f6fc; border-left: 4px solid #2271b1; padding: 15px; margin: 20px 0; border-radius: 4px; }
                .info-item { margin: 10px 0; }
                .info-label { font-weight: 600; color: #1d2327; }
                .button { display: inline-block; padding: 12px 24px; background: #2271b1; color: white; text-decoration: none; border-radius: 6px; margin: 10px 5px 0 0; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; text-align: center; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>🚀 Génération Lancée !</h1>
            </div>
            <div class="content">
                <p>Bonjour,</p>
                <p>Votre génération a été ajoutée à la file d'attente et va démarrer sous peu.</p>
                
                <div class="info-box">
                    <div class="info-item">
                        <span class="info-label">📝 Titre :</span> <?php echo esc_html($task->title); ?>
                    </div>
                    <div class="info-item">
                        <span class="info-label">🎨 API utilisée :</span> <?php echo esc_html($api_info['name']); ?>
                    </div>
                    <div class="info-item">
                        <span class="info-label">💰 Coût estimé :</span> $<?php echo number_format($task->cost_estimate, 2); ?>
                    </div>
                    <div class="info-item">
                        <span class="info-label">⏱️ Temps estimé :</span> <?php echo $task->time_estimate; ?> minutes
                    </div>
                    <div class="info-item">
                        <span class="info-label">🍽️ Items :</span> <?php echo $task->total_items; ?>
                    </div>
                </div>
                
                <p>Vous pouvez suivre la progression en temps réel :</p>
                <a href="<?php echo esc_url($instances_url); ?>" class="button">📊 Voir le suivi en temps réel</a>
                
                <p style="margin-top: 30px; font-size: 14px; color: #666;">
                    Vous recevrez un nouvel email dès que la génération sera terminée avec les liens de téléchargement.
                </p>
            </div>
            <div class="footer">
                <p>AI Content Factory Pro - <?php echo esc_html(get_bloginfo('name')); ?></p>
                <p><?php echo esc_url(home_url()); ?></p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Template email de complétion
     */
    private static function build_completion_email_template($task, $album_type) {
        $generated_images = maybe_unserialize($task->generated_images);
        $generated_content = maybe_unserialize($task->generated_content);
        $image_api = get_post_meta($task->id, '_aicfp_image_api', true) ?: 'midjourney';
        $api_info = AICFP_Image_API_Manager::get_api_info($image_api);
        
        // URLs Google Drive et Docs (si configurés)
        $google_doc_url = get_post_meta($task->id, '_aicfp_google_doc_url', true);
        $google_drive_url = get_post_meta($task->id, '_aicfp_google_drive_url', true);
        
        // URL de l'article WordPress
        $article_url = $task->post_id ? get_edit_post_link($task->post_id) : null;
        $article_view_url = $task->post_id ? get_permalink($task->post_id) : null;
        
        // Historique des 5 dernières générations
        global $wpdb;
        $table_name = AICFP_Database::get_table_name();
        $history = $wpdb->get_results($wpdb->prepare(
            "SELECT id, title, status, created_at, completed_at FROM $table_name 
             WHERE status = 'completed' AND id <= %d 
             ORDER BY completed_at DESC LIMIT 5",
            $task->id
        ));
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1d2327; max-width: 650px; margin: 0 auto; padding: 0; background: #f0f0f1; }
                .email-container { background: white; margin: 20px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #00a32a 0%, #008a24 100%); color: white; padding: 40px 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; font-weight: 700; }
                .header p { margin: 10px 0 0 0; opacity: 0.95; font-size: 16px; }
                .content { padding: 35px 30px; }
                .success-banner { background: #d7f0db; border-left: 5px solid #00a32a; padding: 20px; margin: 25px 0; border-radius: 6px; }
                .success-banner h2 { margin: 0 0 10px 0; color: #006700; font-size: 20px; }
                .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 25px 0; }
                .info-card { background: #f9fafb; padding: 18px; border-radius: 8px; border: 1px solid #e5e7eb; }
                .info-label { font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: 600; margin-bottom: 6px; }
                .info-value { font-size: 20px; font-weight: 700; color: #1d2327; }
                .download-section { background: linear-gradient(135deg, #e7f5fe 0%, #f0f9ff 100%); padding: 25px; margin: 25px 0; border-radius: 10px; border: 2px solid #2271b1; }
                .download-section h3 { margin: 0 0 20px 0; color: #0c5d8c; font-size: 18px; }
                .button { display: inline-block; padding: 14px 28px; background: #2271b1; color: white; text-decoration: none; border-radius: 8px; margin: 8px 8px 8px 0; font-weight: 600; font-size: 15px; transition: all 0.2s; }
                .button:hover { background: #135e96; transform: translateY(-2px); }
                .button-success { background: #00a32a; }
                .button-success:hover { background: #008a24; }
                .history { background: #f9fafb; padding: 20px; border-radius: 8px; margin: 25px 0; }
                .history h3 { margin: 0 0 15px 0; color: #1d2327; font-size: 16px; }
                .history-item { padding: 12px; background: white; margin: 8px 0; border-radius: 6px; border-left: 3px solid #00a32a; }
                .footer { background: #f9fafb; padding: 25px 30px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb; }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="header">
                    <h1>🎉 Génération Terminée !</h1>
                    <p>Votre <?php echo esc_html($album_type === 'idees' ? 'album d\'idées' : 'album recettes'); ?> est prêt !</p>
                </div>
                
                <div class="content">
                    <div class="success-banner">
                        <h2>✅ Génération réussie</h2>
                        <p style="margin: 5px 0 0 0; color: #006700;"><?php echo esc_html($task->title); ?></p>
                    </div>
                    
                    <!-- Résumé -->
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-label">🎨 API Utilisée</div>
                            <div class="info-value"><?php echo esc_html($api_info['name']); ?></div>
                        </div>
                        <div class="info-card">
                            <div class="info-label">💰 Coût Total</div>
                            <div class="info-value">$<?php echo number_format($task->cost_estimate, 2); ?></div>
                        </div>
                        <div class="info-card">
                            <div class="info-label">⏱️ Temps Réel</div>
                            <div class="info-value"><?php echo self::calculate_real_time($task); ?> min</div>
                        </div>
                        <div class="info-card">
                            <div class="info-label">🍽️ Items Générés</div>
                            <div class="info-value"><?php echo $task->total_items; ?></div>
                        </div>
                    </div>
                    
                    <!-- Téléchargements -->
                    <div class="download-section">
                        <h3>📦 Téléchargements</h3>
                        
                        <?php if (!empty($google_drive_url)): ?>
                            <a href="<?php echo esc_url($google_drive_url); ?>" class="button button-success">
                                📁 Télécharger les images (Google Drive)
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($google_doc_url)): ?>
                            <a href="<?php echo esc_url($google_doc_url); ?>" class="button">
                                📄 Ouvrir le document (Google Docs)
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($article_url): ?>
                            <a href="<?php echo esc_url($article_url); ?>" class="button">
                                ✏️ Éditer l'article WordPress
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($article_view_url): ?>
                            <a href="<?php echo esc_url($article_view_url); ?>" class="button">
                                👁️ Voir l'article publié
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Suivi -->
                    <p style="text-align: center; margin: 25px 0;">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=aicfp-instances')); ?>" class="button">
                            📊 Voir le suivi des générations
                        </a>
                    </p>
                    
                    <!-- Historique -->
                    <?php if (!empty($history)): ?>
                        <div class="history">
                            <h3>📚 Historique récent</h3>
                            <?php foreach ($history as $item): ?>
                                <div class="history-item">
                                    <strong><?php echo esc_html($item->title); ?></strong>
                                    <br>
                                    <small style="color: #6b7280;">
                                        Terminé le <?php echo date('d/m/Y à H:i', strtotime($item->completed_at)); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="footer">
                    <p><strong>AI Content Factory Pro</strong></p>
                    <p><?php echo esc_html(get_bloginfo('name')); ?> - <?php echo esc_url(home_url()); ?></p>
                    <p style="margin-top: 15px;">
                        Cet email a été envoyé automatiquement. Ne pas répondre.
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Calculer le temps réel de génération
     */
    private static function calculate_real_time($task) {
        if ($task->started_at && $task->completed_at) {
            $start = strtotime($task->started_at);
            $end = strtotime($task->completed_at);
            return round(($end - $start) / 60, 1);
        }
        return $task->time_estimate;
    }
    
    /**
     * Construire le contenu de l'email (ancienne version)
     */
    private static function build_email_content($task) {
        $generated_images = maybe_unserialize($task->generated_images);
        $prompts_log = maybe_unserialize($task->prompts_log);
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                }
                h1 {
                    color: #0073aa;
                    border-bottom: 2px solid #0073aa;
                    padding-bottom: 10px;
                }
                h2 {
                    color: #23282d;
                    margin-top: 30px;
                }
                .info-box {
                    background: #f5f5f5;
                    padding: 15px;
                    border-left: 4px solid #0073aa;
                    margin: 20px 0;
                }
                .image-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                    gap: 15px;
                    margin: 20px 0;
                }
                .image-item {
                    border: 1px solid #ddd;
                    padding: 10px;
                    border-radius: 5px;
                }
                .image-item img {
                    max-width: 100%;
                    height: auto;
                    border-radius: 3px;
                }
                .prompt-item {
                    background: #fff;
                    border: 1px solid #ddd;
                    padding: 10px;
                    margin: 10px 0;
                    border-radius: 5px;
                }
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    background: #0073aa;
                    color: #fff;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 10px 5px 10px 0;
                }
                .footer {
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    font-size: 12px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <h1><?php echo esc_html__('Tâche terminée avec succès !', 'ai-content-factory-pro'); ?></h1>
            
            <div class="info-box">
                <strong><?php echo esc_html__('Titre:', 'ai-content-factory-pro'); ?></strong> <?php echo esc_html($task->title); ?><br>
                <strong><?php echo esc_html__('Date de création:', 'ai-content-factory-pro'); ?></strong> <?php echo esc_html($task->created_at); ?><br>
                <strong><?php echo esc_html__('Date de complétion:', 'ai-content-factory-pro'); ?></strong> <?php echo esc_html($task->completed_at); ?><br>
                <strong><?php echo esc_html__('Items générés:', 'ai-content-factory-pro'); ?></strong> <?php echo esc_html($task->total_items); ?>
            </div>
            
            <?php if ($task->generate_text && $task->post_id): ?>
                <h2><?php echo esc_html__('Article créé', 'ai-content-factory-pro'); ?></h2>
                <p><?php echo esc_html__('Un article brouillon a été créé dans votre WordPress.', 'ai-content-factory-pro'); ?></p>
                <a href="<?php echo esc_url(get_edit_post_link($task->post_id)); ?>" class="button">
                    <?php echo esc_html__('Voir l\'article', 'ai-content-factory-pro'); ?>
                </a>
            <?php endif; ?>
            
            <?php if (!empty($generated_images)): ?>
                <h2><?php echo esc_html__('Images générées', 'ai-content-factory-pro'); ?></h2>
                <div class="image-grid">
                    <?php foreach ($generated_images as $image): ?>
                        <div class="image-item">
                            <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['prompt']); ?>">
                            <p style="font-size: 12px; margin: 5px 0 0 0;">
                                <strong><?php echo esc_html__('Item', 'ai-content-factory-pro'); ?> <?php echo esc_html($image['item']); ?></strong><br>
                                <a href="<?php echo esc_url($image['url']); ?>" target="_blank"><?php echo esc_html__('Voir l\'image', 'ai-content-factory-pro'); ?></a>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($prompts_log)): ?>
                <h2><?php echo esc_html__('Prompts utilisés', 'ai-content-factory-pro'); ?></h2>
                <?php foreach ($prompts_log as $prompt): ?>
                    <div class="prompt-item">
                        <strong><?php echo esc_html__('Item', 'ai-content-factory-pro'); ?> <?php echo esc_html($prompt['item']); ?>:</strong><br>
                        <?php echo esc_html($prompt['prompt']); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="footer">
                <p><?php echo esc_html__('Cet email a été envoyé automatiquement par AI Content Factory Pro.', 'ai-content-factory-pro'); ?></p>
                <p><?php echo esc_html(get_bloginfo('name')); ?> - <?php echo esc_url(home_url()); ?></p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
