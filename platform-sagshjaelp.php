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
                        <label>📂 Sagskategori *</label>
                        <select name="case_category" class="form-control" required>
                            <option value="">Vælg kategori...</option>
                            <?php foreach ($t['categories'] as $key => $cat): ?>
                                <option value="<?php echo $key; ?>"><?php echo $cat['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>🏛️ Myndighed du klager over *</label>
                        <input type="text" name="authority" class="form-control" 
                               placeholder="F.eks. København Kommune, Aarhus Kommune, Socialstyrelsen" required>
                    </div>

                    <div class="form-group">
                        <label>📋 Afgørelsens journalnummer (hvis du har det)</label>
                        <input type="text" name="case_number" class="form-control" 
                               placeholder="F.eks. 2024-12345">
                    </div>

                    <div class="form-group">
                        <label>📅 Dato for afgørelsen</label>
                        <input type="date" name="decision_date" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>✍️ Beskriv din klage i detaljer *</label>
                        <textarea name="complaint_text" class="form-control" rows="12" required 
                                  placeholder="Beskriv så detaljeret som muligt:
- Hvad handler sagen om?
- Hvilken afgørelse er du uenig i?
- Hvorfor mener du, afgørelsen er forkert?
- Hvilke fakta mangler myndigheden at tage højde for?
- Hvilke love/paragraffer mener du støtter din sag?

Jo mere information, jo bedre kan Kate AI hjælpe dig."></textarea>
                    </div>

                    <div class="form-group">
                        <label>📎 Vedhæft relevante dokumenter</label>
                        <?php
                        $docs = $wpdb->get_results($wpdb->prepare(
                            "SELECT id, title, document_type FROM {$wpdb->prefix}rtf_platform_documents 
                             WHERE user_id = %d ORDER BY created_at DESC",
                            $current_user->id
                        ));
                        ?>
                        <?php if (!empty($docs)): ?>
                            <div style="max-height: 200px; overflow-y: auto; border: 2px solid var(--border); border-radius: 8px; padding: 1rem;">
                                <?php foreach ($docs as $doc): ?>
                                    <label style="display: block; padding: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" name="attached_docs[]" value="<?php echo $doc->id; ?>">
                                        <?php echo esc_html($doc->title); ?> (<?php echo esc_html($doc->document_type); ?>)
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--text-light);">
                                Du har ingen uploadede dokumenter. 
                                <a href="<?php echo home_url('/platform-dokumenter/?lang='.$lang); ?>" 
                                   style="color: var(--primary);">Upload dokumenter her</a>
                            </p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 1.2rem;">
                        🤖 Generer Professionel Klage med Kate AI
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
let selectedCategory = null;

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
    submitBtn.disabled = true;
    submitBtn.textContent = '⏳ Genererer klage...';
    
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
            alert('Fejl: ' + result.message);
        }
    } catch (error) {
        alert('Der opstod en fejl. Prøv igen.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '🤖 Generer Professionel Klage med Kate AI';
    }
});

function copyComplaint() {
    const text = document.getElementById('complaint-output').textContent;
    navigator.clipboard.writeText(text).then(() => {
        alert('✅ Klagen er kopieret til udklipsholderen!');
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


