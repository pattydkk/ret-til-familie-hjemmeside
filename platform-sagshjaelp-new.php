<?php
/**
 * Template Name: Platform - Sagshjælp (Legal Help)
 * Redesigned for ALL social services: Family, Jobcenter, Handicap, Elderly
 */

if (!session_id()) session_start();

if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth'));
    exit;
}

$user = rtf_get_current_user();
$lang = rtf_get_lang();

get_header();
?>

<style>
/* Platform Layout */
.platform-layout {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 2rem;
    max-width: 1400px;
    margin: 2rem auto;
    padding: 0 1.5rem;
}

.platform-content {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 2.5rem;
    margin-bottom: 2.5rem;
    color: white;
    text-align: center;
}

.hero-section h1 {
    margin: 0 0 1rem 0;
    font-size: 2.5rem;
    font-weight: 700;
}

.hero-section p {
    font-size: 1.1rem;
    margin: 0;
    opacity: 0.95;
}

/* Category Selection */
.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.category-card {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
}

.category-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(102, 126, 234, 0.3);
    border-color: #667eea;
}

.category-card.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
}

.category-icon {
    font-size: 3.5rem;
    margin-bottom: 1rem;
    display: block;
}

.category-card h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.3rem;
    font-weight: 700;
}

.category-card p {
    margin: 0;
    font-size: 0.9rem;
    opacity: 0.8;
}

/* Services Section */
.services-section {
    margin-bottom: 3rem;
}

.services-section h2 {
    color: #2563eb;
    margin: 0 0 1.5rem 0;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.service-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
}

.service-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.2s ease;
}

.service-card:hover {
    border-color: #667eea;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
}

.service-card h4 {
    margin: 0 0 0.75rem 0;
    color: #1e293b;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.service-card p {
    margin: 0 0 1rem 0;
    color: #64748b;
    font-size: 0.9rem;
    line-height: 1.6;
}

.service-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn-service {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #f1f5f9;
    color: #475569;
}

.btn-secondary:hover {
    background: #e2e8f0;
}

/* Info Boxes */
.info-box {
    background: #e0f2fe;
    border: 2px solid #2563eb;
    border-radius: 12px;
    padding: 1.5rem;
    margin: 2rem 0;
}

.info-box h3 {
    margin: 0 0 1rem 0;
    color: #2563eb;
    font-size: 1.2rem;
}

.warning-box {
    background: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 12px;
    padding: 1.5rem;
    margin: 2rem 0;
}

.warning-box h3 {
    margin: 0 0 1rem 0;
    color: #856404;
    font-size: 1.2rem;
}

/* Documentation Tips */
.tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
}

.tip-card {
    background: white;
    border-radius: 10px;
    padding: 1.25rem;
    border: 1px solid #e2e8f0;
}

.tip-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    display: block;
}

.tip-card strong {
    display: block;
    margin-bottom: 0.5rem;
    color: #1e293b;
}

.tip-card p {
    margin: 0;
    color: #64748b;
    font-size: 0.875rem;
    line-height: 1.5;
}

/* Tabs (for Kate AI integration) */
.tabs-nav {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid #e2e8f0;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 0.75rem 1.5rem;
    border: none;
    background: #f1f5f9;
    color: #475569;
    border-radius: 10px 10px 0 0;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.95rem;
}

.tab-btn:hover {
    background: #e2e8f0;
}

.tab-btn.active {
    background: #667eea;
    color: white;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .platform-layout {
        grid-template-columns: 1fr;
    }
    
    .category-grid {
        grid-template-columns: 1fr;
    }
    
    .service-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="platform-layout">
    <?php get_template_part('template-parts/platform-sidebar'); ?>
    
    <main class="platform-content">
        <!-- HERO -->
        <div class="hero-section">
            <h1>⚖️ <?php echo $lang === 'da' ? 'Sagshjælp' : ($lang === 'sv' ? 'Ärendehjälp' : 'Case Help'); ?></h1>
            <p><?php echo $lang === 'da' ? 'Professionel hjælp til alle sociale sager - familie, jobcenter, handicap og ældre' : ($lang === 'sv' ? 'Professionell hjälp för alla sociala ärenden - familj, jobbcenter, funktionsnedsättning och äldre' : 'Professional help for all social cases - family, job center, disability and elderly'); ?></p>
        </div>

        <!-- DISCLAIMER -->
        <div class="warning-box">
            <h3>⚠️ <?php echo $lang === 'da' ? 'Vigtigt: Vi er ikke advokater' : ($lang === 'sv' ? 'Viktigt: Vi är inte advokater' : 'Important: We are not lawyers'); ?></h3>
            <p style="margin: 0 0 1rem 0; color: #856404; line-height: 1.6;">
                <?php 
                if ($lang === 'da') {
                    echo 'Vi hjælper dig til bedre at hjælpe dig selv gennem selvstændig sagsopbygning, dokumentation og forståelse af din sag. Dette er IKKE juridisk rådgivning fra en advokat.';
                } elseif ($lang === 'sv') {
                    echo 'Vi hjälper dig att bättre hjälpa dig själv genom självständig ärendeuppbyggnad, dokumentation och förståelse av ditt ärende. Detta är INTE juridisk rådgivning från en advokat.';
                } else {
                    echo 'We help you better help yourself through independent case building, documentation and understanding of your case. This is NOT legal advice from a lawyer.';
                }
                ?>
            </p>
            <div style="background: white; border-radius: 8px; padding: 1rem; margin-top: 1rem;">
                <strong style="color: #2563eb;"><?php echo $lang === 'da' ? 'Har du brug for professionel juridisk hjælp?' : ($lang === 'sv' ? 'Behöver du professionell juridisk hjälp?' : 'Need professional legal help?'); ?></strong>
                <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem; color: #475569;">
                    <li><strong>🤝 <?php echo $lang === 'da' ? 'Konflikthåndtering' : ($lang === 'sv' ? 'Konflikthantering' : 'Conflict Mediation'); ?></strong></li>
                    <li><strong>📄 <?php echo $lang === 'da' ? 'Partsrepræsentation' : ($lang === 'sv' ? 'Partsrepresentation' : 'Party Representation'); ?></strong></li>
                    <li><strong>🔍 <?php echo $lang === 'da' ? 'Sagsgennemgang' : ($lang === 'sv' ? 'Ärendegranskning' : 'Case Review'); ?></strong></li>
                    <li style="margin-top: 0.5rem;">📧 Email: kontakt@rettiltifamilie.dk</li>
                    <li>📞 Telefon: +45 123 456 78</li>
                </ul>
            </div>
        </div>

        <!-- CATEGORY SELECTION -->
        <h2 style="color: #1e293b; margin: 0 0 1.5rem 0; font-size: 1.5rem;">📋 <?php echo $lang === 'da' ? 'Vælg din sagstype' : ($lang === 'sv' ? 'Välj din ärendetyp' : 'Select your case type'); ?></h2>
        
        <div class="category-grid">
            <div class="category-card active" onclick="showCategory('family')">
                <span class="category-icon">👨‍👩‍👧‍👦</span>
                <h3><?php echo $lang === 'da' ? 'Familie & Børn' : ($lang === 'sv' ? 'Familj & Barn' : 'Family & Children'); ?></h3>
                <p><?php echo $lang === 'da' ? 'Forældremyndighed, samvær, anbringelse' : ($lang === 'sv' ? 'Vårdnad, umgänge, placering' : 'Custody, visitation, placement'); ?></p>
            </div>
            
            <div class="category-card" onclick="showCategory('jobcenter')">
                <span class="category-icon">💼</span>
                <h3><?php echo $lang === 'da' ? 'Jobcenter & Økonomi' : ($lang === 'sv' ? 'Jobbcenter & Ekonomi' : 'Job Center & Economy'); ?></h3>
                <p><?php echo $lang === 'da' ? 'Kontanthjælp, dagpenge, ressourceforløb' : ($lang === 'sv' ? 'Kontanthjälp, dagpenning, resursprocess' : 'Cash benefits, unemployment, resource process'); ?></p>
            </div>
            
            <div class="category-card" onclick="showCategory('handicap')">
                <span class="category-icon">♿</span>
                <h3><?php echo $lang === 'da' ? 'Handicap & Funktionsnedsættelse' : ($lang === 'sv' ? 'Funktionsnedsättning' : 'Disability'); ?></h3>
                <p><?php echo $lang === 'da' ? 'Handicaptillæg, personlig hjælper, BPA' : ($lang === 'sv' ? 'Handikappersättning, personlig assistent, BPA' : 'Disability allowance, personal assistant, BPA'); ?></p>
            </div>
            
            <div class="category-card" onclick="showCategory('elderly')">
                <span class="category-icon">👵</span>
                <h3><?php echo $lang === 'da' ? 'Ældre & Omsorg' : ($lang === 'sv' ? 'Äldre & Omsorg' : 'Elderly & Care'); ?></h3>
                <p><?php echo $lang === 'da' ? 'Hjemmepleje, plejehjem, følgelæge' : ($lang === 'sv' ? 'Hemvård, äldreboende, läkare' : 'Home care, nursing home, doctor'); ?></p>
            </div>
        </div>

        <!-- FAMILY SERVICES -->
        <div id="category-family" class="services-section">
            <h2><span>👨‍👩‍👧‍👦</span> <?php echo $lang === 'da' ? 'Familie & Børn' : ($lang === 'sv' ? 'Familj & Barn' : 'Family & Children'); ?></h2>
            
            <div class="service-grid">
                <div class="service-card">
                    <h4>👨‍⚖️ <?php echo $lang === 'da' ? 'Forældremyndighed' : ($lang === 'sv' ? 'Vårdnad' : 'Custody'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Hjælp til sager om delt eller fuld forældremyndighed, ændring af forældremyndighed, og familieretlige afgørelser.' : ($lang === 'sv' ? 'Hjälp med ärenden om delad eller ensam vårdnad, ändring av vårdnad och familjerättsliga beslut.' : 'Help with cases about shared or sole custody, custody changes, and family law decisions.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
                
                <div class="service-card">
                    <h4>🏠 <?php echo $lang === 'da' ? 'Samvær & Brev/Besøgskontakt' : ($lang === 'sv' ? 'Umgänge' : 'Visitation'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Få hjælp til samværssager, overvåget samvær, nægtelse af samvær, og rettidig kontakt til dit barn.' : ($lang === 'sv' ? 'Få hjälp med umgängesärenden, övervakat umgänge, nekad umgänge och rättvis kontakt med ditt barn.' : 'Get help with visitation cases, supervised visitation, denied visitation, and proper contact with your child.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
                
                <div class="service-card">
                    <h4>🏥 <?php echo $lang === 'da' ? 'Anbringelse & Tvangsfjernelse' : ($lang === 'sv' ? 'Placering & Tvångsomhändertagande' : 'Placement & Forced Removal'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Juridisk vejledning til anbringelsessager, hjemgivelse, tvangsfjernelse efter serviceloven § 58.' : ($lang === 'sv' ? 'Juridisk vägledning för placeringsärenden, hemgivning, tvångsomhändertagande enligt socialtjänstlagen.' : 'Legal guidance for placement cases, return home, forced removal under the Social Services Act § 58.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
                
                <div class="service-card">
                    <h4>💰 <?php echo $lang === 'da' ? 'Børnebidrag & Underholdsbidrag' : ($lang === 'sv' ? 'Barnbidrag & Underhållsbidrag' : 'Child Support'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Hjælp til fastsættelse, ændring og inddrivelse af børnebidrag og underholdsbidrag.' : ($lang === 'sv' ? 'Hjälp med fastställande, ändring och indrivning av barnbidrag och underhållsbidrag.' : 'Help with determining, changing, and collecting child support and alimony.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- JOBCENTER SERVICES -->
        <div id="category-jobcenter" class="services-section" style="display: none;">
            <h2><span>💼</span> <?php echo $lang === 'da' ? 'Jobcenter & Økonomi' : ($lang === 'sv' ? 'Jobbcenter & Ekonomi' : 'Job Center & Economy'); ?></h2>
            
            <div class="service-grid">
                <div class="service-card">
                    <h4>💵 <?php echo $lang === 'da' ? 'Kontanthjælp' : ($lang === 'sv' ? 'Kontanthjälp' : 'Cash Benefits'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Hjælp til ansøgning, afslag, nedsættelse eller standsning af kontanthjælp. Vi guider dig gennem klagemuligheder.' : ($lang === 'sv' ? 'Hjälp med ansökan, avslag, sänkning eller inställning av kontanthjälp. Vi vägleder dig genom klagomöjligheter.' : 'Help with application, rejection, reduction or termination of cash benefits. We guide you through complaint options.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
                
                <div class="service-card">
                    <h4>🏥 <?php echo $lang === 'da' ? 'Sygedagpenge' : ($lang === 'sv' ? 'Sjukpenning' : 'Sick Leave Benefits'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Vejledning om sygedagpenge, forlængelse, afslag, og overgang til ressourceforløb eller førtidspension.' : ($lang === 'sv' ? 'Vägledning om sjukpenning, förlängning, avslag och övergång till resursprocess eller förtidspension.' : 'Guidance on sick leave benefits, extension, rejection, and transition to resource program or early retirement.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
                
                <div class="service-card">
                    <h4>🔄 <?php echo $lang === 'da' ? 'Ressourceforløb & Jobafklaringsforløb' : ($lang === 'sv' ? 'Resursprocess' : 'Resource Program'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Hjælp til ressourceforløb, jobafklaringsforløb, og klager over afgørelser om aktivering og arbejdsevnevurdering.' : ($lang === 'sv' ? 'Hjälp med resursprocess och klagomål över beslut om aktivering och arbetsförmågebedömning.' : 'Help with resource programs and complaints about decisions on activation and work capacity assessment.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
                
                <div class="service-card">
                    <h4>🛡️ <?php echo $lang === 'da' ? 'Førtidspension' : ($lang === 'sv' ? 'Förtidspension' : 'Disability Pension'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Ansøgning, afslag og klage over førtidspension. Vi hjælper med dokumentation og argumentation.' : ($lang === 'sv' ? 'Ansökan, avslag och klagomål över förtidspension. Vi hjälper med dokumentation och argumentation.' : 'Application, rejection, and complaint about disability pension. We help with documentation and arguments.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- HANDICAP SERVICES -->
        <div id="category-handicap" class="services-section" style="display: none;">
            <h2><span>♿</span> <?php echo $lang === 'da' ? 'Handicap & Funktionsnedsættelse' : ($lang === 'sv' ? 'Funktionsnedsättning' : 'Disability'); ?></h2>
            
            <div class="service-grid">
                <div class="service-card">
                    <h4>💰 <?php echo $lang === 'da' ? 'Handicaptillæg & Forhøjet Dagpenge' : ($lang === 'sv' ? 'Handikappersättning' : 'Disability Allowance'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Hjælp til ansøgning om handicaptillæg, forhøjet dagpenge, og klager over afslag på handicapkompensation.' : ($lang === 'sv' ? 'Hjälp med ansökan om handikappersättning och klagomål över avslag på handikappkompensation.' : 'Help with application for disability allowance and complaints about rejection of disability compensation.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
                
                <div class="service-card">
                    <h4>👤 <?php echo $lang === 'da' ? 'Personlig Hjælper & BPA' : ($lang === 'sv' ? 'Personlig Assistent & BPA' : 'Personal Assistant & BPA'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Ansøgning og klage om borgerstyret personlig assistance (BPA), personlig hjælper og antal timer.' : ($lang === 'sv' ? 'Ansökan och klagomål om brukarstyrd personlig assistans (BPA), personlig assistent och antal timmar.' : 'Application and complaint about user-controlled personal assistance (BPA), personal assistant, and number of hours.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
                
                <div class="service-card">
                    <h4>🦽 <?php echo $lang === 'da' ? 'Hjælpemidler' : ($lang === 'sv' ? 'Hjälpmedel' : 'Assistive Devices'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Hjælp til ansøgning om kørestol, handicapbil, høreapparater, proteser og andre hjælpemidler efter serviceloven.' : ($lang === 'sv' ? 'Hjälp med ansökan om rullstol, handikappaanpassad bil, hörapparater, proteser och andra hjälpmedel enligt socialtjänstlagen.' : 'Help with application for wheelchair, disability car, hearing aids, prostheses, and other assistive devices under the Social Services Act.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
                
                <div class="service-card">
                    <h4>🏠 <?php echo $lang === 'da' ? 'Botilbud & Boligstøtte' : ($lang === 'sv' ? 'Boende & Bostadsstöd' : 'Housing & Support'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Vejledning om botilbud, boligindretning, boligstøtte og specialboliger til personer med funktionsnedsættelse.' : ($lang === 'sv' ? 'Vägledning om boende, bostadsanpassning, bostadsstöd och specialbostäder för personer med funktionsnedsättning.' : 'Guidance on housing, home adaptation, housing support, and special housing for people with disabilities.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ELDERLY SERVICES -->
        <div id="category-elderly" class="services-section" style="display: none;">
            <h2><span>👵</span> <?php echo $lang === 'da' ? 'Ældre & Omsorg' : ($lang === 'sv' ? 'Äldre & Omsorg' : 'Elderly & Care'); ?></h2>
            
            <div class="service-grid">
                <div class="service-card">
                    <h4>🏠 <?php echo $lang === 'da' ? 'Hjemmepleje & Hjemmehjælp' : ($lang === 'sv' ? 'Hemvård & Hemhjälp' : 'Home Care'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Ansøgning, ændring og klage over hjemmepleje, personlig pleje, praktisk hjælp og antal besøg.' : ($lang === 'sv' ? 'Ansökan, ändring och klagomål över hemvård, personlig vård, praktisk hjälp och antal besök.' : 'Application, change, and complaint about home care, personal care, practical help, and number of visits.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
                
                <div class="service-card">
                    <h4>🏥 <?php echo $lang === 'da' ? 'Plejehjem & Ældrebolig' : ($lang === 'sv' ? 'Äldreboende' : 'Nursing Home'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Hjælp til ansøgning om plejehjem, ældrebolig, demensplads og klager over visitation eller tilbud.' : ($lang === 'sv' ? 'Hjälp med ansökan om äldreboende, demensplats och klagomål över remiss eller erbjudande.' : 'Help with application for nursing home, dementia care, and complaints about referral or offer.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
                
                <div class="service-card">
                    <h4>🩺 <?php echo $lang === 'da' ? 'Følgelæge & Lægeordning' : ($lang === 'sv' ? 'Läkare' : 'Doctor'); ?></h4>
                    <p><?php echo $lang === 'da' ? 'Vejledning om følgelæge, lægetilsyn på plejehjem og ret til sundhedshjælp for ældre.' : ($lang === 'sv' ? 'Vägledning om läkare, läkartillsyn på äldreboende och rätt till hälsovård för äldre.' : 'Guidance on doctor follow-up, medical supervision in nursing homes, and right to healthcare for the elderly.'); ?></p>
                    <div class="service-actions">
                        <button class="btn-service btn-primary" onclick="showTab('kate')"><?php echo $lang === 'da' ? 'Spørg Kate AI' : ($lang === 'sv' ? 'Fråga Kate AI' : 'Ask Kate AI'); ?></button>
                        <button class="btn-service btn-secondary" onclick="showTab('complaint')"><?php echo $lang === 'da' ? 'Opret klage' : ($lang === 'sv' ? 'Skapa klagomål' : 'Create complaint'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- DOCUMENTATION GUIDE -->
        <div class="info-box">
            <h3><?php echo $lang === 'da' ? 'Dokumentation er ALT i din sag' : ($lang === 'sv' ? 'Dokumentation är ALLT i ditt ärende' : 'Documentation is EVERYTHING in your case'); ?></h3>
            <p style="margin: 0 0 1rem 0; color: #1e40af;">
                <?php echo $lang === 'da' ? 'Uden dokumentation har du ingen sag. Her er de vigtigste tips til at sikre din dokumentation:' : ($lang === 'sv' ? 'Utan dokumentation har du inget ärende. Här är de viktigaste tipsen för att säkra din dokumentation:' : 'Without documentation, you have no case. Here are the most important tips to secure your documentation:'); ?>
            </p>
            
            <div class="tips-grid">
                <div class="tip-card">
                    <span class="tip-icon">🎙️</span>
                    <strong><?php echo $lang === 'da' ? 'Optag ALLE møder' : ($lang === 'sv' ? 'Spela in ALLA möten' : 'Record ALL meetings'); ?></strong>
                    <p><?php echo $lang === 'da' ? 'Det er lovligt at optage egne samtaler uden samtykke (til privat brug). Optagelser kan bruges som bevis.' : ($lang === 'sv' ? 'Det är lagligt att spela in egna samtal utan samtycke (för privat bruk). Inspelningar kan användas som bevis.' : 'It is legal to record your own conversations without consent (for private use). Recordings can be used as evidence.'); ?></p>
                </div>
                
                <div class="tip-card">
                    <span class="tip-icon">📄</span>
                    <strong><?php echo $lang === 'da' ? 'Få transskriptioner' : ($lang === 'sv' ? 'Få transkriptioner' : 'Get transcriptions'); ?></strong>
                    <p><?php echo $lang === 'da' ? 'Få professionelle transskriptioner af alle møder. Dette viser hvad der blev sagt og kan fremvise modsætninger.' : ($lang === 'sv' ? 'Få professionella transkriberingar av alla möten. Detta visar vad som sades och kan påvisa motsägelser.' : 'Get professional transcriptions of all meetings. This shows what was said and can reveal contradictions.'); ?></p>
                </div>
                
                <div class="tip-card">
                    <span class="tip-icon">📧</span>
                    <strong><?php echo $lang === 'da' ? 'Gem alle emails og SMS' : ($lang === 'sv' ? 'Spara alla e-post och SMS' : 'Save all emails and SMS'); ?></strong>
                    <p><?php echo $lang === 'da' ? 'Gem ALLE emails, SMS' . "'" . 'er og beskeder. Tag screenshots. Print emails og gem i mapper med dato.' : ($lang === 'sv' ? 'Spara ALLA e-post, SMS och meddelanden. Ta skärmdumpar. Skriv ut e-post och spara i mappar med datum.' : 'Save ALL emails, SMS, and messages. Take screenshots. Print emails and save in folders with dates.'); ?></p>
                </div>
                
                <div class="tip-card">
                    <span class="tip-icon">📔</span>
                    <strong><?php echo $lang === 'da' ? 'Før dagbog' : ($lang === 'sv' ? 'För dagbok' : 'Keep a diary'); ?></strong>
                    <p><?php echo $lang === 'da' ? 'Skriv dagbog med dato, tid og hvad der skete. Dette er stærk dokumentation i retten.' : ($lang === 'sv' ? 'Skriv dagbok med datum, tid och vad som hände. Detta är stark dokumentation i rätten.' : 'Write a diary with date, time, and what happened. This is strong documentation in court.'); ?></p>
                </div>
                
                <div class="tip-card">
                    <span class="tip-icon">📸</span>
                    <strong><?php echo $lang === 'da' ? 'Tag billeder' : ($lang === 'sv' ? 'Ta bilder' : 'Take photos'); ?></strong>
                    <p><?php echo $lang === 'da' ? 'Tag billeder af vigtige dokumenter, forholdene i hjemmet, eller relevante situationer.' : ($lang === 'sv' ? 'Ta bilder av viktiga dokument, förhållanden i hemmet eller relevanta situationer.' : 'Take photos of important documents, home conditions, or relevant situations.'); ?></p>
                </div>
                
                <div class="tip-card">
                    <span class="tip-icon">👥</span>
                    <strong><?php echo $lang === 'da' ? 'Få vidneudsagn' : ($lang === 'sv' ? 'Få vittnesmål' : 'Get witness statements'); ?></strong>
                    <p><?php echo $lang === 'da' ? 'Få skriftlige vidneudsagn fra venner, familie, lærer, læge osv. som kan støtte din sag.' : ($lang === 'sv' ? 'Få skriftliga vittnesmål från vänner, familj, lärare, läkare osv. som kan stödja ditt ärende.' : 'Get written witness statements from friends, family, teachers, doctors, etc. who can support your case.'); ?></p>
                </div>
            </div>
        </div>

        <!-- TABS FOR TOOLS -->
        <div class="tabs-container" style="background: #f8fafc; border-radius: 16px; padding: 2rem; margin-top: 3rem;">
            <div class="tabs-nav">
                <button class="tab-btn active" onclick="switchTab('complaint')" id="btn-complaint">📝 <?php echo $lang === 'da' ? 'Klage Generator' : ($lang === 'sv' ? 'Klagomålsgenerator' : 'Complaint Generator'); ?></button>
                <button class="tab-btn" onclick="switchTab('kate')" id="btn-kate">🤖 Kate AI</button>
            </div>
            
            <!-- COMPLAINT GENERATOR TAB -->
            <div id="tab-complaint" class="tab-content active">
                <h3 style="margin: 0 0 1.5rem 0; color: #1e293b;">📝 <?php echo $lang === 'da' ? 'Klage Generator' : ($lang === 'sv' ? 'Klagomålsgenerator' : 'Complaint Generator'); ?></h3>
                <p style="margin: 0 0 1.5rem 0; color: #64748b;">
                    <?php echo $lang === 'da' ? 'Opret professionelle klager over afgørelser fra kommunen. Kate AI hjælper dig med at formulere din klage korrekt.' : ($lang === 'sv' ? 'Skapa professionella klagomål över beslut från kommunen. Kate AI hjälper dig att formulera ditt klagomål korrekt.' : 'Create professional complaints about decisions from the municipality. Kate AI helps you formulate your complaint correctly.'); ?>
                </p>
                <button class="btn-service btn-primary" style="font-size: 1rem; padding: 0.875rem 1.75rem;" onclick="window.location.href='<?php echo home_url('/platform-kate-ai/?question=Jeg vil oprette en klage over en afgørelse'); ?>'"><?php echo $lang === 'da' ? 'Start Klage Generator' : ($lang === 'sv' ? 'Starta Klagomålsgenerator' : 'Start Complaint Generator'); ?></button>
            </div>
            
            <!-- KATE AI TAB -->
            <div id="tab-kate" class="tab-content">
                <h3 style="margin: 0 0 1.5rem 0; color: #1e293b;">🤖 Kate AI</h3>
                <p style="margin: 0 0 1.5rem 0; color: #64748b;">
                    <?php echo $lang === 'da' ? 'Stil spørgsmål til Kate AI om din sag. Kate kan hjælpe med juridisk vejledning, sagsopbygning og dokumentation.' : ($lang === 'sv' ? 'Ställ frågor till Kate AI om ditt ärende. Kate kan hjälpa med juridisk vägledning, ärendeuppbyggnad och dokumentation.' : 'Ask Kate AI questions about your case. Kate can help with legal guidance, case building, and documentation.'); ?>
                </p>
                <button class="btn-service btn-primary" style="font-size: 1rem; padding: 0.875rem 1.75rem;" onclick="window.location.href='<?php echo home_url('/platform-kate-ai/'); ?>'"><?php echo $lang === 'da' ? 'Åbn Kate AI' : ($lang === 'sv' ? 'Öppna Kate AI' : 'Open Kate AI'); ?></button>
            </div>
        </div>
    </main>
</div>

<script>
// Category switching
function showCategory(category) {
    // Hide all service sections
    document.querySelectorAll('.services-section').forEach(section => {
        section.style.display = 'none';
    });
    
    // Show selected category
    document.getElementById('category-' + category).style.display = 'block';
    
    // Update active state on cards
    document.querySelectorAll('.category-card').forEach(card => {
        card.classList.remove('active');
    });
    event.target.closest('.category-card').classList.add('active');
}

// Tab switching (for complaint generator and Kate AI)
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById('tab-' + tabName).classList.add('active');
    
    // Update button states
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById('btn-' + tabName).classList.add('active');
}

// Show tab from service card buttons
function showTab(tabName) {
    switchTab(tabName);
    // Scroll to tabs section
    document.querySelector('.tabs-container').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<?php get_footer(); ?>
