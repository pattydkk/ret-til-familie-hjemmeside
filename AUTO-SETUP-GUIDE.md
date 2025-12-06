# 🚀 ONE-CLICK AUTO SETUP GUIDE
**Ret til Familie Platform - Automatisk Installation**

---

## ⚡ HURTIG START (3 MINUTTER)

### STEP 1: Upload Tema (1 minut)
```bash
1. Pak hele mappen som .zip
2. WordPress Admin → Udseende → Temaer → Tilføj nyt → Upload tema
3. Upload .zip filen
4. Klik "Aktiver"
```

### STEP 2: Kør Auto-Setup (30 sekunder)
Besøg denne URL ÉN GANG:
```
https://rettilfamilie.com/wp-content/themes/ret-til-familie-hjemmeside/rtf-setup.php
```

**Dette opretter automatisk:**
- ✅ Alle 29 database tabeller
- ✅ Alle sider med korrekte templates
- ✅ Admin bruger (patrickfoersle@gmail.com)
- ✅ Kate AI system
- ✅ Stripe integration

### STEP 3: Tilføj Stripe Keys (1 minut)
Åbn `functions.php` og find line 198-199:
```php
$stripe_secret_key = 'din_stripe_secret_key_her';
$stripe_publishable_key = 'din_stripe_publishable_key_her';
```

Udskift med dine Stripe keys fra: https://dashboard.stripe.com/apikeys

**DONE! ✅ Siden kører nu!**

---

## 📋 HVAD SKER DER AUTOMATISK?

### Når du besøger `rtf-setup.php`:

#### 1. Database Setup ✅
Opretter automatisk alle tabeller:
```
✅ rtf_platform_users (brugere)
✅ rtf_platform_messages (beskeder)
✅ rtf_platform_posts (væg posts)
✅ rtf_platform_forum_topics (forum)
✅ rtf_kate_chat_sessions (Kate AI)
✅ rtf_stripe_subscriptions (betalinger)
... og 23 andre
```

#### 2. WordPress Sider ✅
Opretter automatisk sider med templates:
```
✅ /platform-auth → Login/Registrering
✅ /platform-profil → Brugerprofil
✅ /platform-admin-dashboard → Admin Panel
✅ /platform-chat → Chat System
✅ /platform-forum → Forum
✅ /platform-kate-ai → Kate AI
✅ /platform-rapporter → Rapporter
... og 10 andre sider
```

#### 3. Admin Bruger ✅
Opretter admin bruger:
```
Email: patrickfoersle@gmail.com
Password: AdminRTF2024!
is_admin: 1
subscription_status: active
```

#### 4. Kate AI ✅
Initialiserer Kate AI system:
```
✅ Knowledge base tabeller
✅ Chat session system
✅ Document analyse
✅ Legal guidance engine
```

#### 5. REST API ✅
Registrerer automatisk endpoints:
```
✅ /wp-json/kate/v1/admin/* (admin endpoints)
✅ /wp-json/kate/v1/messages/* (chat system)
✅ /wp-json/kate/v1/chat (Kate AI)
✅ /wp-json/kate/v1/reports/* (rapporter)
```

---

## 🔑 LOGIN INFORMATION

### Admin Login
```
URL: https://rettilfamilie.com/platform-auth
Email: patrickfoersle@gmail.com
Password: AdminRTF2024!
```

**OBS:** Skift password efter første login!

### Admin Panel
```
URL: https://rettilfamilie.com/platform-admin-dashboard
- Opret brugere
- Se statistik
- Administrer forum
- Se alle posts
- Download rapporter
```

---

## 💳 STRIPE SETUP

### Hent Dine Keys
1. Gå til: https://dashboard.stripe.com/apikeys
2. Kopier **Secret key** (sk_test_... eller sk_live_...)
3. Kopier **Publishable key** (pk_test_... eller pk_live_...)

### Indsæt i functions.php
Åbn `functions.php` line 198-199 og udskift:

**FØR:**
```php
$stripe_secret_key = 'din_stripe_secret_key_her';
$stripe_publishable_key = 'din_stripe_publishable_key_her';
```

**EFTER:**
```php
$stripe_secret_key = 'sk_live_51Abc...'; // Din rigtige key
$stripe_publishable_key = 'pk_live_51Abc...'; // Din rigtige key
```

### Webhook Setup (VALGFRI)
Hvis du vil have automatiske subscription updates:
```
1. Gå til: https://dashboard.stripe.com/webhooks
2. Klik "Add endpoint"
3. URL: https://rettilfamilie.com/wp-content/themes/ret-til-familie-hjemmeside/stripe-webhook.php
4. Events: customer.subscription.updated, customer.subscription.deleted
5. Kopier webhook secret
6. Indsæt i functions.php line 2700
```

---

## ✅ VERIFICER INSTALLATION

### 1. Tjek Database
Besøg: `https://rettilfamilie.com/wp-content/themes/ret-til-familie-hjemmeside/ADMIN-SYSTEM-TEST.php`

Skal vise:
```
✅ Session Status: AKTIV
✅ Current User: patrickfoersle@gmail.com
✅ Admin Check: JA (is_admin = 1)
✅ Database Tables: 29/29 oprettet
✅ REST API: Alle endpoints aktive
```

### 2. Test Login
```
1. Gå til: https://rettilfamilie.com/platform-auth
2. Log ind med patrickfoersle@gmail.com / AdminRTF2024!
3. Skal redirecte til /platform-profil
```

### 3. Test Admin Panel
```
1. Gå til: https://rettilfamilie.com/platform-admin-dashboard
2. Skal vise dashboard med statistik
3. Klik "Opret Bruger" - skal virke
```

### 4. Test Stripe (hvis konfigureret)
```
1. Opret test bruger på /platform-auth
2. Skal redirecte til Stripe checkout
3. Brug test card: 4242 4242 4242 4242
4. Success skal redirecte til /platform-profil
```

---

## 🔧 HVIS NOGET IKKE VIRKER

### Problem: "Headers already sent" fejl
**Løsning:** Åbn `functions.php` - sørg for INGEN whitespace før `<?php` på line 1

### Problem: Database tabeller oprettes ikke
**Løsning:** 
```
1. Tjek database rettigheder
2. Kør rtf-setup.php igen
3. Eller kør denne SQL manuel (se nedenfor)
```

### Problem: Admin bruger kan ikke logge ind
**Løsning:**
```
1. Besøg: https://rettilfamilie.com/wp-content/themes/ret-til-familie-hjemmeside/EMERGENCY-ADMIN-FIX.php
2. Sætter automatisk is_admin=1 for patrickfoersle@gmail.com
```

### Problem: Stripe virker ikke
**Løsning:**
```
1. Tjek at secret key og publishable key er korrekte
2. Tjek at de starter med sk_live_ og pk_live_ (eller sk_test_/pk_test_)
3. Tjek at der er ingen mellemrum eller ekstra tegn
```

### Problem: Kate AI virker ikke
**Løsning:**
```
1. Tjek at kate-ai mappen er uploaded
2. Tjek at vendor mappen er uploaded (Composer dependencies)
3. Kør rtf-setup.php igen for at re-initialisere Kate AI
```

---

## 📁 MANUEL DATABASE SETUP

Hvis automatisk setup fejler, kør denne SQL manuelt i phpMyAdmin:

```sql
-- Kør functions.php's rtf_create_tables() function
-- Eller brug WordPress admin: Plugins → Add New → Upload rtf-setup.php som plugin
```

---

## 🚀 EFTER INSTALLATION

### 1. Sikkerhed
```
✅ Skift admin password
✅ Slet test filer: debug-login.php, diagnose-system.php
✅ Aktiver SSL (HTTPS) hvis ikke allerede
✅ Backup database dagligt
```

### 2. Konfiguration
```
✅ Tilføj Stripe webhook (for auto subscription update)
✅ Test alle sider manuelt
✅ Opret test brugere
✅ Test forum, chat, Kate AI
```

### 3. Content
```
✅ Upload rapporter til /platform-rapporter
✅ Opret forum kategorier
✅ Skriv velkomst posts
✅ Test document upload
```

---

## 📊 SYSTEM OVERSIGT

### Aktive Sider (17)
```
/platform-auth - Login/Registrering ✅
/platform-profil - Brugerprofil ✅
/platform-admin-dashboard - Admin Panel ✅
/platform-chat - Chat System ✅
/platform-forum - Forum ✅
/platform-kate-ai - Kate AI ✅
/platform-vaeg - Social Væg ✅
/platform-venner - Venneliste ✅
/platform-nyheder - Nyheder ✅
/platform-dokumenter - Dokumenter ✅
/platform-billeder - Billeder ✅
/platform-sagshjaelp - Sagshjaelp ✅
/platform-rapporter - Rapporter ✅
/platform-subscription - Abonnement ✅
/platform-indstillinger - Indstillinger ✅
/platform-find-borgere - Find Brugere ✅
/borger-platform - Platform Redirect ✅
```

### REST API Endpoints (21)
```
/wp-json/kate/v1/admin/user - Create user ✅
/wp-json/kate/v1/admin/users - List users ✅
/wp-json/kate/v1/admin/subscription/{id} - Update subscription ✅
/wp-json/kate/v1/messages/* - Chat system ✅
/wp-json/kate/v1/chat - Kate AI chat ✅
/wp-json/kate/v1/reports/* - Rapporter ✅
... og 15 andre
```

### Database Tabeller (29)
```
rtf_platform_users - Brugere ✅
rtf_platform_messages - Beskeder ✅
rtf_platform_posts - Væg posts ✅
rtf_platform_forum_topics - Forum ✅
rtf_kate_chat_sessions - Kate AI ✅
rtf_stripe_subscriptions - Stripe ✅
... og 23 andre
```

---

## ✅ CHECKLIST

### Installation
- [ ] Upload tema som .zip
- [ ] Aktiver tema
- [ ] Besøg rtf-setup.php
- [ ] Verificer database tabeller oprettes
- [ ] Verificer sider oprettes
- [ ] Log ind som admin

### Konfiguration
- [ ] Tilføj Stripe secret key
- [ ] Tilføj Stripe publishable key
- [ ] Test Stripe checkout (test mode)
- [ ] Skift til live keys når klar
- [ ] Test admin bruger oprettelse
- [ ] Verificer alle sider loader

### Testing
- [ ] Test login/logout
- [ ] Test user registration
- [ ] Test admin panel
- [ ] Test chat system
- [ ] Test forum
- [ ] Test Kate AI
- [ ] Test document upload
- [ ] Test rapporter

### Sikkerhed
- [ ] Skift admin password
- [ ] Aktiver SSL/HTTPS
- [ ] Slet test filer
- [ ] Backup database
- [ ] Test restore procedure

### Go Live
- [ ] Skift til Stripe live keys
- [ ] Test payment flow
- [ ] Verificer webhook setup
- [ ] Monitor error logs
- [ ] Test all features live

---

## 🆘 SUPPORT

### Fejl Logs
Tjek WordPress debug log:
```
wp-content/debug.log
```

### Test System
```
https://rettilfamilie.com/wp-content/themes/ret-til-familie-hjemmeside/ADMIN-SYSTEM-TEST.php
```

### Emergency Admin Fix
```
https://rettilfamilie.com/wp-content/themes/ret-til-familie-hjemmeside/EMERGENCY-ADMIN-FIX.php
```

---

## 🎯 NEXT STEPS

1. ✅ **DONE:** Upload tema
2. ✅ **DONE:** Kør rtf-setup.php
3. ⏭️ **TODO:** Tilføj Stripe keys
4. ⏭️ **TODO:** Test alle funktioner
5. ⏭️ **TODO:** Go live med rigtige Stripe keys

**Installation tid: ~3 minutter**  
**Test tid: ~10 minutter**  
**Total tid til live: ~15 minutter**

---

**Status:** KLAR TIL ONE-CLICK DEPLOYMENT ✅
