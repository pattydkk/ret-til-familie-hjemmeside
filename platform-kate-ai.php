<?php
/**
 * Template Name: Platform - Kate AI
 */

if (!session_id()) session_start();

if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth'));
    exit;
}

get_header();
?>

<style>
.platform-layout {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 2rem;
    max-width: 1400px;
    margin: 2rem auto;
    padding: 0 1.5rem;
}

.platform-sidebar {
    background: white;
    border: 1px solid #dbeafe;
    border-radius: 18px;
    padding: 1.5rem;
    height: fit-content;
    position: sticky;
    top: 80px;
}

.platform-sidebar h3 {
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
    color: #2563eb;
}

.platform-nav {
    list-style: none;
    padding: 0;
    margin: 0;
}

.platform-nav li {
    margin-bottom: 0.5rem;
}

.platform-nav a {
    display: block;
    padding: 0.625rem 0.875rem;
    border-radius: 8px;
    color: #475569;
    text-decoration: none;
    transition: all 0.2s ease;
}

.platform-nav a:hover,
.platform-nav a.active {
    background: #e0f2fe;
    color: #2563eb;
}

.platform-content {
    min-height: 600px;
}

.kate-intro {
    background: white;
    border: 1px solid #dbeafe;
    border-radius: 18px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.kate-intro h1 {
    margin: 0 0 1rem 0;
    font-size: 1.8rem;
    color: #0f172a;
}

.kate-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin: 1.5rem 0;
}

.feature-card {
    padding: 1rem;
    background: #f9fafb;
    border: 1px solid #dbeafe;
    border-radius: 12px;
}

.feature-card h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    color: #2563eb;
}

.feature-card p {
    margin: 0;
    font-size: 0.85rem;
    color: #64748b;
}

@media (max-width: 768px) {
    .platform-layout {
        grid-template-columns: 1fr;
    }
    
    .platform-sidebar {
        position: static;
    }
}
</style>

<div class="platform-layout" style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <?php get_template_part('template-parts/platform-sidebar'); ?>
    
    <main class="platform-content">
        <div class="kate-intro">
            <h1>🤖 Kate - Din AI Assistent</h1>
            <p style="color: #64748b; font-size: 1rem; line-height: 1.6;">
                Kate er din personlige AI-assistent, der kan hjælpe dig med juridiske spørgsmål, 
                analysere dokumenter og guide dig gennem komplekse sager inden for familie- og socialret.
            </p>
            
            <div class="kate-features">
                <div class="feature-card">
                    <h3>💬 Spørg om alt</h3>
                    <p>Stil spørgsmål om Barnets Lov, klager, aktindsigt og meget mere</p>
                </div>
                <div class="feature-card">
                    <h3>📄 Dokument analyse</h3>
                    <p>Få analyseret afgørelser, handleplaner og undersøgelser</p>
                </div>
                <div class="feature-card">
                    <h3>⚖️ Juridisk vejledning</h3>
                    <p>Få konkrete lovhenvisninger og trin-for-trin guides</p>
                </div>
                <div class="feature-card">
                    <h3>🎯 98% præcision</h3>
                    <p>Baseret på dansk lovgivning og socialfaglig praksis</p>
                </div>
            </div>
        </div>
        
        <!-- KATE AI CHAT INTERFACE -->
        <div style="background: white; border: 1px solid #dbeafe; border-radius: 18px; padding: 2rem; display: flex; flex-direction: column; height: 600px;">
            <div id="chatMessages" style="flex: 1; overflow-y: auto; padding: 1rem; border: 1px solid #e0f2fe; border-radius: 12px; background: #f9fafb; margin-bottom: 1rem;">
                <div class="kate-message" style="display: flex; gap: 12px; margin-bottom: 1rem;">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #8b5cf6, #6366f1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; flex-shrink: 0;">K</div>
                    <div style="flex: 1; background: white; padding: 12px 16px; border-radius: 12px; border: 1px solid #e0f2fe;">
                        <p style="margin: 0; color: #1e293b; line-height: 1.6;">Hej! Jeg er Kate, din AI assistent. Jeg kan hjælpe dig med juridiske spørgsmål om Barnets Lov, analysere dokumenter og give vejledning. Hvad kan jeg hjælpe dig med i dag?</p>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <textarea id="userMessage" placeholder="Skriv din besked til Kate..." style="flex: 1; padding: 12px; border: 2px solid #e0f2fe; border-radius: 12px; font-size: 1rem; resize: none; font-family: inherit;" rows="3"></textarea>
                <button id="sendMessage" style="padding: 12px 24px; background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; white-space: nowrap; align-self: flex-end;">
                    <svg style="width: 20px; height: 20px; fill: currentColor; display: inline; vertical-align: middle; margin-right: 4px;" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                    Send
                </button>
            </div>
            
            <div id="loadingIndicator" style="display: none; margin-top: 12px; text-align: center; color: #8b5cf6; font-size: 14px;">
                Kate tænker...
            </div>
        </div>
        
        <script>
        const chatMessages = document.getElementById('chatMessages');
        const userMessage = document.getElementById('userMessage');
        const sendMessage = document.getElementById('sendMessage');
        const loadingIndicator = document.getElementById('loadingIndicator');
        
        let sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        
        // Send message function
        async function sendMessageToKate() {
            const message = userMessage.value.trim();
            if (!message) return;
            
            // Add user message to chat
            const userDiv = document.createElement('div');
            userDiv.style.cssText = 'display: flex; gap: 12px; margin-bottom: 1rem; flex-direction: row-reverse;';
            userDiv.innerHTML = `
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #60a5fa, #2563eb); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; flex-shrink: 0;">U</div>
                <div style="flex: 1; background: #e0f2fe; padding: 12px 16px; border-radius: 12px;">
                    <p style="margin: 0; color: #1e293b; line-height: 1.6;">${message}</p>
                </div>
            `;
            chatMessages.appendChild(userDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            userMessage.value = '';
            loadingIndicator.style.display = 'block';
            
            try {
                const response = await fetch('/wp-json/kate/v1/chat', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        message: message,
                        session_id: sessionId
                    })
                });
                
                const data = await response.json();
                loadingIndicator.style.display = 'none';
                
                if (data.response) {
                    // Add Kate's response
                    const kateDiv = document.createElement('div');
                    kateDiv.style.cssText = 'display: flex; gap: 12px; margin-bottom: 1rem;';
                    kateDiv.innerHTML = `
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #8b5cf6, #6366f1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; flex-shrink: 0;">K</div>
                        <div style="flex: 1; background: white; padding: 12px 16px; border-radius: 12px; border: 1px solid #e0f2fe;">
                            <p style="margin: 0; color: #1e293b; line-height: 1.6;">${data.response}</p>
                        </div>
                    `;
                    chatMessages.appendChild(kateDiv);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            } catch (error) {
                console.error('Kate chat error:', error);
                loadingIndicator.style.display = 'none';
                alert('Fejl ved kommunikation med Kate. Prøv igen.');
            }
        }
        
        // Event listeners
        sendMessage.addEventListener('click', sendMessageToKate);
        userMessage.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessageToKate();
            }
        });
        </script>
    </main>
</div>

<?php get_footer(); ?>
