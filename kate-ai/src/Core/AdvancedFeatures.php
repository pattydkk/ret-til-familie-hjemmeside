<?php
namespace KateAI\Core;

/**
 * Advanced Kate AI Features - "og lidt til"
 * Document generation, case law search, timeline builder, legal calculator
 */
class AdvancedFeatures {
    private $webSearcher;
    private $knowledgeBase;
    private $databaseManager;
    
    public function __construct(WebSearcher $webSearcher, KnowledgeBase $knowledgeBase, DatabaseManager $databaseManager = null) {
        $this->webSearcher = $webSearcher;
        $this->knowledgeBase = $knowledgeBase;
        $this->databaseManager = $databaseManager ?? new DatabaseManager();
    }
    
    /**
     * Generate complaint letter draft based on case details
     * @param array $caseDetails Case information
     * @return array Generated letter with sections
     */
    public function generateComplaintLetter($caseDetails) {
        $letter = [
            'title' => 'Klage over afgørelse',
            'generated_at' => date('Y-m-d H:i:s'),
            'sections' => []
        ];
        
        // Header section
        $letter['sections'][] = [
            'type' => 'header',
            'content' => [
                'Til: ' . ($caseDetails['municipality'] ?? '[Kommune navn]'),
                'Vedr.: Klage over afgørelse af ' . ($caseDetails['decision_date'] ?? '[dato]'),
                'Sagsnummer: ' . ($caseDetails['case_number'] ?? '[sagsnummer]'),
                'Dato: ' . date('d-m-Y')
            ]
        ];
        
        // Introduction
        $letter['sections'][] = [
            'type' => 'introduction',
            'title' => 'Klage',
            'content' => [
                'Jeg klager hermed over kommunens afgørelse af ' . ($caseDetails['decision_date'] ?? '[dato]') . 
                ' vedrørende ' . ($caseDetails['subject'] ?? '[emne]') . '.',
                '',
                'Jeg ønsker at klage over afgørelsen, da jeg mener den er:',
                '☐ Faktisk forkert',
                '☐ Baseret på mangelfuld sagsoplysning',
                '☐ I strid med loven',
                '☐ I strid med god forvaltningsskik'
            ]
        ];
        
        // Legal grounds
        $letter['sections'][] = [
            'type' => 'legal_grounds',
            'title' => 'Juridisk begrundelse',
            'content' => [
                'Min klage er baseret på følgende juridiske forhold:',
                '',
                '1. Sagsbehandlingen',
                '   - Jeg mener kommunen ikke har foretaget tilstrækkelig sagsoplysning (Forvaltningsloven § 10)',
                '   - Jeg blev ikke partshørt før afgørelsen (Forvaltningsloven § 19)',
                '',
                '2. Materielle forhold',
                '   - Afgørelsen er ikke tilstrækkeligt begrundet (Forvaltningsloven § 22, § 24)',
                '   - Det mindste middel princip er ikke overholdt (Barnets Lov § 46)',
                '',
                '3. Barnets perspektiv',
                '   - Barnets perspektiv er ikke inddraget tilstrækkeligt (Barnets Lov § 46)',
                '   - Mit barn blev ikke hørt ordentligt (Barnets Lov § 47)'
            ]
        ];
        
        // Facts
        $letter['sections'][] = [
            'type' => 'facts',
            'title' => 'Faktiske forhold',
            'content' => [
                'Følgende faktiske forhold er relevante for klagen:',
                '',
                '[Beskriv her de faktiske forhold der er relevante for din klage. Vær konkret og faktuel.]',
                '',
                'Særligt vil jeg fremhæve:',
                '- [Punkt 1]',
                '- [Punkt 2]',
                '- [Punkt 3]'
            ]
        ];
        
        // Request
        $letter['sections'][] = [
            'type' => 'request',
            'title' => 'Påstand',
            'content' => [
                'På denne baggrund anmoder jeg om:',
                '',
                '1. At afgørelsen ophæves',
                '2. At sagen hjemvises til fornyet behandling',
                '3. At der foretages ny og fyldestgørende sagsoplysning',
                '',
                'Subsidiært anmoder jeg om:',
                '[Alternativ løsning]'
            ]
        ];
        
        // Deadline
        $letter['sections'][] = [
            'type' => 'deadline',
            'title' => 'Frist',
            'content' => [
                'Jeg er opmærksom på klagefristen på 4 uger fra modtagelse af afgørelsen.',
                'Denne klage er indgivet rettidigt.',
                '',
                'Jeg anmoder om skriftlig bekræftelse på modtagelsen af denne klage.'
            ]
        ];
        
        // Signature
        $letter['sections'][] = [
            'type' => 'signature',
            'content' => [
                '',
                'Med venlig hilsen',
                '',
                ($caseDetails['name'] ?? '[Dit navn]'),
                ($caseDetails['address'] ?? '[Din adresse]'),
                ($caseDetails['phone'] ?? '[Dit telefonnummer]'),
                ($caseDetails['email'] ?? '[Din email]')
            ]
        ];
        
        // Add law references used
        $letter['law_refs'] = [
            ['law' => 'Forvaltningsloven', 'paragraphs' => ['§ 10', '§ 19', '§ 22', '§ 24']],
            ['law' => 'Barnets Lov', 'paragraphs' => ['§ 46', '§ 47']]
        ];
        
        // DATABASE: Save complaint if user_id provided
        if ($this->databaseManager && isset($caseDetails['user_id'])) {
            $complaintId = $this->databaseManager->saveComplaint(
                $caseDetails['user_id'],
                $caseDetails['case_id'] ?? null,
                $caseDetails['municipality'] ?? null,
                $caseDetails['decision_date'] ?? null,
                $caseDetails['case_number'] ?? null,
                $caseDetails['subject'] ?? null,
                $letter
            );
            $letter['saved_id'] = $complaintId;
        }
        
        return $letter;
    }
    
    /**
     * Calculate deadlines for legal processes
     * @param string $type Type of deadline (complaint, case_access, etc.)
     * @param string $startDate Start date (Y-m-d)
     * @return array Deadline information
     */
    public function calculateDeadline($type, $startDate) {
        $deadlines = [
            'complaint' => [
                'days' => 28, // 4 weeks
                'name' => 'Klagefrist',
                'law' => 'Barnets Lov § 168',
                'note' => 'Klagen skal være indgivet inden 4 uger efter modtagelse af afgørelsen'
            ],
            'case_access' => [
                'days' => 7,
                'name' => 'Frist for aktindsigt',
                'law' => 'Forvaltningsloven § 16',
                'note' => 'Kommunen skal som udgangspunkt svare inden 7 dage'
            ],
            'complaint_response' => [
                'days' => 28,
                'name' => 'Genoptagelse',
                'law' => 'Forvaltningsloven § 21',
                'note' => 'Kommunen skal normalt behandle genoptagelsesanmodning inden 4 uger'
            ],
            'action_plan' => [
                'days' => 90,
                'name' => 'Handleplan revision',
                'law' => 'Barnets Lov § 140',
                'note' => 'Handleplanen skal revideres mindst hver 3. måned'
            ]
        ];
        
        if (!isset($deadlines[$type])) {
            return ['error' => 'Ukendt frist type'];
        }
        
        $config = $deadlines[$type];
        $start = new \DateTime($startDate);
        $deadline = clone $start;
        $deadline->modify('+' . $config['days'] . ' days');
        
        // Calculate days remaining
        $now = new \DateTime();
        $daysRemaining = $now->diff($deadline)->days;
        $isPast = $now > $deadline;
        
        $result = [
            'type' => $type,
            'name' => $config['name'],
            'start_date' => $start->format('d-m-Y'),
            'deadline' => $deadline->format('d-m-Y'),
            'days_total' => $config['days'],
            'days_remaining' => $isPast ? 0 : $daysRemaining,
            'is_overdue' => $isPast,
            'law_reference' => $config['law'],
            'note' => $config['note'],
            'urgency' => $this->calculateUrgency($daysRemaining, $isPast)
        ];
        
        // DATABASE: Save deadline if user_id provided (via external call)
        // This will be called from REST controller with user context
        
        return $result;
    }
    
    /**
     * Build case timeline from events
     * @param array $events Case events
     * @return array Timeline with analysis
     */
    public function buildCaseTimeline($events) {
        // Sort events by date
        usort($events, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });
        
        $timeline = [
            'events' => [],
            'duration_days' => 0,
            'analysis' => [],
            'critical_dates' => []
        ];
        
        foreach ($events as $event) {
            $eventDate = new \DateTime($event['date']);
            $eventData = [
                'date' => $eventDate->format('d-m-Y'),
                'type' => $event['type'],
                'description' => $event['description'],
                'legal_significance' => $this->getLegalSignificance($event['type'])
            ];
            
            // Check for deadline violations
            if ($event['type'] === 'decision' && isset($event['received_date'])) {
                $received = new \DateTime($event['received_date']);
                $complaintDeadline = clone $received;
                $complaintDeadline->modify('+28 days');
                
                $eventData['complaint_deadline'] = $complaintDeadline->format('d-m-Y');
                $timeline['critical_dates'][] = [
                    'date' => $complaintDeadline->format('d-m-Y'),
                    'type' => 'complaint_deadline',
                    'description' => 'Frist for klage over afgørelse'
                ];
            }
            
            $timeline['events'][] = $eventData;
        }
        
        // Calculate total duration
        if (count($events) > 1) {
            $first = new \DateTime($events[0]['date']);
            $last = new \DateTime($events[count($events)-1]['date']);
            $timeline['duration_days'] = $first->diff($last)->days;
        }
        
        // Add analysis
        $timeline['analysis'] = $this->analyzeTimeline($timeline['events']);
        
        return $timeline;
    }
    
    /**
     * Search case law and precedents
     * @param string $topic Search topic
     * @param string $country User's country code (da_DK or sv_SE)
     * @return array Relevant case law
     */
    public function searchCaseLaw($topic, $country = 'da_DK') {
        // Search Ankestyrelsen for relevant cases
        $results = $this->webSearcher->search($topic, $country, ['ankestyrelsen', 'domstol'], 5);
        
        $caseLaw = [
            'topic' => $topic,
            'searched_at' => date('Y-m-d H:i:s'),
            'cases' => []
        ];
        
        foreach ($results['results'] as $source) {
            foreach ($source['items'] as $item) {
                $caseLaw['cases'][] = [
                    'title' => $item['title'],
                    'snippet' => $item['snippet'],
                    'url' => $item['url'],
                    'source' => $source['source'],
                    'date' => $item['date'] ?? null,
                    'relevance' => $item['relevance_score'] ?? 0.5
                ];
            }
        }
        
        return $caseLaw;
    }
    
    /**
     * Check document for common legal errors
     * @param string $documentText Document content
     * @param string $documentType Type of document
     * @return array Analysis with suggestions
     */
    public function checkDocumentQuality($documentText, $documentType) {
        $checks = [
            'missing_elements' => [],
            'suggestions' => [],
            'compliance_score' => 100
        ];
        
        // Check for required elements based on document type
        switch ($documentType) {
            case 'decision':
                $required = [
                    'begrundelse' => 'Afgørelsen skal indeholde en begrundelse (Forvaltningsloven § 22)',
                    'klagevejledning' => 'Afgørelsen skal indeholde klagevejledning (Forvaltningsloven § 25)',
                    'lovhenvisning' => 'Afgørelsen skal henvise til retsgrundlaget',
                    'partshøring' => 'Der skal være foretaget partshøring (Forvaltningsloven § 19)'
                ];
                break;
            
            case 'action_plan':
                $required = [
                    'formål' => 'Handleplanen skal angive formålet (Barnets Lov § 140)',
                    'tidsramme' => 'Handleplanen skal angive tidsramme',
                    'ansvarlig' => 'Handleplanen skal angive ansvarlig',
                    'barnets perspektiv' => 'Barnets perspektiv skal fremgå'
                ];
                break;
                
            default:
                $required = [];
        }
        
        // Check for missing elements
        $textLower = mb_strtolower($documentText);
        foreach ($required as $element => $requirement) {
            if (strpos($textLower, $element) === false) {
                $checks['missing_elements'][] = [
                    'element' => $element,
                    'requirement' => $requirement,
                    'severity' => 'high'
                ];
                $checks['compliance_score'] -= 15;
            }
        }
        
        // Add suggestions
        if (!empty($checks['missing_elements'])) {
            $checks['suggestions'][] = 'Dokumentet mangler væsentlige elementer som krævet af loven';
            $checks['suggestions'][] = 'Dette kan være grundlag for at klage over afgørelsen';
        }
        
        return $checks;
    }
    
    private function calculateUrgency($daysRemaining, $isPast) {
        if ($isPast) return 'OVERSKREDET';
        if ($daysRemaining <= 3) return 'KRITISK';
        if ($daysRemaining <= 7) return 'HØJT';
        if ($daysRemaining <= 14) return 'MELLEM';
        return 'LAV';
    }
    
    private function getLegalSignificance($eventType) {
        $significance = [
            'decision' => 'Afgørelse træffet - starter klagefrist på 4 uger',
            'complaint' => 'Klage indgivet - kommunen skal behandle',
            'hearing' => 'Partshøring - skal besvares',
            'meeting' => 'Møde afholdt',
            'investigation' => 'Undersøgelse påbegyndt - Barnets Lov § 50',
            'placement' => 'Anbringelse iværksat - Barnets Lov § 58 eller § 76'
        ];
        
        return $significance[$eventType] ?? 'Hændelse registreret';
    }
    
    private function analyzeTimeline($events) {
        $analysis = [];
        
        // Check for long case duration
        if (count($events) > 0) {
            $first = new \DateTime($events[0]['date']);
            $last = new \DateTime($events[count($events)-1]['date']);
            $duration = $first->diff($last)->days;
            
            if ($duration > 180) {
                $analysis[] = [
                    'type' => 'warning',
                    'message' => 'Sagen har haft lang varighed (' . $duration . ' dage)',
                    'suggestion' => 'Overvej at klage over sagsbehandlingstiden'
                ];
            }
        }
        
        // Check for missing action plan updates
        $actionPlanEvents = array_filter($events, function($e) {
            return $e['type'] === 'action_plan_update';
        });
        
        if (count($actionPlanEvents) < 2) {
            $analysis[] = [
                'type' => 'info',
                'message' => 'Få handleplan opdateringer registreret',
                'suggestion' => 'Handleplanen skal revideres mindst hver 3. måned (Barnets Lov § 140)'
            ];
        }
        
        return $analysis;
    }
    
    // ============================================================================
    // DATABASE HELPER METHODS
    // ============================================================================
    
    /**
     * Save deadline to database
     */
    public function saveDeadlineToDatabase($userId, $caseId, $deadlineData) {
        if (!$this->databaseManager) {
            return null;
        }
        
        return $this->databaseManager->saveDeadline(
            $userId,
            $caseId,
            $deadlineData['type'],
            $deadlineData['start_date'],
            $deadlineData['deadline'],
            $deadlineData['days_total'],
            $deadlineData['name'],
            $deadlineData['note'],
            $deadlineData['law_reference']
        );
    }
    
    /**
     * Save timeline event to database
     */
    public function saveTimelineEvent($userId, $caseId, $eventDate, $eventType, $title, $description, $documentId = null) {
        if (!$this->databaseManager) {
            return null;
        }
        
        $legalSignificance = $this->getLegalSignificance($eventType);
        
        return $this->databaseManager->saveTimelineEvent(
            $userId,
            $caseId,
            $eventDate,
            $eventType,
            $title,
            $description,
            $documentId,
            $legalSignificance
        );
    }
    
    /**
     * Get user's saved complaints from database
     */
    public function getUserComplaints($userId) {
        if (!$this->databaseManager) {
            return [];
        }
        
        return $this->databaseManager->getUserComplaints($userId);
    }
    
    /**
     * Get user's deadlines from database
     */
    public function getUserDeadlines($userId) {
        if (!$this->databaseManager) {
            return [];
        }
        
        $active = $this->databaseManager->getActiveDeadlines($userId);
        $overdue = $this->databaseManager->getOverdueDeadlines($userId);
        
        return [
            'active' => $active,
            'overdue' => $overdue,
            'total' => count($active) + count($overdue)
        ];
    }
    
    /**
     * Get case timeline from database
     */
    public function getCaseTimeline($caseId) {
        if (!$this->databaseManager) {
            return [];
        }
        
        return $this->databaseManager->getCaseTimeline($caseId);
    }
}
