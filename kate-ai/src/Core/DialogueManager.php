<?php
namespace KateAI\Core;

class DialogueManager {
    private $sessions = [];
    
    public function getSession($sessionId) {
        if (!isset($this->sessions[$sessionId])) {
            $this->sessions[$sessionId] = [
                'id' => $sessionId,
                'created_at' => time(),
                'last_activity' => time(),
                'context' => [],
                'history' => [],
                'current_flow' => null,
                'flow_step' => 0
            ];
        }
        
        $this->sessions[$sessionId]['last_activity'] = time();
        return $this->sessions[$sessionId];
    }
    
    public function updateSession($sessionId, $data) {
        if (!isset($this->sessions[$sessionId])) {
            $this->getSession($sessionId);
        }
        
        $this->sessions[$sessionId] = array_merge($this->sessions[$sessionId], $data);
        $this->sessions[$sessionId]['last_activity'] = time();
    }
    
    public function addToHistory($sessionId, $userMessage, $intentId, $response) {
        if (!isset($this->sessions[$sessionId])) {
            $this->getSession($sessionId);
        }
        
        $this->sessions[$sessionId]['history'][] = [
            'timestamp' => time(),
            'user_message' => $userMessage,
            'intent_id' => $intentId,
            'response_summary' => isset($response['summary']) ? $response['summary'] : ''
        ];
        
        // Keep only last 10 exchanges
        if (count($this->sessions[$sessionId]['history']) > 10) {
            array_shift($this->sessions[$sessionId]['history']);
        }
    }
    
    public function setContext($sessionId, $key, $value) {
        if (!isset($this->sessions[$sessionId])) {
            $this->getSession($sessionId);
        }
        
        $this->sessions[$sessionId]['context'][$key] = $value;
    }
    
    public function getContext($sessionId, $key = null) {
        $session = $this->getSession($sessionId);
        
        if ($key === null) {
            return $session['context'];
        }
        
        return $session['context'][$key] ?? null;
    }
    
    public function clearSession($sessionId) {
        unset($this->sessions[$sessionId]);
    }
}
