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
                'phone' => sanitize_text_field($_POST['phone'] ?? ''),
                'case_type' => sanitize_text_field($_POST['case_type'] ?? ''),
                'country' => sanitize_text_field($_POST['country'] ?? 'DK'),
                'age' => intval($_POST['age'] ?? 0),
                'city' => sanitize_text_field($_POST['city'] ?? ''),
                'postal_code' => sanitize_text_field($_POST['postal_code'] ?? ''),
                'address' => sanitize_text_field($_POST['address'] ?? ''),
                'language_preference' => sanitize_text_field($_POST['language_preference'] ?? 'da_DK'),
                'bio' => sanitize_textarea_field($_POST['bio'] ?? ''),
                'website' => esc_url_raw($_POST['website'] ?? ''),
                'occupation' => sanitize_text_field($_POST['occupation'] ?? ''),
                'facebook_url' => esc_url_raw($_POST['facebook_url'] ?? ''),
                'twitter_url' => esc_url_raw($_POST['twitter_url'] ?? ''),
                'instagram_url' => esc_url_raw($_POST['instagram_url'] ?? ''),
                'linkedin_url' => esc_url_raw($_POST['linkedin_url'] ?? ''),
                'interests' => sanitize_text_field($_POST['interests'] ?? ''),
                'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
                'two_factor_enabled' => isset($_POST['two_factor_enabled']) ? 1 : 0,
                'show_online_status' => isset($_POST['show_online_status']) ? 1 : 0,
                'allow_friend_requests' => isset($_POST['allow_friend_requests']) ? 1 : 0
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
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo esc_html($txt['fullname']); ?></label>
                        <input type="text" name="full_name" value="<?php echo esc_attr($current_user->full_name); ?>" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo esc_html($txt['email']); ?></label>
                        <input type="email" name="email" value="<?php echo esc_attr($current_user->email); ?>" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo $lang === 'da' ? 'Telefonnummer' : 'Telefonnummer'; ?></label>
                        <input type="tel" name="phone" value="<?php echo esc_attr($current_user->phone); ?>" placeholder="<?php echo $lang === 'da' ? '+45 12 34 56 78' : '+46 70 123 45 67'; ?>" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo $lang === 'da' ? 'Sagstype' : 'Ärendetyp'; ?></label>
                        <select name="case_type" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                            <option value=""><?php echo $lang === 'da' ? 'Vælg sagstype' : 'Välj ärendetyp'; ?></option>
                            <option value="custody" <?php echo $current_user->case_type === 'custody' ? 'selected' : ''; ?>><?php echo $lang === 'da' ? 'Forældremyndighed' : 'Vårdnad'; ?></option>
                            <option value="visitation" <?php echo $current_user->case_type === 'visitation' ? 'selected' : ''; ?>><?php echo $lang === 'da' ? 'Samvær' : 'Umgänge'; ?></option>
                            <option value="divorce" <?php echo $current_user->case_type === 'divorce' ? 'selected' : ''; ?>><?php echo $lang === 'da' ? 'Skilsmisse' : 'Skilsmässa'; ?></option>
                            <option value="support" <?php echo $current_user->case_type === 'support' ? 'selected' : ''; ?>><?php echo $lang === 'da' ? 'Børnebidrag' : 'Barnbidrag'; ?></option>
                            <option value="other" <?php echo $current_user->case_type === 'other' ? 'selected' : ''; ?>><?php echo $lang === 'da' ? 'Andet' : 'Annat'; ?></option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo $lang === 'da' ? 'Land' : 'Land'; ?></label>
                        <select name="country" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                            <option value="DK" <?php echo $current_user->country === 'DK' ? 'selected' : ''; ?>>Danmark</option>
                            <option value="SE" <?php echo $current_user->country === 'SE' ? 'selected' : ''; ?>>Sverige</option>
                            <option value="NO" <?php echo $current_user->country === 'NO' ? 'selected' : ''; ?>>Norge</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo $lang === 'da' ? 'Alder' : 'Ålder'; ?></label>
                        <input type="number" name="age" value="<?php echo esc_attr($current_user->age); ?>" min="18" max="120" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo $lang === 'da' ? 'By' : 'Stad'; ?></label>
                        <input type="text" name="city" value="<?php echo esc_attr($current_user->city ?? ''); ?>" placeholder="<?php echo $lang === 'da' ? 'København' : 'Stockholm'; ?>" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo $lang === 'da' ? 'Postnummer' : 'Postnummer'; ?></label>
                        <input type="text" name="postal_code" value="<?php echo esc_attr($current_user->postal_code ?? ''); ?>" placeholder="<?php echo $lang === 'da' ? '2000' : '100 00'; ?>" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                    </div>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo $lang === 'da' ? 'Adresse' : 'Adress'; ?></label>
                    <input type="text" name="address" value="<?php echo esc_attr($current_user->address ?? ''); ?>" placeholder="<?php echo $lang === 'da' ? 'Gadenavn 123' : 'Gatnamn 123'; ?>" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo $lang === 'da' ? 'Sprog præference' : 'Språkpreferens'; ?></label>
                    <select name="language_preference" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                        <option value="da_DK" <?php echo $current_user->language_preference === 'da_DK' ? 'selected' : ''; ?>>🇩🇰 Dansk</option>
                        <option value="sv_SE" <?php echo $current_user->language_preference === 'sv_SE' ? 'selected' : ''; ?>>🇸🇪 Svenska</option>
                        <option value="en_US" <?php echo $current_user->language_preference === 'en_US' ? 'selected' : ''; ?>>🇬🇧 English</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php echo $lang === 'da' ? 'Bio (maks 500 tegn)' : 'Bio (max 500 tecken)'; ?></label>
                    <textarea name="bio" rows="4" maxlength="500" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; font-family: inherit; resize: vertical;" placeholder="<?php echo $lang === 'da' ? 'Fortæl lidt om dig selv...' : 'Berätta lite om dig själv...'; ?>"><?php echo esc_textarea($current_user->bio ?? ''); ?></textarea>
                </div>
                
                <h4 style="margin: 25px 0 15px; color: var(--rtf-text);"><?php echo $lang === 'da' ? 'Sociale Links' : 'Sociala länkar'; ?></h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">🌐 <?php echo $lang === 'da' ? 'Hjemmeside' : 'Hemsida'; ?></label>
                        <input type="url" name="website" value="<?php echo esc_attr($current_user->website ?? ''); ?>" placeholder="https://example.com" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">💼 <?php echo $lang === 'da' ? 'Erhverv' : 'Yrke'; ?></label>
                        <input type="text" name="occupation" value="<?php echo esc_attr($current_user->occupation ?? ''); ?>" placeholder="<?php echo $lang === 'da' ? 'Dit erhverv' : 'Ditt yrke'; ?>" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">📘 Facebook</label>
                        <input type="url" name="facebook_url" value="<?php echo esc_attr($current_user->facebook_url ?? ''); ?>" placeholder="https://facebook.com/ditprofil" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">🐦 Twitter / X</label>
                        <input type="url" name="twitter_url" value="<?php echo esc_attr($current_user->twitter_url ?? ''); ?>" placeholder="https://twitter.com/ditbrugernavn" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">📷 Instagram</label>
                        <input type="url" name="instagram_url" value="<?php echo esc_attr($current_user->instagram_url ?? ''); ?>" placeholder="https://instagram.com/ditbrugernavn" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">💼 LinkedIn</label>
                        <input type="url" name="linkedin_url" value="<?php echo esc_attr($current_user->linkedin_url ?? ''); ?>" placeholder="https://linkedin.com/in/ditprofil" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                    </div>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">🎯 <?php echo $lang === 'da' ? 'Interesser (kommasepareret)' : 'Intressen (kommaseparerade)'; ?></label>
                    <input type="text" name="interests" value="<?php echo esc_attr($current_user->interests ?? ''); ?>" placeholder="<?php echo $lang === 'da' ? 'Familie, lovgivning, sport...' : 'Familj, lagstiftning, sport...'; ?>" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px;">
                </div>
                
                <h4 style="margin: 25px 0 15px; color: var(--rtf-text);"><?php echo $lang === 'da' ? 'Sikkerhed & Notifikationer' : 'Säkerhet & Notifikationer'; ?></h4>
                
                <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 2px solid #e0f2fe; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; margin-bottom: 15px;">
                        <input type="checkbox" name="email_notifications" value="1" <?php echo ($current_user->email_notifications ?? 1) ? 'checked' : ''; ?> style="width: 20px; height: 20px; cursor: pointer;">
                        <div>
                            <div style="font-weight: 600; margin-bottom: 4px;">📧 <?php echo $lang === 'da' ? 'Email notifikationer' : 'E-postmeddelanden'; ?></div>
                            <div style="font-size: 0.9em; color: var(--rtf-muted);"><?php echo $lang === 'da' ? 'Modtag email når du får nye beskeder eller svar' : 'Ta emot e-post när du får nya meddelanden eller svar'; ?></div>
                        </div>
                    </label>
                    
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; margin-bottom: 15px;">
                        <input type="checkbox" name="two_factor_enabled" value="1" <?php echo ($current_user->two_factor_enabled ?? 0) ? 'checked' : ''; ?> style="width: 20px; height: 20px; cursor: pointer;">
                        <div>
                            <div style="font-weight: 600; margin-bottom: 4px;">🔐 <?php echo $lang === 'da' ? 'To-faktor godkendelse (2FA)' : 'Tvåfaktorsautentisering (2FA)'; ?></div>
                            <div style="font-size: 0.9em; color: var(--rtf-muted);"><?php echo $lang === 'da' ? 'Ekstra sikkerhed ved login med SMS kode' : 'Extra säkerhet vid inloggning med SMS-kod'; ?></div>
                        </div>
                    </label>
                    
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; margin-bottom: 15px;">
                        <input type="checkbox" name="show_online_status" value="1" <?php echo ($current_user->show_online_status ?? 1) ? 'checked' : ''; ?> style="width: 20px; height: 20px; cursor: pointer;">
                        <div>
                            <div style="font-weight: 600; margin-bottom: 4px;">🟢 <?php echo $lang === 'da' ? 'Vis online status' : 'Visa onlinestatus'; ?></div>
                            <div style="font-size: 0.9em; color: var(--rtf-muted);"><?php echo $lang === 'da' ? 'Lad andre se når du er online' : 'Låt andra se när du är online'; ?></div>
                        </div>
                    </label>
                    
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                        <input type="checkbox" name="allow_friend_requests" value="1" <?php echo ($current_user->allow_friend_requests ?? 1) ? 'checked' : ''; ?> style="width: 20px; height: 20px; cursor: pointer;">
                        <div>
                            <div style="font-weight: 600; margin-bottom: 4px;">👥 <?php echo $lang === 'da' ? 'Tillad venneanmodninger' : 'Tillåt vänförfrågningar'; ?></div>
                            <div style="font-size: 0.9em; color: var(--rtf-muted);"><?php echo $lang === 'da' ? 'Modtag anmodninger fra andre brugere' : 'Ta emot förfrågningar från andra användare'; ?></div>
                        </div>
                    </label>
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
