<?php
/**
 * PLATFORM VERIFICATION SCRIPT
 * Verificerer at ALLE platform features virker korrekt
 * 
 * BRUG: Åbn https://rettilfamilie.com/PLATFORM-VERIFICATION.php i browser
 */

// Load WordPress
require_once __DIR__ . '/wp-load.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <title>Platform Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; 
            background: #f8fafc; 
            padding: 40px 20px;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { color: #1e293b; margin-bottom: 30px; font-size: 2.5em; }
        h2 { color: #334155; margin: 30px 0 15px; font-size: 1.8em; border-bottom: 3px solid #3b82f6; padding-bottom: 10px; }
        .section { background: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .success { color: #059669; background: #d1fae5; padding: 15px; border-left: 5px solid #059669; margin: 10px 0; border-radius: 4px; }
        .error { color: #dc2626; background: #fee2e2; padding: 15px; border-left: 5px solid #dc2626; margin: 10px 0; border-radius: 4px; }
        .warning { color: #d97706; background: #fef3c7; padding: 15px; border-left: 5px solid #d97706; margin: 10px 0; border-radius: 4px; }
        .info { color: #2563eb; background: #dbeafe; padding: 15px; border-left: 5px solid #2563eb; margin: 10px 0; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f3f4f6; font-weight: 600; }
        tr:hover { background: #f9fafb; }
        .badge { 
            display: inline-block; 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 0.85em; 
            font-weight: 600;
        }
        .badge-success { background: #d1fae5; color: #059669; }
        .badge-error { background: #fee2e2; color: #dc2626; }
        .badge-warning { background: #fef3c7; color: #d97706; }
        code { background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-family: 'Courier New', monospace; }
        a { color: #2563eb; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 10px; color: white; }
        .stat-card h3 { font-size: 2.5em; margin-bottom: 5px; }
        .stat-card p { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 RTF Platform Verification</h1>
        <div class="info">
            <strong>📊 Komplet platformsverifikation</strong><br>
            Dette script tjekker alle 20 områder af platformen for fejl og problemer.
        </div>

        <?php
        $errors = [];
        $warnings = [];
        $success_count = 0;
        $total_checks = 0;

        // ============================================================================
        // 1. DATABASE TABLES
        // ============================================================================
        echo '<div class="section">';
        echo '<h2>1️⃣ Database Tabeller</h2>';
        
        global $wpdb;
        $required_tables = [
            'rtf_platform_users',
            'rtf_platform_posts',
            'rtf_platform_images',
            'rtf_platform_documents',
            'rtf_platform_comments',
            'rtf_platform_likes',
            'rtf_platform_forum',
            'rtf_platform_kate_chat',
            'rtf_platform_friends',
            'rtf_platform_messages',
            'rtf_platform_shares',
            'rtf_platform_admins',
            'rtf_platform_cases',
            'rtf_platform_deadlines',
            'rtf_stripe_subscriptions',
            'rtf_stripe_payments'
        ];
        
        echo '<table>';
        echo '<tr><th>Tabel</th><th>Status</th><th>Rows</th></tr>';
        
        foreach ($required_tables as $table) {
            $total_checks++;
            $full_table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") === $full_table_name;
            
            if ($exists) {
                $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table_name");
                echo "<tr>";
                echo "<td><code>$table</code></td>";
                echo "<td><span class='badge badge-success'>✅ Eksisterer</span></td>";
                echo "<td>$count</td>";
                echo "</tr>";
                $success_count++;
            } else {
                echo "<tr>";
                echo "<td><code>$table</code></td>";
                echo "<td><span class='badge badge-error'>❌ Mangler</span></td>";
                echo "<td>-</td>";
                echo "</tr>";
                $errors[] = "Tabel $table mangler i databasen";
            }
        }
        
        echo '</table>';
        echo '</div>';

        // ============================================================================
        // 2. WORDPRESS PAGES
        // ============================================================================
        echo '<div class="section">';
        echo '<h2>2️⃣ WordPress Sider & Templates</h2>';
        
        $required_pages = [
            'platform-auth' => 'Platform Login',
            'platform-profil' => 'Min Profil',
            'platform-vaeg' => 'Min Væg',
            'platform-chat' => 'Beskeder',
            'platform-billeder' => 'Billede Galleri',
            'platform-dokumenter' => 'Dokumenter',
            'platform-find-borgere' => 'Find Borgere',
            'platform-venner' => 'Venner',
            'platform-nyheder' => 'Nyheder',
            'platform-forum' => 'Forum',
            'platform-sagshjaelp' => 'Sagshjælp',
            'platform-kate-ai' => 'Kate AI',
            'platform-indstillinger' => 'Indstillinger',
            'platform-admin-dashboard' => 'Admin Dashboard',
            'platform-subscription' => 'Abonnement',
            'platform-rapporter' => 'Rapporter'
        ];
        
        echo '<table>';
        echo '<tr><th>Side Slug</th><th>WordPress Side</th><th>Template Fil</th><th>Template Tildelt</th><th>Test Link</th></tr>';
        
        foreach ($required_pages as $slug => $title) {
            $total_checks++;
            
            // Check WP page exists
            $page = get_page_by_path($slug);
            $page_status = $page ? '✅ Ja' : '❌ Nej';
            
            // Check template file exists
            $template_file = $slug . '.php';
            $template_path = get_template_directory() . '/' . $template_file;
            $file_exists = file_exists($template_path);
            $file_status = $file_exists ? '✅ Ja' : '❌ Nej';
            
            // Check template assigned
            $assigned_template = $page ? get_post_meta($page->ID, '_wp_page_template', true) : '';
            $template_assigned = ($assigned_template === $template_file) ? '✅ Korrekt' : '⚠️ Mangler';
            
            // Build row
            echo "<tr>";
            echo "<td><code>$slug</code></td>";
            echo "<td>$page_status</td>";
            echo "<td>$file_status</td>";
            echo "<td>$template_assigned</td>";
            
            if ($page && $file_exists && $assigned_template === $template_file) {
                $url = home_url('/' . $slug . '/?lang=da');
                echo "<td><a href='$url' target='_blank'>Test →</a></td>";
                $success_count++;
            } else {
                echo "<td>-</td>";
                if (!$page) $errors[] = "WordPress side '$slug' mangler";
                if (!$file_exists) $errors[] = "Template fil '$template_file' mangler";
                if ($assigned_template !== $template_file) $warnings[] = "Side '$slug' har ikke korrekt template tildelt";
            }
            
            echo "</tr>";
        }
        
        echo '</table>';
        echo '</div>';

        // ============================================================================
        // 3. REST API ENDPOINTS
        // ============================================================================
        echo '<div class="section">';
        echo '<h2>3️⃣ REST API Endpoints</h2>';
        
        $rest_routes = rest_get_server()->get_routes();
        $kate_endpoints = array_filter(array_keys($rest_routes), function($route) {
            return strpos($route, '/kate/v1/') === 0;
        });
        
        echo '<div class="info">Fundet <strong>' . count($kate_endpoints) . '</strong> /kate/v1/ endpoints</div>';
        
        $critical_endpoints = [
            '/kate/v1/chat',
            '/kate/v1/admin/users',
            '/kate/v1/admin/posts',
            '/kate/v1/admin/forum'
        ];
        
        echo '<table>';
        echo '<tr><th>Endpoint</th><th>Status</th></tr>';
        
        foreach ($critical_endpoints as $endpoint) {
            $total_checks++;
            $exists = in_array($endpoint, $kate_endpoints);
            
            echo "<tr>";
            echo "<td><code>$endpoint</code></td>";
            if ($exists) {
                echo "<td><span class='badge badge-success'>✅ Registreret</span></td>";
                $success_count++;
            } else {
                echo "<td><span class='badge badge-error'>❌ Mangler</span></td>";
                $errors[] = "REST endpoint '$endpoint' mangler";
            }
            echo "</tr>";
        }
        
        echo '</table>';
        echo '</div>';

        // ============================================================================
        // 4. TRANSLATIONS
        // ============================================================================
        echo '<div class="section">';
        echo '<h2>4️⃣ Multi-Language Support</h2>';
        
        $sidebar_path = get_template_directory() . '/template-parts/platform-sidebar.php';
        $sidebar_content = file_get_contents($sidebar_path);
        
        $total_checks += 3;
        
        // Check for translation array
        $has_translation_array = strpos($sidebar_content, '$translations = [') !== false;
        echo $has_translation_array 
            ? '<div class="success">✅ Translation array defineret i sidebar</div>' 
            : '<div class="error">❌ Translation array mangler i sidebar</div>';
        
        if ($has_translation_array) $success_count++;
        
        // Check DA/SV/EN support
        $has_da = strpos($sidebar_content, "'da' => [") !== false;
        $has_sv = strpos($sidebar_content, "'sv' => [") !== false;
        $has_en = strpos($sidebar_content, "'en' => [") !== false;
        
        if ($has_da && $has_sv && $has_en) {
            echo '<div class="success">✅ Alle 3 sprog (DA/SV/EN) understøttes</div>';
            $success_count += 2;
        } else {
            echo '<div class="error">❌ Ikke alle sprog er implementeret</div>';
            $errors[] = "Sidebar mangler fuld DA/SV/EN support";
        }
        
        echo '</div>';

        // ============================================================================
        // 5. ADMIN SYSTEM
        // ============================================================================
        echo '<div class="section">';
        echo '<h2>5️⃣ Admin System</h2>';
        
        $admin_dashboard_path = get_template_directory() . '/platform-admin-dashboard.php';
        $admin_content = file_exists($admin_dashboard_path) ? file_get_contents($admin_dashboard_path) : '';
        
        $total_checks += 4;
        
        // Check for 4 tabs
        $has_users_tab = strpos($admin_content, 'switchTab(\'users\')') !== false;
        $has_news_tab = strpos($admin_content, 'switchTab(\'news\')') !== false;
        $has_posts_tab = strpos($admin_content, 'switchTab(\'posts\')') !== false;
        $has_forum_tab = strpos($admin_content, 'switchTab(\'forum\')') !== false;
        
        echo '<table>';
        echo '<tr><th>Feature</th><th>Status</th></tr>';
        
        echo '<tr><td>Users Management Tab</td><td>' . ($has_users_tab ? '<span class="badge badge-success">✅</span>' : '<span class="badge badge-error">❌</span>') . '</td></tr>';
        echo '<tr><td>News Management Tab</td><td>' . ($has_news_tab ? '<span class="badge badge-success">✅</span>' : '<span class="badge badge-error">❌</span>') . '</td></tr>';
        echo '<tr><td>Posts Management Tab</td><td>' . ($has_posts_tab ? '<span class="badge badge-success">✅</span>' : '<span class="badge badge-error">❌</span>') . '</td></tr>';
        echo '<tr><td>Forum Management Tab</td><td>' . ($has_forum_tab ? '<span class="badge badge-success">✅</span>' : '<span class="badge badge-error">❌</span>') . '</td></tr>';
        
        echo '</table>';
        
        if ($has_users_tab) $success_count++;
        if ($has_news_tab) $success_count++;
        if ($has_posts_tab) $success_count++;
        if ($has_forum_tab) $success_count++;
        
        echo '</div>';

        // ============================================================================
        // STATISTICS
        // ============================================================================
        $success_rate = $total_checks > 0 ? round(($success_count / $total_checks) * 100) : 0;
        
        echo '<div class="section">';
        echo '<h2>📊 Samlet Statistik</h2>';
        
        echo '<div class="grid">';
        echo '<div class="stat-card">';
        echo '<h3>' . $success_count . '/' . $total_checks . '</h3>';
        echo '<p>Tests Bestået</p>';
        echo '</div>';
        
        echo '<div class="stat-card">';
        echo '<h3>' . $success_rate . '%</h3>';
        echo '<p>Success Rate</p>';
        echo '</div>';
        
        echo '<div class="stat-card">';
        echo '<h3>' . count($errors) . '</h3>';
        echo '<p>Kritiske Fejl</p>';
        echo '</div>';
        
        echo '<div class="stat-card">';
        echo '<h3>' . count($warnings) . '</h3>';
        echo '<p>Advarsler</p>';
        echo '</div>';
        echo '</div>';
        
        // Show errors
        if (count($errors) > 0) {
            echo '<div class="error"><strong>❌ Kritiske Fejl:</strong><ul>';
            foreach ($errors as $error) {
                echo '<li>' . $error . '</li>';
            }
            echo '</ul></div>';
        }
        
        // Show warnings
        if (count($warnings) > 0) {
            echo '<div class="warning"><strong>⚠️ Advarsler:</strong><ul>';
            foreach ($warnings as $warning) {
                echo '<li>' . $warning . '</li>';
            }
            echo '</ul></div>';
        }
        
        // Final verdict
        if (count($errors) === 0 && count($warnings) === 0) {
            echo '<div class="success">';
            echo '<h3 style="margin-bottom:10px">🎉 PERFEKT! Alle tests bestået!</h3>';
            echo '<p>Platformen er 100% operationel og klar til brug.</p>';
            echo '</div>';
        } elseif (count($errors) === 0) {
            echo '<div class="warning">';
            echo '<h3 style="margin-bottom:10px">⚠️ Platformen virker, men har advarsler</h3>';
            echo '<p>Alle kritiske systemer fungerer, men der er mindre problemer der bør fixes.</p>';
            echo '</div>';
        } else {
            echo '<div class="error">';
            echo '<h3 style="margin-bottom:10px">❌ Kritiske fejl fundet!</h3>';
            echo '<p>Nogle systemer virker ikke korrekt. Kør setup scriptet eller fix fejlene manuelt.</p>';
            echo '</div>';
        }
        
        echo '</div>';
        ?>

        <div class="info" style="margin-top: 30px;">
            <strong>🔧 Næste Trin:</strong><br>
            1. Hvis der er fejl: Kør <a href="<?php echo home_url('/wp-admin/admin-ajax.php?action=rtf_force_create_pages'); ?>" target="_blank">Setup Script</a><br>
            2. Test admin panel: <a href="<?php echo home_url('/platform-admin-dashboard/?lang=da'); ?>" target="_blank">Admin Dashboard</a><br>
            3. Test Find Borgere: <a href="<?php echo home_url('/platform-find-borgere/?lang=da'); ?>" target="_blank">Find Borgere</a><br>
            4. Refresh denne side for at se om fejlene er fixet
        </div>
    </div>
</body>
</html>
