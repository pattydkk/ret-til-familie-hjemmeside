<?php
/**
 * Template Name: Platform Social Væg
 */

get_header();
$lang = rtf_get_lang();

if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth/?lang=' . $lang));
    exit;
}

$current_user = rtf_get_current_user();
global $wpdb;
$table_posts = $wpdb->prefix . 'rtf_platform_posts';
$table_users = $wpdb->prefix . 'rtf_platform_users';
$table_shares = $wpdb->prefix . 'rtf_platform_shares';
$table_news = $wpdb->prefix . 'rtf_platform_news';
$table_forum = $wpdb->prefix . 'rtf_platform_forum_topics';

// Handle new post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_post') {
    // CSRF protection
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'rtf_create_post')) {
        wp_die('Security check failed');
    }
    
    $content = sanitize_textarea_field($_POST['content']);
    if (!empty($content)) {
        $wpdb->insert($table_posts, array(
            'user_id' => $current_user->id,
            'content' => $content,
            'likes' => 0,
            'created_at' => current_time('mysql')
        ));
        wp_redirect(home_url('/platform-vaeg/?lang=' . $lang));
        exit;
    }
}

// Handle like
if (isset($_GET['like'])) {
    $post_id = intval($_GET['like']);
    $wpdb->query($wpdb->prepare("UPDATE $table_posts SET likes = likes + 1 WHERE id = %d", $post_id));
    wp_redirect(home_url('/platform-vaeg/?lang=' . $lang));
    exit;
}

// Handle delete
if (isset($_GET['delete']) && (rtf_is_admin_user() || true)) {
    $post_id = intval($_GET['delete']);
    $post = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_posts WHERE id = %d", $post_id));
    if ($post && ($post->user_id == $current_user->id || rtf_is_admin_user())) {
        $wpdb->delete($table_posts, array('id' => $post_id));
    }
    wp_redirect(home_url('/platform-vaeg/?lang=' . $lang));
    exit;
}

// Get all posts
$posts = $wpdb->get_results("
    SELECT p.*, u.username, u.full_name, 'post' as content_type, NULL as shared_by_id, NULL as shared_by_name
    FROM $table_posts p 
    JOIN $table_users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 50
");

// Get shares
$shares = $wpdb->get_results("
    SELECT s.id as share_id, s.source_type, s.source_id, s.created_at as shared_at,
           u.id as shared_by_id, u.username as shared_by_username, u.full_name as shared_by_name,
           CASE 
               WHEN s.source_type = 'post' THEN (SELECT content FROM $table_posts WHERE id = s.source_id)
               WHEN s.source_type = 'news' THEN (SELECT title FROM $table_news WHERE id = s.source_id)
               WHEN s.source_type = 'forum' THEN (SELECT title FROM $table_forum WHERE id = s.source_id)
           END as content,
           CASE 
               WHEN s.source_type = 'post' THEN (SELECT u2.full_name FROM $table_posts p2 JOIN $table_users u2 ON p2.user_id = u2.id WHERE p2.id = s.source_id)
               WHEN s.source_type = 'news' THEN (SELECT u2.full_name FROM $table_news n2 JOIN $table_users u2 ON n2.author_id = u2.id WHERE n2.id = s.source_id)
               WHEN s.source_type = 'forum' THEN (SELECT u2.full_name FROM $table_forum f2 JOIN $table_users u2 ON f2.user_id = u2.id WHERE f2.id = s.source_id)
           END as original_author
    FROM $table_shares s
    JOIN $table_users u ON s.user_id = u.id
    WHERE s.source_id IS NOT NULL
    ORDER BY s.created_at DESC
    LIMIT 20
");

// Merge posts and shares
$feed_items = array_merge($posts, $shares);

// Sort by date
usort($feed_items, function($a, $b) {
    $date_a = isset($a->shared_at) ? $a->shared_at : $a->created_at;
    $date_b = isset($b->shared_at) ? $b->shared_at : $b->created_at;
    return strtotime($date_b) - strtotime($date_a);
});

$t = array(
    'da' => array(
        'title' => 'Social Væg',
        'write_post' => 'Skriv noget...',
        'post_btn' => 'Del',
        'no_posts' => 'Ingen opslag endnu. Vær den første til at dele noget!',
        'like' => 'Synes godt om',
        'delete' => 'Slet',
        'posted' => 'Oprettet',
    ),
    'sv' => array(
        'title' => 'Social Vägg',
        'write_post' => 'Skriv något...',
        'post_btn' => 'Dela',
        'no_posts' => 'Inga inlägg än. Var först med att dela något!',
        'like' => 'Gilla',
        'delete' => 'Radera',
        'posted' => 'Skapad',
    ),
    'en' => array(
        'title' => 'Social Wall',
        'write_post' => 'Write something...',
        'post_btn' => 'Share',
        'no_posts' => 'No posts yet. Be the first to share something!',
        'like' => 'Like',
        'delete' => 'Delete',
        'posted' => 'Posted',
    )
);
$txt = $t[$lang];
?>

<div class="platform-layout" style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <?php get_template_part('template-parts/platform-sidebar'); ?>
    
    <main class="platform-vaeg" style="min-width: 0;">
    <div class="container" style="max-width: 800px; margin: 40px auto; padding: 20px;">
        
        <h1 style="margin-bottom: 30px; color: var(--rtf-text);"><?php echo esc_html($txt['title']); ?></h1>

        <!-- NEW POST FORM -->
        <div class="new-post-card" style="background: var(--rtf-card); padding: 30px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 30px;">
            <form method="POST" action="">
                <input type="hidden" name="action" value="create_post">
                <?php wp_nonce_field('rtf_create_post'); ?>
                <textarea name="content" rows="4" placeholder="<?php echo esc_attr($txt['write_post']); ?>" required style="width: 100%; padding: 15px; border: 1px solid #e0f2fe; border-radius: 12px; font-size: 1em; font-family: inherit; resize: vertical; margin-bottom: 15px;"></textarea>
                <button type="submit" class="btn-primary" style="padding: 12px 30px;">
                    <?php echo esc_html($txt['post_btn']); ?>
                </button>
            </form>
        </div>

        <!-- POSTS FEED -->
        <div class="posts-feed">
            <?php if (empty($feed_items)): ?>
                <div style="text-align: center; padding: 60px 20px; color: var(--rtf-muted);">
                    <div style="font-size: 4em; margin-bottom: 20px;">💬</div>
                    <p style="font-size: 1.2em;"><?php echo esc_html($txt['no_posts']); ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($feed_items as $item): ?>
                    <?php if (isset($item->share_id)): ?>
                        <!-- SHARED CONTENT -->
                        <div class="post-card" style="background: var(--rtf-card); padding: 25px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 20px; border-left: 4px solid #10b981;">
                            <div style="display: flex; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e0f2fe;">
                                <div style="font-size: 1.2em; margin-right: 10px;">🔄</div>
                                <div style="font-size: 0.9em; color: #10b981; font-weight: 600;">
                                    <?php echo esc_html($item->shared_by_name); ?> <?php echo $lang === 'da' ? 'delte' : 'delade'; ?>
                                    <span style="color: var(--rtf-muted);">• <?php echo rtf_time_ago($item->shared_at); ?></span>
                                </div>
                            </div>
                            
                            <div style="padding-left: 20px; border-left: 2px solid #e0f2fe;">
                                <div style="font-size: 0.9em; color: var(--rtf-muted); margin-bottom: 10px;">
                                    <?php echo $lang === 'da' ? 'Originalt af' : 'Ursprungligen av'; ?> <?php echo esc_html($item->original_author); ?>
                                </div>
                                <div class="post-content" style="color: var(--rtf-text); line-height: 1.6; white-space: pre-wrap;">
                                    <?php echo esc_html($item->content); ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- REGULAR POST -->
                        <div class="post-card" style="background: var(--rtf-card); padding: 25px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                <div class="avatar" style="width: 48px; height: 48px; background: linear-gradient(135deg, #60a5fa, #2563eb); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5em; color: white; margin-right: 15px;">
                                    <?php echo strtoupper(substr($item->username, 0, 1)); ?>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; color: var(--rtf-text);"><?php echo esc_html($item->full_name); ?></div>
                                    <div style="font-size: 0.9em; color: var(--rtf-muted);">@<?php echo esc_html($item->username); ?> • <?php echo rtf_time_ago($item->created_at); ?></div>
                                </div>
                                <?php if ($item->user_id == $current_user->id || rtf_is_admin_user()): ?>
                                    <a href="?delete=<?php echo $item->id; ?>&lang=<?php echo $lang; ?>" onclick="return confirm('Er du sikker?')" style="color: #ef4444; text-decoration: none; font-size: 0.9em;">
                                        <?php echo esc_html($txt['delete']); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="post-content" style="color: var(--rtf-text); line-height: 1.6; margin-bottom: 15px; white-space: pre-wrap;">
                                <?php echo esc_html($item->content); ?>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 20px; padding-top: 15px; border-top: 1px solid #e0f2fe;">
                                <a href="?like=<?php echo $item->id; ?>&lang=<?php echo $lang; ?>" class="post-action" style="display: flex; align-items: center; gap: 8px; color: #2563eb; text-decoration: none; font-weight: 600;">
                                    <span style="font-size: 1.2em;">👍</span>
                                    <span><?php echo $item->likes; ?> <?php echo esc_html($txt['like']); ?></span>
                                </a>
                                <button onclick="shareContent('post', <?php echo $item->id; ?>)" class="post-action" style="display: flex; align-items: center; gap: 8px; color: #10b981; background: none; border: none; cursor: pointer; font-weight: 600; font-size: 1em;">
                                    <span style="font-size: 1.2em;">🔄</span>
                                    <span><?php echo $lang === 'da' ? 'Del' : 'Dela'; ?></span>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="<?php echo home_url('/platform-profil/?lang=' . $lang); ?>" style="color: #2563eb; text-decoration: none;">
                ← Tilbage til profil
            </a>
        </div>

    </div>
</main>

<script>
async function shareContent(type, id) {
    try {
        const response = await fetch('/wp-json/kate/v1/share', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                source_type: type,
                source_id: id
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message || '<?php echo $lang === "da" ? "Indhold delt til din væg!" : "Innehåll delat till din vägg!"; ?>');
            location.reload();
        } else {
            alert(data.error || '<?php echo $lang === "da" ? "Kunne ikke dele" : "Kunde inte dela"; ?>');
        }
    } catch (error) {
        console.error('Share error:', error);
        alert('<?php echo $lang === "da" ? "Der opstod en fejl" : "Ett fel uppstod"; ?>');
    }
}
</script>

</div>

<?php get_footer(); ?>
