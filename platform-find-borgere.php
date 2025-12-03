<?php
/**
 * Template Name: Platform - Find Borgere
 */

get_header();
$lang = rtf_get_lang();

// Tjek om brugeren er logget ind
if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth/?lang=' . $lang));
    exit;
}

rtf_require_subscription();

$current_user = rtf_get_current_user();
global $wpdb;
$users_table = $wpdb->prefix . 'rtf_platform_users';
$friends_table = $wpdb->prefix . 'rtf_platform_friends';

// Hent filtre fra URL
$filter_country = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
$filter_city = isset($_GET['city']) ? sanitize_text_field($_GET['city']) : '';
$filter_case_type = isset($_GET['case_type']) ? sanitize_text_field($_GET['case_type']) : '';
$filter_age_min = isset($_GET['age_min']) ? intval($_GET['age_min']) : 0;
$filter_age_max = isset($_GET['age_max']) ? intval($_GET['age_max']) : 100;

// Opbyg søgequery med rtf_platform_users
$search_query = "SELECT id, username, full_name, email, country, city, case_type, age, bio, profile_image, is_public_profile
                 FROM $users_table
                 WHERE id != {$current_user->id} AND is_active = 1";

// Tilføj filtre
if (!empty($filter_country)) {
    $search_query .= $wpdb->prepare(" AND country = %s", $filter_country);
}
if (!empty($filter_city)) {
    $search_query .= $wpdb->prepare(" AND city LIKE %s", '%' . $wpdb->esc_like($filter_city) . '%');
}
if (!empty($filter_case_type)) {
    $search_query .= $wpdb->prepare(" AND case_type = %s", $filter_case_type);
}
if ($filter_age_min > 0) {
    $search_query .= $wpdb->prepare(" AND age >= %d", $filter_age_min);
}
if ($filter_age_max < 100) {
    $search_query .= $wpdb->prepare(" AND age <= %d", $filter_age_max);
}

// Vis kun offentlige profiler
$search_query .= " AND (is_public_profile = 1 OR is_public_profile IS NULL)";
$search_query .= " ORDER BY full_name ASC LIMIT 50";

$search_results = $wpdb->get_results($search_query);

// Tjek eksisterende venskabsforespørgsler
$existing_requests = [];
$friends_data = $wpdb->get_results($wpdb->prepare(
    "SELECT friend_id, status FROM $friends_table WHERE user_id = %d",
    $current_user->id
));
foreach ($friends_data as $friend) {
    $existing_requests[$friend->friend_id] = $friend;
}
?>

<div class="platform-container" style="display: grid; grid-template-columns: 300px 1fr; gap: 30px; max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <?php get_template_part('template-parts/platform-sidebar'); ?>
    
    <div class="platform-content" style="min-width: 0;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; border-radius: 15px; margin-bottom: 40px;">
            <h1 style="color: white; text-align: center; margin: 0; font-size: 2.5em;">
                🔍 <?php echo $lang === 'en' ? 'Find Citizens' : 'Find Borgere'; ?>
            </h1>
            <p style="color: rgba(255,255,255,0.9); text-align: center; margin: 10px 0 0 0; font-size: 1.1em;">
                <?php echo $lang === 'en' ? 'Connect with others who share similar experiences' : 'Find og connect med andre i samme situation'; ?>
            </p>
        </div>

        <div style="max-width: 100%;">
    
    <!-- Filter sektion -->
    <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-bottom: 40px;">
        <h2 style="margin: 0 0 20px 0; font-size: 1.5em; color: #333;">
            🎯 <?php echo $lang === 'en' ? 'Search Filters' : 'Søgefiltre'; ?>
        </h2>
        
        <form method="get" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <input type="hidden" name="lang" value="<?php echo esc_attr($lang); ?>">
            
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">
                    🌍 <?php echo $lang === 'en' ? 'Country' : 'Land'; ?>
                </label>
                <select name="country" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1em;">
                    <option value=""><?php echo $lang === 'en' ? 'All countries' : 'Alle lande'; ?></option>
                    <option value="DK" <?php selected($filter_country, 'DK'); ?>>🇩🇰 Danmark</option>
                    <option value="SE" <?php selected($filter_country, 'SE'); ?>>🇸🇪 Sverige</option>
                    <option value="NO" <?php selected($filter_country, 'NO'); ?>>🇳🇴 Norge</option>
                </select>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">
                    📍 <?php echo $lang === 'en' ? 'City' : 'By'; ?>
                </label>
                <input type="text" name="city" value="<?php echo esc_attr($filter_city); ?>" 
                       placeholder="<?php echo $lang === 'en' ? 'Enter city name' : 'Indtast bynavn'; ?>" 
                       style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1em;">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">
                    📁 <?php echo $lang === 'en' ? 'Case Type' : 'Sagstype'; ?>
                </label>
                <select name="case_type" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1em;">
                    <option value=""><?php echo $lang === 'en' ? 'All case types' : 'Alle sagstyper'; ?></option>
                    <optgroup label="Familie & Børn">
                        <option value="forældremyndighed" <?php selected($filter_case_type, 'forældremyndighed'); ?>>Forældremyndighed</option>
                        <option value="samvær" <?php selected($filter_case_type, 'samvær'); ?>>Samvær</option>
                        <option value="anbringelse" <?php selected($filter_case_type, 'anbringelse'); ?>>Anbringelse</option>
                        <option value="tvangsfjernelse" <?php selected($filter_case_type, 'tvangsfjernelse'); ?>>Tvangsfjernelse</option>
                        <option value="børnebidrag" <?php selected($filter_case_type, 'børnebidrag'); ?>>Børnebidrag</option>
                        <option value="skilsmisse" <?php selected($filter_case_type, 'skilsmisse'); ?>>Skilsmisse</option>
                    </optgroup>
                    <optgroup label="Jobcenter & Økonomi">
                        <option value="kontanthjælp" <?php selected($filter_case_type, 'kontanthjælp'); ?>>Kontanthjælp</option>
                        <option value="dagpenge" <?php selected($filter_case_type, 'dagpenge'); ?>>Dagpenge</option>
                        <option value="sygedagpenge" <?php selected($filter_case_type, 'sygedagpenge'); ?>>Sygedagpenge</option>
                        <option value="førtidspension" <?php selected($filter_case_type, 'førtidspension'); ?>>Førtidspension</option>
                        <option value="ressourceforløb" <?php selected($filter_case_type, 'ressourceforløb'); ?>>Ressourceforløb</option>
                        <option value="jobafklaringsforløb" <?php selected($filter_case_type, 'jobafklaringsforløb'); ?>>Jobafklaringsforløb</option>
                    </optgroup>
                    <optgroup label="Handicap & Funktionsnedsættelse">
                        <option value="handicaptillæg" <?php selected($filter_case_type, 'handicaptillæg'); ?>>Handicaptillæg</option>
                        <option value="handicapbil" <?php selected($filter_case_type, 'handicapbil'); ?>>Handicapbil</option>
                        <option value="personlig_hjælper" <?php selected($filter_case_type, 'personlig_hjælper'); ?>>Personlig hjælper</option>
                        <option value="botilbud" <?php selected($filter_case_type, 'botilbud'); ?>>Botilbud</option>
                        <option value="hjælpemidler" <?php selected($filter_case_type, 'hjælpemidler'); ?>>Hjælpemidler</option>
                    </optgroup>
                    <optgroup label="Ældre & Pleje">
                        <option value="hjemmepleje" <?php selected($filter_case_type, 'hjemmepleje'); ?>>Hjemmepleje</option>
                        <option value="plejehjem" <?php selected($filter_case_type, 'plejehjem'); ?>>Plejehjem</option>
                        <option value="ældreboliger" <?php selected($filter_case_type, 'ældreboliger'); ?>>Ældreboliger</option>
                        <option value="værgemål" <?php selected($filter_case_type, 'værgemål'); ?>>Værgemål</option>
                    </optgroup>
                    <optgroup label="Andet">
                        <option value="boligstøtte" <?php selected($filter_case_type, 'boligstøtte'); ?>>Boligstøtte</option>
                        <option value="fri_retshjælp" <?php selected($filter_case_type, 'fri_retshjælp'); ?>>Fri retshjælp</option>
                        <option value="anden_sag" <?php selected($filter_case_type, 'anden_sag'); ?>>Anden sag</option>
                    </optgroup>
                </select>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">
                    🎂 <?php echo $lang === 'en' ? 'Age (min)' : 'Alder (min)'; ?>
                </label>
                <input type="number" name="age_min" value="<?php echo esc_attr($filter_age_min > 0 ? $filter_age_min : ''); ?>" 
                       min="18" max="100" placeholder="18" 
                       style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1em;">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">
                    🎂 <?php echo $lang === 'en' ? 'Age (max)' : 'Alder (max)'; ?>
                </label>
                <input type="number" name="age_max" value="<?php echo esc_attr($filter_age_max < 100 ? $filter_age_max : ''); ?>" 
                       min="18" max="100" placeholder="100" 
                       style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 1em;">
            </div>
            
            <div style="display: flex; align-items: flex-end; gap: 10px;">
                <button type="submit" style="flex: 1; padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 1em; font-weight: 600; cursor: pointer; transition: transform 0.2s;">
                    🔍 <?php echo $lang === 'en' ? 'Search' : 'Søg'; ?>
                </button>
                <a href="<?php echo home_url('/platform-find-borgere/?lang=' . $lang); ?>" 
                   style="padding: 12px 24px; background: #f0f0f0; color: #555; border: none; border-radius: 8px; font-size: 1em; font-weight: 600; text-decoration: none; text-align: center; transition: background 0.2s;">
                    🔄 <?php echo $lang === 'en' ? 'Reset' : 'Nulstil'; ?>
                </a>
            </div>
        </form>
    </div>
    
    <!-- Resultater -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; font-size: 1.5em; color: #333;">
                👥 <?php echo $lang === 'en' ? 'Search Results' : 'Søgeresultater'; ?> 
                <span style="color: #667eea;">(<?php echo count($search_results); ?>)</span>
            </h2>
        </div>
        
        <?php if (empty($search_results)): ?>
            <div style="background: white; padding: 60px 40px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center;">
                <div style="font-size: 4em; margin-bottom: 20px;">🔍</div>
                <h3 style="margin: 0 0 10px 0; font-size: 1.5em; color: #333;">
                    <?php echo $lang === 'en' ? 'No users found' : 'Ingen brugere fundet'; ?>
                </h3>
                <p style="color: #666; margin: 0; font-size: 1.1em;">
                    <?php echo $lang === 'en' ? 'Try adjusting your filters or search again' : 'Prøv at justere dine filtre eller søg igen'; ?>
                </p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;">
                <?php foreach ($search_results as $user): 
                    $friend_status = isset($existing_requests[$user->id]) ? $existing_requests[$user->id]->status : null;
                    $profile_url = home_url('/platform-profil-view/?user_id=' . $user->id . '&lang=' . $lang);
                    
                    // Get profile image (generate initials badge if no image)
                    $profile_image = !empty($user->profile_image) ? $user->profile_image : '';
                    $initials = '';
                    if (empty($profile_image)) {
                        $name_parts = explode(' ', $user->full_name);
                        $initials = strtoupper(substr($name_parts[0], 0, 1));
                        if (isset($name_parts[1])) {
                            $initials .= strtoupper(substr($name_parts[1], 0, 1));
                        }
                    }
                    
                    // Truncate bio
                    $bio_preview = !empty($user->bio) ? mb_substr($user->bio, 0, 120) : ($lang === 'en' ? 'No bio available' : 'Ingen beskrivelse tilgængelig');
                    if (mb_strlen($user->bio) > 120) {
                        $bio_preview .= '...';
                    }
                    
                    // Country flag
                    $country_flag = $user->country === 'DK' ? '🇩🇰' : ($user->country === 'SE' ? '🇸🇪' : ($user->country === 'NO' ? '🇳🇴' : '🌍'));
                ?>
                    <div style="background: white; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; transition: transform 0.3s, box-shadow 0.3s; cursor: pointer;" 
                         onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 30px rgba(0,0,0,0.15)';" 
                         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.08)';">
                        
                        <!-- Profile header with image or initials -->
                        <div style="position: relative; height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: flex-end; justify-content: center; padding-bottom: 20px;">
                            <?php if (!empty($profile_image)): ?>
                                <img src="<?php echo esc_url($profile_image); ?>" 
                                     alt="<?php echo esc_attr($user->full_name); ?>" 
                                     style="width: 120px; height: 120px; border-radius: 50%; border: 5px solid white; object-fit: cover; position: relative; z-index: 2;">
                            <?php else: ?>
                                <div style="width: 120px; height: 120px; border-radius: 50%; border: 5px solid white; background: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; color: white; position: relative; z-index: 2;">
                                    <?php echo $initials; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- User info -->
                        <div style="padding: 80px 25px 25px; margin-top: -60px;">
                            <h3 style="margin: 0 0 10px 0; font-size: 1.4em; color: #333; text-align: center;">
                                <?php echo esc_html($user->full_name); ?>
                            </h3>
                            
                            <div style="display: flex; justify-content: center; gap: 15px; margin-bottom: 15px; flex-wrap: wrap;">
                                <?php if (!empty($user->age)): ?>
                                    <span style="padding: 6px 12px; background: #f0f0f0; border-radius: 20px; font-size: 0.9em; color: #666;">
                                        🎂 <?php echo esc_html($user->age); ?> <?php echo $lang === 'en' ? 'years' : 'år'; ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($user->country)): ?>
                                    <span style="padding: 6px 12px; background: #f0f0f0; border-radius: 20px; font-size: 0.9em; color: #666;">
                                        <?php echo $country_flag; ?> <?php echo esc_html($user->country); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($user->city)): ?>
                                    <span style="padding: 6px 12px; background: #f0f0f0; border-radius: 20px; font-size: 0.9em; color: #666;">
                                        📍 <?php echo esc_html($user->city); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($user->case_type)): ?>
                                <div style="text-align: center; margin-bottom: 15px;">
                                    <span style="padding: 8px 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 20px; font-size: 0.9em; font-weight: 600; display: inline-block;">
                                        📁 <?php echo esc_html(ucfirst($user->case_type)); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <p style="color: #666; margin: 0 0 20px 0; font-size: 0.95em; line-height: 1.6; text-align: center;">
                                <?php echo esc_html($bio_preview); ?>
                            </p>
                            
                            <!-- Action buttons -->
                            <div style="display: flex; gap: 10px;">
                                <a href="<?php echo esc_url($profile_url); ?>" 
                                   style="flex: 1; padding: 12px; background: #f8f9fa; color: #333; text-align: center; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.2s;">
                                    👤 <?php echo $lang === 'en' ? 'View Profile' : 'Se Profil'; ?>
                                </a>
                                
                                <?php if ($friend_status === 'accepted'): ?>
                                    <button disabled style="flex: 1; padding: 12px; background: #e8f5e9; color: #4caf50; border: none; border-radius: 8px; font-weight: 600; cursor: not-allowed;">
                                        ✅ <?php echo $lang === 'en' ? 'Friends' : 'Venner'; ?>
                                    </button>
                                <?php elseif ($friend_status === 'pending'): ?>
                                    <button disabled style="flex: 1; padding: 12px; background: #fff3e0; color: #ff9800; border: none; border-radius: 8px; font-weight: 600; cursor: not-allowed;">
                                        ⏳ <?php echo $lang === 'en' ? 'Pending' : 'Afventer'; ?>
                                    </button>
                                <?php else: ?>
                                    <button onclick="sendFriendRequest(<?php echo $user->ID; ?>, this)" 
                                            style="flex: 1; padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;">
                                        🤝 <?php echo $lang === 'en' ? 'Connect' : 'Connect'; ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function sendFriendRequest(userId, button) {
    if (confirm('<?php echo $lang === "en" ? "Send connection request to this user?" : "Send forbindelses-anmodning til denne bruger?"; ?>')) {
        button.disabled = true;
        button.innerHTML = '⏳ <?php echo $lang === "en" ? "Sending..." : "Sender..."; ?>';
        
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
                button.innerHTML = '⏳ <?php echo $lang === "en" ? "Pending" : "Afventer"; ?>';
                button.style.background = '#fff3e0';
                button.style.color = '#ff9800';
                alert('<?php echo $lang === "en" ? "Connection request sent!" : "Forbindelses-anmodning sendt!"; ?>');
            } else {
                alert('<?php echo $lang === "en" ? "Error: " : "Fejl: "; ?>' + (data.message || 'Unknown error'));
                button.disabled = false;
                button.innerHTML = '🤝 <?php echo $lang === "en" ? "Connect" : "Connect"; ?>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?php echo $lang === "en" ? "Error sending request. Please try again." : "Fejl ved afsendelse. Prøv igen."; ?>');
            button.disabled = false;
            button.innerHTML = '🤝 <?php echo $lang === "en" ? "Connect" : "Connect"; ?>';
        });
    }
}
</script>

    </div><!-- .platform-content -->
</div><!-- .platform-container -->

<?php get_footer(); ?>
