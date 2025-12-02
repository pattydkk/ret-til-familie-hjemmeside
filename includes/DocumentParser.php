<?php
/**
 * Document Parsing Helper
 * Håndterer parsing af PDF, DOCX og andre dokumenttyper
 */

namespace RTF\Platform;

class DocumentParser {
    
    /**
     * Parse dokument baseret på filtype
     */
    public static function parse($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'pdf':
                return self::parsePdf($filePath);
            case 'docx':
                return self::parseDocx($filePath);
            case 'txt':
                return self::parseTxt($filePath);
            default:
                throw new \Exception('Ikke-understøttet filtype: ' . $extension);
        }
    }
    
    /**
     * Parse PDF dokument
     */
    private static function parsePdf($filePath) {
        if (!class_exists('\Smalot\PdfParser\Parser')) {
            throw new \Exception('PDF parser ikke installeret. Kør: composer require smalot/pdfparser');
        }
        
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            
            // Rens tekst
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);
            
            return [
                'text' => $text,
                'metadata' => [
                    'pages' => count($pdf->getPages()),
                    'title' => $pdf->getDetails()['Title'] ?? '',
                    'author' => $pdf->getDetails()['Author'] ?? ''
                ],
                'success' => true
            ];
        } catch (\Exception $e) {
            return [
                'text' => '',
                'error' => 'PDF parsing fejl: ' . $e->getMessage(),
                'success' => false
            ];
        }
    }
    
    /**
     * Parse DOCX dokument
     */
    private static function parseDocx($filePath) {
        if (!class_exists('\PhpOffice\PhpWord\IOFactory')) {
            throw new \Exception('PHPWord ikke installeret. Kør: composer require phpoffice/phpword');
        }
        
        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
            $text = '';
            
            // Gennemgå alle sections
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $elementClass = get_class($element);
                    
                    // Text elements
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    }
                    // TextRun elements
                    elseif (method_exists($element, 'getElements')) {
                        foreach ($element->getElements() as $textElement) {
                            if (method_exists($textElement, 'getText')) {
                                $text .= $textElement->getText();
                            }
                        }
                        $text .= "\n";
                    }
                }
            }
            
            // Rens tekst
            $text = preg_replace('/\n{3,}/', "\n\n", $text);
            $text = trim($text);
            
            return [
                'text' => $text,
                'metadata' => [
                    'sections' => count($phpWord->getSections()),
                    'characters' => strlen($text)
                ],
                'success' => true
            ];
        } catch (\Exception $e) {
            return [
                'text' => '',
                'error' => 'DOCX parsing fejl: ' . $e->getMessage(),
                'success' => false
            ];
        }
    }
    
    /**
     * Parse TXT dokument
     */
    private static function parseTxt($filePath) {
        try {
            $text = file_get_contents($filePath);
            
            // Konverter encoding til UTF-8 hvis nødvendigt
            if (!mb_check_encoding($text, 'UTF-8')) {
                $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            }
            
            return [
                'text' => $text,
                'metadata' => [
                    'lines' => substr_count($text, "\n"),
                    'characters' => strlen($text)
                ],
                'success' => true
            ];
        } catch (\Exception $e) {
            return [
                'text' => '',
                'error' => 'TXT læsefejl: ' . $e->getMessage(),
                'success' => false
            ];
        }
    }
    
    /**
     * Ekstraher metadata fra dokument
     */
    public static function extractMetadata($filePath) {
        $metadata = [
            'filename' => basename($filePath),
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'size_bytes' => filesize($filePath),
            'size_mb' => round(filesize($filePath) / (1024 * 1024), 2),
            'mime_type' => mime_content_type($filePath),
            'created_at' => date('Y-m-d H:i:s', filectime($filePath)),
            'modified_at' => date('Y-m-d H:i:s', filemtime($filePath))
        ];
        
        return $metadata;
    }
    
    /**
     * Validér dokument før parsing
     */
    public static function validate($filePath) {
        $errors = [];
        
        // Check file exists
        if (!file_exists($filePath)) {
            $errors[] = 'Fil ikke fundet';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check file size (max 10MB)
        $maxSize = 10 * 1024 * 1024;
        if (filesize($filePath) > $maxSize) {
            $errors[] = 'Fil er for stor (max 10MB)';
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'docx', 'txt'];
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'Ikke-understøttet filtype (tilladt: PDF, DOCX, TXT)';
        }
        
        // Check MIME type
        $mimeType = mime_content_type($filePath);
        $allowedMimes = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ];
        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = 'Ugyldig MIME type';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Detektér og censurér personidentificerbare oplysninger (PII)
     * GDPR-compliant anonymisering
     */
    public static function censorPII($text) {
        $censored = $text;
        $detected_pii = [];
        
        // 1. CPR-numre (DDMMYY-XXXX eller DDMMYYXXXX)
        $cpr_pattern = '/\b(\d{2})(\d{2})(\d{2})-?(\d{4})\b/';
        if (preg_match_all($cpr_pattern, $censored, $matches)) {
            foreach ($matches[0] as $cpr) {
                $detected_pii[] = ['type' => 'cpr', 'value' => $cpr];
            }
            $censored = preg_replace($cpr_pattern, '[CPR-NUMMER CENSURERET]', $censored);
        }
        
        // 2. Email adresser
        $email_pattern = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/';
        if (preg_match_all($email_pattern, $censored, $matches)) {
            foreach ($matches[0] as $email) {
                $detected_pii[] = ['type' => 'email', 'value' => $email];
            }
            $censored = preg_replace($email_pattern, '[EMAIL CENSURERET]', $censored);
        }
        
        // 3. Telefonnumre (danske og svenske formater)
        $phone_patterns = [
            '/\b(\+45|0045)?\s?(\d{2}\s?\d{2}\s?\d{2}\s?\d{2})\b/', // Dansk: +45 12 34 56 78
            '/\b(\+46|0046)?\s?(\d{2,3}\s?\d{3}\s?\d{2}\s?\d{2})\b/' // Svensk: +46 70 123 45 67
        ];
        foreach ($phone_patterns as $pattern) {
            if (preg_match_all($pattern, $censored, $matches)) {
                foreach ($matches[0] as $phone) {
                    $detected_pii[] = ['type' => 'phone', 'value' => $phone];
                }
                $censored = preg_replace($pattern, '[TELEFON CENSURERET]', $censored);
            }
        }
        
        // 4. Adresser (vejnavn + husnummer)
        // Match: "Hovedgaden 12" eller "Storgatan 45A"
        $address_pattern = '/\b([A-ZÆØÅ][a-zæøåäöü]+(?:vej|gade|allé|stræde|gatan|vägen|gränd))\s+(\d+[A-Z]?)\b/i';
        if (preg_match_all($address_pattern, $censored, $matches)) {
            foreach ($matches[0] as $address) {
                $detected_pii[] = ['type' => 'address', 'value' => $address];
            }
            $censored = preg_replace($address_pattern, '[ADRESSE CENSURERET]', $censored);
        }
        
        // 5. Postnumre med bynavn
        // Match: "2100 København Ø" eller "11428 Stockholm"
        $postal_pattern = '/\b(\d{4,5})\s+([A-ZÆØÅÄÖ][a-zæøåäöü\s]+)\b/';
        if (preg_match_all($postal_pattern, $censored, $matches)) {
            foreach ($matches[0] as $postal) {
                $detected_pii[] = ['type' => 'postal', 'value' => $postal];
            }
            $censored = preg_replace($postal_pattern, '[POSTNR OG BY CENSURERET]', $censored);
        }
        
        // 6. Fødselsdatoer (flere formater)
        $birthdate_patterns = [
            '/\b(?:født|fødselsdag|fødselsdato|birthday|date of birth)[\s:]+([\d]{1,2}[\.\/\-][\d]{1,2}[\.\/\-][\d]{4})\b/i',
            '/\b([\d]{1,2}\.\s?(?:januar|februar|marts|april|maj|juni|juli|august|september|oktober|november|december)\s?[\d]{4})\b/i'
        ];
        foreach ($birthdate_patterns as $pattern) {
            if (preg_match_all($pattern, $censored, $matches)) {
                foreach ($matches[1] as $date) {
                    $detected_pii[] = ['type' => 'birthdate', 'value' => $date];
                }
                $censored = preg_replace($pattern, '[FØDSELSDATO CENSURERET]', $censored);
            }
        }
        
        // 7. Navne (simplificeret - match ord der starter med stort og står sammen)
        // Dette er konservativt for at undgå false positives
        // Kun censurer hvis der er "navn:" eller lignende markør
        $name_pattern = '/\b(?:navn|name|barnets navn|forælders navn|mothers name|fathers name)[\s:]+([A-ZÆØÅÄÖ][a-zæøåäöü]+(?:\s[A-ZÆØÅÄÖ][a-zæøåäöü]+){1,3})\b/i';
        if (preg_match_all($name_pattern, $censored, $matches)) {
            foreach ($matches[1] as $name) {
                $detected_pii[] = ['type' => 'name', 'value' => $name];
            }
            $censored = preg_replace($name_pattern, 'Navn: [NAVN CENSURERET]', $censored);
        }
        
        return [
            'censored_text' => $censored,
            'detected_pii' => $detected_pii,
            'censored_count' => count($detected_pii)
        ];
    }
    
    /**
     * Detektér PII uden at censurere (til preview)
     */
    public static function detectPII($text) {
        $result = self::censorPII($text);
        return [
            'detected_pii' => $result['detected_pii'],
            'count' => $result['censored_count']
        ];
    }
    
    /**
     * Parse OG censurer dokument i én operation
     */
    public static function parseAndCensor($filePath, $censorPII = true) {
        $parsed = self::parse($filePath);
        
        if (!$parsed['success']) {
            return $parsed;
        }
        
        if ($censorPII) {
            $censorResult = self::censorPII($parsed['text']);
            $parsed['original_text'] = $parsed['text'];
            $parsed['text'] = $censorResult['censored_text'];
            $parsed['detected_pii'] = $censorResult['detected_pii'];
            $parsed['censored_count'] = $censorResult['censored_count'];
        }
        
        return $parsed;
    }
}
