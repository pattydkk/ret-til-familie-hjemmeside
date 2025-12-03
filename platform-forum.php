<?php
/**
 * Template Name: Platform Forum
 */
get_header();
$lang = rtf_get_lang();
if (!rtf_is_logged_in()) { wp_redirect(home_url('/platform-auth/?lang=' . $lang)); exit; }
rtf_require_subscription();
$current_user = rtf_get_current_user();
global $wpdb;
$table_topics = $wpdb->prefix . 'rtf_platform_forum_topics';
$table_replies = $wpdb->prefix . 'rtf_platform_forum_replies';
$table_users = $wpdb->prefix . 'rtf_platform_users';

// View topic
if (isset($_GET['topic'])) {
    $topic_id = intval($_GET['topic']);
    $wpdb->query($wpdb->prepare("UPDATE $table_topics SET views = views + 1 WHERE id = %d", $topic_id));
    $topic = $wpdb->get_row($wpdb->prepare("SELECT t.*, u.username, u.full_name FROM $table_topics t JOIN $table_users u ON t.user_id = u.id WHERE t.id = %d", $topic_id));
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // CSRF protection
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'rtf_forum_reply')) {
            wp_die('Security check failed');
        }
        
        $wpdb->insert($table_replies, array('topic_id' => $topic_id, 'user_id' => $current_user->id, 'content' => sanitize_textarea_field($_POST['content']), 'created_at' => current_time('mysql')));
        $wpdb->query($wpdb->prepare("UPDATE $table_topics SET replies_count = replies_count + 1 WHERE id = %d", $topic_id));
        wp_redirect(home_url('/platform-forum/?topic=' . $topic_id . '&lang=' . $lang));
        exit;
    }
    
    $replies = $wpdb->get_results($wpdb->prepare("SELECT r.*, u.username, u.full_name FROM $table_replies r JOIN $table_users u ON r.user_id = u.id WHERE r.topic_id = %d ORDER BY r.created_at ASC", $topic_id));
    ?>
    <main class="platform-forum-topic">
        <div class="container" style="max-width: 900px; margin: 40px auto; padding: 20px;">
            <div style="background: var(--rtf-card); padding: 35px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 30px;">
                <h1 style="color: var(--rtf-text); margin-bottom: 15px;"><?php echo esc_html($topic->title); ?></h1>
                <div style="color: var(--rtf-muted); margin-bottom: 20px;">
                    <?php echo esc_html($topic->full_name); ?> • <?php echo rtf_time_ago($topic->created_at); ?> • <?php echo $topic->views; ?> visninger
                </div>
                <div style="color: var(--rtf-text); line-height: 1.8; white-space: pre-wrap;"><?php echo esc_html($topic->content); ?></div>
                
                <?php if (!empty($topic->image_url)): ?>
                    <div style="margin-top: 20px;">
                        <img src="<?php echo esc_url($topic->image_url); ?>" alt="Topic image" style="max-width: 100%; height: auto; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($topic->category) || !empty($topic->country) || !empty($topic->city)): ?>
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e0f2fe;">
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <?php if (!empty($topic->category)): ?>
                                <span style="background: #dbeafe; color: #1e40af; padding: 6px 14px; border-radius: 20px; font-size: 0.85em; font-weight: 600;">
                                    📂 <?php echo esc_html($topic->category); ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($topic->subcategory)): ?>
                                <span style="background: #dcfce7; color: #15803d; padding: 6px 14px; border-radius: 20px; font-size: 0.85em; font-weight: 600;">
                                    📋 <?php echo esc_html($topic->subcategory); ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($topic->country)): ?>
                                <span style="background: #fef3c7; color: #92400e; padding: 6px 14px; border-radius: 20px; font-size: 0.85em; font-weight: 600;">
                                    🌍 <?php echo $topic->country === 'DK' ? '🇩🇰 Danmark' : '🇸🇪 Sverige'; ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($topic->city)): ?>
                                <span style="background: #e0e7ff; color: #3730a3; padding: 6px 14px; border-radius: 20px; font-size: 0.85em; font-weight: 600;">
                                    📍 <?php echo esc_html($topic->city); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <h3 style="margin-bottom: 20px;">Svar (<?php echo count($replies); ?>)</h3>
            
            <?php foreach ($replies as $reply): ?>
                <div style="background: var(--rtf-card); padding: 25px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 15px;">
                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #60a5fa, #2563eb); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; margin-right: 15px;">
                            <?php echo strtoupper(substr($reply->username, 0, 1)); ?>
                        </div>
                        <div>
                            <div style="font-weight: 600; color: var(--rtf-text);"><?php echo esc_html($reply->full_name); ?></div>
                            <div style="font-size: 0.85em; color: var(--rtf-muted);"><?php echo rtf_time_ago($reply->created_at); ?></div>
                        </div>
                    </div>
                    <div style="color: var(--rtf-text); line-height: 1.6; white-space: pre-wrap;"><?php echo esc_html($reply->content); ?></div>
                </div>
            <?php endforeach; ?>

            <div style="background: var(--rtf-card); padding: 30px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-top: 30px;">
                <h3 style="margin-bottom: 20px;">Skriv Svar</h3>
                <form method="POST" action="">
                    <?php wp_nonce_field('rtf_forum_reply'); ?>
                    <textarea name="content" rows="5" required style="width: 100%; padding: 15px; border: 1px solid #e0f2fe; border-radius: 12px; font-family: inherit; margin-bottom: 15px;"></textarea>
                    <button type="submit" class="btn-primary">Send Svar</button>
                </form>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="<?php echo home_url('/platform-forum/?lang=' . $lang); ?>" style="color: #2563eb; text-decoration: none;">← Tilbage til forum</a>
            </div>
        </div>
    </main>
    <?php
    get_footer();
    exit;
}

// Create topic with image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_topic'])) {
    // CSRF protection
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'rtf_create_topic')) {
        wp_die('Security check failed');
    }
    
    // Handle image upload
    $image_url = '';
    if (!empty($_FILES['topic_image']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $uploaded = media_handle_upload('topic_image', 0);
        if (!is_wp_error($uploaded)) {
            $image_url = wp_get_attachment_url($uploaded);
        }
    }
    
    $wpdb->insert($table_topics, array(
        'user_id' => $current_user->id, 
        'title' => sanitize_text_field($_POST['title']), 
        'content' => sanitize_textarea_field($_POST['content']),
        'country' => sanitize_text_field($_POST['country'] ?? ''),
        'city' => sanitize_text_field($_POST['city'] ?? ''),
        'category' => sanitize_text_field($_POST['category'] ?? ''),
        'subcategory' => sanitize_text_field($_POST['subcategory'] ?? ''),
        'case_type' => sanitize_text_field($_POST['case_type'] ?? ''),
        'image_url' => $image_url,
        'gdpr_consent' => isset($_POST['gdpr_consent']) ? 1 : 0,
        'created_at' => current_time('mysql')
    ));
    wp_redirect(home_url('/platform-forum/?lang=' . $lang));
    exit;
}

// FILTER TOPICS - SQL WHERE CLAUSES
$where_clauses = ["1=1"];
$filter_country = isset($_GET['filter_country']) ? sanitize_text_field($_GET['filter_country']) : '';
$filter_city = isset($_GET['filter_city']) ? sanitize_text_field($_GET['filter_city']) : '';
$filter_category = isset($_GET['filter_category']) ? sanitize_text_field($_GET['filter_category']) : '';
$filter_subcategory = isset($_GET['filter_subcategory']) ? sanitize_text_field($_GET['filter_subcategory']) : '';
$filter_case_type = isset($_GET['filter_case_type']) ? sanitize_text_field($_GET['filter_case_type']) : '';

if (!empty($filter_country)) {
    $where_clauses[] = $wpdb->prepare("t.country = %s", $filter_country);
}
if (!empty($filter_city)) {
    $where_clauses[] = $wpdb->prepare("t.city = %s", $filter_city);
}
if (!empty($filter_category)) {
    $where_clauses[] = $wpdb->prepare("t.category = %s", $filter_category);
}
if (!empty($filter_subcategory)) {
    $where_clauses[] = $wpdb->prepare("t.subcategory = %s", $filter_subcategory);
}
if (!empty($filter_case_type)) {
    $where_clauses[] = $wpdb->prepare("t.case_type = %s", $filter_case_type);
}

$where_sql = implode(' AND ', $where_clauses);

$topics = $wpdb->get_results(
    "SELECT t.*, u.username, u.full_name 
     FROM $table_topics t 
     JOIN $table_users u ON t.user_id = u.id 
     WHERE $where_sql
     ORDER BY t.created_at DESC 
     LIMIT 30"
);
?>

<div class="platform-layout" style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <?php get_template_part('template-parts/platform-sidebar'); ?>
    
    <main class="platform-forum" style="min-width: 0;">
    <div class="container" style="max-width: 900px; margin: 40px auto; padding: 20px;">
        <h1 style="margin-bottom: 30px; color: var(--rtf-text);">Forum</h1>

        <!-- Filter sektion -->
        <div style="background: var(--rtf-card); padding: 25px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 30px;">
            <h3 style="margin: 0 0 20px 0; color: var(--rtf-text);">
                🎯 <?php echo $lang === 'da' ? 'Filtrer Emner' : 'Filtrera Ämnen'; ?>
            </h3>
            
            <form method="get" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <input type="hidden" name="lang" value="<?php echo esc_attr($lang); ?>">
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-muted); font-size: 0.9em;">
                        🌍 <?php echo $lang === 'da' ? 'Land' : 'Land'; ?>
                    </label>
                    <select name="filter_country" style="width: 100%; padding: 10px; border: 2px solid #e0f2fe; border-radius: 8px; font-size: 1em; background: white;">
                        <option value=""><?php echo $lang === 'da' ? 'Alle lande' : 'Alla länder'; ?></option>
                        <option value="DK" <?php selected($filter_country, 'DK'); ?>>🇩🇰 Danmark</option>
                        <option value="SE" <?php selected($filter_country, 'SE'); ?>>🇸🇪 Sverige</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-muted); font-size: 0.9em;">
                        📍 <?php echo $lang === 'da' ? 'By' : 'Stad'; ?>
                    </label>
                    <input type="text" name="filter_city" value="<?php echo esc_attr($filter_city); ?>" 
                           placeholder="<?php echo $lang === 'da' ? 'Indtast bynavn' : 'Ange stad'; ?>" 
                           style="width: 100%; padding: 10px; border: 2px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-muted); font-size: 0.9em;">
                        📂 <?php echo $lang === 'da' ? 'Kategori' : 'Kategori'; ?>
                    </label>
                    <select name="filter_category" id="filter_category_search" onchange="updateSearchSubcategories()" style="width: 100%; padding: 10px; border: 2px solid #e0f2fe; border-radius: 8px; font-size: 1em; background: white;">
                        <option value=""><?php echo $lang === 'da' ? 'Alle kategorier' : 'Alla kategorier'; ?></option>
                        <option value="familie_born" <?php selected($filter_category, 'familie_born'); ?>>👨‍👩‍👧 <?php echo $lang === 'da' ? 'Familie & Børn' : 'Familj & Barn'; ?></option>
                        <option value="jobcenter" <?php selected($filter_category, 'jobcenter'); ?>>💼 <?php echo $lang === 'da' ? 'Jobcenter & Beskæftigelse' : 'Jobcenter & Sysselsättning'; ?></option>
                        <option value="handicap" <?php selected($filter_category, 'handicap'); ?>>♿ <?php echo $lang === 'da' ? 'Handicap & Funktionsnedsættelse' : 'Handikapp & Funktionsnedsättning'; ?></option>
                        <option value="sundhed_patient" <?php selected($filter_category, 'sundhed_patient'); ?>>🏥 <?php echo $lang === 'da' ? 'Sundhed & Patientklager' : 'Hälsa & Patientklagomål'; ?></option>
                        <option value="bolig" <?php selected($filter_category, 'bolig'); ?>>🏠 <?php echo $lang === 'da' ? 'Bolig & Huslejenævn' : 'Bostad & Hyresjuridik'; ?></option>
                        <option value="aeldre" <?php selected($filter_category, 'aeldre'); ?>>👴 <?php echo $lang === 'da' ? 'Ældre & Plejehjem' : 'Äldre & Vårdhem'; ?></option>
                        <option value="oekonomi" <?php selected($filter_category, 'oekonomi'); ?>>💰 <?php echo $lang === 'da' ? 'Økonomi & Gældssanering' : 'Ekonomi & Skuldsanering'; ?></option>
                        <option value="diskrimination" <?php selected($filter_category, 'diskrimination'); ?>>⚖️ <?php echo $lang === 'da' ? 'Diskrimination & Ligebehandling' : 'Diskriminering & Likabehandling'; ?></option>
                        <option value="databeskyttelse" <?php selected($filter_category, 'databeskyttelse'); ?>>🔒 <?php echo $lang === 'da' ? 'Databeskyttelse & GDPR' : 'Dataskydd & GDPR'; ?></option>
                        <option value="generel" <?php selected($filter_category, 'generel'); ?>>💬 <?php echo $lang === 'da' ? 'Generel Diskussion' : 'Generell Diskussion'; ?></option>
                    </select>
                </div>
                
                <div id="filter_subcategory_container" style="<?php echo empty($filter_category) ? 'display: none;' : ''; ?>">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-muted); font-size: 0.9em;">
                        📋 <?php echo $lang === 'da' ? 'Subkategori' : 'Underkategori'; ?>
                    </label>
                    <select name="filter_subcategory" id="filter_subcategory_search" style="width: 100%; padding: 10px; border: 2px solid #e0f2fe; border-radius: 8px; font-size: 1em; background: white;">
                        <option value=""><?php echo $lang === 'da' ? 'Alle subkategorier' : 'Alla underkategorier'; ?></option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--rtf-muted); font-size: 0.9em;">
                        📁 <?php echo $lang === 'da' ? 'Sagstype' : 'Ärendetyp'; ?>
                    </label>
                    <select name="filter_case_type" style="width: 100%; padding: 10px; border: 2px solid #e0f2fe; border-radius: 8px; font-size: 1em; background: white;">
                        <option value=""><?php echo $lang === 'da' ? 'Alle sagstyper' : 'Alla ärendetyper'; ?></option>
                        <option value="forældremyndighed" <?php selected($filter_case_type, 'forældremyndighed'); ?>><?php echo $lang === 'da' ? 'Forældremyndighed' : 'Vårdnad'; ?></option>
                        <option value="samvær" <?php selected($filter_case_type, 'samvær'); ?>><?php echo $lang === 'da' ? 'Samvær' : 'Umgänge'; ?></option>
                        <option value="anbringelse" <?php selected($filter_case_type, 'anbringelse'); ?>><?php echo $lang === 'da' ? 'Anbringelse' : 'Placering'; ?></option>
                        <option value="tvangsfjernelse" <?php selected($filter_case_type, 'tvangsfjernelse'); ?>><?php echo $lang === 'da' ? 'Tvangsfjernelse' : 'Tvångsomhändertagande'; ?></option>
                        <option value="børnebidrag" <?php selected($filter_case_type, 'børnebidrag'); ?>><?php echo $lang === 'da' ? 'Børnebidrag' : 'Barnbidrag'; ?></option>
                        <option value="skilsmisse" <?php selected($filter_case_type, 'skilsmisse'); ?>><?php echo $lang === 'da' ? 'Skilsmisse' : 'Skilsmässa'; ?></option>
                        <option value="andet" <?php selected($filter_case_type, 'andet'); ?>><?php echo $lang === 'da' ? 'Andet' : 'Annat'; ?></option>
                    </select>
                </div>
                
                <div style="display: flex; align-items: flex-end; gap: 10px;">
                    <button type="submit" style="flex: 1; padding: 10px 20px; background: linear-gradient(135deg, #60a5fa, #2563eb); color: white; border: none; border-radius: 8px; font-size: 1em; font-weight: 600; cursor: pointer; transition: opacity 0.2s;">
                        🔍 <?php echo $lang === 'da' ? 'Søg' : 'Sök'; ?>
                    </button>
                    <a href="<?php echo home_url('/platform-forum/?lang=' . $lang); ?>" 
                       style="padding: 10px 20px; background: #f0f0f0; color: #555; border: none; border-radius: 8px; font-size: 1em; font-weight: 600; text-decoration: none; text-align: center; transition: background 0.2s;">
                        🔄
                    </a>
                </div>
            </form>
            
            <?php if (!empty($filter_country) || !empty($filter_city) || !empty($filter_category) || !empty($filter_subcategory) || !empty($filter_case_type)): ?>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0f2fe;">
                    <p style="margin: 0; color: var(--rtf-muted); font-size: 0.9em;">
                        <strong><?php echo $lang === 'da' ? 'Aktive filtre:' : 'Aktiva filter:'; ?></strong>
                        <?php if (!empty($filter_country)): ?>
                            <span style="display: inline-block; background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 12px; margin-left: 5px; font-size: 0.85em;">
                                <?php echo $filter_country === 'DK' ? '🇩🇰 Danmark' : ($filter_country === 'SE' ? '🇸🇪 Sverige' : '🇳🇴 Norge'); ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filter_city)): ?>
                            <span style="display: inline-block; background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 12px; margin-left: 5px; font-size: 0.85em;">
                                📍 <?php echo esc_html($filter_city); ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filter_category)): ?>
                            <span style="display: inline-block; background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 12px; margin-left: 5px; font-size: 0.85em;">
                                📂 <?php echo esc_html(ucfirst($filter_category)); ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filter_subcategory)): ?>
                            <span style="display: inline-block; background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 12px; margin-left: 5px; font-size: 0.85em;">
                                📋 <?php echo esc_html($filter_subcategory); ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filter_case_type)): ?>
                            <span style="display: inline-block; background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 12px; margin-left: 5px; font-size: 0.85em;">
                                📁 <?php echo esc_html(ucfirst($filter_case_type)); ?>
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <div style="background: var(--rtf-card); padding: 30px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 30px;">
            <h3 style="margin-bottom: 20px;"><?php echo $lang === 'da' ? 'Opret Nyt Emne' : 'Skapa Nytt Ämne'; ?></h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <?php wp_nonce_field('rtf_create_topic'); ?>
                
                <input type="text" name="title" placeholder="<?php echo $lang === 'da' ? 'Titel' : 'Titel'; ?>" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; margin-bottom: 15px;">
                
                <textarea name="content" rows="5" placeholder="<?php echo $lang === 'da' ? 'Indhold...' : 'Innehåll...'; ?>" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-family: inherit; margin-bottom: 15px;"></textarea>
                
                <!-- Billede upload -->
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;">
                        📸 <?php echo $lang === 'da' ? 'Tilføj billede (valgfrit)' : 'Lägg till bild (valfritt)'; ?>
                    </label>
                    <input type="file" name="topic_image" accept="image/*" style="width: 100%; padding: 10px; border: 2px dashed #e0f2fe; border-radius: 8px; background: #f8fafc;">
                    <small style="color: var(--rtf-muted); display: block; margin-top: 5px;">
                        <?php echo $lang === 'da' ? 'Max 5 MB. Format: JPG, PNG, GIF' : 'Max 5 MB. Format: JPG, PNG, GIF'; ?>
                    </small>
                </div>
                
                <!-- Hovedkategori og subkategori -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;">
                            📂 <?php echo $lang === 'da' ? 'Hovedkategori' : 'Huvudkategori'; ?>
                        </label>
                        <select name="category" id="forum_category" onchange="updateForumSubcategories()" style="width: 100%; padding: 10px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                            <option value=""><?php echo $lang === 'da' ? 'Vælg kategori' : 'Välj kategori'; ?></option>
                            <option value="familie_born">👨‍👩‍👧 <?php echo $lang === 'da' ? 'Familie & Børn' : 'Familj & Barn'; ?></option>
                            <option value="jobcenter">💼 <?php echo $lang === 'da' ? 'Jobcenter & Beskæftigelse' : 'Arbetsförmedling & Sysselsättning'; ?></option>
                            <option value="handicap">♿ <?php echo $lang === 'da' ? 'Handicap & Funktionsnedsættelse' : 'Handikapp & Funktionsnedsättning'; ?></option>
                            <option value="sundhed_patient">🏥 <?php echo $lang === 'da' ? 'Sundhed & Patientklager' : 'Hälsa & Patientklagomål'; ?></option>
                            <option value="bolig">🏠 <?php echo $lang === 'da' ? 'Bolig & Huslejenævn' : 'Boende & Hyrerätt'; ?></option>
                            <option value="aeldre">👴 <?php echo $lang === 'da' ? 'Ældre & Plejehjem' : 'Äldre & Äldreboende'; ?></option>
                            <option value="oekonomi">💰 <?php echo $lang === 'da' ? 'Økonomi & Gældssanering' : 'Ekonomi & Skuldsanering'; ?></option>
                            <option value="diskrimination">⚖️ <?php echo $lang === 'da' ? 'Diskrimination & Ligebehandling' : 'Diskriminering & Likabehandling'; ?></option>
                            <option value="databeskyttelse">🔒 <?php echo $lang === 'da' ? 'Databeskyttelse & GDPR' : 'Dataskydd & GDPR'; ?></option>
                            <option value="generel">💬 <?php echo $lang === 'da' ? 'Generel Diskussion' : 'Allmän Diskussion'; ?></option>
                        </select>
                    </div>
                    
                    <div id="forum_subcategory_container" style="display: none;">
                        <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;">
                            📋 <?php echo $lang === 'da' ? 'Subkategori' : 'Underkategori'; ?>
                        </label>
                        <select name="subcategory" id="forum_subcategory" style="width: 100%; padding: 10px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                            <option value=""><?php echo $lang === 'da' ? 'Vælg subkategori' : 'Välj underkategori'; ?></option>
                        </select>
                    </div>
                </div>
                
                <!-- Land, by og case type -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-bottom: 15px;">
                    <select name="country" style="padding: 10px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                        <option value=""><?php echo $lang === 'da' ? 'Vælg land' : 'Välj land'; ?></option>
                        <option value="DK">🇩🇰 Danmark</option>
                        <option value="SE">🇸🇪 Sverige</option>
                    </select>
                    
                    <input type="text" name="city" placeholder="<?php echo $lang === 'da' ? 'By (valgfri)' : 'Stad (valfritt)'; ?>" style="padding: 10px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                    
                    <select name="case_type" style="padding: 10px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                        <option value=""><?php echo $lang === 'da' ? 'Sagstype' : 'Ärendetyp'; ?></option>
                        <option value="aktiv_sag"><?php echo $lang === 'da' ? '⚡ Aktiv sag' : '⚡ Aktivt ärende'; ?></option>
                        <option value="afsluttet_sag"><?php echo $lang === 'da' ? '✅ Afsluttet sag' : '✅ Avslutat ärende'; ?></option>
                        <option value="overvejer"><?php echo $lang === 'da' ? '🤔 Overvejer klage' : '🤔 Överväger klagomål'; ?></option>
                        <option value="raad_stoette"><?php echo $lang === 'da' ? '💬 Søger råd/støtte' : '💬 Söker råd/stöd'; ?></option>
                        <option value="deler_erfaring"><?php echo $lang === 'da' ? '📖 Deler erfaring' : '📖 Delar erfarenhet'; ?></option>
                    </select>
                </div>
                
                <!-- GDPR Consent -->
                <div style="background: #fef3c7; border: 2px solid #fbbf24; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                    <label style="display: flex; align-items: start; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="gdpr_consent" value="1" required style="margin-top: 4px; width: 20px; height: 20px;">
                        <div>
                            <strong style="display: block; color: #92400e; margin-bottom: 5px;">
                                🔒 <?php echo $lang === 'da' ? 'GDPR & Databeskyttelse' : 'GDPR & Dataskydd'; ?>
                            </strong>
                            <p style="margin: 0; font-size: 0.9em; color: #78350f; line-height: 1.5;">
                                <?php echo $lang === 'da' 
                                    ? 'Jeg forstår at mit indlæg bliver offentligt synligt for andre brugere. Jeg bekræfter at jeg IKKE deler følsomme personoplysninger som CPR-nummer, fuldstændige navne på involverede personer, børns navne, eller andre identificerbare oplysninger der kan krænke andres privatliv. Ved at oprette indlægget accepterer jeg at overholde GDPR-reglerne.' 
                                    : 'Jag förstår att mitt inlägg blir offentligt synligt för andra användare. Jag bekräftar att jag INTE delar känslig personinformation som personnummer, fullständiga namn på involverade personer, barns namn, eller annan identifierbar information som kan kränka andras integritet. Genom att skapa inlägget accepterar jag att följa GDPR-reglerna.'; ?>
                            </p>
                        </div>
                    </label>
                </div>
                
                <button type="submit" name="create_topic" class="btn-primary" style="width: 100%; padding: 14px; font-size: 1.1em;">
                    ✍️ <?php echo $lang === 'da' ? 'Opret Emne' : 'Skapa Ämne'; ?>
                </button>
            </form>
        </div>

        <div class="topics-list">
            <?php if (empty($topics)): ?>
                <div style="text-align: center; padding: 60px; background: var(--rtf-card); border-radius: 16px;"><div style="font-size: 4em; margin-bottom: 20px;">💭</div><p style="font-size: 1.2em; color: var(--rtf-muted);">Ingen emner endnu</p></div>
            <?php else: ?>
                <?php foreach ($topics as $topic): ?>
                    <div style="background: var(--rtf-card); padding: 25px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 15px;">
                        <a href="?topic=<?php echo $topic->id; ?>&lang=<?php echo $lang; ?>" style="display: block; text-decoration: none; color: inherit; margin-bottom: 15px;">
                            <h3 style="color: var(--rtf-text); margin-bottom: 10px;"><?php echo esc_html($topic->title); ?></h3>
                            <div style="color: var(--rtf-muted); font-size: 0.9em;">
                                <?php echo esc_html($topic->full_name); ?> • <?php echo rtf_time_ago($topic->created_at); ?> • <?php echo $topic->replies_count; ?> svar • <?php echo $topic->views; ?> visninger
                            </div>
                        </a>
                        <div style="padding-top: 10px; border-top: 1px solid #e0f2fe;">
                            <button onclick="shareContent('forum', <?php echo $topic->id; ?>)" style="display: inline-flex; align-items: center; gap: 8px; color: #10b981; background: none; border: none; cursor: pointer; font-weight: 600; font-size: 1em;">
                                <span style="font-size: 1.2em;">🔄</span>
                                <span><?php echo $lang === 'da' ? 'Del' : 'Dela'; ?></span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="<?php echo home_url('/platform-profil/?lang=' . $lang); ?>" style="color: #2563eb; text-decoration: none;">← Tilbage til profil</a>
        </div>
    </div>
</main>

<script>
const lang = '<?php echo $lang; ?>';

// Comprehensive subcategories for forum (100+ options)
const forumSubcategories = {
    'familie_born': [
        {da: 'Forældremyndighed - fastsættelse', sv: 'Vårdnad - fastställande', en: 'Custody - establishment'},
        {da: 'Forældremyndighed - ændring', sv: 'Vårdnad - ändring', en: 'Custody - change'},
        {da: 'Samvær - omfang', sv: 'Umgänge - omfattning', en: 'Visitation - scope'},
        {da: 'Samvær - afbrydelse', sv: 'Umgänge - avbrott', en: 'Visitation - termination'},
        {da: 'Samværschikane', sv: 'Umgängestrakasserier', en: 'Visitation harassment'},
        {da: 'Overvåget samvær', sv: 'Övervakat umgänge', en: 'Supervised visitation'},
        {da: 'Anbringelse - tvangsm æssig', sv: 'Omhändertagande - tvångsmässig', en: 'Foster care - mandatory'},
        {da: 'Anbringelse - frivillig', sv: 'Omhändertagande - frivillig', en: 'Foster care - voluntary'},
        {da: 'Tvangsfjernelse akut', sv: 'Akut omhändertagande', en: 'Emergency removal'},
        {da: 'Hjemgivelse - genforening', sv: 'Återförening', en: 'Reunification'},
        {da: 'Børnebidrag - fastsættelse', sv: 'Barnbidrag - fastställande', en: 'Child support - establishment'},
        {da: 'Børnebidrag - ændring', sv: 'Barnbidrag - ändring', en: 'Child support - change'},
        {da: 'Barnets Reform - støtte', sv: 'Barnreform - stöd', en: 'Child Reform - support'},
        {da: 'Adoption - proces', sv: 'Adoption - process', en: 'Adoption - process'},
        {da: 'Skilsmisse - forløb', sv: 'Skilsmässa - förlopp', en: 'Divorce - process'},
        {da: 'Børnefaglig undersøgelse § 50', sv: 'Barnfaglig undersökning', en: 'Child welfare investigation'},
        {da: 'Familiepleje - godkendelse', sv: 'Familjevård - godkännande', en: 'Family foster care - approval'},
        {da: 'Søskendekontakt', sv: 'Syskonkontakt', en: 'Sibling contact'},
        {da: 'Forældrekompetence - vurdering', sv: 'Föräldraförmåga - bedömning', en: 'Parental competence - assessment'},
        {da: 'Netværkspleje', sv: 'Nätverksvård', en: 'Kinship care'}
    ],
    'jobcenter': [
        {da: 'Kontanthjælp - ansøgning', sv: 'Försörjningsstöd - ansökan', en: 'Cash benefits - application'},
        {da: 'Kontanthjælp - afslag', sv: 'Försörjningsstöd - avslag', en: 'Cash benefits - rejection'},
        {da: 'Sygedagpenge - berettigelse', sv: 'Sjukpenning - berättigande', en: 'Sick pay - eligibility'},
        {da: 'Sygedagpenge - ophør', sv: 'Sjukpenning - upphörande', en: 'Sick pay - termination'},
        {da: 'Ressourceforløb', sv: 'Resursförlopp', en: 'Resource pathway'},
        {da: 'Jobafklaringsforløb', sv: 'Arbetsklarering', en: 'Job clarification'},
        {da: 'Fleksjob - bevilling', sv: 'Flexjobb - beviljande', en: 'Flex job - granting'},
        {da: 'Førtidspension - ansøgning', sv: 'Förtidspension - ansökan', en: 'Early retirement - application'},
        {da: 'Førtidspension - afslag', sv: 'Förtidspension - avslag', en: 'Early retirement - rejection'},
        {da: 'Sanktioner - nedsættelse', sv: 'Sanktioner - nedsättning', en: 'Sanctions - reduction'},
        {da: 'Sanktioner - ophør', sv: 'Sanktioner - upphörande', en: 'Sanctions - cessation'},
        {da: 'Tilbagebetaling af ydelser', sv: 'Återbetalning', en: 'Repayment'},
        {da: 'Rådighed - manglende', sv: 'Tillgänglighet - bristande', en: 'Availability - lacking'},
        {da: 'Aktivering - pligt', sv: 'Aktivering - skyldighet', en: 'Activation - obligation'},
        {da: 'Revalidering', sv: 'Rehabilitering', en: 'Rehabilitation'},
        {da: 'Løntilskud', sv: 'Lönebidrag', en: 'Wage subsidy'},
        {da: 'Integrationsydelse', sv: 'Integrationsersättning', en: 'Integration benefits'},
        {da: 'Uddannelseshjælp', sv: 'Utbildningshjälp', en: 'Education assistance'},
        {da: 'Selvstændig virksomhed', sv: 'Egen verksamhet', en: 'Self-employment'},
        {da: 'Mentorordning', sv: 'Mentorordning', en: 'Mentor scheme'}
    ],
    'handicap': [
        {da: 'Handicaptillæg', sv: 'Handikappstillägg', en: 'Disability supplement'},
        {da: 'BPA - Borgerstyret Personlig Assistance', sv: 'BPA - Brukarstyrd Personlig Assistans', en: 'User-controlled Personal Assistance'},
        {da: 'Hjælpemidler - ansøgning', sv: 'Hjälpmedel - ansökan', en: 'Assistive devices - application'},
        {da: 'Hjælpemidler - afslag', sv: 'Hjälpmedel - avslag', en: 'Assistive devices - rejection'},
        {da: 'Boligindretning', sv: 'Bostadsanpassning', en: 'Home adaptation'},
        {da: 'Merudgifter § 100', sv: 'Merkostnader', en: 'Additional expenses'},
        {da: 'Ledsageordning § 97', sv: 'Ledsagarordning', en: 'Companion scheme'},
        {da: 'Støtte-kontaktperson', sv: 'Stöd-kontaktperson', en: 'Support contact person'},
        {da: 'Botilbud - midlertidigt', sv: 'Boende - tillfällig', en: 'Housing - temporary'},
        {da: 'Botilbud - permanent', sv: 'Boende - permanent', en: 'Housing - permanent'},
        {da: 'Hjemmehjælp', sv: 'Hemhjälp', en: 'Home help'},
        {da: 'Personlig pleje', sv: 'Personlig vård', en: 'Personal care'},
        {da: 'Aflastning', sv: 'Avlastning', en: 'Respite care'},
        {da: 'Beskyttet beskæftigelse', sv: 'Skyddat arbete', en: 'Sheltered employment'},
        {da: 'Aktivitets- og samværstilbud', sv: 'Aktivitets- och samvaroerbjudanden', en: 'Activity offers'},
        {da: 'Bil på særlige vilkår', sv: 'Bil på särskilda villkor', en: 'Car on special terms'},
        {da: 'Tolkebistand', sv: 'Tolkassistans', en: 'Interpreter assistance'}
    ],
    'sundhed_patient': [
        {da: 'Patientklage - behandlingsfejl', sv: 'Patientklagomål - behandlingsfel', en: 'Patient complaint - treatment error'},
        {da: 'Patientklage - manglende samtykke', sv: 'Patientklagomål - bristande samtycke', en: 'Patient complaint - lack of consent'},
        {da: 'Aktindsigt i journal', sv: 'Journalutskrift', en: 'Access to medical records'},
        {da: 'Ventetid - behandling', sv: 'Väntetid - behandling', en: 'Waiting time - treatment'},
        {da: 'Frit sygehusvalg', sv: 'Fritt sjukhusval', en: 'Free choice of hospital'},
        {da: 'Genoptræning - afslag', sv: 'Rehabilitering - avslag', en: 'Rehabilitation - rejection'},
        {da: 'Hjælpemidler - medicinske', sv: 'Hjälpmedel - medicinska', en: 'Medical devices'},
        {da: 'Psykiatri - tvang', sv: 'Psykiatri - tvång', en: 'Psychiatry - coercion'},
        {da: 'Lægemidler - tilskud', sv: 'Läkemedel - bidrag', en: 'Medicine - subsidy'}
    ],
    'bolig': [
        {da: 'Boligstøtte - ansøgning', sv: 'Bostadsbidrag - ansökan', en: 'Housing benefit - application'},
        {da: 'Boligstøtte - afslag', sv: 'Bostadsbidrag - avslag', en: 'Housing benefit - rejection'},
        {da: 'Huslejenævn - husleje for høj', sv: 'Hyresnämnd - hyra för hög', en: 'Rent tribunal - rent too high'},
        {da: 'Huslejenævn - depositum', sv: 'Hyresnämnd - deposition', en: 'Rent tribunal - deposit'},
        {da: 'Udsættelse af bolig', sv: 'Vräkning', en: 'Eviction'},
        {da: 'Boliganvisning - kommunal', sv: 'Bostadsanvisning', en: 'Housing allocation'},
        {da: 'Midlertidig bolig - akut', sv: 'Tillfällig bostad - akut', en: 'Temporary housing - emergency'},
        {da: 'Hjemløshed', sv: 'Hemlöshet', en: 'Homelessness'},
        {da: 'Tilskud til indskud', sv: 'Bidrag till deposition', en: 'Deposit grant'},
        {da: 'Boligsocial støtte', sv: 'Boendestöd', en: 'Housing support'}
    ],
    'aeldre': [
        {da: 'Hjemmepleje - omfang', sv: 'Hemtjänst - omfattning', en: 'Home care - scope'},
        {da: 'Hjemmepleje - kvalitet', sv: 'Hemtjänst - kvalitet', en: 'Home care - quality'},
        {da: 'Plejehjem - visitering', sv: 'Äldreboende - hänvisning', en: 'Nursing home - referral'},
        {da: 'Plejehjem - ventetid', sv: 'Äldreboende - väntetid', en: 'Nursing home - waiting time'},
        {da: 'Værgemål - beskikkelse', sv: 'Förmyndarskap - förordnande', en: 'Guardianship - appointment'},
        {da: 'Værgemål - ophør', sv: 'Förmyndarskap - upphörande', en: 'Guardianship - termination'},
        {da: 'Madservice', sv: 'Måltidsservice', en: 'Meal service'},
        {da: 'Genoptræning', sv: 'Rehabilitering', en: 'Rehabilitation'},
        {da: 'Dagcenter', sv: 'Dagcenter', en: 'Day center'},
        {da: 'Hjælpemidler til ældre', sv: 'Hjälpmedel för äldre', en: 'Assistive devices for elderly'}
    ],
    'oekonomi': [
        {da: 'Gældssanering - ansøgning', sv: 'Skuldsanering - ansökan', en: 'Debt restructuring - application'},
        {da: 'Gældssanering - afslag', sv: 'Skuldsanering - avslag', en: 'Debt restructuring - rejection'},
        {da: 'Budgetrådgivning', sv: 'Budgetrådgivning', en: 'Budget counseling'},
        {da: 'Enkeltudgifter', sv: 'Enskilda utgifter', en: 'Single expenses'},
        {da: 'Tilbagebetaling af ydelser', sv: 'Återbetalning', en: 'Repayment'},
        {da: 'Inkasso - problemer', sv: 'Inkasso - problem', en: 'Debt collection - problems'}
    ],
    'diskrimination': [
        {da: 'Diskrimination - arbejde', sv: 'Diskriminering - arbete', en: 'Discrimination - work'},
        {da: 'Diskrimination - køn', sv: 'Diskriminering - kön', en: 'Discrimination - gender'},
        {da: 'Diskrimination - etnicitet', sv: 'Diskriminering - etnicitet', en: 'Discrimination - ethnicity'},
        {da: 'Diskrimination - handicap', sv: 'Diskriminering - handikapp', en: 'Discrimination - disability'},
        {da: 'Diskrimination - alder', sv: 'Diskriminering - ålder', en: 'Discrimination - age'},
        {da: 'Chikane', sv: 'Trakasserier', en: 'Harassment'},
        {da: 'Ligeløn', sv: 'Jämlik lön', en: 'Equal pay'}
    ],
    'databeskyttelse': [
        {da: 'GDPR - aktindsigt', sv: 'GDPR - registerutdrag', en: 'GDPR - access request'},
        {da: 'GDPR - sletning af data', sv: 'GDPR - radering av data', en: 'GDPR - data deletion'},
        {da: 'GDPR - databrud', sv: 'GDPR - dataintrång', en: 'GDPR - data breach'},
        {da: 'GDPR - klage til Datatilsynet', sv: 'GDPR - klagomål till Datainspektionen', en: 'GDPR - complaint to authority'},
        {da: 'Ulovlig overvågning', sv: 'Olaglig övervakning', en: 'Illegal surveillance'}
    ],
    'generel': [
        {da: 'Generel diskussion', sv: 'Allmän diskussion', en: 'General discussion'},
        {da: 'Erfaringsudveksling', sv: 'Erfarenhetsutbyte', en: 'Experience sharing'},
        {da: 'Støtte og motivation', sv: 'Stöd och motivation', en: 'Support and motivation'},
        {da: 'Juridiske spørgsmål', sv: 'Juridiska frågor', en: 'Legal questions'},
        {da: 'Myndighedskontakt', sv: 'Myndighetskontakt', en: 'Authority contact'}
    ]
};

// Update subcategories when main category changes
function updateForumSubcategories() {
    const category = document.getElementById('forum_category').value;
    const container = document.getElementById('forum_subcategory_container');
    const select = document.getElementById('forum_subcategory');
    
    if (!category || !forumSubcategories[category]) {
        container.style.display = 'none';
        select.removeAttribute('required');
        return;
    }
    
    const placeholder = lang === 'da' ? 'Vælg subkategori...' : (lang === 'sv' ? 'Välj underkategori...' : 'Select subcategory...');
    select.innerHTML = `<option value="">${placeholder}</option>`;
    
    forumSubcategories[category].forEach(sub => {
        const text = lang === 'da' ? sub.da : (lang === 'sv' ? sub.sv : sub.en);
        const value = sub.da; // Always use Danish as value for backend consistency
        select.innerHTML += `<option value="${value}">${text}</option>`;
    });
    
    container.style.display = 'block';
}

async function shareContent(type, id) {
    try {
        const response = await fetch('/wp-json/kate/v1/share', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ source_type: type, source_id: id })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const lang = '<?php echo $lang; ?>';
            const msg = lang === 'da' ? 'Indhold delt til din væg!' : 'Innehåll delat till din vägg!';
            alert(data.message || msg);
            location.reload();
        } else {
            const lang = '<?php echo $lang; ?>';
            const errMsg = lang === 'da' ? 'Kunne ikke dele' : 'Kunde inte dela';
            alert(data.error || errMsg);
        }
    } catch (error) {
        console.error('Share error:', error);
        const lang = '<?php echo $lang; ?>';
        const errMsg = lang === 'da' ? 'Der opstod en fejl' : 'Ett fel uppstod';
        alert(errMsg);
    }
}

// Filter subcategory update function
function updateSearchSubcategories() {
    const category = document.getElementById('filter_category_search').value;
    const container = document.getElementById('filter_subcategory_container');
    const select = document.getElementById('filter_subcategory_search');
    
    if (!category || !forumSubcategories[category]) {
        container.style.display = 'none';
        return;
    }
    
    const placeholder = lang === 'da' ? 'Alle subkategorier' : (lang === 'sv' ? 'Alla underkategorier' : 'All subcategories');
    select.innerHTML = `<option value="">${placeholder}</option>`;
    
    forumSubcategories[category].forEach(sub => {
        const text = lang === 'da' ? sub.da : (lang === 'sv' ? sub.sv : sub.en);
        const value = sub.da; // Danish for backend consistency
        select.innerHTML += `<option value="${value}">${text}</option>`;
    });
    
    container.style.display = 'block';
}

// Initialize filter subcategories on page load if category is already selected
document.addEventListener('DOMContentLoaded', function() {
    const filterCategory = document.getElementById('filter_category_search');
    if (filterCategory && filterCategory.value) {
        updateSearchSubcategories();
        
        // Set selected subcategory if exists
        const urlParams = new URLSearchParams(window.location.search);
        const selectedSubcat = urlParams.get('filter_subcategory');
        if (selectedSubcat) {
            setTimeout(() => {
                const subcatSelect = document.getElementById('filter_subcategory_search');
                if (subcatSelect) {
                    subcatSelect.value = selectedSubcat;
                }
            }, 100);
        }
    }
});
</script>

</div>

<?php get_footer(); ?>
