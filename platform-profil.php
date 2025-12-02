<?php
/**
 * Template Name: Platform Profil
 */

get_header();
$lang = rtf_get_lang();

if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth/?lang=' . $lang));
    exit;
}

$current_user = rtf_get_current_user();
global $wpdb;
$table_privacy = $wpdb->prefix . 'rtf_platform_privacy';
$privacy = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_privacy WHERE user_id = %d", $current_user->id));

$birthday_display = $privacy && $privacy->gdpr_anonymize_birthday ? rtf_anonymize_birthday($current_user->birthday) : $current_user->birthday;

$t = array(
    'da' => array(
        'title' => 'Min Profil',
        'username' => 'Brugernavn',
        'email' => 'Email',
        'fullname' => 'Fulde navn',
        'birthday' => 'Fødselsdag',
        'member_since' => 'Medlem siden',
        'subscription' => 'Abonnement',
        'active' => 'Aktiv',
        'inactive' => 'Inaktiv',
        'manage_sub' => 'Administrer Abonnement',
        'logout' => 'Log ud',
        'platform_menu' => 'Platform Menu',
        'wall' => 'Min Væg',
        'messages' => 'Beskeder',
        'images' => 'Billeder',
        'documents' => 'Dokumenter',
        'settings' => 'Indstillinger',
        'news' => 'Nyheder',
        'forum' => 'Forum',
        'legal_help' => 'Sagshjælp',
        'kate_ai' => 'Kate AI',
        'complaint_gen' => 'Klage Generator',
        'admin_panel' => 'Admin Panel',
    ),
    'sv' => array(
        'title' => 'Min Profil',
        'username' => 'Användarnamn',
        'email' => 'E-post',
        'fullname' => 'Fullständigt namn',
        'birthday' => 'Födelsedag',
        'member_since' => 'Medlem sedan',
        'subscription' => 'Prenumeration',
        'active' => 'Aktiv',
        'inactive' => 'Inaktiv',
        'manage_sub' => 'Hantera Prenumeration',
        'logout' => 'Logga ut',
        'platform_menu' => 'Plattform Meny',
        'wall' => 'Min Vägg',
        'messages' => 'Meddelanden',
        'images' => 'Bilder',
        'documents' => 'Dokument',
        'settings' => 'Inställningar',
        'news' => 'Nyheter',
        'forum' => 'Forum',
        'legal_help' => 'Juridisk Hjälp',
        'kate_ai' => 'Kate AI',
        'complaint_gen' => 'Klagomålsgenerator',
        'admin_panel' => 'Adminpanel',
    ),
    'en' => array(
        'title' => 'My Profile',
        'username' => 'Username',
        'email' => 'Email',
        'fullname' => 'Full Name',
        'birthday' => 'Birthday',
        'member_since' => 'Member since',
        'subscription' => 'Subscription',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'manage_sub' => 'Manage Subscription',
        'logout' => 'Logout',
        'platform_menu' => 'Platform Menu',
        'wall' => 'My Wall',
        'images' => 'Images',
        'documents' => 'Documents',
        'settings' => 'Settings',
        'news' => 'News',
        'forum' => 'Forum',
        'legal_help' => 'Legal Help',
        'kate_ai' => 'Kate AI',
        'complaint_gen' => 'Complaint Generator',
        'admin_panel' => 'Admin Panel',
    )
);
$txt = $t[$lang];

// Handle wall post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_wall_post') {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'create_wall_post')) {
        wp_die('Security check failed');
    }
    
    $content = sanitize_textarea_field($_POST['content']);
    $visibility = sanitize_text_field($_POST['visibility']);
    
    if (!empty($content) && in_array($visibility, ['private', 'public'])) {
        $wpdb->insert($wpdb->prefix . 'rtf_platform_posts', [
            'user_id' => $current_user->id,
            'content' => $content,
            'visibility' => $visibility,
            'likes' => 0,
            'created_at' => current_time('mysql')
        ]);
        wp_redirect(home_url('/platform-profil/?lang=' . $lang . '#wall'));
        exit;
    }
}

// Handle post deletion
if (isset($_GET['delete_post']) && isset($_GET['nonce'])) {
    $post_id = intval($_GET['delete_post']);
    if (wp_verify_nonce($_GET['nonce'], 'delete_post_' . $post_id)) {
        $post = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rtf_platform_posts WHERE id = %d AND user_id = %d",
            $post_id,
            $current_user->id
        ));
        if ($post) {
            $wpdb->delete($wpdb->prefix . 'rtf_platform_posts', ['id' => $post_id]);
        }
    }
    wp_redirect(home_url('/platform-profil/?lang=' . $lang . '#wall'));
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    wp_redirect(home_url('/borger-platform/?lang=' . $lang));
    exit;
}
?>

<main class="platform-profil">
    <div class="container" style="max-width: 1200px; margin: 40px auto; padding: 20px;">
        
        <div style="display: grid; grid-template-columns: 300px 1fr; gap: 30px;">
            
            <!-- SIDEBAR -->
            <div class="sidebar" style="position: sticky; top: 80px; height: fit-content;">
                <div class="profile-card" style="background: var(--rtf-card); border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 20px; overflow: hidden; text-align: center;">
                    <!-- Cover Image -->
                    <div class="cover-image" style="position: relative; height: 120px; background: <?php echo $current_user->cover_image ? 'url(' . esc_url($current_user->cover_image) . ') center/cover' : 'linear-gradient(135deg, #60a5fa, #2563eb)'; ?>;">
                        <label for="coverUpload" style="position: absolute; bottom: 10px; right: 10px; background: rgba(0,0,0,0.5); color: white; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-size: 12px;">
                            <svg style="width: 16px; height: 16px; fill: currentColor; display: inline; vertical-align: middle;" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                            <?php echo $lang === 'da' ? 'Skift' : 'Byt'; ?>
                        </label>
                        <input type="file" id="coverUpload" accept="image/*" style="display: none;">
                    </div>
                    
                    <!-- Profile Avatar -->
                    <div style="position: relative; margin-top: -50px; padding: 0 30px 20px;">
                        <div class="profile-avatar" style="position: relative; width: 100px; height: 100px; background: <?php echo $current_user->profile_image ? 'url(' . esc_url($current_user->profile_image) . ') center/cover' : 'linear-gradient(135deg, #60a5fa, #2563eb)'; ?>; border: 4px solid var(--rtf-card); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 3em; color: white;">
                            <?php if (!$current_user->profile_image): ?>
                                <?php echo strtoupper(substr($current_user->username, 0, 1)); ?>
                            <?php endif; ?>
                            <label for="profileUpload" style="position: absolute; bottom: 0; right: 0; background: #0ea5e9; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                                <svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                            </label>
                            <input type="file" id="profileUpload" accept="image/*" style="display: none;">
                        </div>
                        <h2 style="margin: 15px 0 10px; color: var(--rtf-text);"><?php echo esc_html($current_user->full_name); ?></h2>
                        <p style="color: var(--rtf-muted); margin-bottom: 15px;">@<?php echo esc_html($current_user->username); ?></p>
                        <div class="subscription-badge" style="display: inline-block; padding: 8px 20px; background: <?php echo $current_user->subscription_status === 'active' ? 'linear-gradient(135deg, #38bdf8, #0ea5e9)' : '#e0f2fe'; ?>; color: <?php echo $current_user->subscription_status === 'active' ? '#ffffff' : '#1e3a8a'; ?>; border-radius: 20px; font-weight: 600;">
                            <?php echo esc_html($current_user->subscription_status === 'active' ? $txt['active'] : $txt['inactive']); ?>
                        </div>
                    </div>
                </div>

                <nav class="platform-nav" style="background: var(--rtf-card); padding: 20px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10);">
                    <h3 style="margin-bottom: 20px; color: var(--rtf-text);"><?php echo esc_html($txt['platform_menu']); ?></h3>
                    <a href="<?php echo home_url('/platform-vaeg/?lang=' . $lang); ?>" class="nav-link active" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; background: #e0f2fe; color: #1e3a8a; text-decoration: none; font-weight: 600;">
                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                        <?php echo esc_html($txt['wall']); ?>
                    </a>
                    <a href="<?php echo home_url('/platform-find-borgere/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">
                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M16.5 12c1.93 0 3.5-1.57 3.5-3.5S18.43 5 16.5 5 13 6.57 13 8.5s1.57 3.5 3.5 3.5zm-9 0c1.93 0 3.5-1.57 3.5-3.5S9.43 5 7.5 5 4 6.57 4 8.5 5.57 12 7.5 12zm0 2C5.01 14 0 15.24 0 17.75V20h15v-2.25C15 15.24 9.99 14 7.5 14zm9 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V20h9v-2.25c0-2.51-5.01-3.75-7.5-3.75z"/></svg>
                        <?php echo $lang === 'da' ? 'Find Borgere' : 'Hitta Medborgare'; ?>
                    </a>
                    <a href="<?php echo home_url('/platform-chat/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none; position: relative;">
                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                        <?php echo esc_html($txt['messages']); ?>
                        <span id="unreadBadge" style="display: none; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: #ef4444; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600;"></span>
                    </a>
                    <a href="<?php echo home_url('/platform-billeder/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">
                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                        <?php echo esc_html($txt['images']); ?>
                    </a>
                    <a href="<?php echo home_url('/platform-dokumenter/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">
                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/></svg>
                        <?php echo esc_html($txt['documents']); ?>
                    </a>
                    <a href="<?php echo home_url('/platform-nyheder/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">
                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg>
                        <?php echo esc_html($txt['news']); ?>
                    </a>
                    <a href="<?php echo home_url('/platform-forum/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">
                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h10c.55 0 1-.45 1-1z"/></svg>
                        <?php echo esc_html($txt['forum']); ?>
                    </a>
                    <a href="<?php echo home_url('/platform-sagshjælp/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">
                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        <?php echo esc_html($txt['legal_help']); ?>
                    </a>
                    <a href="<?php echo home_url('/platform-kate-ai/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">
                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        <?php echo esc_html($txt['kate_ai']); ?>
                    </a>
                    <a href="<?php echo home_url('/platform-klagegenerator/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">
                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                        <?php echo esc_html($txt['complaint_gen']); ?>
                    </a>
                    <a href="<?php echo home_url('/platform-indstillinger/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">
                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                        <?php echo esc_html($txt['settings']); ?>
                    </a>
                    <?php if (rtf_is_admin_user()): ?>
                    <a href="<?php echo home_url('/platform-admin/?lang=' . $lang); ?>" class="nav-link" style="display: flex; align-items: center; gap: 10px; padding: 12px; margin-bottom: 8px; border-radius: 8px; background: linear-gradient(135deg, #60a5fa, #2563eb); color: #ffffff; text-decoration: none; font-weight: 600;">
                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
                        <?php echo esc_html($txt['admin_panel']); ?>
                    </a>
                    <?php endif; ?>
                </nav>
                
                <script>
                // Load unread count for badge
                fetch('/wp-json/kate/v1/messages/unread-count', {
                    credentials: 'same-origin'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.unread_count > 0) {
                        const badge = document.getElementById('unreadBadge');
                        badge.textContent = data.unread_count;
                        badge.style.display = 'inline-block';
                    }
                })
                .catch(err => console.error('Error loading unread count:', err));
                </script>
            </div>

            <!-- MAIN CONTENT -->
            <div class="main-content">
                <!-- PROFILE INFO -->
                <div class="profile-info" style="background: var(--rtf-card); padding: 40px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 30px;">
                    <h1 style="margin-bottom: 30px; color: var(--rtf-text);"><?php echo esc_html($txt['title']); ?></h1>
                    
                    <form id="profileUpdateForm">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;">
                            <div>
                                <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;"><?php echo esc_html($txt['username']); ?></label>
                                <p style="font-size: 1.1em; color: var(--rtf-text);"><?php echo esc_html($current_user->username); ?></p>
                            </div>
                            
                            <div>
                                <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;"><?php echo esc_html($txt['email']); ?></label>
                                <p style="font-size: 1.1em; color: var(--rtf-text);"><?php echo esc_html($current_user->email); ?></p>
                            </div>
                            
                            <div>
                                <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;"><?php echo esc_html($txt['fullname']); ?></label>
                                <input type="text" name="full_name" value="<?php echo esc_attr($current_user->full_name); ?>" style="width: 100%; padding: 12px; border: 2px solid #e0f2fe; border-radius: 8px; font-size: 1em; color: var(--rtf-text); background: #f8fafc;">
                            </div>
                            
                            <div>
                                <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;"><?php echo esc_html($txt['birthday']); ?></label>
                                <p style="font-size: 1.1em; color: var(--rtf-text);"><?php echo esc_html($birthday_display); ?></p>
                            </div>
                            
                            <div>
                                <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;"><?php echo $lang === 'da' ? 'Sagstype' : 'Ärendetyp'; ?></label>
                                <select name="case_type" style="width: 100%; padding: 12px; border: 2px solid #e0f2fe; border-radius: 8px; font-size: 1em; color: var(--rtf-text); background: #f8fafc;">
                                    <option value=""><?php echo $lang === 'da' ? 'Vælg sagstype' : 'Välj ärendetyp'; ?></option>
                                    <option value="custody" <?php echo $current_user->case_type === 'custody' ? 'selected' : ''; ?>><?php echo $lang === 'da' ? 'Forældremyndighed' : 'Vårdnad'; ?></option>
                                    <option value="visitation" <?php echo $current_user->case_type === 'visitation' ? 'selected' : ''; ?>><?php echo $lang === 'da' ? 'Samvær' : 'Umgänge'; ?></option>
                                    <option value="divorce" <?php echo $current_user->case_type === 'divorce' ? 'selected' : ''; ?>><?php echo $lang === 'da' ? 'Skilsmisse' : 'Skilsmässa'; ?></option>
                                    <option value="support" <?php echo $current_user->case_type === 'support' ? 'selected' : ''; ?>><?php echo $lang === 'da' ? 'Børnebidrag' : 'Barnbidrag'; ?></option>
                                    <option value="other" <?php echo $current_user->case_type === 'other' ? 'selected' : ''; ?>><?php echo $lang === 'da' ? 'Andet' : 'Annat'; ?></option>
                                </select>
                            </div>
                            
                            <div>
                                <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;"><?php echo $lang === 'da' ? 'Land' : 'Land'; ?></label>
                                <select name="country" style="width: 100%; padding: 12px; border: 2px solid #e0f2fe; border-radius: 8px; font-size: 1em; color: var(--rtf-text); background: #f8fafc;">
                                    <option value="DK" <?php echo $current_user->country === 'DK' ? 'selected' : ''; ?>>Danmark</option>
                                    <option value="SE" <?php echo $current_user->country === 'SE' ? 'selected' : ''; ?>>Sverige</option>
                                    <option value="NO" <?php echo $current_user->country === 'NO' ? 'selected' : ''; ?>>Norge</option>
                                </select>
                            </div>
                            
                            <div>
                                <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;"><?php echo $lang === 'da' ? 'Alder' : 'Ålder'; ?></label>
                                <input type="number" name="age" value="<?php echo esc_attr($current_user->age); ?>" min="18" max="120" style="width: 100%; padding: 12px; border: 2px solid #e0f2fe; border-radius: 8px; font-size: 1em; color: var(--rtf-text); background: #f8fafc;">
                            </div>
                            
                            <div>
                                <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;"><?php echo esc_html($txt['member_since']); ?></label>
                                <p style="font-size: 1.1em; color: var(--rtf-text);"><?php echo rtf_format_date($current_user->created_at); ?></p>
                            </div>
                            
                            <div style="grid-column: 1 / -1;">
                                <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;"><?php echo $lang === 'da' ? 'Kort biografi' : 'Kort biografi'; ?></label>
                                <textarea name="bio" rows="4" style="width: 100%; padding: 12px; border: 2px solid #e0f2fe; border-radius: 8px; font-size: 1em; color: var(--rtf-text); background: #f8fafc; resize: vertical;" placeholder="<?php echo $lang === 'da' ? 'Fortæl lidt om dig selv...' : 'Berätta lite om dig själv...'; ?>"><?php echo esc_textarea($current_user->bio); ?></textarea>
                            </div>
                            
                            <div>
                                <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;"><?php echo esc_html($txt['subscription']); ?></label>
                                <p style="font-size: 1.1em; color: var(--rtf-text);"><?php echo esc_html($current_user->subscription_status === 'active' ? $txt['active'] : $txt['inactive']); ?></p>
                            </div>
                        </div>

                        <div style="display: flex; gap: 15px;">
                            <button type="submit" class="btn-primary" style="padding: 15px 30px; border: none; cursor: pointer;">
                                <?php echo $lang === 'da' ? 'Gem ændringer' : 'Spara ändringar'; ?>
                            </button>
                            <a href="<?php echo home_url('/platform-subscription/?lang=' . $lang); ?>" class="btn-secondary" style="padding: 15px 30px; text-decoration: none; display: inline-block;">
                                <?php echo esc_html($txt['manage_sub']); ?>
                            </a>
                            <a href="?logout=1" class="btn-secondary" style="padding: 15px 30px; text-decoration: none; display: inline-block;">
                                <?php echo esc_html($txt['logout']); ?>
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- DASHBOARD STATISTICS -->
                <div class="dashboard-stats" style="background: var(--rtf-card); padding: 40px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10);">
                    <h2 style="margin-bottom: 25px; color: var(--rtf-text); display: flex; align-items: center; gap: 10px;">
                        <svg style="width: 24px; height: 24px; fill: currentColor;" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
                        <?php echo $lang === 'da' ? 'Min Aktivitet' : 'Min Aktivitet'; ?>
                    </h2>
                    
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
                        <?php
                        // Get user statistics
                        $posts_count = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_posts WHERE user_id = %d",
                            $current_user->id
                        ));
                        
                        $messages_sent = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_messages WHERE sender_id = %d",
                            $current_user->id
                        ));
                        
                        $kate_sessions = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(DISTINCT session_id) FROM {$wpdb->prefix}rtf_kate_chat WHERE user_id = %d",
                            $current_user->id
                        ));
                        ?>
                        
                        <div style="background: linear-gradient(135deg, #60a5fa, #2563eb); padding: 25px; border-radius: 12px; color: white; text-align: center;">
                            <div style="font-size: 2.5em; font-weight: bold; margin-bottom: 10px;"><?php echo $posts_count; ?></div>
                            <div style="font-size: 0.9em; opacity: 0.9;"><?php echo $lang === 'da' ? 'Opslag' : 'Inlägg'; ?></div>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #38bdf8, #0ea5e9); padding: 25px; border-radius: 12px; color: white; text-align: center;">
                            <div style="font-size: 2.5em; font-weight: bold; margin-bottom: 10px;"><?php echo $messages_sent; ?></div>
                            <div style="font-size: 0.9em; opacity: 0.9;"><?php echo $lang === 'da' ? 'Beskeder' : 'Meddelanden'; ?></div>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #8b5cf6, #6366f1); padding: 25px; border-radius: 12px; color: white; text-align: center;">
                            <div style="font-size: 2.5em; font-weight: bold; margin-bottom: 10px;"><?php echo $kate_sessions; ?></div>
                            <div style="font-size: 0.9em; opacity: 0.9;">Kate AI Sessioner</div>
                        </div>
                    </div>
                    
                    <!-- Recent Kate AI Usage -->
                    <h3 style="margin-bottom: 20px; color: var(--rtf-text); display: flex; align-items: center; gap: 10px;">
                        <svg style="width: 22px; height: 22px; fill: currentColor;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        <?php echo $lang === 'da' ? 'Seneste Kate AI Brug' : 'Senaste Kate AI Användning'; ?>
                    </h3>
                    <div style="background: #f8fafc; padding: 20px; border-radius: 12px;">
                        <?php
                        $recent_kate = $wpdb->get_results($wpdb->prepare(
                            "SELECT session_id, created_at 
                             FROM {$wpdb->prefix}rtf_kate_chat 
                             WHERE user_id = %d 
                             GROUP BY session_id 
                             ORDER BY MAX(created_at) DESC 
                             LIMIT 5",
                            $current_user->id
                        ));
                        
                        if (empty($recent_kate)): ?>
                            <p style="color: var(--rtf-muted); text-align: center;"><?php echo $lang === 'da' ? 'Ingen Kate AI sessioner endnu' : 'Inga Kate AI sessioner ännu'; ?></p>
                        <?php else: ?>
                            <?php foreach ($recent_kate as $session): ?>
                                <div style="padding: 12px; border-bottom: 1px solid #e0f2fe; display: flex; justify-content: space-between;">
                                    <span style="font-family: monospace; font-size: 0.9em; color: #475569;"><?php echo substr($session->session_id, 0, 30); ?>...</span>
                                    <span style="color: var(--rtf-muted); font-size: 0.9em;"><?php echo rtf_time_ago($session->created_at); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- WALL TABS SECTION -->
                <div class="wall-section" style="background: var(--rtf-card); padding: 40px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-top: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <h2 style="margin: 0; color: var(--rtf-text);">
                            <?php echo $lang === 'da' ? 'Min Væg' : 'Min Vägg'; ?>
                        </h2>
                        <button onclick="viewAsOthers()" style="padding: 10px 20px; background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                            <svg style="width: 18px; height: 18px; fill: currentColor;" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                            <?php echo $lang === 'da' ? 'Se som andre' : 'Se som andra'; ?>
                        </button>
                    </div>
                    
                    <!-- Wall Tabs Navigation -->
                    <div class="wall-tabs-nav" style="display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 2px solid #e2e8f0;">
                        <button class="wall-tab-button active" onclick="showWallTab('private')" style="background: none; border: none; padding: 1rem 1.5rem; cursor: pointer; font-size: 1rem; font-weight: 600; color: #64748b; border-bottom: 3px solid transparent; margin-bottom: -2px; transition: all 0.3s ease;">
                            🔒 <?php echo $lang === 'da' ? 'Privat Væg' : 'Privat Vägg'; ?>
                        </button>
                        <button class="wall-tab-button" onclick="showWallTab('public')" style="background: none; border: none; padding: 1rem 1.5rem; cursor: pointer; font-size: 1rem; font-weight: 600; color: #64748b; border-bottom: 3px solid transparent; margin-bottom: -2px; transition: all 0.3s ease;">
                            🌐 <?php echo $lang === 'da' ? 'Offentlig Væg' : 'Offentlig Vägg'; ?>
                        </button>
                    </div>
                    
                    <!-- PRIVATE WALL TAB -->
                    <div id="wall-tab-private" class="wall-tab-content">
                        <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
                            <h4 style="margin: 0 0 0.5rem 0; color: #92400e; display: flex; align-items: center; gap: 0.5rem;">
                                <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                                🔒 <?php echo $lang === 'da' ? 'Privat Væg - Kun du kan se dette' : 'Privat Vägg - Bara du kan se detta'; ?>
                            </h4>
                            <p style="margin: 0; color: #92400e; font-size: 0.9rem;">
                                <?php echo $lang === 'da' ? 'Dine private noter og tanker. Ingen andre kan se dette.' : 'Dina privata anteckningar och tankar. Ingen annan kan se detta.'; ?>
                            </p>
                        </div>
                        
                        <!-- New Private Post Form -->
                        <form method="POST" action="" style="margin-bottom: 2rem;">
                            <input type="hidden" name="action" value="create_wall_post">
                            <input type="hidden" name="visibility" value="private">
                            <?php wp_nonce_field('create_wall_post'); ?>
                            <textarea name="content" rows="4" placeholder="<?php echo $lang === 'da' ? 'Skriv dine private noter her...' : 'Skriv dina privata anteckningar här...'; ?>" required style="width: 100%; padding: 1rem; border: 2px solid #e0f2fe; border-radius: 8px; font-size: 1rem; font-family: inherit; resize: vertical;"></textarea>
                            <button type="submit" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #60a5fa, #2563eb); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                📝 <?php echo $lang === 'da' ? 'Gem Note' : 'Spara Anteckning'; ?>
                            </button>
                        </form>
                        
                        <!-- Private Posts List -->
                        <div class="posts-list">
                            <?php
                            $private_posts = $wpdb->get_results($wpdb->prepare(
                                "SELECT * FROM {$wpdb->prefix}rtf_platform_posts WHERE user_id = %d AND visibility = 'private' ORDER BY created_at DESC LIMIT 20",
                                $current_user->id
                            ));
                            
                            if (empty($private_posts)): ?>
                                <p style="text-align: center; color: var(--rtf-muted); padding: 2rem;">
                                    <?php echo $lang === 'da' ? 'Ingen private noter endnu. Skriv din første note!' : 'Inga privata anteckningar ännu. Skriv din första anteckning!'; ?>
                                </p>
                            <?php else: ?>
                                <?php foreach ($private_posts as $post): ?>
                                    <div class="wall-post" style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem; border: 1px solid #e0f2fe;">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                            <span style="color: var(--rtf-muted); font-size: 0.9rem;">
                                                <?php echo rtf_format_date($post->created_at); ?>
                                            </span>
                                            <a href="?delete_post=<?php echo $post->id; ?>&nonce=<?php echo wp_create_nonce('delete_post_' . $post->id); ?>" onclick="return confirm('<?php echo $lang === 'da' ? 'Slet denne note?' : 'Radera denna anteckning?'; ?>')" style="color: #ef4444; text-decoration: none; font-size: 0.9rem;">
                                                🗑️ <?php echo $lang === 'da' ? 'Slet' : 'Radera'; ?>
                                            </a>
                                        </div>
                                        <p style="margin: 0; color: var(--rtf-text); line-height: 1.6; white-space: pre-wrap;"><?php echo esc_html($post->content); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- PUBLIC WALL TAB -->
                    <div id="wall-tab-public" class="wall-tab-content" style="display: none;">
                        <div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border: 2px solid #2563eb; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
                            <h4 style="margin: 0 0 0.5rem 0; color: #1e3a8a; display: flex; align-items: center; gap: 0.5rem;">
                                <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                                🌐 <?php echo $lang === 'da' ? 'Offentlig Væg - Andre kan se dette' : 'Offentlig Vägg - Andra kan se detta'; ?>
                            </h4>
                            <p style="margin: 0; color: #1e3a8a; font-size: 0.9rem;">
                                <?php echo $lang === 'da' ? 'Del dine opslag med andre på platformen.' : 'Dela dina inlägg med andra på plattformen.'; ?>
                            </p>
                        </div>
                        
                        <!-- New Public Post Form -->
                        <form method="POST" action="" style="margin-bottom: 2rem;">
                            <input type="hidden" name="action" value="create_wall_post">
                            <input type="hidden" name="visibility" value="public">
                            <?php wp_nonce_field('create_wall_post'); ?>
                            <textarea name="content" rows="4" placeholder="<?php echo $lang === 'da' ? 'Del noget med andre...' : 'Dela något med andra...'; ?>" required style="width: 100%; padding: 1rem; border: 2px solid #e0f2fe; border-radius: 8px; font-size: 1rem; font-family: inherit; resize: vertical;"></textarea>
                            <button type="submit" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #60a5fa, #2563eb); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                                🌐 <?php echo $lang === 'da' ? 'Del Offentligt' : 'Dela Offentligt'; ?>
                            </button>
                        </form>
                        
                        <!-- Public Posts List -->
                        <div class="posts-list">
                            <?php
                            $public_posts = $wpdb->get_results($wpdb->prepare(
                                "SELECT * FROM {$wpdb->prefix}rtf_platform_posts WHERE user_id = %d AND visibility = 'public' ORDER BY created_at DESC LIMIT 20",
                                $current_user->id
                            ));
                            
                            if (empty($public_posts)): ?>
                                <p style="text-align: center; color: var(--rtf-muted); padding: 2rem;">
                                    <?php echo $lang === 'da' ? 'Ingen offentlige opslag endnu. Del dit første opslag!' : 'Inga offentliga inlägg ännu. Dela ditt första inlägg!'; ?>
                                </p>
                            <?php else: ?>
                                <?php foreach ($public_posts as $post): ?>
                                    <div class="wall-post" style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem; border: 1px solid #e0f2fe;">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                            <div style="display: flex; align-items: center; gap: 1rem;">
                                                <span style="color: var(--rtf-muted); font-size: 0.9rem;">
                                                    <?php echo rtf_format_date($post->created_at); ?>
                                                </span>
                                                <span style="background: #dbeafe; color: #1e3a8a; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600;">
                                                    👁️ <?php echo $lang === 'da' ? 'Offentlig' : 'Offentlig'; ?>
                                                </span>
                                            </div>
                                            <a href="?delete_post=<?php echo $post->id; ?>&nonce=<?php echo wp_create_nonce('delete_post_' . $post->id); ?>" onclick="return confirm('<?php echo $lang === 'da' ? 'Slet dette opslag?' : 'Radera detta inlägg?'; ?>')" style="color: #ef4444; text-decoration: none; font-size: 0.9rem;">
                                                🗑️ <?php echo $lang === 'da' ? 'Slet' : 'Radera'; ?>
                                            </a>
                                        </div>
                                        <p style="margin: 0; color: var(--rtf-text); line-height: 1.6; white-space: pre-wrap;"><?php echo esc_html($post->content); ?></p>
                                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e0f2fe; display: flex; gap: 1rem;">
                                            <button onclick="likePost(<?php echo $post->id; ?>)" style="background: none; border: none; color: #475569; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; transition: all 0.2s;">
                                                ❤️ <span><?php echo $post->likes; ?></span>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<style>
.wall-tab-button.active {
    color: #2563eb !important;
    border-bottom-color: #2563eb !important;
}
.wall-tab-button:hover {
    color: #2563eb !important;
    background: rgba(37, 99, 235, 0.05) !important;
}
</style>

<script>
// Wall Tab Switching
function showWallTab(tab) {
    // Hide all tabs
    document.querySelectorAll('.wall-tab-content').forEach(el => el.style.display = 'none');
    
    // Remove active class from all buttons
    document.querySelectorAll('.wall-tab-button').forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab
    document.getElementById('wall-tab-' + tab).style.display = 'block';
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

// View As Others
function viewAsOthers() {
    const url = '<?php echo home_url('/platform-profil-view/?user_id=' . $current_user->id . '&lang=' . $lang); ?>';
    window.open(url, '_blank');
}

// Like Post
function likePost(postId) {
    fetch('/wp-json/kate/v1/like-post', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ post_id: postId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// Profile Image Upload
document.getElementById('profileUpload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('image', file);
    formData.append('type', 'profile');
    
    fetch('/wp-json/kate/v1/upload-profile-image', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to show new image
        } else {
            alert(data.message || 'Upload fejlede');
        }
    })
    .catch(err => {
        console.error('Upload error:', err);
        alert('Upload fejlede. Prøv igen.');
    });
});

// Cover Image Upload
document.getElementById('coverUpload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('image', file);
    formData.append('type', 'cover');
    
    fetch('/wp-json/kate/v1/upload-profile-image', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to show new cover
        } else {
            alert(data.message || 'Upload fejlede');
        }
    })
    .catch(err => {
        console.error('Upload error:', err);
        alert('Upload fejlede. Prøv igen.');
    });
});

// Profile Update Form
document.getElementById('profileUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    fetch('/wp-json/kate/v1/update-profile', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert('<?php echo $lang === "da" ? "Profil opdateret!" : "Profil uppdaterad!"; ?>');
            location.reload();
        } else {
            alert(result.message || 'Opdatering fejlede');
        }
    })
    .catch(err => {
        console.error('Update error:', err);
        alert('Opdatering fejlede. Prøv igen.');
    });
});
</script>

<?php get_footer(); ?>
