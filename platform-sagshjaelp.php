<?php
/**
 * Template Name: Platform - Sagshjælp (Legal Help)
 */

if (!session_id()) session_start();

if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth'));
    exit;
}

$user = rtf_get_current_user();
$lang = rtf_get_lang();

// Simple translations
$t = array(
    'da' => array(
        'case_help_title' => 'Sagshjælp',
        'disclaimer_not_lawyer' => 'Vigtigt: Vi er ikke advokater',
        'need_professional_help' => 'Har du brug for professionel juridisk hjælp?',
        'conflict_mediation' => 'Konflikthåndtering',
        'party_representation' => 'Partsrepræsentation',
        'case_review' => 'Sagsgennemgang',
        'documentation_importance' => 'Dokumentation er ALT i din sag',
        'tip_record_meetings' => 'Optag ALLE møder',
        'tip_transcribe' => 'Få transskriptioner',
        'tip_save_emails' => 'Gem alle emails og SMS',
        'tip_keep_diary' => 'Før dagbog',
        'tip_take_photos' => 'Tag billeder',
        'tip_witnesses' => 'Få vidneudsagn'
    ),
    'sv' => array(
        'case_help_title' => 'Ärendehjälp',
        'disclaimer_not_lawyer' => 'Viktigt: Vi är inte advokater',
        'need_professional_help' => 'Behöver du professionell juridisk hjälp?',
        'conflict_mediation' => 'Konflikthantering',
        'party_representation' => 'Partsrepresentation',
        'case_review' => 'Ärendegranskning',
        'documentation_importance' => 'Dokumentation är ALLT i ditt ärende',
        'tip_record_meetings' => 'Spela in ALLA möten',
        'tip_transcribe' => 'Få transkriptioner',
        'tip_save_emails' => 'Spara alla e-post och SMS',
        'tip_keep_diary' => 'För dagbok',
        'tip_take_photos' => 'Ta bilder',
        'tip_witnesses' => 'Få vittnesmål'
    ),
    'en' => array(
        'case_help_title' => 'Case Help',
        'disclaimer_not_lawyer' => 'Important: We are not lawyers',
        'need_professional_help' => 'Do you need professional legal help?',
        'conflict_mediation' => 'Conflict Mediation',
        'party_representation' => 'Party Representation',
        'case_review' => 'Case Review',
        'documentation_importance' => 'Documentation is EVERYTHING in your case',
        'tip_record_meetings' => 'Record ALL meetings',
        'tip_transcribe' => 'Get transcriptions',
        'tip_save_emails' => 'Save all emails and SMS',
        'tip_keep_diary' => 'Keep a diary',
        'tip_take_photos' => 'Take photos',
        'tip_witnesses' => 'Get witness statements'
    )
);
$txt = $t[$lang];

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

.help-intro {
    background: white;
    border: 1px solid #dbeafe;
    border-radius: 18px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.help-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.help-card {
    background: white;
    border: 1px solid #dbeafe;
    border-radius: 18px;
    padding: 2rem;
    transition: all 0.2s ease;
}

.help-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(37, 99, 235, 0.15);
}

.help-card h3 {
    margin: 0 0 1rem 0;
    font-size: 1.2rem;
    color: #2563eb;
}

.help-card p {
    margin: 0 0 1.5rem 0;
    color: #64748b;
    line-height: 1.6;
}

.help-card ul {
    margin: 0 0 1.5rem 0;
    padding-left: 1.5rem;
    color: #475569;
}

.help-card ul li {
    margin-bottom: 0.5rem;
}

.btn-help {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #60a5fa, #2563eb);
    color: white;
    border: none;
    border-radius: 999px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
}

.kate-embed {
    background: white;
    border: 2px solid #2563eb;
    border-radius: 18px;
    padding: 2rem;
    margin-top: 2rem;
}

@media (max-width: 768px) {
    .platform-layout {
        grid-template-columns: 1fr;
    }
    
    .platform-sidebar {
        position: static;
    }
    
    .help-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="platform-layout" style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <?php get_template_part('template-parts/platform-sidebar'); ?>
    
    <main class="platform-content">
        <!-- HERO SECTION -->
        <div class="hero-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 3rem; margin-bottom: 2rem; color: white; box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);">
            <h1 style="margin: 0 0 1rem 0; font-size: 2.5rem; font-weight: 700;">⚖️ <?php echo $txt['case_help_title']; ?></h1>
            <p style="font-size: 1.2rem; margin: 0; opacity: 0.95; line-height: 1.6;">
                <?php 
                if ($lang_code === 'da') {
                    echo 'Professionel sagshjælp, klage generator og juridisk vejledning - Alt du behøver for at navigere i dit familie- eller socialsag.';
                } elseif ($lang_code === 'sv') {
                    echo 'Professionell ärendehjälp, klagomålsgenerator och juridisk vägledning - Allt du behöver för att navigera i ditt familje- eller socialärende.';
                } else {
                    echo 'Professional case help, complaint generator and legal guidance - Everything you need to navigate your family or social case.';
                }
                ?>
            </p>
        </div>

        <!-- NAVIGATION TABS -->
        <div class="tabs-container" style="background: white; border-radius: 16px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <div class="tabs-nav" style="display: flex; gap: 1rem; flex-wrap: wrap; border-bottom: 2px solid #e2e8f0; padding-bottom: 1rem;">
                <button class="tab-btn active" onclick="showTab('overview')" style="padding: 0.75rem 1.5rem; border: none; background: #667eea; color: white; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                    📋 Oversigt
                </button>
                <button class="tab-btn" onclick="showTab('complaint')" style="padding: 0.75rem 1.5rem; border: none; background: #f1f5f9; color: #475569; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                    📝 Klage Generator
                </button>
                <button class="tab-btn" onclick="showTab('guidance')" style="padding: 0.75rem 1.5rem; border: none; background: #f1f5f9; color: #475569; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                    💡 Råd & Vejledning
                </button>
                <button class="tab-btn" onclick="showTab('documentation')" style="padding: 0.75rem 1.5rem; border: none; background: #f1f5f9; color: #475569; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                    📄 Dokumentation
                </button>
                <button class="tab-btn" onclick="showTab('kate')" style="padding: 0.75rem 1.5rem; border: none; background: #f1f5f9; color: #475569; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                    🤖 Kate AI
                </button>
            </div>
        </div>

        <!-- TAB CONTENT: OVERVIEW -->
        </div>

        <!-- TAB CONTENT: OVERVIEW -->
        <div id="tab-overview" class="tab-content">
            
            <!-- QUICK ACTIONS -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
                <div class="action-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem; border-radius: 16px; color: white; cursor: pointer; transition: transform 0.3s;" onclick="showTab('complaint')">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
                    <h3 style="margin: 0 0 0.5rem 0; color: white;">Klage Generator</h3>
                    <p style="margin: 0; opacity: 0.9;">Opret professionelle klager over afgørelser</p>
                </div>
                
                <div class="action-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 2rem; border-radius: 16px; color: white; cursor: pointer; transition: transform 0.3s;" onclick="showTab('guidance')">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">💡</div>
                    <h3 style="margin: 0 0 0.5rem 0; color: white;">Råd & Vejledning</h3>
                    <p style="margin: 0; opacity: 0.9;">Få juridisk vejledning til din sag</p>
                </div>
                
                <div class="action-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 2rem; border-radius: 16px; color: white; cursor: pointer; transition: transform 0.3s;" onclick="showTab('kate')">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🤖</div>
                    <h3 style="margin: 0 0 0.5rem 0; color: white;">Kate AI</h3>
                    <p style="margin: 0; opacity: 0.9;">Stil spørgsmål til Kate AI</p>
                </div>
            </div>
        
        <!-- DISCLAIMER -->
        <div style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="margin: 0 0 1rem 0; color: #856404;">⚠️ <?php echo $txt['disclaimer_not_lawyer']; ?></h3>
            <p style=\"margin: 0 0 1rem 0; color: #856404; line-height: 1.6;\">
                <?php 
                if ($lang_code === 'da') {
                    echo 'Vi hjælper dig til bedre at hjælpe dig selv gennem selvstændig sagsopbygning, dokumentation og forståelse af din sag. Dette er IKKE juridisk rådgivning fra en advokat.';
                } elseif ($lang_code === 'sv') {
                    echo 'Vi hjälper dig att bättre hjälpa dig själv genom självständig ärendeuppbyggnad, dokumentation och förståelse av ditt ärende. Detta är INTE juridisk rådgivning från en advokat.';
                } else {
                    echo 'We help you better help yourself through independent case building, documentation and understanding of your case. This is NOT legal advice from a lawyer.';
                }
                ?>
            </p>
            <div style=\"background: white; border-radius: 8px; padding: 1rem; margin-top: 1rem;\">
                <strong style=\"color: #2563eb;\"><?php echo $txt['need_professional_help']; ?></strong><br>
                <ul style=\"margin: 0.5rem 0 0 0; padding-left: 1.5rem; color: #475569;\">
                    <li><strong>\ud83e\udd1d <?php echo $txt['conflict_mediation']; ?></strong></li>
                    <li><strong>\ud83d\udcc4 <?php echo $txt['party_representation']; ?></strong></li>
                    <li><strong>\ud83d\udd0d <?php echo $txt['case_review']; ?></strong></li>
                    <li style=\"margin-top: 0.5rem;\">📧 Email: kontakt@rettiltifamilie.dk</li>
                    <li>📞 Telefon: +45 123 456 78</li>
                </ul>
            </div>
        </div>
        
        <!-- DOCUMENTATION GUIDE -->
        <div style=\"background: #e0f2fe; border: 2px solid #2563eb; border-radius: 12px; padding: 2rem; margin-bottom: 2rem;\">
            <h2 style=\"margin: 0 0 1rem 0; color: #2563eb;\"><?php echo $txt['documentation_importance']; ?></h2>
            <div style=\"display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; margin-top: 1.5rem;\">
                <div style=\"background: white; border-radius: 8px; padding: 1.25rem;\">
                    <div style=\"font-size: 2rem; margin-bottom: 0.5rem;\">\ud83c\udf99\ufe0f</div>
                    <strong><?php echo $txt['tip_record_meetings']; ?></strong>
                    <p style=\"margin: 0.5rem 0 0 0; color: #64748b; font-size: 0.9rem;\">
                        <?php 
                        if ($lang_code === 'da') {
                            echo 'Det er lovligt i både Danmark og Sverige at optage egne samtaler uden den andens samtykke (til privat brug). Optagelser kan bruges som bevis i retten.';
                        } elseif ($lang_code === 'sv') {
                            echo 'Det är lagligt i både Danmark och Sverige att spela in egna samtal utan den andres samtycke (för privat bruk). Inspelningar kan användas som bevis i rätten.';
                        } else {
                            echo 'It is legal in both Denmark and Sweden to record your own conversations without the other party\'s consent (for private use). Recordings can be used as evidence in court.';
                        }
                        ?>
                    </p>
                </div>
                
                <div style=\"background: white; border-radius: 8px; padding: 1.25rem;\">
                    <div style=\"font-size: 2rem; margin-bottom: 0.5rem;\">\ud83d\udcc4</div>
                    <strong><?php echo $txt['tip_transcribe']; ?></strong>
                    <p style=\"margin: 0.5rem 0 0 0; color: #64748b; font-size: 0.9rem;\">
                        <?php 
                        if ($lang_code === 'da') {
                            echo 'Få professionelle transskriptioner (notatudtag) af alle møder. Dette viser hvad der blev sagt og kan fremvise modsætninger i myndighedernes forklaringer.';
                        } elseif ($lang_code === 'sv') {
                            echo 'Få professionella transkriberingar av alla möten. Detta visar vad som sades och kan påvisa motsägelser i myndigheternas förklaringar.';
                        } else {
                            echo 'Get professional transcriptions of all meetings. This shows what was said and can reveal contradictions in authorities' . "'" . ' statements.';
                        }
                        ?>
                    </p>
                </div>
                
                <div style=\"background: white; border-radius: 8px; padding: 1.25rem;\">
                    <div style=\"font-size: 2rem; margin-bottom: 0.5rem;\">\ud83d\udce7</div>
                    <strong><?php echo $txt['tip_save_emails']; ?></strong>
                    <p style=\"margin: 0.5rem 0 0 0; color: #64748b; font-size: 0.9rem;\">
                        <?php 
                        if ($lang_code === 'da') {
                            echo 'Gem ALLE emails, SMS' . "'" . 'er og beskeder i din sag. Tag screenshots af vigtige beskeder. Print emails og gem i mapper med dato.';
                        } elseif ($lang_code === 'sv') {
                            echo 'Spara ALLA e-postmeddelanden, SMS och meddelanden i ditt ärende. Ta skärmdumpar av viktiga meddelanden. Skriv ut e-post och spara i mappar med datum.';
                        } else {
                            echo 'Save ALL emails, SMS and messages in your case. Take screenshots of important messages. Print emails and save in folders with dates.';
                        }
                        ?>
                    </p>
                </div>
                
                <div style=\"background: white; border-radius: 8px; padding: 1.25rem;\">
                    <div style=\"font-size: 2rem; margin-bottom: 0.5rem;\">\ud83d\udcd4</div>
                    <strong><?php echo $txt['tip_keep_diary']; ?></strong>
                    <p style=\"margin: 0.5rem 0 0 0; color: #64748b; font-size: 0.9rem;\">
                        <?php 
                        if ($lang_code === 'da') {
                            echo 'Før dagbog over ALT: møder, telefonopkald, hændelser med børnene. Skriv dato, tidspunkt og hvad der skete. Dette er stærkt bevis.';
                        } elseif ($lang_code === 'sv') {
                            echo 'För dagbok över ALLT: möten, telefonsamtal, händelser med barnen. Skriv datum, tid och vad som hände. Detta är stark bevisning.';
                        } else {
                            echo 'Keep a diary of EVERYTHING: meetings, phone calls, events with the children. Write date, time and what happened. This is strong evidence.';
                        }
                        ?>
                    </p>
                </div>
                
                <div style=\"background: white; border-radius: 8px; padding: 1.25rem;\">
                    <div style=\"font-size: 2rem; margin-bottom: 0.5rem;\">\ud83d\udcf8</div>
                    <strong><?php echo $txt['tip_take_photos']; ?></strong>
                    <p style=\"margin: 0.5rem 0 0 0; color: #64748b; font-size: 0.9rem;\">
                        <?php 
                        if ($lang_code === 'da') {
                            echo 'Tag billeder af alle dokumenter, brevkassen, din bolig (hvis relevant), børnenes trivsel. Billeder taler mere end ord.';
                        } elseif ($lang_code === 'sv') {
                            echo 'Ta bilder av alla dokument, brevlådan, din bostad (om relevant), barnens välbefinnande. Bilder säger mer än ord.';
                        } else {
                            echo 'Take photos of all documents, mailbox, your home (if relevant), children' . "'" . 's well-being. Pictures speak louder than words.';
                        }
                        ?>
                    </p>
                </div>
                
                <div style=\"background: white; border-radius: 8px; padding: 1.25rem;\">
                    <div style=\"font-size: 2rem; margin-bottom: 0.5rem;\">\ud83d\udc65</div>
                    <strong><?php echo $txt['tip_witnesses']; ?></strong>
                    <p style=\"margin: 0.5rem 0 0 0; color: #64748b; font-size: 0.9rem;\">
                        <?php 
                        if ($lang_code === 'da') {
                            echo 'Få vidneudsagn fra personer der kender dig og dine børn. Læger, lærere, naboer, venner kan alle vidne om din forældreevne.';
                        } elseif ($lang_code === 'sv') {
                            echo 'Få vittnesutsagor från personer som känner dig och dina barn. Läkare, lärare, grannar, vänner kan alla vittna om din föräldraförmåga.';
                        } else {
                            echo 'Get witness statements from people who know you and your children. Doctors, teachers, neighbors, friends can all testify about your parenting.';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="help-grid"
            <div class="help-card">
                <h3>📄 Klage over afgørelse</h3>
                <p>Lær hvordan du klager over en afgørelse fra kommunen eller Ankestyrelsen.</p>
                <ul>
                    <li>4 ugers klagefrist</li>
                    <li>Begrundelse og partshøring</li>
                    <li>Klagevejledning</li>
                    <li>Dokumentation</li>
                </ul>
                <a href="<?php echo home_url('/platform-klagegenerator'); ?>" class="btn-help">Start klagegenerator →</a>
            </div>
            
            <div class="help-card">
                <h3>🔍 Aktindsigt</h3>
                <p>Få adgang til din sag og dokumenter hos myndighederne.</p>
                <ul>
                    <li>Forvaltningsloven §9</li>
                    <li>7 dages svarfrist</li>
                    <li>Hvad kan du få aktindsigt i?</li>
                    <li>Undtagelser og begrænsninger</li>
                </ul>
                <a href="<?php echo home_url('/platform-kate-ai'); ?>" class="btn-help">Spørg Kate AI →</a>
            </div>
            
            <div class="help-card">
                <h3>👨‍👩‍👧 Anbringelse</h3>
                <p>Information om anbringelse med og uden samtykke.</p>
                <ul>
                    <li>Barnets Lov §76</li>
                    <li>Årsager til anbringelse</li>
                    <li>Dine rettigheder som forælder</li>
                    <li>Samvær og kontakt</li>
                </ul>
                <a href="<?php echo home_url('/platform-kate-ai'); ?>" class="btn-help">Læs mere →</a>
            </div>
            
            <div class="help-card">
                <h3>📋 Handleplan</h3>
                <p>Krav til handleplaner og hvordan du bliver inddraget.</p>
                <ul>
                    <li>Barnets Lov §140</li>
                    <li>Indhold og mål</li>
                    <li>Revision hver 6. måned</li>
                    <li>Forældreinddragelse</li>
                </ul>
                <a href="<?php echo home_url('/platform-kate-ai'); ?>" class="btn-help">Få vejledning →</a>
            </div>
            
            <div class="help-card">
                <h3>🤝 Bisidder</h3>
                <p>Din ret til at have en bisidder med til møder.</p>
                <ul>
                    <li>Hvem kan være bisidder?</li>
                    <li>Bisidderens rolle</li>
                    <li>Anmodning om bisidder</li>
                    <li>Kommunens pligter</li>
                </ul>
                <a href="<?php echo home_url('/platform-kate-ai'); ?>" class="btn-help">Få hjælp →</a>
            </div>
            
            <div class="help-card">
                <h3>📊 Dokumentanalyse</h3>
                <p>Få dine dokumenter analyseret med Kate AI's 98% præcision.</p>
                <ul>
                    <li>Afgørelser</li>
                    <li>Handleplaner</li>
                    <li>Børnefaglige undersøgelser</li>
                    <li>Samværsaftaler</li>
                </ul>
                <a href="<?php echo home_url('/platform-dokumenter'); ?>" class="btn-help">Upload dokument →</a>
            </div>
        </div>
        
        <div class="kate-embed">
            <h2 style="margin: 0 0 1.5rem 0; color: #2563eb;">🤖 Spørg Kate AI</h2>
            <p style="margin-bottom: 1.5rem; color: #64748b;">
                Kate kan svare på alle dine juridiske spørgsmål direkte her. Prøv at spørge:
            </p>
            
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem;">
                <button onclick="askKate('Hvordan klager jeg over en afgørelse?')" style="padding: 0.5rem 1rem; background: #e0f2fe; color: #2563eb; border: 1px solid #93c5fd; border-radius: 999px; cursor: pointer; font-weight: 600;">
                    💬 Hvordan klager jeg?
                </button>
                <button onclick="askKate('Hvad skal en handleplan indeholde?')" style="padding: 0.5rem 1rem; background: #e0f2fe; color: #2563eb; border: 1px solid #93c5fd; border-radius: 999px; cursor: pointer; font-weight: 600;">
                    📋 Krav til handleplan
                </button>
                <button onclick="askKate('Hvordan får jeg aktindsigt?')" style="padding: 0.5rem 1rem; background: #e0f2fe; color: #2563eb; border: 1px solid #93c5fd; border-radius: 999px; cursor: pointer; font-weight: 600;">
                    🔍 Aktindsigt
                </button>
            </div>
            
            <a href="<?php echo home_url('/platform-kate-ai'); ?>" class="btn-help">Gå til Kate AI →</a>
        </div>
        
        <!-- BARNETS LOV LOVOPSLAG -->
        <div class="kate-embed" style="margin-top: 2rem;">
            <h2 style="margin: 0 0 1.5rem 0; color: #2563eb;">📖 Barnets Lov - Lovopslag</h2>
            <p style="margin-bottom: 1.5rem; color: #64748b;">
                Slå paragraffer op i Barnets Lov og få dem forklaret på almindeligt dansk med eksempler.
            </p>
            
            <!-- Søg i Barnets Lov -->
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">
                    🔍 Søg i Barnets Lov
                </label>
                <div style="display: flex; gap: 0.5rem;">
                    <input 
                        type="text" 
                        id="lawSearchInput" 
                        placeholder="Søg efter paragraf, emne eller begreb..." 
                        style="flex: 1; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem;"
                    />
                    <button 
                        onclick="searchBarnetsLov()" 
                        style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #60a5fa, #2563eb); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;"
                    >
                        Søg
                    </button>
                </div>
                <div id="lawSearchResults" style="margin-top: 1rem;"></div>
            </div>
            
            <!-- Vigtige paragraffer -->
            <div>
                <h3 style="margin: 0 0 1rem 0; color: #1e293b; font-size: 1.1rem;">⚖️ Vigtige paragraffer</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                    <button onclick="explainLaw('47')" class="law-btn">§ 47 - Barnets ret til at blive hørt</button>
                    <button onclick="explainLaw('51')" class="law-btn">§ 51 - Ret til bisidder</button>
                    <button onclick="explainLaw('76')" class="law-btn">§ 76 - Anbringelse uden samtykke</button>
                    <button onclick="explainLaw('83')" class="law-btn">§ 83 - Samvær og kontakt</button>
                    <button onclick="explainLaw('140')" class="law-btn">§ 140 - Handleplan</button>
                    <button onclick="explainLaw('168')" class="law-btn">§ 168 - Klageadgang</button>
                </div>
            </div>
            
            <div id="lawExplanation" style="margin-top: 2rem; display: none;"></div>
        </div>
        
        <!-- JURIDISK VEJLEDNINGSGENERATOR -->
        <div class="kate-embed" style="margin-top: 2rem;">
            <h2 style="margin: 0 0 1.5rem 0; color: #2563eb;">🎯 Få Personlig Vejledning</h2>
            <p style="margin-bottom: 1.5rem; color: #64748b;">
                Kate kan generere personlig juridisk vejledning baseret på din situation.
            </p>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">
                    Hvad handler din sag om?
                </label>
                <select 
                    id="situationType" 
                    style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem;"
                >
                    <option value="">Vælg situation...</option>
                    <option value="anbringelse">Anbringelse af mit barn</option>
                    <option value="klage">Klage over afgørelse</option>
                    <option value="samvaer">Samvær med anbragte børn</option>
                    <option value="aktindsigt">Aktindsigt i min sag</option>
                    <option value="handleplan">Handleplan</option>
                    <option value="bisidder">Bisidder til møder</option>
                    <option value="boernesamtale">Børnesamtale</option>
                </select>
            </div>
            
            <button 
                onclick="generateGuidance()" 
                style="width: 100%; padding: 1rem; background: linear-gradient(135deg, #60a5fa, #2563eb); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem;"
            >
                📋 Generér Personlig Vejledning
            </button>
            
            <div id="guidanceResult" style="margin-top: 2rem; display: none;"></div>
        </div>
    </main>
</div>

<style>
.law-btn {
    padding: 0.75rem 1rem;
    background: white;
    color: #2563eb;
    border: 2px solid #2563eb;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    text-align: left;
}

.law-btn:hover {
    background: #2563eb;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.law-explanation-box {
    background: #f8fafc;
    border: 2px solid #2563eb;
    border-radius: 12px;
    padding: 2rem;
}

.law-explanation-box h3 {
    margin: 0 0 1rem 0;
    color: #2563eb;
    font-size: 1.3rem;
}

.law-explanation-box .section {
    margin-bottom: 1.5rem;
}

.law-explanation-box .section h4 {
    margin: 0 0 0.5rem 0;
    color: #1e293b;
    font-size: 1.1rem;
}

.law-explanation-box ul {
    margin: 0.5rem 0;
    padding-left: 1.5rem;
}

.law-explanation-box li {
    margin-bottom: 0.5rem;
    color: #475569;
    line-height: 1.6;
}

.guidance-box {
    background: #f0f9ff;
    border: 2px solid #0ea5e9;
    border-radius: 12px;
    padding: 2rem;
}

.guidance-box h3 {
    margin: 0 0 1rem 0;
    color: #0ea5e9;
    font-size: 1.3rem;
}

.guidance-section {
    margin-bottom: 2rem;
}

.guidance-section h4 {
    margin: 0 0 0.75rem 0;
    color: #1e293b;
    font-size: 1.1rem;
    border-bottom: 2px solid #0ea5e9;
    padding-bottom: 0.5rem;
}

.immediate-action {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    border-radius: 4px;
    font-weight: 600;
    color: #92400e;
}

.your-rights {
    background: #dbeafe;
    border-left: 4px solid #2563eb;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    border-radius: 4px;
    color: #1e3a8a;
}

.common-mistake {
    background: #fee2e2;
    border-left: 4px solid #ef4444;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    border-radius: 4px;
    color: #7f1d1d;
}
</style>

<script>
function askKate(question) {
    window.location.href = '<?php echo home_url('/platform-kate-ai'); ?>?question=' + encodeURIComponent(question);
}

// Søg i Barnets Lov
async function searchBarnetsLov() {
    const query = document.getElementById('lawSearchInput').value;
    const resultsDiv = document.getElementById('lawSearchResults');
    
    if (!query) {
        alert('Indtast venligst et søgeord');
        return;
    }
    
    resultsDiv.innerHTML = '<p style="color: #64748b;">Søger...</p>';
    
    try {
        const response = await fetch('<?php echo rest_url('kate/v1/search-barnets-lov'); ?>', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success && result.results.results.length > 0) {
            let html = '<div style="background: #f8fafc; border-radius: 8px; padding: 1rem;">';
            html += '<h4 style="margin: 0 0 1rem 0; color: #1e293b;">Søgeresultater for "' + query + '":</h4>';
            
            result.results.results.forEach(item => {
                html += '<div style="background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem;">';
                html += '<h5 style="margin: 0 0 0.5rem 0; color: #2563eb; font-size: 1rem;">' + item.paragraph + ' - ' + item.title + '</h5>';
                html += '<p style="margin: 0 0 0.5rem 0; color: #64748b; font-size: 0.9rem;">' + item.snippet + '</p>';
                html += '<button onclick="explainLaw(\'' + item.paragraph.replace('§ ', '') + '\')" style="padding: 0.5rem 1rem; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; font-weight: 600;">Læs mere →</button>';
                html += '</div>';
            });
            
            html += '</div>';
            resultsDiv.innerHTML = html;
        } else {
            resultsDiv.innerHTML = '<p style="color: #ef4444;">Ingen resultater fundet. Prøv andre søgeord.</p>';
        }
    } catch (error) {
        resultsDiv.innerHTML = '<p style="color: #ef4444;">Der opstod en fejl. Prøv igen.</p>';
        console.error(error);
    }
}

// Forklar lovparagraf
async function explainLaw(paragraph) {
    const explanationDiv = document.getElementById('lawExplanation');
    explanationDiv.style.display = 'block';
    explanationDiv.innerHTML = '<p style="color: #64748b;">Henter forklaring...</p>';
    
    try {
        const response = await fetch('<?php echo rest_url('kate/v1/explain-law'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                law: 'barnets_lov',
                paragraph: paragraph,
                include_examples: true
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const exp = result.explanation;
            let html = '<div class="law-explanation-box">';
            html += '<h3>' + exp.paragraph + ' - ' + exp.title + '</h3>';
            
            // Lovtekst
            if (exp.law_text) {
                html += '<div class="section">';
                html += '<h4>📜 Lovtekst</h4>';
                html += '<p style="color: #64748b; font-style: italic;">' + exp.law_text + '</p>';
                html += '</div>';
            }
            
            // På dansk
            if (exp.plain_danish) {
                html += '<div class="section">';
                html += '<h4>💡 På almindeligt dansk</h4>';
                html += '<p style="color: #1e293b; font-size: 1.05rem; font-weight: 600;">' + exp.plain_danish + '</p>';
                html += '</div>';
            }
            
            // Eksempler
            if (exp.examples && exp.examples.length > 0) {
                html += '<div class="section">';
                html += '<h4>✅ Eksempler</h4>';
                html += '<ul>';
                exp.examples.forEach(ex => {
                    html += '<li>' + ex + '</li>';
                });
                html += '</ul>';
                html += '</div>';
            }
            
            // Dine rettigheder
            if (exp.your_rights && exp.your_rights.length > 0) {
                html += '<div class="section">';
                html += '<h4>⚖️ Dine rettigheder</h4>';
                html += '<ul>';
                exp.your_rights.forEach(right => {
                    html += '<li style="color: #2563eb; font-weight: 600;">' + right + '</li>';
                });
                html += '</ul>';
                html += '</div>';
            }
            
            // Almindelige spørgsmål
            if (exp.common_questions && exp.common_questions.length > 0) {
                html += '<div class="section">';
                html += '<h4>❓ Almindelige spørgsmål</h4>';
                exp.common_questions.forEach(qa => {
                    html += '<p style="margin: 0.5rem 0;"><strong>' + qa.split('A:')[0] + '</strong><br>' + qa.split('A:')[1] + '</p>';
                });
                html += '</div>';
            }
            
            // Link
            if (exp.official_link) {
                html += '<div style="margin-top: 1.5rem;">';
                html += '<a href="' + exp.official_link + '" target="_blank" style="display: inline-block; padding: 0.75rem 1.5rem; background: #2563eb; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">Læs på Retsinformation.dk →</a>';
                html += '</div>';
            }
            
            html += '</div>';
            explanationDiv.innerHTML = html;
            explanationDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            explanationDiv.innerHTML = '<p style="color: #ef4444;">Kunne ikke hente forklaring.</p>';
        }
    } catch (error) {
        explanationDiv.innerHTML = '<p style="color: #ef4444;">Der opstod en fejl. Prøv igen.</p>';
        console.error(error);
    }
}

// Generér vejledning
async function generateGuidance() {
    const situationType = document.getElementById('situationType').value;
    const resultDiv = document.getElementById('guidanceResult');
    
    if (!situationType) {
        alert('Vælg venligst en situation');
        return;
    }
    
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<p style="color: #64748b;">Genererer personlig vejledning... Dette kan tage et øjeblik.</p>';
    
    try {
        const response = await fetch('<?php echo rest_url('kate/v1/guidance'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                situation: {
                    situation_type: situationType,
                    details: {}
                }
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const guidance = result.guidance;
            let html = '<div class="guidance-box">';
            html += '<h3>📋 ' + guidance.title + '</h3>';
            html += '<p style="margin-bottom: 1.5rem; color: #64748b;">' + guidance.summary + '</p>';
            
            // Øjeblikkelige handlinger
            if (guidance.immediate_actions && guidance.immediate_actions.length > 0) {
                html += '<div class="guidance-section">';
                html += '<h4>🚨 ØJEBLIKKELIGE HANDLINGER</h4>';
                guidance.immediate_actions.forEach(action => {
                    html += '<div class="immediate-action">' + action + '</div>';
                });
                html += '</div>';
            }
            
            // Dine rettigheder
            if (guidance.your_rights && guidance.your_rights.length > 0) {
                html += '<div class="guidance-section">';
                html += '<h4>⚖️ DINE RETTIGHEDER</h4>';
                guidance.your_rights.forEach(right => {
                    html += '<div class="your-rights">' + right + '</div>';
                });
                html += '</div>';
            }
            
            // Almindelige fejl
            if (guidance.common_mistakes && guidance.common_mistakes.length > 0) {
                html += '<div class="guidance-section">';
                html += '<h4>⚠️ UNDGÅ DISSE FEJL</h4>';
                guidance.common_mistakes.forEach(mistake => {
                    html += '<div class="common-mistake">' + mistake + '</div>';
                });
                html += '</div>';
            }
            
            // Næste trin
            if (guidance.next_steps && guidance.next_steps.length > 0) {
                html += '<div class="guidance-section">';
                html += '<h4>📌 NÆSTE TRIN</h4>';
                html += '<ol style="margin: 0; padding-left: 1.5rem;">';
                guidance.next_steps.forEach(step => {
                    html += '<li style="margin-bottom: 0.5rem; color: #475569;">' + step + '</li>';
                });
                html += '</ol>';
                html += '</div>';
            }
            
            html += '<div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #cbd5e1;">';
            html += '<p style="color: #64748b; font-size: 0.9rem; margin: 0;">💡 <strong>Tip:</strong> Gem denne vejledning til senere brug. Du kan altid generere ny vejledning hvis din situation ændrer sig.</p>';
            html += '</div>';
            
            html += '</div>';
            resultDiv.innerHTML = html;
            resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            resultDiv.innerHTML = '<p style="color: #ef4444;">Kunne ikke generere vejledning. Prøv igen.</p>';
        }
    } catch (error) {
        resultDiv.innerHTML = '<p style="color: #ef4444;">Der opstod en fejl. Prøv igen.</p>';
        console.error(error);
    }
}

// Søg ved Enter-tast
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('lawSearchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBarnetsLov();
            }
        });
    }
});
</script>

<script>
// Tab switching funktionalitet
function showTab(tabName) {
    // Skjul alle tabs
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.style.display = 'none');
    
    // Fjern active class fra alle knapper
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => {
        btn.style.background = '#f1f5f9';
        btn.style.color = '#475569';
    });
    
    // Vis valgt tab
    const selectedTab = document.getElementById('tab-' + tabName);
    if (selectedTab) {
        selectedTab.style.display = 'block';
    }
    
    // Marker active knap
    event.target.style.background = '#667eea';
    event.target.style.color = 'white';
}

// Hover effekt på action cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.action-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = '0 20px 40px rgba(0,0,0,0.2)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
});
</script>

<?php get_footer(); ?>


