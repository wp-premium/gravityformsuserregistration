<?php
class GFUserData{

	public static function get_feed_by_form( $form_id, $is_active = false ) {

		_deprecated_function( __FUNCTION__, '3.0', 'gf_user_registration()->get_feeds' );

		return gf_user_registration()->get_feeds( $form_id );
	}

	public static function get_feeds_by_form( $form_id, $is_active = false ) {

		_deprecated_function( __FUNCTION__, '3.0', 'gf_user_registration()->get_feeds' );

		return gf_user_registration()->get_feeds( $form_id );
	}

	public static function get_user_by_entry_id( $entry_id ) {

		_deprecated_function( __FUNCTION__, '3.0', 'gf_user_registration()->get_user_by_entry_id' );

		return gf_user_registration()->get_user_by_entry_id( $entry_id );
	}
}
