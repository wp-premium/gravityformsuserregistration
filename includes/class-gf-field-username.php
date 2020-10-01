<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class GF_Field_Username extends GF_Field_Text {

	public $type = 'username';

	/**
	 * Adds the field button to the specified group.
	 *
	 * @param array $field_groups
	 *
	 * @return array
	 */
	public function add_button( $field_groups ) {

		// Check a button for the type hasn't already been added
		foreach ( $field_groups as $group ) {
			foreach ( $group['fields'] as $button ) {
				if ( isset( $button['data-type'] ) && $button['data-type'] == $this->type ) {
					return $field_groups;
				}
			}
		}

		$new_button = $this->get_form_editor_button();
		if ( ! empty( $new_button ) ) {
			foreach ( $field_groups as &$group ) {
				if ( $group['name'] == $new_button['group'] ) {
					
					// Prepare username button.
					$username_button = array(
						'value'      => $new_button['text'],
						'class'            => 'button',
						'data-type'        => $this->type,
						'onclick'          => "StartAddField('{$this->type}');",
						'onkeypress'       => "StartAddField('{$this->type}');",
						'text'             => $this->get_form_editor_field_title(),
						'data-icon'        => empty( $new_button['icon'] ) ? $this->get_form_editor_field_icon() : $new_button['icon'],
						'data-description' => empty( $new_button['description'] ) ? $this->get_form_editor_field_description() : $new_button['description'],
					);
					
					// Get index of email button.
					foreach ( $group['fields'] as $i => $field ) {
						if ( 'email' === $field['data-type'] ) {
							$email_button = $field;
							$email_index  = $i;
						}
					}
					
					// Insert username button after email button.
					array_splice( $group['fields'], $email_index+1, 0, array( $username_button ) );
					
					// Remove email button.
					unset( $group['fields'][ $email_index ] );
					
					// Insert email button after password button.
					array_splice( $group['fields'], $email_index+2, 0, array( $email_button ) );					
					
					break;
				}
			}
		}
		
		return $field_groups;
	}

	/**
	 * Return the field title.
	 * 
	 * @access public
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'Username', 'gravityformsuserregistration' );
	}

	/**
	 * Returns the field's form editor description.
	 *
	 * @since 4.5
	 *
	 * @return string
	 */
	public function get_form_editor_field_description() {
		return esc_attr__( 'Allows users to choose their own username when registering a new account.', 'gravityformsuserregistration' );
	}

	/**
	 * Returns the field's form editor icon.
	 *
	 * This could be an icon url or a dashicons class.
	 *
	 * @since 4.5
	 *
	 * @return string
	 */
	public function get_form_editor_field_icon() {
		return gf_user_registration()->get_base_url() . '/images/menu-icon.svg';
	}

	/**
	 * Return the button for the form editor.
	 *
	 * @sicne unknown
	 * @since 4.5 Added icon and description to button array.
	 *
	 * @access public
	 * @return array
	 */
	public function get_form_editor_button() {
		return array(
			'group'       => 'advanced_fields',
			'text'        => $this->get_form_editor_field_title(),
			'icon'        => $this->get_form_editor_field_icon(),
			'description' => $this->get_form_editor_field_description(),
		);
	}

	/**
	 * Include the script to set the default label for new fields.
	 *
	 * @return string
	 */
	public function get_form_editor_inline_script_on_page_render() {
		return sprintf( "function SetDefaultValues_%s(field) {field.label = '%s';}", $this->type, $this->get_form_editor_field_title() ) . PHP_EOL;
	}

}

GF_Fields::register( new GF_Field_Username() );