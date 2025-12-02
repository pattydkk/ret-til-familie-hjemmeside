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

<main class="platform-forum">
    <div class="container" style="max-width: 900px; margin: 40px auto; padding: 20px;">
        <h1 style="margin-bottom: 30px; color: var(--rtf-text);">Forum</h1>

        <div style="background: var(--rtf-card); padding: 30px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 30px;">
            <h3 style="margin-bottom: 20px;">Opret Nyt Emne</h3>
            <form method="POST" action="">
                <?php wp_nonce_field('rtf_create_topic'); ?>
                <input type="text" name="title" placeholder="Titel" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; margin-bottom: 15px;">
                <textarea name="content" rows="5" placeholder="Indhold..." required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-family: inherit; margin-bottom: 15px;"></textarea>
                <button type="submit" class="btn-primary">Opret Emne</button>
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

<?php get_footer(); ?>
