<?php
/**
 * Create Extra Admin Users: Kaya, Nanna, Charlotte
 * PLACE THIS FILE IN YOUR WORDPRESS ROOT (where wp-config.php is)
 * Then run: php create-extra-admins.php
 * Patrick's account (patrickforslev@gmail.com) will NOT be touched
 */

// Try to load WordPress - works from theme OR root
if (file_exists(__DIR__ . '/wp-load.php')) {
    require_once __DIR__ . '/wp-load.php';
} elseif (file_exists(__DIR__ . '/../../../wp-load.php')) {
    require_once __DIR__ . '/../../../wp-load.php';
} elseif (file_exists(__DIR__ . '/../../../../wp-load.php')) {
    require_once __DIR__ . '/../../../../wp-load.php';
} else {
    die("❌ Kan ikke finde wp-load.php\n\nFlyt denne fil til WordPress root directory og kør igen.\n");
}

if (!defined('ABSPATH')) {
    die('WordPress not loaded');
}

global $wpdb;
$table = $wpdb->prefix . 'rtf_platform_users';

// Admin users to create (besides Patrick who already exists)
$new_admins = [
    [
        'username' => 'kaya',
        'email' => 'kaya@rettilfamilie.dk',
        'password' => 'KayaAdmin2024!',
        'full_name' => 'Kaya',
        'phone' => ''
    ],
    [
        'username' => 'nanna',
        'email' => 'nanna@rettilfamilie.dk',
        'password' => 'NannaAdmin2024!',
        'full_name' => 'Nanna',
        'phone' => ''
    ],
    [
        'username' => 'charlotte',
        'email' => 'charlotte@rettilfamilie.dk',
        'password' => 'CharlotteAdmin2024!',
        'full_name' => 'Charlotte',
        'phone' => ''
    ]
];

echo "<h1>🔧 Opret Ekstra Administratorer</h1>";
echo "<p><strong>VIGTIGT:</strong> Patrick's konto (patrickforslev@gmail.com) røres IKKE ved.</p>";
echo "<hr>";

foreach ($new_admins as $admin) {
    echo "<h2>👤 {$admin['full_name']} ({$admin['username']})</h2>";
    
    // Check if user already exists
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE email = %s OR username = %s",
        $admin['email'],
        $admin['username']
    ));
    
    if ($existing) {
        echo "<p style='color: orange;'>⚠️ Bruger findes allerede med ID: {$existing->id}</p>";
        
        // Check if they're already admin
        if ($existing->is_admin == 1) {
            echo "<p style='color: green;'>✅ Er allerede administrator</p>";
        } else {
            // Upgrade to admin
            $wpdb->update(
                $table,
                [
                    'is_admin' => 1,
                    'subscription_status' => 'active',
                    'subscription_end_date' => date('Y-m-d H:i:s', strtotime('+10 years')),
                    'is_active' => 1
                ],
                ['id' => $existing->id],
                ['%d', '%s', '%s', '%d'],
                ['%d']
            );
            echo "<p style='color: green;'>✅ Opgraderet til administrator</p>";
        }
    } else {
        // Create new admin user
        $password_hash = password_hash($admin['password'], PASSWORD_DEFAULT);
        
        $insert = $wpdb->insert(
            $table,
            [
                'username' => $admin['username'],
                'email' => $admin['email'],
                'password' => $password_hash,
                'full_name' => $admin['full_name'],
                'phone' => $admin['phone'],
                'is_admin' => 1,
                'is_active' => 1,
                'subscription_status' => 'active',
                'subscription_start_date' => date('Y-m-d H:i:s'),
                'subscription_end_date' => date('Y-m-d H:i:s', strtotime('+10 years')),
                'email_verified' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s'
            ]
        );
        
        if ($insert) {
            $new_id = $wpdb->insert_id;
            echo "<p style='color: green;'>✅ Oprettet med ID: {$new_id}</p>";
            echo "<p><strong>Login:</strong> {$admin['email']}</p>";
            echo "<p><strong>Password:</strong> {$admin['password']}</p>";
        } else {
            echo "<p style='color: red;'>❌ Fejl ved oprettelse: " . $wpdb->last_error . "</p>";
        }
    }
    
    echo "<hr>";
}

echo "<h2>📋 Alle Administratorer</h2>";
$admins = $wpdb->get_results("SELECT id, username, email, full_name, is_admin, is_active FROM $table WHERE is_admin = 1 ORDER BY id ASC");

if ($admins) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #eef2ff;'>";
    echo "<th>ID</th><th>Brugernavn</th><th>Email</th><th>Fulde Navn</th><th>Admin</th><th>Aktiv</th>";
    echo "</tr>";
    
    foreach ($admins as $admin) {
        $highlight = ($admin->email === 'patrickforslev@gmail.com') ? "style='background: #fef3c7;'" : "";
        echo "<tr $highlight>";
        echo "<td>{$admin->id}</td>";
        echo "<td>{$admin->username}</td>";
        echo "<td>{$admin->email}</td>";
        echo "<td>{$admin->full_name}</td>";
        echo "<td>" . ($admin->is_admin ? '👑 JA' : 'Nej') . "</td>";
        echo "<td>" . ($admin->is_active ? '✅ JA' : '❌ NEJ') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Ingen administratorer fundet!</p>";
}

echo "<hr>";
echo "<h2>✅ Færdig!</h2>";
echo "<p>Du kan nu logge ind som Kaya, Nanna eller Charlotte med deres respektive passwords.</p>";
echo "<p><a href='" . home_url('/platform-auth') . "' style='background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block;'>Gå til Login</a></p>";
