<?php
namespace KateAI\Core;

class KnowledgeBase {
    private $dataPath;
    private $cache = [];
    
    public function __construct($dataPath) {
        $this->dataPath = rtrim($dataPath, '/');
    }
    
    private function loadJson($filename) {
        if (isset($this->cache[$filename])) {
            return $this->cache[$filename];
        }
        
        $filepath = $this->dataPath . '/' . $filename;
        if (!file_exists($filepath)) {
            return null;
        }
        
        $content = file_get_contents($filepath);
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Kate AI: Failed to parse $filename - " . json_last_error_msg());
            return null;
        }
        
        $this->cache[$filename] = $data;
        return $data;
    }
    
    public function getAllIntents() {
        return $this->loadJson('intents.json') ?? [];
    }
    
    public function getIntentData($intentId) {
        $intents = $this->getAllIntents();
        foreach ($intents as $intent) {
            if ($intent['intent_id'] === $intentId) {
                return $intent;
            }
        }
        return null;
    }
    
    public function getLawSummary($lawId, $paragraph = null) {
        $laws = $this->loadJson('laws_' . $lawId . '.json');
        if (!$laws) {
            return null;
        }
        
        if ($paragraph) {
            foreach ($laws as $law) {
                if (isset($law['paragraph']) && $law['paragraph'] === $paragraph) {
                    return $law;
                }
            }
            return null;
        }
        
        return $laws;
    }
    
    public function getFlow($flowId) {
        return $this->loadJson('flows_' . $flowId . '.json');
    }
    
    public function getEthicsPrinciple($id) {
        $ethics = $this->loadJson('ethics_social.json') ?? [];
        foreach ($ethics as $principle) {
            if ($principle['id'] === $id) {
                return $principle;
            }
        }
        return null;
    }
    
    public function getAllFlows() {
        $flows = [];
        $files = glob($this->dataPath . '/flows_*.json');
        foreach ($files as $file) {
            $flowId = str_replace(['flows_', '.json'], '', basename($file));
            $flows[$flowId] = $this->loadJson(basename($file));
        }
        return $flows;
    }
}
