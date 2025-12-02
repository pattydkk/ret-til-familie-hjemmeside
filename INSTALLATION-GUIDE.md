# 🚀 RET TIL FAMILIE BORGER PLATFORM - INSTALLATIONS GUIDE

**Version:** 31.0 LIVE  
**Dato:** 1. december 2025  
**Status:** PRODUKTIONSKLAR

---

## 📋 SYSTEM KRAV

### Server Requirements
- **PHP:** 7.4 eller nyere (8.0+ anbefalet)
- **MySQL:** 5.7+ eller MariaDB 10.3+
- **WordPress:** 6.0+
- **Apache/Nginx** med mod_rewrite aktiveret
- **PHP Extensions:**
  - GD Library (til billedbehandling)
  - MySQLi eller PDO
  - cURL
  - JSON
  - mbstring

### Composer Dependencies
```json
{
  "stripe/stripe-php": "^13.0",
  "phpoffice/phpword": "^1.2",
  "smalot/pdfparser": "^2.7",
  "mpdf/mpdf": "^8.2"
}
```

---

## 📦 TRIN 1: INSTALL COMPOSER DEPENDENCIES

**På din lokale maskine ELLER på serveren:**

```bash
cd "c:\Users\patrick f. hansen\OneDrive\Skrivebord\ret til familie hjemmeside"

# Installer Composer (hvis ikke allerede installeret)
# Download fra: https://getcomposer.org/download/

# Kør composer install
composer install
```

**Dette vil installere:**
- ✅ Stripe PHP SDK (betalinger)
- ✅ PHPWord (DOCX parsing)
- ✅ PDF Parser (PDF læsning)
- ✅ mPDF (PDF generering)

---

## 🔑 TRIN 2: STRIPE OPSÆTNING

### 2.1 Opret Stripe Konto
1. Gå til https://stripe.com
2. Opret gratis konto
3. Verificér email og business detaljer

### 2.2 Hent API Keys
1. Log ind på Stripe Dashboard: https://dashboard.stripe.com
2. Gå til **Developers** → **API keys**
3. Kopiér:
   - **Publishable key** (starter med `pk_live_...`)
   - **Secret key** (starter med `sk_live_...`)

### 2.3 Opret Produkt (49 DKK/måned)
1. Gå til **Products** → **Add product**
2. Udfyld:
   - **Name:** Borger Platform Abonnement
   - **Description:** Månedligt abonnement til Ret til Familie platform
   - **Pricing:** Recurring
   - **Price:** 49 DKK
   - **Billing period:** Monthly
3. Klik **Save product**
4. Kopiér **Price ID** (starter med `price_...`)

### 2.4 Opsæt Webhook
1. Gå til **Developers** → **Webhooks**
2. Klik **Add endpoint**
3. **Endpoint URL:** `https://rettilfamilie.com/stripe-webhook.php`
4. **Listen to:** Select events
5. Vælg disse events:
   - `checkout.session.completed`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_failed`
6. Klik **Add endpoint**
7. Kopiér **Signing secret** (starter med `whsec_...`)

### 2.5 Opdatér functions.php
Åbn `functions.php` og erstat linje 5-8:

```php
define('RTF_STRIPE_PUBLIC_KEY', 'pk_live_XXXXXXXXXX'); // Din publishable key
define('RTF_STRIPE_SECRET_KEY', 'sk_live_XXXXXXXXXX'); // Din secret key
define('RTF_STRIPE_PRICE_ID', 'price_XXXXXXXXXX');    // Din price ID
define('RTF_STRIPE_WEBHOOK_SECRET', 'whsec_XXXXXXXXXX'); // Din webhook secret
```

---

## 📁 TRIN 3: UPLOAD TIL WORDPRESS

### 3.1 Forbered Tema Fil
```bash
# Zip tema (ekskludér unødvendige filer)
cd "c:\Users\patrick f. hansen\OneDrive\Skrivebord\ret til familie hjemmeside"

# Windows PowerShell
Compress-Archive -Path * -DestinationPath "ret-til-familie-v31-live.zip" -Force `
  -Exclude @('node_modules','*.md','*.git*','.vscode')
```

### 3.2 Upload via WordPress Admin
1. Log ind på **WordPress Admin Panel**
2. Gå til **Udseende** → **Temaer**
3. Klik **Tilføj nyt** → **Upload tema**
4. Vælg `ret-til-familie-v31-live.zip`
5. Klik **Installér nu**
6. Klik **Aktivér**

### 3.3 Verificér Database Tabeller
Efter aktivering oprettes automatisk 13 tabeller:

```sql
-- Tjek at alle tabeller eksisterer
SHOW TABLES LIKE 'wp_rtf_platform_%';
```

**Forventede tabeller:**
- ✅ `wp_rtf_platform_users`
- ✅ `wp_rtf_platform_privacy`
- ✅ `wp_rtf_platform_posts`
- ✅ `wp_rtf_platform_images`
- ✅ `wp_rtf_platform_documents`
- ✅ `wp_rtf_platform_transactions`
- ✅ `wp_rtf_platform_news`
- ✅ `wp_rtf_platform_forum_topics`
- ✅ `wp_rtf_platform_forum_replies`
- ✅ `wp_rtf_platform_cases`
- ✅ `wp_rtf_platform_kate_chat`
- ✅ `wp_rtf_platform_friends`
- ✅ `wp_rtf_platform_document_analysis`

---

## 🧪 TRIN 4: TEST ALT FUNKTIONALITET

### 4.1 Test Registrering
1. Gå til `https://rettilfamilie.com/platform-auth`
2. Opret ny bruger
3. Verificér login fungerer

### 4.2 Test Stripe Subscription
1. Log ind som test bruger
2. Gå til **Abonnement**
3. Klik **Start Abonnement**
4. **Test betalingskort:**
   ```
   Kortnummer: 4242 4242 4242 4242
   Udløb: 12/34
   CVC: 123
   ZIP: 12345
   ```
5. Verificér at `subscription_status` = `active` i database

### 4.3 Test Kate AI
1. Gå til **Kate AI** side
2. Send testbesked: "Hvad er anbringelse?"
3. Verificér svar kommer tilbage
4. Tjek at samtale logges i `wp_rtf_platform_kate_chat`

### 4.4 Test Dokument Upload & Parsing
1. Gå til **Dokumenter**
2. Upload en PDF eller DOCX fil
3. Verificér at filen parses korrekt
4. Tjek `analysis_status` sættes

### 4.5 Test PDF Download (Klager)
1. Gå til **Klagegenerator**
2. Udfyld klageformular
3. Klik **Generer PDF**
4. Verificér PDF downloades med korrekt indhold

### 4.6 Test Ansigts-Blur
1. Gå til **Billeder**
2. Upload et billede
3. Afkryds **"Slør ansigter (GDPR)"**
4. Verificér at billede blur'es

### 4.7 Test GDPR User Isolation
**KRITISK TEST:**
1. Opret 2 brugere (Bruger A og Bruger B)
2. Upload dokumenter som Bruger A
3. Log ind som Bruger B
4. Verificér at Bruger B **IKKE** kan se Bruger A's dokumenter
5. Test Kate AI session isolation

---

## 🔒 TRIN 5: SIKKERHED & PERMISSIONS

### 5.1 Sæt Correct File Permissions
```bash
# Mapper der skal være writable (777)
chmod 777 wp-content/uploads
chmod 777 wp-content/uploads/platform_documents
chmod 777 wp-content/uploads/platform_images

# Tema filer (644)
find themes/ret-til-familie -type f -exec chmod 644 {} \;

# PHP filer (644)
find themes/ret-til-familie -name "*.php" -exec chmod 644 {} \;
```

### 5.2 SSL Certificate (HTTPS)
```bash
# Let's Encrypt via Certbot
sudo certbot --apache -d rettilfamilie.com -d www.rettilfamilie.com
```

### 5.3 Security Headers (.htaccess)
```apache
# Tilføj til .htaccess
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

---

## 📊 TRIN 6: MONITORING & LOGGING

### 6.1 Error Logging
Tilføj til `wp-config.php`:
```php
define('WP_DEBUG', false); // Sæt til false i produktion
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### 6.2 Kate AI Logging
Kate AI logger automatisk til:
- `wp_rtf_platform_kate_chat` (alle samtaler)
- PHP error log (hvis parsing fejler)

### 6.3 Stripe Webhook Logging
Webhook events logges til:
- `wp_rtf_platform_transactions`
- Stripe Dashboard → Developers → Webhooks

---

## 🆘 TROUBLESHOOTING

### Problem: Composer dependencies mangler
**Løsning:**
```bash
cd themes/ret-til-familie
composer install --no-dev --optimize-autoloader
```

### Problem: PDF generering fejler
**Fejl:** `mPDF not found`  
**Løsning:**
```bash
composer require mpdf/mpdf
```

### Problem: Stripe webhook fejler
**Fejl:** `Invalid signature`  
**Løsning:**
1. Verificér `RTF_STRIPE_WEBHOOK_SECRET` er korrekt
2. Tjek webhook URL er nøjagtig: `https://rettilfamilie.com/stripe-webhook.php`
3. Test webhook via Stripe Dashboard → Send test webhook

### Problem: Kate AI ingen svar
**Fejl:** `Unauthorized`  
**Løsning:**
- Verificér bruger er logget ind
- Tjek session starter: `session_start()` i functions.php
- Tjek REST API route: `/wp-json/kate/v1/message`

### Problem: Billeder blur'er ikke
**Fejl:** `GD library not available`  
**Løsning:**
```bash
# Ubuntu/Debian
sudo apt-get install php-gd
sudo systemctl restart apache2

# CentOS/RHEL
sudo yum install php-gd
sudo systemctl restart httpd
```

---

## ✅ POST-INSTALLATION CHECKLIST

```
☐ Composer dependencies installeret
☐ Stripe keys konfigureret (live mode)
☐ Stripe webhook oprettet og testet
☐ Tema uploadet og aktiveret
☐ 13 database tabeller eksisterer
☐ Test bruger oprettet
☐ Subscription flow testet
☐ Kate AI testet (svar modtaget)
☐ Dokument upload testet (parsing virker)
☐ PDF download testet (korrekt indhold)
☐ Ansigts-blur testet
☐ GDPR user isolation verificeret
☐ SSL certificate aktiveret
☐ File permissions sat korrekt
☐ Error logging aktiveret
☐ Backup strategi implementeret
```

---

## 📈 PERFORMANCE OPTIMIZATION (Optional)

### Caching
```php
// wp-config.php
define('WP_CACHE', true);

// Install caching plugin
// W3 Total Cache eller WP Rocket
```

### CDN (Optional)
```
- Cloudflare (gratis tier)
- AWS CloudFront
- BunnyCDN
```

### Database Optimization
```sql
-- Kør månedligt
OPTIMIZE TABLE wp_rtf_platform_users;
OPTIMIZE TABLE wp_rtf_platform_posts;
OPTIMIZE TABLE wp_rtf_platform_kate_chat;
```

---

## 🔄 BACKUP STRATEGI

### Daglig Backup (Anbefalet)
```bash
# Database backup
mysqldump -u root -p wordpress_db > backup_$(date +%Y%m%d).sql

# Filer backup
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz wp-content/uploads/
```

### Automatisk Backup Plugin
- **UpdraftPlus** (gratis)
- **BackWPup**
- **VaultPress**

---

## 📞 SUPPORT

**Teknisk support:**
- Email: support@rettilfamilie.com
- Forum: https://rettilfamilie.com/platform-forum

**Stripe support:**
- https://support.stripe.com

**WordPress support:**
- https://wordpress.org/support/

---

## 🎉 TILLYKKE!

Din Borger Platform er nu live og klar til brug! 🚀

**Næste skridt:**
1. Inviter første testbrugere
2. Monitér Stripe transactions
3. Tjek Kate AI samtaler for kvalitet
4. Saml feedback fra brugere
5. Iterér og forbedre baseret på data

**God fornøjelse!** 💙
