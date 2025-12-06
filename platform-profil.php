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

// Check for payment success message
$payment_success = isset($_GET['payment']) && $_GET['payment'] === 'success';
$session_id = isset($_GET['session_id']) ? sanitize_text_field($_GET['session_id']) : null;

// DEBUG: Force refresh user data if ?refresh=1
if (isset($_GET['refresh']) && $_GET['refresh'] == '1') {
    // Clear any potential caches
    if (isset($_SESSION['rtf_user_cache'])) {
        unset($_SESSION['rtf_user_cache']);
    }
}

rtf_require_subscription();

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
    <div class="platform-container" style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; max-width: 1400px; margin: 0 auto; padding: 2rem;">
        
        <?php get_template_part('template-parts/platform-sidebar'); ?>
        
        <div class="platform-content" style="min-width: 0;">
            
            <?php if (isset($_GET['payment']) && $_GET['payment'] === 'success'): ?>
            <!-- PAYMENT SUCCESS BANNER -->
            <div style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 20px 30px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <svg style="width: 32px; height: 32px; fill: currentColor; flex-shrink: 0;" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <div>
                        <h3 style="margin: 0 0 5px 0; font-size: 1.3em;">
                            <?php echo $lang === 'da' ? '🎉 Betaling gennemført!' : '🎉 Betalning genomförd!'; ?>
                        </h3>
                        <p style="margin: 0; opacity: 0.95;">
                            <?php 
                            if ($lang === 'da') {
                                echo 'Dit abonnement er nu aktivt! Du har fuld adgang til alle platformens funktioner.';
                            } else {
                                echo 'Din prenumeration är nu aktiv! Du har full tillgång till alla plattformens funktioner.';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['payment']) && $_GET['payment'] === 'cancelled'): ?>
            <!-- PAYMENT CANCELLED BANNER -->
            <div style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 20px 30px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <svg style="width: 32px; height: 32px; fill: currentColor; flex-shrink: 0;" viewBox="0 0 24 24">
                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg>
                    <div>
                        <h3 style="margin: 0 0 5px 0; font-size: 1.3em;">
                            <?php echo $lang === 'da' ? 'Betaling annulleret' : 'Betalning avbruten'; ?>
                        </h3>
                        <p style="margin: 0; opacity: 0.95;">
                            <?php 
                            if ($lang === 'da') {
                                echo 'Du afbrød betalingen. Få fuld adgang ved at gennemføre betalingen.';
                            } else {
                                echo 'Du avbröt betalningen. Få full åtkomst genom att genomföra betalningen.';
                            }
                            ?>
                            <a href="<?php echo home_url('/platform-subscription/?lang=' . $lang); ?>" style="color: white; text-decoration: underline; margin-left: 10px; font-weight: 600;">
                                <?php echo $lang === 'da' ? 'Prøv igen' : 'Försök igen'; ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Profile Card (at top of content area) -->
            <div class="profile-card" style="background: var(--rtf-card); border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 30px; overflow: hidden; text-align: center;">
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

            <!-- MAIN CONTENT -->
            <div class="main-content">
                <!-- PROFILE INFO -->
                <div class="profile-info" style="background: var(--rtf-card); padding: 40px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                        <h1 style="margin: 0; color: var(--rtf-text);"><?php echo esc_html($txt['title']); ?></h1>
                        <a href="<?php echo home_url('/platform-indstillinger/?lang=' . $lang); ?>" class="btn-primary" style="padding: 12px 24px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                            <svg style="width: 18px; height: 18px; fill: currentColor;" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                            <?php echo $lang === 'da' ? 'Rediger Profil' : 'Redigera Profil'; ?>
                        </a>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                        <div>
                            <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px; font-size: 0.9em;"><?php echo esc_html($txt['username']); ?></label>
                            <p style="font-size: 1.1em; color: var(--rtf-text); margin: 0;"><?php echo esc_html($current_user->username); ?></p>
                        </div>
                        
                        <div>
                            <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px; font-size: 0.9em;"><?php echo esc_html($txt['email']); ?></label>
                            <p style="font-size: 1.1em; color: var(--rtf-text); margin: 0;"><?php echo esc_html($current_user->email); ?></p>
                        </div>
                        
                        <div>
                            <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px; font-size: 0.9em;"><?php echo esc_html($txt['fullname']); ?></label>
                            <p style="font-size: 1.1em; color: var(--rtf-text); margin: 0;"><?php echo esc_html($current_user->full_name); ?></p>
                        </div>
                        
                        <div>
                            <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px; font-size: 0.9em;"><?php echo esc_html($txt['birthday']); ?></label>
                            <p style="font-size: 1.1em; color: var(--rtf-text); margin: 0;"><?php echo esc_html($birthday_display); ?></p>
                        </div>
                        
                        <?php if ($current_user->case_type): ?>
                        <div>
                            <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px; font-size: 0.9em;"><?php echo $lang === 'da' ? 'Sagstype' : 'Ärendetyp'; ?></label>
                            <p style="font-size: 1.1em; color: var(--rtf-text); margin: 0;">
                                <?php 
                                $case_types = [
                                    'custody' => $lang === 'da' ? 'Forældremyndighed' : 'Vårdnad',
                                    'visitation' => $lang === 'da' ? 'Samvær' : 'Umgänge',
                                    'divorce' => $lang === 'da' ? 'Skilsmisse' : 'Skilsmässa',
                                    'support' => $lang === 'da' ? 'Børnebidrag' : 'Barnbidrag',
                                    'other' => $lang === 'da' ? 'Andet' : 'Annat'
                                ];
                                echo esc_html($case_types[$current_user->case_type] ?? $current_user->case_type);
                                ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px; font-size: 0.9em;"><?php echo $lang === 'da' ? 'Land' : 'Land'; ?></label>
                            <p style="font-size: 1.1em; color: var(--rtf-text); margin: 0;">
                                <?php 
                                $countries = ['DK' => 'Danmark', 'SE' => 'Sverige', 'NO' => 'Norge'];
                                echo esc_html($countries[$current_user->country] ?? $current_user->country);
                                ?>
                            </p>
                        </div>
                        
                        <?php if ($current_user->city): ?>
                        <div>
                            <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px; font-size: 0.9em;"><?php echo $lang === 'da' ? 'By' : 'Stad'; ?></label>
                            <p style="font-size: 1.1em; color: var(--rtf-text); margin: 0;"><?php echo esc_html($current_user->city); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div>
                            <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px; font-size: 0.9em;"><?php echo esc_html($txt['member_since']); ?></label>
                            <p style="font-size: 1.1em; color: var(--rtf-text); margin: 0;"><?php echo rtf_format_date($current_user->created_at); ?></p>
                        </div>
                        
                        <div>
                            <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px; font-size: 0.9em;"><?php echo esc_html($txt['subscription']); ?></label>
                            <p style="font-size: 1.1em; margin: 0;">
                                <span style="padding: 6px 12px; border-radius: 20px; font-size: 0.9em; font-weight: 600; <?php echo $current_user->subscription_status === 'active' ? 'background: #d1fae5; color: #065f46;' : 'background: #fee2e2; color: #991b1b;'; ?>">
                                    <?php echo esc_html($current_user->subscription_status === 'active' ? $txt['active'] : $txt['inactive']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <?php if ($current_user->bio): ?>
                    <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e0f2fe;">
                        <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 12px; font-size: 0.9em;"><?php echo $lang === 'da' ? 'Om mig' : 'Om mig'; ?></label>
                        <p style="font-size: 1em; color: var(--rtf-text); line-height: 1.6; margin: 0;"><?php echo nl2br(esc_html($current_user->bio)); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 15px; margin-top: 30px; padding-top: 30px; border-top: 2px solid #e0f2fe;">
                        <a href="<?php echo home_url('/platform-subscription/?lang=' . $lang); ?>" class="btn-secondary" style="padding: 12px 24px; text-decoration: none; display: inline-block;">
                            <?php echo esc_html($txt['manage_sub']); ?>
                        </a>
                        <a href="?logout=1" class="btn-secondary" style="padding: 12px 24px; text-decoration: none; display: inline-block;">
                            <?php echo esc_html($txt['logout']); ?>
                        </a>
                    </div>
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
                    
                    <!-- Document Analysis Overview -->
                    <h3 style="margin: 30px 0 20px; color: var(--rtf-text); display: flex; align-items: center; gap: 10px;">
                        <svg style="width: 22px; height: 22px; fill: currentColor;" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                        <?php echo $lang === 'da' ? 'Dokument Analyser' : 'Dokumentanalyser'; ?>
                    </h3>
                    <div style="background: #f8fafc; padding: 20px; border-radius: 12px;">
                        <?php
                        // Get document analyses with findings
                        $analyses = $wpdb->get_results($wpdb->prepare(
                            "SELECT da.*, d.file_name, d.created_at as doc_date
                             FROM {$wpdb->prefix}rtf_platform_document_analysis da
                             JOIN {$wpdb->prefix}rtf_platform_documents d ON da.document_id = d.id
                             WHERE da.user_id = %d 
                             ORDER BY da.analyzed_at DESC 
                             LIMIT 10",
                            $current_user->id
                        ));
                        
                        if (empty($analyses)): ?>
                            <div style="text-align: center; padding: 40px 20px;">
                                <svg style="width: 64px; height: 64px; fill: #cbd5e1; margin-bottom: 15px;" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                <p style="color: var(--rtf-muted); margin-bottom: 15px;"><?php echo $lang === 'da' ? 'Ingen dokument analyser endnu' : 'Inga dokumentanalyser ännu'; ?></p>
                                <a href="<?php echo home_url('/platform-dokumenter/?lang='.$lang); ?>" style="display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                                    📄 <?php echo $lang === 'da' ? 'Upload første dokument' : 'Ladda upp första dokumentet'; ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($analyses as $analysis): 
                                $findings = json_decode($analysis->key_findings, true) ?? [];
                                $violations = json_decode($analysis->legal_violations, true) ?? [];
                                $recommendations = json_decode($analysis->recommendations, true) ?? [];
                                $finding_count = count($findings) + count($violations);
                            ?>
                                <div style="padding: 20px; margin-bottom: 15px; background: white; border-radius: 10px; border-left: 4px solid <?php echo $finding_count > 0 ? '#ef4444' : '#10b981'; ?>; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                        <div>
                                            <h4 style="margin: 0 0 5px; color: var(--rtf-text); font-size: 1.1em;">
                                                📄 <?php echo esc_html($analysis->file_name); ?>
                                            </h4>
                                            <div style="font-size: 0.85em; color: var(--rtf-muted);">
                                                Analyseret: <?php echo rtf_time_ago($analysis->analyzed_at); ?>
                                                <?php if ($analysis->confidence_score): ?>
                                                    | Sikkerhed: <?php echo round($analysis->confidence_score); ?>%
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <span style="padding: 4px 12px; background: <?php echo $finding_count > 0 ? '#fee2e2' : '#d1fae5'; ?>; color: <?php echo $finding_count > 0 ? '#991b1b' : '#065f46'; ?>; border-radius: 20px; font-size: 0.85em; font-weight: 600;">
                                            <?php echo $finding_count; ?> <?php echo $lang === 'da' ? 'fund' : 'fynd'; ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (!empty($violations)): ?>
                                        <div style="background: #fef2f2; padding: 12px; border-radius: 6px; margin-bottom: 10px;">
                                            <div style="font-weight: 600; color: #991b1b; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                                <svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
                                                <?php echo $lang === 'da' ? 'Juridiske fund' : 'Juridiska fynd'; ?>
                                            </div>
                                            <ul style="margin: 0; padding-left: 20px; color: #7f1d1d;">
                                                <?php foreach (array_slice($violations, 0, 3) as $violation): ?>
                                                    <li style="margin-bottom: 5px;"><?php echo esc_html($violation); ?></li>
                                                <?php endforeach; ?>
                                                <?php if (count($violations) > 3): ?>
                                                    <li style="color: #991b1b; font-style: italic;">+ <?php echo count($violations) - 3; ?> flere...</li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($recommendations)): ?>
                                        <div style="background: #eff6ff; padding: 12px; border-radius: 6px;">
                                            <div style="font-weight: 600; color: #1e40af; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                                <svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                                                <?php echo $lang === 'da' ? 'Anbefalinger' : 'Rekommendationer'; ?>
                                            </div>
                                            <ul style="margin: 0; padding-left: 20px; color: #1e40af;">
                                                <?php foreach (array_slice($recommendations, 0, 2) as $recommendation): ?>
                                                    <li style="margin-bottom: 5px;"><?php echo esc_html($recommendation); ?></li>
                                                <?php endforeach; ?>
                                                <?php if (count($recommendations) > 2): ?>
                                                    <li style="color: #2563eb; font-style: italic;">+ <?php echo count($recommendations) - 2; ?> flere...</li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div style="margin-top: 12px; display: flex; gap: 10px;">
                                        <a href="<?php echo home_url('/platform-dokumenter/?lang='.$lang.'#doc-'.$analysis->document_id); ?>" style="padding: 8px 16px; background: #f1f5f9; color: #334155; text-decoration: none; border-radius: 6px; font-size: 0.9em; font-weight: 600;">
                                            📋 <?php echo $lang === 'da' ? 'Se detaljer' : 'Se detaljer'; ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div style="text-align: center; margin-top: 20px;">
                                <a href="<?php echo home_url('/platform-dokumenter/?lang='.$lang); ?>" style="color: #6366f1; text-decoration: none; font-weight: 600;">
                                    → <?php echo $lang === 'da' ? 'Se alle dokumenter' : 'Se alla dokument'; ?>
                                </a>
                            </div>
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
            </div><!-- .platform-content -->
        </div><!-- .platform-container -->
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
</script>

<?php get_footer(); ?>
