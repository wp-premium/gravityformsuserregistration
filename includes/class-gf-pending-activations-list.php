<?php

require_once( ABSPATH . '/wp-admin/includes/class-wp-list-table.php' );

class GF_Pending_Activations_List extends WP_List_Table {

	var $_column_headers;
	var $_actions_added = false;

	function __construct() {

		$this->items = array();
		$this->_column_headers = array(
			array(
				'cb'         => '<input type="checkbox" />',
				'user_login' => __( 'Username', 'gravityformsuserregistration' ),
				'email'      => __( 'Email', 'gravityformsuserregistration' ),
				'date'       => __( 'Sign Up Date', 'gravityformsuserregistation' ),
			),
			array(),
			array()
		);

		parent::__construct();

	}

	function prepare_items() {

		$forms               = array();
		$per_page            = 10;
		$page                = rgget( 'paged' ) ? rgget( 'paged' ) : 1;
		$pending_activations = GF_Pending_Activations::get_pending_activations( rgget( 'id' ), array(
			'per_page' => $per_page,
			'page'     => $page
		) );
		$total_pending       = GF_Pending_Activations::get_pending_activations( rgget( 'id' ), array(
			'per_page'  => $per_page,
			'page'      => $page,
			'get_total' => true
		) );

		foreach ( $pending_activations as $pending_activation ) {

			$signup_meta = unserialize( $pending_activation->meta );

			$lead = RGFormsModel::get_lead( rgar( $signup_meta, 'lead_id' ) );

			$form_id           = $lead['form_id'];
			$form              = rgar( $forms, $form_id ) ? rgar( $forms, $form_id ) : RGFormsModel::get_form_meta( $form_id );
			$forms[ $form_id ] = $form;

			$item               = array();
			$item['form']       = $form['title'];
			$item['user_login'] = rgar( $signup_meta, 'user_login' );
			$item['email']      = rgar( $signup_meta, 'email' );
			$item['date']       = $lead['date_created'];

			// non-columns
			$item['lead_id']        = $lead['id'];
			$item['form_id']        = $form_id;
			$item['activation_key'] = $pending_activation->activation_key;

			array_push( $this->items, $item );

		}

		$this->set_pagination_args( array(
			'total_items' => $total_pending,
			'per_page'    => $per_page
		) );

	}

	function column_default( $item, $column_name ) {

		$value = rgar( $item, $column_name );

		if( $column_name == 'user_login' ) {
			$value .= '
	            <div class="row-actions">
	                <span class="inline hide-if-no-js">
	                    <a title="Activate this sign up" href="javascript: if(confirm(\'' . __( 'Activate this sign up? ', 'gravityformsuserregistration' ) . __( "\'Cancel\' to stop, \'OK\' to activate.", 'gravityformsuserregistration' ) . '\')) { singleItemAction(\'activate\',\'' . $item['activation_key'] . '\'); } ">Activate</a> |
	                </span>
	                <span class="inline hide-if-no-js">
	                    <a title="View the entry associated with this sign up" href="' . admin_url("admin.php?page=gf_entries&view=entry&id={$item['form_id']}&lid={$item['lead_id']}") . '">View Entry</a> |
	                </span>
	                <span class="inline hide-if-no-js">
	                    <a title="Delete this sign up?" href="javascript: if(confirm(\'' . __( 'Delete this sign up? ', 'gravityformsuserregistration' ) . __( "\'Cancel\' to stop, \'OK\' to delete.", 'gravityformsuserregistration' ) . '\')) { singleItemAction(\'delete\',\'' . $item['activation_key'] . '\'); } ">Delete</a>
	                </span>
	            </div>';
		}

		return $value;
	}

	function column_cb( $item ) {
		return '<input type="checkbox" name="items[]" value="' . $item['activation_key'] . '" />';
	}

	function column_date( $item ) {
		return GFCommon::format_date( rgar( $item, 'date' ), false );
	}

	function get_bulk_actions() {

		$actions = array(
			'activate' => __( 'Activate', 'gravityformsuserregistration' ),
			'delete'   => __( 'Delete', 'gravityformsuserregistration' )
		);

		return $actions;
	}

	function get_columns() {
		return array();
	}
}