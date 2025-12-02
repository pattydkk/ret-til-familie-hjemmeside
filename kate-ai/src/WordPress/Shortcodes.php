<?php

namespace KateAI\WordPress;

use KateAI\Core\KateKernel;

class Shortcodes {
    private $kernel;
    
    public function __construct(KateKernel $kernel) {
        $this->kernel = $kernel;
    }
    
    public function register() {
        add_shortcode('kate_ai_assistant', [$this, 'render_chat_widget']);
    }
    
    public function render_chat_widget($atts) {
        $atts = shortcode_atts([
            'title' => 'Kate - Din AI Assistent',
            'position' => 'bottom-right',
            'theme' => 'pastel-blue'
        ], $atts);
        
        ob_start();
        include KATE_AI_PATH . '/templates/chat-widget.php';
        return ob_get_clean();
    }
}
