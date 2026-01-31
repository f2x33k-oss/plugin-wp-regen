<?php
/**
 * Gestionnaire de fichiers
 *
 * @package AI_Content_Factory_Pro
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de gestion des fichiers
 */
class AICFP_File_Handler {
    
    /**
     * Extraire un fichier ZIP et retourner les URLs des images
     */
    public static function extract_zip($zip_path) {
        if (!file_exists($zip_path)) {
            return new WP_Error('file_not_found', __('Fichier ZIP introuvable.', 'ai-content-factory-pro'));
        }
        
        $zip = new ZipArchive();
        $result = $zip->open($zip_path);
        
        if ($result !== true) {
            return new WP_Error('zip_error', __('Impossible d\'ouvrir le fichier ZIP.', 'ai-content-factory-pro'));
        }
        
        // Créer un dossier temporaire pour l'extraction
        $upload_dir = wp_upload_dir();
        $extract_dir = $upload_dir['basedir'] . '/aicfp-temp/' . uniqid() . '/';
        wp_mkdir_p($extract_dir);
        
        // Extraire le ZIP
        $zip->extractTo($extract_dir);
        $zip->close();
        
        // Trouver toutes les images
        $image_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $images = array();
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($extract_dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $extension = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                
                if (in_array($extension, $image_extensions)) {
                    // Copier l'image dans le dossier d'uploads
                    $new_filename = uniqid() . '.' . $extension;
                    $new_path = $upload_dir['basedir'] . '/aicfp-temp/' . $new_filename;
                    
                    copy($file->getPathname(), $new_path);
                    
                    $images[] = $upload_dir['baseurl'] . '/aicfp-temp/' . $new_filename;
                }
            }
        }
        
        // Nettoyer le dossier d'extraction
        self::delete_directory($extract_dir);
        
        if (empty($images)) {
            return new WP_Error('no_images', __('Aucune image trouvée dans le ZIP.', 'ai-content-factory-pro'));
        }
        
        return $images;
    }
    
    /**
     * Supprimer un dossier récursivement
     */
    private static function delete_directory($dir) {
        if (!file_exists($dir)) {
            return;
        }
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        
        rmdir($dir);
    }
    
    /**
     * Nettoyer les fichiers temporaires anciens (plus de 7 jours)
     */
    public static function cleanup_temp_files() {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/aicfp-temp/';
        
        if (!file_exists($temp_dir)) {
            return;
        }
        
        $files = glob($temp_dir . '*');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 7 * 24 * 3600) { // 7 jours
                    unlink($file);
                }
            }
        }
    }
}
