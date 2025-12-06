<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Create Admin Users</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 40px auto; padding: 20px; background: #f3f4f6; }
        .container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #1e40af; margin-bottom: 20px; }
        .success { background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 10px 0; border-radius: 6px; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 10px 0; border-radius: 6px; }
        .error { background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 10px 0; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #3b82f6; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
        tr:hover { background: #f9fafb; }
        .highlight { background: #fef3c7; font-weight: bold; }
        .credentials { background: #eff6ff; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .credentials h3 { margin-top: 0; color: #1e40af; }
        .cred-item { margin: 15px 0; padding: 10px; background: white; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Create Admin Users</h1>
        
        <?php
        // Load WordPress
        $wp_load_paths = [
            __DIR__ . '/wp-load.php',
            __DIR__ . '/../../../wp-load.php',
            dirname(dirname(dirname(__DIR__))) . '/wp-load.php'
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
            echo '<div class="error">❌ Could not load WordPress. Place this file in your WordPress root directory.</div>';
            echo '<p>Tried paths:</p><ul>';
            foreach ($wp_load_paths as $path) {
                echo '<li>' . htmlspecialchars($path) . '</li>';
            }
            echo '</ul>';
            exit;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'rtf_platform_users';
        
        // Admin users to create
        $admins = [
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
        
        echo '<h2>Creating Admin Users...</h2>';
        
        foreach ($admins as $admin) {
            echo '<div style="margin: 20px 0; padding: 15px; background: #f9fafb; border-radius: 8px;">';
            echo '<h3>👤 ' . htmlspecialchars($admin['full_name']) . '</h3>';
            
            // Check if exists
            $exists = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE email = %s OR username = %s",
                $admin['email'], $admin['username']
            ));
            
            if ($exists) {
                echo '<div class="warning">⚠️ User already exists (ID: ' . $exists->id . ')</div>';
                
                // Update to admin
                $hashed = password_hash($admin['password'], PASSWORD_BCRYPT);
                $result = $wpdb->update(
                    $table,
                    [
                        'is_admin' => 1,
                        'is_active' => 1,
                        'password_hash' => $hashed,
                        'subscription_status' => 'active',
                        'subscription_end_date' => date('Y-m-d H:i:s', strtotime('+10 years')),
                        'email_verified' => 1,
                        'updated_at' => current_time('mysql')
                    ],
                    ['id' => $exists->id],
                    ['%d', '%d', '%s', '%s', '%s', '%d', '%s'],
                    ['%d']
                );
                
                if ($result !== false) {
                    echo '<div class="success">✅ Updated to admin status</div>';
                } else {
                    echo '<div class="error">❌ Update failed: ' . $wpdb->last_error . '</div>';
                }
            } else {
                // Create new
                $hashed = password_hash($admin['password'], PASSWORD_BCRYPT);
                $result = $wpdb->insert(
                    $table,
                    [
                        'username' => $admin['username'],
                        'email' => $admin['email'],
                        'password_hash' => $hashed,
                        'full_name' => $admin['full_name'],
                        'phone' => $admin['phone'],
                        'is_admin' => 1,
                        'is_active' => 1,
                        'subscription_status' => 'active',
                        'subscription_start_date' => current_time('mysql'),
                        'subscription_end_date' => date('Y-m-d H:i:s', strtotime('+10 years')),
                        'email_verified' => 1,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ],
                    ['%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s']
                );
                
                if ($result) {
                    echo '<div class="success">✅ Created with ID: ' . $wpdb->insert_id . '</div>';
                } else {
                    echo '<div class="error">❌ Creation failed: ' . $wpdb->last_error . '</div>';
                }
            }
            
            echo '<p><strong>📧 Email:</strong> ' . htmlspecialchars($admin['email']) . '</p>';
            echo '<p><strong>🔑 Password:</strong> <code>' . htmlspecialchars($admin['password']) . '</code></p>';
            echo '</div>';
        }
        
        // Show all admins
        echo '<hr style="margin: 40px 0;">';
        echo '<h2>📋 All Admin Users</h2>';
        
        $all_admins = $wpdb->get_results("SELECT * FROM $table WHERE is_admin = 1 ORDER BY id ASC");
        
        if ($all_admins) {
            echo '<table>';
            echo '<thead><tr>';
            echo '<th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Admin</th><th>Active</th><th>Subscription</th>';
            echo '</tr></thead>';
            echo '<tbody>';
            
            foreach ($all_admins as $user) {
                $row_class = ($user->email === 'patrickforslev@gmail.com') ? 'class="highlight"' : '';
                $crown = ($user->email === 'patrickforslev@gmail.com') ? ' 👑' : '';
                
                echo '<tr ' . $row_class . '>';
                echo '<td>' . $user->id . '</td>';
                echo '<td>' . htmlspecialchars($user->username) . $crown . '</td>';
                echo '<td>' . htmlspecialchars($user->email) . '</td>';
                echo '<td>' . htmlspecialchars($user->full_name) . '</td>';
                echo '<td>' . ($user->is_admin ? '✅ YES' : '❌ NO') . '</td>';
                echo '<td>' . ($user->is_active ? '✅ YES' : '❌ NO') . '</td>';
                echo '<td>' . htmlspecialchars($user->subscription_status) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
        }
        
        // Display credentials
        echo '<div class="credentials">';
        echo '<h3>🔐 Login Credentials</h3>';
        
        echo '<div class="cred-item">';
        echo '<strong>1. Patrick (OWNER) 👑</strong><br>';
        echo 'Email: patrickforslev@gmail.com<br>';
        echo 'Password: [Your existing password]';
        echo '</div>';
        
        $counter = 2;
        foreach ($admins as $admin) {
            echo '<div class="cred-item">';
            echo '<strong>' . $counter . '. ' . htmlspecialchars($admin['full_name']) . '</strong><br>';
            echo 'Email: ' . htmlspecialchars($admin['email']) . '<br>';
            echo 'Password: <code>' . htmlspecialchars($admin['password']) . '</code>';
            echo '</div>';
            $counter++;
        }
        
        echo '</div>';
        
        echo '<div style="margin-top: 30px; text-align: center;">';
        echo '<a href="' . home_url('/platform-auth') . '" style="background: #2563eb; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: bold;">Go to Login Page</a>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
