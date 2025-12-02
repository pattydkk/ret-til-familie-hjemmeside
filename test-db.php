<?php
/**
 * Template Name: Test Database
 */

get_header();

global $wpdb;
$table = $wpdb->prefix . 'rtf_platform_users';

echo '<div style="max-width: 1200px; margin: 40px auto; padding: 20px; background: white; border-radius: 16px;">';
echo '<h1>Database Test</h1>';

// Check if table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
echo '<p><strong>Table exists:</strong> ' . ($table_exists ? 'YES ✅' : 'NO ❌') . '</p>';

if ($table_exists) {
    // Count users
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    echo '<p><strong>Total users:</strong> ' . $count . '</p>';
    
    // Show all users
    $users = $wpdb->get_results("SELECT id, username, email, is_active, created_at FROM $table");
    if ($users) {
        echo '<h2>Users:</h2>';
        echo '<table border="1" cellpadding="10" style="width: 100%; border-collapse: collapse;">';
        echo '<tr><th>ID</th><th>Username</th><th>Email</th><th>Active</th><th>Created</th></tr>';
        foreach ($users as $user) {
            echo '<tr>';
            echo '<td>' . $user->id . '</td>';
            echo '<td>' . $user->username . '</td>';
            echo '<td>' . $user->email . '</td>';
            echo '<td>' . ($user->is_active ? 'Yes' : 'No') . '</td>';
            echo '<td>' . $user->created_at . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}

// Test session
echo '<h2>Session Test</h2>';
echo '<p><strong>Session started:</strong> ' . (session_status() === PHP_SESSION_ACTIVE ? 'YES ✅' : 'NO ❌') . '</p>';
if (isset($_SESSION['rtf_user_id'])) {
    echo '<p><strong>Logged in user ID:</strong> ' . $_SESSION['rtf_user_id'] . '</p>';
} else {
    echo '<p><strong>Status:</strong> Not logged in</p>';
}

// Show all tables
echo '<h2>All RTF Tables:</h2>';
$tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}rtf_%'");
echo '<ul>';
foreach ($tables as $table_obj) {
    $table_name = current((array)$table_obj);
    echo '<li>' . $table_name . '</li>';
}
echo '</ul>';

echo '</div>';

get_footer();
?>
