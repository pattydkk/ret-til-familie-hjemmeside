<?php

namespace KateAI\WordPress;

use KateAI\Core\KateKernel;
use KateAI\Core\Config;
use KateAI\Core\KnowledgeBase;
use KateAI\Core\Logger;

class WPAdapter {
    private $kernel;
    
    public function init() {
        // Initialize Core Engine
        $config = new Config([
            'language' => 'da',
            'confidence_threshold' => 0.3,
            'max_history' => 10,
            'enable_logging' => true
        ]);
        
        $kb_path = KATE_AI_PATH . '/data';
        $kb = new KnowledgeBase($kb_path);
        
        $this->kernel = new KateKernel($config, $kb);
        
        // Register WordPress integrations
        $this->register_rest_routes();
        $this->register_shortcodes();
        $this->register_admin_pages();
        $this->enqueue_assets();
    }
    
    private function register_rest_routes() {
        $rest_controller = new RestController($this->kernel);
        add_action('rest_api_init', [$rest_controller, 'register_routes']);
    }
    
    private function register_shortcodes() {
        $shortcodes = new Shortcodes($this->kernel);
        add_action('init', [$shortcodes, 'register']);
    }
    
    private function register_admin_pages() {
        $admin_page = new AdminPage();
        add_action('admin_menu', [$admin_page, 'register']);
    }
    
    private function enqueue_assets() {
        $assets = new Assets();
        add_action('wp_enqueue_scripts', [$assets, 'enqueue']);
    }
    
    public function get_kernel() {
        return $this->kernel;
    }
}
