<?php

/**
 * Special part of programm which makes postings on Pinterest
 *
 * @package LemonadeSNAPinterest
 * @since 1.0.0
 *
 * @param string $note Pin description.
 * @param string $image_url Link to image for the pin.
 * @param string $board_id Pinterest board ID.
 * @param string $link Link to the post.
 */

/**
 * Requires DirkGroenen Pinterest API
 */ 
require_once( LEMONADE_SNA_PINTEREST_EXT_PATH . '/api/Pinterest-API-PHP-master/autoload.php' );

use DirkGroenen\Pinterest\Pinterest;

$pinterest_poster = new Pinterest( '', '' );

$pinterest_token = get_option( 'lemonade_sna_access_token' );

$pin_posted = '';

if( !empty( $pinterest_token ) ) {
	$pinterest_poster->auth->setOAuthToken( $pinterest_token );
	$pin_posted = $pinterest_poster->pins->create( array(
		"note"          => $note,
		"image_url"     => $image_url,
		"board"         => $board_id,
		"link" => $link
	) );
}

?>