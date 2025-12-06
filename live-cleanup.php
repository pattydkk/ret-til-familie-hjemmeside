<?php
/**
 * CLEANUP SCRIPT - Ryd database og forbered til live
 * VIGTIGT: Kun Patrickfoerslev@gmail.com skal eksistere
 */

// Find WordPress
$wp_load_paths = [
    __DIR__ . '/../../../wp-load.php',
    __DIR__ . '/../../wp-load.php',
    __DIR__ . '/../wp-load.php',
];

foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        break;
    }
}

if (!function_exists('wp')) {
    die("ERROR: WordPress ikke fundet!\n");
}

global $wpdb;

echo "===========================================\n";
echo "LIVE DATABASE CLEANUP\n";
echo "===========================================\n\n";

// Step 1: Find Patrick's profil
$patrick = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE email = 'Patrickfoerslev@gmail.com'");

if (!$patrick) {
    echo "ERROR: Patrickfoerslev@gmail.com profil ikke fundet!\n";
    echo "Opretter profil nu...\n\n";
    
    // Opret Patrick's profil
    $wpdb->insert(
        $wpdb->prefix . 'rtf_platform_users',
        [
            'username' => 'patrick',
            'email' => 'Patrickfoerslev@gmail.com',
            'password_hash' => password_hash('PatrickAdmin2024!', PASSWORD_DEFAULT),
            'full_name' => 'Patrick Hansen',
            'birthday' => '##-##-1989',
            'phone' => '+45 12 34 56 78',
            'is_admin' => 1,
            'subscription_status' => 'active',
            'subscription_end_date' => date('Y-m-d H:i:s', strtotime('+1 year')),
            'language_preference' => 'da_DK',
            'created_at' => current_time('mysql'),
            'last_login' => current_time('mysql')
        ],
        ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s']
    );
    
    $patrick_id = $wpdb->insert_id;
    echo "✅ Patrick's profil oprettet (ID: $patrick_id)\n";
    
    // Reload Patrick
    $patrick = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE id = $patrick_id");
} else {
    echo "✅ Patrick's profil fundet (ID: {$patrick->id})\n";
    echo "   Email: {$patrick->email}\n";
    echo "   Username: {$patrick->username}\n";
    echo "   Is Admin: " . ($patrick->is_admin ? 'JA' : 'NEJ') . "\n";
    echo "   Subscription: {$patrick->subscription_status}\n\n";
}

// Step 2: Tjek alle andre brugere
$all_users = $wpdb->get_results("SELECT id, username, email FROM {$wpdb->prefix}rtf_platform_users WHERE id != {$patrick->id}");

if (empty($all_users)) {
    echo "✅ Ingen andre brugere fundet - databasen er ren!\n\n";
} else {
    echo "FUNDET " . count($all_users) . " ANDRE BRUGERE:\n";
    foreach ($all_users as $user) {
        echo "  - ID {$user->id}: {$user->username} ({$user->email})\n";
    }
    
    echo "\nSLETTER alle andre brugere...\n";
    
    // Slet alle posts, comments, images, documents fra andre brugere
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}rtf_platform_posts WHERE user_id != %d",
        $patrick->id
    ));
    
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}rtf_platform_comments WHERE user_id != %d",
        $patrick->id
    ));
    
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}rtf_platform_images WHERE user_id != %d",
        $patrick->id
    ));
    
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}rtf_platform_documents WHERE user_id != %d",
        $patrick->id
    ));
    
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}rtf_platform_forum_posts WHERE author_id != %d",
        $patrick->id
    ));
    
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}rtf_platform_kate_chat WHERE user_id != %d",
        $patrick->id
    ));
    
    // Slet alle andre brugere
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->prefix}rtf_platform_users WHERE id != %d",
        $patrick->id
    ));
    
    echo "✅ Alle andre brugere og deres data slettet!\n\n";
}

// Step 3: Opdater Patrick's profil til live-ready
echo "OPDATERER Patrick's profil...\n";

$wpdb->update(
    $wpdb->prefix . 'rtf_platform_users',
    [
        'is_admin' => 1,
        'subscription_status' => 'active',
        'subscription_end_date' => date('Y-m-d H:i:s', strtotime('+1 year'))
    ],
    ['id' => $patrick->id],
    ['%d', '%s', '%s'],
    ['%d']
);

echo "✅ Patrick er sat som admin med aktivt abonnement\n\n";

// Step 4: Final verification
echo "===========================================\n";
echo "FINAL VERIFICATION\n";
echo "===========================================\n\n";

$final_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_users");
echo "Total brugere: $final_count\n";

if ($final_count == 1) {
    echo "✅ SUCCESS - Kun 1 bruger i databasen (Patrick)\n";
} else {
    echo "⚠️ WARNING - Der er $final_count brugere (burde være 1)\n";
}

echo "\n===========================================\n";
echo "DATABASE CLEANUP COMPLETE!\n";
echo "===========================================\n";
