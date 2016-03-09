<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

add_action( 'widgets_init', 'gf_userregistration_register_widget' );

function gf_userregistration_register_widget() {
	register_widget( 'GFLoginWidget' );
}

if ( ! class_exists( 'GFLoginWidget' ) ) {
	class GFLoginWidget extends WP_Widget {

		function __construct() {

			//load text domains
			GFCommon::load_gf_text_domain( 'gravityformsuserregistration' );

			$description = esc_html__( 'Gravity Forms Login Widget', 'gravityformsuserregistration' );

			WP_Widget::__construct( 
				'gform_login_widget',
				__( 'Login', 'gravityformsuserregistration' ),
				array( 'classname' => 'gform_login_widget', 'description' => $description ),
				array( 'width' => 200, 'height' => 250, 'id_base' => 'gform_login_widget' )
			);

		}

		function widget( $args, $instance ) {

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
				'logged_in_avatar'      => boolval( $instance['logged_in_avatar'] ),
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

		function update( $new_instance, $old_instance ) {
			
			$instance                           = $old_instance;
			$instance['title']                  = strip_tags( $new_instance['title'] );
			$instance['tabindex']               = rgar( $new_instance, 'tabindex' );
			$instance['login_redirect_url']     = rgar( $new_instance, 'login_redirect_url' );
			$instance['logged_in_title']        = rgar( $new_instance, 'logged_in_title' );
			$instance['logged_in_avatar']       = rgar( $new_instance, 'logged_in_avatar' );
			$instance['logged_in_message']      = rgar( $new_instance, 'logged_in_message' );
			$instance['logged_in_links']        = json_decode( rgar( $new_instance, 'logged_in_links' ), true );
			$instance['logged_out_links']       = json_decode( rgar( $new_instance, 'logged_out_links' ), true );
			$instance['logout_redirect_url']    = rgar( $new_instance, 'logout_redirect_url' );

			foreach ( $instance['logged_in_links'] as $i => $link ) {
				
				if ( rgblank( $link['text'] ) && rgblank( $link['url'] ) ) {
					unset( $instance['logged_in_links'][$i] );
				}
				
			}
			$instance['logged_in_links'] = array_values( $instance['logged_in_links'] );

			foreach ( $instance['logged_out_links'] as $i => $link ) {
				
				if ( rgblank( $link['text'] ) && rgblank( $link['url'] ) ) {
					unset( $instance['logged_out_links'][$i] );
				}
				
			}
			$instance['logged_out_links'] = array_values( $instance['logged_out_links'] );

			return $instance;
		}

		function form( $instance ) {

			$instance = wp_parse_args(
				(array) $instance,
				array(
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
							'url'  => '{logout_url}'
						)
					),
					'logged_out_links'       => array(
						array(
							'text' => esc_html__( 'Register', 'gravityformsuserregistration' ),
							'url'  => '{register_url}'
						),
						array(
							'text' => esc_html__( 'Forgot Password?', 'gravityformsuserregistration' ),
							'url'  => '{password_url}'
						),
					)
				)
			);
			
		?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'gravityformsuserregistration' ); ?>:</label>
				<input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" style="width:90%;" />
			</p>
			
			<p>
				<a href="#" onclick="jQuery( this ).parent().next().toggle( 'slow' ); return false;"><?php esc_html_e( 'Login Form Options', 'gravityforms' ); ?></a>
			</p>
			<div class="gf_login_widget_loginform" style="display:none;">
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'login_redirect_url' ) ); ?>"><?php esc_html_e( 'Login Redirect URL', 'gravityformsuserregistration' ); ?>:</label><br />
					<input id="<?php echo esc_attr( $this->get_field_id( 'login_redirect_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'login_redirect_url' ) ); ?>" value="<?php echo esc_attr( $instance['login_redirect_url'] ); ?>" class="widefat" />
				</p>
				<label for=""><?php esc_html_e( 'Links', 'gravityformsuserregistration' ); ?>:</label><br />
				<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'logged_out_links' ) ); ?>" class="gf_login_widget_links_json" value='<?php echo json_encode( $instance['logged_out_links'] ); ?>' />
				<table class="gf_login_widget_links">
					<?php
						if ( ! empty( $instance['logged_out_links'] ) ) {
							foreach ( $instance['logged_out_links'] as $i => $link ) {
					?>
					<tr>
						<td width="47.5%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_out_links' ) ); ?>[<?php echo $i; ?>][text]" class="widefat" placeholder="<?php esc_attr_e( 'Link Text', 'gravityformsuserregistration' ); ?>" value="<?php echo esc_attr( $link['text'] ); ?>" /></td>
						<td width="47.5%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_out_links' ) ); ?>[<?php echo $i; ?>][url]" class="widefat" placeholder="<?php esc_attr_e( 'Link URL', 'gravityformsuserregistration' ); ?>" value="<?php echo esc_attr( $link['url'] ); ?>" /></td>
						<td width="5%"><a href="#" data-action="delete-link"><img src="<?php echo GFCommon::get_base_url(); ?>/images/remove.png" alt="Delete Link" /></a></td>
					</tr>
					<?php } } ?>
					<tr data-repeater style="display:none;">
						<td width="47.5%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_out_links' ) ); ?>" class="widefat link-text" placeholder="<?php esc_attr_e( 'Link Text', 'gravityformsuserregistration' ); ?>" /></td>
						<td width="47.5%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_out_links' ) ); ?>" class="widefat link-url" placeholder="<?php esc_attr_e( 'Link URL', 'gravityformsuserregistration' ); ?>" /></td>
						<td width="5%"><a href="#" data-action="delete-link"><img src="<?php echo GFCommon::get_base_url(); ?>/images/remove.png" alt="Delete Link" /></a></td>
					</tr>
				</table>
				<a href="#" class="gf_login_widget_add_link">Add Link</a>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'tabindex' ) ); ?>"><?php esc_html_e( 'Tab Index Start', 'gravityforms' ); ?>: </label>
					<input id="<?php echo esc_attr( $this->get_field_id( 'tabindex' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tabindex' ) ); ?>" value="<?php echo esc_attr( rgar( $instance, 'tabindex' ) ); ?>" style="width:15%;" /><br />
				</p>
			</div>

			<p>
				<a href="#" onclick="jQuery( this ).parent().next().toggle( 'slow' ); return false;"><?php esc_html_e( 'Logged In Options', 'gravityforms' ); ?></a>
			</p>
			<div class="gf_login_widget_loggedin" style="display:none;">
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'logged_in_title' ) ); ?>"><?php esc_html_e( 'Title', 'gravityformsuserregistration' ); ?>:</label><br />
					<input id="<?php echo esc_attr( $this->get_field_id( 'logged_in_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'logged_in_title' ) ); ?>" value="<?php echo esc_attr( $instance['logged_in_title'] ); ?>" class="widefat" />
				</p>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'logged_in_message' ) ); ?>"><?php esc_html_e( 'Welcome Message', 'gravityformsuserregistration' ); ?>:</label><br />
					<textarea id="<?php echo esc_attr( $this->get_field_id( 'logged_in_message' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'logged_in_message' ) ); ?>" class="widefat" rows="6"><?php echo esc_attr( $instance['logged_in_message'] ); ?></textarea>
				</p>
				<label for=""><?php esc_html_e( 'Links', 'gravityformsuserregistration' ); ?>:</label><br />
				<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'logged_in_links' ) ); ?>" class="gf_login_widget_links_json" value='<?php echo json_encode( $instance['logged_in_links'] ); ?>' />
				<table class="gf_login_widget_links">
					<?php
						if ( ! empty( $instance['logged_in_links'] ) ) {
							foreach ( $instance['logged_in_links'] as $i => $link ) {
					?>
					<tr>
						<td width="47.5%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_in_links' ) ); ?>[<?php echo $i; ?>][text]" class="widefat" placeholder="<?php esc_attr_e( 'Link Text', 'gravityformsuserregistration' ); ?>" value="<?php echo esc_attr( $link['text'] ); ?>" /></td>
						<td width="47.5%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_in_links' ) ); ?>[<?php echo $i; ?>][url]" class="widefat" placeholder="<?php esc_attr_e( 'Link URL', 'gravityformsuserregistration' ); ?>" value="<?php echo esc_attr( $link['url'] ); ?>" /></td>
						<td width="5%"><a href="#" data-action="delete-link"><img src="<?php echo GFCommon::get_base_url(); ?>/images/remove.png" alt="Delete Link" /></a></td>
					</tr>
					<?php } } ?>
					<tr data-repeater style="display:none;">
						<td width="47.5%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_in_links' ) ); ?>" class="widefat link-text" placeholder="<?php esc_attr_e( 'Link Text', 'gravityformsuserregistration' ); ?>" /></td>
						<td width="47.5%"><input type="text" data-name="<?php echo esc_attr( $this->get_field_name( 'logged_in_links' ) ); ?>" class="widefat link-url" placeholder="<?php esc_attr_e( 'Link URL', 'gravityformsuserregistration' ); ?>" /></td>
						<td width="5%"><a href="#" data-action="delete-link"><img src="<?php echo GFCommon::get_base_url(); ?>/images/remove.png" alt="Delete Link" /></a></td>
					</tr>
				</table>
				<a href="#" class="gf_login_widget_add_link">Add Link</a>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'logout_redirect_url' ) ); ?>"><?php esc_html_e( 'Logout Redirect URL', 'gravityformsuserregistration' ); ?>:</label><br />
					<input id="<?php echo esc_attr( $this->get_field_id( 'logout_redirect_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'logout_redirect_url' ) ); ?>" value="<?php echo esc_attr( $instance['logout_redirect_url'] ); ?>" class="widefat" />
				</p>
				<p>
					<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'logged_in_avatar' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'logged_in_avatar' ) ); ?>" <?php checked( rgar( $instance, 'logged_in_avatar' ) ); ?> value="1" />
					<label for="<?php echo esc_attr( $this->get_field_id( 'logged_in_avatar' ) ); ?>"><?php esc_html_e( 'Show user avatar', 'gravityforms' ); ?></label><br />
				</p>
			</div>
			
		<?php
		}
	}
}
