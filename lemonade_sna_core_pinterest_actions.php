<?php

/**
 * Hooks, filters and related on functions used by plugin
 *
 * @package LemonadeSNAPinterest
 * @since 1.0.0
 */
 
/////////////////
//PLUGIN LOADED//
///////////////// 
 
add_action( 'plugins_loaded', 'lemonade_sna_pinterest_language_domain' ); 

///////////////////// 
//PLUGIN ACTIVATION//
/////////////////////

add_action( 'lemonade_sna_pinterest_activate_plugin', 'lemonade_sna_pinterest_create_db_tables', 10 ); //Create DB tables

////////
//INIT//
////////

add_action( 'init', 'lemonade_sna_pinterest_schedules' ); //Activate/deactivate Cron Schedules

add_action( 'init', 'lemonade_sna_pinterest_edit_mode' ); //Deactivate template if it is currently editting

//////////////
//ADMIN INIT//
//////////////

add_action( 'admin_init', 'lemonade_sna_pinterest_start_session' ); //Start session

//////////////////////
//SCRIPTS AND STYLES//
//////////////////////

add_action( 'admin_enqueue_scripts', 'lemonade_sna_pinterest_core_enqueue_scripts' );

add_action( 'admin_enqueue_scripts', 'lemonade_sna_pinterest_core_enqueue_styles' );

////////
//AJAX//
////////

if( defined( 'DOING_AJAX' ) && DOING_AJAX ){

	add_action( 'wp_ajax_lemonade_sna_pinterest_activate_template', 'lemonade_sna_pinterest_activate_template' ); //Activate template
	
	add_action( 'wp_ajax_lemonade_sna_pinterest_delete_template', 'lemonade_sna_pinterest_delete_template_action' ); //Delete template
	
}

/////////
//OTHER//
/////////

add_action( 'wp_logout', 'lemonade_sna_pinterest_end_session' ); //End session

add_action( 'wp_login', 'lemonade_sna_pinterest_end_session' ); //End session

/////////////
//FUNCTIONS//
/////////////

/**
 * Loads translated strings for the plugin.
 *
 * Looks for files like .mo and loads translated strings.
 *
 * @since 2.0.0
 */
function lemonade_sna_pinterest_language_domain() {

	/**
	 * Filters the plugin locale.
	 *
	 * @since 2.0.0
	 */
	$locale = apply_filters( 'lemonade_sna_pinterest_plugin_locale', get_locale(), 'lemonade_sna' );

	load_textdomain( 'lemonade_sna', LEMONADE_SNA_PINTEREST_PLUGIN_PATH . '/' . 'languages' . '/' . 'lemonade_sna_pinterest' . '-' . $locale . '.mo' );
	load_plugin_textdomain( 'lemonade_sna', false, LEMONADE_SNA_PINTEREST_PLUGIN_PATH . '/languages' );	

}

/**
 * Creates MySql Data Tables.
 *
 * Creates '{pref}_lemonade_autoposter_templates'  
 * and '{pref}_lemonade_autoposter_posts_published' tables in the DB. 
 * This function fires after the plugin is activated.
 *
 * @since 2.0.0
 * @global object $wpdb The WPDB object.
 */
function lemonade_sna_pinterest_create_db_tables() {

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	
	$table1 = $wpdb->prefix . 'lemonade_autoposter_templates';
	
	$sql1 = "CREATE TABLE IF NOT EXISTS " . $table1 . " (
		id int(11) NOT NULL AUTO_INCREMENT,
		title varchar(254) NOT NULL,
		network varchar(254) NOT NULL,
		rules longtext NOT NULL,
		is_active enum( '1', '0' ) DEFAULT '0' NULL,
		date_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		next_update datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,		
		PRIMARY KEY  (id)) " . $charset_collate . "; ";	

	$table2 = $wpdb->prefix . 'lemonade_autoposter_posts_published';

	$sql2 = "CREATE TABLE IF NOT EXISTS " . $table2 . " (
		id int(11) NOT NULL AUTO_INCREMENT,
		template_id int(11) NOT NULL,
		post_id int(11) NOT NULL,
		cycle_count int(11) NULL DEFAULT NULL,
		when_done datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,	
		PRIMARY KEY  (id)) " . $charset_collate . "; ";	

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );	
	
	dbDelta( $sql1 );
	dbDelta( $sql2 );	

}

/**
 * Enqueues scripts.
 *
 * Enqueus scripts to special pages.
 *
 * @since 2.0.0
 *
 * @param string $hook Admin page slug.
 */
function lemonade_sna_pinterest_core_enqueue_scripts( $hook ) {

	wp_register_script( 'lemonade_sna_pinterest_script', LEMONADE_SNA_PINTEREST_PLUGIN_URL . '/assets/js/lemonade_sna_pinterest_core_script.js', array() );
	
	//Metro UI scripts
	wp_register_script( 'lemonade_sna_metro_ui', LEMONADE_SNA_PINTEREST_PLUGIN_URL . '/assets/metro-ui/js/metro.min.js', array() );
	wp_register_script( 'lemonade_sna_metro_ui_select2', LEMONADE_SNA_PINTEREST_PLUGIN_URL . '/assets/metro-ui/js/select2.full.min.js', array() );
	
	$nonce = wp_create_nonce( 'lemonade-sna-ajax' );
	$lemonade_sna = array(
		'nonce' => $nonce
	);
	
	if( strpos( $hook, 'lemonade_sna' ) !== false ) { //Enqueue only to plugin pages
	
		wp_enqueue_script( 'lemonade_sna_metro_ui' );
		wp_enqueue_script( 'lemonade_sna_metro_ui_select2' );	
		wp_enqueue_script( 'lemonade_sna_pinterest_script' );
		
		wp_localize_script( 'lemonade_sna_pinterest_script', 'lemonade_sna', $lemonade_sna );
	
	}

}

/**
 * Enqueues styles.
 *
 * Enqueues styles to special pages in the admin area.
 *
 * @since 2.0.0
 *
 * @param string $hook Admin page slug 
 */
function lemonade_sna_pinterest_core_enqueue_styles( $hook ) {

	//Plugin styles
	wp_register_style( 'lemonade_sna_pinterest_core', LEMONADE_SNA_PINTEREST_PLUGIN_URL . '/assets/css/lemonade-sna-pinterest-core.css', array() );	
	wp_register_style( 'lemonade_sna_pinterest_dashboard', LEMONADE_SNA_PINTEREST_PLUGIN_URL . '/assets/css/lemonade-sna-pinterest-dashboard.css', array() );
	
	//Metro UI css
	wp_register_style( 'lemonade_sna_pinterest_metro_ui_main', LEMONADE_SNA_PINTEREST_PLUGIN_URL . '/assets/metro-ui/css/metro.css', array() );
	wp_register_style( 'lemonade_sna_pinterest_metro_ui_colors', LEMONADE_SNA_PINTEREST_PLUGIN_URL . '/assets/metro-ui/css/metro-colors.min.css', array() );
	wp_register_style( 'lemonade_sna_pinterest_metro_ui_icons', LEMONADE_SNA_PINTEREST_PLUGIN_URL . '/assets/metro-ui/css/metro-icons.min.css', array() );
	wp_register_style( 'lemonade_sna_pinterest_metro_ui_schemes', LEMONADE_SNA_PINTEREST_PLUGIN_URL . '/assets/metro-ui/css/metro-schemes.min.css', array() );
	wp_register_style( 'lemonade_sna_pinterest_metro_ui_rtl', LEMONADE_SNA_PINTEREST_PLUGIN_URL . '/assets/metro-ui/css/metro-rtl.min.css', array() );
	wp_register_style( 'lemonade_sna_pinterest_metro_ui_responsive', LEMONADE_SNA_PINTEREST_PLUGIN_URL . '/assets/metro-ui/css/metro-responsive.min.css', array() );

	wp_enqueue_style( 'lemonade_sna_pinterest_core' );
	
	if( strpos( $hook, 'lemonade_sna' ) !== false ) { // Add styles only to plugin admin pages
	
		wp_enqueue_style( 'lemonade_sna_pinterest_metro_ui_main' );
		wp_enqueue_style( 'lemonade_sna_pinterest_metro_ui_colors' );
		wp_enqueue_style( 'lemonade_sna_pinterest_metro_ui_icons' );
		wp_enqueue_style( 'lemonade_sna_pinterest_metro_ui_schemes' );
		wp_enqueue_style( 'lemonade_sna_pinterest_metro_ui_rtl' );
		wp_enqueue_style( 'lemonade_sna_pinterest_metro_ui_responsive' );
		wp_enqueue_style( 'lemonade_sna_pinterest_dashboard' );
		
	}
	
}

/** 
 * Activates template.
 *
 * Sets a template as active in the DB. Ajax action.
 *
 * @since 2.0.0
 *
 * @see 'wp_ajax_lemonade_sna_pinterest_activate_template'
 * @global object $wpdb WPDB object
 */
function lemonade_sna_pinterest_activate_template() {

	global $wpdb;
	
	$nonce = $_POST['nonce'];
	
	if( !wp_verify_nonce( $nonce, 'lemonade-sna-ajax' ) ) {
		echo false;
		wp_die();	
	}
	
	$table = $wpdb->prefix . 'lemonade_autoposter_templates';
	
	if( isset( $_POST['checked'] ) && $_POST['checked'] == 1 ) {
		$is_active = 1;
	} else {
		$is_active = 0;
	}
	
	if( empty( $_POST['id'] ) ) {
		echo false;
		wp_die();
	}
	
	$templ_id = (int)$_POST['id'];	
	
	if( isset( $_REQUEST['edit_template'] ) && $_REQUEST['edit_template'] == 1 && !empty( $_REQUEST['template_id'] ) && $_REQUEST['template_id'] == $templ_id )	{
		echo false;
		wp_die();
	}
	
	$activate = $wpdb->update(
		$table,
		array(
			'is_active' => $is_active
		),
		array(
			'id' => $templ_id
		),
		array( 
			'%d'
		),
		array( 
			'%d'
		)
	);
	
	if( $activate ) {
		echo $is_active;
	} else {
		echo false;
	}	

	wp_die();

}

/**
 * Deletes a template.
 *
 * Deletes a template from the DB. Ajax action.
 * 
 * @since 2.0.0
 *
 * @see lemonade_sna_pinterest_delete_template()
 * @see 'wp_ajax_lemonade_sna_pinterest_delete_template'
 */
function lemonade_sna_pinterest_delete_template_action() {
	
	$nonce = $_POST['nonce'];
	
	$settings = get_option( 'lemonade_sna_pinterest_settings' );
	if( !empty( $settings ) ) {
		$cap_read = !empty( $settings['roles']['autoposter'] ) ? sanitize_text_field( $settings['roles']['autoposter'] ) : 'administrator';
		$cap_read = array_keys( get_role( $cap_read )->capabilities );
		$cap_read = $cap_read[0];
	} else {
		$cap_read = 'manage_options';
	}	
	
	if( !wp_verify_nonce( $nonce, 'lemonade-sna-ajax' ) ) {
		wp_die();	
	}	
	
	if ( !current_user_can( $cap_read ) ) {
		$_SESSION['lsna_error'][] = __( 'You have not capability to manage these settings.', 'lemonade_sna' );
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();
	}	
	
	$id = (int)$_POST['id'];
	if( empty( $id ) ) {
		echo 0;
		wp_die();
	}
	$delete = lemonade_sna_pinterest_delete_template( $id );
	if( $delete ) {
		echo 1;
		$_SESSION['lsna_success'] = __( 'Template was deteled.', 'lemonade_sna' );
	} else {
		echo 0;
	}
	wp_die();
	
}

/**
 * Deactivates a template.
 *
 * Sets given template as inactive in the DB.
 *
 * @since 2.0.0
 *
 * @global object $wpdb WPDB object.
 *
 * @param int $templ_id Template ID
 * @return bool|int False if unsuccess or a number of updated strings.
 */
function lemonade_sna_pinterest_deactivate_template( $templ_id ) {

	global $wpdb;
	
	$table = $wpdb->prefix . 'lemonade_autoposter_templates';
	
	$update = false;
	
	if( $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id=%d", $templ_id ) ) ) {
		$update = $wpdb->update( 
			$table,
			array(
				'is_active' => 0
			),
			array(
				'id' => $templ_id
			),
			array(
			),
			array(
				'%d'
			)
		);
	}
	
	return $update;

}

/**
 * Controls active and inactive templates.
 * 
 * Adds template to WP Cron schedule if it is active in the DB, removes from 
 * schedule if opposite.
 *
 * @since 2.0.0
 *
 * @see lemonade_sna_pinterest_get_list_of_active_templates()
 * @see lemonade_sna_pinterest_get_list_of_unactive_templates()
 * @see lemonade_sna_pinterest_deactivate_template()
 */
function lemonade_sna_pinterest_schedules() {

	$active = lemonade_sna_pinterest_get_list_of_active_templates();
	
	if( !empty( $active ) && is_array( $active ) ) {
		foreach( $active as $a ) {
			$aid = (int)$a->id;
			if( function_exists( 'wp_next_scheduled' ) && function_exists( 'wp_schedule_event' ) ) {
				if( !wp_next_scheduled( 'lemonade_sna_cron', array( $aid ) ) ) {					
					$scheduled = wp_schedule_event( time(), 'lemonade_sna_' . $aid, 'lemonade_sna_cron', array( $aid ) );
					if( $scheduled === false ) {
						lemonade_sna_pinterest_deactivate_template( $aid );
					}
				}			
			}
		}
	}

	$unactive = lemonade_sna_pinterest_get_list_of_unactive_templates();
	
	if( !empty( $unactive ) && is_array( $unactive ) ) {
		foreach( $unactive as $u ) {
			$uid = (int)$u->id;
			if( function_exists( 'wp_next_scheduled' ) && function_exists( 'wp_schedule_event' ) ) {
				if( wp_next_scheduled( 'lemonade_sna_cron', array( $uid ) ) ) {
					if( function_exists( 'wp_clear_scheduled_hook' ) ) {
						wp_clear_scheduled_hook( 'lemonade_sna_cron', array( $uid ) );
					}
				}
			}
		}
	}

}

/**
 * Deactivates template which is under edition.
 *
 * Deactivates a template when it is currently editioned.
 *
 * @since 2.0.0
 *
 * @see lemonade_sna_pinterest_deactivate_template()
 */
function lemonade_sna_pinterest_edit_mode() {

	if( isset( $_REQUEST['edit_template'] ) && $_REQUEST['edit_template'] == 1 && !empty( $_REQUEST['template_id'] ) ) {
		lemonade_sna_pinterest_deactivate_template( (int)$_REQUEST['template_id'] );
	}

}

/**
 * Starts the session.
 *
 * @since 2.0.0
 */
function lemonade_sna_pinterest_start_session() {
    if( !session_id() ) {
        session_start();
    }
}

/**
 * Finishes the session.
 *
 * @since 2.0.0
 */
function lemonade_sna_pinterest_end_session() {
	if( session_id() ) {
		session_destroy();
	}
}

///////////
//FILTERS//
///////////

add_filter( 'cron_schedules', 'lemonade_sna_pinterest_cron_schedules' );

/**
 * Set up WP Cron custom intervals.
 * 
 * Adds custom intervals to WP Cron schedules array.
 *
 * @since 2.0.0
 *
 * @param array $schedules Array of built-in intervals.
 * @return array Array of intervals.
 */
function lemonade_sna_pinterest_cron_schedules( $schedules ) {

	$templates = lemonade_sna_pinterest_get_list_of_active_templates();
	
	if( !empty( $templates ) && is_array( $templates ) ) {
		foreach( $templates as $t ) {
			$rules = json_decode( $t->rules );
			$schedule = !empty( $rules->freq ) ? (array)$rules->freq : array( 'days' => 0, 'hours' => 1, 'minutes' => 0 );
			$days = !empty( $schedule['days'] ) ? (int)$schedule['days'] : 0;
			$hours = !empty( $schedule['hours'] ) ? (int)$schedule['hours'] : 0;
			$minutes = !empty( $schedule['minutes'] ) ? (int)$schedule['minutes'] : 0;
			$interval = $days * 24 * 60 * 60 + $hours * 60 * 60 + $minutes * 60;
			$schedule_name = 'lemonade_sna_' . (int)$t->id;
			if( !isset( $schedules[$schedule_name] ) ) {
				$schedules[$schedule_name] = array(
					'interval' => $interval,
					'display' => __( 'Lemonade SNA custom WP Cron interval', 'lemonade_sna' )
				);
			}
		}
	}
	
	return $schedules;

}

?>
