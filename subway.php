<?php
/**
 * Plugin Name: Subway
 * Description: A WordPress plugin that will help you make your website private. Useful for intranet websites.
 * Version: 0.1
 * Author: Dunhakdis
 * Author URI: http://dunhakdis.me
 * Text Domain: subway
 * License: GPL2
 *
 * Includes all the file necessary for Subway.
 *
 * PHP version 5
 *
 * @since     1.0
 * @package subway
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

require_once plugin_dir_path( __FILE__ ) . 'login-form.php';
require_once plugin_dir_path( __FILE__ ) . 'functions.php';
require_once plugin_dir_path( __FILE__ ) . 'private.php';

register_activation_hook( __FILE__, 'subway_deactivate_thrive_intranet' );

/**
 * Register our activation hook
 * This will actually deactivate the Thrive Intranet plugin.
 *
 * @return void
 */
function subway_deactivate_thrive_intranet() {

	// Deactivate Thrive Intranet in case it is used to prevent conflict.
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	// Deactivate the plugin.
	deactivate_plugins( '/thrive-intranet/thrive-intranet.php' );

	return;
}
