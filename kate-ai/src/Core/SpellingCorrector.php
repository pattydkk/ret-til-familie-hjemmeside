<?php
namespace KateAI\Core;

/**
 * Kate AI - Stavefejls Korrektor
 * Håndterer stavefejl og forstår hvad brugeren mente
 */
class SpellingCorrector {
    private $commonMisspellings = [
        // Juridiske termer
        'anbringelse' => ['anbringels', 'ambringelse', 'andbringelse', 'anbringels'],
        'samtykke' => ['samtycke', 'samtykk', 'samtykce'],
        'afgørelse' => ['afgörelse', 'afgørels', 'afgøreles', 'afgørels'],
        'aktindsigt' => ['aktindsight', 'aktindsigth', 'akt indsigt'],
        'forældremyndighed' => ['forældremyndighd', 'foreldremyndighed', 'forældremyndighedt'],
        'ankestyrelsen' => ['ankestyrelsen', 'ankestyrelen', 'ank styrelsen'],
        'klage' => ['klager', 'kalge', 'klgae'],
        'børnefaglig' => ['bornefaglig', 'børne faglig', 'børnfaglig'],
        'undersøgelse' => ['undersøgels', 'undersögelse', 'undersögels'],
        'partshøring' => ['partshörring', 'parts høring', 'partshöring'],
        
        // Barnets Lov termer
        'barnets lov' => ['barnetslov', 'barnetsloven', 'barnet lov'],
        'tvang' => ['tvnag', 'tvangs', 'twang'],
        'magtanvendelse' => ['magt anvendelse', 'magtanvendles'],
        'handleplan' => ['handlesplan', 'handle plan'],
        'samvær' => ['samvaer', 'samvær', 'sammvær'],
        'overvåget samvær' => ['overvaget samvaer', 'overvåget samvaer'],
        
        // Socialret termer
        'socialrådgiver' => ['social radgiver', 'socialrådgivr', 'social rådgiver'],
        'kommunen' => ['kommuenen', 'kommnen', 'komune'],
        'forvaltning' => ['forvalting', 'forvaltining'],
        'paragraf' => ['pargraf', 'paraggraf', 'pargraph'],
        
        // Almindelige ord
        'hvordan' => ['vordan', 'hvorden', 'hwordan'],
        'hvornår' => ['hvornå', 'vornår', 'hvonar'],
        'hjælp' => ['hjaelp', 'hjelp', 'hjælpe'],
        'skal' => ['skall', 'skla'],
        'måske' => ['maske', 'måske'],
        'selvfølgelig' => ['selvføgelig', 'selvfølelig', 'selvfölgelig'],
        'undskyld' => ['undskyldt', 'undskyld'],
        'tak' => ['tack', 'takk'],
        'godt' => ['got', 'godt'],
        'dårligt' => ['darligt', 'dårlig'],
        
        // Svenske varianter
        'barn' => ['barne', 'barnet'],
        'föräldrar' => ['foreldre', 'foraeldre'],
        'beslut' => ['beslud', 'besluit']
    ];
    
    private $contextualReplacements = [
        'børn' => ['born', 'boren', 'börn'],
        'ret' => ['rätt', 'rät'],
        'lov' => ['lag', 'low'],
        'kan' => ['kna', 'akn'],
        'jeg' => ['jge', 'jeg', 'jag']
    ];
    
    /**
     * Korrigerer stavefejl i besked
     */
    public function correct($message) {
        $originalMessage = $message;
        $message = mb_strtolower($message);
        
        // 1. Erstat almindelige stavefejl
        foreach ($this->commonMisspellings as $correct => $misspellings) {
            foreach ($misspellings as $wrong) {
                $pattern = '/\b' . preg_quote($wrong, '/') . '\b/i';
                $message = preg_replace($pattern, $correct, $message);
            }
        }
        
        // 2. Håndter manglende mellemrum
        $message = $this->fixMissingSpaces($message);
        
        // 3. Håndter ekstra mellemrum
        $message = preg_replace('/\s+/', ' ', $message);
        
        return trim($message);
    }
    
    /**
     * Finder sandsynlige stavefejl og returnerer forslag
     */
    public function getSuggestions($word) {
        $suggestions = [];
        $wordLower = mb_strtolower($word);
        
        foreach ($this->commonMisspellings as $correct => $misspellings) {
            if (in_array($wordLower, $misspellings)) {
                $suggestions[] = $correct;
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Beregner Levenshtein distance for fuzzy matching
     */
    public function similarity($str1, $str2) {
        $str1 = mb_strtolower($str1);
        $str2 = mb_strtolower($str2);
        
        $len1 = mb_strlen($str1);
        $len2 = mb_strlen($str2);
        
        if ($len1 == 0) return $len2;
        if ($len2 == 0) return $len1;
        
        $distance = levenshtein($str1, $str2);
        $maxLength = max($len1, $len2);
        
        // Return similarity score 0-1 (1 = identical)
        return 1 - ($distance / $maxLength);
    }
    
    /**
     * Finder bedste match blandt kandidater
     */
    public function findBestMatch($input, $candidates, $threshold = 0.75) {
        $inputLower = mb_strtolower($input);
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($candidates as $candidate) {
            $score = $this->similarity($inputLower, mb_strtolower($candidate));
            
            if ($score > $bestScore && $score >= $threshold) {
                $bestScore = $score;
                $bestMatch = $candidate;
            }
        }
        
        return ['match' => $bestMatch, 'score' => $bestScore];
    }
    
    /**
     * Tilføjer manglende mellemrum mellem ord
     */
    private function fixMissingSpaces($message) {
        $patterns = [
            '/barnetslov/' => 'barnets lov',
            '/aktindsigt/' => 'akt indsigt',
            '/ankestyrelsen/' => 'anke styrelsen',
            '/forældremyndighed/' => 'forældremyndighed',
            '/socialrådgiver/' => 'social rådgiver',
            '/hvordanhar/' => 'hvordan har',
            '/hvordanger/' => 'hvordan er',
            '/kanjeg/' => 'kan jeg'
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $message = preg_replace($pattern, $replacement, $message);
        }
        
        return $message;
    }
    
    /**
     * Identificerer om besked sandsynligvis har stavefejl
     */
    public function hasLikelyErrors($message) {
        $words = explode(' ', mb_strtolower($message));
        $errorCount = 0;
        
        foreach ($words as $word) {
            // Check if word exists in misspelling list
            foreach ($this->commonMisspellings as $correct => $misspellings) {
                if (in_array($word, $misspellings)) {
                    $errorCount++;
                    break;
                }
            }
        }
        
        // If more than 20% of words have errors, likely has mistakes
        return ($errorCount / max(count($words), 1)) > 0.2;
    }
}
