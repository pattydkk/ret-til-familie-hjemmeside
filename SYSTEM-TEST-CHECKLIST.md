# RTF Platform - Komplet System Test Tjekliste
**Dato**: 2. december 2025  
**Version**: 2.0 (Efter komplet redesign)

## ✅ = Testet og virker | ⚠️ = Kræver test | ❌ = Ikke testet | 🔧 = Kræver fix

---

## 🔐 AUTENTIFIKATION & ADGANGSKONTROL

### Login System
- [ ] Login side vises korrekt (`/platform-auth/`)
- [ ] Login med korrekt email + password virker
- [ ] Login med forkert credentials giver fejlbesked
- [ ] `rtf_is_logged_in()` returnerer `true` efter login
- [ ] `rtf_get_current_user()` returnerer korrekt bruger-objekt
- [ ] Session gemmes korrekt efter login
- [ ] Redirect til `/platform-profil/` efter succesfuldt login

### Registrering
- [ ] Registreringsformular vises korrekt
- [ ] Nye brugere kan oprettes med: navn, email, password, land (DK/SE)
- [ ] Brugere gemmes i `wp_rtf_platform_users` tabellen
- [ ] Email-validering fungerer
- [ ] Password hashes korrekt med `password_hash()`
- [ ] Automatisk login efter registrering
- [ ] Dubletter forhindres (samme email kan ikke bruges 2x)

### Adgangskontrol
- [ ] Ikke-logged-in brugere redirectes til `/platform-auth/`
- [ ] Logged-in brugere har adgang til alle platform-sider
- [ ] Admin-brugere (`is_admin = 1`) kan se admin-panel
- [ ] Ikke-admins kan IKKE se admin-panel
- [ ] Suspenderede brugere (`is_suspended = 1`) får adgang nægtet

---

## 👤 PROFIL & BRUGERSTYRING

### Min Profil (`/platform-profil/`)
- [ ] Profil-side vises med brugerens data
- [ ] Profilbillede kan uploades
- [ ] Navn kan redigeres
- [ ] Email kan redigeres
- [ ] Bio/beskrivelse kan redigeres
- [ ] Land (DK/SE) vises korrekt
- [ ] Sprog-præference vises korrekt
- [ ] Ændringer gemmes korrekt i database
- [ ] `last_active` opdateres ved login/aktivitet

### Indstillinger (`/platform-indstillinger/`)
- [ ] Sprog kan skiftes (Dansk/Svensk/Engelsk)
- [ ] Sprog-præference gemmes i `language_preference`
- [ ] Privatliv-indstillinger kan ændres
- [ ] `is_public_profile` kan toggles (synlig/skjult profil)
- [ ] Notifikations-indstillinger fungerer
- [ ] Password kan ændres
- [ ] Email-præferencer gemmes

---

## 📰 INDHOLD & KOMMUNIKATION

### Væg/Feed (`/platform-vaeg/`)
- [ ] Posts vises i omvendt kronologisk rækkefølge
- [ ] Nye posts kan oprettes
- [ ] Posts gemmes i `wp_rtf_platform_posts`
- [ ] Posts viser bruger-navn og timestamp
- [ ] Like-funktion virker
- [ ] Kommentarer kan tilføjes til posts
- [ ] Kommentarer gemmes i `wp_rtf_platform_comments`
- [ ] Kommentarer viser korrekt forfatter
- [ ] Infinite scroll/paginering virker

### Beskeder/Chat (`/platform-chat/`)
- [ ] Chat-liste viser samtaler
- [ ] Ulæste beskeder vises med badge i sidebar
- [ ] Nye beskeder kan sendes
- [ ] Beskeder gemmes i `wp_rtf_platform_messages`
- [ ] Real-time opdatering af beskeder (eller refresh)
- [ ] Besked-historik vises korrekt
- [ ] Søg efter brugere til at starte ny samtale
- [ ] Ulæste tæller opdateres korrekt

### Venner (`/platform-venner/`)
- [ ] Venneliste vises
- [ ] Venneanmodninger kan sendes
- [ ] Anmodninger gemmes i `wp_rtf_platform_friendships`
- [ ] Indgående venneanmodninger vises
- [ ] Anmodninger kan accepteres
- [ ] Anmodninger kan afvises
- [ ] Accepterede venskaber vises i liste
- [ ] Venner kan fjernes

### Find Borgere (`/platform-find-borgere/`)
- [ ] Søgeside vises
- [ ] Brugere med `is_public_profile = 1` vises
- [ ] Søgning på navn virker
- [ ] Filter efter land (DK/SE) virker
- [ ] Filter efter sagstype virker
- [ ] Resultater vises som kort
- [ ] "Send venneanmodning"-knap virker
- [ ] Profiler kan åbnes

### Nyheder (`/platform-nyheder/`)
- [ ] Nyheder vises fra `wp_rtf_platform_news`
- [ ] Land-filter virker (DK/SE/begge)
- [ ] Kun relevante nyheder vises baseret på brugerens land
- [ ] Nyheder vises med titel, indhold, dato
- [ ] Paginering/infinite scroll virker

### Forum (`/platform-forum/`)
- [ ] Forum-topics vises
- [ ] Kun DK og SE vises i land-dropdown (IKKE Norge) ✅
- [ ] Nye topics kan oprettes
- [ ] Topics gemmes i database
- [ ] Filter efter land virker (DK/SE)
- [ ] Filter efter sagstype virker
- [ ] Kommentarer/svar kan tilføjes til topics
- [ ] Svar vises korrekt under topics

---

## 🤖 KATE AI & SAGSHJÆLP

### Kate AI (`/platform-kate-ai/`)
- [ ] Chat-interface vises
- [ ] Beskeder kan sendes til Kate AI
- [ ] API endpoint `/wp-json/kate/v1/chat` virker
- [ ] OpenAI API integration virker
- [ ] Svar vises korrekt i chat
- [ ] Chat-historik gemmes
- [ ] Multiple samtaler kan oprettes
- [ ] Tidligere samtaler kan genåbnes
- [ ] "Barnets Lov" søgning virker
- [ ] Lovparagraffer kan søges

### Sagshjælp (`/platform-sagshjaelp/`) ✅ NY VERSION
- [ ] **6 kategorier** vises korrekt:
  - [ ] 👨‍👩‍👧‍👦 Familie & Børn
  - [ ] 💼 Jobcenter & Kontanthjælp
  - [ ] ♿ Handicap & Særlig Støtte
  - [ ] 👴 Ældre & Pleje
  - [ ] 🏠 Bolig & Udsættelse
  - [ ] 💰 Økonomi & Gæld
- [ ] **5 tabs** fungerer:
  - [ ] Oversigt-tab vises med info
  - [ ] Lav Klage-tab vises
  - [ ] Mine Dokumenter-tab vises
  - [ ] Mine Sager-tab vises
  - [ ] Juridisk Guide-tab vises
- [ ] Kategorikort kan klikkes
- [ ] Valgt kategori markeres (blå border)
- [ ] Klage-generator formular virker:
  - [ ] Sagskategori vælges fra dropdown
  - [ ] Myndighed kan indtastes
  - [ ] Journalnummer kan indtastes
  - [ ] Afgørelsesdato kan vælges
  - [ ] Klagetekst kan skrives (textarea)
  - [ ] Dokumenter kan vedhæftes
- [ ] Kate AI genererer professionel klage
- [ ] Genereret klage vises i output-felt
- [ ] "Kopier til udklipsholder" virker
- [ ] "Download som PDF" virker
- [ ] Mine sager vises fra `wp_rtf_platform_cases`

---

## 📄 DOKUMENTER & FILER

### Dokumenter (`/platform-dokumenter/`)
- [ ] Upload-formular vises
- [ ] Filer kan uploades
- [ ] Uploads gemmes i `wp-content/uploads/`
- [ ] Metadata gemmes i `wp_rtf_platform_documents`
- [ ] Dokumenttype kan vælges (Afgørelse, Klage, etc.)
- [ ] Uploadede dokumenter vises i liste
- [ ] Dokumenter kan downloades
- [ ] Dokumenter kan slettes
- [ ] Filstørrelse-begrænsning virker (max 10MB)

### Billeder (`/platform-billeder/`)
- [ ] Billede-upload virker
- [ ] Billeder gemmes i `wp-content/uploads/images/`
- [ ] Metadata gemmes i `wp_rtf_platform_images`
- [ ] Billeder vises i grid
- [ ] Thumbnail-generering virker
- [ ] Billedtekst/beskrivelse kan tilføjes
- [ ] Billeder kan slettes
- [ ] Galleri-visning virker (lightbox?)

---

## 🛡️ ADMIN PANEL (`/platform-admin-dashboard/`) ✅ NY VERSION

### Adgang
- [ ] Kun brugere med `is_admin = 1` har adgang
- [ ] Ikke-admins redirectes til `/platform-profil/`
- [ ] Admin-link vises kun i sidebar for admins

### 📊 Statistik-sektion
- [ ] Total brugere vises korrekt
- [ ] Aktive brugere (7 dage) beregnes korrekt
- [ ] Total posts tælles korrekt
- [ ] Total beskeder tælles korrekt
- [ ] Kate AI sessioner tælles
- [ ] Statistik opdateres real-time
- [ ] Platform sundhed vises

### 👥 Brugerstyring-sektion
- [ ] Alle brugere vises i tabel
- [ ] Søg efter bruger (navn/email/ID) virker
- [ ] Filter efter status (aktiv/suspenderet/admin) virker
- [ ] Filter efter land (DK/SE) virker
- [ ] "✏️ Rediger"-knap virker:
  - [ ] Navn kan ændres
  - [ ] Ændringer gemmes i database
- [ ] "⏸️ Suspender"-knap virker:
  - [ ] `is_suspended` sættes til 1
  - [ ] Bruger kan ikke logge ind
- [ ] "✓ Aktiver"-knap virker (undo suspender)
- [ ] "🗑️ Slet"-knap virker:
  - [ ] Bekræftelsesdialog vises
  - [ ] Bruger slettes permanent fra database
- [ ] Admins kan IKKE suspenderes/slettes af andre admins

### 🛡️ Indholdsmoderation-sektion
- [ ] Posts vises i liste
- [ ] Filter efter indholdstype (posts/kommentarer/billeder) virker
- [ ] Sorter efter (nyeste/ældste/rapporterede) virker
- [ ] "🗑️ Slet"-knap virker:
  - [ ] Post slettes fra `wp_rtf_platform_posts`
  - [ ] Tilhørende kommentarer slettes
- [ ] Rapporterede posts markeres
- [ ] Paginering virker

### 📰 Nyhedsstyring-sektion
- [ ] Nyhedsformular vises
- [ ] Nye nyheder kan oprettes:
  - [ ] Titel kan indtastes
  - [ ] Indhold kan skrives
  - [ ] Land kan vælges (DK/SE/Begge)
- [ ] Nyheder gemmes i `wp_rtf_platform_news`
- [ ] Seneste nyheder vises i liste
- [ ] "🗑️ Slet"-knap virker
- [ ] Nyheder vises med land-badge
- [ ] Timestamp vises korrekt

### ⚙️ Systemindstillinger-sektion
- [ ] WordPress version vises
- [ ] PHP version vises
- [ ] MySQL version vises
- [ ] Databasetabeller tjekkes:
  - [ ] `rtf_platform_users` ✅/✗
  - [ ] `rtf_platform_posts` ✅/✗
  - [ ] `rtf_platform_messages` ✅/✗
  - [ ] `rtf_platform_news` ✅/✗
  - [ ] `rtf_platform_documents` ✅/✗
  - [ ] `rtf_foster_care_stats` ✅/✗
- [ ] Manglende tabeller markeres tydeligt

### 📋 Logfiler-sektion
- [ ] Systemlog vises
- [ ] Kritiske fejl vises
- [ ] Brugeraktivitet logges
- [ ] Sikkerhedshændelser vises
- [ ] Sidste tjek-timestamp vises

---

## 📊 STATISTIK & DATA

### Fosterbørn-tæller (Footer på forside)
- [ ] Tæller vises på forsiden
- [ ] Danmark-data vises: **11.247 børn**
- [ ] Sverige-data vises: **24.685 børn**
- [ ] Confidence badges vises (98.5% DK, 98.2% SE)
- [ ] Kilder-links virker (Ankestyrelsen, Socialstyrelsen)
- [ ] Flag-emojis vises (🇩🇰 🇸🇪)
- [ ] Animeret tæller virker (count-up effect)
- [ ] Data opdateres hver time via cron
- [ ] Data hentes fra `wp_rtf_foster_care_stats` tabel
- [ ] API endpoint `/wp-json/kate/v1/foster-care-stats` virker
- [ ] JavaScript refresh hver 5. minut virker

### Database Initialization
- [ ] `rtf_init_foster_care_stats()` kører ved theme activation
- [ ] Data indsættes korrekt i tabel
- [ ] Tabel-existence check virker
- [ ] Dual hooks virker (`after_setup_theme` + `init`)
- [ ] Error log bekræfter initialization
- [ ] Admin force-init endpoint virker: POST `/wp-json/kate/v1/foster-care-stats/init`

---

## 🌍 SPROG & INTERNATIONALISERING

### Sprogvalg
- [ ] Dansk (DA) kan vælges ✅
- [ ] Svensk (SV) kan vælges ✅
- [ ] Engelsk (EN) kan vælges ✅
- [ ] Sprog-vælger vises i **header.php** ✅
- [ ] Sprog-vælger vises i **platform-sidebar** ✅ NY
- [ ] `?lang=` parameter gemmes i URLs
- [ ] `rtf_get_lang()` returnerer korrekt sprog
- [ ] Sprog gemmes i session/cookie
- [ ] Sprog-præference gemmes i bruger-profil

### Oversættelser
- [ ] Alle platform-sider oversættes til DA/SV/EN
- [ ] Sagshjælp viser korrekt sprog
- [ ] Admin panel viser korrekt sprog
- [ ] Kate AI forstår dansk og svensk
- [ ] Nyheder vises på korrekt sprog
- [ ] Forum viser korrekt sprog
- [ ] Fejlbeskeder oversættes

---

## 🔗 REST API ENDPOINTS

### Bruger Endpoints
- [ ] `GET /wp-json/kate/v1/user/profile` - Hent profil
- [ ] `POST /wp-json/kate/v1/user/update` - Opdater profil
- [ ] `POST /wp-json/kate/v1/auth/login` - Login
- [ ] `POST /wp-json/kate/v1/auth/register` - Registrer
- [ ] `POST /wp-json/kate/v1/auth/logout` - Logout

### Content Endpoints
- [ ] `GET /wp-json/kate/v1/posts` - Hent posts
- [ ] `POST /wp-json/kate/v1/posts` - Opret post
- [ ] `DELETE /wp-json/kate/v1/posts/{id}` - Slet post
- [ ] `GET /wp-json/kate/v1/messages` - Hent beskeder
- [ ] `POST /wp-json/kate/v1/messages` - Send besked
- [ ] `GET /wp-json/kate/v1/messages/unread-count` - Ulæste beskeder

### Kate AI Endpoints
- [ ] `POST /wp-json/kate/v1/chat` - Chat med Kate AI
- [ ] `POST /wp-json/kate/v1/search-barnets-lov` - Søg i Barnets Lov
- [ ] `POST /wp-json/kate/v1/explain-law` - Forklar lovparagraf
- [ ] `POST /wp-json/kate/v1/guidance` - Juridisk vejledning

### Admin Endpoints
- [ ] `GET /wp-json/kate/v1/admin/analytics` - Admin statistik
- [ ] `POST /wp-json/kate/v1/admin/news` - Opret nyhed
- [ ] `DELETE /wp-json/kate/v1/admin/news/{id}` - Slet nyhed
- [ ] `PUT /wp-json/kate/v1/admin/users/{id}` - Rediger bruger
- [ ] `POST /wp-json/kate/v1/admin/users/{id}/suspend` - Suspender bruger
- [ ] `DELETE /wp-json/kate/v1/admin/users/{id}` - Slet bruger
- [ ] `DELETE /wp-json/kate/v1/admin/posts/{id}` - Slet post

### Statistik Endpoints
- [ ] `GET /wp-json/kate/v1/foster-care-stats` - Fosterbørn statistik
- [ ] `POST /wp-json/kate/v1/foster-care-stats/init` - Force init (admin only)

---

## 🔧 TEKNISK VALIDERING

### Database
- [ ] Alle tabeller eksisterer
- [ ] Foreign keys er sat korrekt
- [ ] Indexes er optimeret
- [ ] Data-typer er korrekte
- [ ] UTF-8 encoding virker
- [ ] Transactions bruges hvor nødvendigt

### Sikkerhed
- [ ] SQL injection forhindres (prepared statements) ✅
- [ ] XSS forhindres (`esc_html()`, `esc_url()`) ✅
- [ ] CSRF protection (nonces) ✅
- [ ] Password hashing (`password_hash()`) ✅
- [ ] Session hijacking forhindres
- [ ] File upload validering virker
- [ ] Admin-actions kræver admin-rolle ✅

### Performance
- [ ] Database queries optimeret
- [ ] Caching implementeret hvor muligt
- [ ] Images komprimeres
- [ ] CSS/JS minificeres
- [ ] Lazy loading virker
- [ ] Pagination/infinite scroll implementeret

### Browser Compatibility
- [ ] Chrome ✅
- [ ] Firefox ✅
- [ ] Safari ✅
- [ ] Edge ✅
- [ ] Mobile Chrome ✅
- [ ] Mobile Safari ✅

### Responsive Design
- [ ] Desktop (1920px+) ✅
- [ ] Laptop (1366px) ✅
- [ ] Tablet (768px) ✅
- [ ] Mobile (375px) ✅
- [ ] Touch gestures virker på mobile

---

## 🚀 GIT & DEPLOYMENT

### Version Control
- [ ] Alle ændringer committed til git ✅
- [ ] Commit messages er beskrivende ✅
- [ ] Branch: `main` er opdateret ✅
- [ ] Ingen konflikter i repository
- [ ] `.gitignore` konfigureret korrekt
- [ ] Seneste commit: `2d532fe` ✅

### GitHub Status
- [ ] Repository: `ret-til-familie-hjemmeside` ✅
- [ ] Owner: `pattydkk` ✅
- [ ] Pushed til remote: ✅
- [ ] Backup-filer ikke uploaded til git
- [ ] Seneste push-dato: 2. december 2025 ✅

---

## 📝 PRIORITERET FEJL-FIX LISTE

### KRITISKE (må fikses NU)
1. [ ] Test fosterbørn-tæller viser tal (11.247 DK, 24.685 SE)
2. [ ] Test admin panel virker med alle funktioner
3. [ ] Test sagshjælp viser alle 6 kategorier korrekt

### HØJT PRIORITERET
4. [ ] Test "Find Borgere" side virker (brugerdata vises)
5. [ ] Test bruger-registrering virker end-to-end
6. [ ] Test login/logout flow virker perfekt

### MEDIUM PRIORITERET
7. [ ] Test Kate AI chat virker med OpenAI
8. [ ] Test klage-generator i sagshjælp
9. [ ] Test dokument-upload og download

### LAV PRIORITERET
10. [ ] Optimering af database queries
11. [ ] CSS/JS minificering
12. [ ] Mobile touch-gesture forbedringer

---

## ✅ AFSLUTTENDE VALIDERING

Når ALLE ovenstående punkter er testet og virker:

- [ ] **Fuld manual test** af hele platformen gennemført
- [ ] **Admin panel** testet af admin-bruger
- [ ] **Bruger-registrering til login** testet end-to-end
- [ ] **Kate AI** testet med reelle spørgsmål
- [ ] **Sagshjælp** testet med alle 6 kategorier
- [ ] **Sprog-skift** testet på alle sider (DA/SV/EN)
- [ ] **Mobile responsiveness** bekræftet
- [ ] **Performance** målt og godkendt
- [ ] **Sikkerhed** valideret (SQL injection, XSS tests)
- [ ] **Git status** verificeret (alt committed og pushed)

---

## 📞 SUPPORT KONTAKT

Ved spørgsmål eller problemer:
- **Email**: support@retttilfamilie.dk
- **GitHub Issues**: https://github.com/pattydkk/ret-til-familie-hjemmeside/issues

---

**Sidst opdateret**: 2. december 2025, 14:30 CET  
**Test udført af**: Systemadministrator  
**Version**: 2.0 (Efter komplet redesign)
