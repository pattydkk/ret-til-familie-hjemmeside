<?php

namespace KateAI\WordPress;

class AdminPage {
    
    public function register() {
        add_menu_page(
            'Kate AI Indstillinger',
            'Kate AI',
            'manage_options',
            'kate-ai-settings',
            [$this, 'render_settings_page'],
            'dashicons-admin-comments',
            30
        );
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Save settings
        if (isset($_POST['kate_ai_save'])) {
            update_option('kate_ai_enabled', isset($_POST['kate_ai_enabled']) ? 1 : 0);
            update_option('kate_ai_disclaimer', sanitize_textarea_field($_POST['kate_ai_disclaimer']));
            update_option('kate_ai_theme_color', sanitize_hex_color($_POST['kate_ai_theme_color']));
            echo '<div class="notice notice-success"><p>Indstillinger gemt!</p></div>';
        }
        
        $enabled = get_option('kate_ai_enabled', 1);
        $disclaimer = get_option('kate_ai_disclaimer', 'Kate er en AI-assistent og erstatter ikke juridisk rådgivning.');
        $theme_color = get_option('kate_ai_theme_color', '#2563eb');
        
        ?>
        <div class="wrap">
            <h1>Kate AI Indstillinger</h1>
            
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row">Aktivér Kate AI</th>
                        <td>
                            <label>
                                <input type="checkbox" name="kate_ai_enabled" value="1" <?php checked($enabled, 1); ?>>
                                Tillad brugere at bruge Kate AI assistent
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Ansvarsfraskrivelse</th>
                        <td>
                            <textarea name="kate_ai_disclaimer" rows="4" cols="50" class="large-text"><?php echo esc_textarea($disclaimer); ?></textarea>
                            <p class="description">Tekst der vises i bunden af alle Kate AI svar</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Tema farve</th>
                        <td>
                            <input type="color" name="kate_ai_theme_color" value="<?php echo esc_attr($theme_color); ?>">
                            <p class="description">Primær farve for Kate AI chat widget</p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="kate_ai_save" class="button button-primary">Gem indstillinger</button>
                </p>
            </form>
            
            <hr>
            
            <h2>Statistik</h2>
            <?php
            global $wpdb;
            $table = $wpdb->prefix . 'rtf_platform_kate_chat';
            $total_messages = $wpdb->get_var("SELECT COUNT(*) FROM $table");
            $unique_sessions = $wpdb->get_var("SELECT COUNT(DISTINCT session_id) FROM $table");
            ?>
            <ul>
                <li><strong>Total beskeder:</strong> <?php echo number_format($total_messages); ?></li>
                <li><strong>Unikke sessioner:</strong> <?php echo number_format($unique_sessions); ?></li>
            </ul>
        </div>
        <?php
    }
}
