<?php
/**
 * Kate AI Chat Widget Template
 */

$title = isset($atts['title']) ? esc_html($atts['title']) : 'Kate - Din AI Assistent';
?>

<div id="kate-chat-widget" class="kate-chat-widget">
    <div class="kate-chat-header">
        <div class="kate-avatar">🤖</div>
        <div>
            <h3><?php echo $title; ?></h3>
            <div class="kate-status">
                <span class="kate-status-dot"></span>
                <span>Online</span>
            </div>
        </div>
    </div>
    
    <div class="kate-quick-actions">
        <button class="kate-quick-action" data-action="klage">🗂️ Klage</button>
        <button class="kate-quick-action" data-action="aktindsigt">📄 Aktindsigt</button>
        <button class="kate-quick-action" data-action="anbringelse">👨‍👩‍👧 Anbringelse</button>
        <button class="kate-quick-action" data-action="handleplan">📋 Handleplan</button>
    </div>
    
    <div class="kate-messages">
        <!-- Messages will be appended here by JavaScript -->
    </div>
    
    <div class="kate-input-area">
        <textarea class="kate-input" placeholder="Skriv dit spørgsmål her..." rows="1"></textarea>
        <button class="kate-send-btn">Send</button>
    </div>
</div>
