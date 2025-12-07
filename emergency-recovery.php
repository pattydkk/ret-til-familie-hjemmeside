<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RTF Emergency Recovery</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #1e293b; color: #e2e8f0; }
        h1 { color: #ef4444; }
        .box { background: #0f172a; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ef4444; }
        button { padding: 15px 30px; font-size: 16px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; }
        button:hover { background: #dc2626; }
        .success { color: #10b981; font-weight: bold; }
        .info { background: #1e40af; border-left-color: #3b82f6; padding: 15px; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>🚨 RTF Emergency Recovery</h1>
    
    <div class="box">
        <h2>Hvad sker der?</h2>
        <p>WordPress viser en "critical error" besked. Dette script aktiverer <strong>EMERGENCY MODE</strong> som:</p>
        <ul>
            <li>Deaktiverer alle custom RTF features</li>
            <li>Loader kun det minimale nødvendige for at WordPress kan køre</li>
            <li>Tillader dig at logge ind i WordPress admin og undersøge problemet</li>
        </ul>
    </div>

    <div class="info">
        <h3>📋 Instruktioner:</h3>
        <ol>
            <li>Klik på knappen nedenfor for at aktivere Emergency Mode</li>
            <li>Prøv at åbne din WordPress admin (wp-admin)</li>
            <li>Kør debug-wordpress.php for at finde den præcise fejl</li>
            <li>Når fejlen er rettet, deaktiver Emergency Mode igen</li>
        </ol>
    </div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme_dir = __DIR__;
    $wp_config_path = $theme_dir . '/../../../wp-config.php';
    
    if (isset($_POST['enable_emergency'])) {
        // Add emergency mode to wp-config.php
        if (file_exists($wp_config_path)) {
            $config_content = file_get_contents($wp_config_path);
            
            // Check if already defined
            if (strpos($config_content, 'RTF_EMERGENCY_MODE') === false) {
                // Add before "That's all, stop editing"
                $emergency_code = "\n// RTF Emergency Mode - Disable all custom theme features\ndefine('RTF_EMERGENCY_MODE', true);\n\n";
                $config_content = str_replace("/* That's all, stop editing!", $emergency_code . "/* That's all, stop editing!", $config_content);
                
                if (file_put_contents($wp_config_path, $config_content)) {
                    echo '<div class="box" style="border-left-color: #10b981;">';
                    echo '<p class="success">✅ EMERGENCY MODE ACTIVATED!</p>';
                    echo '<p>WordPress skulle nu kunne starte igen. Prøv at åbn din admin panel.</p>';
                    echo '</div>';
                } else {
                    echo '<div class="box"><p style="color: #ef4444;">❌ Could not write to wp-config.php. Please add this manually:</p>';
                    echo '<pre style="background: #0f172a; padding: 10px; border-radius: 4px;">define(\'RTF_EMERGENCY_MODE\', true);</pre></div>';
                }
            } else {
                echo '<div class="box"><p style="color: #f59e0b;">⚠️ Emergency mode is already enabled in wp-config.php</p></div>';
            }
        } else {
            echo '<div class="box"><p style="color: #ef4444;">❌ wp-config.php not found at: ' . htmlspecialchars($wp_config_path) . '</p></div>';
        }
    }
    
    if (isset($_POST['disable_emergency'])) {
        // Remove emergency mode from wp-config.php
        if (file_exists($wp_config_path)) {
            $config_content = file_get_contents($wp_config_path);
            
            if (strpos($config_content, 'RTF_EMERGENCY_MODE') !== false) {
                // Remove the emergency mode line
                $config_content = preg_replace('/\/\/ RTF Emergency Mode.*?\n.*?RTF_EMERGENCY_MODE.*?\n\n/s', '', $config_content);
                
                if (file_put_contents($wp_config_path, $config_content)) {
                    echo '<div class="box" style="border-left-color: #10b981;">';
                    echo '<p class="success">✅ EMERGENCY MODE DEACTIVATED!</p>';
                    echo '<p>Normal theme functionality restored.</p>';
                    echo '</div>';
                } else {
                    echo '<div class="box"><p style="color: #ef4444;">❌ Could not write to wp-config.php. Please remove manually.</p></div>';
                }
            } else {
                echo '<div class="box"><p style="color: #f59e0b;">⚠️ Emergency mode is not currently enabled</p></div>';
            }
        }
    }
}
?>

    <form method="POST" style="margin: 30px 0;">
        <button type="submit" name="enable_emergency">🚨 ENABLE Emergency Mode</button>
    </form>

    <form method="POST" style="margin: 30px 0;">
        <button type="submit" name="disable_emergency" style="background: #059669;">✅ DISABLE Emergency Mode</button>
    </form>

    <div class="box">
        <h3>🔗 Useful Links:</h3>
        <ul>
            <li><a href="debug-wordpress.php" style="color: #3b82f6;">Run Full Debug Report</a></li>
            <li><a href="FINAL-SYSTEM-CHECK.php" style="color: #3b82f6;">Run System Check</a></li>
            <li><a href="../../../wp-admin/" style="color: #3b82f6;">WordPress Admin</a></li>
        </ul>
    </div>

</body>
</html>
