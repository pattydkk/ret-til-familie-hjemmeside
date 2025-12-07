<?php
/**
 * MINIMAL SAFE VERSION - Test locally first
 * 
 * Rename this to functions.php to test locally
 * If it works, use full functions.php on live server
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    return;
}

// ABSOLUTE MINIMUM theme support
add_action('after_setup_theme', function() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
});

// Simple session start
add_action('init', function() {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
});

// Basic language function
function rtf_get_lang() {
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['da', 'sv', 'en'])) {
        return sanitize_key($_GET['lang']);
    }
    return 'da';
}

// Check if user is logged in
function rtf_is_logged_in() {
    return isset($_SESSION['rtf_user_id']) && !empty($_SESSION['rtf_user_id']);
}

// Get current user
function rtf_get_current_user() {
    if (!rtf_is_logged_in()) {
        return null;
    }
    
    global $wpdb;
    $user_id = intval($_SESSION['rtf_user_id']);
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}rtf_platform_users WHERE id = %d",
        $user_id
    ));
}

// Simple redirect helper
function rtf_redirect($url) {
    if (!headers_sent()) {
        wp_redirect($url);
        exit;
    }
}

echo "<!-- RTF Theme: Minimal version loaded successfully -->\n";
