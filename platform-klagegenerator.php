<?php
/**
 * Template Name: Platform - Klagegenerator
 */

if (!session_id()) session_start();

if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth'));
    exit;
}

$user = rtf_get_current_user();
$lang = $_GET['lang'] ?? $user['language_preference'] ?? 'da_DK';
$lang_code = substr($lang, 0, 2); // da, sv, en

// Load translations
require_once get_template_directory() . '/translations.php';
$t = rtf_get_all_translations($lang_code);

global $wpdb;
$docs_table = $wpdb->prefix . 'rtf_platform_documents';

// PDF GENERATION HANDLER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_complaint'])) {
    require_once get_template_directory() . '/includes/PdfGenerator.php';
    
    $complaint_data = [
        'date' => date('d-m-Y'),
        'name' => sanitize_text_field($_POST['complainant_name'] ?? $user['username']),
        'address' => sanitize_text_field($_POST['complainant_address'] ?? ''),
        'email' => sanitize_email($_POST['complainant_email'] ?? $user['email']),
        'subject' => sanitize_text_field($_POST['complaint_subject'] ?? ''),
        'description' => sanitize_textarea_field($_POST['complaint_description'] ?? ''),
        'complaint_points' => array_map('sanitize_textarea_field', $_POST['complaint_points'] ?? []),
        'desired_outcome' => sanitize_textarea_field($_POST['desired_outcome'] ?? '')
    ];
    
    try {
        $result = \RTF\Platform\PdfGenerator::generateComplaint($complaint_data);
        
        if ($result['success']) {
            // Download PDF
            \RTF\Platform\PdfGenerator::download($result['mpdf'], $result['filename']);
            exit;
        } else {
            $pdf_error = $result['error'];
        }
    } catch (\Exception $e) {
        $pdf_error = $e->getMessage();
    }
}

// Get user's documents for selection
$documents = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $docs_table WHERE user_id = %d ORDER BY created_at DESC",
    $user['id']
));

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

.generator-section {
    background: white;
    border: 1px solid #dbeafe;
    border-radius: 18px;
    padding: 2rem;
}

.generator-section h1 {
    margin: 0 0 1rem 0;
    font-size: 1.8rem;
    color: #0f172a;
}

.generator-steps {
    display: flex;
    justify-content: space-between;
    margin: 2rem 0;
    padding: 0 1rem;
}

.step {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    font-size: 0.9rem;
}

.step.active {
    color: #2563eb;
    font-weight: 600;
}

.step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e0f2fe;
    color: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #475569;
}

.form-group input[type="text"],
.form-group input[type="date"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.875rem;
    border: 1px solid #dbeafe;
    border-radius: 8px;
    font-size: 0.95rem;
    font-family: inherit;
}

.form-group textarea {
    min-height: 150px;
    resize: vertical;
}

.documents-selection {
    background: #f9fafb;
    border: 1px solid #dbeafe;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.documents-selection h3 {
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
    color: #2563eb;
}

.doc-checkbox {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem;
    background: white;
    border: 1px solid #dbeafe;
    border-radius: 8px;
    margin-bottom: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.doc-checkbox:hover {
    background: #e0f2fe;
}

.doc-checkbox input[type="checkbox"] {
    width: 20px;
    height: 20px;
}

.kate-suggestions {
    background: #eff6ff;
    border: 2px solid #2563eb;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.kate-suggestions h3 {
    margin: 0 0 1rem 0;
    color: #2563eb;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.kate-suggestions ul {
    margin: 0;
    padding-left: 1.5rem;
    color: #475569;
}

.kate-suggestions ul li {
    margin-bottom: 0.75rem;
    line-height: 1.6;
}

.btn-generate {
    padding: 1rem 2.5rem;
    background: linear-gradient(135deg, #60a5fa, #2563eb);
    color: white;
    border: none;
    border-radius: 999px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
}

.btn-secondary {
    padding: 1rem 2rem;
    background: #e0f2fe;
    color: #2563eb;
    border: 1px solid #93c5fd;
    border-radius: 999px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

@media (max-width: 768px) {
    .platform-layout {
        grid-template-columns: 1fr;
    }
    
    .platform-sidebar {
        position: static;
    }
    
    .generator-steps {
        flex-direction: column;
        gap: 1rem;
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
            <li><a href="<?php echo home_url('/platform-kate-ai'); ?>">🤖 Kate AI</a></li>
            <li><a href="<?php echo home_url('/platform-indstillinger'); ?>">⚙️ Indstillinger</a></li>
        </ul>
    </aside>
    
    <main class="platform-content">
        <div class="generator-section">
            <h1>📝 <?php echo $t['complaint_generator_title']; ?></h1>
            <p style="color: #64748b; margin-bottom: 2rem;">
                <?php 
                if ($lang_code === 'da') {
                    echo 'Lav en professionel klage med hjælp fra Kate AI. Vælg mellem kommunal klage, Ankestyrelsen, eller international klage til EMK/EU.';
                } elseif ($lang_code === 'sv') {
                    echo 'Skapa ett professionellt klagomål med hjälp av Kate AI. Välj mellan kommunalt klagomål, Överklagan eller internationellt klagomål till EMRK/EU.';
                } else {
                    echo 'Create a professional complaint with help from Kate AI. Choose between municipal complaint, Appeals Board, or international complaint to ECHR/EU.';
                }
                ?>
            </p>
            
            <div class="generator-steps">
                <div class="step active">
                    <span class="step-number">1</span>
                    <span>Grundoplysninger</span>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <span>Vælg dokumenter</span>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span>Beskriv situation</span>
                </div>
                <div class="step">
                    <span class="step-number">4</span>
                    <span>Generer klage</span>
                </div>
            </div>
            
            <form method="POST" id="complaint-form">
                <div class="form-group">
                    <label><?php echo $t['complaint_to']; ?> *</label>
                    <select name="complaint_destination" required onchange="updateComplaintInfo(this.value)">
                        <option value=""><?php echo $lang_code === 'da' ? 'Vælg destination...' : ($lang_code === 'sv' ? 'Välj destination...' : 'Select destination...'); ?></option>
                        <option value="municipality"><?php echo $t['municipal_complaint']; ?></option>
                        <option value="ankestyrelsen"><?php echo $t['ankestyrelsen']; ?></option>
                        <option value="ombudsman"><?php echo $t['ombudsmand']; ?></option>
                        <option value="echr">🇪🇺 <?php echo $t['echr_complaint']; ?></option>
                        <option value="eu_commission">🇪🇺 <?php echo $t['european_commission']; ?></option>
                        <option value="un_child_committee">🌍 <?php echo $t['child_committee']; ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><?php echo $t['output_language']; ?> *</label>
                    <select name="output_language" required>
                        <option value="da">🇩🇰 Dansk</option>
                        <option value="sv">🇸🇪 Svenska</option>
                        <option value="en" <?php echo $lang_code === 'en' ? 'selected' : ''; ?>>🇬🇧 English</option>
                        <option value="fr">🇫🇷 Français (for EMK/EU)</option>
                    </select>
                    <p style="font-size: 0.85rem; color: #64748b; margin: 0.5rem 0 0 0;">
                        <?php 
                        if ($lang_code === 'da') {
                            echo '💡 EMK og EU klager kan genereres på engelsk eller fransk';
                        } elseif ($lang_code === 'sv') {
                            echo '💡 EMRK och EU klagomål kan genereras på engelska eller franska';
                        } else {
                            echo '💡 ECHR and EU complaints can be generated in English or French';
                        }
                        ?>
                    </p>
                </div>
                
                <div class="form-group">
                    <label><?php echo $lang_code === 'da' ? 'Hvad klager du over?' : ($lang_code === 'sv' ? 'Vad klagar du över?' : 'What are you complaining about?'); ?> *</label>
                    <select name="complaint_type" required>
                        <option value=""><?php echo $lang_code === 'da' ? 'Vælg type...' : ($lang_code === 'sv' ? 'Välj typ...' : 'Select type...'); ?></option>
                        <option value="anbringelse"><?php echo $lang_code === 'da' ? 'Anbringelse uden samtykke' : ($lang_code === 'sv' ? 'Omhändertagande utan samtycke' : 'Foster care without consent'); ?></option>
                        <option value="tvangsfjernelse"><?php echo $lang_code === 'da' ? 'Tvangsfjernelse' : ($lang_code === 'sv' ? 'Tvångsomhändertagande' : 'Forced removal'); ?></option>
                        <option value="samvaer"><?php echo $lang_code === 'da' ? 'Samværsbegrænsning' : ($lang_code === 'sv' ? 'Umgängesbegränsning' : 'Visitation restrictions'); ?></option>
                        <option value="handleplan"><?php echo $lang_code === 'da' ? 'Handleplan' : ($lang_code === 'sv' ? 'Handlingsplan' : 'Action plan'); ?></option>
                        <option value="magtanvendelse"><?php echo $lang_code === 'da' ? 'Magtanvendelse' : ($lang_code === 'sv' ? 'Maktanvändning' : 'Use of force'); ?></option>
                        <option value="human_rights"><?php echo $lang_code === 'da' ? 'Menneskerettighedskrænkelse' : ($lang_code === 'sv' ? 'Kränkning av mänskliga rättigheter' : 'Human rights violation'); ?></option>
                        <option value="andet"><?php echo $lang_code === 'da' ? 'Andet' : ($lang_code === 'sv' ? 'Annat' : 'Other'); ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Afgørelsesdato *</label>
                    <input type="date" name="decision_date" required>
                    <p style="font-size: 0.85rem; color: #64748b; margin: 0.5rem 0 0 0;">
                        ⚠️ Du har 4 uger fra denne dato til at klage
                    </p>
                </div>
                
                <div class="form-group">
                    <label>Myndighed/Kommune *</label>
                    <input type="text" name="authority" placeholder="F.eks. København Kommune" required>
                </div>
                
                <div class="documents-selection">
                    <h3>📄 Vælg relevante dokumenter</h3>
                    <?php if (empty($documents)): ?>
                        <p style="color: #64748b;">
                            Du har ingen uploadede dokumenter. <a href="<?php echo home_url('/platform-dokumenter'); ?>" style="color: #2563eb;">Upload dokumenter her</a>
                        </p>
                    <?php else: ?>
                        <?php foreach ($documents as $doc): ?>
                            <label class="doc-checkbox">
                                <input type="checkbox" name="selected_docs[]" value="<?php echo $doc->id; ?>">
                                <span><?php echo esc_html($doc->title); ?> (<?php echo esc_html($doc->document_type); ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="kate-suggestions">
                    <h3>🤖 Kate AI's anbefalinger</h3>
                    <p style="margin: 0 0 1rem 0; color: #475569;">
                        Baseret på din situation anbefaler Kate at inkludere følgende i din klage:
                    </p>
                    <ul>
                        <li>Tydelig beskrivelse af, hvad du klager over (afgørelsens indhold)</li>
                        <li>Begrundelse for hvorfor afgørelsen er forkert (juridiske og faktuelle grunde)</li>
                        <li>Henvisning til relevant lovgivning (Barnets Lov, Forvaltningsloven)</li>
                        <li>Dokumentation der understøtter din klage</li>
                        <li>Ønske om opsættende virkning, hvis relevant</li>
                        <li>Anmodning om partshøring, hvis ikke modtaget</li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <label>Beskriv din situation og klagegrunde *</label>
                    <textarea name="complaint_text" placeholder="Forklar detaljeret hvorfor du klager. Kate AI vil hjælpe med at formulere dette professionelt..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="suspensive_effect" value="1" style="width: auto; margin-right: 0.5rem;">
                        Anmod om opsættende virkning (afgørelsen skal ikke træde i kraft før klagesagen er behandlet)
                    </label>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" name="generate_complaint" class="btn-generate">
                        🤖 Generer klage med Kate AI
                    </button>
                    <a href="<?php echo home_url('/platform-sagshjaelp'); ?>" class="btn-secondary">
                        ← Tilbage
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<?php get_footer(); ?>
