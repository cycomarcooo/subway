<?php
/**
 * Plugin Name: Subway
 * Description: A WordPress plugin that will help you make your website private. Useful for intranet websites.
 * Version: 1.0.0
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

// Include our plugin login forms and shortcodes.
require_once plugin_dir_path( __FILE__ ) . 'login-form.php';

// Include our plugin functions.
require_once plugin_dir_path( __FILE__ ) . 'functions.php';

// Include the script that handles the redirections.
require_once plugin_dir_path( __FILE__ ) . 'private.php';

// Disable Thrive-Intranet plugin if this plugin is active to prevent conflicts.
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

// Enable GitHub Updater.
add_action( 'init', 'subway_plugin_updater_init' );

/**
 * The callback function that wraps 'WP_GitHub_Updater'
 * to init action of WordPress to check for our plugin updates.
 *
 * @return void
 */
function subway_plugin_updater_init() {

	if ( is_admin() ) {

		include_once plugin_dir_path( __FILE__ ) . '/update-check.php';

		$repo_name = 'subway';

	    $config = array(
	        'slug' => plugin_basename( __FILE__ ),
	        'proper_folder_name' => 'subway',
	        'api_url' => sprintf('https://api.github.com/codehaiku/%s', $repo_name),
	        'raw_url' => sprintf('https://raw.github.com/codehaiku/%s/master', $repo_name),
	        'github_url' => sprintf('https://github.com/codehaiku/%s', $repo_name),
	        'zip_url' => sprintf('https://github.com/codehaiku/%s/zipball/master', $repo_name),
	        'sslverify' => true,
	        'requires' => '4.0',
	        'tested' => '4.4.2',
	        'readme' => 'README.md',
	        'access_token' => '', // Access private repositories by authorizing under Appearance > GitHub Updates when this example plugin is installed
	    );

    	new WP_GitHub_Updater( $config );

	}

	return;
}
