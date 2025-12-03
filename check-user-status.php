<?php
/**
 * DEBUG TOOL: Check User Subscription Status
 * Access via: https://ret-til-familie.dk/wp-content/themes/ret-til-familie/check-user-status.php
 */

// Load WordPress
$wp_load_paths = [
    '../../../wp-load.php',
    '../../wp-load.php',
    '../wp-load.php',
    'wp-load.php'
];

foreach ($wp_load_paths as $path) {
    if (file_exists(__DIR__ . '/' . $path)) {
        require_once(__DIR__ . '/' . $path);
        break;
    }
}

if (!function_exists('wp')) {
    die('Could not load WordPress');
}

global $wpdb;

// Test emails
$emails = [
    'niller.jensen.89@gmail.com',
    'patrickfoerslev@gmail.com'
];

echo "<h1>RTF USER STATUS DEBUG</h1>";
echo "<style>
    body { font-family: system-ui, -apple-system, sans-serif; padding: 20px; background: #f8fafc; }
    h1 { color: #0ea5e9; }
    h2 { color: #334155; margin-top: 30px; }
    .user-box { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .status { display: inline-block; padding: 6px 16px; border-radius: 20px; font-weight: 600; }
    .status.active { background: #10b981; color: white; }
    .status.inactive { background: #ef4444; color: white; }
    pre { background: #f1f5f9; padding: 15px; border-radius: 6px; overflow-x: auto; }
    .error { color: #ef4444; font-weight: bold; }
</style>";

foreach ($emails as $email) {
    echo "<div class='user-box'>";
    echo "<h2>🔍 Checking: " . htmlspecialchars($email) . "</h2>";
    
    $user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE email = %s",
        $email
    ));
    
    if ($user) {
        $status_class = $user->subscription_status === 'active' ? 'active' : 'inactive';
        
        echo "<pre>";
        echo "ID:                  " . $user->id . "\n";
        echo "Username:            " . $user->username . "\n";
        echo "Email:               " . $user->email . "\n";
        echo "Full Name:           " . $user->full_name . "\n";
        echo "Subscription Status: <span class='status $status_class'>" . strtoupper($user->subscription_status) . "</span>\n";
        echo "End Date:            " . ($user->subscription_end_date ?? 'NULL') . "\n";
        echo "Stripe Customer ID:  " . ($user->stripe_customer_id ?? 'NULL') . "\n";
        echo "Is Admin:            " . ($user->is_admin ? 'YES' : 'NO') . "\n";
        echo "Is Active:           " . ($user->is_active ? 'YES' : 'NO') . "\n";
        echo "Created:             " . $user->created_at . "\n";
        echo "</pre>";
        
        // Check if would pass subscription check
        if ($user->subscription_status === 'active') {
            echo "<p style='color: #10b981; font-weight: bold;'>✅ User SHOULD have platform access</p>";
        } else {
            echo "<p class='error'>❌ User BLOCKED from platform (subscription_status = '" . $user->subscription_status . "')</p>";
        }
        
    } else {
        echo "<p class='error'>User not found in database</p>";
    }
    
    echo "</div>";
}

// Check recent webhook logs in stripe_payments table
echo "<div class='user-box'>";
echo "<h2>📋 Recent Stripe Payments</h2>";

$payments = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}rtf_stripe_payments ORDER BY created_at DESC LIMIT 10"
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
    echo "<p>No payments found in database</p>";
}

echo "</div>";

// Database table info
echo "<div class='user-box'>";
echo "<h2>🗄️ Database Table Info</h2>";

$table_name = $wpdb->prefix . 'rtf_platform_users';
$columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");

echo "<pre>";
echo "Table: $table_name\n";
echo "Columns:\n";
foreach ($columns as $col) {
    echo "  - " . $col->Field . " (" . $col->Type . ")" . ($col->Null === 'NO' ? ' NOT NULL' : '') . "\n";
}
echo "</pre>";

echo "</div>";
