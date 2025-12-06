<?php
namespace KateAI\Core;

/**
 * Kate AI - Conversational Module
 * Håndterer casual samtaler, hilsner, følelser og social interaktion
 */
class ConversationalModule {
    
    private $greetings = [
        'da' => [
            'patterns' => [
                'hej', 'hej kate', 'hejsa', 'goddag', 'god morgen', 'god aften', 
                'hey', 'hi', 'hallå', 'hallo', 'dav', 'mojn'
            ],
            'responses' => [
                'Hej! Dejligt at høre fra dig. Hvordan kan jeg hjælpe dig i dag?',
                'Hej! Jeg er her for at hjælpe. Hvad har du brug for?',
                'Hej! Godt at se dig. Hvad kan jeg gøre for dig?',
                'Hej! Jeg er klar til at hjælpe. Hvad tænker du på?'
            ]
        ],
        'sv' => [
            'patterns' => [
                'hej', 'hejsan', 'god dag', 'god morgon', 'god kväll', 'hallå', 'tjena'
            ],
            'responses' => [
                'Hej! Vad kan jag hjälpa dig med idag?',
                'Hej! Jag är här för att hjälpa. Vad behöver du?',
                'Hej! Roligt att höra från dig. Vad funderar du på?'
            ]
        ]
    ];
    
    private $wellbeing = [
        'da' => [
            'patterns' => [
                'hvordan har du det', 'hvordan går det', 'hvordan går det med dig',
                'hvordan er du', 'har du det godt', 'går det godt',
                'er du okay', 'er du ok', 'hvordan'
            ],
            'responses' => [
                'Tak for at spørge! Jeg har det godt og er klar til at hjælpe dig. Hvordan har DU det?',
                'Jeg er i topform og klar til at hjælpe! Men vigtigst - hvordan har du det?',
                'Det er sødt at du spørger! Jeg fungerer godt. Men lad os tale om dig - hvordan går det med dig?',
                'Jeg er i god form, tak! Men jeg er mere interesseret i at høre hvordan DU har det?'
            ]
        ],
        'sv' => [
            'patterns' => [
                'hur mår du', 'hur har du det', 'hur går det', 'mår du bra'
            ],
            'responses' => [
                'Tack för att du frågar! Jag mår bra. Hur mår DU?',
                'Jag är i toppform! Men viktigast - hur har du det?'
            ]
        ]
    ];
    
    private $userWellbeingResponses = [
        'positive' => [
            'patterns' => ['godt', 'fint', 'rigtig godt', 'super', 'fantastisk', 'perfekt', 'dejligt', 'skønt', 'glædeligt'],
            'responses' => [
                'Dejligt at høre! 😊 Det glæder mig virkelig. Hvad kan jeg hjælpe dig med i dag?',
                'Det lyder godt! Jeg er glad for at høre det. Hvordan kan jeg være til hjælp?',
                'Så godt! 😊 Hvad bringer dig her i dag?'
            ]
        ],
        'neutral' => [
            'patterns' => ['okay', 'det går', 'nogenlunde', 'sådan', 'det går vel', 'går', 'fint nok'],
            'responses' => [
                'Jeg forstår. Nogle dage er sådan. Er der noget jeg kan hjælpe dig med?',
                'Det lyder som en almindelig dag. Hvad kan jeg gøre for dig?',
                'Jeg er her hvis du har brug for hjælp med noget. Hvad tænker du på?'
            ]
        ],
        'negative' => [
            'patterns' => ['dårligt', 'skidt', 'ikke så godt', 'hårdt', 'svært', 'træt', 'frustreret', 'ked af det', 'trist', 'bekymret', 'stresset'],
            'responses' => [
                'Jeg er ked af at høre det. 💙 Jeg er her for at hjælpe. Vil du fortælle mig hvad der er svært?',
                'Det lyder hårdt. Jeg er her for at støtte dig. Hvad kan jeg hjælpe med?',
                'Jeg forstår godt det kan være svært. 💙 Lad mig prøve at hjælpe. Hvad er der sket?',
                'Det er okay at have det svært. Jeg er her for dig. Vil du dele hvad der bekymrer dig?'
            ]
        ]
    ];
    
    private $thanks = [
        'da' => [
            'patterns' => ['tak', 'mange tak', 'tak for hjælpen', 'tusind tak', 'super tak', 'tak kate', 'takker'],
            'responses' => [
                'Det var så lidt! 😊 Jeg er her altid når du har brug for hjælp.',
                'Velbekomme! Kom endelig tilbage hvis du har flere spørgsmål.',
                'Det glæder mig at jeg kunne hjælpe! 😊',
                'Så lidt! Tøv ikke med at kontakte mig igen.'
            ]
        ],
        'sv' => [
            'patterns' => ['tack', 'tack så mycket', 'stort tack', 'tack för hjälpen'],
            'responses' => [
                'Varsågod! 😊 Jag är här om du behöver mer hjälp.',
                'Det var så lite! Kom gärna tillbaka om du har fler frågor.'
            ]
        ]
    ];
    
    private $apologies = [
        'da' => [
            'patterns' => ['undskyld', 'beklager', 'sorry', 'undskyld mig'],
            'responses' => [
                'Du behøver ikke undskylde! 😊 Jeg er her for at hjælpe dig.',
                'Ingen bekymringer! Hvad kan jeg gøre for dig?',
                'Alt i orden! Hvordan kan jeg hjælpe?'
            ]
        ]
    ];
    
    private $jokes = [
        'da' => [
            'patterns' => ['fortæl en joke', 'fortæl en vittighed', 'kan du fortælle en joke', 'joke'],
            'responses' => [
                'Haha, jeg er bedre til juridisk rådgivning end jokes! 😄 Men jeg kan fortælle dig en ting: Barnets Lov er ikke til at grine ad - den er her for at beskytte børn! Hvad kan jeg hjælpe dig med?',
                'Jokes er ikke min stærke side, men jeg er MEGET god til at hjælpe med Barnets Lov! 😊 Hvad skal du bruge?'
            ]
        ]
    ];
    
    private $capabilities = [
        'da' => [
            'patterns' => ['hvad kan du', 'hvad kan du hjælpe med', 'hvad kan du gøre', 'hvad ved du', 'hvad er dine evner'],
            'responses' => [
                "Jeg kan hjælpe med mange ting! 😊\n\n" .
                "📚 **Juridisk rådgivning**: Barnets Lov, Serviceloven, Forvaltningsloven, og mere\n" .
                "📄 **Dokument analyse**: Jeg kan analysere afgørelser, handleplaner, undersøgelser\n" .
                "⚖️ **Klager**: Guide til hvordan du klager over afgørelser\n" .
                "📋 **Aktindsigt**: Hjælp til at søge aktindsigt i din sag\n" .
                "💬 **Samtale**: Jeg er også her bare for at snakke hvis du har en dårlig dag\n" .
                "🎯 **98% præcision**: Mine svar er baseret på faktisk dansk lovgivning\n\n" .
                "Hvad vil du gerne have hjælp til?"
            ]
        ]
    ];
    
    private $smallTalk = [
        'da' => [
            'patterns' => ['hvem er du', 'fortæl om dig selv', 'hvad er du', 'er du en robot', 'er du rigtig'],
            'responses' => [
                "Jeg er Kate, din AI assistent! 🤖💙\n\n" .
                "Jeg er skabt for at hjælpe forældre som dig med juridiske spørgsmål om Barnets Lov, socialret og meget mere. " .
                "Jeg har adgang til hele den danske lovgivning og kan analysere dokumenter, guide dig gennem klageprocesser, " .
                "og bare være her når du har brug for nogen at tale med.\n\n" .
                "Jeg er en AI, men jeg forstår godt hvor hårdt det kan være at navigere i systemet. " .
                "Jeg er her 24/7 for at støtte dig. 💙\n\n" .
                "Hvad kan jeg hjælpe dig med i dag?"
            ]
        ]
    ];
    
    /**
     * Detecterer om besked er en casual samtale
     */
    public function isConversational($message) {
        $messageLower = mb_strtolower($message);
        $messageLower = trim($messageLower);
        
        // Check all conversational patterns
        $allPatterns = array_merge(
            $this->greetings['da']['patterns'],
            $this->wellbeing['da']['patterns'],
            $this->thanks['da']['patterns'],
            $this->apologies['da']['patterns'],
            $this->jokes['da']['patterns'],
            $this->capabilities['da']['patterns'],
            $this->smallTalk['da']['patterns']
        );
        
        foreach ($allPatterns as $pattern) {
            // Exact match or starts with pattern
            if ($messageLower === $pattern || 
                strpos($messageLower, $pattern) === 0 ||
                strpos($messageLower, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Genererer conversational response
     */
    public function generateResponse($message, $context = []) {
        $messageLower = mb_strtolower(trim($message));
        
        // Check hver kategori
        $categories = [
            'greetings' => $this->greetings['da'],
            'wellbeing' => $this->wellbeing['da'],
            'thanks' => $this->thanks['da'],
            'apologies' => $this->apologies['da'],
            'jokes' => $this->jokes['da'],
            'capabilities' => $this->capabilities['da'],
            'smallTalk' => $this->smallTalk['da']
        ];
        
        foreach ($categories as $category => $data) {
            foreach ($data['patterns'] as $pattern) {
                if (strpos($messageLower, $pattern) !== false) {
                    return $this->getRandomResponse($data['responses']);
                }
            }
        }
        
        // Check user wellbeing responses
        foreach ($this->userWellbeingResponses as $mood => $data) {
            foreach ($data['patterns'] as $pattern) {
                if (strpos($messageLower, $pattern) !== false) {
                    return $this->getRandomResponse($data['responses']);
                }
            }
        }
        
        // Fallback
        return "Jeg er ikke helt sikker på hvad du mener, men jeg er her for at hjælpe! Kan du omformulere dit spørgsmål? 😊";
    }
    
    /**
     * Returnerer tilfældig response for variation
     */
    private function getRandomResponse($responses) {
        return $responses[array_rand($responses)];
    }
    
    /**
     * Tilføjer empati til response baseret på kontekst
     */
    public function addEmpathy($response, $context = []) {
        $empathyPhrases = [
            'Jeg forstår godt det kan være svært. ',
            'Det lyder hårdt. ',
            'Jeg er ked af at høre det. ',
            'Det er ikke let, men jeg er her for at hjælpe. '
        ];
        
        // Hvis brugeren virker frustreret eller ked af det
        if (isset($context['user_mood']) && $context['user_mood'] === 'negative') {
            $prefix = $empathyPhrases[array_rand($empathyPhrases)];
            return $prefix . $response;
        }
        
        return $response;
    }
    
    /**
     * Detector brugerens humør fra besked
     */
    public function detectMood($message) {
        $messageLower = mb_strtolower($message);
        
        $negativeWords = ['dårlig', 'trist', 'ked', 'frustreret', 'vred', 'harm', 'desperat', 'hjælp', 'hvad gør jeg', 'panic', 'bang'];
        $positiveWords = ['god', 'glad', 'lykkelig', 'dejlig', 'super', 'fantastisk', 'perfekt'];
        
        $negativeScore = 0;
        $positiveScore = 0;
        
        foreach ($negativeWords as $word) {
            if (strpos($messageLower, $word) !== false) {
                $negativeScore++;
            }
        }
        
        foreach ($positiveWords as $word) {
            if (strpos($messageLower, $word) !== false) {
                $positiveScore++;
            }
        }
        
        if ($negativeScore > $positiveScore) {
            return 'negative';
        } elseif ($positiveScore > $negativeScore) {
            return 'positive';
        }
        
        return 'neutral';
    }
}
