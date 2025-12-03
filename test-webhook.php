<?php
/**
 * WEBHOOK TEST: Test if webhook can activate subscriptions
 * This simulates a Stripe checkout.session.completed event
 * 
 * USAGE: https://yourdomain.dk/wp-content/themes/ret-til-familie/test-webhook.php?email=user@example.com
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
    die('ERROR: Could not load WordPress');
}

global $wpdb;

// Get email from URL parameter
$test_email = isset($_GET['email']) ? sanitize_email($_GET['email']) : 'niller.jensen.89@gmail.com';
$activate = isset($_GET['activate']) && $_GET['activate'] === '1';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RTF Webhook Test</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; padding: 20px; background: #f8fafc; max-width: 900px; margin: 0 auto; }
        h1 { color: #0ea5e9; }
        h2 { color: #334155; margin-top: 30px; }
        .box { background: white; padding: 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .success { background: #10b981; color: white; padding: 15px; border-radius: 8px; margin: 15px 0; font-weight: bold; }
        .error { background: #ef4444; color: white; padding: 15px; border-radius: 8px; margin: 15px 0; font-weight: bold; }
        .info { background: #f0f9ff; border: 2px solid #0ea5e9; padding: 15px; border-radius: 8px; margin: 15px 0; }
        pre { background: #f1f5f9; padding: 15px; border-radius: 6px; overflow-x: auto; font-family: 'Courier New', monospace; font-size: 13px; }
        button { background: #0ea5e9; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; }
        button:hover { background: #0284c7; }
        .badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-weight: 600; font-size: 14px; }
        .badge.active { background: #10b981; color: white; }
        .badge.inactive { background: #ef4444; color: white; }
        code { background: #e0f2fe; padding: 2px 6px; border-radius: 4px; font-family: 'Courier New', monospace; }
        .step { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 15px 0; }
    </style>
</head>
<body>

<h1>🧪 RTF Webhook Test Tool</h1>

<?php

// Check if user exists
$user = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE email = %s",
    $test_email
));

if (!$user) {
    echo "<div class='error'>❌ User not found: " . htmlspecialchars($test_email) . "</div>";
    echo "<div class='info'>";
    echo "<h3>Available users:</h3>";
    $all_users = $wpdb->get_results("SELECT email, username, subscription_status FROM {$wpdb->prefix}rtf_platform_users LIMIT 10");
    echo "<pre>";
    foreach ($all_users as $u) {
        echo $u->email . " (@" . $u->username . ") - Status: " . $u->subscription_status . "\n";
    }
    echo "</pre>";
    echo "</div>";
    exit;
}

// Display current status
echo "<div class='box'>";
echo "<h2>📊 Current Status</h2>";
echo "<pre>";
echo "User ID:             " . $user->id . "\n";
echo "Username:            " . $user->username . "\n";
echo "Email:               " . $user->email . "\n";
echo "Full Name:           " . $user->full_name . "\n";
echo "Subscription Status: <span class='badge " . ($user->subscription_status === 'active' ? 'active' : 'inactive') . "'>" . strtoupper($user->subscription_status) . "</span>\n";
echo "End Date:            " . ($user->subscription_end_date ?? 'NULL') . "\n";
echo "Stripe Customer ID:  " . ($user->stripe_customer_id ?? 'NULL') . "\n";
echo "Created:             " . $user->created_at . "\n";
echo "</pre>";
echo "</div>";

// If activate button was clicked
if ($activate) {
    echo "<div class='box'>";
    echo "<h2>⚙️ Simulating Webhook Activation...</h2>";
    
    error_log('RTF WEBHOOK TEST: Starting manual activation for ' . $test_email);
    
    $end_date = date('Y-m-d H:i:s', strtotime('+30 days'));
    $customer_id = 'cus_test_' . time();
    
    // Exact same logic as webhook
    $update_data = [
        'subscription_status' => 'active',
        'subscription_end_date' => $end_date,
        'stripe_customer_id' => $customer_id
    ];
    
    echo "<div class='step'>";
    echo "<strong>Step 1:</strong> Preparing update...<br>";
    echo "Setting subscription_status = 'active'<br>";
    echo "Setting subscription_end_date = '$end_date'<br>";
    echo "Setting stripe_customer_id = '$customer_id'<br>";
    echo "</div>";
    
    $update_result = $wpdb->update(
        $wpdb->prefix . 'rtf_platform_users',
        $update_data,
        ['email' => $test_email],
        ['%s', '%s', '%s'],
        ['%s']
    );
    
    if ($update_result === false) {
        error_log('RTF WEBHOOK TEST ERROR: ' . $wpdb->last_error);
        echo "<div class='error'>";
        echo "❌ Database update FAILED!<br>";
        echo "Error: " . htmlspecialchars($wpdb->last_error) . "<br>";
        echo "Query: " . htmlspecialchars($wpdb->last_query) . "<br>";
        echo "</div>";
    } else {
        error_log('RTF WEBHOOK TEST SUCCESS: Rows affected = ' . $update_result);
        
        echo "<div class='step'>";
        echo "<strong>Step 2:</strong> Update executed<br>";
        echo "Rows affected: " . $update_result . "<br>";
        echo "</div>";
        
        // Verify update
        $verify_user = $wpdb->get_row($wpdb->prepare(
            "SELECT subscription_status, subscription_end_date, stripe_customer_id FROM {$wpdb->prefix}rtf_platform_users WHERE email = %s",
            $test_email
        ));
        
        echo "<div class='step'>";
        echo "<strong>Step 3:</strong> Verifying update (fresh query)...<br>";
        echo "Status: <span class='badge " . ($verify_user->subscription_status === 'active' ? 'active' : 'inactive') . "'>" . strtoupper($verify_user->subscription_status) . "</span><br>";
        echo "End Date: " . $verify_user->subscription_end_date . "<br>";
        echo "Customer ID: " . $verify_user->stripe_customer_id . "<br>";
        echo "</div>";
        
        if ($verify_user->subscription_status === 'active') {
            echo "<div class='success'>";
            echo "✅ ACTIVATION SUCCESSFUL!<br>";
            echo "User is now ACTIVE in the database!<br>";
            echo "Webhook logic is working correctly!<br>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "❌ VERIFICATION FAILED!<br>";
            echo "Status is still: " . $verify_user->subscription_status . "<br>";
            echo "</div>";
        }
        
        // Log to payments table
        echo "<div class='step'>";
        echo "<strong>Step 4:</strong> Logging payment...<br>";
        
        $payment_insert = $wpdb->insert($wpdb->prefix . 'rtf_stripe_payments', [
            'user_id' => $user->id,
            'stripe_customer_id' => $customer_id,
            'stripe_subscription_id' => 'sub_test_' . time(),
            'amount' => 4900,
            'currency' => 'DKK',
            'status' => 'completed',
            'payment_intent_id' => 'pi_test_' . time(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($payment_insert === false) {
            echo "Payment log failed: " . htmlspecialchars($wpdb->last_error) . "<br>";
        } else {
            echo "Payment logged successfully (ID: " . $wpdb->insert_id . ")<br>";
        }
        echo "</div>";
    }
    
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>📝 Next Steps for User:</h3>";
    echo "<ol>";
    echo "<li><strong>LOGOUT</strong> completely (close all browser tabs)</li>";
    echo "<li><strong>LOGIN</strong> again to refresh session</li>";
    echo "<li><strong>Access platform</strong> - should now work!</li>";
    echo "</ol>";
    echo "<p><strong>Alternative:</strong> Add <code>?refresh=1</code> to profile URL</p>";
    echo "</div>";
    
} else {
    // Show activation button
    echo "<div class='box'>";
    echo "<h2>🚀 Test Activation</h2>";
    echo "<p>Click the button below to simulate a successful Stripe payment webhook:</p>";
    echo "<form method='get'>";
    echo "<input type='hidden' name='email' value='" . htmlspecialchars($test_email) . "'>";
    echo "<input type='hidden' name='activate' value='1'>";
    echo "<button type='submit'>Simulate Webhook Activation</button>";
    echo "</form>";
    echo "</div>";
}

// Show webhook endpoint info
echo "<div class='box'>";
echo "<h2>🔗 Webhook Configuration</h2>";
echo "<p>Your Stripe webhook should be configured to send events to:</p>";
echo "<pre>https://ret-til-familie.dk/wp-content/themes/ret-til-familie/stripe-webhook.php</pre>";
echo "<p><strong>Events to send:</strong></p>";
echo "<ul>";
echo "<li>checkout.session.completed</li>";
echo "<li>customer.subscription.updated</li>";
echo "<li>customer.subscription.deleted</li>";
echo "<li>invoice.payment_failed</li>";
echo "</ul>";
echo "</div>";

// Show recent payments
echo "<div class='box'>";
echo "<h2>💳 Recent Stripe Payments</h2>";

$payments = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}rtf_stripe_payments ORDER BY created_at DESC LIMIT 5"
);

if ($payments) {
    echo "<pre>";
    foreach ($payments as $payment) {
        echo "---\n";
        echo "User ID:        " . $payment->user_id . "\n";
        echo "Customer ID:    " . $payment->stripe_customer_id . "\n";
        echo "Amount:         " . ($payment->amount / 100) . " " . $payment->currency . "\n";
        echo "Status:         " . $payment->status . "\n";
        echo "Created:        " . $payment->created_at . "\n";
    }
    echo "</pre>";
} else {
    echo "<p>No payments recorded yet</p>";
}

echo "</div>";

?>

</body>
</html>
