<?php
/**
 * Test User Deletion
 * Verify that delete actually removes user from database
 */

require_once __DIR__ . '/wp-load.php';
require_once get_template_directory() . '/includes/class-rtf-user-system.php';

global $wpdb, $rtf_user_system;

if (!$rtf_user_system) {
    $rtf_user_system = new RtfUserSystem();
}

header('Content-Type: text/html; charset=utf-8');

// Get test user ID from query string
$test_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : 'view';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test User Deletion</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e293b; color: #e2e8f0; }
        .success { color: #10b981; background: #064e3b; padding: 10px; margin: 10px 0; }
        .error { color: #ef4444; background: #7f1d1d; padding: 10px; margin: 10px 0; }
        .info { color: #3b82f6; background: #1e3a8a; padding: 10px; margin: 10px 0; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
        .delete-btn { background: #dc2626; color: white; border: none; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #475569; padding: 8px; text-align: left; }
        th { background: #334155; }
    </style>
</head>
<body>
    <h1>🧪 Test User Deletion</h1>

<?php

if ($action === 'delete' && $test_user_id) {
    echo '<div class="info">Attempting to delete user ID: ' . $test_user_id . '</div>';
    
    // Check if user exists BEFORE deletion
    $user_before = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE id = %d",
        $test_user_id
    ));
    
    if ($user_before) {
        echo '<div class="info">✓ User exists before deletion:</div>';
        echo '<pre>' . print_r($user_before, true) . '</pre>';
        
        // Perform deletion
        $result = $rtf_user_system->delete_user($test_user_id);
        
        if ($result['success']) {
            echo '<div class="success">✓ RtfUserSystem::delete_user() returned SUCCESS</div>';
            echo '<div class="info">Message: ' . $result['message'] . '</div>';
            
            // Check if user exists AFTER deletion
            $user_after = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE id = %d",
                $test_user_id
            ));
            
            if ($user_after) {
                echo '<div class="error">✗✗✗ CRITICAL: User STILL exists in database after deletion!</div>';
                echo '<pre>' . print_r($user_after, true) . '</pre>';
                
                // Try direct deletion
                echo '<div class="info">Trying direct $wpdb->delete()...</div>';
                $direct_delete = $wpdb->delete(
                    $wpdb->prefix . 'rtf_platform_users',
                    ['id' => $test_user_id],
                    ['%d']
                );
                
                if ($direct_delete) {
                    echo '<div class="success">✓ Direct deletion successful</div>';
                } else {
                    echo '<div class="error">✗ Direct deletion failed: ' . $wpdb->last_error . '</div>';
                }
                
            } else {
                echo '<div class="success">✓✓✓ VERIFIED: User successfully deleted from database</div>';
            }
            
        } else {
            echo '<div class="error">✗ RtfUserSystem::delete_user() returned FAILURE</div>';
            echo '<div class="error">Error: ' . $result['message'] . '</div>';
        }
        
    } else {
        echo '<div class="error">✗ User ID ' . $test_user_id . ' not found before deletion</div>';
    }
    
    echo '<p><a href="?action=view" style="color: #3b82f6;">← Back to user list</a></p>';
}

// Show all users
echo '<h2>All Users in Database</h2>';

$all_users = $wpdb->get_results("
    SELECT id, username, email, subscription_status, stripe_customer_id, created_at 
    FROM {$wpdb->prefix}rtf_platform_users 
    ORDER BY id DESC
");

if ($all_users) {
    echo '<table>';
    echo '<tr><th>ID</th><th>Username</th><th>Email</th><th>Subscription</th><th>Stripe ID</th><th>Created</th><th>Action</th></tr>';
    
    foreach ($all_users as $user) {
        echo '<tr>';
        echo '<td>' . $user->id . '</td>';
        echo '<td>' . htmlspecialchars($user->username) . '</td>';
        echo '<td>' . htmlspecialchars($user->email) . '</td>';
        echo '<td>' . $user->subscription_status . '</td>';
        echo '<td>' . ($user->stripe_customer_id ?: 'None') . '</td>';
        echo '<td>' . $user->created_at . '</td>';
        echo '<td>';
        echo '<a href="?action=delete&user_id=' . $user->id . '" onclick="return confirm(\'DELETE user ' . htmlspecialchars($user->username) . '?\')">';
        echo '<button class="delete-btn">🗑️ Delete</button>';
        echo '</a>';
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    echo '<p>Total users: ' . count($all_users) . '</p>';
} else {
    echo '<p>No users found</p>';
}

?>

<h2>Debug Info</h2>
<div class="info">
    <p><strong>Database prefix:</strong> <?php echo $wpdb->prefix; ?></p>
    <p><strong>Table name:</strong> <?php echo $wpdb->prefix . 'rtf_platform_users'; ?></p>
    <p><strong>RtfUserSystem loaded:</strong> <?php echo class_exists('RtfUserSystem') ? 'Yes' : 'No'; ?></p>
    <p><strong>Last wpdb error:</strong> <?php echo $wpdb->last_error ?: 'None'; ?></p>
</div>

</body>
</html>
