<?php
/**
 * Template Name: Platform Profil View (Offentlig visning)
 * Description: Viser en brugers offentlige profil for andre brugere
 */

// Tjek om brugeren er logget ind
if (!is_user_logged_in()) {
    wp_redirect(home_url('/platform-login'));
    exit;
}

// Hent sprog parameter
$lang = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : 'da';

// Hent user_id fra URL
$view_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if (!$view_user_id) {
    wp_redirect(home_url('/platform-profil/?lang=' . $lang));
    exit;
}

global $wpdb;
$current_user = wp_get_current_user();
$viewing_own_profile = ($current_user->ID === $view_user_id);

// Redirect hvis bruger ser sin egen profil
if ($viewing_own_profile) {
    wp_redirect(home_url('/platform-profil/?lang=' . $lang));
    exit;
}

// Hent brugerdata fra rtf_platform_users
$viewed_user = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE id = %d",
    $view_user_id
));

if (!$viewed_user) {
    wp_redirect(home_url('/platform-find-borgere/?lang=' . $lang));
    exit;
}

// Hent data fra rtf_platform_users tabel
$profile_image = $viewed_user->profile_image;
$cover_image = $viewed_user->cover_image;
$full_name = $viewed_user->full_name ?: $viewed_user->username;
$bio = $viewed_user->bio;
$age = $viewed_user->age;
$country = $viewed_user->country;
$city = $viewed_user->city;
$case_type = $viewed_user->case_type;
$is_public_profile = $viewed_user->is_public_profile;

// Tjek om profilen er offentlig
if (!$is_public_profile) {
    wp_die('Denne profil er privat');
}

// Hent offentlige posts
$public_posts = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}rtf_platform_posts 
     WHERE user_id = %d AND visibility = 'public'
     ORDER BY created_at DESC
     LIMIT 20",
    $view_user_id
));

// Hent offentlige billeder
$public_images = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}rtf_platform_images 
     WHERE user_id = %d AND is_public = 1
     ORDER BY uploaded_at DESC
     LIMIT 6",
    $view_user_id
));

// Hent venskabsstatus
$friendship = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}rtf_platform_friends 
     WHERE (user_id = %d AND friend_id = %d) OR (user_id = %d AND friend_id = %d)",
    $current_user->ID, $view_user_id, $view_user_id, $current_user->ID
));

$friend_status = $friendship ? $friendship->status : null;
$country_flag = $country === 'DK' ? '🇩🇰' : ($country === 'SE' ? '🇸🇪' : ($country === 'NO' ? '🇳🇴' : '🌍'));

get_header();
?>

<style>
.profile-view-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.profile-header {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

.cover-section {
    height: 200px;
    background: <?php echo $cover_image ? 'url(' . esc_url($cover_image) . ') center/cover' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'; ?>;
    position: relative;
}

.profile-info {
    padding: 0 40px 40px;
    position: relative;
    margin-top: -60px;
}

.profile-avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 5px solid white;
    background: <?php echo $profile_image ? 'url(' . esc_url($profile_image) . ') center/cover' : 'linear-gradient(135deg, #60a5fa, #2563eb)'; ?>;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3em;
    color: white;
    font-weight: 600;
    margin-bottom: 20px;
}

.profile-actions {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-secondary {
    background: #f0f0f0;
    color: #333;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.info-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.section-card {
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

.post-item {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 15px;
}

.image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
}

.image-item {
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
}

.image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
</style>

<div class="profile-view-container">
    <div class="profile-header">
        <div class="cover-section"></div>
        
        <div class="profile-info">
            <div class="profile-avatar-large">
                <?php if (!$profile_image): ?>
                    <?php echo strtoupper(substr($viewed_user->user_login, 0, 1)); ?>
                <?php endif; ?>
            </div>
            
            <h1 style="margin: 0 0 10px 0; font-size: 2em;"><?php echo esc_html($full_name); ?></h1>
            <p style="color: #666; margin: 0 0 5px 0;">@<?php echo esc_html($viewed_user->user_login); ?></p>
            
            <?php if ($bio): ?>
                <p style="color: #333; margin: 20px 0; line-height: 1.6; font-size: 1.1em;">
                    <?php echo esc_html($bio); ?>
                </p>
            <?php endif; ?>
            
            <div class="info-grid">
                <?php if ($age): ?>
                    <div class="info-item">
                        <div style="font-size: 0.9em; color: #666; margin-bottom: 5px;">Alder</div>
                        <div style="font-weight: 600; font-size: 1.1em;">🎂 <?php echo esc_html($age); ?> år</div>
                    </div>
                <?php endif; ?>
                
                <?php if ($country): ?>
                    <div class="info-item">
                        <div style="font-size: 0.9em; color: #666; margin-bottom: 5px;">Land</div>
                        <div style="font-weight: 600; font-size: 1.1em;"><?php echo $country_flag; ?> <?php echo esc_html($country); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ($city): ?>
                    <div class="info-item">
                        <div style="font-size: 0.9em; color: #666; margin-bottom: 5px;">By</div>
                        <div style="font-weight: 600; font-size: 1.1em;">📍 <?php echo esc_html($city); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ($case_type): ?>
                    <div class="info-item">
                        <div style="font-size: 0.9em; color: #666; margin-bottom: 5px;">Sagstype</div>
                        <div style="font-weight: 600; font-size: 1.1em;">📁 <?php echo esc_html(ucfirst($case_type)); ?></div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="profile-actions">
                <?php if ($friend_status === 'accepted'): ?>
                    <button class="btn btn-secondary" disabled>
                        ✅ <?php echo $lang === 'da' ? 'Venner' : 'Vänner'; ?>
                    </button>
                    <a href="<?php echo home_url('/platform-chat/?user_id=' . $view_user_id . '&lang=' . $lang); ?>" class="btn btn-primary">
                        💬 <?php echo $lang === 'da' ? 'Send besked' : 'Skicka meddelande'; ?>
                    </a>
                <?php elseif ($friend_status === 'pending'): ?>
                    <button class="btn btn-secondary" disabled>
                        ⏳ <?php echo $lang === 'da' ? 'Anmodning afventer' : 'Begäran väntar'; ?>
                    </button>
                <?php else: ?>
                    <button onclick="sendFriendRequest(<?php echo $view_user_id; ?>)" class="btn btn-primary">
                        🤝 <?php echo $lang === 'da' ? 'Send venneanmodning' : 'Skicka vänförfrågan'; ?>
                    </button>
                <?php endif; ?>
                
                <a href="<?php echo home_url('/platform-find-borgere/?lang=' . $lang); ?>" class="btn btn-secondary">
                    ← <?php echo $lang === 'da' ? 'Tilbage til søgning' : 'Tillbaka till sökning'; ?>
                </a>
            </div>
        </div>
    </div>
    
    <?php if (!empty($public_images)): ?>
        <div class="section-card">
            <h2 style="margin: 0 0 20px 0;">🖼️ <?php echo $lang === 'da' ? 'Offentlige billeder' : 'Offentliga bilder'; ?></h2>
            <div class="image-grid">
                <?php foreach ($public_images as $image): ?>
                    <div class="image-item">
                        <img src="<?php echo esc_url($image->image_url); ?>" alt="<?php echo esc_attr($image->caption ?: 'Billede'); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($public_posts)): ?>
        <div class="section-card">
            <h2 style="margin: 0 0 20px 0;">📝 <?php echo $lang === 'da' ? 'Offentlige opslag' : 'Offentliga inlägg'; ?></h2>
            <?php foreach ($public_posts as $post): ?>
                <div class="post-item">
                    <div style="color: #666; font-size: 0.9em; margin-bottom: 10px;">
                        <?php echo date('j. F Y \k\l. H:i', strtotime($post->created_at)); ?>
                    </div>
                    <div style="color: #333; line-height: 1.6; white-space: pre-wrap;">
                        <?php echo esc_html($post->content); ?>
                    </div>
                    <?php if ($post->likes > 0): ?>
                        <div style="margin-top: 10px; color: #666; font-size: 0.9em;">
                            ❤️ <?php echo $post->likes; ?> <?php echo $lang === 'da' ? 'likes' : 'gillningar'; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($public_posts) && empty($public_images)): ?>
        <div class="section-card" style="text-align: center; padding: 60px;">
            <div style="font-size: 4em; margin-bottom: 20px;">🔒</div>
            <h3 style="margin: 0 0 10px 0; color: #333;">
                <?php echo $lang === 'da' ? 'Intet offentligt indhold' : 'Inget offentligt innehåll'; ?>
            </h3>
            <p style="color: #666; margin: 0;">
                <?php echo $lang === 'da' ? 'Denne bruger har ikke delt noget offentligt endnu' : 'Denna användare har inte delat något offentligt än'; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
function sendFriendRequest(userId) {
    if (confirm('<?php echo $lang === "da" ? "Send venneanmodning til denne bruger?" : "Skicka vänförfrågan till denna användare?"; ?>')) {
        fetch('<?php echo rest_url('kate/v1/send-friend-request'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
            },
            body: JSON.stringify({
                friend_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('<?php echo $lang === "da" ? "Venneanmodning sendt!" : "Vänförfrågan skickad!"; ?>');
                location.reload();
            } else {
                alert('<?php echo $lang === "da" ? "Fejl: " : "Fel: "; ?>' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php echo $lang === "da" ? "Fejl ved afsendelse. Prøv igen." : "Fel vid skickande. Försök igen."; ?>');
        });
    }
}
</script>

<?php get_footer(); ?>
