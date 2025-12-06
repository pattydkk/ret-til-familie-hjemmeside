# 🚀 Deployment Guide - GitHub til DanDomain WordPress

## Problem: Theme er for stort (176 MB)
- DanDomain upload limit: 2-10 MB
- Vendor folder alene: ~170 MB (Stripe, mPDF, PHPWord)
- **Løsning:** Deploy via GitHub + auto-install dependencies

---

## 📋 METODE 1: GitHub Repository (ANBEFALET)

### Step 1: Push til GitHub
```bash
cd "c:\Users\patrick f. hansen\OneDrive\Skrivebord\ret til familie hjemmeside"

# Initialiser Git repository (hvis ikke allerede gjort)
git init

# Tilføj alle filer (vendor/ bliver ignoreret af .gitignore)
git add .

# Commit
git commit -m "Initial commit - Ret til Familie Platform v2.0"

# Tilføj remote (brug dit GitHub repository)
git remote add origin https://github.com/hansenhr89dkk/ret-til-familie-hjemmeside.git

# Push til GitHub
git push -u origin main
```

### Step 2: SSH til DanDomain server
```bash
# SSH til din DanDomain WordPress installation
ssh dindomain@server.dandomain.dk

# Gå til themes folder
cd public_html/wp-content/themes/

# Clone repository
git clone https://github.com/hansenhr89dkk/ret-til-familie-hjemmeside.git rtf-platform

# Gå ind i theme folder
cd rtf-platform

# Installer dependencies via Composer
composer install --no-dev --optimize-autoloader

# Sæt korrekte permissions
chmod -R 755 .
chmod -R 777 kate-ai/logs
chmod -R 777 kate-ai/cache
chmod -R 777 kate-ai/data
```

### Step 3: Aktiver theme i WordPress
1. Log ind på WordPress admin
2. Gå til **Udseende → Temaer**
3. Aktiver **Ret til Familie Platform**
4. Database tabeller oprettes automatisk

---

## 📋 METODE 2: FTP Upload (Lightweight version)

### Step 1: Opret lightweight version
Ekskluder disse mapper fra upload:
```
/vendor/                    (170 MB - installeres på server)
/stripe-php-master/         (Legacy, brug ikke)
/.git/                      (Git metadata)
/kate-ai/logs/*.log         (Logs)
/kate-ai/cache/*            (Cache)
```

### Step 2: Upload via FTP
```
Folder: /public_html/wp-content/themes/rtf-platform/
Upload: Alle filer UNDTAGEN ovenstående
Størrelse: ~6 MB (uden vendor/)
```

### Step 3: SSH eller cPanel Terminal
```bash
cd /public_html/wp-content/themes/rtf-platform/
composer install --no-dev --optimize-autoloader
```

---

## 📋 METODE 3: WordPress Plugin Format (Anbefalet for store systemer)

Opdel i:
1. **Theme** (2-3 MB) - Kun templates og style.css
2. **Kate AI Plugin** (100 MB med vendor) - Installeres separat

### Opret Kate AI Plugin:

**Fil: kate-ai-plugin/kate-ai-plugin.php**
```php
<?php
/**
 * Plugin Name: Kate AI - Ret til Familie Assistant
 * Plugin URI: https://rettilf familie.dk
 * Description: AI juridisk assistent med 55+ love, 750+ paragraffer
 * Version: 1.0.0
 * Author: Ret til Familie
 * Requires PHP: 7.4
 */

// Include Composer autoloader
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// Include Kate AI bootstrap
require_once plugin_dir_path(__FILE__) . 'kate-ai/kate-ai.php';
```

Upload som plugin via WordPress admin (max 100 MB).

---

## 🔧 Server Requirements

### PHP Requirements
```
PHP >= 7.4
memory_limit >= 256M
max_execution_time >= 60
post_max_size >= 100M
upload_max_filesize >= 100M
```

### Composer Installation (hvis ikke installeret)
```bash
# Check om Composer er installeret
composer --version

# Hvis ikke, installer Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### WordPress wp-config.php optimizations
```php
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
```

---

## 📊 Deployment Checklist

### Før deployment:
- ✅ Fjern vendor/ folder (installeres på server)
- ✅ Fjern stripe-php-master/ (legacy)
- ✅ Commit .gitignore til GitHub
- ✅ Commit composer.json til GitHub
- ✅ Test at composer.json er valid

### På server:
- ✅ SSH adgang eller cPanel Terminal adgang
- ✅ Composer installeret
- ✅ PHP >= 7.4 med required extensions
- ✅ Write permissions på kate-ai/logs, cache, data

### Efter deployment:
- ✅ Kør `composer install`
- ✅ Aktiver theme i WordPress
- ✅ Test Kate AI funktionalitet
- ✅ Test Stripe integration
- ✅ Verificer database tabeller oprettet

---

## 🐛 Troubleshooting

### "Class not found" error
```bash
cd /path/to/theme
composer dump-autoload -o
```

### Memory limit errors
Øg i wp-config.php:
```php
define('WP_MEMORY_LIMIT', '512M');
```

### Timeout errors
Øg i .htaccess:
```apache
php_value max_execution_time 300
php_value max_input_time 300
```

### File permissions
```bash
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 777 kate-ai/logs kate-ai/cache kate-ai/data
```

---

## 📞 Support

**Repository:** https://github.com/hansenhr89dkk/ret-til-familie-hjemmeside
**Docs:** https://rettilfamilie.com/docs

---

## 🎯 Quick Deploy Command

```bash
# One-liner deployment
cd /public_html/wp-content/themes && \
git clone https://github.com/hansenhr89dkk/ret-til-familie-hjemmeside.git rtf-platform && \
cd rtf-platform && \
composer install --no-dev --optimize-autoloader && \
chmod -R 755 . && \
chmod -R 777 kate-ai/logs kate-ai/cache kate-ai/data && \
echo "✅ Deployment complete!"
```

