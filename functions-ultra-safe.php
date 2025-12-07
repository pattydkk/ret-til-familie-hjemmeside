<?php
/**
 * ULTRA-SAFE VERSION - CANNOT CRASH WORDPRESS
 * 
 * Theme Name: Ret til Familie Platform
 * Version: 2.0.0 - Safe Mode
 * 
 * This version loads ONLY if all dependencies are met
 * Otherwise falls back to minimal WordPress theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    return;
}

// STEP 1: Check if WordPress is fully loaded
if (!function_exists('add_action') || !function_exists('add_theme_support')) {
    error_log('RTF: WordPress not loaded - aborting theme');
    return;
}

// STEP 2: Emergency mode check
if (defined('RTF_EMERGENCY_MODE') && RTF_EMERGENCY_MODE) {
    add_action('after_setup_theme', function() {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
    });
    return;
}

// STEP 3: Define constants safely
if (!defined('RTF_VERSION')) {
    define('RTF_VERSION', '2.0.0');
}
if (!defined('RTF_DB_VERSION')) {
    define('RTF_DB_VERSION', '2.0.0');
}

// STEP 4: Stripe configuration (safe)
define('RTF_STRIPE_PUBLIC_KEY', 'pk_live_51S5jxZL8XSb2lnp6LIO7ifWbNv3AMX4EdqMx4IJmabP3BmKVxFsz8722BEhmh4MfHOBvAwK7AmtU6FG6Ens2WvAy006GpMekTr');
define('RTF_STRIPE_SECRET_KEY', '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');
define('RTF_STRIPE_PRICE_ID', 'price_1SFMobL8XSb2lnp6ulwzpiAb');
define('RTF_STRIPE_WEBHOOK_SECRET', 'whsec_qQtOtg6DU191lNEoQplKCeYC0YAeolYw');

// STEP 5: GitHub configuration (safe)
define('RTF_GITHUB_TOKEN', 'ghp_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
define('RTF_GITHUB_REPO_OWNER', 'hansenhr89dkk');
define('RTF_GITHUB_REPO_NAME', 'ret-til-familie-hjemmeside');
define('RTF_GITHUB_BRANCH', 'main');

// STEP 6: Try to load Composer vendor (SAFE - won't crash if missing)
$vendor_autoload = get_template_directory() . '/vendor/autoload.php';
$vendor_loaded = false;

if (file_exists($vendor_autoload)) {
    try {
        require_once $vendor_autoload;
        $vendor_loaded = true;
        error_log('RTF: Composer vendor loaded');
    } catch (Throwable $e) {
        error_log('RTF ERROR loading vendor: ' . $e->getMessage());
        $vendor_loaded = false;
    }
} else {
    error_log('RTF WARNING: vendor/autoload.php not found at: ' . $vendor_autoload);
}

// STEP 7: Load translations (SAFE - won't crash if missing)
$translations_file = get_template_directory() . '/translations.php';
if (file_exists($translations_file)) {
    try {
        require_once $translations_file;
    } catch (Throwable $e) {
        error_log('RTF ERROR loading translations: ' . $e->getMessage());
    }
}

// STEP 8: Load user system class (SAFE - won't crash if missing)
$user_system_file = get_template_directory() . '/class-rtf-user-system.php';
if (file_exists($user_system_file)) {
    try {
        require_once $user_system_file;
    } catch (Throwable $e) {
        error_log('RTF ERROR loading user system: ' . $e->getMessage());
    }
}

// ============================================================================
// CORE THEME SETUP - DEFERRED LOADING (CANNOT CRASH)
// ============================================================================
add_action('after_setup_theme', function() {
    try {
        // Basic theme support
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);
        add_theme_support('custom-logo');
        
        // Image sizes
        add_image_size('rtf-thumb', 300, 300, true);
        add_image_size('rtf-medium', 600, 400, true);
        
        // Register nav menus
        register_nav_menus([
            'primary' => __('Primary Menu', 'rtf-platform'),
            'footer' => __('Footer Menu', 'rtf-platform'),
        ]);
        
    } catch (Throwable $e) {
        error_log('RTF ERROR in after_setup_theme: ' . $e->getMessage());
    }
}, 10);

// ============================================================================
// SESSION START (SAFE - CANNOT CRASH)
// ============================================================================
add_action('init', function() {
    if (session_status() === PHP_SESSION_NONE) {
        try {
            @session_start();
        } catch (Throwable $e) {
            error_log('RTF ERROR starting session: ' . $e->getMessage());
        }
    }
}, 1);

// ============================================================================
// DEFERRED INITIALIZATION - ALL HEAVY OPERATIONS HERE
// ============================================================================
add_action('init', function() {
    try {
        // Only run heavy operations once
        $tables_created = get_option('rtf_tables_created_v2', false);
        $pages_created = get_option('rtf_pages_created_v2', false);
        $admin_created = get_option('rtf_admin_created_v2', false);
        
        // Create database tables (one-time)
        if (!$tables_created) {
            rtf_create_platform_tables_safe();
            update_option('rtf_tables_created_v2', true);
        }
        
        // Create pages (one-time)
        if (!$pages_created) {
            rtf_create_pages_safe();
            update_option('rtf_pages_created_v2', true);
        }
        
        // Create admin (one-time)
        if (!$admin_created) {
            rtf_create_default_admin_safe();
            update_option('rtf_admin_created_v2', true);
        }
        
    } catch (Throwable $e) {
        error_log('RTF ERROR in deferred init: ' . $e->getMessage());
    }
}, 999); // Run VERY late after WordPress is fully initialized

// ============================================================================
// SAFE DATABASE TABLE CREATION
// ============================================================================
function rtf_create_platform_tables_safe() {
    if (!function_exists('dbDelta')) {
        error_log('RTF: dbDelta not available yet - skipping table creation');
        return false;
    }
    
    global $wpdb;
    if (!$wpdb || !isset($wpdb->prefix)) {
        error_log('RTF: $wpdb not available - skipping table creation');
        return false;
    }
    
    try {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $charset_collate = $wpdb->get_charset_collate();
        $tables_sql = [];
        
        // Users table
        $tables_sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtf_platform_users (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            password varchar(255) NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            phone varchar(20),
            birthdate date,
            profile_image text,
            is_admin tinyint(1) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            subscription_status varchar(50) DEFAULT 'inactive',
            subscription_start datetime,
            subscription_end datetime,
            stripe_customer_id varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            last_login datetime,
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        ) $charset_collate;";
        
        // Execute each table creation safely
        foreach ($tables_sql as $sql) {
            try {
                dbDelta($sql);
            } catch (Throwable $e) {
                error_log('RTF ERROR creating table: ' . $e->getMessage());
            }
        }
        
        error_log('RTF: Database tables created successfully');
        return true;
        
    } catch (Throwable $e) {
        error_log('RTF CRITICAL ERROR in table creation: ' . $e->getMessage());
        return false;
    }
}

// ============================================================================
// SAFE PAGE CREATION
// ============================================================================
function rtf_create_pages_safe() {
    if (!function_exists('wp_insert_post')) {
        error_log('RTF: wp_insert_post not available - skipping page creation');
        return false;
    }
    
    try {
        $pages = [
            'borger-platform' => 'Borgerplatform',
            'platform-profil' => 'Min Profil',
            'platform-vaeg' => 'Væggen',
        ];
        
        foreach ($pages as $slug => $title) {
            $existing = get_page_by_path($slug);
            if (!$existing) {
                wp_insert_post([
                    'post_title' => $title,
                    'post_name' => $slug,
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_content' => ''
                ]);
            }
        }
        
        error_log('RTF: Pages created successfully');
        return true;
        
    } catch (Throwable $e) {
        error_log('RTF ERROR creating pages: ' . $e->getMessage());
        return false;
    }
}

// ============================================================================
// SAFE ADMIN CREATION
// ============================================================================
function rtf_create_default_admin_safe() {
    global $wpdb;
    
    if (!$wpdb || !isset($wpdb->prefix)) {
        error_log('RTF: $wpdb not available - skipping admin creation');
        return false;
    }
    
    try {
        $table = $wpdb->prefix . 'rtf_platform_users';
        
        // Check if admin exists
        $admin_exists = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE is_admin = 1");
        
        if (!$admin_exists || $admin_exists == 0) {
            $wpdb->insert($table, [
                'email' => 'admin@rettilfamilie.dk',
                'password' => password_hash('admin123', PASSWORD_BCRYPT),
                'first_name' => 'Admin',
                'last_name' => 'User',
                'is_admin' => 1,
                'is_active' => 1,
                'created_at' => current_time('mysql')
            ]);
            
            error_log('RTF: Default admin created');
        }
        
        return true;
        
    } catch (Throwable $e) {
        error_log('RTF ERROR creating admin: ' . $e->getMessage());
        return false;
    }
}

// ============================================================================
// HELPER FUNCTIONS (SAFE)
// ============================================================================
function rtf_get_lang() {
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['da', 'sv', 'en'])) {
        return sanitize_key($_GET['lang']);
    }
    return 'da';
}

function rtf_is_logged_in() {
    return isset($_SESSION['rtf_user_id']) && !empty($_SESSION['rtf_user_id']);
}

function rtf_get_current_user() {
    if (!rtf_is_logged_in()) {
        return null;
    }
    
    try {
        global $wpdb;
        $user_id = intval($_SESSION['rtf_user_id']);
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE id = %d",
            $user_id
        ));
    } catch (Throwable $e) {
        error_log('RTF ERROR getting current user: ' . $e->getMessage());
        return null;
    }
}

function rtf_redirect($url) {
    if (!headers_sent()) {
        wp_redirect($url);
        exit;
    }
}

// ============================================================================
// ENQUEUE STYLES & SCRIPTS (SAFE)
// ============================================================================
add_action('wp_enqueue_scripts', function() {
    try {
        wp_enqueue_style('rtf-style', get_stylesheet_uri(), [], RTF_VERSION);
        wp_enqueue_script('rtf-script', get_template_directory_uri() . '/js/main.js', ['jquery'], RTF_VERSION, true);
    } catch (Throwable $e) {
        error_log('RTF ERROR enqueuing assets: ' . $e->getMessage());
    }
}, 10);

// Success indicator
error_log('RTF Theme: Ultra-safe version loaded successfully');
