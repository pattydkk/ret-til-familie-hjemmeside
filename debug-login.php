<?php
/**
 * Template Name: Debug Login System
 */

get_header();

global $wpdb;
$table = $wpdb->prefix . 'rtf_platform_users';

echo '<div style="max-width: 1200px; margin: 40px auto; padding: 30px; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">';
echo '<h1 style="color: #2563eb; margin-bottom: 30px;">🔍 Login System Debug</h1>';

// Check if table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
echo '<div style="padding: 20px; background: ' . ($table_exists ? '#d1fae5' : '#fee2e2') . '; border-radius: 8px; margin-bottom: 20px;">';
echo '<h2>1. Database Table Status</h2>';
echo '<p><strong>Table exists:</strong> ' . ($table_exists ? '✅ YES' : '❌ NO - RUN SETUP!') . '</p>';
echo '<p><strong>Table name:</strong> <code>' . $table . '</code></p>';
echo '</div>';

if ($table_exists) {
    // Show all users
    echo '<div style="padding: 20px; background: #f0f9ff; border-radius: 8px; margin-bottom: 20px;">';
    echo '<h2>2. All Users in Database</h2>';
    $users = $wpdb->get_results("SELECT id, username, email, is_admin, is_active, subscription_status, created_at FROM $table");
    
    if ($users) {
        echo '<table style="width: 100%; border-collapse: collapse; background: white;">';
        echo '<thead><tr style="background: #2563eb; color: white;">';
        echo '<th style="padding: 12px; text-align: left;">ID</th>';
        echo '<th style="padding: 12px; text-align: left;">Username</th>';
        echo '<th style="padding: 12px; text-align: left;">Email</th>';
        echo '<th style="padding: 12px; text-align: left;">Admin</th>';
        echo '<th style="padding: 12px; text-align: left;">Active</th>';
        echo '<th style="padding: 12px; text-align: left;">Subscription</th>';
        echo '<th style="padding: 12px; text-align: left;">Created</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($users as $user) {
            echo '<tr style="border-bottom: 1px solid #e5e7eb;">';
            echo '<td style="padding: 12px;">' . $user->id . '</td>';
            echo '<td style="padding: 12px;"><strong>' . $user->username . '</strong></td>';
            echo '<td style="padding: 12px;">' . $user->email . '</td>';
            echo '<td style="padding: 12px;">' . ($user->is_admin ? '👑 YES' : 'No') . '</td>';
            echo '<td style="padding: 12px;">' . ($user->is_active ? '✅ Active' : '❌ Inactive') . '</td>';
            echo '<td style="padding: 12px;">' . $user->subscription_status . '</td>';
            echo '<td style="padding: 12px;">' . date('Y-m-d H:i', strtotime($user->created_at)) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        
        echo '<p style="margin-top: 15px;"><strong>Total users:</strong> ' . count($users) . '</p>';
    } else {
        echo '<p style="color: #dc2626;">❌ No users found in database!</p>';
    }
    echo '</div>';
    
    // Check admin user specifically
    echo '<div style="padding: 20px; background: #fef3c7; border-radius: 8px; margin-bottom: 20px;">';
    echo '<h2>3. Admin User Check</h2>';
    $admin = $wpdb->get_row("SELECT * FROM $table WHERE email = 'patrickfoerslev@gmail.com'");
    
    if ($admin) {
        echo '<p>✅ Admin user EXISTS</p>';
        echo '<p><strong>ID:</strong> ' . $admin->id . '</p>';
        echo '<p><strong>Username:</strong> ' . $admin->username . '</p>';
        echo '<p><strong>Email:</strong> ' . $admin->email . '</p>';
        echo '<p><strong>Is Admin:</strong> ' . ($admin->is_admin ? '✅ YES' : '❌ NO') . '</p>';
        echo '<p><strong>Is Active:</strong> ' . ($admin->is_active ? '✅ YES' : '❌ NO') . '</p>';
        echo '<p><strong>Password Hash:</strong> ' . (!empty($admin->password) ? '✅ EXISTS (' . substr($admin->password, 0, 20) . '...)' : '❌ MISSING') . '</p>';
        
        // Test password verification
        if (!empty($admin->password)) {
            $test_password = 'Ph1357911';
            $verify_result = password_verify($test_password, $admin->password);
            echo '<p><strong>Password Test (Ph1357911):</strong> ' . ($verify_result ? '✅ CORRECT' : '❌ WRONG') . '</p>';
        }
    } else {
        echo '<p style="color: #dc2626;">❌ Admin user NOT FOUND!</p>';
        echo '<p>Email searched: patrickfoerslev@gmail.com</p>';
    }
    echo '</div>';
    
    // Test login form
    echo '<div style="padding: 20px; background: #e0f2fe; border-radius: 8px;">';
    echo '<h2>4. Test Login Form</h2>';
    echo '<form method="POST" style="max-width: 400px;">';
    echo '<input type="hidden" name="test_login" value="1">';
    echo '<div style="margin-bottom: 15px;">';
    echo '<label style="display: block; margin-bottom: 5px; font-weight: 600;">Email:</label>';
    echo '<input type="text" name="test_email" value="patrickfoerslev@gmail.com" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">';
    echo '</div>';
    echo '<div style="margin-bottom: 15px;">';
    echo '<label style="display: block; margin-bottom: 5px; font-weight: 600;">Password:</label>';
    echo '<input type="password" name="test_password" value="Ph1357911" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">';
    echo '</div>';
    echo '<button type="submit" style="background: #2563eb; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Test Login</button>';
    echo '</form>';
    
    // Process test login
    if (isset($_POST['test_login'])) {
        echo '<div style="margin-top: 20px; padding: 15px; background: #f8fafc; border: 2px solid #2563eb; border-radius: 6px;">';
        echo '<h3>Test Result:</h3>';
        
        $test_email = sanitize_email($_POST['test_email']);
        $test_password = $_POST['test_password'];
        
        $test_user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE email = %s", $test_email));
        
        if ($test_user) {
            echo '<p>✅ User found with email: ' . $test_email . '</p>';
            echo '<p>User ID: ' . $test_user->id . '</p>';
            echo '<p>Username: ' . $test_user->username . '</p>';
            echo '<p>Is Active: ' . ($test_user->is_active ? 'YES' : 'NO') . '</p>';
            
            if (password_verify($test_password, $test_user->password)) {
                echo '<p style="color: #059669; font-weight: bold;">✅✅✅ PASSWORD CORRECT! Login should work!</p>';
                echo '<p><strong>Session status:</strong> ' . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not Active') . '</p>';
            } else {
                echo '<p style="color: #dc2626; font-weight: bold;">❌ PASSWORD WRONG!</p>';
                echo '<p>Hash in DB: ' . substr($test_user->password, 0, 30) . '...</p>';
            }
        } else {
            echo '<p style="color: #dc2626;">❌ User not found with email: ' . $test_email . '</p>';
        }
        echo '</div>';
    }
    echo '</div>';
}

// Session info
echo '<div style="padding: 20px; background: #f3f4f6; border-radius: 8px; margin-top: 20px;">';
echo '<h2>5. Session Information</h2>';
echo '<p><strong>Session Status:</strong> ' . (session_status() === PHP_SESSION_ACTIVE ? '✅ Active' : '❌ Not Active') . '</p>';
echo '<p><strong>Session ID:</strong> ' . (session_status() === PHP_SESSION_ACTIVE ? session_id() : 'N/A') . '</p>';
if (isset($_SESSION['rtf_user_id'])) {
    echo '<p><strong>Logged in User ID:</strong> ' . $_SESSION['rtf_user_id'] . '</p>';
    echo '<p><strong>Logged in Username:</strong> ' . $_SESSION['rtf_username'] . '</p>';
} else {
    echo '<p>Not logged in</p>';
}
echo '</div>';

echo '<div style="margin-top: 30px; padding: 20px; background: #059669; color: white; border-radius: 8px;">';
echo '<h2>Next Steps:</h2>';
echo '<ol style="line-height: 2;">';
echo '<li>If admin user doesn\'t exist → Run setup again</li>';
echo '<li>If password test shows WRONG → Setup needs to recreate admin</li>';
echo '<li>If everything shows ✅ → Try login at <a href="' . home_url('/platform-auth/') . '" style="color: white; text-decoration: underline;">/platform-auth/</a></li>';
echo '</ol>';
echo '</div>';

echo '</div>';

get_footer();
?>
