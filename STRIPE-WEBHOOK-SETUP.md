# 🔧 STRIPE WEBHOOK SETUP GUIDE

## KRITISK: Webhook URL Konfiguration

### 1. Find din Webhook URL

Din webhook URL afhænger af hvordan WordPress er sat op:

**MULIGHED A: Theme i standard WordPress installation**
```
https://dit-domæne.dk/wp-content/themes/dit-theme-navn/stripe-webhook.php
```

**MULIGHED B: WordPress i rodmappe (mest almindeligt)**
```
https://dit-domæne.dk/wp-content/themes/dit-theme-navn/stripe-webhook.php
```

**MULIGHED C: WordPress i undermappe**
```
https://dit-domæne.dk/wordpress/wp-content/themes/dit-theme-navn/stripe-webhook.php
```

---

## 2. Find Dit Theme Navn

### Via WordPress Admin:
1. Log ind i WordPress admin
2. Gå til **Udseende → Temaer**
3. Find aktivt tema - navnet står under logo/billede

### Via FTP/File Manager:
1. Naviger til: `/wp-content/themes/`
2. Find mappen med dine filer
3. Det er dit theme navn

---

## 3. Opsæt Webhook i Stripe

### Trin-for-Trin:

1. **Log ind i Stripe Dashboard:**
   - Gå til: https://dashboard.stripe.com/

2. **Naviger til Webhooks:**
   ```
   Developers → Webhooks → Add endpoint
   ```

3. **Tilføj Endpoint URL:**
   ```
   https://dit-domæne.dk/wp-content/themes/dit-theme-navn/stripe-webhook.php
   ```
   
   **ERSTAT:**
   - `dit-domæne.dk` → med din rigtige domæne
   - `dit-theme-navn` → med dit theme mappenavn

4. **Vælg Events:**
   Klik på "Select events" og vælg følgende:
   
   - ✅ `checkout.session.completed`
   - ✅ `customer.subscription.updated`
   - ✅ `customer.subscription.deleted`
   - ✅ `invoice.payment_failed`

5. **Klik "Add endpoint"**

6. **Kopier Signing Secret:**
   - Efter oprettelse vises "Signing secret"
   - Format: `whsec_xxxxxxxxxxxxx`
   - Gem denne til næste trin!

---

## 4. Opdater functions.php med Webhook Secret

### Find functions.php:
```
/wp-content/themes/dit-theme-navn/functions.php
```

### Find denne linje (omkring linje 48):
```php
define('RTF_STRIPE_WEBHOOK_SECRET', 'whsec_qQtOtg6DU191lNEoQplKCeYC0YAeolYw');
```

### Erstat med din nye signing secret:
```php
define('RTF_STRIPE_WEBHOOK_SECRET', 'whsec_DIN_SIGNING_SECRET_HER');
```

---

## 5. Test Webhook

### Metode 1: Stripe Dashboard Test

1. Gå til **Developers → Webhooks**
2. Klik på din webhook
3. Klik **"Send test webhook"**
4. Vælg event: `checkout.session.completed`
5. Klik **"Send test webhook"**

**Forventet resultat:**
- ✅ Status: `200 OK`
- ✅ Response body: tomt (det er korrekt!)

### Metode 2: Rigtig Test

1. Gå til din registreringsside:
   ```
   https://dit-domæne.dk/platform-auth/
   ```

2. Opret en test bruger

3. Gennemfør betaling med Stripe test kort:
   ```
   Kort:  4242 4242 4242 4242
   Dato:  12/34
   CVC:   123
   ```

4. Verificer:
   - ✅ Omdirigeret til profil med success besked
   - ✅ Subscription status = "Aktiv"

---

## 6. Verificer Webhook Logs

### WordPress Debug Log:
```
/wp-content/debug.log
```

**Søg efter:**
```
RTF Webhook: checkout.session.completed received
RTF Webhook: ✓ User found
RTF Webhook: ✓ Subscription activated
RTF Webhook: ✓ Payment logged successfully
```

### Hvis fejl opstår:
```
RTF Webhook ERROR: Could not load WordPress!
→ Check webhook URL path

RTF Webhook ERROR: User not found
→ Email mismatch mellem Stripe og database

RTF Webhook ERROR: Invalid signature
→ Webhook secret ikke korrekt i functions.php
```

---

## 7. Webhook URL Eksempler (Reelle)

### Eksempel 1: Standard WordPress
```
https://rettilfamilie.dk/wp-content/themes/rtf-platform/stripe-webhook.php
```

### Eksempel 2: WordPress i undermappe
```
https://rettilfamilie.dk/blog/wp-content/themes/rtf-platform/stripe-webhook.php
```

### Eksempel 3: Subdomain
```
https://platform.rettilfamilie.dk/wp-content/themes/rtf-platform/stripe-webhook.php
```

---

## 🚨 COMMON ISSUES

### Problem: "404 Not Found" på webhook
**Løsning:**
- Verificer theme mappenavn er korrekt
- Check at `stripe-webhook.php` findes i theme mappen
- Test URL direkte i browser (skal returnere blank side eller "Method Not Allowed")

### Problem: "Invalid signature"
**Løsning:**
- Kopier webhook signing secret fra Stripe Dashboard
- Indsæt i `RTF_STRIPE_WEBHOOK_SECRET` i functions.php
- Gem fil og upload til server
- Test igen

### Problem: "Could not load WordPress"
**Løsning:**
- Webhook finder ikke wp-load.php
- Check `wp_load_paths` array i stripe-webhook.php (linje 13-17)
- Tilføj korrekt sti til din WordPress installation

---

## ✅ CHECKLIST

Before går live:

- [ ] Webhook URL tilføjet i Stripe Dashboard
- [ ] Alle 4 events valgt (checkout.completed, subscription.updated, subscription.deleted, invoice.failed)
- [ ] Webhook signing secret kopieret fra Stripe
- [ ] `RTF_STRIPE_WEBHOOK_SECRET` opdateret i functions.php
- [ ] Test webhook sendt fra Stripe Dashboard → 200 OK
- [ ] Registrering testet med test betalingskort
- [ ] Bruger aktiveret automatisk efter betaling
- [ ] Success besked vises på profil side
- [ ] Debug log verificeret for korrekt flow

---

## 📞 SUPPORT

Hvis problemer opstår:

**Email:** socialfagligafd.rtf@outlook.dk  
**Subject:** "Stripe Webhook Setup Issue"

**Medtag:**
1. Din webhook URL
2. Screenshot af Stripe webhook dashboard
3. Debug log uddrag (sidste 20 linjer med "RTF Webhook")
4. Theme navn og WordPress version

---

**HELD OG LYKKE! 🚀**
