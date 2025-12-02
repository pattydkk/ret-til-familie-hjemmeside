<?php
/**
 * Template Name: Platform Billede Galleri
 */

get_header();
$lang = rtf_get_lang();

if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth/?lang=' . $lang));
    exit;
}

$current_user = rtf_get_current_user();
global $wpdb;
$table_images = $wpdb->prefix . 'rtf_platform_images';

// Handle upload - MED ANSIGTS-BLUR HVIS ØNSKET
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    // CSRF protection
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'rtf_upload_image')) {
        wp_die('Security check failed');
    }
    
    require_once get_template_directory() . '/includes/ImageProcessor.php';
    
    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'] . '/platform_images/';
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
    $target_file = $target_dir . $file_name;
    $image_url = $upload_dir['baseurl'] . '/platform_images/' . $file_name;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        
        // Hvis blur_faces er valgt, blur billedet
        $blur_faces = isset($_POST['blur_faces']) ? 1 : 0;
        $final_image_url = $image_url;
        
        if ($blur_faces) {
            try {
                $blurred_path = \RTF\Platform\ImageProcessor::blurFaces($target_file, 15);
                $blurred_filename = basename($blurred_path);
                $final_image_url = $upload_dir['baseurl'] . '/platform_images/' . $blurred_filename;
            } catch (\Exception $e) {
                // Log fejl men fortsæt med original billede
                error_log('Blur faces fejl: ' . $e->getMessage());
            }
        }
        
        $wpdb->insert($table_images, array(
            'user_id' => $current_user->id,
            'image_url' => $final_image_url,
            'title' => sanitize_text_field($_POST['title']),
            'description' => sanitize_textarea_field($_POST['description']),
            'blur_faces' => $blur_faces,
            'created_at' => current_time('mysql')
        ));
    }
    
    wp_redirect(home_url('/platform-billeder/?lang=' . $lang));
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $image_id = intval($_GET['delete']);
    $image = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_images WHERE id = %d", $image_id));
    if ($image && ($image->user_id == $current_user->id || rtf_is_admin_user())) {
        $wpdb->delete($table_images, array('id' => $image_id));
    }
    wp_redirect(home_url('/platform-billeder/?lang=' . $lang));
    exit;
}

// Get images
$images = $wpdb->get_results("SELECT i.*, u.username FROM $table_images i JOIN " . $wpdb->prefix . "rtf_platform_users u ON i.user_id = u.id ORDER BY i.created_at DESC LIMIT 50");

$t = array('da' => array('title' => 'Billede Galleri', 'upload' => 'Upload Billede', 'title_label' => 'Titel', 'desc' => 'Beskrivelse', 'blur' => 'Slør ansigter (GDPR)', 'upload_btn' => 'Upload', 'no_images' => 'Ingen billeder endnu', 'delete' => 'Slet'), 'sv' => array('title' => 'Bildgalleri', 'upload' => 'Ladda upp Bild', 'title_label' => 'Titel', 'desc' => 'Beskrivning', 'blur' => 'Sudda ansikten (GDPR)', 'upload_btn' => 'Ladda upp', 'no_images' => 'Inga bilder än', 'delete' => 'Radera'), 'en' => array('title' => 'Photo Gallery', 'upload' => 'Upload Photo', 'title_label' => 'Title', 'desc' => 'Description', 'blur' => 'Blur faces (GDPR)', 'upload_btn' => 'Upload', 'no_images' => 'No images yet', 'delete' => 'Delete'));
$txt = $t[$lang];
?>

<main class="platform-billeder">
    <div class="container" style="max-width: 1200px; margin: 40px auto; padding: 20px;">
        <h1 style="margin-bottom: 30px; color: var(--rtf-text);"><?php echo esc_html($txt['title']); ?></h1>

        <div class="upload-card" style="background: var(--rtf-card); padding: 30px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 40px;">
            <h3 style="margin-bottom: 20px;"><?php echo esc_html($txt['upload']); ?></h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <?php wp_nonce_field('rtf_upload_image'); ?>
                <input type="text" name="title" placeholder="<?php echo esc_attr($txt['title_label']); ?>" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; margin-bottom: 15px;">
                <textarea name="description" rows="3" placeholder="<?php echo esc_attr($txt['desc']); ?>" style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; margin-bottom: 15px;"></textarea>
                <input type="file" name="image" accept="image/*" required style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 15px;">
                    <input type="checkbox" name="blur_faces" value="1">
                    <?php echo esc_html($txt['blur']); ?>
                </label>
                <button type="submit" class="btn-primary"><?php echo esc_html($txt['upload_btn']); ?></button>
            </form>
        </div>

        <div class="gallery-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px;">
            <?php if (empty($images)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: var(--rtf-muted);">
                    <div style="font-size: 4em; margin-bottom: 20px;">📸</div>
                    <p style="font-size: 1.2em;"><?php echo esc_html($txt['no_images']); ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($images as $img): ?>
                    <div class="image-card" style="background: var(--rtf-card); border-radius: 16px; overflow: hidden; box-shadow: 0 14px 35px rgba(15,23,42,0.10);">
                        <div style="aspect-ratio: 4/3; overflow: hidden; background: #f0f0f0;">
                            <img src="<?php echo esc_url($img->image_url); ?>" alt="<?php echo esc_attr($img->title); ?>" style="width: 100%; height: 100%; object-fit: cover; <?php echo $img->blur_faces ? 'filter: blur(8px);' : ''; ?>">
                        </div>
                        <div style="padding: 20px;">
                            <h4 style="margin-bottom: 10px; color: var(--rtf-text);"><?php echo esc_html($img->title); ?></h4>
                            <?php if ($img->description): ?>
                                <p style="color: var(--rtf-muted); font-size: 0.9em; margin-bottom: 10px;"><?php echo esc_html($img->description); ?></p>
                            <?php endif; ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0f2fe;">
                                <span style="font-size: 0.85em; color: var(--rtf-muted);">@<?php echo esc_html($img->username); ?></span>
                                <?php if ($img->user_id == $current_user->id || rtf_is_admin_user()): ?>
                                    <a href="?delete=<?php echo $img->id; ?>&lang=<?php echo $lang; ?>" onclick="return confirm('Slet billede?')" style="color: #ef4444; text-decoration: none; font-size: 0.9em;"><?php echo esc_html($txt['delete']); ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="<?php echo home_url('/platform-profil/?lang=' . $lang); ?>" style="color: #2563eb; text-decoration: none;">← Tilbage til profil</a>
        </div>
    </div>
</main>

<?php get_footer(); ?>
