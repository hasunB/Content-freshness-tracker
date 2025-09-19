<?php

/**
 * Plugin Name: Content Freshness Tracker
 * Description: Flags posts older than a configurable threshold and reminds editors to update them.
 * Version: 1.0.0
 * Author: Hasun Akash Bandara
 * License: GPL-2.0-or-later
 * Text Domain: content-freshness-tracker
 * Domain Path: /languages
 * Author URI: https://github.com/hasunB
 * Plugin URI: https://github.com/hasunB/Content-freshness-tracker
 */


defined( 'ABSPATH' ) || exit;

/* Constants */
define( 'CFT_VERSION', '1.0.0');  // Hold the version of the plugin
define( 'CFT_PLUGIN_FILE',__FILE__); // Hold the path of the plugin file "content-freshness-tracker.php"
define( 'CFT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) ); // Hold the absolute path of the plugin directory 'c:\xampp\htdocs\wp-plugin-dev\wp-content\plugins\content-freshness-tracker\'
define( 'CFT_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // Hold the web address (URL) of the plugin's directory. This is used to link to assets like CSS, JavaScript, or images.
define( 'CFT_OPTION_NAME', 'cft_settings' ); 
define( 'CFT_CACHE_OPTION', 'cft_stale_posts_cache' );

/* Includes */
require_once CFT_PLUGIN_DIR . 'includes/class-cft-cron.php';
require_once CFT_PLUGIN_DIR . 'admin/class-cft-admin.php';

/* Activation / Deactivation */
register_activation_hook(__FILE__,array('CFT_Cron', 'activate'));  // It's call the static "activate" method "CFT_Cron" class
register_deactivation_hook(__FILE__,array('CFT_Cron', 'deactivate'));  // It's call the static "deactivate" method "CFT_Cron" class

/* Init plugin */
add_action( 'plugins_loaded', function() {
    error_log("plugin loaded");
    // load_plugin_textdomain( 'content-freshness-tracker', false, dirname( plugin_basename( CFT_PLUGIN_FILE ) ) . '/languages' );
    if ( is_admin() ) {
        CFT_Admin::init();
    }
});