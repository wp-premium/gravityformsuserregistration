( function( $ ) {

	var GFURFeedSettings = function() {

		var self = this;

		self.init = function() {

			self.$password  = $( '#password' );
			self.$sendEmail = $( '#sendemailenable' );
			self.$sendEmailLocked = $( '#sendEmailLocked' );

			$( '.gfur-cs-checkbox' ).on( 'change', function() {

				var name    = $( this ).attr( 'name' ),
					$select = $( '#{0}ValueSpan, #{1}ValueSpan'.format( name, name.replace( 'Enable', '' ) ) ),
					$label  = $( 'label[for="{0}"]'.format( $( this ).attr( 'id' ) ) );

				// Set hidden input value for Checkbox.
				$( this ).siblings( 'input[type=hidden]' ).val( $( this ).prop( 'checked' ) ? 1 : 0 );

				if( $( this ).is( ':checked' ) ) {
					$label.find( 'span' ).html( $( this ).attr( 'data-enabledLabel' ) );
					$select.show();
				} else {
					$label.find( 'span' ).html( $( this ).attr( 'data-label' ) );
					$select.hide();
				}

			} );

			self.$password.change( function() {
				self.toggleSendEmail();
			} );

			if( self.$password.val() === 'email_link' || /* deprecated */ self.$password.val() === 'generatepass' ) {
				self.toggleSendEmail();
			}

		};

		self.toggleSendEmail = function() {

			var isEmailLink  = self.$password.val() === 'email_link',
				wasChecked   = self.$sendEmail.is( ':checked' ) && ! self.$sendEmail.is( ':disabled' ),
				isChecked    = isEmailLink;

			self.$sendEmail
				.data( 'was-checked', wasChecked )
				.prop( 'disabled', isEmailLink )
				.prop( 'checked', isChecked )
				.change();

 			if( isEmailLink ) {
				self.$sendEmailLocked.show();
			} else {
				self.$sendEmailLocked.hide();
			}

		};

		this.init();

	};

	$( document ).ready( function() {

		GFURFeedSettings();

	} );

} )( jQuery );