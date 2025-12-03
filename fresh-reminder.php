<?php

/**
 * Plugin Name: Fresh Reminder
 * Description: Flags posts older than a configurable threshold and reminds editors to update them.
 * Version: 1.1.3
 * Author: Hasun Akash Bandara
 * License: GPL-2.0-or-later
 * Text Domain: fresh-reminder
 * Domain Path: /languages
 * Author URI: https://github.com/hasunB
 * Plugin URI: https://github.com/hasunB/fresh-reminder
 * Requires at least: 5.5
 * Requires PHP: 7.4
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Copyright: 2025 Hasun Akash Bandara
 */


defined('ABSPATH') || exit;

/* Constants */
define('FRESRE_VERSION', '1.1.3');  // Hold the version of the plugin
define('FRESRE_PLUGIN_FILE', __FILE__); // Hold the path of the plugin file "fresh-reminder.php"
define('FRESRE_PLUGIN_DIR', plugin_dir_path(__FILE__)); // Hold the absolute path of the plugin directory 
define('FRESRE_PLUGIN_URL', plugin_dir_url(__FILE__)); // Hold the web address (URL) of the plugin's directory. This is used to link to assets like CSS, JavaScript, or images.
define('FRESRE_OPTION_NAME', 'fresre_settings');
define('FRESRE_CACHE_OPTION', 'fresre_stale_posts_cache');

/* Includes */
require_once FRESRE_PLUGIN_DIR . 'includes/class-fresre-cron.php';
require_once FRESRE_PLUGIN_DIR . 'src/Admin/class-fresre-admin.php';
require_once FRESRE_PLUGIN_DIR . 'src/Utils/class-fresre-logger.php';

// Register custom cron schedules early
add_filter('cron_schedules', array('FRESRE_Cron', 'register_custom_schedules'));

/* Activation / Deactivation */
register_activation_hook(__FILE__, array('FRESRE_Cron', 'activate'));  // It's call the static "activate" method "FRESRE_Cron" class
register_deactivation_hook(__FILE__, array('FRESRE_Cron', 'deactivate'));  // It's call the static "deactivate" method "FRESRE_Cron" class

/* Init plugin */
add_action('plugins_loaded', function () {
    if (is_admin()) {
        FRESRE_Admin::init();
    }
});
