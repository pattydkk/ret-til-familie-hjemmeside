<?php
namespace KateAI\Core;

/**
 * Language Detector for Kate AI
 * Detects user's language preference and loads appropriate intents/laws
 */
class LanguageDetector {
    private $logger;
    private $db_manager;
    private $supported_languages = ['da_DK', 'sv_SE', 'en_US'];
    private $default_language = 'da_DK';
    
    public function __construct($db_manager = null, $logger = null) {
        $this->db_manager = $db_manager;
        $this->logger = $logger;
    }
    
    /**
     * Get user's language preference from database
     */
    public function getUserLanguage($user_id) {
        if (!$this->db_manager) {
            return $this->default_language;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'rtf_platform_users';
        
        $language = $wpdb->get_var($wpdb->prepare(
            "SELECT language_preference FROM $table WHERE id = %d",
            $user_id
        ));
        
        if ($language && in_array($language, $this->supported_languages)) {
            return $language;
        }
        
        return $this->default_language;
    }
    
    /**
     * Get country code from language preference
     */
    public function getCountryFromLanguage($language) {
        $map = [
            'da_DK' => 'DK',
            'sv_SE' => 'SE',
            'en_US' => 'INTL'
        ];
        
        return $map[$language] ?? 'DK';
    }
    
    /**
     * Load appropriate intents file based on language
     */
    public function loadIntents($language) {
        $intents_file = dirname(__DIR__, 2) . '/data/intents.json';
        
        // Check if language-specific intents file exists
        if ($language !== 'da_DK') {
            $language_suffix = ($language === 'sv_SE') ? '_se' : '';
            $language_intents_file = dirname(__DIR__, 2) . '/data/intents' . $language_suffix . '.json';
            
            if (file_exists($language_intents_file)) {
                $intents_file = $language_intents_file;
            }
        }
        
        if (!file_exists($intents_file)) {
            if ($this->logger) {
                $this->logger->warning("Intents file not found: $intents_file");
            }
            return [];
        }
        
        $content = file_get_contents($intents_file);
        $data = json_decode($content, true);
        
        if (!$data || !isset($data['intents'])) {
            if ($this->logger) {
                $this->logger->error("Invalid intents file format: $intents_file");
            }
            return [];
        }
        
        return $data['intents'];
    }
    
    /**
     * Get translated UI strings
     */
    public function getUIStrings($language) {
        $strings = [
            'da_DK' => [
                'greeting' => 'Hej! Jeg er Kate, din juridiske assistent.',
                'how_can_i_help' => 'Hvordan kan jeg hjælpe dig i dag?',
                'thinking' => 'Tænker...',
                'searching' => 'Søger efter information...',
                'error' => 'Beklager, der opstod en fejl.',
                'no_results' => 'Ingen resultater fundet.',
                'session_expired' => 'Din session er udløbet. Log venligst ind igen.',
                'ask_kate' => 'Spørg Kate',
                'send' => 'Send',
                'clear_chat' => 'Ryd chat',
                'new_chat' => 'Ny chat',
                'history' => 'Historik',
                'settings' => 'Indstillinger',
                'logout' => 'Log ud',
                'search_placeholder' => 'Skriv dit spørgsmål her...',
                'guidance' => 'Vejledning',
                'law_explanation' => 'Lovforklaring',
                'complaint_generator' => 'Klage generator',
                'deadline_tracker' => 'Frist oversigt',
                'case_timeline' => 'Sags tidslinje',
                'sources' => 'Kilder',
                'confidence' => 'Tillid',
                'relevant_laws' => 'Relevante love',
                'your_rights' => 'Dine rettigheder',
                'next_steps' => 'Næste skridt',
                'pricing_note' => 'Alle priser er i DKK (danske kroner)'
            ],
            'sv_SE' => [
                'greeting' => 'Hej! Jag är Kate, din juridiska assistent.',
                'how_can_i_help' => 'Hur kan jag hjälpa dig idag?',
                'thinking' => 'Tänker...',
                'searching' => 'Söker efter information...',
                'error' => 'Förlåt, ett fel uppstod.',
                'no_results' => 'Inga resultat hittades.',
                'session_expired' => 'Din session har gått ut. Vänligen logga in igen.',
                'ask_kate' => 'Fråga Kate',
                'send' => 'Skicka',
                'clear_chat' => 'Rensa chatt',
                'new_chat' => 'Ny chatt',
                'history' => 'Historik',
                'settings' => 'Inställningar',
                'logout' => 'Logga ut',
                'search_placeholder' => 'Skriv din fråga här...',
                'guidance' => 'Vägledning',
                'law_explanation' => 'Lagförklaring',
                'complaint_generator' => 'Klagogenerator',
                'deadline_tracker' => 'Fristöversikt',
                'case_timeline' => 'Ärende tidslinje',
                'sources' => 'Källor',
                'confidence' => 'Förtroende',
                'relevant_laws' => 'Relevanta lagar',
                'your_rights' => 'Dina rättigheter',
                'next_steps' => 'Nästa steg',
                'pricing_note' => 'Alla priser är i DKK (danska kronor)'
            ],
            'en_US' => [
                'greeting' => 'Hello! I am Kate, your legal assistant.',
                'how_can_i_help' => 'How can I help you today?',
                'thinking' => 'Thinking...',
                'searching' => 'Searching for information...',
                'error' => 'Sorry, an error occurred.',
                'no_results' => 'No results found.',
                'session_expired' => 'Your session has expired. Please log in again.',
                'ask_kate' => 'Ask Kate',
                'send' => 'Send',
                'clear_chat' => 'Clear chat',
                'new_chat' => 'New chat',
                'history' => 'History',
                'settings' => 'Settings',
                'logout' => 'Log out',
                'search_placeholder' => 'Type your question here...',
                'guidance' => 'Guidance',
                'law_explanation' => 'Law Explanation',
                'complaint_generator' => 'Complaint Generator',
                'deadline_tracker' => 'Deadline Overview',
                'case_timeline' => 'Case Timeline',
                'sources' => 'Sources',
                'confidence' => 'Confidence',
                'relevant_laws' => 'Relevant Laws',
                'your_rights' => 'Your Rights',
                'next_steps' => 'Next Steps',
                'pricing_note' => 'All prices are in DKK (Danish Kroner)'
            ]
        ];
        
        return $strings[$language] ?? $strings['da_DK'];
    }
    
    /**
     * Get language name for display
     */
    public function getLanguageName($language) {
        $names = [
            'da_DK' => 'Dansk',
            'sv_SE' => 'Svenska',
            'en_US' => 'English'
        ];
        
        return $names[$language] ?? 'Unknown';
    }
    
    /**
     * Get all supported languages
     */
    public function getSupportedLanguages() {
        return [
            'da_DK' => 'Dansk (Danmark)',
            'sv_SE' => 'Svenska (Sverige)',
            'en_US' => 'English (International)'
        ];
    }
    
    /**
     * Validate language code
     */
    public function isValidLanguage($language) {
        return in_array($language, $this->supported_languages);
    }
    
    /**
     * Auto-detect language from text (basic implementation)
     * More sophisticated detection can be added later
     */
    public function detectLanguageFromText($text) {
        // Danish indicators
        $danish_words = ['jeg', 'du', 'han', 'hun', 'de', 'at', 'og', 'men', 'med', 'for', 'til', 'på', 'om', 'som', 'kan', 'vil', 'skal', 'har', 'er', 'være'];
        
        // Swedish indicators  
        $swedish_words = ['jag', 'du', 'han', 'hon', 'de', 'att', 'och', 'men', 'med', 'för', 'till', 'på', 'om', 'som', 'kan', 'vill', 'ska', 'har', 'är', 'vara'];
        
        // English indicators
        $english_words = ['i', 'you', 'he', 'she', 'they', 'the', 'and', 'but', 'with', 'for', 'to', 'on', 'about', 'as', 'can', 'will', 'shall', 'have', 'is', 'be'];
        
        $text_lower = mb_strtolower($text);
        $words = preg_split('/\s+/', $text_lower);
        
        $danish_score = 0;
        $swedish_score = 0;
        $english_score = 0;
        
        foreach ($words as $word) {
            if (in_array($word, $danish_words)) {
                $danish_score++;
            }
            if (in_array($word, $swedish_words)) {
                $swedish_score++;
            }
            if (in_array($word, $english_words)) {
                $english_score++;
            }
        }
        
        // Swedish-specific characters
        if (preg_match('/[åäö]/u', $text_lower)) {
            $swedish_score += 2;
        }
        
        // Danish-specific characters
        if (preg_match('/[æø]/u', $text_lower)) {
            $danish_score += 2;
        }
        
        // English has no special characters, but lacks Scandinavian ones
        if (!preg_match('/[æøåäö]/u', $text_lower) && $english_score > 2) {
            $english_score += 2;
        }
        
        $scores = [
            'da_DK' => $danish_score,
            'sv_SE' => $swedish_score,
            'en_US' => $english_score
        ];
        
        arsort($scores);
        $detected = array_key_first($scores);
        
        return $detected ?? 'da_DK';
    }
    
    /**
     * Set user's language preference
     */
    public function setUserLanguage($user_id, $language) {
        if (!$this->isValidLanguage($language)) {
            return false;
        }
        
        if (!$this->db_manager) {
            return false;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'rtf_platform_users';
        
        $result = $wpdb->update(
            $table,
            ['language_preference' => $language, 'country' => $this->getCountryFromLanguage($language)],
            ['id' => $user_id],
            ['%s', '%s'],
            ['%d']
        );
        
        if ($this->logger) {
            $this->logger->info("Language preference updated for user $user_id: $language");
        }
        
        return $result !== false;
    }
}
