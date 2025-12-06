<?php
/**
 * Template Name: Platform - Chat Rooms (Grupperum)
 * Multi-room chat med moderation
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
$is_admin = rtf_is_admin_user();
$language = $current_user->language ?? 'da';
$is_danish = ($language === 'da');

global $wpdb;
$table_rooms = $wpdb->prefix . 'rtf_chat_rooms';
$table_messages = $wpdb->prefix . 'rtf_chat_room_messages';
$table_members = $wpdb->prefix . 'rtf_chat_room_members';
$table_users = $wpdb->prefix . 'rtf_platform_users';

// Get all available rooms
$rooms = $wpdb->get_results("
    SELECT r.*, 
           (SELECT COUNT(*) FROM $table_members WHERE room_id = r.id) as member_count,
           (SELECT COUNT(*) FROM $table_messages WHERE room_id = r.id AND created_at > NOW() - INTERVAL 24 HOUR) as messages_today
    FROM $table_rooms r
    WHERE r.is_private = 0
    ORDER BY 
        CASE r.room_type
            WHEN 'support' THEN 1
            WHEN 'sagstype' THEN 2
            WHEN 'landsdel' THEN 3
            ELSE 4
        END,
        r.name ASC
");
?>

<div class="platform-container" style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <?php get_template_part('template-parts/platform-sidebar'); ?>
    
    <div class="platform-content" style="min-width: 0;">
        <div class="rtf-card">
            <div class="chat-rooms-layout">
                <!-- Room selector sidebar -->
                <div class="rooms-sidebar">
                    <div class="rooms-header">
                        <h2><?php echo $is_danish ? '💬 Chat Rum' : '💬 Chattrum'; ?></h2>
                    </div>
                    
                    <div class="rooms-tabs">
                        <button class="room-tab active" data-type="all">
                            <?php echo $is_danish ? 'Alle' : 'Alla'; ?>
                        </button>
                        <button class="room-tab" data-type="support">
                            <?php echo $is_danish ? 'Støtte' : 'Support'; ?>
                        </button>
                        <button class="room-tab" data-type="sagstype">
                            <?php echo $is_danish ? 'Sagstype' : 'Ärendetyp'; ?>
                        </button>
                        <button class="room-tab" data-type="landsdel">
                            <?php echo $is_danish ? 'Område' : 'Område'; ?>
                        </button>
                    </div>
                    
                    <div class="rooms-list">
                        <?php 
                        $grouped_rooms = [];
                        foreach ($rooms as $room) {
                            $grouped_rooms[$room->room_type][] = $room;
                        }
                        
                        foreach ($grouped_rooms as $type => $type_rooms):
                            $type_label = [
                                'support' => $is_danish ? '🛟 Støtte' : '🛟 Support',
                                'sagstype' => $is_danish ? '⚖️ Sagstype' : '⚖️ Ärendetyp',
                                'landsdel' => $is_danish ? '📍 Område' : '📍 Område'
                            ][$type] ?? $type;
                        ?>
                            <div class="room-group" data-room-type="<?php echo esc_attr($type); ?>">
                                <h4 class="room-group-title"><?php echo $type_label; ?></h4>
                                <?php foreach ($type_rooms as $room): ?>
                                    <div class="room-item" data-room-id="<?php echo $room->id; ?>" data-room-type="<?php echo esc_attr($room->room_type); ?>">
                                        <div class="room-item-content">
                                            <div class="room-info">
                                                <strong><?php echo esc_html($room->name); ?></strong>
                                                <small><?php echo esc_html($room->description); ?></small>
                                            </div>
                                            <div class="room-meta">
                                                <span class="room-members">
                                                    <i class="fas fa-users"></i> <?php echo $room->member_count; ?>
                                                </span>
                                                <?php if ($room->messages_today > 0): ?>
                                                    <span class="room-activity">
                                                        <i class="fas fa-comment"></i> <?php echo $room->messages_today; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Chat area -->
                <div class="room-chat-area">
                    <div id="roomWelcome" class="room-welcome">
                        <div class="welcome-content">
                            <i class="fas fa-comments" style="font-size: 4em; color: #2563eb; margin-bottom: 1rem;"></i>
                            <h3><?php echo $is_danish ? 'Vælg et chat rum' : 'Välj ett chattrum'; ?></h3>
                            <p><?php echo $is_danish ? 
                                'Vælg et rum fra listen for at begynde at chatte med andre forældre' : 
                                'Välj ett rum från listan för att börja chatta med andra föräldrar'; ?></p>
                            
                            <div class="welcome-info" style="margin-top: 2rem; text-align: left; max-width: 600px;">
                                <h4><?php echo $is_danish ? '📌 Chat Regler' : '📌 Chattregler'; ?></h4>
                                <ul style="margin-top: 1rem;">
                                    <li><?php echo $is_danish ? 'Vær venlig og respektfuld' : 'Var vänlig och respektfull'; ?></li>
                                    <li><?php echo $is_danish ? 'Ingen trusler eller groft sprog' : 'Inga hot eller grovt språk'; ?></li>
                                    <li><?php echo $is_danish ? 'Respekter andres privatliv' : 'Respektera andras integritet'; ?></li>
                                    <li><?php echo $is_danish ? 'Beskeder modereres automatisk' : 'Meddelanden modereras automatiskt'; ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div id="roomChat" class="room-chat" style="display: none;">
                        <div class="room-chat-header">
                            <div class="room-header-info">
                                <h3 id="activeRoomName"></h3>
                                <span id="activeRoomDesc"></span>
                            </div>
                            <div class="room-actions">
                                <span class="online-count">
                                    <i class="fas fa-circle" style="color: #10b981;"></i>
                                    <span id="onlineCount">0</span> <?php echo $is_danish ? 'online' : 'online'; ?>
                                </span>
                                <button class="btn-icon" id="leaveRoomBtn" title="<?php echo $is_danish ? 'Forlad rum' : 'Lämna rum'; ?>">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div id="roomMessages" class="room-messages">
                            <div class="loading-spinner">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        
                        <div class="room-input-wrapper">
                            <div class="typing-indicator" id="typingIndicator" style="display: none;">
                                <i class="fas fa-ellipsis-h"></i>
                                <span id="typingText"></span>
                            </div>
                            <div class="input-controls">
                                <button class="btn-icon" id="toggleModeBtn" title="<?php echo $is_danish ? 'Skift mellem offentlig/privat' : 'Växla mellan offentlig/privat'; ?>">
                                    <i class="fas fa-globe"></i>
                                </button>
                                <textarea 
                                    id="roomMessageInput" 
                                    placeholder="<?php echo $is_danish ? 'Skriv til alle i rummet...' : 'Skriv till alla i rummet...'; ?>"
                                    rows="2"></textarea>
                                <button id="sendRoomMessageBtn" class="btn-send">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                            <div class="input-mode-indicator" id="inputModeIndicator">
                                <i class="fas fa-globe"></i>
                                <span id="inputModeText"><?php echo $is_danish ? 'Sender til alle' : 'Skickar till alla'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-rooms-layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    height: calc(100vh - 200px);
    min-height: 600px;
    gap: 0;
    background: white;
    border-radius: 12px;
    overflow: hidden;
}

.rooms-sidebar {
    background: #f8fafc;
    border-right: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
}

.rooms-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.rooms-header h2 {
    margin: 0;
    font-size: 1.25rem;
}

.rooms-tabs {
    display: flex;
    padding: 0.5rem;
    gap: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
    flex-wrap: wrap;
}

.room-tab {
    padding: 0.5rem 1rem;
    border: none;
    background: transparent;
    cursor: pointer;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.room-tab:hover {
    background: #e2e8f0;
}

.room-tab.active {
    background: #2563eb;
    color: white;
}

.rooms-list {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
}

.room-group {
    margin-bottom: 1.5rem;
}

.room-group-title {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 0.5rem;
    padding: 0 0.5rem;
}

.room-item {
    padding: 1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 0.5rem;
    border: 2px solid transparent;
}

.room-item:hover {
    background: white;
    border-color: #2563eb;
}

.room-item.active {
    background: #eff6ff;
    border-color: #2563eb;
}

.room-item-content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.room-info strong {
    display: block;
    color: #0f172a;
}

.room-info small {
    display: block;
    color: #64748b;
    font-size: 0.875rem;
}

.room-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.75rem;
    color: #64748b;
}

.room-chat-area {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.room-welcome {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.welcome-content {
    text-align: center;
    max-width: 800px;
}

.room-chat {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.room-chat-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.room-header-info h3 {
    margin: 0 0 0.25rem 0;
}

.room-header-info span {
    color: #64748b;
    font-size: 0.875rem;
}

.room-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.online-count {
    font-size: 0.875rem;
    color: #10b981;
}

.room-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.room-message {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #60a5fa, #2563eb);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    flex-shrink: 0;
}

.message-content {
    flex: 1;
    background: #f1f5f9;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    border-top-left-radius: 4px;
}

.room-message.own .message-content {
    background: #eff6ff;
    border: 1px solid #2563eb;
}

.room-message.admin .message-avatar {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
}

.room-message.moderated .message-content {
    background: #fee2e2;
    border: 1px solid #ef4444;
}

.message-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.25rem;
}

.message-author {
    font-weight: 600;
    color: #0f172a;
}

.admin-badge {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
    margin-left: 0.5rem;
}

.message-time {
    font-size: 0.75rem;
    color: #94a3b8;
}

.message-text {
    color: #334155;
    line-height: 1.5;
}

.moderation-notice {
    margin-top: 0.5rem;
    padding: 0.5rem;
    background: #fef2f2;
    border-radius: 6px;
    font-size: 0.875rem;
    color: #dc2626;
}

.room-input-wrapper {
    padding: 1.5rem;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}

.typing-indicator {
    padding: 0.5rem 0;
    color: #64748b;
    font-size: 0.875rem;
    font-style: italic;
}

.input-controls {
    display: flex;
    gap: 0.75rem;
    align-items: flex-end;
}

.input-controls textarea {
    flex: 1;
    padding: 0.75rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    resize: none;
    font-family: inherit;
}

.input-mode-indicator {
    margin-top: 0.5rem;
    padding: 0.5rem;
    background: #eff6ff;
    border-radius: 6px;
    font-size: 0.875rem;
    color: #2563eb;
}

.input-mode-indicator.private {
    background: #fef3c7;
    color: #f59e0b;
}

@media (max-width: 768px) {
    .chat-rooms-layout {
        grid-template-columns: 1fr;
        height: calc(100vh - 150px);
    }
    
    .rooms-sidebar {
        display: none;
    }
    
    .rooms-sidebar.mobile-open {
        display: flex;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1000;
    }
}
</style>

<script>
const currentUserId = <?php echo $user_id; ?>;
const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;
const isDanish = <?php echo $is_danish ? 'true' : 'false'; ?>;
let activeRoomId = null;
let messageMode = 'public'; // 'public' or 'private'
let pollInterval = null;

// Bad words for moderation (Danish + Swedish)
const badWords = [
    'fuck', 'satan', 'helvede', 'lort', 'pis', 'kusse', 'fisse', 
    'luder', 'h', 'svin', 'idiot', 'taber', 'dum',
    'fan', 'helvete', 'jävlar', 'skit', 'fitta', 'hora'
];

const threatWords = [
    'slår', 'slå', 'dræber', 'dræbe', 'skyder', 'skyde', 'kniv',
    'slåss', 'döda', 'skjuter', 'skjuta', 'kniv'
];

// Room tab filtering
document.querySelectorAll('.room-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.room-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const type = this.dataset.type;
        document.querySelectorAll('.room-group').forEach(group => {
            if (type === 'all' || group.dataset.roomType === type) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        });
    });
});

// Room selection
document.querySelectorAll('.room-item').forEach(item => {
    item.addEventListener('click', function() {
        const roomId = parseInt(this.dataset.roomId);
        joinRoom(roomId);
        
        document.querySelectorAll('.room-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
    });
});

// Join room
async function joinRoom(roomId) {
    activeRoomId = roomId;
    
    const response = await fetch('/wp-json/kate/v1/chat-rooms/join', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        credentials: 'same-origin',
        body: JSON.stringify({room_id: roomId})
    });
    
    const data = await response.json();
    
    if (data.success) {
        document.getElementById('roomWelcome').style.display = 'none';
        document.getElementById('roomChat').style.display = 'flex';
        document.getElementById('activeRoomName').textContent = data.room.name;
        document.getElementById('activeRoomDesc').textContent = data.room.description;
        
        loadRoomMessages(roomId);
        startPolling();
    }
}

// Load messages
async function loadRoomMessages(roomId) {
    const response = await fetch(`/wp-json/kate/v1/chat-rooms/${roomId}/messages`);
    const data = await response.json();
    
    const container = document.getElementById('roomMessages');
    container.innerHTML = '';
    
    if (data.success && data.messages) {
        data.messages.forEach(msg => {
            container.appendChild(createMessageElement(msg));
        });
        container.scrollTop = container.scrollHeight;
    }
}

// Create message element
function createMessageElement(msg) {
    const div = document.createElement('div');
    div.className = 'room-message';
    if (msg.user_id == currentUserId) div.classList.add('own');
    if (msg.is_admin) div.classList.add('admin');
    if (msg.is_moderated) div.classList.add('moderated');
    
    const initial = msg.username ? msg.username.charAt(0).toUpperCase() : '?';
    
    div.innerHTML = `
        <div class="message-avatar">${initial}</div>
        <div class="message-content">
            <div class="message-header">
                <div>
                    <span class="message-author">${msg.username || 'Anonym'}</span>
                    ${msg.is_admin ? '<span class="admin-badge">👑 ADMIN</span>' : ''}
                </div>
                <span class="message-time">${formatTime(msg.created_at)}</span>
            </div>
            <div class="message-text">${escapeHtml(msg.message)}</div>
            ${msg.is_moderated ? `<div class="moderation-notice">⚠️ ${msg.moderation_reason || 'Besked modereret'}</div>` : ''}
        </div>
    `;
    
    return div;
}

// Send message
document.getElementById('sendRoomMessageBtn').addEventListener('click', sendMessage);
document.getElementById('roomMessageInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

async function sendMessage() {
    const input = document.getElementById('roomMessageInput');
    const message = input.value.trim();
    
    if (!message || !activeRoomId) return;
    
    // Check for bad words/threats
    const lowerMsg = message.toLowerCase();
    let moderated = false;
    let moderationReason = '';
    
    if (badWords.some(word => lowerMsg.includes(word))) {
        moderated = true;
        moderationReason = isDanish ? 'Upassende sprog' : 'Olämpligt språk';
    }
    
    if (threatWords.some(word => lowerMsg.includes(word))) {
        moderated = true;
        moderationReason = isDanish ? 'Trusler er ikke tilladt' : 'Hot är inte tillåtna';
    }
    
    const response = await fetch('/wp-json/kate/v1/chat-rooms/send', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        credentials: 'same-origin',
        body: JSON.stringify({
            room_id: activeRoomId,
            message: message,
            is_moderated: moderated ? 1 : 0,
            moderation_reason: moderationReason
        })
    });
    
    const data = await response.json();
    
    if (data.success) {
        input.value = '';
        loadRoomMessages(activeRoomId);
    }
}

// Toggle public/private mode
document.getElementById('toggleModeBtn').addEventListener('click', function() {
    messageMode = messageMode === 'public' ? 'private' : 'public';
    updateModeIndicator();
});

function updateModeIndicator() {
    const indicator = document.getElementById('inputModeIndicator');
    const text = document.getElementById('inputModeText');
    const icon = indicator.querySelector('i');
    const input = document.getElementById('roomMessageInput');
    
    if (messageMode === 'private') {
        indicator.classList.add('private');
        icon.className = 'fas fa-lock';
        text.textContent = isDanish ? 'Privat besked' : 'Privat meddelande';
        input.placeholder = isDanish ? 'Skriv privat til...' : 'Skriv privat till...';
    } else {
        indicator.classList.remove('private');
        icon.className = 'fas fa-globe';
        text.textContent = isDanish ? 'Sender til alle' : 'Skickar till alla';
        input.placeholder = isDanish ? 'Skriv til alle...' : 'Skriv till alla...';
    }
}

// Poll for new messages
function startPolling() {
    if (pollInterval) clearInterval(pollInterval);
    pollInterval = setInterval(() => {
        if (activeRoomId) {
            loadRoomMessages(activeRoomId);
        }
    }, 3000);
}

// Leave room
document.getElementById('leaveRoomBtn').addEventListener('click', function() {
    activeRoomId = null;
    if (pollInterval) clearInterval(pollInterval);
    document.getElementById('roomChat').style.display = 'none';
    document.getElementById('roomWelcome').style.display = 'flex';
    document.querySelectorAll('.room-item').forEach(i => i.classList.remove('active'));
});

// Utility functions
function formatTime(datetime) {
    const date = new Date(datetime);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return isDanish ? 'Lige nu' : 'Nyss';
    if (diff < 3600000) return Math.floor(diff / 60000) + (isDanish ? ' min' : ' min');
    if (diff < 86400000) return date.toLocaleTimeString(isDanish ? 'da-DK' : 'sv-SE', {hour: '2-digit', minute: '2-digit'});
    return date.toLocaleDateString(isDanish ? 'da-DK' : 'sv-SE');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php get_footer(); ?>
