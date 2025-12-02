<?php
/*
Template Name: Platform - Chat
*/

get_header();
$lang = rtf_get_lang();

if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth/?lang=' . $lang));
    exit;
}

$current_user = rtf_get_current_user();
$user_id = $current_user->id;
$language = $current_user->language ?? 'da';
$is_danish = ($language === 'da');
?>

<div class="platform-container" style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <?php get_template_part('template-parts/platform-sidebar'); ?>
    
    <div class="platform-content" style="min-width: 0;">
        <div class="rtf-card chat-container">
            <div class="chat-layout">
                <!-- Left sidebar: Conversation list -->
                <div class="chat-sidebar">
                    <div class="chat-header">
                        <h2><?php echo $is_danish ? 'Beskeder' : 'Meddelanden'; ?></h2>
                        <button class="btn-new-chat" id="newChatBtn">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    
                    <div class="chat-search">
                        <input type="text" 
                               id="conversationSearch" 
                               placeholder="<?php echo $is_danish ? 'Søg i samtaler...' : 'Sök i konversationer...'; ?>">
                    </div>
                    
                    <div id="conversationList" class="conversation-list">
                        <div class="loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Main area: Message thread -->
                <div class="chat-main">
                    <div id="chatWelcome" class="chat-welcome">
                        <div class="welcome-content">
                            <i class="fas fa-comments"></i>
                            <h3><?php echo $is_danish ? 'Vælg en samtale' : 'Välj en konversation'; ?></h3>
                            <p><?php echo $is_danish ? 
                                'Vælg en samtale fra listen eller start en ny' : 
                                'Välj en konversation från listan eller starta en ny'; ?></p>
                        </div>
                    </div>
                    
                    <div id="chatThread" class="chat-thread" style="display: none;">
                        <div class="chat-thread-header">
                            <div class="recipient-info">
                                <img src="" alt="" class="recipient-avatar" id="recipientAvatar">
                                <div>
                                    <h3 id="recipientName"></h3>
                                    <span class="recipient-status" id="recipientStatus"></span>
                                </div>
                            </div>
                            <div class="chat-actions">
                                <button class="btn-icon" id="closeThreadBtn" title="<?php echo $is_danish ? 'Luk' : 'Stäng'; ?>">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div id="messageContainer" class="message-container">
                            <div class="loading-spinner">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        
                        <div class="chat-input-wrapper">
                            <textarea 
                                id="messageInput" 
                                placeholder="<?php echo $is_danish ? 'Skriv en besked...' : 'Skriv ett meddelande...'; ?>"
                                rows="3"></textarea>
                            <button id="sendMessageBtn" class="btn-send">
                                <i class="fas fa-paper-plane"></i>
                                <?php echo $is_danish ? 'Send' : 'Skicka'; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Chat Modal -->
<div id="newChatModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php echo $is_danish ? 'Ny samtale' : 'Ny konversation'; ?></h3>
            <button class="modal-close" id="closeModalBtn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <input type="text" 
                   id="userSearchInput" 
                   placeholder="<?php echo $is_danish ? 'Søg efter bruger...' : 'Sök efter användare...'; ?>"
                   class="search-input">
            <div id="userSearchResults" class="user-search-results"></div>
        </div>
    </div>
</div>

<style>
.chat-container {
    height: calc(100vh - 120px);
    padding: 0;
    overflow: hidden;
}

.chat-layout {
    display: flex;
    height: 100%;
}

.chat-sidebar {
    width: 320px;
    border-right: 1px solid var(--rtf-border);
    display: flex;
    flex-direction: column;
    background: var(--rtf-bg);
}

.chat-header {
    padding: 20px;
    border-bottom: 1px solid var(--rtf-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header h2 {
    margin: 0;
    font-size: 20px;
}

.btn-new-chat {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--rtf-primary);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-new-chat:hover {
    background: var(--rtf-primary-dark);
    transform: scale(1.1);
}

.chat-search {
    padding: 15px 20px;
    border-bottom: 1px solid var(--rtf-border);
}

.chat-search input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid var(--rtf-border);
    border-radius: 20px;
    font-size: 14px;
}

.conversation-list {
    flex: 1;
    overflow-y: auto;
}

.conversation-item {
    padding: 15px 20px;
    border-bottom: 1px solid var(--rtf-border);
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    gap: 12px;
    position: relative;
}

.conversation-item:hover {
    background: var(--rtf-hover);
}

.conversation-item.active {
    background: var(--rtf-primary-light);
}

.conversation-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.conversation-info {
    flex: 1;
    min-width: 0;
}

.conversation-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
}

.conversation-name {
    font-weight: 600;
    font-size: 15px;
}

.conversation-time {
    font-size: 12px;
    color: var(--rtf-text-secondary);
}

.conversation-preview {
    font-size: 14px;
    color: var(--rtf-text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.unread-badge {
    position: absolute;
    top: 15px;
    right: 20px;
    background: var(--rtf-primary);
    color: white;
    border-radius: 12px;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 600;
}

.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #f8f9fa;
}

.chat-welcome {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.welcome-content {
    text-align: center;
    color: var(--rtf-text-secondary);
}

.welcome-content i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.3;
}

.chat-thread {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.chat-thread-header {
    padding: 20px;
    background: white;
    border-bottom: 1px solid var(--rtf-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.recipient-info {
    display: flex;
    gap: 12px;
    align-items: center;
}

.recipient-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.recipient-info h3 {
    margin: 0;
    font-size: 16px;
}

.recipient-status {
    font-size: 12px;
    color: var(--rtf-text-secondary);
}

.message-container {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.message {
    display: flex;
    gap: 10px;
    max-width: 70%;
}

.message.sent {
    margin-left: auto;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.message-content {
    background: white;
    padding: 10px 15px;
    border-radius: 12px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.message.sent .message-content {
    background: var(--rtf-primary);
    color: white;
}

.message-text {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
    word-wrap: break-word;
}

.message-time {
    font-size: 11px;
    color: var(--rtf-text-secondary);
    margin-top: 4px;
}

.message.sent .message-time {
    color: rgba(255,255,255,0.7);
}

.chat-input-wrapper {
    padding: 20px;
    background: white;
    border-top: 1px solid var(--rtf-border);
    display: flex;
    gap: 10px;
}

#messageInput {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid var(--rtf-border);
    border-radius: 20px;
    resize: none;
    font-family: inherit;
    font-size: 14px;
}

.btn-send {
    padding: 10px 20px;
    background: var(--rtf-primary);
    color: white;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-send:hover {
    background: var(--rtf-primary-dark);
}

.btn-send:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid var(--rtf-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: var(--rtf-text-secondary);
}

.modal-body {
    padding: 20px;
    flex: 1;
    overflow-y: auto;
}

.search-input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--rtf-border);
    border-radius: 8px;
    margin-bottom: 15px;
}

.user-search-results {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.user-result-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border: 1px solid var(--rtf-border);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.user-result-item:hover {
    background: var(--rtf-hover);
    border-color: var(--rtf-primary);
}

.user-result-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.user-result-info h4 {
    margin: 0 0 4px 0;
    font-size: 15px;
}

.user-result-info p {
    margin: 0;
    font-size: 13px;
    color: var(--rtf-text-secondary);
}

.loading-spinner {
    text-align: center;
    padding: 40px;
    color: var(--rtf-text-secondary);
}

.loading-spinner i {
    font-size: 32px;
}

@media (max-width: 768px) {
    .chat-sidebar {
        width: 100%;
    }
    
    .chat-sidebar.has-active-thread {
        display: none;
    }
    
    .message {
        max-width: 85%;
    }
}
</style>

<script>
const ChatApp = {
    currentThread: null,
    conversations: [],
    pollInterval: null,
    lastPollTime: null,
    language: '<?php echo $language; ?>',
    
    strings: {
        da_DK: {
            sent: 'Sendt',
            loadingMessages: 'Indlæser beskeder...',
            noResults: 'Ingen resultater',
            messageSent: 'Besked sendt',
            errorSending: 'Fejl ved afsendelse',
            errorLoading: 'Fejl ved indlæsning',
            justNow: 'Lige nu',
            minuteAgo: 'minut siden',
            minutesAgo: 'minutter siden',
            hourAgo: 'time siden',
            hoursAgo: 'timer siden',
            yesterday: 'I går',
            allowMessages: 'Denne bruger modtager ikke beskeder'
        },
        sv_SE: {
            sent: 'Skickat',
            loadingMessages: 'Laddar meddelanden...',
            noResults: 'Inga resultat',
            messageSent: 'Meddelande skickat',
            errorSending: 'Fel vid sändning',
            errorLoading: 'Fel vid laddning',
            justNow: 'Just nu',
            minuteAgo: 'minut sedan',
            minutesAgo: 'minuter sedan',
            hourAgo: 'timme sedan',
            hoursAgo: 'timmar sedan',
            yesterday: 'I går',
            allowMessages: 'Denna användare tar inte emot meddelanden'
        }
    },
    
    init() {
        this.loadConversations();
        this.attachEventListeners();
        this.startPolling();
        this.lastPollTime = Math.floor(Date.now() / 1000);
    },
    
    t(key) {
        return this.strings[this.language][key] || key;
    },
    
    attachEventListeners() {
        // New chat button
        document.getElementById('newChatBtn').addEventListener('click', () => {
            document.getElementById('newChatModal').style.display = 'flex';
        });
        
        // Close modal
        document.getElementById('closeModalBtn').addEventListener('click', () => {
            document.getElementById('newChatModal').style.display = 'none';
        });
        
        // User search
        let searchTimeout;
        document.getElementById('userSearchInput').addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => this.searchUsers(query), 300);
            } else {
                document.getElementById('userSearchResults').innerHTML = '';
            }
        });
        
        // Send message
        document.getElementById('sendMessageBtn').addEventListener('click', () => {
            this.sendMessage();
        });
        
        // Send on Enter (Shift+Enter for new line)
        document.getElementById('messageInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        
        // Close thread (mobile)
        document.getElementById('closeThreadBtn').addEventListener('click', () => {
            this.closeThread();
        });
    },
    
    async loadConversations() {
        try {
            const response = await fetch('/wp-json/kate/v1/messages/list', {
                credentials: 'same-origin'
            });
            
            if (!response.ok) throw new Error('Failed to load conversations');
            
            const data = await response.json();
            this.conversations = data.conversations || [];
            this.renderConversations();
            
        } catch (error) {
            console.error('Error loading conversations:', error);
            document.getElementById('conversationList').innerHTML = 
                `<p style="padding: 20px; text-align: center; color: var(--rtf-error);">${this.t('errorLoading')}</p>`;
        }
    },
    
    renderConversations() {
        const container = document.getElementById('conversationList');
        
        if (this.conversations.length === 0) {
            container.innerHTML = `<p style="padding: 20px; text-align: center; color: var(--rtf-text-secondary);">${this.t('noResults')}</p>`;
            return;
        }
        
        container.innerHTML = this.conversations.map(conv => `
            <div class="conversation-item" data-user-id="${conv.user_id}" onclick="ChatApp.openThread(${conv.user_id}, '${conv.username}', '${conv.full_name}')">
                <img src="${this.getGravatar(conv.email)}" alt="${conv.username}" class="conversation-avatar">
                <div class="conversation-info">
                    <div class="conversation-header">
                        <span class="conversation-name">${conv.full_name || conv.username}</span>
                        <span class="conversation-time">${this.formatTime(conv.last_message_time)}</span>
                    </div>
                    <div class="conversation-preview">${conv.last_message}</div>
                </div>
                ${conv.unread_count > 0 ? `<span class="unread-badge">${conv.unread_count}</span>` : ''}
            </div>
        `).join('');
    },
    
    async openThread(userId, username, fullName) {
        this.currentThread = userId;
        
        // Update active state
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.toggle('active', item.dataset.userId == userId);
        });
        
        // Show thread, hide welcome
        document.getElementById('chatWelcome').style.display = 'none';
        document.getElementById('chatThread').style.display = 'flex';
        
        // Update header
        document.getElementById('recipientName').textContent = fullName || username;
        document.getElementById('recipientAvatar').src = this.getGravatar('');
        
        // Load messages
        await this.loadMessages(userId);
        
        // Mark as read
        await this.markAsRead(userId);
        
        // Mobile: hide sidebar
        if (window.innerWidth <= 768) {
            document.querySelector('.chat-sidebar').classList.add('has-active-thread');
        }
    },
    
    async loadMessages(userId) {
        const container = document.getElementById('messageContainer');
        container.innerHTML = `<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i></div>`;
        
        try {
            const response = await fetch(`/wp-json/kate/v1/messages/conversation/${userId}`, {
                credentials: 'same-origin'
            });
            
            if (!response.ok) throw new Error('Failed to load messages');
            
            const data = await response.json();
            this.renderMessages(data.messages || []);
            
        } catch (error) {
            console.error('Error loading messages:', error);
            container.innerHTML = `<p style="text-align: center; color: var(--rtf-error);">${this.t('errorLoading')}</p>`;
        }
    },
    
    renderMessages(messages) {
        const container = document.getElementById('messageContainer');
        
        if (messages.length === 0) {
            container.innerHTML = `<p style="text-align: center; color: var(--rtf-text-secondary);">${this.t('noResults')}</p>`;
            return;
        }
        
        container.innerHTML = messages.map(msg => {
            const isSent = msg.sender_id == <?php echo $user_id; ?>;
            return `
                <div class="message ${isSent ? 'sent' : 'received'}">
                    <img src="${this.getGravatar(msg.email)}" alt="${msg.username}" class="message-avatar">
                    <div class="message-content">
                        <p class="message-text">${this.escapeHtml(msg.message)}</p>
                        <div class="message-time">${this.formatTime(msg.created_at)}</div>
                    </div>
                </div>
            `;
        }).join('');
        
        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    },
    
    async sendMessage() {
        if (!this.currentThread) return;
        
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        
        if (!message) return;
        
        const sendBtn = document.getElementById('sendMessageBtn');
        sendBtn.disabled = true;
        
        try {
            const response = await fetch('/wp-json/kate/v1/messages/send', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    recipient_id: this.currentThread,
                    message: message
                })
            });
            
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'Failed to send');
            }
            
            // Clear input
            input.value = '';
            
            // Reload messages
            await this.loadMessages(this.currentThread);
            
            // Update conversation list
            await this.loadConversations();
            
        } catch (error) {
            console.error('Error sending message:', error);
            alert(this.t('errorSending') + ': ' + error.message);
        } finally {
            sendBtn.disabled = false;
            input.focus();
        }
    },
    
    async searchUsers(query) {
        try {
            const response = await fetch(`/wp-json/kate/v1/messages/search-users?query=${encodeURIComponent(query)}`, {
                credentials: 'same-origin'
            });
            
            if (!response.ok) throw new Error('Search failed');
            
            const data = await response.json();
            this.renderUserResults(data.users || []);
            
        } catch (error) {
            console.error('Error searching users:', error);
        }
    },
    
    renderUserResults(users) {
        const container = document.getElementById('userSearchResults');
        
        if (users.length === 0) {
            container.innerHTML = `<p style="text-align: center; color: var(--rtf-text-secondary);">${this.t('noResults')}</p>`;
            return;
        }
        
        container.innerHTML = users.map(user => `
            <div class="user-result-item" onclick="ChatApp.startConversation(${user.id}, '${user.username}', '${user.full_name}')" 
                 ${!user.allow_messages ? 'style="opacity: 0.5; cursor: not-allowed;"' : ''}>
                <img src="${this.getGravatar(user.email)}" alt="${user.username}" class="user-result-avatar">
                <div class="user-result-info">
                    <h4>${user.full_name || user.username}</h4>
                    <p>@${user.username}</p>
                    ${!user.allow_messages ? `<p style="color: var(--rtf-error); font-size: 11px;">${this.t('allowMessages')}</p>` : ''}
                </div>
            </div>
        `).join('');
    },
    
    async startConversation(userId, username, fullName) {
        // Close modal
        document.getElementById('newChatModal').style.display = 'none';
        
        // Open thread
        await this.openThread(userId, username, fullName);
    },
    
    async markAsRead(userId) {
        try {
            await fetch(`/wp-json/kate/v1/messages/mark-read/${userId}`, {
                method: 'POST',
                credentials: 'same-origin'
            });
        } catch (error) {
            console.error('Error marking as read:', error);
        }
    },
    
    closeThread() {
        this.currentThread = null;
        document.getElementById('chatWelcome').style.display = 'flex';
        document.getElementById('chatThread').style.display = 'none';
        
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Mobile: show sidebar
        document.querySelector('.chat-sidebar').classList.remove('has-active-thread');
    },
    
    startPolling() {
        // Poll every 5 seconds for new messages
        this.pollInterval = setInterval(() => {
            if (this.currentThread) {
                this.pollNewMessages();
            }
        }, 5000);
    },
    
    async pollNewMessages() {
        try {
            const response = await fetch(`/wp-json/kate/v1/messages/poll?since=${this.lastPollTime}`, {
                credentials: 'same-origin'
            });
            
            if (!response.ok) return;
            
            const data = await response.json();
            
            if (data.messages && data.messages.length > 0) {
                // Reload current conversation
                await this.loadMessages(this.currentThread);
                // Update conversation list
                await this.loadConversations();
            }
            
            this.lastPollTime = Math.floor(Date.now() / 1000);
            
        } catch (error) {
            console.error('Poll error:', error);
        }
    },
    
    getGravatar(email) {
        const hash = email ? this.md5(email.toLowerCase().trim()) : '00000000000000000000000000000000';
        return `https://www.gravatar.com/avatar/${hash}?d=mp&s=80`;
    },
    
    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000);
        
        if (diff < 60) return this.t('justNow');
        if (diff < 3600) {
            const mins = Math.floor(diff / 60);
            return mins === 1 ? `1 ${this.t('minuteAgo')}` : `${mins} ${this.t('minutesAgo')}`;
        }
        if (diff < 86400) {
            const hours = Math.floor(diff / 3600);
            return hours === 1 ? `1 ${this.t('hourAgo')}` : `${hours} ${this.t('hoursAgo')}`;
        }
        if (diff < 172800) return this.t('yesterday');
        
        return date.toLocaleDateString(this.language.replace('_', '-'));
    },
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },
    
    md5(string) {
        // Simple MD5 implementation (use a library in production)
        return string; // Placeholder - implement proper MD5
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    ChatApp.init();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (ChatApp.pollInterval) {
        clearInterval(ChatApp.pollInterval);
    }
});
</script>

<?php get_footer('platform'); ?>
