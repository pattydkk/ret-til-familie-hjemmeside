# 🚨 WORDPRESS CRITICAL ERROR - LØSNINGSGUIDE

## Problemet
WordPress viser: "There has been a critical error on this website"

## ✅ LØSNINGER (Prøv i denne rækkefølge)

### 1️⃣ KØR DEBUG VÆRKTØJ (FØRST!)
**Åbn i din browser:**
```
https://dit-domæne.dk/wp-content/themes/dit-tema/debug-wordpress.php
```

Dette vil vise dig præcist hvad der er galt:
- ✅ Syntax fejl i PHP filer
- ✅ Manglende klasser
- ✅ Database problemer  
- ✅ WordPress error logs

---

### 2️⃣ EMERGENCY RECOVERY MODE
Hvis WordPress ikke starter overhovedet:

**Åbn:**
```
https://dit-domæne.dk/wp-content/themes/dit-tema/emergency-recovery.php
```

**Klik på: "ENABLE Emergency Mode"**

Dette deaktiverer alle custom RTF features midlertidigt, så WordPress kan køre.
Du kan så:
1. Logge ind i WordPress admin
2. Undersøge fejlen
3. Fikse problemet
4. Deaktivere Emergency Mode igen

---

### 3️⃣ ALMINDELIGE PROBLEMER & FIXES

#### Problem: "Class not found"
**Fix via SSH/FTP:**
```bash
cd /path/to/theme/
composer install --no-dev
```

#### Problem: "Database error"
**Tjek wp-config.php:**
- Er database navn, brugernavn, password korrekte?
- Er database serveren online?

#### Problem: "Memory exhausted"
**Øg PHP memory i php.ini:**
```ini
memory_limit = 256M
```

Eller tilføj i wp-config.php:
```php
define('WP_MEMORY_LIMIT', '256M');
```

#### Problem: "Parse error" eller "Syntax error"
**Find filen i fejlmeddelelsen og tjek:**
- Manglende `;` eller `}` eller `)`
- Forkerte anførselstegn `"` vs `'`
- Kør: `php -l filename.php` for at teste

---

### 4️⃣ SYSTEM CHECK
Når siden virker igen, kør fuld system check:

```
https://dit-domæne.dk/wp-content/themes/dit-tema/FINAL-SYSTEM-CHECK.php
```

Dette verificerer:
- ✅ Alle PHP filer har korrekt syntax
- ✅ Database tabeller eksisterer
- ✅ User system er loaded
- ✅ Stripe er konfigureret
- ✅ Sessions virker

---

## 🔧 HVAD ER BLEVET FIKSET

### functions.php Forbedringer:
1. ✅ **Error Handling** - Alle require/include er wrapped i try-catch
2. ✅ **Emergency Mode** - Kan deaktivere alt med en konstant
3. ✅ **Global Error Handler** - Logger fejl i stedet for at crashe
4. ✅ **Shutdown Handler** - Fanger fatal errors
5. ✅ **Database Safe** - Alle ALTER TABLE queries tjekker først

### Nye Recovery Værktøjer:
- ✅ `debug-wordpress.php` - Komplet diagnostik
- ✅ `emergency-recovery.php` - Enable/disable emergency mode
- ✅ `FINAL-SYSTEM-CHECK.php` - Pre-launch verification

---

## 📋 TJEKLISTE FØR GO-LIVE

- [ ] Kør `debug-wordpress.php` - ingen røde fejl
- [ ] Kør `FINAL-SYSTEM-CHECK.php` - alle tests pass
- [ ] Test login på `/platform-auth/`
- [ ] Test admin panel på `/platform-admin-dashboard/`
- [ ] Verificer Stripe keys er sat (ikke default!)
- [ ] Tjek at `vendor/autoload.php` eksisterer
- [ ] Backup database
- [ ] Test på staging server først!

---

## 🆘 HVIS INTET VIRKER

### Plan B: Manuel Recovery

1. **Via FTP/cPanel:**
   - Åbn `wp-config.php`
   - Tilføj denne linje før "That's all, stop editing":
   ```php
   define('RTF_EMERGENCY_MODE', true);
   ```
   - Gem filen
   - Prøv at åbne WordPress admin

2. **Hvis stadig ikke virker:**
   - Skift til et standard WordPress theme (Twenty Twenty-Three)
   - Log ind i admin
   - Undersøg fejl logs
   - Fix problemet i RTF theme
   - Skift tilbage til RTF theme

3. **Kontakt support:**
   - Inkluder output fra `debug-wordpress.php`
   - Inkluder WordPress error log
   - Beskriv hvad du gjorde før fejlen opstod

---

## ✅ SYSTEMET ER NU SIKRET

- 🛡️ Robuste error handlers
- 🔧 Recovery værktøjer tilgængelige
- 📊 Debug tools for hurtig fejlfinding
- 🚨 Emergency mode hvis alt går galt

**VIGTIGT:** Gem links til debug værktøjerne et sikkert sted!
