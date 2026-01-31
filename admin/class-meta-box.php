<?php
/**
 * Meta Box pour l'éditeur d'articles
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de la Meta Box
 */
class AICFP_Meta_Box {
    
    /**
     * Constructeur
     */
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
    }
    
    /**
     * Ajouter la Meta Box
     */
    public function add_meta_box() {
        add_meta_box(
            'aicfp_generation_info',
            __('AI Content Factory - Informations de génération', 'ai-content-factory-pro'),
            array($this, 'render_meta_box'),
            'post',
            'side',
            'default'
        );
    }
    
    /**
     * Render la Meta Box
     */
    public function render_meta_box($post) {
        // Vérifier si cet article a été généré par le plugin
        $task_id = get_post_meta($post->ID, '_aicfp_task_id', true);
        
        if (!$task_id) {
            ?>
            <p><?php echo esc_html__('Cet article n\'a pas été généré par AI Content Factory Pro.', 'ai-content-factory-pro'); ?></p>
            <?php
            return;
        }
        
        // Récupérer les informations de la tâche
        $task = AICFP_Database::get_task($task_id);
        
        if (!$task) {
            ?>
            <p><?php echo esc_html__('Informations de génération non disponibles.', 'ai-content-factory-pro'); ?></p>
            <?php
            return;
        }
        
        $generated_images = get_post_meta($post->ID, '_aicfp_generated_images', true);
        $prompts_log = get_post_meta($post->ID, '_aicfp_prompts_log', true);
        
        ?>
        <div class="aicfp-meta-box">
            <div class="aicfp-meta-section">
                <h4><?php echo esc_html__('Informations générales', 'ai-content-factory-pro'); ?></h4>
                <table class="aicfp-meta-table">
                    <tr>
                        <td><strong><?php echo esc_html__('ID de tâche:', 'ai-content-factory-pro'); ?></strong></td>
                        <td>#<?php echo esc_html($task->id); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo esc_html__('Date de génération:', 'ai-content-factory-pro'); ?></strong></td>
                        <td><?php echo esc_html($task->completed_at); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo esc_html__('Items générés:', 'ai-content-factory-pro'); ?></strong></td>
                        <td><?php echo esc_html($task->total_items); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo esc_html__('Coût:', 'ai-content-factory-pro'); ?></strong></td>
                        <td>$<?php echo esc_html(number_format($task->cost_estimate, 2)); ?></td>
                    </tr>
                </table>
            </div>
            
            <?php if (!empty($generated_images) && is_array($generated_images)): ?>
                <div class="aicfp-meta-section">
                    <h4><?php echo esc_html__('Images générées', 'ai-content-factory-pro'); ?></h4>
                    <div class="aicfp-images-grid">
                        <?php foreach ($generated_images as $image): ?>
                            <div class="aicfp-image-item">
                                <a href="<?php echo esc_url($image['url']); ?>" target="_blank">
                                    <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['prompt']); ?>">
                                </a>
                                <div class="aicfp-image-number">#<?php echo esc_html($image['item']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($prompts_log) && is_array($prompts_log)): ?>
                <div class="aicfp-meta-section">
                    <h4><?php echo esc_html__('Journal des prompts', 'ai-content-factory-pro'); ?></h4>
                    <div class="aicfp-prompts-log">
                        <?php foreach ($prompts_log as $log): ?>
                            <div class="aicfp-prompt-item">
                                <strong><?php echo esc_html__('Item', 'ai-content-factory-pro'); ?> <?php echo esc_html($log['item']); ?>:</strong>
                                <p><?php echo esc_html($log['prompt']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($task->error_log): ?>
                <?php $errors = maybe_unserialize($task->error_log); ?>
                <?php if (!empty($errors)): ?>
                    <div class="aicfp-meta-section aicfp-meta-errors">
                        <h4><?php echo esc_html__('Erreurs', 'ai-content-factory-pro'); ?></h4>
                        <div class="aicfp-error-list">
                            <?php foreach ($errors as $error): ?>
                                <div class="aicfp-error-item">
                                    <small><?php echo esc_html($error['time']); ?></small>
                                    <p><?php echo esc_html($error['message']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="aicfp-meta-section">
                <a href="<?php echo esc_url(admin_url('admin.php?page=aicfp-instances')); ?>" class="button button-secondary button-large" style="width: 100%;">
                    <?php echo esc_html__('Voir toutes les tâches', 'ai-content-factory-pro'); ?>
                </a>
            </div>
        </div>
        
        <style>
            .aicfp-meta-box {
                margin: -6px -12px -12px;
            }
            
            .aicfp-meta-section {
                padding: 15px;
                border-bottom: 1px solid #f0f0f1;
            }
            
            .aicfp-meta-section:last-child {
                border-bottom: none;
            }
            
            .aicfp-meta-section h4 {
                margin: 0 0 10px 0;
                font-size: 13px;
                font-weight: 600;
                color: #1d2327;
            }
            
            .aicfp-meta-table {
                width: 100%;
                font-size: 12px;
            }
            
            .aicfp-meta-table td {
                padding: 5px 0;
            }
            
            .aicfp-meta-table td:first-child {
                width: 40%;
            }
            
            .aicfp-images-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .aicfp-image-item {
                position: relative;
                aspect-ratio: 1;
                overflow: hidden;
                border-radius: 4px;
                border: 1px solid #ddd;
            }
            
            .aicfp-image-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .aicfp-image-number {
                position: absolute;
                bottom: 5px;
                right: 5px;
                background: rgba(0,0,0,0.7);
                color: white;
                padding: 2px 8px;
                border-radius: 10px;
                font-size: 11px;
                font-weight: bold;
            }
            
            .aicfp-prompts-log {
                max-height: 300px;
                overflow-y: auto;
            }
            
            .aicfp-prompt-item {
                background: #f6f7f7;
                padding: 10px;
                margin-bottom: 8px;
                border-radius: 4px;
                font-size: 12px;
            }
            
            .aicfp-prompt-item strong {
                display: block;
                margin-bottom: 5px;
                color: #1d2327;
            }
            
            .aicfp-prompt-item p {
                margin: 0;
                color: #666;
                line-height: 1.4;
            }
            
            .aicfp-meta-errors {
                background: #fcf0f1;
            }
            
            .aicfp-error-list {
                max-height: 200px;
                overflow-y: auto;
            }
            
            .aicfp-error-item {
                background: #fff;
                padding: 8px;
                margin-bottom: 8px;
                border-radius: 4px;
                border-left: 3px solid #d63638;
                font-size: 12px;
            }
            
            .aicfp-error-item small {
                display: block;
                color: #666;
                margin-bottom: 3px;
            }
            
            .aicfp-error-item p {
                margin: 0;
                color: #d63638;
            }
        </style>
        <?php
    }
}
