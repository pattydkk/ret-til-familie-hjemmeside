<?php
/**
 * Plugin Name: RTF Vendor Dependencies
 * Plugin URI: https://github.com/pattydkk/ret-til-familie-hjemmeside
 * Description: Loads Composer vendor dependencies (Stripe, mPDF, PHPWord, PDF Parser) for Ret til Familie theme. Auto-updates from GitHub.
 * Version: 1.0.1
 * Author: Ret til Familie
 * Author URI: https://rettilfamilie.com
 * License: GPL v2 or later
 * Text Domain: rtf-vendor
 * GitHub Plugin URI: pattydkk/ret-til-familie-hjemmeside
 * GitHub Branch: main
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RTF_VENDOR_VERSION', '1.0.1');
define('RTF_VENDOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RTF_VENDOR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load GitHub Updater for plugin
require_once RTF_VENDOR_PLUGIN_DIR . 'github-updater.php';

/**
 * Load Composer autoloader
 * This makes all vendor libraries available globally
 */
function rtf_vendor_load_dependencies() {
    // Try multiple paths for vendor/autoload.php
    $autoload_paths = [
        RTF_VENDOR_PLUGIN_DIR . 'vendor/autoload.php',  // In plugin folder
        get_template_directory() . '/vendor/autoload.php',  // In theme folder
        ABSPATH . 'wp-content/themes/rtf-platform/vendor/autoload.php'  // Absolute path
    ];
    
    $autoload_path = false;
    foreach ($autoload_paths as $path) {
        if (file_exists($path)) {
            $autoload_path = $path;
            break;
        }
    }
    
    if ($autoload_path) {
        require_once $autoload_path;
        
        // Log successful load
        error_log('RTF Vendor Plugin: Composer autoloader loaded successfully');
        
        // Set global flag that vendor is available
        define('RTF_VENDOR_LOADED', true);
        
        // Make Stripe available globally
        if (class_exists('\Stripe\Stripe')) {
            error_log('RTF Vendor Plugin: Stripe library loaded');
        }
        
        // Make mPDF available
        if (class_exists('\Mpdf\Mpdf')) {
            error_log('RTF Vendor Plugin: mPDF library loaded');
        }
        
        // Make PHPWord available
        if (class_exists('\PhpOffice\PhpWord\PhpWord')) {
            error_log('RTF Vendor Plugin: PHPWord library loaded');
        }
        
        // Make PDF Parser available
        if (class_exists('\Smalot\PdfParser\Parser')) {
            error_log('RTF Vendor Plugin: PDF Parser library loaded');
        }
        
    } else {
        // Vendor folder missing - show admin notice
        add_action('admin_notices', 'rtf_vendor_missing_notice');
        error_log('RTF Vendor Plugin: vendor/autoload.php not found in any location');
        error_log('RTF Vendor Plugin: Tried paths: ' . implode(', ', $autoload_paths));
    }
}

// Load vendor dependencies early (before theme loads)
add_action('plugins_loaded', 'rtf_vendor_load_dependencies', 1);

/**
 * Admin notice if vendor folder is missing
 */
function rtf_vendor_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <strong>RTF Vendor Plugin:</strong> 
            Vendor folder mangler. Upload <code>vendor/</code> til plugin mappen: 
            <code><?php echo RTF_VENDOR_PLUGIN_DIR; ?></code>
        </p>
        <p>
            Du kan uploade vendor/ via File Manager eller FTP til denne sti.
        </p>
    </div>
    <?php
}

/**
 * Plugin activation hook
 */
function rtf_vendor_activate() {
    // Check if vendor folder exists
    $autoload_path = RTF_VENDOR_PLUGIN_DIR . 'vendor/autoload.php';
    
    if (!file_exists($autoload_path)) {
        // Set transient to show notice
        set_transient('rtf_vendor_missing', true, 60);
    }
}
register_activation_hook(__FILE__, 'rtf_vendor_activate');

/**
 * Show activation notice if vendor is missing
 */
function rtf_vendor_activation_notice() {
    if (get_transient('rtf_vendor_missing')) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong>RTF Vendor Plugin aktiveret!</strong><br>
                Du skal nu uploade <code>vendor/</code> mappen til plugin folderen for at bruge dependencies.
            </p>
            <p>
                <strong>Upload sti:</strong> 
                <code>/wp-content/plugins/rtf-vendor-plugin/vendor/</code>
            </p>
        </div>
        <?php
        delete_transient('rtf_vendor_missing');
    }
}
add_action('admin_notices', 'rtf_vendor_activation_notice');

/**
 * Plugin settings page
 */
function rtf_vendor_settings_page() {
    add_options_page(
        'RTF Vendor Status',
        'RTF Vendor',
        'manage_options',
        'rtf-vendor-status',
        'rtf_vendor_status_page_html'
    );
}
add_action('admin_menu', 'rtf_vendor_settings_page');

/**
 * Settings page HTML
 */
function rtf_vendor_status_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $autoload_path = RTF_VENDOR_PLUGIN_DIR . 'vendor/autoload.php';
    $vendor_exists = file_exists($autoload_path);
    
    ?>
    <div class="wrap">
        <h1>RTF Vendor Dependencies Status</h1>
        
        <div class="card">
            <h2>Vendor Status</h2>
            <?php if ($vendor_exists) : ?>
                <p style="color: green; font-weight: bold;">✅ Vendor folder fundet!</p>
                <p><strong>Sti:</strong> <code><?php echo $autoload_path; ?></code></p>
                
                <h3>Loaded Libraries:</h3>
                <ul>
                    <li>
                        <?php echo class_exists('\Stripe\Stripe') ? '✅' : '❌'; ?>
                        <strong>Stripe PHP SDK</strong>
                        <?php if (class_exists('\Stripe\Stripe')) : ?>
                            (Version: <?php echo \Stripe\Stripe::VERSION; ?>)
                        <?php endif; ?>
                    </li>
                    <li>
                        <?php echo class_exists('\Mpdf\Mpdf') ? '✅' : '❌'; ?>
                        <strong>mPDF</strong>
                    </li>
                    <li>
                        <?php echo class_exists('\PhpOffice\PhpWord\PhpWord') ? '✅' : '❌'; ?>
                        <strong>PHPWord</strong>
                    </li>
                    <li>
                        <?php echo class_exists('\Smalot\PdfParser\Parser') ? '✅' : '❌'; ?>
                        <strong>PDF Parser</strong>
                    </li>
                </ul>
                
                <?php
                $vendor_size = rtf_get_directory_size(RTF_VENDOR_PLUGIN_DIR . 'vendor');
                ?>
                <p><strong>Vendor størrelse:</strong> <?php echo rtf_format_bytes($vendor_size); ?></p>
                
            <?php else : ?>
                <p style="color: red; font-weight: bold;">❌ Vendor folder ikke fundet</p>
                <p><strong>Forventet sti:</strong> <code><?php echo $autoload_path; ?></code></p>
                
                <h3>Upload Instruktioner:</h3>
                <ol>
                    <li>Download vendor/ fra GitHub eller pak fra ZIP</li>
                    <li>Upload til: <code><?php echo RTF_VENDOR_PLUGIN_DIR; ?>vendor/</code></li>
                    <li>Verificer at <code>vendor/autoload.php</code> eksisterer</li>
                    <li>Reload denne side for at se status</li>
                </ol>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Get directory size
 */
function rtf_get_directory_size($path) {
    $size = 0;
    if (is_dir($path)) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $file) {
            $size += $file->getSize();
        }
    }
    return $size;
}

/**
 * Format bytes to human readable
 */
function rtf_format_bytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
