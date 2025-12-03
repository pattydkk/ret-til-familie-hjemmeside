<?php
/**
 * EMERGENCY FIX: Activate Subscription Directly
 * 
 * INSTRUCTIONS:
 * 1. Edit database credentials below
 * 2. Upload to server
 * 3. Run via browser: yourdomain.dk/wp-content/themes/ret-til-familie/fix-subscription-now.php
 * 4. DELETE this file after use!
 */

// ==========================================
// EDIT THESE DATABASE CREDENTIALS:
// ==========================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');  // ← CHANGE THIS
define('DB_USER', 'your_database_user');  // ← CHANGE THIS
define('DB_PASS', 'your_database_password');  // ← CHANGE THIS
define('DB_PREFIX', 'wp_');  // Usually 'wp_'

// ==========================================
// EMAIL TO ACTIVATE:
// ==========================================
$email_to_activate = 'niller.jensen.89@gmail.com';

// ==========================================
// NO NEED TO EDIT BELOW THIS LINE
// ==========================================

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RTF Emergency Subscription Fix</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; padding: 20px; background: #f8fafc; max-width: 800px; margin: 0 auto; }
        h1 { color: #0ea5e9; }
        h2 { color: #334155; margin-top: 30px; }
        .box { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .success { background: #10b981; color: white; padding: 15px; border-radius: 8px; margin: 20px 0; font-weight: bold; }
        .error { background: #ef4444; color: white; padding: 15px; border-radius: 8px; margin: 20px 0; font-weight: bold; }
        .warning { background: #f59e0b; color: white; padding: 15px; border-radius: 8px; margin: 20px 0; font-weight: bold; }
        .info { background: #f0f9ff; border: 2px solid #0ea5e9; padding: 15px; border-radius: 8px; margin: 20px 0; }
        pre { background: #f1f5f9; padding: 15px; border-radius: 6px; overflow-x: auto; font-family: 'Courier New', monospace; }
        code { background: #e0f2fe; padding: 2px 6px; border-radius: 4px; font-family: 'Courier New', monospace; }
    </style>
</head>
<body>

<h1>🚨 RTF Emergency Subscription Fix</h1>

<?php

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo "<div class='error'>❌ DATABASE CONNECTION FAILED!<br>";
    echo "Error: " . htmlspecialchars($conn->connect_error) . "<br>";
    echo "<br>Please check your database credentials at the top of this file.</div>";
    exit;
}

echo "<div class='success'>✅ Database connected successfully!</div>";

// ==========================================
// STEP 1: Check current status
// ==========================================

echo "<div class='box'>";
echo "<h2>📊 Step 1: Current User Status</h2>";

$email_safe = $conn->real_escape_string($email_to_activate);
$table = DB_PREFIX . 'rtf_platform_users';

$result = $conn->query("SELECT id, username, email, subscription_status, subscription_end_date, stripe_customer_id, created_at FROM $table WHERE email = '$email_safe'");

if (!$result) {
    echo "<div class='error'>❌ Query failed: " . htmlspecialchars($conn->error) . "</div>";
    $conn->close();
    exit;
}

if ($result->num_rows === 0) {
    echo "<div class='error'>❌ User not found with email: " . htmlspecialchars($email_to_activate) . "</div>";
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();

echo "<pre>";
echo "User ID:             " . htmlspecialchars($user['id']) . "\n";
echo "Username:            " . htmlspecialchars($user['username']) . "\n";
echo "Email:               " . htmlspecialchars($user['email']) . "\n";
echo "Current Status:      <strong>" . strtoupper(htmlspecialchars($user['subscription_status'])) . "</strong>\n";
echo "End Date:            " . htmlspecialchars($user['subscription_end_date'] ?? 'NULL') . "\n";
echo "Stripe Customer ID:  " . htmlspecialchars($user['stripe_customer_id'] ?? 'NULL') . "\n";
echo "Account Created:     " . htmlspecialchars($user['created_at']) . "\n";
echo "</pre>";

echo "</div>";

// ==========================================
// STEP 2: Activate subscription
// ==========================================

echo "<div class='box'>";
echo "<h2>⚙️ Step 2: Activating Subscription</h2>";

$end_date = date('Y-m-d H:i:s', strtotime('+30 days'));
$customer_id = 'cus_manual_fix_' . time();

$sql = "UPDATE $table 
        SET subscription_status = 'active',
            subscription_end_date = '$end_date',
            stripe_customer_id = '$customer_id'
        WHERE email = '$email_safe'";

if ($conn->query($sql)) {
    echo "<div class='success'>";
    echo "✅ SUBSCRIPTION ACTIVATED SUCCESSFULLY!<br>";
    echo "Rows updated: " . $conn->affected_rows . "<br>";
    echo "New Status: ACTIVE<br>";
    echo "Valid Until: " . $end_date . " (30 days)<br>";
    echo "</div>";
    
    // ==========================================
    // STEP 3: Verify
    // ==========================================
    
    echo "<h2>✅ Step 3: Verification (Fresh Query)</h2>";
    
    $verify = $conn->query("SELECT subscription_status, subscription_end_date, stripe_customer_id FROM $table WHERE email = '$email_safe'");
    
    if ($verify && $vrow = $verify->fetch_assoc()) {
        echo "<pre>";
        echo "Subscription Status: <strong style='color:#10b981;'>" . strtoupper(htmlspecialchars($vrow['subscription_status'])) . "</strong>\n";
        echo "Valid Until:         " . htmlspecialchars($vrow['subscription_end_date']) . "\n";
        echo "Stripe Customer ID:  " . htmlspecialchars($vrow['stripe_customer_id']) . "\n";
        echo "</pre>";
        
        if ($vrow['subscription_status'] === 'active') {
            echo "<div class='success'>✅ CONFIRMED: User is now ACTIVE in database!</div>";
        } else {
            echo "<div class='error'>❌ ERROR: Status is still " . htmlspecialchars($vrow['subscription_status']) . "</div>";
        }
    }
    
} else {
    echo "<div class='error'>";
    echo "❌ UPDATE FAILED!<br>";
    echo "Error: " . htmlspecialchars($conn->error) . "<br>";
    echo "</div>";
}

echo "</div>";

// ==========================================
// STEP 4: Instructions
// ==========================================

echo "<div class='info'>";
echo "<h2>📝 Step 4: What to do next</h2>";
echo "<ol>";
echo "<li><strong>User must LOGOUT completely</strong> (close all browser tabs)</li>";
echo "<li><strong>User must LOGIN again</strong> to refresh session data</li>";
echo "<li><strong>User can now access the platform</strong></li>";
echo "</ol>";
echo "<p><strong>Alternative:</strong> User can add <code>?refresh=1</code> to the profile URL to force refresh session.</p>";
echo "</div>";

// ==========================================
// STEP 5: Security warning
// ==========================================

echo "<div class='warning'>";
echo "⚠️ <strong>IMPORTANT SECURITY WARNING!</strong><br>";
echo "DELETE THIS FILE IMMEDIATELY AFTER USE!<br>";
echo "This file contains database credentials and should not remain on the server.";
echo "</div>";

$conn->close();

?>

<div class='box'>
    <h2>🔍 SQL Query Used</h2>
    <p>For reference, here's the exact SQL query that was executed:</p>
    <pre><?php echo htmlspecialchars($sql); ?></pre>
</div>

</body>
</html>

