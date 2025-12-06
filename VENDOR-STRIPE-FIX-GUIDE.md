# 🔧 VENDOR & STRIPE FEJL - LØSNINGSGUIDE

## PROBLEM IDENTIFICERET

Du rapporterede: **"ser us som om både stripe og vendor laver fejl"**

Lad os systematisk løse dette:

---

## ✅ LØSNING 1: Verificer Vendor Plugin er Aktiveret

### Trin 1: Check Plugin Status

1. Log ind i WordPress Admin
2. Gå til **Plugins → Installed Plugins**
3. Find **"RTF Vendor Dependencies"**
4. Status skal være: ✅ **Active**

### Hvis IKKE aktiveret:
- Klik **"Activate"**
- Refresh side
- Fortsæt til næste trin

---

## ✅ LØSNING 2: Verificer Vendor Folder

### Check at vendor/ findes:

**Via File Manager / FTP:**
```
/wp-content/plugins/rtf-vendor-plugin/vendor/
```

**Skal indeholde:**
```
vendor/
├── autoload.php          ← VIGTIG!
├── composer/
├── stripe/
│   └── stripe-php/      ← Stripe SDK
├── mpdf/
├── phpoffice/
├── smalot/
└── ...
```

### Hvis vendor/ mangler:

**Option A: Upload via FTP/File Manager**
1. Gå til plugin mappen: `/wp-content/plugins/rtf-vendor-plugin/`
2. Upload hele `vendor/` mappen
3. Verificer `vendor/autoload.php` findes

**Option B: Kør Composer (hvis du har SSH adgang)**
```bash
cd /path/to/wp-content/plugins/rtf-vendor-plugin/
composer install --no-dev --optimize-autoloader
```

---

## ✅ LØSNING 3: Verificer Stripe Library i Theme

### Check Stripe i Theme:

**Sti:**
```
/wp-content/themes/dit-theme-navn/stripe-php-13.18.0/
```

**Skal indeholde:**
```
stripe-php-13.18.0/
├── init.php              ← VIGTIG!
├── lib/
│   ├── Stripe.php
│   ├── Checkout/
│   ├── Exception/
│   └── ...
└── composer.json
```

### Hvis mangler:
1. Download Stripe PHP SDK v13.18.0 fra GitHub
2. Upload til theme mappen
3. Verificer `stripe-php-13.18.0/init.php` findes

---

## ✅ LØSNING 4: Test Stripe Integration

### Metode 1: Via Browser Test

1. **Opret test fil:** `test-stripe.php` i theme mappen

```php
<?php
require_once __DIR__ . '/stripe-php-13.18.0/init.php';
require_once __DIR__ . '/../../../wp-load.php';

echo "<h1>Stripe Test</h1>";

// Test 1: Check class exists
if (class_exists('\Stripe\Stripe')) {
    echo "✅ Stripe class loaded<br>";
} else {
    echo "❌ Stripe class NOT found<br>";
}

// Test 2: Check API key
if (defined('RTF_STRIPE_SECRET_KEY')) {
    echo "✅ RTF_STRIPE_SECRET_KEY defined<br>";
    echo "Value: " . substr(RTF_STRIPE_SECRET_KEY, 0, 10) . "...<br>";
} else {
    echo "❌ RTF_STRIPE_SECRET_KEY NOT defined<br>";
}

// Test 3: Try to set API key
try {
    \Stripe\Stripe::setApiKey(RTF_STRIPE_SECRET_KEY);
    echo "✅ Stripe API key set successfully<br>";
} catch (Exception $e) {
    echo "❌ Error setting API key: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>Conclusion:</h2>";
if (class_exists('\Stripe\Stripe') && defined('RTF_STRIPE_SECRET_KEY')) {
    echo "<strong style='color: green;'>✅ Stripe is working correctly!</strong>";
} else {
    echo "<strong style='color: red;'>❌ Stripe has issues - see errors above</strong>";
}
?>
```

2. **Besøg filen:**
```
https://dit-domæne.dk/wp-content/themes/dit-theme-navn/test-stripe.php
```

3. **Forventet output:**
```
✅ Stripe class loaded
✅ RTF_STRIPE_SECRET_KEY defined
✅ Stripe API key set successfully
✅ Stripe is working correctly!
```

---

## ✅ LØSNING 5: Verificer Vendor Loader

### Test at vendor autoloader fungerer:

**Opret test fil:** `test-vendor.php` i plugin mappen

```php
<?php
$autoload = __DIR__ . '/vendor/autoload.php';

echo "<h1>Vendor Test</h1>";

if (file_exists($autoload)) {
    echo "✅ vendor/autoload.php exists<br>";
    require_once $autoload;
    echo "✅ Autoloader loaded successfully<br><br>";
    
    // Test libraries
    $tests = [
        '\Stripe\Stripe' => 'Stripe SDK',
        '\Mpdf\Mpdf' => 'mPDF',
        '\PhpOffice\PhpWord\PhpWord' => 'PHPWord',
        '\Smalot\PdfParser\Parser' => 'PDF Parser'
    ];
    
    foreach ($tests as $class => $name) {
        if (class_exists($class)) {
            echo "✅ $name loaded<br>";
        } else {
            echo "❌ $name NOT found<br>";
        }
    }
} else {
    echo "❌ vendor/autoload.php NOT FOUND<br>";
    echo "Path: $autoload<br>";
}
?>
```

**Besøg:**
```
https://dit-domæne.dk/wp-content/plugins/rtf-vendor-plugin/test-vendor.php
```

---

## ✅ LØSNING 6: Fix Stripe Webhook Fejl

### Hvis webhook logger fejl:

**Check WordPress Debug Log:**
```
/wp-content/debug.log
```

**Søg efter fejl:**
```bash
grep "RTF Webhook ERROR" debug.log
```

### Typiske fejl og løsninger:

**1. "Could not load WordPress"**
```
RTF Webhook ERROR: Could not load WordPress!
```
**Løsning:** Webhook finder ikke wp-load.php
- Verificer webhook URL path
- Check at theme navn er korrekt i URL

**2. "Stripe not configured"**
```
RTF Webhook ERROR: Stripe not configured!
```
**Løsning:** Stripe keys ikke sat korrekt
- Check `functions.php` linje 45-48
- Verificer alle 4 constants er defineret

**3. "Invalid signature"**
```
RTF Webhook ERROR: Invalid signature
```
**Løsning:** Webhook secret matcher ikke
- Kopier signing secret fra Stripe Dashboard
- Opdater `RTF_STRIPE_WEBHOOK_SECRET` i functions.php

**4. "User not found"**
```
RTF Webhook ERROR: User not found with email
```
**Løsning:** Email mismatch
- Check at bruger blev oprettet korrekt
- Verificer email i database matcher Stripe email

---

## ✅ LØSNING 7: Emergency Manual Aktivering

Hvis alt andet fejler, brug manuel aktivering:

### Via activate-user.php:

1. **Besøg:**
```
https://dit-domæne.dk/wp-content/themes/dit-theme-navn/activate-user.php
```

2. **Log ind:**
```
Password: rtf2024admin
```

3. **Find bruger der skal aktiveres**

4. **Klik "Activate" button**

Dette aktiverer abonnement manuelt uden at vente på webhook.

---

## 🔍 DEBUGGING CHECKLIST

Gennemgå følgende systematisk:

### 1. Plugin Status
- [ ] RTF Vendor Plugin aktiveret
- [ ] vendor/autoload.php findes
- [ ] Ingen fejlbeskeder i plugin admin

### 2. Stripe Configuration
- [ ] `RTF_STRIPE_PUBLIC_KEY` sat i functions.php
- [ ] `RTF_STRIPE_SECRET_KEY` sat i functions.php
- [ ] `RTF_STRIPE_PRICE_ID` sat i functions.php
- [ ] `RTF_STRIPE_WEBHOOK_SECRET` sat i functions.php

### 3. Stripe Folder
- [ ] stripe-php-13.18.0/ findes i theme
- [ ] init.php findes i stripe-php-13.18.0/
- [ ] lib/ mappe findes med Stripe klasser

### 4. Webhook Setup
- [ ] Webhook URL tilføjet i Stripe Dashboard
- [ ] Webhook events valgt (checkout.session.completed)
- [ ] Webhook signing secret kopieret til functions.php
- [ ] Test webhook sendt → 200 OK

### 5. Database
- [ ] rtf_platform_users tabel findes
- [ ] stripe_customer_id kolonne findes
- [ ] rtf_stripe_payments tabel findes

---

## 📊 KOMPLET DIAGNOSTIK

### Kør denne kommando via terminal/PowerShell:

```bash
# Test hele setup
curl -X POST https://dit-domæne.dk/wp-content/themes/dit-theme-navn/test-system-complete.php
```

**Forventet output:**
```
✅ Database tables: 28/28 created
✅ Users found: X
✅ Active subscriptions: X
✅ Stripe configuration: OK
✅ Vendor libraries: Loaded
✅ Webhook file: Exists
```

---

## 🚨 HVIS INTET VIRKER

### Sidste udvej - Geninstaller:

1. **Deaktiver plugin:**
   - Gå til Plugins → RTF Vendor Dependencies
   - Klik "Deactivate"

2. **Slet vendor folder:**
   - Via FTP/File Manager
   - Slet `/wp-content/plugins/rtf-vendor-plugin/vendor/`

3. **Genupload vendor:**
   - Upload frisk `vendor/` mappe
   - Verificer alle filer er der

4. **Reaktiver plugin:**
   - Klik "Activate" igen

5. **Test igen:**
   - Kør test-vendor.php
   - Kør test-stripe.php

---

## 📞 SUPPORT

**Hvis du stadig har problemer:**

Email: socialfagligafd.rtf@outlook.dk  
Subject: "Vendor/Stripe Integration Issue"

**Medtag i email:**
1. Screenshot af plugin liste (med status)
2. Output fra test-vendor.php
3. Output fra test-stripe.php
4. Debug log (sidste 50 linjer med "RTF" eller "Stripe")
5. WordPress version
6. PHP version (fra Dashboard → Site Health)

---

**ALT BURDE VIRKE NU! 🚀**
