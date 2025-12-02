<?php
/**
 * Template Name: Platform Forum
 */
get_header();
$lang = rtf_get_lang();
if (!rtf_is_logged_in()) { wp_redirect(home_url('/platform-auth/?lang=' . $lang)); exit; }
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

// Create topic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_topic'])) {
    // CSRF protection
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'rtf_create_topic')) {
        wp_die('Security check failed');
    }
    
    $wpdb->insert($table_topics, array(
        'user_id' => $current_user->id, 
        'title' => sanitize_text_field($_POST['title']), 
        'content' => sanitize_textarea_field($_POST['content']),
        'country' => sanitize_text_field($_POST['country'] ?? ''),
        'city' => sanitize_text_field($_POST['city'] ?? ''),
        'case_type' => sanitize_text_field($_POST['case_type'] ?? ''),
        'created_at' => current_time('mysql')
    ));
    wp_redirect(home_url('/platform-forum/?lang=' . $lang));
    exit;
}

// FILTER TOPICS - SQL WHERE CLAUSES
$where_clauses = ["1=1"];
$filter_country = isset($_GET['filter_country']) ? sanitize_text_field($_GET['filter_country']) : '';
$filter_city = isset($_GET['filter_city']) ? sanitize_text_field($_GET['filter_city']) : '';
$filter_case_type = isset($_GET['filter_case_type']) ? sanitize_text_field($_GET['filter_case_type']) : '';

if (!empty($filter_country)) {
    $where_clauses[] = $wpdb->prepare("t.country = %s", $filter_country);
}
if (!empty($filter_city)) {
    $where_clauses[] = $wpdb->prepare("t.city = %s", $filter_city);
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
                        <option value="NO" <?php selected($filter_country, 'NO'); ?>>🇳🇴 Norge</option>
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
            
            <?php if (!empty($filter_country) || !empty($filter_city) || !empty($filter_case_type)): ?>
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
            <form method="POST" action="">
                <?php wp_nonce_field('rtf_create_topic'); ?>
                <input type="text" name="title" placeholder="<?php echo $lang === 'da' ? 'Titel' : 'Titel'; ?>" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; margin-bottom: 15px;">
                <textarea name="content" rows="5" placeholder="<?php echo $lang === 'da' ? 'Indhold...' : 'Innehåll...'; ?>" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-family: inherit; margin-bottom: 15px;"></textarea>
                
                <!-- Metadata for filtering -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-bottom: 15px;">
                    <select name="country" style="padding: 10px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                        <option value=""><?php echo $lang === 'da' ? 'Vælg land' : 'Välj land'; ?></option>
                        <option value="DK">🇩🇰 Danmark</option>
                        <option value="SE">🇸🇪 Sverige</option>
                        <option value="NO">🇳🇴 Norge</option>
                    </select>
                    
                    <input type="text" name="city" placeholder="<?php echo $lang === 'da' ? 'By (valgfri)' : 'Stad (valfritt)'; ?>" style="padding: 10px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                    
                    <select name="case_type" style="padding: 10px; border: 1px solid #e0f2fe; border-radius: 8px; font-size: 1em;">
                        <option value=""><?php echo $lang === 'da' ? 'Sagstype' : 'Ärendetyp'; ?></option>
                        <option value="forældremyndighed"><?php echo $lang === 'da' ? 'Forældremyndighed' : 'Vårdnad'; ?></option>
                        <option value="samvær"><?php echo $lang === 'da' ? 'Samvær' : 'Umgänge'; ?></option>
                        <option value="anbringelse"><?php echo $lang === 'da' ? 'Anbringelse' : 'Placering'; ?></option>
                        <option value="tvangsfjernelse"><?php echo $lang === 'da' ? 'Tvangsfjernelse' : 'Tvångsomhändertagande'; ?></option>
                        <option value="børnebidrag"><?php echo $lang === 'da' ? 'Børnebidrag' : 'Barnbidrag'; ?></option>
                        <option value="skilsmisse"><?php echo $lang === 'da' ? 'Skilsmisse' : 'Skilsmässa'; ?></option>
                        <option value="andet"><?php echo $lang === 'da' ? 'Andet' : 'Annat'; ?></option>
                    </select>
                </div>
                
                <button type="submit" name="create_topic" class="btn-primary"><?php echo $lang === 'da' ? 'Opret Emne' : 'Skapa Ämne'; ?></button>
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
</script>

</div>

<?php get_footer(); ?>
