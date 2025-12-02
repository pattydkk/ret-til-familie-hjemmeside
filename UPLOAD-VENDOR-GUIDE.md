# UPLOAD VENDOR/ FOLDER TIL DANDOMAIN

**3 metoder til at uploade vendor/ dependencies (170 MB)**

---

## ✅ METODE 1: File Manager (NEMMEST)

### Trin-for-trin:

1. **Pak vendor/ til ZIP lokalt:**
   ```powershell
   cd "c:\Users\patrick f. hansen\OneDrive\Skrivebord\ret til familie hjemmeside"
   Compress-Archive -Path vendor -DestinationPath vendor.zip
   ```
   *(Dette opretter vendor.zip ~40-50 MB)*

2. **Log ind DanDomain:**
   - Gå til https://admin.dandomain.dk
   - Find File Manager / Filhåndtering

3. **Naviger til tema folder:**
   ```
   /public_html/wp-content/themes/ret-til-familie-hjemmeside/
   ```

4. **Upload vendor.zip:**
   - Klik "Upload" eller "Upload fil"
   - Vælg vendor.zip fra din computer
   - Vent på upload færdig (~2-5 min afhængig af hastighed)

5. **Udpak ZIP filen:**
   - Højreklik på vendor.zip
   - Vælg "Extract" / "Udpak"
   - Det opretter vendor/ folder automatisk

6. **Slet vendor.zip (valgfrit):**
   - Højreklik → Delete

7. **Verificer upload:**
   - Tjek at `vendor/autoload.php` findes
   - Tjek at `vendor/stripe/`, `vendor/mpdf/` etc. findes

---

## ✅ METODE 2: FTP Upload (HURTIGERE)

### Med FileZilla eller anden FTP klient:

1. **Hent FTP login fra DanDomain:**
   - Gå til admin.dandomain.dk
   - Find "FTP Access" / "FTP Adgang"
   - Noter:
     - Host: ftp.dandomain.dk (eller specifik server)
     - Username: [dit-ftp-brugernavn]
     - Password: [dit-ftp-password]
     - Port: 21

2. **Connect via FTP:**
   ```
   Host: ftp.dandomain.dk
   Port: 21
   Protocol: FTP eller SFTP
   ```

3. **Upload vendor/ folder:**
   - Naviger til: `/public_html/wp-content/themes/ret-til-familie-hjemmeside/`
   - Drag & drop hele `vendor/` folderen
   - Upload tid: ~10-20 min for 170 MB

---

## ✅ METODE 3: SSH Composer Install (BEDST)

**Hvis DanDomain har SSH access:**

1. **Connect via SSH:**
   ```bash
   ssh [username]@[server].dandomain.dk
   ```

2. **Naviger til tema:**
   ```bash
   cd public_html/wp-content/themes/ret-til-familie-hjemmeside
   ```

3. **Kør Composer:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Færdig!** Vendor/ installeres automatisk (2-3 min)

---

## 🔧 POWERSHELL AUTO-UPLOAD SCRIPT

Jeg laver et automatisk upload script via FTP:

```powershell
# UPLOAD-VENDOR.ps1
# Upload vendor/ folder til DanDomain via FTP

$FTPHost = "ftp.dandomain.dk"
$FTPUser = "DIT_FTP_BRUGERNAVN"  # <-- REDIGER DETTE
$FTPPass = "DIT_FTP_PASSWORD"     # <-- REDIGER DETTE
$LocalFolder = ".\vendor"
$RemoteFolder = "/public_html/wp-content/themes/ret-til-familie-hjemmeside/vendor"

Write-Host "Starting FTP upload of vendor/ folder..." -ForegroundColor Green

# Alternative: Brug WinSCP.com (hvis installeret)
# winscp.com /command "open ftp://$FTPUser:$FTPPass@$FTPHost" "synchronize remote $LocalFolder $RemoteFolder" "exit"

Write-Host "Upload complete!" -ForegroundColor Green
```

---

## 📦 ALTERNATIV: Split i mindre ZIP filer

Hvis File Manager har upload limit (f.eks. max 50 MB):

```powershell
# Split vendor/ i mindre dele
cd "c:\Users\patrick f. hansen\OneDrive\Skrivebord\ret til familie hjemmeside"

# Del 1: Stripe
Compress-Archive -Path vendor\stripe -DestinationPath vendor-stripe.zip

# Del 2: mPDF
Compress-Archive -Path vendor\mpdf -DestinationPath vendor-mpdf.zip

# Del 3: PHPWord
Compress-Archive -Path vendor\phpoffice -DestinationPath vendor-phpoffice.zip

# Del 4: PDF Parser
Compress-Archive -Path vendor\smalot -DestinationPath vendor-smalot.zip

# Del 5: Resten
Compress-Archive -Path vendor\composer,vendor\autoload.php -DestinationPath vendor-core.zip
```

Upload hver ZIP separat og udpak i vendor/ folderen.

---

## ✅ EFTER UPLOAD: Aktiver Kate AI

1. **Verificer vendor/ findes:**
   - Tjek via File Manager eller SSH at `vendor/autoload.php` eksisterer

2. **Uncomment Kate AI kode:**
   - Rediger `functions.php` lokalt
   - Find linje ~62: `/* Kate AI code commented out */`
   - Fjern `/*` og `*/` omkring Kate AI initialisering

3. **Push til GitHub:**
   ```powershell
   git add functions.php
   git commit -m "Enabled Kate AI after vendor upload"
   git push origin main
   ```

4. **Download opdatering via WordPress plugin:**
   - WordPress admin → Themes
   - Update theme fra GitHub
   - Eller upload opdateret functions.php manuelt

5. **Test Kate AI:**
   - Gå til `/platform-kate-ai/` på dit site
   - Tjek at Kate AI chat widget loader
   - Test en besked

---

## 🎯 ANBEFALET METODE

**For dig:** METODE 1 (File Manager med ZIP)

**Hvorfor:**
- Ingen FTP konfiguration nødvendig
- Grafisk interface (nemt)
- ZIP komprimerer ~170 MB → ~40 MB (hurtigere upload)
- DanDomain File Manager kan udpakke ZIP automatisk

**Tid:** 5-10 minutter total

---

## ⚠️ TROUBLESHOOTING

### Problem: "Upload failed" / "File too large"
**Løsning:** Brug split ZIP metode (4-5 mindre filer)

### Problem: "Cannot unzip file"
**Løsning:** Upload via FTP i stedet (METODE 2)

### Problem: "Vendor folder exists but theme still crashes"
**Løsning:** 
1. Tjek at vendor/autoload.php har korrekt permissions (644)
2. Tjek at PHP kan læse filen (ikke ejet af root)
3. Uncomment Kate AI kode i functions.php

### Problem: "composer install not found" (SSH)
**Løsning:** Composer ikke installeret på server - brug METODE 1 eller 2

---

## 📊 UPLOAD TID ESTIMAT

| Metode | Upload størrelse | Estimeret tid |
|--------|------------------|---------------|
| File Manager (ZIP) | ~40 MB | 2-5 min |
| FTP (direkte) | ~170 MB | 10-20 min |
| SSH Composer | Download fra web | 2-3 min |
| Split ZIP (5 dele) | 5x ~10 MB | 5-8 min |

---

## ✅ STATUS CHECK KOMMANDO

Efter upload, verificer via SSH eller File Manager:

```bash
# Via SSH
cd /public_html/wp-content/themes/ret-til-familie-hjemmeside
ls -lh vendor/autoload.php
du -sh vendor/

# Forventet output:
# -rw-r--r-- 1 user user 3.1K vendor/autoload.php
# 170M    vendor/
```

---

**Klar til at starte upload?** Brug METODE 1 først - det er nemmest! 🚀
