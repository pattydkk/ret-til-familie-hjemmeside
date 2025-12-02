<?php
/**
 * Platform Sidebar Navigation
 * Reusable sidebar component for all platform pages
 */

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$language = get_user_meta($user_id, 'language_preference', true) ?: 'da_DK';
$is_danish = ($language === 'da_DK');
$lang = rtf_get_lang();

// Get current page
$current_url = $_SERVER['REQUEST_URI'];
$rtf_user = rtf_get_current_user();
$is_admin = rtf_is_admin_user();
?>

<nav class="platform-nav" style="background: var(--rtf-card); padding: 20px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); position: sticky; top: 80px; height: fit-content;">
    <h3 style="margin-bottom: 20px; color: var(--rtf-text);"><?php echo $is_danish ? 'Platform Menu' : 'Plattform Meny'; ?></h3>
    
    <a href="<?php echo home_url('/platform-profil/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; <?php echo strpos($current_url, 'platform-profil') !== false ? 'background: #e0f2fe; color: #1e3a8a;' : 'color: var(--rtf-text);'; ?> text-decoration: none; <?php echo strpos($current_url, 'platform-profil') !== false ? 'font-weight: 600;' : ''; ?>">
        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        <?php echo $is_danish ? 'Min Profil' : 'Min Profil'; ?>
    </a>
    
    <a href="<?php echo home_url('/platform-vaeg/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; <?php echo strpos($current_url, 'platform-vaeg') !== false ? 'background: #e0f2fe; color: #1e3a8a;' : 'color: var(--rtf-text);'; ?> text-decoration: none; <?php echo strpos($current_url, 'platform-vaeg') !== false ? 'font-weight: 600;' : ''; ?>">
        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
        <?php echo $is_danish ? 'Væg' : 'Vägg'; ?>
    </a>
    
    <a href="<?php echo home_url('/platform-chat/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; <?php echo strpos($current_url, 'platform-chat') !== false ? 'background: #e0f2fe; color: #1e3a8a;' : 'color: var(--rtf-text);'; ?> text-decoration: none; <?php echo strpos($current_url, 'platform-chat') !== false ? 'font-weight: 600;' : ''; ?> position: relative;">
        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
        <?php echo $is_danish ? 'Beskeder' : 'Meddelanden'; ?>
        <span id="unreadBadge" style="display: none; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: #ef4444; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600;"></span>
    </a>
    
    <a href="<?php echo home_url('/platform-billeder/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; <?php echo strpos($current_url, 'platform-billeder') !== false ? 'background: #e0f2fe; color: #1e3a8a;' : 'color: var(--rtf-text);'; ?> text-decoration: none; <?php echo strpos($current_url, 'platform-billeder') !== false ? 'font-weight: 600;' : ''; ?>">
        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
        <?php echo $is_danish ? 'Billeder' : 'Bilder'; ?>
    </a>
    
    <a href="<?php echo home_url('/platform-dokumenter/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; <?php echo strpos($current_url, 'platform-dokumenter') !== false ? 'background: #e0f2fe; color: #1e3a8a;' : 'color: var(--rtf-text);'; ?> text-decoration: none; <?php echo strpos($current_url, 'platform-dokumenter') !== false ? 'font-weight: 600;' : ''; ?>">
        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/></svg>
        <?php echo $is_danish ? 'Dokumenter' : 'Dokument'; ?>
    </a>
    
    <a href="<?php echo home_url('/platform-find-borgere/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; <?php echo strpos($current_url, 'platform-find-borgere') !== false ? 'background: #e0f2fe; color: #1e3a8a;' : 'color: var(--rtf-text);'; ?> text-decoration: none; <?php echo strpos($current_url, 'platform-find-borgere') !== false ? 'font-weight: 600;' : ''; ?>">
        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M16.5 12c1.93 0 3.5-1.57 3.5-3.5S18.43 5 16.5 5 13 6.57 13 8.5s1.57 3.5 3.5 3.5zm-9 0c1.93 0 3.5-1.57 3.5-3.5S9.43 5 7.5 5 4 6.57 4 8.5 5.57 12 7.5 12zm0 2C5.01 14 0 15.24 0 17.75V20h15v-2.25C15 15.24 9.99 14 7.5 14zm9 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V20h9v-2.25c0-2.51-5.01-3.75-7.5-3.75z"/></svg>
        <?php echo $is_danish ? 'Find Borgere' : 'Hitta Medborgare'; ?>
    </a>
    
    <a href="<?php echo home_url('/platform-venner/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; <?php echo strpos($current_url, 'platform-venner') !== false ? 'background: #e0f2fe; color: #1e3a8a;' : 'color: var(--rtf-text);'; ?> text-decoration: none; <?php echo strpos($current_url, 'platform-venner') !== false ? 'font-weight: 600;' : ''; ?>">
        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        <?php echo $is_danish ? 'Venner' : 'Vänner'; ?>
    </a>
    
    <a href="<?php echo home_url('/platform-nyheder/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; <?php echo strpos($current_url, 'platform-nyheder') !== false ? 'background: #e0f2fe; color: #1e3a8a;' : 'color: var(--rtf-text);'; ?> text-decoration: none; <?php echo strpos($current_url, 'platform-nyheder') !== false ? 'font-weight: 600;' : ''; ?>">
        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg>
        <?php echo $is_danish ? 'Nyheder' : 'Nyheter'; ?>
    </a>
    
    <a href="<?php echo home_url('/platform-forum/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; <?php echo strpos($current_url, 'platform-forum') !== false ? 'background: #e0f2fe; color: #1e3a8a;' : 'color: var(--rtf-text);'; ?> text-decoration: none; <?php echo strpos($current_url, 'platform-forum') !== false ? 'font-weight: 600;' : ''; ?>">
        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h10c.55 0 1-.45 1-1z"/></svg>
        Forum
    </a>
    
    <a href="<?php echo home_url('/platform-sagshjaelp/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; <?php echo strpos($current_url, 'platform-sagshjaelp') !== false ? 'background: #e0f2fe; color: #1e3a8a;' : 'color: var(--rtf-text);'; ?> text-decoration: none; <?php echo strpos($current_url, 'platform-sagshjaelp') !== false ? 'font-weight: 600;' : ''; ?>">
        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
        <?php echo $is_danish ? 'Sagshjælp' : 'Ärendehjälp'; ?>
    </a>
    
    <a href="<?php echo home_url('/platform-kate-ai/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; <?php echo strpos($current_url, 'platform-kate-ai') !== false ? 'background: #e0f2fe; color: #1e3a8a;' : 'color: var(--rtf-text);'; ?> text-decoration: none; <?php echo strpos($current_url, 'platform-kate-ai') !== false ? 'font-weight: 600;' : ''; ?>">
        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
        Kate AI
    </a>
    
    <a href="<?php echo home_url('/platform-klagegenerator/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; <?php echo strpos($current_url, 'platform-klagegenerator') !== false ? 'background: #e0f2fe; color: #1e3a8a;' : 'color: var(--rtf-text);'; ?> text-decoration: none; <?php echo strpos($current_url, 'platform-klagegenerator') !== false ? 'font-weight: 600;' : ''; ?>">
        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
        <?php echo $is_danish ? 'Klage Generator' : 'Klagomålsgenerator'; ?>
    </a>
    
    <a href="<?php echo home_url('/platform-indstillinger/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; <?php echo strpos($current_url, 'platform-indstillinger') !== false ? 'background: #e0f2fe; color: #1e3a8a;' : 'color: var(--rtf-text);'; ?> text-decoration: none; <?php echo strpos($current_url, 'platform-indstillinger') !== false ? 'font-weight: 600;' : ''; ?>">
        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
        <?php echo $is_danish ? 'Indstillinger' : 'Inställningar'; ?>
    </a>
    
    <?php if ($is_admin): ?>
    <a href="<?php echo home_url('/platform-admin-dashboard/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; background: linear-gradient(135deg, #60a5fa, #2563eb); color: #ffffff; text-decoration: none; font-weight: 600;">
        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
        <?php echo $is_danish ? 'Admin Panel' : 'Admin Panel'; ?>
    </a>
    <?php endif; ?>
</nav>

<script>
// Load unread message count
fetch('/wp-json/kate/v1/messages/unread-count', {
    credentials: 'same-origin'
})
.then(res => res.json())
.then(data => {
    if (data.unread_count > 0) {
        const badge = document.getElementById('unreadBadge');
        if (badge) {
            badge.textContent = data.unread_count;
            badge.style.display = 'inline-block';
        }
    }
})
.catch(err => console.error('Error loading unread count:', err));
</script>
