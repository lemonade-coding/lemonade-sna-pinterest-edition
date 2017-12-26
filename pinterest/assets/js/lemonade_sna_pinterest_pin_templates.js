//Opens Delete Pin Template Dialog
function lemonade_sna_delete_pin_template_dialog( event, el, id ) {
	
	var title = jQuery( el ).data( 'title' );
	
	event.preventDefault();
	
	jQuery( '#lsna-dialog-delete-pin-template' ).find( '.lsna-dialog-delete-template-title' ).html( "<b>" + title + "</b>" );
	jQuery( '#lsna-dialog-delete-pin-template' ).find( '.lsna-delete-template-button' ).data( 'templateId', id );
	metroDialog.open( '#lsna-dialog-delete-pin-template' );
	
}

//Delete Pin template using Ajax
function lemonade_sna_delete_pin_template( el ) {
	
	var id = jQuery( el ).parents( '#lsna-dialog-delete-pin-template' ).find( '.lsna-delete-template-button' ).data( 'templateId' );
	var backurl = jQuery( el ).parents( '#lsna-dialog-delete-pin-template' ).find( '.lsna-delete-template-button' ).data( 'backurl' );
	
	jQuery( el ).find( '.lsna-delete-spinner' ).removeClass( 'lsna-invisible' );
	jQuery.post(
		ajaxurl,
		{
			id: id,
			action: 'lemonade_sna_delete_pin_template',
			nonce: lemonade_sna_test.nonce
		},
		function( responce ) {
			if( responce ) {
				if( responce == 0 ) {
					metroDialog.open( '#lsna-dialog-delete-pin-template-error' ); //Open Dialog with an error message
					return;
				}				
			}
			if( backurl && backurl.length > 0 ) {
				window.location.href = "" + backurl;
			} else {
				window.location.reload( true );
			}
		}
	).always( function() {
		jQuery( el ).find( '.lsna-delete-spinner' ).addClass( 'lsna-invisible' );
		metroDialog.close( '#lsna-dialog-delete-pin-template' );
	} );		
}

//Tests a Pin template
function lemonade_sna_test_pin_tmpl_permalink( input ) {

	var rules = jQuery( '#test_pin_template_rules' ).val(),
	pid = jQuery( input ).val();
	
	jQuery( '#lsna-pin-template-test-result' ).addClass( 'lsna-invisible' );

 	jQuery.post(
		ajaxurl,
		{
			action: 'lemonade_sna_pin_template_test',
			pid: pid,
			rules: rules
		},
		function( responce ) {

			if( responce && 0 !== responce ) {
			
				responce = JSON.parse( responce );
				
				//Add html to the blocks on the page
				if( responce.pin_image ) {
					jQuery( '#lsna-pin-template-test-image-link' ).html( responce.pin_image );
				}
				if( responce.pin_link ) {
					jQuery( '#lsna-pin-template-test-link' ).html( responce.pin_link );
				}
				if( responce.pin_note ) {
					jQuery( '#lsna-pin-template-test-note' ).html( responce.pin_note );
				}
				jQuery( '#lsna-pin-template-test-result' ).removeClass( 'lsna-invisible' );
				
			}
		}
	);
}
