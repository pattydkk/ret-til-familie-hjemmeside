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
    
    global $rtf_user_system;
    
    $username_or_email = sanitize_text_field($_POST['username']);
    $password = $_POST['password'];
    
    // Authenticate using robust system
    $result = $rtf_user_system->authenticate($username_or_email, $password);
    
    if ($result['success']) {
        // Login successful!
        $user = $result['user'];
        
        // Create secure session
        session_regenerate_id(true);
        $_SESSION['rtf_user_id'] = $user->id;
        $_SESSION['rtf_username'] = $user->username;
        
        // Redirect to profile
        wp_redirect(home_url('/platform-profil/?lang=' . $lang));
        exit;
        
    } else {
        // Login failed
        $error_msg = $result['error'];
        
        // Translate error
        if ($lang === 'da') {
            $error = 'Forkert brugernavn/email eller adgangskode';
        } elseif ($lang === 'sv') {
            $error = 'Fel användarnamn/e-post eller lösenord';
        } else {
            $error = 'Wrong username/email or password';
        }
    }
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    // CSRF protection
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'rtf_register')) {
        wp_die('Security check failed');
    }
    
    global $rtf_user_system;
    
    // Prepare registration data
    $registration_data = [
        'username' => $_POST['username'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'full_name' => $_POST['full_name'] ?? '',
        'birthday' => $_POST['birthday'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'bio' => $_POST['bio'] ?? '',
        'language_preference' => $_POST['language_preference'] ?? 'da_DK',
        'is_admin' => isset($_POST['is_admin']) ? intval($_POST['is_admin']) : 0
    ];
    
    // Register user using robust system
    $result = $rtf_user_system->register($registration_data);
    
    if (!$result['success']) {
        // Registration failed - show error
        $error = $result['error'];
        
        // Translate common errors
        if ($lang === 'da') {
            if (strpos($error, 'Username already exists') !== false) $error = 'Brugernavn er allerede i brug';
            elseif (strpos($error, 'Email already registered') !== false) $error = 'Email er allerede registreret';
            elseif (strpos($error, 'Invalid email') !== false) $error = 'Ugyldig email adresse';
            elseif (strpos($error, 'Password must be') !== false) $error = 'Adgangskode skal være mindst 8 tegn';
            elseif (strpos($error, 'Username must be') !== false) $error = 'Brugernavn skal være 3-50 tegn (kun bogstaver, tal, underscore)';
        } elseif ($lang === 'sv') {
            if (strpos($error, 'Username already exists') !== false) $error = 'Användarnamn används redan';
            elseif (strpos($error, 'Email already registered') !== false) $error = 'E-post är redan registrerad';
            elseif (strpos($error, 'Invalid email') !== false) $error = 'Ogiltig e-postadress';
            elseif (strpos($error, 'Password must be') !== false) $error = 'Lösenord måste vara minst 8 tecken';
            elseif (strpos($error, 'Username must be') !== false) $error = 'Användarnamn måste vara 3-50 tecken (endast bokstäver, siffror, understreck)';
        }
        
        error_log('RTF Registration Failed: ' . $result['error'] . ' for ' . $registration_data['email']);
        
    } else {
        // Registration successful!
        $user_id = $result['user_id'];
        $email = $result['email'];
        $username = $result['username'];
        
        // Create secure session
        session_regenerate_id(true);
        $_SESSION['rtf_user_id'] = $user_id;
        $_SESSION['rtf_username'] = $username;
        
        error_log("RTF Registration Success: User $username (ID: $user_id, Email: $email) created");
        
        // Redirect to Stripe checkout
        require_once(__DIR__ . '/stripe-php-13.18.0/init.php');
        \Stripe\Stripe::setApiKey(RTF_STRIPE_SECRET_KEY);
        
        try {
            // Create Stripe checkout session
            $checkout_session = \Stripe\Stripe\Checkout\Session::create([
                'success_url' => home_url('/platform-profil/?lang=' . $lang . '&payment=success'),
                'cancel_url' => home_url('/platform-subscription/?lang=' . $lang . '&payment=cancelled'),
                'payment_method_types' => ['card'],
                'mode' => 'subscription',
                'customer_email' => $email, // CRITICAL: Email matches database
                'client_reference_id' => (string)$user_id,
                'line_items' => [[
                    'price' => RTF_STRIPE_PRICE_ID,
                    'quantity' => 1
                ]],
                'subscription_data' => [
                    'metadata' => [
                        'user_id' => (string)$user_id,
                        'username' => $username,
                        'email' => $email,
                        'rtf_platform' => 'true'
                    ]
                ]
            ]);
            
            error_log("RTF Stripe: Checkout session created for user $user_id - Session ID: " . $checkout_session->id);
            error_log("RTF Stripe: Customer will be created on payment completion");
                
            // Redirect to Stripe checkout
            wp_redirect($checkout_session->url);
            exit;
                
        } catch (\Exception $e) {
            error_log('RTF Stripe Checkout Error: ' . $e->getMessage());
            error_log('RTF Stripe Error Details: User ID ' . $user_id . ', Email: ' . $email);
            $error = $lang === 'da' ? 'Kunne ikke oprette betalingslink' : ($lang === 'sv' ? 'Kunde inte skapa betalningslänk' : 'Could not create payment link');
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

                <!-- Bio (Optional) -->
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
