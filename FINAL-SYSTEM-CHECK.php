<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RTF - Final System Check</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
        .header { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white; padding: 40px; text-align: center; }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .content { padding: 40px; }
        .test-section { background: #f8fafc; border-radius: 12px; padding: 30px; margin-bottom: 30px; border-left: 5px solid #2563eb; }
        .test-section h2 { color: #1e293b; margin-bottom: 20px; font-size: 1.8em; }
        .test-item { background: white; border-radius: 8px; padding: 20px; margin-bottom: 15px; border: 2px solid #e2e8f0; }
        .test-item.pass { border-color: #10b981; background: #ecfdf5; }
        .test-item.fail { border-color: #ef4444; background: #fef2f2; }
        .test-item.warn { border-color: #f59e0b; background: #fffbeb; }
        .icon { font-size: 1.5em; margin-right: 10px; }
        .pass .icon { color: #10b981; }
        .fail .icon { color: #ef4444; }
        .warn .icon { color: #f59e0b; }
        .details { margin-top: 10px; padding: 15px; background: rgba(0,0,0,0.03); border-radius: 6px; font-family: 'Courier New', monospace; font-size: 0.9em; }
        .summary { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; text-align: center; border-radius: 12px; margin-top: 40px; }
        .summary.warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .summary.error { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .stat { display: inline-block; margin: 0 20px; font-size: 1.5em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 RTF Platform - Final System Check</h1>
            <p style="font-size: 1.2em; opacity: 0.9;">Comprehensive Pre-Launch Verification</p>
        </div>
        
        <div class="content">
<?php
// Find WordPress
$wp_load_paths = [
    __DIR__ . '/../../../wp-load.php',
    __DIR__ . '/../../wp-load.php',
    __DIR__ . '/../wp-load.php',
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    echo '<div class="test-section"><div class="test-item fail"><span class="icon">❌</span><strong>CRITICAL:</strong> WordPress not found!</div></div>';
    exit;
}

global $wpdb;
$pass_count = 0;
$fail_count = 0;
$warn_count = 0;

// TEST 1: PHP Syntax
echo '<div class="test-section">';
echo '<h2>1️⃣ PHP Syntax & Core Files</h2>';

$php_check = shell_exec('php -l ' . get_template_directory() . '/functions.php 2>&1');
if (strpos($php_check, 'No syntax errors') !== false) {
    echo '<div class="test-item pass"><span class="icon">✅</span><strong>functions.php:</strong> No syntax errors detected</div>';
    $pass_count++;
} else {
    echo '<div class="test-item fail"><span class="icon">❌</span><strong>functions.php:</strong> SYNTAX ERROR!';
    echo '<div class="details">' . htmlspecialchars($php_check) . '</div></div>';
    $fail_count++;
}

// Check critical files exist
$critical_files = [
    'platform-auth.php',
    'platform-profil.php',
    'includes/class-rtf-user-system.php',
    'header.php',
    'footer.php'
];

foreach ($critical_files as $file) {
    $path = get_template_directory() . '/' . $file;
    if (file_exists($path)) {
        echo '<div class="test-item pass"><span class="icon">✅</span><strong>' . esc_html($file) . ':</strong> EXISTS</div>';
        $pass_count++;
    } else {
        echo '<div class="test-item fail"><span class="icon">❌</span><strong>' . esc_html($file) . ':</strong> MISSING!</div>';
        $fail_count++;
    }
}

echo '</div>';

// TEST 2: Database Structure
echo '<div class="test-section">';
echo '<h2>2️⃣ Database Tables</h2>';

$required_tables = [
    'rtf_platform_users',
    'rtf_platform_privacy',
    'rtf_platform_posts',
    'rtf_platform_comments',
    'rtf_platform_likes',
    'rtf_platform_messages',
    'rtf_platform_friends',
    'rtf_platform_forum_posts',
    'rtf_platform_kate_chat'
];

foreach ($required_tables as $table) {
    $full_table = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'");
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table");
        echo '<div class="test-item pass"><span class="icon">✅</span><strong>' . esc_html($table) . ':</strong> EXISTS (' . $count . ' records)</div>';
        $pass_count++;
    } else {
        echo '<div class="test-item fail"><span class="icon">❌</span><strong>' . esc_html($table) . ':</strong> MISSING!</div>';
        $fail_count++;
    }
}

echo '</div>';

// TEST 3: User System
echo '<div class="test-section">';
echo '<h2>3️⃣ User Authentication System</h2>';

// Check if RTF_User_System class is loaded
if (class_exists('RTF_User_System')) {
    echo '<div class="test-item pass"><span class="icon">✅</span><strong>RTF_User_System:</strong> Class loaded</div>';
    $pass_count++;
    
    // Check if global variable exists
    global $rtf_user_system;
    if (isset($rtf_user_system) && $rtf_user_system instanceof RTF_User_System) {
        echo '<div class="test-item pass"><span class="icon">✅</span><strong>$rtf_user_system:</strong> Global variable initialized</div>';
        $pass_count++;
    } else {
        echo '<div class="test-item fail"><span class="icon">❌</span><strong>$rtf_user_system:</strong> NOT initialized!</div>';
        $fail_count++;
    }
} else {
    echo '<div class="test-item fail"><span class="icon">❌</span><strong>RTF_User_System:</strong> Class NOT loaded!</div>';
    $fail_count++;
}

// Check helper functions
$helper_functions = ['rtf_is_logged_in', 'rtf_get_current_user', 'rtf_is_admin_user', 'rtf_require_login'];
foreach ($helper_functions as $func) {
    if (function_exists($func)) {
        echo '<div class="test-item pass"><span class="icon">✅</span><strong>' . esc_html($func) . '():</strong> Function exists</div>';
        $pass_count++;
    } else {
        echo '<div class="test-item fail"><span class="icon">❌</span><strong>' . esc_html($func) . '():</strong> Function NOT found!</div>';
        $fail_count++;
    }
}

echo '</div>';

// TEST 4: Admin Users
echo '<div class="test-section">';
echo '<h2>4️⃣ Admin Users</h2>';

$admins = $wpdb->get_results("SELECT id, username, email, is_admin, subscription_status FROM {$wpdb->prefix}rtf_platform_users WHERE is_admin = 1");
if ($admins) {
    echo '<div class="test-item pass"><span class="icon">✅</span><strong>Admin Users Found:</strong> ' . count($admins) . ' admin(s)</div>';
    foreach ($admins as $admin) {
        $status_icon = ($admin->subscription_status === 'active') ? '🟢' : '🔴';
        echo '<div class="test-item pass">';
        echo '<span class="icon">' . $status_icon . '</span>';
        echo '<strong>' . esc_html($admin->username) . '</strong> (' . esc_html($admin->email) . ')';
        echo '<div class="details">ID: ' . $admin->id . ' | Status: ' . esc_html($admin->subscription_status) . '</div>';
        echo '</div>';
    }
    $pass_count++;
} else {
    echo '<div class="test-item warn"><span class="icon">⚠️</span><strong>No Admin Users:</strong> You should create at least one admin</div>';
    $warn_count++;
}

echo '</div>';

// TEST 5: Stripe Configuration
echo '<div class="test-section">';
echo '<h2>5️⃣ Stripe Payment System</h2>';

if (defined('RTF_STRIPE_SECRET_KEY') && RTF_STRIPE_SECRET_KEY !== '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') {
    echo '<div class="test-item fail"><span class="icon">❌</span><strong>Stripe Secret Key:</strong> Using DEFAULT key - CHANGE THIS!</div>';
    $fail_count++;
} else if (defined('RTF_STRIPE_SECRET_KEY') && strpos(RTF_STRIPE_SECRET_KEY, 'sk_live_') === 0) {
    echo '<div class="test-item pass"><span class="icon">✅</span><strong>Stripe Secret Key:</strong> Configured (live mode)</div>';
    $pass_count++;
} else {
    echo '<div class="test-item warn"><span class="icon">⚠️</span><strong>Stripe Secret Key:</strong> Not configured or using test mode</div>';
    $warn_count++;
}

$vendor_autoload = get_template_directory() . '/vendor/autoload.php';
if (file_exists($vendor_autoload)) {
    echo '<div class="test-item pass"><span class="icon">✅</span><strong>Composer Vendor:</strong> vendor/autoload.php EXISTS</div>';
    $pass_count++;
    
    if (class_exists('\Stripe\Stripe')) {
        echo '<div class="test-item pass"><span class="icon">✅</span><strong>Stripe Library:</strong> Loaded and available</div>';
        $pass_count++;
    } else {
        echo '<div class="test-item fail"><span class="icon">❌</span><strong>Stripe Library:</strong> NOT loaded (run composer install)</div>';
        $fail_count++;
    }
} else {
    echo '<div class="test-item fail"><span class="icon">❌</span><strong>Composer Vendor:</strong> MISSING! Run "composer install" in theme directory</div>';
    $fail_count++;
}

echo '</div>';

// TEST 6: Session Management
echo '<div class="test-section">';
echo '<h2>6️⃣ Session Management</h2>';

if (session_status() === PHP_SESSION_ACTIVE) {
    echo '<div class="test-item pass"><span class="icon">✅</span><strong>PHP Session:</strong> Active</div>';
    $pass_count++;
} else {
    echo '<div class="test-item fail"><span class="icon">❌</span><strong>PHP Session:</strong> NOT active!</div>';
    $fail_count++;
}

if (isset($_SESSION)) {
    echo '<div class="test-item pass"><span class="icon">✅</span><strong>$_SESSION:</strong> Available</div>';
    $pass_count++;
} else {
    echo '<div class="test-item fail"><span class="icon">❌</span><strong>$_SESSION:</strong> NOT available!</div>';
    $fail_count++;
}

echo '</div>';

// TEST 7: WordPress Integration
echo '<div class="test-section">';
echo '<h2>7️⃣ WordPress Integration</h2>';

$wp_functions = ['get_header', 'get_footer', 'wp_redirect', 'sanitize_text_field', 'esc_html', 'esc_url'];
foreach ($wp_functions as $func) {
    if (function_exists($func)) {
        echo '<div class="test-item pass"><span class="icon">✅</span><strong>' . esc_html($func) . '():</strong> Available</div>';
        $pass_count++;
    } else {
        echo '<div class="test-item fail"><span class="icon">❌</span><strong>' . esc_html($func) . '():</strong> NOT available!</div>';
        $fail_count++;
    }
}

echo '</div>';

// SUMMARY
$total = $pass_count + $fail_count + $warn_count;
$status_class = ($fail_count > 0) ? 'error' : (($warn_count > 0) ? 'warning' : '');

echo '<div class="summary ' . $status_class . '">';
echo '<h2>📊 Test Summary</h2>';
echo '<div style="margin: 20px 0;">';
echo '<span class="stat">✅ ' . $pass_count . ' Passed</span>';
echo '<span class="stat">⚠️ ' . $warn_count . ' Warnings</span>';
echo '<span class="stat">❌ ' . $fail_count . ' Failed</span>';
echo '</div>';

if ($fail_count === 0 && $warn_count === 0) {
    echo '<p style="font-size: 1.5em; margin-top: 20px;">🎉 ALL TESTS PASSED! System is ready for production!</p>';
} elseif ($fail_count === 0) {
    echo '<p style="font-size: 1.3em; margin-top: 20px;">⚠️ System is functional but has warnings. Review them before going live.</p>';
} else {
    echo '<p style="font-size: 1.3em; margin-top: 20px;">❌ CRITICAL ISSUES FOUND! Fix failed tests before going live.</p>';
}

echo '</div>';
?>
        </div>
    </div>
</body>
</html>
