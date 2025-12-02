<?php
/**
 * Template Name: Platform - Komplet Admin Panel
 * Fuld kontrol over brugere, indhold, statistik og system
 */

get_header();
$lang = rtf_get_lang();

// Tjek admin login
if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth/?lang=' . $lang));
    exit;
}

$current_user = rtf_get_current_user();
if (!$current_user->is_admin) {
    wp_redirect(home_url('/platform-profil/?lang=' . $lang));
    exit;
}

global $wpdb;
$users_table = $wpdb->prefix . 'rtf_platform_users';
$posts_table = $wpdb->prefix . 'rtf_platform_posts';
$forum_table = $wpdb->prefix . 'rtf_platform_forum_topics';
$news_table = $wpdb->prefix . 'rtf_platform_news';
$messages_table = $wpdb->prefix . 'rtf_platform_messages';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitize_text_field($_POST['action']);
    $target_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    if ($target_user_id > 0 && $target_user_id != $current_user->id) {
        switch ($action) {
            case 'ban':
                $wpdb->update($users_table, ['is_active' => 0], ['id' => $target_user_id]);
                $success_message = "Bruger er blevet blokeret";
                break;
            case 'unban':
                $wpdb->update($users_table, ['is_active' => 1], ['id' => $target_user_id]);
                $success_message = "Bruger er blevet aktiveret";
                break;
            case 'delete':
                $wpdb->delete($users_table, ['id' => $target_user_id]);
                $wpdb->delete($posts_table, ['user_id' => $target_user_id]);
                $wpdb->delete($forum_table, ['user_id' => $target_user_id]);
                $success_message = "Bruger og alt indhold er blevet slettet";
                break;
            case 'make_admin':
                $wpdb->update($users_table, ['is_admin' => 1], ['id' => $target_user_id]);
                $success_message = "Bruger er nu administrator";
                break;
            case 'remove_admin':
                $wpdb->update($users_table, ['is_admin' => 0], ['id' => $target_user_id]);
                $success_message = "Admin rettigheder fjernet";
                break;
        }
    }
}

// Get statistics
$stats = [
    'total_users' => $wpdb->get_var("SELECT COUNT(*) FROM $users_table"),
    'active_users' => $wpdb->get_var("SELECT COUNT(*) FROM $users_table WHERE is_active = 1"),
    'banned_users' => $wpdb->get_var("SELECT COUNT(*) FROM $users_table WHERE is_active = 0"),
    'admins' => $wpdb->get_var("SELECT COUNT(*) FROM $users_table WHERE is_admin = 1"),
    'total_posts' => $wpdb->get_var("SELECT COUNT(*) FROM $posts_table"),
    'total_forum' => $wpdb->get_var("SELECT COUNT(*) FROM $forum_table"),
    'total_news' => $wpdb->get_var("SELECT COUNT(*) FROM $news_table"),
    'total_messages' => $wpdb->get_var("SELECT COUNT(*) FROM $messages_table"),
    'new_users_30d' => $wpdb->get_var("SELECT COUNT(*) FROM $users_table WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"),
];

// Get all users with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

$where = "WHERE 1=1";
if (!empty($search)) {
    $where .= $wpdb->prepare(" AND (username LIKE %s OR email LIKE %s OR full_name LIKE %s)", 
        '%' . $wpdb->esc_like($search) . '%',
        '%' . $wpdb->esc_like($search) . '%',
        '%' . $wpdb->esc_like($search) . '%'
    );
}
if ($filter_status === 'active') {
    $where .= " AND is_active = 1";
} elseif ($filter_status === 'banned') {
    $where .= " AND is_active = 0";
} elseif ($filter_status === 'admin') {
    $where .= " AND is_admin = 1";
}

$total_users = $wpdb->get_var("SELECT COUNT(*) FROM $users_table $where");
$total_pages = ceil($total_users / $per_page);

$users = $wpdb->get_results(
    "SELECT id, username, email, full_name, country, age, is_active, is_admin, created_at, last_login 
     FROM $users_table 
     $where 
     ORDER BY created_at DESC 
     LIMIT $per_page OFFSET $offset"
);
?>

<style>
:root {
    --admin-primary: #1e40af;
    --admin-secondary: #3b82f6;
    --admin-danger: #dc2626;
    --admin-success: #16a34a;
    --admin-warning: #f59e0b;
    --admin-bg: #f8fafc;
    --admin-card: #ffffff;
}

.admin-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 2rem;
    background: var(--admin-bg);
    min-height: 100vh;
}

.admin-header {
    background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.admin-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.admin-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.05rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--admin-card);
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border-left: 4px solid var(--admin-secondary);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.stat-card .stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--admin-primary);
    margin: 0.5rem 0;
}

.stat-card .stat-label {
    font-size: 0.9rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.admin-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 0.75rem 1.5rem;
    background: var(--admin-card);
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
    color: #64748b;
}

.tab-btn:hover {
    background: var(--admin-secondary);
    color: white;
    border-color: var(--admin-secondary);
}

.tab-btn.active {
    background: var(--admin-primary);
    color: white;
    border-color: var(--admin-primary);
}

.admin-card {
    background: var(--admin-card);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}

.search-bar {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.search-bar input,
.search-bar select {
    padding: 0.75rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    flex: 1;
    min-width: 200px;
}

.search-bar button {
    padding: 0.75rem 1.5rem;
    background: var(--admin-primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.search-bar button:hover {
    background: var(--admin-secondary);
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

.users-table thead {
    background: #f1f5f9;
}

.users-table th,
.users-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.users-table th {
    font-weight: 700;
    color: #475569;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.users-table tr:hover {
    background: #f8fafc;
}

.user-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.badge-active {
    background: #dcfce7;
    color: #16a34a;
}

.badge-banned {
    background: #fee2e2;
    color: #dc2626;
}

.badge-admin {
    background: #dbeafe;
    color: #1e40af;
}

.action-btns {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: var(--admin-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--admin-secondary);
}

.btn-success {
    background: var(--admin-success);
    color: white;
}

.btn-success:hover {
    background: #15803d;
}

.btn-danger {
    background: var(--admin-danger);
    color: white;
}

.btn-danger:hover {
    background: #b91c1c;
}

.btn-warning {
    background: var(--admin-warning);
    color: white;
}

.btn-warning:hover {
    background: #d97706;
}

.pagination {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.pagination a,
.pagination span {
    padding: 0.5rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    text-decoration: none;
    color: #64748b;
    font-weight: 600;
}

.pagination a:hover {
    background: var(--admin-secondary);
    color: white;
    border-color: var(--admin-secondary);
}

.pagination .current {
    background: var(--admin-primary);
    color: white;
    border-color: var(--admin-primary);
}

.success-message {
    background: #dcfce7;
    color: #16a34a;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border-left: 4px solid #16a34a;
    font-weight: 600;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}
</style>

<div class="admin-container">
    <div class="admin-header">
        <h1>
            <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
            Komplet Admin Panel
        </h1>
        <p>Velkommen, <?php echo esc_html($current_user->full_name); ?> • Fuld kontrol over platformen</p>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="success-message">✅ <?php echo esc_html($success_message); ?></div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">👥 Brugere i alt</div>
            <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">✅ Aktive brugere</div>
            <div class="stat-value"><?php echo number_format($stats['active_users']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">🚫 Blokerede</div>
            <div class="stat-value"><?php echo number_format($stats['banned_users']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">👑 Administratorer</div>
            <div class="stat-value"><?php echo number_format($stats['admins']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">📝 Posts</div>
            <div class="stat-value"><?php echo number_format($stats['total_posts']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">💬 Forum emner</div>
            <div class="stat-value"><?php echo number_format($stats['total_forum']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">📰 Nyheder</div>
            <div class="stat-value"><?php echo number_format($stats['total_news']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">📨 Beskeder</div>
            <div class="stat-value"><?php echo number_format($stats['total_messages']); ?></div>
        </div>
        <div class="stat-card" style="border-left-color: var(--admin-success);">
            <div class="stat-label">🆕 Nye (30 dage)</div>
            <div class="stat-value"><?php echo number_format($stats['new_users_30d']); ?></div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="admin-tabs">
        <button class="tab-btn active" onclick="switchTab('users')">👥 Brugerstyring</button>
        <button class="tab-btn" onclick="switchTab('content')">📝 Indholdsstyring</button>
        <button class="tab-btn" onclick="switchTab('settings')">⚙️ Systemindstillinger</button>
        <button class="tab-btn" onclick="switchTab('logs')">📋 Aktivitetslog</button>
    </div>

    <!-- Users Tab -->
    <div id="tab-users" class="tab-content active">
        <div class="admin-card">
            <h2 style="margin: 0 0 1.5rem 0;">Brugerstyring</h2>
            
            <form method="get" class="search-bar">
                <input type="text" name="search" placeholder="Søg bruger (navn, email, brugernavn)..." value="<?php echo esc_attr($search); ?>">
                <select name="status">
                    <option value="">Alle statuser</option>
                    <option value="active" <?php selected($filter_status, 'active'); ?>>Kun aktive</option>
                    <option value="banned" <?php selected($filter_status, 'banned'); ?>>Kun blokerede</option>
                    <option value="admin" <?php selected($filter_status, 'admin'); ?>>Kun admins</option>
                </select>
                <button type="submit">🔍 Søg</button>
                <?php if ($search || $filter_status): ?>
                    <a href="<?php echo home_url('/platform-admin/?lang=' . $lang); ?>" class="btn btn-warning">Nulstil</a>
                <?php endif; ?>
            </form>

            <div style="overflow-x: auto;">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Bruger</th>
                            <th>Email</th>
                            <th>Land</th>
                            <th>Alder</th>
                            <th>Status</th>
                            <th>Oprettet</th>
                            <th>Sidste login</th>
                            <th>Handlinger</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?php echo $user->id; ?></td>
                                <td>
                                    <strong><?php echo esc_html($user->full_name); ?></strong><br>
                                    <span style="font-size: 0.875rem; color: #64748b;">@<?php echo esc_html($user->username); ?></span>
                                </td>
                                <td><?php echo esc_html($user->email); ?></td>
                                <td>
                                    <?php 
                                    $flags = ['DK' => '🇩🇰', 'SE' => '🇸🇪', 'NO' => '🇳🇴'];
                                    echo isset($flags[$user->country]) ? $flags[$user->country] : '🌍';
                                    ?>
                                    <?php echo esc_html($user->country); ?>
                                </td>
                                <td><?php echo esc_html($user->age ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($user->is_admin): ?>
                                        <span class="user-badge badge-admin">👑 Admin</span>
                                    <?php endif; ?>
                                    <?php if ($user->is_active): ?>
                                        <span class="user-badge badge-active">✅ Aktiv</span>
                                    <?php else: ?>
                                        <span class="user-badge badge-banned">🚫 Blokeret</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.875rem; color: #64748b;">
                                    <?php echo date('d/m/Y', strtotime($user->created_at)); ?>
                                </td>
                                <td style="font-size: 0.875rem; color: #64748b;">
                                    <?php echo $user->last_login ? date('d/m/Y H:i', strtotime($user->last_login)) : 'Aldrig'; ?>
                                </td>
                                <td>
                                    <?php if ($user->id != $current_user->id): ?>
                                        <div class="action-btns">
                                            <a href="<?php echo home_url('/platform-profil-view/?user_id=' . $user->id); ?>" class="btn btn-primary" target="_blank">👁️ Se</a>
                                            
                                            <?php if ($user->is_active): ?>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="action" value="ban">
                                                    <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Blokér denne bruger?')">🚫 Blokér</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="action" value="unban">
                                                    <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                                                    <button type="submit" class="btn btn-success">✅ Aktivér</button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if (!$user->is_admin): ?>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="action" value="make_admin">
                                                    <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                                                    <button type="submit" class="btn btn-primary" onclick="return confirm('Gør til administrator?')">👑 Admin</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="action" value="remove_admin">
                                                    <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Fjern admin?')">↓ Fjern Admin</button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('SLET denne bruger og ALT indhold? Dette kan IKKE fortrydes!')">🗑️ Slet</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #64748b; font-style: italic;">Det er dig</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $filter_status; ?>">← Forrige</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $filter_status; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $filter_status; ?>">Næste →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Content Tab -->
    <div id="tab-content" class="tab-content">
        <div class="admin-card">
            <h2>Indholdsstyring</h2>
            <p>Se og moderer alt indhold på platformen</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
                <a href="<?php echo home_url('/platform-nyheder/?lang=' . $lang); ?>" style="text-decoration: none;">
                    <div class="stat-card" style="border-left-color: var(--admin-success);">
                        <div class="stat-label">📰 Administrer nyheder</div>
                        <div class="stat-value"><?php echo number_format($stats['total_news']); ?></div>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #64748b;">Opret, rediger og slet nyheder</p>
                    </div>
                </a>
                
                <a href="<?php echo home_url('/platform-forum/?lang=' . $lang); ?>" style="text-decoration: none;">
                    <div class="stat-card" style="border-left-color: var(--admin-warning);">
                        <div class="stat-label">💬 Se forum emner</div>
                        <div class="stat-value"><?php echo number_format($stats['total_forum']); ?></div>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #64748b;">Moderer og slet emner</p>
                    </div>
                </a>
                
                <div class="stat-card" style="border-left-color: var(--admin-secondary);">
                    <div class="stat-label">📝 Bruger posts</div>
                    <div class="stat-value"><?php echo number_format($stats['total_posts']); ?></div>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem; color: #64748b;">Se alle brugerposts</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Tab -->
    <div id="tab-settings" class="tab-content">
        <div class="admin-card">
            <h2>Systemindstillinger</h2>
            <p>Konfigurer platform indstillinger</p>
            
            <div style="margin-top: 2rem;">
                <h3>Platform status</h3>
                <div style="display: grid; gap: 1rem; margin-top: 1rem;">
                    <div style="padding: 1rem; background: #f8fafc; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                        <span>✅ Platform status: <strong>Aktiv</strong></span>
                        <span class="user-badge badge-active">Online</span>
                    </div>
                    <div style="padding: 1rem; background: #f8fafc; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                        <span>🗄️ Database: <strong><?php echo DB_NAME; ?></strong></span>
                        <span class="user-badge badge-active">Forbundet</span>
                    </div>
                    <div style="padding: 1rem; background: #f8fafc; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                        <span>📊 Statistik tæller: <strong>Real-time</strong></span>
                        <span class="user-badge badge-active">Kører</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Tab -->
    <div id="tab-logs" class="tab-content">
        <div class="admin-card">
            <h2>Aktivitetslog</h2>
            <p>De seneste handlinger på platformen</p>
            
            <div style="margin-top: 2rem;">
                <?php
                $recent_users = $wpdb->get_results(
                    "SELECT id, username, full_name, created_at FROM $users_table ORDER BY created_at DESC LIMIT 10"
                );
                ?>
                <h3>Seneste registreringer</h3>
                <table class="users-table" style="margin-top: 1rem;">
                    <thead>
                        <tr>
                            <th>Bruger</th>
                            <th>Tidspunkt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_users as $user): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($user->full_name); ?></strong><br>
                                    <span style="font-size: 0.875rem; color: #64748b;">@<?php echo esc_html($user->username); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($user->created_at)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById('tab-' + tabName).classList.add('active');
    event.target.classList.add('active');
}
</script>

<?php get_footer(); ?>
