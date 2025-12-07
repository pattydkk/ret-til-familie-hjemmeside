<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTF Standalone Test</title>
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
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        h1 {
            color: #333;
        }
    </style>
</head>
<body>
    <h1>🧪 RTF PHP Environment Test</h1>
    
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
        <h2>Test 2: Required PHP Extensions</h2>
        <?php
        $required = ['mbstring', 'json', 'curl', 'openssl'];
        $all_loaded = true;
        foreach ($required as $ext) {
            if (extension_loaded($ext)) {
                echo '<p class="success">✅ ' . $ext . '</p>';
            } else {
                echo '<p class="error">❌ ' . $ext . ' ikke loaded</p>';
                $all_loaded = false;
            }
        }
        ?>
    </div>

    <div class="test-box">
        <h2>Test 3: MySQL/MySQLi Extension</h2>
        <?php
        $mysql_ok = false;
        if (extension_loaded('mysqli')) {
            echo '<p class="success">✅ mysqli extension loaded</p>';
            $mysql_ok = true;
        } else {
            echo '<p class="error">❌ mysqli extension IKKE loaded</p>';
            echo '<p>Fix: Åbn php.ini og fjern ; foran extension=mysqli</p>';
        }
        
        if (extension_loaded('mysql')) {
            echo '<p class="success">✅ mysql extension loaded (legacy)</p>';
        }
        ?>
    </div>

    <div class="test-box">
        <h2>Test 4: functions-minimal.php Syntax</h2>
        <?php
        $functions_file = __DIR__ . '/functions-minimal.php';
        if (file_exists($functions_file)) {
            $output = [];
            $return_var = 0;
            exec('php -l ' . escapeshellarg($functions_file) . ' 2>&1', $output, $return_var);
            
            if ($return_var === 0 && strpos(implode('', $output), 'No syntax errors') !== false) {
                echo '<p class="success">✅ functions-minimal.php syntax OK</p>';
            } else {
                echo '<p class="error">❌ functions-minimal.php har syntax fejl</p>';
                echo '<pre>' . htmlspecialchars(implode("\n", $output)) . '</pre>';
            }
        } else {
            echo '<p class="error">❌ functions-minimal.php ikke fundet</p>';
        }
        ?>
    </div>

    <div class="test-box">
        <h2>Test 5: functions.php Syntax</h2>
        <?php
        $functions_file = __DIR__ . '/functions.php';
        if (file_exists($functions_file)) {
            $output = [];
            $return_var = 0;
            exec('php -l ' . escapeshellarg($functions_file) . ' 2>&1', $output, $return_var);
            
            if ($return_var === 0 && strpos(implode('', $output), 'No syntax errors') !== false) {
                echo '<p class="success">✅ functions.php syntax OK</p>';
            } else {
                echo '<p class="error">❌ functions.php har syntax fejl</p>';
                echo '<pre>' . htmlspecialchars(implode("\n", $output)) . '</pre>';
            }
        } else {
            echo '<p class="error">❌ functions.php ikke fundet</p>';
        }
        ?>
    </div>

    <div class="test-box">
        <h2>Test 6: File Permissions</h2>
        <?php
        $files_to_check = ['functions.php', 'functions-minimal.php', 'index.php', 'style.css'];
        foreach ($files_to_check as $file) {
            if (file_exists($file)) {
                if (is_readable($file)) {
                    echo '<p class="success">✅ ' . $file . ' readable</p>';
                } else {
                    echo '<p class="error">❌ ' . $file . ' IKKE readable</p>';
                }
            } else {
                echo '<p class="warning">⚠️ ' . $file . ' ikke fundet</p>';
            }
        }
        ?>
    </div>

    <div class="test-box">
        <h2>Test 7: Memory & Limits</h2>
        <p>Memory Limit: <strong><?php echo ini_get('memory_limit'); ?></strong></p>
        <p>Max Execution Time: <strong><?php echo ini_get('max_execution_time'); ?>s</strong></p>
        <p>Upload Max Size: <strong><?php echo ini_get('upload_max_filesize'); ?></strong></p>
        <p>Post Max Size: <strong><?php echo ini_get('post_max_size'); ?></strong></p>
        <p>Current Memory Usage: <strong><?php echo round(memory_get_usage(true) / 1024 / 1024, 2); ?> MB</strong></p>
        <?php
        $mem_limit = ini_get('memory_limit');
        if (preg_match('/(\d+)M/', $mem_limit, $matches)) {
            $mem_mb = intval($matches[1]);
            if ($mem_mb >= 128) {
                echo '<p class="success">✅ Memory limit OK (128M+)</p>';
            } else {
                echo '<p class="warning">⚠️ Memory limit lav (anbefalet: 256M)</p>';
            }
        }
        ?>
    </div>

    <div class="test-box">
        <h2>Test 8: Error Display Settings</h2>
        <p>display_errors: <strong><?php echo ini_get('display_errors') ? 'On' : 'Off'; ?></strong></p>
        <p>error_reporting: <strong><?php echo error_reporting(); ?></strong></p>
        <p>log_errors: <strong><?php echo ini_get('log_errors') ? 'On' : 'Off'; ?></strong></p>
        <?php if (ini_get('error_log')): ?>
            <p>error_log: <strong><?php echo ini_get('error_log'); ?></strong></p>
        <?php endif; ?>
    </div>

    <div class="test-box">
        <h2>🎯 Konklusion</h2>
        <?php
        $critical_ok = true;
        
        // Check critical issues
        if (version_compare(PHP_VERSION, '7.4.0', '<')) $critical_ok = false;
        if (!extension_loaded('mysqli')) $critical_ok = false;
        if (!extension_loaded('mbstring')) $critical_ok = false;
        if (!extension_loaded('json')) $critical_ok = false;
        
        if ($critical_ok): ?>
            <p class="success">✅ PHP ENVIRONMENT OK FOR WORDPRESS!</p>
            <p><strong>Næste trin:</strong></p>
            <ol>
                <li>Upload filer til live server</li>
                <li>Kør denne test på live server: test-standalone.php</li>
                <li>Hvis live test også er grøn, brug functions-minimal.php</li>
            </ol>
        <?php else: ?>
            <p class="error">❌ KRITISKE PROBLEMER FUNDET</p>
            <p><strong>Fix følgende før upload:</strong></p>
            <ul>
                <?php if (version_compare(PHP_VERSION, '7.4.0', '<')): ?>
                    <li>Opgrader PHP til 7.4+</li>
                <?php endif; ?>
                <?php if (!extension_loaded('mysqli')): ?>
                    <li>Aktivér mysqli extension i php.ini</li>
                <?php endif; ?>
                <?php if (!extension_loaded('mbstring')): ?>
                    <li>Aktivér mbstring extension i php.ini</li>
                <?php endif; ?>
                <?php if (!extension_loaded('json')): ?>
                    <li>Aktivér json extension i php.ini</li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="test-box">
        <h2>📝 PHP Info</h2>
        <p>PHP SAPI: <strong><?php echo php_sapi_name(); ?></strong></p>
        <p>OS: <strong><?php echo PHP_OS; ?></strong></p>
        <p>Architecture: <strong><?php echo PHP_INT_SIZE * 8; ?>-bit</p>
        <p>php.ini: <strong><?php echo php_ini_loaded_file(); ?></strong></p>
    </div>

</body>
</html>
