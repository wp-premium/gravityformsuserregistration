<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

add_action( 'widgets_init', 'gf_userregistration_register_widget' );

/**
 * Register Gravity Forms Login widget.
 */
function gf_userregistration_register_widget() {
	register_widget( 'GFLoginWidget' );
}

if ( ! class_exists( 'GFLoginWidget' ) ) {

	/**
	 * Adds Gravity Forms Login widget.
	 *
	 * @see WP_Widget
	 */
	class GFLoginWidget extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 */
		public function __construct() {

			// Load text domain.
			gf_user_registration()->load_text_domain();

			// Initialize widget.
			WP_Widget::__construct(
				'gform_login_widget',
				esc_html__( 'Login', 'gravityformsuserregistration' ),
				array(
					'classname'   => 'gform_login_widget',
					'description' => esc_html__( 'Gravity Forms Login Widget', 'gravityformsuserregistration' ),
				),
				array(
					'width'   => 200,
					'height'  => 250,
					'id_base' => 'gform_login_widget',
				)
			);

		}

		/**
		 * Displays login form.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @access public
		 * @param  array $args Widget arguments.
		 * @param  array $instance Saved values from database.
		 */
		public function widget( $args, $instance ) {

			extract( $args );

			// Open widget.
			$widget = $before_widget;

			// Get the widget title.
			$title = is_user_logged_in() ? $instance['logged_in_title'] : $instance['title'];
			$title = GFCommon::replace_variables( $title, array(), array() );
			$title = apply_filters( 'widget_title', $title );

			// Display the widget title.
			$widget .= $title ? $before_title . $title . $after_title : null;

			// Get the tab index.
			$tabindex = is_numeric( $instance['tabindex'] ) ? $instance['tabindex'] : 1;

			// Create form.
			$form = gf_user_registration()->login_form_object();

			if ( empty( $instance['disable_scripts'] ) && ! is_admin() ) {
				RGForms::print_form_scripts( $form, false );
			}

			$form_markup = gf_user_registration()->get_login_html( array(
				'display_title'         => false,
				'display_description'   => false,
				'logged_in_avatar'      => '1' === $instance['logged_in_avatar'] ? true : false,
				'logged_in_links'       => $instance['logged_in_links'],
				'logged_in_message'     => $instance['logged_in_message'],
				'logged_out_links'      => $instance['logged_out_links'],
				'login_redirect'        => $instance['login_redirect_url'],
				'logout_redirect'       => $instance['logout_redirect_url'],
				'tabindex'              => $tabindex,
			) );

			// Display form.
			$widget .= $form_markup;
			$widget .= $after_widget;

			echo $widget;

		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @access public
		 * @param mixed $new_instance Values just sent to be saved.
		 * @param mixed $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {

			// Prepare instance.
			$instance                           = $old_instance;
			$instance['active_view']            = rgar( $new_instance, 'active_view' );
			$instance['title']                  = strip_tags( $new_instance['title'] );
			$instance['tabindex']               = rgar( $new_instance, 'tabindex' );
			$instance['login_redirect_url']     = rgar( $new_instance, 'login_redirect_url' );
			$instance['logged_in_title']        = rgar( $new_instance, 'logged_in_title' );
			$instance['logged_in_avatar']       = rgar( $new_instance, 'logged_in_avatar' );
			$instance['logged_in_message']      = rgar( $new_instance, 'logged_in_message' );
			$instance['logged_in_links']        = json_decode( rgar( $new_instance, 'logged_in_links' ), true );
			$instance['logged_out_links']       = json_decode( rgar( $new_instance, 'logged_out_links' ), true );
			$instance['logout_redirect_url']    = rgar( $new_instance, 'logout_redirect_url' );

			// Remove empty logged in links.
			foreach ( $instance['logged_in_links'] as $i => $link ) {
				if ( rgblank( $link['text'] ) && rgblank( $link['url'] ) ) {
					unset( $instance['logged_in_links'][ $i ] );
				}
			}
			$instance['logged_in_links'] = array_values( $instance['logged_in_links'] );

			// Remove empty logged out links.
			foreach ( $instance['logged_out_links'] as $i => $link ) {
				if ( rgblank( $link['text'] ) && rgblank( $link['url'] ) ) {
					unset( $instance['logged_out_links'][ $i ] );
				}
			}
			$instance['logged_out_links'] = array_values( $instance['logged_out_links'] );

			// Loop through instance properties and sanitize.
			foreach ( $instance as $key => &$value ) {

				// Loop through array items and sanitize individually.
				if ( is_array( $value ) ) {

					foreach ( $value as &$child_item ) {

						// If child item is array, map sanitization to array.
						$child_item = is_array( $child_item ) ? array_map( 'sanitize_text_field', $child_item ) : sanitize_text_field( $child_item );

					}

				} else {

					$value = sanitize_text_field( $value );

				}

			}

			return $instance;

		}

		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @access public
		 * @param  array $instance Previously saved values from database.
		 */
		public function form( $instance ) {

			// Add defaults to widget instance.
			$instance = wp_parse_args(
				(array) $instance,
				array(
					'active_view'            => 'logged-out',
					'title'                  => __( 'Login', 'gravityformsuserregistration' ),
					'tabindex'               => '1',
					'login_redirect_url'     => '',
					'logged_in_title'        => 'Welcome {user:display_name}',
					'logged_in_avatar'       => '1',
					'logged_in_message'      => '',
					'logout_redirect_url'    => '',
					'logged_in_links'        => array(
						array(
							'text' => esc_html__( 'Logout', 'gravityformsuserregistration' ),
							'url'  => '{logout_url}',
						),
					),
					'logged_out_links'       => array(
						array(
							'text' => esc_html__( 'Register', 'gravityformsuserregistration' ),
							'url'  => '{register_url}',
						),
						array(
							'text' => esc_html__( 'Forgot Password?', 'gravityformsuserregistration' ),
							'url'  => '{password_url}',
						),
					),
				)
			);

			// Ensure there is at least one logged in links row.
			if ( empty( $instance['logged_in_links'] ) ) {
				$instance['logged_in_links'] = array( array( 'text' => null, 'url' => null ) );
			}

			// Ensure there is at least one logged out links row.
			if ( empty( $instance['logged_out_links'] ) ) {
				$instance['logged_out_links'] = array( array( 'text' => null, 'url' => null ) );
			}

		?>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'gravityformsuserregistration' ); ?>:</label>
				<input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" style="width:90%;" />
			</p>

			<div class="gf_login_widget_editor">

				<nav class="tabs">
					<a href="#" data-tab="logged-out"<?php echo 'logged-out' === $instance['active_view'] ? ' class="active"' : ''; ?>><span class="tabtitle">Logged Out</span></a>
					<a href="#" data-tab="logged-in"<?php echo 'logged-in' === $instance['active_view'] ? ' class="active"' : ''; ?>><span class="tabtitle">Logged In</span></a>
				</nav>
				<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'active_view' ) ); ?>" data-type="active_view" value="<?php echo esc_attr( $instance['active_view'] ); ?>" />

				<div class="tab-content" data-tab="logged-out"<?php echo 'logged-out' === $instance['active_view'] ? ' style="display:block;"' : ''; ?>>

					<!-- Login Redirect URL -->
					<p>
						<label for="<?php echo esc_attr( $this->get_field_id( 'login_redirect_url' ) ); ?>"><?php esc_html_e( 'Login Redirect URL', 'gravityformsuserregistration' ); ?>:</label><br />
						<input id="<?php echo esc_attr( $this->get_field_id( 'login_redirect_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'login_redirect_url' ) ); ?>" value="<?php echo esc_attr( $instance['login_redirect_url'] ); ?>" placeholder="<?php esc_attr_e( 'Current Page', 'gravityformsuserregistration' ); ?>" class="widefat" />
					</p>

					<!-- Logged Out Links -->
					<label for=""><?php esc_html_e( 'Links', 'gravityformsuserregistration' ); ?>:</label><br />
					<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'logged_out_links' ) ); ?>" class="gf_login_widget_links_json" value='<?php echo json_encode( $instance['logged_out_links'] ); ?>' />
					<table class="gf_login_widget_links" cellpadding="0" cellspacing="0">
						<?php
						if ( ! empty( $instance['logged_out_links'] ) ) {
							foreach ( $instance['logged_out_links'] as $i => $link ) {
						?>
						<tr>
							<td width="47%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_out_links' ) ); ?>[<?php echo esc_attr( $i ); ?>][text]" class="widefat" placeholder="<?php esc_attr_e( 'Link Text', 'gravityformsuserregistration' ); ?>" value="<?php echo esc_attr( $link['text'] ); ?>" /></td>
							<td width=".5%">&nbsp;</td>
							<td width="47%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_out_links' ) ); ?>[<?php echo esc_attr( $i ); ?>][url]" class="widefat" placeholder="<?php esc_attr_e( 'Link URL', 'gravityformsuserregistration' ); ?>" value="<?php echo esc_attr( $link['url'] ); ?>" /></td>
							<td width=".5%">&nbsp;</td>
							<td width="5%"><a href="#" data-action="delete-link"><img src="<?php echo esc_attr( GFCommon::get_base_url() ); ?>/images/remove.png" alt="Delete Link" /></a></td>
						</tr>
						<?php } } ?>
						<tr data-repeater style="display:none;">
							<td width="47%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_out_links' ) ); ?>" class="widefat link-text" placeholder="<?php esc_attr_e( 'Link Text', 'gravityformsuserregistration' ); ?>" /></td>
							<td width=".5%">&nbsp;</td>
							<td width="47%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_out_links' ) ); ?>" class="widefat link-url" placeholder="<?php esc_attr_e( 'Link URL', 'gravityformsuserregistration' ); ?>" /></td>
							<td width=".5%">&nbsp;</td>
							<td width="5%"><a href="#" data-action="delete-link"><img src="<?php echo esc_attr( GFCommon::get_base_url() ); ?>/images/remove.png" alt="Delete Link" /></a></td>
						</tr>
					</table>
					<a href="#" class="gf_login_widget_add_link"><?php esc_html_e( 'Add Link', 'gravityformsuserregistration' ); ?></a>

					<!-- Tab Index -->
					<p>
						<label for="<?php echo esc_attr( $this->get_field_id( 'tabindex' ) ); ?>"><?php esc_html_e( 'Tab Index Start', 'gravityformsuserregistration' ); ?>: </label>
						<input id="<?php echo esc_attr( $this->get_field_id( 'tabindex' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tabindex' ) ); ?>" value="<?php echo esc_attr( rgar( $instance, 'tabindex' ) ); ?>" style="width:15%;" /><br />
					</p>

				</div>

				<div class="tab-content" data-tab="logged-in"<?php echo 'logged-in' === $instance['active_view'] ? ' style="display:block;"' : ''; ?>>

					<!-- Logged In Widget Title -->
					<p>
						<label for="<?php echo esc_attr( $this->get_field_id( 'logged_in_title' ) ); ?>"><?php esc_html_e( 'Title', 'gravityformsuserregistration' ); ?>:</label><br />
						<input id="<?php echo esc_attr( $this->get_field_id( 'logged_in_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'logged_in_title' ) ); ?>" value="<?php echo esc_attr( $instance['logged_in_title'] ); ?>" class="widefat" />
					</p>

					<!-- Welcome Message -->
					<p>
						<label for="<?php echo esc_attr( $this->get_field_id( 'logged_in_message' ) ); ?>"><?php esc_html_e( 'Welcome Message', 'gravityformsuserregistration' ); ?>:</label><br />
						<textarea id="<?php echo esc_attr( $this->get_field_id( 'logged_in_message' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'logged_in_message' ) ); ?>" class="widefat" rows="6"><?php echo esc_attr( $instance['logged_in_message'] ); ?></textarea>
					</p>

					<!-- Logged In Links -->
					<label for=""><?php esc_html_e( 'Links', 'gravityformsuserregistration' ); ?>:</label><br />
					<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'logged_in_links' ) ); ?>" class="gf_login_widget_links_json" value='<?php echo json_encode( $instance['logged_in_links'] ); ?>' />
					<table class="gf_login_widget_links" cellpadding="0" cellspacing="0">
						<?php
						if ( ! empty( $instance['logged_in_links'] ) ) {
							foreach ( $instance['logged_in_links'] as $i => $link ) {
						?>
						<tr>
							<td width="47%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_in_links' ) ); ?>[<?php echo esc_attr( $i ); ?>][text]" class="widefat" placeholder="<?php esc_attr_e( 'Link Text', 'gravityformsuserregistration' ); ?>" value="<?php echo esc_attr( $link['text'] ); ?>" /></td>
							<td width=".5%">&nbsp;</td>
							<td width="47%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_in_links' ) ); ?>[<?php echo esc_attr( $i ); ?>][url]" class="widefat" placeholder="<?php esc_attr_e( 'Link URL', 'gravityformsuserregistration' ); ?>" value="<?php echo esc_attr( $link['url'] ); ?>" /></td>
							<td width=".5%">&nbsp;</td>
							<td width="5%"><a href="#" data-action="delete-link"><img src="<?php echo esc_attr( GFCommon::get_base_url() ); ?>/images/remove.png" alt="Delete Link" /></a></td>
						</tr>
						<?php } } ?>
						<tr data-repeater style="display:none;">
							<td width="47%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_in_links' ) ); ?>" class="widefat link-text" placeholder="<?php esc_attr_e( 'Link Text', 'gravityformsuserregistration' ); ?>" /></td>
							<td width=".5%">&nbsp;</td>
							<td width="47%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_in_links' ) ); ?>" class="widefat link-url" placeholder="<?php esc_attr_e( 'Link URL', 'gravityformsuserregistration' ); ?>" /></td>
							<td width=".5%">&nbsp;</td>
							<td width="5%"><a href="#" data-action="delete-link"><img src="<?php echo esc_attr( GFCommon::get_base_url() ); ?>/images/remove.png" alt="Delete Link" /></a></td>
						</tr>
					</table>
					<a href="#" class="gf_login_widget_add_link"><?php esc_html_e( 'Add Link', 'gravityformsuserregistration' ); ?></a>

					<!-- Logout Redirect URL -->
					<p>
						<label for="<?php echo esc_attr( $this->get_field_id( 'logout_redirect_url' ) ); ?>"><?php esc_html_e( 'Logout Redirect URL', 'gravityformsuserregistration' ); ?>:</label><br />
						<input id="<?php echo esc_attr( $this->get_field_id( 'logout_redirect_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'logout_redirect_url' ) ); ?>" value="<?php echo esc_attr( $instance['logout_redirect_url'] ); ?>" class="widefat" />
					</p>

					<!-- Logged In Avatar -->
					<p>
						<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'logged_in_avatar' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'logged_in_avatar' ) ); ?>" <?php checked( rgar( $instance, 'logged_in_avatar' ) ); ?> value="1" />
						<label for="<?php echo esc_attr( $this->get_field_id( 'logged_in_avatar' ) ); ?>"><?php esc_html_e( 'Show user avatar', 'gravityformsuserregistration' ); ?></label><br />
					</p>

				</div>

			</div>

		<?php
		}
	}
}
