<?php
/**
 * Template Name: Platform Dokumenter
 */

get_header();
$lang = rtf_get_lang();

if (!rtf_is_logged_in()) {
    wp_redirect(home_url('/platform-auth/?lang=' . $lang));
    exit;
}

$current_user = rtf_get_current_user();
global $wpdb;
$table_documents = $wpdb->prefix . 'rtf_platform_documents';

// Handle upload - MED DOKUMENT PARSING OG KATE AI ANALYSE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    require_once get_template_directory() . '/includes/DocumentParser.php';
    
    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'] . '/platform_documents/';
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $file_name = uniqid() . '_' . basename($_FILES['document']['name']);
    $target_file = $target_dir . $file_name;
    $file_url = $upload_dir['baseurl'] . '/platform_documents/' . $file_name;
    
    if (move_uploaded_file($_FILES['document']['tmp_name'], $target_file)) {
        
        // Parse dokument indhold
        $parsed_text = '';
        $parse_error = null;
        try {
            $parseResult = \RTF\Platform\DocumentParser::parse($target_file);
            if ($parseResult['success']) {
                $parsed_text = $parseResult['text'];
            } else {
                $parse_error = $parseResult['error'];
            }
        } catch (\Exception $e) {
            $parse_error = $e->getMessage();
        }
        
        // Gem dokument til database
        $wpdb->insert($table_documents, array(
            'user_id' => $current_user->id,
            'file_url' => $file_url,
            'file_name' => sanitize_text_field($_POST['file_name']),
            'file_type' => $_FILES['document']['type'],
            'file_size' => $_FILES['document']['size'],
            'is_public' => isset($_POST['is_public']) ? 1 : 0,
            'created_at' => current_time('mysql')
        ));
        
        $document_id = $wpdb->insert_id;
        
        // Automatisk Kate AI analyse hvis dokument er parseret
        if (!empty($parsed_text) && strlen($parsed_text) > 50) {
            // Trigger Kate AI analyse via REST API (asynkront)
            $document_type = $_POST['document_type'] ?? 'afgørelse';
            
            // I en real implementation ville dette være AJAX call
            // Her gemmer vi bare et flag om at analyse er pending
            $wpdb->update(
                $table_documents,
                ['analysis_status' => 'pending'],
                ['id' => $document_id]
            );
        }
    }
    
    wp_redirect(home_url('/platform-dokumenter/?lang=' . $lang));
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $doc_id = intval($_GET['delete']);
    $doc = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_documents WHERE id = %d", $doc_id));
    if ($doc && ($doc->user_id == $current_user->id || rtf_is_admin_user())) {
        $wpdb->delete($table_documents, array('id' => $doc_id));
    }
    wp_redirect(home_url('/platform-dokumenter/?lang=' . $lang));
    exit;
}

// Get documents
$documents = $wpdb->get_results($wpdb->prepare("SELECT d.*, u.username FROM $table_documents d JOIN " . $wpdb->prefix . "rtf_platform_users u ON d.user_id = u.id WHERE d.user_id = %d OR d.is_public = 1 ORDER BY d.created_at DESC", $current_user->id));

$t = array('da' => array('title' => 'Dokumenter', 'upload' => 'Upload Dokument', 'name' => 'Dokument navn', 'public' => 'Gør offentlig', 'upload_btn' => 'Upload', 'no_docs' => 'Ingen dokumenter', 'download' => 'Download', 'delete' => 'Slet', 'size' => 'Størrelse'), 'sv' => array('title' => 'Dokument', 'upload' => 'Ladda upp Dokument', 'name' => 'Dokumentnamn', 'public' => 'Gör offentlig', 'upload_btn' => 'Ladda upp', 'no_docs' => 'Inga dokument', 'download' => 'Ladda ner', 'delete' => 'Radera', 'size' => 'Storlek'), 'en' => array('title' => 'Documents', 'upload' => 'Upload Document', 'name' => 'Document name', 'public' => 'Make public', 'upload_btn' => 'Upload', 'no_docs' => 'No documents', 'download' => 'Download', 'delete' => 'Delete', 'size' => 'Size'));
$txt = $t[$lang];
?>

<main class="platform-dokumenter">
    <div class="container" style="max-width: 900px; margin: 40px auto; padding: 20px;">
        <h1 style="margin-bottom: 30px; color: var(--rtf-text);"><?php echo esc_html($txt['title']); ?></h1>

        <div class="upload-card" style="background: var(--rtf-card); padding: 30px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 40px;">
            <h3 style="margin-bottom: 20px;"><?php echo esc_html($txt['upload']); ?></h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="text" name="file_name" placeholder="<?php echo esc_attr($txt['name']); ?>" required style="width: 100%; padding: 12px; border: 1px solid #e0f2fe; border-radius: 8px; margin-bottom: 15px;">
                <input type="file" name="document" required style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 15px;">
                    <input type="checkbox" name="is_public" value="1">
                    <?php echo esc_html($txt['public']); ?>
                </label>
                <button type="submit" class="btn-primary"><?php echo esc_html($txt['upload_btn']); ?></button>
            </form>
        </div>

        <div class="documents-list">
            <?php if (empty($documents)): ?>
                <div style="text-align: center; padding: 60px; color: var(--rtf-muted); background: var(--rtf-card); border-radius: 16px;">
                    <div style="font-size: 4em; margin-bottom: 20px;">📄</div>
                    <p style="font-size: 1.2em;"><?php echo esc_html($txt['no_docs']); ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($documents as $doc): ?>
                    <div class="doc-card" style="background: var(--rtf-card); padding: 25px; border-radius: 16px; box-shadow: 0 14px 35px rgba(15,23,42,0.10); margin-bottom: 15px; display: flex; align-items: center; gap: 20px;">
                        <div style="font-size: 3em;">📄</div>
                        <div style="flex: 1;">
                            <h4 style="margin-bottom: 5px; color: var(--rtf-text);"><?php echo esc_html($doc->file_name); ?></h4>
                            <p style="font-size: 0.9em; color: var(--rtf-muted);">
                                @<?php echo esc_html($doc->username); ?> • 
                                <?php echo esc_html($txt['size']); ?>: <?php echo number_format($doc->file_size / 1024, 2); ?> KB •
                                <?php echo rtf_time_ago($doc->created_at); ?>
                                <?php if ($doc->is_public): ?>
                                    • <span style="color: #10b981;">Offentlig</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <a href="<?php echo esc_url($doc->file_url); ?>" download class="btn-secondary" style="padding: 10px 20px; text-decoration: none; white-space: nowrap;"><?php echo esc_html($txt['download']); ?></a>
                            <?php if ($doc->user_id == $current_user->id || rtf_is_admin_user()): ?>
                                <a href="?delete=<?php echo $doc->id; ?>&lang=<?php echo $lang; ?>" onclick="return confirm('Slet dokument?')" style="color: #ef4444; padding: 10px; text-decoration: none;"><?php echo esc_html($txt['delete']); ?></a>
                            <?php endif; ?>
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
