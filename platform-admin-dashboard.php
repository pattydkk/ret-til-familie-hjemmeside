<?php
/**
 * Template Name: Platform Admin Dashboard
 * Description: Admin control panel with analytics, user management, and moderation
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['rtf_user_id'])) {
    header('Location: ' . home_url('/platform-login'));
    exit;
}

$user_id = intval($_SESSION['rtf_user_id']);

// Verify admin status
global $wpdb;
$user = $wpdb->get_row($wpdb->prepare(
    "SELECT is_admin FROM {$wpdb->prefix}platform_users WHERE id = %d",
    $user_id
));

if (!$user || !$user->is_admin) {
    header('Location: ' . home_url('/platform-profil'));
    exit;
}

// Get language preference
$lang = $wpdb->get_var($wpdb->prepare(
    "SELECT language_preference FROM {$wpdb->prefix}platform_users WHERE id = %d",
    $user_id
));
$lang = substr($lang, 0, 2); // da or sv

$translations = [
    'da' => [
        'admin_dashboard' => 'Admin Dashboard',
        'analytics' => 'Statistik',
        'total_users' => 'Brugere i alt',
        'active_users' => 'Aktive brugere (7 dage)',
        'active_subscriptions' => 'Aktive abonnementer',
        'total_posts' => 'Posts i alt',
        'total_messages' => 'Beskeder i alt',
        'kate_sessions' => 'Kate AI sessioner',
        'recent_registrations' => 'Nye registreringer (30 dage)',
        'language_breakdown' => 'Sprogfordeling',
        'country_breakdown' => 'Landefordeling',
        'quick_actions' => 'Hurtige handlinger',
        'manage_users' => 'Administrer brugere',
        'view_content' => 'Vis indhold',
        'moderate' => 'Moderer',
        'loading' => 'Indlæser...'
    ],
    'sv' => [
        'admin_dashboard' => 'Admin Dashboard',
        'analytics' => 'Statistik',
        'total_users' => 'Användare totalt',
        'active_users' => 'Aktiva användare (7 dagar)',
        'active_subscriptions' => 'Aktiva prenumerationer',
        'total_posts' => 'Inlägg totalt',
        'total_messages' => 'Meddelanden totalt',
        'kate_sessions' => 'Kate AI sessioner',
        'recent_registrations' => 'Nya registreringar (30 dagar)',
        'language_breakdown' => 'Språkfördelning',
        'country_breakdown' => 'Landsfördelning',
        'quick_actions' => 'Snabbåtgärder',
        'manage_users' => 'Hantera användare',
        'view_content' => 'Visa innehåll',
        'moderate' => 'Moderera',
        'loading' => 'Laddar...'
    ]
];

$t = $translations[$lang];

get_header();
?>

<style>
.admin-dashboard {
    max-width: 1400px;
    margin: 50px auto;
    padding: 30px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.admin-header {
    margin-bottom: 40px;
    border-bottom: 3px solid #e3342f;
    padding-bottom: 20px;
}

.admin-header h1 {
    margin: 0;
    color: #2d3748;
    font-size: 2.5em;
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card.users { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-card.active { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stat-card.subscriptions { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stat-card.posts { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
.stat-card.messages { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
.stat-card.kate { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
.stat-card.registrations { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #2d3748; }

.stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 0.9em;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.stat-card .value {
    font-size: 3em;
    font-weight: bold;
    margin: 10px 0;
}

.stat-card .icon {
    font-size: 2.5em;
    opacity: 0.3;
    float: right;
}

.breakdown-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.breakdown-card {
    background: #f7fafc;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.breakdown-card h3 {
    margin: 0 0 20px 0;
    color: #2d3748;
    font-size: 1.2em;
}

.breakdown-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e2e8f0;
}

.breakdown-item:last-child {
    border-bottom: none;
}

.breakdown-item .label {
    font-weight: 600;
    color: #4a5568;
}

.breakdown-item .count {
    background: #667eea;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: bold;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 20px;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1.1em;
    font-weight: 600;
    color: #4a5568;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.action-btn:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.action-btn .icon {
    font-size: 1.5em;
}

.loading {
    text-align: center;
    padding: 60px 20px;
    color: #718096;
    font-size: 1.2em;
}

.loading-spinner {
    display: inline-block;
    width: 40px;
    height: 40px;
    border: 4px solid #e2e8f0;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .admin-dashboard {
        margin: 20px;
        padding: 20px;
    }
    
    .analytics-grid {
        grid-template-columns: 1fr;
    }
    
    .breakdown-section {
        grid-template-columns: 1fr;
    }
    
    .stat-card .value {
        font-size: 2.5em;
    }
}
</style>

<div class="admin-dashboard">
    <div class="admin-header">
        <h1>🛡️ <?php echo $t['admin_dashboard']; ?></h1>
    </div>
    
    <div id="analytics-container">
        <div class="loading">
            <div class="loading-spinner"></div>
            <p><?php echo $t['loading']; ?></p>
        </div>
    </div>
    
    <div class="quick-actions">
        <a href="<?php echo home_url('/platform-admin-users'); ?>" class="action-btn">
            <span class="icon">👥</span>
            <span><?php echo $t['manage_users']; ?></span>
        </a>
        <a href="<?php echo home_url('/platform-admin-moderation'); ?>" class="action-btn">
            <span class="icon">🛡️</span>
            <span><?php echo $t['moderate']; ?></span>
        </a>
        <a href="<?php echo home_url('/platform-vaeg'); ?>" class="action-btn">
            <span class="icon">📰</span>
            <span><?php echo $t['view_content']; ?></span>
        </a>
    </div>
</div>

<script>
// Load analytics on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAnalytics();
});

async function loadAnalytics() {
    try {
        const response = await fetch('/wp-json/kate/v1/admin/analytics', {
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.analytics) {
            displayAnalytics(data.analytics);
        } else {
            document.getElementById('analytics-container').innerHTML = 
                '<p style="text-align: center; color: #e3342f;">Kunne ikke indlæse statistik</p>';
        }
    } catch (error) {
        console.error('Analytics error:', error);
        document.getElementById('analytics-container').innerHTML = 
            '<p style="text-align: center; color: #e3342f;">Der opstod en fejl</p>';
    }
}

function displayAnalytics(analytics) {
    const lang = '<?php echo $lang; ?>';
    const t = <?php echo json_encode($t); ?>;
    
    let html = '<div class="analytics-grid">';
    
    // Stat cards
    html += `
        <div class="stat-card users">
            <span class="icon">👥</span>
            <h3>${t.total_users}</h3>
            <div class="value">${analytics.total_users || 0}</div>
        </div>
        
        <div class="stat-card active">
            <span class="icon">⚡</span>
            <h3>${t.active_users}</h3>
            <div class="value">${analytics.active_users || 0}</div>
        </div>
        
        <div class="stat-card subscriptions">
            <span class="icon">💎</span>
            <h3>${t.active_subscriptions}</h3>
            <div class="value">${analytics.active_subscriptions || 0}</div>
        </div>
        
        <div class="stat-card posts">
            <span class="icon">📝</span>
            <h3>${t.total_posts}</h3>
            <div class="value">${analytics.total_posts || 0}</div>
        </div>
        
        <div class="stat-card messages">
            <span class="icon">💬</span>
            <h3>${t.total_messages}</h3>
            <div class="value">${analytics.total_messages || 0}</div>
        </div>
        
        <div class="stat-card kate">
            <span class="icon">🤖</span>
            <h3>${t.kate_sessions}</h3>
            <div class="value">${analytics.kate_sessions || 0}</div>
        </div>
        
        <div class="stat-card registrations">
            <span class="icon">🆕</span>
            <h3>${t.recent_registrations}</h3>
            <div class="value">${analytics.recent_registrations || 0}</div>
        </div>
    `;
    
    html += '</div>';
    
    // Breakdown section
    html += '<div class="breakdown-section">';
    
    // Language breakdown
    html += `
        <div class="breakdown-card">
            <h3>🌍 ${t.language_breakdown}</h3>
    `;
    
    if (analytics.language_breakdown) {
        for (const [lang_code, count] of Object.entries(analytics.language_breakdown)) {
            const langName = lang_code === 'da_DK' ? 'Dansk' : 'Svenska';
            html += `
                <div class="breakdown-item">
                    <span class="label">${langName}</span>
                    <span class="count">${count}</span>
                </div>
            `;
        }
    }
    
    html += '</div>';
    
    // Country breakdown
    html += `
        <div class="breakdown-card">
            <h3>🗺️ ${t.country_breakdown}</h3>
    `;
    
    if (analytics.country_breakdown) {
        for (const [country, count] of Object.entries(analytics.country_breakdown)) {
            const countryName = country === 'DK' ? 'Danmark' : 'Sverige';
            const flag = country === 'DK' ? '🇩🇰' : '🇸🇪';
            html += `
                <div class="breakdown-item">
                    <span class="label">${flag} ${countryName}</span>
                    <span class="count">${count}</span>
                </div>
            `;
        }
    }
    
    html += '</div></div>';
    
    document.getElementById('analytics-container').innerHTML = html;
}
</script>

<?php get_footer(); ?>
