<?php
/**
 * Template Name: Platform Indstillinger
 */

get_header();
$lang = rtf_get_lang();

if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth/?lang=' . $lang));
    exit;
}

rtf_require_subscription();

$current_user = rtf_get_current_user();
global $wpdb;
$table_users = $wpdb->prefix . 'rtf_platform_users';
$table_privacy = $wpdb->prefix . 'rtf_platform_privacy';

$privacy = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_privacy WHERE user_id = %d", $current_user->id));
if (!$privacy) {
    $wpdb->insert($table_privacy, array('user_id' => $current_user->id, 'gdpr_anonymize_birthday' => 1));
    $privacy = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_privacy WHERE user_id = %d", $current_user->id));
}

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'rtf_settings')) {
        wp_die('Security check failed');
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $wpdb->update($table_users, 
            array(
                'full_name' => sanitize_text_field($_POST['full_name']), 
                'email' => sanitize_email($_POST['email']),
                'bio' => sanitize_textarea_field($_POST['bio'] ?? '')
            ),
            array('id' => $current_user->id)
        );
        $success = 'Profil opdateret';
        $current_user = rtf_get_current_user(); // Reload user data
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'update_privacy') {
        $wpdb->update($table_privacy, array(
            'gdpr_anonymize_birthday' => isset($_POST['gdpr_anonymize']) ? 1 : 0,
            'profile_visibility' => sanitize_text_field($_POST['visibility']),
            'show_in_forum' => isset($_POST['show_in_forum']) ? 1 : 0,
            'allow_messages' => isset($_POST['allow_messages']) ? 1 : 0
        ), array('user_id' => $current_user->id));
        $success = 'Privacy indstillinger opdateret';
        $privacy = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_privacy WHERE user_id = %d", $current_user->id));
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            $wpdb->update($table_users, array('password' => password_hash($_POST['new_password'], PASSWORD_DEFAULT)), array('id' => $current_user->id));
            $success = 'Adgangskode ændret';
        } else {
            $error = 'Adgangskoder matcher ikke';
        }
    }
    
    wp_redirect(home_url('/platform-indstillinger/?lang=' . $lang . ($success ? '&success=1' : ($error ? '&error=1' : ''))));
    exit;
}

$t = array('da' => array('title' => 'Indstillinger', 'profile' => 'Profil Oplysninger', 'fullname' => 'Fulde navn', 'email' => 'Email', 'privacy' => 'Privacy & GDPR', 'anonymize' => 'Anonymiser fødselsdag (##-##-ÅÅÅÅ)', 'visibility' => 'Profil synlighed', 'show_forum' => 'Vis i forum', 'allow_msg' => 'Tillad beskeder', 'password' => 'Skift Adgangskode', 'new_pass' => 'Ny adgangskode', 'confirm' => 'Bekræft adgangskode', 'save' => 'Gem', 'all' => 'Alle', 'members' => 'Kun medlemmer', 'private' => 'Privat'), 'sv' => array('title' => 'Inställningar', 'profile' => 'Profiluppgifter', 'fullname' => 'Fullständigt namn', 'email' => 'E-post', 'privacy' => 'Integritet & GDPR', 'anonymize' => 'Anonymisera födelsedag (##-##-ÅÅÅÅ)', 'visibility' => 'Profilsynlighet', 'show_forum' => 'Visa i forum', 'allow_msg' => 'Tillåt meddelanden', 'password' => 'Byt Lösenord', 'new_pass' => 'Nytt lösenord', 'confirm' => 'Bekräfta lösenord', 'save' => 'Spara', 'all' => 'Alla', 'members' => 'Endast medlemmar', 'private' => 'Privat'), 'en' => array('title' => 'Settings', 'profile' => 'Profile Information', 'fullname' => 'Full Name', 'email' => 'Email', 'privacy' => 'Privacy & GDPR', 'anonymize' => 'Anonymize birthday (##-##-YYYY)', 'visibility' => 'Profile visibility', 'show_forum' => 'Show in forum', 'allow_msg' => 'Allow messages', 'password' => 'Change Password', 'new_pass' => 'New password', 'confirm' => 'Confirm password', 'save' => 'Save', 'all' => 'All', 'members' => 'Members only', 'private' => 'Private'));
$txt = $t[$lang];
?>

<div class="platform-layout" style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <?php get_template_part('template-parts/platform-sidebar'); ?>
    
    <main class="platform-indstillinger" style="min-width: 0;">
    <div class="container" style="max-width: 800px; margin: 40px auto; padding: 20px;">
        <h1 style="margin-bottom: 30px; color: var(--rtf-text);"><?php echo esc_html($txt['title']); ?></h1>

        <?php if (isset($_GET['success'])): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">✓ Ændringer gemt</div>
        <?php endif; ?>

        <!-- PROFIL -->
        <div class="settings-card" style="background: var(--rtf-card); padding: 30px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 25px;">
            <h3 style="margin-bottom: 20px;"><?php echo esc_html($txt['profile']); ?></h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_profile">
                <?php wp_nonce_field('rtf_settings'); ?>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo esc_html($txt['fullname']); ?></label>
                    <input type="text" name="full_name" value="<?php echo esc_attr($current_user->full_name); ?>" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo esc_html($txt['email']); ?></label>
                    <input type="email" name="email" value="<?php echo esc_attr($current_user->email); ?>" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo $lang === 'da' ? 'Bio (maks 500 tegn)' : 'Bio (max 500 tecken)'; ?></label>
                    <textarea name="bio" rows="4" maxlength="500" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-family: inherit; resize: vertical;" placeholder="<?php echo $lang === 'da' ? 'Fortæl lidt om dig selv...' : 'Berätta lite om dig själv...'; ?>"><?php echo esc_textarea($current_user->bio ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn-primary"><?php echo esc_html($txt['save']); ?></button>
            </form>
        </div>

        <!-- PRIVACY -->
        <div class="settings-card" style="background: var(--rtf-card); padding: 30px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 25px;">
            <h3 style="margin-bottom: 20px;"><?php echo esc_html($txt['privacy']); ?></h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_privacy">
                <?php wp_nonce_field('rtf_settings'); ?>
                <div style="margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="gdpr_anonymize" value="1" <?php echo $privacy->gdpr_anonymize_birthday ? 'checked' : ''; ?>>
                        <?php echo esc_html($txt['anonymize']); ?>
                    </label>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo esc_html($txt['visibility']); ?></label>
                    <select name="visibility" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                        <option value="all" <?php echo $privacy->profile_visibility === 'all' ? 'selected' : ''; ?>><?php echo esc_html($txt['all']); ?></option>
                        <option value="members" <?php echo $privacy->profile_visibility === 'members' ? 'selected' : ''; ?>><?php echo esc_html($txt['members']); ?></option>
                        <option value="private" <?php echo $privacy->profile_visibility === 'private' ? 'selected' : ''; ?>><?php echo esc_html($txt['private']); ?></option>
                    </select>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="show_in_forum" value="1" <?php echo $privacy->show_in_forum ? 'checked' : ''; ?>>
                        <?php echo esc_html($txt['show_forum']); ?>
                    </label>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="allow_messages" value="1" <?php echo $privacy->allow_messages ? 'checked' : ''; ?>>
                        <?php echo esc_html($txt['allow_msg']); ?>
                    </label>
                </div>
                <button type="submit" class="btn-primary"><?php echo esc_html($txt['save']); ?></button>
            </form>
        </div>

        <!-- PASSWORD -->
        <div class="settings-card" style="background: var(--rtf-card); padding: 30px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10);">
            <h3 style="margin-bottom: 20px;"><?php echo esc_html($txt['password']); ?></h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="change_password">
                <?php wp_nonce_field('rtf_settings'); ?>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo esc_html($txt['new_pass']); ?></label>
                    <input type="password" name="new_password" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo esc_html($txt['confirm']); ?></label>
                    <input type="password" name="confirm_password" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                </div>
                <button type="submit" class="btn-primary"><?php echo esc_html($txt['save']); ?></button>
            </form>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="<?php echo home_url('/platform-profil/?lang=' . $lang); ?>" style="color: #2563eb; text-decoration: none;">← Tilbage til profil</a>
        </div>
    </div>
</main>

</div>

<?php get_footer(); ?>
