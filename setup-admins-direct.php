<?php
/**
 * Create Admin Users Directly via Database
 * Run: php setup-admins-direct.php
 */

// Database config
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ret_til_familie';

// Connect
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_error) {
    die("❌ Database connection failed: " . $mysqli->connect_error . "\n");
}

$mysqli->set_charset("utf8mb4");
echo "✅ Connected to database\n\n";

$table = 'wp_rtf_platform_users';

// Admin users to create
$admins = [
    [
        'username' => 'kaya',
        'email' => 'kaya@rettilfamilie.dk',
        'password' => 'KayaAdmin2024!',
        'full_name' => 'Kaya'
    ],
    [
        'username' => 'nanna',
        'email' => 'nanna@rettilfamilie.dk',
        'password' => 'NannaAdmin2024!',
        'full_name' => 'Nanna'
    ],
    [
        'username' => 'charlotte',
        'email' => 'charlotte@rettilfamilie.dk',
        'password' => 'CharlotteAdmin2024!',
        'full_name' => 'Charlotte'
    ]
];

echo "🔧 Creating admin users...\n\n";

foreach ($admins as $admin) {
    echo "Processing: {$admin['full_name']} ({$admin['email']})\n";
    
    // Check if exists
    $stmt = $mysqli->prepare("SELECT id FROM $table WHERE email = ? OR username = ?");
    $stmt->bind_param('ss', $admin['email'], $admin['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $existing = $result->fetch_assoc();
        echo "  ⚠️ User already exists (ID: {$existing['id']})\n";
        
        // Update to admin
        $hashed = password_hash($admin['password'], PASSWORD_BCRYPT);
        $sub_end = date('Y-m-d H:i:s', strtotime('+10 years'));
        
        $stmt = $mysqli->prepare("UPDATE $table SET 
            is_admin = 1, 
            is_active = 1,
            password_hash = ?,
            subscription_status = 'active',
            subscription_end_date = ?,
            email_verified = 1,
            updated_at = NOW()
            WHERE id = ?");
        $stmt->bind_param('ssi', $hashed, $sub_end, $existing['id']);
        $stmt->execute();
        
        echo "  ✅ Updated to admin status\n";
        echo "  📧 Email: {$admin['email']}\n";
        echo "  🔑 Password: {$admin['password']}\n\n";
    } else {
        // Create new
        $hashed = password_hash($admin['password'], PASSWORD_BCRYPT);
        $sub_start = date('Y-m-d H:i:s');
        $sub_end = date('Y-m-d H:i:s', strtotime('+10 years'));
        
        $stmt = $mysqli->prepare("INSERT INTO $table (
            username, email, password_hash, full_name, phone,
            is_admin, is_active, subscription_status, subscription_start_date, subscription_end_date,
            email_verified, created_at, updated_at
        ) VALUES (?, ?, ?, ?, '', 1, 1, 'active', ?, ?, 1, NOW(), NOW())");
        
        $stmt->bind_param('ssssss', 
            $admin['username'],
            $admin['email'],
            $hashed,
            $admin['full_name'],
            $sub_start,
            $sub_end
        );
        
        if ($stmt->execute()) {
            $new_id = $mysqli->insert_id;
            echo "  ✅ Created with ID: {$new_id}\n";
            echo "  📧 Email: {$admin['email']}\n";
            echo "  🔑 Password: {$admin['password']}\n\n";
        } else {
            echo "  ❌ Error: " . $mysqli->error . "\n\n";
        }
    }
}

// Show all admins
echo "\n📋 ALL ADMIN USERS:\n";
echo str_repeat("=", 80) . "\n";

$result = $mysqli->query("SELECT id, username, email, full_name, is_admin, is_active FROM $table WHERE is_admin = 1 ORDER BY id ASC");

if ($result && $result->num_rows > 0) {
    printf("%-5s %-15s %-30s %-20s %-8s %-8s\n", "ID", "Username", "Email", "Name", "Admin", "Active");
    echo str_repeat("-", 80) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        $is_patrick = ($row['email'] === 'patrickforslev@gmail.com') ? " 👑" : "";
        printf("%-5s %-15s %-30s %-20s %-8s %-8s%s\n",
            $row['id'],
            $row['username'],
            $row['email'],
            $row['full_name'],
            $row['is_admin'] ? 'YES' : 'NO',
            $row['is_active'] ? 'YES' : 'NO',
            $is_patrick
        );
    }
} else {
    echo "❌ No admins found!\n";
}

echo "\n✅ DONE!\n\n";
echo "🔐 LOGIN CREDENTIALS:\n";
echo "---------------------\n";
echo "1. Patrick (OWNER):\n";
echo "   Email: patrickforslev@gmail.com\n";
echo "   Password: [Existing password]\n\n";

foreach ($admins as $admin) {
    echo "2. {$admin['full_name']}:\n";
    echo "   Email: {$admin['email']}\n";
    echo "   Password: {$admin['password']}\n\n";
}

$mysqli->close();
