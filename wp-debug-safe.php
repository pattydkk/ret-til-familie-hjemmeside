<?php
/**
 * SAFE WordPress Debug Tool
 * Upload this file to your WordPress root directory
 * Access via: https://your-site.dk/wp-debug-safe.php
 */

// Start output buffering to catch any errors
ob_start();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>WordPress Safe Debug</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 2em; margin-bottom: 10px; }
        .content { padding: 30px; }
        .test-section {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #2563eb;
        }
        .test-section h2 {
            color: #1e293b;
            margin-bottom: 15px;
            font-size: 1.4em;
        }
        .success {
            background: #d1fae5;
            border-left: 4px solid #059669;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            color: #065f46;
        }
        .error {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            color: #991b1b;
        }
        .warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            color: #92400e;
        }
        .info {
            background: #dbeafe;
            border-left: 4px solid #2563eb;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            color: #1e40af;
        }
        code {
            background: #1e293b;
            color: #10b981;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        .code-block {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        .icon { font-size: 1.3em; margin-right: 8px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: white;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f1f5f9;
            font-weight: 600;
            color: #1e293b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 WordPress Safe Debug Tool</h1>
            <p>Comprehensive diagnostic for Ret til Familie Platform</p>
        </div>
        <div class="content">

<?php

// TEST 1: PHP Version and Extensions
echo '<div class="test-section">';
echo '<h2>1️⃣ PHP Environment</h2>';

$php_version = phpversion();
if (version_compare($php_version, '7.4', '>=')) {
    echo '<div class="success"><span class="icon">✅</span><strong>PHP Version:</strong> ' . $php_version . ' (Compatible)</div>';
} else {
    echo '<div class="error"><span class="icon">❌</span><strong>PHP Version:</strong> ' . $php_version . ' (Requires 7.4+)</div>';
}

$required_extensions = ['mysqli', 'mbstring', 'json', 'curl', 'openssl'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo '<div class="success"><span class="icon">✅</span>Extension: <code>' . $ext . '</code></div>';
    } else {
        echo '<div class="error"><span class="icon">❌</span>Missing extension: <code>' . $ext . '</code></div>';
    }
}

echo '</div>';

// TEST 2: File System Check
echo '<div class="test-section">';
echo '<h2>2️⃣ WordPress Installation</h2>';

$wp_load = __DIR__ . '/wp-load.php';
$wp_config = __DIR__ . '/wp-config.php';

if (file_exists($wp_load)) {
    echo '<div class="success"><span class="icon">✅</span><code>wp-load.php</code> found</div>';
} else {
    echo '<div class="error"><span class="icon">❌</span><code>wp-load.php</code> NOT FOUND - Upload to WordPress root!</div>';
}

if (file_exists($wp_config)) {
    echo '<div class="success"><span class="icon">✅</span><code>wp-config.php</code> found</div>';
} else {
    echo '<div class="error"><span class="icon">❌</span><code>wp-config.php</code> NOT FOUND</div>';
}

echo '</div>';

// TEST 3: Try to load WordPress (SAFE MODE)
echo '<div class="test-section">';
echo '<h2>3️⃣ WordPress Loading Test</h2>';

if (file_exists($wp_load)) {
    try {
        // Disable all error output temporarily
        $old_error_reporting = error_reporting(0);
        ini_set('display_errors', 0);
        
        // Try to load WordPress
        define('WP_USE_THEMES', false);
        @include_once($wp_load);
        
        // Restore error reporting
        error_reporting($old_error_reporting);
        
        if (defined('ABSPATH')) {
            echo '<div class="success"><span class="icon">✅</span>WordPress loaded successfully!</div>';
            echo '<div class="info"><strong>WordPress Path:</strong> <code>' . ABSPATH . '</code></div>';
            
            // Check if functions are available
            if (function_exists('get_option')) {
                echo '<div class="success"><span class="icon">✅</span>WordPress functions available</div>';
                
                $site_url = get_option('siteurl');
                $home_url = get_option('home');
                echo '<div class="info"><strong>Site URL:</strong> ' . $site_url . '</div>';
                echo '<div class="info"><strong>Home URL:</strong> ' . $home_url . '</div>';
            } else {
                echo '<div class="error"><span class="icon">❌</span>WordPress functions NOT available</div>';
            }
            
        } else {
            echo '<div class="error"><span class="icon">❌</span>WordPress did not load - ABSPATH not defined</div>';
        }
        
    } catch (Exception $e) {
        echo '<div class="error"><span class="icon">❌</span><strong>WordPress Load Error:</strong> ' . $e->getMessage() . '</div>';
    } catch (Error $e) {
        echo '<div class="error"><span class="icon">❌</span><strong>WordPress Fatal Error:</strong> ' . $e->getMessage() . '</div>';
    }
} else {
    echo '<div class="warning"><span class="icon">⚠️</span>Skipping WordPress load test (wp-load.php not found)</div>';
}

echo '</div>';

// TEST 4: Theme Files Check
echo '<div class="test-section">';
echo '<h2>4️⃣ Theme Files Check</h2>';

if (defined('ABSPATH')) {
    $theme_dir = ABSPATH . 'wp-content/themes/ret-til-familie-hjemmeside/';
    
    if (is_dir($theme_dir)) {
        echo '<div class="success"><span class="icon">✅</span>Theme directory found: <code>' . $theme_dir . '</code></div>';
        
        $critical_files = [
            'functions.php',
            'style.css',
            'index.php',
            'header.php',
            'footer.php'
        ];
        
        foreach ($critical_files as $file) {
            if (file_exists($theme_dir . $file)) {
                echo '<div class="success"><span class="icon">✅</span><code>' . $file . '</code></div>';
            } else {
                echo '<div class="error"><span class="icon">❌</span><code>' . $file . '</code> MISSING</div>';
            }
        }
        
        // Check functions.php syntax
        $functions_file = $theme_dir . 'functions.php';
        if (file_exists($functions_file)) {
            $syntax_check = shell_exec('php -l ' . escapeshellarg($functions_file) . ' 2>&1');
            if (strpos($syntax_check, 'No syntax errors') !== false) {
                echo '<div class="success"><span class="icon">✅</span><strong>functions.php syntax:</strong> Valid</div>';
            } else {
                echo '<div class="error"><span class="icon">❌</span><strong>functions.php syntax error:</strong><div class="code-block">' . htmlspecialchars($syntax_check) . '</div></div>';
            }
        }
        
    } else {
        echo '<div class="error"><span class="icon">❌</span>Theme directory NOT FOUND: <code>' . $theme_dir . '</code></div>';
    }
} else {
    echo '<div class="warning"><span class="icon">⚠️</span>WordPress not loaded - cannot check theme files</div>';
}

echo '</div>';

// TEST 5: Database Connection
echo '<div class="test-section">';
echo '<h2>5️⃣ Database Connection</h2>';

if (defined('ABSPATH') && file_exists($wp_config)) {
    global $wpdb;
    
    if (isset($wpdb) && $wpdb) {
        echo '<div class="success"><span class="icon">✅</span>Database object available</div>';
        
        // Try a simple query
        try {
            $result = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "options");
            echo '<div class="success"><span class="icon">✅</span>Database connected successfully</div>';
            echo '<div class="info"><strong>Options count:</strong> ' . $result . '</div>';
            echo '<div class="info"><strong>Table prefix:</strong> <code>' . $wpdb->prefix . '</code></div>';
            
            // Check for RTF tables
            $tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}rtf_platform_%'");
            if ($tables) {
                echo '<div class="success"><span class="icon">✅</span><strong>RTF Platform tables found:</strong> ' . count($tables) . ' tables</div>';
            } else {
                echo '<div class="warning"><span class="icon">⚠️</span>No RTF Platform tables found (will be created on first theme activation)</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="error"><span class="icon">❌</span><strong>Database Error:</strong> ' . $e->getMessage() . '</div>';
        }
    } else {
        echo '<div class="error"><span class="icon">❌</span>Database object NOT available</div>';
    }
}

echo '</div>';

// TEST 6: Error Log Check
echo '<div class="test-section">';
echo '<h2>6️⃣ Recent Errors</h2>';

if (defined('ABSPATH')) {
    $debug_log = ABSPATH . 'wp-content/debug.log';
    
    if (file_exists($debug_log)) {
        echo '<div class="info"><span class="icon">📝</span>Debug log found: <code>' . $debug_log . '</code></div>';
        
        // Read last 50 lines
        $lines = file($debug_log);
        $recent_lines = array_slice($lines, -50);
        
        echo '<div class="code-block">';
        echo '<strong>Last 50 lines from debug.log:</strong><br><br>';
        foreach ($recent_lines as $line) {
            echo htmlspecialchars($line) . '<br>';
        }
        echo '</div>';
    } else {
        echo '<div class="info"><span class="icon">ℹ️</span>No debug.log found (enable WP_DEBUG_LOG in wp-config.php)</div>';
    }
}

echo '</div>';

// TEST 7: Recommended Actions
echo '<div class="test-section">';
echo '<h2>7️⃣ Recommended Actions</h2>';

echo '<div class="info">';
echo '<strong>To enable full debugging, add to wp-config.php:</strong>';
echo '<div class="code-block">';
echo "define('WP_DEBUG', true);<br>";
echo "define('WP_DEBUG_LOG', true);<br>";
echo "define('WP_DEBUG_DISPLAY', false);<br>";
echo "@ini_set('display_errors', 0);";
echo '</div>';
echo '</div>';

echo '<div class="warning">';
echo '<strong>If site is completely broken, enable emergency mode:</strong>';
echo '<div class="code-block">';
echo "define('RTF_EMERGENCY_MODE', true);";
echo '</div>';
echo '</div>';

echo '</div>';

?>

        </div>
    </div>
</body>
</html>
<?php
$output = ob_get_clean();
echo $output;
?>
