
jQuery( document ).ready( function( $ ) {
	
	$( '.lsna-collapsible' ).panel(); //Make object collapsible
	
	$( '.input-control' ).change( function() {
		$( this ).removeClass( 'lsna-error' ); //Remove error message every time when change input field
	} );
	
} );

//Clears field with dates
function lemonade_sna_pinterest_clear_dates( i ) {
	var el = jQuery( i );
	el.parent( '.input-control' ).prev( '.input-control' ).children( 'input' ).val( '' );
}

//Activates template with Ajax
function lemonade_sna_pinterest_activate_template( i ) {

	var el = jQuery( i ),
	checked = el.prop( 'checked' ),
	id = el.data( 'templateId' );
	
	if( true === checked ) {
		checked = 1;
	} else {
		checked = 0;
	}
	
	if( el.hasClass( 'activated' ) ) {
		if( 1 === checked ) {
			el.prop( 'checked', 0 );
		} else {
			el.prop( 'checked', 1 );
		}
		return; //Stop, if object already has class 'activated'
	}
	
	el.addClass( 'activated' );	
	el.parents( '.lsna-template-card-inside' ).find( '.lsna-activate-spinner' ).removeClass( 'lsna-invisible' ); //Make spinner visible
	
	jQuery.post(
		ajaxurl,
		{
			checked: checked,
			action: 'lemonade_sna_pinterest_activate_template',
			id: id,
			nonce: lemonade_sna.nonce	
		},
		function( r ) {
			if( false === r ) {
				metroDialog.open( '#lsna-dialog-error' ); //Show an error message in the Metro UI Dialog
			} else if( '1' === r ) {
				metroDialog.open('#lsna-dialog-activated');	//Show a success message about activation		
			} else if( '0' === r ) {
				metroDialog.open('#lsna-dialog-deactivated'); //Show a success message about deactivation
			}
		}
	).always( function() {
		el.removeClass( 'activated' );
		el.parents( '.lsna-template-card-inside' ).find( '.lsna-activate-spinner' ).addClass( 'lsna-invisible' ); //Make the spinner invisible
	} );
	
}

//Validates string with numbers only
function lemonade_sna_pinterest_validate_number( n ) {
	return /^\d+$/.test( n );
}

//Validates string with numbers and letters only
function lemonade_sna_pinterest_validate_numbers_symbols( s ) {
	return /^[a-z0-9]+$/i.test( s );
}

//Checks that amount of days|hours|minutes or limit value are valid.
function lemonade_sna_pinterest_validate_integer_range( type, val ) {

	var valid = true;
	
	switch( type ) {
		case 'limit' :
		case 'days' :
			if( val < 0 ) {
				valid = false;
			}
			break;
		case 'hours' :
			if( val < 0 || val > 24 ) {
				valid = false;
			}
			break;
		case 'minutes' :
			if( val < 0 || val > 60 ) {
				valid = false;
			}
			break;
	}
	
	return valid;

}

//Gets dates from created with Metro UI API calendar and copies it into a special field
function lemonade_sna_pinterest_get_dates( s, f ) {
	var dates =  jQuery( '#special-calendar' ).calendar( 'getDates' );
	jQuery( '#special-dates' ).val( dates );
}


//Opens Delete Template Dialog
function lemonade_sna_pinterest_delete_template_dialog( event, element ) {
	
	var id = jQuery( element ).data( 'id' ),
	title = jQuery( element ).data( 'title' );
	
	event.preventDefault();
	
	jQuery( '#lsna-dialog-delete-template' ).find( '.lsna-dialog-delete-template-title' ).html( "<b>" + title + "</b>" );
	jQuery( '#lsna-dialog-delete-template' ).find( '.lsna-delete-template-button' ).data( 'templateId', id );
	metroDialog.open( '#lsna-dialog-delete-template' ); //Open the Delete Template Dialog 

}

//Deletes a template using Ajax
function lemonade_sna_pinterest_delete_template( element ) {
	
	var id = jQuery( element ).parents( '#lsna-dialog-delete-template' ).find( '.lsna-delete-template-button' ).data( 'templateId' );
	
	jQuery( element ).find( '.lsna-delete-spinner' ).removeClass( 'lsna-invisible' );
	
	jQuery.post(
		ajaxurl,
		{
			id: id,
			action: 'lemonade_sna_pinterest_delete_template',
			nonce: lemonade_sna.nonce
		},
		function( responce ) {
			if( responce ) {
				if( responce == 0 ) {
					metroDialog.open( '#lsna-dialog-delete-template-error' );
					return;
				}
				window.location.reload( true );
			}
		}
	).always( function() {
		jQuery( element ).find( '.lsna-delete-spinner' ).addClass( 'lsna-invisible' );
		metroDialog.close( '#lsna-dialog-delete-template' );
	} );
	
}
