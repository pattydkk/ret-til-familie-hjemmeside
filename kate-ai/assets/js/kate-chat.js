/**
 * Kate AI Chat - Frontend JavaScript
 * Version: 1.0.0
 */

(function($) {
    'use strict';
    
    class KateChat {
        constructor(containerId) {
            this.container = document.getElementById(containerId);
            if (!this.container) return;
            
            this.sessionId = this.getOrCreateSessionId();
            this.isTyping = false;
            
            this.init();
        }
        
        init() {
            this.bindEvents();
            this.loadChatHistory();
            this.showWelcomeMessage();
        }
        
        bindEvents() {
            const sendBtn = this.container.querySelector('.kate-send-btn');
            const input = this.container.querySelector('.kate-input');
            
            if (sendBtn) {
                sendBtn.addEventListener('click', () => this.sendMessage());
            }
            
            if (input) {
                input.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        this.sendMessage();
                    }
                });
            }
            
            // Quick action buttons
            this.container.addEventListener('click', (e) => {
                if (e.target.classList.contains('kate-quick-action')) {
                    const action = e.target.dataset.action;
                    this.handleQuickAction(action);
                }
            });
        }
        
        async sendMessage() {
            const input = this.container.querySelector('.kate-input');
            const message = input.value.trim();
            
            if (!message || this.isTyping) return;
            
            // Display user message
            this.appendMessage('user', message);
            input.value = '';
            
            // Show typing indicator
            this.showTyping();
            
            try {
                const response = await fetch(kateAI.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': kateAI.nonce
                    },
                    body: JSON.stringify({
                        message: message,
                        session_id: this.sessionId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.sessionId = data.session_id;
                    this.saveSessionId();
                    
                    this.hideTyping();
                    this.appendKateResponse(data.data);
                } else {
                    throw new Error(data.message || 'Fejl ved kommunikation med Kate');
                }
                
            } catch (error) {
                console.error('Kate AI Error:', error);
                this.hideTyping();
                this.appendMessage('kate', '⚠️ Undskyld, jeg kunne ikke behandle din besked. Prøv venligst igen.');
            }
        }
        
        appendMessage(sender, text) {
            const messagesContainer = this.container.querySelector('.kate-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `kate-message kate-message-${sender}`;
            
            const avatar = document.createElement('div');
            avatar.className = 'kate-avatar';
            avatar.textContent = sender === 'user' ? '👤' : '🤖';
            
            const content = document.createElement('div');
            content.className = 'kate-message-content';
            content.textContent = text;
            
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(content);
            messagesContainer.appendChild(messageDiv);
            
            this.scrollToBottom();
        }
        
        appendKateResponse(response) {
            const messagesContainer = this.container.querySelector('.kate-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'kate-message kate-message-kate';
            
            const avatar = document.createElement('div');
            avatar.className = 'kate-avatar';
            avatar.textContent = '🤖';
            
            const content = document.createElement('div');
            content.className = 'kate-message-content';
            
            // Title
            if (response.title) {
                const title = document.createElement('h4');
                title.textContent = response.title;
                content.appendChild(title);
            }
            
            // Summary
            if (response.summary) {
                const summary = document.createElement('p');
                summary.className = 'kate-summary';
                summary.textContent = response.summary;
                content.appendChild(summary);
            }
            
            // Details (collapsible)
            if (response.details) {
                const detailsToggle = document.createElement('button');
                detailsToggle.className = 'kate-details-toggle';
                detailsToggle.textContent = '📖 Læs mere';
                
                const detailsContent = document.createElement('div');
                detailsContent.className = 'kate-details-content';
                detailsContent.style.display = 'none';
                detailsContent.innerHTML = this.formatDetails(response.details);
                
                detailsToggle.addEventListener('click', () => {
                    const isHidden = detailsContent.style.display === 'none';
                    detailsContent.style.display = isHidden ? 'block' : 'none';
                    detailsToggle.textContent = isHidden ? '📖 Skjul detaljer' : '📖 Læs mere';
                });
                
                content.appendChild(detailsToggle);
                content.appendChild(detailsContent);
            }
            
            // Law references
            if (response.law_refs && response.law_refs.length > 0) {
                const lawRefsDiv = document.createElement('div');
                lawRefsDiv.className = 'kate-law-refs';
                lawRefsDiv.innerHTML = '<strong>📚 Lovhjemmel:</strong> ' + 
                    response.law_refs.map(ref => `<span class="kate-law-ref">${ref}</span>`).join(', ');
                content.appendChild(lawRefsDiv);
            }
            
            // External links
            if (response.external_links && response.external_links.length > 0) {
                const linksDiv = document.createElement('div');
                linksDiv.className = 'kate-external-links';
                linksDiv.innerHTML = '<strong>🔗 Links:</strong> ';
                
                response.external_links.forEach(link => {
                    const a = document.createElement('a');
                    a.href = link.url;
                    a.textContent = link.title;
                    a.target = '_blank';
                    a.className = 'kate-external-link';
                    linksDiv.appendChild(a);
                    linksDiv.appendChild(document.createTextNode(' '));
                });
                
                content.appendChild(linksDiv);
            }
            
            // Follow-up questions
            if (response.follow_up_questions && response.follow_up_questions.length > 0) {
                const followUpDiv = document.createElement('div');
                followUpDiv.className = 'kate-follow-up';
                followUpDiv.innerHTML = '<strong>💬 Du kan også spørge:</strong>';
                
                const questionsList = document.createElement('div');
                questionsList.className = 'kate-follow-up-list';
                
                response.follow_up_questions.forEach(question => {
                    const btn = document.createElement('button');
                    btn.className = 'kate-follow-up-btn';
                    btn.textContent = question;
                    btn.addEventListener('click', () => {
                        this.container.querySelector('.kate-input').value = question;
                        this.sendMessage();
                    });
                    questionsList.appendChild(btn);
                });
                
                followUpDiv.appendChild(questionsList);
                content.appendChild(followUpDiv);
            }
            
            // Disclaimer
            if (kateAI.disclaimer) {
                const disclaimer = document.createElement('p');
                disclaimer.className = 'kate-disclaimer';
                disclaimer.textContent = '⚠️ ' + kateAI.disclaimer;
                content.appendChild(disclaimer);
            }
            
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(content);
            messagesContainer.appendChild(messageDiv);
            
            this.scrollToBottom();
        }
        
        formatDetails(details) {
            return details.split('\n').map(line => {
                if (line.trim().startsWith('##')) {
                    return `<h5>${line.replace('##', '').trim()}</h5>`;
                } else if (line.trim().startsWith('-')) {
                    return `<li>${line.replace('-', '').trim()}</li>`;
                } else {
                    return `<p>${line}</p>`;
                }
            }).join('');
        }
        
        showTyping() {
            this.isTyping = true;
            const messagesContainer = this.container.querySelector('.kate-messages');
            
            const typingDiv = document.createElement('div');
            typingDiv.className = 'kate-typing-indicator';
            typingDiv.innerHTML = `
                <div class="kate-avatar">🤖</div>
                <div class="kate-typing-dots">
                    <span></span><span></span><span></span>
                </div>
            `;
            
            messagesContainer.appendChild(typingDiv);
            this.scrollToBottom();
        }
        
        hideTyping() {
            this.isTyping = false;
            const typingIndicator = this.container.querySelector('.kate-typing-indicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }
        
        showWelcomeMessage() {
            const messagesContainer = this.container.querySelector('.kate-messages');
            if (messagesContainer.children.length === 0) {
                this.appendMessage('kate', 'Hej! Jeg er Kate, din AI-assistent. Jeg kan hjælpe dig med spørgsmål om Barnets Lov, klager, aktindsigt og meget mere. Hvad kan jeg hjælpe dig med i dag?');
            }
        }
        
        handleQuickAction(action) {
            const actions = {
                'klage': 'Hvordan klager jeg over en afgørelse?',
                'aktindsigt': 'Hvordan får jeg aktindsigt i min sag?',
                'anbringelse': 'Hvad er reglerne for anbringelse uden samtykke?',
                'handleplan': 'Hvad skal en handleplan indeholde?'
            };
            
            const message = actions[action];
            if (message) {
                this.container.querySelector('.kate-input').value = message;
                this.sendMessage();
            }
        }
        
        scrollToBottom() {
            const messagesContainer = this.container.querySelector('.kate-messages');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        getOrCreateSessionId() {
            let sessionId = sessionStorage.getItem('kate_session_id');
            if (!sessionId) {
                sessionId = 'kate_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                sessionStorage.setItem('kate_session_id', sessionId);
            }
            return sessionId;
        }
        
        saveSessionId() {
            sessionStorage.setItem('kate_session_id', this.sessionId);
        }
        
        loadChatHistory() {
            // Could load from localStorage if needed
        }
    }
    
    // Initialize Kate Chat when DOM is ready
    $(document).ready(function() {
        if ($('#kate-chat-widget').length) {
            new KateChat('kate-chat-widget');
        }
    });
    
})(jQuery);
