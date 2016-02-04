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
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Deactivate Thrive Intranet in case it is used to prevent conflict
deactivate_plugins( '/thrive-intranet/thrive-intranet.php' );

require_once plugin_dir_path( __FILE__ ) . 'login-form.php';
require_once plugin_dir_path( __FILE__ ) . 'functions.php';
require_once plugin_dir_path( __FILE__ ) . 'private.php';
