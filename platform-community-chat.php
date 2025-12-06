<?php
/**
 * Template Name: Platform - Community Chat
 * Multi-room chat with moderation
 */

get_header();
$lang = rtf_get_lang();

if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth/?lang=' . $lang));
    exit;
}

rtf_require_subscription();

$current_user = rtf_get_current_user();
$user_id = $current_user->id;
$language = $current_user->language ?? 'da';
$is_danish = ($language === 'da');
$is_admin = rtf_is_admin_user();
?>

<div class="platform-container" style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; max-width: 1600px; margin: 0 auto; padding: 2rem;">
    <?php get_template_part('template-parts/platform-sidebar'); ?>
    
    <div class="platform-content" style="min-width: 0;">
        <div class="rtf-card community-chat-container">
            <div class="chat-layout">
                <!-- Room selector sidebar -->
                <div class="room-sidebar">
                    <div class="room-header">
                        <h2><?php echo $is_danish ? '💬 Chat Rum' : '💬 Chattrum'; ?></h2>
                    </div>
                    
                    <!-- Støttechatten -->
                    <div class="room-category">
                        <h4>🆘 <?php echo $is_danish ? 'Støttechat' : 'Stödchat'; ?></h4>
                        <button class="room-btn" data-room="support" data-type="support">
                            <div class="room-info">
                                <span class="room-name"><?php echo $is_danish ? 'Støttechatten' : 'Stödchatten'; ?></span>
                                <span class="room-desc"><?php echo $is_danish ? 'Admins + alle brugere' : 'Admins + alla användare'; ?></span>
                            </div>
                            <span class="unread-badge" id="unread-support" style="display: none;">0</span>
                        </button>
                    </div>

                    <!-- Sagstype rum -->
                    <div class="room-category">
                        <h4>📋 <?php echo $is_danish ? 'Sagstype' : 'Ärendetyp'; ?></h4>
                        <button class="room-btn" data-room="anbringelse" data-type="casetype">
                            <div class="room-info">
                                <span class="room-name"><?php echo $is_danish ? 'Anbringelsessager' : 'Placeringsärenden'; ?></span>
                                <span class="room-desc"><?php echo $is_danish ? 'Om tvangsanbringelse' : 'Om tvångsplacering'; ?></span>
                            </div>
                            <span class="unread-badge" id="unread-anbringelse" style="display: none;">0</span>
                        </button>
                        <button class="room-btn" data-room="samvaer" data-type="casetype">
                            <div class="room-info">
                                <span class="room-name"><?php echo $is_danish ? 'Samværssager' : 'Umgängesärenden'; ?></span>
                                <span class="room-desc"><?php echo $is_danish ? 'Om samvær med børn' : 'Om umgänge med barn'; ?></span>
                            </div>
                            <span class="unread-badge" id="unread-samvaer" style="display: none;">0</span>
                        </button>
                        <button class="room-btn" data-room="forældremyndighed" data-type="casetype">
                            <div class="room-info">
                                <span class="room-name"><?php echo $is_danish ? 'Forældremyndighed' : 'Vårdnad'; ?></span>
                                <span class="room-desc"><?php echo $is_danish ? 'Om forældremyndighed' : 'Om vårdnadsfrågor'; ?></span>
                            </div>
                            <span class="unread-badge" id="unread-forældremyndighed" style="display: none;">0</span>
                        </button>
                        <button class="room-btn" data-room="aktindsigt" data-type="casetype">
                            <div class="room-info">
                                <span class="room-name"><?php echo $is_danish ? 'Aktindsigt & Journaler' : 'Aktinsyn & Journaler'; ?></span>
                                <span class="room-desc"><?php echo $is_danish ? 'Om aktindsigt' : 'Om aktinsyn'; ?></span>
                            </div>
                            <span class="unread-badge" id="unread-aktindsigt" style="display: none;">0</span>
                        </button>
                    </div>

                    <!-- Landsdel rum -->
                    <div class="room-category">
                        <h4>🌍 <?php echo $is_danish ? 'Landsdel' : 'Region'; ?></h4>
                        <button class="room-btn" data-room="region-hovedstaden" data-type="region">
                            <div class="room-info">
                                <span class="room-name">Region Hovedstaden</span>
                                <span class="room-desc"><?php echo $is_danish ? 'København & omegn' : 'Köpenhamn & omnejd'; ?></span>
                            </div>
                            <span class="unread-badge" id="unread-region-hovedstaden" style="display: none;">0</span>
                        </button>
                        <button class="room-btn" data-room="region-sjaelland" data-type="region">
                            <div class="room-info">
                                <span class="room-name">Region Sjælland</span>
                            </div>
                            <span class="unread-badge" id="unread-region-sjaelland" style="display: none;">0</span>
                        </button>
                        <button class="room-btn" data-room="region-syddanmark" data-type="region">
                            <div class="room-info">
                                <span class="room-name">Region Syddanmark</span>
                            </div>
                            <span class="unread-badge" id="unread-region-syddanmark" style="display: none;">0</span>
                        </button>
                        <button class="room-btn" data-room="region-midtjylland" data-type="region">
                            <div class="room-info">
                                <span class="room-name">Region Midtjylland</span>
                            </div>
                            <span class="unread-badge" id="unread-region-midtjylland" style="display: none;">0</span>
                        </button>
                        <button class="room-btn" data-room="region-nordjylland" data-type="region">
                            <div class="room-info">
                                <span class="room-name">Region Nordjylland</span>
                            </div>
                            <span class="unread-badge" id="unread-region-nordjylland" style="display: none;">0</span>
                        </button>
                        <button class="room-btn" data-room="sweden" data-type="region">
                            <div class="room-info">
                                <span class="room-name">🇸🇪 Sverige</span>
                                <span class="room-desc">För svenska användare</span>
                            </div>
                            <span class="unread-badge" id="unread-sweden" style="display: none;">0</span>
                        </button>
                    </div>

                    <!-- Online users -->
                    <div class="online-users">
                        <h4>🟢 <?php echo $is_danish ? 'Online nu' : 'Online nu'; ?> (<span id="onlineCount">0</span>)</h4>
                        <div id="onlineUsersList"></div>
                    </div>
                </div>
                
                <!-- Main chat area -->
                <div class="chat-main-area">
                    <div id="chatWelcome" class="chat-welcome">
                        <div class="welcome-content">
                            <i class="fas fa-comments" style="font-size: 4em; color: var(--rtf-accent); margin-bottom: 20px;"></i>
                            <h2><?php echo $is_danish ? 'Velkommen til Community Chatten' : 'Välkommen till Community-chatten'; ?></h2>
                            <p><?php echo $is_danish ? 
                                'Vælg et chatrum fra listen for at komme i gang. Du kan chatte med andre brugere om dine erfaringer, få støtte eller dele juridisk viden.' : 
                                'Välj ett chattrum från listan för att komma igång. Du kan chatta med andra användare om dina erfaringar, få stöd eller dela juridisk kunskap.'; ?></p>
                            <div class="chat-rules" style="margin-top: 30px; padding: 20px; background: var(--rtf-bg); border-radius: 12px; text-align: left; max-width: 600px;">
                                <h3 style="margin-top: 0;">⚠️ <?php echo $is_danish ? 'Chatregler' : 'Chattregler'; ?></h3>
                                <ul style="margin: 10px 0;">
                                    <li><?php echo $is_danish ? 'Vær respektfuld og venlig' : 'Var respektfull och vänlig'; ?></li>
                                    <li><?php echo $is_danish ? 'Ingen trusler eller krænkende sprog' : 'Inga hot eller kränkande språk'; ?></li>
                                    <li><?php echo $is_danish ? 'Del ikke personlige CPR-numre eller følsomme data' : 'Dela inte personliga personnummer eller känslig data'; ?></li>
                                    <li><?php echo $is_danish ? 'Chatmoderering er aktiveret - grove overtrædelser resulterer i advarsel/blokering' : 'Chattmoderering är aktiverad - grova överträdelser resulterar i varning/blockering'; ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div id="chatRoom" class="chat-room" style="display: none;">
                        <div class="chat-room-header">
                            <div class="room-title-area">
                                <h3 id="currentRoomName"></h3>
                                <span id="currentRoomDesc" class="room-description"></span>
                            </div>
                            <div class="chat-header-actions">
                                <button class="btn-icon" id="roomInfoBtn" title="<?php echo $is_danish ? 'Ruminfo' : 'Rumsinfo'; ?>">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <?php if ($is_admin): ?>
                                <button class="btn-icon" id="moderationBtn" title="Moderation">
                                    <i class="fas fa-shield-alt"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div id="messageArea" class="message-area">
                            <div class="loading-spinner">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        
                        <div id="moderationWarning" class="moderation-warning" style="display: none;">
                            ⚠️ <?php echo $is_danish ? 'Din besked indeholdt potentielt krænkende indhold og blev ikke sendt' : 'Ditt meddelande innehöll potentiellt kränkande innehåll och skickades inte'; ?>
                        </div>
                        
                        <div class="chat-input-area">
                            <div class="chat-input-wrapper">
                                <textarea 
                                    id="messageInput" 
                                    placeholder="<?php echo $is_danish ? 'Skriv din besked...' : 'Skriv ditt meddelande...'; ?>"
                                    rows="2"></textarea>
                                <div class="input-actions">
                                    <button id="sendToAllBtn" class="btn-primary">
                                        <i class="fas fa-users"></i>
                                        <?php echo $is_danish ? 'Send til alle' : 'Skicka till alla'; ?>
                                    </button>
                                    <button id="sendPrivateBtn" class="btn-secondary" style="display: none;">
                                        <i class="fas fa-user"></i>
                                        <?php echo $is_danish ? 'Send privat' : 'Skicka privat'; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Main Layout */
.community-chat-container {
    height: calc(100vh - 140px);
    padding: 0;
    overflow: hidden;
}

.chat-layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    height: 100%;
}

/* Room Sidebar */
.room-sidebar {
    border-right: 1px solid var(--rtf-border);
    background: var(--rtf-bg);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.room-header {
    padding: 20px;
    border-bottom: 1px solid var(--rtf-border);
    background: var(--rtf-card);
}

.room-header h2 {
    margin: 0;
    font-size: 18px;
}

.room-category {
    padding: 15px;
    border-bottom: 1px solid var(--rtf-border);
}

.room-category h4 {
    margin: 0 0 10px 0;
    font-size: 13px;
    text-transform: uppercase;
    color: var(--rtf-muted);
    font-weight: 600;
}

.room-btn {
    width: 100%;
    padding: 12px;
    margin-bottom: 8px;
    background: var(--rtf-card);
    border: 1px solid var(--rtf-border);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-align: left;
}

.room-btn:hover {
    background: var(--rtf-accent);
    color: white;
    transform: translateX(4px);
}

.room-btn.active {
    background: var(--rtf-accent);
    color: white;
    border-color: var(--rtf-accent);
}

.room-info {
    flex: 1;
}

.room-name {
    display: block;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 2px;
}

.room-desc {
    display: block;
    font-size: 12px;
    opacity: 0.8;
}

.unread-badge {
    background: #ef4444;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

/* Online Users */
.online-users {
    padding: 15px;
    margin-top: auto;
}

.online-users h4 {
    margin: 0 0 10px 0;
    font-size: 13px;
    color: var(--rtf-muted);
}

#onlineUsersList {
    max-height: 200px;
    overflow-y: auto;
}

.online-user {
    padding: 8px;
    margin-bottom: 4px;
    background: var(--rtf-card);
    border-radius: 6px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.online-user .status-dot {
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
}

/* Chat Main Area */
.chat-main-area {
    display: flex;
    flex-direction: column;
    height: 100%;
    background: var(--rtf-card);
}

.chat-welcome {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
}

.welcome-content {
    text-align: center;
    max-width: 700px;
}

.welcome-content h2 {
    margin: 0 0 15px 0;
    color: var(--rtf-text);
}

.chat-rules ul {
    list-style: none;
    padding: 0;
}

.chat-rules li {
    padding: 8px 0;
    border-bottom: 1px solid var(--rtf-border);
}

.chat-rules li:last-child {
    border-bottom: none;
}

/* Chat Room */
.chat-room {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.chat-room-header {
    padding: 20px;
    border-bottom: 1px solid var(--rtf-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.room-title-area h3 {
    margin: 0 0 5px 0;
    font-size: 20px;
}

.room-description {
    font-size: 13px;
    opacity: 0.9;
}

.chat-header-actions {
    display: flex;
    gap: 10px;
}

.btn-icon {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-icon:hover {
    background: rgba(255,255,255,0.3);
}

/* Message Area */
.message-area {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8fafc;
}

.chat-message {
    margin-bottom: 16px;
    display: flex;
    gap: 12px;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    flex-shrink: 0;
}

.message-content {
    flex: 1;
    background: white;
    padding: 12px 16px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.message-author {
    font-weight: 600;
    color: var(--rtf-accent);
    font-size: 14px;
}

.message-time {
    font-size: 11px;
    color: var(--rtf-muted);
}

.message-text {
    color: var(--rtf-text);
    line-height: 1.6;
    font-size: 14px;
}

.message-private {
    background: #fef3c7;
    border-left: 3px solid #f59e0b;
}

.message-private .message-author::after {
    content: "🔒";
    margin-left: 6px;
}

.message-admin .message-author {
    color: #dc2626;
}

.message-admin .message-author::after {
    content: "👑";
    margin-left: 6px;
}

/* Moderation Warning */
.moderation-warning {
    padding: 12px 20px;
    background: #fee2e2;
    border-top: 2px solid #ef4444;
    color: #991b1b;
    font-weight: 500;
    animation: slideDown 0.3s;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Chat Input */
.chat-input-area {
    border-top: 1px solid var(--rtf-border);
    padding: 20px;
    background: var(--rtf-card);
}

.chat-input-wrapper {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

#messageInput {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--rtf-border);
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    resize: none;
    transition: border-color 0.2s;
}

#messageInput:focus {
    outline: none;
    border-color: var(--rtf-accent);
}

.input-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn-primary, .btn-secondary {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: var(--rtf-accent);
    color: white;
}

.btn-primary:hover {
    background: #1e40af;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37,99,235,0.3);
}

.btn-secondary {
    background: #f59e0b;
    color: white;
}

.btn-secondary:hover {
    background: #d97706;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .chat-layout {
        grid-template-columns: 1fr;
    }
    
    .room-sidebar {
        position: fixed;
        left: -100%;
        top: 0;
        bottom: 0;
        width: 280px;
        z-index: 1000;
        transition: left 0.3s;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    
    .room-sidebar.open {
        left: 0;
    }
    
    .community-chat-container {
        height: calc(100vh - 80px);
    }
}
</style>

<script>
const currentUserId = <?php echo $user_id; ?>;
const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;
const language = '<?php echo $language; ?>';
let currentRoom = null;
let currentRoomType = null;
let pollingInterval = null;
let lastMessageId = 0;
let selectedPrivateUser = null;

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    initializeChat();
    setupRoomButtons();
    setupInputHandlers();
    startOnlineUsersPolling();
});

function initializeChat() {
    console.log('Community Chat initialized');
}

function setupRoomButtons() {
    document.querySelectorAll('.room-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const room = this.dataset.room;
            const type = this.dataset.type;
            joinRoom(room, type);
            
            // Mark as active
            document.querySelectorAll('.room-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

function joinRoom(roomId, roomType) {
    currentRoom = roomId;
    currentRoomType = roomType;
    
    // Hide welcome, show chat room
    document.getElementById('chatWelcome').style.display = 'none';
    document.getElementById('chatRoom').style.display = 'flex';
    
    // Update room header
    const roomNames = {
        'support': language === 'da' ? 'Støttechatten' : 'Stödchatten',
        'anbringelse': language === 'da' ? 'Anbringelsessager' : 'Placeringsärenden',
        'samvaer': language === 'da' ? 'Samværssager' : 'Umgängesärenden',
        'forældremyndighed': language === 'da' ? 'Forældremyndighed' : 'Vårdnad',
        'aktindsigt': language === 'da' ? 'Aktindsigt & Journaler' : 'Aktinsyn & Journaler',
        'region-hovedstaden': 'Region Hovedstaden',
        'region-sjaelland': 'Region Sjælland',
        'region-syddanmark': 'Region Syddanmark',
        'region-midtjylland': 'Region Midtjylland',
        'region-nordjylland': 'Region Nordjylland',
        'sweden': '🇸🇪 Sverige'
    };
    
    document.getElementById('currentRoomName').textContent = roomNames[roomId] || roomId;
    document.getElementById('currentRoomDesc').textContent = getRoomDescription(roomId);
    
    // Load messages
    loadRoomMessages(roomId);
    
    // Start polling for new messages
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
    pollingInterval = setInterval(() => loadRoomMessages(roomId), 3000);
    
    // Hide unread badge
    const badge = document.getElementById(`unread-${roomId}`);
    if (badge) {
        badge.style.display = 'none';
    }
}

function getRoomDescription(roomId) {
    const descriptions = {
        'support': language === 'da' ? 'Få støtte fra admins og andre brugere' : 'Få stöd från admins och andra användare',
        'anbringelse': language === 'da' ? 'Chat om tvangsanbringelse' : 'Chatta om tvångsplacering',
        'samvaer': language === 'da' ? 'Chat om samvær med børn' : 'Chatta om umgänge',
        'sweden': 'För svenska användare'
    };
    return descriptions[roomId] || '';
}

function loadRoomMessages(roomId) {
    fetch(`/wp-json/kate/v1/community-chat/messages?room=${roomId}&after=${lastMessageId}`, {
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.messages && data.messages.length > 0) {
            const messageArea = document.getElementById('messageArea');
            const wasAtBottom = isScrolledToBottom(messageArea);
            
            data.messages.forEach(msg => {
                if (msg.id > lastMessageId) {
                    appendMessage(msg);
                    lastMessageId = msg.id;
                }
            });
            
            if (wasAtBottom) {
                scrollToBottom(messageArea);
            }
        }
    })
    .catch(err => console.error('Failed to load messages:', err));
}

function appendMessage(msg) {
    const messageArea = document.getElementById('messageArea');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message';
    
    if (msg.is_private) {
        messageDiv.classList.add('message-private');
    }
    
    if (msg.is_admin) {
        messageDiv.classList.add('message-admin');
    }
    
    const initials = msg.author_name.substring(0, 2).toUpperCase();
    
    messageDiv.innerHTML = `
        <div class="message-avatar">${initials}</div>
        <div class="message-content">
            <div class="message-header">
                <span class="message-author">${escapeHtml(msg.author_name)}</span>
                <span class="message-time">${formatTime(msg.created_at)}</span>
            </div>
            <div class="message-text">${escapeHtml(msg.message)}</div>
            ${msg.is_private ? `<small style="color: #f59e0b; font-size: 11px;">🔒 ${language === 'da' ? 'Privat besked' : 'Privat meddelande'}</small>` : ''}
        </div>
    `;
    
    // Add click handler for private reply
    messageDiv.addEventListener('click', function() {
        if (msg.user_id !== currentUserId) {
            selectedPrivateUser = {id: msg.user_id, name: msg.author_name};
            document.getElementById('sendPrivateBtn').style.display = 'flex';
            document.getElementById('sendPrivateBtn').innerHTML = `
                <i class="fas fa-user"></i>
                ${language === 'da' ? 'Svar til' : 'Svara till'} ${msg.author_name}
            `;
        }
    });
    
    messageArea.appendChild(messageDiv);
}

function setupInputHandlers() {
    // Send to all
    document.getElementById('sendToAllBtn').addEventListener('click', function() {
        sendMessage(false);
    });
    
    // Send private
    document.getElementById('sendPrivateBtn').addEventListener('click', function() {
        if (selectedPrivateUser) {
            sendMessage(true);
        }
    });
    
    // Enter to send (shift+enter for newline)
    document.getElementById('messageInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(false);
        }
    });
}

function sendMessage(isPrivate) {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    
    if (!message || !currentRoom) return;
    
    // Hide previous warning
    document.getElementById('moderationWarning').style.display = 'none';
    
    const data = {
        room: currentRoom,
        message: message,
        is_private: isPrivate,
        recipient_id: isPrivate ? selectedPrivateUser.id : null
    };
    
    fetch('/wp-json/kate/v1/community-chat/send', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            if (data.message) {
                appendMessage(data.message);
                scrollToBottom(document.getElementById('messageArea'));
            }
            // Hide private button
            document.getElementById('sendPrivateBtn').style.display = 'none';
            selectedPrivateUser = null;
        } else if (data.moderated) {
            // Show moderation warning
            document.getElementById('moderationWarning').style.display = 'block';
            setTimeout(() => {
                document.getElementById('moderationWarning').style.display = 'none';
            }, 5000);
        } else {
            alert(data.message || 'Kunne ikke sende besked');
        }
    })
    .catch(err => {
        console.error('Send error:', err);
        alert('Fejl ved afsendelse');
    });
}

function startOnlineUsersPolling() {
    updateOnlineUsers();
    setInterval(updateOnlineUsers, 10000); // Every 10 seconds
}

function updateOnlineUsers() {
    fetch('/wp-json/kate/v1/community-chat/online-users', {
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('onlineCount').textContent = data.count;
            
            const list = document.getElementById('onlineUsersList');
            list.innerHTML = '';
            
            data.users.forEach(user => {
                const div = document.createElement('div');
                div.className = 'online-user';
                div.innerHTML = `
                    <span class="status-dot"></span>
                    <span>${escapeHtml(user.name)}</span>
                `;
                list.appendChild(div);
            });
        }
    })
    .catch(err => console.error('Online users error:', err));
}

// Utility functions
function isScrolledToBottom(el) {
    return el.scrollHeight - el.clientHeight <= el.scrollTop + 50;
}

function scrollToBottom(el) {
    el.scrollTop = el.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return language === 'da' ? 'Lige nu' : 'Nyss';
    if (diff < 3600000) return Math.floor(diff / 60000) + 'm';
    if (diff < 86400000) return Math.floor(diff / 3600000) + 't';
    
    return date.toLocaleDateString(language === 'da' ? 'da-DK' : 'sv-SE', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<?php get_footer(); ?>
