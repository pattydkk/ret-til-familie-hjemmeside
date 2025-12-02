<?php
namespace KateAI\Core;

/**
 * WebSearcher - Searches Danish AND Swedish legal websites for relevant information
 * Danish: domstol.dk, Ankestyrelsen, retsinformation.dk, borger.dk
 * Swedish: riksdagen.se, domstol.se, socialstyrelsen.se, IVO, JO
 */
class WebSearcher {
    private $logger;
    private $cacheDir;
    private $cacheTime = 3600; // 1 hour cache
    
    // Trusted Danish legal sources
    private $danishSources = [
        'retsinformation' => [
            'name' => 'Retsinformation.dk',
            'url' => 'https://www.retsinformation.dk',
            'search_url' => 'https://www.retsinformation.dk/forms/r0000.aspx?q=',
            'relevance' => 'high',
            'type' => 'legislation',
            'country' => 'da_DK'
        ],
        'ankestyrelsen' => [
            'name' => 'Ankestyrelsen',
            'url' => 'https://ast.dk',
            'search_url' => 'https://ast.dk/search?q=',
            'relevance' => 'high',
            'type' => 'case_law',
            'country' => 'da_DK'
        ],
        'domstol' => [
            'name' => 'Domstol.dk',
            'url' => 'https://www.domstol.dk',
            'search_url' => 'https://www.domstol.dk/soeg?q=',
            'relevance' => 'medium',
            'type' => 'court_decisions',
            'country' => 'da_DK'
        ],
        'borger' => [
            'name' => 'Borger.dk',
            'url' => 'https://www.borger.dk',
            'search_url' => 'https://www.borger.dk/search?q=',
            'relevance' => 'medium',
            'type' => 'citizen_info',
            'country' => 'da_DK'
        ]
    ];
    
    // Trusted Swedish legal sources
    private $swedishSources = [
        'riksdagen' => [
            'name' => 'Riksdagen.se',
            'url' => 'https://www.riksdagen.se',
            'search_url' => 'https://www.riksdagen.se/sv/sok/?q=',
            'relevance' => 'high',
            'type' => 'legislation',
            'country' => 'sv_SE'
        ],
        'domstol_se' => [
            'name' => 'Domstol.se',
            'url' => 'https://www.domstol.se',
            'search_url' => 'https://www.domstol.se/sok/?q=',
            'relevance' => 'high',
            'type' => 'court_decisions',
            'country' => 'sv_SE'
        ],
        'socialstyrelsen' => [
            'name' => 'Socialstyrelsen',
            'url' => 'https://www.socialstyrelsen.se',
            'search_url' => 'https://www.socialstyrelsen.se/sok/?q=',
            'relevance' => 'high',
            'type' => 'regulations',
            'country' => 'sv_SE'
        ],
        'ivo_se' => [
            'name' => 'IVO - Inspektionen för vård och omsorg',
            'url' => 'https://www.ivo.se',
            'search_url' => 'https://www.ivo.se/sok/?q=',
            'relevance' => 'high',
            'type' => 'supervision',
            'country' => 'sv_SE'
        ],
        'jo_se' => [
            'name' => 'Justitieombudsmannen',
            'url' => 'https://www.jo.se',
            'search_url' => 'https://www.jo.se/sok/?q=',
            'relevance' => 'high',
            'type' => 'ombudsman',
            'country' => 'sv_SE'
        ],
        'lawline_se' => [
            'name' => 'Lawline.se',
            'url' => 'https://www.lawline.se',
            'search_url' => 'https://www.lawline.se/search?q=',
            'relevance' => 'medium',
            'type' => 'legal_guidance',
            'country' => 'sv_SE'
        ]
    ];
    
    public function __construct(Logger $logger = null, $cacheDir = null) {
        $this->logger = $logger;
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir() . '/kate_websearch';
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Search across all configured sources based on country
     * @param string $query Search query
     * @param string $country Country code (da_DK or sv_SE)
     * @param array $sources Specific sources to search (or all if empty)
     * @param int $maxResults Maximum results per source
     * @return array Search results
     */
    public function search($query, $country = 'da_DK', array $sources = [], $maxResults = 3) {
        if (empty($query)) {
            return $this->errorResponse('Tom søgeforespørgsel');
        }
        
        // Sanitize query
        $query = $this->sanitizeQuery($query);
        
        // Get sources for country
        $allSources = $this->getSourcesForCountry($country);
        
        // Determine which sources to search
        $sourcesToSearch = empty($sources) ? array_keys($allSources) : $sources;
        
        $results = [
            'query' => $query,
            'country' => $country,
            'searched_at' => date('Y-m-d H:i:s'),
            'sources_searched' => count($sourcesToSearch),
            'total_results' => 0,
            'results' => []
        ];
        
        // Search each source
        foreach ($sourcesToSearch as $sourceId) {
            if (!isset($allSources[$sourceId])) {
                continue;
            }
            
            $sourceResults = $this->searchSource($sourceId, $query, $country, $maxResults);
            if (!empty($sourceResults['items'])) {
                $results['results'][$sourceId] = $sourceResults;
                $results['total_results'] += count($sourceResults['items']);
            }
        }
        
        // Log search
        if ($this->logger) {
            $this->logger->log('websearch', $query, [$country, $sourcesToSearch], $results['total_results']);
        }
        
        return $results;
    }
    
    /**
     * Get sources for specific country
     */
    private function getSourcesForCountry($country) {
        if ($country === 'sv_SE') {
            return $this->swedishSources;
        }
        return $this->danishSources;
    }
    
    /**
     * Search a specific source
     * @param string $sourceId Source identifier
     * @param string $query Search query
     * @param string $country Country code
     * @param int $maxResults Maximum results
     * @return array Results from source
     */
    private function searchSource($sourceId, $query, $country, $maxResults = 3) {
        $allSources = $this->getSourcesForCountry($country);
        
        if (!isset($allSources[$sourceId])) {
            return [];
        }
        
        $source = $allSources[$sourceId];
        $cacheKey = $this->getCacheKey($sourceId . '_' . $country, $query);
        
        // Check cache first
        $cached = $this->getFromCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        // Build search URL
        $searchUrl = $source['search_url'] . urlencode($query);
        
        $results = [
            'source' => $source['name'],
            'source_id' => $sourceId,
            'url' => $source['url'],
            'type' => $source['type'],
            'relevance' => $source['relevance'],
            'country' => $source['country'],
            'searched_at' => time(),
            'items' => []
        ];
        
        // Fetch results (simplified - in production would use proper scraping/API)
        try {
            // Get results based on country
            $results['items'] = $this->getSimulatedResults($sourceId, $query, $country, $maxResults);
            
            // Cache results
            $this->saveToCache($cacheKey, $results);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->log('websearch', 'ERROR: ' . $sourceId, [], $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Get simulated search results with real URLs
     * In production, this would scrape/call APIs
     */
    private function getSimulatedResults($sourceId, $query, $country, $maxResults) {
        $results = [];
        
        // Normalize query for matching
        $queryNorm = mb_strtolower($query);
        
        // DANISH SOURCES
        if ($country === 'da_DK') {
            // Retsinformation - Link directly to relevant laws
            if ($sourceId === 'retsinformation') {
                if (strpos($queryNorm, 'barnets lov') !== false || strpos($queryNorm, 'anbringelse') !== false) {
                    $results[] = [
                        'title' => 'Barnets Lov - Konsolideringslag',
                        'snippet' => 'Barnets Lov regulerer anbringelser, undersøgelser og hjælpeforanstaltninger for børn og unge.',
                        'url' => 'https://www.retsinformation.dk/eli/lta/2022/1146',
                        'relevance_score' => 0.95,
                        'date' => '2022'
                    ];
                }
                if (strpos($queryNorm, 'forvaltning') !== false || strpos($queryNorm, 'klage') !== false || strpos($queryNorm, 'aktindsigt') !== false) {
                    $results[] = [
                        'title' => 'Forvaltningsloven',
                        'snippet' => 'Forvaltningsloven regulerer sagsbehandling, begrundelse, klagevejledning og aktindsigt.',
                        'url' => 'https://www.retsinformation.dk/eli/lta/2022/1451',
                        'relevance_score' => 0.92,
                        'date' => '2022'
                    ];
                }
                if (strpos($queryNorm, 'samvær') !== false || strpos($queryNorm, 'forældremyndighed') !== false) {
                    $results[] = [
                        'title' => 'Forældreansvarslov',
                        'snippet' => 'Lov om forældremyndighed, samvær og barnets rettigheder.',
                        'url' => 'https://www.retsinformation.dk/eli/lta/2023/775',
                        'relevance_score' => 0.88,
                        'date' => '2023'
                    ];
                }
            }
            
            // Ankestyrelsen - Link to relevant case law and guidance
            if ($sourceId === 'ankestyrelsen') {
                if (strpos($queryNorm, 'anbringelse') !== false) {
                    $results[] = [
                        'title' => 'Ankestyrelsens praksis om anbringelser',
                        'snippet' => 'Afgørelser og principper for anbringelsessager.',
                        'url' => 'https://ast.dk/born-familie-og-unge/anbringelse',
                        'relevance_score' => 0.90,
                        'date' => '2024'
                    ];
                }
                if (strpos($queryNorm, 'klage') !== false) {
                    $results[] = [
                        'title' => 'Sådan klager du - Ankestyrelsen',
                        'snippet' => 'Vejledning til klager over kommunale afgørelser.',
                        'url' => 'https://ast.dk/born-familie-og-unge/klagevejledning',
                        'relevance_score' => 0.93,
                        'date' => '2024'
                    ];
                }
                if (strpos($queryNorm, 'samvær') !== false) {
                    $results[] = [
                        'title' => 'Samvær med anbragte børn',
                        'snippet' => 'Ankestyrelsens vejledning om samvær og kontakt.',
                        'url' => 'https://ast.dk/born-familie-og-unge/samvaer',
                        'relevance_score' => 0.87,
                        'date' => '2024'
                    ];
                }
            }
            
            // Domstol.dk - Court decisions
            if ($sourceId === 'domstol') {
                $results[] = [
                    'title' => 'Domme og afgørelser - ' . $query,
                    'snippet' => 'Søg i domstolenes afgørelser og kendelser.',
                    'url' => 'https://www.domstol.dk/soeg?q=' . urlencode($query),
                    'relevance_score' => 0.75,
                    'date' => date('Y')
                ];
            }
            
            // Borger.dk - Citizen information
            if ($sourceId === 'borger') {
                if (strpos($queryNorm, 'anbringelse') !== false || strpos($queryNorm, 'børn') !== false) {
                    $results[] = [
                        'title' => 'Anbringelse af børn - Borger.dk',
                        'snippet' => 'Information om anbringelse, dine rettigheder og hvem du kan kontakte.',
                        'url' => 'https://www.borger.dk/familie-og-boern/Anbringelse-af-boern',
                        'relevance_score' => 0.80,
                        'date' => '2024'
                    ];
                }
                if (strpos($queryNorm, 'aktindsigt') !== false) {
                    $results[] = [
                        'title' => 'Aktindsigt - Borger.dk',
                        'snippet' => 'Sådan får du aktindsigt i din sag hos kommunen.',
                        'url' => 'https://www.borger.dk/familie-og-boern/Aktindsigt-i-boernesager',
                        'relevance_score' => 0.85,
                        'date' => '2024'
                    ];
                }
            }
        }
        
        // SWEDISH SOURCES
        if ($country === 'sv_SE') {
            // Riksdagen - Swedish legislation
            if ($sourceId === 'riksdagen') {
                if (strpos($queryNorm, 'lvu') !== false || strpos($queryNorm, 'omhändertagande') !== false) {
                    $results[] = [
                        'title' => 'LVU - Lag med särskilda bestämmelser om vård av unga',
                        'snippet' => 'LVU reglerar tvångsvård av barn och unga under 20 år.',
                        'url' => 'https://www.riksdagen.se/sv/dokument-och-lagar/dokument/svensk-forfattningssamling/lag-1990-52-med-sarskilda-bestammelser-om_sfs-1990-52/',
                        'relevance_score' => 0.95,
                        'date' => '1990'
                    ];
                }
                if (strpos($queryNorm, 'lss') !== false || strpos($queryNorm, 'funktionshinder') !== false) {
                    $results[] = [
                        'title' => 'LSS - Lag om stöd och service',
                        'snippet' => 'Rättighetsbaserad lag för personer med funktionsnedsättning.',
                        'url' => 'https://www.riksdagen.se/sv/dokument-och-lagar/dokument/svensk-forfattningssamling/lag-199387-om-stod-och-service-till-vissa_sfs-1993-387/',
                        'relevance_score' => 0.93,
                        'date' => '1993'
                    ];
                }
                if (strpos($queryNorm, 'socialtjänstlagen') !== false || strpos($queryNorm, 'sol') !== false) {
                    $results[] = [
                        'title' => 'Socialtjänstlagen (SoL)',
                        'snippet' => 'Grundlagen för socialtjänstens verksamhet i Sverige.',
                        'url' => 'https://www.riksdagen.se/sv/dokument-och-lagar/dokument/svensk-forfattningssamling/socialtjanstlag-2001453_sfs-2001-453/',
                        'relevance_score' => 0.94,
                        'date' => '2001'
                    ];
                }
                if (strpos($queryNorm, 'offentlighet') !== false || strpos($queryNorm, 'osl') !== false) {
                    $results[] = [
                        'title' => 'Offentlighets- och sekretesslag',
                        'snippet' => 'Grundlag om rätt till insyn i allmänna handlingar.',
                        'url' => 'https://www.riksdagen.se/sv/dokument-och-lagar/dokument/svensk-forfattningssamling/offentlighets--och-sekretesslag-2009400_sfs-2009-400/',
                        'relevance_score' => 0.92,
                        'date' => '2009'
                    ];
                }
            }
            
            // Socialstyrelsen - Regulations and guidance
            if ($sourceId === 'socialstyrelsen') {
                if (strpos($queryNorm, 'dokumentation') !== false) {
                    $results[] = [
                        'title' => 'SOSFS 2014:5 - Dokumentation i verksamhet',
                        'snippet' => 'Föreskrifter om dokumentation inom socialtjänst och LSS.',
                        'url' => 'https://www.socialstyrelsen.se/regler-och-riktlinjer/foreskrifter-och-allmanna-rad/konsoliderade-foreskrifter/20145-om-dokumentation-i-verksamhet-som-bedrivs-med-stod-av-sol-och-lvu/',
                        'relevance_score' => 0.90,
                        'date' => '2014'
                    ];
                }
                if (strpos($queryNorm, 'lvu') !== false || strpos($queryNorm, 'placering') !== false) {
                    $results[] = [
                        'title' => 'Socialstyrelsens vägledning LVU',
                        'snippet' => 'Vägledning för tillämpning av LVU.',
                        'url' => 'https://www.socialstyrelsen.se/kunskapsstod-och-regler/regler-och-riktlinjer/vagledningar/lvu/',
                        'relevance_score' => 0.88,
                        'date' => '2023'
                    ];
                }
                if (strpos($queryNorm, 'lss') !== false) {
                    $results[] = [
                        'title' => 'Handläggning av LSS-ärenden',
                        'snippet' => 'Vägledning för handläggning enligt LSS.',
                        'url' => 'https://www.socialstyrelsen.se/kunskapsstod-och-regler/regler-och-riktlinjer/vagledningar/lss/',
                        'relevance_score' => 0.91,
                        'date' => '2023'
                    ];
                }
            }
            
            // IVO - Supervision authority
            if ($sourceId === 'ivo_se') {
                $results[] = [
                    'title' => 'Anmäl till IVO - Inspektionen för vård och omsorg',
                    'snippet' => 'Här kan du anmäla missförhållanden inom socialtjänst, vård och omsorg.',
                    'url' => 'https://www.ivo.se/for-privatpersoner/anmal/',
                    'relevance_score' => 0.95,
                    'date' => '2024'
                ];
                
                if (strpos($queryNorm, 'tillsyn') !== false) {
                    $results[] = [
                        'title' => 'IVOs tillsyn av socialtjänsten',
                        'snippet' => 'Så granskar IVO socialtjänsten och vad de kan göra.',
                        'url' => 'https://www.ivo.se/tillsyn/socialtjanst/',
                        'relevance_score' => 0.92,
                        'date' => '2024'
                    ];
                }
            }
            
            // JO - Justitieombudsmannen
            if ($sourceId === 'jo_se') {
                $results[] = [
                    'title' => 'Anmäl till JO - Justitieombudsmannen',
                    'snippet' => 'JO granskar myndigheter och kan ta emot klagomål.',
                    'url' => 'https://www.jo.se/anmal/',
                    'relevance_score' => 0.93,
                    'date' => '2024'
                ];
                
                if (strpos($queryNorm, 'socialtjänst') !== false) {
                    $results[] = [
                        'title' => 'JO:s avgöranden om socialtjänst',
                        'snippet' => 'JO:s beslut och kritik mot socialtjänsten.',
                        'url' => 'https://www.jo.se/beslut/socialtjanst/',
                        'relevance_score' => 0.88,
                        'date' => '2024'
                    ];
                }
            }
            
            // Domstol.se - Court decisions
            if ($sourceId === 'domstol_se') {
                $results[] = [
                    'title' => 'Domar och beslut - ' . $query,
                    'snippet' => 'Sök bland svenska domstolars avgöranden.',
                    'url' => 'https://www.domstol.se/sok/?q=' . urlencode($query),
                    'relevance_score' => 0.80,
                    'date' => date('Y')
                ];
            }
            
            // Lawline - Legal guidance
            if ($sourceId === 'lawline_se') {
                $results[] = [
                    'title' => 'Juridisk vägledning - ' . $query,
                    'snippet' => 'Gratis juridisk rådgivning från Lawline.',
                    'url' => 'https://www.lawline.se/search?q=' . urlencode($query),
                    'relevance_score' => 0.75,
                    'date' => date('Y')
                ];
            }
        }
        
        // Limit results
        return array_slice($results, 0, $maxResults);
    }
    
    /**
     * Search for specific law paragraph
     * @param string $lawName Name of law (e.g., "Barnets Lov")
     * @param string $paragraph Paragraph number (e.g., "§ 76")
     * @return array Law information
     */
    public function searchLaw($lawName, $paragraph = null) {
        $lawUrls = [
            'barnets lov' => 'https://www.retsinformation.dk/eli/lta/2022/1146',
            'forvaltningsloven' => 'https://www.retsinformation.dk/eli/lta/2022/1451',
            'forældreansvarslov' => 'https://www.retsinformation.dk/eli/lta/2023/775',
            'retssikkerhedsloven' => 'https://www.retsinformation.dk/eli/lta/2019/1054',
            'persondataloven' => 'https://www.retsinformation.dk/eli/lta/2018/502'
        ];
        
        $lawKey = mb_strtolower($lawName);
        $url = $lawUrls[$lawKey] ?? null;
        
        if (!$url) {
            return ['error' => 'Lov ikke fundet', 'law_name' => $lawName, 'found' => false];
        }
        
        $result = [
            'law_name' => $lawName,
            'url' => $url,
            'found' => true
        ];
        
        if ($paragraph) {
            $result['paragraph'] = $paragraph;
            $result['direct_url'] = $url . '#' . str_replace(['§', ' '], '', $paragraph);
        }
        
        return $result;
    }
    
    /**
     * Get Ankestyrelsen case law for specific topic
     */
    public function getAnkestyrelseCases($topic) {
        $topics = [
            'anbringelse' => 'https://ast.dk/born-familie-og-unge/anbringelse',
            'samvær' => 'https://ast.dk/born-familie-og-unge/samvaer',
            'aktindsigt' => 'https://ast.dk/born-familie-og-unge/aktindsigt',
            'handleplan' => 'https://ast.dk/born-familie-og-unge/handleplan',
            'undersøgelse' => 'https://ast.dk/born-familie-og-unge/undersoegelse'
        ];
        
        $topicKey = mb_strtolower($topic);
        
        return [
            'topic' => $topic,
            'url' => $topics[$topicKey] ?? 'https://ast.dk/born-familie-og-unge',
            'source' => 'Ankestyrelsen',
            'found' => isset($topics[$topicKey])
        ];
    }
    
    private function sanitizeQuery($query) {
        // Remove potentially harmful characters
        $query = strip_tags($query);
        $query = trim($query);
        return $query;
    }
    
    private function getCacheKey($sourceId, $query) {
        return md5($sourceId . '_' . $query);
    }
    
    private function getFromCache($key) {
        $cacheFile = $this->cacheDir . '/' . $key . '.json';
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        $age = time() - filemtime($cacheFile);
        if ($age > $this->cacheTime) {
            @unlink($cacheFile);
            return null;
        }
        
        $content = @file_get_contents($cacheFile);
        if ($content === false) {
            return null;
        }
        
        return json_decode($content, true);
    }
    
    private function saveToCache($key, $data) {
        $cacheFile = $this->cacheDir . '/' . $key . '.json';
        @file_put_contents($cacheFile, json_encode($data));
    }
    
    private function errorResponse($message) {
        return [
            'error' => true,
            'message' => $message,
            'timestamp' => time()
        ];
    }
}
