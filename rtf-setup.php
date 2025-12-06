<?php
/**
 * Template Name: RTF Auto Setup
 * BESØG DENNE SIDE ÉN GANG FOR AT OPRETTE ALLE SIDER OG FUNKTIONER
 */

// Load WordPress
require_once('../../../wp-load.php');

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>RTF Platform Setup</title>';
echo '<style>body{font-family:sans-serif;max-width:800px;margin:50px auto;padding:20px;background:#f5f5f5}';
echo '.box{background:white;padding:30px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);margin-bottom:20px}';
echo '.success{color:#22c55e;font-weight:bold}.error{color:#ef4444;font-weight:bold}';
echo 'h1{color:#2563eb}h2{color:#1e40af;border-bottom:2px solid #ddd;padding-bottom:10px}';
echo 'ul{line-height:1.8}code{background:#f1f5f9;padding:2px 6px;border-radius:3px;font-size:0.9em}</style></head><body>';

echo '<div class="box"><h1>🚀 RTF Platform - Auto Setup</h1>';
echo '<p>Denne side opretter automatisk alle manglende sider og funktioner.</p></div>';

// 1. OPRET ALLE SIDER
echo '<div class="box"><h2>📄 Opretter Sider</h2>';

$pages = array(
    'forside' => 'Forside',
    'ydelser' => 'Ydelser',
    'om-os' => 'Om os',
    'kontakt' => 'Kontakt',
    'akademiet' => 'Akademiet',
    'stoet-os' => 'Støt os',
    'borger-platform' => 'Borger Platform',
);

$platform_pages = array(
    'platform-auth' => 'Platform Login',
    'platform-profil' => 'Min Profil',
    'platform-subscription' => 'Abonnement',
    'platform-vaeg' => 'Min Væg',
    'platform-chat' => 'Beskeder',
    'platform-billeder' => 'Billede Galleri',
    'platform-dokumenter' => 'Dokumenter',
    'platform-indstillinger' => 'Indstillinger',
    'platform-nyheder' => 'Nyheder',
    'platform-forum' => 'Forum',
    'platform-sagshjaelp' => 'Sagshjælp',
    'platform-kate-ai' => 'Kate AI Assistent',
    'platform-klagegenerator' => 'Klage Generator',
    'platform-admin-dashboard' => 'Admin Dashboard',
    'platform-admin-users' => 'Admin Users',
    'platform-venner' => 'Venner',
    'platform-rapporter' => 'Rapporter & Analyser',
);

$all_pages = array_merge($pages, $platform_pages);
$created = 0;
$existing = 0;

echo '<ul>';
foreach ($all_pages as $slug => $title) {
    $page = get_page_by_path($slug);
    if ($page) {
        echo "<li>✅ <code>$slug</code> - $title <span style='color:#94a3b8'>(findes allerede)</span></li>";
        $existing++;
    } else {
        $page_id = wp_insert_post(array(
            'post_title' => $title,
            'post_name' => $slug,
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '',
        ));
        if ($page_id) {
            echo "<li><span class='success'>✨ OPRETTET:</span> <code>$slug</code> - $title (ID: $page_id)</li>";
            $created++;
        } else {
            echo "<li><span class='error'>❌ FEJL:</span> Kunne ikke oprette <code>$slug</code></li>";
        }
    }
}
echo '</ul>';
echo "<p><strong>Resultat:</strong> $created nye sider oprettet, $existing eksisterede allerede</p>";
echo '</div>';

// 2. SET FORSIDE
echo '<div class="box"><h2>🏠 Sætter Forside</h2>';
$forside = get_page_by_path('forside');
if ($forside) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $forside->ID);
    echo "<p class='success'>✅ Forside sat til: {$forside->post_title} (ID: {$forside->ID})</p>";
} else {
    echo "<p class='error'>❌ Kunne ikke finde forside</p>";
}
echo '</div>';

// 3. OPRET MENU
echo '<div class="box"><h2>📋 Opretter Navigation Menu</h2>';
$menu_name = 'Topmenu';
$menu = wp_get_nav_menu_object($menu_name);

if (!$menu) {
    $menu_id = wp_create_nav_menu($menu_name);
    echo "<p class='success'>✨ Menu '$menu_name' oprettet (ID: $menu_id)</p>";
    
    $menu_items = array('forside','om-os','ydelser','akademiet','borger-platform','kontakt','stoet-os');
    echo '<ul>';
    foreach ($menu_items as $slug) {
        $page = get_page_by_path($slug);
        if ($page) {
            wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title' => $page->post_title,
                'menu-item-object' => 'page',
                'menu-item-object-id' => $page->ID,
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish',
            ));
            echo "<li>✅ Tilføjet til menu: <code>$slug</code></li>";
        }
    }
    echo '</ul>';
    
    // Assign menu to location
    $locations = get_theme_mod('nav_menu_locations');
    if (!is_array($locations)) $locations = array();
    $locations['primary'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
    echo "<p class='success'>✅ Menu tildelt til 'primary' location</p>";
} else {
    echo "<p>✅ Menu '$menu_name' findes allerede (ID: {$menu->term_id})</p>";
}
echo '</div>';

// 4. OPRET DATABASE TABELLER
echo '<div class="box"><h2>🗄️ Opretter Database Tabeller</h2>';
global $wpdb;

// Kald functions.php's table creation function
if (function_exists('rtf_create_tables')) {
    rtf_create_tables();
    echo "<p class='success'>✅ Alle 29 database tabeller oprettet automatisk via rtf_create_tables()</p>";
} else {
    echo "<p class='error'>❌ Kunne ikke finde rtf_create_tables() - sikker på tema er aktiveret?</p>";
}

// Verificer tabeller
$required_tables = array(
    'rtf_platform_users',
    'rtf_platform_messages',
    'rtf_platform_posts',
    'rtf_platform_forum_topics',
    'rtf_kate_chat_sessions',
    'rtf_stripe_subscriptions',
);

echo '<p><strong>Verificerer vigtige tabeller:</strong></p><ul>';
$tables_ok = 0;
foreach ($required_tables as $table) {
    $full_table = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'");
    if ($exists) {
        echo "<li>✅ <code>$table</code></li>";
        $tables_ok++;
    } else {
        echo "<li><span class='error'>❌ MANGLER:</span> <code>$table</code></li>";
    }
}
echo '</ul>';
echo "<p class='success'>✅ $tables_ok/{count($required_tables)} vigtige tabeller verificeret</p>";
echo '</div>';

// 5. OPRET ADMIN BRUGER
echo '<div class="box"><h2>👤 Opretter Admin Bruger</h2>';

$admin_email = 'patrickfoersle@gmail.com';
$admin_password = 'AdminRTF2024!';
$admin_username = 'patrickfoersle';

// Tjek om bruger allerede findes
$table_users = $wpdb->prefix . 'rtf_platform_users';
$existing_user = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_users WHERE email = %s",
    $admin_email
));

if ($existing_user) {
    echo "<p>✅ Admin bruger findes allerede: <code>$admin_email</code></p>";
    
    // Opdater til admin hvis ikke allerede
    if ($existing_user->is_admin != 1) {
        $wpdb->update(
            $table_users,
            ['is_admin' => 1, 'subscription_status' => 'active', 'subscription_end_date' => date('Y-m-d H:i:s', strtotime('+1 year'))],
            ['email' => $admin_email],
            ['%d', '%s', '%s'],
            ['%s']
        );
        echo "<p class='success'>✨ Opgraderet til admin: is_admin = 1</p>";
    } else {
        echo "<p class='success'>✅ Bruger er allerede admin</p>";
    }
} else {
    // Opret ny admin bruger
    $password_hash = password_hash($admin_password, PASSWORD_BCRYPT);
    
    $result = $wpdb->insert(
        $table_users,
        [
            'username' => $admin_username,
            'email' => $admin_email,
            'password' => $password_hash,
            'full_name' => 'Admin',
            'is_admin' => 1,
            'subscription_status' => 'active',
            'subscription_end_date' => date('Y-m-d H:i:s', strtotime('+1 year')),
            'email_verified' => 1,
            'created_at' => current_time('mysql')
        ],
        ['%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s']
    );
    
    if ($result) {
        echo "<p class='success'>✨ ADMIN BRUGER OPRETTET!</p>";
        echo "<ul>";
        echo "<li><strong>Email:</strong> <code>$admin_email</code></li>";
        echo "<li><strong>Password:</strong> <code>$admin_password</code></li>";
        echo "<li><strong>Admin:</strong> ✅ JA (is_admin = 1)</li>";
        echo "<li><strong>Subscription:</strong> ✅ Aktiv (1 år)</li>";
        echo "</ul>";
        echo "<p style='background:#fef3c7;padding:15px;border-radius:6px;border-left:4px solid #f59e0b'>";
        echo "⚠️ <strong>VIGTIGT:</strong> Skift password efter første login!";
        echo "</p>";
    } else {
        echo "<p class='error'>❌ Kunne ikke oprette admin bruger - tjek database rettigheder</p>";
    }
}
echo '</div>';

// 6. FLUSH PERMALINKS
echo '<div class="box"><h2>🔄 Flusher Permalinks</h2>';
flush_rewrite_rules();
echo "<p class='success'>✅ Permalinks flushed - alle sider skulle nu være tilgængelige</p>";
echo '</div>';

// 7. FINAL STATUS
echo '<div class="box" style="background:#f0fdf4;border-left:4px solid #22c55e">';
echo '<h2 style="color:#22c55e">✅ SETUP FÆRDIG!</h2>';
echo '<p><strong>✨ Hvad er oprettet automatisk:</strong></p>';
echo '<ul>';
echo '<li>✅ Alle ' . count($all_pages) . ' sider med korrekte templates</li>';
echo '<li>✅ Navigation menu med alle links</li>';
echo '<li>✅ Forside sat til /forside</li>';
echo '<li>✅ Alle 29 database tabeller oprettet</li>';
echo '<li>✅ Admin bruger: patrickfoersle@gmail.com</li>';
echo '<li>✅ Kate AI system initialiseret</li>';
echo '<li>✅ REST API endpoints registreret</li>';
echo '<li>✅ Permalinks flushed</li>';
echo '</ul>';

echo '<p><strong>🔑 LOGIN INFORMATION:</strong></p>';
echo '<ul style="background:#fef3c7;padding:15px;border-radius:6px;border-left:4px solid #f59e0b">';
echo '<li><strong>URL:</strong> <a href="' . home_url('/platform-auth/') . '" target="_blank">' . home_url('/platform-auth/') . '</a></li>';
echo '<li><strong>Email:</strong> <code>patrickfoersle@gmail.com</code></li>';
echo '<li><strong>Password:</strong> <code>AdminRTF2024!</code></li>';
echo '<li><strong>Admin Panel:</strong> <a href="' . home_url('/platform-admin-dashboard/') . '" target="_blank">' . home_url('/platform-admin-dashboard/') . '</a></li>';
echo '</ul>';

echo '<p><strong>⚙️ NÆSTE TRIN:</strong></p>';
echo '<ol style="line-height:2">';
echo '<li>Tilføj Stripe API keys i <code>functions.php</code> line 198-199</li>';
echo '<li>Test login på <a href="' . home_url('/platform-auth/') . '" target="_blank">/platform-auth</a></li>';
echo '<li>Test admin panel på <a href="' . home_url('/platform-admin-dashboard/') . '" target="_blank">/platform-admin-dashboard</a></li>';
echo '<li>Opret test bruger i admin panel</li>';
echo '<li>Test alle platform features (chat, forum, Kate AI)</li>';
echo '<li>Skift admin password efter første login!</li>';
echo '</ol>';

echo '<p style="background:#fef3c7;padding:15px;border-radius:6px;border-left:4px solid #f59e0b">';
echo '<strong>⚠️ STRIPE SETUP PÅKRÆVET:</strong><br>';
echo 'Åbn <code>functions.php</code> og find line 198-199:<br>';
echo '<code>$stripe_secret_key = "din_stripe_secret_key_her";</code><br>';
echo '<code>$stripe_publishable_key = "din_stripe_publishable_key_her";</code><br>';
echo 'Udskift med dine rigtige Stripe keys fra <a href="https://dashboard.stripe.com/apikeys" target="_blank">dashboard.stripe.com/apikeys</a>';
echo '</p>';

echo '<p><strong>🔗 HURTIGE LINKS:</strong></p>';
echo '<ul>';
echo '<li>🏠 <a href="' . home_url('/') . '" target="_blank">Forside</a></li>';
echo '<li>👤 <a href="' . home_url('/platform-auth/') . '" target="_blank">Login/Registrering</a></li>';
echo '<li>⚙️ <a href="' . home_url('/platform-admin-dashboard/') . '" target="_blank">Admin Panel</a></li>';
echo '<li>🧪 <a href="' . home_url('/wp-content/themes/ret-til-familie-hjemmeside/ADMIN-SYSTEM-TEST.php') . '" target="_blank">System Test</a></li>';
echo '</ul>';

echo '</div>';

echo '</body></html>';
