<?php
/**
 * Legal Guidance Generator
 * 
 * Genererer personaliseret juridisk rådgivning og vejledning
 * baseret på brugerens specifikke situation.
 * 
 * @package KateAI
 * @subpackage Core
 */

namespace KateAI\Core;

class LegalGuidanceGenerator
{
    private $knowledgeBase;
    private $webSearcher;
    private $databaseManager;
    private $logger;

    /**
     * Constructor
     * 
     * @param KnowledgeBase $knowledgeBase
     * @param WebSearcher $webSearcher
     * @param DatabaseManager $databaseManager
     * @param Logger|null $logger
     */
    public function __construct($knowledgeBase, $webSearcher, $databaseManager, $logger = null)
    {
        $this->knowledgeBase = $knowledgeBase;
        $this->webSearcher = $webSearcher;
        $this->databaseManager = $databaseManager;
        $this->logger = $logger;
    }

    /**
     * Generér personaliseret juridisk vejledning
     * 
     * @param array $situation Brugerens situation
     *   - situation_type: 'anbringelse', 'klage', 'samvaer', 'aktindsigt', etc.
     *   - details: array med specifikke detaljer
     *   - user_id: (optional) bruger ID for at gemme
     *   - case_id: (optional) sag ID
     * 
     * @return array Guidance data med råd, handlingsplan, ressourcer
     */
    public function generateGuidance($situation)
    {
        $situationType = $situation['situation_type'] ?? 'general';
        $details = $situation['details'] ?? [];
        $userId = $situation['user_id'] ?? null;
        $caseId = $situation['case_id'] ?? null;

        // Find relevant intents fra knowledge base
        $relevantIntents = $this->findRelevantIntents($situationType);

        // Søg online efter opdateret information
        $onlineResources = $this->searchOnlineGuidance($situationType, $details);

        // Generér guidance baseret på situation type
        $guidance = $this->buildGuidance($situationType, $details, $relevantIntents, $onlineResources);

        // Gem til database hvis user_id er angivet
        if ($userId) {
            $savedId = $this->saveGuidanceToDatabase($userId, $caseId, $guidance);
            $guidance['saved_id'] = $savedId;
        }

        return $guidance;
    }

    /**
     * Find relevante intents fra knowledge base
     */
    private function findRelevantIntents($situationType)
    {
        $topicMap = [
            'anbringelse' => 'barnets_lov',
            'klage' => 'klage',
            'samvaer' => 'barnets_lov',
            'aktindsigt' => 'aktindsigt',
            'handleplan' => 'barnets_lov',
            'bisidder' => 'barnets_lov',
            'boernesamtale' => 'barnets_lov'
        ];

        $topic = $topicMap[$situationType] ?? 'barnets_lov';
        
        $allIntents = $this->knowledgeBase->getAllIntents();
        $relevant = [];

        foreach ($allIntents as $intent) {
            if (isset($intent['topic']) && $intent['topic'] === $topic) {
                $relevant[] = $intent;
            }
        }

        return $relevant;
    }

    /**
     * Søg online efter guidance ressourcer
     */
    private function searchOnlineGuidance($situationType, $details)
    {
        $queries = $this->buildSearchQueries($situationType, $details);
        $resources = [];

        foreach ($queries as $query) {
            try {
                // AST (Ankestyrelsen) is Danish, so use da_DK
                $results = $this->webSearcher->search($query, 'da_DK', ['ankestyrelsen']);
                if (!empty($results)) {
                    $resources = array_merge($resources, array_slice($results, 0, 3));
                }
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error("Guidance search failed: " . $e->getMessage());
                }
            }
        }

        return $resources;
    }

    /**
     * Byg søgeforespørgsler baseret på situation
     */
    private function buildSearchQueries($situationType, $details)
    {
        $queries = [];

        switch ($situationType) {
            case 'anbringelse':
                $queries[] = "anbringelse uden samtykke vejledning";
                $queries[] = "rettigheder ved anbringelse";
                if (!empty($details['with_consent'])) {
                    $queries[] = "anbringelse med samtykke";
                }
                break;

            case 'klage':
                $queries[] = "klage til ankestyrelsen vejledning";
                $queries[] = "hvordan klager jeg over afgørelse";
                if (!empty($details['decision_type'])) {
                    $queries[] = "klage over " . $details['decision_type'];
                }
                break;

            case 'samvaer':
                $queries[] = "samvær anbragte børn vejledning";
                $queries[] = "rettigheder til samvær";
                break;

            case 'aktindsigt':
                $queries[] = "aktindsigt børnesag vejledning";
                $queries[] = "hvordan søger jeg aktindsigt";
                break;

            case 'handleplan':
                $queries[] = "handleplan krav vejledning";
                $queries[] = "hvad skal handleplan indeholde";
                break;

            default:
                $queries[] = "juridisk vejledning " . $situationType;
                break;
        }

        return $queries;
    }

    /**
     * Byg komplet guidance baseret på alle data
     */
    private function buildGuidance($situationType, $details, $relevantIntents, $onlineResources)
    {
        $guidance = [
            'situation_type' => $situationType,
            'title' => $this->getGuidanceTitle($situationType),
            'summary' => $this->buildSummary($situationType, $details),
            'immediate_actions' => $this->buildImmediateActions($situationType, $details),
            'detailed_steps' => $this->buildDetailedSteps($situationType, $details, $relevantIntents),
            'legal_basis' => $this->extractLegalBasis($relevantIntents),
            'your_rights' => $this->buildRightsList($situationType, $relevantIntents),
            'common_mistakes' => $this->buildCommonMistakes($situationType),
            'resources' => $this->buildResourcesList($relevantIntents, $onlineResources),
            'next_steps' => $this->buildNextSteps($situationType, $details),
            'related_topics' => $this->buildRelatedTopics($situationType, $relevantIntents),
            'generated_at' => current_time('mysql'),
            'confidence' => $this->calculateConfidence($relevantIntents, $onlineResources)
        ];

        return $guidance;
    }

    /**
     * Hent titel til guidance
     */
    private function getGuidanceTitle($situationType)
    {
        $titles = [
            'anbringelse' => 'Vejledning: Anbringelse af dit barn',
            'klage' => 'Vejledning: Klage over afgørelse',
            'samvaer' => 'Vejledning: Samvær med anbragte børn',
            'aktindsigt' => 'Vejledning: Aktindsigt i din sag',
            'handleplan' => 'Vejledning: Handleplan',
            'bisidder' => 'Vejledning: Ret til bisidder',
            'boernesamtale' => 'Vejledning: Børnesamtale',
            'general' => 'Juridisk vejledning'
        ];

        return $titles[$situationType] ?? 'Juridisk vejledning';
    }

    /**
     * Byg opsummering af situationen
     */
    private function buildSummary($situationType, $details)
    {
        $summaries = [
            'anbringelse' => "Du står i en situation hvor dit barn er blevet eller skal anbringes. Dette er en meget svær situation, men du har rettigheder og muligheder for at påvirke forløbet. Denne vejledning giver dig konkrete råd om hvad du skal gøre nu.",
            
            'klage' => "Du ønsker at klage over en afgørelse fra kommunen. Du har 4 uger fra afgørelsens modtagelse til at klage. Denne vejledning hjælper dig gennem klageprocessen trin for trin.",
            
            'samvaer' => "Du ønsker vejledning om samvær med dit anbragte barn. Du har ret til samvær medmindre det er til skade for barnet. Denne vejledning forklarer dine rettigheder og hvordan du kan påvirke samværet.",
            
            'aktindsigt' => "Du ønsker at få aktindsigt i din sag hos kommunen. Du har ret til at se alle dokumenter i din sag. Denne vejledning viser hvordan du anmoder om aktindsigt.",
            
            'handleplan' => "Du har brug for vejledning om handleplan. Handleplanen er et vigtigt dokument der beskriver mål og indsatser. Denne vejledning forklarer hvad handleplanen skal indeholde og hvordan du bliver inddraget.",
            
            'general' => "Denne vejledning giver dig juridisk information og råd om din situation. Følg trinnene nedenfor for at få et overblik."
        ];

        return $summaries[$situationType] ?? $summaries['general'];
    }

    /**
     * Byg liste over øjeblikkelige handlinger
     */
    private function buildImmediateActions($situationType, $details)
    {
        $actions = [];

        switch ($situationType) {
            case 'anbringelse':
                $actions = [
                    '📄 Få kopi af afgørelsen i skriftlig form hvis du ikke har den',
                    '📅 Tjek klagefristen - du har kun 4 uger',
                    '👥 Kontakt en bisidder eller advokat hurtigst muligt',
                    '🔍 Anmod om aktindsigt i hele sagen',
                    '📝 Skriv ned hvad der er sket kronologisk'
                ];
                break;

            case 'klage':
                $actions = [
                    '📅 Tjek STRAKS om klagefristen er udløbet (4 uger)',
                    '📄 Saml alle dokumenter i sagen',
                    '🔍 Anmod om aktindsigt hvis du mangler dokumenter',
                    '✍️ Begynd at skrive klage - brug vores skabelon',
                    '📧 Send klagen anbefalet eller via e-Boks'
                ];
                break;

            case 'samvaer':
                $actions = [
                    '📄 Få kopi af samværsaftalen skriftligt',
                    '📝 Dokumentér alle samvær (dato, tidspunkt, hvad I lavede)',
                    '👥 Kontakt din sagsbehandler om du ønsker mere samvær',
                    '🔍 Anmod om aktindsigt i begrundelser for begrænsninger',
                    '📅 Aftal fast samvær så barnet kan regne med det'
                ];
                break;

            case 'aktindsigt':
                $actions = [
                    '✍️ Skriv aktindsigtsanmodning til kommunen NU',
                    '📧 Send via e-Boks eller anbefalet brev',
                    '📅 Notér dato for afsendelse (kommunen har 7 dage)',
                    '📋 Vær konkret om hvilke dokumenter du vil se',
                    '💾 Gem kopi af din anmodning'
                ];
                break;

            case 'handleplan':
                $actions = [
                    '📄 Få kopi af den nuværende handleplan',
                    '📝 Læs planen grundigt igennem',
                    '✅ Tjek om planen indeholder alt den skal (se tjekliste)',
                    '💭 Skriv dine kommentarer og forslag ned',
                    '📅 Book møde med sagsbehandler om handleplanen'
                ];
                break;

            default:
                $actions = [
                    '📄 Saml alle relevante dokumenter',
                    '🔍 Overvej om du skal søge aktindsigt',
                    '👥 Kontakt en bisidder eller rådgiver',
                    '📝 Dokumentér situationen skriftligt',
                    '📅 Vær opmærksom på frister'
                ];
        }

        return $actions;
    }

    /**
     * Byg detaljeret trin-for-trin guide
     */
    private function buildDetailedSteps($situationType, $details, $relevantIntents)
    {
        $steps = [];

        // Hent answer_long fra relevante intents
        foreach ($relevantIntents as $intent) {
            if (!empty($intent['answer_long'])) {
                // Kombiner answer_long arrays
                if (is_array($intent['answer_long'])) {
                    $steps = array_merge($steps, $intent['answer_long']);
                }
            }
        }

        // Hvis ingen steps fundet, byg basic steps
        if (empty($steps)) {
            $steps = $this->buildBasicSteps($situationType);
        }

        return $steps;
    }

    /**
     * Byg basic steps hvis ingen intents matcher
     */
    private function buildBasicSteps($situationType)
    {
        return [
            "TRIN 1: Saml information",
            "Få overblik over din situation ved at samle alle relevante dokumenter og notater.",
            "",
            "TRIN 2: Forstå dine rettigheder",
            "Læs om dine juridiske rettigheder i forhold til denne situation.",
            "",
            "TRIN 3: Søg hjælp",
            "Kontakt en bisidder, advokat eller rådgivningsorganisation.",
            "",
            "TRIN 4: Tag handling",
            "Følg de anbefalede handlinger beskrevet i denne vejledning."
        ];
    }

    /**
     * Udtræk juridisk grundlag fra intents
     */
    private function extractLegalBasis($relevantIntents)
    {
        $legalBasis = [];

        foreach ($relevantIntents as $intent) {
            if (!empty($intent['law_refs'])) {
                foreach ($intent['law_refs'] as $lawRef) {
                    $key = $lawRef['law'] . ' ' . $lawRef['paragraph'];
                    if (!isset($legalBasis[$key])) {
                        $legalBasis[$key] = [
                            'law' => $lawRef['law'],
                            'paragraph' => $lawRef['paragraph'],
                            'note' => $lawRef['note'] ?? '',
                            'url' => $lawRef['url'] ?? ''
                        ];
                    }
                }
            }
        }

        return array_values($legalBasis);
    }

    /**
     * Byg liste over brugerens rettigheder
     */
    private function buildRightsList($situationType, $relevantIntents)
    {
        $rights = [];

        // Standard rettigheder
        $standardRights = [
            '✓ Ret til at blive hørt (partshøring)',
            '✓ Ret til aktindsigt i sagen',
            '✓ Ret til bisidder ved møder',
            '✓ Ret til skriftlig og begrundet afgørelse',
            '✓ Ret til at klage til Ankestyrelsen',
            '✓ Ret til klagevejledning'
        ];

        $rights = array_merge($rights, $standardRights);

        // Tilføj situation-specifikke rettigheder
        switch ($situationType) {
            case 'anbringelse':
                $rights[] = '✓ Ret til samvær med barnet';
                $rights[] = '✓ Ret til inddragelse i handleplan';
                $rights[] = '✓ Ret til at anmode om hjemgivelse';
                break;

            case 'samvaer':
                $rights[] = '✓ Ret til samvær medmindre det skader barnet';
                $rights[] = '✓ Ret til begrundelse for samværsbegrænsning';
                $rights[] = '✓ Ret til at anmode om ændring af samvær';
                break;

            case 'handleplan':
                $rights[] = '✓ Ret til aktiv inddragelse i handleplanen';
                $rights[] = '✓ Ret til at komme med forslag';
                $rights[] = '✓ Ret til revision hver 6. måned';
                break;
        }

        return $rights;
    }

    /**
     * Byg liste over almindelige fejl
     */
    private function buildCommonMistakes($situationType)
    {
        $mistakes = [
            'anbringelse' => [
                '❌ At vente med at reagere - du har kun 4 ugers klagefrist!',
                '❌ At underskrive noget uden at forstå det',
                '❌ At gå til møder alene uden bisidder',
                '❌ At ikke bede om aktindsigt',
                '❌ At kommunikere ukonstruktivt med kommunen'
            ],
            'klage' => [
                '❌ At overskride 4 ugers klagefristen',
                '❌ At sende klagen det forkerte sted hen (send til kommunen, ikke Ankestyrelsen)',
                '❌ At ikke være konkret nok i klagen',
                '❌ At glemme at vedlægge dokumentation',
                '❌ At ikke holde kopi af alt'
            ],
            'samvaer' => [
                '❌ At udeblive fra aftalt samvær uden grund',
                '❌ At bruge samvær til at pumpe barnet for information',
                '❌ At tale negativt om plejefamilie/institution til barnet',
                '❌ At ikke dokumentere samværsproblemer',
                '❌ At kontakte barnet uden for aftalt samvær'
            ],
            'aktindsigt' => [
                '❌ At være for ukonkret i anmodningen',
                '❌ At acceptere afslag uden at klage',
                '❌ At vente for længe med at anmode',
                '❌ At ikke følge op hvis kommunen ikke svarer',
                '❌ At betale for meget for kopier'
            ]
        ];

        return $mistakes[$situationType] ?? [
            '❌ At handle uden juridisk rådgivning',
            '❌ At ignorere frister',
            '❌ At ikke dokumentere forhold',
            '❌ At kommunikere ukonstruktivt'
        ];
    }

    /**
     * Byg ressourceliste
     */
    private function buildResourcesList($relevantIntents, $onlineResources)
    {
        $resources = [];

        // Tilføj links fra intents
        foreach ($relevantIntents as $intent) {
            if (!empty($intent['external_links'])) {
                foreach ($intent['external_links'] as $link) {
                    $resources[] = [
                        'type' => 'law_reference',
                        'title' => $link['title'],
                        'url' => $link['url'],
                        'source' => 'knowledge_base'
                    ];
                }
            }
        }

        // Tilføj online ressourcer
        foreach ($onlineResources as $resource) {
            $resources[] = [
                'type' => 'online_resource',
                'title' => $resource['title'] ?? 'Ressource',
                'url' => $resource['url'] ?? '',
                'snippet' => $resource['snippet'] ?? '',
                'source' => $resource['source'] ?? 'web'
            ];
        }

        // Tilføj standard ressourcer
        $resources[] = [
            'type' => 'organization',
            'title' => 'Ankestyrelsen',
            'url' => 'https://ast.dk',
            'description' => 'Klagemyndighed og vejledning',
            'source' => 'standard'
        ];

        $resources[] = [
            'type' => 'organization',
            'title' => 'Ret til Familie',
            'url' => 'https://rettilfamilie.com',
            'description' => 'Forening for forældre i sager',
            'source' => 'standard'
        ];

        return $resources;
    }

    /**
     * Byg næste trin
     */
    private function buildNextSteps($situationType, $details)
    {
        $steps = [];

        switch ($situationType) {
            case 'anbringelse':
                $steps = [
                    '1. Læs denne vejledning grundigt igennem',
                    '2. Følg "Øjeblikkelige handlinger" øverst',
                    '3. Book tid med bisidder eller advokat',
                    '4. Beslut om du vil klage (4 ugers frist!)',
                    '5. Følg op på samvær og handleplan'
                ];
                break;

            case 'klage':
                $steps = [
                    '1. Tjek klagefristen NU (4 uger)',
                    '2. Anmod om aktindsigt hvis nødvendigt',
                    '3. Skriv klagen med vores værktøj',
                    '4. Få bisidder til at gennemlæse klagen',
                    '5. Send klagen anbefalet',
                    '6. Følg op efter 4 uger hvis ikke hørt fra kommunen'
                ];
                break;

            default:
                $steps = [
                    '1. Gennemgå vejledningen',
                    '2. Følg anbefalede øjeblikkelige handlinger',
                    '3. Søg yderligere hjælp hvis nødvendigt',
                    '4. Dokumentér alt',
                    '5. Følg op regelmæssigt'
                ];
        }

        return $steps;
    }

    /**
     * Byg liste over relaterede emner
     */
    private function buildRelatedTopics($situationType, $relevantIntents)
    {
        $related = [];

        foreach ($relevantIntents as $intent) {
            if ($intent['intent_id'] !== $situationType) {
                $related[] = [
                    'intent_id' => $intent['intent_id'],
                    'title' => $intent['title'],
                    'topic' => $intent['topic'] ?? 'general'
                ];
            }
        }

        return array_slice($related, 0, 5); // Max 5 related topics
    }

    /**
     * Beregn confidence score
     */
    private function calculateConfidence($relevantIntents, $onlineResources)
    {
        $score = 50; // Base score

        // +10 for hver relevant intent (max +30)
        $score += min(count($relevantIntents) * 10, 30);

        // +20 hvis vi har online ressourcer
        if (!empty($onlineResources)) {
            $score += 20;
        }

        return min($score, 98); // Max 98%
    }

    /**
     * Gem guidance til database
     */
    private function saveGuidanceToDatabase($userId, $caseId, $guidance)
    {
        return $this->databaseManager->saveGuidance(
            $userId,
            $caseId,
            $guidance['situation_type'],
            $guidance['title'],
            json_encode($guidance),
            $guidance['confidence']
        );
    }

    /**
     * Hent brugerens guidance historik
     */
    public function getUserGuidanceHistory($userId, $limit = 10)
    {
        return $this->databaseManager->getUserGuidanceHistory($userId, $limit);
    }
}
