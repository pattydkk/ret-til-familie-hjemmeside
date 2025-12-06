<?php
/**
 * KOMPLET SYSTEM DIAGNOSE V2
 * Dette script tester alle komponenter i hele platformen
 */

// Load WordPress
require_once(__DIR__ . '/wp-load.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

global $wpdb, $rtf_user_system;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>System Diagnose V2 - Ret til Familie</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #0c4a6e; border-bottom: 3px solid #0c4a6e; padding-bottom: 10px; }
        h2 { color: #075985; margin-top: 30px; border-bottom: 2px solid #bae6fd; padding-bottom: 5px; }
        .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #15803d; background: #dcfce7; padding: 10px; border-left: 4px solid #15803d; margin: 10px 0; }
        .error { color: #dc2626; background: #fee2e2; padding: 10px; border-left: 4px solid #dc2626; margin: 10px 0; }
        .warning { color: #d97706; background: #fef3c7; padding: 10px; border-left: 4px solid #d97706; margin: 10px 0; }
        .info { color: #0c4a6e; background: #e0f2fe; padding: 10px; border-left: 4px solid #0c4a6e; margin: 10px 0; }
        code { background: #1e293b; color: #f1f5f9; padding: 2px 6px; border-radius: 3px; font-size: 13px; }
        pre { background: #1e293b; color: #f1f5f9; padding: 15px; border-radius: 5px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #0c4a6e; color: white; font-weight: 600; }
        tr:hover { background: #f8fafc; }
        .status-ok { color: #15803d; font-weight: bold; }
        .status-fail { color: #dc2626; font-weight: bold; }
        .btn { display: inline-block; padding: 10px 20px; background: #0c4a6e; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #075985; }
    </style>
</head>
<body>

<h1>🔍 System Diagnose V2 - Ret til Familie Platform</h1>

<div class="info">
    <strong>🎯 Hurtig Navigation:</strong><br>
    <a href="#database" class="btn">Database</a>
    <a href="#usersystem" class="btn">User System</a>
    <a href="#stripe" class="btn">Stripe</a>
    <a href="#restapi" class="btn">REST API</a>
    <a href="#session" class="btn">Sessions</a>
    <a href="#users" class="btn">Brugere</a><br>
    <a href="/platform-auth/" class="btn" style="background: #15803d;">✨ Test Registrering</a>
    <a href="/platform-admin-dashboard/" class="btn" style="background: #d97706;">👑 Admin Panel</a>
</div>

<?php
// =============================================================================
// TEST 1: Database Structure
// =============================================================================
echo '<div class="test-section" id="database">';
echo '<h2>1️⃣ Database Struktur</h2>';

$required_tables = [
    'rtf_platform_users',
    'rtf_stripe_payments',
    'rtf_platform_posts',
    'rtf_platform_forum',
    'rtf_platform_comments',
    'rtf_platform_messages'
];

$all_tables_ok = true;
foreach ($required_tables as $table) {
    $table_name = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo '<div class="success">✅ Tabel <code>' . $table . '</code> eksisterer (' . $count . ' rækker)</div>';
    } else {
        echo '<div class="error">❌ Tabel <code>' . $table . '</code> MANGLER!</div>';
        $all_tables_ok = false;
    }
}

if ($all_tables_ok) {
    echo '<div class="success"><strong>✅ Alle database tabeller OK</strong></div>';
}

echo '</div>';

// =============================================================================
// TEST 2: User System
// =============================================================================
echo '<div class="test-section" id="usersystem">';
echo '<h2>2️⃣ User System Funktioner</h2>';

if (isset($rtf_user_system) && is_object($rtf_user_system)) {
    echo '<div class="success">✅ User system instans fundet ($rtf_user_system)</div>';
    
    $required_methods = [
        'register' => 'Registrer ny bruger',
        'authenticate' => 'Login bruger',
        'delete_user' => 'Slet bruger',
        'admin_update_subscription' => 'Opdater subscription (admin)',
        'activate_subscription_by_email' => 'Aktiver subscription (webhook)',
        'get_user' => 'Hent bruger',
        'get_user_by_email' => 'Hent bruger via email',
        'log_payment' => 'Log betaling'
    ];
    
    $all_methods_ok = true;
    foreach ($required_methods as $method => $description) {
        if (method_exists($rtf_user_system, $method)) {
            echo '<div class="success">✅ Metode <code>' . $method . '()</code> - ' . $description . '</div>';
        } else {
            echo '<div class="error">❌ Metode <code>' . $method . '()</code> MANGLER!</div>';
            $all_methods_ok = false;
        }
    }
    
    if ($all_methods_ok) {
        echo '<div class="success"><strong>✅ Alle user system metoder OK</strong></div>';
    }
} else {
    echo '<div class="error">❌ User system IKKE initialiseret! $rtf_user_system mangler.</div>';
}

echo '</div>';

// =============================================================================
// TEST 3: Stripe Configuration
// =============================================================================
echo '<div class="test-section" id="stripe">';
echo '<h2>3️⃣ Stripe Konfiguration</h2>';

$stripe_constants = [
    'RTF_STRIPE_SECRET_KEY' => 'Secret Key',
    'RTF_STRIPE_PUBLISHABLE_KEY' => 'Publishable Key',
    'RTF_STRIPE_WEBHOOK_SECRET' => 'Webhook Secret',
    'RTF_STRIPE_PRICE_ID' => 'Price ID (49 DKK/month)'
];

$all_stripe_ok = true;
foreach ($stripe_constants as $const => $description) {
    if (defined($const) && !empty(constant($const))) {
        $value = constant($const);
        $masked = substr($value, 0, 12) . '***' . substr($value, -4);
        echo '<div class="success">✅ <code>' . $const . '</code><br>';
        echo '&nbsp;&nbsp;&nbsp;' . $description . ': <code>' . $masked . '</code></div>';
    } else {
        echo '<div class="error">❌ <code>' . $const . '</code> IKKE defineret!</div>';
        $all_stripe_ok = false;
    }
}

// Check Stripe library
$stripe_lib = get_template_directory() . '/stripe-php-13.18.0/init.php';
if (file_exists($stripe_lib)) {
    echo '<div class="success">✅ Stripe Library fundet: <code>stripe-php-13.18.0/init.php</code></div>';
} else {
    echo '<div class="error">❌ Stripe Library MANGLER!</div>';
    $all_stripe_ok = false;
}

if ($all_stripe_ok) {
    echo '<div class="success"><strong>✅ Stripe konfiguration komplet</strong></div>';
}

echo '</div>';

// =============================================================================
// TEST 4: REST API Endpoints
// =============================================================================
echo '<div class="test-section" id="restapi">';
echo '<h2>4️⃣ REST API Endpoints</h2>';

$rest_server = rest_get_server();
$routes = $rest_server->get_routes();

$required_endpoints = [
    '/kate/v1/admin/users' => 'GET - Hent alle brugere (admin)',
    '/kate/v1/admin/user' => 'POST - Opret bruger (admin)',
    '/kate/v1/admin/user/(?P<id>\d+)' => 'DELETE - Slet bruger (admin)',
    '/kate/v1/admin/subscription/(?P<id>\d+)' => 'PUT - Opdater subscription (admin)',
    '/kate/v1/admin/post/(?P<id>\d+)' => 'DELETE - Slet indlæg (admin)'
];

$all_endpoints_ok = true;
foreach ($required_endpoints as $endpoint => $description) {
    $found = false;
    foreach ($routes as $route => $handlers) {
        if (preg_match('#^' . $endpoint . '$#', $route)) {
            $found = true;
            break;
        }
    }
    
    if ($found) {
        echo '<div class="success">✅ <code>' . $endpoint . '</code><br>';
        echo '&nbsp;&nbsp;&nbsp;' . $description . '</div>';
    } else {
        echo '<div class="error">❌ <code>' . $endpoint . '</code> MANGLER!<br>';
        echo '&nbsp;&nbsp;&nbsp;' . $description . '</div>';
        $all_endpoints_ok = false;
    }
}

if ($all_endpoints_ok) {
    echo '<div class="success"><strong>✅ Alle REST API endpoints registreret</strong></div>';
}

echo '</div>';

// =============================================================================
// TEST 5: Session Management
// =============================================================================
echo '<div class="test-section" id="session">';
echo '<h2>5️⃣ Session Management</h2>';

if (session_status() === PHP_SESSION_ACTIVE) {
    echo '<div class="success">✅ PHP Session aktiv</div>';
    echo '<div class="info">Session ID: <code>' . session_id() . '</code></div>';
    
    if (isset($_SESSION['rtf_user_id'])) {
        echo '<div class="success">✅ Bruger logget ind: User ID ' . $_SESSION['rtf_user_id'] . '</div>';
    } else {
        echo '<div class="warning">⚠️ Ingen bruger logget ind i nuværende session</div>';
    }
} else {
    echo '<div class="warning">⚠️ Session ikke startet (normal hvis ikke logget ind)</div>';
}

echo '</div>';

// =============================================================================
// TEST 6: Recent Users
// =============================================================================
echo '<div class="test-section" id="users">';
echo '<h2>6️⃣ Nyeste Brugere</h2>';

$recent_users = $wpdb->get_results("
    SELECT id, username, email, subscription_status, subscription_expiry, 
           stripe_customer_id, created_at 
    FROM {$wpdb->prefix}rtf_platform_users 
    ORDER BY created_at DESC 
    LIMIT 10
");

if ($recent_users) {
    echo '<table>';
    echo '<tr><th>ID</th><th>Brugernavn</th><th>Email</th><th>Status</th><th>Udløber</th><th>Stripe ID</th><th>Oprettet</th></tr>';
    
    foreach ($recent_users as $user) {
        $status_class = ($user->subscription_status === 'active') ? 'status-ok' : 'status-fail';
        $stripe_id = $user->stripe_customer_id ? substr($user->stripe_customer_id, 0, 20) . '...' : 'Ingen';
        
        echo '<tr>';
        echo '<td>' . $user->id . '</td>';
        echo '<td><strong>' . esc_html($user->username) . '</strong></td>';
        echo '<td>' . esc_html($user->email) . '</td>';
        echo '<td class="' . $status_class . '">' . $user->subscription_status . '</td>';
        echo '<td>' . ($user->subscription_expiry ?? 'N/A') . '</td>';
        echo '<td><code>' . $stripe_id . '</code></td>';
        echo '<td>' . $user->created_at . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    echo '<div class="success">✅ Fandt ' . count($recent_users) . ' brugere</div>';
} else {
    echo '<div class="warning">⚠️ Ingen brugere i databasen endnu</div>';
}

echo '</div>';
?>

<div class="test-section">
    <h2>🎯 Næste Skridt</h2>
    <div class="info">
        <strong>✅ Hvis alle tests er grønne:</strong><br>
        1. <a href="/platform-auth/" class="btn">Test frontend registrering →</a><br>
        2. <a href="/platform-admin-dashboard/" class="btn">Test admin panel →</a>
    </div>
    
    <div class="warning">
        <strong>❌ Hvis noget er rødt:</strong><br>
        Tag screenshot og send til udvikler for fejlretning.
    </div>
</div>

</body>
</html>
