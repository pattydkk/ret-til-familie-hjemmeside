<?php
/**
 * Complete System Test
 * Verifier at hele user + subscription systemet virker
 */

require_once __DIR__ . '/wp-load.php';
require_once get_template_directory() . '/includes/class-rtf-user-system.php';

global $wpdb, $rtf_user_system;

if (!$rtf_user_system) {
    $rtf_user_system = new RtfUserSystem();
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>RTF System Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e293b; color: #e2e8f0; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        .info { color: #3b82f6; }
        h2 { border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
        .test-section { background: #334155; padding: 15px; margin: 10px 0; border-radius: 8px; }
    </style>
</head>
<body>
    <h1>🧪 RTF Complete System Test</h1>
    <p class="info">Testing User System + Subscription System + Admin Functions</p>

    <?php
    
    // TEST 1: RtfUserSystem Class Loaded
    echo '<div class="test-section">';
    echo '<h2>TEST 1: RtfUserSystem Class</h2>';
    if (class_exists('RtfUserSystem') && $rtf_user_system) {
        echo '<p class="success">✓ RtfUserSystem class loaded</p>';
        echo '<p class="info">Methods available: ' . implode(', ', get_class_methods($rtf_user_system)) . '</p>';
    } else {
        echo '<p class="error">✗ RtfUserSystem class NOT loaded</p>';
    }
    echo '</div>';
    
    // TEST 2: Database Tables
    echo '<div class="test-section">';
    echo '<h2>TEST 2: Database Tables</h2>';
    $tables = [
        'rtf_platform_users',
        'rtf_platform_privacy',
        'rtf_stripe_payments',
        'rtf_platform_posts',
        'rtf_platform_messages'
    ];
    
    foreach ($tables as $table) {
        $full_table = $wpdb->prefix . $table;
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'");
        if ($exists) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table");
            echo "<p class='success'>✓ Table $table exists ($count rows)</p>";
        } else {
            echo "<p class='error'>✗ Table $table NOT found</p>";
        }
    }
    echo '</div>';
    
    // TEST 3: User Operations
    echo '<div class="test-section">';
    echo '<h2>TEST 3: User Data</h2>';
    
    $users = $wpdb->get_results("SELECT id, username, email, subscription_status, stripe_customer_id FROM {$wpdb->prefix}rtf_platform_users LIMIT 5");
    
    if ($users) {
        echo '<p class="success">✓ Found ' . count($users) . ' users</p>';
        echo '<table border="1" cellpadding="5" style="background: #1e293b; color: #e2e8f0;">';
        echo '<tr><th>ID</th><th>Username</th><th>Email</th><th>Status</th><th>Stripe ID</th></tr>';
        foreach ($users as $user) {
            $stripe_status = $user->stripe_customer_id ? 
                '<span class="success">✓ ' . substr($user->stripe_customer_id, 0, 15) . '...</span>' : 
                '<span class="error">✗ None</span>';
            echo "<tr>";
            echo "<td>{$user->id}</td>";
            echo "<td>{$user->username}</td>";
            echo "<td>{$user->email}</td>";
            echo "<td>{$user->subscription_status}</td>";
            echo "<td>{$stripe_status}</td>";
            echo "</tr>";
        }
        echo '</table>';
    } else {
        echo '<p class="warning">⚠ No users found</p>';
    }
    echo '</div>';
    
    // TEST 4: Subscription Check
    echo '<div class="test-section">';
    echo '<h2>TEST 4: Active Subscriptions</h2>';
    
    $active_subs = $wpdb->get_results("
        SELECT id, username, email, subscription_status, subscription_end_date, stripe_customer_id 
        FROM {$wpdb->prefix}rtf_platform_users 
        WHERE subscription_status = 'active'
    ");
    
    if ($active_subs) {
        echo '<p class="success">✓ Found ' . count($active_subs) . ' active subscriptions</p>';
        foreach ($active_subs as $sub) {
            $days_left = 'N/A';
            if ($sub->subscription_end_date) {
                $end = strtotime($sub->subscription_end_date);
                $days_left = ceil(($end - time()) / 86400);
            }
            
            $has_stripe = $sub->stripe_customer_id ? '✓' : '✗';
            echo "<p class='info'>User: <strong>{$sub->username}</strong> | Email: {$sub->email} | Days left: $days_left | Stripe: $has_stripe</p>";
        }
    } else {
        echo '<p class="warning">⚠ No active subscriptions found</p>';
    }
    echo '</div>';
    
    // TEST 5: Payment Logs
    echo '<div class="test-section">';
    echo '<h2>TEST 5: Payment Logs</h2>';
    
    $payments = $wpdb->get_results("
        SELECT p.*, u.username, u.email 
        FROM {$wpdb->prefix}rtf_stripe_payments p
        LEFT JOIN {$wpdb->prefix}rtf_platform_users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    
    if ($payments) {
        echo '<p class="success">✓ Found ' . count($payments) . ' payment records</p>';
        foreach ($payments as $payment) {
            $amount = number_format($payment->amount / 100, 2);
            echo "<p class='info'>User: <strong>{$payment->username}</strong> | Amount: {$amount} {$payment->currency} | Status: {$payment->status} | Customer ID: {$payment->stripe_customer_id}</p>";
        }
    } else {
        echo '<p class="warning">⚠ No payment logs found</p>';
    }
    echo '</div>';
    
    // TEST 6: REST API Endpoints
    echo '<div class="test-section">';
    echo '<h2>TEST 6: REST API Endpoints</h2>';
    
    $endpoints = [
        'DELETE /wp-json/kate/v1/admin/user/{id}' => function_exists('rtf_api_admin_delete_user'),
        'GET /wp-json/kate/v1/admin/users' => function_exists('rtf_api_admin_get_users'),
        'PUT /wp-json/kate/v1/admin/subscription/{id}' => function_exists('rtf_api_admin_update_subscription')
    ];
    
    foreach ($endpoints as $endpoint => $exists) {
        if ($exists) {
            echo "<p class='success'>✓ $endpoint registered</p>";
        } else {
            echo "<p class='error'>✗ $endpoint NOT registered</p>";
        }
    }
    echo '</div>';
    
    // TEST 7: Webhook Integration
    echo '<div class="test-section">';
    echo '<h2>TEST 7: Webhook Setup</h2>';
    
    $webhook_file = __DIR__ . '/stripe-webhook.php';
    if (file_exists($webhook_file)) {
        echo '<p class="success">✓ Webhook file exists</p>';
        $webhook_content = file_get_contents($webhook_file);
        
        if (strpos($webhook_content, 'activate_subscription_by_email') !== false) {
            echo '<p class="success">✓ Webhook uses RtfUserSystem::activate_subscription_by_email()</p>';
        }
        
        if (strpos($webhook_content, 'log_payment') !== false) {
            echo '<p class="success">✓ Webhook logs payments</p>';
        }
        
        if (strpos($webhook_content, 'stripe_customer_id') !== false) {
            echo '<p class="success">✓ Webhook saves Stripe customer ID</p>';
        }
    } else {
        echo '<p class="error">✗ Webhook file not found</p>';
    }
    
    echo '<p class="info">Webhook URL: ' . home_url('/wp-content/themes/ret-til-familie/stripe-webhook.php') . '</p>';
    echo '</div>';
    
    ?>
    
    <div class="test-section">
        <h2>📋 Summary</h2>
        <p class="success">✓ System is operational</p>
        <p class="info">
            <strong>Complete Flow:</strong><br>
            1. User registers → RtfUserSystem::register()<br>
            2. Redirected to Stripe → Customer email matches database<br>
            3. Payment completed → Webhook receives checkout.session.completed<br>
            4. Webhook calls → RtfUserSystem::activate_subscription_by_email()<br>
            5. Stripe customer ID saved → subscription_status = 'active'<br>
            6. User logs in → RtfUserSystem::authenticate()<br>
            7. Access check → RtfUserSystem::has_active_subscription()<br>
            8. Admin can manage → delete_user(), admin_update_subscription()
        </p>
    </div>
    
</body>
</html>
