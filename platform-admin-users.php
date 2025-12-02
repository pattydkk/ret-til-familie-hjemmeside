<?php
/**
 * Template Name: Platform Admin Users
 * Description: User management interface for admins
 */

session_start();

// Check admin
if (!isset($_SESSION['rtf_user_id'])) {
    header('Location: ' . home_url('/platform-login'));
    exit;
}

$user_id = intval($_SESSION['rtf_user_id']);

global $wpdb;
$user = $wpdb->get_row($wpdb->prepare(
    "SELECT is_admin FROM {$wpdb->prefix}platform_users WHERE id = %d",
    $user_id
));

if (!$user || !$user->is_admin) {
    header('Location: ' . home_url('/platform-profil'));
    exit;
}

// Get language
$lang = $wpdb->get_var($wpdb->prepare(
    "SELECT language_preference FROM {$wpdb->prefix}platform_users WHERE id = %d",
    $user_id
));
$lang = substr($lang, 0, 2);

$t = [
    'da' => [
        'user_management' => 'Brugeradministration',
        'search' => 'Søg brugere...',
        'username' => 'Brugernavn',
        'email' => 'Email',
        'status' => 'Status',
        'subscription' => 'Abonnement',
        'actions' => 'Handlinger',
        'active' => 'Aktiv',
        'inactive' => 'Inaktiv',
        'edit' => 'Rediger',
        'delete' => 'Slet',
        'activate_sub' => 'Aktiver abonnement',
        'days' => 'Dage',
        'activate' => 'Aktiver',
        'loading' => 'Indlæser...',
        'no_users' => 'Ingen brugere fundet',
        'back_to_dashboard' => 'Tilbage til dashboard'
    ],
    'sv' => [
        'user_management' => 'Användarhantering',
        'search' => 'Sök användare...',
        'username' => 'Användarnamn',
        'email' => 'E-post',
        'status' => 'Status',
        'subscription' => 'Prenumeration',
        'actions' => 'Åtgärder',
        'active' => 'Aktiv',
        'inactive' => 'Inaktiv',
        'edit' => 'Redigera',
        'delete' => 'Radera',
        'activate_sub' => 'Aktivera prenumeration',
        'days' => 'Dagar',
        'activate' => 'Aktivera',
        'loading' => 'Laddar...',
        'no_users' => 'Inga användare hittades',
        'back_to_dashboard' => 'Tillbaka till dashboard'
    ]
][$lang];

get_header();
?>

<style>
.admin-users {
    max-width: 1400px;
    margin: 50px auto;
    padding: 30px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    border-bottom: 3px solid #667eea;
    padding-bottom: 20px;
}

.admin-header h1 {
    margin: 0;
    color: #2d3748;
}

.back-btn {
    padding: 12px 24px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
}

.back-btn:hover {
    background: #5a67d8;
    transform: translateY(-2px);
}

.search-bar {
    margin-bottom: 30px;
}

.search-bar input {
    width: 100%;
    padding: 15px 20px;
    font-size: 1.1em;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    transition: border-color 0.3s ease;
}

.search-bar input:focus {
    outline: none;
    border-color: #667eea;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
}

.users-table th {
    background: #f7fafc;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #2d3748;
    border-bottom: 2px solid #e2e8f0;
}

.users-table td {
    padding: 15px;
    border-bottom: 1px solid #e2e8f0;
}

.users-table tr:hover {
    background: #f7fafc;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.9em;
    font-weight: 600;
}

.status-badge.active {
    background: #c6f6d5;
    color: #22543d;
}

.status-badge.inactive {
    background: #fed7d7;
    color: #742a2a;
}

.sub-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.9em;
    font-weight: 600;
}

.sub-badge.active {
    background: #bee3f8;
    color: #2c5282;
}

.sub-badge.expired, .sub-badge.trial {
    background: #fefcbf;
    color: #744210;
}

.action-btns {
    display: flex;
    gap: 8px;
}

.action-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.action-btn.edit {
    background: #667eea;
    color: white;
}

.action-btn.edit:hover {
    background: #5a67d8;
}

.action-btn.delete {
    background: #e3342f;
    color: white;
}

.action-btn.delete:hover {
    background: #cc1f1a;
}

.action-btn.activate {
    background: #38c172;
    color: white;
}

.action-btn.activate:hover {
    background: #2f9e5f;
}

.loading {
    text-align: center;
    padding: 60px 20px;
    color: #718096;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 30px;
}

.page-btn {
    padding: 10px 20px;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.page-btn:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

.modal-content h3 {
    margin: 0 0 20px 0;
    color: #2d3748;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #4a5568;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1em;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.modal-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.modal-btn.cancel {
    background: #e2e8f0;
    color: #4a5568;
}

.modal-btn.submit {
    background: #667eea;
    color: white;
}

.modal-btn.submit:hover {
    background: #5a67d8;
}

@media (max-width: 768px) {
    .admin-users {
        margin: 20px;
        padding: 20px;
    }
    
    .users-table {
        font-size: 0.9em;
    }
    
    .admin-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
}
</style>

<div class="admin-users">
    <div class="admin-header">
        <h1>👥 <?php echo $t['user_management']; ?></h1>
        <a href="<?php echo home_url('/platform-admin-dashboard'); ?>" class="back-btn">
            ← <?php echo $t['back_to_dashboard']; ?>
        </a>
    </div>
    
    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="<?php echo $t['search']; ?>" />
    </div>
    
    <div id="users-container">
        <div class="loading">
            <div class="loading-spinner"></div>
            <p><?php echo $t['loading']; ?></p>
        </div>
    </div>
    
    <div class="pagination">
        <button class="page-btn" id="prevBtn" onclick="changePage(-1)">←</button>
        <span id="pageInfo" style="padding: 10px 20px; font-weight: 600;"></span>
        <button class="page-btn" id="nextBtn" onclick="changePage(1)">→</button>
    </div>
</div>

<!-- Subscription Modal -->
<div id="subModal" class="modal">
    <div class="modal-content">
        <h3><?php echo $t['activate_sub']; ?></h3>
        <div class="form-group">
            <label><?php echo $t['days']; ?>:</label>
            <input type="number" id="subDays" value="30" min="1" max="365" />
        </div>
        <div class="modal-actions">
            <button class="modal-btn cancel" onclick="closeSubModal()"><?php echo $t['back_to_dashboard']; ?></button>
            <button class="modal-btn submit" onclick="activateSubscription()"><?php echo $t['activate']; ?></button>
        </div>
    </div>
</div>

<script>
let currentPage = 0;
let totalUsers = 0;
let selectedUserId = null;
const limit = 50;
const t = <?php echo json_encode($t); ?>;

document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    
    // Search debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 0;
            loadUsers(this.value);
        }, 500);
    });
});

async function loadUsers(search = '') {
    try {
        const offset = currentPage * limit;
        const url = `/wp-json/kate/v1/admin/users?limit=${limit}&offset=${offset}&search=${encodeURIComponent(search)}`;
        
        const response = await fetch(url, {
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (data.success && data.users) {
            totalUsers = data.total;
            displayUsers(data.users);
            updatePagination();
        } else {
            document.getElementById('users-container').innerHTML = 
                `<p style="text-align: center;">${t.no_users}</p>`;
        }
    } catch (error) {
        console.error('Load users error:', error);
    }
}

function displayUsers(users) {
    if (users.length === 0) {
        document.getElementById('users-container').innerHTML = 
            `<p style="text-align: center;">${t.no_users}</p>`;
        return;
    }
    
    let html = '<table class="users-table"><thead><tr>';
    html += `<th>${t.username}</th>`;
    html += `<th>${t.email}</th>`;
    html += `<th>${t.status}</th>`;
    html += `<th>${t.subscription}</th>`;
    html += `<th>${t.actions}</th>`;
    html += '</tr></thead><tbody>';
    
    users.forEach(user => {
        const statusClass = user.is_active == 1 ? 'active' : 'inactive';
        const statusText = user.is_active == 1 ? t.active : t.inactive;
        
        html += '<tr>';
        html += `<td><strong>${user.username}</strong></td>`;
        html += `<td>${user.email}</td>`;
        html += `<td><span class="status-badge ${statusClass}">${statusText}</span></td>`;
        html += `<td><span class="sub-badge ${user.subscription_status}">${user.subscription_status}</span></td>`;
        html += `<td><div class="action-btns">`;
        html += `<button class="action-btn activate" onclick="openSubModal(${user.id})">💎 ${t.activate_sub}</button>`;
        html += `<button class="action-btn delete" onclick="deleteUser(${user.id})">🗑️ ${t.delete}</button>`;
        html += `</div></td>`;
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    document.getElementById('users-container').innerHTML = html;
}

function updatePagination() {
    const totalPages = Math.ceil(totalUsers / limit);
    document.getElementById('pageInfo').textContent = `${currentPage + 1} / ${totalPages}`;
    document.getElementById('prevBtn').disabled = currentPage === 0;
    document.getElementById('nextBtn').disabled = (currentPage + 1) >= totalPages;
}

function changePage(direction) {
    currentPage += direction;
    if (currentPage < 0) currentPage = 0;
    loadUsers(document.getElementById('searchInput').value);
}

function openSubModal(userId) {
    selectedUserId = userId;
    document.getElementById('subModal').classList.add('show');
}

function closeSubModal() {
    document.getElementById('subModal').classList.remove('show');
    selectedUserId = null;
}

async function activateSubscription() {
    const days = document.getElementById('subDays').value;
    
    try {
        const response = await fetch(`/wp-json/kate/v1/admin/subscription/${selectedUserId}`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ days: parseInt(days) })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Abonnement aktiveret!');
            closeSubModal();
            loadUsers();
        } else {
            alert('Fejl: ' + (data.message || 'Kunne ikke aktivere'));
        }
    } catch (error) {
        console.error('Activate subscription error:', error);
        alert('Der opstod en fejl');
    }
}

async function deleteUser(userId) {
    if (!confirm('Er du sikker på at du vil slette denne bruger?')) {
        return;
    }
    
    try {
        const response = await fetch(`/wp-json/kate/v1/admin/user/${userId}`, {
            method: 'DELETE',
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Bruger slettet');
            loadUsers();
        } else {
            alert('Fejl: Kunne ikke slette bruger');
        }
    } catch (error) {
        console.error('Delete user error:', error);
        alert('Der opstod en fejl');
    }
}
</script>

<?php get_footer(); ?>
