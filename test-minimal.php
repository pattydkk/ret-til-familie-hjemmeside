<?php
// Load WordPress
$wp_load_path = __DIR__ . '/wp-load.php';
$wp_loaded = false;

if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
    $wp_loaded = defined('ABSPATH');
}
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTF Test - Minimal Version</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        h1 {
            color: #333;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🧪 RTF Minimal Test</h1>
    
    <div class="test-box">
        <h2>Test 1: PHP Version</h2>
        <p>PHP Version: <strong><?php echo PHP_VERSION; ?></strong></p>
        <?php if (version_compare(PHP_VERSION, '7.4.0', '>=')): ?>
            <p class="success">✅ PHP version OK (7.4+)</p>
        <?php else: ?>
            <p class="error">❌ PHP version for gammel (skal være 7.4+)</p>
        <?php endif; ?>
    </div>

    <div class="test-box">
        <h2>Test 2: Session</h2>
        <?php
        if (session_status() === PHP_SESSION_ACTIVE) {
            echo '<p class="success">✅ Session aktiv</p>';
            echo '<p>Session ID: ' . session_id() . '</p>';
        } else {
            echo '<p class="error">❌ Session ikke aktiv</p>';
        }
        ?>
    </div>

    <div class="test-box">
        <h2>Test 3: WordPress Load</h2>
        <?php
        if ($wp_loaded && defined('ABSPATH')) {
            echo '<p class="success">✅ WordPress loaded</p>';
            echo '<p>WordPress Path: ' . ABSPATH . '</p>';
            global $wpdb;
            echo '<p>DB Connected: ' . ($wpdb && $wpdb->dbh ? 'Ja' : 'Nej') . '</p>';
        } else {
            echo '<p class="error">❌ WordPress ikke loaded</p>';
            echo '<p>Denne test kræver at filen ligger i WordPress root folder</p>';
        }
        ?>
    </div>

    <div class="test-box">
        <h2>Test 4: Theme Functions</h2>
        <?php
        if (function_exists('rtf_get_lang')) {
            echo '<p class="success">✅ rtf_get_lang() findes</p>';
            echo '<p>Current language: ' . rtf_get_lang() . '</p>';
        } else {
            echo '<p class="error">❌ rtf_get_lang() findes ikke</p>';
        }
        
        if (function_exists('rtf_is_logged_in')) {
            echo '<p class="success">✅ rtf_is_logged_in() findes</p>';
            echo '<p>Logged in: ' . (rtf_is_logged_in() ? 'Ja' : 'Nej') . '</p>';
        } else {
            echo '<p class="error">❌ rtf_is_logged_in() findes ikke</p>';
        }
        ?>
    </div>

    <div class="test-box">
        <h2>Test 5: Database Tables</h2>
        <?php
        if ($wp_loaded && isset($wpdb)) {
            $tables = [
                'rtf_platform_users',
                'rtf_platform_posts',
                'rtf_platform_messages',
                'rtf_platform_chatrooms',
            ];
            
            foreach ($tables as $table) {
                $full_table = $wpdb->prefix . $table;
                $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'") === $full_table;
                
                if ($exists) {
                    $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table");
                    echo '<p class="success">✅ ' . $table . ' (rows: ' . $count . ')</p>';
                } else {
                    echo '<p class="error">❌ ' . $table . ' findes ikke</p>';
                }
            }
        } else {
            echo '<p class="error">❌ WordPress ikke loaded - kan ikke tjekke database</p>';
        }
        ?>
    </div>

    <div class="test-box">
        <h2>Test 6: PHP Extensions</h2>
        <?php
        $required = ['mysqli', 'mbstring', 'json', 'curl', 'openssl'];
        foreach ($required as $ext) {
            if (extension_loaded($ext)) {
                echo '<p class="success">✅ ' . $ext . '</p>';
            } else {
                echo '<p class="error">❌ ' . $ext . ' ikke loaded</p>';
            }
        }
        ?>
    </div>

    <div class="test-box">
        <h2>Test 7: Memory & Limits</h2>
        <p>Memory Limit: <strong><?php echo ini_get('memory_limit'); ?></strong></p>
        <p>Max Execution Time: <strong><?php echo ini_get('max_execution_time'); ?>s</strong></p>
        <p>Upload Max Size: <strong><?php echo ini_get('upload_max_filesize'); ?></strong></p>
        <p>Current Memory Usage: <strong><?php echo round(memory_get_usage(true) / 1024 / 1024, 2); ?> MB</strong></p>
    </div>

    <div class="test-box">
        <h2>🎯 Næste Trin</h2>
        <?php
        $all_ok = true;
        
        // Check critical issues
        if (version_compare(PHP_VERSION, '7.4.0', '<')) $all_ok = false;
        if (session_status() !== PHP_SESSION_ACTIVE) $all_ok = false;
        if (!$wp_loaded || !defined('ABSPATH')) $all_ok = false;
        if (!function_exists('rtf_get_lang')) $all_ok = false;
        
        if ($all_ok): ?>
            <p class="success">✅ ALT VIRKER LOKALT!</p>
            <p>Du kan nu:</p>
            <ol>
                <li>Omdøb <code>functions-minimal.php</code> til <code>functions.php</code></li>
                <li>Upload til live server</li>
                <li>Test på live site</li>
                <li>Hvis det virker, brug fuld functions.php</li>
            </ol>
        <?php else: ?>
            <p class="error">❌ Der er problemer - fix disse først</p>
            <p>Se røde fejl ovenfor</p>
        <?php endif; ?>
    </div>

</body>
</html>
