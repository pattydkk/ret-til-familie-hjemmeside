<?php

namespace KateAI\WordPress;

use KateAI\Core\KateKernel;
use KateAI\Core\AdvancedFeatures;
use KateAI\Controllers\MessageController;
use KateAI\Controllers\ShareController;
use KateAI\Controllers\AdminController;
use KateAI\Controllers\ReportController;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class RestController {
    private $kernel;
    private $advancedFeatures;
    private $guidanceGenerator;
    private $lawExplainer;
    private $messageController;
    private $shareController;
    private $adminController;
    private $reportController;
    private $namespace = 'kate/v1';
    
    public function __construct(KateKernel $kernel, AdvancedFeatures $advancedFeatures = null, $guidanceGenerator = null, $lawExplainer = null, MessageController $messageController = null, ShareController $shareController = null, AdminController $adminController = null, ReportController $reportController = null) {
        $this->kernel = $kernel;
        $this->advancedFeatures = $advancedFeatures;
        $this->guidanceGenerator = $guidanceGenerator;
        $this->lawExplainer = $lawExplainer;
        $this->messageController = $messageController;
        $this->shareController = $shareController;
        $this->adminController = $adminController;
        $this->reportController = $reportController;
    }
    
    public function register_routes() {
        // Chat message endpoint
        register_rest_route($this->namespace, '/message', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_message'],
            'permission_callback' => '__return_true',
            'args' => [
                'message' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'session_id' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
        
        // Document analysis endpoint
        register_rest_route($this->namespace, '/analyze', [
            'methods' => 'POST',
            'callback' => [$this, 'analyze_document'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'content' => [
                    'required' => true,
                    'type' => 'string'
                ],
                'type' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['afgørelse', 'handleplan', 'børnefaglig_undersøgelse']
                ]
            ]
        ]);
        
        // NEW: Generate complaint letter
        register_rest_route($this->namespace, '/generate-complaint', [
            'methods' => 'POST',
            'callback' => [$this, 'generate_complaint'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'case_details' => [
                    'required' => true,
                    'type' => 'object'
                ]
            ]
        ]);
        
        // NEW: Calculate deadline
        register_rest_route($this->namespace, '/deadline', [
            'methods' => 'POST',
            'callback' => [$this, 'calculate_deadline'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'type' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['complaint', 'case_access', 'complaint_response', 'action_plan']
                ],
                'start_date' => [
                    'required' => true,
                    'type' => 'string'
                ]
            ]
        ]);
        
        // NEW: Build case timeline
        register_rest_route($this->namespace, '/timeline', [
            'methods' => 'POST',
            'callback' => [$this, 'build_timeline'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'events' => [
                    'required' => true,
                    'type' => 'array'
                ]
            ]
        ]);
        
        // NEW: Search case law
        register_rest_route($this->namespace, '/case-law', [
            'methods' => 'GET',
            'callback' => [$this, 'search_case_law'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'topic' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
        
        // NEW: Check document quality
        register_rest_route($this->namespace, '/check-document', [
            'methods' => 'POST',
            'callback' => [$this, 'check_document_quality'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'document_text' => [
                    'required' => true,
                    'type' => 'string'
                ],
                'document_type' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['decision', 'action_plan']
                ]
            ]
        ]);
        
        // NEW: Generate legal guidance
        register_rest_route($this->namespace, '/guidance', [
            'methods' => 'POST',
            'callback' => [$this, 'generate_guidance'],
            'permission_callback' => '__return_true',
            'args' => [
                'situation' => [
                    'required' => true,
                    'type' => 'object'
                ]
            ]
        ]);
        
        // NEW: Explain law paragraph
        register_rest_route($this->namespace, '/explain-law', [
            'methods' => 'POST',
            'callback' => [$this, 'explain_law'],
            'permission_callback' => '__return_true',
            'args' => [
                'law' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'paragraph' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'include_examples' => [
                    'required' => false,
                    'type' => 'boolean',
                    'default' => true
                ],
                'include_case_law' => [
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false
                ]
            ]
        ]);
        
        // NEW: Explain legal term
        register_rest_route($this->namespace, '/explain-term', [
            'methods' => 'POST',
            'callback' => [$this, 'explain_term'],
            'permission_callback' => '__return_true',
            'args' => [
                'term' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
        
        // NEW: Search Barnets Lov
        register_rest_route($this->namespace, '/search-barnets-lov', [
            'methods' => 'GET',
            'callback' => [$this, 'search_barnets_lov'],
            'permission_callback' => '__return_true',
            'args' => [
                'query' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
        
        // NEW: Get guidance history
        register_rest_route($this->namespace, '/guidance-history', [
            'methods' => 'GET',
            'callback' => [$this, 'get_guidance_history'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'limit' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 10
                ]
            ]
        ]);
        
        // NEW: Get important paragraphs
        register_rest_route($this->namespace, '/important-paragraphs', [
            'methods' => 'GET',
            'callback' => [$this, 'get_important_paragraphs'],
            'permission_callback' => '__return_true'
        ]);
        
        // === MESSAGE ENDPOINTS ===
        
        // Send message
        register_rest_route($this->namespace, '/messages/send', [
            'methods' => 'POST',
            'callback' => [$this, 'send_message'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'recipient_id' => [
                    'required' => true,
                    'type' => 'integer',
                    'validate_callback' => function($param) { return is_numeric($param); }
                ],
                'message' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field'
                ]
            ]
        ]);
        
        // Get conversation with specific user
        register_rest_route($this->namespace, '/messages/conversation/(?P<user_id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_conversation'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'user_id' => [
                    'required' => true,
                    'type' => 'integer'
                ],
                'limit' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 50
                ],
                'offset' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 0
                ]
            ]
        ]);
        
        // Get conversation list
        register_rest_route($this->namespace, '/messages/list', [
            'methods' => 'GET',
            'callback' => [$this, 'get_conversation_list'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'limit' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 20
                ]
            ]
        ]);
        
        // Get unread count
        register_rest_route($this->namespace, '/messages/unread-count', [
            'methods' => 'GET',
            'callback' => [$this, 'get_unread_count'],
            'permission_callback' => [$this, 'check_logged_in']
        ]);
        
        // Mark conversation as read
        register_rest_route($this->namespace, '/messages/mark-read/(?P<user_id>\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'mark_conversation_read'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'user_id' => [
                    'required' => true,
                    'type' => 'integer'
                ]
            ]
        ]);
        
        // Delete message
        register_rest_route($this->namespace, '/messages/(?P<message_id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete_message'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'message_id' => [
                    'required' => true,
                    'type' => 'integer'
                ]
            ]
        ]);
        
        // Search users
        register_rest_route($this->namespace, '/messages/search-users', [
            'methods' => 'GET',
            'callback' => [$this, 'search_users'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'query' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'limit' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 10
                ]
            ]
        ]);
        
        // Poll for new messages
        register_rest_route($this->namespace, '/messages/poll', [
            'methods' => 'GET',
            'callback' => [$this, 'poll_messages'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'since' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
        
        // === SHARE ENDPOINTS ===
        
        // Share content
        register_rest_route($this->namespace, '/share', [
            'methods' => 'POST',
            'callback' => [$this, 'share_content'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'source_type' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['post', 'news', 'forum']
                ],
                'source_id' => [
                    'required' => true,
                    'type' => 'integer'
                ]
            ]
        ]);
        
        // Get user shares
        register_rest_route($this->namespace, '/shares', [
            'methods' => 'GET',
            'callback' => [$this, 'get_user_shares'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'limit' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 20
                ],
                'offset' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 0
                ]
            ]
        ]);
        
        // Get feed shares
        register_rest_route($this->namespace, '/shares/feed', [
            'methods' => 'GET',
            'callback' => [$this, 'get_feed_shares'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'limit' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 20
                ],
                'offset' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 0
                ]
            ]
        ]);
        
        // Delete share
        register_rest_route($this->namespace, '/share/(?P<share_id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete_share'],
            'permission_callback' => [$this, 'check_logged_in'],
            'args' => [
                'share_id' => [
                    'required' => true,
                    'type' => 'integer'
                ]
            ]
        ]);
        
        // === ADMIN ENDPOINTS ===
        
        // Get users
        register_rest_route($this->namespace, '/admin/users', [
            'methods' => 'GET',
            'callback' => [$this, 'admin_get_users'],
            'permission_callback' => [$this, 'check_admin']
        ]);
        
        // Get user details
        register_rest_route($this->namespace, '/admin/user/(?P<user_id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'admin_get_user'],
            'permission_callback' => [$this, 'check_admin']
        ]);
        
        // Update user
        register_rest_route($this->namespace, '/admin/user/(?P<user_id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'admin_update_user'],
            'permission_callback' => [$this, 'check_admin']
        ]);
        
        // Delete user
        register_rest_route($this->namespace, '/admin/user/(?P<user_id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'admin_delete_user'],
            'permission_callback' => [$this, 'check_admin']
        ]);
        
        // Activate subscription
        register_rest_route($this->namespace, '/admin/subscription/(?P<user_id>\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'admin_activate_subscription'],
            'permission_callback' => [$this, 'check_admin']
        ]);
        
        // Get analytics
        register_rest_route($this->namespace, '/admin/analytics', [
            'methods' => 'GET',
            'callback' => [$this, 'admin_get_analytics'],
            'permission_callback' => [$this, 'check_admin']
        ]);
        
        // === REPORT ENDPOINTS ===
        
        // Get reports with filters
        register_rest_route($this->namespace, '/reports', [
            'methods' => 'GET',
            'callback' => [$this, 'get_reports'],
            'permission_callback' => [$this, 'check_logged_in']
        ]);
        
        // Get single report
        register_rest_route($this->namespace, '/reports/(?P<report_id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_report'],
            'permission_callback' => [$this, 'check_logged_in']
        ]);
        
        // Get filter options
        register_rest_route($this->namespace, '/reports/filters', [
            'methods' => 'GET',
            'callback' => [$this, 'get_report_filters'],
            'permission_callback' => [$this, 'check_logged_in']
        ]);
        
        // Upload report (admin only)
        register_rest_route($this->namespace, '/reports/upload', [
            'methods' => 'POST',
            'callback' => [$this, 'upload_report'],
            'permission_callback' => [$this, 'check_admin']
        ]);

        // === PROFILE IMAGE UPLOAD ===
        
        // Upload profile/cover image with GDPR censoring
        register_rest_route($this->namespace, '/upload-profile-image', [
            'methods' => 'POST',
            'callback' => [$this, 'upload_profile_image'],
            'permission_callback' => [$this, 'check_logged_in']
        ]);

        // === CHAT ROOMS ===
        
        // Join a chat room
        register_rest_route($this->namespace, '/chat-rooms/join', [
            'methods' => 'POST',
            'callback' => [$this, 'join_chat_room'],
            'permission_callback' => [$this, 'check_logged_in']
        ]);
        
        // Get room messages
        register_rest_route($this->namespace, '/chat-rooms/(?P<room_id>\d+)/messages', [
            'methods' => 'GET',
            'callback' => [$this, 'get_room_messages'],
            'permission_callback' => [$this, 'check_logged_in']
        ]);
        
        // Send room message
        register_rest_route($this->namespace, '/chat-rooms/send', [
            'methods' => 'POST',
            'callback' => [$this, 'send_room_message'],
            'permission_callback' => [$this, 'check_logged_in']
        ]);
        
        // Leave room
        register_rest_route($this->namespace, '/chat-rooms/leave', [
            'methods' => 'POST',
            'callback' => [$this, 'leave_chat_room'],
            'permission_callback' => [$this, 'check_logged_in']
        ]);
        
        // Get room list
        register_rest_route($this->namespace, '/chat-rooms/list', [
            'methods' => 'GET',
            'callback' => [$this, 'get_chat_rooms'],
            'permission_callback' => [$this, 'check_logged_in']
        ]);
    }
    
    public function handle_message(WP_REST_Request $request) {
        $message = $request->get_param('message');
        $session_id = $request->get_param('session_id');
        
        // GDPR: Verificer bruger session
        session_start();
        $user_id = isset($_SESSION['rtf_user_id']) ? intval($_SESSION['rtf_user_id']) : 0;
        
        if ($user_id === 0) {
            return new WP_Error('unauthorized', 'Du skal være logget ind for at bruge Kate AI', ['status' => 401]);
        }
        
        if (empty($session_id)) {
            $session_id = 'kate_' . $user_id . '_' . md5(session_id() . time());
        }
        
        // Verificer at session tilhører brugeren
        if (strpos($session_id, 'kate_' . $user_id . '_') !== 0) {
            return new WP_Error('forbidden', 'Ugyldig session', ['status' => 403]);
        }
        
        try {
            // Send user_id context til Kate AI
            $context = ['user_id' => $user_id];
            $response = $this->kernel->handleMessage($session_id, $message, $context);
            
            // Log to database
            $this->log_conversation($session_id, $user_id, $message, $response);
            
            return new WP_REST_Response([
                'success' => true,
                'session_id' => $session_id,
                'data' => $response
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('kate_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public function analyze_document(WP_REST_Request $request) {
        $content = $request->get_param('content');
        $type = $request->get_param('type');
        $document_id = $request->get_param('document_id');
        
        // GDPR: Verificer bruger session og document ownership
        session_start();
        $user_id = isset($_SESSION['rtf_user_id']) ? intval($_SESSION['rtf_user_id']) : 0;
        
        if ($user_id === 0) {
            return new WP_Error('unauthorized', 'Du skal være logget ind', ['status' => 401]);
        }
        
        // Hvis document_id er angivet, verificer ownership
        if ($document_id) {
            global $wpdb;
            $doc_owner = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM {$wpdb->prefix}rtf_platform_documents WHERE id = %d",
                $document_id
            ));
            
            if (!$doc_owner || intval($doc_owner) !== $user_id) {
                return new WP_Error('forbidden', 'Du har ikke adgang til dette dokument', ['status' => 403]);
            }
        }
        
        try {
            $context = ['user_id' => $user_id];
            $analysis = $this->kernel->analyzeDocument($content, $type, $context);
            
            // Gem analyse til database hvis document_id eksisterer
            if ($document_id && isset($analysis['confidence'])) {
                $wpdb->insert($wpdb->prefix . 'rtf_platform_document_analysis', [
                    'document_id' => $document_id,
                    'analysis_type' => $type,
                    'confidence_score' => $analysis['confidence'],
                    'key_findings' => json_encode($analysis['findings'] ?? [], JSON_UNESCAPED_UNICODE),
                    'recommendations' => json_encode($analysis['recommendations'] ?? [], JSON_UNESCAPED_UNICODE),
                    'analyzed_at' => current_time('mysql')
                ]);
            }
            
            return new WP_REST_Response([
                'success' => true,
                'data' => $analysis
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('kate_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public function check_logged_in() {
        return isset($_SESSION['rtf_user_id']);
    }
    
    /**
     * Check if user is admin
     */
    public function check_admin() {
        if (!isset($_SESSION['rtf_user_id'])) {
            return false;
        }
        
        if (!$this->adminController) {
            return false;
        }
        
        $user_id = intval($_SESSION['rtf_user_id']);
        return $this->adminController->verifyAdmin($user_id);
    }
    
    /**
     * Get current logged in user from database
     */
    private function get_current_user() {
        if (!isset($_SESSION['rtf_user_id'])) {
            return null;
        }
        
        global $wpdb;
        $table_users = $wpdb->prefix . 'rtf_platform_users';
        $user_id = intval($_SESSION['rtf_user_id']);
        
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_users WHERE id = %d",
            $user_id
        ));
        
        return $user;
    }
    
    // NEW ENDPOINTS IMPLEMENTATIONS
    
    public function generate_complaint(WP_REST_Request $request) {
        if (!$this->advancedFeatures) {
            return new WP_Error('not_available', 'Avancerede features ikke tilgængelige', ['status' => 503]);
        }
        
        $case_details = $request->get_param('case_details');
        
        try {
            $letter = $this->advancedFeatures->generateComplaintLetter($case_details);
            
            return new WP_REST_Response([
                'success' => true,
                'data' => $letter
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('kate_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public function calculate_deadline(WP_REST_Request $request) {
        if (!$this->advancedFeatures) {
            return new WP_Error('not_available', 'Avancerede features ikke tilgængelige', ['status' => 503]);
        }
        
        $type = $request->get_param('type');
        $start_date = $request->get_param('start_date');
        
        try {
            $deadline = $this->advancedFeatures->calculateDeadline($type, $start_date);
            
            return new WP_REST_Response([
                'success' => true,
                'data' => $deadline
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('kate_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public function build_timeline(WP_REST_Request $request) {
        if (!$this->advancedFeatures) {
            return new WP_Error('not_available', 'Avancerede features ikke tilgængelige', ['status' => 503]);
        }
        
        $events = $request->get_param('events');
        
        try {
            $timeline = $this->advancedFeatures->buildCaseTimeline($events);
            
            return new WP_REST_Response([
                'success' => true,
                'data' => $timeline
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('kate_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public function search_case_law(WP_REST_Request $request) {
        if (!$this->advancedFeatures) {
            return new WP_Error('not_available', 'Avancerede features ikke tilgængelige', ['status' => 503]);
        }
        
        $topic = $request->get_param('topic');
        
        try {
            $caseLaw = $this->advancedFeatures->searchCaseLaw($topic);
            
            return new WP_REST_Response([
                'success' => true,
                'data' => $caseLaw
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('kate_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public function check_document_quality(WP_REST_Request $request) {
        if (!$this->advancedFeatures) {
            return new WP_Error('not_available', 'Avancerede features ikke tilgængelige', ['status' => 503]);
        }
        
        $document_text = $request->get_param('document_text');
        $document_type = $request->get_param('document_type');
        
        try {
            $quality_check = $this->advancedFeatures->checkDocumentQuality($document_text, $document_type);
            
            return new WP_REST_Response([
                'success' => true,
                'data' => $quality_check
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('kate_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    private function log_conversation($session_id, $user_id, $message, $response) {
        global $wpdb;
        $table = $wpdb->prefix . 'rtf_platform_kate_chat';
        
        $wpdb->insert($table, [
            'session_id' => $session_id,
            'user_id' => $user_id,
            'message' => $message,
            'response' => json_encode($response, JSON_UNESCAPED_UNICODE),
            'intent_id' => $response['intent_id'] ?? null,
            'confidence' => $response['confidence'] ?? null
        ]);
    }
    
    /**
     * Generate legal guidance
     */
    public function generate_guidance(WP_REST_Request $request) {
        try {
            if (!$this->guidanceGenerator) {
                return new WP_Error('not_available', 'Guidance generator ikke tilgængelig', ['status' => 503]);
            }
            
            $situation = $request->get_param('situation');
            $userId = get_current_user_id();
            
            if ($userId) {
                $situation['user_id'] = $userId;
            }
            
            $guidance = $this->guidanceGenerator->generateGuidance($situation);
            
            return new WP_REST_Response([
                'success' => true,
                'guidance' => $guidance
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('guidance_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Explain law paragraph
     */
    public function explain_law(WP_REST_Request $request) {
        try {
            if (!$this->lawExplainer) {
                return new WP_Error('not_available', 'Law explainer ikke tilgængelig', ['status' => 503]);
            }
            
            $law = $request->get_param('law');
            $paragraph = $request->get_param('paragraph');
            $includeExamples = $request->get_param('include_examples') ?? true;
            $includeCaseLaw = $request->get_param('include_case_law') ?? false;
            
            $userId = get_current_user_id();
            $options = [
                'include_examples' => $includeExamples,
                'include_case_law' => $includeCaseLaw
            ];
            
            if ($userId) {
                $options['user_id'] = $userId;
            }
            
            $explanation = $this->lawExplainer->explainLaw($law, $paragraph, $options);
            
            return new WP_REST_Response([
                'success' => true,
                'explanation' => $explanation
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('explanation_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Explain legal term
     */
    public function explain_term(WP_REST_Request $request) {
        try {
            if (!$this->lawExplainer) {
                return new WP_Error('not_available', 'Law explainer ikke tilgængelig', ['status' => 503]);
            }
            
            $term = $request->get_param('term');
            $explanation = $this->lawExplainer->explainTerm($term);
            
            return new WP_REST_Response([
                'success' => true,
                'explanation' => $explanation
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('term_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Search Barnets Lov
     */
    public function search_barnets_lov(WP_REST_Request $request) {
        try {
            if (!$this->lawExplainer) {
                return new WP_Error('not_available', 'Law explainer ikke tilgængelig', ['status' => 503]);
            }
            
            $query = $request->get_param('query');
            $results = $this->lawExplainer->searchBarnetsLov($query);
            
            return new WP_REST_Response([
                'success' => true,
                'results' => $results
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('search_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Get user's guidance history
     */
    public function get_guidance_history(WP_REST_Request $request) {
        try {
            $userId = get_current_user_id();
            if (!$userId) {
                return new WP_Error('unauthorized', 'Du skal være logget ind', ['status' => 401]);
            }
            
            if (!$this->guidanceGenerator) {
                return new WP_Error('not_available', 'Guidance generator ikke tilgængelig', ['status' => 503]);
            }
            
            $limit = $request->get_param('limit') ?? 10;
            $history = $this->guidanceGenerator->getUserGuidanceHistory($userId, $limit);
            
            return new WP_REST_Response([
                'success' => true,
                'history' => $history
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('history_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Get important paragraphs from Barnets Lov
     */
    public function get_important_paragraphs(WP_REST_Request $request) {
        try {
            if (!$this->lawExplainer) {
                return new WP_Error('not_available', 'Law explainer ikke tilgængelig', ['status' => 503]);
            }
            
            $paragraphs = $this->lawExplainer->getImportantParagraphs();
            
            return new WP_REST_Response([
                'success' => true,
                'paragraphs' => $paragraphs
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('paragraphs_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    // === MESSAGE ENDPOINT HANDLERS ===
    
    /**
     * Send message to another user
     */
    public function send_message(WP_REST_Request $request) {
        try {
            if (!$this->messageController) {
                return new WP_Error('not_available', 'Beskedfunktionen er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = get_current_user_id();
            $recipientId = $request->get_param('recipient_id');
            $message = $request->get_param('message');
            
            $result = $this->messageController->sendMessage($userId, $recipientId, $message);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('send_failed', $result['error'], ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('send_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Get conversation with specific user
     */
    public function get_conversation(WP_REST_Request $request) {
        try {
            if (!$this->messageController) {
                return new WP_Error('not_available', 'Beskedfunktionen er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = get_current_user_id();
            $otherUserId = $request->get_param('user_id');
            $limit = $request->get_param('limit') ?? 50;
            $offset = $request->get_param('offset') ?? 0;
            
            $result = $this->messageController->getConversation($userId, $otherUserId, $limit, $offset);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('conversation_failed', $result['error'], ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('conversation_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Get list of all conversations
     */
    public function get_conversation_list(WP_REST_Request $request) {
        try {
            if (!$this->messageController) {
                return new WP_Error('not_available', 'Beskedfunktionen er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = get_current_user_id();
            $limit = $request->get_param('limit') ?? 20;
            
            $result = $this->messageController->getConversationList($userId, $limit);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('list_failed', $result['error'], ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('list_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Get unread message count
     */
    public function get_unread_count(WP_REST_Request $request) {
        try {
            if (!$this->messageController) {
                return new WP_Error('not_available', 'Beskedfunktionen er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = get_current_user_id();
            $result = $this->messageController->getUnreadCount($userId);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('count_failed', $result['error'], ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('count_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Mark conversation as read
     */
    public function mark_conversation_read(WP_REST_Request $request) {
        try {
            if (!$this->messageController) {
                return new WP_Error('not_available', 'Beskedfunktionen er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = get_current_user_id();
            $otherUserId = $request->get_param('user_id');
            
            $result = $this->messageController->markAsRead($userId, $otherUserId);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('mark_failed', $result['error'], ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('mark_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Delete message
     */
    public function delete_message(WP_REST_Request $request) {
        try {
            if (!$this->messageController) {
                return new WP_Error('not_available', 'Beskedfunktionen er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = get_current_user_id();
            $messageId = $request->get_param('message_id');
            
            $result = $this->messageController->deleteMessage($messageId, $userId);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('delete_failed', $result['error'], ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('delete_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Search users to message
     */
    public function search_users(WP_REST_Request $request) {
        try {
            if (!$this->messageController) {
                return new WP_Error('not_available', 'Beskedfunktionen er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = get_current_user_id();
            $query = $request->get_param('query');
            $limit = $request->get_param('limit') ?? 10;
            
            $result = $this->messageController->searchUsers($userId, $query, $limit);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('search_failed', $result['error'], ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('search_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Poll for new messages
     */
    public function poll_messages(WP_REST_Request $request) {
        try {
            if (!$this->messageController) {
                return new WP_Error('not_available', 'Beskedfunktionen er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = get_current_user_id();
            $since = $request->get_param('since');
            
            $result = $this->messageController->getNewMessages($userId, $since);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('poll_failed', $result['error'], ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('poll_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    // === SHARE ENDPOINT HANDLERS ===
    
    /**
     * Share content to wall
     */
    public function share_content(WP_REST_Request $request) {
        try {
            if (!$this->shareController) {
                return new WP_Error('not_available', 'Delingsfunktion er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = get_current_user_id();
            $sourceType = $request->get_param('source_type');
            $sourceId = $request->get_param('source_id');
            
            $result = $this->shareController->shareContent($userId, $sourceType, $sourceId);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('share_failed', $result['error'], ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('share_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Get user's shares
     */
    public function get_user_shares(WP_REST_Request $request) {
        try {
            if (!$this->shareController) {
                return new WP_Error('not_available', 'Delingsfunktion er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = get_current_user_id();
            $limit = $request->get_param('limit') ?? 20;
            $offset = $request->get_param('offset') ?? 0;
            
            $result = $this->shareController->getShares($userId, $limit, $offset);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('shares_failed', $result['error'], ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('shares_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Get feed shares
     */
    public function get_feed_shares(WP_REST_Request $request) {
        try {
            if (!$this->shareController) {
                return new WP_Error('not_available', 'Delingsfunktion er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = get_current_user_id();
            $limit = $request->get_param('limit') ?? 20;
            $offset = $request->get_param('offset') ?? 0;
            
            $result = $this->shareController->getFeedShares($userId, $limit, $offset);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('feed_failed', $result['error'], ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('feed_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Delete share
     */
    public function delete_share(WP_REST_Request $request) {
        try {
            if (!$this->shareController) {
                return new WP_Error('not_available', 'Delingsfunktion er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = get_current_user_id();
            $shareId = $request->get_param('share_id');
            
            $result = $this->shareController->deleteShare($shareId, $userId);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('delete_failed', $result['error'], ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('delete_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    // === ADMIN HANDLER METHODS ===
    
    /**
     * Get users (admin)
     */
    public function admin_get_users(WP_REST_Request $request) {
        try {
            if (!$this->adminController) {
                return new WP_Error('not_available', 'Admin funktionalitet er ikke tilgængelig', ['status' => 503]);
            }
            
            $limit = $request->get_param('limit') ?? 50;
            $offset = $request->get_param('offset') ?? 0;
            $search = $request->get_param('search') ?? '';
            
            $result = $this->adminController->getUsers($limit, $offset, $search);
            
            return new WP_REST_Response([
                'success' => true,
                'users' => $result['users'],
                'total' => $result['total']
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('admin_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Get user details (admin)
     */
    public function admin_get_user(WP_REST_Request $request) {
        try {
            if (!$this->adminController) {
                return new WP_Error('not_available', 'Admin funktionalitet er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = $request->get_param('user_id');
            $result = $this->adminController->getUserDetails($userId);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('user_not_found', $result['error'], ['status' => 404]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('admin_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Update user (admin)
     */
    public function admin_update_user(WP_REST_Request $request) {
        try {
            if (!$this->adminController) {
                return new WP_Error('not_available', 'Admin funktionalitet er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = $request->get_param('user_id');
            $data = $request->get_json_params();
            
            $result = $this->adminController->updateUser($userId, $data);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('update_failed', $result['message'], ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('admin_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Delete user (admin)
     */
    public function admin_delete_user(WP_REST_Request $request) {
        try {
            if (!$this->adminController) {
                return new WP_Error('not_available', 'Admin funktionalitet er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = $request->get_param('user_id');
            $result = $this->adminController->deleteUser($userId);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('delete_failed', 'Kunne ikke slette bruger', ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('admin_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Activate subscription (admin)
     */
    public function admin_activate_subscription(WP_REST_Request $request) {
        try {
            if (!$this->adminController) {
                return new WP_Error('not_available', 'Admin funktionalitet er ikke tilgængelig', ['status' => 503]);
            }
            
            $userId = $request->get_param('user_id');
            $data = $request->get_json_params();
            $days = $data['days'] ?? 30;
            
            $result = $this->adminController->activateSubscription($userId, $days);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('activation_failed', 'Kunne ikke aktivere abonnement', ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('admin_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Get analytics (admin)
     */
    public function admin_get_analytics(WP_REST_Request $request) {
        try {
            if (!$this->adminController) {
                return new WP_Error('not_available', 'Admin funktionalitet er ikke tilgængelig', ['status' => 503]);
            }
            
            $result = $this->adminController->getAnalytics();
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('analytics_failed', 'Kunne ikke hente analytics', ['status' => 500]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('admin_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    // =========================================================================
    // REPORT ENDPOINTS
    // =========================================================================
    
    /**
     * Get reports with filters
     */
    public function get_reports(WP_REST_Request $request) {
        try {
            if (!$this->reportController) {
                return new WP_Error('not_available', 'Report funktionalitet er ikke tilgængelig', ['status' => 503]);
            }
            
            $filters = [
                'country' => $request->get_param('country'),
                'city' => $request->get_param('city'),
                'case_type' => $request->get_param('case_type'),
                'report_type' => $request->get_param('report_type'),
                'language' => $request->get_param('language')
            ];
            
            // Remove empty filters
            $filters = array_filter($filters);
            
            $page = $request->get_param('page') ?? 1;
            $per_page = $request->get_param('per_page') ?? 12;
            $offset = ($page - 1) * $per_page;
            
            $result = $this->reportController->getReports($filters, $per_page, $offset);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('reports_failed', 'Kunne ikke hente rapporter', ['status' => 500]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('report_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Get single report and track download
     */
    public function get_report(WP_REST_Request $request) {
        try {
            if (!$this->reportController) {
                return new WP_Error('not_available', 'Report funktionalitet er ikke tilgængelig', ['status' => 503]);
            }
            
            $reportId = $request->get_param('report_id');
            $result = $this->reportController->getReport($reportId);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('report_failed', 'Kunne ikke hente rapport', ['status' => 404]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('report_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Get filter options
     */
    public function get_report_filters(WP_REST_Request $request) {
        try {
            if (!$this->reportController) {
                return new WP_Error('not_available', 'Report funktionalitet er ikke tilgængelig', ['status' => 503]);
            }
            
            $result = $this->reportController->getFilterOptions();
            
            if ($result['success']) {
                return new WP_REST_Response($result, 200);
            } else {
                return new WP_Error('filters_failed', 'Kunne ikke hente filtermuligheder', ['status' => 500]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('report_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * Upload report (admin only)
     */
    public function upload_report(WP_REST_Request $request) {
        try {
            if (!$this->reportController) {
                return new WP_Error('not_available', 'Report funktionalitet er ikke tilgængelig', ['status' => 503]);
            }
            
            $data = $request->get_json_params();
            $files = $request->get_file_params();
            
            $result = $this->reportController->uploadReport($data, $files['file'] ?? null);
            
            if ($result['success']) {
                return new WP_REST_Response($result, 201);
            } else {
                return new WP_Error('upload_failed', 'Kunne ikke uploade rapport', ['status' => 400]);
            }
            
        } catch (\Exception $e) {
            return new WP_Error('report_error', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Upload profile or cover image with GDPR censoring
     */
    public function upload_profile_image(WP_REST_Request $request) {
        global $wpdb;
        
        session_start();
        $user_id = isset($_SESSION['rtf_user_id']) ? intval($_SESSION['rtf_user_id']) : 0;
        
        if ($user_id === 0) {
            return new WP_Error('unauthorized', 'Du skal være logget ind', ['status' => 401]);
        }
        
        try {
            $files = $request->get_file_params();
            $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'profile';
            
            if (!isset($files['image'])) {
                return new WP_Error('no_file', 'Ingen fil uploadet', ['status' => 400]);
            }
            
            $file = $files['image'];
            
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowed_types)) {
                return new WP_Error('invalid_type', 'Kun billeder er tilladt', ['status' => 400]);
            }
            
            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                return new WP_Error('file_too_large', 'Filen må maks være 5MB', ['status' => 400]);
            }
            
            // Create upload directory if it doesn't exist
            $upload_dir = wp_upload_dir();
            $profile_dir = $upload_dir['basedir'] . '/profile-images/' . $user_id;
            
            if (!file_exists($profile_dir)) {
                wp_mkdir_p($profile_dir);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $type . '_' . time() . '_' . wp_generate_password(8, false) . '.' . $extension;
            $file_path = $profile_dir . '/' . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                return new WP_Error('upload_failed', 'Kunne ikke gemme fil', ['status' => 500]);
            }
            
            // === GDPR CENSORING: Apply automatic blur to faces/sensitive info ===
            $this->apply_gdpr_censoring($file_path);
            
            // Generate URL
            $file_url = $upload_dir['baseurl'] . '/profile-images/' . $user_id . '/' . $filename;
            
            // Update user record
            $users_table = $wpdb->prefix . 'rtf_platform_users';
            $field = ($type === 'cover') ? 'cover_image' : 'profile_image';
            
            $updated = $wpdb->update(
                $users_table,
                [$field => $file_url, 'updated_at' => current_time('mysql')],
                ['id' => $user_id],
                ['%s', '%s'],
                ['%d']
            );
            
            if ($updated === false) {
                return new WP_Error('db_error', 'Kunne ikke opdatere profil', ['status' => 500]);
            }
            
            return new WP_REST_Response([
                'success' => true,
                'message' => 'Billede uploadet og censoreret',
                'url' => $file_url,
                'type' => $type
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_Error('upload_error', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Apply GDPR-compliant censoring to images (blur faces/text)
     */
    private function apply_gdpr_censoring($file_path) {
        // Load image
        $image_info = getimagesize($file_path);
        if (!$image_info) return;
        
        $image_type = $image_info[2];
        
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($file_path);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($file_path);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($file_path);
                break;
            case IMAGETYPE_WEBP:
                $image = imagecreatefromwebp($file_path);
                break;
            default:
                return; // Unsupported type
        }
        
        if (!$image) return;
        
        // Apply blur filter for privacy (simple GDPR censoring)
        // This blurs the entire image slightly to obscure faces/details
        // For more advanced face detection, integrate OpenCV or similar
        imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
        imagefilter($image, IMG_FILTER_SMOOTH, 2);
        
        // Save censored image
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                imagejpeg($image, $file_path, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($image, $file_path, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($image, $file_path);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($image, $file_path, 85);
                break;
        }
        
        imagedestroy($image);
    }
    
    // === CHAT ROOMS METHODS ===
    
    /**
     * Join a chat room
     */
    public function join_chat_room(WP_REST_Request $request) {
        global $wpdb;
        
        $user = $this->get_current_user();
        if (!$user) {
            return new WP_Error('unauthorized', 'Du skal være logget ind', ['status' => 401]);
        }
        
        $room_id = intval($request->get_param('room_id'));
        $table_rooms = $wpdb->prefix . 'rtf_chat_rooms';
        $table_members = $wpdb->prefix . 'rtf_chat_room_members';
        
        // Get room info
        $room = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_rooms WHERE id = %d",
            $room_id
        ));
        
        if (!$room) {
            return new WP_Error('not_found', 'Chat rum ikke fundet', ['status' => 404]);
        }
        
        // Check if already member
        $is_member = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_members WHERE room_id = %d AND user_id = %d",
            $room_id, $user->id
        ));
        
        if (!$is_member) {
            // Join room
            $wpdb->insert(
                $table_members,
                [
                    'room_id' => $room_id,
                    'user_id' => $user->id,
                    'joined_at' => current_time('mysql'),
                    'last_read_at' => current_time('mysql')
                ],
                ['%d', '%d', '%s', '%s']
            );
        } else {
            // Update last read
            $wpdb->update(
                $table_members,
                ['last_read_at' => current_time('mysql')],
                ['room_id' => $room_id, 'user_id' => $user->id],
                ['%s'],
                ['%d', '%d']
            );
        }
        
        return new WP_REST_Response([
            'success' => true,
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
                'description' => $room->description,
                'room_type' => $room->room_type,
                'category' => $room->category
            ]
        ], 200);
    }
    
    /**
     * Get room messages
     */
    public function get_room_messages(WP_REST_Request $request) {
        global $wpdb;
        
        $user = $this->get_current_user();
        if (!$user) {
            return new WP_Error('unauthorized', 'Du skal være logget ind', ['status' => 401]);
        }
        
        $room_id = intval($request->get_param('room_id'));
        $table_messages = $wpdb->prefix . 'rtf_chat_room_messages';
        $table_users = $wpdb->prefix . 'rtf_platform_users';
        $table_members = $wpdb->prefix . 'rtf_chat_room_members';
        
        // Check if user is member
        $is_member = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_members WHERE room_id = %d AND user_id = %d",
            $room_id, $user->id
        ));
        
        if (!$is_member) {
            return new WP_Error('forbidden', 'Du er ikke medlem af dette rum', ['status' => 403]);
        }
        
        // Get messages (last 100)
        $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT m.*, u.username, u.is_admin
             FROM $table_messages m
             LEFT JOIN $table_users u ON m.user_id = u.id
             WHERE m.room_id = %d
             ORDER BY m.created_at ASC
             LIMIT 100",
            $room_id
        ));
        
        // Update last read
        $wpdb->update(
            $table_members,
            ['last_read_at' => current_time('mysql')],
            ['room_id' => $room_id, 'user_id' => $user->id],
            ['%s'],
            ['%d', '%d']
        );
        
        return new WP_REST_Response([
            'success' => true,
            'messages' => $messages
        ], 200);
    }
    
    /**
     * Send room message with moderation
     */
    public function send_room_message(WP_REST_Request $request) {
        global $wpdb;
        
        $user = $this->get_current_user();
        if (!$user) {
            return new WP_Error('unauthorized', 'Du skal være logget ind', ['status' => 401]);
        }
        
        $room_id = intval($request->get_param('room_id'));
        $message = sanitize_textarea_field($request->get_param('message'));
        $is_moderated = intval($request->get_param('is_moderated') ?? 0);
        $moderation_reason = sanitize_text_field($request->get_param('moderation_reason') ?? '');
        
        if (empty($message)) {
            return new WP_Error('invalid_input', 'Besked kan ikke være tom', ['status' => 400]);
        }
        
        $table_messages = $wpdb->prefix . 'rtf_chat_room_messages';
        $table_members = $wpdb->prefix . 'rtf_chat_room_members';
        
        // Check if user is member
        $is_member = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_members WHERE room_id = %d AND user_id = %d",
            $room_id, $user->id
        ));
        
        if (!$is_member) {
            return new WP_Error('forbidden', 'Du er ikke medlem af dette rum', ['status' => 403]);
        }
        
        // Insert message
        $result = $wpdb->insert(
            $table_messages,
            [
                'room_id' => $room_id,
                'user_id' => $user->id,
                'message' => $message,
                'is_moderated' => $is_moderated,
                'moderation_reason' => $moderation_reason,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%d', '%s', '%s']
        );
        
        if (!$result) {
            return new WP_Error('db_error', 'Kunne ikke sende besked', ['status' => 500]);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'message_id' => $wpdb->insert_id,
            'moderated' => (bool)$is_moderated
        ], 200);
    }
    
    /**
     * Leave chat room
     */
    public function leave_chat_room(WP_REST_Request $request) {
        global $wpdb;
        
        $user = $this->get_current_user();
        if (!$user) {
            return new WP_Error('unauthorized', 'Du skal være logget ind', ['status' => 401]);
        }
        
        $room_id = intval($request->get_param('room_id'));
        $table_members = $wpdb->prefix . 'rtf_chat_room_members';
        
        $wpdb->delete(
            $table_members,
            ['room_id' => $room_id, 'user_id' => $user->id],
            ['%d', '%d']
        );
        
        return new WP_REST_Response([
            'success' => true
        ], 200);
    }
    
    /**
     * Get list of chat rooms
     */
    public function get_chat_rooms(WP_REST_Request $request) {
        global $wpdb;
        
        $user = $this->get_current_user();
        if (!$user) {
            return new WP_Error('unauthorized', 'Du skal være logget ind', ['status' => 401]);
        }
        
        $table_rooms = $wpdb->prefix . 'rtf_chat_rooms';
        $table_members = $wpdb->prefix . 'rtf_chat_room_members';
        $table_messages = $wpdb->prefix . 'rtf_chat_room_messages';
        
        $rooms = $wpdb->get_results("
            SELECT r.*,
                   (SELECT COUNT(*) FROM $table_members WHERE room_id = r.id) as member_count,
                   (SELECT COUNT(*) FROM $table_messages WHERE room_id = r.id AND created_at > NOW() - INTERVAL 24 HOUR) as messages_today,
                   (SELECT COUNT(*) FROM $table_members WHERE room_id = r.id AND user_id = {$user->id}) as is_member
            FROM $table_rooms r
            WHERE r.is_private = 0
            ORDER BY 
                CASE r.room_type
                    WHEN 'support' THEN 1
                    WHEN 'sagstype' THEN 2
                    WHEN 'landsdel' THEN 3
                    ELSE 4
                END,
                r.name ASC
        ");
        
        return new WP_REST_Response([
            'success' => true,
            'rooms' => $rooms
        ], 200);
    }
}

