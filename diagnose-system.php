<?php
/**
 * KOMPLET SYSTEM DIAGNOSE
 * Dette script tester alle komponenter i registrerings-flowet
 */

// Disable WordPress (hvis loaded)
if (defined('ABSPATH')) {
    // Vi er i WordPress context
    $in_wordpress = true;
} else {
    // Standalone test
    $in_wordpress = false;
    define('ABSPATH', __DIR__ . '/');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>System Diagnose - Ret til Familie</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #0c4a6e; border-bottom: 3px solid #0c4a6e; padding-bottom: 10px; }
        h2 { color: #075985; margin-top: 30px; border-bottom: 2px solid #bae6fd; padding-bottom: 5px; }
        .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #15803d; background: #dcfce7; padding: 10px; border-left: 4px solid #15803d; margin: 10px 0; }
        .error { color: #dc2626; background: #fee2e2; padding: 10px; border-left: 4px solid #dc2626; margin: 10px 0; }
        .warning { color: #d97706; background: #fef3c7; padding: 10px; border-left: 4px solid #d97706; margin: 10px 0; }
        .info { color: #0c4a6e; background: #e0f2fe; padding: 10px; border-left: 4px solid #0c4a6e; margin: 10px 0; }
        code { background: #1e293b; color: #f1f5f9; padding: 2px 6px; border-radius: 3px; font-size: 13px; }
        pre { background: #1e293b; color: #f1f5f9; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .test-item { margin: 15px 0; padding: 10px; border-left: 3px solid #cbd5e1; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #0c4a6e; color: white; font-weight: 600; }
        tr:hover { background: #f8fafc; }
        .status-ok { color: #15803d; font-weight: bold; }
        .status-fail { color: #dc2626; font-weight: bold; }
        .btn { display: inline-block; padding: 10px 20px; background: #0c4a6e; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #075985; }
    </style>
</head>
<body>

<h1>🔍 System Diagnose - Ret til Familie Platform</h1>

<?php

// =============================================================================
// TEST 1: PHP Environment
// =============================================================================
echo '<div class="test-section">';
echo '<h2>1️⃣ PHP Environment</h2>';

$php_ok = version_compare(PHP_VERSION, '7.4', '>=');
if ($php_ok) {
    echo '<div class="success">✅ PHP Version: ' . PHP_VERSION . ' (OK)</div>';
} else {
    echo '<div class="error">❌ PHP Version: ' . PHP_VERSION . ' (Kræver 7.4+)</div>';
}

$extensions = ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo '<div class="success">✅ Extension <code>' . $ext . '</code> loaded</div>';
    } else {
        echo '<div class="error">❌ Extension <code>' . $ext . '</code> MISSING</div>';
    }
}

echo '</div>';

// =============================================================================
// TEST 2: File System
// =============================================================================
echo '<div class="test-section">';
echo '<h2>2️⃣ Kritiske Filer</h2>';

$critical_files = [
    'functions.php' => 'Theme functions',
    'platform-auth.php' => 'Login/Registration page',
    'includes/class-rtf-user-system.php' => 'User system class',
    'stripe-php-13.18.0/init.php' => 'Stripe library',
    'translations.php' => 'Translation system'
];

foreach ($critical_files as $file => $description) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        $size = filesize($full_path);
        echo '<div class="success">✅ <strong>' . $description . '</strong><br>';
        echo '&nbsp;&nbsp;&nbsp;Fil: <code>' . $file . '</code> (' . number_format($size) . ' bytes)</div>';
    } else {
        echo '<div class="error">❌ <strong>' . $description . '</strong><br>';
        echo '&nbsp;&nbsp;&nbsp;Fil: <code>' . $file . '</code> MANGLER!</div>';
    }
}

echo '</div>';

// =============================================================================
// TEST 3: WordPress Functions
// =============================================================================
echo '<div class="test-section">';
echo '<h2>3️⃣ WordPress Integration</h2>';

if ($in_wordpress) {
    echo '<div class="success">✅ WordPress context detected</div>';
    
    $wp_functions = ['get_template_directory', 'home_url', 'wp_redirect', 'wp_verify_nonce', 'current_time'];
    foreach ($wp_functions as $func) {
        if (function_exists($func)) {
            echo '<div class="success">✅ Function <code>' . $func . '()</code> exists</div>';
        } else {
            echo '<div class="error">❌ Function <code>' . $func . '()</code> MISSING</div>';
        }
    }
} else {
    echo '<div class="warning">⚠️ Not in WordPress context - loading standalone</div>';
    
    // Define mock functions for standalone testing
    if (!function_exists('get_template_directory')) {
        function get_template_directory() { return __DIR__; }
    }
    if (!function_exists('home_url')) {
        function home_url($path = '') { return 'http://localhost' . $path; }
    }
}

echo '</div>';

// =============================================================================
// TEST 4: Stripe Configuration
// =============================================================================
echo '<div class="test-section">';
echo '<h2>4️⃣ Stripe Integration</h2>';

// Load Stripe
$stripe_init = __DIR__ . '/stripe-php-13.18.0/init.php';
if (file_exists($stripe_init)) {
    echo '<div class="success">✅ Stripe library found at:<br><code>' . $stripe_init . '</code></div>';
    
    require_once($stripe_init);
    echo '<div class="success">✅ Stripe library loaded successfully</div>';
    
    // Load constants from functions.php
    $functions_file = __DIR__ . '/functions.php';
    if (file_exists($functions_file)) {
        // Parse constants from file without executing whole file
        $content = file_get_contents($functions_file);
        preg_match("/define\('RTF_STRIPE_SECRET_KEY',\s*'([^']+)'\)/", $content, $secret_match);
        preg_match("/define\('RTF_STRIPE_PRICE_ID',\s*'([^']+)'\)/", $content, $price_match);
        
        if (!empty($secret_match[1])) {
            $secret_key = $secret_match[1];
            $secret_preview = substr($secret_key, 0, 15) . '...' . substr($secret_key, -4);
            echo '<div class="info">ℹ️ Secret Key: <code>' . $secret_preview . '</code></div>';
            
            // Test Stripe API
            try {
                \Stripe\Stripe::setApiKey($secret_key);
                echo '<div class="success">✅ Stripe API key set successfully</div>';
                
                // Try to create a test checkout session
                if (!empty($price_match[1])) {
                    $price_id = $price_match[1];
                    echo '<div class="info">ℹ️ Price ID: <code>' . $price_id . '</code></div>';
                    
                    try {
                        $test_session = \Stripe\Checkout\Session::create([
                            'success_url' => 'http://example.com/success',
                            'cancel_url' => 'http://example.com/cancel',
                            'payment_method_types' => ['card'],
                            'mode' => 'subscription',
                            'customer_email' => 'test@example.com',
                            'client_reference_id' => '999999',
                            'line_items' => [[
                                'price' => $price_id,
                                'quantity' => 1
                            ]],
                            'subscription_data' => [
                                'metadata' => [
                                    'user_id' => '999999',
                                    'username' => 'testuser',
                                    'email' => 'test@example.com',
                                    'rtf_platform' => 'true',
                                    'test_mode' => 'true'
                                ]
                            ]
                        ]);
                        
                        echo '<div class="success">✅ <strong>SUCCESS!</strong> Test checkout session created<br>';
                        echo '&nbsp;&nbsp;&nbsp;Session ID: <code>' . $test_session->id . '</code><br>';
                        echo '&nbsp;&nbsp;&nbsp;<a href="' . $test_session->url . '" target="_blank" class="btn">Åbn Test Checkout</a></div>';
                        
                    } catch (\Stripe\Exception\InvalidRequestException $e) {
                        echo '<div class="error">❌ <strong>Stripe Invalid Request:</strong><br>';
                        echo '&nbsp;&nbsp;&nbsp;' . htmlspecialchars($e->getMessage()) . '<br>';
                        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre></div>';
                    } catch (\Exception $e) {
                        echo '<div class="error">❌ <strong>Stripe Error:</strong><br>';
                        echo '&nbsp;&nbsp;&nbsp;Type: ' . get_class($e) . '<br>';
                        echo '&nbsp;&nbsp;&nbsp;Message: ' . htmlspecialchars($e->getMessage()) . '<br>';
                        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre></div>';
                    }
                } else {
                    echo '<div class="error">❌ Price ID not found in functions.php</div>';
                }
                
            } catch (\Exception $e) {
                echo '<div class="error">❌ Failed to set Stripe API key: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        } else {
            echo '<div class="error">❌ Secret Key not found in functions.php</div>';
        }
    }
    
} else {
    echo '<div class="error">❌ Stripe library NOT FOUND at:<br><code>' . $stripe_init . '</code></div>';
}

echo '</div>';

// =============================================================================
// TEST 5: Database Connection
// =============================================================================
echo '<div class="test-section">';
echo '<h2>5️⃣ Database Connection</h2>';

if ($in_wordpress && isset($GLOBALS['wpdb'])) {
    global $wpdb;
    echo '<div class="success">✅ WordPress database object available</div>';
    
    // Test connection
    $result = $wpdb->get_var("SELECT 1");
    if ($result === '1') {
        echo '<div class="success">✅ Database connection working</div>';
        
        // Check for users table
        $table = $wpdb->prefix . 'rtf_platform_users';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
        
        if ($table_exists) {
            $user_count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
            echo '<div class="success">✅ Users table exists: <code>' . $table . '</code><br>';
            echo '&nbsp;&nbsp;&nbsp;Total users: ' . $user_count . '</div>';
            
            // Show recent users
            $recent = $wpdb->get_results("SELECT id, username, email, subscription_status, created_at FROM $table ORDER BY created_at DESC LIMIT 5");
            if ($recent) {
                echo '<table>';
                echo '<tr><th>ID</th><th>Username</th><th>Email</th><th>Subscription</th><th>Created</th></tr>';
                foreach ($recent as $user) {
                    $sub_class = $user->subscription_status === 'active' ? 'status-ok' : 'status-fail';
                    echo '<tr>';
                    echo '<td>' . $user->id . '</td>';
                    echo '<td>' . htmlspecialchars($user->username) . '</td>';
                    echo '<td>' . htmlspecialchars($user->email) . '</td>';
                    echo '<td class="' . $sub_class . '">' . htmlspecialchars($user->subscription_status) . '</td>';
                    echo '<td>' . $user->created_at . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        } else {
            echo '<div class="error">❌ Users table NOT FOUND: <code>' . $table . '</code></div>';
        }
    } else {
        echo '<div class="error">❌ Database connection FAILED</div>';
    }
} else {
    echo '<div class="warning">⚠️ WordPress database not available (standalone mode)</div>';
}

echo '</div>';

// =============================================================================
// TEST 6: Session Management
// =============================================================================
echo '<div class="test-section">';
echo '<h2>6️⃣ Session Management</h2>';

if (session_status() === PHP_SESSION_ACTIVE) {
    echo '<div class="success">✅ PHP session is active</div>';
    echo '<div class="info">Session ID: <code>' . session_id() . '</code></div>';
} else {
    echo '<div class="warning">⚠️ PHP session not started</div>';
    if (@session_start()) {
        echo '<div class="success">✅ Session started successfully</div>';
    } else {
        echo '<div class="error">❌ Failed to start session</div>';
    }
}

echo '</div>';

// =============================================================================
// SUMMARY
// =============================================================================
echo '<div class="test-section">';
echo '<h2>📊 Summary</h2>';

echo '<div class="info">';
echo '<h3>Næste Skridt:</h3>';
echo '<ol>';
echo '<li>Hvis ALLE tests er grønne ✅, så burde systemet virke</li>';
echo '<li>Hvis Stripe testen virker, prøv at registrere en rigtig bruger på <code>/platform-auth/</code></li>';
echo '<li>Tjek fejlloggen efter registrering (hvis WordPress har debug mode)</li>';
echo '<li>Test admin panel user creation på <code>/platform-admin-dashboard/</code></li>';
echo '</ol>';
echo '</div>';

echo '<div class="warning">';
echo '<h3>⚠️ Almindelige Problemer:</h3>';
echo '<ul>';
echo '<li><strong>Stripe Price ID ikke fundet:</strong> Tjek at price_1SFMobL8XSb2lnp6ulwzpiAb findes i din Stripe account</li>';
echo '<li><strong>Database connection failed:</strong> Tjek WordPress database credentials i wp-config.php</li>';
echo '<li><strong>Critical Error på registration:</strong> Tjek WordPress debug.log for præcis fejl</li>';
echo '<li><strong>Admin panel JavaScript virker ikke:</strong> Clear browser cache (Ctrl+Shift+R)</li>';
echo '</ul>';
echo '</div>';

echo '</div>';

?>

<div class="test-section">
    <h2>🔗 Quick Links</h2>
    <a href="/platform-auth/" class="btn">Test Registration</a>
    <a href="/platform-admin-dashboard/" class="btn">Admin Panel</a>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn">Reload Test</a>
</div>

</body>
</html>
