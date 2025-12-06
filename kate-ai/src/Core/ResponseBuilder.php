<?php
namespace KateAI\Core;

class ResponseBuilder {
    private $knowledgeBase;
    private $config;
    private $webSearcher;
    private $conversationalModule;
    private $confidenceThreshold = 0.98; // 98% confidence requirement
    
    public function __construct(KnowledgeBase $knowledgeBase, Config $config, WebSearcher $webSearcher = null) {
        $this->knowledgeBase = $knowledgeBase;
        $this->config = $config;
        $this->webSearcher = $webSearcher;
        $this->conversationalModule = new ConversationalModule();
    }
    
    public function buildResponse($intentId, $confidence, $context = []) {
        // Handle conversational responses
        if ($intentId === 'CONVERSATIONAL' && isset($context['is_conversational'])) {
            $userMood = $this->conversationalModule->detectMood($context['original_message'] ?? '');
            $conversationalContext = ['user_mood' => $userMood];
            
            $response = $this->conversationalModule->generateResponse(
                $context['original_message'] ?? '', 
                $conversationalContext
            );
            
            return [
                'intent_id' => 'CONVERSATIONAL',
                'confidence' => 0.95,
                'title' => 'Kate',
                'summary' => $response,
                'details' => [],
                'law_refs' => [],
                'links' => [],
                'follow_up_questions' => [],
                'is_conversational' => true,
                'timestamp' => time()
            ];
        }
        
        // 98% CONFIDENCE SAFETY NET
        // If confidence is below 98%, add disclaimer
        $needsDisclaimer = $confidence < $this->confidenceThreshold;
        
        // Handle unknown intent - try web search
        if ($intentId === 'INTENT_UNKNOWN' || $confidence < 0.3) {
            return $this->buildUnknownResponseWithSearch($context['original_message'] ?? '', $context);
        }
        
        // Get intent data
        $intentData = $this->knowledgeBase->getIntentData($intentId);
        if (!$intentData) {
            return $this->buildUnknownResponse();
        }
        
        // Build response structure
        $response = [
            'intent_id' => $intentId,
            'confidence' => $confidence,
            'title' => $intentData['title'] ?? 'Svar fra Kate',
            'summary' => $intentData['answer_short'] ?? '',
            'details' => [],
            'law_refs' => [],
            'links' => [],
            'follow_up_questions' => [],
            'disclaimer' => $this->config->get('disclaimer'),
            'timestamp' => time()
        ];
        
        // Add detailed answer
        if (isset($intentData['answer_long']) && is_array($intentData['answer_long'])) {
            $response['details'] = $intentData['answer_long'];
        } elseif (isset($intentData['answer_long'])) {
            $response['details'] = [$intentData['answer_long']];
        }
        
        // Add law references
        if (isset($intentData['law_refs']) && is_array($intentData['law_refs'])) {
            foreach ($intentData['law_refs'] as $lawRef) {
                $response['law_refs'][] = [
                    'law' => $lawRef['law'] ?? $lawRef['law_id'] ?? '',
                    'paragraph' => $lawRef['paragraph'] ?? '',
                    'note' => $lawRef['note'] ?? '',
                    'url' => $lawRef['url'] ?? $this->buildRetsinfoUrl($lawRef['law_id'] ?? '')
                ];
            }
        }
        
        // Add external links
        if (isset($intentData['links']) && is_array($intentData['links'])) {
            $response['links'] = $intentData['links'];
        }
        
        // Add follow-up questions
        if (isset($intentData['follow_up_questions']) && is_array($intentData['follow_up_questions'])) {
            $response['follow_up_questions'] = $intentData['follow_up_questions'];
        }
        
        // Add quick actions if available
        if (isset($intentData['quick_actions'])) {
            $response['quick_actions'] = $intentData['quick_actions'];
        }
        
        // Add related flows
        if (isset($intentData['related_flow'])) {
            $flow = $this->knowledgeBase->getFlow($intentData['related_flow']);
            if ($flow) {
                $response['flow'] = [
                    'id' => $intentData['related_flow'],
                    'title' => $flow['title'] ?? '',
                    'steps' => $flow['steps'] ?? []
                ];
            }
        }
        
        // ADD EMPATHY IF USER IS STRUGGLING
        if (isset($context['user_mood']) && $context['user_mood'] === 'negative') {
            $response['summary'] = $this->conversationalModule->addEmpathy(
                $response['summary'], 
                $context
            );
        }
        
        // 98% CONFIDENCE SAFETY NET
        if ($needsDisclaimer) {
            $response['confidence_notice'] = '⚠️ Jeg er ' . round($confidence * 100) . '% sikker på dette svar. ' .
                'Hvis du har brug for 100% sikkerhed, bør du kontakte en advokat eller juridisk rådgiver. ' .
                'Mit svar er baseret på gældende lovgivning, men din specifikke situation kan være anderledes.';
        }
        
        // ALWAYS add disclaimer for legal advice
        $response['disclaimer'] = $this->config->get('disclaimer') ?? 
            '💡 Jeg giver juridisk information baseret på dansk lovgivning, men dette erstatter ikke professionel juridisk rådgivning. ' .
            'Ved komplicerede sager anbefaler jeg at du kontakter en advokat.';
        
        // ENHANCED: Add web search supplement for better answers
        if ($this->webSearcher && isset($context['original_message'])) {
            $this->enhanceResponseWithWebSearch($response, $context['original_message'], $intentData, $context);
        }
        
        return $response;
    }
    
    private function buildUnknownResponse() {
        return [
            'intent_id' => 'INTENT_UNKNOWN',
            'confidence' => 0,
            'title' => 'Jeg forstod ikke helt dit spørgsmål',
            'summary' => 'Jeg er stadig ved at lære, og forstod ikke helt hvad du spurgte om. Kan du formulere dit spørgsmål anderledes?',
            'details' => [
                'Jeg kan hjælpe med spørgsmål om:',
                '• Anbringelse uden samtykke',
                '• Klager over afgørelser',
                '• Aktindsigt',
                '• Handleplaner',
                '• Børnesamtaler',
                '• Forældremyndighed og samvær',
                '• Undersøgelser efter Barnets Lov',
                '• Bisidder og partsrepræsentant',
                '',
                'Prøv at stille et mere specifikt spørgsmål om ét af disse emner.'
            ],
            'follow_up_questions' => [
                'Hvordan klager jeg over en afgørelse?',
                'Hvad er mine rettigheder ved en børnesamtale?',
                'Hvordan søger jeg om aktindsigt?',
                'Hvad skal der stå i en handleplan?'
            ],
            'disclaimer' => $this->config->get('disclaimer'),
            'timestamp' => time()
        ];
    }
    
    /**
     * Build unknown response with web search results
     */
    private function buildUnknownResponseWithSearch($message, $context = []) {
        $response = $this->buildUnknownResponse();
        
        // If web searcher is available and message is not empty, try searching
        if ($this->webSearcher && !empty($message)) {
            $country = $context['user_country'] ?? 'da_DK';
            $searchResults = $this->webSearcher->search($message, $country, ['retsinformation', 'ankestyrelsen', 'borger'], 2);
            
            if (!empty($searchResults['results']) && $searchResults['total_results'] > 0) {
                $response['title'] = 'Jeg fandt noget relevant information online';
                $response['summary'] = 'Jeg kendte ikke svaret direkte, men har fundet relevante kilder til dig:';
                $response['web_search'] = $searchResults;
                
                // Add web results to details
                $response['details'] = ['Jeg har søgt på følgende kilder for dig:', ''];
                
                foreach ($searchResults['results'] as $sourceId => $sourceData) {
                    if (!empty($sourceData['items'])) {
                        $response['details'][] = '📚 ' . $sourceData['source'] . ':';
                        foreach ($sourceData['items'] as $item) {
                            $response['details'][] = '• ' . $item['title'];
                            $response['details'][] = '  ' . $item['snippet'];
                            $response['links'][] = [
                                'title' => $item['title'],
                                'url' => $item['url'],
                                'source' => $sourceData['source']
                            ];
                        }
                        $response['details'][] = '';
                    }
                }
            }
        }
        
        return $response;
    }
    
    private function buildRetsinfoUrl($lawId) {
        $lawIds = [
            'barnets_lov' => 'https://www.retsinformation.dk/eli/lta/2022/1146',
            'forvaltningsloven' => 'https://www.retsinformation.dk/eli/lta/2022/1451',
            'persondataloven' => 'https://www.retsinformation.dk/eli/lta/2018/502',
            'retssikkerhedsloven' => 'https://www.retsinformation.dk/eli/lta/2019/1054'
        ];
        
        return $lawIds[$lawId] ?? 'https://www.retsinformation.dk/';
    }
    
    /**
     * Enhance response with additional web search results
     */
    private function enhanceResponseWithWebSearch(&$response, $message, $intentData, $context = []) {
        // Search for additional sources
        $topic = $intentData['title'] ?? $message;
        $country = $context['user_country'] ?? 'da_DK';
        $searchResults = $this->webSearcher->search($topic, $country, ['ankestyrelsen', 'domstol'], 1);
        
        if (!empty($searchResults['results']) && $searchResults['total_results'] > 0) {
            // Add section for additional resources
            if (!isset($response['additional_resources'])) {
                $response['additional_resources'] = [
                    'title' => 'Yderligere ressourcer fra officielle kilder',
                    'sources' => []
                ];
            }
            
            foreach ($searchResults['results'] as $sourceId => $sourceData) {
                if (!empty($sourceData['items'])) {
                    foreach ($sourceData['items'] as $item) {
                        $response['additional_resources']['sources'][] = [
                            'title' => $item['title'],
                            'snippet' => $item['snippet'],
                            'url' => $item['url'],
                            'source' => $sourceData['source'],
                            'date' => $item['date'] ?? null
                        ];
                    }
                }
            }
        }
    }
}
