<?php
/**
 * SYSTEM VERIFICATION & TEST PAGE
 * Tjek at alt er klar til live
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
    die("WordPress ikke fundet - kør dette script fra WordPress tema mappen");
}

global $wpdb;

?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ret til Familie - System Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #2563eb;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        .subtitle {
            color: #64748b;
            margin-bottom: 40px;
            font-size: 1.1em;
        }
        .section {
            margin-bottom: 30px;
            padding: 25px;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #2563eb;
        }
        .section h2 {
            color: #1e293b;
            margin-bottom: 15px;
            font-size: 1.4em;
        }
        .check-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .check-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
            font-size: 16px;
        }
        .success {
            background: #10b981;
            color: white;
        }
        .warning {
            background: #f59e0b;
            color: white;
        }
        .error {
            background: #ef4444;
            color: white;
        }
        .check-label {
            flex: 1;
            color: #334155;
            font-size: 1.05em;
        }
        .check-value {
            color: #64748b;
            font-family: monospace;
            font-size: 0.95em;
        }
        .summary {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        .summary h2 {
            color: white;
            margin-bottom: 15px;
        }
        .summary-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 20px;
        }
        .stat {
            text-align: center;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
        }
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #1e40af;
            transform: translateY(-2px);
        }
        .code-block {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 0.9em;
            margin-top: 10px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 System Verification</h1>
        <p class="subtitle">Ret til Familie Platform - Live Readiness Check</p>

        <?php
        $checks = [];
        $success_count = 0;
        $warning_count = 0;
        $error_count = 0;

        // CHECK 1: Stripe Configuration
        $stripe_ok = defined('RTF_STRIPE_SECRET_KEY') && 
                     !empty(RTF_STRIPE_SECRET_KEY) && 
                     defined('RTF_STRIPE_PRICE_ID') && 
                     !empty(RTF_STRIPE_PRICE_ID);
        
        $checks[] = [
            'status' => $stripe_ok ? 'success' : 'error',
            'label' => 'Stripe Konfiguration',
            'value' => $stripe_ok ? 'Konfigureret ✓' : 'MANGLER!'
        ];
        if ($stripe_ok) $success_count++; else $error_count++;

        // CHECK 2: Stripe Library
        $stripe_loaded = class_exists('\Stripe\Stripe');
        $checks[] = [
            'status' => $stripe_loaded ? 'success' : 'error',
            'label' => 'Stripe Library',
            'value' => $stripe_loaded ? 'Loaded ✓' : 'NOT LOADED!'
        ];
        if ($stripe_loaded) $success_count++; else $error_count++;

        // CHECK 3: Database Tables
        $tables_required = ['rtf_platform_users', 'rtf_platform_posts', 'rtf_platform_privacy'];
        $tables_exist = 0;
        foreach ($tables_required as $table) {
            $exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}{$table}'");
            if ($exists) $tables_exist++;
        }
        $tables_ok = $tables_exist === count($tables_required);
        $checks[] = [
            'status' => $tables_ok ? 'success' : 'error',
            'label' => 'Database Tabeller',
            'value' => "$tables_exist/" . count($tables_required) . " OK"
        ];
        if ($tables_ok) $success_count++; else $error_count++;

        // CHECK 4: User Count
        $user_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_users");
        $users_ok = $user_count > 0;
        $checks[] = [
            'status' => $users_ok ? ($user_count == 1 ? 'success' : 'warning') : 'error',
            'label' => 'Brugere i database',
            'value' => $user_count . ' bruger' . ($user_count != 1 ? 'e' : '')
        ];
        if ($users_ok) {
            if ($user_count == 1) $success_count++;
            else $warning_count++;
        } else {
            $error_count++;
        }

        // CHECK 5: Patrick's Account
        $patrick = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE email = 'Patrickfoerslev@gmail.com'");
        $patrick_ok = $patrick !== null;
        $checks[] = [
            'status' => $patrick_ok ? 'success' : 'error',
            'label' => 'Patrick\'s Admin Konto',
            'value' => $patrick_ok ? "Findes (ID: {$patrick->id})" : 'MANGLER!'
        ];
        if ($patrick_ok) $success_count++; else $error_count++;

        // CHECK 6: Patrick Admin Status
        if ($patrick_ok) {
            $admin_ok = $patrick->is_admin == 1;
            $checks[] = [
                'status' => $admin_ok ? 'success' : 'warning',
                'label' => 'Patrick Admin Rettigheder',
                'value' => $admin_ok ? 'Admin ✓' : 'Ikke admin'
            ];
            if ($admin_ok) $success_count++; else $warning_count++;
        }

        // CHECK 7: Patrick Subscription
        if ($patrick_ok) {
            $sub_ok = $patrick->subscription_status === 'active';
            $checks[] = [
                'status' => $sub_ok ? 'success' : 'warning',
                'label' => 'Patrick Abonnement',
                'value' => $patrick->subscription_status
            ];
            if ($sub_ok) $success_count++; else $warning_count++;
        }

        // CHECK 8: Vendor Directory
        $vendor_exists = file_exists(get_template_directory() . '/vendor/autoload.php');
        $checks[] = [
            'status' => $vendor_exists ? 'success' : 'error',
            'label' => 'Composer Vendor',
            'value' => $vendor_exists ? 'Findes ✓' : 'MANGLER - Kør composer install'
        ];
        if ($vendor_exists) $success_count++; else $error_count++;

        // CHECK 9: Kate AI Files
        $kate_exists = file_exists(get_template_directory() . '/kate-ai/kate-ai.php');
        $checks[] = [
            'status' => $kate_exists ? 'success' : 'warning',
            'label' => 'Kate AI System',
            'value' => $kate_exists ? 'Installeret ✓' : 'Ikke fundet'
        ];
        if ($kate_exists) $success_count++; else $warning_count++;

        // CHECK 10: Intents File
        $intents_path = get_template_directory() . '/kate-ai/data/intents.json';
        $intents_exists = file_exists($intents_path);
        if ($intents_exists) {
            $intents_data = json_decode(file_get_contents($intents_path), true);
            $intent_count = is_array($intents_data) ? count($intents_data) : 0;
        } else {
            $intent_count = 0;
        }
        $intents_ok = $intent_count > 30;
        $checks[] = [
            'status' => $intents_ok ? 'success' : 'warning',
            'label' => 'Kate AI Intents',
            'value' => $intent_count . ' intents'
        ];
        if ($intents_ok) $success_count++; else $warning_count++;

        $total_checks = count($checks);
        $overall_status = $error_count === 0 ? 'success' : ($error_count < 3 ? 'warning' : 'error');
        ?>

        <div class="summary">
            <h2>System Status: 
                <?php if ($overall_status === 'success'): ?>
                    ✅ KLAR TIL LIVE
                <?php elseif ($overall_status === 'warning'): ?>
                    ⚠️ NÆSTEN KLAR
                <?php else: ?>
                    ❌ IKKE KLAR
                <?php endif; ?>
            </h2>
            <div class="summary-stats">
                <div class="stat">
                    <div class="stat-number" style="color: #10b981;"><?php echo $success_count; ?></div>
                    <div class="stat-label">Success</div>
                </div>
                <div class="stat">
                    <div class="stat-number" style="color: #f59e0b;"><?php echo $warning_count; ?></div>
                    <div class="stat-label">Warnings</div>
                </div>
                <div class="stat">
                    <div class="stat-number" style="color: #ef4444;"><?php echo $error_count; ?></div>
                    <div class="stat-label">Errors</div>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>📋 System Checks (<?php echo $total_checks; ?>)</h2>
            <?php foreach ($checks as $check): ?>
                <div class="check-item">
                    <div class="check-icon <?php echo $check['status']; ?>">
                        <?php 
                        if ($check['status'] === 'success') echo '✓';
                        elseif ($check['status'] === 'warning') echo '!';
                        else echo '✗';
                        ?>
                    </div>
                    <div class="check-label"><?php echo $check['label']; ?></div>
                    <div class="check-value"><?php echo $check['value']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($patrick_ok): ?>
        <div class="section">
            <h2>👤 Patrick's Profil</h2>
            <div class="code-block">
ID: <?php echo $patrick->id; ?>

Username: <?php echo $patrick->username; ?>

Email: <?php echo $patrick->email; ?>

Full Name: <?php echo $patrick->full_name; ?>

Admin: <?php echo $patrick->is_admin ? 'JA ✓' : 'NEJ'; ?>

Subscription: <?php echo $patrick->subscription_status; ?>

Created: <?php echo $patrick->created_at; ?>

Last Login: <?php echo $patrick->last_login ?? 'Aldrig'; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="section">
            <h2>🔗 Vigtige Links</h2>
            <div style="margin-top: 15px;">
                <a href="<?php echo home_url('/platform-auth/'); ?>" class="btn">🔐 Platform Login</a>
                <a href="<?php echo home_url('/platform-profil/'); ?>" class="btn">👤 Min Profil</a>
                <a href="<?php echo home_url('/platform-admin-dashboard/'); ?>" class="btn">⚙️ Admin Panel</a>
            </div>
        </div>

        <?php if ($error_count > 0): ?>
        <div class="section" style="border-left-color: #ef4444;">
            <h2 style="color: #ef4444;">❌ Kritiske Fejl Fundet</h2>
            <p style="color: #64748b; margin-top: 10px;">
                Der er <?php echo $error_count; ?> kritiske fejl der skal rettes før live deployment.
                Tjek loggen og ret fejlene.
            </p>
        </div>
        <?php elseif ($warning_count > 0): ?>
        <div class="section" style="border-left-color: #f59e0b;">
            <h2 style="color: #f59e0b;">⚠️ Advarsler</h2>
            <p style="color: #64748b; margin-top: 10px;">
                Der er <?php echo $warning_count; ?> advarsler. Systemet kan køre, men bør optimeres.
            </p>
        </div>
        <?php else: ?>
        <div class="section" style="border-left-color: #10b981;">
            <h2 style="color: #10b981;">✅ Alt er Klart!</h2>
            <p style="color: #64748b; margin-top: 10px;">
                Systemet er klar til live deployment. Alle checks er bestået!
            </p>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>
