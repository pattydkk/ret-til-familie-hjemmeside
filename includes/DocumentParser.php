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
}
