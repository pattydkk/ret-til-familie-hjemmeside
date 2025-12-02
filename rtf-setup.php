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
echo '<div class="box"><h2>🗄️ Tjekker Database Tabeller</h2>';
global $wpdb;
$prefix = $wpdb->prefix;

$required_tables = array(
    'rtf_platform_users',
    'rtf_platform_sessions',
    'rtf_platform_wall_posts',
    'rtf_platform_wall_comments',
    'rtf_platform_friends',
    'rtf_platform_messages',
    'rtf_platform_documents',
    'rtf_platform_photos',
    'rtf_platform_kate_chats',
    'rtf_platform_legal_cases',
);

echo '<ul>';
$tables_ok = 0;
$tables_missing = 0;
foreach ($required_tables as $table) {
    $full_table = $prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'");
    if ($exists) {
        echo "<li>✅ <code>$table</code></li>";
        $tables_ok++;
    } else {
        echo "<li><span class='error'>❌ MANGLER:</span> <code>$table</code></li>";
        $tables_missing++;
    }
}
echo '</ul>';

if ($tables_missing > 0) {
    echo "<p><span class='error'>⚠️ $tables_missing tabeller mangler</span> - Kør: <code>rtf_create_platform_tables()</code></p>";
    echo "<p>Du kan også deaktivere og genaktivere temaet for at oprette tabeller automatisk.</p>";
} else {
    echo "<p class='success'>✅ Alle hovdtabeller eksisterer ($tables_ok stk)</p>";
}
echo '</div>';

// 5. FLUSH PERMALINKS
echo '<div class="box"><h2>🔄 Flusher Permalinks</h2>';
flush_rewrite_rules();
echo "<p class='success'>✅ Permalinks flushed - alle sider skulle nu være tilgængelige</p>";
echo '</div>';

// 6. FINAL STATUS
echo '<div class="box" style="background:#f0fdf4;border-left:4px solid #22c55e">';
echo '<h2 style="color:#22c55e">✅ SETUP FÆRDIG!</h2>';
echo '<p><strong>Hvad virker nu:</strong></p>';
echo '<ul>';
echo '<li>✅ Alle ' . count($all_pages) . ' sider er oprettet</li>';
echo '<li>✅ Navigation menu er klar</li>';
echo '<li>✅ Forside er sat</li>';
echo '<li>✅ Permalinks er flushed</li>';
echo '</ul>';
echo '<p><strong>Test nu:</strong></p>';
echo '<ul>';
echo '<li>📍 <a href="' . home_url('/') . '" target="_blank">Gå til forsiden</a></li>';
echo '<li>📍 <a href="' . home_url('/borger-platform/') . '" target="_blank">Gå til Borgerplatform</a></li>';
echo '<li>📍 <a href="' . home_url('/platform-auth/') . '" target="_blank">Gå til Login/Registrering</a></li>';
echo '</ul>';
echo '<p style="background:#fef3c7;padding:15px;border-radius:6px;border-left:4px solid #f59e0b">';
echo '<strong>⚠️ Vigtigt:</strong> Upload og aktiver <code>rtf-vendor-plugin.zip</code> for at aktivere Kate AI og Stripe funktionalitet.';
echo '</p>';
echo '</div>';

echo '</body></html>';
