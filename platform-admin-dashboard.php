<?php
/**
 * Template Name: Platform - Admin Dashboard
 * Complete modern admin panel with full user management
 */

get_header();

// Check admin access
$current_user = rtf_get_current_user();
if (!$current_user || !rtf_is_admin_user()) {
    wp_redirect(home_url('/platform-login'));
    exit;
}

$lang = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : 'da';

// Translations
$translations = [
    'da' => [
        'title' => 'Komplet Admin Panel',
        'dashboard' => 'Dashboard',
        'users' => 'Bruger Styring',
        'content' => 'Indhold',
        'news' => 'Nyheder',
        'forum' => 'Forum',
        'posts' => 'Vægindlæg',
        'moderation' => 'Moderering',
        'create_user' => 'Opret Bruger',
        'create_news' => 'Opret Nyhed',
        'user_list' => 'Bruger Liste',
        'username' => 'Brugernavn',
        'email' => 'Email',
        'password' => 'Adgangskode',
        'full_name' => 'Fulde Navn',
        'phone' => 'Telefon',
        'birthday' => 'Fødselsdag',
        'subscription' => 'Abonnement',
        'status' => 'Status',
        'actions' => 'Handlinger',
        'edit' => 'Rediger',
        'delete' => 'Slet',
        'activate' => 'Aktiver',
        'deactivate' => 'Deaktiver',
        'search' => 'Søg brugere...',
        'total_users' => 'Totale Brugere',
        'active_subs' => 'Aktive Abonnementer',
        'total_posts' => 'Totale Indlæg',
        'total_news' => 'Totale Nyheder',
        'create' => 'Opret',
        'cancel' => 'Annuller',
        'save' => 'Gem',
        'stripe_id' => 'Stripe ID',
        'created' => 'Oprettet',
        'last_login' => 'Sidste Login',
        'refresh' => 'Opdater',
        'title_label' => 'Titel',
        'content_label' => 'Indhold',
        'publish' => 'Publicer',
        'author' => 'Forfatter',
        'date' => 'Dato',
        'view' => 'Vis',
        'approve' => 'Godkend',
        'reject' => 'Afvis'
    ]
];

$t = $translations[$lang];
?>

<style>
:root {
    --admin-primary: #2563eb;
    --admin-success: #10b981;
    --admin-danger: #ef4444;
    --admin-warning: #f59e0b;
    --admin-bg: #0f172a;
    --admin-card: #1e293b;
    --admin-border: #334155;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: var(--admin-bg) !important;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.admin-complete {
    background: var(--admin-bg);
    min-height: 100vh;
    padding: 20px;
    color: #e2e8f0;
}

.admin-header {
    background: var(--admin-card);
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    border: 1px solid var(--admin-border);
}

.admin-header h1 {
    margin: 0;
    color: var(--admin-primary);
    font-size: 2em;
}

.admin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--admin-card);
    padding: 25px;
    border-radius: 12px;
    border: 1px solid var(--admin-border);
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card h3 {
    margin: 0 0 10px 0;
    color: #94a3b8;
    font-size: 0.9em;
    text-transform: uppercase;
}

.stat-card .number {
    font-size: 2.5em;
    font-weight: bold;
    color: var(--admin-primary);
}

.admin-section {
    background: var(--admin-card);
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    border: 1px solid var(--admin-border);
}

.admin-section h2 {
    margin: 0 0 20px 0;
    color: var(--admin-primary);
    border-bottom: 2px solid var(--admin-border);
    padding-bottom: 10px;
}

.admin-toolbar {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.admin-toolbar input,
.admin-toolbar select {
    flex: 1;
    min-width: 200px;
    padding: 12px 15px;
    background: var(--admin-bg);
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    color: #e2e8f0;
    font-size: 1em;
}

.admin-toolbar button {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-primary {
    background: var(--admin-primary);
    color: white;
}

.btn-success {
    background: var(--admin-success);
    color: white;
}

.btn-danger {
    background: var(--admin-danger);
    color: white;
}

.btn-primary:hover { background: #1d4ed8; }
.btn-success:hover { background: #059669; }
.btn-danger:hover { background: #dc2626; }

.user-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.user-table th,
.user-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid var(--admin-border);
}

.user-table th {
    background: var(--admin-bg);
    color: #94a3b8;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85em;
}

.user-table tr:hover {
    background: rgba(37, 99, 235, 0.1);
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
}

.badge-active { background: #064e3b; color: #10b981; }
.badge-inactive { background: #7f1d1d; color: #ef4444; }
.badge-expired { background: #78350f; color: #f59e0b; }

.action-btns {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.action-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.85em;
    transition: all 0.2s;
}

.action-btn:hover {
    transform: scale(1.05);
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: var(--admin-card);
    padding: 30px;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-content h2 {
    margin: 0 0 20px 0;
    color: var(--admin-primary);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #cbd5e1;
    font-weight: 600;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px;
    background: var(--admin-bg);
    border: 1px solid var(--admin-border);
    border-radius: 8px;
    color: #e2e8f0;
    font-size: 1em;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 30px;
}

.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.pagination {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 20px;
    flex-wrap: wrap;
}

.pagination button {
    padding: 8px 16px;
    background: var(--admin-bg);
    border: 1px solid var(--admin-border);
    border-radius: 6px;
    color: #e2e8f0;
    cursor: pointer;
    transition: all 0.2s;
}

.pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination button.active {
    background: var(--admin-primary);
}

.pagination button:not(:disabled):hover {
    background: var(--admin-primary);
}
</style>

<div class="admin-complete">
    
    <!-- Header -->
    <div class="admin-header">
        <h1>🎛️ <?php echo $t['title']; ?></h1>
        <p style="margin: 10px 0 0 0; color: #94a3b8;">Complete user management and system control</p>
    </div>

    <!-- Stats -->
    <div class="admin-stats">
        <div class="stat-card">
            <h3><?php echo $t['total_users']; ?></h3>
            <div class="number" id="stat-total-users">-</div>
        </div>
        <div class="stat-card">
            <h3><?php echo $t['active_subs']; ?></h3>
            <div class="number" id="stat-active-subs" style="color: var(--admin-success);">-</div>
        </div>
        <div class="stat-card">
            <h3><?php echo $t['total_posts']; ?></h3>
            <div class="number" id="stat-posts" style="color: var(--admin-warning);">-</div>
        </div>
        <div class="stat-card">
            <h3><?php echo $t['total_news']; ?></h3>
            <div class="number" id="stat-news" style="color: #8b5cf6;">-</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 10px; margin-bottom: 30px; flex-wrap: wrap;">
        <button class="tab-button active" onclick="switchTab('users')" style="padding: 12px 24px; background: var(--admin-primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
            👥 <?php echo $t['users']; ?>
        </button>
        <button class="tab-button" onclick="switchTab('news')" style="padding: 12px 24px; background: var(--admin-card); color: #e2e8f0; border: 1px solid var(--admin-border); border-radius: 8px; cursor: pointer; font-weight: 600;">
            📰 <?php echo $t['news']; ?>
        </button>
        <button class="tab-button" onclick="switchTab('posts')" style="padding: 12px 24px; background: var(--admin-card); color: #e2e8f0; border: 1px solid var(--admin-border); border-radius: 8px; cursor: pointer; font-weight: 600;">
            📝 <?php echo $t['posts']; ?>
        </button>
        <button class="tab-button" onclick="switchTab('forum')" style="padding: 12px 24px; background: var(--admin-card); color: #e2e8f0; border: 1px solid var(--admin-border); border-radius: 8px; cursor: pointer; font-weight: 600;">
            💬 <?php echo $t['forum']; ?>
        </button>
    </div>

    <!-- User Management Tab -->
    <div class="admin-section" id="tab-users">
        <h2><?php echo $t['users']; ?></h2>
        
        <div class="admin-toolbar">
            <input type="text" id="searchInput" placeholder="<?php echo $t['search']; ?>">
            <select id="statusFilter">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="expired">Expired</option>
            </select>
            <button class="btn-success" onclick="openCreateModal()">
                ➕ <?php echo $t['create_user']; ?>
            </button>
            <button class="btn-primary" onclick="refreshUsers()">
                🔄 <?php echo $t['refresh']; ?>
            </button>
        </div>

        <div id="users-container">
            <p style="text-align: center; color: #64748b;">Loading users...</p>
        </div>

        <div class="pagination" id="pagination"></div>
    </div>

    <!-- News Management Tab -->
    <div class="admin-section" id="tab-news" style="display: none;">
        <h2>📰 <?php echo $t['news']; ?></h2>
        
        <div class="admin-toolbar">
            <input type="text" id="searchNews" placeholder="Søg nyheder...">
            <button class="btn-success" onclick="openNewsModal()">
                ➕ <?php echo $t['create_news']; ?>
            </button>
            <button class="btn-primary" onclick="loadNews()">
                🔄 <?php echo $t['refresh']; ?>
            </button>
        </div>

        <div id="news-container">
            <p style="text-align: center; color: #64748b;">Loading news...</p>
        </div>
    </div>

    <!-- Posts Management Tab -->
    <div class="admin-section" id="tab-posts" style="display: none;">
        <h2>📝 <?php echo $t['posts']; ?> (Vægindlæg)</h2>
        
        <div class="admin-toolbar">
            <input type="text" id="searchPosts" placeholder="Søg indlæg...">
            <select id="postStatusFilter">
                <option value="all">Alle</option>
                <option value="approved">Godkendte</option>
                <option value="pending">Afventende</option>
            </select>
            <button class="btn-primary" onclick="loadPosts()">
                🔄 <?php echo $t['refresh']; ?>
            </button>
        </div>

        <div id="posts-container">
            <p style="text-align: center; color: #64748b;">Loading posts...</p>
        </div>
    </div>

    <!-- Forum Management Tab -->
    <div class="admin-section" id="tab-forum" style="display: none;">
        <h2>💬 <?php echo $t['forum']; ?></h2>
        
        <div class="admin-toolbar">
            <input type="text" id="searchForum" placeholder="Søg forum emner...">
            <button class="btn-primary" onclick="loadForum()">
                🔄 <?php echo $t['refresh']; ?>
            </button>
        </div>

        <div id="forum-container">
            <p style="text-align: center; color: #64748b;">Loading forum topics...</p>
        </div>
    </div>

</div>

<!-- News Modal -->
<div id="newsModal" class="modal">
    <div class="modal-content">
        <h2 id="newsModalTitle"><?php echo $t['create_news']; ?></h2>
        
        <form id="newsForm" onsubmit="return false;">
            <input type="hidden" id="newsId" value="">
            
            <div class="form-group">
                <label><?php echo $t['title_label']; ?> *</label>
                <input type="text" id="news_title" required>
            </div>
            
            <div class="form-group">
                <label><?php echo $t['content_label']; ?> *</label>
                <textarea id="news_content" rows="10" style="width: 100%; padding: 12px; background: var(--admin-bg); border: 1px solid var(--admin-border); border-radius: 8px; color: #e2e8f0; font-size: 1em; font-family: inherit;" required></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-danger" onclick="closeNewsModal()"><?php echo $t['cancel']; ?></button>
                <button type="button" class="btn-success" onclick="saveNews()"><?php echo $t['publish']; ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Create/Edit User Modal -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <h2 id="modalTitle"><?php echo $t['create_user']; ?></h2>
        
        <form id="userForm" onsubmit="return false;">
            <input type="hidden" id="userId" value="">
            
            <div class="form-group">
                <label><?php echo $t['username']; ?> *</label>
                <input type="text" id="username" required>
            </div>
            
            <div class="form-group">
                <label><?php echo $t['email']; ?> *</label>
                <input type="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label><?php echo $t['password']; ?> *</label>
                <input type="password" id="password" required>
            </div>
            
            <div class="form-group">
                <label><?php echo $t['full_name']; ?> *</label>
                <input type="text" id="full_name" required>
            </div>
            
            <div class="form-group">
                <label><?php echo $t['phone']; ?></label>
                <input type="tel" id="phone">
            </div>
            
            <div class="form-group">
                <label><?php echo $t['birthday']; ?></label>
                <input type="date" id="birthday">
            </div>
            
            <div class="form-group">
                <label><?php echo $t['subscription']; ?></label>
                <select id="subscription_status">
                    <option value="inactive">Inactive</option>
                    <option value="active">Active</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-danger" onclick="closeModal()"><?php echo $t['cancel']; ?></button>
                <button type="button" class="btn-success" onclick="saveUser()"><?php echo $t['save']; ?></button>
            </div>
        </form>
    </div>
</div>

<script>
const t = <?php echo json_encode($t); ?>;
let currentPage = 0;
let totalUsers = 0;
const limit = 20;

document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadUsers();
    
    // Search with debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 0;
            loadUsers();
        }, 500);
    });
    
    // Status filter
    document.getElementById('statusFilter').addEventListener('change', function() {
        currentPage = 0;
        loadUsers();
    });
});

async function loadStats() {
    try {
        // Load user stats
        const response = await fetch('/wp-json/kate/v1/admin/users?limit=10000', {
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (data.success && data.users) {
            const users = data.users;
            document.getElementById('stat-total-users').textContent = users.length;
            
            const active = users.filter(u => u.subscription_status === 'active').length;
            document.getElementById('stat-active-subs').textContent = active;
        }
        
        // Load posts count
        const postsResp = await fetch('/wp-json/wp/v2/posts?per_page=1', {
            credentials: 'same-origin'
        });
        const postsTotal = postsResp.headers.get('X-WP-Total');
        document.getElementById('stat-posts').textContent = postsTotal || '0';
        
        // Load news count from custom table
        const newsResp = await fetch('/wp-json/wp/v2/pages?per_page=1&parent=0', {
            credentials: 'same-origin'
        });
        const newsTotal = newsResp.headers.get('X-WP-Total');
        document.getElementById('stat-news').textContent = newsTotal || '0';
        
    } catch (error) {
        console.error('Stats error:', error);
    }
}

async function loadUsers() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const offset = currentPage * limit;
    
    const url = `/wp-json/kate/v1/admin/users?limit=${limit}&offset=${offset}&search=${encodeURIComponent(search)}&status=${status}`;
    
    try {
        const response = await fetch(url, { credentials: 'same-origin' });
        const data = await response.json();
        
        if (data.success && data.users) {
            totalUsers = data.total;
            displayUsers(data.users);
            updatePagination();
            loadStats(); // Refresh stats
        } else {
            document.getElementById('users-container').innerHTML = 
                `<p style="text-align: center; color: #ef4444;">Error loading users</p>`;
        }
    } catch (error) {
        console.error('Load users error:', error);
        document.getElementById('users-container').innerHTML = 
            `<p style="text-align: center; color: #ef4444;">Error: ${error.message}</p>`;
    }
}

function displayUsers(users) {
    if (users.length === 0) {
        document.getElementById('users-container').innerHTML = 
            `<p style="text-align: center; color: #64748b;">No users found</p>`;
        return;
    }
    
    let html = '<table class="user-table"><thead><tr>';
    html += '<th>ID</th>';
    html += '<th>' + t.username + '</th>';
    html += '<th>' + t.email + '</th>';
    html += '<th>Stripe ID</th>';
    html += '<th>' + t.subscription + '</th>';
    html += '<th>' + t.created + '</th>';
    html += '<th>' + t.actions + '</th>';
    html += '</tr></thead><tbody>';
    
    users.forEach(user => {
        const subClass = user.subscription_status === 'active' ? 'badge-active' : 
                        user.subscription_status === 'expired' ? 'badge-expired' : 'badge-inactive';
        
        const stripeId = user.stripe_customer_id ? 
            `<small style="color: #10b981;">✓ ${user.stripe_customer_id.substring(0, 12)}...</small>` : 
            '<small style="color: #64748b;">-</small>';
        
        const created = new Date(user.created_at).toLocaleDateString('da-DK');
        
        html += `<tr id="user-row-${user.id}">`;
        html += `<td><strong>${user.id}</strong></td>`;
        html += `<td>${user.username}</td>`;
        html += `<td>${user.email}</td>`;
        html += `<td>${stripeId}</td>`;
        html += `<td><span class="badge ${subClass}">${user.subscription_status}</span></td>`;
        html += `<td>${created}</td>`;
        html += `<td><div class="action-btns">`;
        
        if (user.subscription_status !== 'active') {
            html += `<button class="action-btn btn-success" onclick="activateSubscription(${user.id})">💎 Activate</button>`;
        }
        
        html += `<button class="action-btn btn-danger" onclick="deleteUser(${user.id}, '${user.username}')">🗑️ Delete</button>`;
        html += `</div></td>`;
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    document.getElementById('users-container').innerHTML = html;
}

function updatePagination() {
    const totalPages = Math.ceil(totalUsers / limit);
    if (totalPages <= 1) {
        document.getElementById('pagination').innerHTML = '';
        return;
    }
    
    let html = '';
    
    html += `<button onclick="changePage(${currentPage - 1})" ${currentPage === 0 ? 'disabled' : ''}>← Previous</button>`;
    
    for (let i = 0; i < Math.min(totalPages, 10); i++) {
        html += `<button class="${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i + 1}</button>`;
    }
    
    html += `<button onclick="changePage(${currentPage + 1})" ${currentPage >= totalPages - 1 ? 'disabled' : ''}>Next →</button>`;
    
    document.getElementById('pagination').innerHTML = html;
}

function changePage(page) {
    if (page < 0) return;
    currentPage = page;
    loadUsers();
}

function refreshUsers() {
    loadUsers();
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = t.create_user;
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('userModal').classList.add('show');
}

function closeModal() {
    document.getElementById('userModal').classList.remove('show');
}

async function saveUser() {
    const userId = document.getElementById('userId').value;
    const isEdit = !!userId;
    
    const userData = {
        username: document.getElementById('username').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        full_name: document.getElementById('full_name').value,
        phone: document.getElementById('phone').value,
        birthday: document.getElementById('birthday').value || '2000-01-01',
        subscription_status: document.getElementById('subscription_status').value
    };
    
    if (!userData.username || !userData.email || !userData.password || !userData.full_name) {
        alert('Udfyld venligst alle påkrævede felter');
        return;
    }
    
    // Create user via registration system
    try {
        const formData = new FormData();
        formData.append('action', 'register');
        formData.append('_wpnonce', '<?php echo wp_create_nonce("rtf_register"); ?>');
        Object.keys(userData).forEach(key => formData.append(key, userData[key]));
        
        const response = await fetch('/platform-auth', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            alert('✓ Bruger oprettet!');
            closeModal();
            loadUsers();
        } else {
            const text = await response.text();
            alert('✗ Fejl: ' + text);
        }
    } catch (error) {
        console.error('Save user error:', error);
        alert('Fejl: ' + error.message);
    }
}

async function activateSubscription(userId) {
    const days = prompt('Hvor mange dage skal abonnementet aktiveres?', '30');
    if (!days) return;
    
    try {
        const response = await fetch(`/wp-json/kate/v1/admin/subscription/${userId}`, {
            method: 'PUT',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                status: 'active',
                days_valid: parseInt(days)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✓ Abonnement aktiveret!');
            loadUsers();
        } else {
            alert('✗ Fejl: ' + (data.message || 'Ukendt fejl'));
        }
    } catch (error) {
        console.error('Activate error:', error);
        alert('Fejl: ' + error.message);
    }
}

async function deleteUser(userId, username) {
    if (!confirm(`🗑️ SLET bruger "${username}"?\n\nDette vil permanent fjerne:\n• Bruger konto\n• Alle opslag og beskeder\n• Al forum aktivitet\n• Alle forbindelser\n\nDette kan IKKE fortrydes!`)) {
        return;
    }
    
    const row = document.getElementById(`user-row-${userId}`);
    if (row) {
        row.style.backgroundColor = '#7f1d1d';
        row.style.opacity = '0.5';
    }
    
    try {
        const response = await fetch(`/wp-json/kate/v1/admin/user/${userId}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Remove from DOM
            if (row) {
                row.style.transition = 'all 0.3s';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            }
            
            // Reload after animation
            setTimeout(() => {
                alert('✓ Bruger slettet permanent!');
                loadUsers();
            }, 500);
        } else {
            alert('✗ Fejl: ' + (data.message || 'Kunne ikke slette bruger'));
            if (row) {
                row.style.backgroundColor = '';
                row.style.opacity = '1';
            }
        }
    } catch (error) {
        console.error('Delete error:', error);
        alert('✗ Fejl: ' + error.message);
        if (row) {
            row.style.backgroundColor = '';
            row.style.opacity = '1';
        }
    }
}

// Tab switching
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.admin-section').forEach(section => {
        section.style.display = 'none';
    });
    
    // Remove active class from buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.style.background = 'var(--admin-card)';
        btn.style.color = '#e2e8f0';
        btn.style.border = '1px solid var(--admin-border)';
        btn.classList.remove('active');
    });
    
    // Show selected tab
    const tab = document.getElementById('tab-' + tabName);
    if (tab) {
        tab.style.display = 'block';
    }
    
    // Mark button as active
    if (event && event.target) {
        event.target.style.background = 'var(--admin-primary)';
        event.target.style.color = 'white';
        event.target.style.border = 'none';
        event.target.classList.add('active');
    }
    
    // Load content for tab
    if (tabName === 'users') {
        loadUsers();
    } else if (tabName === 'news') {
        loadNews();
    } else if (tabName === 'posts') {
        loadPosts();
    } else if (tabName === 'forum') {
        loadForum();
    }
}

// News Management
async function loadNews() {
    try {
        const response = await fetch('/wp-json/wp/v2/posts?per_page=20&orderby=date&order=desc', {
            credentials: 'same-origin'
        });
        
        const news = await response.json();
        
        if (news && news.length > 0) {
            let html = '<table class="user-table"><thead><tr>';
            html += '<th>ID</th><th>' + t.title_label + '</th><th>' + t.author + '</th><th>' + t.date + '</th><th>' + t.actions + '</th>';
            html += '</tr></thead><tbody>';
            
            news.forEach(item => {
                const date = new Date(item.date).toLocaleDateString('da-DK');
                html += '<tr>';
                html += `<td>${item.id}</td>`;
                html += `<td>${item.title.rendered}</td>`;
                html += `<td>Admin</td>`;
                html += `<td>${date}</td>`;
                html += `<td><div class="action-btns">`;
                html += `<button class="action-btn btn-primary" onclick="editNews(${item.id})">✏️ ${t.edit}</button>`;
                html += `<button class="action-btn btn-danger" onclick="deleteNews(${item.id}, '${item.title.rendered}')">🗑️ ${t.delete}</button>`;
                html += `</div></td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            document.getElementById('news-container').innerHTML = html;
        } else {
            document.getElementById('news-container').innerHTML = '<p style="text-align: center; color: #64748b;">Ingen nyheder fundet</p>';
        }
    } catch (error) {
        console.error('Load news error:', error);
        document.getElementById('news-container').innerHTML = '<p style="text-align: center; color: #ef4444;">Fejl: ' + error.message + '</p>';
    }
}

function openNewsModal() {
    document.getElementById('newsModalTitle').textContent = t.create_news;
    document.getElementById('newsForm').reset();
    document.getElementById('newsId').value = '';
    document.getElementById('newsModal').classList.add('show');
}

function closeNewsModal() {
    document.getElementById('newsModal').classList.remove('show');
}

async function saveNews() {
    const title = document.getElementById('news_title').value;
    const content = document.getElementById('news_content').value;
    
    if (!title || !content) {
        alert('Udfyld titel og indhold');
        return;
    }
    
    try {
        const response = await fetch('/wp-json/wp/v2/posts', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
            },
            body: JSON.stringify({
                title: title,
                content: content,
                status: 'publish'
            })
        });
        
        if (response.ok) {
            alert('✓ Nyhed oprettet!');
            closeNewsModal();
            loadNews();
            loadStats();
        } else {
            alert('✗ Fejl ved oprettelse');
        }
    } catch (error) {
        console.error('Save news error:', error);
        alert('Fejl: ' + error.message);
    }
}

async function deleteNews(newsId, title) {
    if (!confirm(`Slet nyhed "${title}"?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/wp-json/wp/v2/posts/${newsId}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
            }
        });
        
        if (response.ok) {
            alert('✓ Nyhed slettet!');
            loadNews();
            loadStats();
        } else {
            alert('✗ Fejl ved sletning');
        }
    } catch (error) {
        console.error('Delete news error:', error);
        alert('Fejl: ' + error.message);
    }
}

// Posts Management
async function loadPosts() {
    try {
        const search = document.getElementById('searchPosts').value;
        const response = await fetch(`/wp-json/kate/v1/admin/posts?search=${encodeURIComponent(search)}&limit=50`, {
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error('Failed to load posts');
        }
        
        const data = await response.json();
        
        if (data.success && data.posts) {
            let html = '<table class="user-table"><thead><tr>';
            html += '<th>ID</th><th>' + t.user + '</th><th>' + t.content + '</th><th>' + t.date + '</th><th>' + t.actions + '</th>';
            html += '</tr></thead><tbody>';
            
            data.posts.forEach(post => {
                const date = new Date(post.created_at).toLocaleDateString('da-DK');
                const preview = post.content.length > 100 ? post.content.substring(0, 100) + '...' : post.content;
                html += `<tr id="post-row-${post.id}">`;
                html += `<td>${post.id}</td>`;
                html += `<td>${post.username || post.full_name || 'Ukendt'}</td>`;
                html += `<td>${preview}</td>`;
                html += `<td>${date}</td>`;
                html += `<td><div class="action-btns">`;
                html += `<button class="action-btn btn-danger" onclick="deletePost(${post.id})">🗑️ ${t.delete}</button>`;
                html += `</div></td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            
            if (data.total > 0) {
                html += `<div style="text-align: center; margin-top: 20px; color: #64748b;">Total: ${data.total} indlæg</div>`;
            }
            
            document.getElementById('posts-container').innerHTML = html;
        } else {
            document.getElementById('posts-container').innerHTML = '<p style="text-align: center; color: #64748b;">Ingen indlæg fundet</p>';
        }
    } catch (error) {
        console.error('Load posts error:', error);
        document.getElementById('posts-container').innerHTML = '<p style="text-align: center; color: #ef4444;">Fejl: ' + error.message + '</p>';
    }
}

// Delete post
async function deletePost(postId) {
    if (!confirm('Er du sikker på du vil slette dette indlæg?')) return;
    
    const row = document.getElementById(`post-row-${postId}`);
    if (row) row.style.opacity = '0.5';
    
    try {
        const response = await fetch(`/wp-json/kate/v1/admin/post/${postId}`, {
            method: 'DELETE',
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        if (data.success) {
            if (row) row.remove();
            alert('✓ Indlæg slettet!');
            loadPosts();
            loadStats();
        } else {
            alert('✗ Fejl: ' + data.message);
            if (row) row.style.opacity = '1';
        }
    } catch (error) {
        alert('✗ Fejl: ' + error.message);
        if (row) row.style.opacity = '1';
    }
}

// Forum Management
async function loadForum() {
    try {
        const search = document.getElementById('searchForum').value;
        const response = await fetch(`/wp-json/kate/v1/admin/forum?search=${encodeURIComponent(search)}&limit=50`, {
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error('Failed to load forum');
        }
        
        const data = await response.json();
        
        if (data.success && data.topics) {
            let html = '<table class="user-table"><thead><tr>';
            html += '<th>ID</th><th>' + t.user + '</th><th>Titel</th><th>' + t.content + '</th><th>' + t.date + '</th><th>' + t.actions + '</th>';
            html += '</tr></thead><tbody>';
            
            data.topics.forEach(topic => {
                const date = new Date(topic.created_at).toLocaleDateString('da-DK');
                const preview = topic.content.length > 100 ? topic.content.substring(0, 100) + '...' : topic.content;
                const title = topic.title.length > 50 ? topic.title.substring(0, 50) + '...' : topic.title;
                html += `<tr id="forum-row-${topic.id}">`;
                html += `<td>${topic.id}</td>`;
                html += `<td>${topic.username || topic.full_name || 'Ukendt'}</td>`;
                html += `<td><strong>${title}</strong></td>`;
                html += `<td>${preview}</td>`;
                html += `<td>${date}</td>`;
                html += `<td><div class="action-btns">`;
                html += `<button class="action-btn btn-danger" onclick="deleteForum(${topic.id})">🗑️ ${t.delete}</button>`;
                html += `</div></td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            
            if (data.total > 0) {
                html += `<div style="text-align: center; margin-top: 20px; color: #64748b;">Total: ${data.total} emner</div>`;
            }
            
            document.getElementById('forum-container').innerHTML = html;
        } else {
            document.getElementById('forum-container').innerHTML = '<p style="text-align: center; color: #64748b;">Ingen forum emner fundet</p>';
        }
    } catch (error) {
        console.error('Load forum error:', error);
        document.getElementById('forum-container').innerHTML = '<p style="text-align: center; color: #ef4444;">Fejl: ' + error.message + '</p>';
    }
}

// Delete forum topic
async function deleteForum(topicId) {
    if (!confirm('Er du sikker på du vil slette dette forum emne?')) return;
    
    const row = document.getElementById(`forum-row-${topicId}`);
    if (row) row.style.opacity = '0.5';
    
    try {
        const response = await fetch(`/wp-json/kate/v1/admin/forum/${topicId}`, {
            method: 'DELETE',
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        if (data.success) {
            if (row) row.remove();
            alert('✓ Forum emne slettet!');
            loadForum();
            loadStats();
        } else {
            alert('✗ Fejl: ' + data.message);
            if (row) row.style.opacity = '1';
        }
    } catch (error) {
        alert('✗ Fejl: ' + error.message);
        if (row) row.style.opacity = '1';
    }
}
</script>

<?php get_footer(); ?>
