<?php
/**
 * Template Name: Platform Login/Registrering
 */

get_header();
$lang = rtf_get_lang();

// Debug mode - show errors
$debug_mode = isset($_GET['debug']); // Kun slået til med ?debug parameter
$debug_messages = array();

// Aktivér PHP error display kun i debug mode
if ($debug_mode) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Check if already logged in
if (rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-profil/?lang=' . $lang));
    exit;
}

$t = array(
    'da' => array(
        'login_title' => 'Log ind',
        'register_title' => 'Opret konto',
        'username' => 'Brugernavn',
        'email' => 'Email',
        'password' => 'Adgangskode',
        'full_name' => 'Fulde navn',
        'birthday' => 'Fødselsdag',
        'phone' => 'Telefonnummer',
        'phone_privacy' => 'Dit telefonnummer er kun synligt for administratorer - aldrig for andre brugere.',
        'login_btn' => 'Log ind',
        'register_btn' => 'Opret konto',
        'switch_to_register' => 'Har du ikke en konto? Registrer dig her',
        'switch_to_login' => 'Har du allerede en konto? Log ind her',
        'gdpr_notice' => 'Ved at oprette en konto accepterer du vores privatlivspolitik. Din fødselsdag vil blive anonymiseret til ##-##-ÅÅÅÅ.',
    ),
    'sv' => array(
        'login_title' => 'Logga in',
        'register_title' => 'Skapa konto',
        'username' => 'Användarnamn',
        'email' => 'E-post',
        'password' => 'Lösenord',
        'full_name' => 'Fullständigt namn',
        'birthday' => 'Födelsedag',
        'phone' => 'Telefonnummer',
        'phone_privacy' => 'Ditt telefonnummer är endast synligt för administratörer - aldrig för andra användare.',
        'login_btn' => 'Logga in',
        'register_btn' => 'Skapa konto',
        'switch_to_register' => 'Har du inget konto? Registrera dig här',
        'switch_to_login' => 'Har du redan ett konto? Logga in här',
        'gdpr_notice' => 'Genom att skapa ett konto accepterar du vår integritetspolicy. Din födelsedag kommer att anonymiseras till ##-##-ÅÅÅÅ.',
    ),
    'en' => array(
        'login_title' => 'Login',
        'register_title' => 'Create Account',
        'username' => 'Username',
        'email' => 'Email',
        'password' => 'Password',
        'full_name' => 'Full Name',
        'birthday' => 'Birthday',
        'phone' => 'Phone Number',
        'phone_privacy' => 'Your phone number is only visible to administrators - never to other users.',
        'login_btn' => 'Login',
        'register_btn' => 'Create Account',
        'switch_to_register' => "Don't have an account? Register here",
        'switch_to_login' => 'Already have an account? Login here',
        'gdpr_notice' => 'By creating an account you accept our privacy policy. Your birthday will be anonymized to ##-##-YYYY.',
    )
);
$txt = $t[$lang];

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    // CSRF protection
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'rtf_login')) {
        wp_die('Security check failed');
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'rtf_platform_users';
    
    $username_or_email = sanitize_text_field($_POST['username']);
    $password = $_POST['password'];
    
    if ($debug_mode) {
        $debug_messages[] = "Attempting login for: $username_or_email";
        $debug_messages[] = "Table: $table";
    }
    
    // Try to find user by username OR email
    $user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE (username = %s OR email = %s) AND is_active = 1",
        $username_or_email,
        $username_or_email
    ));
    
    if ($debug_mode) {
        $debug_messages[] = "User found: " . ($user ? 'YES' : 'NO');
        if ($user) {
            $debug_messages[] = "User ID: " . $user->id;
            $debug_messages[] = "Username: " . $user->username;
            $debug_messages[] = "Email: " . $user->email;
            $debug_messages[] = "Password hash exists: " . (!empty($user->password) ? 'YES' : 'NO');
            $debug_messages[] = "Password verify: " . (password_verify($password, $user->password) ? 'YES' : 'NO');
        }
    }
    
    if ($user && password_verify($password, $user->password)) {
        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);
        
        $_SESSION['rtf_user_id'] = $user->id;
        $_SESSION['rtf_username'] = $user->username;
        
        if ($debug_mode) {
            $debug_messages[] = "Login successful! Session ID: " . session_id();
            $debug_messages[] = "Redirecting to: " . home_url('/platform-profil/?lang=' . $lang);
        } else {
            wp_redirect(home_url('/platform-profil/?lang=' . $lang));
            exit;
        }
    } else {
        $error = $lang === 'da' ? 'Forkert brugernavn/email eller adgangskode' : ($lang === 'sv' ? 'Fel användarnamn/e-post eller lösenord' : 'Wrong username/email or password');
        if ($debug_mode) {
            $debug_messages[] = "Login failed - wrong credentials";
            if ($user) {
                $debug_messages[] = "User exists but password doesn't match";
            } else {
                $debug_messages[] = "User not found in database";
            }
        }
    }
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    // CSRF protection
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'rtf_register')) {
        wp_die('Security check failed');
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'rtf_platform_users';
    $table_privacy = $wpdb->prefix . 'rtf_platform_privacy';
    
    $username = sanitize_text_field($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = sanitize_text_field($_POST['full_name']);
    $birthday = sanitize_text_field($_POST['birthday']);
    $phone = sanitize_text_field($_POST['phone']);
    $case_type = sanitize_text_field($_POST['case_type'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $bio = sanitize_textarea_field($_POST['bio'] ?? '');
    $language_preference = isset($_POST['language_preference']) ? sanitize_text_field($_POST['language_preference']) : 'da_DK';
    
    // Map language to country
    $country_map = [
        'da_DK' => 'DK',
        'sv_SE' => 'SE',
        'en_US' => 'INTL'
    ];
    $country = $country_map[$language_preference] ?? 'DK';
    
    // Check if username or email exists
    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE username = %s OR email = %s", $username, $email));
    
    if ($debug_mode) {
        $debug_messages[] = "Checking duplicates - Found: $exists";
    }
    
    if ($exists) {
        $error = $lang === 'da' ? 'Brugernavn eller email er allerede i brug' : ($lang === 'sv' ? 'Användarnamn eller e-post används redan' : 'Username or email already in use');
    } else {
        // Prepare data for insert
        $user_data = array(
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'full_name' => $full_name,
            'birthday' => $birthday,
            'phone' => $phone,
            'language_preference' => $language_preference,
            'country' => $country,
            'subscription_status' => 'inactive',
            'is_admin' => 0,
            'is_active' => 1
        );
        
        // Only add optional fields if they have values
        if (!empty($case_type)) {
            $user_data['case_type'] = $case_type;
        }
        if (!empty($age) && $age > 0) {
            $user_data['age'] = $age;
        }
        if (!empty($bio)) {
            $user_data['bio'] = $bio;
        }
        
        if ($debug_mode) {
            $debug_messages[] = "Attempting insert with data: " . print_r($user_data, true);
        }
        
        $insert_result = $wpdb->insert($table, $user_data);
        
        if ($insert_result === false) {
            // Database error - LOG IT!
            error_log('RTF Registration Error: ' . $wpdb->last_error);
            error_log('RTF Registration Data: ' . print_r($user_data, true));
            
            if ($debug_mode) {
                wp_die('Database error: ' . $wpdb->last_error . '<br><br>Data attempted: <pre>' . print_r($user_data, true) . '</pre>');
            }
            $error = $lang === 'da' ? 'Der opstod en fejl ved oprettelse af bruger. Kontakt support hvis problemet fortsætter.' : ($lang === 'sv' ? 'Ett fel uppstod vid skapandet av användare. Kontakta support om problemet fortsätter.' : 'An error occurred creating user. Contact support if problem persists.');
        } else {
            $user_id = $wpdb->insert_id;
            
            // Create privacy settings with GDPR anonymization
            $wpdb->insert($table_privacy, array(
                'user_id' => $user_id,
                'gdpr_anonymize_birthday' => 1,
                'profile_visibility' => 'all',
                'show_in_forum' => 1,
                'allow_messages' => 1
            ));
            
            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);
            
            $_SESSION['rtf_user_id'] = $user_id;
            $_SESSION['rtf_username'] = $username;
            
            // Redirect til LIVE Stripe checkout - direkte include
            require_once(__DIR__ . '/stripe-php-13.18.0/init.php');
            \Stripe\Stripe::setApiKey(RTF_STRIPE_SECRET_KEY);
            
            try {
                $checkout_session = \Stripe\Checkout\Session::create([
                    'success_url' => home_url('/platform-profil/?lang=' . $lang . '&payment=success'),
                    'cancel_url' => home_url('/platform-subscription/?lang=' . $lang . '&payment=cancelled'),
                    'payment_method_types' => ['card'],
                    'mode' => 'subscription',
                    'customer_email' => $email,
                    'client_reference_id' => $user_id,
                    'line_items' => [[
                        'price' => RTF_STRIPE_PRICE_ID,
                        'quantity' => 1,
                    ]],
                    'metadata' => [
                        'user_id' => $user_id,
                        'username' => $username
                    ]
                ]);
                
                if (isset($checkout_session->customer)) {
                    $wpdb->update($table, 
                        array('stripe_customer_id' => $checkout_session->customer), 
                        array('id' => $user_id)
                    );
                }
                
                wp_redirect($checkout_session->url);
                exit;
                
            } catch (\Exception $e) {
                if ($debug_mode) {
                    wp_die('Stripe error: ' . $e->getMessage());
                }
                wp_redirect(home_url('/platform-subscription/?lang=' . $lang . '&error=stripe'));
                exit;
            }
        }
    }
}
?>

<main class="platform-auth">
    <div class="auth-container" style="max-width: 500px; margin: 60px auto; padding: 20px;">
        
        <?php if ($debug_mode && !empty($debug_messages)): ?>
            <div style="background: #f0f9ff; border: 2px solid #0ea5e9; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin: 0 0 10px 0; color: #0369a1;">Debug Info:</h3>
                <?php foreach ($debug_messages as $msg): ?>
                    <div style="font-family: monospace; font-size: 12px; margin: 5px 0; color: #0c4a6e;"><?php echo esc_html($msg); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fca5a5;">
                <strong>❌ Fejl:</strong> <?php echo esc_html($error); ?>
            </div>
        <?php endif; ?>

        <div class="auth-tabs" style="display: flex; margin-bottom: 30px; border-bottom: 2px solid #e0f2fe;">
            <button class="tab-btn active" data-tab="login" style="flex: 1; padding: 15px; background: none; border: none; cursor: pointer; font-size: 1.1em; color: var(--rtf-text); border-bottom: 3px solid #2563eb;">
                <?php echo esc_html($txt['login_title']); ?>
            </button>
            <button class="tab-btn" data-tab="register" style="flex: 1; padding: 15px; background: none; border: none; cursor: pointer; font-size: 1.1em; color: var(--rtf-muted);">
                <?php echo esc_html($txt['register_title']); ?>
            </button>
        </div>

        <!-- LOGIN FORM -->
        <div id="login-form" class="auth-form">
            <form method="POST" action="" style="background: var(--rtf-card); padding: 40px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10);">
                <input type="hidden" name="action" value="login">
                <?php wp_nonce_field('rtf_login'); ?>
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-text);"><?php echo esc_html($txt['username']); ?> eller Email</label>
                    <input type="text" name="username" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;" placeholder="Indtast brugernavn eller email">
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-text);"><?php echo esc_html($txt['password']); ?></label>
                    <input type="password" name="password" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; font-size: 1.1em;">
                    <?php echo esc_html($txt['login_btn']); ?>
                </button>
            </form>
        </div>

        <!-- REGISTER FORM -->
        <div id="register-form" class="auth-form" style="display: none;">
            <form method="POST" action="" style="background: var(--rtf-card); padding: 40px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10);">
                <input type="hidden" name="action" value="register">
                <?php wp_nonce_field('rtf_register'); ?>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-text);"><?php echo esc_html($txt['username']); ?></label>
                    <input type="text" name="username" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-text);"><?php echo esc_html($txt['email']); ?></label>
                    <input type="email" name="email" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-text);"><?php echo esc_html($txt['password']); ?></label>
                    <input type="password" name="password" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-text);"><?php echo esc_html($txt['full_name']); ?></label>
                    <input type="text" name="full_name" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-text);"><?php echo esc_html($txt['birthday']); ?></label>
                    <input type="date" name="birthday" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-text);"><?php echo esc_html($txt['phone']); ?></label>
                    <input type="tel" name="phone" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;" placeholder="+45 12 34 56 78">
                    <small style="display: block; margin-top: 8px; padding: 12px; background: #e0f2fe; border-left: 3px solid #2563eb; border-radius: 4px; color: #1e3a8a; font-size: 0.85em;">
                        <strong>🔒 Privat:</strong> <?php echo esc_html($txt['phone_privacy']); ?>
                    </small>
                </div>

                <!-- NEW: Case Type -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-text);"><?php echo $lang === 'da' ? 'Sagstype' : 'Ärendetyp'; ?></label>
                    <select name="case_type" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em; background: white;">
                        <option value=""><?php echo $lang === 'da' ? 'Vælg sagstype' : 'Välj ärendetyp'; ?></option>
                        <option value="custody"><?php echo $lang === 'da' ? 'Forældremyndighed' : 'Vårdnad'; ?></option>
                        <option value="visitation"><?php echo $lang === 'da' ? 'Samvær' : 'Umgänge'; ?></option>
                        <option value="placement"><?php echo $lang === 'da' ? 'Anbringelse' : 'Placering'; ?></option>
                        <option value="disability"><?php echo $lang === 'da' ? 'Handicap' : 'Funktionsnedsättning'; ?></option>
                        <option value="jobcenter"><?php echo $lang === 'da' ? 'Jobcenter' : 'Arbetsförmedling'; ?></option>
                        <option value="pension"><?php echo $lang === 'da' ? 'Førtidspension' : 'Förtidspension'; ?></option>
                        <option value="divorce"><?php echo $lang === 'da' ? 'Skilsmisse' : 'Skilsmässa'; ?></option>
                        <option value="support"><?php echo $lang === 'da' ? 'Børnebidrag' : 'Barnbidrag'; ?></option>
                        <option value="other"><?php echo $lang === 'da' ? 'Andet' : 'Annat'; ?></option>
                    </select>
                </div>

                <!-- NEW: Age -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-text);"><?php echo $lang === 'da' ? 'Alder' : 'Ålder'; ?></label>
                    <input type="number" name="age" min="18" max="120" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                </div>

                <!-- NEW: Bio -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-text);"><?php echo $lang === 'da' ? 'Kort biografi (valgfrit)' : 'Kort biografi (valfritt)'; ?></label>
                    <textarea name="bio" rows="3" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em; resize: vertical;" placeholder="<?php echo $lang === 'da' ? 'Fortæl lidt om dig selv...' : 'Berätta lite om dig själv...'; ?>"></textarea>
                </div>

                <!-- MULTI-LANGUAGE: Language Selection -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-text);">
                        <?php echo $lang === 'da' ? 'Vælg sprog / Land' : ($lang === 'sv' ? 'Välj språk / Land' : 'Choose Language / Country'); ?>
                    </label>
                    <select name="language_preference" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em; background: white;">
                        <option value="da_DK"><?php echo $lang === 'da' ? 'Dansk (Danmark)' : ($lang === 'sv' ? 'Danska (Danmark)' : 'Danish (Denmark)'); ?></option>
                        <option value="sv_SE"><?php echo $lang === 'da' ? 'Svenska (Sverige)' : ($lang === 'sv' ? 'Svenska (Sverige)' : 'Swedish (Sweden)'); ?></option>
                        <option value="en_US"><?php echo $lang === 'da' ? 'English (International)' : ($lang === 'sv' ? 'Engelska (Internationell)' : 'English (International)'); ?></option>
                    </select>
                    <small style="display: block; margin-top: 8px; color: #1e3a8a; font-size: 0.85em; padding: 10px; background: #fef3c7; border-left: 3px solid #fbbf24; border-radius: 4px;">
                        <strong>Alle priser er i DKK (danske kroner)</strong>
                    </small>
                </div>

                <div class="gdpr-notice" style="background: #e0f2fe; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-size: 0.9em; color: var(--rtf-text);">
                    <?php echo esc_html($txt['gdpr_notice']); ?>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; font-size: 1.1em;">
                    <?php echo esc_html($txt['register_btn']); ?>
                </button>
            </form>
        </div>

    </div>
</main>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tab = this.getAttribute('data-tab');
        
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('active');
            b.style.borderBottom = 'none';
            b.style.color = 'var(--rtf-muted)';
        });
        
        this.classList.add('active');
        this.style.borderBottom = '3px solid #2563eb';
        this.style.color = 'var(--rtf-text)';
        
        document.querySelectorAll('.auth-form').forEach(form => {
            form.style.display = 'none';
        });
        
        document.getElementById(tab + '-form').style.display = 'block';
    });
});
</script>

<?php get_footer(); ?>
