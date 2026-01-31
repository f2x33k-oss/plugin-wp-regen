<?php
/**
 * Page Génération de Vidéos IA
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de la page Vidéos
 */
class AICFP_Videos_Page {
    
    /**
     * Render la page
     */
    public static function render() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'ai-content-factory-pro'));
        }
        
        ?>
        <div class="wrap aicfp-videos-page">
            <h1><?php echo esc_html__('Génération de Vidéos IA - AI Content Factory Pro', 'ai-content-factory-pro'); ?></h1>
            
            <div class="aicfp-videos-intro">
                <p><strong><?php echo esc_html__('🎬 Génération automatique de vidéos par intelligence artificielle', 'ai-content-factory-pro'); ?></strong></p>
                <p><?php echo esc_html__('Transformez vos idées en vidéos professionnelles grâce à Google VEO et nos agents IA spécialisés.', 'ai-content-factory-pro'); ?></p>
            </div>
            
            <!-- Onglets -->
            <div class="nav-tab-wrapper aicfp-nav-tabs">
                <a href="#tab-simple" class="nav-tab nav-tab-active" data-tab="simple">
                    <?php echo esc_html__('Mode Simple', 'ai-content-factory-pro'); ?>
                </a>
                <a href="#tab-advanced" class="nav-tab" data-tab="advanced">
                    <?php echo esc_html__('Mode Avancé', 'ai-content-factory-pro'); ?>
                </a>
                <a href="#tab-presets" class="nav-tab" data-tab="presets">
                    <?php echo esc_html__('Presets', 'ai-content-factory-pro'); ?>
                </a>
            </div>
            
            <!-- Tab Simple -->
            <div id="tab-simple" class="aicfp-tab-content active">
                <div class="aicfp-card">
                    <h2><?php echo esc_html__('Génération rapide', 'ai-content-factory-pro'); ?></h2>
                    
                    <form id="aicfp-video-simple-form">
                        <?php wp_nonce_field('aicfp_video_nonce', 'aicfp_video_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="aicfp_video_idea">
                                        <?php echo esc_html__('Décrivez votre idée', 'ai-content-factory-pro'); ?>
                                        <span class="required">*</span>
                                    </label>
                                </th>
                                <td>
                                    <textarea id="aicfp_video_idea" 
                                              name="idea" 
                                              rows="5" 
                                              class="large-text"
                                              required
                                              placeholder="<?php echo esc_attr__('Ex: Une vidéo inspirante sur l\'entrepreneuriat montrant le parcours d\'un jeune créateur de startup...', 'ai-content-factory-pro'); ?>"></textarea>
                                    <p class="description">
                                        <?php echo esc_html__('Décrivez votre idée librement. L\'IA analysera et créera le scénario optimal.', 'ai-content-factory-pro'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="aicfp_video_type">
                                        <?php echo esc_html__('Type de contenu', 'ai-content-factory-pro'); ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="aicfp_video_type" name="content_type" class="regular-text">
                                        <option value="single"><?php echo esc_html__('Vidéo unique', 'ai-content-factory-pro'); ?></option>
                                        <option value="series"><?php echo esc_html__('Mini-série (8 épisodes)', 'ai-content-factory-pro'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="aicfp_video_style">
                                        <?php echo esc_html__('Style visuel', 'ai-content-factory-pro'); ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="aicfp_video_style" name="visual_style" class="regular-text">
                                        <option value="cinematic"><?php echo esc_html__('Cinématique', 'ai-content-factory-pro'); ?></option>
                                        <option value="realistic"><?php echo esc_html__('Réaliste', 'ai-content-factory-pro'); ?></option>
                                        <option value="cartoon"><?php echo esc_html__('Cartoon / Animation', 'ai-content-factory-pro'); ?></option>
                                        <option value="futuristic"><?php echo esc_html__('Futuriste / IA', 'ai-content-factory-pro'); ?></option>
                                        <option value="documentary"><?php echo esc_html__('Documentaire', 'ai-content-factory-pro'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="aicfp_video_tone">
                                        <?php echo esc_html__('Ton narratif', 'ai-content-factory-pro'); ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="aicfp_video_tone" name="narrative_tone" class="regular-text">
                                        <option value="epic"><?php echo esc_html__('Épique', 'ai-content-factory-pro'); ?></option>
                                        <option value="funny"><?php echo esc_html__('Drôle / Humoristique', 'ai-content-factory-pro'); ?></option>
                                        <option value="emotional"><?php echo esc_html__('Émotionnel', 'ai-content-factory-pro'); ?></option>
                                        <option value="neutral"><?php echo esc_html__('Neutre / Informatif', 'ai-content-factory-pro'); ?></option>
                                        <option value="inspiring"><?php echo esc_html__('Inspirant / Motivant', 'ai-content-factory-pro'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="aicfp_video_duration">
                                        <?php echo esc_html__('Durée cible', 'ai-content-factory-pro'); ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="aicfp_video_duration" name="target_duration" class="regular-text">
                                        <option value="short"><?php echo esc_html__('≤ 30 secondes (Short/Reel)', 'ai-content-factory-pro'); ?></option>
                                        <option value="medium"><?php echo esc_html__('30-60 secondes', 'ai-content-factory-pro'); ?></option>
                                        <option value="long"><?php echo esc_html__('60+ secondes', 'ai-content-factory-pro'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="aicfp_video_platform">
                                        <?php echo esc_html__('Plateforme cible', 'ai-content-factory-pro'); ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="aicfp_video_platform" name="target_platform" class="regular-text">
                                        <option value="tiktok"><?php echo esc_html__('TikTok', 'ai-content-factory-pro'); ?></option>
                                        <option value="instagram"><?php echo esc_html__('Instagram Reels', 'ai-content-factory-pro'); ?></option>
                                        <option value="youtube"><?php echo esc_html__('YouTube Shorts', 'ai-content-factory-pro'); ?></option>
                                        <option value="facebook"><?php echo esc_html__('Facebook', 'ai-content-factory-pro'); ?></option>
                                        <option value="generic"><?php echo esc_html__('Générique', 'ai-content-factory-pro'); ?></option>
                                    </select>
                                    <p class="description">
                                        <?php echo esc_html__('Optimise le format et le contenu pour la plateforme choisie.', 'ai-content-factory-pro'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="aicfp_video_audio">
                                        <?php echo esc_html__('Audio (optionnel)', 'ai-content-factory-pro'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="file" 
                                           id="aicfp_video_audio" 
                                           name="audio_file" 
                                           accept="audio/*,.mp3,.wav">
                                    <p class="description">
                                        <?php echo esc_html__('Uploadez un fichier audio (voix off, musique) pour accompagner la vidéo.', 'ai-content-factory-pro'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Estimation -->
                        <div class="aicfp-video-estimator">
                            <h3><?php echo esc_html__('Estimation', 'ai-content-factory-pro'); ?></h3>
                            <div class="aicfp-estimator-grid">
                                <div class="aicfp-estimator-box">
                                    <span class="aicfp-estimator-icon">💰</span>
                                    <span class="aicfp-estimator-label"><?php echo esc_html__('Coût estimé', 'ai-content-factory-pro'); ?></span>
                                    <span class="aicfp-estimator-value" id="aicfp-video-cost">$0.50</span>
                                </div>
                                <div class="aicfp-estimator-box">
                                    <span class="aicfp-estimator-icon">⏱️</span>
                                    <span class="aicfp-estimator-label"><?php echo esc_html__('Temps estimé', 'ai-content-factory-pro'); ?></span>
                                    <span class="aicfp-estimator-value" id="aicfp-video-time">5-10 min</span>
                                </div>
                                <div class="aicfp-estimator-box">
                                    <span class="aicfp-estimator-icon">🎬</span>
                                    <span class="aicfp-estimator-label"><?php echo esc_html__('Vidéos', 'ai-content-factory-pro'); ?></span>
                                    <span class="aicfp-estimator-value" id="aicfp-video-count">1</span>
                                </div>
                            </div>
                        </div>
                        
                        <p class="submit">
                            <button type="submit" class="button button-primary button-hero">
                                <span class="dashicons dashicons-video-alt3"></span>
                                <?php echo esc_html__('Générer la vidéo', 'ai-content-factory-pro'); ?>
                            </button>
                        </p>
                    </form>
                    
                    <div id="aicfp-video-result" style="display:none;"></div>
                </div>
            </div>
            
            <!-- Tab Advanced -->
            <div id="tab-advanced" class="aicfp-tab-content">
                <div class="aicfp-card">
                    <h2><?php echo esc_html__('Mode Avancé', 'ai-content-factory-pro'); ?></h2>
                    <p><?php echo esc_html__('Fonctionnalités avancées : mode chat interactif, instructions détaillées, contrôle fin du scénario...', 'ai-content-factory-pro'); ?></p>
                    <p class="description"><?php echo esc_html__('🚧 En cours de développement - Version 1.2', 'ai-content-factory-pro'); ?></p>
                </div>
            </div>
            
            <!-- Tab Presets -->
            <div id="tab-presets" class="aicfp-tab-content">
                <div class="aicfp-card">
                    <h2><?php echo esc_html__('Presets intelligents', 'ai-content-factory-pro'); ?></h2>
                    
                    <div class="aicfp-presets-grid">
                        <div class="aicfp-preset-card" data-preset="tiktok-viral">
                            <h3>🔥 TikTok Viral</h3>
                            <p><?php echo esc_html__('Optimisé pour devenir viral sur TikTok', 'ai-content-factory-pro'); ?></p>
                            <ul>
                                <li>Durée: 15-30s</li>
                                <li>Style: Dynamique</li>
                                <li>Hook: 3 premières secondes captivantes</li>
                            </ul>
                            <button class="button button-secondary aicfp-use-preset">
                                <?php echo esc_html__('Utiliser ce preset', 'ai-content-factory-pro'); ?>
                            </button>
                        </div>
                        
                        <div class="aicfp-preset-card" data-preset="educational-short">
                            <h3>📚 Short Éducatif</h3>
                            <p><?php echo esc_html__('Vidéo courte et informative', 'ai-content-factory-pro'); ?></p>
                            <ul>
                                <li>Durée: 30-60s</li>
                                <li>Style: Clair et structuré</li>
                                <li>Ton: Pédagogique</li>
                            </ul>
                            <button class="button button-secondary aicfp-use-preset">
                                <?php echo esc_html__('Utiliser ce preset', 'ai-content-factory-pro'); ?>
                            </button>
                        </div>
                        
                        <div class="aicfp-preset-card" data-preset="emotional-story">
                            <h3>💙 Story Émotionnelle</h3>
                            <p><?php echo esc_html__('Histoire touchante et inspirante', 'ai-content-factory-pro'); ?></p>
                            <ul>
                                <li>Durée: 60s+</li>
                                <li>Style: Cinématique</li>
                                <li>Ton: Émotionnel</li>
                            </ul>
                            <button class="button button-secondary aicfp-use-preset">
                                <?php echo esc_html__('Utiliser ce preset', 'ai-content-factory-pro'); ?>
                            </button>
                        </div>
                        
                        <div class="aicfp-preset-card" data-preset="product-showcase">
                            <h3>🛍️ Showcase Produit</h3>
                            <p><?php echo esc_html__('Mise en valeur d\'un produit', 'ai-content-factory-pro'); ?></p>
                            <ul>
                                <li>Durée: 30-45s</li>
                                <li>Style: Professionnel</li>
                                <li>Focus: Caractéristiques + Bénéfices</li>
                            </ul>
                            <button class="button button-secondary aicfp-use-preset">
                                <?php echo esc_html__('Utiliser ce preset', 'ai-content-factory-pro'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .aicfp-videos-intro {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
                border-radius: 10px;
                margin-bottom: 20px;
            }
            
            .aicfp-videos-intro p {
                margin: 5px 0;
            }
            
            .aicfp-nav-tabs {
                margin-bottom: 0;
            }
            
            .aicfp-tab-content {
                display: none;
                margin-top: 20px;
            }
            
            .aicfp-tab-content.active {
                display: block;
            }
            
            .aicfp-video-estimator {
                background: #f0f6fc;
                border: 1px solid #c3e4ff;
                border-radius: 10px;
                padding: 20px;
                margin: 20px 0;
            }
            
            .aicfp-estimator-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-top: 15px;
            }
            
            .aicfp-estimator-box {
                background: white;
                padding: 20px;
                border-radius: 8px;
                text-align: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .aicfp-estimator-icon {
                font-size: 32px;
                display: block;
                margin-bottom: 10px;
            }
            
            .aicfp-estimator-label {
                display: block;
                font-size: 12px;
                color: #666;
                margin-bottom: 5px;
                text-transform: uppercase;
            }
            
            .aicfp-estimator-value {
                display: block;
                font-size: 24px;
                font-weight: bold;
                color: #0c5d8c;
            }
            
            .aicfp-presets-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            
            .aicfp-preset-card {
                background: white;
                border: 2px solid #ddd;
                border-radius: 10px;
                padding: 20px;
                transition: all 0.3s;
            }
            
            .aicfp-preset-card:hover {
                border-color: #2271b1;
                box-shadow: 0 4px 12px rgba(34, 113, 177, 0.2);
                transform: translateY(-2px);
            }
            
            .aicfp-preset-card h3 {
                margin-top: 0;
                color: #1d2327;
            }
            
            .aicfp-preset-card ul {
                list-style: none;
                padding: 0;
                margin: 15px 0;
            }
            
            .aicfp-preset-card ul li {
                padding: 5px 0;
                color: #666;
            }
            
            .aicfp-preset-card ul li:before {
                content: "✓ ";
                color: #00a32a;
                font-weight: bold;
                margin-right: 5px;
            }
            
            .button-hero .dashicons {
                line-height: 40px;
                font-size: 20px;
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Gestion des onglets
            $('.aicfp-nav-tabs .nav-tab').on('click', function(e) {
                e.preventDefault();
                
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                $('.aicfp-tab-content').removeClass('active');
                $('#tab-' + $(this).data('tab')).addClass('active');
            });
            
            // Mise à jour de l'estimation
            function updateEstimation() {
                const contentType = $('#aicfp_video_type').val();
                const videoCount = contentType === 'series' ? 8 : 1;
                const costPerVideo = 0.50;
                const timePerVideo = 7; // minutes
                
                $('#aicfp-video-count').text(videoCount);
                $('#aicfp-video-cost').text('$' + (videoCount * costPerVideo).toFixed(2));
                $('#aicfp-video-time').text((videoCount * timePerVideo) + ' min');
            }
            
            $('#aicfp_video_type').on('change', updateEstimation);
            updateEstimation();
            
            // Application des presets
            $('.aicfp-use-preset').on('click', function() {
                const preset = $(this).closest('.aicfp-preset-card').data('preset');
                
                // Basculer vers l'onglet simple
                $('.nav-tab[data-tab="simple"]').click();
                
                // Appliquer le preset
                switch(preset) {
                    case 'tiktok-viral':
                        $('#aicfp_video_duration').val('short');
                        $('#aicfp_video_platform').val('tiktok');
                        $('#aicfp_video_style').val('realistic');
                        $('#aicfp_video_tone').val('funny');
                        break;
                    case 'educational-short':
                        $('#aicfp_video_duration').val('medium');
                        $('#aicfp_video_style').val('realistic');
                        $('#aicfp_video_tone').val('neutral');
                        break;
                    case 'emotional-story':
                        $('#aicfp_video_duration').val('long');
                        $('#aicfp_video_style').val('cinematic');
                        $('#aicfp_video_tone').val('emotional');
                        break;
                    case 'product-showcase':
                        $('#aicfp_video_duration').val('medium');
                        $('#aicfp_video_style').val('realistic');
                        $('#aicfp_video_tone').val('neutral');
                        break;
                }
                
                // Scroll vers le formulaire
                $('html, body').animate({
                    scrollTop: $('#aicfp-video-simple-form').offset().top - 100
                }, 500);
            });
            
            // Soumission du formulaire
            $('#aicfp-video-simple-form').on('submit', function(e) {
                e.preventDefault();
                
                const $result = $('#aicfp-video-result');
                const formData = new FormData(this);
                formData.append('action', 'aicfp_submit_video_generation');
                
                $result.html('<div class="aicfp-loading"><span class="dashicons dashicons-update-alt"></span> Analyse de votre idée en cours...</div>').show();
                
                $.ajax({
                    url: aicfp_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $result.html('<div class="notice notice-success"><p><strong>Succès !</strong> ' + response.data.message + '</p></div>');
                            
                            setTimeout(function() {
                                window.location.href = 'admin.php?page=aicfp-instances';
                            }, 2000);
                        } else {
                            $result.html('<div class="notice notice-error"><p><strong>Erreur !</strong> ' + response.data.message + '</p></div>');
                        }
                    },
                    error: function() {
                        $result.html('<div class="notice notice-error"><p>Une erreur est survenue.</p></div>');
                    }
                });
            });
        });
        </script>
        <?php
    }
}
