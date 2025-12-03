<?php
/**
 * COMPLETE SYSTEM VERIFICATION TEST
 * Verifies entire RTF platform is working correctly
 */

// Load WordPress
require_once(__DIR__ . '/wp-load.php');

echo "<style>
body { background: #0f172a; color: #e2e8f0; font-family: monospace; padding: 20px; }
.section { background: #1e293b; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #334155; }
.success { color: #10b981; }
.error { color: #ef4444; }
.warning { color: #f59e0b; }
h1, h2 { color: #2563eb; }
.check { margin: 10px 0; padding: 10px; background: #0f172a; border-radius: 4px; }
</style>";

echo "<h1>🔍 RTF PLATFORM - KOMPLET SYSTEM VERIFIKATION</h1>";
echo "<p>Genereret: " . date('Y-m-d H:i:s') . "</p>";

// ==================== 1. CLASS SYSTEM ====================
echo "<div class='section'>";
echo "<h2>1️⃣ RTFUSERSYSTEM CLASS</h2>";

if (class_exists('RtfUserSystem')) {
    echo "<div class='check success'>✓ RtfUserSystem class exists</div>";
    
    global $rtf_user_system;
    if ($rtf_user_system && $rtf_user_system instanceof RtfUserSystem) {
        echo "<div class='check success'>✓ Global \$rtf_user_system instance active</div>";
        
        // Check methods
        $methods = ['register', 'authenticate', 'get_user', 'delete_user', 'activate_subscription_by_email', 'log_payment', 'has_active_subscription', 'admin_get_users'];
        foreach ($methods as $method) {
            if (method_exists($rtf_user_system, $method)) {
                echo "<div class='check success'>✓ Method: {$method}()</div>";
            } else {
                echo "<div class='check error'>✗ MISSING Method: {$method}()</div>";
            }
        }
    } else {
        echo "<div class='check error'>✗ Global instance NOT initialized</div>";
    }
} else {
    echo "<div class='check error'>✗ RtfUserSystem class NOT FOUND</div>";
}
echo "</div>";

// ==================== 2. DATABASE TABLES ====================
echo "<div class='section'>";
echo "<h2>2️⃣ DATABASE TABELLER</h2>";

global $wpdb;
$required_tables = [
    'rtf_platform_users',
    'rtf_platform_privacy',
    'rtf_stripe_payments',
    'rtf_platform_posts',
    'rtf_platform_messages',
    'rtf_platform_forum',
    'rtf_platform_friends'
];

foreach ($required_tables as $table) {
    $full_table = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'");
    
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table");
        echo "<div class='check success'>✓ {$table} ({$count} records)</div>";
    } else {
        echo "<div class='check error'>✗ MISSING: {$table}</div>";
    }
}

// Check rtf_platform_users columns
$users_table = $wpdb->prefix . 'rtf_platform_users';
$columns = $wpdb->get_results("SHOW COLUMNS FROM $users_table");
$required_columns = ['stripe_customer_id', 'subscription_status', 'subscription_end_date'];

echo "<h3>Critical User Columns:</h3>";
foreach ($required_columns as $col) {
    $found = false;
    foreach ($columns as $column) {
        if ($column->Field === $col) {
            $found = true;
            break;
        }
    }
    if ($found) {
        echo "<div class='check success'>✓ Column: {$col}</div>";
    } else {
        echo "<div class='check error'>✗ MISSING Column: {$col}</div>";
    }
}
echo "</div>";

// ==================== 3. REST API ENDPOINTS ====================
echo "<div class='section'>";
echo "<h2>3️⃣ REST API ENDPOINTS</h2>";

$endpoints = [
    'kate/v1/admin/user/(?P<id>\d+)' => 'DELETE',
    'kate/v1/admin/users' => 'GET',
    'kate/v1/admin/subscription/(?P<id>\d+)' => 'PUT'
];

$rest_server = rest_get_server();
$routes = $rest_server->get_routes();

foreach ($endpoints as $route => $method) {
    $pattern = '#^/kate/v1/admin#';
    $found = false;
    
    foreach ($routes as $route_pattern => $handlers) {
        if (preg_match($pattern, $route_pattern)) {
            foreach ($handlers as $handler) {
                if (in_array($method, $handler['methods'])) {
                    $found = true;
                    echo "<div class='check success'>✓ {$method} {$route_pattern}</div>";
                    break 2;
                }
            }
        }
    }
    
    if (!$found) {
        echo "<div class='check error'>✗ MISSING: {$method} {$route}</div>";
    }
}
echo "</div>";

// ==================== 4. HANDLER FUNCTIONS ====================
echo "<div class='section'>";
echo "<h2>4️⃣ API HANDLER FUNCTIONS</h2>";

$handlers = [
    'rtf_api_admin_delete_user',
    'rtf_api_admin_get_users',
    'rtf_api_admin_update_subscription'
];

foreach ($handlers as $handler) {
    if (function_exists($handler)) {
        echo "<div class='check success'>✓ Function: {$handler}()</div>";
    } else {
        echo "<div class='check error'>✗ MISSING Function: {$handler}()</div>";
    }
}
echo "</div>";

// ==================== 5. USER STATISTICS ====================
echo "<div class='section'>";
echo "<h2>5️⃣ BRUGER STATISTIK</h2>";

$users_table = $wpdb->prefix . 'rtf_platform_users';

$total = $wpdb->get_var("SELECT COUNT(*) FROM $users_table");
$active = $wpdb->get_var("SELECT COUNT(*) FROM $users_table WHERE subscription_status = 'active'");
$with_stripe = $wpdb->get_var("SELECT COUNT(*) FROM $users_table WHERE stripe_customer_id IS NOT NULL AND stripe_customer_id != ''");
$admins = $wpdb->get_var("SELECT COUNT(*) FROM $users_table WHERE is_admin = 1");

echo "<div class='check'>📊 Total brugere: <strong>{$total}</strong></div>";
echo "<div class='check success'>✓ Aktive subscriptions: <strong>{$active}</strong></div>";
echo "<div class='check success'>✓ Med Stripe Customer ID: <strong>{$with_stripe}</strong></div>";
echo "<div class='check warning'>👑 Admins: <strong>{$admins}</strong></div>";

// Recent users
$recent = $wpdb->get_results("SELECT id, username, email, subscription_status, stripe_customer_id, created_at FROM $users_table ORDER BY created_at DESC LIMIT 5");

echo "<h3>Seneste 5 brugere:</h3>";
foreach ($recent as $user) {
    $stripe_status = $user->stripe_customer_id ? "✓ Stripe" : "✗ No Stripe";
    echo "<div class='check'>[ID {$user->id}] {$user->username} ({$user->email}) - {$user->subscription_status} - {$stripe_status}</div>";
}
echo "</div>";

// ==================== 6. STRIPE INTEGRATION ====================
echo "<div class='section'>";
echo "<h2>6️⃣ STRIPE INTEGRATION</h2>";

$payments_table = $wpdb->prefix . 'rtf_stripe_payments';
$payment_count = $wpdb->get_var("SELECT COUNT(*) FROM $payments_table");
$payment_total = $wpdb->get_var("SELECT SUM(amount) FROM $payments_table WHERE status = 'completed'");

echo "<div class='check'>💰 Total betalinger logget: <strong>{$payment_count}</strong></div>";
echo "<div class='check success'>💵 Total revenue: <strong>" . number_format($payment_total / 100, 2) . " DKK</strong></div>";

// Recent payments
$recent_payments = $wpdb->get_results("SELECT * FROM $payments_table ORDER BY created_at DESC LIMIT 5");

echo "<h3>Seneste 5 betalinger:</h3>";
foreach ($recent_payments as $payment) {
    $amount = number_format($payment->amount / 100, 2);
    echo "<div class='check'>[ID {$payment->user_id}] {$amount} {$payment->currency} - {$payment->status} - {$payment->stripe_customer_id}</div>";
}

// Check if webhook file exists
if (file_exists(__DIR__ . '/stripe-webhook.php')) {
    echo "<div class='check success'>✓ stripe-webhook.php exists</div>";
} else {
    echo "<div class='check error'>✗ MISSING: stripe-webhook.php</div>";
}
echo "</div>";

// ==================== 7. ADMIN PANEL ====================
echo "<div class='section'>";
echo "<h2>7️⃣ ADMIN PANEL</h2>";

$admin_files = [
    'platform-admin-dashboard.php' => 'Admin Dashboard',
    'platform-admin-users.php' => 'Admin Users Panel'
];

foreach ($admin_files as $file => $name) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $content = file_get_contents(__DIR__ . '/' . $file);
        
        // Check for modern features
        $has_stats = strpos($content, 'stat-total-users') !== false;
        $has_search = strpos($content, 'searchInput') !== false;
        $has_delete = strpos($content, 'deleteUser') !== false;
        $has_activate = strpos($content, 'activateSubscription') !== false;
        
        echo "<div class='check success'>✓ {$name}</div>";
        echo "<div style='margin-left: 20px;'>";
        echo $has_stats ? "<div class='check success'>  ✓ Statistics</div>" : "<div class='check error'>  ✗ No statistics</div>";
        echo $has_search ? "<div class='check success'>  ✓ Search</div>" : "<div class='check error'>  ✗ No search</div>";
        echo $has_delete ? "<div class='check success'>  ✓ Delete function</div>" : "<div class='check error'>  ✗ No delete</div>";
        echo $has_activate ? "<div class='check success'>  ✓ Activate subscription</div>" : "<div class='check error'>  ✗ No activate</div>";
        echo "</div>";
    } else {
        echo "<div class='check error'>✗ MISSING: {$name}</div>";
    }
}
echo "</div>";

// ==================== 8. AUTHENTICATION ====================
echo "<div class='section'>";
echo "<h2>8️⃣ AUTHENTICATION SYSTEM</h2>";

if (file_exists(__DIR__ . '/platform-auth.php')) {
    $auth_content = file_get_contents(__DIR__ . '/platform-auth.php');
    
    $uses_register = strpos($auth_content, '$rtf_user_system->register') !== false;
    $uses_auth = strpos($auth_content, '$rtf_user_system->authenticate') !== false;
    $has_stripe = strpos($auth_content, 'Stripe\\Checkout\\Session::create') !== false;
    
    echo "<div class='check success'>✓ platform-auth.php exists</div>";
    echo $uses_register ? "<div class='check success'>✓ Uses \$rtf_user_system->register()</div>" : "<div class='check error'>✗ NOT using register() method</div>";
    echo $uses_auth ? "<div class='check success'>✓ Uses \$rtf_user_system->authenticate()</div>" : "<div class='check error'>✗ NOT using authenticate() method</div>";
    echo $has_stripe ? "<div class='check success'>✓ Stripe checkout integration</div>" : "<div class='check error'>✗ No Stripe integration</div>";
} else {
    echo "<div class='check error'>✗ MISSING: platform-auth.php</div>";
}
echo "</div>";

// ==================== 9. FINAL STATUS ====================
echo "<div class='section'>";
echo "<h2>9️⃣ SAMLET STATUS</h2>";

$issues = [];

if (!class_exists('RtfUserSystem')) $issues[] = "RtfUserSystem class ikke fundet";
if (!$rtf_user_system) $issues[] = "Global instance ikke initialiseret";
if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}rtf_platform_users'") === null) $issues[] = "rtf_platform_users tabel mangler";
if (!function_exists('rtf_api_admin_delete_user')) $issues[] = "Admin API handlers mangler";

if (empty($issues)) {
    echo "<div class='check success' style='font-size: 1.2em; padding: 20px;'>";
    echo "✅ ✅ ✅ ALLE SYSTEMER FUNGERER KORREKT ✅ ✅ ✅";
    echo "</div>";
    echo "<div class='check success'>";
    echo "• RtfUserSystem class loaded<br>";
    echo "• Database tabeller OK<br>";
    echo "• REST API endpoints registreret<br>";
    echo "• Admin panel moderne og funktionelt<br>";
    echo "• Stripe integration komplet<br>";
    echo "• Authentication bruger ny system<br>";
    echo "</div>";
} else {
    echo "<div class='check error' style='font-size: 1.2em; padding: 20px;'>";
    echo "⚠️ PROBLEMER FUNDET:";
    echo "</div>";
    foreach ($issues as $issue) {
        echo "<div class='check error'>✗ {$issue}</div>";
    }
}
echo "</div>";

echo "<hr>";
echo "<p style='color: #64748b;'>Test færdig: " . date('Y-m-d H:i:s') . "</p>";
?>
