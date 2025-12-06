<?php
/**
 * KOMPLET SYSTEM DIAGNOSE
 * Tester ALLE kritiske funktioner
 */

// Load WordPress
$wp_load_paths = [
    __DIR__ . '/../../../wp-load.php',
    __DIR__ . '/../../wp-load.php',
    __DIR__ . '/../wp-load.php',
    __DIR__ . '/wp-load.php'
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('❌ Could not load WordPress!');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>KOMPLET SYSTEM DIAGNOSE</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            max-width: 1400px; 
            margin: 20px auto; 
            padding: 20px; 
            background: #0f172a;
            color: #e2e8f0;
        }
        h1 { 
            color: #f1f5f9; 
            border-bottom: 3px solid #3b82f6; 
            padding-bottom: 10px; 
        }
        h2 { 
            color: #cbd5e1; 
            margin-top: 30px; 
            border-bottom: 2px solid #475569;
            padding-bottom: 5px; 
        }
        .test-section { 
            background: #1e293b; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.3); 
        }
        .success { 
            color: #10b981; 
            background: #064e3b; 
            padding: 10px; 
            border-left: 4px solid #10b981; 
            margin: 10px 0; 
        }
        .error { 
            color: #ef4444; 
            background: #7f1d1d; 
            padding: 10px; 
            border-left: 4px solid #ef4444; 
            margin: 10px 0; 
        }
        .warning { 
            color: #f59e0b; 
            background: #78350f; 
            padding: 10px; 
            border-left: 4px solid #f59e0b; 
            margin: 10px 0; 
        }
        .info { 
            color: #3b82f6; 
            background: #1e3a8a; 
            padding: 10px; 
            border-left: 4px solid #3b82f6; 
            margin: 10px 0; 
        }
        code { 
            background: #334155; 
            color: #f1f5f9; 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-size: 13px; 
        }
        pre { 
            background: #0f172a; 
            color: #f1f5f9; 
            padding: 15px; 
            border-radius: 5px; 
            overflow-x: auto; 
            border: 1px solid #334155;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 15px 0; 
        }
        th, td { 
            padding: 10px; 
            text-align: left; 
            border-bottom: 1px solid #334155; 
        }
        th { 
            background: #1e293b; 
            color: #f1f5f9; 
            font-weight: 600; 
        }
        tr:hover { 
            background: #334155; 
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #2563eb;
        }
        .btn-success { background: #10b981; }
        .btn-success:hover { background: #059669; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
    </style>
</head>
<body>

<h1>🔍 KOMPLET SYSTEM DIAGNOSE</h1>
<p style="color: #94a3b8;">Tester ALLE kritiske dele af platformen</p>

<?php
global $wpdb, $rtf_user_system;

$all_tests_passed = true;

// =============================================================================
// TEST 1: DATABASE STRUKTUR
// =============================================================================
echo '<div class="test-section">';
echo '<h2>1️⃣ Database Struktur</h2>';

$required_tables = [
    'rtf_platform_users' => 'Bruger data',
    'rtf_platform_privacy' => 'Privacy indstillinger',
    'rtf_stripe_payments' => 'Betalingshistorik',
    'rtf_platform_posts' => 'Vægindlæg',
    'rtf_platform_forum' => 'Forum indlæg',
    'rtf_platform_messages' => 'Beskeder'
];

foreach ($required_tables as $table_name => $description) {
    $full_table = $wpdb->prefix . $table_name;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'") === $full_table;
    
    if ($exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table");
        echo "<div class='success'>✅ <strong>$description:</strong> <code>$full_table</code> ($count records)</div>";
    } else {
        echo "<div class='error'>❌ <strong>$description:</strong> <code>$full_table</code> MANGLER!</div>";
        $all_tests_passed = false;
    }
}

echo '</div>';

// =============================================================================
// TEST 2: USER SYSTEM
// =============================================================================
echo '<div class="test-section">';
echo '<h2>2️⃣ User System</h2>';

if ($rtf_user_system) {
    echo "<div class='success'>✅ RtfUserSystem object findes</div>";
    
    // Check methods
    $required_methods = [
        'register',
        'authenticate',
        'delete_user',
        'activate_subscription_by_email',
        'admin_update_subscription',
        'admin_get_users'
    ];
    
    foreach ($required_methods as $method) {
        if (method_exists($rtf_user_system, $method)) {
            echo "<div class='success'>✅ Method <code>$method()</code> findes</div>";
        } else {
            echo "<div class='error'>❌ Method <code>$method()</code> MANGLER!</div>";
            $all_tests_passed = false;
        }
    }
} else {
    echo "<div class='error'>❌ \$rtf_user_system global object IKKE sat!</div>";
    $all_tests_passed = false;
}

echo '</div>';

// =============================================================================
// TEST 3: STRIPE CONFIGURATION
// =============================================================================
echo '<div class="test-section">';
echo '<h2>3️⃣ Stripe Configuration</h2>';

$stripe_constants = [
    'RTF_STRIPE_PUBLIC_KEY' => 'Public Key',
    'RTF_STRIPE_SECRET_KEY' => 'Secret Key',
    'RTF_STRIPE_PRICE_ID' => 'Price ID',
    'RTF_STRIPE_WEBHOOK_SECRET' => 'Webhook Secret'
];

foreach ($stripe_constants as $constant => $description) {
    if (defined($constant)) {
        $value = constant($constant);
        $preview = substr($value, 0, 15) . '...' . substr($value, -4);
        echo "<div class='success'>✅ <strong>$description:</strong> <code>$preview</code></div>";
    } else {
        echo "<div class='error'>❌ <strong>$description:</strong> <code>$constant</code> IKKE defineret!</div>";
        $all_tests_passed = false;
    }
}

// Test Stripe library
$stripe_init = get_template_directory() . '/stripe-php-13.18.0/init.php';
if (file_exists($stripe_init)) {
    echo "<div class='success'>✅ Stripe library findes: <code>$stripe_init</code></div>";
    
    require_once($stripe_init);
    
    try {
        \Stripe\Stripe::setApiKey(RTF_STRIPE_SECRET_KEY);
        echo "<div class='success'>✅ Stripe API key kan sættes</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Stripe API error: " . htmlspecialchars($e->getMessage()) . "</div>";
        $all_tests_passed = false;
    }
} else {
    echo "<div class='error'>❌ Stripe library IKKE fundet: <code>$stripe_init</code></div>";
    $all_tests_passed = false;
}

echo '</div>';

// =============================================================================
// TEST 4: REST API ENDPOINTS
// =============================================================================
echo '<div class="test-section">';
echo '<h2>4️⃣ REST API Endpoints</h2>';

$rest_endpoints = rest_get_server()->get_routes();
$kate_endpoints = array_filter(array_keys($rest_endpoints), function($route) {
    return strpos($route, '/kate/v1/') === 0;
});

if (count($kate_endpoints) > 0) {
    echo "<div class='success'>✅ Fandt " . count($kate_endpoints) . " Kate API endpoints</div>";
    
    $critical_endpoints = [
        '/kate/v1/admin/user/(?P<id>\d+)' => 'DELETE user',
        '/kate/v1/admin/user' => 'CREATE user',
        '/kate/v1/admin/users' => 'GET users list',
        '/kate/v1/admin/subscription/(?P<id>\d+)' => 'UPDATE subscription'
    ];
    
    foreach ($critical_endpoints as $pattern => $description) {
        $found = false;
        foreach ($kate_endpoints as $endpoint) {
            if (preg_match('#^' . $pattern . '$#', $endpoint)) {
                $found = true;
                break;
            }
        }
        
        if ($found) {
            echo "<div class='success'>✅ <strong>$description:</strong> <code>$pattern</code></div>";
        } else {
            echo "<div class='error'>❌ <strong>$description:</strong> <code>$pattern</code> MANGLER!</div>";
            $all_tests_passed = false;
        }
    }
} else {
    echo "<div class='error'>❌ Ingen Kate API endpoints fundet!</div>";
    $all_tests_passed = false;
}

echo '</div>';

// =============================================================================
// TEST 5: SESSION MANAGEMENT
// =============================================================================
echo '<div class="test-section">';
echo '<h2>5️⃣ Session Management</h2>';

if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<div class='success'>✅ PHP session er aktiv</div>";
    echo "<div class='info'>Session ID: <code>" . session_id() . "</code></div>";
} else {
    echo "<div class='warning'>⚠️ PHP session ikke startet - starter nu...</div>";
    if (@session_start()) {
        echo "<div class='success'>✅ Session startet successfully</div>";
    } else {
        echo "<div class='error'>❌ Kunne ikke starte session!</div>";
        $all_tests_passed = false;
    }
}

// Check current user
$current_user = rtf_get_current_user();
if ($current_user) {
    $admin_status = $current_user->is_admin ? 'JA' : 'NEJ';
    echo "<div class='success'>✅ Nuværende bruger: <code>{$current_user->username}</code> (Admin: $admin_status)</div>";
} else {
    echo "<div class='warning'>⚠️ Ingen bruger logget ind</div>";
}

echo '</div>';

// =============================================================================
// TEST 6: CRITICAL FILES
// =============================================================================
echo '<div class="test-section">';
echo '<h2>6️⃣ Kritiske Filer</h2>';

$critical_files = [
    'platform-auth.php' => 'Registrering/Login',
    'platform-admin-dashboard.php' => 'Admin panel',
    'stripe-webhook.php' => 'Stripe webhook',
    'includes/class-rtf-user-system.php' => 'User system class',
    'functions.php' => 'Theme functions'
];

foreach ($critical_files as $file => $description) {
    $full_path = get_template_directory() . '/' . $file;
    if (file_exists($full_path)) {
        $size = filesize($full_path);
        echo "<div class='success'>✅ <strong>$description:</strong> <code>$file</code> (" . number_format($size) . " bytes)</div>";
    } else {
        echo "<div class='error'>❌ <strong>$description:</strong> <code>$file</code> MANGLER!</div>";
        $all_tests_passed = false;
    }
}

echo '</div>';

// =============================================================================
// TEST 7: RECENT USERS
// =============================================================================
echo '<div class="test-section">';
echo '<h2>7️⃣ Seneste Brugere (Test Data)</h2>';

$users_table = $wpdb->prefix . 'rtf_platform_users';
$recent_users = $wpdb->get_results("
    SELECT id, username, email, subscription_status, stripe_customer_id, created_at 
    FROM $users_table 
    ORDER BY created_at DESC 
    LIMIT 10
");

if ($recent_users) {
    echo '<table>';
    echo '<tr><th>ID</th><th>Username</th><th>Email</th><th>Subscription</th><th>Stripe ID</th><th>Oprettet</th></tr>';
    foreach ($recent_users as $user) {
        $sub_class = $user->subscription_status === 'active' ? 'success' : 'warning';
        $stripe_status = $user->stripe_customer_id ? '✅' : '❌';
        echo '<tr>';
        echo '<td>' . $user->id . '</td>';
        echo '<td><strong>' . htmlspecialchars($user->username) . '</strong></td>';
        echo '<td>' . htmlspecialchars($user->email) . '</td>';
        echo '<td class="' . $sub_class . '">' . $user->subscription_status . '</td>';
        echo '<td>' . $stripe_status . ' ' . htmlspecialchars($user->stripe_customer_id ?? 'N/A') . '</td>';
        echo '<td>' . $user->created_at . '</td>';
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo "<div class='warning'>⚠️ Ingen brugere i databasen</div>";
}

echo '</div>';

// =============================================================================
// SUMMARY
// =============================================================================
echo '<div class="test-section">';
echo '<h2>📊 KONKLUSION</h2>';

if ($all_tests_passed) {
    echo '<div class="success">';
    echo '<h3 style="color: #10b981; margin-top: 0;">✅ ALLE TESTS BESTÅET!</h3>';
    echo '<p>Systemet er korrekt konfigureret og klar til brug.</p>';
    echo '<p><strong>Næste skridt:</strong></p>';
    echo '<ol>';
    echo '<li>Test bruger oprettelse i admin panel</li>';
    echo '<li>Test registrering via forsiden (/platform-auth/)</li>';
    echo '<li>Test Stripe betaling med test kort</li>';
    echo '<li>Verificer webhook aktiverer subscription</li>';
    echo '</ol>';
    echo '</div>';
} else {
    echo '<div class="error">';
    echo '<h3 style="color: #ef4444; margin-top: 0;">❌ FEJL FUNDET!</h3>';
    echo '<p>Se røde fejlbeskeder ovenfor for detaljer.</p>';
    echo '<p><strong>Almindelige løsninger:</strong></p>';
    echo '<ul>';
    echo '<li>Manglende tabeller: Kør <code>rtf-setup.php</code></li>';
    echo '<li>Stripe constants: Check <code>functions.php</code> linje 45-48</li>';
    echo '<li>REST API endpoints: Verificer <code>functions.php</code> rest_api_init</li>';
    echo '<li>User system: Check at <code>class-rtf-user-system.php</code> er loaded korrekt</li>';
    echo '</ul>';
    echo '</div>';
}

echo '</div>';

?>

<div class="test-section">
    <h2>🔗 Quick Links</h2>
    <a href="<?php echo home_url('/platform-auth/'); ?>" class="btn btn-success">Test Registrering</a>
    <a href="<?php echo home_url('/platform-admin-dashboard/'); ?>" class="btn">Admin Panel</a>
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn">⟳ Reload Test</a>
</div>

</body>
</html>
