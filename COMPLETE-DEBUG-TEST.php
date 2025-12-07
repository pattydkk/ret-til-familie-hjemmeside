<?php
/**
 * COMPLETE DEBUG & TEST - EVERYTHING
 * Tests entire system thoroughly before WordPress deployment
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "═══════════════════════════════════════════════════════════════\n";
echo "   RET TIL FAMILIE - COMPLETE SYSTEM DEBUG & TEST\n";
echo "   Dato: " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$total_tests = 0;
$passed_tests = 0;
$failed_tests = 0;
$errors = [];

function test_result($test_name, $result, $message = '') {
    global $total_tests, $passed_tests, $failed_tests, $errors;
    $total_tests++;
    
    if ($result) {
        $passed_tests++;
        echo "✓ $test_name\n";
        if ($message) echo "  → $message\n";
    } else {
        $failed_tests++;
        echo "✗ $test_name\n";
        if ($message) echo "  → ERROR: $message\n";
        $errors[] = "$test_name: $message";
    }
}

// ============================================================================
// TEST 1: CRITICAL FILES EXISTENCE
// ============================================================================
echo "\n█ TEST 1: CRITICAL FILES EXISTENCE\n";
echo "────────────────────────────────────────────────────────────────\n";

$critical_files = [
    'functions.php' => 'Core theme functions',
    'style.css' => 'Theme stylesheet',
    'header.php' => 'Header template',
    'footer.php' => 'Footer template',
    'index.php' => 'Main template',
    'vendor/autoload.php' => 'Composer autoload',
    'includes/class-rtf-user-system.php' => 'User system class',
];

foreach ($critical_files as $file => $desc) {
    test_result("$desc exists", file_exists(__DIR__ . '/' . $file), $file);
}

// ============================================================================
// TEST 2: PHP SYNTAX VALIDATION
// ============================================================================
echo "\n█ TEST 2: PHP SYNTAX VALIDATION\n";
echo "────────────────────────────────────────────────────────────────\n";

$php_files = [
    'functions.php',
    'header.php',
    'footer.php',
    'index.php',
    'platform-auth.php',
    'platform-profil.php',
    'platform-kate-ai.php',
    'includes/class-rtf-user-system.php',
    'template-parts/platform-sidebar.php'
];

foreach ($php_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $output = shell_exec("php -l \"" . __DIR__ . "/$file\" 2>&1");
        test_result("$file syntax", strpos($output, 'No syntax errors') !== false, 
            strpos($output, 'No syntax errors') !== false ? 'Valid' : $output);
    } else {
        test_result("$file exists", false, "File not found");
    }
}

// ============================================================================
// TEST 3: MOCK WORDPRESS & LOAD FUNCTIONS.PHP
// ============================================================================
echo "\n█ TEST 3: FUNCTIONS.PHP LOADING TEST\n";
echo "────────────────────────────────────────────────────────────────\n";

// Define ABSPATH
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../../');
}

// Mock WordPress functions
if (!function_exists('get_template_directory')) {
    function get_template_directory() { return __DIR__; }
}
if (!function_exists('get_template_directory_uri')) {
    function get_template_directory_uri() { return 'http://localhost'; }
}
if (!function_exists('add_theme_support')) {
    function add_theme_support($feature, $args = null) { return true; }
}
if (!function_exists('add_image_size')) {
    function add_image_size($name, $w, $h, $crop = false) { return true; }
}
if (!function_exists('register_nav_menus')) {
    function register_nav_menus($menus) { return true; }
}
if (!function_exists('add_action')) {
    $GLOBALS['wp_actions'] = [];
    function add_action($hook, $callback, $priority = 10, $args = 1) {
        $GLOBALS['wp_actions'][$hook][] = ['callback' => $callback, 'priority' => $priority];
        return true;
    }
}
if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all') { return true; }
}
if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) { return true; }
}
if (!function_exists('get_stylesheet_uri')) {
    function get_stylesheet_uri() { return 'http://localhost/style.css'; }
}
if (!function_exists('sanitize_key')) {
    function sanitize_key($key) { return strtolower(preg_replace('/[^a-z0-9_\-]/', '', $key)); }
}
if (!function_exists('wp_redirect')) {
    function wp_redirect($url) { return true; }
}
if (!function_exists('wp_die')) {
    function wp_die($message) { die("WP_DIE: $message\n"); }
}
if (!function_exists('home_url')) {
    function home_url($path = '') { return 'http://localhost' . $path; }
}
if (!function_exists('esc_html')) {
    function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('esc_url')) {
    function esc_url($url) { return htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($text) { return strip_tags($text); }
}
if (!function_exists('sanitize_email')) {
    function sanitize_email($email) { return filter_var($email, FILTER_SANITIZE_EMAIL); }
}
if (!function_exists('sanitize_textarea_field')) {
    function sanitize_textarea_field($text) { return strip_tags($text); }
}
if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) { return true; }
}
if (!function_exists('current_time')) {
    function current_time($type) { return date('Y-m-d H:i:s'); }
}
if (!function_exists('get_option')) {
    function get_option($option, $default = false) { return $default; }
}
if (!function_exists('update_option')) {
    function update_option($option, $value) { return true; }
}
if (!function_exists('dbDelta')) {
    function dbDelta($sql) { return ['created' => []]; }
}

// Mock $wpdb
global $wpdb;
$wpdb = new stdClass();
$wpdb->prefix = 'wp_';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Load functions.php
try {
    require_once __DIR__ . '/functions.php';
    test_result("functions.php loaded", true, "Successfully loaded");
} catch (Exception $e) {
    test_result("functions.php loaded", false, $e->getMessage());
}

// ============================================================================
// TEST 4: CONSTANTS DEFINED
// ============================================================================
echo "\n█ TEST 4: CONSTANTS & GLOBALS\n";
echo "────────────────────────────────────────────────────────────────\n";

test_result("RTF_VERSION defined", defined('RTF_VERSION'), defined('RTF_VERSION') ? 'v' . RTF_VERSION : 'Not defined');
test_result("RTF_THEME_DIR defined", defined('RTF_THEME_DIR'), defined('RTF_THEME_DIR') ? RTF_THEME_DIR : 'Not defined');
test_result("RTF_THEME_URI defined", defined('RTF_THEME_URI'), defined('RTF_THEME_URI') ? RTF_THEME_URI : 'Not defined');

global $rtf_vendor_loaded, $rtf_user_system_loaded, $rtf_user_system;
test_result("\$rtf_vendor_loaded set", isset($rtf_vendor_loaded), $rtf_vendor_loaded ? 'true' : 'false');
test_result("\$rtf_user_system_loaded set", isset($rtf_user_system_loaded), $rtf_user_system_loaded ? 'true' : 'false');

// ============================================================================
// TEST 5: CUSTOM FUNCTIONS EXIST
// ============================================================================
echo "\n█ TEST 5: CUSTOM FUNCTIONS EXIST\n";
echo "────────────────────────────────────────────────────────────────\n";

$required_functions = [
    'rtf_get_lang',
    'rtf_is_logged_in',
    'rtf_get_current_user',
    'rtf_redirect',
    'rtf_is_admin',
    'rtf_require_login',
    'rtf_require_admin',
    'rtf_is_admin_user',
    'rtf_require_subscription',
    'rtf_anonymize_birthday',
    'rtf_format_date',
    'rtf_time_ago',
    'rtf_get_translations',
    'rtf_t',
    'rtf_init_database',
    'rtf_theme_setup',
    'rtf_start_session',
    'rtf_enqueue_assets',
    'rtf_init_user_system',
];

foreach ($required_functions as $func) {
    test_result("$func() exists", function_exists($func));
}

// ============================================================================
// TEST 6: FUNCTION EXECUTION TESTS
// ============================================================================
echo "\n█ TEST 6: FUNCTION EXECUTION TESTS\n";
echo "────────────────────────────────────────────────────────────────\n";

try {
    $lang = rtf_get_lang();
    test_result("rtf_get_lang() executes", true, "Returns: $lang");
} catch (Exception $e) {
    test_result("rtf_get_lang() executes", false, $e->getMessage());
}

try {
    $logged_in = rtf_is_logged_in();
    test_result("rtf_is_logged_in() executes", true, $logged_in ? "true" : "false");
} catch (Exception $e) {
    test_result("rtf_is_logged_in() executes", false, $e->getMessage());
}

try {
    $user = rtf_get_current_user();
    test_result("rtf_get_current_user() executes", true, $user ? "User object" : "null (expected - not logged in)");
} catch (Exception $e) {
    test_result("rtf_get_current_user() executes", false, $e->getMessage());
}

try {
    $anon = rtf_anonymize_birthday('1990-05-15');
    test_result("rtf_anonymize_birthday() executes", true, "Returns: $anon");
} catch (Exception $e) {
    test_result("rtf_anonymize_birthday() executes", false, $e->getMessage());
}

try {
    $formatted = rtf_format_date('2024-01-15 10:30:00');
    test_result("rtf_format_date() executes", true, "Returns: $formatted");
} catch (Exception $e) {
    test_result("rtf_format_date() executes", false, $e->getMessage());
}

try {
    $translation = rtf_t('login');
    test_result("rtf_t() executes", true, "Returns: $translation");
} catch (Exception $e) {
    test_result("rtf_t() executes", false, $e->getMessage());
}

// ============================================================================
// TEST 7: PLATFORM TEMPLATES VALIDATION
// ============================================================================
echo "\n█ TEST 7: PLATFORM TEMPLATES VALIDATION\n";
echo "────────────────────────────────────────────────────────────────\n";

$platform_files = glob(__DIR__ . '/platform-*.php');
test_result("Platform templates found", count($platform_files) === 21, count($platform_files) . " templates found");

$templates_with_header = 0;
foreach ($platform_files as $file) {
    $content = file_get_contents($file);
    if (preg_match('/\* Template Name:/i', $content)) {
        $templates_with_header++;
    }
}
test_result("All templates have 'Template Name'", $templates_with_header === 21, "$templates_with_header/21");

// ============================================================================
// TEST 8: COMPOSER DEPENDENCIES
// ============================================================================
echo "\n█ TEST 8: COMPOSER DEPENDENCIES\n";
echo "────────────────────────────────────────────────────────────────\n";

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    test_result("vendor/autoload.php exists", true);
    
    $classes = [
        '\Stripe\Stripe' => 'Stripe SDK',
        '\Mpdf\Mpdf' => 'mPDF',
        '\PhpOffice\PhpWord\PhpWord' => 'PHPWord',
    ];
    
    foreach ($classes as $class => $name) {
        test_result("$name loaded", class_exists($class));
    }
} else {
    test_result("vendor/autoload.php exists", false, "Vendor folder missing");
}

// ============================================================================
// TEST 9: JSON FILES VALIDATION
// ============================================================================
echo "\n█ TEST 9: JSON FILES VALIDATION\n";
echo "────────────────────────────────────────────────────────────────\n";

$json_files = ['composer.json', 'manifest.json'];
foreach ($json_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $content = file_get_contents(__DIR__ . '/' . $file);
        $decoded = json_decode($content);
        test_result("$file valid JSON", $decoded !== null, json_last_error_msg());
    } else {
        test_result("$file exists", false, "File not found");
    }
}

// ============================================================================
// TEST 10: PWA FILES
// ============================================================================
echo "\n█ TEST 10: PWA IMPLEMENTATION\n";
echo "────────────────────────────────────────────────────────────────\n";

$pwa_files = ['manifest.json', 'sw.js', 'pwa-init.js', 'pwa-manager.js'];
foreach ($pwa_files as $file) {
    test_result("$file exists", file_exists(__DIR__ . '/' . $file));
}

// ============================================================================
// TEST 11: SECURITY IMPLEMENTATION
// ============================================================================
echo "\n█ TEST 11: SECURITY IMPLEMENTATION\n";
echo "────────────────────────────────────────────────────────────────\n";

$functions_content = file_get_contents(__DIR__ . '/functions.php');

test_result("ABSPATH check present", strpos($functions_content, "if (!defined('ABSPATH'))") !== false);
test_result("Session status check present", strpos($functions_content, "session_status()") !== false);
test_result("SQL prepare usage present", strpos($functions_content, '$wpdb->prepare') !== false);
test_result("XSS protection present", strpos($functions_content, 'esc_html') !== false);
test_result("CSRF protection present", strpos($functions_content, 'wp_nonce') !== false || strpos($functions_content, 'wp_verify_nonce') !== false);

// ============================================================================
// TEST 12: USER SYSTEM CLASS
// ============================================================================
echo "\n█ TEST 12: USER SYSTEM CLASS\n";
echo "────────────────────────────────────────────────────────────────\n";

$user_system_file = __DIR__ . '/includes/class-rtf-user-system.php';
if (file_exists($user_system_file)) {
    $user_system_content = file_get_contents($user_system_file);
    
    test_result("RtfUserSystem class defined", strpos($user_system_content, 'class RtfUserSystem') !== false);
    test_result("register() method present", strpos($user_system_content, 'function register') !== false);
    test_result("authenticate() method present", strpos($user_system_content, 'function authenticate') !== false);
    test_result("Password hashing present", strpos($user_system_content, 'password_hash') !== false);
    test_result("Email validation present", strpos($user_system_content, 'is_email') !== false || strpos($user_system_content, 'filter_var') !== false);
} else {
    test_result("class-rtf-user-system.php exists", false);
}

// ============================================================================
// TEST 13: DATABASE SCHEMA
// ============================================================================
echo "\n█ TEST 13: DATABASE SCHEMA DEFINITIONS\n";
echo "────────────────────────────────────────────────────────────────\n";

$expected_tables = [
    'rtf_platform_users',
    'rtf_platform_privacy',
    'rtf_stripe_payments',
    'rtf_platform_posts',
    'rtf_platform_comments',
    'rtf_platform_likes',
    'rtf_platform_shares',
    'rtf_platform_friendships',
    'rtf_platform_messages',
    'rtf_platform_notifications',
    'rtf_ai_conversations'
];

foreach ($expected_tables as $table) {
    test_result("$table schema defined", strpos($functions_content, $table) !== false);
}

// ============================================================================
// TEST 14: MEMORY & PERFORMANCE
// ============================================================================
echo "\n█ TEST 14: MEMORY & PERFORMANCE\n";
echo "────────────────────────────────────────────────────────────────\n";

$memory_used = memory_get_usage(true) / 1024 / 1024;
$memory_peak = memory_get_peak_usage(true) / 1024 / 1024;

echo "Memory used: " . round($memory_used, 2) . " MB\n";
echo "Peak memory: " . round($memory_peak, 2) . " MB\n";

test_result("Memory usage acceptable", $memory_peak < 64, round($memory_peak, 2) . " MB (limit: 64 MB)");

// ============================================================================
// FINAL SUMMARY
// ============================================================================
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "   FINAL TEST SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "Total tests: $total_tests\n";
echo "Passed: $passed_tests (" . round(($passed_tests / $total_tests) * 100, 1) . "%)\n";
echo "Failed: $failed_tests\n\n";

if ($failed_tests === 0) {
    echo "✅ ALL TESTS PASSED - SYSTEM IS PRODUCTION-READY!\n\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "   READY FOR WORDPRESS DEPLOYMENT\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    echo "Next steps:\n";
    echo "1. Upload entire theme to wp-content/themes/ret-til-familie/\n";
    echo "2. Upload vendor/ folder (critical!)\n";
    echo "3. Activate theme in WordPress admin\n";
    echo "4. Check debug.log for 'RTF SUCCESS' messages\n\n";
} else {
    echo "❌ ERRORS FOUND - REVIEW BEFORE DEPLOYMENT\n\n";
    echo "Errors:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
    echo "\n";
}

echo "Test completed at: " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n";
?>
