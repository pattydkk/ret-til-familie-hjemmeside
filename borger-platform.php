

<?php
/**
 * Template Name: Borger Platform Landing
 */

get_header();
$lang = rtf_get_lang();
$logged_in = rtf_is_logged_in();

if ($logged_in) {
    wp_redirect(home_url('/platform-profil/?lang=' . $lang));
    exit;
}

$t = array(
    'da' => array(
        'title' => 'Velkommen til Borger Platformen',
        'subtitle' => 'Et trygt fællesskab for forældre der kæmper for deres børn',
        'desc' => 'Få adgang til juridisk vejledning, støtte fra andre forældre, og værktøjer der hjælper dig gennem systemet.',
        'features' => 'Funktioner',
        'feature1' => 'Social Væg',
        'feature1_desc' => 'Del dine oplevelser og få støtte fra fællesskabet',
        'feature2' => 'Juridisk Hjælp',
        'feature2_desc' => 'Få adgang til sagshjælp og klage generator',
        'feature3' => 'Kate AI Assistent',
        'feature3_desc' => 'Chat med vores AI der forstår familieretlige spørgsmål',
        'feature4' => 'Sikker Dokumentdeling',
        'feature4_desc' => 'Upload og del dokumenter med fuld GDPR beskyttelse',
        'feature5' => 'Forum & Nyheder',
        'feature5_desc' => 'Deltag i diskussioner og hold dig opdateret',
        'feature6' => 'Billede Galleri',
        'feature6_desc' => 'Del billeder med automatisk ansigts-sløring',
        'pricing' => 'Pris',
        'price' => '49 DKK/måned',
        'price_desc' => 'Fuld adgang til alle funktioner og fællesskabet',
        'cta' => 'Kom i Gang',
        'login' => 'Allerede medlem? Log ind her'
    ),
    'sv' => array(
        'title' => 'Välkommen till Medborgarplattformen',
        'subtitle' => 'Ett tryggt samfund för föräldrar som kämpar för sina barn',
        'desc' => 'Få tillgång till juridisk vägledning, stöd från andra föräldrar och verktyg som hjälper dig genom systemet.',
        'features' => 'Funktioner',
        'feature1' => 'Social Vägg',
        'feature1_desc' => 'Dela dina upplevelser och få stöd från samfundet',
        'feature2' => 'Juridisk Hjälp',
        'feature2_desc' => 'Få tillgång till ärendehjälp och klagomålsgenerator',
        'feature3' => 'Kate AI Assistent',
        'feature3_desc' => 'Chatta med vår AI som förstår familjerättsliga frågor',
        'feature4' => 'Säker Dokumentdelning',
        'feature4_desc' => 'Ladda upp och dela dokument med fullt GDPR-skydd',
        'feature5' => 'Forum & Nyheter',
        'feature5_desc' => 'Delta i diskussioner och håll dig uppdaterad',
        'feature6' => 'Bildgalleri',
        'feature6_desc' => 'Dela bilder med automatisk ansiktssuddning',
        'pricing' => 'Pris',
        'price' => '49 DKK/månad',
        'price_desc' => 'Full tillgång till alla funktioner och samfundet',
        'cta' => 'Kom Igång',
        'login' => 'Redan medlem? Logga in här'
    ),
    'en' => array(
        'title' => 'Welcome to the Citizen Platform',
        'subtitle' => 'A safe community for parents fighting for their children',
        'desc' => 'Get access to legal guidance, support from other parents, and tools that help you through the system.',
        'features' => 'Features',
        'feature1' => 'Social Wall',
        'feature1_desc' => 'Share your experiences and get support from the community',
        'feature2' => 'Legal Help',
        'feature2_desc' => 'Access case help and complaint generator',
        'feature3' => 'Kate AI Assistant',
        'feature3_desc' => 'Chat with our AI that understands family law questions',
        'feature4' => 'Secure Document Sharing',
        'feature4_desc' => 'Upload and share documents with full GDPR protection',
        'feature5' => 'Forum & News',
        'feature5_desc' => 'Participate in discussions and stay updated',
        'feature6' => 'Photo Gallery',
        'feature6_desc' => 'Share photos with automatic face blurring',
        'pricing' => 'Pricing',
        'price' => '49 DKK/month',
        'price_desc' => 'Full access to all features and the community',
        'cta' => 'Get Started',
        'login' => 'Already a member? Log in here'
    )
);
$txt = $t[$lang];
?>

<main class="platform-landing">
    <div class="hero-section" style="padding: 100px 20px; text-align: center; background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 50%, #fefce8 100%);">
        <div class="container" style="max-width: 900px; margin: 0 auto;">
            <h1 style="font-size: 3em; margin-bottom: 20px; color: var(--rtf-text);"><?php echo esc_html($txt['title']); ?></h1>
            <p style="font-size: 1.5em; margin-bottom: 15px; color: var(--rtf-muted);"><?php echo esc_html($txt['subtitle']); ?></p>
            <p style="font-size: 1.2em; margin-bottom: 40px; color: var(--rtf-text); line-height: 1.6;"><?php echo esc_html($txt['desc']); ?></p>
            <a href="<?php echo esc_url(home_url('/platform-auth/?lang=' . $lang)); ?>" class="btn-primary" style="display: inline-block; padding: 18px 40px; font-size: 1.2em;"><?php echo esc_html($txt['cta']); ?></a>
        </div>
    </div>

    <div class="features-section" style="padding: 80px 20px; background: var(--rtf-card);">
        <div class="container" style="max-width: 1200px; margin: 0 auto;">
            <h2 style="text-align: center; font-size: 2.5em; margin-bottom: 60px; color: var(--rtf-text);"><?php echo esc_html($txt['features']); ?></h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                
                <div class="feature-card" style="background: var(--rtf-card); padding: 30px; border-radius: 16px; border: 1px solid var(--rtf-border); box-shadow: 0 8px 20px rgba(15,23,42,0.06);">
                    <div class="feature-icon" style="width: 60px; height: 60px; border-radius: 12px; background: linear-gradient(135deg, #dbeafe, #93c5fd); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <svg style="width: 32px; height: 32px; fill: #1e3a8a;" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                    </div>
                    <h3 style="font-size: 1.5em; margin-bottom: 15px; color: var(--rtf-text);"><?php echo esc_html($txt['feature1']); ?></h3>
                    <p style="color: var(--rtf-muted); line-height: 1.6;"><?php echo esc_html($txt['feature1_desc']); ?></p>
                </div>

                <div class="feature-card" style="background: var(--rtf-card); padding: 30px; border-radius: 16px; border: 1px solid var(--rtf-border); box-shadow: 0 8px 20px rgba(15,23,42,0.06);">
                    <div class="feature-icon" style="width: 60px; height: 60px; border-radius: 12px; background: linear-gradient(135deg, #dbeafe, #93c5fd); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <svg style="width: 32px; height: 32px; fill: #1e3a8a;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    </div>
                    <h3 style="font-size: 1.5em; margin-bottom: 15px; color: var(--rtf-text);"><?php echo esc_html($txt['feature2']); ?></h3>
                    <p style="color: var(--rtf-muted); line-height: 1.6;"><?php echo esc_html($txt['feature2_desc']); ?></p>
                </div>

                <div class="feature-card" style="background: var(--rtf-card); padding: 30px; border-radius: 16px; border: 1px solid var(--rtf-border); box-shadow: 0 8px 20px rgba(15,23,42,0.06);">
                    <div class="feature-icon" style="width: 60px; height: 60px; border-radius: 12px; background: linear-gradient(135deg, #dbeafe, #93c5fd); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <svg style="width: 32px; height: 32px; fill: #1e3a8a;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                    </div>
                    <h3 style="font-size: 1.5em; margin-bottom: 15px; color: var(--rtf-text);"><?php echo esc_html($txt['feature3']); ?></h3>
                    <p style="color: var(--rtf-muted); line-height: 1.6;"><?php echo esc_html($txt['feature3_desc']); ?></p>
                </div>

                <div class="feature-card" style="background: var(--rtf-card); padding: 30px; border-radius: 16px; border: 1px solid var(--rtf-border); box-shadow: 0 8px 20px rgba(15,23,42,0.06);">
                    <div class="feature-icon" style="width: 60px; height: 60px; border-radius: 12px; background: linear-gradient(135deg, #dbeafe, #93c5fd); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <svg style="width: 32px; height: 32px; fill: #1e3a8a;" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/></svg>
                    </div>
                    <h3 style="font-size: 1.5em; margin-bottom: 15px; color: var(--rtf-text);"><?php echo esc_html($txt['feature4']); ?></h3>
                    <p style="color: var(--rtf-muted); line-height: 1.6;"><?php echo esc_html($txt['feature4_desc']); ?></p>
                </div>

                <div class="feature-card" style="background: var(--rtf-card); padding: 30px; border-radius: 16px; border: 1px solid var(--rtf-border); box-shadow: 0 8px 20px rgba(15,23,42,0.06);">
                    <div class="feature-icon" style="width: 60px; height: 60px; border-radius: 12px; background: linear-gradient(135deg, #dbeafe, #93c5fd); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <svg style="width: 32px; height: 32px; fill: #1e3a8a;" viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg>
                    </div>
                    <h3 style="font-size: 1.5em; margin-bottom: 15px; color: var(--rtf-text);"><?php echo esc_html($txt['feature5']); ?></h3>
                    <p style="color: var(--rtf-muted); line-height: 1.6;"><?php echo esc_html($txt['feature5_desc']); ?></p>
                </div>

                <div class="feature-card" style="background: var(--rtf-card); padding: 30px; border-radius: 16px; border: 1px solid var(--rtf-border); box-shadow: 0 8px 20px rgba(15,23,42,0.06);">
                    <div class="feature-icon" style="width: 60px; height: 60px; border-radius: 12px; background: linear-gradient(135deg, #dbeafe, #93c5fd); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <svg style="width: 32px; height: 32px; fill: #1e3a8a;" viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                    </div>
                    <h3 style="font-size: 1.5em; margin-bottom: 15px; color: var(--rtf-text);"><?php echo esc_html($txt['feature6']); ?></h3>
                    <p style="color: var(--rtf-muted); line-height: 1.6;"><?php echo esc_html($txt['feature6_desc']); ?></p>
                </div>

            </div>
        </div>
    </div>

    <div class="pricing-section" style="padding: 80px 20px; background: #eef2ff; text-align: center;">
        <div class="container" style="max-width: 600px; margin: 0 auto;">
            <h2 style="font-size: 2.5em; margin-bottom: 30px; color: var(--rtf-text);"><?php echo esc_html($txt['pricing']); ?></h2>
            <div class="pricing-card" style="background: var(--rtf-card); padding: 50px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10);">
                <div class="price" style="font-size: 3em; font-weight: bold; color: #2563eb; margin-bottom: 20px;"><?php echo esc_html($txt['price']); ?></div>
                <p style="font-size: 1.2em; color: var(--rtf-muted); margin-bottom: 40px;"><?php echo esc_html($txt['price_desc']); ?></p>
                <a href="<?php echo esc_url(home_url('/platform-auth/?lang=' . $lang)); ?>" class="btn-primary" style="display: inline-block; padding: 18px 50px; font-size: 1.2em;"><?php echo esc_html($txt['cta']); ?></a>
                <p style="margin-top: 30px; color: var(--rtf-muted);">
                    <a href="<?php echo esc_url(home_url('/platform-auth/?lang=' . $lang)); ?>" style="color: #2563eb; text-decoration: none;"><?php echo esc_html($txt['login']); ?></a>
                </p>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
