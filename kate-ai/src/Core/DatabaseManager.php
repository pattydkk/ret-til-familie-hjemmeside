<?php
namespace KateAI\Core;

/**
 * DatabaseManager - Handles all Kate AI database operations
 * Connects Kate AI to WordPress database tables for persistent storage
 */
class DatabaseManager {
    private $wpdb;
    private $table_prefix;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_prefix = $wpdb->prefix;
    }
    
    // ============================================================================
    // CHAT HISTORY
    // ============================================================================
    
    /**
     * Save chat interaction to database
     */
    public function saveChatMessage($sessionId, $userId, $message, $response, $intentId, $confidence, $webSearchUsed = false, $sources = []) {
        $this->wpdb->insert(
            $this->table_prefix . 'rtf_platform_kate_chat',
            [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'message' => $message,
                'response' => json_encode($response, JSON_UNESCAPED_UNICODE),
                'intent_id' => $intentId,
                'confidence' => $confidence,
                'web_search_used' => $webSearchUsed ? 1 : 0,
                'sources_used' => !empty($sources) ? json_encode($sources, JSON_UNESCAPED_UNICODE) : null,
                'created_at' => current_time('mysql')
            ]
        );
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Get user's chat history
     */
    public function getChatHistory($userId, $limit = 50) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_platform_kate_chat 
             WHERE user_id = %d 
             ORDER BY created_at DESC 
             LIMIT %d",
            $userId,
            $limit
        ), ARRAY_A);
    }
    
    /**
     * Get session conversation history
     */
    public function getSessionHistory($sessionId, $limit = 20) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_platform_kate_chat 
             WHERE session_id = %s 
             ORDER BY created_at ASC 
             LIMIT %d",
            $sessionId,
            $limit
        ), ARRAY_A);
    }
    
    // ============================================================================
    // GENERATED COMPLAINTS
    // ============================================================================
    
    /**
     * Save generated complaint letter
     */
    public function saveComplaint($userId, $caseId, $municipality, $decisionDate, $caseNumber, $subject, $generatedLetter) {
        $this->wpdb->insert(
            $this->table_prefix . 'rtf_kate_complaints',
            [
                'user_id' => $userId,
                'case_id' => $caseId,
                'municipality' => $municipality,
                'decision_date' => $decisionDate,
                'case_number' => $caseNumber,
                'subject' => $subject,
                'generated_letter' => json_encode($generatedLetter, JSON_UNESCAPED_UNICODE),
                'status' => 'draft',
                'created_at' => current_time('mysql')
            ]
        );
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Get user's complaints
     */
    public function getUserComplaints($userId) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_kate_complaints 
             WHERE user_id = %d 
             ORDER BY created_at DESC",
            $userId
        ), ARRAY_A);
    }
    
    /**
     * Update complaint status
     */
    public function updateComplaintStatus($complaintId, $status, $sentAt = null) {
        $data = ['status' => $status];
        if ($sentAt) {
            $data['sent_at'] = $sentAt;
        }
        
        return $this->wpdb->update(
            $this->table_prefix . 'rtf_kate_complaints',
            $data,
            ['id' => $complaintId]
        );
    }
    
    // ============================================================================
    // DEADLINES
    // ============================================================================
    
    /**
     * Save deadline
     */
    public function saveDeadline($userId, $caseId, $deadlineType, $startDate, $deadlineDate, $daysTotal, $title, $description, $lawReference) {
        $this->wpdb->insert(
            $this->table_prefix . 'rtf_kate_deadlines',
            [
                'user_id' => $userId,
                'case_id' => $caseId,
                'deadline_type' => $deadlineType,
                'start_date' => $startDate,
                'deadline_date' => $deadlineDate,
                'days_total' => $daysTotal,
                'title' => $title,
                'description' => $description,
                'law_reference' => $lawReference,
                'status' => 'active',
                'created_at' => current_time('mysql')
            ]
        );
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Get active deadlines for user
     */
    public function getActiveDeadlines($userId) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_kate_deadlines 
             WHERE user_id = %d 
             AND status = 'active'
             AND deadline_date >= CURDATE()
             ORDER BY deadline_date ASC",
            $userId
        ), ARRAY_A);
    }
    
    /**
     * Get overdue deadlines
     */
    public function getOverdueDeadlines($userId) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_kate_deadlines 
             WHERE user_id = %d 
             AND status = 'active'
             AND deadline_date < CURDATE()
             ORDER BY deadline_date DESC",
            $userId
        ), ARRAY_A);
    }
    
    /**
     * Mark deadline as completed
     */
    public function completeDeadline($deadlineId) {
        return $this->wpdb->update(
            $this->table_prefix . 'rtf_kate_deadlines',
            [
                'status' => 'completed',
                'completed_at' => current_time('mysql')
            ],
            ['id' => $deadlineId]
        );
    }
    
    // ============================================================================
    // TIMELINE EVENTS
    // ============================================================================
    
    /**
     * Save timeline event
     */
    public function saveTimelineEvent($userId, $caseId, $eventDate, $eventType, $title, $description, $documentId = null, $legalSignificance = null) {
        $this->wpdb->insert(
            $this->table_prefix . 'rtf_kate_timeline',
            [
                'user_id' => $userId,
                'case_id' => $caseId,
                'event_date' => $eventDate,
                'event_type' => $eventType,
                'title' => $title,
                'description' => $description,
                'document_id' => $documentId,
                'legal_significance' => $legalSignificance,
                'created_at' => current_time('mysql')
            ]
        );
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Get case timeline
     */
    public function getCaseTimeline($caseId) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_kate_timeline 
             WHERE case_id = %d 
             ORDER BY event_date ASC",
            $caseId
        ), ARRAY_A);
    }
    
    /**
     * Get user's all timeline events
     */
    public function getUserTimeline($userId, $limit = 100) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_kate_timeline 
             WHERE user_id = %d 
             ORDER BY event_date DESC 
             LIMIT %d",
            $userId,
            $limit
        ), ARRAY_A);
    }
    
    // ============================================================================
    // WEB SEARCH CACHE
    // ============================================================================
    
    /**
     * Save web search result to cache
     */
    public function cacheSearchResult($queryHash, $queryText, $source, $results, $resultCount, $expiresAt) {
        // Check if exists
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$this->table_prefix}rtf_kate_search_cache 
             WHERE query_hash = %s AND source = %s",
            $queryHash,
            $source
        ));
        
        if ($existing) {
            // Update hit count
            $this->wpdb->query($this->wpdb->prepare(
                "UPDATE {$this->table_prefix}rtf_kate_search_cache 
                 SET hit_count = hit_count + 1 
                 WHERE id = %d",
                $existing
            ));
            return $existing;
        }
        
        // Insert new
        $this->wpdb->insert(
            $this->table_prefix . 'rtf_kate_search_cache',
            [
                'query_hash' => $queryHash,
                'query_text' => $queryText,
                'source' => $source,
                'results' => json_encode($results, JSON_UNESCAPED_UNICODE),
                'result_count' => $resultCount,
                'cached_at' => current_time('mysql'),
                'expires_at' => $expiresAt,
                'hit_count' => 1
            ]
        );
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Get cached search result
     */
    public function getCachedSearch($queryHash, $source) {
        $result = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_kate_search_cache 
             WHERE query_hash = %s 
             AND source = %s 
             AND expires_at > NOW()",
            $queryHash,
            $source
        ), ARRAY_A);
        
        if ($result && !empty($result['results'])) {
            $result['results'] = json_decode($result['results'], true);
        }
        
        return $result;
    }
    
    /**
     * Clean expired cache entries
     */
    public function cleanExpiredCache() {
        return $this->wpdb->query(
            "DELETE FROM {$this->table_prefix}rtf_kate_search_cache 
             WHERE expires_at < NOW()"
        );
    }
    
    // ============================================================================
    // SESSIONS & CONTEXT
    // ============================================================================
    
    /**
     * Save or update session context
     */
    public function saveSession($sessionId, $userId, $contextData, $lastIntent, $conversationHistory) {
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$this->table_prefix}rtf_kate_sessions 
             WHERE session_id = %s",
            $sessionId
        ));
        
        $data = [
            'user_id' => $userId,
            'context_data' => json_encode($contextData, JSON_UNESCAPED_UNICODE),
            'last_intent' => $lastIntent,
            'conversation_history' => json_encode($conversationHistory, JSON_UNESCAPED_UNICODE),
            'last_activity' => current_time('mysql')
        ];
        
        if ($existing) {
            $this->wpdb->update(
                $this->table_prefix . 'rtf_kate_sessions',
                $data,
                ['session_id' => $sessionId]
            );
            return $existing;
        } else {
            $data['session_id'] = $sessionId;
            $data['started_at'] = current_time('mysql');
            $this->wpdb->insert($this->table_prefix . 'rtf_kate_sessions', $data);
            return $this->wpdb->insert_id;
        }
    }
    
    /**
     * Get session
     */
    public function getSession($sessionId) {
        $result = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_kate_sessions 
             WHERE session_id = %s",
            $sessionId
        ), ARRAY_A);
        
        if ($result) {
            if (!empty($result['context_data'])) {
                $result['context_data'] = json_decode($result['context_data'], true);
            }
            if (!empty($result['conversation_history'])) {
                $result['conversation_history'] = json_decode($result['conversation_history'], true);
            }
        }
        
        return $result;
    }
    
    // ============================================================================
    // KNOWLEDGE BASE CACHE
    // ============================================================================
    
    /**
     * Cache intent from knowledge base
     */
    public function cacheIntent($intentId, $intentData) {
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$this->table_prefix}rtf_kate_knowledge_base 
             WHERE intent_id = %s",
            $intentId
        ));
        
        $data = [
            'title' => $intentData['title'] ?? '',
            'answer_short' => $intentData['answer_short'] ?? null,
            'answer_long' => isset($intentData['answer_long']) ? json_encode($intentData['answer_long'], JSON_UNESCAPED_UNICODE) : null,
            'keywords' => isset($intentData['keywords']) ? json_encode($intentData['keywords'], JSON_UNESCAPED_UNICODE) : null,
            'law_refs' => isset($intentData['law_refs']) ? json_encode($intentData['law_refs'], JSON_UNESCAPED_UNICODE) : null,
            'external_links' => isset($intentData['external_links']) ? json_encode($intentData['external_links'], JSON_UNESCAPED_UNICODE) : null,
            'follow_up_questions' => isset($intentData['follow_up_questions']) ? json_encode($intentData['follow_up_questions'], JSON_UNESCAPED_UNICODE) : null,
            'category' => $intentData['category'] ?? null
        ];
        
        if ($existing) {
            $data['usage_count'] = $this->wpdb->get_var($this->wpdb->prepare(
                "SELECT usage_count FROM {$this->table_prefix}rtf_kate_knowledge_base WHERE id = %d",
                $existing
            )) + 1;
            $data['last_used'] = current_time('mysql');
            
            $this->wpdb->update(
                $this->table_prefix . 'rtf_kate_knowledge_base',
                $data,
                ['intent_id' => $intentId]
            );
            return $existing;
        } else {
            $data['intent_id'] = $intentId;
            $data['usage_count'] = 1;
            $data['last_used'] = current_time('mysql');
            $this->wpdb->insert($this->table_prefix . 'rtf_kate_knowledge_base', $data);
            return $this->wpdb->insert_id;
        }
    }
    
    /**
     * Get most used intents
     */
    public function getMostUsedIntents($limit = 10) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_kate_knowledge_base 
             ORDER BY usage_count DESC 
             LIMIT %d",
            $limit
        ), ARRAY_A);
    }
    
    // ============================================================================
    // ANALYTICS
    // ============================================================================
    
    /**
     * Log Kate AI action for analytics
     */
    public function logAnalytics($userId, $actionType, $intentId, $confidence, $webSearchTriggered, $responseTimeMs, $success = true, $errorMessage = null) {
        $this->wpdb->insert(
            $this->table_prefix . 'rtf_kate_analytics',
            [
                'user_id' => $userId,
                'action_type' => $actionType,
                'intent_id' => $intentId,
                'confidence' => $confidence,
                'web_search_triggered' => $webSearchTriggered ? 1 : 0,
                'response_time_ms' => $responseTimeMs,
                'success' => $success ? 1 : 0,
                'error_message' => $errorMessage,
                'created_at' => current_time('mysql')
            ]
        );
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Get analytics summary
     */
    public function getAnalyticsSummary($days = 30) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT 
                action_type,
                COUNT(*) as total_count,
                AVG(confidence) as avg_confidence,
                SUM(web_search_triggered) as web_searches,
                AVG(response_time_ms) as avg_response_time,
                SUM(success) as success_count
             FROM {$this->table_prefix}rtf_kate_analytics 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
             GROUP BY action_type",
            $days
        ), ARRAY_A);
    }
    
    /**
     * Get user activity summary
     */
    public function getUserActivity($userId, $days = 30) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as interactions,
                COUNT(DISTINCT intent_id) as unique_intents,
                SUM(web_search_triggered) as web_searches
             FROM {$this->table_prefix}rtf_kate_analytics 
             WHERE user_id = %d 
             AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
             GROUP BY DATE(created_at)
             ORDER BY date DESC",
            $userId,
            $days
        ), ARRAY_A);
    }
    
    // ============================================================================
    // DOCUMENT ANALYSIS
    // ============================================================================
    
    /**
     * Save document analysis
     */
    public function saveDocumentAnalysis($documentId, $userId, $analysisType, $confidenceScore, $keyFindings, $recommendations, $legalViolations, $socialWorkIssues) {
        $this->wpdb->insert(
            $this->table_prefix . 'rtf_platform_document_analysis',
            [
                'document_id' => $documentId,
                'user_id' => $userId,
                'analysis_type' => $analysisType,
                'confidence_score' => $confidenceScore,
                'key_findings' => json_encode($keyFindings, JSON_UNESCAPED_UNICODE),
                'recommendations' => json_encode($recommendations, JSON_UNESCAPED_UNICODE),
                'legal_violations' => json_encode($legalViolations, JSON_UNESCAPED_UNICODE),
                'social_work_issues' => json_encode($socialWorkIssues, JSON_UNESCAPED_UNICODE),
                'analyzed_at' => current_time('mysql')
            ]
        );
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Get document analyses
     */
    public function getDocumentAnalyses($documentId) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_platform_document_analysis 
             WHERE document_id = %d 
             ORDER BY analyzed_at DESC",
            $documentId
        ), ARRAY_A);
    }
    
    // ============================================================================
    // LEGAL GUIDANCE
    // ============================================================================
    
    /**
     * Save legal guidance
     */
    public function saveGuidance($userId, $caseId, $situationType, $title, $guidanceDataJson, $confidence) {
        $this->wpdb->insert(
            $this->table_prefix . 'rtf_kate_guidance',
            [
                'user_id' => $userId,
                'case_id' => $caseId,
                'situation_type' => $situationType,
                'title' => $title,
                'guidance_data' => $guidanceDataJson,
                'confidence' => $confidence,
                'created_at' => current_time('mysql')
            ]
        );
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Get user's guidance history
     */
    public function getUserGuidanceHistory($userId, $limit = 10) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_kate_guidance 
             WHERE user_id = %d 
             ORDER BY created_at DESC 
             LIMIT %d",
            $userId,
            $limit
        ), ARRAY_A);
    }
    
    /**
     * Get guidance by ID
     */
    public function getGuidanceById($guidanceId) {
        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_kate_guidance 
             WHERE id = %d",
            $guidanceId
        ), ARRAY_A);
    }
    
    /**
     * Update guidance access count
     */
    public function incrementGuidanceUsage($guidanceId) {
        $this->wpdb->query($this->wpdb->prepare(
            "UPDATE {$this->table_prefix}rtf_kate_guidance 
             SET used_count = used_count + 1,
                 last_accessed = %s
             WHERE id = %d",
            current_time('mysql'),
            $guidanceId
        ));
    }
    
    /**
     * Get guidance by case ID
     */
    public function getGuidanceByCase($caseId) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_kate_guidance 
             WHERE case_id = %d 
             ORDER BY created_at DESC",
            $caseId
        ), ARRAY_A);
    }
    
    // ============================================================================
    // LAW EXPLANATIONS
    // ============================================================================
    
    /**
     * Save law explanation
     */
    public function saveLawExplanation($userId, $law, $paragraph, $title, $explanationDataJson, $confidence) {
        // Check if already exists
        $existing = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$this->table_prefix}rtf_kate_law_explanations 
             WHERE user_id = %d AND law = %s AND paragraph = %s",
            $userId,
            $law,
            $paragraph
        ));
        
        if ($existing) {
            // Update existing
            $this->wpdb->update(
                $this->table_prefix . 'rtf_kate_law_explanations',
                [
                    'access_count' => $this->wpdb->prepare("access_count + 1"),
                    'last_accessed' => current_time('mysql')
                ],
                ['id' => $existing]
            );
            
            return $existing;
        } else {
            // Insert new
            $this->wpdb->insert(
                $this->table_prefix . 'rtf_kate_law_explanations',
                [
                    'user_id' => $userId,
                    'law' => $law,
                    'paragraph' => $paragraph,
                    'title' => $title,
                    'explanation_data' => $explanationDataJson,
                    'confidence' => $confidence,
                    'created_at' => current_time('mysql')
                ]
            );
            
            return $this->wpdb->insert_id;
        }
    }
    
    /**
     * Get law explanation
     */
    public function getLawExplanation($userId, $law, $paragraph) {
        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_kate_law_explanations 
             WHERE user_id = %d AND law = %s AND paragraph = %s",
            $userId,
            $law,
            $paragraph
        ), ARRAY_A);
    }
    
    /**
     * Get user's law explanation history
     */
    public function getUserLawExplanations($userId, $limit = 20) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}rtf_kate_law_explanations 
             WHERE user_id = %d 
             ORDER BY last_accessed DESC 
             LIMIT %d",
            $userId,
            $limit
        ), ARRAY_A);
    }
    
    /**
     * Get most accessed law explanations (for all users)
     */
    public function getPopularLawExplanations($limit = 10) {
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT law, paragraph, title, 
                    SUM(access_count) as total_accesses,
                    COUNT(DISTINCT user_id) as unique_users
             FROM {$this->table_prefix}rtf_kate_law_explanations 
             GROUP BY law, paragraph, title
             ORDER BY total_accesses DESC
             LIMIT %d",
            $limit
        ), ARRAY_A);
    }
    
    /**
     * Increment law explanation access count
     */
    public function incrementLawExplanationAccess($explanationId) {
        $this->wpdb->query($this->wpdb->prepare(
            "UPDATE {$this->table_prefix}rtf_kate_law_explanations 
             SET access_count = access_count + 1,
                 last_accessed = %s
             WHERE id = %d",
            current_time('mysql'),
            $explanationId
        ));
    }
}
