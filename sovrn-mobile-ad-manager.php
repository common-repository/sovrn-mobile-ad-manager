<?php
/**
 * Plugin Name: sovrn WPTouch mobile ad manager
 * Description: Allows adding/editing and displaying sovrn mobile ads on a Wordpress/WPTouch site. Requires WPTouch plugin to be installed.
 * Version: 0.2.0
 * Author: sovrn
 * Author URI: http://www.sovrn.net/
 */

// @todo Activation hook!
// @todo Check for WPTouch installed in admin
// @todo Add link to settings on plugin list page, maybe alert there for WPTouch?

// Shortcircuit if no Wordpress

if (!function_exists('add_filter')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

require_once 'lib/class.SovrnAdManager.php';

define('SOVRN_AD_MANAGER_VERSION', '0.2.0');
define('SOVRN_AD_MANAGER_DEBUG', false);
define('SOVRN_AD_MANAGER_MAIN_FILE', __FILE__);
define('SOVRN_AD_MANAGER_DIR_PATH', dirname(__FILE__));
define('SOVRN_AD_MANAGER_KEY', trim(basename(SOVRN_AD_MANAGER_DIR_PATH)));
define('SOVRN_AD_MANAGER_PLUGIN_FILE', sprintf('%s/%s.php', SOVRN_AD_MANAGER_KEY, SOVRN_AD_MANAGER_KEY));
define('SOVRN_AD_MANAGER_STATIC_DIR', SOVRN_AD_MANAGER_KEY . '/static'); // Relative static directory

if (SOVRN_AD_MANAGER_DEBUG) {
    require_once 'debug.php';
}

$sovrn = new SovrnAdManager;

?>