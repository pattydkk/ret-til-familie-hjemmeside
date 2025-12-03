<?php
/**
 * Emergency User Activation
 * Aktiverer manuelt en bruger hvis webhook fejlede
 */

require_once __DIR__ . '/wp-load.php';

// Simpel password beskyttelse
$admin_password = 'rtf2024admin'; // Ændr denne!
if (!isset($_POST['admin_pass']) || $_POST['admin_pass'] !== $admin_password) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>User Activation</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            input, button { padding: 10px; margin: 10px 0; width: 100%; font-size: 16px; }
            button { background: #2563eb; color: white; border: none; cursor: pointer; border-radius: 5px; }
            button:hover { background: #1d4ed8; }
        </style>
    </head>
    <body>
        <h2>🔒 User Activation - Admin Login</h2>
        <form method="POST">
            <input type="password" name="admin_pass" placeholder="Admin password" required>
            <button type="submit">Login</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

global $wpdb;
$table = $wpdb->prefix . 'rtf_platform_users';

// Handle activation
if (isset($_POST['activate_user'])) {
    $user_id = intval($_POST['user_id']);
    
    // Opdater til aktiv med 30 dages abonnement
    $end_date = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $result = $wpdb->update(
        $table,
        [
            'subscription_status' => 'active',
            'subscription_end_date' => $end_date
        ],
        ['id' => $user_id],
        ['%s', '%s'],
        ['%d']
    );
    
    if ($result !== false) {
        echo "<div style='background: #d1fae5; padding: 20px; margin: 20px 0; border-radius: 8px; color: #065f46;'>";
        echo "<strong>✅ SUCCESS!</strong> Bruger aktiveret til: " . $end_date;
        echo "</div>";
    } else {
        echo "<div style='background: #fee2e2; padding: 20px; margin: 20px 0; border-radius: 8px; color: #991b1b;'>";
        echo "<strong>❌ ERROR:</strong> " . $wpdb->last_error;
        echo "</div>";
    }
}

// Get all users
$users = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 20");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Activation Tool</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1200px; 
            margin: 20px auto; 
            padding: 20px; 
            background: #f8fafc;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th, td { 
            padding: 12px; 
            text-align: left; 
            border-bottom: 1px solid #e5e7eb; 
        }
        th { 
            background: #1e40af; 
            color: white;
            font-weight: 600;
        }
        tr:hover { background: #f9fafb; }
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .btn {
            padding: 8px 16px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover { background: #1d4ed8; }
        .btn-success { background: #10b981; }
        .btn-success:hover { background: #059669; }
        h1 { color: #1e40af; }
        .info { 
            background: #dbeafe; 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 20px;
            border-left: 4px solid #2563eb;
        }
    </style>
</head>
<body>
    <h1>👥 User Activation Tool</h1>
    
    <div class="info">
        <strong>ℹ️ Info:</strong> Aktiverer brugere manuelt hvis webhook fejlede ved betaling.<br>
        Subscription end date sættes automatisk til +30 dage fra nu.
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Status</th>
                <th>End Date</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user->id; ?></td>
                <td><?php echo htmlspecialchars($user->username); ?></td>
                <td><?php echo htmlspecialchars($user->email); ?></td>
                <td>
                    <span class="badge <?php echo $user->subscription_status === 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                        <?php echo strtoupper($user->subscription_status); ?>
                    </span>
                </td>
                <td><?php echo $user->subscription_end_date ?? 'N/A'; ?></td>
                <td><?php echo $user->created_at; ?></td>
                <td>
                    <?php if ($user->subscription_status !== 'active'): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="admin_pass" value="<?php echo htmlspecialchars($_POST['admin_pass']); ?>">
                        <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                        <button type="submit" name="activate_user" class="btn btn-success">
                            ✅ Activate
                        </button>
                    </form>
                    <?php else: ?>
                    <span style="color: #10b981; font-weight: 600;">✓ Active</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 30px; padding: 20px; background: white; border-radius: 8px;">
        <h3>📋 Quick Stats</h3>
        <p><strong>Total Users:</strong> <?php echo $wpdb->get_var("SELECT COUNT(*) FROM $table"); ?></p>
        <p><strong>Active Subscriptions:</strong> <?php echo $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE subscription_status = 'active'"); ?></p>
        <p><strong>Inactive Subscriptions:</strong> <?php echo $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE subscription_status = 'inactive'"); ?></p>
    </div>
</body>
</html>
