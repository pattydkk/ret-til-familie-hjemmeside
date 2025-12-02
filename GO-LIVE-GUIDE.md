# 🚀 GO LIVE GUIDE - RET TIL FAMILIE PLATFORM

**Status:** ✅ KLAR TIL DEPLOYMENT  
**Dato:** 2. december 2025  
**Version:** 2.0.1

---

## ✅ PRE-FLIGHT CHECK

### Core Files
- ✅ `functions.php` - Alle funktioner inkluderet (1258 linjer)
- ✅ `header.php` - Menu med borgerplatform
- ✅ `footer.php` - Footer layout
- ✅ `style.css` - Theme metadata
- ✅ `index.php` - Fallback template
- ✅ `page.php` - Standard page template
- ✅ `rtf-setup.php` - Setup wizard side

### Info Pages (7 templates)
- ✅ `forside` (via index.php)
- ✅ `om-os` (via page.php)
- ✅ `ydelser` (via page.php)
- ✅ `kontakt` (via page.php)
- ✅ `akademiet` (via page.php)
- ✅ `stoet-os` (via page.php)
- ✅ `borger-platform.php` - Platform landing page

### Platform Pages (17 templates)
- ✅ `platform-auth.php` - Login/Registrering
- ✅ `platform-profil.php` - Bruger profil
- ✅ `platform-subscription.php` - Stripe abonnement
- ✅ `platform-vaeg.php` - Social væg
- ✅ `platform-chat.php` - Private beskeder
- ✅ `platform-billeder.php` - Billede galleri
- ✅ `platform-dokumenter.php` - Dokument manager
- ✅ `platform-indstillinger.php` - Bruger indstillinger
- ✅ `platform-nyheder.php` - Nyheder
- ✅ `platform-forum.php` - Forum
- ✅ `platform-sagshjaelp.php` - Legal help
- ✅ `platform-kate-ai.php` - Kate AI chat
- ✅ `platform-klagegenerator.php` - Complaint generator
- ✅ `platform-admin-dashboard.php` - Admin oversigt
- ✅ `platform-admin-users.php` - User management
- ✅ `platform-venner.php` - Friends system
- ✅ `platform-rapporter.php` - Reports & analytics

### Core Functions
- ✅ `rtf_create_default_pages()` - Opret alle sider
- ✅ `rtf_create_pages_menu_on_switch()` - Menu creation
- ✅ `rtf_create_platform_tables()` - 28 database tables
- ✅ `rtf_create_default_admin()` - Admin user creation
- ✅ `rtf_get_lang()` - Multi-sprog (da/sv/en)
- ✅ `rtf_is_logged_in()` - Session management
- ✅ Kate AI initialization - Med vendor checks

### Vendor Plugin
- ✅ `rtf-vendor-plugin.php` - Plugin hovedfil
- ✅ `github-updater.php` - Auto-update fra GitHub
- ✅ README.md - Installation guide
- ⚠️ `vendor/` folder - Skal uploades separat (340 MB)

---

## 📦 STEP 1: FORBERED FILER

### A) Theme ZIP (Uden Vendor)
```bash
# I theme mappen:
cd "C:\Users\patrick f. hansen\OneDrive\Skrivebord\ret til familie hjemmeside"

# Opret ZIP uden vendor/ (ca. 6 MB)
Compress-Archive -Path * -DestinationPath ..\rtf-theme.zip -Force -Exclude vendor,vendor\*,node_modules,node_modules\*,.git,.git\*
```

### B) Vendor Plugin ZIP (Med Vendor)
```bash
# I rtf-vendor-plugin mappen:
cd "C:\Users\patrick f. hansen\OneDrive\Skrivebord"

# Opret ZIP med vendor/ (ca. 340 MB)
Compress-Archive -Path rtf-vendor-plugin -DestinationPath rtf-vendor-plugin.zip -Force
```

**Resultat:**
- ✅ `rtf-theme.zip` (~6 MB) - Theme uden vendor
- ✅ `rtf-vendor-plugin.zip` (~340 MB) - Plugin med vendor

---

## 🌐 STEP 2: UPLOAD TIL WORDPRESS

### A) Upload Theme

1. **Log ind på WordPress Admin:**
   ```
   https://dit-domæne.dk/wp-admin/
   ```

2. **Slet gammelt tema (hvis det findes):**
   ```
   Udseende → Temaer
   → Find "Ret til Familie"
   → Klik "Tema Detaljer"
   → Klik "Slet"
   ```

3. **Upload nyt tema:**
   ```
   Udseende → Temaer → Tilføj nyt
   → Upload Tema
   → Vælg: rtf-theme.zip
   → Klik "Installer Nu"
   → Vent på upload (30 sekunder)
   → Klik "Aktiver"
   ```

### B) Upload Vendor Plugin

1. **Upload plugin:**
   ```
   Plugins → Tilføj nyt
   → Upload Plugin
   → Vælg: rtf-vendor-plugin.zip (340 MB!)
   → Klik "Installer Nu"
   → VENT 5-10 MINUTTER (stor fil!)
   ```

2. **Aktiver plugin:**
   ```
   → Klik "Aktiver Plugin" efter upload
   ```

3. **Verificer vendor status:**
   ```
   Indstillinger → RTF Vendor
   → Se status for Stripe, mPDF, PHPWord, PDF Parser
   → Skal alle være ✅ grønne
   ```

---

## ⚙️ STEP 3: KØR SETUP

### Auto-Setup Via Wizard

1. **Besøg setup siden:**
   ```
   https://dit-domæne.dk/rtf-setup/
   ```

2. **Klik "KØR SETUP NU" knap**

3. **Setup opretter automatisk:**
   - ✅ 28 database tabeller
   - ✅ 24 WordPress sider
   - ✅ Navigation menu (Topmenu)
   - ✅ Admin bruger (username: admin, password: admin123)
   - ✅ Flush permalinks

4. **Bekræft success:**
   - Se grøn boks: "✅ SETUP GENNEMFØRT!"
   - Check alle punkter er ✅ grønne

---

## 🔍 STEP 4: VERIFICER ALT VIRKER

### Test Info Sider
```
✅ https://dit-domæne.dk/
✅ https://dit-domæne.dk/om-os/
✅ https://dit-domæne.dk/ydelser/
✅ https://dit-domæne.dk/kontakt/
✅ https://dit-domæne.dk/akademiet/
✅ https://dit-domæne.dk/stoet-os/
```

### Test Borgerplatform
```
✅ https://dit-domæne.dk/borger-platform/
   → Skal vise landing page med features
   → Klik "Kom i Gang" → Skal gå til platform-auth

✅ https://dit-domæne.dk/platform-auth/
   → Skal vise login/registrering forms
   → Test registrering af ny bruger
   → Test login med admin/admin123
```

### Test Platform Features (Efter Login)
```
✅ https://dit-domæne.dk/platform-profil/
✅ https://dit-domæne.dk/platform-vaeg/
✅ https://dit-domæne.dk/platform-chat/
✅ https://dit-domæne.dk/platform-dokumenter/
✅ https://dit-domæne.dk/platform-forum/
✅ https://dit-domæne.dk/platform-nyheder/
```

### Test Kate AI (Kræver Vendor Plugin)
```
✅ https://dit-domæne.dk/platform-kate-ai/
   → Skal vise chat interface
   → Test at sende besked
   → Verificer AI svarer (kræver OpenAI API key)
```

### Test Stripe (Kræver Vendor Plugin)
```
✅ https://dit-domæne.dk/platform-subscription/
   → Skal vise abonnement plans
   → Test Stripe checkout flow
   → Verificer betalinger logges i database
```

---

## 🔧 STEP 5: KONFIGURER STRIPE & KATE AI

### A) Stripe Configuration

1. **Få Stripe API keys:**
   ```
   → Log ind på: https://dashboard.stripe.com/
   → Developers → API keys
   → Copy "Publishable key" og "Secret key"
   ```

2. **Tilføj til functions.php:**
   ```php
   // Find linje ~40 i functions.php:
   define('RTF_STRIPE_PUBLIC_KEY', 'pk_live_...');  // Indsæt din key
   define('RTF_STRIPE_SECRET_KEY', 'sk_live_...');  // Indsæt din key
   ```

3. **Upload opdateret functions.php via GitHub (se Step 6)**

### B) Kate AI / OpenAI Configuration

1. **Få OpenAI API key:**
   ```
   → Log ind på: https://platform.openai.com/
   → API Keys → Create new secret key
   → Copy key (gemmes kun én gang!)
   ```

2. **Tilføj til functions.php:**
   ```php
   // Find linje ~38 i functions.php:
   define('RTF_OPENAI_API_KEY', 'sk-proj-...');  // Indsæt din key
   ```

3. **Upload opdateret functions.php via GitHub**

---

## 🔄 STEP 6: LIVE OPDATERINGER VIA GITHUB

### Setup Auto-Update

1. **Theme er allerede konfigureret med GitHub updater**
   - `github-updater.php` inkluderet i theme
   - Tracker: `pattydkk/ret-til-familie-hjemmeside`

2. **Når du laver ændringer lokalt:**
   ```bash
   # I VS Code eller terminal:
   git add .
   git commit -m "Beskrivelse af ændring"
   git push origin main
   ```

3. **Opdater i WordPress:**
   ```
   WordPress Admin → Dashboard → Opdateringer
   → Find "Ret til Familie" tema
   → Klik "Opdater Nu"
   → Theme downloades automatisk fra GitHub
   → Vent på installation
   → Refresh siden
   ```

### Test Live Update

1. **Lav en lille ændring i VS Code:**
   ```php
   // Åbn functions.php og tilføj kommentar:
   // Updated: 2025-12-02 15:30
   ```

2. **Push til GitHub:**
   ```bash
   git add functions.php
   git commit -m "Test update"
   git push origin main
   ```

3. **Check WordPress:**
   ```
   Dashboard → Opdateringer
   → Skulle vise ny opdatering til Ret til Familie
   → Klik "Opdater Nu"
   → Verificer ændring er i functions.php
   ```

---

## 🛡️ STEP 7: SIKKERHED & GDPR

### Ændr Standard Admin Password

1. **Log ind som admin:**
   ```
   Username: admin
   Password: admin123
   ```

2. **Skift password:**
   ```
   Platform Profil → Indstillinger
   → Skift adgangskode til noget sikkert
   ```

### GDPR Compliance

✅ **Allerede implementeret:**
- Fødselsdag anonymiseres til ##-##-ÅÅÅÅ
- Telefonnummer kun synligt for admins
- GDPR notice ved registrering
- Privacy policy accept required

### SSL Certificate

**VIGTIGT:** Sørg for SSL er aktiveret:
```
WordPress Admin → Indstillinger → Generelt
→ WordPress adresse: https://dit-domæne.dk
→ Site adresse: https://dit-domæne.dk
```

---

## 📊 STEP 8: MONITORING & MAINTENANCE

### Check System Health

**Endpoint:**
```
https://dit-domæne.dk/wp-json/rtf/v1/health
```

**Response skal vise:**
```json
{
  "theme_version": "2.0.1",
  "db_version": "2.0.0",
  "kate_ai": true,
  "stripe_configured": true,
  "database_tables": [...],
  "features": {...}
}
```

### WordPress Debug Log

**Aktiver debug logging:**
```php
// I wp-config.php:
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

**Check logs:**
```
/wp-content/debug.log
```

### Database Backup

**Anbefalet:** Daglige backups via:
- WordPress plugin (UpdraftPlus)
- DanDomain backup system
- Manual phpMyAdmin export

---

## 🚨 TROUBLESHOOTING

### Problem: "Intet indhold fundet" på sider

**Løsning:**
```
1. Besøg: https://dit-domæne.dk/rtf-setup/
2. Kør setup igen (opretter manglende sider)
3. Eller: Indstillinger → Permalinks → Gem ændringer
```

### Problem: Kate AI virker ikke

**Check:**
```
1. Vendor plugin aktiveret? (Plugins → Installed Plugins)
2. OpenAI API key sat? (functions.php linje 38)
3. Vendor status: Indstillinger → RTF Vendor (skal være ✅ grøn)
```

### Problem: Stripe virker ikke

**Check:**
```
1. Vendor plugin aktiveret?
2. Stripe API keys sat? (functions.php linje 40-41)
3. Stripe mode: Test eller Live? (brug test keys først)
```

### Problem: Tema opdaterer ikke fra GitHub

**Løsning:**
```
1. Check GitHub repo er PUBLIC
2. Slet WordPress cache:
   wp_options → update_themes → Slet
3. Dashboard → Opdateringer → Tjek igen
```

---

## ✅ GO LIVE CHECKLIST

```
☐ Theme uploaded og aktiveret
☐ Vendor plugin uploaded og aktiveret
☐ Setup kørt via /rtf-setup/
☐ Alle info sider virker
☐ Borgerplatform landing virker
☐ Login/registrering virker
☐ Stripe API keys konfigureret
☐ OpenAI API key konfigureret
☐ Admin password ændret fra standard
☐ SSL aktiveret (HTTPS)
☐ Permalinks flushed
☐ Database backup sat op
☐ Test registrering af bruger
☐ Test platform features
☐ Test Kate AI chat
☐ Test Stripe betalinger (test mode)
☐ Verificer GitHub auto-update virker
```

---

## 📞 SUPPORT

**GitHub Issues:**
https://github.com/pattydkk/ret-til-familie-hjemmeside/issues

**Debug Info:**
```
WordPress Admin → Dashboard → Site Health
→ Info → Copy site info
```

---

## 🎉 DONE!

**Din platform er nu LIVE!** 🚀

Brugere kan:
- ✅ Læse info sider
- ✅ Se borgerplatform features
- ✅ Registrere sig og logge ind
- ✅ Bruge social væg, chat, dokumenter
- ✅ Få hjælp fra Kate AI
- ✅ Generere klager
- ✅ Betale abonnement via Stripe

**Admins kan:**
- ✅ Administrere brugere
- ✅ Moderere indhold
- ✅ Se rapporter og analytics
- ✅ Opdatere tema live fra GitHub
- ✅ Kode ændringer i VS Code → Push → Auto-update

---

**VERSION: 2.0.1**  
**READY FOR PRODUCTION** ✅
