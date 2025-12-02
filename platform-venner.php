<?php
/**
 * Template Name: Platform - Venner (Friends)
 */

if (!session_id()) session_start();

if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth'));
    exit;
}

$user = rtf_get_current_user();
global $wpdb;
$friends_table = $wpdb->prefix . 'rtf_platform_friends';
$users_table = $wpdb->prefix . 'rtf_platform_users';

// Handle friend request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_request'])) {
    // CSRF protection
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'rtf_friend_request')) {
        wp_die('Security check failed');
    }
    
    $friend_username = sanitize_text_field($_POST['friend_username']);
    $friend = $wpdb->get_row($wpdb->prepare("SELECT * FROM $users_table WHERE username = %s", $friend_username));
    
    if ($friend && $friend->id != $user['id']) {
        // Check if request already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $friends_table WHERE (user_id = %d AND friend_id = %d) OR (user_id = %d AND friend_id = %d)",
            $user['id'], $friend->id, $friend->id, $user['id']
        ));
        
        if (!$existing) {
            $wpdb->insert($friends_table, [
                'user_id' => $user['id'],
                'friend_id' => $friend->id,
                'status' => 'pending'
            ]);
        }
    }
    
    wp_redirect(home_url('/platform-venner'));
    exit;
}

// Handle accept/decline
if (isset($_GET['action']) && isset($_GET['request_id'])) {
    $request_id = intval($_GET['request_id']);
    $action = sanitize_text_field($_GET['action']);
    
    $request = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $friends_table WHERE id = %d AND friend_id = %d",
        $request_id, $user['id']
    ));
    
    if ($request) {
        if ($action === 'accept') {
            $wpdb->update($friends_table, ['status' => 'accepted'], ['id' => $request_id]);
        } elseif ($action === 'decline') {
            $wpdb->delete($friends_table, ['id' => $request_id]);
        }
    }
    
    wp_redirect(home_url('/platform-venner'));
    exit;
}

// Get friend requests (incoming)
$requests = $wpdb->get_results($wpdb->prepare(
    "SELECT f.*, u.username, u.full_name 
     FROM $friends_table f 
     JOIN $users_table u ON f.user_id = u.id 
     WHERE f.friend_id = %d AND f.status = 'pending'",
    $user['id']
));

// Get friends list
$friends = $wpdb->get_results($wpdb->prepare(
    "SELECT u.*, f.created_at as friends_since
     FROM $friends_table f
     JOIN $users_table u ON (f.user_id = u.id OR f.friend_id = u.id)
     WHERE (f.user_id = %d OR f.friend_id = %d) AND f.status = 'accepted' AND u.id != %d",
    $user['id'], $user['id'], $user['id']
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

.search-section {
    background: white;
    border: 1px solid #dbeafe;
    border-radius: 18px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.search-form {
    display: flex;
    gap: 1rem;
}

.search-form input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 1px solid #dbeafe;
    border-radius: 999px;
    font-size: 0.95rem;
}

.btn-search {
    padding: 0.75rem 2rem;
    background: linear-gradient(135deg, #60a5fa, #2563eb);
    color: white;
    border: none;
    border-radius: 999px;
    font-weight: 600;
    cursor: pointer;
}

.requests-section,
.friends-section {
    background: white;
    border: 1px solid #dbeafe;
    border-radius: 18px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.requests-section h2,
.friends-section h2 {
    margin: 0 0 1.5rem 0;
    font-size: 1.3rem;
    color: #0f172a;
}

.friend-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem;
    background: #f9fafb;
    border: 1px solid #dbeafe;
    border-radius: 12px;
    margin-bottom: 1rem;
}

.friend-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.friend-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #60a5fa, #2563eb);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.2rem;
}

.friend-details h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1.05rem;
    color: #0f172a;
}

.friend-details p {
    margin: 0;
    font-size: 0.85rem;
    color: #64748b;
}

.friend-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-accept {
    padding: 0.625rem 1.25rem;
    background: linear-gradient(135deg, #34d399, #10b981);
    color: white;
    border: none;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    text-decoration: none;
}

.btn-decline {
    padding: 0.625rem 1.25rem;
    background: #fee2e2;
    color: #ef4444;
    border: none;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    text-decoration: none;
}

@media (max-width: 768px) {
    .platform-layout {
        grid-template-columns: 1fr;
    }
    
    .platform-sidebar {
        position: static;
    }
    
    .friend-card {
        flex-direction: column;
        align-items: flex-start;
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
            <li><a href="<?php echo home_url('/platform-venner'); ?>" class="active">👥 Venner</a></li>
            <li><a href="<?php echo home_url('/platform-forum'); ?>">💬 Forum</a></li>
            <li><a href="<?php echo home_url('/platform-nyheder'); ?>">📰 Nyheder</a></li>
            <li><a href="<?php echo home_url('/platform-sagshjaelp'); ?>">⚖️ Sagshjælp</a></li>
            <li><a href="<?php echo home_url('/platform-kate-ai'); ?>">🤖 Kate AI</a></li>
            <li><a href="<?php echo home_url('/platform-indstillinger'); ?>">⚙️ Indstillinger</a></li>
        </ul>
    </aside>
    
    <main class="platform-content">
        <div class="search-section">
            <h2>🔍 Find venner</h2>
            <form method="POST" class="search-form">
                <?php wp_nonce_field('rtf_friend_request'); ?>
                <input type="text" name="friend_username" placeholder="Indtast brugernavn..." required>
                <button type="submit" name="send_request" class="btn-search">Send venneanmodning</button>
            </form>
        </div>
        
        <?php if (!empty($requests)): ?>
            <div class="requests-section">
                <h2>📬 Venneanmodninger (<?php echo count($requests); ?>)</h2>
                <?php foreach ($requests as $request): ?>
                    <div class="friend-card">
                        <div class="friend-info">
                            <div class="friend-avatar">
                                <?php echo strtoupper(substr($request->username, 0, 2)); ?>
                            </div>
                            <div class="friend-details">
                                <h3><?php echo esc_html($request->full_name); ?></h3>
                                <p>@<?php echo esc_html($request->username); ?></p>
                            </div>
                        </div>
                        
                        <div class="friend-actions">
                            <a href="?action=accept&request_id=<?php echo $request->id; ?>" class="btn-accept">
                                ✓ Acceptér
                            </a>
                            <a href="?action=decline&request_id=<?php echo $request->id; ?>" class="btn-decline">
                                ✕ Afvis
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="friends-section">
            <h2>👥 Mine venner (<?php echo count($friends); ?>)</h2>
            
            <?php if (empty($friends)): ?>
                <div style="text-align: center; padding: 3rem; color: #64748b;">
                    <p style="font-size: 3rem; margin-bottom: 1rem;">👥</p>
                    <p>Du har ingen venner endnu. Send en venneanmodning ovenfor!</p>
                </div>
            <?php else: ?>
                <?php foreach ($friends as $friend): ?>
                    <div class="friend-card">
                        <div class="friend-info">
                            <div class="friend-avatar">
                                <?php echo strtoupper(substr($friend->username, 0, 2)); ?>
                            </div>
                            <div class="friend-details">
                                <h3><?php echo esc_html($friend->full_name); ?></h3>
                                <p>@<?php echo esc_html($friend->username); ?> • Venner siden <?php echo rtf_format_date($friend->friends_since); ?></p>
                            </div>
                        </div>
                        
                        <div class="friend-actions">
                            <a href="<?php echo home_url('/platform-profil?user=' . $friend->id); ?>" class="btn-accept" style="background: #e0f2fe; color: #2563eb;">
                                👤 Se profil
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php get_footer(); ?>
