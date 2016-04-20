<?php
/**
Plugin Name: Gravity Forms User Registration Add-On
Plugin URI: http://www.gravityforms.com
Description: Allows WordPress users to be automatically created upon submitting a Gravity Form
Version: 3.3
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
**/

define( 'GF_USER_REGISTRATION_VERSION', '3.3' );

// If Gravity Forms is loaded, bootstrap the User Registration Add-On.
add_action( 'gform_loaded', array( 'GF_User_Registration_Bootstrap', 'load' ), 5 );

/**
 * Class GF_User_Registration_Bootstrap
 *
 * Handles the loading of the User Registration add-on and registers with the add-on framework
 */
class GF_User_Registration_Bootstrap {

	/**
	 * If the Feed Add-On Framework exists, User Registration Add-On and login widget are loaded.
	 *
	 * @access public
	 * @static
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-user-registration.php' );
		require_once( 'includes/class-gf-login-widget.php' );

		GFAddOn::register( 'GF_User_Registration' );

	}

}

/**
 * Returns an instance of the GF_User_Registration class
 *
 * @see    GF_User_Registration::get_instance()
 * @return object GF_User_Registration
 */
function gf_user_registration() {
	return GF_User_Registration::get_instance();
}

/**
 * Obtains the login form HTML markup
 *
 * @see    GF_User_Registration->get_login_html()
 * @return string The login form HTML
 */
function gf_user_registration_login_form() {
	return gf_user_registration()->get_login_html();
}