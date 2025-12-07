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
// 2B. COMPOSER AUTOLOAD & DEPENDENCIES
// ============================================================================

// Load Composer dependencies (Stripe, PHPWord, PDF Parser, mPDF)
if (file_exists(RTF_THEME_DIR . '/vendor/autoload.php')) {
    require_once RTF_THEME_DIR . '/vendor/autoload.php';
    error_log('RTF: Composer autoload loaded successfully');
} else {
    error_log('RTF WARNING: Composer vendor/autoload.php not found. Run: composer install');
    // EMERGENCY: Return early if vendor not available to prevent fatal errors
    add_action('after_setup_theme', function() {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
    });
    return;
}

// Load RtfUserSystem class
if (file_exists(RTF_THEME_DIR . '/includes/class-rtf-user-system.php')) {
    require_once RTF_THEME_DIR . '/includes/class-rtf-user-system.php';
} else {
    error_log('RTF ERROR: class-rtf-user-system.php not found');
    // Continue without user system - basic WordPress will work
}

// Initialize global user system
global $rtf_user_system;
$rtf_user_system = null;

// Initialize user system after WordPress is loaded
function rtf_init_user_system() {
    global $rtf_user_system;
    if (class_exists('RtfUserSystem')) {
        $rtf_user_system = new RtfUserSystem();
        error_log('RTF: User system initialized successfully');
    } else {
        error_log('RTF ERROR: RtfUserSystem class not found');
    }
}
add_action('init', 'rtf_init_user_system', 1);

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

/**
 * Alias for rtf_is_admin() - Used in platform files
 */
function rtf_is_admin_user() {
    return rtf_is_admin();
}

/**
 * Require subscription (placeholder - always returns true for now)
 */
function rtf_require_subscription() {
    // For now, just check if logged in
    rtf_require_login();
    // TODO: Add subscription check when payment system is implemented
}

/**
 * Anonymize birthday for GDPR compliance
 */
function rtf_anonymize_birthday($birthday) {
    if (empty($birthday)) {
        return '';
    }
    $date = new DateTime($birthday);
    return $date->format('d. F'); // Only day and month
}

/**
 * Format date to Danish format
 */
function rtf_format_date($datetime) {
    if (empty($datetime)) {
        return '';
    }
    $date = new DateTime($datetime);
    $months_da = ['januar', 'februar', 'marts', 'april', 'maj', 'juni', 'juli', 'august', 'september', 'oktober', 'november', 'december'];
    return $date->format('d') . '. ' . $months_da[(int)$date->format('n') - 1] . ' ' . $date->format('Y');
}

/**
 * Get time ago string (e.g. "5 minutes ago")
 */
function rtf_time_ago($datetime) {
    if (empty($datetime)) {
        return '';
    }
    
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'lige nu';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minut' . ($mins > 1 ? 'ter' : '') . ' siden';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' time' . ($hours > 1 ? 'r' : '') . ' siden';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' dag' . ($days > 1 ? 'e' : '') . ' siden';
    } else {
        return rtf_format_date($datetime);
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
    
    // 1. Main users table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtf_platform_users (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        email varchar(255) NOT NULL,
        password varchar(255) NOT NULL,
        username varchar(100) NOT NULL,
        full_name varchar(255) NOT NULL,
        first_name varchar(100) DEFAULT NULL,
        last_name varchar(100) DEFAULT NULL,
        phone varchar(20) DEFAULT NULL,
        birthdate date DEFAULT NULL,
        birthday date DEFAULT NULL,
        bio text DEFAULT NULL,
        profile_image text DEFAULT NULL,
        language_preference varchar(10) DEFAULT 'da_DK',
        country varchar(10) DEFAULT 'DK',
        stripe_customer_id varchar(255) DEFAULT NULL,
        is_admin tinyint(1) DEFAULT 0,
        is_active tinyint(1) DEFAULT 1,
        subscription_status varchar(50) DEFAULT 'inactive',
        subscription_end_date datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        last_login datetime DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY email (email),
        UNIQUE KEY username (username)
    ) $charset_collate;";
    dbDelta($sql);
    
    // 2. Privacy settings table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtf_platform_privacy (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        gdpr_anonymize_birthday tinyint(1) DEFAULT 0,
        profile_visibility varchar(20) DEFAULT 'public',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // 3. Posts table (wall posts)
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtf_platform_posts (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        content text NOT NULL,
        likes int DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // 4. News table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtf_platform_news (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        content text NOT NULL,
        author_id bigint(20) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY author_id (author_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // 5. Friends table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtf_platform_friends (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        friend_id bigint(20) NOT NULL,
        status varchar(20) DEFAULT 'pending',
        friends_since datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY friend_id (friend_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // 6. Shares table (for wall shares)
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtf_platform_shares (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        source_type varchar(50) NOT NULL,
        source_id bigint(20) NOT NULL,
        item_id bigint(20) DEFAULT NULL,
        item_type varchar(50) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        shared_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY source_id (source_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // 7. Forum topics table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtf_platform_forum_topics (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        title varchar(255) NOT NULL,
        content text NOT NULL,
        views int DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // 8. Forum replies table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtf_platform_forum_replies (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        topic_id bigint(20) NOT NULL,
        user_id bigint(20) NOT NULL,
        content text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY topic_id (topic_id),
        KEY user_id (user_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // 9. Documents table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtf_platform_documents (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        filename varchar(255) NOT NULL,
        file_path text NOT NULL,
        file_size bigint(20) DEFAULT 0,
        is_public tinyint(1) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // 10. Images table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtf_platform_images (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        filename varchar(255) NOT NULL,
        file_path text NOT NULL,
        caption text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // 11. Stripe payments table
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rtf_stripe_payments (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) DEFAULT NULL,
        stripe_customer_id varchar(255) DEFAULT NULL,
        stripe_subscription_id varchar(255) DEFAULT NULL,
        payment_intent_id varchar(255) DEFAULT NULL,
        amount decimal(10,2) DEFAULT 0.00,
        currency varchar(3) DEFAULT 'DKK',
        status varchar(50) DEFAULT 'completed',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY stripe_customer_id (stripe_customer_id)
    ) $charset_collate;";
    dbDelta($sql);
    
    // Mark as initialized
    update_option('rtf_db_initialized_v2', true);
    
    error_log('RTF: Database initialized successfully with all tables');
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
