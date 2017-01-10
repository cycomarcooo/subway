<?php
/**
 * Includes all the file necessary for Subway.
 *
 * @package  Subway
 */

if ( ! defined( 'ABSPATH' ) ) { die(); }

// Redirect the user when he/she visit wp-admin or wp-login.php.
add_action( 'init', 'subway_redirect_login' );

// Redirect the user after successful logged in attempt.
add_filter( 'login_redirect', 'subway_redirect_user_after_logged_in', 10, 3 );

// Handle failed login redirection.
add_action( 'wp_login_failed', 'subway_redirect_login_handle_failure' );

/**
 * Redirect all wp-login.php post
 * and get request to the user assigned log-in page
 *
 * @return void
 */
function subway_redirect_login() {

	// Only run this function when on wp-login.php.
	if ( ! in_array( $GLOBALS['pagenow'], array( 'wp-login.php' ), true ) ) {
		return;
	}

	// Bypass login if specified.
	$no_redirect = filter_input( INPUT_GET, 'no_redirect', FILTER_VALIDATE_BOOLEAN );

	// Bypass wp-login.php?action=*.
	$has_action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );

	// Has any errors?
	$has_error = filter_input( INPUT_GET, 'error', FILTER_SANITIZE_STRING );

	// Error Types.
	$has_type = filter_input( INPUT_GET, 'type', FILTER_SANITIZE_STRING );

	// Set the default to our login page.
	$redirect_page = subway_get_redirect_page_url();

	if ( $has_error && $has_type ) {

		$redirect_to = add_query_arg( array(
				'login' => 'failed',
				'type' => $has_type,
		), $redirect_page );

		wp_safe_redirect( esc_url_raw( $redirect_to ) );

		die();
	}

	// Bypass wp-login.php?action=* link.
	if ( $has_action ) {
		return;
	}

	if ( $no_redirect ) {
		return;
	}

	// Check if buddypress activate page.
	if ( function_exists( 'bp_is_activation_page' ) ) {
		if ( bp_is_activation_page() ) {
			return;
		}
	}

	// Check if buddypress registration page.
	if ( function_exists( 'bp_is_register_page' ) ) {
		if ( bp_is_register_page() ) {
			return;
		}
	}

	// Store for checking if this page equals wp-login.php.
	$curr_paged = basename( $_SERVER['REQUEST_URI'] );

	if ( empty( $redirect_page ) ) {

		return;

	}

	// Ff user visits wp-admin or wp-login.php, redirect them.
	if ( strstr( $curr_paged, 'wp-login.php' ) ) {

		if ( isset( $_GET['interim-login'] ) ) {
			return;
		}

		// Check if there is an action present action might represent user trying to log out.
		if ( isset( $_GET['action'] ) ) {

			$action = $_GET['action'];

			if ( 'logout' === $action ) {

				return;

			}
		}

		// Only redirect if there are no incoming post data.
		if ( empty( $_POST ) ) {
			wp_safe_redirect( $redirect_page );
		}

		// Redirect to error page if user left username and password blank.
		if ( ! empty( $_POST ) ) {

			if ( empty( $_POST['log'] ) && empty( $_POST['pwd'] ) ) {
				$redirect_to = add_query_arg( array(
						'login' => 'failed',
						'type' => '__blank',
				), $redirect_page );

				wp_safe_redirect( esc_url_raw( $redirect_to ) );
			} elseif ( empty( $_POST['log'] ) && ! empty( $_POST['pwd'] ) && ! empty( $_POST['redirect_to'] ) ) {
				// Username empty.
				$redirect_to = add_query_arg( array(
						'login' => 'failed',
						'type' => '__userempty',
				), $redirect_page );

				wp_safe_redirect( esc_url_raw( $redirect_to ) );
			} elseif ( ! empty( $_POST['log'] ) && empty( $_POST['pwd'] ) && ! empty( $_POST['redirect_to'] ) ) {
				// Password empty.
				$redirect_to = add_query_arg( array(
						'login' => 'failed',
						'type' => '__passempty',
				), $redirect_page );

				wp_safe_redirect( esc_url_raw( $redirect_to ) );
			} else {
				wp_safe_redirect( $redirect_page );
			}
		}
	}

	return;
}

/**
 * Redirects the user to selected page or custom url.
 *
 * @param  string $redirect_to 'login_redirect' filter callback argument that holds the current $redirect_to value.
 * @param  object $request     'login_redirect' filter callback argument that holds request header.
 * @param  object $user        'login_redirect' filter callback argument that holds logged in user info.
 * @return string              The final redirection url
 */
function subway_redirect_user_after_logged_in( $redirect_to, $request, $user ) {

	$subway_redirect_type = get_option( 'subway_redirect_type' );

	// Redirect the user to default behaviour if there are no redirect type option saved.
	if ( empty( $subway_redirect_type ) ) {

		return $redirect_to;

	}

	if ( 'default' === $subway_redirect_type ) {
		return $redirect_to;
	}

	if ( 'page' === $subway_redirect_type ) {

		// Get the page url of the selected page if the admin selected 'Custom Page' in the redirect type settings.
		$selected_redirect_page = intval( get_option( 'subway_redirect_page_id' ) );

		// Redirect to default WordPress behaviour if the user did not select page.
		if ( empty( $selected_redirect_page ) ) {

			return $redirect_to;
		}

		// Otherwise, get the permalink of the saved page and let the user go into that page.
		return get_permalink( $selected_redirect_page );

	} elseif ( 'custom_url' === $subway_redirect_type ) {

		// Get the custom url saved in the redirect type settings.
		$entered_custom_url = get_option( 'subway_redirect_custom_url' );

		// Redirect to default WordPress behaviour if the user did enter a custom url.
		if ( empty( $entered_custom_url ) ) {

			return $redirect_to;

		}

		// Otherwise, get the custom url saved and let the user go into that page.
		$current_user = wp_get_current_user();

		$entered_custom_url = str_replace( '%user_id%', $user->ID, $entered_custom_url );

		$entered_custom_url = str_replace( '%user_name%', $user->user_login, $entered_custom_url );

		return $entered_custom_url;

	}

	// Otherwise, quit and redirect the user back to default WordPress behaviour.
	return $redirect_to;
}

/**
 * Handles the failure login attempt from customized login page.
 *
 * @param  object $user WordPress callback function.
 * @return void
 */
function subway_redirect_login_handle_failure( $user ) {

	// Pull the sign-in page url.
	$sign_in_page = wp_login_url();

	$custom_sign_in_page_url = subway_get_redirect_page_url();

	if ( ! empty( $custom_sign_in_page_url ) ) {

		$sign_in_page = $custom_sign_in_page_url;

	}

	// Check that were not on the default login page.
	if ( ! empty( $sign_in_page ) && ! strstr( $sign_in_page,'wp-login' ) && ! strstr( $sign_in_page,'wp-admin' ) && null !== $user ) {

		// make sure we don't already have a failed login attempt.
		if ( ! strstr( $sign_in_page, '?login=failed' ) ) {

			// Redirect to the login page and append a querystring of login failed.
			$permalink = add_query_arg( array(
	    		'login' => 'failed',
			), $custom_sign_in_page_url );

	  		wp_safe_redirect( esc_url_raw( $permalink ) );

	  		die();

	    } else {

	      	wp_safe_redirect( $sign_in_page );

	      	die();
	    }

	    return;
	}

	return;
}

/**
 * Helper function to return the user selected page
 * inside the 'Login' under 'Reading' settings
 *
 * @return void
 */
function subway_get_redirect_page_url() {

	$selected_login_post_id = intval( get_option( 'subway_login_page' ) );

	if ( 0 === $selected_login_post_id ) {

		return;

	}

	$login_post = get_post( $selected_login_post_id );

	if ( ! empty( $login_post ) ) {

		return get_permalink( $login_post->ID );

	}

	return false;

}
