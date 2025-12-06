<?php
/**
 * KOMPLET ADMIN SYSTEM TEST
 * Test ALT: Login check, admin check, brugeroprettelse
 */

// Load WordPress
$wp_load_paths = [
    __DIR__ . '/../../../wp-load.php',
    __DIR__ . '/../../../../wp-load.php',
    __DIR__ . '/../../../../../wp-load.php'
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
    die('ERROR: Could not find wp-load.php');
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

global $wpdb, $rtf_user_system;
$table_users = $wpdb->prefix . 'rtf_platform_users';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin System Test</title>
    <style>
        body { font-family: Arial; max-width: 1200px; margin: 50px auto; padding: 20px; background: #0f172a; color: #e2e8f0; }
        .box { background: #1e293b; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #334155; }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .warning { color: #f59e0b; font-weight: bold; }
        h1 { color: #3b82f6; }
        h2 { color: #60a5fa; border-bottom: 2px solid #334155; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #334155; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #334155; }
        .btn { background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #2563eb; }
        .btn-danger { background: #ef4444; }
        .btn-success { background: #10b981; }
        input, select { padding: 8px; margin: 5px 0; width: 100%; max-width: 400px; background: #334155; border: 1px solid #475569; color: #e2e8f0; border-radius: 4px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #60a5fa; }
    </style>
</head>
<body>

<h1>🔧 KOMPLET ADMIN SYSTEM TEST</h1>

<?php
// TEST 1: Session Check
echo "<div class='box'>";
echo "<h2>1️⃣ Session Status</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session aktiv:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? '<span class="success">✓ JA</span>' : '<span class="error">✗ NEJ</span>') . "</p>";

if (isset($_SESSION['rtf_user_id'])) {
    echo "<p><strong>Logged in user ID:</strong> <span class='success'>" . $_SESSION['rtf_user_id'] . "</span></p>";
} else {
    echo "<p class='warning'>⚠️ Ingen bruger logget ind (rtf_user_id ikke sat i session)</p>";
}
echo "</div>";

// TEST 2: Current User Check
echo "<div class='box'>";
echo "<h2>2️⃣ Current User (rtf_get_current_user)</h2>";

$current_user = rtf_get_current_user();

if ($current_user) {
    echo "<table>";
    echo "<tr><td><strong>ID:</strong></td><td>" . $current_user->id . "</td></tr>";
    echo "<tr><td><strong>Username:</strong></td><td>" . $current_user->username . "</td></tr>";
    echo "<tr><td><strong>Email:</strong></td><td>" . $current_user->email . "</td></tr>";
    echo "<tr><td><strong>Full Name:</strong></td><td>" . $current_user->full_name . "</td></tr>";
    echo "<tr><td><strong>Is Admin:</strong></td><td>" . ($current_user->is_admin == 1 ? '<span class="success">✓ JA (is_admin = 1)</span>' : '<span class="error">✗ NEJ (is_admin = ' . $current_user->is_admin . ')</span>') . "</td></tr>";
    echo "<tr><td><strong>Subscription:</strong></td><td>" . $current_user->subscription_status . "</td></tr>";
    echo "</table>";
} else {
    echo "<p class='error'>❌ INGEN BRUGER LOGGET IND</p>";
    echo "<p>Du skal logge ind først på <a href='/platform-auth' style='color:#60a5fa'>/platform-auth</a></p>";
}
echo "</div>";

// TEST 3: Admin Check Function
echo "<div class='box'>";
echo "<h2>3️⃣ Admin Check (rtf_is_admin_user)</h2>";

$is_admin = rtf_is_admin_user();
if ($is_admin) {
    echo "<p class='success'>✅ DU ER ADMIN - Har fuld adgang til admin funktioner</p>";
} else {
    echo "<p class='error'>❌ DU ER IKKE ADMIN - Ingen adgang til admin funktioner</p>";
    
    if ($current_user) {
        echo "<p class='warning'>⚠️ Du er logget ind som: " . $current_user->username . " (email: " . $current_user->email . ")</p>";
        echo "<p>Men is_admin = " . $current_user->is_admin . " (skal være 1)</p>";
    }
}
echo "</div>";

// TEST 4: patrickfoersle@gmail.com Check
echo "<div class='box'>";
echo "<h2>4️⃣ patrickfoersle@gmail.com Status</h2>";

$patrick_user = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$table_users} WHERE email = %s",
    'patrickfoersle@gmail.com'
));

if ($patrick_user) {
    echo "<table>";
    echo "<tr><td><strong>ID:</strong></td><td>" . $patrick_user->id . "</td></tr>";
    echo "<tr><td><strong>Username:</strong></td><td>" . $patrick_user->username . "</td></tr>";
    echo "<tr><td><strong>Email:</strong></td><td>" . $patrick_user->email . "</td></tr>";
    echo "<tr><td><strong>Is Admin:</strong></td><td>" . ($patrick_user->is_admin == 1 ? '<span class="success">✓ JA</span>' : '<span class="error">✗ NEJ (is_admin = ' . $patrick_user->is_admin . ')</span>') . "</td></tr>";
    echo "<tr><td><strong>Subscription:</strong></td><td>" . $patrick_user->subscription_status . "</td></tr>";
    echo "<tr><td><strong>Created:</strong></td><td>" . $patrick_user->created_at . "</td></tr>";
    echo "</table>";
    
    if ($patrick_user->is_admin != 1) {
        echo "<form method='POST' style='margin-top:15px'>";
        echo "<button type='submit' name='make_patrick_admin' class='btn btn-success'>👑 GØR PATRICK TIL ADMIN</button>";
        echo "</form>";
    }
} else {
    echo "<p class='error'>❌ Bruger ikke fundet i databasen!</p>";
    echo "<p>Opret først brugeren via registrering på <a href='/platform-auth' style='color:#60a5fa'>/platform-auth</a></p>";
}
echo "</div>";

// Handle form submission
if (isset($_POST['make_patrick_admin']) && $patrick_user) {
    $wpdb->update(
        $table_users,
        ['is_admin' => 1],
        ['email' => 'patrickfoersle@gmail.com'],
        ['%d'],
        ['%s']
    );
    
    echo "<div class='box'><p class='success'>✅ patrickfoersle@gmail.com er nu ADMIN! Genindlæs siden.</p></div>";
    echo "<meta http-equiv='refresh' content='2'>";
}

// TEST 5: Test Create User API
if ($is_admin) {
    echo "<div class='box'>";
    echo "<h2>5️⃣ Test Brugeroprettelse</h2>";
    
    if (isset($_POST['test_create_user'])) {
        $test_data = [
            'username' => 'test_user_' . time(),
            'email' => 'test_' . time() . '@example.com',
            'password' => 'testpass123',
            'full_name' => 'Test Bruger',
            'birthday' => '1990-01-01',
            'phone' => '12345678',
            'language_preference' => 'da_DK',
            'is_admin' => 0
        ];
        
        echo "<p><strong>Opretter bruger:</strong></p>";
        echo "<pre style='background:#334155;padding:10px;border-radius:4px'>" . print_r($test_data, true) . "</pre>";
        
        $result = $rtf_user_system->register($test_data);
        
        if ($result['success']) {
            echo "<p class='success'>✅ BRUGER OPRETTET!</p>";
            echo "<p>User ID: " . $result['user_id'] . "</p>";
            echo "<p>Username: " . $result['username'] . "</p>";
            echo "<p>Email: " . $result['email'] . "</p>";
        } else {
            echo "<p class='error'>❌ FEJL: " . $result['error'] . "</p>";
        }
    } else {
        echo "<form method='POST'>";
        echo "<p>Test om brugeroprettelse virker (via PHP direkte, ikke REST API)</p>";
        echo "<button type='submit' name='test_create_user' class='btn'>🧪 OPRET TEST BRUGER</button>";
        echo "</form>";
    }
    echo "</div>";
}

// TEST 6: All Admin Users
echo "<div class='box'>";
echo "<h2>6️⃣ Alle Admin Brugere</h2>";

$all_admins = $wpdb->get_results("SELECT id, username, email, full_name, subscription_status FROM {$table_users} WHERE is_admin = 1");

if ($all_admins) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Navn</th><th>Subscription</th></tr>";
    foreach ($all_admins as $admin) {
        $highlight = ($current_user && $admin->id == $current_user->id) ? "style='background:#334155;font-weight:bold'" : "";
        echo "<tr $highlight>";
        echo "<td>" . $admin->id . "</td>";
        echo "<td>" . $admin->username . "</td>";
        echo "<td>" . $admin->email . "</td>";
        echo "<td>" . $admin->full_name . "</td>";
        echo "<td>" . $admin->subscription_status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>Total:</strong> " . count($all_admins) . " admin brugere</p>";
} else {
    echo "<p class='error'>❌ INGEN admin brugere fundet!</p>";
}
echo "</div>";

// TEST 7: REST API Test
if ($is_admin) {
    echo "<div class='box'>";
    echo "<h2>7️⃣ REST API Endpoint Test</h2>";
    echo "<p>REST API endpoint: <code>/wp-json/kate/v1/admin/user</code></p>";
    
    echo "<div style='background:#334155;padding:15px;border-radius:4px;margin:10px 0'>";
    echo "<p><strong>JavaScript test kode:</strong></p>";
    echo "<button onclick='testCreateUser()' class='btn'>🧪 TEST REST API</button>";
    echo "<div id='api-result' style='margin-top:15px'></div>";
    echo "</div>";
    
    echo "<script>
    async function testCreateUser() {
        const resultDiv = document.getElementById('api-result');
        resultDiv.innerHTML = '<p style=\"color:#f59e0b\">⏳ Tester...</p>';
        
        const testData = {
            username: 'api_test_' + Date.now(),
            email: 'api_test_' + Date.now() + '@example.com',
            password: 'testpass123',
            full_name: 'API Test Bruger',
            birthday: '1990-01-01',
            phone: '12345678',
            is_admin: 0
        };
        
        console.log('Sending to API:', testData);
        
        try {
            const response = await fetch('/wp-json/kate/v1/admin/user', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(testData)
            });
            
            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success) {
                resultDiv.innerHTML = '<p style=\"color:#10b981\">✅ SUCCESS!</p>' +
                    '<p>User ID: ' + data.user_id + '</p>' +
                    '<p>Username: ' + data.username + '</p>' +
                    '<p>Email: ' + data.email + '</p>';
            } else {
                resultDiv.innerHTML = '<p style=\"color:#ef4444\">❌ FEJL: ' + (data.error || data.message) + '</p>';
            }
        } catch (error) {
            console.error('Error:', error);
            resultDiv.innerHTML = '<p style=\"color:#ef4444\">❌ Exception: ' + error.message + '</p>';
        }
    }
    </script>";
    echo "</div>";
}
?>

<div class='box'>
    <h2>📋 KONKLUSION</h2>
    <p>Hvis alle tests er grønne (✅), så virker systemet perfekt!</p>
    <p><strong>Hvad skal fungere:</strong></p>
    <ul>
        <li>✅ Du skal være logget ind</li>
        <li>✅ patrickfoersle@gmail.com skal have is_admin = 1</li>
        <li>✅ rtf_is_admin_user() skal returnere true</li>
        <li>✅ Du kan oprette brugere via både PHP og REST API</li>
    </ul>
</div>

</body>
</html>
