<?php
/**
 * Page de réglages
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de la page de réglages
 */
class AICFP_Settings_Page {
    
    /**
     * Render la page
     */
    public static function render() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'ai-content-factory-pro'));
        }
        
        // Enregistrer les paramètres
        if (isset($_POST['aicfp_save_settings'])) {
            check_admin_referer('aicfp_settings_nonce');
            self::save_settings();
        }
        
        ?>
        <div class="wrap aicfp-settings-page">
            <h1><?php echo esc_html__('Réglages - AI Content Factory Pro', 'ai-content-factory-pro'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('aicfp_settings_nonce'); ?>
                
                <!-- API Keys Section -->
                <div class="aicfp-card">
                    <h2><?php echo esc_html__('Clés API', 'ai-content-factory-pro'); ?></h2>
                    
                    <div style="background: #e7f5fe; border-left: 4px solid #2271b1; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                        <p style="margin: 0; font-weight: 600; color: #0c5d8c;">
                            ℹ️ <?php echo esc_html__('Important : Configuration des clés API', 'ai-content-factory-pro'); ?>
                        </p>
                        <ul style="margin: 10px 0 0 0; color: #1d2327;">
                            <li><strong>OpenAI</strong> : Obligatoire pour génération de textes (ChatGPT)</li>
                            <li><strong>RapidAPI (Midjourney et Pinterest)</strong> : Pré-configurée et fonctionnelle ✅</li>
                            <li><strong>Autres APIs</strong> : À configurer manuellement selon vos besoins</li>
                        </ul>
                        <p style="margin: 10px 0 0 0; font-size: 13px; color: #3c434a;">
                            <?php echo esc_html__('La clé RapidAPI fournie fonctionne uniquement pour Midjourney et Pinterest. Pour les autres moteurs (SDXL, Flux Pro, etc.), vous devez souscrire séparément sur RapidAPI.', 'ai-content-factory-pro'); ?>
                        </p>
                    </div>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aicfp_openai_api_key">
                                    <?php echo esc_html__('Clé API OpenAI', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="aicfp_openai_api_key" 
                                       name="aicfp_openai_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_openai_api_key', '')); ?>" 
                                       class="regular-text" 
                                       autocomplete="off">
                                <p class="description">
                                    <?php echo esc_html__('Votre clé API OpenAI pour la génération de texte (GPT-4o).', 'ai-content-factory-pro'); ?>
                                    <a href="https://platform.openai.com/api-keys" target="_blank"><?php echo esc_html__('Obtenir une clé', 'ai-content-factory-pro'); ?></a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_rapidapi_key">
                                    <?php echo esc_html__('Clé RapidAPI (Midjourney)', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_rapidapi_key" 
                                       name="aicfp_rapidapi_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_rapidapi_key', '60bcbb5fe7mshd88f23d138be003p1be084jsnc1e30b0bb6d3')); ?>" 
                                       class="regular-text" 
                                       autocomplete="off">
                                <p class="description">
                                    <?php echo esc_html__('Votre clé RapidAPI pour la génération d\'images via Midjourney (API: midjourney-best-experience).', 'ai-content-factory-pro'); ?>
                                    <a href="https://rapidapi.com/hub" target="_blank"><?php echo esc_html__('Obtenir une clé', 'ai-content-factory-pro'); ?></a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_pinterest_rapidapi_key">
                                    <?php echo esc_html__('Clé RapidAPI (Pinterest)', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_pinterest_rapidapi_key" 
                                       name="aicfp_pinterest_rapidapi_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_pinterest_rapidapi_key', '60bcbb5fe7mshd88f23d138be003p1be084jsnc1e30b0bb6d3')); ?>" 
                                       class="regular-text" 
                                       autocomplete="off">
                                <p class="description">
                                    <?php echo esc_html__('Votre clé RapidAPI pour la recherche d\'images sur Pinterest (API non officielle).', 'ai-content-factory-pro'); ?>
                                    <a href="https://rapidapi.com/hub" target="_blank"><?php echo esc_html__('Obtenir une clé', 'ai-content-factory-pro'); ?></a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Image Generation APIs Section -->
                <div class="aicfp-card">
                    <h2><?php echo esc_html__('Moteurs de génération d\'images', 'ai-content-factory-pro'); ?></h2>
                    <p class="description"><?php echo esc_html__('Configurez les clés API pour les différents moteurs de génération d\'images. Seules les clés des moteurs que vous utilisez sont nécessaires.', 'ai-content-factory-pro'); ?></p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aicfp_sdxl_api_key">
                                    <?php echo esc_html__('Clé Stable Diffusion XL', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_sdxl_api_key" 
                                       name="aicfp_sdxl_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_sdxl_api_key', '')); ?>" 
                                       class="regular-text"
                                       placeholder="Entrez votre clé API RapidAPI pour SDXL">
                                <p class="description">
                                    <?php echo esc_html__('Polyvalent, rapide, économique (~$0.01/image) - Clé RapidAPI à configurer manuellement', 'ai-content-factory-pro'); ?>
                                    <a href="https://rapidapi.com/hub" target="_blank"><?php echo esc_html__('Obtenir une clé', 'ai-content-factory-pro'); ?></a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_sdxl_food_api_key">
                                    <?php echo esc_html__('Clé SDXL Food LoRA', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_sdxl_food_api_key" 
                                       name="aicfp_sdxl_food_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_sdxl_food_api_key', '')); ?>" 
                                       class="regular-text"
                                       placeholder="Entrez votre clé API RapidAPI pour SDXL Food">
                                <p class="description">
                                    <strong style="color: #f57c00;">⭐ RECOMMANDÉ POUR RECETTES</strong> - Spécialisé food photography (~$0.02/image)
                                    <br><?php echo esc_html__('Nécessite clé RapidAPI avec abonnement SDXL Food LoRA', 'ai-content-factory-pro'); ?>
                                    <a href="https://rapidapi.com/hub" target="_blank"><?php echo esc_html__('Obtenir une clé', 'ai-content-factory-pro'); ?></a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_sdxl_finetuned_api_key">
                                    <?php echo esc_html__('Clé Fine-tuned SDXL', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_sdxl_finetuned_api_key" 
                                       name="aicfp_sdxl_finetuned_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_sdxl_finetuned_api_key', '')); ?>" 
                                       class="regular-text"
                                       placeholder="Entrez votre clé API pour Fine-tuned SDXL">
                                <p class="description">
                                    <?php echo esc_html__('Modèle optimisé personnalisé (~$0.02/image) - À configurer manuellement', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_nanobanana_api_key">
                                    <?php echo esc_html__('Clé Nanobanana', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_nanobanana_api_key" 
                                       name="aicfp_nanobanana_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_nanobanana_api_key', '')); ?>" 
                                       class="regular-text"
                                       placeholder="Entrez votre clé API pour Nanobanana">
                                <p class="description">
                                    <?php echo esc_html__('Rapide et créatif (~$0.02/image) - À configurer manuellement', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_replicate_api_key">
                                    <?php echo esc_html__('Clé Replicate', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_replicate_api_key" 
                                       name="aicfp_replicate_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_replicate_api_key', '')); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    <?php echo esc_html__('Accès à multiples modèles (Coût variable)', 'ai-content-factory-pro'); ?>
                                    <a href="https://replicate.com/account/api-tokens" target="_blank"><?php echo esc_html__('Obtenir une clé', 'ai-content-factory-pro'); ?></a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_flux_api_key">
                                    <?php echo esc_html__('Clé Flux Pro', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_flux_api_key" 
                                       name="aicfp_flux_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_flux_api_key', '')); ?>" 
                                       class="regular-text"
                                       placeholder="Entrez votre clé API pour Flux Pro">
                                <p class="description">
                                    <?php echo esc_html__('Nouvelle génération, ultra-rapide (~$0.03/image, ~15s) - À configurer manuellement', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="aicfp-api-comparison-table" style="margin-top: 20px;">
                        <h3><?php echo esc_html__('Comparatif des moteurs', 'ai-content-factory-pro'); ?></h3>
                        <table class="widefat" style="margin-top: 10px;">
                            <thead>
                                <tr>
                                    <th><?php echo esc_html__('Moteur', 'ai-content-factory-pro'); ?></th>
                                    <th><?php echo esc_html__('Coût/image', 'ai-content-factory-pro'); ?></th>
                                    <th><?php echo esc_html__('Temps', 'ai-content-factory-pro'); ?></th>
                                    <th><?php echo esc_html__('Qualité', 'ai-content-factory-pro'); ?></th>
                                    <th><?php echo esc_html__('Spécialité', 'ai-content-factory-pro'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>SDXL Food LoRA</strong> ⭐</td>
                                    <td>$0.02</td>
                                    <td>~45s</td>
                                    <td>⭐⭐⭐⭐⭐</td>
                                    <td>Recettes</td>
                                </tr>
                                <tr>
                                    <td><strong>Flux Pro</strong></td>
                                    <td>$0.03</td>
                                    <td>~15s</td>
                                    <td>⭐⭐⭐⭐</td>
                                    <td>Rapide</td>
                                </tr>
                                <tr>
                                    <td><strong>DALL-E 3</strong></td>
                                    <td>$0.04</td>
                                    <td>~20s</td>
                                    <td>⭐⭐⭐⭐⭐</td>
                                    <td>Général</td>
                                </tr>
                                <tr>
                                    <td><strong>Midjourney</strong></td>
                                    <td>$0.05</td>
                                    <td>~2 min</td>
                                    <td>⭐⭐⭐⭐⭐</td>
                                    <td>Artistique</td>
                                </tr>
                                <tr>
                                    <td><strong>Replicate</strong></td>
                                    <td>$0.03</td>
                                    <td>~1 min</td>
                                    <td>⭐⭐⭐⭐</td>
                                    <td>Flexible</td>
                                </tr>
                                <tr>
                                    <td><strong>SDXL</strong></td>
                                    <td>$0.01</td>
                                    <td>~30s</td>
                                    <td>⭐⭐⭐</td>
                                    <td>Économique</td>
                                </tr>
                                <tr>
                                    <td><strong>Nanobanana</strong></td>
                                    <td>$0.02</td>
                                    <td>~30s</td>
                                    <td>⭐⭐⭐⭐</td>
                                    <td>Créatif</td>
                                </tr>
                                <tr>
                                    <td><strong>Fine-tuned SDXL</strong></td>
                                    <td>$0.02</td>
                                    <td>~40s</td>
                                    <td>⭐⭐⭐⭐</td>
                                    <td>Personnalisé</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Title Suggestions Section -->
                <div class="aicfp-card">
                    <h2><?php echo esc_html__('Suggestions de titres', 'ai-content-factory-pro'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aicfp_title_history_count">
                                    <?php echo esc_html__('Historique à analyser', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="aicfp_title_history_count" 
                                       name="aicfp_title_history_count" 
                                       value="<?php echo esc_attr(get_option('aicfp_title_history_count', '15')); ?>" 
                                       class="small-text"
                                       min="5"
                                       max="50">
                                <p class="description">
                                    <?php echo esc_html__('Nombre de titres d\'albums précédents à analyser pour générer des suggestions (5-50).', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_suggestions_count">
                                    <?php echo esc_html__('Nombre de suggestions', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="aicfp_suggestions_count" 
                                       name="aicfp_suggestions_count" 
                                       value="<?php echo esc_attr(get_option('aicfp_suggestions_count', '3')); ?>" 
                                       class="small-text"
                                       min="1"
                                       max="10">
                                <p class="description">
                                    <?php echo esc_html__('Nombre de suggestions de titres à afficher (1-10).', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Video Generation APIs Section -->
                <div class="aicfp-card">
                    <h2><?php echo esc_html__('🎬 Moteurs de génération vidéo', 'ai-content-factory-pro'); ?></h2>
                    <p class="description" style="font-size: 14px; margin-bottom: 15px;">
                        <?php echo esc_html__('Configurez les clés API pour les moteurs de génération vidéo IA. Nécessaire pour le module Vidéos.', 'ai-content-factory-pro'); ?>
                    </p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aicfp_google_veo_api_key">
                                    <?php echo esc_html__('Clé Google VEO', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_google_veo_api_key" 
                                       name="aicfp_google_veo_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_google_veo_api_key', '')); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    <strong style="color: #2271b1;">⭐ RECOMMANDÉ</strong> - Google VEO 2/3, haute qualité, jusqu'à 2 min (~$0.50/vidéo)
                                    <a href="https://deepmind.google/technologies/veo/" target="_blank"><?php echo esc_html__('En savoir plus', 'ai-content-factory-pro'); ?></a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_sora_api_key">
                                    <?php echo esc_html__('Clé OpenAI Sora', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_sora_api_key" 
                                       name="aicfp_sora_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_sora_api_key', '')); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    <?php echo esc_html__('Sora d\'OpenAI, qualité exceptionnelle, jusqu\'à 1 min (~$1.00/vidéo)', 'ai-content-factory-pro'); ?>
                                    <a href="https://openai.com/sora" target="_blank"><?php echo esc_html__('Obtenir accès', 'ai-content-factory-pro'); ?></a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_runway_api_key">
                                    <?php echo esc_html__('Clé RunwayML', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_runway_api_key" 
                                       name="aicfp_runway_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_runway_api_key', '')); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    <?php echo esc_html__('RunwayML Gen-2/Gen-3, rapide et créatif, 10s max (~$0.60/vidéo)', 'ai-content-factory-pro'); ?>
                                    <a href="https://runwayml.com/" target="_blank"><?php echo esc_html__('Obtenir une clé', 'ai-content-factory-pro'); ?></a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_pika_api_key">
                                    <?php echo esc_html__('Clé Pika Labs', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_pika_api_key" 
                                       name="aicfp_pika_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_pika_api_key', '')); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    <?php echo esc_html__('Pika Labs 1.5, animations fluides, 3s max (~$0.40/vidéo)', 'ai-content-factory-pro'); ?>
                                    <a href="https://pika.art/" target="_blank"><?php echo esc_html__('Obtenir accès', 'ai-content-factory-pro'); ?></a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_luma_api_key">
                                    <?php echo esc_html__('Clé Luma AI', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_luma_api_key" 
                                       name="aicfp_luma_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_luma_api_key', '')); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    <?php echo esc_html__('Luma Dream Machine, vidéos immersives, 5s max (~$0.50/vidéo)', 'ai-content-factory-pro'); ?>
                                    <a href="https://lumalabs.ai/" target="_blank"><?php echo esc_html__('Obtenir une clé', 'ai-content-factory-pro'); ?></a>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_kling_api_key">
                                    <?php echo esc_html__('Clé Kling AI', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_kling_api_key" 
                                       name="aicfp_kling_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_kling_api_key', '')); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    <?php echo esc_html__('Kling AI, vidéos longues durées, jusqu\'à 2 min (~$0.45/vidéo)', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_genmo_api_key">
                                    <?php echo esc_html__('Clé Genmo', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_genmo_api_key" 
                                       name="aicfp_genmo_api_key" 
                                       value="<?php echo esc_attr(get_option('aicfp_genmo_api_key', '')); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    <?php echo esc_html__('Genmo Replay, animations créatives, 6s max (~$0.35/vidéo)', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="aicfp-api-comparison-table" style="margin-top: 20px; padding: 15px; background: #f9fafb; border-radius: 8px;">
                        <h3 style="margin-top: 0; color: #1d2327;"><?php echo esc_html__('📊 Comparatif des moteurs vidéo', 'ai-content-factory-pro'); ?></h3>
                        <table class="widefat" style="margin-top: 10px;">
                            <thead>
                                <tr style="background: #f0f0f1;">
                                    <th style="padding: 12px; font-weight: 700;"><?php echo esc_html__('Moteur', 'ai-content-factory-pro'); ?></th>
                                    <th style="padding: 12px; font-weight: 700;"><?php echo esc_html__('Coût/vidéo', 'ai-content-factory-pro'); ?></th>
                                    <th style="padding: 12px; font-weight: 700;"><?php echo esc_html__('Durée max', 'ai-content-factory-pro'); ?></th>
                                    <th style="padding: 12px; font-weight: 700;"><?php echo esc_html__('Qualité', 'ai-content-factory-pro'); ?></th>
                                    <th style="padding: 12px; font-weight: 700;"><?php echo esc_html__('Spécialité', 'ai-content-factory-pro'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid #f0f0f1;">
                                    <td style="padding: 12px;"><strong>Google VEO 2/3</strong> ⭐</td>
                                    <td style="padding: 12px; color: #00a32a; font-weight: 600;">$0.50</td>
                                    <td style="padding: 12px;">2 min</td>
                                    <td style="padding: 12px;">⭐⭐⭐⭐⭐</td>
                                    <td style="padding: 12px;">Polyvalent</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #f0f0f1;">
                                    <td style="padding: 12px;"><strong>OpenAI Sora</strong></td>
                                    <td style="padding: 12px; color: #d63638;">$1.00</td>
                                    <td style="padding: 12px;">1 min</td>
                                    <td style="padding: 12px;">⭐⭐⭐⭐⭐</td>
                                    <td style="padding: 12px;">Premium</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #f0f0f1;">
                                    <td style="padding: 12px;"><strong>RunwayML Gen-3</strong></td>
                                    <td style="padding: 12px; color: #dba617;">$0.60</td>
                                    <td style="padding: 12px;">10s</td>
                                    <td style="padding: 12px;">⭐⭐⭐⭐</td>
                                    <td style="padding: 12px;">Créatif</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #f0f0f1;">
                                    <td style="padding: 12px;"><strong>Luma AI</strong></td>
                                    <td style="padding: 12px; color: #00a32a; font-weight: 600;">$0.50</td>
                                    <td style="padding: 12px;">5s</td>
                                    <td style="padding: 12px;">⭐⭐⭐⭐</td>
                                    <td style="padding: 12px;">Immersif</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #f0f0f1;">
                                    <td style="padding: 12px;"><strong>Kling AI</strong></td>
                                    <td style="padding: 12px; color: #00a32a; font-weight: 600;">$0.45</td>
                                    <td style="padding: 12px;">2 min</td>
                                    <td style="padding: 12px;">⭐⭐⭐⭐</td>
                                    <td style="padding: 12px;">Longues vidéos</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #f0f0f1;">
                                    <td style="padding: 12px;"><strong>Pika Labs</strong></td>
                                    <td style="padding: 12px; color: #00a32a; font-weight: 600;">$0.40</td>
                                    <td style="padding: 12px;">3s</td>
                                    <td style="padding: 12px;">⭐⭐⭐</td>
                                    <td style="padding: 12px;">Animation</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px;"><strong>Genmo</strong></td>
                                    <td style="padding: 12px; color: #00a32a; font-weight: 600;">$0.35</td>
                                    <td style="padding: 12px;">6s</td>
                                    <td style="padding: 12px;">⭐⭐⭐</td>
                                    <td style="padding: 12px;">Créatif</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Debug Mode Section -->
                <div class="aicfp-card" style="border: 2px solid #ff6b6b;">
                    <h2 style="color: #ff6b6b;">🔧 <?php echo esc_html__('Mode Debug', 'ai-content-factory-pro'); ?></h2>
                    <p class="description" style="font-size: 14px; margin-bottom: 15px;">
                        <?php echo esc_html__('Activez le mode debug pour accéder aux outils de diagnostic et faciliter le reporting de bugs.', 'ai-content-factory-pro'); ?>
                    </p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aicfp_debug_mode_enabled">
                                    <?php echo esc_html__('Activer le mode Debug', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           id="aicfp_debug_mode_enabled" 
                                           name="aicfp_debug_mode_enabled" 
                                           value="1" 
                                           <?php checked(get_option('aicfp_debug_mode_enabled', false), true); ?>>
                                    <strong style="color: #ff6b6b;"><?php echo esc_html__('Afficher le menu Debug dans le plugin', 'ai-content-factory-pro'); ?></strong>
                                </label>
                                <p class="description">
                                    <?php echo esc_html__('Active un menu avec informations système, logs, tests automatiques et export de rapport pour faciliter le diagnostic.', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_verbose_logging">
                                    <?php echo esc_html__('Logging détaillé', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           id="aicfp_verbose_logging" 
                                           name="aicfp_verbose_logging" 
                                           value="1" 
                                           <?php checked(get_option('aicfp_verbose_logging', true), true); ?>>
                                    <?php echo esc_html__('Logger toutes les actions (API, AJAX, génération)', 'ai-content-factory-pro'); ?>
                                </label>
                                <p class="description">
                                    <?php echo esc_html__('Écrit les détails dans wp-content/debug.log. Recommandé pour diagnostic.', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Notifications Section -->
                <div class="aicfp-card">
                    <h2><?php echo esc_html__('Notifications', 'ai-content-factory-pro'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aicfp_email_notifications_enabled">
                                    <?php echo esc_html__('Notifications par email', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           id="aicfp_email_notifications_enabled" 
                                           name="aicfp_email_notifications_enabled" 
                                           value="1" 
                                           <?php checked(get_option('aicfp_email_notifications_enabled', true), true); ?>>
                                    <?php echo esc_html__('Envoyer un email quand un album est prêt', 'ai-content-factory-pro'); ?>
                                </label>
                                <p class="description">
                                    <?php echo esc_html__('Vous recevrez un email avec le lien de téléchargement une fois la génération terminée.', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_notification_email">
                                    <?php echo esc_html__('Email de notification par défaut', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="email" 
                                       id="aicfp_notification_email" 
                                       name="aicfp_notification_email" 
                                       value="<?php echo esc_attr(get_option('aicfp_notification_email', get_option('admin_email'))); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    <?php echo esc_html__('Email utilisé par défaut pour les notifications (peut être modifié pour chaque album).', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="aicfp_notify_errors">
                                    <?php echo esc_html__('Notifier les erreurs', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           id="aicfp_notify_errors" 
                                           name="aicfp_notify_errors" 
                                           value="1" 
                                           <?php checked(get_option('aicfp_notify_errors', true), true); ?>>
                                    <?php echo esc_html__('Envoyer un email en cas d\'erreur pendant la génération', 'ai-content-factory-pro'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- SMTP Settings Section -->
                <div class="aicfp-card">
                    <h2><?php echo esc_html__('Paramètres SMTP', 'ai-content-factory-pro'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="aicfp_smtp_enabled">
                                    <?php echo esc_html__('Activer SMTP', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" 
                                           id="aicfp_smtp_enabled" 
                                           name="aicfp_smtp_enabled" 
                                           value="1" 
                                           <?php checked(get_option('aicfp_smtp_enabled', false), true); ?>>
                                    <?php echo esc_html__('Utiliser SMTP pour l\'envoi d\'emails', 'ai-content-factory-pro'); ?>
                                </label>
                            </td>
                        </tr>
                        
                        <tr class="aicfp-smtp-field">
                            <th scope="row">
                                <label for="aicfp_smtp_host">
                                    <?php echo esc_html__('Hôte SMTP', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_smtp_host" 
                                       name="aicfp_smtp_host" 
                                       value="<?php echo esc_attr(get_option('aicfp_smtp_host', '')); ?>" 
                                       class="regular-text">
                                <p class="description">
                                    <?php echo esc_html__('Ex: smtp.gmail.com', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="aicfp-smtp-field">
                            <th scope="row">
                                <label for="aicfp_smtp_port">
                                    <?php echo esc_html__('Port SMTP', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="aicfp_smtp_port" 
                                       name="aicfp_smtp_port" 
                                       value="<?php echo esc_attr(get_option('aicfp_smtp_port', '587')); ?>" 
                                       class="small-text">
                                <p class="description">
                                    <?php echo esc_html__('Port commun: 587 (TLS) ou 465 (SSL)', 'ai-content-factory-pro'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="aicfp-smtp-field">
                            <th scope="row">
                                <label for="aicfp_smtp_encryption">
                                    <?php echo esc_html__('Chiffrement', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <select id="aicfp_smtp_encryption" name="aicfp_smtp_encryption">
                                    <option value="tls" <?php selected(get_option('aicfp_smtp_encryption', 'tls'), 'tls'); ?>>TLS</option>
                                    <option value="ssl" <?php selected(get_option('aicfp_smtp_encryption', 'tls'), 'ssl'); ?>>SSL</option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr class="aicfp-smtp-field">
                            <th scope="row">
                                <label for="aicfp_smtp_username">
                                    <?php echo esc_html__('Nom d\'utilisateur', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_smtp_username" 
                                       name="aicfp_smtp_username" 
                                       value="<?php echo esc_attr(get_option('aicfp_smtp_username', '')); ?>" 
                                       class="regular-text" 
                                       autocomplete="off">
                            </td>
                        </tr>
                        
                        <tr class="aicfp-smtp-field">
                            <th scope="row">
                                <label for="aicfp_smtp_password">
                                    <?php echo esc_html__('Mot de passe', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="password" 
                                       id="aicfp_smtp_password" 
                                       name="aicfp_smtp_password" 
                                       value="<?php echo esc_attr(get_option('aicfp_smtp_password', '')); ?>" 
                                       class="regular-text" 
                                       autocomplete="off">
                            </td>
                        </tr>
                        
                        <tr class="aicfp-smtp-field">
                            <th scope="row">
                                <label for="aicfp_smtp_from_email">
                                    <?php echo esc_html__('Email expéditeur', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="email" 
                                       id="aicfp_smtp_from_email" 
                                       name="aicfp_smtp_from_email" 
                                       value="<?php echo esc_attr(get_option('aicfp_smtp_from_email', get_option('admin_email'))); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                        
                        <tr class="aicfp-smtp-field">
                            <th scope="row">
                                <label for="aicfp_smtp_from_name">
                                    <?php echo esc_html__('Nom expéditeur', 'ai-content-factory-pro'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="aicfp_smtp_from_name" 
                                       name="aicfp_smtp_from_name" 
                                       value="<?php echo esc_attr(get_option('aicfp_smtp_from_name', get_bloginfo('name'))); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                    </table>
                </div>
                
                <p class="submit">
                    <button type="submit" name="aicfp_save_settings" class="button button-primary">
                        <?php echo esc_html__('Enregistrer les modifications', 'ai-content-factory-pro'); ?>
                    </button>
                </p>
            </form>
        </div>
        
        <style>
            .aicfp-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
                margin-bottom: 20px;
                padding: 20px;
            }
            .aicfp-card h2 {
                margin-top: 0;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            function toggleSMTPFields() {
                if ($('#aicfp_smtp_enabled').is(':checked')) {
                    $('.aicfp-smtp-field').show();
                } else {
                    $('.aicfp-smtp-field').hide();
                }
            }
            
            toggleSMTPFields();
            $('#aicfp_smtp_enabled').on('change', toggleSMTPFields);
        });
        </script>
        <?php
    }
    
    /**
     * Enregistrer les paramètres
     */
    private static function save_settings() {
        // Sauvegarder les clés API
        if (isset($_POST['aicfp_openai_api_key'])) {
            update_option('aicfp_openai_api_key', sanitize_text_field($_POST['aicfp_openai_api_key']));
        }
        
        if (isset($_POST['aicfp_rapidapi_key'])) {
            update_option('aicfp_rapidapi_key', sanitize_text_field($_POST['aicfp_rapidapi_key']));
        }
        
        if (isset($_POST['aicfp_pinterest_rapidapi_key'])) {
            update_option('aicfp_pinterest_rapidapi_key', sanitize_text_field($_POST['aicfp_pinterest_rapidapi_key']));
        }
        
        // Sauvegarder les clés des moteurs de génération d'images
        $image_apis = array('sdxl', 'sdxl_food', 'sdxl_finetuned', 'nanobanana', 'replicate', 'flux');
        foreach ($image_apis as $api) {
            $key_name = 'aicfp_' . $api . '_api_key';
            if (isset($_POST[$key_name])) {
                update_option($key_name, sanitize_text_field($_POST[$key_name]));
            }
        }
        
        // Sauvegarder les clés des moteurs de génération vidéo
        $video_apis = array('google_veo', 'sora', 'runway', 'pika', 'luma', 'kling', 'genmo');
        foreach ($video_apis as $api) {
            $key_name = 'aicfp_' . $api . '_api_key';
            if (isset($_POST[$key_name])) {
                update_option($key_name, sanitize_text_field($_POST[$key_name]));
            }
        }
        
        // Sauvegarder les paramètres de suggestions de titres
        if (isset($_POST['aicfp_title_history_count'])) {
            $history_count = max(5, min(50, intval($_POST['aicfp_title_history_count'])));
            update_option('aicfp_title_history_count', $history_count);
        }
        
        if (isset($_POST['aicfp_suggestions_count'])) {
            $suggestions_count = max(1, min(10, intval($_POST['aicfp_suggestions_count'])));
            update_option('aicfp_suggestions_count', $suggestions_count);
        }
        
        // Sauvegarder le mode debug
        update_option('aicfp_debug_mode_enabled', isset($_POST['aicfp_debug_mode_enabled']));
        update_option('aicfp_verbose_logging', isset($_POST['aicfp_verbose_logging']));
        
        // Sauvegarder les options de notification
        update_option('aicfp_email_notifications_enabled', isset($_POST['aicfp_email_notifications_enabled']));
        
        if (isset($_POST['aicfp_notification_email'])) {
            update_option('aicfp_notification_email', sanitize_email($_POST['aicfp_notification_email']));
        }
        
        update_option('aicfp_notify_errors', isset($_POST['aicfp_notify_errors']));
        
        // Sauvegarder les paramètres SMTP
        update_option('aicfp_smtp_enabled', isset($_POST['aicfp_smtp_enabled']));
        
        if (isset($_POST['aicfp_smtp_host'])) {
            update_option('aicfp_smtp_host', sanitize_text_field($_POST['aicfp_smtp_host']));
        }
        
        if (isset($_POST['aicfp_smtp_port'])) {
            update_option('aicfp_smtp_port', intval($_POST['aicfp_smtp_port']));
        }
        
        if (isset($_POST['aicfp_smtp_encryption'])) {
            update_option('aicfp_smtp_encryption', sanitize_text_field($_POST['aicfp_smtp_encryption']));
        }
        
        if (isset($_POST['aicfp_smtp_username'])) {
            update_option('aicfp_smtp_username', sanitize_text_field($_POST['aicfp_smtp_username']));
        }
        
        if (isset($_POST['aicfp_smtp_password'])) {
            update_option('aicfp_smtp_password', $_POST['aicfp_smtp_password']); // Ne pas sanitize le mot de passe
        }
        
        if (isset($_POST['aicfp_smtp_from_email'])) {
            update_option('aicfp_smtp_from_email', sanitize_email($_POST['aicfp_smtp_from_email']));
        }
        
        if (isset($_POST['aicfp_smtp_from_name'])) {
            update_option('aicfp_smtp_from_name', sanitize_text_field($_POST['aicfp_smtp_from_name']));
        }
        
        // Afficher un message de succès
        add_settings_error(
            'aicfp_messages',
            'aicfp_message',
            __('Paramètres enregistrés avec succès.', 'ai-content-factory-pro'),
            'updated'
        );
        
        settings_errors('aicfp_messages');
    }
}
