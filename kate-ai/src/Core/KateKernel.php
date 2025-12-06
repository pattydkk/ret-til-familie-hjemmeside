<?php
namespace KateAI\Core;

class KateKernel {
    private $config;
    private $knowledgeBase;
    private $intentDetector;
    private $dialogueManager;
    private $responseBuilder;
    private $normalizer;
    private $logger;
    private $webSearcher;
    private $databaseManager;
    private $languageDetector;
    private $lawDatabase;
    
    public function __construct(Config $config, KnowledgeBase $knowledgeBase, Logger $logger = null, WebSearcher $webSearcher = null, DatabaseManager $databaseManager = null, LanguageDetector $languageDetector = null, LawDatabase $lawDatabase = null) {
        $this->config = $config;
        $this->knowledgeBase = $knowledgeBase;
        $this->logger = $logger;
        $this->webSearcher = $webSearcher ?? new WebSearcher($logger);
        $this->databaseManager = $databaseManager ?? new DatabaseManager();
        $this->languageDetector = $languageDetector ?? new LanguageDetector($databaseManager, $logger);
        $this->lawDatabase = $lawDatabase ?? new LawDatabase($databaseManager, $logger);
        
        // Initialize components
        $this->normalizer = new Normalizer();
        $this->intentDetector = new IntentDetector(
            $knowledgeBase,
            $this->normalizer,
            $config->get('intent_threshold')
        );
        $this->dialogueManager = new DialogueManager();
        $this->responseBuilder = new ResponseBuilder($knowledgeBase, $config, $this->webSearcher);
    }
    
    /**
     * Main API method - handles a user message and returns response
     * @param string $sessionId Session identifier
     * @param string $message User message
     * @param array $context Additional context (e.g., user_id for GDPR)
     */
    public function handleMessage($sessionId, $message, array $context = []) {
        // Validate input
        if (empty($sessionId) || empty($message)) {
            return $this->errorResponse('Invalid input');
        }
        
        // CRITICAL: Validate user_id for multi-user isolation
        if (!isset($context['user_id'])) {
            if ($this->logger) {
                $this->logger->error('Missing user_id in context - potential security issue');
            }
            return $this->errorResponse('Authentication required');
        }
        
        $userId = $context['user_id'];
        
        // MULTI-USER ISOLATION: Validate session belongs to user
        if (!$this->validateSessionOwnership($sessionId, $userId)) {
            if ($this->logger) {
                $this->logger->warning("Session ownership validation failed: session=$sessionId, user=$userId");
            }
            return $this->errorResponse('Invalid session');
        }
        
        // LANGUAGE DETECTION: Get user's language preference
        $userLanguage = $this->languageDetector->getUserLanguage($userId);
        $userCountry = $this->languageDetector->getCountryFromLanguage($userLanguage);
        
        // Load language-specific intents if needed
        if ($userLanguage !== 'da_DK') {
            $languageIntents = $this->languageDetector->loadIntents($userLanguage);
            if (!empty($languageIntents)) {
                // Temporarily replace knowledge base intents with language-specific ones
                $this->knowledgeBase->setIntents($languageIntents);
            }
        }
        
        // Store user context (with language info)
        $this->dialogueManager->setContext($sessionId, 'user_id', $userId);
        $this->dialogueManager->setContext($sessionId, 'language', $userLanguage);
        $this->dialogueManager->setContext($sessionId, 'country', $userCountry);
        
        // Get or create session
        $session = $this->dialogueManager->getSession($sessionId);
        
        // Detect intent (with spelling correction and conversational support)
        $intentResult = $this->intentDetector->detectIntent($message);
        $intentId = $intentResult['intent_id'];
        $confidence = $intentResult['confidence'];
        
        // Check if conversational
        $isConversational = isset($intentResult['is_conversational']) && $intentResult['is_conversational'];
        
        // Get context from session
        $sessionContext = $this->dialogueManager->getContext($sessionId);
        
        // Add original message to context for web search and conversational
        $sessionContext['original_message'] = $message;
        $sessionContext['is_conversational'] = $isConversational;
        
        // DETECT USER MOOD for empathy support
        $conversationalModule = new ConversationalModule();
        $userMood = $conversationalModule->detectMood($message);
        $sessionContext['user_mood'] = $userMood;
        
        // Add law database access with user's country
        $sessionContext['law_database'] = $this->lawDatabase;
        $sessionContext['user_country'] = $userCountry;
        
        // Build response (with 98% confidence safety net)
        $startTime = microtime(true);
        $response = $this->responseBuilder->buildResponse($intentId, $confidence, $sessionContext);
        $responseTime = round((microtime(true) - $startTime) * 1000); // ms
        
        // Add conversation metadata
        $response['session_id'] = $sessionId;
        $response['message_received'] = $message;
        $response['processing_time'] = $responseTime;
        
        // Check if web search was used
        $webSearchUsed = isset($response['web_search']) || isset($response['additional_resources']);
        $sources = [];
        if ($webSearchUsed) {
            if (isset($response['web_search']['results'])) {
                $sources = array_keys($response['web_search']['results']);
            }
        }
        
        // DATABASE: Save chat interaction
        if ($this->databaseManager && isset($context['user_id'])) {
            $this->databaseManager->saveChatMessage(
                $sessionId,
                $context['user_id'],
                $message,
                $response,
                $intentId,
                $confidence,
                $webSearchUsed,
                $sources
            );
            
            // Cache intent if successful
            if ($intentId !== 'INTENT_UNKNOWN') {
                $intentData = $this->knowledgeBase->getIntentData($intentId);
                if ($intentData) {
                    $this->databaseManager->cacheIntent($intentId, $intentData);
                }
            }
            
            // Save session context
            $conversationHistory = $this->dialogueManager->getHistory($sessionId);
            $this->databaseManager->saveSession(
                $sessionId,
                $context['user_id'],
                $sessionContext,
                $intentId,
                $conversationHistory
            );
            
            // Analytics
            $this->databaseManager->logAnalytics(
                $context['user_id'],
                'chat_message',
                $intentId,
                $confidence,
                $webSearchUsed,
                $responseTime,
                true
            );
        }
        
        // Update session
        $this->dialogueManager->addToHistory($sessionId, $message, $intentId, $response);
        
        // Update context based on intent
        $this->updateContextFromIntent($sessionId, $intentId, $intentResult);
        
        // Log interaction
        if ($this->logger) {
            $this->logger->log($sessionId, $message, $intentId, $response, $confidence);
        }
        
        return $response;
    }
    
    /**
     * Analyze document for legal and social work violations
     * Returns analysis with 98% confidence targeting
     * @param string $documentContent Document text
     * @param string $documentType Type of document
     * @param array $context Additional context (e.g., user_id for GDPR)
     */
    public function analyzeDocument($documentContent, $documentType = 'general', array $context = []) {
        $analysis = [
            'document_type' => $documentType,
            'analyzed_at' => date('Y-m-d H:i:s'),
            'confidence' => 0.98, // Target confidence level
            'violations' => [],
            'recommendations' => [],
            'legal_issues' => [],
            'social_work_issues' => [],
            'overall_score' => 0
        ];
        
        // GDPR: Log user_id if provided (for audit trail)
        if (isset($context['user_id'])) {
            $analysis['analyzed_by_user'] = $context['user_id'];
        }
        
        // Normalize document text
        $normalizedContent = $this->normalizer->normalize($documentContent);
        $keywords = $this->normalizer->extractKeywords($documentContent);
        
        // Check for common legal violations
        $legalChecks = [
            'missing_reasoning' => [
                'patterns' => ['uden begrundelse', 'ikke begrundet', 'mangler begrundelse'],
                'severity' => 'high',
                'law_ref' => 'Forvaltningsloven § 22'
            ],
            'missing_complaint_info' => [
                'patterns' => ['klagevejledning'],
                'severity' => 'high',
                'law_ref' => 'Forvaltningsloven § 25',
                'check_absence' => true
            ],
            'missing_deadline' => [
                'patterns' => ['frist', 'inden'],
                'severity' => 'medium',
                'check_absence' => true
            ],
            'missing_party_hearing' => [
                'patterns' => ['partshøring', 'høring'],
                'severity' => 'high',
                'law_ref' => 'Forvaltningsloven § 19',
                'check_absence' => true
            ]
        ];
        
        foreach ($legalChecks as $checkId => $check) {
            $found = false;
            foreach ($check['patterns'] as $pattern) {
                if (strpos($normalizedContent, $pattern) !== false) {
                    $found = true;
                    break;
                }
            }
            
            // Check if absence is a violation
            if (isset($check['check_absence']) && $check['check_absence'] && !$found) {
                $analysis['violations'][] = [
                    'type' => 'legal',
                    'check_id' => $checkId,
                    'severity' => $check['severity'],
                    'description' => $this->getViolationDescription($checkId),
                    'law_reference' => $check['law_ref'] ?? '',
                    'recommendation' => $this->getViolationRecommendation($checkId)
                ];
            }
        }
        
        // Check for social work quality indicators
        $socialWorkChecks = [
            'child_perspective' => [
                'patterns' => ['barnets perspektiv', 'barnets synspunkt', 'barnets oplevelse'],
                'importance' => 'critical'
            ],
            'concrete_concerns' => [
                'patterns' => ['konkret bekymring', 'dokumenteret'],
                'importance' => 'high'
            ],
            'least_intervention' => [
                'patterns' => ['mindste indgreb', 'proportionalitet'],
                'importance' => 'high'
            ]
        ];
        
        foreach ($socialWorkChecks as $checkId => $check) {
            $found = false;
            foreach ($check['patterns'] as $pattern) {
                if (strpos($normalizedContent, $pattern) !== false) {
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $analysis['social_work_issues'][] = [
                    'check_id' => $checkId,
                    'importance' => $check['importance'],
                    'description' => $this->getSocialWorkIssueDescription($checkId),
                    'recommendation' => $this->getSocialWorkRecommendation($checkId)
                ];
            }
        }
        
        // Calculate overall score
        $totalChecks = count($legalChecks) + count($socialWorkChecks);
        $issues = count($analysis['violations']) + count($analysis['social_work_issues']);
        $analysis['overall_score'] = max(0, (($totalChecks - $issues) / $totalChecks) * 100);
        
        // Generate recommendations
        if ($analysis['overall_score'] < 50) {
            $analysis['recommendations'][] = 'Dokumentet har alvorlige mangler. Overvej at søge juridisk bistand.';
        } elseif ($analysis['overall_score'] < 75) {
            $analysis['recommendations'][] = 'Dokumentet har enkelte mangler som bør adresseres.';
        } else {
            $analysis['recommendations'][] = 'Dokumentet ser overordnet set acceptabelt ud, men gennemgå de specifikke punkter.';
        }
        
        return $analysis;
    }
    
    private function updateContextFromIntent($sessionId, $intentId, $intentResult) {
        // Store last intent
        $this->dialogueManager->setContext($sessionId, 'last_intent', $intentId);
        $this->dialogueManager->setContext($sessionId, 'last_confidence', $intentResult['confidence']);
        
        // Extract and store entities from intent
        // (This would be more sophisticated in a full implementation)
        if (strpos($intentId, 'ANBRINGELSE') !== false) {
            $this->dialogueManager->setContext($sessionId, 'topic', 'anbringelse');
        } elseif (strpos($intentId, 'KLAGE') !== false) {
            $this->dialogueManager->setContext($sessionId, 'topic', 'klage');
        } elseif (strpos($intentId, 'AKTINDSIGT') !== false) {
            $this->dialogueManager->setContext($sessionId, 'topic', 'aktindsigt');
        }
    }
    
    private function getProcessingTime() {
        static $startTime;
        if ($startTime === null) {
            $startTime = microtime(true);
        }
        return round((microtime(true) - $startTime) * 1000, 2); // milliseconds
    }
    
    /**
     * Validate that session belongs to user (multi-user isolation)
     */
    private function validateSessionOwnership($sessionId, $userId) {
        if (!$this->databaseManager) {
            // If no database manager, allow (for backwards compatibility)
            return true;
        }
        
        // Check if session exists and belongs to user
        $session = $this->databaseManager->getSession($sessionId);
        
        if (!$session) {
            // New session - will be created with user_id
            return true;
        }
        
        // Validate ownership
        if (isset($session['user_id']) && $session['user_id'] != $userId) {
            // Session belongs to different user - security violation
            return false;
        }
        
        return true;
    }
    
    private function errorResponse($message) {
        return [
            'error' => true,
            'message' => $message,
            'timestamp' => time()
        ];
    }
    
    private function getViolationDescription($checkId) {
        $descriptions = [
            'missing_reasoning' => 'Afgørelsen mangler eller har utilstrækkelig begrundelse',
            'missing_complaint_info' => 'Afgørelsen mangler klagevejledning',
            'missing_deadline' => 'Afgørelsen mangler klagefrister',
            'missing_party_hearing' => 'Ingen dokumentation for partshøring'
        ];
        return $descriptions[$checkId] ?? 'Ukendt violation';
    }
    
    private function getViolationRecommendation($checkId) {
        $recommendations = [
            'missing_reasoning' => 'Anmod kommunen om fyldestgørende begrundelse jf. Forvaltningsloven § 22',
            'missing_complaint_info' => 'Afgørelsen skal indeholde klagevejledning - anmod om korrekt afgørelse',
            'missing_deadline' => 'Kontakt kommunen for afklaring af klagefrister',
            'missing_party_hearing' => 'Anmod om dokumentation for partshøring eller kræv ny høring'
        ];
        return $recommendations[$checkId] ?? '';
    }
    
    private function getSocialWorkIssueDescription($checkId) {
        $descriptions = [
            'child_perspective' => 'Barnets perspektiv er ikke tilstrækkeligt beskrevet',
            'concrete_concerns' => 'Bekymringerne er ikke konkret dokumenteret',
            'least_intervention' => 'Princippet om mindste indgreb er ikke påvist'
        ];
        return $descriptions[$checkId] ?? '';
    }
    
    private function getSocialWorkRecommendation($checkId) {
        $recommendations = [
            'child_perspective' => 'Anmod om konkret beskrivelse af hvordan barnets perspektiv er inddraget',
            'concrete_concerns' => 'Kræv konkret og dokumenteret beskrivelse af bekymringerne',
            'least_intervention' => 'Anmod om begrundelse for hvorfor mindre indgribende tiltag ikke er tilstrækkelige'
        ];
        return $recommendations[$checkId] ?? '';
    }
}
