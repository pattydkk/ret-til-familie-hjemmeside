<?php
// Find WordPress root
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
    die("WordPress ikke fundet!\n");
}

global $wpdb;

echo "=== ALLE BRUGERE ===\n\n";
$users = $wpdb->get_results("SELECT id, username, email, subscription_status, is_admin FROM {$wpdb->prefix}rtf_platform_users ORDER BY id");

if (empty($users)) {
    echo "Ingen brugere fundet!\n";
} else {
    foreach ($users as $user) {
        echo "ID: " . $user->id . "\n";
        echo "Username: " . $user->username . "\n";
        echo "Email: " . $user->email . "\n";
        echo "Subscription: " . $user->subscription_status . "\n";
        echo "Is Admin: " . ($user->is_admin ? 'Ja' : 'Nej') . "\n";
        echo "-------------------\n";
    }
}

echo "\n\nTOTAL: " . count($users) . " brugere\n";
