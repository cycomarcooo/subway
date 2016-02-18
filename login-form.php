<?php
/**
 * The login for shortcode template
 *
 * @since 1.0
 */

if ( ! defined( 'ABSPATH' ) ) { die(); }
/**
 * Callback function to register 'subway_login'
 * shortcode in order to display the login form
 * inside the page using 'subway_login' shortcode
 *
 * @param  array $atts shortcode callback attributes
 * @return string 	output_buffer
 */
function subway_wp_login( $atts ) {

	// begin output buffering
	ob_start();

	$args = array(
	    'echo'           => true,
	    'form_id'        => 'loginform',
	    'label_username' => __( 'Username', 'subway' ),
	    'label_password' => __( 'Password', 'subway' ),
	    'label_remember' => __( 'Remember Me', 'subway' ),
	    'label_log_in'   => __( 'Log In', 'subway' ),
	    'id_username'    => 'user_login',
	    'id_password'    => 'user_pass',
	    'id_remember'    => 'rememberme',
	    'id_submit'      => 'wp-submit',
	    'remember'       => true,
	    'value_username' => '',
	    'value_remember' => false,
	);

	$error_login_message = '';
	$message_types = array();

	if ( isset( $_GET['login'] ) ) {

		if ( 'failed' === $_GET['login'] ) {

			if ( isset( $_GET['type'] ) ) {

				$message_types = array(

					'default' => array(
							'message' => __('Error: There was an error trying to sign-in to your account. Make sure your credentials are correct', 'subway')
						),
					'__blank' => array(
							'message' => __( 'Required: Username and Password cannot not be empty.', 'subway' )
						),
					'__userempty' => array(
							'message' => __( 'Required: Username cannot not be empty.', 'subway' )
						),
					'__passempty' => array(
							'message' => __( 'Required: Password cannot not be empty.', 'subway' )
						),
					'fb_invalid_email' => array(
							'message' => __( 'Facebook email is invalid or is not verified.', 'subway' )
						),
					'fb_error' => array(
							'message' => __( 'Facebook Application Error. Misconfig or App is rejected.', 'subway' )
						),
					'app_not_live' => array(
							'message' => __( 'Unable to fetch your Facebook profile.', 'subway' )
						),
					'gears_username_or_email_exists' => array(
							'message' => __('Username or e-mail already exists', 'subway')
						),
					'gp_error_authentication' => array(
							'message' => __( 'Google Plus Authentication Error. Invalid Client ID or Secret.', 'subway' )
						)
				);

				$message = $message_types['default']['message'];

				if ( array_key_exists ( $_GET['type'], $message_types ) ) {

					$message = $message_types[ $_GET['type'] ]['message'];

				}

				$error_login_message = '<div id="message" class="error">'. esc_html( $message ) .'</div>';

			} else {

				$error_login_message = '<div id="message" class="error">'.__( 'Error: Invalid username and password combination.', 'subway' ).'</div>';

			}
		}
	}

	if ( isset( $_GET['_redirected'] ) ) {
		$error_login_message = '<div id="message" class="success">'.__( 'Oops! Looks like you need to login in order to view the page.', 'subway' ).'</div>';
	}

	?>
	<div class="mg-top-35 mg-bottom-35 subway-login-form">
		<div class="subway-login-form-form">
			<div class="subway-login-form__actions">
				<h3>
					<?php _e('Account Sign-in', 'subway'); ?>
				</h3>
				<?php do_action( 'gears_login_form' ); ?>
			</div>
			<div class="subway-login-form-message">
				<?php echo $error_login_message; ?>
			</div>
			<div class="subway-login-form__form">
				<?php echo wp_login_form( $args ); ?>
			</div>
		</div>
	</div>
	<script>

	jQuery(document).ready(function($){

		"use strict";

		$(window).load( function(){

			var $input = $('.subway-login-form__form p > input');

			if ( $input.val().length >= 1 ) {
				$input.prev('label').addClass('inactive');
			}

		});

		$('.subway-login-form__form p > input').focusin(function(){
			$(this).prev('label').addClass('inactive');
		}).focusout(function(){
			if ( $(this).val().length < 1 ) {
				$(this).prev('label').removeClass('inactive');
			}
		});
	});
	</script>
	<?php

	return ob_get_clean();

}

add_action( 'login_form_middle', 'subway_add_lost_password_link' );

function subway_add_lost_password_link() {

	return '<p class="subway-login-lost-password"><a href="'.esc_url( wp_lostpassword_url( $redirect = "" ) ).'">' . __('Forgot Password', 'subway') . '</a></p>';

}

/**
 * callback function to 'init' hook to register
 * @return  void
 */
function subway_register_shortcode() {

	add_shortcode( 'subway_login', 'subway_wp_login' );

	return;
}

add_action( 'init', 'subway_register_shortcode' );
?>
