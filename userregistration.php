<?php
/*
Plugin Name: Gravity Forms User Registration Add-On
Plugin URI: http://www.gravityforms.com
Description: Allows WordPress users to be automatically created upon submitting a Gravity Form
Version: 3.2.1
Author: rocketgenius
Author URI: http://www.rocketgenius.com
Text Domain: gravityformsuserregistration
Domain Path: /languages

------------------------------------------------------------------------
Copyright 2009-2016 rocketgenius

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

define( 'GF_USER_REGISTRATION_VERSION', '3.2.1' );

add_action( 'gform_loaded', array( 'GF_User_Registration_Bootstrap', 'load' ), 5 );

class GF_User_Registration_Bootstrap {

	public static function load(){

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-user-registration.php' );
		require_once( 'includes/class-gf-login-widget.php' );

		GFAddOn::register( 'GF_User_Registration' );

	}

}

function gf_user_registration() {
	return GF_User_Registration::get_instance();
}

function gf_user_registration_login_form() {
	return gf_user_registration()->get_login_html();
}

if ( ! function_exists( 'wp_new_user_notification' ) ) {
// ----------- Forked from WP 4.4.1, restoring the $plaintext_pass param which was deprecated in WP 4.3.1. ---------------

	function wp_new_user_notification( $user_id, $plaintext_pass = '', $notify = '' ) {
		global $wpdb, $wp_hasher;
		$user = get_userdata( $user_id );

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$message = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
		$message .= sprintf( __( 'Email: %s' ), $user->user_email ) . "\r\n";

		$result = @wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration' ), $blogname ), $message );
		gf_user_registration()->log_wp_mail( $result, 'admin' );

		if ( 'admin' === $notify || ( empty( $plaintext_pass ) && empty( $notify ) ) ) {
			return;
		}

		$message = sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";

		if ( empty( $plaintext_pass ) ) {
			// Generate something random for a password reset key.
			$key = wp_generate_password( 20, false );

			/** This action is documented in wp-login.php */
			do_action( 'retrieve_password_key', $user->user_login, $key );

			// Now insert the key, hashed, into the DB.
			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . WPINC . '/class-phpass.php';
				$wp_hasher = new PasswordHash( 8, true );
			}
			$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );


			$message .= __( 'To set your password, visit the following address:' ) . "\r\n\r\n";
			$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . ">\r\n\r\n";
		} else {
			$message .= sprintf( __( 'Password: %s' ), $plaintext_pass ) . "\r\n\r\n";
		}

		$message .= wp_login_url() . "\r\n";

		$result = wp_mail( $user->user_email, sprintf( __( '[%s] Your username and password info' ), $blogname ), $message );
		gf_user_registration()->log_wp_mail( $result, 'user' );
	}
}