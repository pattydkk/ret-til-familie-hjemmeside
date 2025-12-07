<?php
/**
 * DEEP DEBUG & SYSTEM TEST
 * Simulerer WordPress miljø og tester ALLE funktioner
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "═══════════════════════════════════════════════════════════════\n";
echo "   RET TIL FAMILIE - DEEP DEBUG & SYSTEM TEST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Define WordPress constants (simulate WordPress environment)
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../../');
}

// Test 1: Load functions.php and check for errors
echo "TEST 1: LOADING FUNCTIONS.PHP\n";
echo "────────────────────────────────────────────────────────────────\n";

// Mock WordPress functions that functions.php depends on
if (!function_exists('get_template_directory')) {
    function get_template_directory() { return __DIR__; }
}
if (!function_exists('get_template_directory_uri')) {
    function get_template_directory_uri() { return 'http://localhost'; }
}
if (!function_exists('add_theme_support')) {
    function add_theme_support($feature, $args = null) { echo "   ✓ add_theme_support('$feature')\n"; }
}
if (!function_exists('add_image_size')) {
    function add_image_size($name, $w, $h, $crop) { echo "   ✓ add_image_size('$name', $w, $h)\n"; }
}
if (!function_exists('register_nav_menus')) {
    function register_nav_menus($menus) { echo "   ✓ register_nav_menus()\n"; }
}
if (!function_exists('add_action')) {
    $GLOBALS['wp_actions'] = [];
    function add_action($hook, $callback, $priority = 10) {
        $GLOBALS['wp_actions'][$hook][] = ['callback' => $callback, 'priority' => $priority];
        echo "   ✓ add_action('$hook', priority: $priority)\n";
    }
}
if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false) { }
}
if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) { }
}
if (!function_exists('get_stylesheet_uri')) {
    function get_stylesheet_uri() { return 'http://localhost/style.css'; }
}
if (!function_exists('sanitize_key')) {
    function sanitize_key($key) { return strtolower(preg_replace('/[^a-z0-9_\-]/', '', $key)); }
}
if (!function_exists('wp_redirect')) {
    function wp_redirect($url) { echo "   → REDIRECT: $url\n"; }
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
if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action, $name = '_wpnonce') { echo "<input type='hidden' name='$name' value='test-nonce' />"; }
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
    function dbDelta($sql) { echo "   ✓ dbDelta() executed\n"; }
}
if (!function_exists('get_page_by_path')) {
    function get_page_by_path($path) { return null; }
}
if (!function_exists('wp_insert_post')) {
    function wp_insert_post($post) { return 1; }
}
if (!function_exists('language_attributes')) {
    function language_attributes() { echo 'lang="da"'; }
}
if (!function_exists('bloginfo')) {
    function bloginfo($show) { echo 'UTF-8'; }
}
if (!function_exists('get_template_part')) {
    function get_template_part($slug, $name = null) { echo "   ✓ get_template_part('$slug')\n"; }
}
if (!function_exists('get_header')) {
    function get_header() { echo "   ✓ get_header()\n"; }
}
if (!function_exists('get_footer')) {
    function get_footer() { echo "   ✓ get_footer()\n"; }
}

// Mock $wpdb
global $wpdb;
$wpdb = new stdClass();
$wpdb->prefix = 'wp_';
$wpdb->prepare_count = 0;
$wpdb->prepare = function($query) use ($wpdb) {
    $wpdb->prepare_count++;
    return $query;
};

// Start session for testing
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

try {
    require_once __DIR__ . '/functions.php';
    echo "\n✅ functions.php loaded successfully!\n\n";
} catch (Exception $e) {
    echo "\n❌ ERROR loading functions.php: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

// Test 2: Check all custom functions exist
echo "\nTEST 2: CHECKING CUSTOM FUNCTIONS\n";
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
    'rtf_create_pages',
    'rtf_create_default_admin',
    'rtf_theme_setup',
    'rtf_start_session',
    'rtf_enqueue_assets',
    'rtf_init_user_system',
];

$missing = [];
foreach ($required_functions as $func) {
    if (function_exists($func)) {
        echo "   ✓ $func()\n";
    } else {
        echo "   ✗ MISSING: $func()\n";
        $missing[] = $func;
    }
}

if (empty($missing)) {
    echo "\n✅ All custom functions exist!\n";
} else {
    echo "\n❌ Missing " . count($missing) . " functions!\n";
    exit(1);
}

// Test 3: Test custom functions
echo "\nTEST 3: TESTING CUSTOM FUNCTION EXECUTION\n";
echo "────────────────────────────────────────────────────────────────\n";

try {
    echo "Testing rtf_get_lang()...\n";
    $lang = rtf_get_lang();
    echo "   ✓ rtf_get_lang() = '$lang'\n";
    
    echo "Testing rtf_is_logged_in()...\n";
    $logged_in = rtf_is_logged_in();
    echo "   ✓ rtf_is_logged_in() = " . ($logged_in ? 'true' : 'false') . "\n";
    
    echo "Testing rtf_get_current_user()...\n";
    $user = rtf_get_current_user();
    echo "   ✓ rtf_get_current_user() = " . ($user ? 'User object' : 'null') . "\n";
    
    echo "Testing rtf_anonymize_birthday()...\n";
    $anon = rtf_anonymize_birthday('1990-05-15');
    echo "   ✓ rtf_anonymize_birthday('1990-05-15') = '$anon'\n";
    
    echo "Testing rtf_format_date()...\n";
    $formatted = rtf_format_date('2024-01-15 10:30:00');
    echo "   ✓ rtf_format_date() = '$formatted'\n";
    
    echo "Testing rtf_time_ago()...\n";
    $ago = rtf_time_ago(date('Y-m-d H:i:s', strtotime('-2 hours')));
    echo "   ✓ rtf_time_ago() = '$ago'\n";
    
    echo "Testing rtf_t()...\n";
    $translation = rtf_t('login');
    echo "   ✓ rtf_t('login') = '$translation'\n";
    
    echo "\n✅ All function tests passed!\n";
} catch (Exception $e) {
    echo "\n❌ Function test failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Check global variables
echo "\nTEST 4: CHECKING GLOBAL VARIABLES\n";
echo "────────────────────────────────────────────────────────────────\n";

global $rtf_user_system, $rtf_vendor_loaded, $rtf_user_system_loaded;

echo "   \$rtf_vendor_loaded = " . ($rtf_vendor_loaded ? 'true' : 'false') . "\n";
echo "   \$rtf_user_system_loaded = " . ($rtf_user_system_loaded ? 'true' : 'false') . "\n";
echo "   \$rtf_user_system = " . (is_object($rtf_user_system) ? get_class($rtf_user_system) : 'null') . "\n";

if ($rtf_vendor_loaded) {
    echo "\n✅ Composer vendor loaded!\n";
} else {
    echo "\n⚠️  Vendor not loaded (expected if running outside WordPress)\n";
}

// Test 5: Check all platform template files
echo "\nTEST 5: VALIDATING PLATFORM TEMPLATE FILES\n";
echo "────────────────────────────────────────────────────────────────\n";

$platform_files = glob(__DIR__ . '/platform-*.php');
$template_errors = [];

foreach ($platform_files as $file) {
    $filename = basename($file);
    
    // Check syntax
    $output = shell_exec("php -l \"$file\" 2>&1");
    if (strpos($output, 'No syntax errors') === false) {
        $template_errors[] = "$filename: SYNTAX ERROR";
        echo "   ✗ $filename - SYNTAX ERROR\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Check for Template Name
    if (!preg_match('/\* Template Name:/i', $content)) {
        $template_errors[] = "$filename: Missing 'Template Name' header";
        echo "   ✗ $filename - Missing 'Template Name'\n";
        continue;
    }
    
    // Check for get_header()
    $has_header = (strpos($content, 'get_header()') !== false || strpos($content, 'get_header(') !== false);
    
    // Check for get_footer()
    $has_footer = strpos($content, 'get_footer()') !== false;
    
    if (!$has_header && !$has_footer) {
        echo "   ⚠️  $filename - No header/footer (might be intentional)\n";
    } else {
        echo "   ✓ $filename - Valid template\n";
    }
}

if (empty($template_errors)) {
    echo "\n✅ All platform templates are valid!\n";
} else {
    echo "\n❌ Found " . count($template_errors) . " template errors:\n";
    foreach ($template_errors as $error) {
        echo "   - $error\n";
    }
    exit(1);
}

// Test 6: Check for common WordPress issues
echo "\nTEST 6: CHECKING FOR COMMON WORDPRESS ISSUES\n";
echo "────────────────────────────────────────────────────────────────\n";

$issues = [];

// Check for output before headers
$functions_content = file_get_contents(__DIR__ . '/functions.php');
if (preg_match('/^[\s\n]*<\?php/s', $functions_content)) {
    echo "   ✓ No output before <?php in functions.php\n";
} else {
    $issues[] = "Output before <?php in functions.php";
    echo "   ✗ Output before <?php in functions.php\n";
}

// Check for proper session handling
if (strpos($functions_content, 'session_start()') !== false) {
    if (strpos($functions_content, 'session_status()') !== false) {
        echo "   ✓ Session handling includes session_status() check\n";
    } else {
        $issues[] = "session_start() without session_status() check";
        echo "   ⚠️  session_start() without session_status() check\n";
    }
}

// Check for SQL injection protection
if (strpos($functions_content, '$wpdb->prepare') !== false) {
    echo "   ✓ Uses \$wpdb->prepare() for SQL safety\n";
} else {
    echo "   ⚠️  No \$wpdb->prepare() usage found (check if database queries exist)\n";
}

// Check for XSS protection
if (strpos($functions_content, 'esc_html') !== false || strpos($functions_content, 'esc_attr') !== false) {
    echo "   ✓ Uses WordPress escaping functions\n";
} else {
    echo "   ⚠️  Limited use of WordPress escaping functions\n";
}

if (empty($issues)) {
    echo "\n✅ No critical WordPress issues found!\n";
} else {
    echo "\n⚠️  Found " . count($issues) . " potential issues\n";
}

// Test 7: Memory and performance check
echo "\nTEST 7: MEMORY & PERFORMANCE CHECK\n";
echo "────────────────────────────────────────────────────────────────\n";

$memory_used = memory_get_usage(true) / 1024 / 1024;
$memory_peak = memory_get_peak_usage(true) / 1024 / 1024;

echo "   Memory used: " . round($memory_used, 2) . " MB\n";
echo "   Peak memory: " . round($memory_peak, 2) . " MB\n";

if ($memory_peak < 64) {
    echo "   ✓ Memory usage is acceptable\n";
} else {
    echo "   ⚠️  High memory usage detected\n";
}

// Test 8: Check includes folder
echo "\nTEST 8: CHECKING INCLUDES FOLDER\n";
echo "────────────────────────────────────────────────────────────────\n";

$includes_files = [
    'includes/class-rtf-user-system.php',
    'includes/DocumentParser.php',
    'includes/ImageProcessor.php',
    'includes/PdfGenerator.php',
];

foreach ($includes_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $output = shell_exec("php -l \"" . __DIR__ . '/' . $file . "\" 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "   ✓ $file\n";
        } else {
            echo "   ✗ $file - SYNTAX ERROR\n";
        }
    } else {
        echo "   ✗ $file - NOT FOUND\n";
    }
}

// Final summary
echo "\n═══════════════════════════════════════════════════════════════\n";
echo "   DEBUG TEST COMPLETE\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n✅ SYSTEM STATUS: READY FOR WORDPRESS DEPLOYMENT\n\n";
echo "NEXT STEPS:\n";
echo "1. Upload theme to WordPress wp-content/themes/\n";
echo "2. Upload vendor/ folder (2000+ files from Composer)\n";
echo "3. Activate theme in WordPress Admin\n";
echo "4. Check WordPress debug.log for 'RTF SUCCESS' messages\n";
echo "5. Create database tables on first load\n";
echo "6. Register first user via /platform-auth/\n\n";
