<?php
/**
 * Template Name: Platform - Sagshjælp
 * Description: Complete social services support for ALL citizens - family, jobcenter, disability, elderly
 */

get_header();
$lang = rtf_get_lang();

// Check login
if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth/?lang=' . $lang));
    exit;
}

rtf_require_subscription();

$current_user = rtf_get_current_user();
global $wpdb;

// Translations
$translations = [
    'da' => [
        'title' => 'Sagshjælp & Borgerservice',
        'subtitle' => 'Få professionel hjælp til alle typer sociale sager',
        'categories' => [
            'family' => ['name' => 'Familie & Børn', 'icon' => '👨‍👩‍👧‍👦', 'desc' => 'Anbringelse, forældremyndighed, samvær, tvangssager'],
            'jobcenter' => ['name' => 'Jobcenter & Kontanthjælp', 'icon' => '💼', 'desc' => 'Aktivering, uddannelseshjælp, sygedagpenge, sanktioner'],
            'disability' => ['name' => 'Handicap & Særlig Støtte', 'icon' => '♿', 'desc' => 'Handicaptillæg, hjælpemidler, BPA, tabt arbejdsfortjeneste'],
            'elderly' => ['name' => 'Ældre & Pleje', 'icon' => '👴', 'desc' => 'Hjemmepleje, plejehjem, demenshjælp, værgemål'],
            'housing' => ['name' => 'Bolig & Udsættelse', 'icon' => '🏠', 'desc' => 'Boligstøtte, husleje, udsættelsessager, hjemløshed'],
            'economy' => ['name' => 'Økonomi & Gæld', 'icon' => '💰', 'desc' => 'Gældssanering, budget, økonomisk rådgivning'],
        ],
        'tabs' => [
            'overview' => 'Oversigt',
            'complaint' => 'Lav Klage',
            'documents' => 'Mine Dokumenter',
            'cases' => 'Mine Sager',
            'guide' => 'Juridisk Guide',
        ],
    ],
    'sv' => [
        'title' => 'Ärendehjälp & Medborgarservice',
        'subtitle' => 'Få professionell hjälp med alla typer av sociala ärenden',
        'categories' => [
            'family' => ['name' => 'Familj & Barn', 'icon' => '👨‍👩‍👧‍👦', 'desc' => 'Omhändertagande, vårdnad, umgänge, tvångsärenden'],
            'jobcenter' => ['name' => 'Arbetsförmedling & Försörjning', 'icon' => '💼', 'desc' => 'Aktivering, utbildningsstöd, sjukpenning, sanktioner'],
            'disability' => ['name' => 'Funktionsnedsättning', 'icon' => '♿', 'desc' => 'Handikappersättning, hjälpmedel, assistans'],
            'elderly' => ['name' => 'Äldre & Omsorg', 'icon' => '👴', 'desc' => 'Hemtjänst, äldreboende, demensvård, god man'],
            'housing' => ['name' => 'Boende & Avhysning', 'icon' => '🏠', 'desc' => 'Bostadsbidrag, hyra, avhysning, hemlöshet'],
            'economy' => ['name' => 'Ekonomi & Skuld', 'icon' => '💰', 'desc' => 'Skuldsanering, budget, ekonomisk rådgivning'],
        ],
        'tabs' => [
            'overview' => 'Översikt',
            'complaint' => 'Skapa Klagomål',
            'documents' => 'Mina Dokument',
            'cases' => 'Mina Ärenden',
            'guide' => 'Juridisk Guide',
        ],
    ],
    'en' => [
        'title' => 'Case Support & Citizen Services',
        'subtitle' => 'Get professional help with all types of social cases',
        'categories' => [
            'family' => ['name' => 'Family & Children', 'icon' => '👨‍👩‍👧‍👦', 'desc' => 'Custody, visitation, child protection cases'],
            'jobcenter' => ['name' => 'Employment & Benefits', 'icon' => '💼', 'desc' => 'Job activation, benefits, sick pay, sanctions'],
            'disability' => ['name' => 'Disability Support', 'icon' => '♿', 'desc' => 'Disability benefits, assistive devices, care'],
            'elderly' => ['name' => 'Elderly & Care', 'icon' => '👴', 'desc' => 'Home care, nursing homes, guardianship'],
            'housing' => ['name' => 'Housing & Eviction', 'icon' => '🏠', 'desc' => 'Housing support, rent issues, eviction, homelessness'],
            'economy' => ['name' => 'Finance & Debt', 'icon' => '💰', 'desc' => 'Debt relief, budgeting, financial counseling'],
        ],
        'tabs' => [
            'overview' => 'Overview',
            'complaint' => 'File Complaint',
            'documents' => 'My Documents',
            'cases' => 'My Cases',
            'guide' => 'Legal Guide',
        ],
    ],
];

$t = $translations[$lang] ?? $translations['da'];
?>

<div class="platform-container" style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <?php get_template_part('template-parts/platform-sidebar'); ?>
    
    <div class="platform-content" style="min-width: 0;">

<style>
:root {
    --primary: #2563eb;
    --primary-dark: #1e40af;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --text: #1f2937;
    --text-light: #6b7280;
    --bg-gray: #f9fafb;
    --border: #e5e7eb;
}

.sagshjaelp-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 3rem 2rem;
    border-radius: 20px;
    margin-bottom: 3rem;
    color: white;
    text-align: center;
}

.hero-section h1 {
    font-size: 2.5rem;
    margin: 0 0 0.5rem 0;
    font-weight: 700;
}

.hero-section p {
    font-size: 1.2rem;
    opacity: 0.95;
    margin: 0;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.category-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.07);
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.15);
    border-color: var(--primary);
}

.category-card.active {
    border-color: var(--primary);
    background: #eff6ff;
}

.category-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
}

.category-name {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 0.5rem;
}

.category-desc {
    font-size: 0.95rem;
    color: var(--text-light);
    line-height: 1.5;
}

.tabs-container {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.07);
}

.tabs-header {
    display: flex;
    gap: 0;
    background: #f3f4f6;
    padding: 0;
    border-bottom: 2px solid var(--border);
    overflow-x: auto;
}

.tab-btn {
    flex: 1;
    padding: 1.2rem 1.5rem;
    background: none;
    border: none;
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-light);
    cursor: pointer;
    transition: all 0.3s;
    white-space: nowrap;
    position: relative;
}

.tab-btn:hover {
    background: rgba(37, 99, 235, 0.05);
    color: var(--primary);
}

.tab-btn.active {
    background: white;
    color: var(--primary);
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--primary);
}

.tab-content {
    padding: 2.5rem;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.info-box {
    background: #eff6ff;
    border-left: 4px solid var(--primary);
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.warning-box {
    background: #fef3c7;
    border-left: 4px solid var(--warning);
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.success-box {
    background: #d1fae5;
    border-left: 4px solid var(--success);
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text);
}

.form-control {
    width: 100%;
    padding: 0.875rem;
    border: 2px solid var(--border);
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
}

.btn {
    padding: 0.875rem 2rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.case-item {
    background: white;
    border: 2px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s;
}

.case-item:hover {
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.action-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    text-align: center;
    cursor: pointer;
    transition: transform 0.3s;
}

.action-card:hover {
    transform: scale(1.05);
}

.action-card-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.action-card-title {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.hidden {
    display: none;
}
</style>

<div class="sagshjaelp-container">
    <!-- Hero Section -->
    <div class="hero-section">
        <h1><?php echo $t['title']; ?></h1>
        <p><?php echo $t['subtitle']; ?></p>
    </div>

    <!-- Categories Grid -->
    <div class="categories-grid">
        <?php foreach ($t['categories'] as $key => $cat): ?>
            <div class="category-card" data-category="<?php echo $key; ?>" onclick="selectCategory('<?php echo $key; ?>')">
                <span class="category-icon"><?php echo $cat['icon']; ?></span>
                <div class="category-name"><?php echo $cat['name']; ?></div>
                <div class="category-desc"><?php echo $cat['desc']; ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Main Content Tabs -->
    <div class="tabs-container">
        <div class="tabs-header">
            <?php foreach ($t['tabs'] as $key => $label): ?>
                <button class="tab-btn <?php echo $key === 'overview' ? 'active' : ''; ?>" 
                        onclick="switchTab('<?php echo $key; ?>')" 
                        data-tab="<?php echo $key; ?>">
                    <?php echo $label; ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="tab-content">
            <!-- Overview Tab -->
            <div class="tab-pane active" id="tab-overview">
                <div class="info-box">
                    <h3 style="margin-top:0;">💡 Velkommen til Sagshjælp</h3>
                    <p>Her kan du få hjælp til ALLE typer sociale sager. Vælg din sagskategori ovenfor, og vi hjælper dig med:</p>
                    <ul style="margin: 1rem 0; line-height: 1.8;">
                        <li>📝 Skrive professionelle klager</li>
                        <li>📚 Forstå dine rettigheder og lovgivningen</li>
                        <li>📄 Organisere dine dokumenter</li>
                        <li>⏰ Holde styr på frister og deadlines</li>
                        <li>🤝 Få AI-assisteret vejledning</li>
                    </ul>
                </div>

                <h3>🚀 Hurtige handlinger</h3>
                <div class="quick-actions">
                    <div class="action-card" onclick="switchTab('complaint')">
                        <div class="action-card-icon">✍️</div>
                        <div class="action-card-title">Lav en klage</div>
                        <p style="margin:0; opacity:0.9; font-size:0.95rem;">Generer professionel klage med Kate AI</p>
                    </div>
                    <div class="action-card" onclick="switchTab('documents')">
                        <div class="action-card-icon">📄</div>
                        <div class="action-card-title">Upload dokumenter</div>
                        <p style="margin:0; opacity:0.9; font-size:0.95rem;">Gem og organiser dine vigtige papirer</p>
                    </div>
                    <div class="action-card" onclick="window.location.href='<?php echo home_url('/platform-kate-ai/?lang='.$lang); ?>'">
                        <div class="action-card-icon">🤖</div>
                        <div class="action-card-title">Spørg Kate AI</div>
                        <p style="margin:0; opacity:0.9; font-size:0.95rem;">Få øjeblikkelig juridisk vejledning</p>
                    </div>
                </div>
            </div>

            <!-- Complaint Tab -->
            <div class="tab-pane" id="tab-complaint">
                <div class="warning-box">
                    <strong>⏰ Vigtigt om frister:</strong> De fleste afgørelser kan påklages inden 4 uger. Kate AI hjælper dig med at formulere en stærk klage.
                </div>

                <form id="complaint-form" method="POST">
                    <?php wp_nonce_field('generate_complaint_action', 'complaint_nonce'); ?>
                    
                    <div class="form-group">
                        <label>📂 Hovedkategori *</label>
                        <select name="case_category" id="case_category" class="form-control" required onchange="updateSubcategories()">
                            <option value="">Vælg hovedkategori...</option>
                            <?php foreach ($t['categories'] as $key => $cat): ?>
                                <option value="<?php echo $key; ?>"><?php echo $cat['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" id="subcategory-container" style="display:none;">
                        <label>📋 Specifik sagstype *</label>
                        <select name="subcategory" id="subcategory" class="form-control" required>
                            <option value="">Vælg specifik sagstype...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>🏛️ Klagetype - Vælg instans *</label>
                        <select name="complaint_type" id="complaint_type" class="form-control" required onchange="updateAuthorityOptions()">
                            <option value="">Vælg klagetype...</option>
                            <optgroup label="<?php echo $lang === 'da' ? 'Kommunale afgørelser' : ($lang === 'sv' ? 'Kommunala beslut' : 'Municipal decisions'); ?>">
                                <option value="kommune_genoptagelse"><?php echo $lang === 'da' ? 'Anmodning om genoptagelse (til kommunen)' : ($lang === 'sv' ? 'Begäran om omprövning (till kommunen)' : 'Request for reconsideration (municipality)'); ?></option>
                                <option value="kommune_klage"><?php echo $lang === 'da' ? 'Klage til kommunen' : ($lang === 'sv' ? 'Klagomål till kommunen' : 'Complaint to municipality'); ?></option>
                            </optgroup>
                            <optgroup label="<?php echo $lang === 'da' ? 'Klager til Ankestyrelsen' : ($lang === 'sv' ? 'Överklaganden till Överklagandenämnden' : 'Appeals to Appeals Board'); ?>">
                                <option value="ankestyrelsen_familie"><?php echo $lang === 'da' ? 'Ankestyrelsen - Familie & Børn' : ($lang === 'sv' ? 'Överklagandenämnden - Familj & Barn' : 'Appeals Board - Family'); ?></option>
                                <option value="ankestyrelsen_beskæftigelse"><?php echo $lang === 'da' ? 'Ankestyrelsen - Beskæftigelse & Sygedagpenge' : ($lang === 'sv' ? 'Överklagandenämnden - Sysselsättning' : 'Appeals Board - Employment'); ?></option>
                                <option value="ankestyrelsen_handicap"><?php echo $lang === 'da' ? 'Ankestyrelsen - Handicap & Funktionsnedsættelse' : ($lang === 'sv' ? 'Överklagandenämnden - Handikapp' : 'Appeals Board - Disability'); ?></option>
                                <option value="ankestyrelsen_social"><?php echo $lang === 'da' ? 'Ankestyrelsen - Social & Bolig' : ($lang === 'sv' ? 'Överklagandenämnden - Socialt & Boende' : 'Appeals Board - Social'); ?></option>
                            </optgroup>
                            <optgroup label="<?php echo $lang === 'da' ? 'Patientklager' : ($lang === 'sv' ? 'Patientklagomål' : 'Patient complaints'); ?>">
                                <option value="patientombuddet"><?php echo $lang === 'da' ? 'Patientombuddet - Behandlingsfejl' : ($lang === 'sv' ? 'Patientombudsmannen - Behandlingsfel' : 'Patient Ombudsman'); ?></option>
                                <option value="sundhedsstyrelsen"><?php echo $lang === 'da' ? 'Sundhedsstyrelsen - Autorisationsklage' : ($lang === 'sv' ? 'Socialstyrelsen - Auktorisationsklagomål' : 'Health Authority'); ?></option>
                            </optgroup>
                            <optgroup label="<?php echo $lang === 'da' ? 'Øvrige klageinstanser' : ($lang === 'sv' ? 'Övriga klagomålsinstanser' : 'Other appeal bodies'); ?>">
                                <option value="datatilsynet"><?php echo $lang === 'da' ? 'Datatilsynet - GDPR/Databeskyttelse' : ($lang === 'sv' ? 'Datainspektionen - GDPR' : 'Data Protection'); ?></option>
                                <option value="ligebehandlingsnævnet"><?php echo $lang === 'da' ? 'Ligebehandlingsnævnet - Diskrimination' : ($lang === 'sv' ? 'Diskrimineringsnämnden' : 'Equality Board'); ?></option>
                                <option value="huslejenævn"><?php echo $lang === 'da' ? 'Huslejenævnet - Boligtvister' : ($lang === 'sv' ? 'Hyresnämnden' : 'Rent Tribunal'); ?></option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>🏛️ Myndighed/kommune der har truffet afgørelsen *</label>
                        <input type="text" name="authority" id="authority" class="form-control" 
                               placeholder="<?php echo $lang === 'da' ? 'F.eks. København Kommune, Aarhus Kommune, Socialstyrelsen' : ($lang === 'sv' ? 'T.ex. Stockholms kommun, Göteborgs kommun' : 'E.g. Copenhagen Municipality'); ?>" required>
                        <small style="color: var(--text-light); display:block; margin-top:0.5rem;">
                            <?php echo $lang === 'da' ? 'Skriv den fulde navn på den myndighed der har truffet den afgørelse du vil klage over' : ($lang === 'sv' ? 'Skriv fullständigt namn på myndigheten som fattat beslutet' : 'Enter the full name of the authority'); ?>
                        </small>
                    </div>

                    <div class="form-group">
                        <label>📋 Afgørelsens journalnummer/sagsnummer</label>
                        <input type="text" name="case_number" class="form-control" 
                               placeholder="<?php echo $lang === 'da' ? 'F.eks. 2024-12345 eller J.nr. 2024/0012345' : ($lang === 'sv' ? 'T.ex. 2024-12345' : 'E.g. 2024-12345'); ?>">
                        <small style="color: var(--text-light); display:block; margin-top:0.5rem;">
                            <?php echo $lang === 'da' ? 'Find journalnummeret på selve afgørelsen - vigtigt for klagen!' : ($lang === 'sv' ? 'Hitta diarienumret på beslutet' : 'Find the case number on the decision'); ?>
                        </small>
                    </div>

                    <div class="form-group">
                        <label>📅 Dato for afgørelsen *</label>
                        <input type="date" name="decision_date" class="form-control" required>
                        <small style="color: var(--text-light); display:block; margin-top:0.5rem;">
                            <?php echo $lang === 'da' ? 'Vigtigt! De fleste klager skal indgives inden 4 uger fra denne dato.' : ($lang === 'sv' ? 'Viktigt! De flesta klagomål måste lämnas in inom 4 veckor.' : 'Important! Most complaints must be filed within 4 weeks.'); ?>
                        </small>
                    </div>

                    <div class="form-group">
                        <label>📧 Din email (til klagen)</label>
                        <input type="email" name="user_email" class="form-control" 
                               value="<?php echo esc_attr($current_user->email); ?>"
                               placeholder="<?php echo $lang === 'da' ? 'Din kontakt email' : ($lang === 'sv' ? 'Din kontakt-e-post' : 'Your contact email'); ?>">
                    </div>

                    <div class="form-group">
                        <label>📞 Dit telefonnummer (til klagen)</label>
                        <input type="tel" name="user_phone" class="form-control" 
                               placeholder="<?php echo $lang === 'da' ? 'F.eks. +45 12 34 56 78' : ($lang === 'sv' ? 'T.ex. +46 70 123 45 67' : 'E.g. +45 12 34 56 78'); ?>">
                    </div>

                    <div class="form-group">
                        <label>✍️ <?php echo $lang === 'da' ? 'Beskriv din klage detaljeret *' : ($lang === 'sv' ? 'Beskriv ditt klagomål i detalj *' : 'Describe your complaint in detail *'); ?></label>
                        <div class="info-box" style="font-size: 0.9rem;">
                            <strong><?php echo $lang === 'da' ? 'Inkluder så mange detaljer som muligt:' : ($lang === 'sv' ? 'Inkludera så många detaljer som möjligt:' : 'Include as many details as possible:'); ?></strong>
                            <ul style="margin: 0.5rem 0; line-height: 1.7;">
                                <li><?php echo $lang === 'da' ? '🔹 Hvad handler sagen helt konkret om? (beskrivelse af situation)' : ($lang === 'sv' ? '🔹 Vad handlar ärendet om konkret?' : '🔹 What is the case specifically about?'); ?></li>
                                <li><?php echo $lang === 'da' ? '🔹 Hvilken afgørelse er du uenig i? (hvad besluttede myndigheden?)' : ($lang === 'sv' ? '🔹 Vilket beslut är du oenig med?' : '🔹 Which decision do you disagree with?'); ?></li>
                                <li><?php echo $lang === 'da' ? '🔹 Hvorfor mener du afgørelsen er forkert? (dine argumenter)' : ($lang === 'sv' ? '🔹 Varför anser du att beslutet är fel?' : '🔹 Why do you think the decision is wrong?'); ?></li>
                                <li><?php echo $lang === 'da' ? '🔹 Hvilke faktiske forhold har myndigheden overset? (hvad mangler de at vide?)' : ($lang === 'sv' ? '🔹 Vilka faktiska förhållanden har myndigheten förbisett?' : '🔹 What facts did the authority overlook?'); ?></li>
                                <li><?php echo $lang === 'da' ? '🔹 Hvilke love/paragraffer støtter din sag? (hvis du kender dem)' : ($lang === 'sv' ? '🔹 Vilka lagar/paragrafer stöder ditt ärende?' : '🔹 Which laws/paragraphs support your case?'); ?></li>
                                <li><?php echo $lang === 'da' ? '🔹 Hvad ønsker du som resultat? (hvad skal Ankestyrelsen beslutte?)' : ($lang === 'sv' ? '🔹 Vad önskar du som resultat?' : '🔹 What outcome do you want?'); ?></li>
                            </ul>
                            <strong style="color: var(--warning);"><?php echo $lang === 'da' ? '⚡ Jo flere detaljer, jo stærkere bliver din klage!' : ($lang === 'sv' ? '⚡ Ju fler detaljer, desto starkare blir ditt klagomål!' : '⚡ More details make a stronger complaint!'); ?></strong>
                        </div>
                        <textarea name="complaint_text" class="form-control" rows="16" required 
                                  placeholder="<?php echo $lang === 'da' ? 'Skriv din detaljerede klage her...

Eksempel:
Min kommune har afslået min ansøgning om merudgifter til kost med begrundelsen at mit behov ikke er dokumenteret. Men jeg har vedlagt lægeerklæring fra både min praktiserende læge og speciallæge som bekræfter at jeg har et medicinske behov for specialkost på grund af [sygdom]. 

Myndigheden har ikke forholdt sig til disse lægelige vurderinger i afgørelsen, og har derfor ikke foretaget en konkret og individuel vurdering som krævet af servicelovens § 41.

Jeg ønsker at Ankestyrelsen omgør kommunens afgørelse og bevilger merudgifterne.' : ($lang === 'sv' ? 'Skriv ditt detaljerade klagomål här...' : 'Write your detailed complaint here...'); ?>"></textarea>
                    </div>

                    <div class="form-group">
                        <label>📎 <?php echo $lang === 'da' ? 'Vælg dokumenter der skal vedlægges klagen' : ($lang === 'sv' ? 'Välj dokument som ska bifogas klagomålet' : 'Select documents to attach'); ?></label>
                        <?php
                        $docs = $wpdb->get_results($wpdb->prepare(
                            "SELECT id, title, document_type, file_path FROM {$wpdb->prefix}rtf_platform_documents 
                             WHERE user_id = %d ORDER BY created_at DESC",
                            $current_user->id
                        ));
                        ?>
                        <?php if (!empty($docs)): ?>
                            <div class="info-box" style="font-size: 0.9rem; margin-bottom: 1rem;">
                                <strong>💡 <?php echo $lang === 'da' ? 'Tips til dokumentvalg:' : ($lang === 'sv' ? 'Tips för dokumentval:' : 'Document selection tips:'); ?></strong>
                                <ul style="margin: 0.5rem 0; line-height: 1.7;">
                                    <li><?php echo $lang === 'da' ? '✅ Vælg ALLE relevante dokumenter der støtter din klage' : ($lang === 'sv' ? '✅ Välj ALLA relevanta dokument' : '✅ Select ALL relevant documents'); ?></li>
                                    <li><?php echo $lang === 'da' ? '📄 Lægeerklæringer, speciallægevurderinger, journalnotater' : ($lang === 'sv' ? '📄 Läkarintyg, specialistbedömningar' : '📄 Medical certificates, specialist assessments'); ?></li>
                                    <li><?php echo $lang === 'da' ? '📋 Tidligere afgørelser, korrespondance med myndigheden' : ($lang === 'sv' ? '📋 Tidigare beslut, korrespondens' : '📋 Previous decisions, correspondence'); ?></li>
                                    <li><?php echo $lang === 'da' ? '📊 Økonomiske dokumenter (ved økonomi-sager)' : ($lang === 'sv' ? '📊 Ekonomiska dokument' : '📊 Financial documents'); ?></li>
                                    <li><?php echo $lang === 'da' ? '🎓 Uddannelsesbeviser, eksamensresultater (ved uddannelseshjælp)' : ($lang === 'sv' ? '🎓 Utbildningsbevis' : '🎓 Educational certificates'); ?></li>
                                </ul>
                            </div>
                            <div style="max-height: 350px; overflow-y: auto; border: 2px solid var(--border); border-radius: 8px; padding: 1rem; background: #f9fafb;">
                                <?php foreach ($docs as $doc): ?>
                                    <label style="display: flex; align-items: center; padding: 0.75rem; cursor: pointer; border-radius: 6px; margin-bottom: 0.5rem; background: white; border: 1px solid #e5e7eb; transition: all 0.2s;">
                                        <input type="checkbox" name="attached_docs[]" value="<?php echo $doc->id; ?>" style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                        <div style="flex: 1;">
                                            <strong style="display: block; color: var(--text);"><?php echo esc_html($doc->title); ?></strong>
                                            <span style="font-size: 0.85rem; color: var(--text-light);">
                                                📑 <?php echo esc_html($doc->document_type); ?>
                                            </span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <small style="color: var(--text-light); display:block; margin-top:0.75rem;">
                                ✓ <?php echo $lang === 'da' ? 'Valgte dokumenter vil blive nævnt i klagen og du skal selv vedhæfte dem når du sender klagen.' : ($lang === 'sv' ? 'Valda dokument kommer att nämnas i klagomålet.' : 'Selected documents will be referenced in the complaint.'); ?>
                            </small>
                        <?php else: ?>
                            <div class="warning-box">
                                <strong><?php echo $lang === 'da' ? '⚠️ Du har ingen uploadede dokumenter' : ($lang === 'sv' ? '⚠️ Du har inga uppladdade dokument' : '⚠️ You have no uploaded documents'); ?></strong>
                                <p style="margin: 0.5rem 0 0 0;">
                                    <?php echo $lang === 'da' ? 'For at styrke din klage bør du uploade relevante dokumenter først.' : ($lang === 'sv' ? 'För att stärka ditt klagomål bör du ladda upp relevanta dokument först.' : 'To strengthen your complaint, upload relevant documents first.'); ?>
                                    <br>
                                    <a href="<?php echo home_url('/platform-dokumenter/?lang='.$lang); ?>" 
                                       style="color: var(--primary); font-weight: 600;">
                                        📤 <?php echo $lang === 'da' ? 'Upload dokumenter her' : ($lang === 'sv' ? 'Ladda upp dokument här' : 'Upload documents here'); ?>
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="request_meeting" value="1" style="margin-right: 0.5rem;">
                            <?php echo $lang === 'da' ? '🤝 Jeg ønsker et møde med Ankestyrelsen (hvis muligt)' : ($lang === 'sv' ? '🤝 Jag önskar ett möte med Överklagandenämnden' : '🤝 I request a meeting'); ?>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.2rem; padding: 1.3rem;">
                        🤖 <?php echo $lang === 'da' ? 'Generer Professionel Klage med Kate AI' : ($lang === 'sv' ? 'Generera Professionellt Klagomål med Kate AI' : 'Generate Professional Complaint with Kate AI'); ?>
                    </button>
                </form>

                <div id="complaint-result" class="hidden" style="margin-top: 2rem;">
                    <div class="success-box">
                        <h3 style="margin-top: 0;">✅ Din klage er klar!</h3>
                        <p>Kate AI har genereret en professionel klage baseret på dine oplysninger.</p>
                    </div>
                    <div id="complaint-output" style="background: white; padding: 2rem; border: 2px solid var(--border); border-radius: 12px; white-space: pre-wrap; font-family: 'Times New Roman', serif; line-height: 1.8;"></div>
                    <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                        <button onclick="copyComplaint()" class="btn btn-primary">📋 Kopier til udklipsholder</button>
                        <button onclick="downloadComplaint()" class="btn btn-primary">💾 Download som PDF</button>
                    </div>
                </div>
            </div>

            <!-- Documents Tab -->
            <div class="tab-pane" id="tab-documents">
                <div class="info-box">
                    <strong>📁 Dokumenthåndtering:</strong> Upload og organiser alle dine vigtige dokumenter her. De vil være tilgængelige når du laver klager.
                </div>
                
                <p style="text-align: center; padding: 3rem;">
                    <a href="<?php echo home_url('/platform-dokumenter/?lang='.$lang); ?>" class="btn btn-primary" style="font-size: 1.1rem;">
                        📤 Gå til Dokumenter
                    </a>
                </p>
            </div>

            <!-- Cases Tab -->
            <div class="tab-pane" id="tab-cases">
                <h3>📊 Mine Aktive Sager</h3>
                <?php
                $cases = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}rtf_platform_cases 
                     WHERE user_id = %d 
                     ORDER BY created_at DESC 
                     LIMIT 20",
                    $current_user->id
                ));
                ?>
                
                <?php if (!empty($cases)): ?>
                    <?php foreach ($cases as $case): ?>
                        <div class="case-item">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <h4 style="margin: 0 0 0.5rem 0;"><?php echo esc_html($case->title); ?></h4>
                                    <p style="color: var(--text-light); margin: 0;"><?php echo esc_html($case->description); ?></p>
                                    <div style="margin-top: 0.75rem;">
                                        <span style="background: #eff6ff; color: var(--primary); padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem;">
                                            <?php echo esc_html($case->category ?? 'Generel'); ?>
                                        </span>
                                        <span style="color: var(--text-light); font-size: 0.85rem; margin-left: 1rem;">
                                            📅 <?php echo date('d/m/Y', strtotime($case->created_at)); ?>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <span style="padding: 0.5rem 1rem; background: <?php echo $case->status === 'open' ? '#10b981' : '#6b7280'; ?>; color: white; border-radius: 20px; font-size: 0.9rem;">
                                        <?php echo $case->status === 'open' ? '✓ Aktiv' : '● Lukket'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 3rem; color: var(--text-light);">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">📋</div>
                        <p>Du har ingen registrerede sager endnu.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Guide Tab -->
            <div class="tab-pane" id="tab-guide">
                <div class="info-box">
                    <strong>📚 Juridisk Guide:</strong> Få adgang til omfattende juridisk information og vejledning.
                </div>
                
                <p style="text-align: center; padding: 3rem;">
                    <a href="<?php echo home_url('/platform-kate-ai/?lang='.$lang); ?>" class="btn btn-primary" style="font-size: 1.1rem;">
                        🤖 Spørg Kate AI om Juridisk Hjælp
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
const lang = '<?php echo $lang; ?>';
let selectedCategory = null;

// Comprehensive subcategory system with DA/SV/EN translations
const subcategories = {
    'family': [
        {da: 'Forældremyndighed - fastsættelse eller ændring', sv: 'Vårdnad - fastställande eller ändring', en: 'Custody - establishment or change'},
        {da: 'Samvær - omfang og udøvelse', sv: 'Umgänge - omfattning och utövande', en: 'Visitation - scope and execution'},
        {da: 'Anbringelse uden for hjemmet - tvangsmæssig', sv: 'Omhändertagande - tvångsmässig', en: 'Foster care - mandatory'},
        {da: 'Anbringelse - frivillig (§ 52)', sv: 'Omhändertagande - frivillig', en: 'Foster care - voluntary'},
        {da: 'Børnebidrag - fastsættelse eller ændring', sv: 'Barnbidrag - fastställande eller ändring', en: 'Child support - establishment or change'},
        {da: 'Adoption - national eller international', sv: 'Adoption - nationell eller internationell', en: 'Adoption - national or international'},
        {da: 'Barnets Reform - økonomisk støtte', sv: 'Barnreform - ekonomiskt stöd', en: 'Child Reform - financial support'},
        {da: 'Familiepleje - godkendelse eller tilsyn', sv: 'Familjehemsvård - godkännande eller tillsyn', en: 'Family foster care - approval or supervision'},
        {da: 'Underretningspligt - om børns trivsel', sv: 'Anmälningsplikt - om barns välbefinnande', en: 'Notification duty - child welfare'},
        {da: 'Børnefaglig undersøgelse (§ 50)', sv: 'Barnfaglig undersökning', en: 'Child welfare investigation'},
        {da: 'Forældremyndighed - fælles vs. ene', sv: 'Vårdnad - gemensam vs. ensam', en: 'Custody - joint vs. sole'},
        {da: 'Samværschikane eller samværsafbrydelse', sv: 'Umgängestrakasserier eller avbrott', en: 'Visitation harassment or termination'},
        {da: 'Børne- og ungeydelse', sv: 'Barn- och ungdomsbidrag', en: 'Child and youth benefits'},
        {da: 'Søskendekontakt under anbringelse', sv: 'Syskonkontakt under omhändertagande', en: 'Sibling contact during placement'}
    ],
    'jobcenter': [
        {da: 'Kontanthjælp - ansøgning eller afslag', sv: 'Försörjningsstöd - ansökan eller avslag', en: 'Cash benefits - application or rejection'},
        {da: 'Sygedagpenge - berettigelse og varighed', sv: 'Sjukpenning - berättigande och varaktighet', en: 'Sick pay - eligibility and duration'},
        {da: 'Ressourceforløb - visitering og indhold', sv: 'Resursförlopp - hänvisning och innehåll', en: 'Resource pathway - referral and content'},
        {da: 'Jobafklaringsforløb - visitering og støtte', sv: 'Arbetsklareringsförlopp', en: 'Job clarification pathway'},
        {da: 'Fleksjob - bevillling og løntilskud', sv: 'Flexjobb - beviljande och lönebidrag', en: 'Flex job - granting and wage subsidy'},
        {da: 'Førtidspension - ansøgning eller afslag', sv: 'Förtidspension - ansökan eller avslag', en: 'Early retirement pension - application or rejection'},
        {da: 'Aktivering og tilbud - pligt og vilkår', sv: 'Aktivering och erbjudanden', en: 'Activation and offers'},
        {da: 'Sanktioner - nedsættelse eller ophør af ydelse', sv: 'Sanktioner - nedsättning eller upphörande', en: 'Sanctions - reduction or cessation'},
        {da: 'Tilbagebetaling af ydelser', sv: 'Återbetalning av förmåner', en: 'Repayment of benefits'},
        {da: 'Rådighed - manglende eller utilstrækkelig', sv: 'Tillgänglighet - bristande eller otillräcklig', en: 'Availability - lacking or insufficient'},
        {da: 'Revalidering - erhvervsrettet', sv: 'Rehabilitering - yrkesriktad', en: 'Rehabilitation - vocational'},
        {da: 'Løntilskud - private eller offentlige arbejdsgivere', sv: 'Lönebidrag - privata eller offentliga arbetsgivare', en: 'Wage subsidy - private or public employers'},
        {da: 'Mentorstøtte under beskæftigelse', sv: 'Mentorstöd under anställning', en: 'Mentor support during employment'},
        {da: 'Skånejob og særlige vilkår', sv: 'Skyddsjobb och särskilda villkor', en: 'Protected job and special conditions'},
        {da: 'Integrationsydelse for nyankomne', sv: 'Integrationsersättning för nyanlända', en: 'Integration benefits for newcomers'},
        {da: 'Uddannelseshjælp til unge under 30', sv: 'Utbildningshjälp för unga under 30', en: 'Education assistance for under 30'},
        {da: 'Forsørgertabserstatning', sv: 'Försörjningsförlustskadestånd', en: 'Loss of provider compensation'},
        {da: 'Tilskud til selvstændig virksomhed', sv: 'Bidrag till egen verksamhet', en: 'Grant for self-employment'},
        {da: 'Hjælp til enkeltudgifter', sv: 'Hjälp till enskilda utgifter', en: 'Help with single expenses'},
        {da: 'Transport til aktivering eller behandling', sv: 'Transport till aktivering eller behandling', en: 'Transport to activation or treatment'}
    ],
    'disability': [
        {da: 'Handicaptillæg og invaliditetsydelse', sv: 'Handikappstillägg och invaliditetsersättning', en: 'Disability supplement and invalidity benefit'},
        {da: 'BPA - Borgerstyret Personlig Assistance', sv: 'Brukarstyrd Personlig Assistans', en: 'User-controlled Personal Assistance'},
        {da: 'Hjælpemidler - bevillging af', sv: 'Hjälpmedel - beviljande av', en: 'Assistive devices - granting'},
        {da: 'Boligindretning - tilpasninger', sv: 'Bostadsanpassning', en: 'Home adaptation'},
        {da: 'Merudgifter til voksne (§ 100)', sv: 'Merkostnader för vuxna', en: 'Additional expenses for adults'},
        {da: 'Ledsageordning (§ 97)', sv: 'Ledsagarordning', en: 'Companion scheme'},
        {da: 'Støtte-kontaktperson ordning', sv: 'Stöd-kontaktpersonordning', en: 'Support contact person scheme'},
        {da: 'Botilbud - midlertidigt eller længerevarende', sv: 'Boendelösning - tillfällig eller långvarig', en: 'Housing solution - temporary or long-term'},
        {da: 'Hjemmehjælp og personlig pleje', sv: 'Hemhjälp och personlig vård', en: 'Home help and personal care'},
        {da: 'Aflastning - regelmæssig eller akut', sv: 'Avlastning - regelbunden eller akut', en: 'Respite care - regular or acute'},
        {da: 'Beskyttet beskæftigelse', sv: 'Skyddat arbete', en: 'Sheltered employment'},
        {da: 'Aktivitets- og samværstilbud', sv: 'Aktivitets- och samvaroerbjudanden', en: 'Activity and social offers'},
        {da: 'Bil på særlige vilkår', sv: 'Bil på särskilda villkor', en: 'Car on special terms'},
        {da: 'Tolkebistand - døve og hørehæmmede', sv: 'Tolkassistans - döva och hörselskadade', en: 'Interpreter assistance - deaf and hearing impaired'}
    ],
    'elderly': [
        {da: 'Hjemmepleje - omfang og kvalitet', sv: 'Hemtjänst - omfattning och kvalitet', en: 'Home care - scope and quality'},
        {da: 'Madservice og madordning', sv: 'Måltidsservice och matordning', en: 'Meal service and meal scheme'},
        {da: 'Plejehjem - visitering og tildeling', sv: 'Äldreboende - hänvisning och tilldelning', en: 'Nursing home - referral and allocation'},
        {da: 'Plejebolig - ældrebolig', sv: 'Vård- och omsorgsboende', en: 'Care and nursing home'},
        {da: 'Værgemål - beskikkelse eller ophør', sv: 'Förmyndarskap - förordnande eller upphörande', en: 'Guardianship - appointment or termination'},
        {da: 'Hjælpemidler til ældre', sv: 'Hjälpmedel för äldre', en: 'Assistive devices for elderly'},
        {da: 'Dagcenter og aktivitetstilbud', sv: 'Dagcenter och aktivitetserbjudanden', en: 'Day center and activity offers'},
        {da: 'Genoptræning og rehabilitering', sv: 'Rehabilitering och återhämtning', en: 'Rehabilitation and recovery'},
        {da: 'Ældrebolig - anvisning', sv: 'Äldrebostad - anvisning', en: 'Senior housing - allocation'},
        {da: 'Aflastning for pårørende', sv: 'Avlastning för anhöriga', en: 'Respite for relatives'}
    ],
    'housing': [
        {da: 'Boligstøtte - husleje eller boligydelse', sv: 'Bostadsbidrag - hyra eller bostadstillägg', en: 'Housing benefit - rent or housing allowance'},
        {da: 'Boliganvisning - kommunal', sv: 'Bostadsanvisning - kommunal', en: 'Housing allocation - municipal'},
        {da: 'Huslejenævn - klage over husleje', sv: 'Hyresnämnd - klagomål över hyra', en: 'Rent tribunal - complaint about rent'},
        {da: 'Udsættelse fra bolig - fogedsager', sv: 'Vräkning från bostad', en: 'Eviction from housing'},
        {da: 'Tilskud til indskud og depositum', sv: 'Bidrag till deposition', en: 'Grant for deposit'},
        {da: 'Nødherberg og akut midlertidig bolig', sv: 'Nödboende och akut tillfällig bostad', en: 'Emergency shelter and temporary housing'},
        {da: 'Husbånd - boligsocial medarbejder', sv: 'Boendestödjare', en: 'Housing support worker'},
        {da: 'Tilbagebetaling af boligstøtte', sv: 'Återbetalning av bostadsstöd', en: 'Repayment of housing support'}
    ],
    'economy': [
        {da: 'Gældssanering - ansøgning og afslag', sv: 'Skuldsanering - ansökan och avslag', en: 'Debt restructuring - application and rejection'},
        {da: 'Budgetrådgivning - kommunal', sv: 'Budgetrådgivning - kommunal', en: 'Budget counseling - municipal'},
        {da: 'Hjælp til enkeltudgifter - særligt formål', sv: 'Hjälp till enskilda utgifter', en: 'Help with single expenses'},
        {da: 'Økonomisk rådgivning og vejledning', sv: 'Ekonomisk rådgivning och vägledning', en: 'Financial advice and guidance'},
        {da: 'Tilbagebetaling af sociale ydelser', sv: 'Återbetalning av sociala förmåner', en: 'Repayment of social benefits'}
    ]
};

function selectCategory(category) {
    selectedCategory = category;
    document.querySelectorAll('.category-card').forEach(card => {
        card.classList.remove('active');
    });
    document.querySelector(`[data-category="${category}"]`).classList.add('active');
    
    // Auto-select in complaint form
    const select = document.querySelector('select[name="case_category"]');
    if (select) {
        select.value = category;
        updateSubcategories(); // Trigger subcategory update
    }
}

// Update subcategories when main category changes
function updateSubcategories() {
    const category = document.getElementById('case_category').value;
    const container = document.getElementById('subcategory-container');
    const select = document.getElementById('subcategory');
    
    if (!category || !subcategories[category]) {
        container.style.display = 'none';
        select.removeAttribute('required');
        return;
    }
    
    const placeholder = lang === 'da' ? 'Vælg specifik sagstype...' : (lang === 'sv' ? 'Välj specifik ärendetyp...' : 'Select specific case type...');
    select.innerHTML = `<option value="">${placeholder}</option>`;
    
    subcategories[category].forEach(sub => {
        const text = lang === 'da' ? sub.da : (lang === 'sv' ? sub.sv : sub.en);
        const value = sub.da; // Always use Danish as value for backend consistency
        select.innerHTML += `<option value="${value}">${text}</option>`;
    });
    
    container.style.display = 'block';
    select.setAttribute('required', 'required');
}

// Update authority field suggestions based on complaint type
function updateAuthorityOptions() {
    const type = document.getElementById('complaint_type').value;
    const authorityField = document.getElementById('authority');
    
    const suggestions = {
        'kommune_genoptagelse': lang === 'da' ? 'Din kommune (f.eks. København Kommune)' : (lang === 'sv' ? 'Din kommun' : 'Your municipality'),
        'kommune_klage': lang === 'da' ? 'Din kommune (f.eks. København Kommune)' : (lang === 'sv' ? 'Din kommun' : 'Your municipality'),
        'ankestyrelsen_familie': 'Ankestyrelsen - Familie og Beskæftigelse',
        'ankestyrelsen_beskæftigelse': 'Ankestyrelsen - Familie og Beskæftigelse',
        'ankestyrelsen_handicap': 'Ankestyrelsen - Handicap og Social',
        'ankestyrelsen_social': 'Ankestyrelsen - Handicap og Social',
        'patientombuddet': 'Patientombuddet',
        'sundhedsstyrelsen': 'Sundhedsstyrelsen',
        'datatilsynet': 'Datatilsynet',
        'ligebehandlingsnævnet': 'Ligebehandlingsnævnet',
        'huslejenævn': lang === 'da' ? 'Huslejenævnet i din kommune' : (lang === 'sv' ? 'Hyresnämnden i din kommun' : 'Rent tribunal in your municipality')
    };
    
    if (suggestions[type]) {
        authorityField.placeholder = suggestions[type];
    }
}

function switchTab(tabName) {
    // Update buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    
    // Update content
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    document.getElementById(`tab-${tabName}`).classList.add('active');
}

// Complaint form submission
document.getElementById('complaint-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalHTML = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = lang === 'da' ? '⏳ Kate AI arbejder på din klage...' : (lang === 'sv' ? '⏳ Kate AI arbetar...' : '⏳ Kate AI is working...');
    
    try {
        const response = await fetch('<?php echo admin_url('admin-post.php'); ?>', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('complaint-output').textContent = result.complaint;
            document.getElementById('complaint-result').classList.remove('hidden');
            document.getElementById('complaint-form').scrollIntoView({ behavior: 'smooth' });
        } else {
            alert(lang === 'da' ? 'Fejl: ' + result.message : 'Error: ' + result.message);
        }
    } catch (error) {
        alert(lang === 'da' ? 'Der opstod en fejl. Prøv igen.' : (lang === 'sv' ? 'Ett fel uppstod.' : 'An error occurred.'));
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
    }
});

function copyComplaint() {
    const text = document.getElementById('complaint-output').textContent;
    navigator.clipboard.writeText(text).then(() => {
        alert(lang === 'da' ? '✅ Klagen er kopieret!' : (lang === 'sv' ? '✅ Klagomålet kopierat!' : '✅ Complaint copied!'));
    });
}

function downloadComplaint() {
    const text = document.getElementById('complaint-output').textContent;
    const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'klage_' + new Date().toISOString().split('T')[0] + '.txt';
    a.click();
    URL.revokeObjectURL(url);
}
</script>

    </div><!-- .platform-content -->
</div><!-- .platform-container -->

<?php get_footer(); ?>


