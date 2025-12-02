<?php
/**
 * Template Name: Test Database
 */

get_header();

global $wpdb;

echo '<div style="max-width: 1200px; margin: 50px auto; padding: 20px; font-family: Arial;">';
echo '<h1 style="color: #2563eb;">🔍 Database Test</h1>';

// Test 1: Check if tables exist
echo '<h2>📊 Database Tabeller</h2>';
$tables = [
    'rtf_platform_users',
    'rtf_platform_privacy',
    'rtf_platform_posts',
    'rtf_platform_messages',
    'rtf_kate_chat'
];

foreach ($tables as $table_name) {
    $table = $wpdb->prefix . $table_name;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        echo '<p style="color: green;">✅ ' . $table . ' - Eksisterer (Rækker: ' . $count . ')</p>';
    } else {
        echo '<p style="color: red;">❌ ' . $table . ' - Eksisterer IKKE</p>';
    }
}

// Test 2: Check session
echo '<h2>🔐 Session Test</h2>';
if (session_id()) {
    echo '<p style="color: green;">✅ Session er aktiv (ID: ' . session_id() . ')</p>';
    if (isset($_SESSION['rtf_user_id'])) {
        echo '<p style="color: green;">✅ Bruger er logget ind (User ID: ' . $_SESSION['rtf_user_id'] . ')</p>';
    } else {
        echo '<p style="color: orange;">⚠️ Ingen bruger logget ind</p>';
    }
} else {
    echo '<p style="color: red;">❌ Session ikke aktiv</p>';
}

// Test 3: List all users
echo '<h2>👥 Brugere i Database</h2>';
$table_users = $wpdb->prefix . 'rtf_platform_users';
if ($wpdb->get_var("SHOW TABLES LIKE '$table_users'")) {
    $users = $wpdb->get_results("SELECT id, username, email, full_name, subscription_status, created_at FROM $table_users ORDER BY id DESC LIMIT 10");
    if ($users) {
        echo '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
        echo '<tr style="background: #e0f2fe;">';
        echo '<th style="padding: 10px; border: 1px solid #ccc;">ID</th>';
        echo '<th style="padding: 10px; border: 1px solid #ccc;">Username</th>';
        echo '<th style="padding: 10px; border: 1px solid #ccc;">Email</th>';
        echo '<th style="padding: 10px; border: 1px solid #ccc;">Fulde Navn</th>';
        echo '<th style="padding: 10px; border: 1px solid #ccc;">Status</th>';
        echo '<th style="padding: 10px; border: 1px solid #ccc;">Oprettet</th>';
        echo '</tr>';
        foreach ($users as $user) {
            echo '<tr>';
            echo '<td style="padding: 10px; border: 1px solid #ccc;">' . $user->id . '</td>';
            echo '<td style="padding: 10px; border: 1px solid #ccc;">' . $user->username . '</td>';
            echo '<td style="padding: 10px; border: 1px solid #ccc;">' . $user->email . '</td>';
            echo '<td style="padding: 10px; border: 1px solid #ccc;">' . $user->full_name . '</td>';
            echo '<td style="padding: 10px; border: 1px solid #ccc;">' . $user->subscription_status . '</td>';
            echo '<td style="padding: 10px; border: 1px solid #ccc;">' . $user->created_at . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p style="color: orange;">⚠️ Ingen brugere i databasen endnu</p>';
    }
} else {
    echo '<p style="color: red;">❌ Brugertabel eksisterer ikke</p>';
}

// Test 4: Check WordPress config
echo '<h2>⚙️ WordPress Konfiguration</h2>';
echo '<p>Database Name: ' . DB_NAME . '</p>';
echo '<p>Table Prefix: ' . $wpdb->prefix . '</p>';
echo '<p>WordPress Version: ' . get_bloginfo('version') . '</p>';
echo '<p>PHP Version: ' . PHP_VERSION . '</p>';

// Test 5: Quick Actions
echo '<h2>⚡ Hurtig Handlinger</h2>';
echo '<p><a href="' . admin_url('admin-ajax.php?action=rtf_force_create_pages') . '" style="display: inline-block; padding: 15px 30px; background: #2563eb; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">🔄 Kør Setup Igen</a></p>';
echo '<p><a href="' . home_url('/platform-auth/') . '" style="display: inline-block; padding: 15px 30px; background: #38bdf8; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">🔐 Gå til Login</a></p>';

echo '</div>';

get_footer();
?>
