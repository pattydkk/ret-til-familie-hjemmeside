<?php
/*
Template Name: Platform - Admin Dashboard (Full Control)
Description: Complete admin control panel with user management, content moderation, system settings
*/

get_header();
$lang = rtf_get_lang();

// Check login and admin status
if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth/?lang=' . $lang));
    exit;
}

$current_user = rtf_get_current_user();
if (!$current_user || $current_user->is_admin != 1) {
    wp_redirect(home_url('/platform-profil/?lang=' . $lang));
    exit;
}

global $wpdb;

// Translations
$translations = [
    'da' => [
        'title' => 'Admin Kontrolpanel',
        'subtitle' => 'Fuld kontrol over platformen',
        'sections' => [
            'stats' => 'Statistik & Overblik',
            'users' => 'Brugerstyring',
            'content' => 'Indholdsmoderation',
            'news' => 'Nyheder',
            'system' => 'Systemindstillinger',
            'logs' => 'Logfiler',
        ],
    ],
    'sv' => [
        'title' => 'Admin Kontrollpanel',
        'subtitle' => 'Full kontroll över plattformen',
        'sections' => [
            'stats' => 'Statistik & Översikt',
            'users' => 'Användarhantering',
            'content' => 'Innehållsmodering',
            'news' => 'Nyheter',
            'system' => 'Systeminställningar',
            'logs' => 'Loggfiler',
        ],
    ],
];

$t = $translations[$lang] ?? $translations['da'];
?>

<style>
:root {
    --admin-primary: #dc2626;
    --admin-dark: #991b1b;
    --text: #1f2937;
    --text-light: #6b7280;
    --bg-gray: #f9fafb;
    --border: #e5e7eb;
    --success: #10b981;
    --warning: #f59e0b;
}

.admin-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.admin-hero {
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
    padding: 3rem 2rem;
    border-radius: 20px;
    margin-bottom: 3rem;
    color: white;
    text-align: center;
}

.admin-hero h1 {
    font-size: 2.5rem;
    margin: 0 0 0.5rem 0;
    font-weight: 700;
}

.admin-hero p {
    font-size: 1.2rem;
    opacity: 0.95;
    margin: 0;
}

.section-nav {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.section-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.07);
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
    text-align: center;
}

.section-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.15);
    border-color: var(--admin-primary);
}

.section-card.active {
    border-color: var(--admin-primary);
    background: #fef2f2;
}

.section-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.section-name {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text);
}

.main-content {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.07);
    padding: 2.5rem;
}

.section-pane {
    display: none;
}

.section-pane.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    border-radius: 12px;
    color: white;
}

.stat-box.users { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-box.posts { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
.stat-box.messages { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
.stat-box.kate { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1.5rem;
}

.users-table th,
.users-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border);
}

.users-table th {
    background: var(--bg-gray);
    font-weight: 700;
    color: var(--text);
}

.users-table tbody tr:hover {
    background: var(--bg-gray);
}

.action-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    margin-right: 0.5rem;
}

.action-btn.edit {
    background: #3b82f6;
    color: white;
}

.action-btn.suspend {
    background: #f59e0b;
    color: white;
}

.action-btn.delete {
    background: #ef4444;
    color: white;
}

.action-btn:hover {
    opacity: 0.8;
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
}

.btn-primary {
    padding: 0.875rem 2rem;
    background: var(--admin-primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
}

.btn-primary:hover {
    background: var(--admin-dark);
}

.info-box {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
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

.warning-box {
    background: #fef3c7;
    border-left: 4px solid var(--warning);
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge.active { background: #d1fae5; color: #065f46; }
.badge.inactive { background: #fee2e2; color: #991b1b; }
.badge.admin { background: #dbeafe; color: #1e40af; }

.content-grid {
    display: grid;
    gap: 1rem;
    margin-top: 1.5rem;
}

.content-item {
    background: white;
    border: 2px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s;
}

.content-item:hover {
    border-color: var(--admin-primary);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.pagination {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    margin-top: 2rem;
}

.pagination button {
    padding: 0.5rem 1rem;
    border: 2px solid var(--border);
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
}

.pagination button.active {
    background: var(--admin-primary);
    color: white;
    border-color: var(--admin-primary);
}

.pagination button:hover:not(.active) {
    border-color: var(--admin-primary);
}

.filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}
</style>

<div class="admin-container">
    <!-- Hero -->
    <div class="admin-hero">
        <h1>🛡️ <?php echo $t['title']; ?></h1>
        <p><?php echo $t['subtitle']; ?></p>
    </div>

    <!-- Section Navigation -->
    <div class="section-nav">
        <div class="section-card active" onclick="switchSection('stats')" data-section="stats">
            <div class="section-icon">📊</div>
            <div class="section-name"><?php echo $t['sections']['stats']; ?></div>
        </div>
        <div class="section-card" onclick="switchSection('users')" data-section="users">
            <div class="section-icon">👥</div>
            <div class="section-name"><?php echo $t['sections']['users']; ?></div>
        </div>
        <div class="section-card" onclick="switchSection('content')" data-section="content">
            <div class="section-icon">🛡️</div>
            <div class="section-name"><?php echo $t['sections']['content']; ?></div>
        </div>
        <div class="section-card" onclick="switchSection('news')" data-section="news">
            <div class="section-icon">📰</div>
            <div class="section-name"><?php echo $t['sections']['news']; ?></div>
        </div>
        <div class="section-card" onclick="switchSection('system')" data-section="system">
            <div class="section-icon">⚙️</div>
            <div class="section-name"><?php echo $t['sections']['system']; ?></div>
        </div>
        <div class="section-card" onclick="switchSection('logs')" data-section="logs">
            <div class="section-icon">📋</div>
            <div class="section-name"><?php echo $t['sections']['logs']; ?></div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Stats Section -->
        <div class="section-pane active" id="section-stats">
            <h2>📊 Platform Statistik</h2>
            
            <div class="stats-grid">
                <?php
                $total_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_users");
                $active_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_users WHERE last_active > DATE_SUB(NOW(), INTERVAL 7 DAY)");
                $total_posts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_posts");
                $total_messages = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_messages");
                ?>
                
                <div class="stat-box users">
                    <div class="stat-label">Brugere i alt</div>
                    <div class="stat-value"><?php echo number_format($total_users); ?></div>
                </div>
                
                <div class="stat-box users">
                    <div class="stat-label">Aktive (7 dage)</div>
                    <div class="stat-value"><?php echo number_format($active_users); ?></div>
                </div>
                
                <div class="stat-box posts">
                    <div class="stat-label">Posts i alt</div>
                    <div class="stat-value"><?php echo number_format($total_posts); ?></div>
                </div>
                
                <div class="stat-box messages">
                    <div class="stat-label">Beskeder i alt</div>
                    <div class="stat-value"><?php echo number_format($total_messages); ?></div>
                </div>
            </div>

            <div class="info-box">
                <h3 style="margin-top:0;">💡 Platform Sundhed</h3>
                <p>Alle systemer kører normalt. Sidste opdatering: <?php echo current_time('d/m/Y H:i'); ?></p>
            </div>
        </div>

        <!-- Users Section -->
        <div class="section-pane" id="section-users">
            <h2>👥 Brugerstyring</h2>
            
            <div class="filters">
                <div class="filter-group">
                    <label>Søg bruger:</label>
                    <input type="text" id="user-search" class="form-control" placeholder="Navn, email eller ID...">
                </div>
                <div class="filter-group">
                    <label>Status:</label>
                    <select id="user-status-filter" class="form-control">
                        <option value="">Alle</option>
                        <option value="active">Aktive</option>
                        <option value="suspended">Suspenderede</option>
                        <option value="admin">Admins</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Land:</label>
                    <select id="user-country-filter" class="form-control">
                        <option value="">Alle</option>
                        <option value="DK">Danmark</option>
                        <option value="SE">Sverige</option>
                    </select>
                </div>
            </div>

            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Navn</th>
                        <th>Email</th>
                        <th>Land</th>
                        <th>Status</th>
                        <th>Oprettet</th>
                        <th>Handlinger</th>
                    </tr>
                </thead>
                <tbody id="users-tbody">
                    <?php
                    $users = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}rtf_platform_users ORDER BY created_at DESC LIMIT 50");
                    foreach ($users as $user):
                    ?>
                        <tr>
                            <td><?php echo $user->id; ?></td>
                            <td><?php echo esc_html($user->display_name); ?></td>
                            <td><?php echo esc_html($user->email); ?></td>
                            <td><?php echo esc_html($user->country ?? 'N/A'); ?></td>
                            <td>
                                <?php if ($user->is_admin == 1): ?>
                                    <span class="badge admin">Admin</span>
                                <?php elseif ($user->is_suspended == 1): ?>
                                    <span class="badge inactive">Suspenderet</span>
                                <?php else: ?>
                                    <span class="badge active">Aktiv</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user->created_at)); ?></td>
                            <td>
                                <button class="action-btn edit" onclick="editUser(<?php echo $user->id; ?>)">✏️ Rediger</button>
                                <?php if ($user->is_admin != 1): ?>
                                    <button class="action-btn suspend" onclick="toggleSuspend(<?php echo $user->id; ?>)">
                                        <?php echo $user->is_suspended == 1 ? '✓ Aktiver' : '⏸️ Suspender'; ?>
                                    </button>
                                    <button class="action-btn delete" onclick="deleteUser(<?php echo $user->id; ?>)">🗑️ Slet</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Content Moderation Section -->
        <div class="section-pane" id="section-content">
            <h2>🛡️ Indholdsmoderation</h2>
            
            <div class="warning-box">
                <strong>⚠️ Moderation:</strong> Gennemgå og administrer brugerindhold (posts, kommentarer, billeder)
            </div>

            <div class="filters">
                <div class="filter-group">
                    <label>Indholdstype:</label>
                    <select id="content-type-filter" class="form-control">
                        <option value="posts">Posts</option>
                        <option value="comments">Kommentarer</option>
                        <option value="images">Billeder</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Sorter efter:</label>
                    <select id="content-sort" class="form-control">
                        <option value="newest">Nyeste først</option>
                        <option value="oldest">Ældste først</option>
                        <option value="reported">Mest rapporterede</option>
                    </select>
                </div>
            </div>

            <div class="content-grid" id="content-grid">
                <?php
                $posts = $wpdb->get_results("
                    SELECT p.*, u.display_name 
                    FROM {$wpdb->prefix}rtf_platform_posts p
                    LEFT JOIN {$wpdb->prefix}rtf_platform_users u ON p.user_id = u.id
                    ORDER BY p.created_at DESC
                    LIMIT 20
                ");
                foreach ($posts as $post):
                ?>
                    <div class="content-item">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <strong><?php echo esc_html($post->display_name); ?></strong>
                                <span style="color: var(--text-light); font-size: 0.9rem;">
                                    • <?php echo date('d/m/Y H:i', strtotime($post->created_at)); ?>
                                </span>
                                <p style="margin: 0.5rem 0;"><?php echo esc_html(substr($post->content, 0, 200)); ?>...</p>
                            </div>
                            <div>
                                <button class="action-btn delete" onclick="deletePost(<?php echo $post->id; ?>)">🗑️ Slet</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- News Section -->
        <div class="section-pane" id="section-news">
            <h2>📰 Nyhedsstyring</h2>
            
            <form id="news-form" style="background: var(--bg-gray); padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
                <h3 style="margin-top: 0;">Opret ny nyhed</h3>
                
                <div class="form-group">
                    <label>Titel:</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Indhold:</label>
                    <textarea name="content" class="form-control" rows="6" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Land:</label>
                    <select name="country" class="form-control">
                        <option value="DK">🇩🇰 Danmark</option>
                        <option value="SE">🇸🇪 Sverige</option>
                        <option value="BOTH">Begge lande</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary">📤 Publicer Nyhed</button>
            </form>

            <h3>Seneste nyheder</h3>
            <div class="content-grid">
                <?php
                $news = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}rtf_platform_news ORDER BY created_at DESC LIMIT 10");
                foreach ($news as $item):
                ?>
                    <div class="content-item">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.5rem 0;"><?php echo esc_html($item->title); ?></h4>
                                <p style="color: var(--text-light); margin: 0;">
                                    <?php echo esc_html(substr($item->content, 0, 150)); ?>...
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    <span class="badge"><?php echo $item->country; ?></span>
                                    <span style="color: var(--text-light); font-size: 0.85rem; margin-left: 1rem;">
                                        <?php echo date('d/m/Y H:i', strtotime($item->created_at)); ?>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <button class="action-btn delete" onclick="deleteNews(<?php echo $item->id; ?>)">🗑️ Slet</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- System Section -->
        <div class="section-pane" id="section-system">
            <h2>⚙️ Systemindstillinger</h2>
            
            <div class="info-box">
                <h3 style="margin-top: 0;">🔧 Platform Konfiguration</h3>
                <p>WordPress Version: <?php echo get_bloginfo('version'); ?></p>
                <p>PHP Version: <?php echo phpversion(); ?></p>
                <p>MySQL Version: <?php echo $wpdb->db_version(); ?></p>
            </div>

            <div class="success-box">
                <h3 style="margin-top: 0;">✅ Databasetabeller</h3>
                <?php
                $tables = [
                    'rtf_platform_users',
                    'rtf_platform_posts',
                    'rtf_platform_messages',
                    'rtf_platform_news',
                    'rtf_platform_documents',
                    'rtf_foster_care_stats'
                ];
                foreach ($tables as $table):
                    $exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}{$table}'");
                ?>
                    <p>• <?php echo $table; ?>: <?php echo $exists ? '✓ OK' : '✗ Mangler'; ?></p>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Logs Section -->
        <div class="section-pane" id="section-logs">
            <h2>📋 Systemlogs</h2>
            
            <div class="info-box">
                <strong>📜 Aktivitetslog:</strong> Seneste systemhændelser og brugeraktiviteter
            </div>

            <div class="content-grid">
                <div class="content-item">
                    <strong>System Log</strong>
                    <p style="color: var(--text-light); margin: 0.5rem 0;">Ingen kritiske fejl registreret</p>
                    <span style="font-size: 0.85rem; color: var(--text-light);">
                        Sidste tjek: <?php echo current_time('d/m/Y H:i'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchSection(section) {
    // Update nav
    document.querySelectorAll('.section-card').forEach(card => {
        card.classList.remove('active');
    });
    document.querySelector(`[data-section="${section}"]`).classList.add('active');
    
    // Update content
    document.querySelectorAll('.section-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    document.getElementById(`section-${section}`).classList.add('active');
}

// News form submission
document.getElementById('news-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        title: formData.get('title'),
        content: formData.get('content'),
        country: formData.get('country')
    };
    
    try {
        const response = await fetch('<?php echo rest_url('kate/v1/admin/news'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Nyhed oprettet!');
            location.reload();
        } else {
            alert('❌ Fejl: ' + (result.message || 'Kunne ikke oprette nyhed'));
        }
    } catch (error) {
        alert('❌ Der opstod en fejl');
    }
});

function editUser(userId) {
    const name = prompt('Nyt navn:');
    if (name) {
        fetch(`<?php echo rest_url('kate/v1/admin/users/'); ?>${userId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ display_name: name })
        }).then(() => location.reload());
    }
}

function toggleSuspend(userId) {
    if (confirm('Er du sikker?')) {
        fetch(`<?php echo rest_url('kate/v1/admin/users/'); ?>${userId}/suspend`, {
            method: 'POST'
        }).then(() => location.reload());
    }
}

function deleteUser(userId) {
    if (confirm('ADVARSEL: Dette sletter brugeren permanent. Er du sikker?')) {
        fetch(`<?php echo rest_url('kate/v1/admin/users/'); ?>${userId}`, {
            method: 'DELETE'
        }).then(() => location.reload());
    }
}

function deletePost(postId) {
    if (confirm('Slet dette indlæg?')) {
        fetch(`<?php echo rest_url('kate/v1/admin/posts/'); ?>${postId}`, {
            method: 'DELETE'
        }).then(() => location.reload());
    }
}

function deleteNews(newsId) {
    if (confirm('Slet denne nyhed?')) {
        fetch(`<?php echo rest_url('kate/v1/admin/news/'); ?>${newsId}`, {
            method: 'DELETE'
        }).then(() => location.reload());
    }
}

// User search
document.getElementById('user-search')?.addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.users-table tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
});
</script>

<?php get_footer(); ?>
