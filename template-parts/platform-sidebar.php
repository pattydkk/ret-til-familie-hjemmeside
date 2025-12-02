<?php
/**
 * Platform Sidebar Navigation
 * Reusable sidebar component for all platform pages
 */

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$language = get_user_meta($user_id, 'language_preference', true) ?: 'da_DK';
$is_danish = ($language === 'da_DK');

// Get current page
$current_url = $_SERVER['REQUEST_URI'];
?>

<aside class="platform-sidebar" style="position: sticky; top: 80px; height: fit-content;">
    <h3>📱 <?php echo $is_danish ? 'Platform' : 'Plattform'; ?></h3>
    <ul class="platform-nav">
        <li><a href="<?php echo home_url('/platform-profil'); ?>" <?php if (strpos($current_url, 'platform-profil') !== false) echo 'class="active"'; ?>>👤 <?php echo $is_danish ? 'Profil' : 'Profil'; ?></a></li>
        <li><a href="<?php echo home_url('/platform-chat'); ?>" <?php if (strpos($current_url, 'platform-chat') !== false) echo 'class="active"'; ?>>💬 <?php echo $is_danish ? 'Beskeder' : 'Meddelanden'; ?></a></li>
        <li><a href="<?php echo home_url('/platform-vaeg'); ?>" <?php if (strpos($current_url, 'platform-vaeg') !== false) echo 'class="active"'; ?>>📝 <?php echo $is_danish ? 'Væg' : 'Vägg'; ?></a></li>
        <li><a href="<?php echo home_url('/platform-billeder'); ?>" <?php if (strpos($current_url, 'platform-billeder') !== false) echo 'class="active"'; ?>>📷 <?php echo $is_danish ? 'Billeder' : 'Bilder'; ?></a></li>
        <li><a href="<?php echo home_url('/platform-dokumenter'); ?>" <?php if (strpos($current_url, 'platform-dokumenter') !== false) echo 'class="active"'; ?>>📄 <?php echo $is_danish ? 'Dokumenter' : 'Dokument'; ?></a></li>
        <li><a href="<?php echo home_url('/platform-venner'); ?>" <?php if (strpos($current_url, 'platform-venner') !== false) echo 'class="active"'; ?>>👥 <?php echo $is_danish ? 'Venner' : 'Vänner'; ?></a></li>
        <li><a href="<?php echo home_url('/platform-forum'); ?>" <?php if (strpos($current_url, 'platform-forum') !== false) echo 'class="active"'; ?>>💬 Forum</a></li>
        <li><a href="<?php echo home_url('/platform-nyheder'); ?>" <?php if (strpos($current_url, 'platform-nyheder') !== false) echo 'class="active"'; ?>>📰 <?php echo $is_danish ? 'Nyheder' : 'Nyheter'; ?></a></li>
        <li><a href="<?php echo home_url('/platform-sagshjaelp'); ?>" <?php if (strpos($current_url, 'platform-sagshjaelp') !== false) echo 'class="active"'; ?>>⚖️ <?php echo $is_danish ? 'Sagshjælp' : 'Ärendehjälp'; ?></a></li>
        <li><a href="<?php echo home_url('/platform-kate-ai'); ?>" <?php if (strpos($current_url, 'platform-kate-ai') !== false) echo 'class="active"'; ?>>🤖 Kate AI</a></li>
        <li><a href="<?php echo home_url('/platform-indstillinger'); ?>" <?php if (strpos($current_url, 'platform-indstillinger') !== false) echo 'class="active"'; ?>>⚙️ <?php echo $is_danish ? 'Indstillinger' : 'Inställningar'; ?></a></li>
    </ul>
</aside>
