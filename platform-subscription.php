<?php
/**
 * Template Name: Platform Subscription (Stripe)
 */

get_header();
$lang = rtf_get_lang();

if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth/?lang=' . $lang));
    exit;
}

$current_user = rtf_get_current_user();

$t = array(
    'da' => array(
        'title' => 'Administrer Abonnement',
        'current_status' => 'Nuværende Status',
        'active' => 'Aktiv - Tak fordi du støtter os!',
        'inactive' => 'Inaktiv - Abonner for fuld adgang',
        'price' => '49 DKK/måned',
        'features_title' => 'Hvad får du adgang til:',
        'feature1' => 'Fuld adgang til social væg og fællesskab',
        'feature2' => 'Juridisk hjælp og klage generator',
        'feature3' => 'Kate AI assistent til spørgsmål',
        'feature4' => 'Forum og nyheder',
        'feature5' => 'Sikker dokumentdeling',
        'feature6' => 'Billede galleri med GDPR funktioner',
        'subscribe_btn' => 'Start Abonnement',
        'manage_btn' => 'Administrer via Stripe',
        'cancel_note' => 'Du kan til enhver tid afmelde dit abonnement',
        'test_mode' => 'TEST MODE - Ingen ægte betalinger!',
    ),
    'sv' => array(
        'title' => 'Hantera Prenumeration',
        'current_status' => 'Nuvarande Status',
        'active' => 'Aktiv - Tack för ditt stöd!',
        'inactive' => 'Inaktiv - Prenumerera för full åtkomst',
        'price' => '49 DKK/månad',
        'features_title' => 'Vad får du tillgång till:',
        'feature1' => 'Full tillgång till social vägg och samfund',
        'feature2' => 'Juridisk hjälp och klagomålsgenerator',
        'feature3' => 'Kate AI assistent för frågor',
        'feature4' => 'Forum och nyheter',
        'feature5' => 'Säker dokumentdelning',
        'feature6' => 'Bildgalleri med GDPR-funktioner',
        'subscribe_btn' => 'Starta Prenumeration',
        'manage_btn' => 'Hantera via Stripe',
        'cancel_note' => 'Du kan avsluta din prenumeration när som helst',
        'test_mode' => 'TEST-LÄGE - Inga riktiga betalningar!',
    ),
    'en' => array(
        'title' => 'Manage Subscription',
        'current_status' => 'Current Status',
        'active' => 'Active - Thank you for your support!',
        'inactive' => 'Inactive - Subscribe for full access',
        'price' => '49 DKK/month',
        'features_title' => 'What you get access to:',
        'feature1' => 'Full access to social wall and community',
        'feature2' => 'Legal help and complaint generator',
        'feature3' => 'Kate AI assistant for questions',
        'feature4' => 'Forum and news',
        'feature5' => 'Secure document sharing',
        'feature6' => 'Photo gallery with GDPR features',
        'subscribe_btn' => 'Start Subscription',
        'manage_btn' => 'Manage via Stripe',
        'cancel_note' => 'You can cancel your subscription at any time',
        'test_mode' => 'TEST MODE - No real payments!',
    )
);
$txt = $t[$lang];

// Load Stripe library
require_once(__DIR__ . '/stripe-php-13.18.0/init.php');

// Handle Stripe Checkout - LIVE IMPLEMENTATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'subscribe') {
    
    try {
        \Stripe\Stripe::setApiKey(RTF_STRIPE_SECRET_KEY);
        
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'subscription',
            'customer_email' => $current_user->email,
            'line_items' => [[
                'price' => RTF_STRIPE_PRICE_ID,
                'quantity' => 1,
            ]],
            'success_url' => home_url('/platform-profil/?lang=' . $lang . '&subscribed=1'),
            'cancel_url' => home_url('/platform-subscription/?lang=' . $lang . '&canceled=1'),
            'metadata' => [
                'user_id' => $current_user->id,
                'username' => $current_user->username
            ]
        ]);
        
        // Redirect til Stripe Checkout
        wp_redirect($checkout_session->url);
        exit;
        
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $error_message = $e->getMessage();
        wp_redirect(home_url('/platform-subscription/?lang=' . $lang . '&error=' . urlencode($error_message)));
        exit;
    }
}
?>

<main class="platform-subscription">
    <div class="container" style="max-width: 800px; margin: 60px auto; padding: 20px;">
        
        <h1 style="text-align: center; margin-bottom: 40px; color: var(--rtf-text);"><?php echo esc_html($txt['title']); ?></h1>

        <div class="subscription-card" style="background: var(--rtf-card); padding: 50px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10);">
            
            <div class="status-section" style="text-align: center; margin-bottom: 40px;">
                <h3 style="color: var(--rtf-muted); margin-bottom: 15px;"><?php echo esc_html($txt['current_status']); ?></h3>
                <div class="status-badge" style="display: inline-block; padding: 15px 40px; background: <?php echo $current_user->subscription_status === 'active' ? 'linear-gradient(135deg, #38bdf8, #0ea5e9)' : '#e0f2fe'; ?>; color: <?php echo $current_user->subscription_status === 'active' ? '#ffffff' : '#1e3a8a'; ?>; border-radius: 30px; font-size: 1.2em; font-weight: 600;">
                    <?php echo esc_html($current_user->subscription_status === 'active' ? $txt['active'] : $txt['inactive']); ?>
                </div>
                <?php if ($current_user->subscription_status === 'active' && $current_user->subscription_end_date): ?>
                    <p style="margin-top: 15px; color: var(--rtf-muted);">
                        <?php echo $lang === 'da' ? 'Næste fornyelse: ' : ($lang === 'sv' ? 'Nästa förnyelse: ' : 'Next renewal: '); ?>
                        <?php echo rtf_format_date($current_user->subscription_end_date); ?>
                    </p>
                <?php endif; ?>
            </div>

            <?php if ($current_user->subscription_status !== 'active'): ?>
                
                <div class="pricing-section" style="text-align: center; margin-bottom: 40px;">
                    <div class="price" style="font-size: 3em; font-weight: bold; color: #2563eb; margin-bottom: 20px;"><?php echo esc_html($txt['price']); ?></div>
                </div>

                <div class="features-section" style="margin-bottom: 40px;">
                    <h3 style="text-align: center; margin-bottom: 30px; color: var(--rtf-text);"><?php echo esc_html($txt['features_title']); ?></h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 15px; margin-bottom: 10px; background: #e0f2fe; border-radius: 8px; color: var(--rtf-text);">✓ <?php echo esc_html($txt['feature1']); ?></li>
                        <li style="padding: 15px; margin-bottom: 10px; background: #e0f2fe; border-radius: 8px; color: var(--rtf-text);">✓ <?php echo esc_html($txt['feature2']); ?></li>
                        <li style="padding: 15px; margin-bottom: 10px; background: #e0f2fe; border-radius: 8px; color: var(--rtf-text);">✓ <?php echo esc_html($txt['feature3']); ?></li>
                        <li style="padding: 15px; margin-bottom: 10px; background: #e0f2fe; border-radius: 8px; color: var(--rtf-text);">✓ <?php echo esc_html($txt['feature4']); ?></li>
                        <li style="padding: 15px; margin-bottom: 10px; background: #e0f2fe; border-radius: 8px; color: var(--rtf-text);">✓ <?php echo esc_html($txt['feature5']); ?></li>
                        <li style="padding: 15px; margin-bottom: 10px; background: #e0f2fe; border-radius: 8px; color: var(--rtf-text);">✓ <?php echo esc_html($txt['feature6']); ?></li>
                    </ul>
                </div>

                <form method="POST" action="" style="text-align: center; margin-top: 30px;">
                    <input type="hidden" name="action" value="subscribe">
                    <button type="submit" class="btn-primary" style="padding: 20px 60px; font-size: 1.2em;">
                        <?php echo esc_html($txt['subscribe_btn']); ?>
                    </button>
                    <p style="margin-top: 20px; color: var(--rtf-muted); font-size: 0.9em;"><?php echo esc_html($txt['cancel_note']); ?></p>
                </form>

            <?php else: ?>
                
                <div style="text-align: center;">
                    <p style="margin-bottom: 30px; font-size: 1.1em; color: var(--rtf-text);">
                        <?php echo $lang === 'da' ? 'Dit abonnement er aktivt. Du har fuld adgang til alle funktioner!' : ($lang === 'sv' ? 'Din prenumeration är aktiv. Du har full tillgång till alla funktioner!' : 'Your subscription is active. You have full access to all features!'); ?>
                    </p>
                    <a href="https://billing.stripe.com/p/login/test_XXXXXXXXXX" target="_blank" class="btn-secondary" style="display: inline-block; padding: 15px 40px; text-decoration: none;">
                        <?php echo esc_html($txt['manage_btn']); ?>
                    </a>
                    <p style="margin-top: 20px; color: var(--rtf-muted); font-size: 0.9em;">
                        <?php echo $lang === 'da' ? 'Administrer betaling, faktura og afmelding via Stripe' : ($lang === 'sv' ? 'Hantera betalning, faktura och avregistrering via Stripe' : 'Manage payment, invoices and cancellation via Stripe'); ?>
                    </p>
                </div>

            <?php endif; ?>

        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo home_url('/platform-profil/?lang=' . $lang); ?>" style="color: #2563eb; text-decoration: none;">
                ← <?php echo $lang === 'da' ? 'Tilbage til profil' : ($lang === 'sv' ? 'Tillbaka till profil' : 'Back to profile'); ?>
            </a>
        </div>

    </div>
</main>

<?php get_footer(); ?>
