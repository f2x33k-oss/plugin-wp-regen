<?php
/**
 * Script de Test Fonctionnel - AI Content Factory Pro
 * 
 * À exécuter depuis WordPress Admin → Outils → Site Health → Info
 * OU via wp-cli: wp eval-file TEST-FONCTIONNEL.php
 * 
 * @package AI_Content_Factory_Pro
 */

// Ne pas exécuter directement
if (!defined('ABSPATH') && !defined('WP_CLI')) {
    die('Script à exécuter depuis WordPress uniquement');
}

echo "=== TEST FONCTIONNEL AI CONTENT FACTORY PRO ===\n\n";

// Test 1 : Plugin activé
echo "Test 1: Plugin activé...\n";
if (class_exists('AI_Content_Factory_Pro')) {
    echo "✅ Plugin chargé correctement\n\n";
} else {
    echo "❌ Plugin non chargé\n\n";
    exit;
}

// Test 2 : Tables de base de données
echo "Test 2: Tables de base de données...\n";
global $wpdb;
$table_name = $wpdb->prefix . 'ai_queue';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

if ($table_exists) {
    echo "✅ Table $table_name existe\n";
    
    // Compter les colonnes
    $columns = $wpdb->get_results("DESCRIBE $table_name");
    echo "   Colonnes: " . count($columns) . "\n";
    
    // Compter les tâches
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    echo "   Tâches totales: $count\n\n";
} else {
    echo "❌ Table $table_name n'existe pas\n";
    echo "   Action: Désactiver/Réactiver le plugin\n\n";
}

// Test 3 : Clés API configurées
echo "Test 3: Clés API...\n";
$openai_key = get_option('aicfp_openai_api_key');
$rapidapi_key = get_option('aicfp_rapidapi_key');

if (!empty($openai_key)) {
    echo "✅ Clé OpenAI configurée (" . strlen($openai_key) . " caractères)\n";
} else {
    echo "⚠️  Clé OpenAI NON configurée (génération texte impossible)\n";
}

if (!empty($rapidapi_key)) {
    echo "✅ Clé RapidAPI configurée\n\n";
} else {
    echo "⚠️  Clé RapidAPI NON configurée\n\n";
}

// Test 4 : WP-Cron
echo "Test 4: WP-Cron...\n";
$cron_schedules = wp_get_schedules();
if (isset($cron_schedules['every_minute'])) {
    echo "✅ Schedule 'every_minute' enregistré\n";
} else {
    echo "❌ Schedule 'every_minute' manquant\n";
}

$next_cron = wp_next_scheduled('aicfp_process_queue');
if ($next_cron) {
    echo "✅ Cron planifié pour: " . date('Y-m-d H:i:s', $next_cron) . "\n\n";
} else {
    echo "⚠️  Cron non planifié\n";
    echo "   Action: Désactiver/Réactiver le plugin\n\n";
}

// Test 5 : Dossiers d'upload
echo "Test 5: Dossiers d'upload...\n";
$upload_dir = wp_upload_dir();
$temp_dir = $upload_dir['basedir'] . '/aicfp-temp/';

if (file_exists($temp_dir)) {
    echo "✅ Dossier temp existe: $temp_dir\n";
    echo "   Fichiers: " . count(glob($temp_dir . '*')) . "\n\n";
} else {
    echo "⚠️  Dossier temp n'existe pas\n";
    echo "   Action: Création automatique...\n";
    wp_mkdir_p($temp_dir);
    echo "   ✅ Dossier créé\n\n";
}

// Test 6 : Classes chargées
echo "Test 6: Classes chargées...\n";
$classes = array(
    'AICFP_Database',
    'AICFP_Queue_Manager',
    'AICFP_API_Handler',
    'AICFP_Image_API_Manager',
    'AICFP_Email_Handler',
    'AICFP_File_Handler',
    'AICFP_Ajax_Handler',
    'AICFP_Admin_Menu',
    'AICFP_Albums_Recettes_Page',
    'AICFP_Albums_Idees_Page',
    'AICFP_Instances_Page',
    'AICFP_Settings_Page'
);

$missing = array();
foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✅ $class\n";
    } else {
        echo "❌ $class manquante\n";
        $missing[] = $class;
    }
}

if (empty($missing)) {
    echo "\n✅ Toutes les classes chargées\n\n";
} else {
    echo "\n❌ Classes manquantes: " . count($missing) . "\n\n";
}

// Test 7 : Hooks AJAX
echo "Test 7: Hooks AJAX enregistrés...\n";
global $wp_filter;

$ajax_actions = array(
    'wp_ajax_aicfp_submit_generation',
    'wp_ajax_aicfp_submit_album_idees',
    'wp_ajax_aicfp_calculate_estimate',
    'wp_ajax_aicfp_suggest_titles',
    'wp_ajax_aicfp_search_pinterest',
    'wp_ajax_aicfp_get_queue_status',
    'wp_ajax_aicfp_start_task',
    'wp_ajax_aicfp_pause_task',
    'wp_ajax_aicfp_resume_task',
    'wp_ajax_aicfp_cancel_task',
    'wp_ajax_aicfp_delete_task'
);

$registered = 0;
foreach ($ajax_actions as $action) {
    if (isset($wp_filter[$action])) {
        echo "✅ " . str_replace('wp_ajax_', '', $action) . "\n";
        $registered++;
    } else {
        echo "❌ " . str_replace('wp_ajax_', '', $action) . " NON ENREGISTRÉ\n";
    }
}

echo "\nTotal: $registered/" . count($ajax_actions) . " enregistrés\n\n";

// Test 8 : Test de création de tâche (simulation)
echo "Test 8: Simulation création de tâche...\n";
try {
    $test_task_id = AICFP_Database::insert_task(array(
        'title' => '[TEST] 1 recette test',
        'generate_text' => 1,
        'email' => 'test@example.com',
        'status' => 'pending',
        'total_items' => 1,
        'cost_estimate' => 0.04,
        'time_estimate' => 1
    ));
    
    if (is_wp_error($test_task_id)) {
        echo "❌ Erreur création: " . $test_task_id->get_error_message() . "\n\n";
    } else {
        echo "✅ Tâche test créée avec ID: $test_task_id\n";
        
        // Vérifier qu'on peut la récupérer
        $task = AICFP_Database::get_task($test_task_id);
        if ($task) {
            echo "✅ Tâche récupérée: " . $task->title . "\n";
            
            // Nettoyer
            AICFP_Database::delete_task($test_task_id);
            echo "✅ Tâche test supprimée\n\n";
        } else {
            echo "❌ Impossible de récupérer la tâche\n\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n\n";
}

// Résumé final
echo "=== RÉSUMÉ ===\n\n";
echo "Plugin: AI Content Factory Pro v" . AICFP_VERSION . "\n";
echo "Status: ";

if ($table_exists && $registered >= 10 && class_exists('AICFP_Database')) {
    echo "✅ PRÊT À UTILISER\n\n";
    echo "Actions recommandées:\n";
    echo "1. Configurer clé OpenAI dans Réglages\n";
    echo "2. Tester avec 1 recette simple\n";
    echo "3. Vérifier debug.log si problème\n";
} else {
    echo "⚠️  CONFIGURATION REQUISE\n\n";
    echo "Actions nécessaires:\n";
    if (!$table_exists) {
        echo "- Désactiver/Réactiver le plugin\n";
    }
    if (empty($openai_key)) {
        echo "- Configurer clé OpenAI\n";
    }
    if ($registered < 10) {
        echo "- Vérifier chargement des classes\n";
    }
}

echo "\n=== FIN DES TESTS ===\n";
