<?php
/**
 * Template Name: Platform Nyheder
 */
get_header();
$lang = rtf_get_lang();
if (!rtf_is_logged_in()) { wp_redirect(home_url('/platform-auth/?lang=' . $lang)); exit; }
$current_user = rtf_get_current_user();
global $wpdb;
$table_news = $wpdb->prefix . 'rtf_platform_news';
$table_users = $wpdb->prefix . 'rtf_platform_users';

// Admin can create news
if ($_SERVER['REQUEST_METHOD'] === 'POST' && rtf_is_admin_user()) {
    // CSRF protection
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'rtf_create_news')) {
        wp_die('Security check failed');
    }
    
    $wpdb->insert($table_news, array('title' => sanitize_text_field($_POST['title']), 'content' => sanitize_textarea_field($_POST['content']), 'author_id' => $current_user->id, 'created_at' => current_time('mysql')));
    wp_redirect(home_url('/platform-nyheder/?lang=' . $lang));
    exit;
}

$news = $wpdb->get_results("SELECT n.*, u.full_name FROM $table_news n JOIN $table_users u ON n.author_id = u.id ORDER BY n.created_at DESC LIMIT 20");
$t = array('da' => array('title' => 'Nyheder', 'create' => 'Opret Nyhed', 'title_field' => 'Titel', 'content' => 'Indhold', 'publish' => 'Publicer', 'no_news' => 'Ingen nyheder endnu', 'by' => 'Af'), 'sv' => array('title' => 'Nyheter', 'create' => 'Skapa Nyhet', 'title_field' => 'Titel', 'content' => 'Innehåll', 'publish' => 'Publicera', 'no_news' => 'Inga nyheter än', 'by' => 'Av'), 'en' => array('title' => 'News', 'create' => 'Create News', 'title_field' => 'Title', 'content' => 'Content', 'publish' => 'Publish', 'no_news' => 'No news yet', 'by' => 'By'));
$txt = $t[$lang];
?>

<div class="platform-layout" style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <?php get_template_part('template-parts/platform-sidebar'); ?>
    
    <main class="platform-nyheder" style="min-width: 0;">
    <div class="container" style="max-width: 900px; margin: 40px auto; padding: 20px;">
        <h1 style="margin-bottom: 30px; color: var(--rtf-text);"><?php echo esc_html($txt['title']); ?></h1>

        <?php if (rtf_is_admin_user()): ?>
            <div style="background: var(--rtf-card); padding: 30px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 30px;">
                <h3 style="margin-bottom: 20px;"><?php echo esc_html($txt['create']); ?></h3>
                <form method="POST" action="">
                    <?php wp_nonce_field('rtf_create_news'); ?>
                    <input type="text" name="title" placeholder="<?php echo esc_attr($txt['title_field']); ?>" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; margin-bottom: 15px;">
                    <textarea name="content" rows="6" placeholder="<?php echo esc_attr($txt['content']); ?>" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-family: inherit; margin-bottom: 15px;"></textarea>
                    <button type="submit" class="btn-primary"><?php echo esc_html($txt['publish']); ?></button>
                </form>
            </div>
        <?php endif; ?>

        <div class="news-feed">
            <?php if (empty($news)): ?>
                <div style="text-align: center; padding: 60px; background: var(--rtf-card); border-radius: 16px;">
                    <div style="font-size: 4em; margin-bottom: 20px;">📰</div>
                    <p style="font-size: 1.2em; color: var(--rtf-muted);"><?php echo esc_html($txt['no_news']); ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($news as $item): ?>
                    <div style="background: var(--rtf-card); padding: 35px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 25px;">
                        <h2 style="color: var(--rtf-text); margin-bottom: 15px;"><?php echo esc_html($item->title); ?></h2>
                        <div style="color: var(--rtf-muted); margin-bottom: 20px; font-size: 0.9em;">
                            <?php echo esc_html($txt['by']); ?> <?php echo esc_html($item->full_name); ?> • <?php echo rtf_time_ago($item->created_at); ?>
                        </div>
                        <div style="color: var(--rtf-text); line-height: 1.8; white-space: pre-wrap;"><?php echo esc_html($item->content); ?></div>
                        
                        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e0f2fe;">
                            <button onclick="shareContent('news', <?php echo $item->id; ?>)" style="display: inline-flex; align-items: center; gap: 8px; color: #10b981; background: none; border: none; cursor: pointer; font-weight: 600; font-size: 1em;">
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
