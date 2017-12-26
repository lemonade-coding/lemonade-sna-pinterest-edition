<?php
/**
 * Pinterest extention 
 *
 * Requires all files for Pinterest part of the plugin.
 *
 * @package     LemonadeSNAPinterest
 * @since       1.0.0
 */
 
/**
 * @since 2.0.0
 */ 	
if( !defined( 'LEMONADE_SNA_PINTEREST_EXT_PATH' ) ) {
	define( 'LEMONADE_SNA_PINTEREST_EXT_PATH', LEMONADE_SNA_PINTEREST_PLUGIN_PATH . '/pinterest' ); //Define path constant
}

/**
 * @since 2.0.0
 */ 
if( !defined( 'LEMONADE_SNA_PINTEREST_EXT_URL' ) ) {
	define( 'LEMONADE_SNA_PINTEREST_EXT_URL', LEMONADE_SNA_PINTEREST_PLUGIN_URL . '/pinterest' ); //Define url constant
}		

//Include plugin's files

require_once( 'lemonade_sna_pinterest_functions.php' );
require_once( 'lemonade_sna_pinterest_actions.php' );
require_once( 'lemonade_sna_pinterest_dashboard.php' );

?>