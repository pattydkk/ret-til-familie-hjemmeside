<?php
/**
 * Template Name: System Test Page
 * TEST URL: /test-system/
 */

// Load WordPress
require_once('../../../wp-load.php');

?>
<!DOCTYPE html>
<html>
<head>
    <title>RTF Platform - System Test</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #2563eb; }
        .status-ok { color: green; font-weight: bold; }
        .status-error { color: red; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2563eb; color: white; }
        .section { margin: 30px 0; padding: 20px; background: #f9fafb; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 RTF Platform - System Status</h1>
        
        <div class="section">
            <h2>📄 WordPress Sider Status</h2>
            <table>
                <tr>
                    <th>Side Slug</th>
                    <th>Titel</th>
                    <th>Status</th>
                    <th>URL</th>
                </tr>
                <?php
                $required_pages = array(
                    'forside' => 'Forside',
                    'ydelser' => 'Ydelser',
                    'om-os' => 'Om os',
                    'kontakt' => 'Kontakt',
                    'akademiet' => 'Akademiet',
                    'stoet-os' => 'Støt os',
                    'borger-platform' => 'Borger Platform',
                    'platform-auth' => 'Platform Login',
                    'platform-profil' => 'Min Profil',
                    'platform-vaeg' => 'Min Væg',
                    'platform-chat' => 'Beskeder',
                    'platform-billeder' => 'Billede Galleri',
                    'platform-dokumenter' => 'Dokumenter',
                    'platform-kate-ai' => 'Kate AI',
                    'platform-forum' => 'Forum',
                    'platform-nyheder' => 'Nyheder',
                );
                
                foreach ($required_pages as $slug => $title) {
                    $page = get_page_by_path($slug);
                    if ($page) {
                        echo '<tr>';
                        echo '<td>' . esc_html($slug) . '</td>';
                        echo '<td>' . esc_html($page->post_title) . '</td>';
                        echo '<td class="status-ok">✅ Eksisterer</td>';
                        echo '<td><a href="' . get_permalink($page->ID) . '" target="_blank">Besøg side</a></td>';
                        echo '</tr>';
                    } else {
                        echo '<tr>';
                        echo '<td>' . esc_html($slug) . '</td>';
                        echo '<td>' . esc_html($title) . '</td>';
                        echo '<td class="status-error">❌ MANGLER</td>';
                        echo '<td>-</td>';
                        echo '</tr>';
                    }
                }
                ?>
            </table>
        </div>

        <div class="section">
            <h2>📁 Template Filer Status</h2>
            <table>
                <tr>
                    <th>Template Fil</th>
                    <th>Status</th>
                </tr>
                <?php
                $template_dir = get_template_directory();
                $required_templates = array(
                    'borger-platform.php',
                    'platform-auth.php',
                    'platform-profil.php',
                    'platform-vaeg.php',
                    'platform-chat.php',
                    'platform-billeder.php',
                    'platform-dokumenter.php',
                    'platform-kate-ai.php',
                    'platform-forum.php',
                    'platform-nyheder.php',
                    'platform-venner.php',
                    'platform-rapporter.php',
                    'platform-admin-dashboard.php',
                );
                
                foreach ($required_templates as $template) {
                    $exists = file_exists($template_dir . '/' . $template);
                    echo '<tr>';
                    echo '<td>' . esc_html($template) . '</td>';
                    if ($exists) {
                        echo '<td class="status-ok">✅ Eksisterer</td>';
                    } else {
                        echo '<td class="status-error">❌ MANGLER</td>';
                    }
                    echo '</tr>';
                }
                ?>
            </table>
        </div>

        <div class="section">
            <h2>🔧 Core Funktioner</h2>
            <table>
                <tr>
                    <th>Funktion</th>
                    <th>Status</th>
                </tr>
                <?php
                $functions = array(
                    'rtf_get_lang',
                    'rtf_is_logged_in',
                    'rtf_get_current_user',
                    'rtf_is_admin_user',
                    'rtf_create_pages_menu_on_switch',
                );
                
                foreach ($functions as $func) {
                    echo '<tr>';
                    echo '<td>' . esc_html($func) . '()</td>';
                    if (function_exists($func)) {
                        echo '<td class="status-ok">✅ Defineret</td>';
                    } else {
                        echo '<td class="status-error">❌ MANGLER</td>';
                    }
                    echo '</tr>';
                }
                ?>
            </table>
        </div>

        <div class="section">
            <h2>🗄️ Database Tabeller</h2>
            <table>
                <tr>
                    <th>Tabel</th>
                    <th>Status</th>
                    <th>Rækker</th>
                </tr>
                <?php
                global $wpdb;
                $tables = array(
                    'rtf_platform_users',
                    'rtf_platform_sessions',
                    'rtf_platform_wall_posts',
                    'rtf_platform_messages',
                    'rtf_platform_documents',
                    'rtf_platform_photos',
                    'rtf_platform_kate_chats',
                );
                
                foreach ($tables as $table) {
                    $full_table = $wpdb->prefix . $table;
                    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'") === $full_table;
                    echo '<tr>';
                    echo '<td>' . esc_html($table) . '</td>';
                    if ($exists) {
                        $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table");
                        echo '<td class="status-ok">✅ Eksisterer</td>';
                        echo '<td>' . intval($count) . '</td>';
                    } else {
                        echo '<td class="status-error">❌ MANGLER</td>';
                        echo '<td>-</td>';
                    }
                    echo '</tr>';
                }
                ?>
            </table>
        </div>

        <div class="section">
            <h2>📦 Vendor Dependencies</h2>
            <p>
                <?php
                if (defined('RTF_VENDOR_LOADED') && RTF_VENDOR_LOADED) {
                    echo '<span class="status-ok">✅ RTF Vendor Loader Plugin er aktiveret</span>';
                } else {
                    echo '<span class="status-error">❌ RTF Vendor Loader Plugin er IKKE aktiveret</span>';
                    echo '<br><small>Kate AI og Stripe vil ikke virke før plugin er uploaded og aktiveret.</small>';
                }
                ?>
            </p>
        </div>

        <div class="section">
            <h2>🎯 Quick Actions</h2>
            <p><strong>Hvis sider mangler:</strong></p>
            <ol>
                <li><strong>Metode 1:</strong> Gå til <a href="/wp-admin/themes.php">Temaer</a> → Aktiver et andet tema → Aktiver "Ret til Familie" igen</li>
                <li><strong>Metode 2:</strong> <a href="/wp-admin/admin-ajax.php?action=rtf_force_create_pages" target="_blank">Klik her for at oprette alle sider</a></li>
            </ol>
            
            <p><strong>Efter sider er oprettet:</strong></p>
            <ul>
                <li>Gå til <a href="/wp-admin/options-permalink.php">Indstillinger → Permalinks</a> og klik "Gem ændringer"</li>
                <li>Besøg <a href="<?php echo home_url('/borger-platform/'); ?>">Borgerplatform</a> for at teste</li>
            </ul>
        </div>

        <div class="section">
            <h2>ℹ️ System Info</h2>
            <table>
                <tr>
                    <th>Parameter</th>
                    <th>Værdi</th>
                </tr>
                <tr>
                    <td>WordPress Version</td>
                    <td><?php echo get_bloginfo('version'); ?></td>
                </tr>
                <tr>
                    <td>PHP Version</td>
                    <td><?php echo PHP_VERSION; ?></td>
                </tr>
                <tr>
                    <td>Theme</td>
                    <td><?php echo wp_get_theme()->get('Name'); ?> (Version: <?php echo wp_get_theme()->get('Version'); ?>)</td>
                </tr>
                <tr>
                    <td>Site URL</td>
                    <td><?php echo home_url(); ?></td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
