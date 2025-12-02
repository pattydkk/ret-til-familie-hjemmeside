# RTF Vendor Dependencies Plugin

**Version:** 1.0.1  
**Requires:** WordPress 5.8+, PHP 7.4+

## 📦 Hvad Gør Dette Plugin?

Loader alle Composer vendor dependencies som er nødvendige for **Ret til Familie** temaet:

- ✅ **Stripe PHP SDK** - Betalinger og abonnementer
- ✅ **mPDF** - PDF generering (klager, rapporter)
- ✅ **PHPWord** - DOCX document generering
- ✅ **PDF Parser** - PDF læsning og parsing
- ✅ **FPDI & Setasign** - PDF manipulation

## 🚀 Installation

### Method 1: Upload ZIP (Anbefalet)

1. **Download plugin:**
   - Pak `rtf-vendor-plugin.zip` fra GitHub eller skrivebord

2. **Upload til WordPress:**
   ```
   WordPress Admin → Plugins → Tilføj ny → Upload Plugin
   → Vælg rtf-vendor-plugin.zip
   → Klik "Installer Nu"
   → Vent 5-10 minutter (filen er 340 MB)
   ```

3. **Aktiver plugin:**
   ```
   → Klik "Aktiver Plugin"
   ```

4. **Verificer:**
   ```
   WordPress Admin → Indstillinger → RTF Vendor
   → Se status for alle libraries
   ```

### Method 2: FTP/File Manager

1. **Upload folder:**
   ```
   Upload rtf-vendor-plugin/ til /wp-content/plugins/
   ```

2. **Verificer struktur:**
   ```
   /wp-content/plugins/rtf-vendor-plugin/
   ├── rtf-vendor-plugin.php
   ├── github-updater.php
   ├── README.md
   └── vendor/
       └── autoload.php (VIGTIGT!)
   ```

3. **Aktiver i WordPress Admin → Plugins**

## ⚙️ Hvad Sker Der Efter Aktivering?

1. ✅ `vendor/autoload.php` loades automatisk
2. ✅ `RTF_VENDOR_LOADED` constant defineres (= true)
3. ✅ Alle vendor libraries er tilgængelige globalt
4. ✅ Temaet kan nu bruge Kate AI, Stripe, mPDF, etc.

## 🔄 Auto-Opdatering Fra GitHub

Plugin opdaterer automatisk når du pusher til GitHub:

1. **Kode ændringer:**
   ```bash
   git add rtf-vendor-plugin/
   git commit -m "Updated vendor plugin"
   git push origin main
   ```

2. **Opdater i WordPress:**
   ```
   WordPress Admin → Dashboard → Opdateringer
   → Se "RTF Vendor Dependencies" opdatering
   → Klik "Opdater Nu"
   ```

3. **Automatisk:**
   - Plugin downloades fra GitHub
   - Erstatter eksisterende filer
   - Bevarer `vendor/` folder (hvis ikke i GitHub)

## 📊 Status Check

**Se vendor status:**
```
WordPress Admin → Indstillinger → RTF Vendor
```

**Status viser:**
- ✅/❌ Vendor folder fundet
- ✅/❌ Stripe SDK loaded
- ✅/❌ mPDF loaded
- ✅/❌ PHPWord loaded
- ✅/❌ PDF Parser loaded
- 📊 Total vendor størrelse

## ⚠️ Troubleshooting

### Problem: "Vendor folder mangler"

**Løsning:**
```bash
# Via FTP/File Manager:
Upload vendor/ til: /wp-content/plugins/rtf-vendor-plugin/vendor/

# Verificer:
/wp-content/plugins/rtf-vendor-plugin/vendor/autoload.php eksisterer
```

### Problem: Plugin opdaterer ikke fra GitHub

**Løsning:**
```
1. Check GitHub repository er PUBLIC
2. Slet WordPress transient: wp_options → update_plugins
3. WordPress Admin → Dashboard → Opdateringer → Tjek Igen
```

### Problem: Kate AI virker ikke

**Tjek:**
```php
// I WordPress debug.log eller theme:
<?php
if (defined('RTF_VENDOR_LOADED') && RTF_VENDOR_LOADED) {
    echo 'Vendor loaded!';
} else {
    echo 'Vendor NOT loaded - check plugin activation';
}
?>
```

## 🗂️ Plugin Struktur

```
rtf-vendor-plugin/
├── rtf-vendor-plugin.php    # Main plugin file
├── github-updater.php        # GitHub auto-update handler
├── README.md                 # This file
└── vendor/                   # Composer dependencies (340 MB)
    ├── autoload.php          # Composer autoloader
    ├── composer/             # Composer metadata
    ├── stripe/               # Stripe PHP SDK (~50 MB)
    ├── mpdf/                 # mPDF library (~80 MB)
    ├── phpoffice/            # PHPWord (~20 MB)
    ├── smalot/               # PDF Parser (~10 MB)
    └── setasign/             # FPDI (~5 MB)
```

## 🔐 Sikkerhed

- ✅ Exit hvis accessed directly (`ABSPATH` check)
- ✅ Kun admins kan se status page
- ✅ Vendor loader kun på `plugins_loaded` hook
- ✅ Error handling hvis vendor mangler

## 📝 Version History

### 1.0.1 (2025-12-02)
- ✅ Added GitHub auto-updater
- ✅ Added README.md
- ✅ Improved admin status page
- ✅ Better error messages

### 1.0.0 (2025-12-01)
- ✅ Initial release
- ✅ Vendor autoloader
- ✅ Admin status page

## 🔗 Links

- **GitHub:** https://github.com/pattydkk/ret-til-familie-hjemmeside
- **Theme:** Ret til Familie
- **Support:** Via GitHub Issues

## 📄 License

GPL v2 or later

---

**Lavet til Ret til Familie Platform** 🇩🇰
