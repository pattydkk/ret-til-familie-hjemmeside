<?php
/**
 * EMERGENCY WP-CONFIG ADDITIONS
 * 
 * Copy these lines to the TOP of your wp-config.php file
 * (right after the opening <?php tag)
 */

// EMERGENCY MODE - Bypass all theme code
define('RTF_EMERGENCY_MODE', true);

// ENABLE FULL DEBUG LOGGING
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);

// INCREASE MEMORY LIMIT
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');

// DISABLE AUTO-UPDATES (for debugging)
define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);
