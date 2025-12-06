<?php
namespace KateAI\Core;

class IntentDetector {
    private $knowledgeBase;
    private $normalizer;
    private $threshold;
    private $spellingCorrector;
    private $conversationalModule;
    
    public function __construct(KnowledgeBase $knowledgeBase, Normalizer $normalizer, $threshold = 0.3) {
        $this->knowledgeBase = $knowledgeBase;
        $this->normalizer = $normalizer;
        $this->threshold = $threshold;
        $this->spellingCorrector = new SpellingCorrector();
        $this->conversationalModule = new ConversationalModule();
    }
    
    public function detectIntent($message) {
        // First check if it's a casual conversation
        if ($this->conversationalModule->isConversational($message)) {
            return [
                'intent_id' => 'CONVERSATIONAL',
                'confidence' => 0.95,
                'is_conversational' => true,
                'message' => $message
            ];
        }
        
        // Correct spelling mistakes
        $correctedMessage = $this->spellingCorrector->correct($message);
        
        $normalizedMessage = $this->normalizer->normalize($correctedMessage);
        $keywords = $this->normalizer->extractKeywords($correctedMessage);
        
        $intents = $this->knowledgeBase->getAllIntents();
        $scores = [];
        
        foreach ($intents as $intent) {
            $score = 0;
            
            // 1. Check regex patterns (highest priority)
            if (isset($intent['regex']) && is_array($intent['regex'])) {
                foreach ($intent['regex'] as $pattern) {
                    if (preg_match('/' . $pattern . '/i', $normalizedMessage)) {
                        $score += 0.5; // Regex match is strong signal
                        break;
                    }
                }
            }
            
            // 2. Check exact keyword matches
            if (isset($intent['keywords']) && is_array($intent['keywords'])) {
                foreach ($intent['keywords'] as $keyword) {
                    $keywordNormalized = $this->normalizer->normalize($keyword);
                    
                    // Exact phrase match
                    if (strpos($normalizedMessage, $keywordNormalized) !== false) {
                        $score += 0.3;
                    }
                    
                    // Individual keyword matches
                    $intentWords = explode(' ', $keywordNormalized);
                    $matchCount = count(array_intersect($intentWords, $keywords));
                    if ($matchCount > 0) {
                        $score += ($matchCount / count($intentWords)) * 0.2;
                    }
                }
            }
            
            // 3. Topic relevance
            if (isset($intent['topic'])) {
                if (strpos($normalizedMessage, $intent['topic']) !== false) {
                    $score += 0.1;
                }
            }
            
            // 4. Question type detection
            $questionTypes = [
                'hvordan' => 'how',
                'hvornår' => 'when',
                'hvad' => 'what',
                'hvor' => 'where',
                'hvorfor' => 'why',
                'kan jeg' => 'can',
                'skal jeg' => 'must',
                'må jeg' => 'may'
            ];
            
            foreach ($questionTypes as $danishQ => $type) {
                if (strpos($normalizedMessage, $danishQ) !== false) {
                    if (isset($intent['answer_type']) && $intent['answer_type'] === $type) {
                        $score += 0.1;
                    }
                }
            }
            
            if ($score > 0) {
                $scores[$intent['intent_id']] = $score;
            }
        }
        
        // Sort by score descending
        arsort($scores);
        
        // Get best match
        if (empty($scores)) {
            return ['intent_id' => 'INTENT_UNKNOWN', 'confidence' => 0];
        }
        
        $bestIntentId = array_key_first($scores);
        $bestScore = $scores[$bestIntentId];
        
        // Check threshold
        if ($bestScore < $this->threshold) {
            return ['intent_id' => 'INTENT_UNKNOWN', 'confidence' => $bestScore];
        }
        
        return [
            'intent_id' => $bestIntentId,
            'confidence' => min($bestScore, 1.0),
            'alternatives' => array_slice($scores, 1, 2, true) // Top 2 alternatives
        ];
    }
}
