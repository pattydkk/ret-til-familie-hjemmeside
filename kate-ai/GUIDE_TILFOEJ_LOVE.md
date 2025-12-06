# Guide: Sådan tilføjer du flere love og intents til Kate

## 📚 TILFØJ NYE LOVE OG PARAGRAFFER

### Trin 1: Tilføj intent til `intents.json`

Åbn filen: `kate-ai/data/intents.json`

Tilføj ny intent i JSON-format:

```json
{
  "intent_id": "BARNETS_LOV_PARAGRAF_83",
  "title": "Barnets Lov § 83 - Samvær og kontakt",
  "keywords": ["samvær", "kontakt", "§ 83", "besøg", "samværsret"],
  "regex": ["samvær", "§\\s*83", "kontakt.*barn"],
  "topic": "barnets_lov",
  "answer_type": "what",
  "law_refs": [
    {
      "law_id": "barnets_lov",
      "law": "Barnets Lov",
      "paragraph": "§ 83",
      "note": "Samvær og kontakt under anbringelse",
      "url": "https://www.retsinformation.dk/eli/lta/2022/1146"
    }
  ],
  "answer_short": "§ 83 handler om barnets ret til samvær og kontakt med forældre under anbringelse. Kommunen skal understøtte kontakten medmindre det er til skade for barnet.",
  "answer_long": [
    "BARNETS LOV § 83 - SAMVÆR OG KONTAKT:",
    "",
    "HOVEDPRINCIP:",
    "Barnet har ret til samvær og kontakt med sine forældre, også under anbringelse.",
    "",
    "KOMMUNENS PLIGT:",
    "✓ Understøtte og fremme kontakten",
    "✓ Tilrettelægge samvær der passer til barnets behov",
    "✓ Tage hensyn til forældrenes muligheder",
    "",
    "FORMER FOR KONTAKT:",
    "• Fysisk samvær (besøg)",
    "• Telefonisk kontakt",
    "• Videokald",
    "• Breve og beskeder",
    "",
    "BEGRÆNSNINGER:",
    "Samvær kan kun begrænses hvis:",
    "• Det er til alvorlig skade for barnet",
    "• Det modarbejder formålet med anbringelsen",
    "• Der er konkret begrundelse",
    "",
    "DINE RETTIGHEDER:",
    "• Du har ret til at få fastsat samvær",
    "• Du kan klage hvis samvær begrænses",
    "• Du kan anmode om ændring af samværsafgørelse",
    "",
    "OVERVÅGET SAMVÆR:",
    "Hvis samvær skal overvåges:",
    "• Skal være begrundet i barnets behov",
    "• Skal være mindst indgribende",
    "• Kan klages til Ankestyrelsen"
  ],
  "follow_up_questions": [
    "Har du samvær med dit barn?",
    "Er samværet blevet begrænset?",
    "Ønsker du at klage over samværsafgørelsen?",
    "Vil du vide mere om overvåget samvær?"
  ],
  "external_links": [
    {
      "title": "Læs § 83 på Retsinformation.dk",
      "url": "https://www.retsinformation.dk/eli/lta/2022/1146"
    }
  ],
  "quick_actions": [
    {
      "label": "Klag over samværsbegrænsning",
      "intent_trigger": "KLAGE_OVER_AFGOERELSE"
    }
  ]
}
```

### Trin 2: Tilføj stavefejls-varianter

Åbn filen: `kate-ai/src/Core/SpellingCorrector.php`

Tilføj til `$commonMisspellings` array:

```php
private $commonMisspellings = [
    // ... eksisterende ...
    'samvær' => ['samvaer', 'sammvær', 'samvær'],
    'overvåget' => ['overvaget', 'overvåget', 'over våget'],
];
```

### Trin 3: Test din nye intent

Kør test:
```bash
php kate-ai/test_kate_v2.php
```

Eller test direkte i chat:
```
"Hvad siger § 83?"
"Må kommunen begrænse mit samvær?"
"overvaget samvaer" ← Test stavefejl
```

---

## 🇸🇪 TILFØJ SVENSKE LOVE

### Trin 1: Tilføj til `intents_se.json`

Opret eller rediger: `kate-ai/data/intents_se.json`

```json
[
  {
    "intent_id": "SOCIALTJANSTLAGEN_KAPITEL_5",
    "title": "Socialtjänstlagen 5 kap - Barn och unga",
    "keywords": ["socialtjänstlagen", "5 kap", "barn och unga", "sol"],
    "regex": ["social.*tjänst", "5\\s*kap"],
    "topic": "socialtjanstlagen",
    "answer_type": "what",
    "law_refs": [
      {
        "law_id": "socialtjanstlagen",
        "law": "Socialtjänstlagen",
        "paragraph": "5 kap",
        "url": "https://www.riksdagen.se/sv/dokument-lagar/dokument/svensk-forfattningssamling/socialtjanstlag-2001453_sfs-2001-453"
      }
    ],
    "answer_short": "Socialtjänstlagen 5 kap handlar om särskilt stöd och skydd för barn och unga.",
    "answer_long": [
      "SOCIALTJÄNSTLAGEN 5 KAP:",
      "",
      "SYFTE:",
      "Skydda barn och unga som far illa eller riskerar att fara illa.",
      "",
      "SOCIALNÄMNDENS ANSVAR:",
      "✓ Bevaka barn och ungas behov",
      "✓ Utreda misstankar om att barn far illa",
      "✓ Erbjuda stöd till familjen",
      "✓ Vid behov ansöka om tvångsvård (LVU)",
      "",
      "ÅTGÄRDER:",
      "• Öppenvårdsinsatser",
      "• Familjehemsplacering (frivilligt)",
      "• Tvångsvård enligt LVU",
      "• Kontaktperson/familj",
      "",
      "DINA RÄTTIGHETER:",
      "• Du har rätt att överklaga beslut",
      "• Du har rätt till god man",
      "• Du har rätt att få tolk",
      "",
      "Vill du veta mer om en specifik paragraf?"
    ]
  }
]
```

---

## ⚖️ TILFØJ NYE JURIDISKE OMRÅDER

### Eksempel: Fogedretten

```json
{
  "intent_id": "FOGEDRET_UDSOEGNING",
  "title": "Fogedret - Udsøgning og tvangsfuldbyrdelse",
  "keywords": ["foged", "udsøgning", "tvangsfuldbyrdelse", "inkasso"],
  "regex": ["foged", "udsøg", "tvang.*fuldbyr"],
  "topic": "fogedret",
  "answer_type": "what",
  "law_refs": [
    {
      "law_id": "retsplejeloven",
      "law": "Retsplejeloven",
      "paragraph": "Kapitel 45-57",
      "url": "https://www.retsinformation.dk/eli/lta/2023/1835"
    }
  ],
  "answer_short": "Fogedretten håndterer udsøgning og tvangsfuldbyrdelse af gæld. Du har rettigheder også som skyldner.",
  "answer_long": [
    "FOGEDRET OG UDSØGNING:",
    "",
    "HVAD ER FOGEDRETTEN:",
    "Fogedretten gennemfører tvangsfuldbyrdelse når du har ubetalte gældskrav.",
    "",
    "HVAD KAN FOGEDEN GØRE:",
    "• Foretage udlæg i dine ejendele",
    "• Foretage lønindeholdelse",
    "• I sjældne tilfælde foretage husundersøgelse",
    "",
    "DINE RETTIGHEDER:",
    "✓ Du skal varsles før fogedforretning",
    "✓ Du må beholde nødvendige ting (møbler, tøj, osv.)",
    "✓ Der er grænser for hvor meget der må tages fra din løn",
    "✓ Du kan søge om henstand eller eftergivelse",
    "",
    "BESKYTTEDE INDTÆGTER:",
    "• Børne- og ungeydelse",
    "• Boligsikring",
    "• SU (studielån kan dog udsøges)",
    "",
    "Har du brug for hjælp med en fogedsag?"
  ],
  "follow_up_questions": [
    "Har du modtaget varsel om fogedforretning?",
    "Vil du søge om henstand?",
    "Er der blevet taget ting du skal bruge?"
  ]
}
```

---

## 💬 TILFØJ FLERE CASUAL RESPONSES

Åbn: `kate-ai/src/Core/ConversationalModule.php`

Tilføj til arrays:

```php
private $greetings = [
    'da' => [
        'patterns' => [
            'hej', 'goddag', 'god morgen', 
            // TILFØJ HER:
            'yo', 'sup', 'hvad så'
        ],
        'responses' => [
            'Hej! Dejligt at høre fra dig.',
            // TILFØJ HER:
            'Yo! Hvad kan jeg gøre for dig?',
            'Hey! Hvad er der på hjerte?'
        ]
    ]
];
```

---

## 🔍 TILFØJ FLERE STAVEFEJLS-VARIANTER

### Almindelige fejl:

```php
private $commonMisspellings = [
    // Juridiske termer
    'anbringelse' => ['anbringels', 'ambringelse', 'anbringlse'],
    
    // TILFØJ NYE:
    'handleplan' => ['handlesplan', 'handle plan', 'handelplan'],
    'børnefaglig' => ['bornefaglig', 'børne faglig', 'børnfaglig'],
    'undersøgelse' => ['undersøgels', 'undersögelse', 'undersögels'],
];
```

---

## 🎯 TEST DIN TILFØJELSE

### 1. Valider JSON syntax:

```bash
php -l kate-ai/data/intents.json
```

### 2. Test spelling:

```php
$corrector = new SpellingCorrector();
$corrected = $corrector->correct('anbringels');
echo $corrected; // Should output: anbringelse
```

### 3. Test intent detection:

I chat interface:
```
"Hvad siger § 83?" ← Skal matche din nye intent
"samvaer med barn" ← Test stavefejl
```

---

## 📝 BEST PRACTICES

### DO:
✅ Brug klare, beskrivende `intent_id`
✅ Tilføj mange `keywords` varianter
✅ Brug `regex` til komplekse patterns
✅ Skriv `answer_short` som 1-2 sætninger
✅ Strukturer `answer_long` med bullets og overskrifter
✅ Tilføj altid `law_refs` med URL
✅ Tilføj `follow_up_questions` for bedre flow

### DON'T:
❌ Genbruge eksisterende `intent_id`
❌ Lave for lange `answer_short`
❌ Glemme at tilføje stavefejls-varianter
❌ Udelade `law_refs`
❌ Skrive uden struktur i `answer_long`

---

## 🔄 EFTER TILFØJELSE

1. **Genstart serveren** hvis den kører
2. **Clear cache** i browseren
3. **Test grundigt** med forskellige formuleringer
4. **Check logs** for fejl
5. **Opdater dokumentation**

---

## 📞 HJÆLP

Hvis noget ikke virker:
1. Check JSON syntax (brug validator)
2. Check PHP logs for fejl
3. Test stavefejls-korrekteren isoleret
4. Verificer intent matcher i test_kate_v2.php

---

**God fornøjelse med at udvide Kate! 🚀**
