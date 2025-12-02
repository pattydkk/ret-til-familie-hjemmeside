<?php
/**
 * Law Explainer
 * 
 * Forklarer lovgivning på almindeligt dansk med konkrete eksempler.
 * Fokus på Barnets Lov og relateret lovgivning.
 * 
 * @package KateAI
 * @subpackage Core
 */

namespace KateAI\Core;

class LawExplainer
{
    private $knowledgeBase;
    private $webSearcher;
    private $databaseManager;
    private $logger;

    /**
     * Omfattende database over Barnets Lov paragraffer
     */
    private $barnetsLovDatabase = [
        '47' => [
            'title' => 'Barnets ret til at blive hørt',
            'law_text' => 'Børn har ret til at blive hørt i alle sager, der vedrører dem. Samtalen skal tilpasses barnets alder og modenhed.',
            'plain_danish' => 'Dit barn skal altid spørges til deres mening i sager der handler om dem. Samtalen skal passe til hvor gammelt og modent barnet er.',
            'examples' => [
                'Før anbringelse skal barnet altid snakke med en sagsbehandler',
                'Barnet skal høres om samvær og handleplan',
                'Selv små børn skal høres på deres niveau',
                'Barnets mening skal tages alvorligt, men er ikke altid afgørende'
            ],
            'your_rights' => [
                'Du kan spørge hvornår børnesamtalen er gennemført',
                'Du kan se hvad barnet har sagt (med visse begrænsninger)',
                'Du kan klage hvis barnet ikke er blevet hørt ordentligt'
            ],
            'common_questions' => [
                'Q: Må jeg være med til børnesamtalen? A: Nej, barnet skal snakke alene med sagsbehandler',
                'Q: Hvad hvis mit barn ikke vil snakke? A: Kommunen skal stadig forsøge, men kan ikke tvinge barnet',
                'Q: Bestemmer barnet selv? A: Nej, men barnets mening vægter tungt'
            ]
        ],
        '50' => [
            'title' => 'Forældreansvar og inddragelse',
            'law_text' => 'Forældre med forældremyndighed skal inddrages aktivt i alle beslutninger om barnet.',
            'plain_danish' => 'Du skal være med til at beslutte ting der handler om dit barn. Kommunen må ikke beslutte noget alene uden at høre dig først.',
            'examples' => [
                'Du skal høres før afgørelser træffes (partshøring)',
                'Du skal inddrages i handleplanen',
                'Dine synspunkter skal fremgå af sagen',
                'Kommunen skal samarbejde med dig'
            ],
            'your_rights' => [
                'Ret til partshøring før afgørelse',
                'Ret til at blive hørt ved alle møder',
                'Ret til at komme med forslag og indsigelser',
                'Ret til at dine synspunkter fremgår af sagen'
            ],
            'common_questions' => [
                'Q: Hvad hvis kommunen beslutter uden at høre mig? A: Det er en sagsbehandlingsfejl - klag!',
                'Q: Skal kommunen følge mine ønsker? A: Nej, men de skal lytte og begrunde hvis de ikke følger dem',
                'Q: Kan jeg kræve bestemte foranstaltninger? A: Du kan foreslå, men kommunen beslutter'
            ]
        ],
        '51' => [
            'title' => 'Ret til bisidder',
            'law_text' => 'Du har ret til at lade dig bistå af en bisidder ved møder med kommunen.',
            'plain_danish' => 'Du må tage en person med dig til møder med kommunen - en bisidder. Det kan være hvem som helst du har tillid til.',
            'examples' => [
                'Du kan tage din mor, søster eller en ven med',
                'Du kan have en frivillig fra en forening med',
                'Du kan have en advokat eller socialrådgiver med',
                'Bisidderen må gerne tale på dine vegne'
            ],
            'your_rights' => [
                'Du kan altid have bisidder med - kommunen må ikke nægte det',
                'Du kan selv vælge hvem der skal være din bisidder',
                'Bisidderen får samme information som dig',
                'Bisidderen må tale på dine vegne hvis du ønsker det'
            ],
            'common_questions' => [
                'Q: Skal jeg betale for bisidder? A: Nej, ikke hvis det er en ven/frivillig. Advokat kan koste penge.',
                'Q: Kan kommunen afvise min bisidder? A: Kun i helt særlige tilfælde',
                'Q: Hvor finder jeg en bisidder? A: Kontakt Ret til Familie eller lignende forening'
            ]
        ],
        '76' => [
            'title' => 'Anbringelse uden samtykke',
            'law_text' => 'Anbringelse uden samtykke kan kun ske når der er åbenbar risiko for barnets sundhed eller udvikling, og mindre indgribende foranstaltninger ikke er tilstrækkelige.',
            'plain_danish' => 'Kommunen kan kun tvangsanbringe dit barn hvis det er helt nødvendigt for at beskytte barnet, og hvis andre former for hjælp ikke er nok.',
            'examples' => [
                'Alvorlig omsorgssvigt over længere tid',
                'Vold eller seksuelle overgreb',
                'Barnets sundhed eller udvikling er i alvorlig fare',
                'Mindre hjælp som familiebehandling har ikke virket'
            ],
            'betingelser' => [
                '1. Åbenbar risiko for barnets sundhed/udvikling',
                '2. På grund af utilstrækkelig omsorg, vold, overgreb eller andre problemer',
                '3. Mindre indgribende foranstaltninger er utilstrækkelige',
                '4. Det er nødvendigt af hensyn til barnets bedste'
            ],
            'your_rights' => [
                'Ret til skriftlig og fyldestgørende begrundet afgørelse',
                'Ret til partshøring før afgørelse',
                'Ret til at klage inden 4 uger',
                'Ret til bisidder og advokat',
                'Ret til samvær (medmindre det skader barnet)'
            ],
            'common_questions' => [
                'Q: Kan kommunen bare tage mit barn? A: Nej, der skal være en afgørelse med begrundelse',
                'Q: Hvad kan jeg gøre? A: Klag inden 4 uger, få bisidder/advokat, dokumentér dit samarbejde',
                'Q: Kan jeg få barnet hjem igen? A: Ja, hvis forholdene ændrer sig kan du anmode om hjemgivelse'
            ]
        ],
        '83' => [
            'title' => 'Samvær og kontakt',
            'law_text' => 'Forældre har ret til samvær med deres anbragte børn, medmindre samværet er til skade for barnet.',
            'plain_danish' => 'Du har ret til at se dit barn selvom det er anbragt. Samværet kan kun begrænses hvis det er dårligt for barnet.',
            'examples' => [
                'Åbent samvær - I ser hinanden uden nogen til stede',
                'Overvåget samvær - En voksen er til stede',
                'Kontrolleret samvær - Samvær på institution',
                'Telefon/video kontakt mellem fysisk samvær'
            ],
            'your_rights' => [
                'Ret til samvær som udgangspunkt',
                'Ret til skriftlig samværsaftale',
                'Ret til begrundelse for begrænsninger',
                'Ret til at anmode om ændring af samvær',
                'Ret til at klage over samværsbegrænsning'
            ],
            'common_questions' => [
                'Q: Hvor ofte kan jeg se mit barn? A: Det afhænger af situationen - kan være alt fra ugentligt til månedligt',
                'Q: Kan kommunen nægte mig samvær? A: Kun hvis det er til skade for barnet, og det skal begrundes',
                'Q: Hvad hvis barnet ikke vil have samvær? A: Barnets ønske vægter, men er ikke altid afgørende'
            ]
        ],
        '140' => [
            'title' => 'Handleplan',
            'law_text' => 'Der skal udarbejdes en handleplan senest 4 måneder efter anbringelse eller iværksættelse af hjælpeforanstaltning.',
            'plain_danish' => 'Kommunen skal lave en handleplan senest 4 måneder efter barnet er anbragt. Planen skal beskrive mål og hvem der gør hvad.',
            'examples' => [
                'Handleplanen skal indeholde konkrete mål for barnets udvikling',
                'Der skal stå hvem der er ansvarlig for hvad',
                'Planen skal beskrive samvær',
                'Der skal være tidsplan for opfølgning'
            ],
            'skal_indeholde' => [
                '✓ Formål og konkrete mål',
                '✓ Hvilke indsatser iværksættes',
                '✓ Hvem er ansvarlig for hvad',
                '✓ Samværsordning',
                '✓ Barnets perspektiv og ønsker',
                '✓ Tidsplan for revision (mindst hver 6. måned)'
            ],
            'your_rights' => [
                'Du skal inddrages i udarbejdelsen',
                'Dine synspunkter skal fremgå',
                'Du skal have kopi af planen',
                'Du kan komme med forslag til ændringer',
                'Planen skal revideres mindst hver 6. måned'
            ],
            'common_questions' => [
                'Q: Hvad hvis jeg er uenig i handleplanen? A: Skriv dine indsigelser i planen og anmod om møde',
                'Q: Kan jeg klage over handleplanen? A: Nej, men du kan klage over afgørelser baseret på planen',
                'Q: Hvad hvis planen ikke følges? A: Kontakt sagsbehandler og dokumentér problemer'
            ]
        ],
        '168' => [
            'title' => 'Klageadgang',
            'law_text' => 'Afgørelser efter Barnets Lov kan påklages til Ankestyrelsen inden 4 uger.',
            'plain_danish' => 'Du kan klage over kommunens beslutninger til Ankestyrelsen. Du har 4 uger til at klage.',
            'examples' => [
                'Klage over anbringelse',
                'Klage over samværsbegrænsning',
                'Klage over afslag på hjælpeforanstaltninger',
                'Klage over manglende inddragelse'
            ],
            'your_rights' => [
                '4 ugers klagefrist fra afgørelsens modtagelse',
                'Klagen sendes til kommunen (ikke Ankestyrelsen)',
                'Kommunen skal videresende klagen',
                'Du kan anmode om opsættende virkning',
                'Det er gratis at klage'
            ],
            'common_questions' => [
                'Q: Hvad hvis fristen er overskredet? A: Søg om genoptagelse med begrundelse',
                'Q: Hvor sender jeg klagen? A: Til kommunen - de videresender til Ankestyrelsen',
                'Q: Hvor lang tid tager det? A: Ofte 3-6 måneder'
            ]
        ]
    ];

    /**
     * Constructor
     */
    public function __construct($knowledgeBase, $webSearcher, $databaseManager, $logger = null)
    {
        $this->knowledgeBase = $knowledgeBase;
        $this->webSearcher = $webSearcher;
        $this->databaseManager = $databaseManager;
        $this->logger = $logger;
    }

    /**
     * Forklar en lovparagraf
     * 
     * @param string $law Lovnavn (f.eks. 'barnets_lov', 'forvaltningsloven')
     * @param string $paragraph Paragraf (f.eks. '76', '47')
     * @param array $options Options array
     *   - user_id: (optional) For at gemme i database
     *   - include_examples: (bool) Inkluder eksempler (default true)
     *   - include_case_law: (bool) Søg efter praksis (default false)
     * 
     * @return array Forklaring med lovtekst, dansk forklaring, eksempler
     */
    public function explainLaw($law, $paragraph, $options = [])
    {
        $userId = $options['user_id'] ?? null;
        $includeExamples = $options['include_examples'] ?? true;
        $includeCaseLaw = $options['include_case_law'] ?? false;

        // Check om vi har paragraffen i vores database
        if ($law === 'barnets_lov' && isset($this->barnetsLovDatabase[$paragraph])) {
            $explanation = $this->buildExplanationFromDatabase($law, $paragraph, $includeExamples);
        } else {
            // Søg online og byg forklaring
            $explanation = $this->buildExplanationFromSearch($law, $paragraph, $includeExamples);
        }

        // Tilføj praksis hvis ønsket
        if ($includeCaseLaw) {
            $explanation['case_law'] = $this->searchCaseLaw($law, $paragraph);
        }

        // Gem til database hvis user_id
        if ($userId) {
            $savedId = $this->saveLawExplanationToDatabase($userId, $law, $paragraph, $explanation);
            $explanation['saved_id'] = $savedId;
        }

        return $explanation;
    }

    /**
     * Byg forklaring fra intern database
     */
    private function buildExplanationFromDatabase($law, $paragraph, $includeExamples)
    {
        $data = $this->barnetsLovDatabase[$paragraph];

        $explanation = [
            'law' => 'Barnets Lov',
            'paragraph' => '§ ' . $paragraph,
            'title' => $data['title'],
            'law_text' => $data['law_text'],
            'plain_danish' => $data['plain_danish'],
            'source' => 'internal_database',
            'confidence' => 98
        ];

        if ($includeExamples && isset($data['examples'])) {
            $explanation['examples'] = $data['examples'];
        }

        if (isset($data['betingelser'])) {
            $explanation['conditions'] = $data['betingelser'];
        }

        if (isset($data['skal_indeholde'])) {
            $explanation['requirements'] = $data['skal_indeholde'];
        }

        if (isset($data['your_rights'])) {
            $explanation['your_rights'] = $data['your_rights'];
        }

        if (isset($data['common_questions'])) {
            $explanation['common_questions'] = $data['common_questions'];
        }

        // Tilføj link til retsinformation
        $explanation['official_link'] = 'https://www.retsinformation.dk/eli/lta/2022/1146#id' . $this->getParagraphAnchor($paragraph);

        return $explanation;
    }

    /**
     * Byg forklaring fra web-søgning
     */
    private function buildExplanationFromSearch($law, $paragraph, $includeExamples)
    {
        $query = $law . " § " . $paragraph;
        
        try {
            $results = $this->webSearcher->search($query, ['source' => 'retsinformation']);
            
            $explanation = [
                'law' => $this->getLawName($law),
                'paragraph' => '§ ' . $paragraph,
                'title' => 'Paragraf ' . $paragraph,
                'plain_danish' => 'Søg efter denne paragraf på retsinformation.dk for fuldstændig lovtekst.',
                'source' => 'web_search',
                'search_results' => $results,
                'confidence' => 60
            ];

            if (!empty($results[0]['url'])) {
                $explanation['official_link'] = $results[0]['url'];
            }

            return $explanation;

        } catch (\Exception $e) {
            return [
                'law' => $this->getLawName($law),
                'paragraph' => '§ ' . $paragraph,
                'error' => 'Kunne ikke finde forklaring',
                'message' => 'Søg på retsinformation.dk: ' . $query,
                'confidence' => 0
            ];
        }
    }

    /**
     * Søg efter praksis
     */
    private function searchCaseLaw($law, $paragraph)
    {
        $query = $law . " § " . $paragraph . " praksis ankestyrelsen";
        
        try {
            $results = $this->webSearcher->search($query, ['source' => 'ast']);
            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Forklar juridisk begreb
     * 
     * @param string $term Begrebet der skal forklares
     * @param array $options Options
     * @return array Forklaring
     */
    public function explainTerm($term, $options = [])
    {
        $commonTerms = [
            'barnets bedste' => [
                'term' => 'Barnets bedste',
                'explanation' => 'Alle beslutninger om børn skal træffes ud fra hvad der er bedst for barnet - ikke hvad der er nemmest for kommunen eller bedst for forældrene.',
                'examples' => [
                    'Barnet skal bo der hvor det trives bedst',
                    'Barnets trivsel vægter tungere end forældres ønsker',
                    'Stabilitet og kontinuitet er vigtig for barnets bedste'
                ],
                'in_practice' => 'I praksis vurderes barnets trivsel, tilknytning, stabilitet og udvikling. Kommunen skal kunne dokumentere hvorfor en beslutning er i barnets bedste.'
            ],
            'åbenbar risiko' => [
                'term' => 'Åbenbar risiko',
                'explanation' => 'Der skal være en klar og tydelig risiko for at barnet tager skade. Det er ikke nok at der "måske" er en risiko - det skal være tydeligt og dokumenteret.',
                'examples' => [
                    'Alvorlig omsorgssvigt over tid',
                    'Dokumenteret vold eller overgreb',
                    'Barnet fejludvikler sig markant',
                    'Forældre kan ikke dække barnets grundlæggende behov'
                ],
                'in_practice' => 'Kommunen skal have konkret dokumentation - ikke bare bekymring. Der skal være faglige vurderinger der viser risikoen er åbenbar.'
            ],
            'mindste indgribens princip' => [
                'term' => 'Mindste indgribens princip',
                'explanation' => 'Kommunen skal altid vælge den løsning der griber mindst muligt ind i familiens liv, men som stadig løser problemet.',
                'examples' => [
                    'Før anbringelse skal støtte i hjemmet forsøges',
                    'Før tvangsanbringelse skal frivillig anbringelse overvejes',
                    'Før døgnanbringelse skal aflastning forsøges',
                    'Den mindst indgribende løsning der virker skal vælges'
                ],
                'in_practice' => 'Kommunen skal dokumentere at mindre indgribende løsninger er forsøgt eller vil være utilstrækkelige. Du kan argumentere for mindre indgribende løsninger.'
            ],
            'partshøring' => [
                'term' => 'Partshøring',
                'explanation' => 'Før kommunen træffer en afgørelse skal du høres. Du skal have mulighed for at komme med dine synspunkter på sagens oplysninger.',
                'examples' => [
                    'Kommunen sender dig et brev med hvad de påtænker at beslutte',
                    'Du får en frist til at svare (typisk 7-14 dage)',
                    'Dine kommentarer skal indgå i afgørelsen',
                    'Hvis du ikke høres er det en sagsbehandlingsfejl'
                ],
                'in_practice' => 'Svar ALTID på partshøring - ellers kan kommunen træffe afgørelse uden dine synspunkter. Vær konkret og saglig i dit svar.'
            ]
        ];

        $termLower = strtolower($term);
        
        if (isset($commonTerms[$termLower])) {
            $explanation = $commonTerms[$termLower];
            $explanation['source'] = 'internal_database';
            $explanation['confidence'] = 95;
        } else {
            // Søg online
            $explanation = [
                'term' => $term,
                'explanation' => 'Søg efter dette begreb på retsinformation.dk eller ast.dk',
                'source' => 'not_found',
                'confidence' => 0
            ];
        }

        return $explanation;
    }

    /**
     * Søg i Barnets Lov
     * 
     * @param string $query Søgeord
     * @return array Søgeresultater med relevante paragraffer
     */
    public function searchBarnetsLov($query)
    {
        $results = [];
        $queryLower = strtolower($query);

        // Søg i vores database
        foreach ($this->barnetsLovDatabase as $paragraphNum => $data) {
            $score = 0;

            // Søg i titel
            if (stripos($data['title'], $query) !== false) {
                $score += 50;
            }

            // Søg i lovtekst
            if (stripos($data['law_text'], $query) !== false) {
                $score += 30;
            }

            // Søg i dansk forklaring
            if (stripos($data['plain_danish'], $query) !== false) {
                $score += 20;
            }

            // Søg i eksempler
            if (isset($data['examples'])) {
                foreach ($data['examples'] as $example) {
                    if (stripos($example, $query) !== false) {
                        $score += 10;
                        break;
                    }
                }
            }

            if ($score > 0) {
                $results[] = [
                    'paragraph' => '§ ' . $paragraphNum,
                    'title' => $data['title'],
                    'snippet' => $data['plain_danish'],
                    'score' => $score,
                    'url' => 'https://www.retsinformation.dk/eli/lta/2022/1146#id' . $this->getParagraphAnchor($paragraphNum)
                ];
            }
        }

        // Sortér efter score
        usort($results, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        return [
            'query' => $query,
            'results' => $results,
            'total' => count($results)
        ];
    }

    /**
     * Få alle vigtige paragraffer
     */
    public function getImportantParagraphs()
    {
        $important = [];

        foreach ($this->barnetsLovDatabase as $num => $data) {
            $important[] = [
                'paragraph' => '§ ' . $num,
                'title' => $data['title'],
                'summary' => $data['plain_danish']
            ];
        }

        return $important;
    }

    /**
     * Hjælpefunktion: Få lovnavn
     */
    private function getLawName($lawId)
    {
        $names = [
            'barnets_lov' => 'Barnets Lov',
            'forvaltningsloven' => 'Forvaltningsloven',
            'offentlighedsloven' => 'Offentlighedsloven'
        ];

        return $names[$lawId] ?? ucfirst(str_replace('_', ' ', $lawId));
    }

    /**
     * Hjælpefunktion: Få anchor ID for paragraf
     */
    private function getParagraphAnchor($paragraph)
    {
        // Simplified - i praksis ville dette være mere komplekst
        return 'p' . $paragraph;
    }

    /**
     * Gem lovforklaring til database
     */
    private function saveLawExplanationToDatabase($userId, $law, $paragraph, $explanation)
    {
        return $this->databaseManager->saveLawExplanation(
            $userId,
            $law,
            $paragraph,
            $explanation['title'] ?? 'Ukendt',
            json_encode($explanation),
            $explanation['confidence'] ?? 50
        );
    }
}
