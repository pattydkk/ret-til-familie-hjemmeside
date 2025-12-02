<?php
/**
 * Template Name: Platform - Kate AI
 */

if (!session_id()) session_start();

if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth'));
    exit;
}

get_header();
?>

<style>
.platform-layout {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 2rem;
    max-width: 1400px;
    margin: 2rem auto;
    padding: 0 1.5rem;
}

.platform-sidebar {
    background: white;
    border: 1px solid #dbeafe;
    border-radius: 18px;
    padding: 1.5rem;
    height: fit-content;
    position: sticky;
    top: 80px;
}

.platform-sidebar h3 {
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
    color: #2563eb;
}

.platform-nav {
    list-style: none;
    padding: 0;
    margin: 0;
}

.platform-nav li {
    margin-bottom: 0.5rem;
}

.platform-nav a {
    display: block;
    padding: 0.625rem 0.875rem;
    border-radius: 8px;
    color: #475569;
    text-decoration: none;
    transition: all 0.2s ease;
}

.platform-nav a:hover,
.platform-nav a.active {
    background: #e0f2fe;
    color: #2563eb;
}

.platform-content {
    min-height: 600px;
}

.kate-intro {
    background: white;
    border: 1px solid #dbeafe;
    border-radius: 18px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.kate-intro h1 {
    margin: 0 0 1rem 0;
    font-size: 1.8rem;
    color: #0f172a;
}

.kate-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin: 1.5rem 0;
}

.feature-card {
    padding: 1rem;
    background: #f9fafb;
    border: 1px solid #dbeafe;
    border-radius: 12px;
}

.feature-card h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    color: #2563eb;
}

.feature-card p {
    margin: 0;
    font-size: 0.85rem;
    color: #64748b;
}

@media (max-width: 768px) {
    .platform-layout {
        grid-template-columns: 1fr;
    }
    
    .platform-sidebar {
        position: static;
    }
}
</style>

<div class="platform-layout">
    <aside class="platform-sidebar">
        <h3>📱 Platform</h3>
        <ul class="platform-nav">
            <li><a href="<?php echo home_url('/platform-profil'); ?>">👤 Profil</a></li>
            <li><a href="<?php echo home_url('/platform-vaeg'); ?>">📝 Væg</a></li>
            <li><a href="<?php echo home_url('/platform-billeder'); ?>">📷 Billeder</a></li>
            <li><a href="<?php echo home_url('/platform-dokumenter'); ?>">📄 Dokumenter</a></li>
            <li><a href="<?php echo home_url('/platform-venner'); ?>">👥 Venner</a></li>
            <li><a href="<?php echo home_url('/platform-forum'); ?>">💬 Forum</a></li>
            <li><a href="<?php echo home_url('/platform-nyheder'); ?>">📰 Nyheder</a></li>
            <li><a href="<?php echo home_url('/platform-sagshjaelp'); ?>">⚖️ Sagshjælp</a></li>
            <li><a href="<?php echo home_url('/platform-kate-ai'); ?>" class="active">🤖 Kate AI</a></li>
            <li><a href="<?php echo home_url('/platform-indstillinger'); ?>">⚙️ Indstillinger</a></li>
        </ul>
    </aside>
    
    <main class="platform-content">
        <div class="kate-intro">
            <h1>🤖 Kate - Din AI Assistent</h1>
            <p style="color: #64748b; font-size: 1rem; line-height: 1.6;">
                Kate er din personlige AI-assistent, der kan hjælpe dig med juridiske spørgsmål, 
                analysere dokumenter og guide dig gennem komplekse sager inden for familie- og socialret.
            </p>
            
            <div class="kate-features">
                <div class="feature-card">
                    <h3>💬 Spørg om alt</h3>
                    <p>Stil spørgsmål om Barnets Lov, klager, aktindsigt og meget mere</p>
                </div>
                <div class="feature-card">
                    <h3>📄 Dokument analyse</h3>
                    <p>Få analyseret afgørelser, handleplaner og undersøgelser</p>
                </div>
                <div class="feature-card">
                    <h3>⚖️ Juridisk vejledning</h3>
                    <p>Få konkrete lovhenvisninger og trin-for-trin guides</p>
                </div>
                <div class="feature-card">
                    <h3>🎯 98% præcision</h3>
                    <p>Baseret på dansk lovgivning og socialfaglig praksis</p>
                </div>
            </div>
        </div>
        
        <?php echo do_shortcode('[kate_ai_assistant title="Kate - Din AI Assistent" theme="pastel-blue"]'); ?>
    </main>
</div>

<?php get_footer(); ?>
