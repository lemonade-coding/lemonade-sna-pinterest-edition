<?php

/**
 * Part of programm which tests Pinterest API configuration
 *
 * @package LemonadeSNAPinterest
 * @since 1.0.0
 * @since 2.0.0 Pinterest App ID and App secret are used for authorization. Some data sanitizaton added.
 */

/**
 * Requires DirkGroenen Pinterest API wrapper
 */ 
require( LEMONADE_SNA_PINTEREST_EXT_PATH . '/api/Pinterest-API-PHP-master/autoload.php' );

use DirkGroenen\Pinterest\Pinterest;

$pinterest = false;
$setted = false;
$connected = lemonade_sna_pinterest_check_internet_connection( 'www.pinterest.com' );	//Check the Internet connection

if( get_option( 'lemonade_sna_access_token' ) != '' && get_option( 'lemonade_sna_pinterest_app_secret' ) != '' && get_option( 'lemonade_sna_pinterest_app_id' ) != '' ) {
	$pinterest = new Pinterest( get_option( 'lemonade_sna_pinterest_app_id' ), get_option( 'lemonade_sna_pinterest_app_secret' ) );
	if( $pinterest ) {				
		$pinterest->auth->setOAuthToken( get_option( 'lemonade_sna_access_token' ) );
		if( $connected ) {
			try {
				if(
					$me = $pinterest->users->me( array( //Get information about user
						'fields' => 'username,first_name,last_name,image[large],url,bio,account_type,counts'
					) )
				){
					$setted = true;
				} else {
					throw new Exception( $e );
				}				
			} catch ( Exception $e ) {		
				echo '<p class="lsna-error-message fg-red"><span class="mif-warning"></span>' . esc_attr( $e->getMessage() ) . '</p>';
			}
		} else {
			echo '<p class="lsna-error-message fg-red"><span class="mif-warning"></span>' . __( 'Please, check your Internet connection.', 'lemonade_sna' ) . '</p>';
		}
	}
}

if( $setted ) : //Output user information ?>
	<p><?php echo __( 'Congratulations! Your Pinterest API is setted up.', 'lemonade_sna' ); ?></p>	
	<figure class="lsna-pinterest-profile">
		<div class="lsna-profile-image">
			<?php if( !empty( $me->image['large']['url'] ) ) : ?>
			<img src="<?php echo esc_url( $me->image['large']['url'] ); ?>">
			<?php endif; ?>
		</div>
		<figcaption>
			<div class="lsna-profile-content">
				<h4>
				<?php 
				if( !empty( $me->first_name ) ) : 
					echo esc_attr( $me->first_name );
				endif;  
				if( !empty( $me->last_name ) ) : 
					echo ' ' . esc_attr( $me->last_name );
				endif; 
				?>			
				</h4>
				<h5>
				<?php 
				if( !empty( $me->username ) ) {
					echo esc_attr( $me->username ); 
				}
				?>
				</h5>
				<?php if( !empty( $me->bio ) ) : ?>
					<p><?php echo esc_attr( $me->bio ); ?></p>
				<?php endif; ?>
			</div>
			<div class="lsna-pinterest-profile-footer">
				<?php $counts = $me->counts; ?>
				<div>
					<small><?php echo __( 'Boards', 'lemonade_sna' ) . ':'; ?></small><br>
					<span><?php echo (int)$counts['boards']; ?></span>
				</div>
				<div>
					<small><?php echo __( 'Following', 'lemonade_sna' ) . ':'; ?></small><br>
					<span><?php echo (int)$counts['following']; ?></span>
				</div>
				<div>
					<small><?php echo __( 'Followers', 'lemonade_sna' ) . ':'; ?></small><br>
					<span><?php echo (int)$counts['followers']; ?></span>
				</div>				
			</div>	
		</figcaption>
	</figure>
<?php
elseif( !$setted && $connected ) :
?>	
<p>
	<span class="mif-not"></span><?php echo __( 'Your Pinterest API is not setted up.', 'lemonade_sna' ); //In case that can not get user information?>
</p>
<?php
endif;
?>