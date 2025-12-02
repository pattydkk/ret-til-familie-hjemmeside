<?php
namespace KateAI\Core;

class Normalizer {
    private $replacements = [
        'kommunen' => 'kommune',
        'sagsbehandleren' => 'sagsbehandler',
        'børneværnet' => 'børneværn',
        'familieretten' => 'familieret',
        'ankestyrelsen' => 'ankestyrelse',
        'aktindsigten' => 'aktindsigt',
        'klagen' => 'klage',
        'afgørelsen' => 'afgørelse',
        'handleplanen' => 'handleplan',
        'undersøgelsen' => 'undersøgelse',
    ];
    
    public function normalize($text) {
        // Lowercase
        $text = mb_strtolower($text, 'UTF-8');
        
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // Common replacements
        foreach ($this->replacements as $from => $to) {
            $text = str_replace($from, $to, $text);
        }
        
        // Remove punctuation except important ones
        $text = preg_replace('/[^\w\sæøåÆØÅ?\-]/', '', $text);
        
        return $text;
    }
    
    public function extractKeywords($text) {
        $stopwords = ['og', 'eller', 'at', 'i', 'på', 'med', 'til', 'af', 'en', 'et', 'det', 'er', 'kan', 'skal', 'vil', 'har', 'jeg', 'du', 'han', 'hun', 'vi', 'de', 'mig', 'dig', 'ham', 'hende', 'os', 'dem'];
        
        $words = explode(' ', $this->normalize($text));
        $keywords = array_filter($words, function($word) use ($stopwords) {
            return strlen($word) > 2 && !in_array($word, $stopwords);
        });
        
        return array_values($keywords);
    }
}
