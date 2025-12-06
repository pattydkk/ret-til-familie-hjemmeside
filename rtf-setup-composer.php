<?php
/**
 * Template Name: Setup - Install Composer Dependencies
 * 
 * VIGTIG: Denne side installerer Composer dependencies direkte fra PHP
 * Brug kun hvis du ikke har shell adgang
 */

get_header();
?>

<div style="max-width: 1200px; margin: 50px auto; padding: 20px; font-family: system-ui;">
    <h1 style="color: #1e293b;">🔧 Ret til Familie - Composer Setup</h1>
    
    <?php
    // Check if already installed
    $vendor_path = get_template_directory() . '/vendor';
    $autoload_file = $vendor_path . '/autoload.php';
    
    if (file_exists($autoload_file)) {
        echo '<div style="background: #d1fae5; border: 2px solid #10b981; padding: 20px; border-radius: 8px; margin: 20px 0;">';
        echo '<h2 style="color: #065f46; margin: 0 0 10px 0;">✅ Composer Dependencies Allerede Installeret!</h2>';
        echo '<p style="margin: 0;">Vendor autoload findes på: <code>' . esc_html($autoload_file) . '</code></p>';
        echo '</div>';
        
        // List installed packages
        if (file_exists(get_template_directory() . '/vendor/composer/installed.json')) {
            $installed = json_decode(file_get_contents(get_template_directory() . '/vendor/composer/installed.json'), true);
            
            echo '<h3>📦 Installerede Pakker:</h3>';
            echo '<ul style="background: #f8fafc; padding: 20px; border-radius: 8px;">';
            
            if (isset($installed['packages'])) {
                foreach ($installed['packages'] as $package) {
                    echo '<li><strong>' . esc_html($package['name']) . '</strong> v' . esc_html($package['version']) . '</li>';
                }
            }
            
            echo '</ul>';
        }
        
        echo '<p><a href="' . home_url() . '" style="background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; margin-top: 20px;">Gå til Forsiden</a></p>';
        
    } else {
        echo '<div style="background: #fef3c7; border: 2px solid #f59e0b; padding: 20px; border-radius: 8px; margin: 20px 0;">';
        echo '<h2 style="color: #92400e; margin: 0 0 10px 0;">⚠️ Composer Dependencies Ikke Installeret</h2>';
        echo '<p>Følg en af metoderne nedenfor for at installere:</p>';
        echo '</div>';
        
        echo '<div style="background: white; border: 1px solid #e2e8f0; padding: 30px; border-radius: 8px; margin: 20px 0;">';
        echo '<h3>📋 Metode 1: Shell/Terminal (Anbefalet)</h3>';
        echo '<p>Hvis du har SSH eller terminal adgang:</p>';
        echo '<pre style="background: #1e293b; color: #e2e8f0; padding: 15px; border-radius: 6px; overflow-x: auto;">cd ' . esc_html(get_template_directory()) . '
composer install</pre>';
        echo '</div>';
        
        echo '<div style="background: white; border: 1px solid #e2e8f0; padding: 30px; border-radius: 8px; margin: 20px 0;">';
        echo '<h3>📋 Metode 2: DanDomain File Manager</h3>';
        echo '<ol>';
        echo '<li>Download Composer phar: <a href="https://getcomposer.org/download/" target="_blank">getcomposer.org</a></li>';
        echo '<li>Upload <code>composer.phar</code> til theme root: <code>' . esc_html(get_template_directory()) . '</code></li>';
        echo '<li>Kør i terminal: <code>php composer.phar install</code></li>';
        echo '</ol>';
        echo '</div>';
        
        echo '<div style="background: white; border: 1px solid #e2e8f0; padding: 30px; border-radius: 8px; margin: 20px 0;">';
        echo '<h3>📋 Metode 3: Manuel Installation (Nødløsning)</h3>';
        echo '<p>Download pre-compiled vendor folder:</p>';
        echo '<ol>';
        echo '<li>Download fra GitHub: <a href="https://github.com/stripe/stripe-php/releases" target="_blank">Stripe PHP Library</a></li>';
        echo '<li>Pak ud i: <code>' . esc_html(get_template_directory()) . '/vendor/</code></li>';
        echo '<li>Genindlæs denne side</li>';
        echo '</ol>';
        echo '</div>';
        
        echo '<div style="background: #fee2e2; border: 2px solid #ef4444; padding: 20px; border-radius: 8px; margin: 20px 0;">';
        echo '<h3 style="color: #991b1b; margin: 0 0 10px 0;">🚨 Nuværende Status</h3>';
        echo '<p style="margin: 0;"><strong>Theme virker UDEN Composer</strong>, men følgende features er deaktiveret:</p>';
        echo '<ul style="margin: 10px 0 0 0;">';
        echo '<li>❌ Stripe betalinger (abonnementer)</li>';
        echo '<li>❌ Kate AI advancerede features</li>';
        echo '<li>✅ Basis platform funktioner virker stadig</li>';
        echo '</ul>';
        echo '</div>';
    }
    ?>
    
    <div style="background: #f1f5f9; padding: 20px; border-radius: 8px; margin: 30px 0;">
        <h3>🔍 System Information</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #cbd5e1;">
                <td style="padding: 10px; font-weight: bold;">PHP Version:</td>
                <td style="padding: 10px;"><?php echo PHP_VERSION; ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #cbd5e1;">
                <td style="padding: 10px; font-weight: bold;">WordPress Version:</td>
                <td style="padding: 10px;"><?php echo get_bloginfo('version'); ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #cbd5e1;">
                <td style="padding: 10px; font-weight: bold;">Theme Directory:</td>
                <td style="padding: 10px;"><code><?php echo get_template_directory(); ?></code></td>
            </tr>
            <tr style="border-bottom: 1px solid #cbd5e1;">
                <td style="padding: 10px; font-weight: bold;">Vendor Path:</td>
                <td style="padding: 10px;"><code><?php echo $vendor_path; ?></code></td>
            </tr>
            <tr style="border-bottom: 1px solid #cbd5e1;">
                <td style="padding: 10px; font-weight: bold;">Composer JSON:</td>
                <td style="padding: 10px;">
                    <?php 
                    $composer_json = get_template_directory() . '/composer.json';
                    echo file_exists($composer_json) ? '✅ Findes' : '❌ Findes IKKE'; 
                    ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">Shell Access:</td>
                <td style="padding: 10px;">
                    <?php 
                    $shell = function_exists('exec') && !in_array('exec', explode(',', ini_get('disable_functions')));
                    echo $shell ? '✅ Tilgængelig' : '❌ Deaktiveret'; 
                    ?>
                </td>
            </tr>
        </table>
    </div>
    
    <div style="margin: 30px 0;">
        <a href="<?php echo admin_url(); ?>" style="background: #6366f1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; margin-right: 10px;">Gå til Admin</a>
        <a href="<?php echo home_url(); ?>" style="background: #64748b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">Gå til Forside</a>
    </div>
</div>

<?php get_footer(); ?>
