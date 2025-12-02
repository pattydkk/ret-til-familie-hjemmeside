<?php
/**
 * RTF Platform Translations
 * Central translation file for all platform pages
 * Supports: Danish (da), Swedish (sv), English (en)
 */

function rtf_translate($key, $lang = 'da') {
    $translations = [
        // Navigation & Common
        'platform' => ['da' => 'Platform', 'sv' => 'Plattform', 'en' => 'Platform'],
        'profile' => ['da' => 'Profil', 'sv' => 'Profil', 'en' => 'Profile'],
        'wall' => ['da' => 'Væg', 'sv' => 'Vägg', 'en' => 'Wall'],
        'images' => ['da' => 'Billeder', 'sv' => 'Bilder', 'en' => 'Images'],
        'documents' => ['da' => 'Dokumenter', 'sv' => 'Dokument', 'en' => 'Documents'],
        'friends' => ['da' => 'Venner', 'sv' => 'Vänner', 'en' => 'Friends'],
        'forum' => ['da' => 'Forum', 'sv' => 'Forum', 'en' => 'Forum'],
        'news' => ['da' => 'Nyheder', 'sv' => 'Nyheter', 'en' => 'News'],
        'case_help' => ['da' => 'Sagshjælp', 'sv' => 'Ärendehjälp', 'en' => 'Case Help'],
        'kate_ai' => ['da' => 'Kate AI', 'sv' => 'Kate AI', 'en' => 'Kate AI'],
        'settings' => ['da' => 'Indstillinger', 'sv' => 'Inställningar', 'en' => 'Settings'],
        'chat' => ['da' => 'Beskeder', 'sv' => 'Meddelanden', 'en' => 'Messages'],
        'reports' => ['da' => 'Rapporter', 'sv' => 'Rapporter', 'en' => 'Reports'],
        'admin' => ['da' => 'Administration', 'sv' => 'Administration', 'en' => 'Administration'],
        
        // Actions
        'send' => ['da' => 'Send', 'sv' => 'Skicka', 'en' => 'Send'],
        'save' => ['da' => 'Gem', 'sv' => 'Spara', 'en' => 'Save'],
        'cancel' => ['da' => 'Annuller', 'sv' => 'Avbryt', 'en' => 'Cancel'],
        'delete' => ['da' => 'Slet', 'sv' => 'Radera', 'en' => 'Delete'],
        'edit' => ['da' => 'Rediger', 'sv' => 'Redigera', 'en' => 'Edit'],
        'upload' => ['da' => 'Upload', 'sv' => 'Ladda upp', 'en' => 'Upload'],
        'download' => ['da' => 'Download', 'sv' => 'Ladda ner', 'en' => 'Download'],
        'share' => ['da' => 'Del', 'sv' => 'Dela', 'en' => 'Share'],
        'search' => ['da' => 'Søg', 'sv' => 'Sök', 'en' => 'Search'],
        'filter' => ['da' => 'Filtrer', 'sv' => 'Filtrera', 'en' => 'Filter'],
        'logout' => ['da' => 'Log ud', 'sv' => 'Logga ut', 'en' => 'Logout'],
        
        // Kate AI
        'ask_kate' => ['da' => 'Spørg Kate', 'sv' => 'Fråga Kate', 'en' => 'Ask Kate'],
        'kate_greeting' => ['da' => 'Hej! Jeg er Kate, din juridiske assistent.', 'sv' => 'Hej! Jag är Kate, din juridiska assistent.', 'en' => 'Hello! I am Kate, your legal assistant.'],
        'kate_intro' => ['da' => 'Kate er din personlige AI-assistent, der kan hjælpe dig med juridiske spørgsmål, analysere dokumenter og guide dig gennem komplekse sager inden for familie- og socialret.', 'sv' => 'Kate är din personliga AI-assistent som kan hjälpa dig med juridiska frågor, analysera dokument och guida dig genom komplexa ärenden inom familje- och socialrätt.', 'en' => 'Kate is your personal AI assistant who can help you with legal questions, analyze documents and guide you through complex cases in family and social law.'],
        'ask_anything' => ['da' => 'Spørg om alt', 'sv' => 'Fråga om allt', 'en' => 'Ask about anything'],
        'document_analysis' => ['da' => 'Dokumentanalyse', 'sv' => 'Dokumentanalys', 'en' => 'Document Analysis'],
        'legal_guidance' => ['da' => 'Juridisk vejledning', 'sv' => 'Juridisk vägledning', 'en' => 'Legal Guidance'],
        'complaint_generator' => ['da' => 'Klage generator', 'sv' => 'Klagogenerator', 'en' => 'Complaint Generator'],
        'deadline_tracker' => ['da' => 'Frist oversigt', 'sv' => 'Fristöversikt', 'en' => 'Deadline Tracker'],
        
        // Profile
        'my_profile' => ['da' => 'Min profil', 'sv' => 'Min profil', 'en' => 'My Profile'],
        'edit_profile' => ['da' => 'Rediger profil', 'sv' => 'Redigera profil', 'en' => 'Edit Profile'],
        'change_password' => ['da' => 'Skift adgangskode', 'sv' => 'Byt lösenord', 'en' => 'Change Password'],
        'full_name' => ['da' => 'Fulde navn', 'sv' => 'Fullständigt namn', 'en' => 'Full Name'],
        'email' => ['da' => 'Email', 'sv' => 'E-post', 'en' => 'Email'],
        'phone' => ['da' => 'Telefon', 'sv' => 'Telefon', 'en' => 'Phone'],
        'birthday' => ['da' => 'Fødselsdag', 'sv' => 'Födelsedag', 'en' => 'Birthday'],
        'bio' => ['da' => 'Biografi', 'sv' => 'Biografi', 'en' => 'Biography'],
        'language' => ['da' => 'Sprog', 'sv' => 'Språk', 'en' => 'Language'],
        'subscription' => ['da' => 'Abonnement', 'sv' => 'Prenumeration', 'en' => 'Subscription'],
        'subscription_active' => ['da' => 'Aktivt abonnement', 'sv' => 'Aktiv prenumeration', 'en' => 'Active Subscription'],
        'subscription_inactive' => ['da' => 'Inaktivt abonnement', 'sv' => 'Inaktiv prenumeration', 'en' => 'Inactive Subscription'],
        
        // Wall/Feed
        'whats_on_your_mind' => ['da' => 'Hvad tænker du på?', 'sv' => 'Vad tänker du på?', 'en' => 'What\'s on your mind?'],
        'write_post' => ['da' => 'Skriv indlæg', 'sv' => 'Skriv inlägg', 'en' => 'Write post'],
        'post' => ['da' => 'Opslag', 'sv' => 'Inlägg', 'en' => 'Post'],
        'like' => ['da' => 'Synes godt om', 'sv' => 'Gilla', 'en' => 'Like'],
        'comment' => ['da' => 'Kommentar', 'sv' => 'Kommentar', 'en' => 'Comment'],
        'shared' => ['da' => 'delte', 'sv' => 'delade', 'en' => 'shared'],
        
        // Chat/Messages
        'new_message' => ['da' => 'Ny besked', 'sv' => 'Nytt meddelande', 'en' => 'New Message'],
        'conversations' => ['da' => 'Samtaler', 'sv' => 'Konversationer', 'en' => 'Conversations'],
        'type_message' => ['da' => 'Skriv en besked...', 'sv' => 'Skriv ett meddelande...', 'en' => 'Type a message...'],
        'unread_messages' => ['da' => 'Ulæste beskeder', 'sv' => 'Olästa meddelanden', 'en' => 'Unread Messages'],
        
        // Reports
        'reports_analyses' => ['da' => 'Rapporter & Analyser', 'sv' => 'Rapporter & Analyser', 'en' => 'Reports & Analyses'],
        'download_reports' => ['da' => 'Download rapporter og analyser fra Ret til Familie', 'sv' => 'Ladda ner rapporter och analyser från Rätt till Familj', 'en' => 'Download reports and analyses from Right to Family'],
        'country' => ['da' => 'Land', 'sv' => 'Land', 'en' => 'Country'],
        'city' => ['da' => 'By', 'sv' => 'Stad', 'en' => 'City'],
        'case_type' => ['da' => 'Sagstype', 'sv' => 'Ärendetyp', 'en' => 'Case Type'],
        'report_type' => ['da' => 'Rapporttype', 'sv' => 'Rapporttyp', 'en' => 'Report Type'],
        'legal' => ['da' => 'Juridisk', 'sv' => 'Juridisk', 'en' => 'Legal'],
        'psychological' => ['da' => 'Psykologisk', 'sv' => 'Psykologisk', 'en' => 'Psychological'],
        'social' => ['da' => 'Socialfaglig', 'sv' => 'Socialfaglig', 'en' => 'Social'],
        'all_reports' => ['da' => 'Alle rapporter', 'sv' => 'Alla rapporter', 'en' => 'All Reports'],
        'reset_filters' => ['da' => 'Nulstil filtre', 'sv' => 'Återställ filter', 'en' => 'Reset Filters'],
        'downloads' => ['da' => 'Downloads', 'sv' => 'Nedladdningar', 'en' => 'Downloads'],
        
        // Admin
        'admin_dashboard' => ['da' => 'Admin Dashboard', 'sv' => 'Admin Dashboard', 'en' => 'Admin Dashboard'],
        'user_management' => ['da' => 'Brugerstyring', 'sv' => 'Användarhantering', 'en' => 'User Management'],
        'total_users' => ['da' => 'Samlede brugere', 'sv' => 'Totalt användare', 'en' => 'Total Users'],
        'active_subscriptions' => ['da' => 'Aktive abonnementer', 'sv' => 'Aktiva prenumerationer', 'en' => 'Active Subscriptions'],
        'kate_sessions' => ['da' => 'Kate sessioner', 'sv' => 'Kate sessioner', 'en' => 'Kate Sessions'],
        'analytics' => ['da' => 'Analytics', 'sv' => 'Statistik', 'en' => 'Analytics'],
        
        // Errors & Messages
        'error' => ['da' => 'Fejl', 'sv' => 'Fel', 'en' => 'Error'],
        'success' => ['da' => 'Succes', 'sv' => 'Framgång', 'en' => 'Success'],
        'loading' => ['da' => 'Indlæser...', 'sv' => 'Laddar...', 'en' => 'Loading...'],
        'no_results' => ['da' => 'Ingen resultater', 'sv' => 'Inga resultat', 'en' => 'No Results'],
        'confirm_delete' => ['da' => 'Er du sikker på du vil slette?', 'sv' => 'Är du säker på att du vill radera?', 'en' => 'Are you sure you want to delete?'],
        'saved_successfully' => ['da' => 'Gemt med succes', 'sv' => 'Sparat framgångsrikt', 'en' => 'Saved Successfully'],
        'unauthorized' => ['da' => 'Ikke autoriseret', 'sv' => 'Ej behörig', 'en' => 'Unauthorized'],
        
        // Privacy & Legal
        'privacy' => ['da' => 'Privatlivspolitik', 'sv' => 'Integritetspolicy', 'en' => 'Privacy Policy'],
        'terms' => ['da' => 'Vilkår', 'sv' => 'Villkor', 'en' => 'Terms'],
        'gdpr_notice' => ['da' => 'Ved at oprette en konto accepterer du vores privatlivspolitik. Din fødselsdag vil blive anonymiseret til ##-##-ÅÅÅÅ.', 'sv' => 'Genom att skapa ett konto accepterar du vår integritetspolicy. Din födelsedag kommer att anonymiseras till ##-##-ÅÅÅÅ.', 'en' => 'By creating an account you accept our privacy policy. Your birthday will be anonymized to ##-##-YYYY.'],
        'phone_privacy' => ['da' => '🔒 Dit telefonnummer er kun synligt for administratorer - aldrig for andre brugere.', 'sv' => '🔒 Ditt telefonnummer är endast synligt för administratörer - aldrig för andra användare.', 'en' => '🔒 Your phone number is only visible to administrators - never to other users.'],
        'pricing_note' => ['da' => '💰 Alle priser er i DKK (danske kroner)', 'sv' => '💰 Alla priser är i DKK (danska kronor)', 'en' => '💰 All prices are in DKK (Danish Kroner)'],
        
        // Subscription
        'subscribe_now' => ['da' => 'Abonner nu', 'sv' => 'Prenumerera nu', 'en' => 'Subscribe Now'],
        'monthly_subscription' => ['da' => 'Månedligt abonnement', 'sv' => 'Månadsprenumeration', 'en' => 'Monthly Subscription'],
        'subscription_benefits' => ['da' => 'Abonnementsfordele', 'sv' => 'Prenumerationsfördelar', 'en' => 'Subscription Benefits'],
        
        // Case Types
        'custody' => ['da' => 'Forældremyndighed', 'sv' => 'Vårdnad', 'en' => 'Custody'],
        'visitation' => ['da' => 'Samvær', 'sv' => 'Umgänge', 'en' => 'Visitation'],
        'child_protection' => ['da' => 'Børnebeskyttelse', 'sv' => 'Barnskydd', 'en' => 'Child Protection'],
        'foster_care' => ['da' => 'Anbringelse', 'sv' => 'Placering', 'en' => 'Foster Care'],
        'social_services' => ['da' => 'Socialforvaltning', 'sv' => 'Socialtjänst', 'en' => 'Social Services'],
        
        // Countries
        'denmark' => ['da' => 'Danmark', 'sv' => 'Danmark', 'en' => 'Denmark'],
        'sweden' => ['da' => 'Sverige', 'sv' => 'Sverige', 'en' => 'Sweden'],
        'international' => ['da' => 'International', 'sv' => 'Internationell', 'en' => 'International'],
        
        // Status
        'online' => ['da' => 'Online', 'sv' => 'Online', 'en' => 'Online'],
        'offline' => ['da' => 'Offline', 'sv' => 'Offline', 'en' => 'Offline'],
        'active' => ['da' => 'Aktiv', 'sv' => 'Aktiv', 'en' => 'Active'],
        'inactive' => ['da' => 'Inaktiv', 'sv' => 'Inaktiv', 'en' => 'Inactive'],
        
        // Complaint Generator (Klagegenerator)
        'complaint_generator_title' => ['da' => 'Klage Generator', 'sv' => 'Klagogenerator', 'en' => 'Complaint Generator'],
        'generate_complaint' => ['da' => 'Generer klage', 'sv' => 'Generera klagomål', 'en' => 'Generate Complaint'],
        'complaint_to' => ['da' => 'Klage til', 'sv' => 'Klagomål till', 'en' => 'Complaint to'],
        'output_language' => ['da' => 'Output sprog', 'sv' => 'Utdataspråk', 'en' => 'Output Language'],
        'complainant_name' => ['da' => 'Dit navn', 'sv' => 'Ditt namn', 'en' => 'Your Name'],
        'complainant_address' => ['da' => 'Din adresse', 'sv' => 'Din adress', 'en' => 'Your Address'],
        'complaint_subject' => ['da' => 'Klagepunkt / Emne', 'sv' => 'Klagomålspunkt / Ämne', 'en' => 'Complaint Subject'],
        'complaint_description' => ['da' => 'Beskriv situationen', 'sv' => 'Beskriv situationen', 'en' => 'Describe the situation'],
        'desired_outcome' => ['da' => 'Ønsket resultat', 'sv' => 'Önskat resultat', 'en' => 'Desired Outcome'],
        'complaint_points' => ['da' => 'Klagepunkter', 'sv' => 'Klagomålspunkter', 'en' => 'Complaint Points'],
        'add_complaint_point' => ['da' => 'Tilføj klagepunkt', 'sv' => 'Lägg till klagomålspunkt', 'en' => 'Add Complaint Point'],
        'municipal_complaint' => ['da' => 'Kommunal klage', 'sv' => 'Kommunalt klagomål', 'en' => 'Municipal Complaint'],
        'ankestyrelsen' => ['da' => 'Ankestyrelsen', 'sv' => 'Överklagandenämnden', 'en' => 'Appeals Board'],
        'ombudsmand' => ['da' => 'Ombudsmanden', 'sv' => 'Ombudsmannen', 'en' => 'Ombudsman'],
        'echr_complaint' => ['da' => 'EMK / Menneskerettighedsdomstolen', 'sv' => 'EMRK / Europadomstolen', 'en' => 'ECHR / European Court of Human Rights'],
        'european_commission' => ['da' => 'Europa-Kommissionen', 'sv' => 'Europeiska kommissionen', 'en' => 'European Commission'],
        'child_committee' => ['da' => 'Børneudvalget (FN)', 'sv' => 'Barnkommittén (FN)', 'en' => 'Committee on the Rights of the Child (UN)'],
        
        // Case Help (Sagshjælp)
        'case_help_title' => ['da' => 'Sagshjælp', 'sv' => 'Ärendehjälp', 'en' => 'Case Help'],
        'document_templates' => ['da' => 'Dokument skabeloner', 'sv' => 'Dokumentmallar', 'en' => 'Document Templates'],
        'letter_generator' => ['da' => 'Brev generator', 'sv' => 'Brevgenerator', 'en' => 'Letter Generator'],
        'request_letter' => ['da' => 'Anmodningsskrivelse', 'sv' => 'Begäran', 'en' => 'Request Letter'],
        'objection_letter' => ['da' => 'Indsigelse', 'sv' => 'Invändning', 'en' => 'Objection'],
        'appeal_letter' => ['da' => 'Klage', 'sv' => 'Överklagande', 'en' => 'Appeal'],
        'documentation_guide' => ['da' => 'Dokumentations vejledning', 'sv' => 'Dokumentationsvägledning', 'en' => 'Documentation Guide'],
        'record_meetings' => ['da' => 'Optag møder', 'sv' => 'Spela in möten', 'en' => 'Record Meetings'],
        'transcription_tips' => ['da' => 'Transskriptions tips', 'sv' => 'Transkriptionstips', 'en' => 'Transcription Tips'],
        'evidence_collection' => ['da' => 'Bevis indsamling', 'sv' => 'Bevisinsamling', 'en' => 'Evidence Collection'],
        
        // Legal Guidance
        'disclaimer_not_lawyer' => ['da' => '⚠️ Vi erstatter IKKE din advokat - vi hjælper dig til bedre at hjælpe dig selv.', 'sv' => '⚠️ Vi ersätter INTE din advokat - vi hjälper dig att bättre hjälpa dig själv.', 'en' => '⚠️ We do NOT replace your lawyer - we help you better help yourself.'],
        'need_professional_help' => ['da' => '👨‍⚖️ Har du brug for professionel hjælp? Kontakt Ret til Familie teamet:', 'sv' => '👨‍⚖️ Behöver du professionell hjälp? Kontakta Rätt till Familj teamet:', 'en' => '👨‍⚖️ Need professional help? Contact Right to Family team:'],
        'conflict_mediation' => ['da' => 'Konflikt mægling', 'sv' => 'Konfliktmedling', 'en' => 'Conflict Mediation'],
        'party_representation' => ['da' => 'Partsrepræsentation', 'sv' => 'Partsrepresentation', 'en' => 'Party Representation'],
        'case_review' => ['da' => 'Sagsgennemgang', 'sv' => 'Ärendegranskning', 'en' => 'Case Review'],
        'not_legal_advice' => ['da' => 'Dette er ikke advokat hjælp', 'sv' => 'Detta är inte juridisk rådgivning', 'en' => 'This is not legal advice'],
        
        // Documentation Tips
        'documentation_importance' => ['da' => '📝 Dokumenter ALT i din sag:', 'sv' => '📝 Dokumentera ALLT i ditt ärende:', 'en' => '📝 Document EVERYTHING in your case:'],
        'tip_record_meetings' => ['da' => '🎙️ Optag alle møder (skjult hvis nødvendigt) - det er lovligt i Danmark og Sverige', 'sv' => '🎙️ Spela in alla möten (dolt om nödvändigt) - det är lagligt i Danmark och Sverige', 'en' => '🎙️ Record all meetings (hidden if necessary) - it is legal in Denmark and Sweden'],
        'tip_transcribe' => ['da' => '📄 Få lavet notatudtag af alle optagelser - kan bruges som bevis i retten', 'sv' => '📄 Få transkriberingar av alla inspelningar - kan användas som bevis i rätten', 'en' => '📄 Get transcriptions of all recordings - can be used as evidence in court'],
        'tip_save_emails' => ['da' => '📧 Gem alle emails, SMS\'er og beskeder', 'sv' => '📧 Spara alla e-postmeddelanden, SMS och meddelanden', 'en' => '📧 Save all emails, SMS and messages'],
        'tip_take_photos' => ['da' => '📸 Tag billeder af alle relevante dokumenter', 'sv' => '📸 Ta bilder av alla relevanta dokument', 'en' => '📸 Take photos of all relevant documents'],
        'tip_keep_diary' => ['da' => '📔 Før dagbog over alle hændelser med dato og tid', 'sv' => '📔 För dagbok över alla händelser med datum och tid', 'en' => '📔 Keep a diary of all events with date and time'],
        'tip_witnesses' => ['da' => '👥 Få vidner til at bekræfte vigtige hændelser', 'sv' => '👥 Få vittnen att bekräfta viktiga händelser', 'en' => '👥 Get witnesses to confirm important events'],
        'tip_official_requests' => ['da' => '📨 Send altid officielle anmodninger skriftligt med kvittering', 'sv' => '📨 Skicka alltid officiella förfrågningar skriftligt med kvitto', 'en' => '📨 Always send official requests in writing with receipt'],
        
        // Kate AI Extended
        'kate_can_help_with' => ['da' => 'Kate kan hjælpe dig med:', 'sv' => 'Kate kan hjälpa dig med:', 'en' => 'Kate can help you with:'],
        'analyze_documents' => ['da' => 'Analysere juridiske dokumenter', 'sv' => 'Analysera juridiska dokument', 'en' => 'Analyze legal documents'],
        'explain_laws' => ['da' => 'Forklare love og paragraffer', 'sv' => 'Förklara lagar och paragrafer', 'en' => 'Explain laws and paragraphs'],
        'generate_letters' => ['da' => 'Generere breve og skrivelser', 'sv' => 'Generera brev och skrivelser', 'en' => 'Generate letters and documents'],
        'case_strategy' => ['da' => 'Rådgive om sagsstrategi', 'sv' => 'Ge råd om ärendestrategi', 'en' => 'Advise on case strategy'],
        'deadline_tracking' => ['da' => 'Holde styr på frister', 'sv' => 'Hålla koll på tidsfrister', 'en' => 'Track deadlines'],
    ];
    
    if (isset($translations[$key]) && isset($translations[$key][$lang])) {
        return $translations[$key][$lang];
    }
    
    // Fallback to Danish if translation not found
    return $translations[$key]['da'] ?? $key;
}

/**
 * Get all translations for a specific language
 */
function rtf_get_all_translations($lang = 'da') {
    $keys = [
        'platform', 'profile', 'wall', 'images', 'documents', 'friends', 'forum', 
        'news', 'case_help', 'kate_ai', 'settings', 'chat', 'reports', 'admin',
        'send', 'save', 'cancel', 'delete', 'edit', 'upload', 'download', 'share', 
        'search', 'filter', 'logout', 'ask_kate', 'kate_greeting', 'kate_intro',
        'my_profile', 'full_name', 'email', 'phone', 'birthday', 'bio', 'language',
        'subscription', 'new_message', 'reports_analyses', 'country', 'city', 
        'case_type', 'report_type', 'legal', 'psychological', 'social', 'error', 
        'success', 'loading', 'no_results', 'privacy', 'terms', 'gdpr_notice',
        'pricing_note'
    ];
    
    $result = [];
    foreach ($keys as $key) {
        $result[$key] = rtf_translate($key, $lang);
    }
    
    return $result;
}
