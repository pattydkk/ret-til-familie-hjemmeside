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
            <div class="sidebar">
                <div class="profile-card" style="background: var(--rtf-card); padding: 30px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 20px; text-align: center;">
                    <div class="profile-avatar" style="width: 100px; height: 100px; background: linear-gradient(135deg, #60a5fa, #2563eb); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 3em; color: white;">
                        <?php echo strtoupper(substr($current_user->username, 0, 1)); ?>
                    </div>
                    <h2 style="margin-bottom: 10px; color: var(--rtf-text);"><?php echo esc_html($current_user->full_name); ?></h2>
                    <p style="color: var(--rtf-muted); margin-bottom: 20px;">@<?php echo esc_html($current_user->username); ?></p>
                    <div class="subscription-badge" style="display: inline-block; padding: 8px 20px; background: <?php echo $current_user->subscription_status === 'active' ? 'linear-gradient(135deg, #38bdf8, #0ea5e9)' : '#e0f2fe'; ?>; color: <?php echo $current_user->subscription_status === 'active' ? '#ffffff' : '#1e3a8a'; ?>; border-radius: 20px; font-weight: 600; margin-bottom: 20px;">
                        <?php echo esc_html($current_user->subscription_status === 'active' ? $txt['active'] : $txt['inactive']); ?>
                    </div>
                </div>

                <nav class="platform-nav" style="background: var(--rtf-card); padding: 20px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10);">
                    <h3 style="margin-bottom: 20px; color: var(--rtf-text);"><?php echo esc_html($txt['platform_menu']); ?></h3>
                    <a href="<?php echo home_url('/platform-vaeg/?lang=' . $lang); ?>" style="display: block; padding: 12px; margin-bottom: 8px; border-radius: 8px; background: #e0f2fe; color: #1e3a8a; text-decoration: none; font-weight: 600;">💬 <?php echo esc_html($txt['wall']); ?></a>
                    <a href="<?php echo home_url('/platform-chat/?lang=' . $lang); ?>" style="display: block; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none; position: relative;">✉️ <?php echo esc_html($txt['messages']); ?> <span id="unreadBadge" style="display: none; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: #ef4444; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600;"></span></a>
                    <a href="<?php echo home_url('/platform-billeder/?lang=' . $lang); ?>" style="display: block; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">📸 <?php echo esc_html($txt['images']); ?></a>
                    <a href="<?php echo home_url('/platform-dokumenter/?lang=' . $lang); ?>" style="display: block; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">📄 <?php echo esc_html($txt['documents']); ?></a>
                    <a href="<?php echo home_url('/platform-nyheder/?lang=' . $lang); ?>" style="display: block; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">📰 <?php echo esc_html($txt['news']); ?></a>
                    <a href="<?php echo home_url('/platform-forum/?lang=' . $lang); ?>" style="display: block; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">💭 <?php echo esc_html($txt['forum']); ?></a>
                    <a href="<?php echo home_url('/platform-sagshjælp/?lang=' . $lang); ?>" style="display: block; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">⚖️ <?php echo esc_html($txt['legal_help']); ?></a>
                    <a href="<?php echo home_url('/platform-kate-ai/?lang=' . $lang); ?>" style="display: block; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">🤖 <?php echo esc_html($txt['kate_ai']); ?></a>
                    <a href="<?php echo home_url('/platform-klagegenerator/?lang=' . $lang); ?>" style="display: block; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">📝 <?php echo esc_html($txt['complaint_gen']); ?></a>
                    <a href="<?php echo home_url('/platform-indstillinger/?lang=' . $lang); ?>" style="display: block; padding: 12px; margin-bottom: 8px; border-radius: 8px; color: var(--rtf-text); text-decoration: none;">⚙️ <?php echo esc_html($txt['settings']); ?></a>
                    <?php if (rtf_is_admin_user()): ?>
                    <a href="<?php echo home_url('/platform-admin/?lang=' . $lang); ?>" style="display: block; padding: 12px; margin-bottom: 8px; border-radius: 8px; background: linear-gradient(135deg, #60a5fa, #2563eb); color: #ffffff; text-decoration: none; font-weight: 600;">🛡️ <?php echo esc_html($txt['admin_panel']); ?></a>
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
                            <p style="font-size: 1.1em; color: var(--rtf-text);"><?php echo esc_html($current_user->full_name); ?></p>
                        </div>
                        
                        <div>
                            <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;"><?php echo esc_html($txt['birthday']); ?></label>
                            <p style="font-size: 1.1em; color: var(--rtf-text);"><?php echo esc_html($birthday_display); ?></p>
                        </div>
                        
                        <div>
                            <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;"><?php echo esc_html($txt['member_since']); ?></label>
                            <p style="font-size: 1.1em; color: var(--rtf-text);"><?php echo rtf_format_date($current_user->created_at); ?></p>
                        </div>
                        
                        <div>
                            <label style="display: block; font-weight: 600; color: var(--rtf-muted); margin-bottom: 8px;"><?php echo esc_html($txt['subscription']); ?></label>
                            <p style="font-size: 1.1em; color: var(--rtf-text);"><?php echo esc_html($current_user->subscription_status === 'active' ? $txt['active'] : $txt['inactive']); ?></p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px;">
                        <a href="<?php echo home_url('/platform-subscription/?lang=' . $lang); ?>" class="btn-primary" style="padding: 15px 30px; text-decoration: none;">
                            <?php echo esc_html($txt['manage_sub']); ?>
                        </a>
                        <a href="?logout=1" class="btn-secondary" style="padding: 15px 30px; text-decoration: none;">
                            <?php echo esc_html($txt['logout']); ?>
                        </a>
                    </div>
                </div>
                
                <!-- DASHBOARD STATISTICS -->
                <div class="dashboard-stats" style="background: var(--rtf-card); padding: 40px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10);">
                    <h2 style="margin-bottom: 25px; color: var(--rtf-text);">📊 <?php echo $lang === 'da' ? 'Min Aktivitet' : 'Min Aktivitet'; ?></h2>
                    
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
                    <h3 style="margin-bottom: 20px; color: var(--rtf-text);">🤖 <?php echo $lang === 'da' ? 'Seneste Kate AI Brug' : 'Senaste Kate AI Användning'; ?></h3>
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
            </div>

        </div>
    </div>
</main>

<?php get_footer(); ?>
