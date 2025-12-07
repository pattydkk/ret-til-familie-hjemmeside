<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RTF WordPress Debug</title>
    <style>
        body { font-family: monospace; background: #1e293b; color: #e2e8f0; padding: 20px; line-height: 1.6; }
        h1 { color: #3b82f6; }
        h2 { color: #60a5fa; border-bottom: 2px solid #334155; padding-bottom: 10px; margin-top: 30px; }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .warning { color: #f59e0b; font-weight: bold; }
        .box { background: #0f172a; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #334155; }
        pre { background: #0f172a; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 RTF WordPress Debug Tool</h1>
    
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>1️⃣ WordPress Load Test</h2>";
echo "<div class='box'>";

$wp_load_paths = [
    __DIR__ . '/../../../wp-load.php',
    __DIR__ . '/../../wp-load.php',
    __DIR__ . '/../wp-load.php',
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        echo "<p>Found wp-load.php at: <code>$path</code></p>";
        try {
            require_once($path);
            $wp_loaded = true;
            echo "<p class='success'>✅ WordPress loaded successfully!</p>";
            break;
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error loading WordPress: " . htmlspecialchars($e->getMessage()) . "</p>";
        } catch (Error $e) {
            echo "<p class='error'>❌ Fatal error loading WordPress: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

if (!$wp_loaded) {
    echo "<p class='error'>❌ WordPress could not be loaded from any standard location</p>";
    exit;
}

echo "</div>";

// Test 2: Theme Files
echo "<h2>2️⃣ Theme Files Check</h2>";
$theme_dir = get_template_directory();
echo "<div class='box'>";
echo "<p><strong>Theme Directory:</strong> <code>$theme_dir</code></p>";

$critical_files = [
    'functions.php',
    'header.php',
    'footer.php',
    'style.css',
    'translations.php',
    'includes/class-rtf-user-system.php',
    'platform-auth.php',
    'platform-profil.php'
];

foreach ($critical_files as $file) {
    $path = $theme_dir . '/' . $file;
    if (file_exists($path)) {
        // Try to parse file for syntax errors
        $check = shell_exec('php -l ' . escapeshellarg($path) . ' 2>&1');
        if (strpos($check, 'No syntax errors') !== false) {
            echo "<p class='success'>✅ $file - Syntax OK</p>";
        } else {
            echo "<p class='error'>❌ $file - SYNTAX ERROR:</p>";
            echo "<pre>" . htmlspecialchars($check) . "</pre>";
        }
    } else {
        echo "<p class='warning'>⚠️ $file - NOT FOUND</p>";
    }
}
echo "</div>";

// Test 3: PHP Extensions
echo "<h2>3️⃣ PHP Configuration</h2>";
echo "<div class='box'>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Required Extensions:</strong></p>";

$required_extensions = ['mysqli', 'json', 'mbstring', 'curl', 'openssl'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ $ext</p>";
    } else {
        echo "<p class='error'>❌ $ext (NOT LOADED)</p>";
    }
}

echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . "s</p>";
echo "</div>";

// Test 4: Database Connection
echo "<h2>4️⃣ Database Connection</h2>";
echo "<div class='box'>";
global $wpdb;
if ($wpdb) {
    echo "<p class='success'>✅ Database connection active</p>";
    echo "<p><strong>Database:</strong> " . DB_NAME . "</p>";
    echo "<p><strong>Table Prefix:</strong> " . $wpdb->prefix . "</p>";
    
    // Check if our tables exist
    $table = $wpdb->prefix . 'rtf_platform_users';
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        echo "<p class='success'>✅ rtf_platform_users table exists ($count users)</p>";
    } else {
        echo "<p class='warning'>⚠️ rtf_platform_users table does NOT exist</p>";
    }
} else {
    echo "<p class='error'>❌ No database connection</p>";
}
echo "</div>";

// Test 5: WordPress Error Log
echo "<h2>5️⃣ Recent WordPress Errors</h2>";
echo "<div class='box'>";

$error_log_paths = [
    ABSPATH . 'wp-content/debug.log',
    ini_get('error_log'),
    '/tmp/php-error.log',
    'C:/Windows/Temp/php-error.log'
];

$found_errors = false;
foreach ($error_log_paths as $log_path) {
    if (file_exists($log_path) && is_readable($log_path)) {
        echo "<p><strong>Log file:</strong> <code>$log_path</code></p>";
        $log_content = file_get_contents($log_path);
        $lines = explode("\n", $log_content);
        $recent_lines = array_slice($lines, -50); // Last 50 lines
        
        $rtf_errors = array_filter($recent_lines, function($line) {
            return stripos($line, 'RTF') !== false || 
                   stripos($line, 'functions.php') !== false ||
                   stripos($line, 'Fatal error') !== false ||
                   stripos($line, 'Parse error') !== false;
        });
        
        if (!empty($rtf_errors)) {
            echo "<p class='warning'>⚠️ Found " . count($rtf_errors) . " relevant error(s):</p>";
            echo "<pre>" . htmlspecialchars(implode("\n", array_slice($rtf_errors, -10))) . "</pre>";
            $found_errors = true;
        }
    }
}

if (!$found_errors) {
    echo "<p class='success'>✅ No error logs found or no recent RTF-related errors</p>";
}
echo "</div>";

// Test 6: Class Loading
echo "<h2>6️⃣ Class Loading Test</h2>";
echo "<div class='box'>";

// Check if RtfUserSystem is loaded
if (class_exists('RtfUserSystem')) {
    echo "<p class='success'>✅ RtfUserSystem class loaded</p>";
    
    global $rtf_user_system;
    if (isset($rtf_user_system) && $rtf_user_system instanceof RtfUserSystem) {
        echo "<p class='success'>✅ \$rtf_user_system global variable initialized</p>";
    } else {
        echo "<p class='error'>❌ \$rtf_user_system NOT initialized</p>";
    }
} else {
    echo "<p class='error'>❌ RtfUserSystem class NOT loaded</p>";
}

// Check vendor autoload
$vendor_path = $theme_dir . '/vendor/autoload.php';
if (file_exists($vendor_path)) {
    echo "<p class='success'>✅ vendor/autoload.php exists</p>";
    if (class_exists('\Stripe\Stripe')) {
        echo "<p class='success'>✅ Stripe library loaded</p>";
    } else {
        echo "<p class='warning'>⚠️ Stripe library NOT loaded (may need composer install)</p>";
    }
} else {
    echo "<p class='warning'>⚠️ vendor/autoload.php NOT found</p>";
}

echo "</div>";

// Test 7: Theme Options
echo "<h2>7️⃣ Theme Status</h2>";
echo "<div class='box'>";

$activated = get_option('rtf_theme_activated', false);
if ($activated) {
    echo "<p class='success'>✅ Theme has been activated</p>";
} else {
    echo "<p class='warning'>⚠️ Theme NOT activated yet (first-time setup pending)</p>";
}

$db_version = get_option('rtf_db_version', 'none');
echo "<p><strong>Database Version:</strong> $db_version</p>";

$theme_version = get_option('rtf_theme_version', 'none');
echo "<p><strong>Theme Version:</strong> $theme_version</p>";

echo "</div>";

echo "<h2>✅ Debug Complete</h2>";
echo "<div class='box'>";
echo "<p>If you see any RED errors above, those are the issues causing WordPress to crash.</p>";
echo "<p><strong>Common Solutions:</strong></p>";
echo "<ul>";
echo "<li>Syntax errors: Fix the file mentioned in the error</li>";
echo "<li>Missing classes: Check file paths and class names match</li>";
echo "<li>Database errors: Verify database credentials in wp-config.php</li>";
echo "<li>Memory errors: Increase PHP memory_limit in php.ini</li>";
echo "<li>Vendor errors: Run 'composer install' in theme directory</li>";
echo "</ul>";
echo "</div>";
?>

</body>
</html>
