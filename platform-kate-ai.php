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

<div class="platform-layout">
    <aside class="platform-sidebar">
        <h3>Platform Menu</h3>
        <ul class="platform-nav">
            <li><a href="<?php echo home_url('/platform-profil'); ?>">
                <svg style="width: 18px; height: 18px; fill: currentColor; display: inline; vertical-align: middle; margin-right: 6px;" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                Profil
            </a></li>
            <li><a href="<?php echo home_url('/platform-vaeg'); ?>">
                <svg style="width: 18px; height: 18px; fill: currentColor; display: inline; vertical-align: middle; margin-right: 6px;" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                Væg
            </a></li>
            <li><a href="<?php echo home_url('/platform-billeder'); ?>">
                <svg style="width: 18px; height: 18px; fill: currentColor; display: inline; vertical-align: middle; margin-right: 6px;" viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                Billeder
            </a></li>
            <li><a href="<?php echo home_url('/platform-dokumenter'); ?>">
                <svg style="width: 18px; height: 18px; fill: currentColor; display: inline; vertical-align: middle; margin-right: 6px;" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/></svg>
                Dokumenter
            </a></li>
            <li><a href="<?php echo home_url('/platform-venner'); ?>">
                <svg style="width: 18px; height: 18px; fill: currentColor; display: inline; vertical-align: middle; margin-right: 6px;" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                Venner
            </a></li>
            <li><a href="<?php echo home_url('/platform-forum'); ?>">
                <svg style="width: 18px; height: 18px; fill: currentColor; display: inline; vertical-align: middle; margin-right: 6px;" viewBox="0 0 24 24"><path d="M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h10c.55 0 1-.45 1-1z"/></svg>
                Forum
            </a></li>
            <li><a href="<?php echo home_url('/platform-nyheder'); ?>">
                <svg style="width: 18px; height: 18px; fill: currentColor; display: inline; vertical-align: middle; margin-right: 6px;" viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg>
                Nyheder
            </a></li>
            <li><a href="<?php echo home_url('/platform-sagshjaelp'); ?>">
                <svg style="width: 18px; height: 18px; fill: currentColor; display: inline; vertical-align: middle; margin-right: 6px;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                Sagshjælp
            </a></li>
            <li><a href="<?php echo home_url('/platform-kate-ai'); ?>" class="active">
                <svg style="width: 18px; height: 18px; fill: currentColor; display: inline; vertical-align: middle; margin-right: 6px;" viewBox="0 0 24 24"><path d="M20 9V7c0-1.1-.9-2-2-2h-3c0-1.66-1.34-3-3-3S9 3.34 9 5H6c-1.1 0-2 .9-2 2v2c-1.66 0-3 1.34-3 3s1.34 3 3 3v4c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-4c1.66 0 3-1.34 3-3s-1.34-3-3-3zm-2 10H6V7h12v12z"/></svg>
                Kate AI
            </a></li>
            <li><a href="<?php echo home_url('/platform-indstillinger'); ?>">
                <svg style="width: 18px; height: 18px; fill: currentColor; display: inline; vertical-align: middle; margin-right: 6px;" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                Indstillinger
            </a></li>
        </ul>
    </aside>
    
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
