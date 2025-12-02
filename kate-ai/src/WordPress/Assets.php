<?php

namespace KateAI\WordPress;

class Assets {
    
    public function enqueue() {
        // Kate AI chat CSS
        wp_enqueue_style(
            'kate-ai-chat',
            KATE_AI_URL . '/assets/css/kate-chat.css',
            [],
            KATE_AI_VERSION
        );
        
        // Kate AI chat JS
        wp_enqueue_script(
            'kate-ai-chat',
            KATE_AI_URL . '/assets/js/kate-chat.js',
            ['jquery'],
            KATE_AI_VERSION,
            true
        );
        
        // Pass config to JS
        wp_localize_script('kate-ai-chat', 'kateAI', [
            'ajaxUrl' => rest_url('kate/v1/message'),
            'analyzeUrl' => rest_url('kate/v1/analyze'),
            'nonce' => wp_create_nonce('wp_rest'),
            'themeColor' => get_option('kate_ai_theme_color', '#2563eb'),
            'disclaimer' => get_option('kate_ai_disclaimer', 'Kate er en AI-assistent og erstatter ikke juridisk rådgivning.')
        ]);
    }
}
