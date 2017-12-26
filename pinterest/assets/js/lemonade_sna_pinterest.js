//Validates form fields on API Settings page
function lemonade_sna_validate_pinterest_api_settings_form() {

	jQuery( '#lsna-pinterest-api-settings-form' ).submit( function( e ) {
		
		var val, message,
		error = 0;
		
		jQuery( 'input' ).each( function() {
			jQuery( this ).parent( '.input-control' ).removeClass( 'lsna-error' );
		} );

		val = jQuery( 'input[name="app_id"]' ).val();
		
		if( false === lemonade_sna_pinterest_validate_number( val ) ) {
			jQuery( 'input[name="app_id"]' ).parent( '.input-control' ).addClass( 'lsna-error' );
			message = jQuery( 'input[name="app_id"]' ).data('validateHint');
			jQuery.Notify( {
				caption: 'Error',
				content: message,
				type: 'alert'
			} );			
			error = 1;		
		}
		
		val = jQuery( 'input[name="app_secret"]' ).val();
		
		if( false === lemonade_sna_pinterest_validate_numbers_symbols( val ) ) {
			jQuery( 'input[name="app_secret"]' ).parent( '.input-control' ).addClass( 'lsna-error' );
			message = jQuery( 'input[name="app_secret"]' ).data('validateHint');
			jQuery.Notify( {
				caption: 'Error',
				content: message,
				type: 'alert'
			} );			
			error = 1;		
		}		
		
		if( 1 === error ) {
			e.preventDefault();
		}	
		
	} );	

}

//Validates form fields on Autoposter Template page
function lemonade_sna_pinterest_validate_autoposter_template_form() {

	jQuery( '#lsna-pinterest-autoposter-template-form' ).submit( function( e ) {
	
		var val, message, type,
		error = 0;	
	
		jQuery( '.lsna-template-name' ).removeClass( 'lsna-error' );
		
		val = jQuery( '.lsna-template-name' ).find( 'input' ).val();
		
		if( val === '' ) { //Template name shouldn't be empty
			jQuery( '.lsna-template-name' ).addClass( 'lsna-error' );
			message = jQuery( '.lsna-template-name' ).find( 'input' ).data('validateHint');
			jQuery.Notify( {
				caption: 'Error',
				content: message,
				type: 'alert'
			} );			
			error = 1;
		}
		
		jQuery( '.input-number' ).each( function() {
			valid = true;
			jQuery( this ).removeClass( 'lsna-error' );
			val = jQuery( this ).find( 'input' ).val();
			
			if( val !== '' ) {
				valid = lemonade_sna_pinterest_validate_number( val ); //Validate a number
				if( false === valid ) {				
				} else {
					
					val = parseInt( val );
					
					if( jQuery( this ).hasClass( 'days' ) ) { 
						type = 'days';
					} else if( jQuery( this ).hasClass( 'hours' ) ) {
						type = 'hours';
					} else if( jQuery( this ).hasClass( 'minutes' ) ) {
						type = 'minutes';
					} else if( jQuery( this ).hasClass( 'limit' ) ) {
						type = 'limit';
					}
					
					if( false === lemonade_sna_pinterest_validate_integer_range( type, val ) ) { //validate a number of days|hours|minutes
						valid = false;
					}
					
				}
				
				if( false === valid ) {
					jQuery( this ).addClass( 'lsna-error' );
					message = jQuery( this ).find( 'input' ).data('validateHint');
					jQuery.Notify( {
						caption: 'Error',
						content: message,
						type: 'alert'
					} );			
					error = 1;				
				}
				
			}
		} );
		
		jQuery( '.lsna-template-boards' ).removeClass( 'lsna-error' );
		val = jQuery( '.lsna-template-boards' ).find( 'select' ).val();

		if( val == [] || val == null ) { //Pinterest boards field shouldn't be empty
			jQuery( '.lsna-template-boards' ).addClass( 'lsna-error' );
			message = jQuery( '.lsna-template-boards' ).find( 'select' ).data('validateHint');
			jQuery.Notify( {
				caption: 'Error',
				content: message,
				type: 'alert'
			} );			
			error = 1;			
		}
		
		if( error == 1 ) {
			e.preventDefault();
		}
	
	} );	

}