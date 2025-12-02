<?php
/**
 * GitHub Theme Updater
 * Automatically updates theme from GitHub repository
 * Usage: Place in theme root, updates via WordPress Admin → Updates
 */

if (!defined('ABSPATH')) {
    exit;
}

class RTF_GitHub_Updater {
    private $github_username = 'hansenhr89dkk';
    private $github_repo = 'ret-til-familie-hjemmeside';
    private $github_branch = 'main';
    private $theme_slug = 'ret-til-familie-hjemmeside';
    
    public function __construct() {
        add_filter('pre_set_site_transient_update_themes', [$this, 'check_for_update']);
        add_filter('upgrader_source_selection', [$this, 'after_update'], 10, 3);
        
        // Add custom update checker in admin
        add_action('admin_init', [$this, 'manual_update_check']);
    }
    
    /**
     * Check GitHub for updates
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        // Get current theme version
        $theme = wp_get_theme($this->theme_slug);
        $current_version = $theme->get('Version');
        
        // Get latest version from GitHub
        $github_data = $this->get_github_release();
        
        if ($github_data && version_compare($current_version, $github_data['version'], '<')) {
            $transient->response[$this->theme_slug] = [
                'theme' => $this->theme_slug,
                'new_version' => $github_data['version'],
                'url' => $github_data['url'],
                'package' => $github_data['download_url'],
            ];
        }
        
        return $transient;
    }
    
    /**
     * Get latest release from GitHub
     */
    private function get_github_release() {
        $api_url = "https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases/latest";
        
        $response = wp_remote_get($api_url, [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
            ],
            'timeout' => 15
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (empty($data['tag_name'])) {
            // Fallback to main branch if no releases
            return $this->get_github_main_branch();
        }
        
        return [
            'version' => ltrim($data['tag_name'], 'v'),
            'url' => $data['html_url'],
            'download_url' => $data['zipball_url'],
        ];
    }
    
    /**
     * Fallback: Get main branch if no releases exist
     */
    private function get_github_main_branch() {
        $download_url = "https://github.com/{$this->github_username}/{$this->github_repo}/archive/refs/heads/{$this->github_branch}.zip";
        
        return [
            'version' => date('Y.m.d'), // Use date as version
            'url' => "https://github.com/{$this->github_username}/{$this->github_repo}",
            'download_url' => $download_url,
        ];
    }
    
    /**
     * After update: Run composer install
     */
    public function after_update($source, $remote_source, $upgrader) {
        global $wp_filesystem;
        
        // Check if this is our theme update
        if (!isset($upgrader->skin->theme_info) || $upgrader->skin->theme_info->get_stylesheet() !== $this->theme_slug) {
            return $source;
        }
        
        // Run composer install after theme update
        $theme_dir = $wp_filesystem->wp_themes_dir() . '/' . $this->theme_slug;
        
        if (file_exists($theme_dir . '/composer.json')) {
            // Check if Composer is available
            $composer_paths = [
                '/usr/local/bin/composer',
                '/usr/bin/composer',
                'composer', // Global
            ];
            
            foreach ($composer_paths as $composer) {
                if ($this->is_command_available($composer)) {
                    $command = "cd " . escapeshellarg($theme_dir) . " && $composer install --no-dev --optimize-autoloader 2>&1";
                    $output = [];
                    $return_var = 0;
                    exec($command, $output, $return_var);
                    
                    if ($return_var === 0) {
                        error_log('RTF Theme: Composer dependencies installed successfully');
                    } else {
                        error_log('RTF Theme: Composer install failed - ' . implode("\n", $output));
                    }
                    break;
                }
            }
        }
        
        return $source;
    }
    
    /**
     * Check if command is available
     */
    private function is_command_available($command) {
        $check = shell_exec("which $command");
        return !empty($check);
    }
    
    /**
     * Manual update check button in admin
     */
    public function manual_update_check() {
        if (!current_user_can('update_themes')) {
            return;
        }
        
        if (isset($_GET['rtf_check_update']) && $_GET['rtf_check_update'] === '1') {
            delete_site_transient('update_themes');
            wp_redirect(admin_url('themes.php'));
            exit;
        }
    }
}

// Initialize updater
new RTF_GitHub_Updater();
