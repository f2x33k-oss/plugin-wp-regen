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
     * Envoyer l'email de complétion
     */
    public static function send_completion_email($task) {
        // Récupérer les paramètres SMTP
        self::configure_smtp();
        
        $to = $task->email;
        $subject = sprintf(
            __('[AI Content Factory Pro] Tâche terminée: %s', 'ai-content-factory-pro'),
            $task->title
        );
        
        // Construire le contenu de l'email
        $message = self::build_email_content($task);
        
        // Headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8'
        );
        
        // Envoyer l'email
        $sent = wp_mail($to, $subject, $message, $headers);
        
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
     * Construire le contenu de l'email
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
