<?php
/**
 * TEST: Manual Subscription Activation
 * This simulates what the webhook should do
 */

// Load WordPress
$wp_load_paths = [
    __DIR__ . '/../../../wp-load.php',
    __DIR__ . '/../../wp-load.php',
    __DIR__ . '/../wp-load.php',
    __DIR__ . '/wp-load.php'
];

foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        break;
    }
}

if (!function_exists('wp')) {
    die('Could not load WordPress');
}

global $wpdb;

// Configuration
$test_email = 'niller.jensen.89@gmail.com';
$activate = isset($_GET['activate']) && $_GET['activate'] == '1';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RTF Subscription Test</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; padding: 20px; background: #f8fafc; }
        h1 { color: #0ea5e9; }
        .box { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .success { background: #10b981; color: white; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .error { background: #ef4444; color: white; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .info { background: #f0f9ff; border: 2px solid #0ea5e9; padding: 15px; border-radius: 8px; margin: 20px 0; }
        pre { background: #f1f5f9; padding: 15px; border-radius: 6px; overflow-x: auto; }
        button { background: #0ea5e9; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; }
        button:hover { background: #0284c7; }
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-weight: 600; }
        .status-badge.active { background: #10b981; color: white; }
        .status-badge.inactive { background: #ef4444; color: white; }
    </style>
</head>
<body>

<h1>🧪 Subscription Activation Test</h1>

<?php

// Get current user data
$user = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE email = %s",
    $test_email
));

if (!$user) {
    echo "<div class='error'>❌ User not found: $test_email</div>";
    exit;
}

// Show current status
echo "<div class='box'>";
echo "<h2>📊 Current Status</h2>";
echo "<pre>";
echo "User ID:             " . $user->id . "\n";
echo "Username:            " . $user->username . "\n";
echo "Email:               " . $user->email . "\n";
echo "Subscription Status: <span class='status-badge " . ($user->subscription_status === 'active' ? 'active' : 'inactive') . "'>" . strtoupper($user->subscription_status) . "</span>\n";
echo "End Date:            " . ($user->subscription_end_date ?? 'NULL') . "\n";
echo "Stripe Customer ID:  " . ($user->stripe_customer_id ?? 'NULL') . "\n";
echo "</pre>";
echo "</div>";

// If activate button clicked
if ($activate) {
    echo "<div class='box'>";
    echo "<h2>⚙️ Activating Subscription...</h2>";
    
    $end_date = date('Y-m-d H:i:s', strtotime('+30 days'));
    $customer_id = 'cus_test_' . time();
    
    $update_result = $wpdb->update(
        $wpdb->prefix . 'rtf_platform_users',
        [
            'subscription_status' => 'active',
            'stripe_customer_id' => $customer_id,
            'subscription_end_date' => $end_date
        ],
        ['email' => $test_email],
        ['%s', '%s', '%s'],
        ['%s']
    );
    
    if ($update_result === false) {
        echo "<div class='error'>";
        echo "❌ Database update FAILED!\n";
        echo "Error: " . $wpdb->last_error . "\n";
        echo "</div>";
    } else {
        echo "<div class='success'>";
        echo "✅ Database update SUCCESSFUL!\n";
        echo "Rows affected: $update_result\n";
        echo "New Status: active\n";
        echo "End Date: $end_date\n";
        echo "</div>";
        
        // Verify the update
        $user_after = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE email = %s",
            $test_email
        ));
        
        echo "<h3>✅ Verification (Fresh Query)</h3>";
        echo "<pre>";
        echo "Subscription Status: <span class='status-badge " . ($user_after->subscription_status === 'active' ? 'active' : 'inactive') . "'>" . strtoupper($user_after->subscription_status) . "</span>\n";
        echo "End Date:            " . $user_after->subscription_end_date . "\n";
        echo "Stripe Customer ID:  " . $user_after->stripe_customer_id . "\n";
        echo "</pre>";
    }
    
    echo "</div>";
    
    echo "<div class='info'>";
    echo "✅ <strong>NEXT STEP:</strong> User must logout and login again to refresh session data!<br>";
    echo "📝 <strong>Or:</strong> User can add <code>?refresh=1</code> to profile URL";
    echo "</div>";
}

// Show activation button
if ($user->subscription_status !== 'active') {
    echo "<div class='box'>";
    echo "<h2>🔄 Activate Subscription</h2>";
    echo "<p>Click the button below to activate the subscription (simulates successful Stripe payment):</p>";
    echo "<form method='get'>";
    echo "<input type='hidden' name='activate' value='1'>";
    echo "<button type='submit'>Activate Subscription Now</button>";
    echo "</form>";
    echo "</div>";
} else {
    echo "<div class='success'>";
    echo "✅ Subscription is already ACTIVE!";
    echo "</div>";
}

// Show SQL queries for manual debugging
echo "<div class='box'>";
echo "<h2>🔍 Manual SQL Queries (for phpMyAdmin)</h2>";
echo "<pre>";
echo "-- Check user status:\n";
echo "SELECT id, username, email, subscription_status, subscription_end_date, stripe_customer_id\n";
echo "FROM {$wpdb->prefix}rtf_platform_users\n";
echo "WHERE email = '$test_email';\n\n";

echo "-- Manually activate user:\n";
echo "UPDATE {$wpdb->prefix}rtf_platform_users\n";
echo "SET subscription_status = 'active',\n";
echo "    subscription_end_date = '" . date('Y-m-d H:i:s', strtotime('+30 days')) . "',\n";
echo "    stripe_customer_id = 'cus_manual_activation'\n";
echo "WHERE email = '$test_email';\n";
echo "</pre>";
echo "</div>";

// Check recent Stripe payments
echo "<div class='box'>";
echo "<h2>💳 Recent Stripe Payments</h2>";

$payments = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}rtf_stripe_payments ORDER BY created_at DESC LIMIT 5"
);

if ($payments) {
    echo "<pre>";
    foreach ($payments as $payment) {
        echo "---\n";
        echo "User ID:       " . $payment->user_id . "\n";
        echo "Customer ID:   " . $payment->stripe_customer_id . "\n";
        echo "Amount:        " . ($payment->amount / 100) . " " . $payment->currency . "\n";
        echo "Status:        " . $payment->status . "\n";
        echo "Created:       " . $payment->created_at . "\n";
    }
    echo "</pre>";
} else {
    echo "<p>No payments recorded yet</p>";
}

echo "</div>";

?>

</body>
</html>
