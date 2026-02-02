<?php
/**
 * Gestionnaire des APIs de génération d'images
 *
 * @package AI_Content_Factory_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class AICFP_Image_API_Manager {
    
    /**
     * Générer une image selon l'API sélectionnée
     */
    public static function generate_image($prompt, $api_type = 'midjourney', $reference_images = null) {
        switch ($api_type) {
            case 'sdxl':
                return self::generate_with_sdxl($prompt, $reference_images);
            
            case 'sdxl-fast':
                return self::generate_with_sdxl_fast($prompt, $reference_images);
            
            case 'sdxl-food':
                return self::generate_with_sdxl_food($prompt, $reference_images);
            
            case 'sdxl-finetuned':
                return self::generate_with_sdxl_finetuned($prompt, $reference_images);
            
            case 'dalle':
                return self::generate_with_dalle($prompt);
            
            case 'nanobanana':
                return self::generate_with_nanobanana($prompt, $reference_images);
            
            case 'replicate':
                return self::generate_with_replicate($prompt, $reference_images);
            
            case 'flux-pro':
                return self::generate_with_flux_pro($prompt, $reference_images);
            
            case 'midjourney':
            default:
                return AICFP_API_Handler::generate_image($prompt, $reference_images);
        }
    }
    
    /**
     * Stable Diffusion XL
     */
    private static function generate_with_sdxl($prompt, $reference_images = null) {
        $api_key = get_option('aicfp_sdxl_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Clé API SDXL non configurée. Veuillez configurer votre clé RapidAPI dans Réglages.', 'ai-content-factory-pro'));
        }
        
        $payload = array(
            'prompt' => $prompt,
            'negative_prompt' => 'blurry, bad quality, distorted',
            'num_inference_steps' => 30,
            'guidance_scale' => 7.5,
            'width' => 1024,
            'height' => 1024
        );
        
        $response = wp_remote_post('https://stable-diffusion-xl.p.rapidapi.com/generate', array(
            'timeout' => 60,
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => 'stable-diffusion-xl.p.rapidapi.com',
                'x-rapidapi-key' => $api_key
            ),
            'body' => wp_json_encode($payload)
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['output']) && is_array($data['output']) && !empty($data['output'])) {
            return $data['output'][0];
        }
        
        if (isset($data['image_url'])) {
            return $data['image_url'];
        }
        
        return new WP_Error('sdxl_error', __('Impossible de générer l\'image avec SDXL.', 'ai-content-factory-pro'));
    }
    
    /**
     * SDXL Fast API (Ultra-rapide)
     */
    private static function generate_with_sdxl_fast($prompt, $reference_images = null) {
        $api_key = get_option('aicfp_rapidapi_key'); // Utilise la même clé que Midjourney
        
        if (empty($api_key)) {
            $api_key = '60bcbb5fe7mshd88f23d138be003p1be084jsnc1e30b0bb6d3';
        }
        
        $response = wp_remote_post('https://sdxl-stable-diffusion-xl-fast-text-to-image-api1.p.rapidapi.com/v2/woiipru2dawbnt/run', array(
            'timeout' => 60,
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => 'sdxl-stable-diffusion-xl-fast-text-to-image-api1.p.rapidapi.com',
                'x-rapidapi-key' => $api_key
            ),
            'body' => wp_json_encode(array(
                'input' => array(
                    'prompt' => $prompt,
                    'negative_prompt' => 'blurry, low quality, watermark, text',
                    'width' => 1024,
                    'height' => 1024,
                    'num_inference_steps' => 25,
                    'guidance_scale' => 7.5,
                    'num_images' => 1,
                    'scheduler' => 'K_EULER'
                )
            ))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($data['output']) && is_array($data['output']) && !empty($data['output'])) {
            return $data['output'][0];
        }
        
        if (isset($data['image_url'])) {
            return $data['image_url'];
        }
        
        return new WP_Error('sdxl_fast_error', __('Erreur avec SDXL Fast.', 'ai-content-factory-pro'));
    }
    
    /**
     * SDXL Food LoRA (Spécialisé recettes)
     */
    private static function generate_with_sdxl_food($prompt, $reference_images = null) {
        $api_key = get_option('aicfp_sdxl_food_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Clé API SDXL Food LoRA non configurée. Veuillez configurer votre clé RapidAPI avec abonnement SDXL Food LoRA dans Réglages.', 'ai-content-factory-pro'));
        }
        
        // Ajouter des prompts optimisés pour la nourriture
        $enhanced_prompt = "food photography, professional food styling, appetizing, " . $prompt . ", high resolution, delicious, well-lit";
        
        $payload = array(
            'prompt' => $enhanced_prompt,
            'negative_prompt' => 'bad food photography, unappetizing, blurry, poorly lit',
            'lora_model' => 'food-photography-v1',
            'num_inference_steps' => 40,
            'guidance_scale' => 8.0,
            'width' => 1024,
            'height' => 1024
        );
        
        $response = wp_remote_post('https://sdxl-food-lora.p.rapidapi.com/generate', array(
            'timeout' => 60,
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => 'sdxl-food-lora.p.rapidapi.com',
                'x-rapidapi-key' => $api_key
            ),
            'body' => wp_json_encode($payload)
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['image_url'])) {
            return $data['image_url'];
        }
        
        return new WP_Error('sdxl_food_error', __('Erreur avec SDXL Food LoRA.', 'ai-content-factory-pro'));
    }
    
    /**
     * Fine-tuned SDXL
     */
    private static function generate_with_sdxl_finetuned($prompt, $reference_images = null) {
        $api_key = get_option('aicfp_sdxl_finetuned_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Clé API Fine-tuned SDXL non configurée. Veuillez configurer dans Réglages.', 'ai-content-factory-pro'));
        }
        
        $response = wp_remote_post('https://finetuned-diffusion.p.rapidapi.com/sdxl', array(
            'timeout' => 60,
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => 'finetuned-diffusion.p.rapidapi.com',
                'x-rapidapi-key' => $api_key
            ),
            'body' => wp_json_encode(array(
                'prompt' => $prompt,
                'steps' => 35
            ))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($data['image'])) {
            return $data['image'];
        }
        
        return new WP_Error('sdxl_finetuned_error', __('Erreur avec Fine-tuned SDXL.', 'ai-content-factory-pro'));
    }
    
    /**
     * DALL-E 3 via OpenAI
     */
    private static function generate_with_dalle($prompt) {
        $api_key = get_option('aicfp_openai_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Clé API OpenAI non configurée.', 'ai-content-factory-pro'));
        }
        
        $response = wp_remote_post('https://api.openai.com/v1/images/generations', array(
            'timeout' => 60,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => wp_json_encode(array(
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'n' => 1,
                'size' => '1024x1024',
                'quality' => 'standard',
                'style' => 'natural'
            ))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($data['data'][0]['url'])) {
            return $data['data'][0]['url'];
        }
        
        return new WP_Error('dalle_error', __('Erreur avec DALL-E 3.', 'ai-content-factory-pro'));
    }
    
    /**
     * Nanobanana
     */
    private static function generate_with_nanobanana($prompt, $reference_images = null) {
        $api_key = get_option('aicfp_nanobanana_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Clé API Nanobanana non configurée. Veuillez configurer dans Réglages.', 'ai-content-factory-pro'));
        }
        
        $response = wp_remote_post('https://nanobanana-ai.p.rapidapi.com/generate', array(
            'timeout' => 45,
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => 'nanobanana-ai.p.rapidapi.com',
                'x-rapidapi-key' => $api_key
            ),
            'body' => wp_json_encode(array(
                'prompt' => $prompt,
                'aspect_ratio' => '1:1'
            ))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($data['image_url'])) {
            return $data['image_url'];
        }
        
        return new WP_Error('nanobanana_error', __('Erreur avec Nanobanana.', 'ai-content-factory-pro'));
    }
    
    /**
     * Replicate
     */
    private static function generate_with_replicate($prompt, $reference_images = null) {
        $api_key = get_option('aicfp_replicate_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Clé API Replicate non configurée.', 'ai-content-factory-pro'));
        }
        
        $response = wp_remote_post('https://api.replicate.com/v1/predictions', array(
            'timeout' => 60,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Token ' . $api_key
            ),
            'body' => wp_json_encode(array(
                'version' => 'stability-ai/sdxl:latest',
                'input' => array(
                    'prompt' => $prompt,
                    'num_outputs' => 1
                )
            ))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($data['id'])) {
            // Polling pour Replicate
            return self::poll_replicate($data['id'], $api_key);
        }
        
        return new WP_Error('replicate_error', __('Erreur avec Replicate.', 'ai-content-factory-pro'));
    }
    
    /**
     * Polling Replicate
     */
    private static function poll_replicate($prediction_id, $api_key) {
        $max_attempts = 30;
        $attempt = 0;
        
        while ($attempt < $max_attempts) {
            sleep(2);
            
            $response = wp_remote_get(
                'https://api.replicate.com/v1/predictions/' . $prediction_id,
                array(
                    'headers' => array(
                        'Authorization' => 'Token ' . $api_key
                    )
                )
            );
            
            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                
                if (isset($data['status']) && $data['status'] === 'succeeded') {
                    if (isset($data['output'][0])) {
                        return $data['output'][0];
                    }
                }
                
                if (isset($data['status']) && $data['status'] === 'failed') {
                    return new WP_Error('replicate_failed', __('Génération échouée.', 'ai-content-factory-pro'));
                }
            }
            
            $attempt++;
        }
        
        return new WP_Error('replicate_timeout', __('Timeout Replicate.', 'ai-content-factory-pro'));
    }
    
    /**
     * Flux Pro
     */
    private static function generate_with_flux_pro($prompt, $reference_images = null) {
        $api_key = get_option('aicfp_flux_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('Clé API Flux Pro non configurée. Veuillez configurer dans Réglages.', 'ai-content-factory-pro'));
        }
        
        $response = wp_remote_post('https://flux-pro.p.rapidapi.com/generate', array(
            'timeout' => 45,
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => 'flux-pro.p.rapidapi.com',
                'x-rapidapi-key' => $api_key
            ),
            'body' => wp_json_encode(array(
                'prompt' => $prompt,
                'width' => 1024,
                'height' => 1024,
                'steps' => 28
            ))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($data['image'])) {
            return $data['image'];
        }
        
        return new WP_Error('flux_error', __('Erreur avec Flux Pro.', 'ai-content-factory-pro'));
    }
    
    /**
     * Obtenir le coût par image selon l'API
     */
    public static function get_cost_per_image($api_type) {
        $costs = array(
            'midjourney' => 0.05,
            'sdxl' => 0.01,
            'sdxl-food' => 0.02,
            'sdxl-finetuned' => 0.02,
            'dalle' => 0.04,
            'nanobanana' => 0.02,
            'replicate' => 0.03,
            'flux-pro' => 0.03
        );
        
        return isset($costs[$api_type]) ? $costs[$api_type] : 0.05;
    }
    
    /**
     * Obtenir le temps estimé par image selon l'API (en minutes)
     */
    public static function get_time_per_image($api_type) {
        $times = array(
            'midjourney' => 2.0,
            'sdxl' => 0.5,
            'sdxl-food' => 0.75,
            'sdxl-finetuned' => 0.67,
            'dalle' => 0.33,
            'nanobanana' => 0.5,
            'replicate' => 1.0,
            'flux-pro' => 0.25
        );
        
        return isset($times[$api_type]) ? $times[$api_type] : 2.0;
    }
    
    /**
     * Obtenir les informations sur une API
     */
    public static function get_api_info($api_type) {
        $infos = array(
            'midjourney' => array(
                'name' => 'Midjourney',
                'description' => 'Qualité premium, style artistique',
                'cost' => 0.05,
                'time' => 2,
                'recommended' => false
            ),
            'sdxl' => array(
                'name' => 'Stable Diffusion XL',
                'description' => 'Polyvalent, rapide, économique',
                'cost' => 0.01,
                'time' => 0.5,
                'recommended' => false
            ),
            'sdxl-food' => array(
                'name' => 'SDXL Food LoRA',
                'description' => 'Spécialisé recettes, ultra-réaliste',
                'cost' => 0.02,
                'time' => 0.75,
                'recommended' => true
            ),
            'sdxl-finetuned' => array(
                'name' => 'Fine-tuned SDXL',
                'description' => 'Modèle optimisé personnalisé',
                'cost' => 0.02,
                'time' => 0.67,
                'recommended' => false
            ),
            'dalle' => array(
                'name' => 'DALL-E 3',
                'description' => 'Par OpenAI, haute qualité',
                'cost' => 0.04,
                'time' => 0.33,
                'recommended' => false
            ),
            'nanobanana' => array(
                'name' => 'Nanobanana',
                'description' => 'Rapide et créatif',
                'cost' => 0.02,
                'time' => 0.5,
                'recommended' => false
            ),
            'replicate' => array(
                'name' => 'Replicate',
                'description' => 'Accès à multiples modèles',
                'cost' => 0.03,
                'time' => 1.0,
                'recommended' => false
            ),
            'flux-pro' => array(
                'name' => 'Flux Pro',
                'description' => 'Nouvelle génération, ultra-rapide',
                'cost' => 0.03,
                'time' => 0.25,
                'recommended' => false
            )
        );
        
        return isset($infos[$api_type]) ? $infos[$api_type] : $infos['midjourney'];
    }
}
