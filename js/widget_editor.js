( function( $ ) {
	
	$( document ).ready( function() {

		// Toggle editor tabs.
		$( document ).on( 'click', '.gf_login_widget_editor .tabs a', function( e ) {
		
			e.preventDefault();
			
			// Get target tab.
			var widgetForm = $( this ).parent().parent(),
				tabsNav = $( this ).parent(),
				targetTab = $( this ).attr( 'data-tab' );
				
			// Switch active tab class.
			tabsNav.find( 'a' ).removeClass( 'active' );
			$( this ).addClass( 'active' );
			
			// Switch active tab.
			widgetForm.find( '.tab-content:not( [data-tab="' + targetTab + '"] )' ).hide();
			widgetForm.find( '.tab-content[data-tab="' + targetTab + '"]' ).show();
			
			// Update active view setting.
			widgetForm.find( 'input[data-type="active_view"]' ).val( targetTab );
		
		} );

		// Add logged in link
		$( document ).on( 'click', '.gf_login_widget_add_link', function( e ) {
			
			e.preventDefault();
			
			var tableElement = $( this ).prev(),
				repeatTemplate = $( 'tr[data-repeater]', tableElement );
			
			/* Remove the new class from the current new row. */
			$( 'tr.new', tableElement ).removeClass( 'new' );
			
			/* Get the index for the new item. */
			var index = 0;
			$( 'tr:not([data-repeater])', tableElement ).each( function() {
				
				var inputName = $( 'td:first-child input', this ).attr( 'data-name' ),
					regex = /\[(\d*)\]/gmi,
					inputIndex = inputName.match( regex )[1];
				
				inputIndex = parseInt( inputIndex.replace( '[', '' ).replace( ']', '' ) );
				
				if ( inputIndex > index ) {
					index = inputIndex;
				}
				
			} );
			index++;
			
			/* Get the HTML for the new row. */
			var newRow = $( '<tr class="new">' + repeatTemplate.html() + '</tr>' );
			
			/* Add the index to each input field. */
			var linkText = $( 'input.link-text', newRow ),
				linkURL = $( 'input.link-url', newRow );
				
			linkText.attr( 'data-name', linkText.attr( 'data-name' ) + '[' + index + '][text]' );
			linkURL.attr( 'data-name', linkURL.attr( 'data-name' ) + '[' + index + '][url]' );
			
			/* Insert the new row before the repeater row. */
			newRow.insertBefore( repeatTemplate );
			
			update_widget_links( tableElement );
			
		} );

		// Delete logged in link
		$( document ).on( 'click', '.gf_login_widget_links a[data-action="delete-link"]', function( e ) {
			
			e.preventDefault();
			
			var linkRow = $( this ).closest( 'tr' ),
				tableElement = $( this ).closest( 'table' );
						
			linkRow.remove();
			
			if ( $( 'tr:not([data-repeater])', tableElement ).length === 0 ) {
				tableElement.next().click();
			}
			
			update_widget_links( tableElement );
			
		} );

		// Update logged in links when input changes
		$( document ).on( 'change', '.gf_login_widget_links input', function() { 
			
			update_widget_links( $( this ).closest( 'table' ) );
			
		} );

		// Toggle custom Registration links inputs
		$( document ).on( 'change', '.gf_login_widget_registrationpage', function() { 
						
			if ( $( this ).val() === 'custom' ) {
				$( this ).next( 'table' ).show();
			} else {
				$( this ).next( 'table' ).hide();
			}
			
		} );

		// Push logged in links to hidden input element
		function update_widget_links( table ) {
			
			var links = [],
				jsonContainer = table.prev();
			
			$( 'tr:not([data-repeater])', table ).each( function() {
				
				$( 'input', this ).each( function() {
				
					var name = $( this ).attr( 'data-name' ),
						explodedName = name.split( /[[\]]{1,2}/ );
					
					if ( links[ explodedName[3] ] === undefined ) {
						links[ explodedName[3] ] = {};
					}
					
					links[ explodedName[3] ][ explodedName[4] ] = $( this ).val();
				
				} );
				
			} );
			
			jsonContainer.val( JSON.stringify( links ) ).trigger( 'change' );
			
		}
		
	} );
	
} )( jQuery );