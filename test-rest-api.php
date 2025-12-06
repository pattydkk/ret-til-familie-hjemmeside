<?php
/**
 * Template Name: Test REST API
 * Debug page to test REST API endpoints
 */

get_header();

// Check admin access
$current_user = rtf_get_current_user();
if (!$current_user || !rtf_is_admin_user()) {
    wp_redirect(home_url('/platform-login'));
    exit;
}
?>

<style>
body { 
    background: #0f172a; 
    color: #e2e8f0; 
    font-family: 'Segoe UI', sans-serif;
    padding: 40px;
}
.test-container {
    max-width: 1000px;
    margin: 0 auto;
    background: #1e293b;
    padding: 30px;
    border-radius: 12px;
}
.test-section {
    background: #334155;
    padding: 20px;
    margin: 20px 0;
    border-radius: 8px;
}
.test-button {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    margin: 10px 5px;
}
.test-button:hover {
    background: #2563eb;
}
.result {
    background: #1e293b;
    padding: 15px;
    margin-top: 15px;
    border-radius: 6px;
    font-family: monospace;
    white-space: pre-wrap;
    max-height: 400px;
    overflow-y: auto;
}
.success { color: #10b981; }
.error { color: #ef4444; }
.info { color: #3b82f6; }
h1 { color: #f1f5f9; margin-bottom: 10px; }
h2 { color: #cbd5e1; margin-top: 0; }
.status { padding: 5px 10px; border-radius: 4px; display: inline-block; margin: 5px 0; }
.status-ok { background: #065f46; }
.status-error { background: #7f1d1d; }
</style>

<div class="test-container">
    <h1>🔧 REST API Test Center</h1>
    <p>Logged in as: <strong><?php echo $current_user->username; ?></strong> (Admin: <?php echo $current_user->is_admin ? 'Yes' : 'No'; ?>)</p>
    
    <div class="test-section">
        <h2>📡 Test API Endpoints</h2>
        
        <button class="test-button" onclick="testGetUsers()">Test GET /admin/users</button>
        <button class="test-button" onclick="testCreateUser()">Test POST /admin/user</button>
        <button class="test-button" onclick="testListEndpoints()">List All Endpoints</button>
        <button class="test-button" onclick="clearResults()">Clear Results</button>
        
        <div id="results"></div>
    </div>
    
    <div class="test-section">
        <h2>📋 System Information</h2>
        <div class="result">
<strong>Base URL:</strong> <?php echo home_url(); ?>

<strong>REST API Base:</strong> <?php echo rest_url(); ?>

<strong>Kate API Base:</strong> <?php echo home_url('/wp-json/kate/v1'); ?>

<strong>Admin Endpoint:</strong> <?php echo home_url('/wp-json/kate/v1/admin/user'); ?>

<strong>Current User ID:</strong> <?php echo $current_user->id; ?>

<strong>Session ID:</strong> <?php echo session_id() ?: 'Not started'; ?>

<strong>WP Nonce:</strong> <?php echo wp_create_nonce('wp_rest'); ?>

<strong>PHP Session Status:</strong> <?php 
    $status = session_status();
    echo $status === PHP_SESSION_ACTIVE ? 'Active' : ($status === PHP_SESSION_NONE ? 'None' : 'Disabled'); 
?>
        </div>
    </div>
</div>

<script>
const nonce = '<?php echo wp_create_nonce("wp_rest"); ?>';
const apiBase = '<?php echo home_url("/wp-json/kate/v1"); ?>';

function addResult(title, content, type = 'info') {
    const results = document.getElementById('results');
    const div = document.createElement('div');
    div.className = 'result';
    div.innerHTML = `<strong class="${type}">${title}</strong>\n${content}`;
    results.appendChild(div);
    results.scrollTop = results.scrollHeight;
}

function clearResults() {
    document.getElementById('results').innerHTML = '';
}

async function testGetUsers() {
    addResult('🔄 Testing GET /admin/users...', 'Sending request...');
    
    try {
        const response = await fetch(`${apiBase}/admin/users?limit=5`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-WP-Nonce': nonce
            }
        });
        
        addResult('📊 Response Status', `HTTP ${response.status} ${response.statusText}`, response.ok ? 'success' : 'error');
        
        const data = await response.json();
        addResult('📦 Response Data', JSON.stringify(data, null, 2), data.success ? 'success' : 'error');
        
    } catch (error) {
        addResult('❌ Error', error.message, 'error');
        console.error('Test error:', error);
    }
}

async function testCreateUser() {
    const timestamp = Date.now();
    const userData = {
        username: `testuser${timestamp}`,
        email: `test${timestamp}@example.com`,
        password: 'test123456',
        full_name: 'Test User',
        phone: '12345678',
        birthday: '1990-01-01',
        subscription_status: 'inactive',
        is_admin: 0
    };
    
    addResult('🔄 Testing POST /admin/user...', `Creating user: ${userData.username}`);
    
    try {
        const response = await fetch(`${apiBase}/admin/user`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': nonce
            },
            body: JSON.stringify(userData)
        });
        
        addResult('📊 Response Status', `HTTP ${response.status} ${response.statusText}`, response.ok ? 'success' : 'error');
        
        if (!response.ok) {
            const errorText = await response.text();
            addResult('❌ Error Response', errorText, 'error');
            return;
        }
        
        const data = await response.json();
        addResult('📦 Response Data', JSON.stringify(data, null, 2), data.success ? 'success' : 'error');
        
        if (data.success) {
            addResult('✅ SUCCESS', `User created: ${data.username} (ID: ${data.user_id})`, 'success');
        }
        
    } catch (error) {
        addResult('❌ Error', error.message, 'error');
        console.error('Test error:', error);
    }
}

async function testListEndpoints() {
    addResult('🔄 Listing WordPress REST API endpoints...', 'Fetching...');
    
    try {
        const response = await fetch('<?php echo rest_url(); ?>', {
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        // Filter for kate/v1 routes
        const kateRoutes = Object.keys(data.routes).filter(route => route.includes('/kate/v1'));
        
        addResult('📋 Kate API Routes', kateRoutes.join('\n'), 'info');
        
    } catch (error) {
        addResult('❌ Error', error.message, 'error');
    }
}

// Auto-run on load
window.addEventListener('load', () => {
    addResult('✨ Test Center Ready', 'Click buttons above to test API endpoints', 'success');
});
</script>

<?php get_footer(); ?>
