<?php

/**
 * Gets a Pinterest access token
 *
 * @package LemonadeSNAPinterest
 * @since 2.0.0
 */
 
/**
 * Requires DirkGroenen Pinterest API wrapper
 */ 
require( LEMONADE_SNA_PINTEREST_EXT_PATH . '/api/Pinterest-API-PHP-master/autoload.php' );

use DirkGroenen\Pinterest\Pinterest;

if( empty( $app_id ) || empty( $app_secret ) ) {
	return;
}

$pinterest = new Pinterest( $app_id, $app_secret );

$loginurl = $pinterest->auth->getLoginUrl( $cur_url, array( 'read_public','write_public','read_relationships','write_relationships' ) );

if( !empty( $loginurl ) ) {
	?>
	<h4>
		<a href="<?php echo esc_url( $loginurl ); ?>"><?php echo __( 'Now you can authorize Pinterest', 'lemonade_sna' ); ?></a>
	</h4>
	<small>
		<?php echo __( 'Click the link above and accept permissions from an application.', 'lemonade_sna' ); ?>
	</small>
	<?php
}

if( isset( $_GET['code'] ) ){
    try {
		if( $token = $pinterest->auth->getOAuthToken( $_GET['code'] ) ) { ?>
			<p>
				<strong><?php echo __( 'Your access token:', 'lemonade_sna' ); ?></strong>
				<?php echo esc_attr( $token->access_token ); ?><br>
				<small>
				<?php echo __( 'Please, copy and paste the access token into the Access Token field in the form above and save settings.', 'lemonade_sna' ); ?>
				</small>				
			</p>
			<?php
		} else {
			throw new Exception( $e );
		}
	} catch ( Exception $e ) {
		return null;
	}	
}

?>