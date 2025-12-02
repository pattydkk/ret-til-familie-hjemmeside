<?php
namespace KateAI\Core;

class Logger {
    private $config;
    private $wpdb;
    
    public function __construct(Config $config, $wpdb = null) {
        $this->config = $config;
        $this->wpdb = $wpdb;
    }
    
    public function log($sessionId, $message, $intent, $response, $confidence = null) {
        if (!$this->config->get('log_enabled')) {
            return;
        }
        
        // Log to database if WordPress is available
        if ($this->wpdb) {
            $table = $this->wpdb->prefix . 'rtf_platform_kate_chat';
            $this->wpdb->insert($table, [
                'user_id' => $this->getUserIdFromSession($sessionId),
                'message' => substr($message, 0, 1000),
                'response' => substr(json_encode($response), 0, 5000),
                'created_at' => current_time('mysql')
            ]);
        }
        
        // Also log to file for debugging
        if ($this->config->get('log_level') === 'debug') {
            $logFile = dirname(__DIR__, 2) . '/logs/kate-' . date('Y-m-d') . '.log';
            $entry = sprintf(
                "[%s] Session: %s | Intent: %s | Confidence: %.2f | Message: %s\n",
                date('Y-m-d H:i:s'),
                $sessionId,
                $intent,
                $confidence ?? 0,
                substr($message, 0, 100)
            );
            file_put_contents($logFile, $entry, FILE_APPEND);
        }
    }
    
    private function getUserIdFromSession($sessionId) {
        if (isset($_SESSION['rtf_user_id'])) {
            return $_SESSION['rtf_user_id'];
        }
        return 0;
    }
}
