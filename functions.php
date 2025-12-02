<?php
/**
 * Theme Name: Ret til Familie Platform
 * Theme URI: https://rettilf familie.dk
 * Description: Advanced family law platform with Kate AI assistant, multi-language support (DA/SV/EN), real-time chat, reports, and comprehensive case management
 * Version: 2.0.0
 * Author: Ret til Familie
 * Author URI: https://rettiltifamilie.dk
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rtf-platform
 * Requires PHP: 7.4
 * Requires at least: 5.8
 * 
 * FEATURES:
 * - Kate AI: Multi-language legal assistant (Danish, Swedish, English)
 * - Real-time user-to-user chat with unread tracking
 * - Share system for wall posts, news, forum posts
 * - Reports & Analyses download system with filtering
 * - Admin panel with user management, analytics
 * - Multi-language law database (14 laws, 100+ paragraphs)
 * - Stripe payment integration
 * - GDPR compliant with data anonymization
 * - Multi-user isolation and security
 * 
 * DATABASE TABLES (28):
 * - rtf_platform_users, rtf_platform_privacy, rtf_platform_posts
 * - rtf_platform_images, rtf_platform_documents, rtf_platform_comments
 * - rtf_platform_likes, rtf_platform_forum_posts, rtf_platform_forum_comments
 * - rtf_platform_kate_chat, rtf_platform_friends, rtf_platform_cases
 * - rtf_platform_deadlines, rtf_platform_kate_analytics, rtf_platform_kate_guidance
 * - rtf_platform_messages, rtf_platform_shares, rtf_platform_admins
 * - rtf_platform_reports, rtf_stripe_subscriptions, rtf_stripe_payments
 * - And more...
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Theme version
define('RTF_VERSION', '2.0.0');
define('RTF_DB_VERSION', '2.0.0');

// ============================================================================
// KONFIGURATION - Stripe & GitHub
// ============================================================================
define('RTF_STRIPE_PUBLIC_KEY', 'pk_live_51S5jxZL8XSb2lnp6LIO7ifWbNv3AMX4EdqMx4IJmabP3BmKVxFsz8722BEhmh4MfHOBvAwK7AmtU6FG6Ens2WvAy006GpMekTr');
define('RTF_STRIPE_SECRET_KEY', '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');
define('RTF_STRIPE_PRICE_ID', 'price_1SFMobL8XSb2lnp6ulwzpiAb');
define('RTF_STRIPE_WEBHOOK_SECRET', 'whsec_qQtOtg6DU191lNEoQplKCeYC0YAeolYw');

define('RTF_GITHUB_TOKEN', 'ghp_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
define('RTF_GITHUB_REPO_OWNER', 'hansenhr89dkk');
define('RTF_GITHUB_REPO_NAME', 'ret-til-familie-hjemmeside');
define('RTF_GITHUB_BRANCH', 'main');

// Load translations (lightweight, always safe)
require_once get_template_directory() . '/translations.php';

// ============================================================================
// KATE AI INITIALIZATION
// ============================================================================
// Requires RTF Vendor Loader plugin to be activated

function rtf_get_kate_ai_instances() {
    global $rtf_kate_ai_initialized;
    static $instances = null;
    
    // Return cached instances if already initialized
    if ($instances !== null) {
        return $instances;
    }
    
    // Check if vendor is loaded via plugin - CRITICAL CHECK
    if (!defined('RTF_VENDOR_LOADED') || !RTF_VENDOR_LOADED) {
        // Vendor not loaded - return null silently (no error)
        return null;
    }
    
    // CRITICAL: Don't initialize if theme not activated yet
    if (!get_option('rtf_theme_activated', false)) {
        return null;
    }
    
    // Load Kate AI classes
    $kate_ai_file = get_template_directory() . '/kate-ai/kate-ai.php';
    if (!file_exists($kate_ai_file)) {
        return null;
    }
    
    try {
        require_once $kate_ai_file;
    } catch (Exception $e) {
        error_log('Kate AI file load failed: ' . $e->getMessage());
        return null;
    }
    
    // Only initialize if Kate AI classes are loaded
    if (!class_exists('\KateAI\Core\KateKernel')) {
        return null;
    }
    
    try {
        // Initialize Kate AI components
        $kate_config = new \KateAI\Core\Config([
            'language' => 'da',
            'intent_threshold' => 0.3,
            'max_response_length' => 2000,
            'log_enabled' => true,
            'log_level' => 'info',
            'disclaimer' => 'Kate AI giver juridisk vejledning, men erstatter ikke professionel juridisk rådgivning.'
        ]);
        
        $knowledge_base_path = get_template_directory() . '/kate-ai/data';
        if (!file_exists($knowledge_base_path)) {
            wp_mkdir_p($knowledge_base_path);
        }
        $knowledge_base = new \KateAI\Core\KnowledgeBase($knowledge_base_path);
        
        global $wpdb;
        $logger = new \KateAI\Core\Logger($kate_config, $wpdb);
        $database_manager = new \KateAI\Core\DatabaseManager();
        
        $cache_dir = get_template_directory() . '/kate-ai/cache';
        if (!is_dir($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }
        $web_searcher = new \KateAI\Core\WebSearcher($logger, $cache_dir);
        
        $language_detector = new \KateAI\Core\LanguageDetector($database_manager, $logger);
        $law_database = new \KateAI\Core\LawDatabase($database_manager, $logger);
        
        $kernel = new \KateAI\Core\KateKernel($kate_config, $knowledge_base, $logger, $web_searcher, $database_manager, $language_detector, $law_database);
        $advanced_features = new \KateAI\Core\AdvancedFeatures($web_searcher, $knowledge_base, $database_manager);
        $guidance_generator = new \KateAI\Core\LegalGuidanceGenerator($knowledge_base, $web_searcher, $database_manager, $logger);
        $law_explainer = new \KateAI\Core\LawExplainer($knowledge_base, $web_searcher, $database_manager, $logger);
        
        $message_controller = new \KateAI\Controllers\MessageController($database_manager, $logger);
        $share_controller = new \KateAI\Controllers\ShareController($database_manager, $logger);
        $admin_controller = new \KateAI\Controllers\AdminController($database_manager, $logger);
        $report_controller = new \KateAI\Controllers\ReportController($database_manager, $logger);
        
        $rest_controller = new \KateAI\WordPress\RestController($kernel, $advanced_features, $guidance_generator, $law_explainer, $message_controller, $share_controller, $admin_controller, $report_controller);
        
        $instances = [
            'kernel' => $kernel,
            'rest_controller' => $rest_controller,
            'advanced_features' => $advanced_features,
            'guidance_generator' => $guidance_generator,
            'law_explainer' => $law_explainer
        ];
        
        $rtf_kate_ai_initialized = true;
        return $instances;
        
    } catch (Exception $e) {
        error_log('Kate AI initialization failed: ' . $e->getMessage());
        return null;
    } catch (Error $e) {
        error_log('Kate AI initialization error: ' . $e->getMessage());
        return null;
    }
}

// LAZY INITIALIZATION: Only initialize Kate AI when REST API is actually used
add_action('rest_api_init', function() {
    if (!get_option('rtf_theme_activated', false)) {
        return;
    }
    
    $instances = rtf_get_kate_ai_instances();
    if ($instances && isset($instances['rest_controller'])) {
        $instances['rest_controller']->register_routes();
    }
});

// Kate AI Shortcode
add_shortcode('kate_ai', function($atts) {
    if (!get_option('rtf_theme_activated', false)) {
        return '';
    }
    
    $instances = rtf_get_kate_ai_instances();
    if ($instances && class_exists('\KateAI\WordPress\Shortcodes')) {
        $shortcodes = new \KateAI\WordPress\Shortcodes($instances['kernel']);
        return $shortcodes->render($atts);
    }
    return '';
});

// ============================================================================
// SPROG SYSTEM
// ============================================================================
function rtf_get_lang() {
    if (isset($_GET['lang'])) {
        $lang = strtolower(sanitize_key($_GET['lang']));
        if (in_array($lang, array('da', 'sv', 'en'), true)) {
            return $lang;
        }
    }
    return 'da';
}

// ============================================================================
// THEME SETUP
// ============================================================================
function rtf_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    
    // Set activation flag on first run
    if (!get_option('rtf_theme_activated', false)) {
        update_option('rtf_theme_activated', true);
        
        // Trigger database and pages creation on first activation
        rtf_create_platform_tables();
        rtf_create_pages_menu_on_switch();
        rtf_create_default_admin();
    }
}
add_action('after_setup_theme', 'rtf_setup');

// ============================================================================
// AUTO COMPOSER INSTALL (når hentet fra GitHub)
// ============================================================================
function rtf_auto_install_composer_dependencies() {
    $theme_dir = get_template_directory();
    
    // Check if composer.json exists
    if (!file_exists($theme_dir . '/composer.json')) {
        error_log('RTF: composer.json not found, skipping auto-install');
        return false;
    }
    
    // Try to run composer install
    $composer_commands = [
        'composer install --no-dev --optimize-autoloader 2>&1',
        'php composer.phar install --no-dev --optimize-autoloader 2>&1',
        '/usr/local/bin/composer install --no-dev --optimize-autoloader 2>&1'
    ];
    
    foreach ($composer_commands as $cmd) {
        $output = [];
        $return_var = 0;
        
        // Change to theme directory and run composer
        $full_cmd = "cd " . escapeshellarg($theme_dir) . " && " . $cmd;
        exec($full_cmd, $output, $return_var);
        
        if ($return_var === 0) {
            error_log('RTF: Composer dependencies installed successfully via: ' . $cmd);
            
            // Show admin notice
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p><strong>Ret til Familie:</strong> Composer dependencies installeret automatisk! ✅</p>';
                echo '</div>';
            });
            
            return true;
        }
    }
    
    // If all attempts failed, show manual instruction
    error_log('RTF: Could not auto-install composer dependencies. Manual installation required.');
    
    add_action('admin_notices', function() {
        echo '<div class="notice notice-warning">';
        echo '<p><strong>Ret til Familie:</strong> Composer dependencies kunne ikke installeres automatisk.</p>';
        echo '<p>Kør venligst følgende kommando via SSH:</p>';
        echo '<pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">cd ' . get_template_directory() . ' && composer install --no-dev --optimize-autoloader</pre>';
        echo '</div>';
    });
    
    return false;
}

// ============================================================================

// ============================================================================
// SESSION START
// ============================================================================
function rtf_start_session() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'rtf_start_session');

// ============================================================================
// DATABASE TABELLER - Oprettes automatisk ved theme aktivering
// ============================================================================
function rtf_create_platform_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // 1. Platform Users
    $table_users = $wpdb->prefix . 'rtf_platform_users';
    $sql_users = "CREATE TABLE IF NOT EXISTS $table_users (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        username varchar(100) NOT NULL,
        email varchar(255) NOT NULL,
        password varchar(255) NOT NULL,
        full_name varchar(255) DEFAULT NULL,
        birthday date DEFAULT NULL,
        phone varchar(20) DEFAULT NULL,
        profile_image varchar(500) DEFAULT NULL,
        bio text DEFAULT NULL,
        language_preference varchar(10) DEFAULT 'da_DK',
        country varchar(5) DEFAULT 'DK',
        is_admin tinyint(1) DEFAULT 0,
        is_active tinyint(1) DEFAULT 1,
        stripe_customer_id varchar(255) DEFAULT NULL,
        subscription_status varchar(50) DEFAULT 'inactive',
        subscription_end_date datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY username (username),
        UNIQUE KEY email (email),
        KEY language_preference (language_preference)
    ) $charset_collate;";

    // 2. Privacy Settings
    $table_privacy = $wpdb->prefix . 'rtf_platform_privacy';
    $sql_privacy = "CREATE TABLE IF NOT EXISTS $table_privacy (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        gdpr_anonymize_birthday tinyint(1) DEFAULT 0,
        profile_visibility varchar(20) DEFAULT 'all',
        show_in_forum tinyint(1) DEFAULT 1,
        allow_messages tinyint(1) DEFAULT 1,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    // 3. Wall Posts
    $table_posts = $wpdb->prefix . 'rtf_platform_posts';
    $sql_posts = "CREATE TABLE IF NOT EXISTS $table_posts (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        content text NOT NULL,
        image_url varchar(500) DEFAULT NULL,
        likes int DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    // 4. Images
    $table_images = $wpdb->prefix . 'rtf_platform_images';
    $sql_images = "CREATE TABLE IF NOT EXISTS $table_images (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        image_url varchar(500) NOT NULL,
        title varchar(255) DEFAULT NULL,
        description text DEFAULT NULL,
        blur_faces tinyint(1) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    // 5. Documents
    $table_documents = $wpdb->prefix . 'rtf_platform_documents';
    $sql_documents = "CREATE TABLE IF NOT EXISTS $table_documents (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        file_url varchar(500) NOT NULL,
        file_name varchar(255) NOT NULL,
        file_type varchar(50) DEFAULT NULL,
        file_size bigint(20) DEFAULT NULL,
        is_public tinyint(1) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    // 6. Transactions
    $table_transactions = $wpdb->prefix . 'rtf_platform_transactions';
    $sql_transactions = "CREATE TABLE IF NOT EXISTS $table_transactions (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        stripe_payment_id varchar(255) DEFAULT NULL,
        amount decimal(10,2) NOT NULL,
        currency varchar(10) DEFAULT 'DKK',
        status varchar(50) DEFAULT 'pending',
        description text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    // 7. News
    $table_news = $wpdb->prefix . 'rtf_platform_news';
    $sql_news = "CREATE TABLE IF NOT EXISTS $table_news (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        content text NOT NULL,
        author_id bigint(20) NOT NULL,
        image_url varchar(500) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY author_id (author_id)
    ) $charset_collate;";

    // 8. Forum Topics
    $table_forum_topics = $wpdb->prefix . 'rtf_platform_forum_topics';
    $sql_forum_topics = "CREATE TABLE IF NOT EXISTS $table_forum_topics (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        title varchar(255) NOT NULL,
        content text NOT NULL,
        views int DEFAULT 0,
        replies_count int DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    // 9. Forum Replies
    $table_forum_replies = $wpdb->prefix . 'rtf_platform_forum_replies';
    $sql_forum_replies = "CREATE TABLE IF NOT EXISTS $table_forum_replies (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        topic_id bigint(20) NOT NULL,
        user_id bigint(20) NOT NULL,
        content text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY topic_id (topic_id),
        KEY user_id (user_id)
    ) $charset_collate;";

    // 10. Cases (Sagshjælp)
    $table_cases = $wpdb->prefix . 'rtf_platform_cases';
    $sql_cases = "CREATE TABLE IF NOT EXISTS $table_cases (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        title varchar(255) NOT NULL,
        description text NOT NULL,
        category varchar(100) DEFAULT NULL,
        status varchar(50) DEFAULT 'open',
        assigned_admin bigint(20) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    // 11. Kate AI Chat (UDVIDET)
    $table_kate_chat = $wpdb->prefix . 'rtf_platform_kate_chat';
    $sql_kate_chat = "CREATE TABLE IF NOT EXISTS $table_kate_chat (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        session_id varchar(255) NOT NULL,
        user_id bigint(20) NOT NULL,
        message text NOT NULL,
        response longtext DEFAULT NULL,
        intent_id varchar(100) DEFAULT NULL,
        confidence decimal(5,2) DEFAULT NULL,
        web_search_used tinyint(1) DEFAULT 0,
        sources_used text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY session_id (session_id),
        KEY intent_id (intent_id)
    ) $charset_collate;";

    // 12. Friends System
    $table_friends = $wpdb->prefix . 'rtf_platform_friends';
    $sql_friends = "CREATE TABLE IF NOT EXISTS $table_friends (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        friend_id bigint(20) NOT NULL,
        status varchar(20) DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY friend_id (friend_id),
        UNIQUE KEY unique_friendship (user_id, friend_id)
    ) $charset_collate;";

    // 13. Document Analysis (Kate AI - UDVIDET)
    $table_doc_analysis = $wpdb->prefix . 'rtf_platform_document_analysis';
    $sql_doc_analysis = "CREATE TABLE IF NOT EXISTS $table_doc_analysis (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        document_id bigint(20) NOT NULL,
        user_id bigint(20) NOT NULL,
        analysis_type varchar(50) DEFAULT NULL,
        confidence_score decimal(5,2) DEFAULT NULL,
        key_findings longtext DEFAULT NULL,
        recommendations longtext DEFAULT NULL,
        legal_violations longtext DEFAULT NULL,
        social_work_issues longtext DEFAULT NULL,
        analyzed_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY document_id (document_id),
        KEY user_id (user_id)
    ) $charset_collate;";

    // 14. Kate AI Generated Complaints (Klager)
    $table_kate_complaints = $wpdb->prefix . 'rtf_kate_complaints';
    $sql_kate_complaints = "CREATE TABLE IF NOT EXISTS $table_kate_complaints (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        case_id bigint(20) DEFAULT NULL,
        municipality varchar(255) DEFAULT NULL,
        decision_date date DEFAULT NULL,
        case_number varchar(255) DEFAULT NULL,
        subject text DEFAULT NULL,
        generated_letter longtext NOT NULL,
        status varchar(50) DEFAULT 'draft',
        sent_at datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY case_id (case_id)
    ) $charset_collate;";

    // 15. Kate AI Deadlines (Frister)
    $table_kate_deadlines = $wpdb->prefix . 'rtf_kate_deadlines';
    $sql_kate_deadlines = "CREATE TABLE IF NOT EXISTS $table_kate_deadlines (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        case_id bigint(20) DEFAULT NULL,
        deadline_type varchar(100) NOT NULL,
        start_date date NOT NULL,
        deadline_date date NOT NULL,
        days_total int NOT NULL,
        title varchar(255) NOT NULL,
        description text DEFAULT NULL,
        law_reference varchar(255) DEFAULT NULL,
        status varchar(50) DEFAULT 'active',
        reminder_sent tinyint(1) DEFAULT 0,
        completed_at datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY deadline_date (deadline_date),
        KEY status (status)
    ) $charset_collate;";

    // 16. Kate AI Case Timeline (Tidslinje)
    $table_kate_timeline = $wpdb->prefix . 'rtf_kate_timeline';
    $sql_kate_timeline = "CREATE TABLE IF NOT EXISTS $table_kate_timeline (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        case_id bigint(20) DEFAULT NULL,
        event_date date NOT NULL,
        event_type varchar(100) NOT NULL,
        title varchar(255) NOT NULL,
        description text DEFAULT NULL,
        document_id bigint(20) DEFAULT NULL,
        legal_significance text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY case_id (case_id),
        KEY event_date (event_date)
    ) $charset_collate;";

    // 17. Kate AI Search Cache (Web Search Cache)
    $table_kate_search_cache = $wpdb->prefix . 'rtf_kate_search_cache';
    $sql_kate_search_cache = "CREATE TABLE IF NOT EXISTS $table_kate_search_cache (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        query_hash varchar(64) NOT NULL,
        query_text varchar(500) NOT NULL,
        source varchar(100) NOT NULL,
        results longtext NOT NULL,
        result_count int DEFAULT 0,
        cached_at datetime DEFAULT CURRENT_TIMESTAMP,
        expires_at datetime NOT NULL,
        hit_count int DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY query_hash_source (query_hash, source),
        KEY expires_at (expires_at)
    ) $charset_collate;";

    // 18. Kate AI User Sessions (For dialog context)
    $table_kate_sessions = $wpdb->prefix . 'rtf_kate_sessions';
    $sql_kate_sessions = "CREATE TABLE IF NOT EXISTS $table_kate_sessions (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        session_id varchar(255) NOT NULL,
        user_id bigint(20) NOT NULL,
        context_data longtext DEFAULT NULL,
        last_intent varchar(100) DEFAULT NULL,
        conversation_history longtext DEFAULT NULL,
        started_at datetime DEFAULT CURRENT_TIMESTAMP,
        last_activity datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY session_id (session_id),
        KEY user_id (user_id),
        KEY last_activity (last_activity)
    ) $charset_collate;";

    // 19. Kate AI Knowledge Base (Lokal cache af intents og svar)
    $table_kate_kb = $wpdb->prefix . 'rtf_kate_knowledge_base';
    $sql_kate_kb = "CREATE TABLE IF NOT EXISTS $table_kate_kb (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        intent_id varchar(100) NOT NULL,
        title varchar(255) NOT NULL,
        answer_short text DEFAULT NULL,
        answer_long longtext DEFAULT NULL,
        keywords longtext DEFAULT NULL,
        law_refs longtext DEFAULT NULL,
        external_links longtext DEFAULT NULL,
        follow_up_questions longtext DEFAULT NULL,
        category varchar(100) DEFAULT NULL,
        usage_count int DEFAULT 0,
        last_used datetime DEFAULT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY intent_id (intent_id),
        KEY category (category)
    ) $charset_collate;";

    // 20. Kate AI Analytics (Brugsstatistik)
    $table_kate_analytics = $wpdb->prefix . 'rtf_kate_analytics';
    $sql_kate_analytics = "CREATE TABLE IF NOT EXISTS $table_kate_analytics (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) DEFAULT NULL,
        action_type varchar(100) NOT NULL,
        intent_id varchar(100) DEFAULT NULL,
        confidence decimal(5,2) DEFAULT NULL,
        web_search_triggered tinyint(1) DEFAULT 0,
        response_time_ms int DEFAULT NULL,
        success tinyint(1) DEFAULT 1,
        error_message text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY action_type (action_type),
        KEY created_at (created_at)
    ) $charset_collate;";

    // 21. Kate AI Guidance (Juridisk Vejledning)
    $table_kate_guidance = $wpdb->prefix . 'rtf_kate_guidance';
    $sql_kate_guidance = "CREATE TABLE IF NOT EXISTS $table_kate_guidance (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        case_id bigint(20) DEFAULT NULL,
        situation_type varchar(100) NOT NULL,
        title varchar(255) NOT NULL,
        guidance_data longtext NOT NULL COMMENT 'JSON with complete guidance',
        confidence decimal(5,2) DEFAULT 0,
        used_count int DEFAULT 1,
        last_accessed datetime DEFAULT CURRENT_TIMESTAMP,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY case_id (case_id),
        KEY situation_type (situation_type),
        KEY created_at (created_at)
    ) $charset_collate;";

    // 22. Kate AI Law Explanations (Lovforklaringer)
    $table_kate_law_explanations = $wpdb->prefix . 'rtf_kate_law_explanations';
    $sql_kate_law_explanations = "CREATE TABLE IF NOT EXISTS $table_kate_law_explanations (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        law varchar(100) NOT NULL COMMENT 'barnets_lov, forvaltningsloven, etc',
        paragraph varchar(50) NOT NULL,
        title varchar(255) NOT NULL,
        explanation_data longtext NOT NULL COMMENT 'JSON with full explanation',
        confidence decimal(5,2) DEFAULT 0,
        access_count int DEFAULT 1,
        last_accessed datetime DEFAULT CURRENT_TIMESTAMP,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY law_paragraph (law, paragraph),
        KEY created_at (created_at)
    ) $charset_collate;";

    // 23. User-to-User Messages (Chat System)
    $table_messages = $wpdb->prefix . 'rtf_platform_messages';
    $sql_messages = "CREATE TABLE IF NOT EXISTS $table_messages (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        sender_id bigint(20) NOT NULL,
        recipient_id bigint(20) NOT NULL,
        message text NOT NULL,
        read_status tinyint(1) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY sender_id (sender_id),
        KEY recipient_id (recipient_id),
        KEY read_status (read_status),
        KEY created_at (created_at)
    ) $charset_collate;";

    // 24. Content Shares (Share to Wall)
    $table_shares = $wpdb->prefix . 'rtf_platform_shares';
    $sql_shares = "CREATE TABLE IF NOT EXISTS $table_shares (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL COMMENT 'User who shared',
        source_type varchar(50) NOT NULL COMMENT 'post, news, forum_topic, forum_reply',
        source_id bigint(20) NOT NULL COMMENT 'ID of the shared content',
        original_user_id bigint(20) NOT NULL COMMENT 'Original content creator',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY source_type_id (source_type, source_id),
        KEY created_at (created_at)
    ) $charset_collate;";

    // 25. Admin Profiles
    $table_admins = $wpdb->prefix . 'rtf_platform_admins';
    $sql_admins = "CREATE TABLE IF NOT EXISTS $table_admins (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        role varchar(50) DEFAULT 'admin' COMMENT 'super_admin, admin, moderator',
        permissions text DEFAULT NULL COMMENT 'JSON array of permissions',
        created_by bigint(20) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_id (user_id),
        KEY role (role)
    ) $charset_collate;";

    // 26. Reports & Analyses
    $table_reports = $wpdb->prefix . 'rtf_platform_reports';
    $sql_reports = "CREATE TABLE IF NOT EXISTS $table_reports (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        description text DEFAULT NULL,
        country varchar(5) NOT NULL COMMENT 'DK or SE',
        city varchar(100) DEFAULT NULL,
        case_type varchar(100) DEFAULT NULL COMMENT 'family, jobcenter, disability, elder, divorce',
        report_type varchar(50) NOT NULL COMMENT 'legal, psychological, social, combined',
        file_url varchar(500) NOT NULL,
        file_size bigint(20) DEFAULT NULL,
        download_count int DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY country (country),
        KEY case_type (case_type),
        KEY report_type (report_type),
        KEY created_at (created_at)
    ) $charset_collate;";

    // 27. Stripe Subscriptions
    $table_stripe_subs = $wpdb->prefix . 'rtf_stripe_subscriptions';
    $sql_stripe_subs = "CREATE TABLE IF NOT EXISTS $table_stripe_subs (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        stripe_subscription_id varchar(255) NOT NULL,
        stripe_customer_id varchar(255) NOT NULL,
        status varchar(50) DEFAULT 'active' COMMENT 'active, canceled, past_due, incomplete',
        current_period_start datetime NOT NULL,
        current_period_end datetime NOT NULL,
        cancel_at_period_end tinyint(1) DEFAULT 0,
        canceled_at datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY stripe_subscription_id (stripe_subscription_id),
        KEY user_id (user_id),
        KEY status (status)
    ) $charset_collate;";

    // 28. Stripe Payments
    $table_stripe_payments = $wpdb->prefix . 'rtf_stripe_payments';
    $sql_stripe_payments = "CREATE TABLE IF NOT EXISTS $table_stripe_payments (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        stripe_payment_intent_id varchar(255) NOT NULL,
        stripe_customer_id varchar(255) DEFAULT NULL,
        amount decimal(10,2) NOT NULL,
        currency varchar(10) DEFAULT 'DKK',
        status varchar(50) DEFAULT 'pending' COMMENT 'succeeded, failed, canceled, processing',
        payment_method varchar(50) DEFAULT NULL,
        description text DEFAULT NULL,
        metadata text DEFAULT NULL COMMENT 'JSON metadata',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY stripe_payment_intent_id (stripe_payment_intent_id),
        KEY user_id (user_id),
        KEY status (status),
        KEY created_at (created_at)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_users);
    dbDelta($sql_privacy);
    dbDelta($sql_posts);
    dbDelta($sql_images);
    dbDelta($sql_documents);
    dbDelta($sql_transactions);
    dbDelta($sql_news);
    dbDelta($sql_forum_topics);
    dbDelta($sql_forum_replies);
    dbDelta($sql_cases);
    dbDelta($sql_kate_chat);
    dbDelta($sql_friends);
    dbDelta($sql_doc_analysis);
    
    // Kate AI specific tables
    dbDelta($sql_kate_complaints);
    dbDelta($sql_kate_deadlines);
    dbDelta($sql_kate_timeline);
    dbDelta($sql_kate_search_cache);
    dbDelta($sql_kate_sessions);
    dbDelta($sql_kate_kb);
    dbDelta($sql_kate_analytics);
    dbDelta($sql_kate_guidance);
    dbDelta($sql_kate_law_explanations);
    
    // Social & Admin tables
    dbDelta($sql_messages);
    dbDelta($sql_shares);
    dbDelta($sql_admins);
    dbDelta($sql_reports);
    
    // Stripe payment tables
    dbDelta($sql_stripe_subs);
    dbDelta($sql_stripe_payments);

    // Create default admin user
    $existing_admin = $wpdb->get_var("SELECT id FROM $table_users WHERE username = 'admin' LIMIT 1");
    if (!$existing_admin) {
        $wpdb->insert($table_users, array(
            'username' => 'admin',
            'email' => 'admin@rettilfamilie.dk',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'full_name' => 'Administrator',
            'is_admin' => 1,
            'is_active' => 1
        ));
    }
}

// ============================================================================
// SIDER OPRETTELSE
// ============================================================================

/**
 * Create all default pages (alias for rtf_create_pages_menu_on_switch)
 */
function rtf_create_default_pages() {
    rtf_create_pages_menu_on_switch();
}

function rtf_create_pages_menu_on_switch() {
    // Standard pages
    $pages = array(
        'forside'   => 'Forside',
        'ydelser'   => 'Ydelser',
        'om-os'     => 'Om os',
        'kontakt'   => 'Kontakt',
        'akademiet' => 'Akademiet',
        'stoet-os'  => 'Støt os',
        'borger-platform' => 'Borger Platform',
    );

    // Platform pages
    $platform_pages = array(
        'platform-auth' => 'Platform Login',
        'platform-profil' => 'Min Profil',
        'platform-subscription' => 'Abonnement',
        'platform-vaeg' => 'Min Væg',
        'platform-chat' => 'Beskeder',
        'platform-billeder' => 'Billede Galleri',
        'platform-dokumenter' => 'Dokumenter',
        'platform-indstillinger' => 'Indstillinger',
        'platform-nyheder' => 'Nyheder',
        'platform-forum' => 'Forum',
        'platform-sagshjaelp' => 'Sagshjælp',
        'platform-kate-ai' => 'Kate AI Assistent',
        'platform-klagegenerator' => 'Klage Generator',
        'platform-admin-dashboard' => 'Admin Dashboard',
        'platform-admin-users' => 'Admin Users',
        'platform-venner' => 'Venner',
        'platform-rapporter' => 'Rapporter & Analyser',
    );

    $all_pages = array_merge($pages, $platform_pages);
    $ids = array();

    foreach ($all_pages as $slug => $title) {
        $existing = get_page_by_path($slug);
        if ($existing) {
            $ids[$slug] = $existing->ID;
        } else {
            $ids[$slug] = wp_insert_post(array(
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ));
        }
    }

    // Set front page
    if (!empty($ids['forside'])) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $ids['forside']);
    }

    // Create menu
    $menu_name = 'Topmenu';
    $menu = wp_get_nav_menu_object($menu_name);
    if (!$menu) {
        $menu_id = wp_create_nav_menu($menu_name);
        foreach (array('forside','ydelser','akademiet','om-os','kontakt','stoet-os','borger-platform') as $slug) {
            if (!empty($ids[$slug])) {
                wp_update_nav_menu_item($menu_id, 0, array(
                    'menu-item-title'  => $all_pages[$slug],
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => $ids[$slug],
                    'menu-item-type'   => 'post_type',
                    'menu-item-status' => 'publish',
                ));
            }
        }
        $locations = get_theme_mod('nav_menu_locations');
        if (!is_array($locations)) {
            $locations = array();
        }
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }

    // Create database tables
    rtf_create_platform_tables();
}
add_action('after_switch_theme', 'rtf_create_pages_menu_on_switch');

// ============================================================================
// DEBUG: Kør denne URL én gang for at oprette sider manuelt
// ============================================================================
add_action('wp_ajax_rtf_force_create_pages', 'rtf_force_create_pages');
add_action('wp_ajax_nopriv_rtf_force_create_pages', 'rtf_force_create_pages');
function rtf_force_create_pages() {
    echo '<html><head><meta charset="utf-8"><title>RTF Setup</title></head><body style="font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px;">';
    echo '<h1 style="color: #2563eb;">🚀 RTF Platform Setup</h1>';
    echo '<div style="background: #dbeafe; padding: 20px; border-left: 4px solid #2563eb; margin: 20px 0;">';
    
    echo '<p><strong>📊 Opretter database tabeller...</strong></p>';
    rtf_create_platform_tables();
    echo '<p style="color: green;">✅ Database tabeller oprettet</p>';
    
    echo '<p><strong>📄 Opretter alle sider...</strong></p>';
    rtf_create_pages_menu_on_switch();
    echo '<p style="color: green;">✅ 24 sider oprettet</p>';
    
    echo '<p><strong>👤 Opretter admin bruger...</strong></p>';
    rtf_create_default_admin();
    echo '<p style="color: green;">✅ Admin bruger oprettet (username: admin, password: admin123)</p>';
    
    echo '<p><strong>🔄 Flusher permalinks...</strong></p>';
    flush_rewrite_rules();
    echo '<p style="color: green;">✅ Permalinks flushed</p>';
    
    echo '</div>';
    echo '<h2 style="color: green;">✅ SETUP GENNEMFØRT!</h2>';
    echo '<p><strong>Test disse sider nu:</strong></p>';
    echo '<ul>';
    echo '<li><a href="' . home_url('/') . '" target="_blank">Forside</a></li>';
    echo '<li><a href="' . home_url('/borger-platform/') . '" target="_blank">Borgerplatform</a></li>';
    echo '<li><a href="' . home_url('/platform-auth/') . '" target="_blank">Login/Registrering</a></li>';
    echo '<li><a href="' . home_url('/om-os/') . '" target="_blank">Om os</a></li>';
    echo '</ul>';
    echo '</body></html>';
    exit;
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================
function rtf_is_logged_in() {
    return isset($_SESSION['rtf_user_id']);
}

function rtf_get_current_user() {
    if (!rtf_is_logged_in()) {
        return null;
    }
    global $wpdb;
    $table = $wpdb->prefix . 'rtf_platform_users';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $_SESSION['rtf_user_id']));
}

function rtf_is_admin_user() {
    $user = rtf_get_current_user();
    return $user && $user->is_admin == 1;
}

function rtf_require_subscription() {
    if (!rtf_is_logged_in()) {
        wp_redirect(home_url('/platform-auth'));
        exit;
    }
    
    $user = rtf_get_current_user();
    if ($user->subscription_status !== 'active') {
        wp_redirect(home_url('/platform-subscription?msg=upgrade_required'));
        exit;
    }
}

function rtf_anonymize_birthday($birthday) {
    if (!$birthday) return '';
    $parts = explode('-', $birthday);
    if (count($parts) === 3) {
        return '##-##-' . $parts[0];
    }
    return '##-##-####';
}

function rtf_format_date($date) {
    $lang = rtf_get_lang();
    $timestamp = strtotime($date);
    if ($lang === 'da') {
        return date('d/m/Y H:i', $timestamp);
    } elseif ($lang === 'sv') {
        return date('Y-m-d H:i', $timestamp);
    } else {
        return date('m/d/Y H:i', $timestamp);
    }
}

function rtf_time_ago($date) {
    $lang = rtf_get_lang();
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return $lang === 'da' ? 'lige nu' : ($lang === 'sv' ? 'just nu' : 'just now');
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ($lang === 'da' ? ' min siden' : ($lang === 'sv' ? ' min sedan' : ' mins ago'));
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ($lang === 'da' ? ' timer siden' : ($lang === 'sv' ? ' timmar sedan' : ' hours ago'));
    } else {
        return rtf_format_date($date);
    }
}

// ============================================================================
// FRIEND SYSTEM HELPERS
// ============================================================================
function rtf_send_friend_request($user_id, $friend_id) {
    if ($user_id === $friend_id) return false;
    
    global $wpdb;
    $table = $wpdb->prefix . 'rtf_platform_friends';
    
    // Check if already exists
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table WHERE (user_id = %d AND friend_id = %d) OR (user_id = %d AND friend_id = %d)",
        $user_id, $friend_id, $friend_id, $user_id
    ));
    
    if ($exists) return false;
    
    return $wpdb->insert($table, array(
        'user_id' => $user_id,
        'friend_id' => $friend_id,
        'status' => 'pending'
    ));
}

function rtf_accept_friend_request($request_id, $user_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'rtf_platform_friends';
    
    // Verify this request is for the current user
    $request = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d AND friend_id = %d", $request_id, $user_id));
    if (!$request) return false;
    
    return $wpdb->update($table, array('status' => 'accepted'), array('id' => $request_id));
}

function rtf_reject_friend_request($request_id, $user_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'rtf_platform_friends';
    
    $request = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d AND friend_id = %d", $request_id, $user_id));
    if (!$request) return false;
    
    return $wpdb->update($table, array('status' => 'rejected'), array('id' => $request_id));
}

function rtf_get_friends($user_id) {
    global $wpdb;
    $table_friends = $wpdb->prefix . 'rtf_platform_friends';
    $table_users = $wpdb->prefix . 'rtf_platform_users';
    
    $friends = $wpdb->get_results($wpdb->prepare(
        "SELECT u.* FROM $table_users u
        INNER JOIN $table_friends f ON (f.friend_id = u.id OR f.user_id = u.id)
        WHERE (f.user_id = %d OR f.friend_id = %d) AND f.status = 'accepted' AND u.id != %d",
        $user_id, $user_id, $user_id
    ));
    
    return $friends;
}

function rtf_get_friend_requests($user_id) {
    global $wpdb;
    $table_friends = $wpdb->prefix . 'rtf_platform_friends';
    $table_users = $wpdb->prefix . 'rtf_platform_users';
    
    $requests = $wpdb->get_results($wpdb->prepare(
        "SELECT f.id as request_id, u.* FROM $table_users u
        INNER JOIN $table_friends f ON f.user_id = u.id
        WHERE f.friend_id = %d AND f.status = 'pending'",
        $user_id
    ));
    
    return $requests;
}

// ============================================================================
// STYLES
// ============================================================================
function rtf_scripts() {
    wp_enqueue_style('rtf-style', get_stylesheet_uri(), array(), RTF_VERSION);
}
add_action('wp_enqueue_scripts', 'rtf_scripts');

// ============================================================================
// THEME ACTIVATION - ONE-CLICK INSTALLATION
// ============================================================================
// Note: rtf_theme_activation removed - handled by rtf_setup and after_switch_theme hook directly

// ============================================================================
// CREATE DEFAULT ADMIN USER
// ============================================================================
function rtf_create_default_admin() {
    global $wpdb;
    $users_table = $wpdb->prefix . 'rtf_platform_users';
    $admins_table = $wpdb->prefix . 'rtf_platform_admins';
    
    // Check if admin already exists
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $users_table WHERE email = %s",
        'patrickfoerslev@gmail.com'
    ));
    
    if ($existing) {
        error_log('[RTF Platform] Admin user already exists: patrickfoerslev@gmail.com');
        return;
    }
    
    // Create admin user
    $password_hash = password_hash('Ph1357911', PASSWORD_DEFAULT);
    
    $wpdb->insert($users_table, [
        'username' => 'Patrick F. Hansen',
        'email' => 'patrickfoerslev@gmail.com',
        'password' => $password_hash,
        'full_name' => 'Patrick F. Hansen',
        'language_preference' => 'da_DK',
        'country' => 'DK',
        'subscription_status' => 'active',
        'is_admin' => 1,
        'created_at' => current_time('mysql')
    ]);
    
    $user_id = $wpdb->insert_id;
    
    // Add to admins table
    $wpdb->insert($admins_table, [
        'user_id' => $user_id,
        'role' => 'super_admin',
        'permissions' => json_encode([
            'manage_users' => true,
            'manage_subscriptions' => true,
            'view_analytics' => true,
            'manage_reports' => true,
            'manage_content' => true,
            'manage_admins' => true,
            'system_settings' => true
        ]),
        'created_at' => current_time('mysql')
    ]);
    
    error_log('[RTF Platform] Default admin user created: patrickfoerslev@gmail.com');
}

// ============================================================================
// VERSION CHECK & UPDATE
// ============================================================================
function rtf_check_version() {
    $current_version = get_option('rtf_theme_version', '0.0.0');
    $current_db_version = get_option('rtf_db_version', '0.0.0');
    
    // Update database if needed
    if (version_compare($current_db_version, RTF_DB_VERSION, '<')) {
        rtf_create_platform_tables();
        update_option('rtf_db_version', RTF_DB_VERSION);
        error_log('[RTF Platform] Database updated to version: ' . RTF_DB_VERSION);
    }
    
    // Update theme version
    if (version_compare($current_version, RTF_VERSION, '<')) {
        update_option('rtf_theme_version', RTF_VERSION);
        error_log('[RTF Platform] Theme updated to version: ' . RTF_VERSION);
    }
}
add_action('admin_init', 'rtf_check_version');

// ============================================================================
// ADMIN NOTICE - CONFIGURATION REQUIRED
// ============================================================================
function rtf_admin_notices() {
    // Check if Stripe keys are configured
    if (RTF_STRIPE_SECRET_KEY === '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') {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>Ret til Familie Platform:</strong> Theme aktiveret! Alle 28 databaser er oprettet. Stripe integration er konfigureret.</p>';
        echo '</div>';
    }
    
    // Check if Kate AI is working
    if (!class_exists('\KateAI\Core\KateKernel')) {
        echo '<div class="notice notice-error">';
        echo '<p><strong>RTF Platform Error:</strong> Kate AI er ikke tilgængelig. Kør venligst <code>composer install</code> i theme mappen.</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'rtf_admin_notices');

// ============================================================================
// HEALTH CHECK ENDPOINT
// ============================================================================
function rtf_health_check() {
    global $wpdb;
    
    $status = [
        'theme_version' => RTF_VERSION,
        'db_version' => get_option('rtf_db_version', '0.0.0'),
        'kate_ai' => class_exists('\KateAI\Core\KateKernel'),
        'database_tables' => [],
        'stripe_configured' => !empty(RTF_STRIPE_SECRET_KEY),
        'languages_supported' => ['da_DK', 'sv_SE', 'en_US'],
        'features' => [
            'chat' => true,
            'share' => true,
            'reports' => true,
            'admin_panel' => true,
            'kate_ai_multilingual' => true,
            'law_database' => true
        ]
    ];
    
    // Check database tables
    $tables = [
        'rtf_platform_users', 'rtf_platform_privacy', 'rtf_platform_posts',
        'rtf_platform_messages', 'rtf_platform_shares', 'rtf_platform_reports',
        'rtf_platform_kate_chat', 'rtf_platform_admins'
    ];
    
    foreach ($tables as $table) {
        $full_table = $wpdb->prefix . $table;
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'") === $full_table;
        $status['database_tables'][$table] = $exists;
    }
    
    return $status;
}

// Add REST endpoint for health check
add_action('rest_api_init', function() {
    register_rest_route('rtf/v1', '/health', [
        'methods' => 'GET',
        'callback' => function() {
            return new WP_REST_Response(rtf_health_check(), 200);
        },
        'permission_callback' => '__return_true'
    ]);
});
