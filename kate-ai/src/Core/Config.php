<?php
namespace KateAI\Core;

class Config {
    private $settings = [];
    
    public function __construct($settings = []) {
        $this->settings = array_merge([
            'language' => 'da',
            'log_enabled' => true,
            'log_level' => 'info',
            'max_response_length' => 2000,
            'intent_threshold' => 0.3,
            'session_timeout' => 3600,
            'disclaimer' => 'Kate giver generel vejledning baseret på danske love og socialfaglige principper. Dette er IKKE juridisk rådgivning eller erstatning for konkret sagsbehandling. Kontakt altid en advokat eller socialrådgiver ved specifikke juridiske spørgsmål.'
        ], $settings);
    }
    
    public function get($key, $default = null) {
        return $this->settings[$key] ?? $default;
    }
    
    public function set($key, $value) {
        $this->settings[$key] = $value;
    }
    
    public function all() {
        return $this->settings;
    }
}
