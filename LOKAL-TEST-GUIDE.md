# 🧪 LOKAL TEST GUIDE - Start Her Først

## 📋 Hvad Er Dette?

Du har nu 2 filer til LOKAL testing:

1. **functions-minimal.php** - Minimal sikker version af theme
2. **test-minimal.php** - Test side der checker alt

## 🎯 Trin 1: Test Lokalt (Live Preview)

### A. Start WordPress Lokalt
```powershell
# I din terminal, naviger til WordPress mappen:
cd "C:\Users\patrick f. hansen\OneDrive\Skrivebord\ret til familie hjemmeside"

# Start en lokal server (vælg én metode):

# Metode 1 - PHP built-in server:
php -S localhost:8000

# Metode 2 - Hvis du har WP-CLI:
wp server

# Metode 3 - Hvis du bruger Local by Flywheel / XAMPP:
# Start fra deres GUI
```

### B. Åbn Test Side
Åbn i browser:
```
http://localhost:8000/test-minimal.php
```

### C. Se Resultater
Test-siden viser 7 tests:
- ✅ Grøn = OK
- ❌ Rød = Problem

**HVIS ALLE ER GRØNNE**: Fortsæt til Trin 2  
**HVIS NOGEN ER RØDE**: Send mig screenshot

## 🎯 Trin 2: Test Minimal Functions

### A. Backup Original
```powershell
# Gem din originale functions.php:
Copy-Item "functions.php" "functions-BACKUP-FULL.php"
```

### B. Brug Minimal Version
```powershell
# Erstat med minimal version:
Copy-Item "functions-minimal.php" "functions.php"
```

### C. Test WordPress Front Page
Åbn i browser:
```
http://localhost:8000
```

**HVIS SIDEN LOADER**: Fortsæt til Trin 3  
**HVIS KRITISK FEJL**: Send mig error message

## 🎯 Trin 3: Upload Til Live Server

### A. Upload Minimal Version
Upload disse filer til live server:
```
✅ functions-minimal.php (omdøb til functions.php)
✅ test-minimal.php
```

### B. Test På Live Server
Åbn i browser:
```
https://dinserver.dk/test-minimal.php
```

### C. Se Resultater
**HVIS ALLE GRØNNE**: Minimal version virker live! 🎉  
**HVIS RØDE**: Send mig output fra test-minimal.php

## 🎯 Trin 4: Gradvist Tilføj Features

Når minimal version virker både lokalt og live:

### A. Tjek Database Tables
På test-minimal.php, under "Test 5: Database Tables":
- Hvis tabeller mangler → Kør rtf-setup.php
- Hvis tabeller findes → Skip setup

### B. Tilføj Full Functions.php
```powershell
# Gå tilbage til fuld version:
Copy-Item "functions-BACKUP-FULL.php" "functions.php"

# Upload til live server
# Test på https://dinserver.dk
```

### C. Hvis Fuld Version Crasher
1. Gå tilbage til minimal: `Copy-Item "functions-minimal.php" "functions.php"`
2. Upload minimal til live
3. Send mig output fra test-minimal.php
4. Vi finder forskellen mellem minimal og full

## ❓ Fejlfinding

### "Critical Error" på lokal server
```
Løsning: Check test-minimal.php output
Se hvilke tests er røde
Send screenshot til mig
```

### "Critical Error" på live server (men ikke lokalt)
```
Løsning: Upload test-minimal.php til live
Åbn https://dinserver.dk/test-minimal.php
Send mig output
```

### Database tabeller findes ikke
```
Løsning: Kør setup
cd til theme folder
php rtf-setup.php
```

### Session errors
```
Løsning: Allerede håndteret i minimal version
Hvis det stadig sker, send error message
```

## 📊 Test Checklist

Før du går videre, tjek af:

**Lokal Test:**
- [ ] test-minimal.php viser alle grønne ✅
- [ ] WordPress front page loader uden error
- [ ] Kan navigate til /borger-platform.php
- [ ] Session fungerer (Test 2 på test-minimal.php)

**Live Test:**
- [ ] Upload test-minimal.php til server
- [ ] test-minimal.php viser alle grønne ✅
- [ ] WordPress front page loader uden error
- [ ] Database tabeller findes (Test 5)

**Fuld Version Test:**
- [ ] functions-BACKUP-FULL.php uploaded som functions.php
- [ ] Front page loader uden error
- [ ] Login fungerer
- [ ] Platform features virker

## 🆘 Hvad Sender Jeg Til Dig?

### Hvis test-minimal.php virker lokalt men ikke live:
```
Send mig:
1. Screenshot af test-minimal.php fra LIVE server
2. Fortæl: Hvilke tests er røde?
```

### Hvis minimal virker men full crasher:
```
Send mig:
1. Exact error message fra live server
2. Indholdet af /wp-content/debug.log (sidste 50 linjer)
```

### Hvis ingenting virker:
```
Send mig:
1. Screenshot af test-minimal.php (lokal)
2. PHP version (fra Test 1)
3. Hvilke extensions mangler (fra Test 6)
```

## 🎯 Success Kriterier

### Minimal Version Success:
```
✅ test-minimal.php: Alle tests grønne
✅ WordPress front page loader
✅ Ingen "Critical Error"
✅ Session virker
```

### Fuld Version Success:
```
✅ Alle ovenstående PLUS:
✅ Login fungerer på /borger-platform.php
✅ Platform sider loader
✅ Database queries virker
✅ Ingen fejl i debug.log
```

## 📝 Quick Commands

```powershell
# Test PHP syntax:
php -l functions-minimal.php

# Start lokal server:
php -S localhost:8000

# Backup full version:
Copy-Item "functions.php" "functions-BACKUP-FULL.php"

# Brug minimal:
Copy-Item "functions-minimal.php" "functions.php"

# Restore full:
Copy-Item "functions-BACKUP-FULL.php" "functions.php"

# Check git status:
git status

# Commit changes:
git add -A; git commit -m "Test minimal version"; git push origin main
```

## 🚀 Start Nu!

1. Åbn terminal
2. Kør: `php -S localhost:8000`
3. Åbn browser: `http://localhost:8000/test-minimal.php`
4. Send mig screenshot af resultaterne

**Jeg venter på dit output fra test-minimal.php! 🎯**
