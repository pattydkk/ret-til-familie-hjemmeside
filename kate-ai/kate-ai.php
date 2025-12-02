<?php
/**
 * Plugin Name: Kate AI - Ret til Familie Assistant
 * Description: 100% deterministisk AI assistent til juridisk rådgivning uden eksterne API'er
 * Version: 1.0.0
 * Author: Ret til Familie
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define Kate AI paths
define('KATE_AI_PATH', dirname(__FILE__));
define('KATE_AI_URL', get_stylesheet_directory_uri() . '/kate-ai');
define('KATE_AI_VERSION', '1.0.0');

// Autoloader for Kate AI classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'KateAI\\') === 0) {
        $class = str_replace('KateAI\\', '', $class);
        $class = str_replace('\\', '/', $class);
        $file = KATE_AI_PATH . '/src/' . $class . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Initialize Kate AI - LAZY LOAD (only when needed)
// Remove heavy initialization from 'init' hook to prevent timeout

// Activation hook
register_activation_hook(__FILE__, 'kate_ai_activate');

function kate_ai_activate() {
    // Create Kate chat log table
    global $wpdb;
    $table_name = $wpdb->prefix . 'rtf_platform_kate_chat';
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        session_id VARCHAR(255) NOT NULL,
        user_id BIGINT(20) UNSIGNED DEFAULT NULL,
        message TEXT NOT NULL,
        response TEXT NOT NULL,
        intent_id VARCHAR(100) DEFAULT NULL,
        confidence DECIMAL(5,2) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY session_id (session_id),
        KEY user_id (user_id),
        KEY created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
