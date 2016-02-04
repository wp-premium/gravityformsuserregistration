<?php

class GF_Pending_Activations {

	protected $_slug  = 'gravityformsuserregistration_pending_activations';
	protected $_title = 'Pending Activations';

	private static $_instance = null;

	public static function get_instance() {

		if ( self::$_instance == null ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	public function __construct() {

		if ( doing_action( 'init' ) || did_action( 'init' ) ) {
			$this->init();
		} else {
			add_action( 'init', array( $this, 'init' ) );
		}

	}

	public function init() {

		add_action( 'gform_form_settings_menu', array( $this, 'add_form_settings_menu' ), 10, 2 );
		add_action( 'admin_menu',               array( $this, 'register_submenu_page_under_users' ) );
		add_action( 'gform_entry_detail_sidebar_middle', array( $this, 'entry_pending_activation_meta_box' ), 10, 2 );

		$view    = rgget( 'view' );
		$subview = rgget( 'subview' );

		if ( rgget( 'page' ) == 'gf_edit_forms' && $view == 'settings' && $subview == $this->_slug ) {
			require_once( GFCommon::get_base_path() . '/tooltips.php' );
			add_action( 'gform_form_settings_page_' . $this->_slug, array( $this, 'form_settings_page' ) );
		}

	}

	public function add_form_settings_menu( $tabs, $form_id ) {
		if ( gf_user_registration()->has_feed_type( 'create', array( 'id' => $form_id ) ) ) {
			$tabs[] = array( 'name' => $this->_slug, 'label' => $this->_title );
		}

		return $tabs;
	}

	public function form_settings_page() {

		$form    = $this->get_current_form();
		$form_id = $form['id'];
		$form    = gf_apply_filters( 'gform_admin_pre_render', $form_id, $form );

		GFFormSettings::page_header( $this->_title );

		?>

		<div class="gform_panel gform_panel_form_settings" id="form_settings">

			<?php $this->get_page_content(); ?>

		</div>

		<?php
		GFFormSettings::page_footer();
	}

	public function get_current_form() {
		return rgempty( 'id', $_GET ) ? false : GFFormsModel::get_form_meta( rgget( 'id' ) );
	}

	public static function get_page_content() {

		require_once( gf_user_registration()->get_base_path() . '/includes/class-gf-pending-activations-list.php' );

		$form      = rgget( 'id' ) ? GFAPI::get_form( rgget( 'id' ) ) : false;
		$is_global = ! $form;

		?>

		<style type="text/css">
			.nav-tab-wrapper { margin: 0 0 10px !important; }
			.fixed .column-date { white-space: nowrap; width: auto; }
		</style>

		<div class="wrap">

			<?php

			printf( '<%1$s>%2$s</%1$s>', $is_global ? 'h2' : 'h3', __( 'Pending Activations', 'gravityformsuserregistration' ) );

			if ( rgpost( 'is_submit' ) ) {
				self::handle_submission();
				GFCommon::display_admin_message();
			}

			?>

			<form id="list_form" method="post" action="">

				<?php
				$table = new GF_Pending_Activations_List();
				$table->prepare_items();
				$table->display();
				?>

				<input type="hidden" name="is_submit" value="1" />
				<input type="hidden" id="single_action" name="single_action" value="" />
				<input type="hidden" id="item" name="item" value="" />

				<?php wp_nonce_field('action', 'action_nonce'); ?>

			</form>

		</div>

		<script type="text/javascript">

			function singleItemAction(action, activationKey) {
				jQuery('#item').val(activationKey);
				jQuery('#single_action').val(action);
				jQuery('#list_form')[0].submit();
			}

		</script>

		<?php
	}

	public static function get_pending_activations( $form_id, $args = array() ) {
		global $wpdb;

		if ( $form_id == 'all' ) {
			$form_id = '';
		}

		extract( wp_parse_args( $args, array(
			'order'     => 'DESC',
			'order_by'  => 'registered',
			'page'      => 1,
			'per_page'  => 10,
			'get_total' => false,
			'lead_id'   => false
		) ) );

		if ( ! is_multisite() ) {
			require_once( gf_user_registration()->get_base_path() . '/includes/signups.php' );
			GFUserSignups::prep_signups_functionality();
		}

		$where = array();

		if ( $form_id ) {
			$where[] = $wpdb->prepare( 'l.form_id = %d', $form_id );
		}

		if ( $lead_id ) {
			$where[] = $wpdb->prepare( "l.id = %d", $lead_id );
		}

		$where[] = "s.active = 0";
		$where   = 'WHERE ' . implode( ' AND ', $where );

		$order        = "ORDER BY {$order_by} {$order}";
		$offset       = ( $page * $per_page ) - $per_page;
		$limit_offset = $get_total ? '' : "LIMIT $per_page OFFSET $offset";
		$method       = $get_total ? 'get_var' : 'get_results';

		if ( $form_id ) {

			$select = $get_total ? 'SELECT count(s.activation_key)' : 'SELECT s.*';
			$sql    = "
                $select FROM {$wpdb->prefix}rg_lead_meta lm
                INNER JOIN {$wpdb->signups} s ON s.activation_key = lm.meta_value AND lm.meta_key = 'activation_key'
                INNER JOIN {$wpdb->prefix}rg_lead l ON l.id = lm.lead_id
                $where
                $order
                $limit_offset";

			$results = $wpdb->$method( $sql );

		} else {

			$select  = $get_total ? 'SELECT count(s.activation_key)' : 'SELECT s.*';
			$results = $wpdb->$method( "
                $select FROM $wpdb->signups s
                $where
                $order
                $limit_offset"
			);

		}

		return $results;
	}

	public static function handle_submission() {

		if ( ! wp_verify_nonce( rgpost( 'action_nonce' ), 'action' ) && ! check_admin_referer( 'action_nonce', 'action_nonce' ) ) {
			die( 'You have failed...' );
		}

		require_once( gf_user_registration()->get_base_path() . '/includes/signups.php' );
		GFUserSignups::prep_signups_functionality();

		$action = rgpost('single_action');
		$action = !$action ? rgpost('action') != -1 ? rgpost('action') : rgpost('action2') : $action;

		$items      = rgpost('item') ? array(rgpost('item')) : rgpost('items');
		$item_count = count( $items );
		$messages   = $errors = array();

		foreach ( $items as $key ) {

			switch ( $action ) {
				case 'delete':
					$success = GFUserSignups::delete_signup( $key );
					if ( $success ) {
						gf_user_registration()->add_message_once( _n( 'Item deleted.', 'Items deleted.', count( $items ), 'gravityformsuserregistration' ) );
					} else {
						gf_user_registration()->add_error_message_once( _n( 'There was an issue deleting this item.', 'There was an issue deleting one or more selected items.', count( $items ), 'gravityformsuserregistration' ) );
					}
					break;

				case 'activate':

					$userdata = GFUserSignups::activate_signup( $key );

					if ( is_wp_error( $userdata ) ) {
						$error = _n( 'There was an issue activating this item', 'There was an issue activating one or more selected items', count( $items ), 'gravityformsuserregistration' );
						$error .= ": " . $userdata->get_error_message();
						gf_user_registration()->add_error_message_once( $error );
					} else {
						$message = _n( 'User activated.', 'Users activated.', count( $items ), 'gravityformsuserregistration' );
						gf_user_registration()->add_message_once( $message );
					}

					break;
			}

		}

	}

	public function register_submenu_page_under_users() {
		add_submenu_page(
			'users.php',
			__( 'Pending Activations', 'gravityformsuserregistration' ),
			__( 'Pending Activations', 'gravityformsuserregistration' ),
			'gravityforms_userregistration',
			'gf-pending-activations',
			array( $this, 'pending_activations_page' )
		);
	}

	public function pending_activations_page() {
		self::get_page_content();
	}

	public function entry_pending_activation_meta_box( $form, $entry ) {

		if ( ! $this->is_entry_pending_activation( $entry ) ) {
			return;
		}

		require_once( gf_user_registration()->get_base_path() . '/includes/signups.php' );

		$entry_id       = rgar( $entry, 'id' );
		$activation_key = GFUserSignups::get_lead_activation_key( $entry_id );

		?>

		<div class="postbox" id="gf_user_registration_pending_activation">

			<h3 class="hndle" style="cursor:default;">
				<span><?php _e( 'User Registration', 'gravityforms' ); ?></span>
			</h3>

			<div class="inside">
				<div>

					<div id="gf_user_pending_activation">
						<a onclick="activateUser( '<?php echo $activation_key; ?>' );" class="button" id="gf_user_pending_activate_link" style="vertical-align:middle;">
							<?php _e( 'Activate User', 'gravityformuserregistraiton' ); ?>
						</a>
						<?php gform_tooltip( sprintf( '<h6>%s</h6> %s', __( 'Pending Activation', 'gravityformsuserregistration' ), __( 'This entry created a user who is pending activation. Click the "Activate User" button to activate the user.', 'gravityformsuserregistration' ) ) ); ?>
					</div>

				</div>
			</div>
		</div>

		<script type="text/javascript">

			function activateUser(activationKey) {

				if( ! confirm( '<?php _e('Are you sure you want to activate this user?', 'gravityformsuserregistration'); ?>' ) ) {
					return;
				}

				var spinner = new ajaxSpinner( '#gf_user_pending_activate_link', 'margin-left:10px' );

				jQuery.post( ajaxurl, {
					key: activationKey,
					action: 'gf_user_activate'
				}, function( response ) {

					// if there is an error message, alert it
					if( response ) {

						alert( response );
						jQuery( '#gf_user_pending_activation' ).fadeOut();
						spinner.destroy();

					} else {

						jQuery( '#gf_user_pending_activation' ).html( '<div class="updated" style="margin:-12px;"><p><?php _e( 'User Activated Successfully!', 'gravityformsuserregistration' ); ?></p></div>' );
						setTimeout( 'jQuery( "#gf_user_registration_pending_activation" ).slideUp();', 5000 );
						spinner.destroy();

					}

				});

			}

			function ajaxSpinner(elem, style) {

				this.elem = elem;
				this.image = '<img src="<?php echo GFCommon::get_base_url(); ?>/images/spinner.gif" style="' + style + '" />';

				this.init = function() {
					this.spinner = jQuery(this.image);
					jQuery(this.elem).after(this.spinner);
					return this;
				}

				this.destroy = function() {
					jQuery(this.spinner).remove();
				}

				return this.init();
			}

		</script>

		<?php
	}

	public function is_entry_pending_activation( $entry ) {
		global $wpdb;
		return self::get_pending_activations( $entry['form_id'], array( 'lead_id' => $entry['id'], 'get_total' => true ) ) > 0;
	}

}

function gf_pending_activations() {
	return GF_Pending_Activations::get_instance();
}