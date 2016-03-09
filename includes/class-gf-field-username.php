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
						'class'      => 'button',
						'value'      => $new_button['text'],
						'data-type'  => $this->type,
						'onclick'    => "StartAddField('{$this->type}');",
						'onkeypress' => "StartAddField('{$this->type}');",
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
	 * Return the button for the form editor.
	 * 
	 * @access public
	 * @return array
	 */
	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title()
		);
	}
	
	/**
	 * Return the JavaScript to set the default field label.
	 * 
	 * @access public
	 * @static
	 */
	public static function set_default_label() {
		
		echo 'case "username":
			field.label = "' . esc_html__( 'Username', 'gravityformsuserregistration' ) . '";
		break;';
		
	}

}

GF_Fields::register( new GF_Field_Username() );