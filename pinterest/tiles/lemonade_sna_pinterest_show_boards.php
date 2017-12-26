<?php

/**
 * Special part of prgramm which gets a list of Pinterest user's boards
 *
 * @package LemonadeSNAPinterest
 * @since 1.0.0
 * @since 2.0.0 App key and App secret are used for authorization. Some data sanitization added.
 */

/**
 * Requires DirkGroenen Pinterest API wrapper
 */ 
require( LEMONADE_SNA_PINTEREST_EXT_PATH . '/api/Pinterest-API-PHP-master/autoload.php' );

use DirkGroenen\Pinterest\Pinterest;

$user_boards = '';

if( get_option( 'lemonade_sna_access_token' ) != '' && get_option( 'lemonade_sna_pinterest_app_id' ) != '' && get_option( 'lemonade_sna_pinterest_app_secret' ) != '' ) {

	$pinterest = new Pinterest( get_option( 'lemonade_sna_pinterest_app_id' ), get_option( 'lemonade_sna_pinterest_app_secret' ) );
	if( $pinterest ) {				
		$pinterest->auth->setOAuthToken( get_option( 'lemonade_sna_access_token' ) );
		$connected = lemonade_sna_pinterest_check_internet_connection( 'www.pinterest.com' );
		if( $connected ) {
			try {
				if( 
					$user_boards = $pinterest->users->getMeBoards( array(
						'fields' => 'id,name,url'
					) )
				) {
					$user_boards = $user_boards->all();
				}
				else {
					throw new Exception( $e );
				}
			} catch ( Exception $e ) {
				echo '<span class="lsna-error-message fg-red"><span class="mif-warning"></span>' . esc_attr( $e->getMessage() ) . '</span>';
			}
		} else {
			echo '<span class="lsna-error-message fg-red"><span class="mif-warning"></span>' . __( 'Please, check Internet connection.', 'lemonade_sna' ) . '</span>';		
		}
	} 
}

?>