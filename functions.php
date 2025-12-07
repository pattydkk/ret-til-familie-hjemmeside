<?php
/**
 * Theme Name: Ret til Familie Platform
 * Version: 2.1.0 - Clean Build
 * Description: WordPress theme for family law platform
 * Requires PHP: 7.4
 */

// ============================================================================
// 1. SECURITY & EMERGENCY MODE
// ============================================================================

if (!defined('ABSPATH')) {
    exit; // Direct access prevention
}

// Emergency mode - bypass all theme code if site broken
if (defined('RTF_EMERGENCY_MODE') && RTF_EMERGENCY_MODE) {
    add_action('after_setup_theme', function() {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
    });
    return; // Stop loading theme
}

// ============================================================================
// 2. CONSTANTS
// ============================================================================

define('RTF_VERSION', '2.1.0');
define('RTF_THEME_DIR', get_template_directory());
define('RTF_THEME_URI', get_template_directory_uri());

// ============================================================================
// 3. THEME SETUP - BASIC WORDPRESS SUPPORT
// ============================================================================

function rtf_theme_setup() {
    // Theme supports
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption']);
    add_theme_support('custom-logo');
    
    // Image sizes
    add_image_size('rtf-thumb', 300, 300, true);
    add_image_size('rtf-medium', 600, 400, true);
    
    // Menus
    register_nav_menus([
        'primary' => 'Primary Menu',
        'footer' => 'Footer Menu'
    ]);
}
add_action('after_setup_theme', 'rtf_theme_setup');

// ============================================================================
// 4. SESSION MANAGEMENT
// ============================================================================

function rtf_start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
}
add_action('init', 'rtf_start_session', 1);

// ============================================================================
// 5. ENQUEUE STYLES & SCRIPTS
// ============================================================================

function rtf_enqueue_assets() {
    wp_enqueue_style('rtf-style', get_stylesheet_uri(), [], RTF_VERSION);
    
    // Bootstrap CSS
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css', [], '5.1.3');
    
    // Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', [], '6.4.0');
    
    // jQuery (already in WordPress)
    wp_enqueue_script('jquery');
    
    // Bootstrap JS
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', ['jquery'], '5.1.3', true);
}
add_action('wp_enqueue_scripts', 'rtf_enqueue_assets');

// ============================================================================
// 6. HELPER FUNCTIONS
// ============================================================================

/**
 * Get current language
 */
function rtf_get_lang() {
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['da', 'sv', 'en'])) {
        return sanitize_key($_GET['lang']);
    }
    if (isset($_SESSION['rtf_lang'])) {
        return sanitize_key($_SESSION['rtf_lang']);
    }
    return 'da';
}

/**
 * Check if user is logged in
 */
function rtf_is_logged_in() {
    return isset($_SESSION['rtf_user_id']) && !empty($_SESSION['rtf_user_id']);
}

/**
 * Get current user from platform database
 */
function rtf_get_current_user() {
    if (!rtf_is_logged_in()) {
        return null;
    }
    
    global $wpdb;
    $user_id = intval($_SESSION['rtf_user_id']);
    $table = $wpdb->prefix . 'rtf_platform_users';
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d AND is_active = 1",
        $user_id
    ));
}

/**
 * Safe redirect
 */
function rtf_redirect($url) {
    if (!headers_sent()) {
        wp_redirect($url);
        exit;
    }
}

/**
 * Check if user is admin
 */
function rtf_is_admin() {
    $user = rtf_get_current_user();
    return $user && isset($user->is_admin) && $user->is_admin == 1;
}

/**
 * Require login for page
 */
function rtf_require_login() {
    if (!rtf_is_logged_in()) {
        rtf_redirect(home_url('/borger-platform.php?action=login'));
    }
}

/**
 * Require admin for page
 */
function rtf_require_admin() {
    rtf_require_login();
    if (!rtf_is_admin()) {
        wp_die('Adgang nægtet. Du skal være administrator.');
    }
}

// ============================================================================
// 7. TRANSLATIONS - SIMPLE ARRAY BASED
// ============================================================================

function rtf_get_translations() {
    return [
        'da' => [
            'welcome' => 'Velkommen',
            'login' => 'Log ind',
            'logout' => 'Log ud',
            'register' => 'Opret konto',
            'profile' => 'Min profil',
            'settings' => 'Indstillinger',
            'wall' => 'Væggen',
            'messages' => 'Beskeder',
            'friends' => 'Venner',
            'forum' => 'Forum',
            'kate_ai' => 'Kate AI',
            'documents' => 'Dokumenter',
            'images' => 'Billeder',
            'reports' => 'Rapporter',
            'chat' => 'Chat',
            'news' => 'Nyheder',
            'case_help' => 'Sagshjælp',
        ],
        'sv' => [
            'welcome' => 'Välkommen',
            'login' => 'Logga in',
            'logout' => 'Logga ut',
            'register' => 'Skapa konto',
            'profile' => 'Min profil',
            'settings' => 'Inställningar',
            'wall' => 'Väggen',
            'messages' => 'Meddelanden',
            'friends' => 'Vänner',
            'forum' => 'Forum',
            'kate_ai' => 'Kate AI',
            'documents' => 'Dokument',
            'images' => 'Bilder',
            'reports' => 'Rapporter',
            'chat' => 'Chatt',
            'news' => 'Nyheter',
            'case_help' => 'Fallhjälp',
        ],
        'en' => [
            'welcome' => 'Welcome',
            'login' => 'Login',
            'logout' => 'Logout',
            'register' => 'Register',
            'profile' => 'My profile',
            'settings' => 'Settings',
            'wall' => 'Wall',
            'messages' => 'Messages',
            'friends' => 'Friends',
            'forum' => 'Forum',
            'kate_ai' => 'Kate AI',
            'documents' => 'Documents',
            'images' => 'Images',
            'reports' => 'Reports',
            'chat' => 'Chat',
            'news' => 'News',
            'case_help' => 'Case Help',
        ]
    ];
}

/**
 * Get translation
 */
function rtf_t($key) {
    $lang = rtf_get_lang();
    $translations = rtf_get_translations();
    
    if (isset($translations[$lang][$key])) {
        return esc_html($translations[$lang][$key]);
    }
    
    // Fallback to Danish
    if (isset($translations['da'][$key])) {
        return esc_html($translations['da'][$key]);
    }
    
    return esc_html($key);
}

// ============================================================================
// 8. DATABASE SETUP - DEFERRED INITIALIZATION
// ============================================================================

function rtf_init_database() {
    // Only run once
    if (get_option('rtf_db_initialized_v2')) {
        return;
    }
    
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // Main users table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtf_platform_users (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        email varchar(255) NOT NULL,
        password varchar(255) NOT NULL,
        first_name varchar(100) NOT NULL,
        last_name varchar(100) NOT NULL,
        phone varchar(20) DEFAULT NULL,
        birthdate date DEFAULT NULL,
        profile_image text DEFAULT NULL,
        is_admin tinyint(1) DEFAULT 0,
        is_active tinyint(1) DEFAULT 1,
        subscription_status varchar(50) DEFAULT 'inactive',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        last_login datetime DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Mark as initialized
    update_option('rtf_db_initialized_v2', true);
    
    error_log('RTF: Database initialized successfully');
}

// Run database setup on init (late priority)
add_action('init', 'rtf_init_database', 999);

// ============================================================================
// 9. CREATE DEFAULT PAGES
// ============================================================================

function rtf_create_pages() {
    // Only run once
    if (get_option('rtf_pages_created_v2')) {
        return;
    }
    
    $pages = [
        'borger-platform' => 'Borgerplatform',
        'platform-profil' => 'Min Profil',
        'platform-vaeg' => 'Væggen',
        'platform-forum' => 'Forum',
        'platform-kate-ai' => 'Kate AI',
        'platform-chat' => 'Chat',
        'platform-venner' => 'Venner',
        'platform-dokumenter' => 'Dokumenter',
        'platform-billeder' => 'Billeder',
        'platform-nyheder' => 'Nyheder',
        'platform-rapporter' => 'Rapporter',
        'platform-indstillinger' => 'Indstillinger',
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
    
    update_option('rtf_pages_created_v2', true);
    error_log('RTF: Pages created successfully');
}

// Run page creation on init (late priority)
add_action('init', 'rtf_create_pages', 999);

// ============================================================================
// 10. CREATE DEFAULT ADMIN USER
// ============================================================================

function rtf_create_default_admin() {
    // Only run once
    if (get_option('rtf_admin_created_v2')) {
        return;
    }
    
    global $wpdb;
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
        
        error_log('RTF: Default admin user created');
    }
    
    update_option('rtf_admin_created_v2', true);
}

// Run admin creation on init (very late priority)
add_action('init', 'rtf_create_default_admin', 1000);

// ============================================================================
// 11. AUTHENTICATION ENDPOINTS
// ============================================================================

/**
 * Handle login
 */
function rtf_handle_login() {
    if (!isset($_POST['rtf_login_nonce']) || !wp_verify_nonce($_POST['rtf_login_nonce'], 'rtf_login')) {
        return;
    }
    
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    
    global $wpdb;
    $table = $wpdb->prefix . 'rtf_platform_users';
    
    $user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE email = %s AND is_active = 1",
        $email
    ));
    
    if ($user && password_verify($password, $user->password)) {
        $_SESSION['rtf_user_id'] = $user->id;
        $_SESSION['rtf_user_email'] = $user->email;
        
        // Update last login
        $wpdb->update($table, 
            ['last_login' => current_time('mysql')],
            ['id' => $user->id]
        );
        
        rtf_redirect(home_url('/platform-profil.php'));
    } else {
        $_SESSION['rtf_login_error'] = 'Forkert email eller adgangskode';
        rtf_redirect(home_url('/borger-platform.php?action=login'));
    }
}
add_action('init', 'rtf_handle_login');

/**
 * Handle logout
 */
function rtf_handle_logout() {
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        session_destroy();
        rtf_redirect(home_url('/borger-platform.php'));
    }
}
add_action('init', 'rtf_handle_logout');

/**
 * Handle registration
 */
function rtf_handle_registration() {
    if (!isset($_POST['rtf_register_nonce']) || !wp_verify_nonce($_POST['rtf_register_nonce'], 'rtf_register')) {
        return;
    }
    
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    
    global $wpdb;
    $table = $wpdb->prefix . 'rtf_platform_users';
    
    // Check if email exists
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE email = %s",
        $email
    ));
    
    if ($existing > 0) {
        $_SESSION['rtf_register_error'] = 'Email findes allerede';
        rtf_redirect(home_url('/borger-platform.php?action=register'));
        return;
    }
    
    // Create user
    $wpdb->insert($table, [
        'email' => $email,
        'password' => password_hash($password, PASSWORD_BCRYPT),
        'first_name' => $first_name,
        'last_name' => $last_name,
        'created_at' => current_time('mysql')
    ]);
    
    // Auto login
    $_SESSION['rtf_user_id'] = $wpdb->insert_id;
    $_SESSION['rtf_user_email'] = $email;
    
    rtf_redirect(home_url('/platform-profil.php'));
}
add_action('init', 'rtf_handle_registration');

// ============================================================================
// 12. SECURITY HEADERS
// ============================================================================

function rtf_security_headers() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
}
add_action('send_headers', 'rtf_security_headers');

// ============================================================================
// END OF FUNCTIONS.PHP
// ============================================================================

error_log('RTF Theme v2.1.0 loaded successfully - Clean build');
