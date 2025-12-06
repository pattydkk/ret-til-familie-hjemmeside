<?php
/**
 * EMERGENCY FIX - Giv patrickfoersle@gmail.com fuld admin adgang
 */

// Load WordPress
$wp_load_paths = [
    __DIR__ . '/../../../wp-load.php',
    __DIR__ . '/../../../../wp-load.php',
    __DIR__ . '/../../../../../wp-load.php'
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('ERROR: Could not find wp-load.php');
}

global $wpdb;
$table_users = $wpdb->prefix . 'rtf_platform_users';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Emergency Admin Fix</title>";
echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;background:#0f172a;color:#e2e8f0}";
echo ".box{background:#1e293b;padding:20px;border-radius:8px;margin:20px 0;border:1px solid #334155}";
echo ".success{color:#10b981;font-weight:bold}.error{color:#ef4444;font-weight:bold}";
echo "h1{color:#3b82f6}h2{color:#60a5fa;border-bottom:2px solid #334155;padding-bottom:10px}</style></head><body>";

echo "<h1>🚨 EMERGENCY ADMIN FIX</h1>";

// Find user
$user = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$table_users} WHERE email = %s",
    'patrickfoersle@gmail.com'
));

if (!$user) {
    echo "<div class='box'>";
    echo "<p class='error'>❌ FEJL: Bruger med email 'patrickfoersle@gmail.com' blev IKKE fundet i databasen!</p>";
    echo "<p>Brugeren eksisterer ikke. Opret først brugeren via registrering.</p>";
    echo "</div>";
} else {
    echo "<div class='box'>";
    echo "<h2>✅ Bruger fundet</h2>";
    echo "<table style='width:100%;border-collapse:collapse'>";
    echo "<tr style='border-bottom:1px solid #334155'><td style='padding:8px'><strong>ID:</strong></td><td>" . $user->id . "</td></tr>";
    echo "<tr style='border-bottom:1px solid #334155'><td style='padding:8px'><strong>Username:</strong></td><td>" . $user->username . "</td></tr>";
    echo "<tr style='border-bottom:1px solid #334155'><td style='padding:8px'><strong>Email:</strong></td><td>" . $user->email . "</td></tr>";
    echo "<tr style='border-bottom:1px solid #334155'><td style='padding:8px'><strong>Full Name:</strong></td><td>" . $user->full_name . "</td></tr>";
    echo "<tr style='border-bottom:1px solid #334155'><td style='padding:8px'><strong>Is Admin:</strong></td><td>" . ($user->is_admin == 1 ? '<span class="success">✓ JA (Admin)</span>' : '<span class="error">✗ NEJ (Almindelig bruger)</span>') . "</td></tr>";
    echo "<tr style='border-bottom:1px solid #334155'><td style='padding:8px'><strong>Subscription:</strong></td><td>" . $user->subscription_status . "</td></tr>";
    echo "<tr style='border-bottom:1px solid #334155'><td style='padding:8px'><strong>Created:</strong></td><td>" . $user->created_at . "</td></tr>";
    echo "</table>";
    echo "</div>";
    
    if ($user->is_admin != 1) {
        echo "<div class='box'>";
        echo "<h2>⚠️ FIKSER ADMIN ADGANG</h2>";
        
        $result = $wpdb->update(
            $table_users,
            ['is_admin' => 1],
            ['email' => 'patrickfoersle@gmail.com'],
            ['%d'],
            ['%s']
        );
        
        if ($result !== false) {
            echo "<p class='success'>✅ SUCCESS! Brugeren har nu FULD ADMIN ADGANG</p>";
            echo "<p>Opdaterede is_admin = 1 for bruger ID " . $user->id . "</p>";
        } else {
            echo "<p class='error'>❌ FEJL ved opdatering: " . $wpdb->last_error . "</p>";
        }
        echo "</div>";
    } else {
        echo "<div class='box'>";
        echo "<p class='success'>✅ Brugeren har allerede admin adgang!</p>";
        echo "</div>";
    }
    
    // Also activate subscription
    echo "<div class='box'>";
    echo "<h2>💎 Aktiverer Abonnement</h2>";
    
    $expire_date = date('Y-m-d H:i:s', strtotime('+365 days'));
    
    $sub_result = $wpdb->update(
        $table_users,
        [
            'subscription_status' => 'active',
            'subscription_start' => current_time('mysql'),
            'subscription_expire' => $expire_date
        ],
        ['email' => 'patrickfoersle@gmail.com'],
        ['%s', '%s', '%s'],
        ['%s']
    );
    
    if ($sub_result !== false) {
        echo "<p class='success'>✅ Abonnement aktiveret til " . $expire_date . "</p>";
    } else {
        echo "<p class='error'>❌ Fejl: " . $wpdb->last_error . "</p>";
    }
    echo "</div>";
}

// Show all admin users
echo "<div class='box'>";
echo "<h2>👑 Alle Admin Brugere i Systemet</h2>";

$admins = $wpdb->get_results("SELECT id, username, email, full_name, is_admin, subscription_status FROM {$table_users} WHERE is_admin = 1");

if ($admins) {
    echo "<table style='width:100%;border-collapse:collapse'>";
    echo "<tr style='background:#334155'><th style='padding:10px;text-align:left'>ID</th><th style='padding:10px;text-align:left'>Username</th><th style='padding:10px;text-align:left'>Email</th><th style='padding:10px;text-align:left'>Navn</th><th style='padding:10px;text-align:left'>Subscription</th></tr>";
    foreach ($admins as $admin) {
        echo "<tr style='border-bottom:1px solid #334155'>";
        echo "<td style='padding:8px'>" . $admin->id . "</td>";
        echo "<td style='padding:8px'>" . $admin->username . "</td>";
        echo "<td style='padding:8px'>" . $admin->email . "</td>";
        echo "<td style='padding:8px'>" . $admin->full_name . "</td>";
        echo "<td style='padding:8px'>" . $admin->subscription_status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>Ingen admin brugere fundet!</p>";
}
echo "</div>";

echo "<div class='box'>";
echo "<h2>✅ FÆRDIG</h2>";
echo "<p>patrickfoersle@gmail.com har nu:</p>";
echo "<ul>";
echo "<li>✅ Fuld admin adgang (is_admin = 1)</li>";
echo "<li>✅ Aktivt abonnement i 365 dage</li>";
echo "<li>✅ Kan oprette, redigere og slette brugere</li>";
echo "<li>✅ Kan se alt indhold i admin panelet</li>";
echo "</ul>";
echo "<p><strong>Log ud og ind igen for at aktivere ændringerne.</strong></p>";
echo "</div>";

echo "</body></html>";
