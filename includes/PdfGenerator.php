<?php
/**
 * PDF Generator Helper
 * Genererer PDF dokumenter til klager, aktindsigt, etc.
 */

namespace RTF\Platform;

class PdfGenerator {
    
    /**
     * Generer PDF dokument
     */
    public static function generate($content, $filename = 'dokument.pdf', $options = []) {
        if (!class_exists('\Mpdf\Mpdf')) {
            throw new \Exception('mPDF ikke installeret. Kør: composer require mpdf/mpdf');
        }
        
        $defaults = [
            'format' => 'A4',
            'margin_left' => 20,
            'margin_right' => 15,
            'margin_top' => 20,
            'margin_bottom' => 15,
            'header' => '',
            'footer' => '',
            'author' => 'Ret til Familie Platform',
            'title' => $filename
        ];
        
        $options = array_merge($defaults, $options);
        
        try {
            $mpdf = new \Mpdf\Mpdf([
                'format' => $options['format'],
                'margin_left' => $options['margin_left'],
                'margin_right' => $options['margin_right'],
                'margin_top' => $options['margin_top'],
                'margin_bottom' => $options['margin_bottom'],
                'default_font' => 'dejavusans'
            ]);
            
            // Metadata
            $mpdf->SetAuthor($options['author']);
            $mpdf->SetTitle($options['title']);
            $mpdf->SetCreator('Ret til Familie Borger Platform');
            
            // Header og footer
            if (!empty($options['header'])) {
                $mpdf->SetHeader($options['header']);
            }
            
            if (!empty($options['footer'])) {
                $mpdf->SetFooter($options['footer']);
            } else {
                $mpdf->SetFooter('{PAGENO} / {nb}');
            }
            
            // CSS styling
            $css = self::getDefaultCss();
            $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
            
            // Indhold
            $mpdf->WriteHTML($content);
            
            return [
                'success' => true,
                'mpdf' => $mpdf,
                'filename' => $filename
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'PDF generering fejl: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generer klage PDF
     */
    public static function generateComplaint($data) {
        $html = '
        <div class="document-header">
            <h1>Klage</h1>
            <p class="date">Dato: ' . htmlspecialchars($data['date'] ?? date('d-m-Y')) . '</p>
        </div>
        
        <div class="section">
            <h2>Klager</h2>
            <p><strong>Navn:</strong> ' . htmlspecialchars($data['name'] ?? 'Ikke angivet') . '</p>
            <p><strong>Adresse:</strong> ' . htmlspecialchars($data['address'] ?? 'Ikke angivet') . '</p>
            <p><strong>Email:</strong> ' . htmlspecialchars($data['email'] ?? 'Ikke angivet') . '</p>
        </div>
        
        <div class="section">
            <h2>Klagen vedrører</h2>
            <p>' . nl2br(htmlspecialchars($data['subject'] ?? 'Ikke angivet')) . '</p>
        </div>
        
        <div class="section">
            <h2>Sagsfremstilling</h2>
            <p>' . nl2br(htmlspecialchars($data['description'] ?? 'Ikke angivet')) . '</p>
        </div>
        
        <div class="section">
            <h2>Klagepunkter</h2>
            ' . (isset($data['complaint_points']) ? '<ol>' . implode('', array_map(function($point) {
                return '<li>' . nl2br(htmlspecialchars($point)) . '</li>';
            }, $data['complaint_points'])) . '</ol>' : '<p>Ingen klagepunkter angivet</p>') . '
        </div>
        
        <div class="section">
            <h2>Ønsket resultat</h2>
            <p>' . nl2br(htmlspecialchars($data['desired_outcome'] ?? 'Ikke angivet')) . '</p>
        </div>
        
        <div class="footer-section">
            <p>Med venlig hilsen</p>
            <br><br>
            <p>______________________________</p>
            <p>' . htmlspecialchars($data['name'] ?? '') . '</p>
        </div>
        ';
        
        return self::generate($html, 'klage_' . date('Y-m-d') . '.pdf', [
            'title' => 'Klage',
            'footer' => 'Genereret via Ret til Familie Platform | Side {PAGENO} af {nb}'
        ]);
    }
    
    /**
     * Generer aktindsigt anmodning PDF
     */
    public static function generateActindsigt($data) {
        $html = '
        <div class="document-header">
            <h1>Anmodning om aktindsigt</h1>
            <p class="date">Dato: ' . htmlspecialchars($data['date'] ?? date('d-m-Y')) . '</p>
        </div>
        
        <div class="section">
            <p><strong>Til:</strong> ' . htmlspecialchars($data['recipient'] ?? 'Ikke angivet') . '</p>
        </div>
        
        <div class="section">
            <p><strong>Fra:</strong></p>
            <p>' . htmlspecialchars($data['name'] ?? 'Ikke angivet') . '</p>
            <p>' . htmlspecialchars($data['address'] ?? 'Ikke angivet') . '</p>
            <p>' . htmlspecialchars($data['email'] ?? 'Ikke angivet') . '</p>
        </div>
        
        <div class="section">
            <h2>Anmodning</h2>
            <p>Med henvisning til Forvaltningslovens § 9 og Offentlighedslovens § 4, 
            anmoder jeg hermed om aktindsigt i følgende:</p>
            <p>' . nl2br(htmlspecialchars($data['request_details'] ?? 'Ikke angivet')) . '</p>
        </div>
        
        <div class="section">
            <h2>Sagsreference</h2>
            <p><strong>Sagsnummer:</strong> ' . htmlspecialchars($data['case_number'] ?? 'Ikke angivet') . '</p>
            <p><strong>Vedrørende:</strong> ' . htmlspecialchars($data['regarding'] ?? 'Ikke angivet') . '</p>
        </div>
        
        <div class="section">
            <p>Jeg ønsker at modtage materialet elektronisk til ovennævnte email.</p>
            <p>Jf. Offentlighedslovens § 7 skal I træffe afgørelse om aktindsigt snarest muligt og senest 7 arbejdsdage fra modtagelsen af anmodningen.</p>
        </div>
        
        <div class="footer-section">
            <p>Med venlig hilsen</p>
            <br><br>
            <p>______________________________</p>
            <p>' . htmlspecialchars($data['name'] ?? '') . '</p>
        </div>
        ';
        
        return self::generate($html, 'aktindsigt_' . date('Y-m-d') . '.pdf', [
            'title' => 'Anmodning om aktindsigt',
            'footer' => 'Genereret via Ret til Familie Platform | Side {PAGENO} af {nb}'
        ]);
    }
    
    /**
     * Default CSS styling til PDF
     */
    private static function getDefaultCss() {
        return '
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11pt;
            color: #000000;
            line-height: 1.6;
        }
        
        .document-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        
        .document-header h1 {
            font-size: 24pt;
            color: #2563eb;
            margin: 0 0 10px 0;
        }
        
        .document-header .date {
            font-size: 10pt;
            color: #666666;
            margin: 0;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section h2 {
            font-size: 14pt;
            color: #1e3a8a;
            margin-bottom: 10px;
            border-bottom: 1px solid #dbeafe;
            padding-bottom: 5px;
        }
        
        .section p {
            margin: 5px 0;
        }
        
        .section ol {
            margin: 10px 0;
            padding-left: 25px;
        }
        
        .section li {
            margin-bottom: 10px;
        }
        
        .footer-section {
            margin-top: 50px;
        }
        
        strong {
            color: #1e3a8a;
        }
        ';
    }
    
    /**
     * Download PDF til browser
     */
    public static function download($mpdf, $filename) {
        $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
    }
    
    /**
     * Gem PDF til fil
     */
    public static function save($mpdf, $filepath) {
        $mpdf->Output($filepath, \Mpdf\Output\Destination::FILE);
    }
}
