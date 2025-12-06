<?php
/**
 * Template Name: Stripe Test
 * Description: Test Stripe integration
 */

// Start output
get_header();
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <h1 style="color: #0c4a6e; border-bottom: 3px solid #0c4a6e; padding-bottom: 10px;">🔍 Stripe Connection Test</h1>
    
    <?php
    // Test 1: Stripe library exists
    $stripe_init = get_template_directory() . '/stripe-php-13.18.0/init.php';
    
    echo '<div style="margin: 20px 0; padding: 15px; background: #f0f9ff; border-left: 4px solid #0c4a6e;">';
    echo '<h2 style="margin-top: 0;">📁 Stripe Library</h2>';
    echo '<p>Looking for: <code>' . $stripe_init . '</code></p>';
    
    if (file_exists($stripe_init)) {
        echo '<p style="color: #15803d; font-weight: bold;">✅ Stripe library found!</p>';
        
        // Load it
        require_once($stripe_init);
        echo '<p style="color: #15803d;">✅ Stripe library loaded successfully</p>';
        
        // Test 2: Constants defined
        echo '</div>';
        echo '<div style="margin: 20px 0; padding: 15px; background: #f0fdf4; border-left: 4px solid #15803d;">';
        echo '<h2 style="margin-top: 0;">🔑 Stripe Configuration</h2>';
        
        if (defined('RTF_STRIPE_SECRET_KEY') && defined('RTF_STRIPE_PRICE_ID')) {
            $key_preview = substr(RTF_STRIPE_SECRET_KEY, 0, 15) . '...' . substr(RTF_STRIPE_SECRET_KEY, -4);
            echo '<p>✅ Secret Key: <code>' . $key_preview . '</code></p>';
            echo '<p>✅ Price ID: <code>' . RTF_STRIPE_PRICE_ID . '</code></p>';
            
            // Test 3: Create test checkout
            echo '</div>';
            echo '<div style="margin: 20px 0; padding: 15px; background: #fef3c7; border-left: 4px solid #d97706;">';
            echo '<h2 style="margin-top: 0;">🧪 Test Checkout Session</h2>';
            
            try {
                \Stripe\Stripe::setApiKey(RTF_STRIPE_SECRET_KEY);
                echo '<p style="color: #15803d;">✅ API Key set successfully</p>';
                
                // Create test session
                $test_session = \Stripe\Checkout\Session::create([
                    'success_url' => home_url('/platform-profil/?payment=success&test=1'),
                    'cancel_url' => home_url('/platform-subscription/?payment=cancelled&test=1'),
                    'payment_method_types' => ['card'],
                    'mode' => 'subscription',
                    'customer_email' => 'test@example.com',
                    'client_reference_id' => 'TEST_' . time(),
                    'line_items' => [[
                        'price' => RTF_STRIPE_PRICE_ID,
                        'quantity' => 1
                    ]],
                    'subscription_data' => [
                        'metadata' => [
                            'user_id' => 'TEST',
                            'username' => 'testuser',
                            'email' => 'test@example.com',
                            'rtf_platform' => 'true',
                            'test_mode' => 'true'
                        ]
                    ]
                ]);
                
                echo '<div style="background: #dcfce7; padding: 20px; border-radius: 8px; margin: 15px 0;">';
                echo '<h3 style="color: #15803d; margin-top: 0;">🎉 SUCCESS!</h3>';
                echo '<p><strong>Stripe virker perfekt!</strong></p>';
                echo '<p>Session ID: <code>' . $test_session->id . '</code></p>';
                echo '<p>Checkout URL oprettet ✅</p>';
                echo '<p style="margin-top: 20px;">';
                echo '<a href="' . $test_session->url . '" style="display: inline-block; padding: 12px 24px; background: #0c4a6e; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;" target="_blank">🔗 Åbn Test Checkout (Ny fane)</a>';
                echo '</p>';
                echo '<p style="font-size: 13px; color: #64748b; margin-top: 10px;">⚠️ Dette er en rigtig Stripe checkout. Brug test kort 4242 4242 4242 4242 eller annuller betalingen.</p>';
                echo '</div>';
                
                echo '<div style="background: #e0f2fe; padding: 15px; border-radius: 8px; margin: 15px 0;">';
                echo '<h3 style="color: #0c4a6e; margin-top: 0;">✅ Konklusion</h3>';
                echo '<p><strong>Stripe integration virker 100%!</strong></p>';
                echo '<p>Hvis registrering stadig fejler, er problemet IKKE Stripe.</p>';
                echo '<p>Problemet er sandsynligvis:</p>';
                echo '<ul>';
                echo '<li>Database bruger ikke bliver oprettet korrekt</li>';
                echo '<li>Session bliver ikke sat</li>';
                echo '<li>Redirect virker ikke</li>';
                echo '</ul>';
                echo '</div>';
                
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                echo '<div style="background: #fee2e2; padding: 15px; border-radius: 8px;">';
                echo '<h3 style="color: #dc2626; margin-top: 0;">❌ Stripe Invalid Request Error</h3>';
                echo '<p><strong>Fejl:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<details><summary>Stack trace</summary><pre style="background: #1e293b; color: #f1f5f9; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 12px;">' . htmlspecialchars($e->getTraceAsString()) . '</pre></details>';
                echo '</div>';
            } catch (\Stripe\Exception\AuthenticationException $e) {
                echo '<div style="background: #fee2e2; padding: 15px; border-radius: 8px;">';
                echo '<h3 style="color: #dc2626; margin-top: 0;">❌ Stripe Authentication Error</h3>';
                echo '<p><strong>Problem:</strong> API key er ugyldig</p>';
                echo '<p><strong>Fejl:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            } catch (\Exception $e) {
                echo '<div style="background: #fee2e2; padding: 15px; border-radius: 8px;">';
                echo '<h3 style="color: #dc2626; margin-top: 0;">❌ Generel Fejl</h3>';
                echo '<p><strong>Type:</strong> ' . get_class($e) . '</p>';
                echo '<p><strong>Fejl:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<details><summary>Stack trace</summary><pre style="background: #1e293b; color: #f1f5f9; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 12px;">' . htmlspecialchars($e->getTraceAsString()) . '</pre></details>';
                echo '</div>';
            }
            
        } else {
            echo '<p style="color: #dc2626; font-weight: bold;">❌ Stripe constants IKKE defineret!</p>';
            echo '<p>Check functions.php for RTF_STRIPE_SECRET_KEY og RTF_STRIPE_PRICE_ID</p>';
        }
        
    } else {
        echo '<p style="color: #dc2626; font-weight: bold;">❌ Stripe library IKKE fundet!</p>';
        echo '<p>Expected: ' . $stripe_init . '</p>';
    }
    
    echo '</div>';
    
    // Test 4: Check for users in database
    global $wpdb;
    $table = $wpdb->prefix . 'rtf_platform_users';
    
    echo '<div style="margin: 20px 0; padding: 15px; background: #fef3c7; border-left: 4px solid #d97706;">';
    echo '<h2 style="margin-top: 0;">👥 Database Users</h2>';
    
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
    if ($table_exists) {
        $users = $wpdb->get_results("SELECT id, username, email, subscription_status, created_at FROM $table ORDER BY created_at DESC LIMIT 10");
        $count = count($users);
        
        echo '<p>✅ Users table: <code>' . $table . '</code></p>';
        echo '<p>Total users found: <strong>' . $count . '</strong></p>';
        
        if ($users) {
            echo '<table style="width: 100%; border-collapse: collapse; margin-top: 15px;">';
            echo '<thead><tr style="background: #0c4a6e; color: white;">';
            echo '<th style="padding: 10px; text-align: left;">ID</th>';
            echo '<th style="padding: 10px; text-align: left;">Username</th>';
            echo '<th style="padding: 10px; text-align: left;">Email</th>';
            echo '<th style="padding: 10px; text-align: left;">Subscription</th>';
            echo '<th style="padding: 10px; text-align: left;">Created</th>';
            echo '</tr></thead><tbody>';
            
            foreach ($users as $user) {
                $status_color = $user->subscription_status === 'active' ? '#15803d' : '#dc2626';
                echo '<tr style="border-bottom: 1px solid #e2e8f0;">';
                echo '<td style="padding: 10px;">' . $user->id . '</td>';
                echo '<td style="padding: 10px;">' . htmlspecialchars($user->username) . '</td>';
                echo '<td style="padding: 10px;">' . htmlspecialchars($user->email) . '</td>';
                echo '<td style="padding: 10px; color: ' . $status_color . '; font-weight: bold;">' . htmlspecialchars($user->subscription_status) . '</td>';
                echo '<td style="padding: 10px;">' . $user->created_at . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<p style="color: #d97706;">⚠️ Ingen brugere i databasen</p>';
        }
    } else {
        echo '<p style="color: #dc2626;">❌ Users table findes ikke: <code>' . $table . '</code></p>';
    }
    
    echo '</div>';
    ?>
    
    <div style="margin: 20px 0; padding: 15px; background: #e0f2fe; border-left: 4px solid #0c4a6e;">
        <h2 style="margin-top: 0;">🔗 Næste Skridt</h2>
        <ol style="line-height: 1.8;">
            <li>Hvis Stripe test VIRKER ✅ → Problem er i registrerings-flowet, ikke Stripe</li>
            <li>Prøv at registrere en bruger på <a href="<?php echo home_url('/platform-auth/'); ?>" style="color: #0c4a6e; font-weight: bold;">/platform-auth/</a></li>
            <li>Hvis du får "Critical Error" → Tjek WordPress debug log</li>
            <li>Hvis brugeren oprettes MEN ikke får Stripe → Kig i tabellen ovenfor</li>
        </ol>
        
        <p style="margin-top: 20px;">
            <a href="<?php echo home_url('/platform-auth/'); ?>" style="display: inline-block; padding: 12px 24px; background: #15803d; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">📝 Test Registrering</a>
            <a href="<?php echo home_url('/platform-admin-dashboard/'); ?>" style="display: inline-block; padding: 12px 24px; background: #0c4a6e; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin-left: 10px;">⚙️ Admin Panel</a>
        </p>
    </div>
</div>

<?php
get_footer();
?>
