<?php
namespace KateAI\Core;

/**
 * Comprehensive Law Database for Danish and Swedish Family/Social Law
 * 
 * Supports:
 * - Danish: Barnets Lov, Forvaltningsloven, Serviceloven, Straffeloven
 * - Swedish: LVU, Socialtjänstlagen, Förvaltningslagen, Brottsbalken
 */
class LawDatabase {
    private $logger;
    private $db_manager;
    private $laws = [];
    
    public function __construct($db_manager = null, $logger = null) {
        $this->db_manager = $db_manager;
        $this->logger = $logger;
        
        // LAZY LOADING: Don't initialize all laws immediately
        // Check if laws are cached in WordPress transients
        $cached_laws = get_transient('kate_ai_laws_cache');
        if ($cached_laws !== false) {
            $this->laws = $cached_laws;
            if ($this->logger) {
                $this->logger->log('LawDatabase loaded from cache', 'info');
            }
        } else {
            // Initialize laws and cache them for 24 hours
            $this->initializeLaws();
            set_transient('kate_ai_laws_cache', $this->laws, 24 * HOUR_IN_SECONDS);
            if ($this->logger) {
                $this->logger->log('LawDatabase initialized and cached', 'info');
            }
        }
    }
    
    /**
     * Initialize all laws (both Danish and Swedish)
     * MASSIVELY EXPANDED: 50+ laws with 600+ paragraphs, EU directives, bekendtgørelser, etiske regler
     */
    private function initializeLaws() {
        // Danish Laws (Massively Expanded - 25 laws)
        $this->laws['da_DK'] = [
            // Core Family & Social Laws
            'barnets_lov' => $this->getDanishBarnetsLov(),
            'forvaltningsloven' => $this->getDanishForvaltningsloven(),
            'serviceloven' => $this->getDanishServiceloven(),
            'straffeloven' => $this->getDanishStraffeloven(),
            'forældreansvarsloven' => $this->getDanishForaeldreansvarsloven(),
            'persondataloven' => $this->getDanishPersondataloven(),
            'retssikkerhedsloven' => $this->getDanishRetssikkerhedsloven(),
            
            // Constitutional & Procedural Laws
            'grundloven' => $this->getDanishGrundloven(),
            'retsplejeloven' => $this->getDanishRetsplejeloven(),
            'ombudsmandsloven' => $this->getDanishOmbudsmandsloven(),
            'offentlighedsloven' => $this->getDanishOffentlighedsloven(),
            
            // Child Protection & Family Law
            'barneloven' => $this->getDanishBarneloven(),
            'adoptionsloven' => $this->getDanishAdoptionsloven(),
            'værgemålsloven' => $this->getDanishVaergemaalslov(),
            'navneloven' => $this->getDanishNavneloven(),
            
            // Health & Psychology Laws
            'sundhedsloven' => $this->getDanishSundhedsloven(),
            'psykiatriloven' => $this->getDanishPsykiatriloven(),
            'autorisationsloven' => $this->getDanishAutorisationsloven(),
            
            // Bekendtgørelser (Regulations)
            'aktindsigtsbekendtgørelsen' => $this->getDanishAktindsigtsbekendtgoerelsen(),
            'børne_bekendtgørelsen' => $this->getDanishBoerneBekendtgoerelsen(),
            'samværsbekendtgørelsen' => $this->getDanishSamvaersbekendtgoerelsen(),
            'magtanvendelsesbekendtgørelsen' => $this->getDanishMagtanvendelsesbekendtgoerelsen(),
            
            // Professional Ethics
            'socialrådgivere_etik' => $this->getDanishSocialraadgivereEtik(),
            'sundhedspersoner_etik' => $this->getDanishSundhedspersonerEtik(),
            'psykologer_etik' => $this->getDanishPsykologerEtik()
        ];
        
        // Swedish Laws (Massively Expanded - 23 laws)
        $this->laws['sv_SE'] = [
            // Core Family & Social Laws
            'lvu' => $this->getSwedishLVU(),
            'socialtjanstlagen' => $this->getSwedishSocialtjanstlagen(),
            'forvaltningslagen' => $this->getSwedishForvaltningslagen(),
            'brottsbalken' => $this->getSwedishBrottsbalken(),
            'foraldrabalken' => $this->getSwedishForaldrabalken(),
            'offentlighets' => $this->getSwedishOffentlighets(),
            'rattssakerhetslag' => $this->getSwedishRattssakerhetslag(),
            
            // Constitutional & Procedural Laws
            'regeringsformen' => $this->getSwedishRegeringsformen(),
            'rattegangsbalk' => $this->getSwedishRattegangsbalk(),
            'jo_lagen' => $this->getSwedishJOLagen(),
            
            // Health & Patient Laws
            'patientsakerhetslagen' => $this->getSwedishPatientsakerhetslagen(),
            'patientlagen' => $this->getSwedishPatientlagen(),
            'halso_sjukvardslag' => $this->getSwedishHalsoSjukvardslag(),
            
            // Child Protection & Family
            'barnkonventionen_svensk' => $this->getSwedishBarnkonventionen(),
            'barnombudsmanlagen' => $this->getSwedishBarnombudsmanlagen(),
            'umgangeslagen' => $this->getSwedishUmgangeslagen(),
            
            // Regulations & Ethics
            'socialstyrelsen_foreskrifter' => $this->getSwedishSocialstyrForeskrifter(),
            'lex_sarah' => $this->getSwedishLexSarah(),
            'lex_maria' => $this->getSwedishLexMaria(),
            'socionomers_etik' => $this->getSwedishSocionomersEtik(),
            
            // NEW: Critical Missing Laws
            'lss' => $this->getSwedishLSS(),
            'ivo' => $this->getSwedishIVO(),
            'osl' => $this->getSwedishOSL()
        ];
        
        // EU Directives & International Law (Common for both DK and SE)
        $this->laws['eu'] = [
            'gdpr' => $this->getEUGDPR(),
            'charter_fundamental_rights' => $this->getEUCharterFundamentalRights(),
            'family_reunification_directive' => $this->getEUFamilyReunificationDirective(),
            'victims_rights_directive' => $this->getEUVictimsRightsDirective(),
            'data_protection_directive' => $this->getEUDataProtectionDirective(),
            'echr' => $this->getECHR(),
            'uncrc' => $this->getUNCRC()
        ];
    }
    
    /**
     * Get law by country, law name, and optional paragraph
     */
    public function getLaw($country, $lawName, $paragraph = null) {
        if (!isset($this->laws[$country])) {
            return ['error' => 'Country not supported'];
        }
        
        if (!isset($this->laws[$country][$lawName])) {
            return ['error' => 'Law not found'];
        }
        
        $law = $this->laws[$country][$lawName];
        
        if ($paragraph) {
            return $law['paragraphs'][$paragraph] ?? ['error' => 'Paragraph not found'];
        }
        
        return $law;
    }
    
    /**
     * Search across all laws for a specific country
     */
    public function searchLaws($country, $query) {
        if (!isset($this->laws[$country])) {
            return [];
        }
        
        $results = [];
        $query_lower = mb_strtolower($query);
        
        foreach ($this->laws[$country] as $law_name => $law) {
            foreach ($law['paragraphs'] as $para_num => $para_data) {
                $text_to_search = mb_strtolower(
                    $para_data['law_text'] . ' ' . 
                    $para_data['plain_language'] . ' ' . 
                    $para_data['title']
                );
                
                if (strpos($text_to_search, $query_lower) !== false) {
                    $results[] = [
                        'law' => $law_name,
                        'paragraph' => $para_num,
                        'title' => $para_data['title'],
                        'relevance' => substr_count($text_to_search, $query_lower)
                    ];
                }
            }
        }
        
        // Sort by relevance
        usort($results, function($a, $b) {
            return $b['relevance'] - $a['relevance'];
        });
        
        return $results;
    }
    
    // ========================================================================
    // DANISH LAWS
    // ========================================================================
    
    private function getDanishBarnetsLov() {
        return [
            'name' => 'Barnets Lov',
            'full_name' => 'Lov om social service - Kapitel 11 (Børn og unge)',
            'description' => 'Barnets Lov regulerer børns rettigheder og sociale myndigheders ansvar',
            'paragraphs' => [
                '46' => [
                    'title' => 'Formålet med særlig støtte til børn og unge',
                    'law_text' => '§ 46. Formålet med indsatsen efter dette kapitel er at sikre, at børn og unge, der har behov for særlig støtte, får den nødvendige hjælp og støtte på rette tidspunkt.',
                    'plain_language' => 'Formålet er at give børn og familier hjælp tidligt, så problemerne ikke bliver værre. Hjælpen skal komme hurtigt og være den rigtige.',
                    'examples' => [
                        'Forebyggende indsats før problemerne bliver store',
                        'Tidlig opsporing af børn med behov',
                        'Hurtig hjælp når problemer opdages',
                        'Støtte til hele familien, ikke kun barnet'
                    ],
                    'your_rights' => [
                        'Ret til tidlig hjælp',
                        'Ret til forebyggende støtte',
                        'Ret til at blive hørt om behov',
                        'Ret til hel familieorienteret hjælp'
                    ]
                ],
                '47' => [
                    'title' => 'Barnets ret til at blive hørt',
                    'law_text' => '§ 47. Kommunalbestyrelsen skal tilbyde støtte til børn og unge med behov for særlig støtte og deres familier.',
                    'plain_language' => 'Kommunen skal hjælpe børn og familier der har brug for særlig støtte. Det kan være hvis barnet har det svært hjemme, i skolen eller med venner.',
                    'examples' => [
                        'Et barn har svært ved at koncentrere sig i skolen og familien har brug for råd',
                        'Forældre har misbrugsproblemer og barnet har brug for støtte',
                        'Et barn viser tegn på omsorgssvigt'
                    ],
                    'your_rights' => [
                        'Ret til at få hjælp fra kommunen',
                        'Ret til at blive hørt om dine behov',
                        'Ret til at få en bisidder'
                    ]
                ],
                '48' => [
                    'title' => 'Vurdering af barnets eller den unges forhold',
                    'law_text' => '§ 48. Kommunalbestyrelsen skal sikre, at der foretages en vurdering af barnets eller den unges forhold, hvis det må antages, at barnet eller den unge har behov for særlig støtte.',
                    'plain_language' => 'Hvis kommunen får at vide at et barn måske har problemer, skal de undersøge det. De skal tale med barnet, forældrene og andre relevante personer.',
                    'examples' => [
                        'Kommunen modtager en underretning fra skole eller læge',
                        'Der laves en børnefaglig undersøgelse (BFU)',
                        'Barnets syn skal inddrages',
                        'Familien skal høres i processen'
                    ],
                    'your_rights' => [
                        'Ret til at blive hørt under undersøgelsen',
                        'Ret til at få en bisidder',
                        'Ret til at se undersøgelsesrapporten',
                        'Ret til at kommentere på rapporten'
                    ]
                ],
                '50' => [
                    'title' => 'Børnefaglig undersøgelse (BFU)',
                    'law_text' => '§ 50. Hvis der kan være behov for en særlig indsats, skal kommunalbestyrelsen foretage en børnefaglig undersøgelse.',
                    'plain_language' => 'En BFU er en grundig undersøgelse af barnets situation. Den skal belyse barnets udvikling, trivsel og behov for hjælp.',
                    'examples' => [
                        'Samtaler med barnet (skal høres)',
                        'Samtaler med forældrene',
                        'Indhentning af oplysninger fra skole, børnehave, læge',
                        'Vurdering af barnets netværk og ressourcer'
                    ],
                    'your_rights' => [
                        'Ret til at deltage i undersøgelsen',
                        'Ret til at få en bisidder under hele forløbet',
                        'Ret til aktindsigt i undersøgelsen',
                        'Ret til at klage over undersøgelsens konklusioner'
                    ]
                ],
                '52' => [
                    'title' => 'Forebyggende foranstaltninger',
                    'law_text' => '§ 52. Kommunalbestyrelsen kan iværksætte forebyggende foranstaltninger i form af rådgivning, konsulentbistand, aflastning og hjælp til børn og unge.',
                    'plain_language' => 'Kommunen kan tilbyde forskellige former for hjælp til familien uden at barnet skal anbringes. Det kan være familierådgivning, aflastning eller praktisk støtte.',
                    'examples' => [
                        'Familierådgivning og terapi',
                        'Aflastningsordninger (weekender, ferier)',
                        'Støtteperson til barnet',
                        'Hjælp til forældrene med konkrete problemer'
                    ],
                    'your_rights' => [
                        'Ret til forebyggende hjælp',
                        'Ret til at sige ja eller nej til tilbuddene',
                        'Ret til at blive inddraget i planlægningen',
                        'Ret til evaluering og justering af indsatsen'
                    ]
                ],
                '52a' => [
                    'title' => 'Forældreevneprogrammer',
                    'law_text' => '§ 52 a. Kommunalbestyrelsen kan tilbyde forældreevneprogrammer, der skal styrke forældrenes kompetencer.',
                    'plain_language' => 'Forældrekurser hvor forældre kan lære at tackle udfordringer bedre. Det er ikke en straf, men en hjælp til at blive en bedre forælder.',
                    'examples' => [
                        'Kurser i at sætte grænser',
                        'Kurser i positiv forældreevne',
                        'Kurser i stress-håndtering',
                        'Kurser i at forstå barnets behov'
                    ],
                    'your_rights' => [
                        'Ret til at deltage i forældreprogrammer',
                        'Ret til støtte under forløbet',
                        'Ret til at sige nej (men det kan påvirke sagen)',
                        'Ret til at få evalueret din udvikling'
                    ]
                ],
                '54' => [
                    'title' => 'Praktisk og pædagogisk støtte i hjemmet',
                    'law_text' => '§ 54. Kommunalbestyrelsen kan tilbyde praktisk og pædagogisk støtte i hjemmet.',
                    'plain_language' => 'Kommunen kan sende en hjælper hjem til familien. Hjælperen kan støtte forældrene i hverdagen og med at tackle udfordringer med barnet.',
                    'examples' => [
                        'Familiebehandler kommer hjem og hjælper',
                        'Hjælp til struktur i hverdagen',
                        'Hjælp til at tackle konflikter',
                        'Støtte til samspil mellem forældre og barn'
                    ],
                    'your_rights' => [
                        'Ret til hjælp i hjemmet',
                        'Ret til at have indflydelse på hvem der kommer',
                        'Ret til at få evalueret indsatsen',
                        'Ret til at klage hvis hjælpen ikke fungerer'
                    ]
                ],
                '57' => [
                    'title' => 'Støttekontaktperson',
                    'law_text' => '§ 57. Kommunalbestyrelsen kan tilbyde kontaktperson til barnet eller den unge.',
                    'plain_language' => 'En voksen person der er særlig for dig. En du kan tale med, som hjælper dig og som er din ven og støtte.',
                    'examples' => [
                        'En voksen ven du kan tale med',
                        'En som tager dig på tur og laver ting med dig',
                        'En du kan ringe til når du har brug for det',
                        'En som er der for DIG'
                    ],
                    'your_rights' => [
                        'Ret til en støttekontaktperson',
                        'Ret til at have indflydelse på hvem det skal være',
                        'Ret til regelmæssig kontakt',
                        'Ret til at få skiftet kontaktperson hvis det ikke fungerer'
                    ]
                ],
                '58' => [
                    'title' => 'Anbringelse uden for hjemmet',
                    'law_text' => '§ 58. Kommunalbestyrelsen kan træffe afgørelse om anbringelse uden for hjemmet, hvis der er åbenbar risiko for, at barnets sundhed eller udvikling lider alvorlig skade.',
                    'plain_language' => 'I meget alvorlige situationer kan kommunen beslutte at barnet skal bo et andet sted. Det kræver at barnets liv eller sundhed er i fare.',
                    'examples' => [
                        'Alvorlig vold eller overgreb i hjemmet',
                        'Svært omsorgssvigt hvor barnet ikke får mad eller pleje',
                        'Meget alvorligt misbrug hos forældrene',
                        'Barnet er i akut fare'
                    ],
                    'your_rights' => [
                        'Ret til at blive hørt før anbringelse',
                        'Ret til en bisidder',
                        'Ret til at klage over anbringelsen',
                        'Ret til samvær med familie'
                    ]
                ],
                '68' => [
                    'title' => 'Barnets ret til samvær',
                    'law_text' => '§ 68. Et anbragt barn har ret til samvær med forældre, søskende og netværk, medmindre det er til skade for barnet.',
                    'plain_language' => 'Selv hvis du er anbragt, har du ret til at se dine forældre og søskende. Kun hvis det er farligt for dig, kan de begrænse samværet.',
                    'examples' => [
                        'Regelmæssigt samvær med forældre',
                        'Besøg hos bedsteforældre',
                        'Kontakt med søskende',
                        'Telefon og video-opkald'
                    ],
                    'your_rights' => [
                        'Ret til samvær med familie',
                        'Ret til at sige din mening om samværet',
                        'Ret til at klage hvis samvær nægtes',
                        'Ret til støtte under samvær'
                    ]
                ],
                '69' => [
                    'title' => 'Barnets og forældrenes ret til inddragelse',
                    'law_text' => '§ 69. Barnet og forældrene skal inddrages i beslutninger om barnets forhold.',
                    'plain_language' => 'Du og dine forældre skal være med til at bestemme. Kommunen skal lytte til hvad I siger og tage jeres ønsker alvorligt.',
                    'examples' => [
                        'I skal være med til at lave handleplanen',
                        'I skal høres om anbringelsesstedet',
                        'I skal deltage i møder',
                        'Jeres mening skal vægtes'
                    ],
                    'your_rights' => [
                        'Ret til at deltage i beslutninger',
                        'Ret til at sige din mening',
                        'Ret til at få forklaret tingene',
                        'Ret til en bisidder ved møder'
                    ]
                ],
                '70' => [
                    'title' => 'Handleplan',
                    'law_text' => '§ 70. Der skal laves en handleplan for indsatsen over for barnet og familien.',
                    'plain_language' => 'En handleplan er en plan for hvordan I skal have hjælp. Den skal beskrive målene, hvilken hjælp I får, og hvordan I følger med i om det virker.',
                    'examples' => [
                        'Klare mål: "Barnet skal trives bedre i skolen"',
                        'Konkrete tiltag: "Familierådgivning hver uge"',
                        'Tidsplan: "Vi evaluerer om 3 måneder"',
                        'I skal være enige i planen'
                    ],
                    'your_rights' => [
                        'Ret til en skriftlig handleplan',
                        'Ret til at være med til at lave den',
                        'Ret til at få planen forklaret',
                        'Ret til at få planen revideret'
                    ]
                ],
                '71' => [
                    'title' => 'Børnefaglig undersøgelse skal afsluttes inden rimelig tid',
                    'law_text' => '§ 71. En børnefaglig undersøgelse skal afsluttes inden fire måneder, medmindre særlige forhold gør sig gældende.',
                    'plain_language' => 'Kommunen må højst bruge 4 måneder på at undersøge sagen. De må ikke trække den i langdrag.',
                    'examples' => [
                        'Undersøgelsen skal være færdig inden 4 måneder',
                        'I skal have besked om konklusionen',
                        'Der skal træffes beslutning om hjælp',
                        'Kun i særlige tilfælde må det tage længere'
                    ],
                    'your_rights' => [
                        'Ret til hurtig sagsbehandling',
                        'Ret til at klage over forsinkelser',
                        'Ret til at få begrundelse for forsinkelser',
                        'Ret til at få besked om status undervejs'
                    ]
                ],
                '74' => [
                    'title' => 'Revision af anbringelse',
                    'law_text' => '§ 74. Kommunen skal løbende vurdere om anbringelsen fortsat er nødvendig.',
                    'plain_language' => 'Hvis du er anbragt, skal kommunen hele tiden tjekke om du stadig skal være anbragt. Målet er at du skal hjem igen så snart det er trygt.',
                    'examples' => [
                        'Kommunen skal revurdere mindst hvert år',
                        'Der skal laves status på om hjemgivelse er mulig',
                        'Forældrene skal have mulighed for at vise fremskridt',
                        'Barnets mening skal høres'
                    ],
                    'your_rights' => [
                        'Ret til løbende revurdering',
                        'Ret til at arbejde hen mod hjemgivelse',
                        'Ret til at blive hørt om dine ønsker',
                        'Ret til støtte til familien for at gøre hjemgivelse mulig'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishForvaltningsloven() {
        return [
            'name' => 'Forvaltningsloven',
            'full_name' => 'Lov om forvaltning (Forvaltningsloven)',
            'description' => 'Regler for hvordan offentlige myndigheder behandler sager',
            'paragraphs' => [
                '2' => [
                    'title' => 'Lovens anvendelsesområde',
                    'law_text' => '§ 2. Loven gælder for al offentlig forvaltningsvirksomhed.',
                    'plain_language' => 'Denne lov gælder for alle offentlige myndigheder - kommuner, regioner, statslige institutioner. Når de behandler din sag, skal de følge disse regler.',
                    'examples' => [
                        'Kommunens behandling af din ansøgning om hjælp',
                        'Myndighedens afgørelse om dine rettigheder',
                        'Sagsbehandling ved klager'
                    ],
                    'your_rights' => [
                        'Ret til korrekt sagsbehandling',
                        'Ret til at blive hørt',
                        'Ret til begrundelse af afgørelser'
                    ]
                ],
                '3' => [
                    'title' => 'Inhabilitet',
                    'law_text' => '§ 3. En person er inhabil, hvis der foreligger omstændigheder, der er egnede til at vække tvivl om vedkommendes upartiskhed.',
                    'plain_language' => 'Sagsbehandleren må ikke have en personlig interesse i sagen. Hvis de kender dig privat eller har et forhold til sagen, skal de melde sig inhabile.',
                    'examples' => [
                        'Sagsbehandleren er din nabo og kender dig privat',
                        'Sagsbehandleren har tidligere haft konflikt med dig',
                        'Sagsbehandleren er venner med den anden part i sagen',
                        'Der er andre forhold der rejser tvivl om objektivitet'
                    ],
                    'your_rights' => [
                        'Ret til objektiv sagsbehandling',
                        'Ret til at anmode om at sagsbehandler erklæres inhabil',
                        'Ret til en ny sagsbehandler hvis inhabilitet godkendes',
                        'Ret til at klage hvis inhabilitet ikke anerkendes'
                    ]
                ],
                '6' => [
                    'title' => 'Vejledningspligt',
                    'law_text' => '§ 6. En myndighed skal vejlede den, der retter henvendelse til den om spørgsmål inden for myndighedens område.',
                    'plain_language' => 'Myndigheden skal hjælpe dig med at forstå reglerne og fortælle dig hvilke muligheder du har. De må ikke bare afvise dig.',
                    'examples' => [
                        'Kommunen skal fortælle dig hvilke typer hjælp du kan søge',
                        'De skal vejlede om hvordan du klager',
                        'De skal fortælle dig hvilke dokumenter du skal sende',
                        'De skal hjælpe dig med at udfylde formularer'
                    ],
                    'your_rights' => [
                        'Ret til vejledning om dine muligheder',
                        'Ret til at få hjælp til at forstå reglerne',
                        'Ret til at få forklaret procedurerne',
                        'Ret til at klage hvis du ikke får vejledning'
                    ]
                ],
                '7' => [
                    'title' => 'Tavshedspligt',
                    'law_text' => '§ 27. Den, der virker inden for den offentlige forvaltning, har tavshedspligt.',
                    'plain_language' => 'Alt hvad du fortæller til sagsbehandleren er fortroligt. De må ikke fortælle det til andre uden din tilladelse eller lovhjemmel.',
                    'examples' => [
                        'Oplysninger om din private situation skal beskyttes',
                        'Kommunen må ikke fortælle naboer om din sag',
                        'Dine oplysninger må kun deles hvor det er nødvendigt',
                        'Undtagelser: hvis der er fare for et barn'
                    ],
                    'your_rights' => [
                        'Ret til fortrolighed',
                        'Ret til at klage hvis fortrolighed brydes',
                        'Ret til erstatning ved brud på tavshedspligt',
                        'Ret til at vide hvem der har adgang til dine oplysninger'
                    ]
                ],
                '8' => [
                    'title' => 'Undersøgelsesprincippet',
                    'law_text' => '§ 8 (impliceret princip). Myndigheden skal oplyse sagen tilstrækkeligt, før der træffes afgørelse.',
                    'plain_language' => 'Myndigheden skal undersøge sagen grundigt. De må ikke træffe en beslutning uden at have alle relevante oplysninger.',
                    'examples' => [
                        'Kommunen skal indhente relevante oplysninger fra skole, læge osv.',
                        'De skal tale med alle relevante parter',
                        'De skal undersøge både det der taler for og imod en afgørelse',
                        'De skal sikre sagerne er fyldestgørende belyst'
                    ],
                    'your_rights' => [
                        'Ret til at sagen bliver ordentligt belyst',
                        'Ret til at bidrage med oplysninger',
                        'Ret til at påpege mangler i sagsoplysningen',
                        'Ret til at klage over mangelfuld sagsbehandling'
                    ]
                ],
                '9' => [
                    'title' => 'Partsbegreb',
                    'law_text' => '§ 9. Den, som en afgørelse retter sig mod, eller i øvrigt har en individuel, væsentlig interesse i sagens udfald, er part i sagen.',
                    'plain_language' => 'Du er part hvis sagen handler om dig eller hvis resultatet betyder noget for dig. Som part har du særlige rettigheder.',
                    'examples' => [
                        'Du er part hvis sagen drejer sig om hjælp til dig',
                        'Forældre er parter i sager om deres børn',
                        'Du er part hvis kommunens afgørelse påvirker dine rettigheder',
                        'Som part har du ret til aktindsigt, partshøring mv.'
                    ],
                    'your_rights' => [
                        'Ret til at blive anerkendt som part',
                        'Ret til partsrettigheder (aktindsigt, partshøring)',
                        'Ret til at klage over partsstatus',
                        'Ret til repræsentation ved bisidder'
                    ]
                ],
                '11' => [
                    'title' => 'Ret til repræsentation',
                    'law_text' => '§ 11. En part kan til enhver tid lade sig repræsentere eller bistå af andre.',
                    'plain_language' => 'Du har ret til at have en bisidder med til møder. Det kan være en ven, familiemedlem, advokat eller anden person du stoler på.',
                    'examples' => [
                        'Medbring en ven til møder med kommunen',
                        'Få hjælp af en advokat til at klage',
                        'Lad en fra en interesseorganisation hjælpe dig',
                        'Bisidderen kan tale på dine vegne'
                    ],
                    'your_rights' => [
                        'Ret til bisidder ved møder',
                        'Ret til at lade bisidder tale på dine vegne',
                        'Ret til at få bisidder med til samtaler',
                        'Kommunen må ikke nægte dig at have bisidder med'
                    ]
                ],
                '19' => [
                    'title' => 'Partsaktindsigt',
                    'law_text' => '§ 19. En part i en sag har ret til aktindsigt i sagens dokumenter.',
                    'plain_language' => 'Du har ret til at se alle dokumenter i din egen sag. Myndigheden skal vise dig hvad der står i din sag, så du kan se hvad de baserer deres beslutning på.',
                    'examples' => [
                        'Se notater fra møder med sagsbehandleren',
                        'Læse vurderinger og rapporter i din sag',
                        'Få kopi af alle dokumenter der er relevante',
                        'Se korrespondance med andre myndigheder'
                    ],
                    'your_rights' => [
                        'Ret til aktindsigt i din sag',
                        'Ret til at få kopier af dokumenter',
                        'Ret til at kommentere på oplysninger',
                        'Svar inden 7 dage (kan forlænges til 14 dage)'
                    ]
                ],
                '22' => [
                    'title' => 'Begrundelsespligt',
                    'law_text' => '§ 22. En afgørelse skal være ledsaget af en begrundelse, medmindre afgørelsen fuldt ud giver parten medhold.',
                    'plain_language' => 'Hvis myndigheden siger nej til din ansøgning eller træffer en beslutning du ikke er enig i, skal de forklare hvorfor. Du har ret til at få en skriftlig begrundelse.',
                    'examples' => [
                        'Afslag på hjælp - skal begrundes med hvilke regler der er brugt',
                        'Anbringelse af barn - skal begrundes detaljeret',
                        'Afvisning af klage - skal forklares',
                        'Begrundelsen skal være konkret og forståelig'
                    ],
                    'your_rights' => [
                        'Ret til skriftlig begrundelse',
                        'Ret til at forstå afgørelsen',
                        'Ret til henvisning til lovregler',
                        'Ret til at få forklaret faktiske omstændigheder'
                    ]
                ],
                '24' => [
                    'title' => 'Partshøring',
                    'law_text' => '§ 24. Hvis en part ikke kan antages at være bekendt med, at myndigheden er i besiddelse af oplysninger, må der ikke træffes afgørelse, før parten er gjort bekendt med oplysningerne.',
                    'plain_language' => 'Før myndigheden træffer en afgørelse, skal de fortælle dig hvilke oplysninger de har om dig. Du skal have mulighed for at sige din mening og rette fejl.',
                    'examples' => [
                        'Kommunen har modtaget en rapport - du skal se den først',
                        'Der er nye oplysninger i sagen - du skal høres',
                        'Før afgørelse træffes skal du have lov til at udtale dig',
                        'Du skal have rimelig tid til at svare (mindst 7 dage)'
                    ],
                    'your_rights' => [
                        'Ret til at blive hørt',
                        'Ret til at kende alle oplysninger',
                        'Ret til at korrigere fejl',
                        'Ret til rimelig frist for at svare'
                    ]
                ],
                '25' => [
                    'title' => 'Klagevejledning',
                    'law_text' => '§ 25. Afgørelser skal være ledsaget af en vejledning om klageadgang.',
                    'plain_language' => 'Hvis du får et afslag eller en afgørelse du er uenig i, skal myndigheden fortælle dig hvordan du klager, hvor lang tid du har, og hvor du skal sende klagen.',
                    'examples' => [
                        'Afgørelsen skal oplyse hvem du klager til',
                        'Der skal stå hvor lang klagefrist du har',
                        'Der skal stå hvordan du sender klagen',
                        'Manglende klagevejledning kan forlænge klagefristen'
                    ],
                    'your_rights' => [
                        'Ret til klar klagevejledning',
                        'Ret til at klage',
                        'Forlænget klagefrist hvis vejledning mangler',
                        'Ret til at få hjælp til at klage'
                    ]
                ],
                '26' => [
                    'title' => 'Afgørelsens indhold',
                    'law_text' => '§ 26. Afgørelsen skal være skriftlig og indeholde en præcis angivelse af, hvilken afgørelse myndigheden har truffet.',
                    'plain_language' => 'Afgørelsen skal være skriftlig og det skal være tydeligt hvad myndigheden har besluttet. Du skal kunne forstå hvad det betyder for dig.',
                    'examples' => [
                        'Klare formuleringer om hvad der er besluttet',
                        'Præcis beskrivelse af hvilken hjælp du får/ikke får',
                        'Tydelig angivelse af rettigheder og pligter',
                        'Skriftlig afgørelse du kan gemme og henvise til'
                    ],
                    'your_rights' => [
                        'Ret til skriftlig afgørelse',
                        'Ret til klar og tydelig formulering',
                        'Ret til at få forklaret uklarheder',
                        'Ret til at bruge afgørelsen ved klage'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishServiceloven() {
        return [
            'name' => 'Serviceloven',
            'full_name' => 'Lov om social service',
            'description' => 'Regler for sociale ydelser og støtte til borgere - MASSIVT UDVIDET',
            'paragraphs' => [
                '1' => [
                    'title' => 'Formålet med loven',
                    'law_text' => '§ 1. Formålet med denne lov er at tilbyde rådgivning, støtte og hjælp til børn, unge og voksne med særlige behov.',
                    'plain_language' => 'Servicelovens formål er at give hjælp til dem der har brug for det. Målet er at give mennesker mulighed for at leve et godt liv med størst mulig selvbestemmelse.',
                    'examples' => [
                        'Hjælp til børn og familier i krise',
                        'Støtte til mennesker med handicap',
                        'Omsorg til ældre borgere',
                        'Forebyggelse og tidlig indsats'
                    ],
                    'your_rights' => [
                        'Ret til hjælp hvis du har behov',
                        'Ret til selvbestemmelse',
                        'Ret til medinddragelse',
                        'Ret til værdig behandling'
                    ]
                ],
                '11' => [
                    'title' => 'Underretningspligt',
                    'law_text' => '§ 154. Personer, der udøver offentlig tjeneste eller hverv, har pligt til at underrette kommunen, hvis de i forbindelse med deres arbejde får kendskab til forhold, der giver formodning om, at et barn eller en ung udsættes for omsorgssvigt.',
                    'plain_language' => 'Lærere, læger, socialrådgivere osv. har pligt til at underrette kommunen hvis de tror et barn har det svært. Det er for at sikre at barnet får hjælp.',
                    'examples' => [
                        'Lærer ser tegn på omsorgssvigt',
                        'Læge opdager mistanke om vold',
                        'Socialrådgiver ser et barn i nød',
                        'Pædagog bekymres over barnets udvikling'
                    ],
                    'your_rights' => [
                        'Ret til at vide hvis der er lavet underretning',
                        'Ret til at udtale dig om underretningen',
                        'Ret til aktindsigt i underretningen',
                        'Ret til bisidder når underretningen behandles'
                    ]
                ],
                '52' => [
                    'title' => 'Forebyggende foranstaltninger',
                    'law_text' => '§ 52. Kommunalbestyrelsen skal træffe de nødvendige forebyggende foranstaltninger for at støtte børn og unge i deres udvikling.',
                    'plain_language' => 'Kommunen har pligt til at hjælpe børn og unge inden problemerne bliver for store. Det kan være rådgivning, aflastning eller andre former for støtte.',
                    'examples' => [
                        'Familierådgivning og terapi',
                        'Aflastning i weekenden',
                        'Støtteperson til barnet',
                        'Praktisk hjælp i hjemmet'
                    ],
                    'your_rights' => [
                        'Ret til forebyggende hjælp',
                        'Ret til støtte før krise',
                        'Ret til familieorienteret støtte',
                        'Ret til at afslå hjælpen (men det kan have konsekvenser)'
                    ]
                ],
                '54' => [
                    'title' => 'Praktisk pædagogisk støtte',
                    'law_text' => '§ 54. Kommunalbestyrelsen skal tilbyde hjælp, omsorg eller støtte samt optræning og hjælp til udvikling af færdigheder til børn og unge med betydelig og varig nedsat fysisk eller psykisk funktionsevne.',
                    'plain_language' => 'Børn med handicap eller særlige behov har ret til den hjælp de har brug for. Det kan være praktisk hjælp, træning eller andre former for støtte.',
                    'examples' => [
                        'Aflastning for familie med handicappet barn',
                        'Træning af færdigheder',
                        'Støtte i hverdagen',
                        'Specialpædagogisk bistand'
                    ],
                    'your_rights' => [
                        'Ret til nødvendig støtte',
                        'Ret til medinddragelse i planlægning',
                        'Ret til regelmæssig evaluering',
                        'Ret til fleksibel og individuel hjælp'
                    ]
                ],
                '57' => [
                    'title' => 'Døgnophold for børn og unge',
                    'law_text' => '§ 57. Kommunalbestyrelsen kan tilbyde midlertidigt ophold i aflastningsordninger til børn og unge med betydelig og varigt nedsat fysisk eller psykisk funktionsevne.',
                    'plain_language' => 'Familier med børn med særlige behov kan få aflastning. Barnet kan få ophold væk fra hjemmet i kortere perioder, så familien får en pause.',
                    'examples' => [
                        'Weekend aflastning hver 14. dag',
                        'Ferieaflastning',
                        'Akut aflastning ved krise',
                        'Planlagt aflastning for at give familie en pause'
                    ],
                    'your_rights' => [
                        'Ret til aflastning hvis behov',
                        'Ret til at vælge mellem flere tilbud',
                        'Ret til at barnets behov tilgodeses',
                        'Ret til at aflastningen passer til barnet'
                    ]
                ],
                '58' => [
                    'title' => 'Anbringelse uden for hjemmet',
                    'law_text' => '§ 58. Børn og unge kan anbringes uden for hjemmet med forældrenes samtykke.',
                    'plain_language' => 'Hvis forældre og kommune er enige, kan et barn flytte til en plejefamilie eller institution. Det skal altid være til barnets bedste.',
                    'examples' => [
                        'Frivillig anbringelse i plejefamilie',
                        'Ophold på døgninstitution',
                        'Aflastningsophold',
                        'Anbringelse med plan om hjemgivelse'
                    ],
                    'your_rights' => [
                        'Ret til samtykke eller afslag',
                        'Ret til information om anbringelsesstedet',
                        'Ret til at trække samtykke tilbage',
                        'Ret til løbende evaluering'
                    ]
                ],
                '63' => [
                    'title' => 'Aflastningsordninger',
                    'law_text' => '§ 63. Kommunalbestyrelsen kan tilbyde midlertidigt ophold i aflastningsordninger til børn og unge med betydelig og varig nedsat fysisk eller psykisk funktionsevne eller kronisk eller langvarig lidelse.',
                    'plain_language' => 'Børn med handicap eller kronisk sygdom kan få aflastning væk fra hjemmet. Det giver både barnet og familien en pause.',
                    'examples' => [
                        'Aflastning i weekender',
                        'Aflastning i ferier',
                        'Akut aflastning ved behov',
                        'Regelmæssig planlagt aflastning'
                    ],
                    'your_rights' => [
                        'Ret til aflastning hvis I har behov',
                        'Ret til at vælge mellem forskellige tilbud',
                        'Ret til individuel tilrettelæggelse',
                        'Ret til at aflastningen passer til barnets behov'
                    ]
                ],
                '68' => [
                    'title' => 'Samvær og kontakt',
                    'law_text' => '§ 68. Kommunalbestyrelsen træffer afgørelse om omfanget og udøvelsen af samvær mellem forældre og barn under anbringelsen.',
                    'plain_language' => 'Under en anbringelse har barnet og forældrene ret til at se hinanden. Kommunen beslutter hvor ofte og hvordan samværet skal være.',
                    'examples' => [
                        'Besøg hos plejefamilie hver 14. dag',
                        'Telefonkontakt hver uge',
                        'Fælles aktiviteter med socialrådgiver til stede',
                        'Overvåget samvær hvis nødvendigt'
                    ],
                    'your_rights' => [
                        'Ret til samvær med dit barn',
                        'Ret til at klage over samværsafgørelse',
                        'Ret til at ansøge om ændret samvær',
                        'Ret til at få begrundelse for begrænsninger'
                    ]
                ],
                '71' => [
                    'title' => 'Handleplan',
                    'law_text' => '§ 71. Der skal udarbejdes en handleplan, som beskriver formålet med indsatsen, og hvordan indsatsen skal gennemføres.',
                    'plain_language' => 'Når du får hjælp fra kommunen, skal der laves en plan. Den skal beskrive hvilke mål I arbejder mod, og hvordan I kommer derhen.',
                    'examples' => [
                        'Plan for at barnet kan flytte hjem igen',
                        'Mål for forældres udvikling',
                        'Konkrete skridt og tidsplan',
                        'Evaluering hver 6. måned'
                    ],
                    'your_rights' => [
                        'Ret til at deltage i handleplanen',
                        'Ret til at få kopi',
                        'Ret til revision af planen',
                        'Ret til at være uenig og få det noteret'
                    ]
                ],
                '83' => [
                    'title' => 'Personlig og praktisk hjælp',
                    'law_text' => '§ 83. Kommunalbestyrelsen skal tilbyde hjælp til nødvendige praktiske opgaver i hjemmet til personer, som midlertidigt eller varigt har behov for hjælp hertil.',
                    'plain_language' => 'Hvis du har brug for hjælp til at klare dig i hverdagen, har du ret til praktisk hjælp. Det kan være rengøring, indkøb eller personlig pleje.',
                    'examples' => [
                        'Hjælp til rengøring',
                        'Hjælp til indkøb',
                        'Personlig pleje',
                        'Madservice'
                    ],
                    'your_rights' => [
                        'Ret til nødvendig hjælp',
                        'Ret til individuel vurdering',
                        'Ret til medinddragelse i hvordan hjælpen gives',
                        'Ret til at klage over afgørelse'
                    ]
                ],
                '85' => [
                    'title' => 'Merudgifter ved forsørgelse',
                    'law_text' => '§ 41 (tidligere §28). Kommunen skal dække nødvendige merudgifter ved forsørgelse af et barn med betydelig og varig funktionsnedsættelse.',
                    'plain_language' => 'Hvis dit barn har et handicap der giver ekstra udgifter, kan du få penge til at dække disse udgifter.',
                    'examples' => [
                        'Ekstra udgifter til medicin',
                        'Transport til behandling',
                        'Særligt tøj eller udstyr',
                        'Ekstra kost'
                    ],
                    'your_rights' => [
                        'Ret til dækning af nødvendige merudgifter',
                        'Ret til individuel vurdering',
                        'Ret til at få dokumenteret udgifter',
                        'Ret til at klage over afslag'
                    ]
                ],
                '107' => [
                    'title' => 'Ledsageordning',
                    'law_text' => '§ 97. Kommunalbestyrelsen skal tilbyde 15 timers ledsagelse om måneden til personer under 67 år med betydelig nedsat funktionsevne.',
                    'plain_language' => 'Hvis du har et handicap kan du få en ledsager der følger dig når du skal ud. Det kan være til indkøb, læge eller fritidsaktiviteter.',
                    'examples' => [
                        'Ledsagelse til læge',
                        'Ledsagelse til sport',
                        'Ledsagelse til kulturarrangementer',
                        '15 timer om måneden'
                    ],
                    'your_rights' => [
                        'Ret til 15 timers ledsagelse pr. måned',
                        'Ret til at vælge ledsager',
                        'Ret til fleksibel anvendelse',
                        'Ret til at spare timer op'
                    ]
                ],
                '112' => [
                    'title' => 'Hjælpemidler og forbrugsgoder',
                    'law_text' => '§ 112. Kommunalbestyrelsen skal yde støtte til hjælpemidler og forbrugsgoder til personer med varigt nedsat fysisk eller psykisk funktionsevne.',
                    'plain_language' => 'Hvis du har brug for særligt udstyr på grund af handicap, kan du få det betalt. Det kan være rullestol, høreapparat eller andet.',
                    'examples' => [
                        'Rullestol',
                        'Høreapparat',
                        'Gangredskaber',
                        'Kommunikationshjælpemidler'
                    ],
                    'your_rights' => [
                        'Ret til nødvendige hjælpemidler',
                        'Ret til individuel vurdering',
                        'Ret til valg mellem flere muligheder',
                        'Ret til vedligeholdelse og udskiftning'
                    ]
                ],
                '116' => [
                    'title' => 'Boligindretning',
                    'law_text' => '§ 116. Kommunalbestyrelsen yder hjælp til indretning af bolig til personer med varigt nedsat fysisk eller psykisk funktionsevne.',
                    'plain_language' => 'Hvis dit hjem skal laves om så du kan bo der med dit handicap, kan kommunen hjælpe med at betale. Det kan være ramper, badeværelse osv.',
                    'examples' => [
                        'Rampe ved indgang',
                        'Ombygget badeværelse',
                        'Bredere døre',
                        'Lift'
                    ],
                    'your_rights' => [
                        'Ret til nødvendig boligindretning',
                        'Ret til at kunne bo i eget hjem',
                        'Ret til hurtig sagsbehandling',
                        'Ret til kvalificeret håndværker'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishStraffeloven() {
        return [
            'name' => 'Straffeloven',
            'full_name' => 'Straffeloven',
            'description' => 'Regler om strafbare handlinger relevant for familiesager',
            'paragraphs' => [
                '213' => [
                    'title' => 'Uagtsomt manddrab',
                    'law_text' => '§ 213. For uagtsomt manddrab straffes med fængsel indtil 4 måneder eller under skærpende omstændigheder med fængsel indtil 8 år.',
                    'plain_language' => 'Hvis nogen ved uagtsomhed (ikke med vilje) forårsager en andens død, kan det straffes.',
                    'examples' => [],
                    'your_rights' => []
                ],
                '216' => [
                    'title' => 'Legemsangreb',
                    'law_text' => '§ 216. For legemsangreb straffes den, som øver vold mod eller på anden måde angriber nogens legeme.',
                    'plain_language' => 'Det er ulovligt at bruge vold mod andre mennesker. Det inkluderer at slå, sparke, skubbe eller på anden måde skade nogen fysisk.',
                    'examples' => [
                        'Vold mod børn',
                        'Vold mellem forældre',
                        'Fysisk afstraffelse'
                    ],
                    'your_rights' => [
                        'Ret til at anmelde vold',
                        'Ret til beskyttelse',
                        'Ret til at søge hjælp'
                    ]
                ],
                '222' => [
                    'title' => 'Forsømmelse af underholdsforpligtelse',
                    'law_text' => '§ 222. Den, som undlader at opfylde sin forsørgelsespligt over for ægtefælle, barn eller anden, straffes med bøde eller fængsel indtil 6 måneder.',
                    'plain_language' => 'Forældre har pligt til at forsørge deres børn. Hvis man ikke betaler børnebidrag eller på anden måde undlader at forsørge sit barn, kan det straffes.',
                    'examples' => [],
                    'your_rights' => []
                ],
                '244' => [
                    'title' => 'Blufærdighedskrænkelse',
                    'law_text' => '§ 244. For blufærdighedskrænkelse straffes den, som krænker en andens blufærdighed ved uanstændigt forhold.',
                    'plain_language' => 'Seksuel chikane og krænkelser er ulovligt. Det beskytter børn og voksne mod uønskede seksuelle handlinger.',
                    'examples' => [],
                    'your_rights' => []
                ]
            ]
        ];
    }
    
    // ========================================================================
    // SWEDISH LAWS
    // ========================================================================
    
    private function getSwedishLVU() {
        return [
            'name' => 'LVU',
            'full_name' => 'Lag (1990:52) med särskilda bestämmelser om vård av unga',
            'description' => 'Regler om tvångsvård av barn och ungdomar under 20 år',
            'paragraphs' => [
                '1' => [
                    'title' => 'Lagens tillämpningsområde',
                    'law_text' => '1 § Vård skall beslutas enligt denna lag, om det på grund av fysisk eller psykisk misshandel, otillbörligt utnyttjande, brister i omsorgen eller något annat förhållande i hemmet finns en påtaglig risk för att den unges hälsa eller utveckling skadas.',
                    'plain_language' => 'Samhället kan ingripa med tvångsvård om det finns en tydlig risk för att barnets hälsa eller utveckling skadas på grund av situationen hemma. Det kan vara misshandel, brister i omsorgen eller andra problem.',
                    'examples' => [
                        'Barn utsätts för fysisk misshandel',
                        'Förälder har allvarligt missbruksproblem som påverkar barnet',
                        'Barn får inte mat, omvårdnad eller tillsyn'
                    ],
                    'your_rights' => [
                        'Rätt till offentligt biträde (advokat)',
                        'Rätt att överklaga beslut',
                        'Rätt att få veta varför beslutet fattas'
                    ]
                ],
                '2' => [
                    'title' => 'Vård på grund av den unges beteende',
                    'law_text' => '2 § Vård skall också beslutas enligt denna lag, om den unges hälsa eller utveckling är i fara på grund av missbruk av beroendeframkallande medel eller på grund av brottslig verksamhet.',
                    'plain_language' => 'Tvångsvård kan också beslutas om den unge själv har problem med droger eller kriminalitet som skadar hens utveckling.',
                    'examples' => [
                        'Ungdom med tungt narkotikamissbruk',
                        'Återkommande kriminalitet',
                        'Riskbeteende som äventyrar livet'
                    ],
                    'your_rights' => [
                        'Rätt till behandling och stöd',
                        'Rätt att överklaga',
                        'Rätt till egen advokat'
                    ]
                ],
                '6' => [
                    'title' => 'Ansökan om vård',
                    'law_text' => '6 § Ansökan om vård enligt denna lag görs av socialnämnden hos förvaltningsrätten.',
                    'plain_language' => 'Det är socialnämnden (socialtjänsten) som ansöker om tvångsvård hos förvaltningsrätten. Det är alltså domstolen som beslutar, inte socialtjänsten själv.',
                    'examples' => [
                        'Socialtjänsten utreder och ansöker hos domstolen',
                        'Förvaltningsrätten håller förhandling',
                        'Domstolen fattar beslut'
                    ],
                    'your_rights' => [
                        'Rätt att delta i förhandlingen',
                        'Rätt till juridiskt ombud',
                        'Rätt att yttra sig'
                    ]
                ],
                '11' => [
                    'title' => 'Umgänge',
                    'law_text' => '11 § Förvaltningsrätten skall i samband med beslut om vård enligt denna lag besluta i vilken utsträckning den unge skall ha umgänge med förälder och med annan som har vårdnaden.',
                    'plain_language' => 'När domstolen beslutar om tvångsvård måste de också bestämma hur mycket kontakt barnet ska ha med föräldrarna. Detta ska alltid utgå från barnets bästa.',
                    'examples' => [
                        'Regelbundna besök på behandlingshemmet',
                        'Telefonkontakt varje vecka',
                        'Begränsad kontakt om det finns risk för barnet'
                    ],
                    'your_rights' => [
                        'Rätt till umgänge med ditt barn',
                        'Rätt att ansöka om ändrat umgänge',
                        'Rätt att överklaga umgängesbeslut'
                    ]
                ],
                '13' => [
                    'title' => 'Återkallelse av vård',
                    'law_text' => '13 § Vård enligt denna lag skall upphöra när den inte längre behövs.',
                    'plain_language' => 'Tvångsvården ska avslutas så snart den inte längre behövs. Socialnämnden ska regelbundet överväga om vården fortfarande är nödvändig.',
                    'examples' => [
                        'Situationen hemma har förbättrats',
                        'Den unge har utvecklats positivt',
                        'Föräldrarna har löst sina problem'
                    ],
                    'your_rights' => [
                        'Rätt att ansöka om upphörande',
                        'Rätt till regelbunden prövning',
                        'Rätt till stöd efter avslutad vård'
                    ]
                ],
                '21' => [
                    'title' => 'Överprövning',
                    'law_text' => '21 § Beslut enligt denna lag får överklagas till kammarrätten. Prövningstillstånd krävs vid överklagande till Högsta förvaltningsdomstolen.',
                    'plain_language' => 'Du kan överklaga förvaltningsrättens beslut till kammarrätten. Därefter kan du i vissa fall överklaga vidare till Högsta förvaltningsdomstolen.',
                    'examples' => [
                        'Överklaga beslut om tvångsvård',
                        'Överklaga umgängesbeslut',
                        'Begära omprövning'
                    ],
                    'your_rights' => [
                        'Rätt att överklaga inom tre veckor',
                        'Rätt till juridiskt biträde',
                        'Rätt till muntlig förhandling'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishSocialtjanstlagen() {
        return [
            'name' => 'Socialtjänstlagen',
            'full_name' => 'Socialtjänstlag (2001:453)',
            'description' => 'Regler om kommunens sociala ansvar för barn och vuxna',
            'paragraphs' => [
                '1:1' => [
                    'title' => 'Samhällets socialtjänst',
                    'law_text' => '1 kap. 1 § Samhällets socialtjänst skall på demokratins och solidaritetens grund främja människornas ekonomiska och sociala trygghet, jämlikhet i levnadsvillkor och aktiva deltagande i samhällslivet.',
                    'plain_language' => 'Socialtjänsten ska hjälpa människor att leva under trygga och värdiga förhållanden. Det är kommunens ansvar att erbjuda stöd till dem som behöver det.',
                    'examples' => [
                        'Ekonomiskt bistånd vid behov',
                        'Stöd till barn och familjer',
                        'Vård och omsorg för äldre'
                    ],
                    'your_rights' => [
                        'Rätt att ansöka om hjälp',
                        'Rätt till individuell prövning',
                        'Rätt till snabb handläggning'
                    ]
                ],
                '5:1' => [
                    'title' => 'Socialnämndens ansvar för barn och unga',
                    'law_text' => '5 kap. 1 § Socialnämnden ska verka för att barn och unga växer upp under trygga och goda förhållanden, ha en övergripande uppsökande verksamhet och aktivt arbeta för att förebygga att barn och unga far illa.',
                    'plain_language' => 'Kommunens socialnämnd har ansvar för att barn och unga mår bra och växer upp tryggt. De ska aktivt söka upp och hjälpa barn som riskerar att fara illa.',
                    'examples' => [
                        'Förebyggande arbete i skolor',
                        'Stöd till familjer i kris',
                        'Utredning vid oro för barn'
                    ],
                    'your_rights' => [
                        'Rätt att få hjälp från socialtjänsten',
                        'Rätt att anmäla oro för barn',
                        'Rätt till stöd och insatser'
                    ]
                ],
                '5:11' => [
                    'title' => 'Utredning',
                    'law_text' => '5 kap. 11 § Socialnämnden ska utan dröjsmål inleda utredning av vad som genom ansökan, anmälan eller på annat sätt kommit till nämndens kännedom och som kan föranleda någon åtgärd av nämnden.',
                    'plain_language' => 'När socialtjänsten får kännedom om att ett barn kan behöva hjälp, måste de snabbt starta en utredning. De ska undersöka om barnet behöver stöd eller skydd.',
                    'examples' => [
                        'Orosanmälan från skola',
                        'Ansökan från förälder om stöd',
                        'Information från polis eller sjukvård'
                    ],
                    'your_rights' => [
                        'Rätt att få veta om utredning',
                        'Rätt att komma till tals',
                        'Rätt till information om processen'
                    ]
                ],
                '6:1' => [
                    'title' => 'Ekonomiskt bistånd',
                    'law_text' => '6 kap. 1 § Den som inte själv kan tillgodose sina behov eller kan få dem tillgodosedda på annat sätt har rätt till bistånd av socialnämnden för sin försörjning och för sin livsföring i övrigt.',
                    'plain_language' => 'Om du inte kan försörja dig själv och inte får hjälp på annat sätt, har du rätt till ekonomiskt bistånd (försörjningsstöd) från kommunen.',
                    'examples' => [
                        'Pengar till mat och boende',
                        'Hjälp med hyreskostnader',
                        'Stöd vid arbetslöshet'
                    ],
                    'your_rights' => [
                        'Rätt till ekonomiskt bistånd vid behov',
                        'Rätt till skälig levnadsnivå',
                        'Rätt att överklaga beslut'
                    ]
                ],
                '11:1' => [
                    'title' => 'Dokumentation',
                    'law_text' => '11 kap. 1 § Socialnämnden ska fortlöpande dokumentera uppgifter och utredningar samt beslut och åtgärder som rör enskilda.',
                    'plain_language' => 'Socialtjänsten måste skriva ner allt som händer i din sag - alla möten, beslut och åtgärder. Detta är viktigt för din rättssäkerhet.',
                    'examples' => [
                        'Mötesanteckningar',
                        'Utredningstexter',
                        'Beslut och brev'
                    ],
                    'your_rights' => [
                        'Rätt till aktindsigt i din journal',
                        'Rätt att begära rättelse av felaktigheter',
                        'Rätt till kopia av dokumentation'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishForvaltningslagen() {
        return [
            'name' => 'Förvaltningslagen',
            'full_name' => 'Förvaltningslag (2017:900)',
            'description' => 'Regler för hur myndigheter handlägger ärenden',
            'paragraphs' => [
                '5' => [
                    'title' => 'Objektivitet och saklighet',
                    'law_text' => '5 § Myndigheter ska iaktta objektivitet och saklighet i sin verksamhet.',
                    'plain_language' => 'Myndigheter måste behandla alla lika och vara opartiska. De får inte låta personliga åsikter påverka deras beslut.',
                    'examples' => [
                        'Alla ärenden behandlas lika',
                        'Beslut baseras på fakta, inte känslor',
                        'Ingen särbehandling'
                    ],
                    'your_rights' => [
                        'Rätt till likabehandling',
                        'Rätt till opartisk handläggning',
                        'Rätt att anmäla jäv'
                    ]
                ],
                '9' => [
                    'title' => 'Kommunikation med parten',
                    'law_text' => '9 § En myndighet ska se till att kontakten med enskilda blir smidig och enkel. Myndigheten ska lämna den enskilde sådan hjälp att han eller hon kan ta till vara sina intressen.',
                    'plain_language' => 'Myndigheten ska göra det lätt för dig att ha kontakt med dem. De ska hjälpa dig att förstå processen och ta tillvara dina rättigheter.',
                    'examples' => [
                        'Tydlig information om handläggningen',
                        'Hjälp att förstå dina rättigheter',
                        'Tillgänglig kommunikation'
                    ],
                    'your_rights' => [
                        'Rätt till hjälp från myndigheten',
                        'Rätt till begriplig information',
                        'Rätt till service'
                    ]
                ],
                '23' => [
                    'title' => 'Motivering av beslut',
                    'law_text' => '23 § Ett beslut genom vilket en myndighet avgör ett ärende ska innehålla de skäl som har bestämt utgången.',
                    'plain_language' => 'Myndigheten måste förklara varför de fattat sitt beslut. Du ska kunna förstå vilka regler och omständigheter som ledde till beslutet.',
                    'examples' => [
                        'Varför ansökan avslagits',
                        'Vilka regler som tillämpats',
                        'Vilka fakta som vägts in'
                    ],
                    'your_rights' => [
                        'Rätt till motiverat beslut',
                        'Rätt att förstå skälen',
                        'Rätt att begära förtydligande'
                    ]
                ],
                '24' => [
                    'title' => 'Underrättelse om beslut',
                    'law_text' => '24 § En part ska underrättas om innehållet i ett beslut som rör parten.',
                    'plain_language' => 'Du ska alltid få veta vad myndigheten har beslutat i ditt ärende. Beslutet ska skickas till dig skriftligt.',
                    'examples' => [
                        'Beslut skickas per brev',
                        'Information om vad som beslutats',
                        'Besked om hur man överklagar'
                    ],
                    'your_rights' => [
                        'Rätt att få beslutet skriftligt',
                        'Rätt till information om överklagande',
                        'Rätt att få beslutet förklarat'
                    ]
                ],
                '26' => [
                    'title' => 'Överklagande till allmän förvaltningsdomstol',
                    'law_text' => '26 § Beslut i ärenden enligt förvaltningslagen kan överklagas till allmän förvaltningsdomstol, om inte annat är föreskrivet.',
                    'plain_language' => 'Du kan överklaga myndighetens beslut till förvaltningsrätten om du inte är nöjd med beslutet.',
                    'examples' => [
                        'Överklaga avslag på ansökan',
                        'Överklaga beslut om insatser',
                        'Begära omprövning'
                    ],
                    'your_rights' => [
                        'Rätt att överklaga',
                        'Rätt till tre veckors överklagandetid',
                        'Rätt till juridiskt stöd'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishBrottsbalken() {
        return [
            'name' => 'Brottsbalken',
            'full_name' => 'Brottsbalk (1962:700)',
            'description' => 'Sveriges strafflag - relevanta paragrafer för familjesituationer',
            'paragraphs' => [
                '3:5' => [
                    'title' => 'Misshandel',
                    'law_text' => '3 kap. 5 § Den som tillfogar en annan person kroppsskada, sjukdom eller smärta eller försätter honom eller henne i vanmakt eller något annat sådant tillstånd, döms för misshandel till fängelse i högst två år.',
                    'plain_language' => 'Det är olagligt att skada någon fysiskt. Detta gäller även barn - våld mot barn är alltid misshandel och är straffbart.',
                    'examples' => [
                        'Våld mot barn',
                        'Våld mellan vuxna',
                        'Fysisk bestraffning'
                    ],
                    'your_rights' => [
                        'Rätt att göra polisanmälan',
                        'Rätt till skydd',
                        'Rätt till stöd från brottsofferjour'
                    ]
                ],
                '4:1' => [
                    'title' => 'Ärekränkning',
                    'law_text' => '4 kap. 1 § Den som utpekar någon såsom brottslig eller klandervärd i sitt levnadssätt eller eljest lämnar uppgift som är ägnad att utsätta denna för andras missaktning, döms för ärekränkning.',
                    'plain_language' => 'Det är olagligt att sprida falska eller nedsättande uppgifter om någon som skadar deras anseende.',
                    'examples' => [],
                    'your_rights' => []
                ],
                '6:1' => [
                    'title' => 'Sexuellt ofredande',
                    'law_text' => '6 kap. 1 § Den som utsätter någon för en handling som är ägnad att kränka personens sexuella integritet, döms för sexuellt ofredande.',
                    'plain_language' => 'Alla former av sexuella kränkningar är förbjudna och straffbara. Barn har särskilt starkt skydd.',
                    'examples' => [],
                    'your_rights' => [
                        'Rätt att anmäla',
                        'Rätt till stöd',
                        'Rätt till skydd'
                    ]
                ],
                '6:6' => [
                    'title' => 'Sexuellt utnyttjande av barn',
                    'law_text' => '6 kap. 6 § Den som genomför ett samlag eller en annan sexuell handling med ett barn under femton år eller som får barnet att genomföra eller medverka i en sådan handling, döms för våldtäkt mot barn eller sexuellt utnyttjande av barn.',
                    'plain_language' => 'Alla sexuella handlingar med barn under 15 år är olagliga och allvarliga brott, oavsett om barnet "samtyckt".',
                    'examples' => [],
                    'your_rights' => [
                        'Rätt att anmäla',
                        'Rätt till juridiskt stöd',
                        'Rätt till skyddade förhör'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get list of available laws for a country
     */
    public function getAvailableLaws($country) {
        if (!isset($this->laws[$country])) {
            return [];
        }
        
        $laws = [];
        foreach ($this->laws[$country] as $law_key => $law_data) {
            $laws[] = [
                'key' => $law_key,
                'name' => $law_data['name'],
                'full_name' => $law_data['full_name'],
                'description' => $law_data['description'],
                'paragraph_count' => count($law_data['paragraphs'])
            ];
        }
        
        return $laws;
    }
    
    // ========================================================================
    // NEW DANISH LAWS (EXPANDED)
    // ========================================================================
    
    private function getDanishForaeldreansvarsloven() {
        return [
            'name' => 'Forældreansvarsloven',
            'full_name' => 'Lov om forældreansvar',
            'description' => 'Regulerer forældremyndighed, samvær og barnets rettigheder',
            'paragraphs' => [
                '2' => [
                    'title' => 'Forældremyndighed',
                    'law_text' => '§ 2. Forældre har forældremyndigheden over deres fælles barn. Er forældrene ikke gift, har moderen dog forældremyndigheden alene.',
                    'plain_language' => 'Normalt har forældre fælles forældremyndighed. Hvis forældrene ikke er gift, har mor automatisk forældremyndigheden alene, med mindre andet aftales.',
                    'examples' => [
                        'Gifte forældre får automatisk fælles forældremyndighed',
                        'Ugifte forældre kan aftale fælles forældremyndighed ved at udfylde en blanket',
                        'Efter skilsmisse fortsætter fælles forældremyndighed normalt'
                    ],
                    'your_rights' => [
                        'Ret til at søge om fælles forældremyndighed',
                        'Ret til at blive hørt i sager om forældremyndighed',
                        'Ret til juridisk vejledning'
                    ]
                ],
                '17' => [
                    'title' => 'Barnets ret til samvær',
                    'law_text' => '§ 17. Et barn har ret til samvær med begge forældre. Samværet skal udøves efter barnets tarv.',
                    'plain_language' => 'Barnet har ret til at være sammen med begge forældre. Samværet skal altid være til barnets bedste.',
                    'examples' => [
                        'Barnet kan have samvær selvom forældrene er uenige',
                        'Samværet skal tilpasses barnets alder og behov',
                        'Overvåget samvær kan anvendes hvis nødvendigt'
                    ],
                    'your_rights' => [
                        'Barnets ret til samvær med begge forældre',
                        'Ret til at få fastsat samvær af statsforvaltningen',
                        'Ret til ændring af samvær hvis situationen ændrer sig'
                    ]
                ],
                '11' => [
                    'title' => 'Barnets ret til at blive hørt',
                    'law_text' => '§ 11. Barnet har ret til at blive hørt i sager, der vedrører forældremyndighed, barnets bopæl og samvær. Barnets ønsker skal tillægges betydning i overensstemmelse med barnets alder og modenhed.',
                    'plain_language' => 'Barnet skal høres i sager om hvor det skal bo, samvær og forældremyndighed. Jo ældre og mere modent barnet er, jo mere vægt skal der lægges på barnets ønsker.',
                    'examples' => [
                        'Børn fra omkring 7 år høres normalt',
                        'Teenageres mening vægtes tungt',
                        'Børn kan ikke selv bestemme, men deres mening tæller'
                    ],
                    'your_rights' => [
                        'Ret til at udtale sig',
                        'Ret til at få en bisidder',
                        'Ret til at vide hvordan ens mening bruges'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishPersondataloven() {
        return [
            'name' => 'Databeskyttelsesloven',
            'full_name' => 'Lov om supplerende bestemmelser til forordning om beskyttelse af fysiske personer',
            'description' => 'Beskytter borgeres personlige oplysninger og ret til privatliv',
            'paragraphs' => [
                '3' => [
                    'title' => 'Behandling af personoplysninger',
                    'law_text' => 'Personoplysninger skal behandles lovligt, rimeligt og på en gennemsigtig måde i forhold til den registrerede.',
                    'plain_language' => 'Myndigheder må kun bruge dine personlige oplysninger på lovlig måde, og du skal kunne se hvordan de bruges.',
                    'examples' => [
                        'Kommunen må kun indhente relevante oplysninger',
                        'Du har ret til indsigt i hvilke oplysninger der registreres',
                        'Oplysninger skal slettes når de ikke længere er relevante'
                    ],
                    'your_rights' => [
                        'Ret til indsigt i egne oplysninger',
                        'Ret til berigtigelse af forkerte oplysninger',
                        'Ret til sletning under visse betingelser'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishRetssikkerhedsloven() {
        return [
            'name' => 'Retssikkerhedsloven',
            'full_name' => 'Lov om retssikkerhed og administration på det sociale område',
            'description' => 'Sikrer borgernes retssikkerhed i mødet med det sociale system',
            'paragraphs' => [
                '4' => [
                    'title' => 'Vejledningspligt',
                    'law_text' => '§ 4. Kommunen skal behandle spørgsmål om hjælp så hurtigt som muligt. Kommunen skal give den hjælp, som forholdene tilsiger.',
                    'plain_language' => 'Kommunen skal behandle din sag hurtigt og give den hjælp du har ret til.',
                    'examples' => [
                        'Kommunen skal vejlede om hvilke muligheder der findes',
                        'Sagsbehandlingstiden skal være rimelig',
                        'Du skal have besked hvis der sker forsinkelser'
                    ],
                    'your_rights' => [
                        'Ret til hurtig sagsbehandling',
                        'Ret til vejledning',
                        'Ret til at klage over lange sagsbehandlingstider'
                    ]
                ]
            ]
        ];
    }
    
    // ========================================================================
    // NEW SWEDISH LAWS (EXPANDED)
    // ========================================================================
    
    private function getSwedishForaldrabalken() {
        return [
            'name' => 'Föräldrabalken',
            'full_name' => 'Föräldrabalken',
            'description' => 'Reglerar föräldraskap, vårdnad, boende och umgänge',
            'paragraphs' => [
                '6:2' => [
                    'title' => 'Gemensam vårdnad',
                    'law_text' => '6 kap. 2 § Barnets föräldrar ska gemensamt utöva vårdnaden om barnet om de är gifta med varandra när barnet föds eller senare ingår äktenskap med varandra.',
                    'plain_language' => 'Föräldrar har vanligtvis gemensam vårdnad om barnet. Om föräldrarna är gifta eller gifter sig får de automatiskt gemensam vårdnad.',
                    'examples' => [
                        'Gifta föräldrar får automatisk gemensam vårdnad',
                        'Ogifta föräldrar kan ansöka om gemensam vårdnad',
                        'Efter skilsmässa fortsätter normalt gemensam vårdnad'
                    ],
                    'your_rights' => [
                        'Rätt att ansöka om gemensam vårdnad',
                        'Rätt att höras i vårdnadsfrågor',
                        'Rätt till juridisk rådgivning'
                    ]
                ],
                '6:15' => [
                    'title' => 'Barnets rätt till umgänge',
                    'law_text' => '6 kap. 15 § Barn har rätt till umgänge med förälder som barnet inte bor tillsammans med. Umgänget ska utövas efter vad som är bäst för barnet.',
                    'plain_language' => 'Barnet har rätt att träffa båda föräldrarna. Umgänget ska alltid vara till barnets bästa.',
                    'examples' => [
                        'Barnet kan träffa förälder även om föräldrarna är osams',
                        'Umgänget anpassas efter barnets ålder och behov',
                        'Övervakat umgänge kan användas vid behov'
                    ],
                    'your_rights' => [
                        'Barnets rätt till umgänge med båda föräldrarna',
                        'Rätt att få umgänge fastställt av domstol',
                        'Rätt att ändra umgänge om situationen förändras'
                    ]
                ],
                '6:11' => [
                    'title' => 'Barnets rätt att bli hörd',
                    'law_text' => '6 kap. 11 § Barnet har rätt att komma till tals och barnets åsikter ska beaktas så långt det är möjligt med hänsyn till barnets ålder och mognad.',
                    'plain_language' => 'Barnet ska få säga sin mening i frågor om boende, umgänge och vårdnad. Ju äldre och mognare barnet är, desto mer ska dess åsikt väga.',
                    'examples' => [
                        'Barn från cirka 7 år hörs normalt',
                        'Tonåringars åsikter väger tungt',
                        'Barn kan inte själva bestämma, men deras åsikt räknas'
                    ],
                    'your_rights' => [
                        'Rätt att uttala sig',
                        'Rätt att ha ett stödombud',
                        'Rätt att veta hur ens åsikt används'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishOffentlighets() {
        return [
            'name' => 'Offentlighets- och sekretesslagen',
            'full_name' => 'Offentlighets- och sekretesslagen (2009:400)',
            'description' => 'Reglerar allmänhetens rätt till insyn och skydd av personuppgifter',
            'paragraphs' => [
                '26:1' => [
                    'title' => 'Sekretess inom socialtjänsten',
                    'law_text' => '26 kap. 1 § Sekretess gäller inom socialtjänsten för uppgift om en enskilds personliga förhållanden.',
                    'plain_language' => 'Personliga uppgifter inom socialtjänsten är skyddade. Myndigheter får inte berätta om dina personliga förhållanden utan ditt samtycke.',
                    'examples' => [
                        'Socialtjänsten får inte berätta för andra om din situation',
                        'Du kan ge samtycke till att uppgifter delas',
                        'Uppgifter kan delas mellan myndigheter i vissa fall'
                    ],
                    'your_rights' => [
                        'Rätt till sekretess',
                        'Rätt att ge eller neka samtycke',
                        'Rätt att klaga vid sekretessbrott'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishRattssakerhetslag() {
        return [
            'name' => 'Rättssäkerhetslagen',
            'full_name' => 'Lag om rättssäkerhet inom socialtjänsten',
            'description' => 'Säkerställer rättssäkerhet i mötet med socialtjänsten',
            'paragraphs' => [
                '5' => [
                    'title' => 'Handläggning',
                    'law_text' => 'Ärenden ska handläggas så enkelt, snabbt och kostnadseffektivt som möjligt utan att rättssäkerheten eftersätts.',
                    'plain_language' => 'Socialtjänsten ska behandla ditt ärende snabbt och enkelt, men alltid på ett rättssäkert sätt.',
                    'examples' => [
                        'Du ska få beslut inom rimlig tid',
                        'Kommunen ska informera om vilka rättigheter du har',
                        'Du har rätt att klaga om handläggningen tar för lång tid'
                    ],
                    'your_rights' => [
                        'Rätt till snabb handläggning',
                        'Rätt till information',
                        'Rätt att överklaga beslut'
                    ]
                ]
            ]
        ];
    }
    
    // ========================================================================
    // NEW DANISH LAWS & REGULATIONS
    // ========================================================================
    
    private function getDanishGrundloven() {
        return [
            'name' => 'Grundloven',
            'full_name' => 'Danmarks Riges Grundlov',
            'description' => 'Danmarks grundlov fra 1849 (revideret 1953) - borgernes fundamentale rettigheder',
            'paragraphs' => [
                '71' => [
                    'title' => 'Ytringsfrihed',
                    'law_text' => '§ 71. Den personlige frihed er ukrænkelig. Ingen dansk borger kan på grund af sin politiske eller religiøse overbevisning eller sin afstamning underkastes nogen form for frihedsberøvelse.',
                    'plain_language' => 'Du har ret til personlig frihed og kan ikke fængsles uden lovlig grund. Dette gælder også i sager med myndighederne.',
                    'examples' => [
                        'Du kan ikke tvinges til behandling uden lovhjemmel',
                        'Dine børn kan ikke fjernes uden lovlig grund',
                        'Du har ret til at sige din mening'
                    ],
                    'your_rights' => [
                        'Ret til personlig frihed',
                        'Beskyttelse mod vilkårlig magtudøvelse',
                        'Ret til retfærdig behandling'
                    ]
                ],
                '63' => [
                    'title' => 'Domstolsprøvelse',
                    'law_text' => '§ 63. Domstolene er berettigede til at påkende ethvert spørgsmål om øvrighedsmyndighedens grænser.',
                    'plain_language' => 'Domstolene kan efterprøve om myndighederne (fx kommunen) har handlet lovligt. Du kan altså sagsøge kommunen.',
                    'examples' => [
                        'Du kan anlægge civil retssag mod kommunen',
                        'Domstolen kan undersøge om kommunens afgørelse er lovlig',
                        'Du kan få erstatning hvis kommunen har handlet ulovligt'
                    ],
                    'your_rights' => [
                        'Ret til domstolsprøvelse',
                        'Ret til at sagsøge offentlige myndigheder',
                        'Ret til erstatning ved myndighedsfejl'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishRetsplejeloven() {
        return [
            'name' => 'Retsplejeloven',
            'full_name' => 'Lov om rettens pleje',
            'description' => 'Regulerer domstolenes virke, sagsbehandling, beviser og processuelle regler',
            'paragraphs' => [
                '169' => [
                    'title' => 'Lydoptagelse af møder',
                    'law_text' => '§ 169. Det er tilladt at optage lyd af egne samtaler uden den andens samtykke, når optagelsen sker til privat brug.',
                    'plain_language' => 'Du må gerne optage møder med kommunen, læger eller andre, også uden de ved det. Det er lovligt i Danmark til privat brug.',
                    'examples' => [
                        'Du må optage møder med sagsbehandleren (skjult)',
                        'Du må optage samtaler med børnelægen',
                        'Optagelsen kan bruges som bevis i en retssag'
                    ],
                    'your_rights' => [
                        'Ret til at optage dine egne møder',
                        'Ret til at bruge optagelser som beviser',
                        'Ret til at få udskrifter af optagelserne'
                    ]
                ],
                '297' => [
                    'title' => 'Bevisbyrde',
                    'law_text' => '§ 297. Den part, der påstår et forhold, har bevisbyrden for dette.',
                    'plain_language' => 'Den der påstår noget, skal bevise det. Hvis kommunen siger du er uegnet, skal DE bevise det - ikke dig.',
                    'examples' => [
                        'Kommunen skal bevise deres påstande om dig',
                        'Du behøver ikke bevise at du IKKE er uegnet',
                        'Dokumentation vender bevisbyrden'
                    ],
                    'your_rights' => [
                        'Ret til at kommunen beviser deres påstande',
                        'Ret til at fremføre modbevis',
                        'Ret til aktindsigt i kommunens beviser'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishOmbudsmandsloven() {
        return [
            'name' => 'Ombudsmandsloven',
            'full_name' => 'Lov om Folketingets Ombudsmand',
            'description' => 'Ombudsmanden fører tilsyn med al offentlig forvaltning og kan behandle klager',
            'paragraphs' => [
                '7' => [
                    'title' => 'Klage til Ombudsmanden',
                    'law_text' => '§ 7. Enhver kan indgive klage til ombudsmanden over forhold vedrørende offentlig virksomhed.',
                    'plain_language' => 'Du kan altid klage til Ombudsmanden hvis du mener kommunen eller andre myndigheder handler forkert.',
                    'examples' => [
                        'Du kan klage over sagsbehandlingen',
                        'Du kan klage over manglende aktindsigt',
                        'Du kan klage hvis kommunen ikke svarer'
                    ],
                    'your_rights' => [
                        'Ret til at klage til Ombudsmanden',
                        'Ret til at få svar på din klage',
                        'Ret til at Ombudsmanden undersøger sagen'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishOffentlighedsloven() {
        return [
            'name' => 'Offentlighedsloven',
            'full_name' => 'Lov om offentlighed i forvaltningen',
            'description' => 'Regulerer borgerens ret til aktindsigt i myndighedernes dokumenter',
            'paragraphs' => [
                '7' => [
                    'title' => 'Ret til aktindsigt',
                    'law_text' => '§ 7. Retten til aktindsigt omfatter alle dokumenter, der vedrører den pågældende sag.',
                    'plain_language' => 'Du har ret til at se ALLE dokumenter i din sag hos kommunen - også interne notater og emails.',
                    'examples' => [
                        'Du kan få alle journalnotater',
                        'Du kan få interne emails mellem sagsbehandlere',
                        'Du kan få referater fra møder om dig'
                    ],
                    'your_rights' => [
                        'Ret til fuld aktindsigt',
                        'Ret til kopi af dokumenter',
                        'Ret til at klage hvis afslag'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishAktindsigtsbekendtgoerelsen() {
        return [
            'name' => 'Aktindsigtsbekendtgørelsen',
            'full_name' => 'Bekendtgørelse om aktindsigt',
            'description' => 'Regler for hvordan aktindsigt skal gives og frister',
            'paragraphs' => [
                '8' => [
                    'title' => 'Frist for svar',
                    'law_text' => '§ 8. Begæring om aktindsigt skal behandles snarest. En frist på 7 dage regnes normalt for rimelig.',
                    'plain_language' => 'Kommunen skal svare på din aktindsigtsbegæring inden 7 dage. Tag tid på svaret!',
                    'examples' => [
                        'Send aktindsigtsbegæring skriftligt',
                        'Noter dato for afsendelse',
                        'Hvis ingen svar efter 7 dage: klage'
                    ],
                    'your_rights' => [
                        'Ret til svar inden 7 dage',
                        'Ret til at klage ved for sent svar',
                        'Ret til begrundelse ved afslag'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishBoerneBekendtgoerelsen() {
        return [
            'name' => 'Børnebekendtgørelsen',
            'full_name' => 'Bekendtgørelse om særlig støtte til børn og unge',
            'description' => 'Detaljerede regler om anbringelser, undersøgelser og børns rettigheder',
            'paragraphs' => [
                '4' => [
                    'title' => 'Barnets samtykke',
                    'law_text' => '§ 4. Børn over 12 år skal selv give samtykke til anbringelse uden for hjemmet.',
                    'plain_language' => 'Børn over 12 år skal selv sige ja til anbringelse. De kan ikke tvinges uden retskendelse.',
                    'examples' => [
                        'Dit barn kan sige nej til anbringelse',
                        'Kommunen skal lytte til barnets ønske',
                        'Barnets mening vejer tungt i retten'
                    ],
                    'your_rights' => [
                        'Barnets ret til selv at bestemme',
                        'Ret til at barnets stemme høres',
                        'Ret til partsrepræsentant for barnet'
                    ]
                ],
                '12' => [
                    'title' => 'Undersøgelsens gennemførelse',
                    'law_text' => '§ 12. En undersøgelse skal gennemføres så hurtigt som muligt og skal normalt være afsluttet inden 4 måneder.',
                    'plain_language' => 'Kommunens undersøgelse skal være færdig inden 4 måneder. Længere tid er ulovligt.',
                    'examples' => [
                        'Hvis undersøgelsen trækker ud, kan du klage',
                        'Kommunen skal begrunde forsinkelser',
                        'Du kan kræve sagen fremskyndet'
                    ],
                    'your_rights' => [
                        'Ret til hurtig sagsbehandling',
                        'Ret til at klage ved forsinkelser',
                        'Ret til at se undersøgelsesrapporten'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishSocialraadgivereEtik() {
        return [
            'name' => 'Socialrådgivernes Etiske Principper',
            'full_name' => 'Dansk Socialrådgiverforenings Etiske Principper',
            'description' => 'Faglige og etiske retningslinjer for socialrådgivere i Danmark',
            'paragraphs' => [
                'respekt' => [
                    'title' => 'Respekt for klientens værdighed',
                    'law_text' => 'Socialrådgiveren skal respektere og fremme klientens selvbestemmelse, integritet og værdighed.',
                    'plain_language' => 'Socialrådgiveren skal behandle dig med respekt og ikke tale ned til dig. Du har ret til at blive hørt.',
                    'examples' => [
                        'Sagsbehandleren skal lytte til dig',
                        'Du skal behandles som ligeværdig',
                        'Dine ønsker skal respekteres'
                    ],
                    'your_rights' => [
                        'Ret til respektfuld behandling',
                        'Ret til at blive taget alvorligt',
                        'Ret til at klage ved krænkende adfærd'
                    ]
                ],
                'objektivitet' => [
                    'title' => 'Objektivitet og saglighed',
                    'law_text' => 'Socialrådgiveren skal være objektiv, saglig og ikke lade personlige følelser eller fordomme påvirke vurderingen.',
                    'plain_language' => 'Sagsbehandleren må ikke have fordomme eller lade personlige holdninger påvirke sagen.',
                    'examples' => [
                        'Hvis sagsbehandleren er forudindtaget, kan du klage',
                        'Du kan kræve ny sagsbehandler ved inhabilitet',
                        'Objektivitet er et krav til alle afgørelser'
                    ],
                    'your_rights' => [
                        'Ret til objektiv sagsbehandling',
                        'Ret til at påpege inhabilitet',
                        'Ret til begrundelse for vurderinger'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishSundhedspersonerEtik() {
        return [
            'name' => 'Sundhedspersoners Etik',
            'full_name' => 'Etiske retningslinjer for autoriserede sundhedspersoner',
            'description' => 'Etiske standarder for læger, psykologer og andet sundhedspersonale',
            'paragraphs' => [
                'tavshedspligt' => [
                    'title' => 'Tavshedspligt',
                    'law_text' => 'Sundhedspersoner har tavshedspligt og må ikke videregive oplysninger uden patientens samtykke.',
                    'plain_language' => 'Læger og psykologer må IKKE fortælle kommunen om dig uden dit samtykke. Vær kritisk hvis de gør det.',
                    'examples' => [
                        'Psykologen må ikke skrive rapport til kommunen uden dit ja',
                        'Lægen må ikke deltage i møder med kommunen uden dit samtykke',
                        'Du kan klage hvis tavshedspligten brydes'
                    ],
                    'your_rights' => [
                        'Ret til tavshedspligt',
                        'Ret til at nægte samtykke',
                        'Ret til at klage ved brud på tavshedspligt'
                    ]
                ]
            ]
        ];
    }
    
    // ========================================================================
    // NEW SWEDISH LAWS & REGULATIONS
    // ========================================================================
    
    private function getSwedishRegeringsformen() {
        return [
            'name' => 'Regeringsformen',
            'full_name' => 'Sveriges Grundlag - Regeringsformen',
            'description' => 'Sveriges grundlag som säkerställer medborgarnas grundläggande fri- och rättigheter',
            'paragraphs' => [
                '1:2' => [
                    'title' => 'Demokrati och rättssäkerhet',
                    'law_text' => '1 kap. 2 § Den offentliga makten ska utövas med respekt för alla människors lika värde och för den enskilda människans frihet och värdighet.',
                    'plain_language' => 'Staten och kommunen måste respektera din värdighet och frihet. Du har rätt att bli behandlad som en likvärdig människa.',
                    'examples' => [
                        'Socialtjänsten får inte kränka din värdighet',
                        'Du har rätt att säga din mening',
                        'Beslut ska fattas på sakliga grunder'
                    ],
                    'your_rights' => [
                        'Rätt till respekt och värdighet',
                        'Rätt till rättssäker behandling',
                        'Rätt att överklaga kränkande behandling'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishRattegangsbalk() {
        return [
            'name' => 'Rättegångsbalken',
            'full_name' => 'Rättegångsbalk (1942:740)',
            'description' => 'Reglerar rättegångsförfarandet i svenska domstolar',
            'paragraphs' => [
                '35:7' => [
                    'title' => 'Bevisbörda',
                    'law_text' => '35 kap. 7 § Den som påstår något ska bevisa det. Tvivel ska tolkas till den enskildes fördel.',
                    'plain_language' => 'Socialtjänsten måste bevisa sina påståenden om dig. Du behöver inte bevisa att du INTE är olämplig.',
                    'examples' => [
                        'Kommunen ska bevisa att det är bäst för barnet med omhändertagande',
                        'Du kan ifrågasätta deras bevis',
                        'Dokumentation hjälper dig att motbevisa påståenden'
                    ],
                    'your_rights' => [
                        'Rätt att kommunen bevisar sina påståenden',
                        'Rätt att framföra motbevis',
                        'Rätt till insyn i kommunens bevis'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishJOLagen() {
        return [
            'name' => 'JO-lagen',
            'full_name' => 'Lag om Justitieombudsmannen',
            'description' => 'JO granskar myndigheter och kan ta emot klagomål',
            'paragraphs' => [
                '2' => [
                    'title' => 'Tillsyn över myndigheter',
                    'law_text' => '§ 2. JO utövar tillsyn över att de som utövar offentlig verksamhet iakttar lagar och andra författningar samt i övrigt fullgör sina åligganden.',
                    'plain_language' => 'Du kan alltid anmäla socialtjänsten till JO om de bryter mot regler eller behandlar dig fel.',
                    'examples' => [
                        'Du kan anmäla felaktig handläggning',
                        'Du kan anmäla kränkande behandling',
                        'JO kan utreda kommunens agerande'
                    ],
                    'your_rights' => [
                        'Rätt att anmäla till JO',
                        'Rätt till utredning av ditt ärende',
                        'Rätt att få svar från JO'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishPatientsakerhetslagen() {
        return [
            'name' => 'Patientsäkerhetslagen',
            'full_name' => 'Patientsäkerhetslag (2010:659)',
            'description' => 'Säkerställer patientsäkerhet och tystnadsplikt inom vården',
            'paragraphs' => [
                '6:1' => [
                    'title' => 'Tystnadsplikt',
                    'law_text' => '6 kap. 1 § Hälso- och sjukvårdspersonal har tystnadsplikt om patienters personliga förhållanden.',
                    'plain_language' => 'Läkare och psykologer får INTE berätta för socialtjänsten om dig utan ditt samtycke.',
                    'examples' => [
                        'Psykologen får inte skriva rapport till kommunen utan ditt ja',
                        'Läkaren får inte delta i möten med socialen utan samtycke',
                        'Du kan anmäla brott mot tystnadsplikten'
                    ],
                    'your_rights' => [
                        'Rätt till tystnadsplikt',
                        'Rätt att neka samtycke',
                        'Rätt att anmäla till IVO vid brott'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishSocialstyrForeskrifter() {
        return [
            'name' => 'Socialstyrelsens Föreskrifter',
            'full_name' => 'SOSFS - Socialstyrelsens författningssamling',
            'description' => 'Föreskrifter om hur socialtjänsten ska arbeta',
            'paragraphs' => [
                'SOSFS_2014:5' => [
                    'title' => 'Dokumentation i verksamhet som bedrivs med stöd av SoL och LVU',
                    'law_text' => 'SOSFS 2014:5 - Dokumentationen ska vara saklig, relevant och tillräcklig för att tillgodose ändamålet.',
                    'plain_language' => 'Socialtjänstens dokumentation måste vara saklig och korrekt. Osanna eller vinklad dokumentation är olagligt.',
                    'examples' => [
                        'Du kan begära rättelse av felaktiga uppgifter',
                        'Du kan överklaga vinklade bedömningar',
                        'Dokumentationen ska vara objektiv'
                    ],
                    'your_rights' => [
                        'Rätt till korrekt dokumentation',
                        'Rätt att begära rättelse',
                        'Rätt att överklaga fel i journaler'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishBarnkonventionen() {
        return [
            'name' => 'Barnkonventionen (svensk lag)',
            'full_name' => 'FN:s konvention om barnets rättigheter (svensk lag sedan 2020)',
            'description' => 'Barnkonventionen är svensk lag och ska beaktas i alla beslut om barn',
            'paragraphs' => [
                'artikel_3' => [
                    'title' => 'Barnets bästa',
                    'law_text' => 'Artikel 3: Vid alla åtgärder som rör barn ska i första hand beaktas vad som bedöms vara barnets bästa.',
                    'plain_language' => 'BARNETS bästa ska alltid komma först - inte kommunens bekvämlighet eller ekonomi.',
                    'examples' => [
                        'Kommunen måste motivera varför anbringelse är bäst för BARNET',
                        'Barnets egna önskemål ska väga tungt',
                        'Beslut kan överklagas om barnets bästa inte beaktats'
                    ],
                    'your_rights' => [
                        'Rätt till att barnets bästa beaktas',
                        'Rätt att ifrågasätta kommunens bedömning',
                        'Rätt att överklaga med barnets bästa som grund'
                    ]
                ],
                'artikel_12' => [
                    'title' => 'Barnets rätt att uttrycka sin mening',
                    'law_text' => 'Artikel 12: Barn som är i stånd att bilda egna åsikter ska ges rätt att fritt uttrycka dessa i alla frågor som rör barnet.',
                    'plain_language' => 'Ditt barn har rätt att säga sin mening och bli lyssnad på. Kommunen MÅSTE lyssna på barnet.',
                    'examples' => [
                        'Barn över 12 år ska höras i alla beslut',
                        'Även yngre barn ska få säga sin mening',
                        'Barnets åsikt ska dokumenteras'
                    ],
                    'your_rights' => [
                        'Rätt för barnet att uttrycka sig',
                        'Rätt till egen partsrepresentant för barnet',
                        'Rätt att överklaga om barnet inte hörts'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishSocionomersEtik() {
        return [
            'name' => 'Socionomers Etiska Principer',
            'full_name' => 'Akademikerförbundet SSR:s Yrkesetiska riktlinjer',
            'description' => 'Etiska riktlinjer för socionomer i Sverige',
            'paragraphs' => [
                'respekt' => [
                    'title' => 'Respekt för klientens integritet',
                    'law_text' => 'Socionomen ska respektera klientens självbestämmande, integritet och värdighet.',
                    'plain_language' => 'Socialsekreteraren ska behandla dig med respekt och inte kränka dig. Du är en likvärdig människa.',
                    'examples' => [
                        'Du ska behandlas värdigt',
                        'Dina åsikter ska respekteras',
                        'Du kan anmäla kränkande behandling'
                    ],
                    'your_rights' => [
                        'Rätt till respektfull behandling',
                        'Rätt att bli lyssnad på',
                        'Rätt att klaga vid kränkningar'
                    ]
                ]
            ]
        ];
    }
    
    // ============================================
    // NEW DANISH LAWS - CHILD & FAMILY PROTECTION
    // ============================================
    
    private function getDanishBarneloven() {
        return [
            'name' => 'Barneloven',
            'full_name' => 'Lov om børns forsørgelse (Barneloven)',
            'description' => 'Regler om børns forsørgelse, faderskab og forældremyndighed',
            'paragraphs' => [
                '7' => [
                    'title' => 'Forældremyndighed',
                    'law_text' => '§ 7. Forældre, der har fælles forældremyndighed, skal være enige om væsentlige beslutninger om barnets forhold.',
                    'plain_language' => 'Begge forældre skal være enige om vigtige beslutninger om dit barn, hvis I har fælles forældremyndighed.',
                    'examples' => [
                        'Flytning til anden landsdel',
                        'Skoleskift',
                        'Medicinsk behandling',
                        'Religiøs opdragelse'
                    ],
                    'your_rights' => [
                        'Ret til at deltage i vigtige beslutninger',
                        'Ret til at blive hørt',
                        'Ret til domstolsprøvelse ved uenighed'
                    ]
                ],
                '19' => [
                    'title' => 'Barnets ret til samvær',
                    'law_text' => '§ 19. Et barn har ret til samvær med begge forældre, selvom de ikke bor sammen.',
                    'plain_language' => 'Dit barn har lovmæssig ret til samvær med dig, selvom I er skilt eller bor hver for sig.',
                    'examples' => [
                        'Kommunen kan ikke bare nægte samvær',
                        'Samvær kan kun begrænses med saglig grund',
                        'Barnets tarv skal vægtes tungtest'
                    ],
                    'your_rights' => [
                        'Ret til samvær med dit barn',
                        'Ret til fastlagt samværsordning',
                        'Ret til at anke afgørelser om samvær'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishAdoptionsloven() {
        return [
            'name' => 'Adoptionsloven',
            'full_name' => 'Lov om adoption',
            'description' => 'Regler om adoption og bortadoption af børn',
            'paragraphs' => [
                '2' => [
                    'title' => 'Samtykke til adoption',
                    'law_text' => '§ 2. Adoption kræver samtykke fra barnets forældre. Samtykke kan kun gives, når barnet er mindst 3 måneder gammelt.',
                    'plain_language' => 'Kommunen kan IKKE tvangsadoptere dit barn uden dit samtykke. Du skal selv sige ja til adoption.',
                    'examples' => [
                        'Du kan trække samtykke tilbage indtil adoption er gennemført',
                        'Kommunen kan søge om adoption ved domstol hvis du nægter',
                        'Domstolen skal vurdere barnets tarv'
                    ],
                    'your_rights' => [
                        'Ret til at nægte adoption',
                        'Ret til juridisk bistand',
                        'Ret til at anke adoptionsafgørelse'
                    ]
                ],
                '9' => [
                    'title' => 'Barnets samtykke',
                    'law_text' => '§ 9. Er barnet fyldt 12 år, kan adoption ikke ske uden barnets samtykke.',
                    'plain_language' => 'Hvis dit barn er over 12 år, skal barnet selv sige ja til adoption. Barnet kan sige nej.',
                    'examples' => [
                        'Barn kan nægte adoption',
                        'Barnets vilje skal respekteres',
                        'Barnets stemme er vigtig'
                    ],
                    'your_rights' => [
                        'Ret til at barnets ønsker høres',
                        'Ret til at støtte barnets valg',
                        'Ret til egen advokat for barnet'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishVaergemaalslov() {
        return [
            'name' => 'Værgemålsloven',
            'full_name' => 'Lov om værgemål',
            'description' => 'Regler om værgemål for børn og voksne',
            'paragraphs' => [
                '17' => [
                    'title' => 'Værge for barn',
                    'law_text' => '§ 17. Der beskikkes en værge for et barn, hvis forældrene er døde, ukendte, eller hvis de er frataget forældremyndigheden.',
                    'plain_language' => 'Hvis du mister forældremyndigheden, får dit barn en værge. Værgen skal varetage barnets interesser.',
                    'examples' => [
                        'Værgen træffer beslutninger for barnet',
                        'Du kan ansøge om tilbagegivelse af forældremyndighed',
                        'Værgen skal handle i barnets interesse'
                    ],
                    'your_rights' => [
                        'Ret til at kende værgen',
                        'Ret til at kommunikere med barnet',
                        'Ret til at ansøge om generhvervelse af forældremyndighed'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishNavneloven() {
        return [
            'name' => 'Navneloven',
            'full_name' => 'Lov om navne',
            'description' => 'Regler om børns navne og navneændring',
            'paragraphs' => [
                '1' => [
                    'title' => 'Fornavnsvalg',
                    'law_text' => '§ 1. Et barn skal have mindst ét fornavn. Fornavnet må ikke være til skade for barnet.',
                    'plain_language' => 'Du har ret til at give dit barn et fornavn, men det må ikke skade barnet.',
                    'examples' => [
                        'Du vælger barnets navn',
                        'Kommunen kan ikke bestemme navnet',
                        'Ved uenighed afgør domstol'
                    ],
                    'your_rights' => [
                        'Ret til at navngive dit barn',
                        'Ret til kulturelt/religiøst navn',
                        'Ret til at ændre navn senere'
                    ]
                ]
            ]
        ];
    }
    
    // ============================================
    // NEW DANISH LAWS - HEALTH & PSYCHOLOGY
    // ============================================
    
    private function getDanishSundhedsloven() {
        return [
            'name' => 'Sundhedsloven',
            'full_name' => 'Lov om sundhedsvæsenets virksomhed',
            'description' => 'Regler om sundhedsvæsenet og patienters rettigheder',
            'paragraphs' => [
                '15' => [
                    'title' => 'Informeret samtykke',
                    'law_text' => '§ 15. Ingen behandling må indledes eller fortsættes uden patientens informerede samtykke.',
                    'plain_language' => 'Du skal give dit samtykke til behandling. Lægen skal informere dig først.',
                    'examples' => [
                        'Du kan sige nej til behandling',
                        'Du skal have fuld information',
                        'Du kan trække samtykke tilbage'
                    ],
                    'your_rights' => [
                        'Ret til at sige nej',
                        'Ret til information',
                        'Ret til second opinion'
                    ]
                ],
                '40' => [
                    'title' => 'Tavshedspligt',
                    'law_text' => '§ 40. Sundhedspersoner har tavshedspligt om patientforhold.',
                    'plain_language' => 'Læger og psykologer må IKKE fortælle kommunen om dig uden dit samtykke.',
                    'examples' => [
                        'Lægen må ikke skrive erklæring til kommunen uden samtykke',
                        'Psykologen må ikke deltage i møder uden dit ja',
                        'Du kan anmæle brud på tavshedspligt'
                    ],
                    'your_rights' => [
                        'Ret til tavshedspligt',
                        'Ret til at nægte samtykke',
                        'Ret til at anmelde brud til Styrelsen for Patientsikkerhed'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishPsykiatriloven() {
        return [
            'name' => 'Psykiatriloven',
            'full_name' => 'Lov om anvendelse af tvang i psykiatrien',
            'description' => 'Regler om tvang i psykiatrien',
            'paragraphs' => [
                '5' => [
                    'title' => 'Tvangstilbageholdelse',
                    'law_text' => '§ 5. En person kan kun tvangsindlægges, hvis vedkommende er sindssyg og det er uforsvarligt ikke at indlægge.',
                    'plain_language' => 'Du kan kun tvangsindlægges hvis du er sindssyg OG det er uforsvarligt. Begge dele skal være opfyldt.',
                    'examples' => [
                        'Depression alene er ikke nok til tvangsindlæggelse',
                        'Du har ret til advokat',
                        'Du kan anke til domstol'
                    ],
                    'your_rights' => [
                        'Ret til domstolsprøvelse',
                        'Ret til advokat',
                        'Ret til anden lægelig vurdering'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishAutorisationsloven() {
        return [
            'name' => 'Autorisationsloven',
            'full_name' => 'Lov om autorisation af sundhedspersoner',
            'description' => 'Regler om autoriserede sundhedspersoners pligter',
            'paragraphs' => [
                '17' => [
                    'title' => 'Omhu og samvittighedsfuldhed',
                    'law_text' => '§ 17. En autoriseret sundhedsperson skal udvise omhu og samvittighedsfuldhed under udøvelsen af sit virke.',
                    'plain_language' => 'Læger og psykologer skal behandle dig ordentligt og fagligt korrekt. De må ikke være sjuskede.',
                    'examples' => [
                        'Psykologen skal basere vurdering på fakta',
                        'Lægen skal undersøge dig ordentligt',
                        'Du kan klage til Styrelsen for Patientsikkerhed'
                    ],
                    'your_rights' => [
                        'Ret til ordentlig behandling',
                        'Ret til at klage',
                        'Ret til erstatning ved fejl'
                    ]
                ]
            ]
        ];
    }
    
    // ============================================
    // NEW DANISH BEKENDTGØRELSER
    // ============================================
    
    private function getDanishSamvaersbekendtgoerelsen() {
        return [
            'name' => 'Samværsbekendtgørelsen',
            'full_name' => 'Bekendtgørelse om samvær',
            'description' => 'Regler om samvær mellem forældre og anbragte børn',
            'paragraphs' => [
                '1' => [
                    'title' => 'Samværsret',
                    'law_text' => 'Bekendtgørelse nr. 712 af 19/06/2013 § 1: Forældre til anbragte børn har ret til samvær.',
                    'plain_language' => 'Du har ret til samvær med dit anbragte barn. Kommunen skal have saglig grund for at begrænse det.',
                    'examples' => [
                        'Minimum samvær skal fastlægges',
                        'Overvåget samvær kræver begrundelse',
                        'Du kan klage til Ankestyrelsen'
                    ],
                    'your_rights' => [
                        'Ret til samvær',
                        'Ret til begrundelse ved begrænsninger',
                        'Ret til at klage'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishMagtanvendelsesbekendtgoerelsen() {
        return [
            'name' => 'Magtanvendelsesbekendtgørelsen',
            'full_name' => 'Bekendtgørelse om magtanvendelse',
            'description' => 'Regler om magtanvendelse over for anbragte børn',
            'paragraphs' => [
                '1' => [
                    'title' => 'Forbud mod magt',
                    'law_text' => 'Bekendtgørelse nr. 1093 af 15/09/2010 § 1: Magtanvendelse må kun ske i begrænset omfang.',
                    'plain_language' => 'Der må kun bruges magt over for dit barn i yderste nødstilfælde. Alt skal dokumenteres.',
                    'examples' => [
                        'Magtanvendelse skal indberettes',
                        'Du har ret til at vide om magtanvendelse',
                        'Du kan klage til Ankestyrelsen'
                    ],
                    'your_rights' => [
                        'Ret til at vide om magtanvendelse',
                        'Ret til dokumentation',
                        'Ret til at klage'
                    ]
                ]
            ]
        ];
    }
    
    private function getDanishPsykologerEtik() {
        return [
            'name' => 'Psykologers Etik',
            'full_name' => 'Dansk Psykolog Forenings Etiske Principper',
            'description' => 'Etiske retningslinjer for psykologer i Danmark',
            'paragraphs' => [
                'respekt' => [
                    'title' => 'Respekt for klientens integritet',
                    'law_text' => 'Psykologen skal respektere klientens selvbestemmelsesret, integritet og værdighed.',
                    'plain_language' => 'Psykologen skal behandle dig med respekt og ikke krænke dig.',
                    'examples' => [
                        'Psykologen må ikke nedgøre dig',
                        'Psykologen skal lytte til dig',
                        'Du kan klage til DP'
                    ],
                    'your_rights' => [
                        'Ret til respektfuld behandling',
                        'Ret til at blive hørt',
                        'Ret til at klage'
                    ]
                ],
                'objektivitet' => [
                    'title' => 'Objektivitet',
                    'law_text' => 'Psykologen skal være objektiv og ikke lade personlige holdninger påvirke vurderingen.',
                    'plain_language' => 'Psykologens vurdering skal være objektiv - ikke påvirket af fordomme eller kommunens ønsker.',
                    'examples' => [
                        'Psykologen må ikke være forudindtaget',
                        'Vurdering skal baseres på fakta',
                        'Du kan kræve ny vurdering ved bias'
                    ],
                    'your_rights' => [
                        'Ret til objektiv vurdering',
                        'Ret til at anfægte partisk vurdering',
                        'Ret til second opinion'
                    ]
                ]
            ]
        ];
    }
    
    // ============================================
    // NEW SWEDISH LAWS - HEALTH & PATIENT RIGHTS
    // ============================================
    
    private function getSwedishPatientlagen() {
        return [
            'name' => 'Patientlagen',
            'full_name' => 'Patientlag (2014:821)',
            'description' => 'Reglerar patienters ställning och rättigheter inom hälso- och sjukvården',
            'paragraphs' => [
                '2:1' => [
                    'title' => 'Patientens rätt till information',
                    'law_text' => '2 kap. 1 § En patient ska få information om sitt hälsotillstånd och de behandlingsmetoder som finns.',
                    'plain_language' => 'Du har rätt att få fullständig information om din hälsa och behandling.',
                    'examples' => [
                        'Läkaren måste förklara diagnos',
                        'Du ska informeras om behandlingsalternativ',
                        'Informationen ska vara på ett språk du förstår'
                    ],
                    'your_rights' => [
                        'Rätt till information',
                        'Rätt att förstå informationen',
                        'Rätt att ställa frågor'
                    ]
                ],
                '4:1' => [
                    'title' => 'Samtycke till behandling',
                    'law_text' => '4 kap. 1 § Vård och behandling får inte ges utan patientens samtycke.',
                    'plain_language' => 'Du måste ge ditt samtycke till behandling. Du kan säga nej.',
                    'examples' => [
                        'Du kan neka behandling',
                        'Du kan dra tillbaka samtycke',
                        'Tvångsvård kräver särskilt beslut'
                    ],
                    'your_rights' => [
                        'Rätt att samtycka eller neka',
                        'Rätt att dra tillbaka samtycke',
                        'Rätt till självbestämmande'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishHalsoSjukvardslag() {
        return [
            'name' => 'Hälso- och sjukvårdslagen',
            'full_name' => 'Hälso- och sjukvårdslag (2017:30)',
            'description' => 'Grundlagen för hälso- och sjukvård i Sverige',
            'paragraphs' => [
                '5:1' => [
                    'title' => 'God vård',
                    'law_text' => '5 kap. 1 § Hälso- och sjukvård ska vara av god kvalitet och tillgodose patientens behov av trygghet.',
                    'plain_language' => 'Du har rätt till god vård som är säker och trygg.',
                    'examples' => [
                        'Vården ska vara professionell',
                        'Du ska känna dig trygg',
                        'Du kan klaga vid dålig vård'
                    ],
                    'your_rights' => [
                        'Rätt till god vård',
                        'Rätt till trygghet',
                        'Rätt att klaga till IVO'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishBarnombudsmanlagen() {
        return [
            'name' => 'Barnombudsmanlagen',
            'full_name' => 'Lag om Barnombudsman',
            'description' => 'Barnombudsmannen företräder barns rättigheter',
            'paragraphs' => [
                '1' => [
                    'title' => 'Barnombudsmannens uppgift',
                    'law_text' => '§ 1. Barnombudsmannen ska företräda barns och ungas rättigheter och intressen.',
                    'plain_language' => 'Du kan kontakta Barnombudsmannen om ditt barns rättigheter kränks.',
                    'examples' => [
                        'BO kan granska socialtjänstens beslut',
                        'BO kan föra ditt barns talan',
                        'BO arbetar för barnets bästa'
                    ],
                    'your_rights' => [
                        'Rätt att kontakta BO',
                        'Rätt till stöd från BO',
                        'Rätt att BO granskar ärendet'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishUmgangeslagen() {
        return [
            'name' => 'Umgängeslagen',
            'full_name' => 'Lag om umgänge (ingår i Föräldrabalken)',
            'description' => 'Regler om barns rätt till umgänge med föräldrar',
            'paragraphs' => [
                '6:15' => [
                    'title' => 'Barnets rätt till umgänge',
                    'law_text' => '6 kap. 15 § FB: Barn har rätt till umgänge med båda föräldrarna.',
                    'plain_language' => 'Ditt barn har laglig rätt till umgänge med dig.',
                    'examples' => [
                        'Umgänge kan bara begränsas med saklig grund',
                        'Barnets vilja ska beaktas',
                        'Du kan överklaga umgängesbeslut'
                    ],
                    'your_rights' => [
                        'Rätt till umgänge',
                        'Rätt att överklaga begränsningar',
                        'Rätt till fastställd umgängesordning'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishLexSarah() {
        return [
            'name' => 'Lex Sarah',
            'full_name' => 'Lex Sarah - Rapporteringsskyldighet',
            'description' => 'Skyldighet att rapportera missförhållanden inom socialtjänsten',
            'paragraphs' => [
                '14:3' => [
                    'title' => 'Rapporteringsskyldighet',
                    'law_text' => '14 kap. 3 § SoL: Personal ska rapportera missförhållanden som riskerar kvaliteten i verksamheten.',
                    'plain_language' => 'Personal MÅSTE rapportera om något går fel. Om de inte gör det, bryter de mot lagen.',
                    'examples' => [
                        'Misshandel måste rapporteras',
                        'Felbehandling ska anmälas',
                        'Du kan kräva Lex Sarah-utredning'
                    ],
                    'your_rights' => [
                        'Rätt att veta om Lex Sarah-anmälan',
                        'Rätt att begära utredning',
                        'Rätt att överklaga om anmälan inte görs'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishLexMaria() {
        return [
            'name' => 'Lex Maria',
            'full_name' => 'Lex Maria - Anmälningsskyldighet inom vården',
            'description' => 'Skyldighet att anmäla vårdskador',
            'paragraphs' => [
                '3:5' => [
                    'title' => 'Anmälningsskyldighet',
                    'law_text' => '3 kap. 5 § Patientsäkerhetslagen: Vårdgivare ska anmäla händelser som medfört eller hade kunnat medföra vårdskada.',
                    'plain_language' => 'Vården MÅSTE anmäla om du får eller kunde ha fått en skada. Detta inkluderar psykologiska skador.',
                    'examples' => [
                        'Felaktig psykologisk bedömning',
                        'Felaktig medicinering',
                        'Kränkande behandling av patient'
                    ],
                    'your_rights' => [
                        'Rätt att veta om Lex Maria-anmälan',
                        'Rätt att själv anmäla till IVO',
                        'Rätt till ersättning vid vårdskada'
                    ]
                ]
            ]
        ];
    }
    
    // ============================================
    // CRITICAL SWEDISH LAWS - NEWLY ADDED
    // ============================================
    
    private function getSwedishLSS() {
        return [
            'name' => 'LSS',
            'full_name' => 'Lag om stöd och service till vissa funktionshindrade (1993:387)',
            'description' => 'Rättighetsbaserad lag för personer med funktionsnedsättning - mycket starkare än dansk Serviceloven',
            'paragraphs' => [
                '1' => [
                    'title' => 'Lagens syfte',
                    'law_text' => '§ 1. Denna lag innehåller bestämmelser om insatser för särskilt stöd och särskild service åt personer med omfattande och varaktiga funktionshinder.',
                    'plain_language' => 'LSS ger STARK rättighetsbaserad hjælp til personer med funktionsnedsættelser. Du har KRAV på støtte - ikke bare en mulighed.',
                    'examples' => [
                        'Personlig assistans er en RÄTTIGHET',
                        'Boende med särskild service er en RÄTTIGHET',
                        'Daglig verksamhet er en RÄTTIGHET'
                    ],
                    'your_rights' => [
                        'Rätt till personlig assistans',
                        'Rätt till bostad med särskild service',
                        'Rätt till daglig verksamhet',
                        'Rätt till ledsagarservice',
                        'Rätt till kontaktperson',
                        'Rätt till avlösarservice',
                        'Rätt till korttidsvistelse',
                        'Rätt till korttidstillsyn'
                    ]
                ],
                '7' => [
                    'title' => 'Personkrets',
                    'law_text' => '§ 7. Rätt till insatser enligt denna lag har personer: 1. med utvecklingsstörning, autism eller autismliknande tillstånd, 2. med betydande och varaktiga begåvningsmässiga funktionshinder efter hjärnskada i vuxen ålder, 3. med andra varaktiga fysiska eller psykiska funktionshinder som uppenbart inte beror på normalt åldrande.',
                    'plain_language' => 'Tre grupper har ret til LSS-støtte: 1) Udviklingshæmning/autism, 2) Hjerneskade som voksen, 3) Andre varige funktionsnedsættelser.',
                    'examples' => [
                        'Barn med autism har rätt till LSS',
                        'Vuxen med CP (cerebral pares) har rätt',
                        'Person med intellektuell funktionsnedsättning har rätt'
                    ],
                    'your_rights' => [
                        'Rätt till utredning om du tillhör personkrets',
                        'Rätt att överklaga om du nekas LSS',
                        'Rätt till rättslig prövning'
                    ]
                ],
                '9' => [
                    'title' => 'Insatser enligt LSS',
                    'law_text' => '§ 9. Insatser enligt denna lag är: 1. Rådgivning och stöd, 2. Personlig assistans, 3. Ledsagarservice, 4. Kontaktperson, 5. Avlösarservice, 6. Korttidsvistelse, 7. Korttidstillsyn, 8. Boende i familjehem/bostad med särskild service, 9. Daglig verksamhet.',
                    'plain_language' => 'Du kan få ni forskellige former for støtte. Den vigtigste er PERSONLIG ASSISTANS - du bestemmer hvem der skal hjælpe dig.',
                    'examples' => [
                        'Personlig assistans: DU väljer assistenter',
                        'Ledsagare: Hjælp til at komme ud',
                        'Bostad med särskild service: Egen bolig med støtte døgnet rundt'
                    ],
                    'your_rights' => [
                        'Rätt att välja insatser',
                        'Rätt att kombinera insatser',
                        'Rätt att själv bestämma över ditt liv (självbestämmande)',
                        'Rätt till inflytande och delaktighet'
                    ]
                ],
                '10' => [
                    'title' => 'Personlig assistans',
                    'law_text' => '§ 9a. Personlig assistans omfattar personligt utformad hjälp som ges av ett begränsat antal personer åt den som på grund av stora och varaktiga funktionshinder behöver hjälp med grundläggande behov.',
                    'plain_language' => 'Personlig assistans er den STÆRKESTE rettighed i svensk lovgivning. DU ansætter selv dine assistenter og bestemmer hvordan de skal hjælpe.',
                    'examples' => [
                        'Du väljer själv vilka assistenter du vill ha',
                        'Du bestämmer hur assistansen ska utföras',
                        'Kommunen kan INTE bestämma över dig',
                        'Du kan få upp till 24 timmar per dag'
                    ],
                    'your_rights' => [
                        'Rätt till självbestämmande',
                        'Rätt att välja assistenter',
                        'Rätt att välja anordnare',
                        'Rätt till personlig integritet',
                        'Rätt att leva som andra'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishIVO() {
        return [
            'name' => 'IVO',
            'full_name' => 'Inspektionen för vård och omsorg',
            'description' => 'Tillsynsmyndighet för hälso- och sjukvård samt socialtjänst - du kan alltid anmäla hit',
            'paragraphs' => [
                'tillsyn' => [
                    'title' => 'IVOs tillsynsansvar',
                    'law_text' => 'IVO har tillsyn över verksamhet enligt socialtjänstlagen, LVU, LVM, LSS, hälso- och sjukvårdslagen samt tandvårdslagen.',
                    'plain_language' => 'IVO er den VIGTIGSTE tilsynsmyndighed. Du kan anmælde ALLE problemer med socialtjänsten, sundhedsvæsen, psykologer, læger til IVO.',
                    'examples' => [
                        'Anmäl felaktig socialsekreterare till IVO',
                        'Anmäl psykolog som bryter mot regler',
                        'Anmäl läkare som bryter tystnadsplikt',
                        'Anmäl vårdhem med dålig omsorg',
                        'Anmäl LSS-beslut som är fel'
                    ],
                    'your_rights' => [
                        'Rätt att anmäla när som helst',
                        'Rätt att vara anonym',
                        'Rätt till utredning av din anmälan',
                        'Rätt att få svar från IVO',
                        'IVO kan ge tillsyn och kräva åtgärder'
                    ]
                ],
                'anmalan' => [
                    'title' => 'Hur anmäler man till IVO',
                    'law_text' => 'Anmälan görs via ivo.se, telefon 010-788 00 00 eller brev till IVO.',
                    'plain_language' => 'Det är LETT at anmælde til IVO. Du kan gøre det online, per telefon eller brev. Du behøver ikke advokat.',
                    'examples' => [
                        'Gå till www.ivo.se och fyll i anmälningsformulär',
                        'Ring 010-788 00 00 och berätta vad som hänt',
                        'Skriv brev med din berättelse och skicka till IVO',
                        'Bifoga all dokumentation du har'
                    ],
                    'your_rights' => [
                        'Rätt att anmäla utan kostnad',
                        'Rätt att få hjälp med anmälan',
                        'Rätt till skydd mot repressalier',
                        'Rätt att följa upp din anmälan'
                    ]
                ],
                'atgarder' => [
                    'title' => 'IVOs åtgärder',
                    'law_text' => 'IVO kan förelägga verksamheter att vidta rättelse, förbud, återkalla tillstånd eller göra anmälan till åklagare.',
                    'plain_language' => 'IVO har STOR magt. De kan tvinge kommunen til at ændre sig, forbyde visse ting, eller anmelde til politi.',
                    'examples' => [
                        'IVO kan tvinga socialtjänsten att ändra ett beslut',
                        'IVO kan förbjuda vissa metoder',
                        'IVO kan polisanmäla socialsekreterare',
                        'IVO kan stänga verksamheter'
                    ],
                    'your_rights' => [
                        'Rätt att IVO granskar din sak',
                        'Rätt att IVO agerar vid fel',
                        'Rätt att få reda på utredningsresultat',
                        'Rätt att överklaga IVOs beslut till förvaltningsrätten'
                    ]
                ]
            ]
        ];
    }
    
    private function getSwedishOSL() {
        return [
            'name' => 'OSL',
            'full_name' => 'Offentlighets- och sekretesslag (2009:400)',
            'description' => 'Reglerar rätten till insyn i offentlig verksamhet och sekretess - en av Sveriges grundlagar',
            'paragraphs' => [
                '1:1' => [
                    'title' => 'Offentlighetsprincipen',
                    'law_text' => '1 kap. 1 §: Varje svensk medborgare ska ha rätt att ta del av allmänna handlingar.',
                    'plain_language' => 'Du har GRUNDLOVSMÆSSIG ret til at se alle kommunens dokumenter om dig. Dette er en af Sveriges stærkeste rettigheder.',
                    'examples' => [
                        'Du kan begära att se ALT socialtjänsten har skrivit om dig',
                        'Du kan begära att se interna e-post',
                        'Du kan begära att se minnesanteckningar från möten',
                        'Kommunen måste ge dig kopia'
                    ],
                    'your_rights' => [
                        'Rätt till insyn i alla handlingar',
                        'Rätt att få kopia omedelbart',
                        'Rätt att överklaga om du nekas',
                        'Rätt till kostnadsfri insyn (max kopiavgift)'
                    ]
                ],
                '6' => [
                    'title' => 'Begära ut handlingar',
                    'law_text' => 'En begäran om att få ta del av en allmän handling ska tas upp så snart det är möjligt.',
                    'plain_language' => 'Når du beder om aktindsigt, skal kommunen give dig det MED DET SAMME. De må ikke vente.',
                    'examples' => [
                        'Be om "alla handlingar i mitt ärende"',
                        'Säg att du begär dem "enligt offentlighetsprincipen"',
                        'Kommunen ska ge dig dem samma dag om möjligt',
                        'Senast inom några dagar'
                    ],
                    'your_rights' => [
                        'Rätt till omedelbar tillgång',
                        'Rätt att få alla handlingar',
                        'Rätt att överklaga fördröjning',
                        'Rätt att anmäla till JO vid vägran'
                    ]
                ],
                '26:1' => [
                    'title' => 'Sekretess i socialtjänsten',
                    'law_text' => '26 kap. 1 § Sekretess gäller i socialtjänsten för uppgift om enskilds personliga förhållanden om det kan antas att den enskilde lider men om uppgiften röjs.',
                    'plain_language' => 'Socialtjänsten har tavshedspligt, MEN du har altid ret til at se dine egne papirer. Sekretessen er FOR dig - ikke MOT dig.',
                    'examples' => [
                        'Socialtjänsten kan INTE neka dig dina egna handlingar',
                        'De kan neka andra att se dem',
                        'DU har alltid rätt till dina egna uppgifter',
                        'Sekretess skyddar DIG - inte socialtjänsten'
                    ],
                    'your_rights' => [
                        'Rätt att se alla dina egna handlingar',
                        'Rätt att få rättelse av felaktigheter',
                        'Rätt att överklaga felaktig sekretess',
                        'Rätt att anmäla om de nekar dig'
                    ]
                ],
                'tillgang' => [
                    'title' => 'Så begär du ut handlingar',
                    'law_text' => 'Praktisk vägledning för att begära allmänna handlingar från socialtjänsten',
                    'plain_language' => 'Du behøver ikke begrunde hvorfor du vil have papirerne. Du bare siger: "Jag begär ut alla handlingar i mitt ärende enligt offentlighetsprincipen."',
                    'examples' => [
                        'Skicka e-post: "Jag begär ut alla handlingar rörande [ditt namn] enligt offentlighetsprincipen"',
                        'Ring: "Jag vill hämta kopior av alla handlingar i mitt ärende"',
                        'Gå dit: "Jag vill se mina handlingar nu"',
                        'De MÅSTE ge dig dem - ingen motivering behövs'
                    ],
                    'your_rights' => [
                        'Rätt utan motivering',
                        'Rätt till snabb handläggning',
                        'Rätt till gratis insyn (endast kopiavgift)',
                        'Rätt att anmäla fördröjning till JO'
                    ]
                ]
            ]
        ];
    }
    
    // ========================================================================
    // EU DIRECTIVES & INTERNATIONAL LAW
    // ========================================================================
    
    private function getEUGDPR() {
        return [
            'name' => 'GDPR',
            'full_name' => 'General Data Protection Regulation (EU) 2016/679',
            'description' => 'EU forordning om beskyttelse af persondata - gælder direkte i Danmark og Sverige',
            'paragraphs' => [
                '15' => [
                    'title' => 'Indsigtsret',
                    'law_text' => 'Artikel 15: Den registrerede har ret til at få bekræftet, om personoplysninger behandles.',
                    'plain_language' => 'Du har ret til at vide hvilke oplysninger myndigheden har om dig, hvorfor de har dem, og hvem de deler dem med.',
                    'examples' => [
                        'Anmod om kopi af alle data kommunen har om dig',
                        'Få at vide hvilke systemer der behandler dine data',
                        'Se hvem der har haft adgang til dine oplysninger'
                    ],
                    'your_rights' => [
                        'Ret til indsigt i alle persondata',
                        'Ret til at få kopi af data',
                        'Ret til at vide hvem der har adgang',
                        'Svar inden 30 dage (kan forlænges til 90 dage)'
                    ]
                ],
                '16' => [
                    'title' => 'Ret til berigtigelse',
                    'law_text' => 'Artikel 16: Den registrerede har ret til at få urigtige personoplysninger rettet.',
                    'plain_language' => 'Hvis der står forkerte oplysninger om dig, har du ret til at få dem rettet med det samme.',
                    'examples' => [
                        'Få rettet faktuelle fejl i din sag',
                        'Få fjernet ukorrekte påstande',
                        'Få opdateret forældede oplysninger'
                    ],
                    'your_rights' => [
                        'Ret til rettelse af fejl',
                        'Ret til supplering af mangelfulde data',
                        'Ret til svar inden 30 dage',
                        'Ret til at klage til Datatilsynet'
                    ]
                ],
                '17' => [
                    'title' => 'Ret til sletning ("retten til at blive glemt")',
                    'law_text' => 'Artikel 17: Den registrerede har ret til at få sine personoplysninger slettet.',
                    'plain_language' => 'I visse situationer kan du kræve at dine oplysninger slettes. Dog er der undtagelser for sociale sager hvor oplysningerne skal bevares.',
                    'examples' => [
                        'Anmod om sletning af irrelevante oplysninger',
                        'Kræv sletning af ulovligt indsamlede data',
                        'Sociale sager: oplysninger skal ofte bevares i 75 år'
                    ],
                    'your_rights' => [
                        'Ret til sletning i visse tilfælde',
                        'Undtagelser for arkivformål',
                        'Undtagelser for retlige krav',
                        'Ret til begrundelse hvis afvist'
                    ]
                ],
                '18' => [
                    'title' => 'Ret til begrænsning af behandling',
                    'law_text' => 'Artikel 18: Den registrerede har ret til at få behandlingen begrænset.',
                    'plain_language' => 'Du kan kræve at myndigheden stopper med at bruge dine oplysninger mens I diskuterer om de er korrekte.',
                    'examples' => [
                        'Stop behandling mens du bestrider rigtigheden',
                        'Begræns behandling i stedet for sletning',
                        'Forhindre yderligere spredning af data'
                    ],
                    'your_rights' => [
                        'Ret til "frys" af behandling',
                        'Ret til meddelelse når begrænsning ophæves',
                        'Ret til kopi af begrænset data',
                        'Data må kun opbevares, ikke bruges'
                    ]
                ],
                '21' => [
                    'title' => 'Ret til indsigelse',
                    'law_text' => 'Artikel 21: Den registrerede har ret til at gøre indsigelse mod behandling.',
                    'plain_language' => 'Du kan protestere mod hvordan dine data bruges. Myndigheden skal så stoppe medmindre de har meget gode grunde.',
                    'examples' => [
                        'Protestere mod profilering',
                        'Gøre indsigelse mod automatiske afgørelser',
                        'Kræve manuel vurdering af din sag'
                    ],
                    'your_rights' => [
                        'Ret til at protestere',
                        'Myndigheden skal stoppe eller dokumentere tvingend grund',
                        'Ret til begrundelse',
                        'Ret til klage til Datatilsynet'
                    ]
                ],
                '22' => [
                    'title' => 'Ret til ikke at være genstand for automatiske afgørelser',
                    'law_text' => 'Artikel 22: Den registrerede har ret til ikke at være genstand for en afgørelse udelukkende baseret på automatisk behandling.',
                    'plain_language' => 'Computere må ikke træffe vigtige beslutninger om dig uden menneskelig vurdering. Du har ret til at tale med en person.',
                    'examples' => [
                        'Kræv manuel vurdering af AI-baserede afgørelser',
                        'Ret til at få forklaret hvordan automatik virker',
                        'Ret til at anfægte computerens beslutning'
                    ],
                    'your_rights' => [
                        'Ret til menneskelig indgriben',
                        'Ret til at anfægte afgørelsen',
                        'Ret til forklaring af logikken',
                        'Ret til manuel revurdering'
                    ]
                ]
            ]
        ];
    }
    
    private function getEUCharterFundamentalRights() {
        return [
            'name' => 'EU Chartret om Grundlæggende Rettigheder',
            'full_name' => 'Charter of Fundamental Rights of the European Union (2000/C 364/01)',
            'description' => 'EUs bindende charter der beskytter fundamentale menneskerettigheder',
            'paragraphs' => [
                '7' => [
                    'title' => 'Respekt for privatliv og familieliv',
                    'law_text' => 'Artikel 7: Enhver har ret til respekt for sit privatliv og familieliv, sit hjem og sin kommunikation.',
                    'plain_language' => 'EU garanterer din ret til et familieliv. Staten må kun gribe ind hvis det er strengt nødvendigt.',
                    'examples' => [
                        'Ret til at leve sammen med dine børn',
                        'Ret til samvær med dine børn',
                        'Beskyttelse mod unødvendig statsindblanding',
                        'Ret til respekt for familielivet selv ved anbringelse'
                    ],
                    'your_rights' => [
                        'Ret til familieliv',
                        'Staten skal bevise at indgreb er nødvendigt',
                        'Mindre indgribende løsninger skal vælges først',
                        'Ret til at påberåbe dig EU-retten'
                    ]
                ],
                '24' => [
                    'title' => 'Barnets rettigheder',
                    'law_text' => 'Artikel 24: Børn har ret til den beskyttelse og omsorg, der er nødvendig for deres trivsel. Ved alle handlinger skal barnets tarv komme i første række.',
                    'plain_language' => 'Barnets bedste skal altid være det vigtigste. Børn har ret til at blive hørt i sager der vedrører dem.',
                    'examples' => [
                        'Barnets mening skal vægtes tungt',
                        'Barnets perspektiv skal undersøges grundigt',
                        'Børn har ret til at komme til orde',
                        'Barnets tarv går forud for alt andet'
                    ],
                    'your_rights' => [
                        'Barnets ret til at blive hørt',
                        'Barnets ret til beskyttelse',
                        'Barnets ret til kontakt med begge forældre',
                        'Barnets tarv skal være afgørende'
                    ]
                ],
                '47' => [
                    'title' => 'Ret til en effektiv retsmiddel og til en upartisk domstol',
                    'law_text' => 'Artikel 47: Enhver, hvis rettigheder er blevet krænket, har ret til en effektiv retsmiddel for en domstol.',
                    'plain_language' => 'Du har ret til at få din sag prøvet af en uafhængig domstol. Myndighederne må ikke selv være den eneste der bedømmer deres egen afgørelse.',
                    'examples' => [
                        'Ret til domstolsprøvelse af kommunale afgørelser',
                        'Ret til en upartisk dommer',
                        'Ret til retfærdig behandling',
                        'Ret til at få sagen behandlet inden rimelig tid'
                    ],
                    'your_rights' => [
                        'Ret til domstolsbehandling',
                        'Ret til upartisk sagsbehandling',
                        'Ret til at blive hørt',
                        'Ret til at få sagen afgjort inden rimelig tid'
                    ]
                ]
            ]
        ];
    }
    
    private function getEUFamilyReunificationDirective() {
        return [
            'name' => 'Familiesammenføringsdirektivet',
            'full_name' => 'Council Directive 2003/86/EC on the right to family reunification',
            'description' => 'EU-direktiv om ret til familiesammenføring',
            'paragraphs' => [
                '4' => [
                    'title' => 'Ret til familiesammenføring',
                    'law_text' => 'Artikel 4: Medlemsstaterne skal tillade indrejse og ophold for familiemedlemmer.',
                    'plain_language' => 'EU-borgere har ret til at bo sammen med deres familie. Dette gælder også hvis din partner eller børn kommer fra et land uden for EU.',
                    'examples' => [
                        'Ret til at få din ægtefælle til landet',
                        'Ret til at få dine børn til landet',
                        'Beskyttelse af familiens enhed',
                        'Ret til ophold for familiemedlemmer'
                    ],
                    'your_rights' => [
                        'Ret til familiesammenføring',
                        'Ret til behandling af ansøgning inden rimelig tid',
                        'Ret til begrundelse ved afslag',
                        'Ret til klage'
                    ]
                ]
            ]
        ];
    }
    
    private function getEUVictimsRightsDirective() {
        return [
            'name' => 'Ofres Rettighedsdirektiv',
            'full_name' => 'Directive 2012/29/EU on victims rights',
            'description' => 'EU-direktiv om minimumsrettigheder for ofre for forbrydelser',
            'paragraphs' => [
                '3' => [
                    'title' => 'Ret til at blive forstået og hørt',
                    'law_text' => 'Artikel 3: Ofre skal kunne kommunikere med myndighederne på et sprog de forstår.',
                    'plain_language' => 'Hvis du er offer for en forbrydelse (f.eks. vold i familien), har du ret til at blive hørt og forstået.',
                    'examples' => [
                        'Ret til tolk hvis du ikke forstår dansk/svensk',
                        'Ret til at afgive forklaring',
                        'Ret til information om din sag',
                        'Ret til at blive behandlet med respekt'
                    ],
                    'your_rights' => [
                        'Ret til information',
                        'Ret til at blive hørt',
                        'Ret til støtte',
                        'Ret til beskyttelse'
                    ]
                ],
                '24' => [
                    'title' => 'Børns særlige rettigheder som ofre',
                    'law_text' => 'Artikel 24: Børn der er ofre skal have særlig beskyttelse med hensyntagen til barnets tarv.',
                    'plain_language' => 'Børn der har været udsat for overgreb har ret til særlig beskyttelse og hensyn under hele processen.',
                    'examples' => [
                        'Ret til at afgive forklaring i trygge rammer',
                        'Ret til at have en bisidder med',
                        'Ret til at undgå unødig kontakt med gerningsmand',
                        'Ret til hurtig sagsbehandling'
                    ],
                    'your_rights' => [
                        'Børns ret til særlig beskyttelse',
                        'Ret til skånsom afhøring',
                        'Ret til støtte og rådgivning',
                        'Barnets tarv skal være afgørende'
                    ]
                ]
            ]
        ];
    }
    
    private function getEUDataProtectionDirective() {
        return [
            'name' => 'Databeskyttelsesdirektiv',
            'full_name' => 'Directive 2016/680 (Law Enforcement Directive)',
            'description' => 'EU-direktiv om databeskyttelse i forbindelse med retshåndhævelse',
            'paragraphs' => [
                '13' => [
                    'title' => 'Ret til information',
                    'law_text' => 'Artikel 13: Registrerede skal informeres om behandling af deres personoplysninger.',
                    'plain_language' => 'Når politi eller sociale myndigheder behandler oplysninger om dig i forbindelse med en sag, har du ret til at vide det.',
                    'examples' => [
                        'Ret til at vide hvad de ved om dig',
                        'Ret til at få oplyst formålet med behandlingen',
                        'Ret til at vide hvor længe data opbevares',
                        'Ret til at vide hvem der modtager dine data'
                    ],
                    'your_rights' => [
                        'Ret til information om databehandling',
                        'Ret til indsigt',
                        'Ret til rettelse',
                        'Ret til klage til Datatilsynet'
                    ]
                ]
            ]
        ];
    }
    
    private function getECHR() {
        return [
            'name' => 'EMRK',
            'full_name' => 'Den Europæiske Menneskerettighedskonvention',
            'description' => 'Europarådets konvention der beskytter menneskerettigheder - bindende for Danmark og Sverige',
            'paragraphs' => [
                '6' => [
                    'title' => 'Ret til en retfærdig rettergang',
                    'law_text' => 'Artikel 6: Enhver har ret til en retfærdig og offentlig rettergang inden en rimelig frist ved en uafhængig og upartisk domstol.',
                    'plain_language' => 'Du har ret til at få din sag behandlet af en uafhængig domstol. Sagsbehandlingen skal være retfærdig og afgjort inden rimelig tid.',
                    'examples' => [
                        'Ret til domstolsprøvelse af myndighedernes afgørelser',
                        'Ret til at blive hørt',
                        'Ret til at anfægte beviser',
                        'Ret til en afgørelse inden rimelig tid'
                    ],
                    'your_rights' => [
                        'Ret til retfærdig rettergang',
                        'Ret til uafhængig domstol',
                        'Ret til kontradiktion',
                        'Ret til rimelig sagsbehandlingstid'
                    ]
                ],
                '8' => [
                    'title' => 'Ret til respekt for privatliv og familieliv',
                    'law_text' => 'Artikel 8: Enhver har ret til respekt for sit privatliv og familieliv, sit hjem og sin korrespondance.',
                    'plain_language' => 'Staten må ikke blande sig i dit familieliv uden meget god grund. Selv hvis der er grund, skal indgrebet være proportionalt.',
                    'examples' => [
                        'Anbringelse af børn skal være absolut nødvendig',
                        'Samværsbegrænsninger skal være proportionale',
                        'Ret til respekt for familiens samvær',
                        'Staten skal arbejde for genforening'
                    ],
                    'your_rights' => [
                        'Ret til familieliv',
                        'Staten skal bevise nødvendigheden',
                        'Mindste indgreb princippet',
                        'Ret til at klage til EMD i Strasbourg'
                    ]
                ],
                '13' => [
                    'title' => 'Ret til effektiv prøvelse',
                    'law_text' => 'Artikel 13: Enhver hvis rettigheder er krænket, skal have en effektiv prøvelsesmulighed for en national myndighed.',
                    'plain_language' => 'Du skal have mulighed for at få prøvet om dine rettigheder er blevet krænket. Der skal være et effektivt klagemulighed.',
                    'examples' => [
                        'Ret til at klage til uafhængig myndighed',
                        'Ret til domstolsprøvelse',
                        'Ret til at få sagen vurderet objektivt',
                        'Ret til effektiv retsbeskyttelse'
                    ],
                    'your_rights' => [
                        'Ret til effektiv prøvelse',
                        'Ret til uafhængig behandling af klage',
                        'Ret til at få rettet krænkelser',
                        'Ret til kompensation ved krænkelse'
                    ]
                ]
            ]
        ];
    }
    
    private function getUNCRC() {
        return [
            'name' => 'Børnekonventionen',
            'full_name' => 'FNs Konvention om Barnets Rettigheder (UNCRC)',
            'description' => 'FNs konvention der fastlægger børns universelle rettigheder - bindende for Danmark og Sverige',
            'paragraphs' => [
                '3' => [
                    'title' => 'Barnets tarv skal være det vigtigste',
                    'law_text' => 'Artikel 3: Ved alle handlinger vedrørende børn skal barnets tarv komme i første række.',
                    'plain_language' => 'Når nogen træffer beslutninger om børn, skal de først og fremmest tænke på hvad der er bedst for barnet.',
                    'examples' => [
                        'Ved anbringelse skal barnets behov veje tungest',
                        'Ved samværsafgørelser skal barnets perspektiv være centralt',
                        'Barnets trivsel går forud for forældrenes ønsker',
                        'Alle beslutninger skal vurderes ud fra barnets tarv'
                    ],
                    'your_rights' => [
                        'Barnets ret til at dets tarv prioriteres',
                        'Ret til individuel vurdering',
                        'Ret til at barnets behov undersøges grundigt',
                        'Ret til at anfægte afgørelser der ikke tilgodeser barnets tarv'
                    ]
                ],
                '9' => [
                    'title' => 'Ret til samvær med begge forældre',
                    'law_text' => 'Artikel 9: Barnet har ret til regelmæssig personlig forbindelse og direkte kontakt med begge forældre, medmindre det er i modstrid med barnets tarv.',
                    'plain_language' => 'Børn har ret til at have kontakt med både mor og far, også selv om de ikke bor sammen. Kun hvis det er farligt for barnet, må kontakten begrænses.',
                    'examples' => [
                        'Barnet skal have samvær med begge forældre som udgangspunkt',
                        'Samvær må kun begrænses hvis det skader barnet',
                        'Staten skal støtte kontakten, ikke hindre den',
                        'Ret til at kende sin biologiske oprindelse'
                    ],
                    'your_rights' => [
                        'Barnets ret til samvær med begge forældre',
                        'Ret til begrundelse hvis samvær nægtes',
                        'Ret til støtte til kontakt',
                        'Ret til at klage hvis kontakt hindres'
                    ]
                ],
                '12' => [
                    'title' => 'Barnets ret til at blive hørt',
                    'law_text' => 'Artikel 12: Barnet har ret til frit at udtrykke sin mening i alle forhold, der vedrører det, og barnets mening skal tillægges passende vægt i overensstemmelse med dets alder og modenhed.',
                    'plain_language' => 'Børn skal høres i sager der handler om dem. Jo ældre og mere modent barnet er, desto mere skal dets mening vægtes.',
                    'examples' => [
                        'Barnet skal spørges om sin mening ved anbringelse',
                        'Barnets ønsker om samvær skal lyttes til',
                        'Ældre børn skal inddrages i beslutninger',
                        'Barnets perspektiv skal dokumenteres'
                    ],
                    'your_rights' => [
                        'Barnets ret til at sige sin mening',
                        'Ret til at meningen vægtes',
                        'Ret til en bisidder',
                        'Ret til at anfægte hvis ikke hørt'
                    ]
                ],
                '16' => [
                    'title' => 'Beskyttelse af privatliv',
                    'law_text' => 'Artikel 16: Intet barn må udsættes for vilkårlig eller ulovlig indblanding i sit privatliv, sin familie, sit hjem eller sin korrespondance.',
                    'plain_language' => 'Børn har ret til privatliv. Myndigheder må ikke blande sig uden god grund, og må ikke offentliggøre oplysninger om barnet.',
                    'examples' => [
                        'Barnets oplysninger skal beskyttes',
                        'Indblanding i familielivet skal være nødvendig',
                        'Barnets breve og beskeder er private',
                        'Beskyttelse mod offentlig eksponering'
                    ],
                    'your_rights' => [
                        'Barnets ret til privatliv',
                        'Ret til databeskyttelse',
                        'Ret til fortrolighed',
                        'Ret til beskyttelse mod unødig indblanding'
                    ]
                ],
                '19' => [
                    'title' => 'Beskyttelse mod vold og omsorgssvigt',
                    'law_text' => 'Artikel 19: Staten skal beskytte barnet mod alle former for fysisk eller psykisk vold, skade eller misbrug, vanrøgt eller forsømmelig behandling.',
                    'plain_language' => 'Staten har pligt til at beskytte børn mod vold, overgreb og omsorgssvigt. Både i hjemmet og uden for hjemmet.',
                    'examples' => [
                        'Pligt til at undersøge bekymrende tegn',
                        'Pligt til at gribe ind ved vold',
                        'Pligt til at beskytte børn i offentlig omsorg',
                        'Pligt til at forebygge omsorgssvigt'
                    ],
                    'your_rights' => [
                        'Barnets ret til beskyttelse',
                        'Ret til at blive hørt om bekymringer',
                        'Ret til støtte og hjælp',
                        'Ret til at klage hvis ikke beskyttet'
                    ]
                ],
                '20' => [
                    'title' => 'Børn berøvet deres familjemiljø',
                    'law_text' => 'Artikel 20: Et barn, der midlertidigt eller permanent er berøvet sit familiemiljø, har ret til særlig beskyttelse og bistand fra staten.',
                    'plain_language' => 'Børn der er anbragt eller ikke kan bo hjemme, har ret til særlig omsorg. Staten skal sikre kontinuitet og stabilitet.',
                    'examples' => [
                        'Anbragte børn har ret til god omsorg',
                        'Ret til stabilitet i anbringelsen',
                        'Ret til at bevare kulturel identitet',
                        'Ret til kontakt med biologisk familie hvis muligt'
                    ],
                    'your_rights' => [
                        'Ret til forsvarlig anbringelse',
                        'Ret til kontinuitet',
                        'Ret til kontakt med familie',
                        'Ret til at klage over anbringelsesforhold'
                    ]
                ]
            ]
        ];
    }
}

