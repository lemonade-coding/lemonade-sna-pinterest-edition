<?php
/**
 * This is the main Lemonade SNA: Pinterest plugin file, in which there are declared main files and all the base stuff
 *
 * @package     LemonadeSNAPinterest
 * @copyright   2017 Lemonade Coding Studio 
 * @license     GPL3
 * @since       1.0.0
 *
 * Plugin Name: Lemonade SNA Pinterest
 * Plugin URI: www.lemonade-coding.com
 * Description: Lemonade Social Networks Autoposter: Pinterest. It makes auto-posting to Pinterest social network using WP Cron functionality. It allows to create simultaneous streams of re-posting to Pinterest, filter publications which will be included to a stream, create templates for pins and etc.
 * Version: 2.0
 * Author: Lemonade Coding Studio
 * Author URI: www.lemonade-coding.com
 * Text Domain: lemonade_sna
 * Domain Path: /languages 
 * Requires at least: 4.6
 * Tested up to: 4.9
 */
 
if( !defined( 'ABSPATH' ) ) 
	exit; //Exit if accessed directly  
	
/**
 * @since 2.0.0
 */
if( !defined( 'LEMONADE_SNA_PINTEREST_PLUGIN_PATH' ) ) {
	define( 'LEMONADE_SNA_PINTEREST_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) ); //Define path constant
}	

/**
 * @since 2.0.0
 */ 
if( !defined( 'LEMONADE_SNA_PINTEREST_PLUGIN_URL' ) ) {
	define( 'LEMONADE_SNA_PINTEREST_PLUGIN_URL', plugins_url( basename( plugin_dir_path( __FILE__ ) ) ) ); //Define url constant
}	

register_activation_hook( __FILE__, 'lemonade_sna_pinterest_activate_plugin' ); //Fires when the plugin is activated
register_deactivation_hook( __FILE__, 'lemonade_sna_pinterest_deactivate_plugin' ); //Fires when the plugin is deactivated

function lemonade_sna_pinterest_activate_plugin(){
	
	/**
	 * Fires when plugin is activated.
	 *
	 * @since 1.0.0
	 */
	do_action( 'lemonade_sna_pinterest_activate_plugin' );
}

function lemonade_sna_pinterest_deactivate_plugin(){
	
	/**
	 * Fires when plugin is deactivated.
	 *
	 * @since 1.0.0
	 */
	do_action( 'lemonade_sna_pinterest_deactivate_plugin' );
}

//Include plugin files

require_once( 'lemonade_sna_core_pinterest_functions.php' );
require_once( 'lemonade_sna_core_pinterest_actions.php' );

//Include extentions

require_once( 'pinterest/lemonade_sna_pinterest.php' );