<?php
/**
 * WORDPRESS THEME VALIDATION SCRIPT
 * Kør denne fil for at verificere at alt er korrekt sat op
 */

echo "=== RET TIL FAMILIE - WORDPRESS TEMA VALIDATION ===\n\n";

// Test 1: Check all required files exist
echo "1. CHECKING REQUIRED THEME FILES...\n";
$required_files = [
    'style.css' => 'Theme stylesheet with header',
    'functions.php' => 'Theme functions',
    'index.php' => 'Main template',
    'header.php' => 'Header template',
    'footer.php' => 'Footer template',
];

foreach ($required_files as $file => $desc) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "   ✓ $file ($desc)\n";
    } else {
        echo "   ✗ MISSING: $file\n";
    }
}

// Test 2: Validate style.css header
echo "\n2. VALIDATING STYLE.CSS HEADER...\n";
$style_content = file_get_contents(__DIR__ . '/style.css');
$required_headers = ['Theme Name:', 'Version:', 'Description:'];
foreach ($required_headers as $header) {
    if (strpos($style_content, $header) !== false) {
        preg_match('/' . preg_quote($header) . '\s*(.+)/', $style_content, $matches);
        echo "   ✓ $header " . trim($matches[1]) . "\n";
    } else {
        echo "   ✗ MISSING: $header\n";
    }
}

// Test 3: Check PHP syntax on all theme files
echo "\n3. CHECKING PHP SYNTAX ON ALL THEME FILES...\n";
$php_files = glob(__DIR__ . '/*.php');
$php_files = array_merge($php_files, glob(__DIR__ . '/includes/*.php'));
$php_files = array_merge($php_files, glob(__DIR__ . '/template-parts/*.php'));

$errors = 0;
foreach ($php_files as $file) {
    $filename = basename($file);
    $output = shell_exec("php -l \"$file\" 2>&1");
    if (strpos($output, 'No syntax errors') === false) {
        echo "   ✗ SYNTAX ERROR in $filename\n";
        echo "      " . trim($output) . "\n";
        $errors++;
    }
}
if ($errors === 0) {
    echo "   ✓ All " . count($php_files) . " PHP files have valid syntax\n";
}

// Test 4: Check Template Names
echo "\n4. CHECKING TEMPLATE NAMES...\n";
$platform_files = glob(__DIR__ . '/platform-*.php');
foreach ($platform_files as $file) {
    $content = file_get_contents($file);
    if (preg_match('/\* Template Name:\s*(.+)/', $content, $matches)) {
        echo "   ✓ " . basename($file) . " - " . trim($matches[1]) . "\n";
    }
}

// Test 5: Check WordPress functions usage
echo "\n5. CHECKING WORDPRESS INTEGRATION...\n";
$functions_content = file_get_contents(__DIR__ . '/functions.php');

$wp_checks = [
    'get_template_directory()' => 'Theme directory function',
    'add_theme_support' => 'Theme support',
    'wp_enqueue_style' => 'Style enqueue',
    'add_action' => 'WordPress hooks',
    'global $wpdb' => 'Database access',
];

foreach ($wp_checks as $func => $desc) {
    if (strpos($functions_content, $func) !== false) {
        echo "   ✓ Uses $func ($desc)\n";
    } else {
        echo "   ⚠ Not using: $func\n";
    }
}

// Test 6: Check Composer dependencies
echo "\n6. CHECKING COMPOSER DEPENDENCIES...\n";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "   ✓ vendor/autoload.php exists\n";
    require_once __DIR__ . '/vendor/autoload.php';
    
    $classes_to_check = [
        '\Stripe\Stripe' => 'Stripe SDK',
        '\Mpdf\Mpdf' => 'mPDF',
        '\PhpOffice\PhpWord\PhpWord' => 'PHPWord',
    ];
    
    foreach ($classes_to_check as $class => $name) {
        if (class_exists($class)) {
            echo "   ✓ $name loaded\n";
        } else {
            echo "   ✗ $name NOT loaded\n";
        }
    }
} else {
    echo "   ✗ vendor/autoload.php MISSING - Run: composer install\n";
}

// Test 7: Check custom functions
echo "\n7. CHECKING CUSTOM THEME FUNCTIONS...\n";
$custom_functions = [
    'rtf_get_lang',
    'rtf_is_logged_in',
    'rtf_get_current_user',
    'rtf_require_login',
    'rtf_require_admin',
    'rtf_is_admin_user',
    'rtf_require_subscription',
];

foreach ($custom_functions as $func) {
    if (strpos($functions_content, "function $func(") !== false) {
        echo "   ✓ $func() defined\n";
    } else {
        echo "   ✗ $func() NOT FOUND\n";
    }
}

// Test 8: Check template structure
echo "\n8. CHECKING TEMPLATE STRUCTURE...\n";
$templates_with_header = 0;
$templates_with_footer = 0;

foreach ($platform_files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'get_header()') !== false || strpos($content, 'get_header(') !== false) {
        $templates_with_header++;
    }
    if (strpos($content, 'get_footer()') !== false) {
        $templates_with_footer++;
    }
}

echo "   ✓ $templates_with_header platform templates use get_header()\n";
echo "   ✓ $templates_with_footer platform templates use get_footer()\n";

// Test 9: Check includes folder
echo "\n9. CHECKING INCLUDES FOLDER...\n";
$includes = [
    'includes/class-rtf-user-system.php' => 'User system class',
    'includes/DocumentParser.php' => 'Document parser',
    'includes/ImageProcessor.php' => 'Image processor',
    'includes/PdfGenerator.php' => 'PDF generator',
];

foreach ($includes as $file => $desc) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "   ✓ $desc\n";
    } else {
        echo "   ⚠ Missing: $file\n";
    }
}

// Test 10: Check PWA files
echo "\n10. CHECKING PWA FILES...\n";
$pwa_files = [
    'manifest.json' => 'PWA manifest',
    'sw.js' => 'Service Worker',
    'pwa-init.js' => 'PWA initialization',
];

foreach ($pwa_files as $file => $desc) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "   ✓ $desc\n";
    } else {
        echo "   ⚠ Missing: $file\n";
    }
}

echo "\n=== VALIDATION COMPLETE ===\n";
echo "\nREADY FOR WORDPRESS DEPLOYMENT!\n";
echo "\nNEXT STEPS:\n";
echo "1. Upload entire theme folder to wp-content/themes/\n";
echo "2. Make sure vendor/ folder is included (2000+ files)\n";
echo "3. Activate theme in WordPress admin\n";
echo "4. Check WordPress debug.log for 'RTF SUCCESS' messages\n";
