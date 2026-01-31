<?php
/**
 * Gestionnaire des services Google (Gmail, Drive, Docs)
 *
 * @package AI_Content_Factory_Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class AICFP_Google_Services {
    
    /**
     * Créer un Google Doc avec les recettes
     */
    public static function create_google_doc($title, $recipes) {
        $api_key = get_option('aicfp_google_api_key');
        $client_email = get_option('aicfp_google_client_email');
        
        if (empty($api_key) || empty($client_email)) {
            return new WP_Error('no_google_config', __('Configuration Google non complétée.', 'ai-content-factory-pro'));
        }
        
        // Créer le contenu du document
        $content = self::format_recipes_for_doc($title, $recipes);
        
        // Appel API Google Docs (nécessite OAuth2)
        // Pour simplicité, on peut utiliser l'API REST de Google
        
        $response = wp_remote_post('https://docs.googleapis.com/v1/documents', array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => wp_json_encode(array(
                'title' => $title
            ))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($data['documentId'])) {
            $doc_id = $data['documentId'];
            $doc_url = 'https://docs.google.com/document/d/' . $doc_id . '/edit';
            
            // Insérer le contenu
            self::insert_content_to_doc($doc_id, $content, $api_key);
            
            return $doc_url;
        }
        
        return new WP_Error('doc_creation_failed', __('Impossible de créer le Google Doc.', 'ai-content-factory-pro'));
    }
    
    /**
     * Uploader des images vers Google Drive
     */
    public static function upload_images_to_drive($folder_name, $images) {
        $api_key = get_option('aicfp_google_api_key');
        
        if (empty($api_key)) {
            return new WP_Error('no_google_config', __('Configuration Google non complétée.', 'ai-content-factory-pro'));
        }
        
        // Créer un dossier sur Drive
        $folder_id = self::create_drive_folder($folder_name, $api_key);
        
        if (is_wp_error($folder_id)) {
            return $folder_id;
        }
        
        $uploaded_files = array();
        
        // Uploader chaque image
        foreach ($images as $index => $image) {
            $file_name = self::generate_image_filename($index + 1, $image);
            
            $file_id = self::upload_file_to_drive($file_name, $image['url'], $folder_id, $api_key);
            
            if (!is_wp_error($file_id)) {
                $uploaded_files[] = array(
                    'name' => $file_name,
                    'id' => $file_id,
                    'url' => 'https://drive.google.com/file/d/' . $file_id . '/view'
                );
            }
        }
        
        // Retourner le lien du dossier
        $folder_url = 'https://drive.google.com/drive/folders/' . $folder_id;
        
        return array(
            'folder_url' => $folder_url,
            'folder_id' => $folder_id,
            'files' => $uploaded_files
        );
    }
    
    /**
     * Créer un dossier sur Google Drive
     */
    private static function create_drive_folder($folder_name, $api_key) {
        $response = wp_remote_post('https://www.googleapis.com/drive/v3/files', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => wp_json_encode(array(
                'name' => $folder_name,
                'mimeType' => 'application/vnd.google-apps.folder'
            ))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($data['id'])) {
            return $data['id'];
        }
        
        return new WP_Error('folder_creation_failed', __('Impossible de créer le dossier Drive.', 'ai-content-factory-pro'));
    }
    
    /**
     * Uploader un fichier vers Google Drive
     */
    private static function upload_file_to_drive($filename, $file_url, $folder_id, $api_key) {
        // Télécharger l'image localement d'abord
        $tmp = download_url($file_url);
        
        if (is_wp_error($tmp)) {
            return $tmp;
        }
        
        $file_content = file_get_contents($tmp);
        @unlink($tmp);
        
        // Upload vers Drive
        $response = wp_remote_post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart', array(
            'timeout' => 60,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key
            ),
            'body' => array(
                'metadata' => wp_json_encode(array(
                    'name' => $filename,
                    'parents' => array($folder_id)
                )),
                'file' => $file_content
            )
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($data['id'])) {
            return $data['id'];
        }
        
        return new WP_Error('upload_failed', __('Erreur upload Drive.', 'ai-content-factory-pro'));
    }
    
    /**
     * Générer un nom de fichier intelligent pour l'image
     */
    private static function generate_image_filename($index, $image) {
        // Extraire le titre de la recette si disponible
        $recipe_title = '';
        
        if (isset($image['recipe_title'])) {
            $recipe_title = $image['recipe_title'];
        } elseif (isset($image['prompt'])) {
            // Extraire un titre court du prompt
            $recipe_title = substr($image['prompt'], 0, 50);
        }
        
        // Nettoyer le titre pour le nom de fichier
        $clean_title = sanitize_title($recipe_title);
        $clean_title = substr($clean_title, 0, 60); // Max 60 caractères
        
        if (!empty($clean_title)) {
            return $index . '-' . $clean_title . '.jpg';
        }
        
        return $index . '.jpg';
    }
    
    /**
     * Formater les recettes pour Google Doc
     */
    private static function format_recipes_for_doc($title, $recipes) {
        $content = $title . "\n\n";
        
        foreach ($recipes as $index => $recipe) {
            $content .= "Recette " . ($index + 1) . "\n";
            $content .= "=" . str_repeat("=", 50) . "\n\n";
            $content .= $recipe['content'] . "\n\n";
        }
        
        return $content;
    }
    
    /**
     * Insérer du contenu dans un Google Doc
     */
    private static function insert_content_to_doc($doc_id, $content, $api_key) {
        $response = wp_remote_post(
            'https://docs.googleapis.com/v1/documents/' . $doc_id . ':batchUpdate',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json'
                ),
                'body' => wp_json_encode(array(
                    'requests' => array(
                        array(
                            'insertText' => array(
                                'location' => array('index' => 1),
                                'text' => $content
                            )
                        )
                    )
                ))
            )
        );
        
        return !is_wp_error($response);
    }
    
    /**
     * Envoyer un email via Gmail API
     */
    public static function send_via_gmail($to, $subject, $message) {
        $api_key = get_option('aicfp_gmail_api_key');
        
        if (empty($api_key)) {
            // Fallback sur wp_mail
            return wp_mail($to, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
        }
        
        // Construire le message MIME
        $email_content = "To: $to\r\n";
        $email_content .= "Subject: $subject\r\n";
        $email_content .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
        $email_content .= $message;
        
        $encoded_message = base64_encode($email_content);
        $encoded_message = str_replace(array('+', '/', '='), array('-', '_', ''), $encoded_message);
        
        $response = wp_remote_post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => wp_json_encode(array(
                'raw' => $encoded_message
            ))
        ));
        
        return !is_wp_error($response);
    }
}
