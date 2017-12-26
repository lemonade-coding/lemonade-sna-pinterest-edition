<?php

/**
 * Hooks and filters with related on functions
 *
 * @package LemonadeSNAPinterest
 * @since 1.0.0 
 */

/////////////////////
//PLUGIN ACTIVATION//
/////////////////////

add_action( 'lemonade_sna_pinterest_activate_plugin', 'lemonade_sna_pinterest_activate_plugin_action' ); //When plugin is activated

//////// 
//INIT// 
////////

add_action( 'init', 'lemonade_sna_pinterest_control_cron_jobs' ); //Control WP Cron Jobs

add_action( 'init', 'lemonade_sna_pinterest_update_templates' ); //Update Pinterest Autoposter Templates
           
/////////////////////////
//ADMIN ENQUEUE SCRIPTS//
/////////////////////////

add_action( 'admin_enqueue_scripts', 'lemonade_sna_pinterest_enqueue_scripts' ); //Enqueue scripts

add_action( 'admin_enqueue_scripts', 'lemonade_sna_pinterest_enqueue_styles' ); //Enqueue styles		   
		   
////////////////
//ADMIN SAVING//
//////////////// 

add_action( 'admin_post_lemonade_sna_pinterest_save_settings', 'lemonade_sna_pinterest_save_settings' ); //Save plugin main settings

add_action( 'admin_post_lemonade_sna_pinterest_save_api', 'lemonade_sna_pinterest_save_api' ); //Save Pinterest API parameters

add_action( 'admin_post_lemonade_sna_pinterest_save_template', 'lemonade_sna_pinterest_save_template' ); //Save Pinterest Autoposter template

add_action( 'admin_post_lemonade_sna_pinterest_save_pin_template', 'lemonade_sna_pinterest_save_pin_template' ); //Save Pinterest Pin template

if( defined( 'DOING_AJAX' ) && DOING_AJAX ){
	
	add_action( 'wp_ajax_lemonade_sna_pinterest_autoposter_stream', 'lemonade_sna_pinterest_autoposter_stream' ); //Update information for Pinterest Autoposter live stream
	
	add_action( 'wp_ajax_lemonade_sna_pinterest_live_stream_list', 'lemonade_sna_pinterest_live_stream_list' ); //Get live stream list html
	
	add_action( 'wp_ajax_lemonade_sna_pin_template_test', 'lemonade_sna_pin_template_test' ); //Test Pin template
	
	add_action( 'wp_ajax_lemonade_sna_delete_pin_template', 'lemonade_sna_delete_pin_template' ); //Delete Pin template
	
}

/////////////
//FUNCTIONS//
/////////////

/**
 * Fires when the plugin is activated.
 *
 * Makes some stuff when the plugin is activated, for example, adds some columns to a DB.
 *
 * @since 2.0.0
 * @global object $wpdb The WPDB object.
 */
function lemonade_sna_pinterest_activate_plugin_action() {

	global $wpdb;
	
	$name = "board_id";
	$table = $wpdb->prefix . 'lemonade_autoposter_posts_published'; //Get name for the table
	$attr = "varchar(255) NOT NULL";

	lemonade_sna_pinterest_add_column_to_db( $name, $table, $attr );	

}

/**
 * Adds job to WP Cron.
 *
 * @since 1.0.0
 *
 * @see 'lemonade_sna_cron'
 */
function lemonade_sna_pinterest_control_cron_jobs() {
	if( defined('DOING_CRON') && DOING_CRON ){			
		add_action( 'lemonade_sna_cron', 'lemonade_sna_pinterest_cron' );
	}
}

/**
 * Lemonade SNA Pinterest WP Cron job.
 *
 * Publishes posts to boards according to rules which are described by a given template.
 *
 * @since 1.0.0
 * @since 2.0.0 Some data validation and sanitization issues fixed.
 *
 * @see lemonade_sna_pinterest_add_posts_to_line()
 * @see lemonade_sna_pinterest_deactivate_template()
 * @see lemonade_sna_is_post_published_on_pinterest()
 * @see lemonade_sna_pinterest_get_date_or_time_with_timezone()
 * @global object $wpdb WPDB object.
 *
 * @param int $templ_id Template ID. 
 * @return bool|object True if success, false or WP_Error object otherwise. 
 */
function lemonade_sna_pinterest_cron( $templ_id ) {

	global $wpdb;
	
	$templ_id = (int)$templ_id;
	
	$reports = '' != get_option( 'lemonade_sna_pinterest_settings' )['reports'] ? get_option( 'lemonade_sna_pinterest_settings' )['reports'] : false;
	$reports = (bool)$reports;
	
	$lines = get_option( 'lemonade_sna_pinterest_autoposter_lines' );
	$line = isset( $lines[$templ_id] ) ? $lines[$templ_id] : array();
	
	if( empty( $line ) || !is_array( $line['line'] ) ) {
		return new WP_Error( 'lemonade_sna_empty_line', __( "The WP Cron Job wasn't done due to an error in Lemonade Autoposter Template", 'lemonade_sna' ) );
	}
	
	$datetimenonce = time();	
	
	if( empty( $line['line'] ) ) {
		$terminate = $line['terminate']; //What to do if all posts in a line were published
		switch( $terminate ) {
			case 'wait_new' : //Wait for new posts
				return false;
			case 'start_new' :
				$line = lemonade_sna_pinterest_add_posts_to_line( array(), $templ_id, true ); //Start a new cycle
				break;
			case 'stop_work' :
				lemonade_sna_pinterest_deactivate_template( $templ_id ); //Deactivate template and remove WP Cron job
				return false;
		}		
	}
	
	$timezone = !empty( $line['special']['time_zone'] ) ? sanitize_text_field( $line['special']['time_zone'] ) : 'UTC';	
	$cur_date = lemonade_sna_pinterest_get_date_or_time_with_timezone( 0, 'Y-m-d', $timezone );
	
	$has_limit = false;
	
	if( !empty( $line['limit'] ) ) { //Check that not over the limit
		$today_limit = !empty( $line['limit']['limit'] ) ? (int)$line['limit']['limit'] : '';
		if( !empty( $today_limit ) ) {
			$has_limit = true;
			$today_posted = !empty( $line['limit']['posted'] ) ? $line['limit']['posted'] : array();
			if( !empty( $today_posted ) ) {
				foreach( $today_posted as $today_date => $posts_amount ) {
					if( strtotime( $today_date ) == strtotime( $cur_date ) ) {
						if( $today_limit <= $posts_amount ) {
							return false;
						}
					}
				}
			}
		}
	}
	
	$to_post = $line['line']; //Posts to post
	$duplicate = !empty( $line['duplicate'] ) ? sanitize_text_field( $line['duplicate'] ) : 'yes'; //Post again if already was posted or not?
	$image_default = !empty( $line['image_default'] ) ? esc_url( $line['image_default'] ) : ''; //Use default image if no image attached to the post?
	$sim = !empty( $line['simultaneous'] ) ? sanitize_text_field( $line['simultaneous'] ) : 'simultaneous'; //How to post on different boards: simultaneously, step by step or random?
	$cycle = !empty( $line['cycle'] ) ? (int)$line['cycle'] : 1; //Get current cycle

	$table = $wpdb->prefix . 'lemonade_autoposter_posts_published';

	$post_prev = '';
	
	if( !empty( $to_post ) ) {
		foreach( $to_post as $key => $post_array ) {

			foreach( $post_array as $post_id => $board_id ) {
				
				$post_id = (int)$post_id;
				
				if( !empty( $post_prev ) && $post_id != $post_prev ) {			
					return true;
				}			
				if( $duplicate == 'no' ) {
					$is_published = lemonade_sna_is_post_published_on_pinterest( $post_id, $board_id ); //Check if a post was already published on a board
					if( false !== $is_published ) {
						//Remove the post with the board from the line
						unset( $line['line'][$key] );
						$lines[$templ_id] = $line;
						update_option( 'lemonade_sna_pinterest_autoposter_lines', $lines );
						continue 2;					
					}
				}
				
				//Try to get a template for Pin
				
				$pins_template = get_post_meta( $post_id, '_lemonade_sna_pins_template', true );
				
				$use = !empty( $pins_template['use'] ) ? (bool)$pins_template['use'] : false; //Use individual template?
				
				if( !$use ) {				
					$pins_template = array();
					$pins_templates = get_option( 'lemonade_sna_pinterest_pin_templates' );
					$post_type = get_post_type( $post_id );
					if( !empty( $pins_templates ) ) {
						if( !empty( $post_type ) ) {
							foreach( $pins_templates as $t ) {
								$pts = !empty( $t['rules']['post_types'] ) ? $t['rules']['post_types'] : array();
								if( empty( $pts ) ) {
									continue;
								} else {
									foreach( $pts as $pt => $tax ) {
										if( $pt == $post_type ) {
											if( empty( $tax ) ) {
												$pins_template = $t;
											} else {
												foreach( $tax as $ct => $terms ) {
													
													$terms = !empty( $terms['terms'] ) ? $terms['terms'] : array();
													
													array_walk( $terms, function( &$item ) {
														$item = (int)$item;
													} );
													
													$post_terms = wp_get_post_terms( $post_id, sanitize_text_field( $ct ), array(
														'fields' => 'ids'
													) );
													
													if( !empty( $post_terms ) && !is_wp_error( $post_terms ) ) {
														foreach( $post_terms as $post_term ) {
															if( in_array( $post_term, $terms ) ) {
																$pins_template = $t;
															}
														}
													}
													
												}
											}
										}
									}
								}
							}					
						}
					}
				}
				
				if( !empty( $pins_template ) ) { //If template exists then use it for posting
					$pin_image = !empty( $pins_template['rules']['pin_image'] ) ? esc_attr( $pins_template['rules']['pin_image'] ) : '';
					$pin_link = !empty( $pins_template['rules']['pin_link'] ) ? esc_attr( $pins_template['rules']['pin_link'] ) : '';
					$pin_note = !empty( $pins_template['rules']['pin_note'] ) ? esc_attr( $pins_template['rules']['pin_note'] ) : '';
					$sep = !empty( $pins_template['rules']['tags_sep'] ) ? esc_attr( $pins_template['rules']['tags_sep'] ) : '';
					$limit = !empty( $pins_template['rules']['text_limit'] ) ? (int)$pins_template['rules']['text_limit'] : '';				
					$split = !empty( $pins_template['rules']['split'] ) ? (bool)$pins_template['rules']['split'] : false;
					$note = lemonade_sna_pinterest_shortcodes( $pin_note, $post_id, $sep, $limit, $split );
					$image_url = lemonade_sna_pinterest_shortcodes( $pin_image, $post_id, $sep, $limit, $split );
					if( empty( $image_url ) ) {
						if( !empty( $image_default ) ) {
							$image_url = $image_default;
						}
					}
					if( wp_http_validate_url( $image_url ) === false ) {
						$image_url = '';
					}				
					$link = lemonade_sna_pinterest_shortcodes( $pin_link, $post_id, $sep, $limit, $split );
					if( wp_http_validate_url( $link ) === false ) {
						$link = '';
					}				
				} else { //Use default values 	
					$note = get_the_excerpt( $post_id );
					$image_url = get_the_post_thumbnail_url( $post_id );
					if( empty( $image_url ) ) {
						if( !empty( $image_default ) ) { //Use default image if no thumbnail is attached
							$image_url = $image_default;
						}
					}
					if( wp_http_validate_url( $image_url ) === false ) {
						$image_url = '';
					}					
					$link = get_permalink( $post_id );
					if( wp_http_validate_url( $link ) === false ) {
						$link = '';
					}					
				}
				
				$done = false;
				$pin_posted = '';
				
				/**
				 * Requires programm for posting
				 */			
				require( LEMONADE_SNA_PINTEREST_EXT_PATH . '/tiles/lemonade_sna_pinterest_post_on_board.php' );
				
				if( isset( $pin_posted ) && !empty( $pin_posted->id ) ) { //Check that there was a responce from Pinterest
					$done = true;
				}
				
				//Check if the post was published before by the same template
				$published = lemonade_sna_is_post_published_on_pinterest( $post_id, $board_id, $templ_id ); 
				$file_time = lemonade_sna_pinterest_get_date_or_time_with_timezone( 0, 'Y-m-d H:i:s', 'UTC' ); //When was posted
				
				if( $done === true ) {
					if( false !== $published ) { 
						$done = $wpdb->update( //Update an existing row in the DB
							$table,
							array(
								'when_done' => $file_time,
								'cycle_count' => $cycle
							),
							array(
								'id' => $published
							),
							array(
								'%s',
								'%d'
							),
							array(
								'%d'
							)
						);
					} else {
						$done = $wpdb->insert( //Insert a new row to the DB
							$table,
							array(
								'post_id' => $post_id,
								'board_id' => $board_id,
								'template_id' => $templ_id,
								'when_done' => $file_time,
								'cycle_count' => $cycle
							),
							array(
								'%d',
								'%s',
								'%d',
								'%s',
								'%d'
							)
						);
					}
				}
				if( false !== $done ) { //If the post was published by WP Cron 
					
					//Update the line for the template
					unset( $line['line'][$key] );
					if( $has_limit ) {
						if( !empty( $line['limit']['posted'][$cur_date] ) ) {
							$line['limit']['posted'][$cur_date] = $line['limit']['posted'][$cur_date] + 1;
						} else {
							$line['limit']['posted'] = array();
							$line['limit']['posted'][$cur_date] = 1;
						}
					}				
					$lines[$templ_id] = $line;
					update_option( 'lemonade_sna_pinterest_autoposter_lines', $lines );
					
					if( $reports ) {
						//Write information into a Log file
						$file = fopen( LEMONADE_SNA_PINTEREST_PLUGIN_PATH . "/lemonade_sna_pinterest_log.log", "a+" );			
						$file_text = $file_time . ' Post ID:' . $post_id . ' published on Board ID:' . $board_id . ' Template ID:' . $templ_id . " \n";
						fwrite( $file, $file_text );
						fclose( $file );
					}
					
					if( $sim == 'simultaneous' ) {
						$post_prev = $post_id;					
					} else {				
						return true;
					}
				} else {
					
					if( $reports ) {
						//Write information about an error into a Log file				
						$file = fopen( LEMONADE_SNA_PINTEREST_PLUGIN_PATH . "/lemonade_sna_pinterest_log.log", "a+" );			
						$file_text = $file_time . ' AN ERROR OCCURED: ' . ' Post ID:' . $post_id . ' was not published on Board ID:' . $board_id . ' Template ID:' . $templ_id . " \n";
						fwrite( $file, $file_text );
						fclose( $file );
					}
					
					if( $sim == 'simultaneous' ) {
						$post_prev = $post_id;				
					}
				}	
			}		

		}
	}
}

/**
 * Enqueue scripts.
 *
 * Adds scripts to admin area.
 *
 * @since 1.0.0
 *
 * @param string $hook Page slug.
 */
function lemonade_sna_pinterest_enqueue_scripts( $hook ) {

	wp_register_script( 'lemonade_sna_pinterest', LEMONADE_SNA_PINTEREST_EXT_URL . '/assets/js/lemonade_sna_pinterest.js', array() );
	wp_register_script( 'lemonade_sna_pinterest_pin_template', LEMONADE_SNA_PINTEREST_EXT_URL . '/assets/js/lemonade_sna_pinterest_pin_templates.js', array() );
	wp_register_script( 'lemonade_sna_pinterest_heartbeat', LEMONADE_SNA_PINTEREST_EXT_URL . '/assets/js/lemonade_sna_pinterest_heartbeat.js', array() );
	
	$nonce = wp_create_nonce( 'lemonade-sna-pinterest-ajax' );
	$local = array(
		'will' => __( 'will be published', 'lemonade_sna' ),
		'was' => __( 'was published', 'lemonade_sna' ),
		'nonce' => $nonce
	);	
	
	if( strpos( $hook, 'lemonade_sna' ) !== false && strpos( $hook, 'pinterest' ) !== false ) { //Enqueue scripts to all Pinterest admin pages
		wp_enqueue_script( 'lemonade_sna_pinterest' );
	}
	
	if( 'lemonade-sna-pinterest_page_lemonade_sna_pinterest3' == $hook ) { //Enqueue script for Pinterest Pin templates settings page
		wp_enqueue_script( 'lemonade_sna_pinterest_pin_template' );
		wp_localize_script( 'lemonade_sna_pinterest_pin_template', 'lemonade_sna_test', $local );
	}	
	
	if( 'lemonade-sna-pinterest_page_lemonade_sna_pinterest6' == $hook ) { //Enqueue script for Pinterets Live Stream page
		wp_enqueue_script( 'lemonade_sna_pinterest_heartbeat' );
		wp_localize_script( 'lemonade_sna_pinterest_heartbeat', 'lemonade_sna_heartbeat', $local );
	}	
	
}

/**
 * Enqueue styles.
 *
 * Enqueues plugin styles.
 *
 * @since 1.0.0
 *
 * @param string $hook Page slug. 
 */
function lemonade_sna_pinterest_enqueue_styles( $hook ) {

	wp_register_style( 'lemonade-sna-pinterest', LEMONADE_SNA_PINTEREST_EXT_URL . '/assets/css/lemonade_sna_pinterest.css', array() );

	if( strpos( $hook, 'lemonade_sna_pinterest' ) !== false ) { //Add styles only to Pinterest plugin dashboard pages
		wp_enqueue_style( 'lemonade-sna-pinterest' );
	}
	
}

/**
 * Updates Lemonade SNA Pinterest templates.
 *
 * Check if any template has to be update (by time) and starts the update. 
 *
 * @since 1.0.0
 * @since 2.0.0 Some data sanitization added.
 *
 * @see lemonade_sna_pinterest_get_list_of_templates()
 * @see lemonade_sna_pinterest_add_posts_to_line()
 * @global object $wpdb WPDB object. 
 */
function lemonade_sna_pinterest_update_templates() {
	
	global $wpdb;
	$table = $wpdb->prefix . 'lemonade_autoposter_templates';
	$templates = lemonade_sna_pinterest_get_list_of_templates();
	$cur_datetime = lemonade_sna_pinterest_get_date_or_time_with_timezone( 0, 'Y-m-d H:i:s', 'UTC' );
	
	if( !empty( $templates ) ) {
		foreach( $templates as $template ) {
			
			$tid = (int)$template->id;
			$no_update = false;
			$updated = false;
			$next_update = !empty( $template->next_update ) ? sanitize_text_field( $template->next_update ) : '0000-00-00 00:00:00'; //When has to be updated
			$new_datetime = '0000-00-00 00:00:00';
			$rules = !empty( $template->rules ) ? json_decode( $template->rules ) : object(); //Get template rules
			
			if( !empty( $rules ) ) {
				$update = $rules->update;
				if( !empty( $update ) ) {
					$days = !empty( $update->days ) ? (int)$update->days : 0;
					$hours = !empty( $update->hours ) ? (int)$update->hours : 0;
					$minutes = !empty( $update->minutes ) ? (int)$update->minutes : 0;
					if( $days == 0 && $hours == 0 && $minutes == 0 ) {
						$no_update = true; //Never to be updated
					}					
				}
				if( $next_update != '0000-00-00 00:00:00' ) {
					if( !$no_update ) {
						$prob_next_update = date( 'Y-m-d H:i:s', strtotime( $cur_datetime . ' +' . $days . 'days +' . $hours . 'hours +' . $minutes . 'minutes' ) ); //When would be the next update from now according to the template settings
						
						if( $prob_next_update && strtotime( $next_update ) ) {
							if( strtotime( $prob_next_update ) < strtotime( $next_update ) ) { //If the probable next update is earlier than the update time in the DB
								$next_update = $prob_next_update; 
								$wpdb->update( //Change update time in the DB
									$table,
									array(
										'next_update' => $prob_next_update
									),
									array(
										'id' => $tid
									),
									array(
										'%s'
									),
									array(
										'%d'
									)
								);							
							}
						}
					}
					if( strtotime( $next_update ) ) {
						if( strtotime( $cur_datetime ) > strtotime( $next_update ) ) { //If it is the time for the update
							lemonade_sna_pinterest_add_posts_to_line( array(), $tid ); //Create a new line for Autoposter
							if( !$no_update ) {
								
								$new_datetime = date( 'Y-m-d H:i:s', strtotime( $cur_datetime . ' +' . $days . 'days +' . $hours . 'hours +' . $minutes . 'minutes' ) );
								
								if( $new_datetime ) {
									$updated = $wpdb->update( //Save new update time in the DB
										$table,
										array(
											'next_update' => $new_datetime
										),
										array(
											'id' => $tid
										),
										array(
											'%s'
										),
										array(
											'%d'
										)
									);
								}
								
							} else {
								return;
							}
						}
					}
				} else {
					if( !$no_update ) {
						
						$new_datetime = date( 'Y-m-d H:i:s', strtotime( $cur_datetime . ' +' . $days . 'days +' . $hours . 'hours +' . $minutes . 'minutes' ) );
						
						if( $new_datetime ) {
							$updated = $wpdb->update( //Save update time in the DB if it was not setted up
								$table,
								array(
									'next_update' => $new_datetime
								),
								array(
									'id' => $tid
								),
								array(
									'%s'
								),
								array(
									'%d'
								)
							);	
						}
						
					} else {
						return;
					}
				}				
			} else {
				return;
			}
			
			/**
			 * Fires after an Autoposter template was updated.
			 *
			 * @param int $tid Updated template ID.
			 * @param object $rules Rules of the template.
			 * @param string $next_update UTC datetime when to do the next update formatted 'Y-m-d H:i:s'.
			 * @param string $new_datetime UTC datetime when to do the new update if the template has been updated. Formatted 'Y-m-d H:i:s'.
			 * @param string $cur_datetime Curent datetime for UTC timezone formatted 'Y-m-d H:i:s'.
			 * @param object $template The template object.
			 * @param bool|int $updated False if error occured or number of rows affected by update.
			 */
			do_action( 'lemonade_sna_pinterest_after_template_updated', $tid, $rules, $next_update, $new_datetime, $cur_datetime, $template, $updated );
		}
	}
}

/**
 * Saves plugin main settings.
 *
 * Saves settings like user capabilities to manage plugin, reports, Rich Pin meta.
 *
 * @since 1.0.0
 * @since 2.0.0 Some data validation and sanitization issues fixed.
 */
function lemonade_sna_pinterest_save_settings() {

	unset( $_SESSION['lsna_error'] );
	unset( $_SESSION['lsna_success'] );
	
	if( ! wp_verify_nonce( $_POST['_wpnonce'], 'lsna-settings' ) ) {
		$_SESSION['lsna_error'][] = __( 'Nonce is invalid.', 'lemonade_sna' ); 
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();
	}	
	
	if ( !current_user_can( 'manage_options' ) ) {
		$_SESSION['lsna_error'][] = __( 'You have not capability to manage these settings.', 'lemonade_sna' );
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();
	}

	$roles['dashboard'] = !empty( $_POST['roles']['dashboard'] ) ? sanitize_text_field( $_POST['roles']['dashboard'] ) : 'administrator';
	$roles['api'] = !empty( $_POST['roles']['api'] ) ? sanitize_text_field( $_POST['roles']['api'] ) : 'administrator';
	$roles['pin'] = !empty( $_POST['roles']['pin'] ) ? sanitize_text_field( $_POST['roles']['pin'] ) : 'administrator';
	$roles['autoposter'] = !empty( $_POST['roles']['autoposter'] ) ? sanitize_text_field( $_POST['roles']['autoposter'] ) : 'administrator';
	
	$switch_reports = isset( $_POST['switch_reports'] ) ? true : false;
	
	$option = array(
		'roles' => $roles,
		'reports' => $switch_reports
	);
	
	update_option( 'lemonade_sna_pinterest_settings', $option );
	
	$_SESSION['lsna_success'] = __( 'Settings were updated.', 'lemonade_sna' );
	
	/**
	 * Fires after saving settings on Lemonade SNA Pinterest page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $option Array of setted up options.
	 */
	do_action( 'lemonade_sna_pinterest_after_settings_savings', $option );

    wp_redirect( $_SERVER['HTTP_REFERER'] );
    exit();	

}

/**
 * Saves Pinterest API parameters.
 *
 * Saves Pinterest API settings token from API Settings page to 'lemonade_sna_access_token' option.
 *
 * @since 1.0.0
 * @since 2.0.0 Some data sanitization added.
 */
function lemonade_sna_pinterest_save_api() {

	$settings = get_option( 'lemonade_sna_pinterest_settings' );
	if( !empty( $settings ) ) {
		$cap_save = !empty( $settings['roles']['api'] ) ? sanitize_text_field( $settings['roles']['api'] ) : 'administrator';
		$cap_save = array_keys( get_role( $cap_save )->capabilities );
		$cap_save = $cap_save[0];
	} else {
		$cap_save = 'manage_options';
	}
	
	unset( $_SESSION['lsna_error'] );
	unset( $_SESSION['lsna_success'] );

	if( ! wp_verify_nonce( $_POST['_wpnonce'], 'lsna-api' ) ) {
		$_SESSION['lsna_error'][] = __( 'Nonce is invalid.', 'lemonade_sna' ); 
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();
	}	
	
	if ( !current_user_can( $cap_save ) ) {
		$_SESSION['lsna_error'][] = __( 'You have not capability to manage these settings.', 'lemonade_sna' );
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();
	}	
	
	if( !empty( $_POST['app_id'] ) ) {
		if( lemonade_sna_pinterest_numbers_only( $_POST['app_id'] ) ) {
			update_option( 'lemonade_sna_pinterest_app_id', $_POST['app_id'] );
		} else {
			$_SESSION['lsna_error'][] = __( 'App ID should be numbers only.', 'lemonade_sna' );
			wp_redirect( $_SERVER['HTTP_REFERER'] );
			exit();			
		}
	}
	
	if( !empty( $_POST['app_secret'] ) ) {
		if( lemonade_sna_pinterest_numbers_letters_only( $_POST['app_secret'] ) ) {
			update_option( 'lemonade_sna_pinterest_app_secret', $_POST['app_secret'] );
		} else {
			$_SESSION['lsna_error'][] = __( 'App secret should be numbers and letters only.', 'lemonade_sna' );
			wp_redirect( $_SERVER['HTTP_REFERER'] );
			exit();			
		}
	}
	
 	if( isset( $_POST['access_token'] ) ) {
		update_option( 'lemonade_sna_access_token', esc_attr( $_POST['access_token'] ) );
	}	

	$_SESSION['lsna_success'] = __( 'Settings were updated.', 'lemonade_sna' );
	
	/**
	 * Fires after Pinterest API settings saving.
	 *
	 * @since 1.0.0
	 */	
	do_action( 'lemonade_sna_pinterest_after_api_settings_saving' ); 

    wp_redirect( $_SERVER['HTTP_REFERER'] );
    exit();

}

/**
 * Saves Pinterest Autoposter template.
 *
 * Prepares Autoposter template and saves it into the DB.
 *
 * @since 1.0.0
 * @since 2.0.0 Some data validation and sanitization issues fixed.
 *
 * @global object $wpdb WPDB object.
 */
function lemonade_sna_pinterest_save_template() {

	global $wpdb;
	
	$settings = get_option( 'lemonade_sna_pinterest_settings' );
	if( !empty( $settings ) ) {
		$cap_save = !empty( $settings['roles']['autoposter'] ) ? sanitize_text_field( $settings['roles']['autoposter'] ) : 'administrator';
		$cap_save = array_keys( get_role( $cap_save )->capabilities );
		$cap_save = $cap_save[0];
	} else {
		$cap_save = 'manage_options';
	}
	
	unset( $_SESSION['lsna_error'] );
	unset( $_SESSION['lsna_success'] );	
	
	$_SESSION['lsna_error'] = array();
	$_SESSION['lsna_success'] = '';	
	
	if( !wp_verify_nonce( $_POST['_wpnonce'], 'lsna-template' ) ) {
		$_SESSION['lsna_error'][] = __( 'Nonce was not verified.', 'lemonade_sna' );
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();			
	}
	
	if ( !current_user_can( $cap_save ) ) {
		$_SESSION['lsna_error'][] = __( 'You have not capability to manage these settings.', 'lemonade_sna' );
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();
	}		
	
	$table = $wpdb->prefix . 'lemonade_autoposter_templates';
	
	$edit = isset( $_POST['edit_template'] ) ? true : false; //If editing existing template
	
	$name = esc_attr( $_POST['template-name'] );
	
	if( empty( $name ) ) { //Check that template name is not empty
		$_SESSION['lsna_error'][] = __( 'Template name can not be empty.', 'lemonade_sna' );
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();	
	}
	
	$number = count( $wpdb->get_results( "SELECT id FROM $table" ) ); //Check number of previous templates
	
	if( $number >= 2 && !$edit ) {
		$_SESSION['lsna_error'][] = __( 'You are not allowed to create more than 2 templates with the free version of plugin.', 'lemonade_sna' );
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();		
	}
	
	$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE title=%s", $name ) );
	
	if( $exists != NULL ) { //Check that the template nameis unique
		if( !$edit ) {
			$_SESSION['lsna_error'][] = __( 'Template name should be unique. This name already exists.', 'lemonade_sna' );
			wp_redirect( $_SERVER['HTTP_REFERER'] );
			exit();	
		} else {
			if( $exists != $_POST['edit_template'] ) {
				$_SESSION['lsna_error'][] = __( 'Template name should be unique. This name already exists.', 'lemonade_sna' );
				wp_redirect( $_SERVER['HTTP_REFERER'] );
				exit();					
			}
		}			
	}
	
	//Get rules
	
	if( !empty( $_POST['post_types'] ) && is_array( $_POST['post_types'] ) ) {
		$post_types = array_filter( $_POST['post_types'] ); // Remove elements with empty values from array
		if( !empty( $post_types ) ) {
			if( isset( $post_types['selected'] ) ) {
				$post_types['selected'] = array_filter( $post_types['selected'] );
				array_walk( $post_types['selected'], function( &$item ) {
					$item = trim( sanitize_text_field( $item ) ); // Sanitize each item from array
				} );
			}
			if( isset( $post_types['include'] ) ) {
				$post_types['include'] = 1;
			}
		}
	} else {
		$post_types = array();
	}
	
	if( !empty( $_POST['post_formats'] ) && is_array( $_POST['post_formats'] ) ) {
		$post_formats = array_filter( $_POST['post_formats'] );
		if( !empty( $post_formats ) ) {
			if( isset( $post_formats['selected'] ) ) {
				$post_formats['selected'] = array_filter( $post_formats['selected'] );
				array_walk( $post_formats['selected'], function( $item ) {
					$item = trim( sanitize_text_field( $item ) );
				} );
			}
			if( isset( $post_formats['include'] ) ) {
				$post_formats['include'] = 1;
			}
		}
	} else {
		$post_formats = array();
	}
	
	if( !empty( $_POST['post_cats'] ) && is_array( $_POST['post_cats'] ) ) {
		$post_cats = array_filter( $_POST['post_cats'] );
		if( !empty( $post_cats ) ) {
			if( isset( $post_cats['selected'] ) ) {
				$post_cats['selected'] = array_filter( $post_cats['selected'] );
				array_walk( $post_cats['selected'], function( &$item ) {
					$item = (int)trim( $item );
				} );
			}
			if( isset( $post_cats['include'] ) ) {
				$post_cats['include'] = 1;
			}
			if( !empty( $post_cats['relation'] ) ) {
				if( !in_array( $post_cats['relation'], array( 'and', 'or' ) ) ) {
					$post_cats['relation'] = 'or';
				}
			}
		}
	} else {
		$post_cats = array();
	}
	
	if( !empty( $_POST['post_tags'] ) && is_array( $_POST['post_tags'] ) ) {
		$post_tags = array_filter( $_POST['post_tags'] );
		if( !empty( $post_tags ) ) {
			if( isset( $post_tags['selected'] ) ) {
				$post_tags['selected'] = array_filter( $post_tags['selected'] );
				array_walk( $post_tags['selected'], function( &$item ) {
					$item = (int)trim( $item );
				} );
			}
			if( isset( $post_tags['include'] ) ) {
				$post_tags['include'] = 1;
			}
			if( !empty( $post_tags['relation'] ) ) {
				if( !in_array( $post_tags['relation'], array( 'and', 'or' ) ) ) {
					$post_tags['relation'] = 'or';
				}
			}			
		}
	} else {
		$post_tags = array();
	}
	
	if( !empty( $_POST['post_authors'] ) && is_array( $_POST['post_authors'] ) ) {
		$post_authors = array_filter( $_POST['post_authors'] );
		if( !empty( $post_authors ) ) {
			if( isset( $post_authors['selected'] ) ) {
				$post_authors['selected'] = array_filter( $post_authors['selected'] );
				array_walk( $post_authors['selected'], function( &$item ) {
					$item = (int)trim( $item );
				} );
			}
			if( isset( $post_authors['include'] ) ) {
				$post_authors['include'] = 1;
			}
		}
	} else {
		$post_authors = array();
	}
	
	if( !empty( $_POST['dates_filter'] ) ) {
		foreach( $_POST['dates_filter'] as $key => $value ) {
			if( $key != 'from' && $key != 'to' ) {
				continue;
			}
			if( empty( $value ) || date( 'Y-m-d', strtotime( $value ) ) ) {
				$dates_filter[$key] = $value;
			}
		}
	} else {
		$dates_filter = array( 'from' => '', 'to' => '' );
	}
	
	$duplicate = trim( $_POST['duplicate'] ) ? trim( sanitize_text_field( $_POST['duplicate'] ) ) : 'yes';	
	$time_zone = !empty( $_POST['time-zone'] ) ? trim( sanitize_text_field( $_POST['time-zone'] ) ) : 'UTC';
	$posts_limit = (int)trim( $_POST['posts_limit'] ) ? (int)trim( $_POST['posts_limit'] ) : '';
	$publish_order = trim( $_POST['publish_order'] ) ? trim( sanitize_text_field( $_POST['publish_order'] ) ) : 'new_to_old';
	$when_finished = trim( $_POST['when_finished'] ) ? trim( sanitize_text_field( $_POST['when_finished'] ) ) : 'wait_new';
	$simultaneous = trim( $_POST['simultaneous'] ) ? trim( sanitize_text_field( $_POST['simultaneous'] ) ) : 'simultaneous';
	
	$image_default = '';
	if( !empty( $_POST['featured_images'] ) ) {
		if( !empty( $_POST['featured_images']['checked'] ) ) {
			$image_default = !empty( $_POST['featured_images']['default'] ) ? trim( esc_attr( $_POST['featured_images']['default'] ) ) : '';
		}
	}
	
	if( !empty( $_POST['boards'] ) ) {
		$boards[] = trim( sanitize_text_field( $_POST['boards'] ) );
	} else {
		$boards = array();
	}
	
	if( !empty( $_POST['freq'] ) ) {
		$freq['days'] = (int)trim( $_POST['freq']['days'] ) ? (int)trim( $_POST['freq']['days'] ) : '';
		$freq['hours'] = (int)trim( $_POST['freq']['hours'] ) ? (int)trim( $_POST['freq']['hours'] ) : '';
		$freq['minutes'] = (int)trim( $_POST['freq']['minutes'] ) ? (int)trim( $_POST['freq']['minutes'] ) : '';
	} else {
		$freq['days'] = '';
		$freq['hours'] = '';
		$freq['minutes'] = '';
	}
	
	if( empty( $_POST['update']['days'] ) && empty( $_POST['update']['hours'] ) && empty( $_POST['update']['minutes'] ) ) {
		$update['days'] = '';
		$update['hours'] = 24;
		$update['minutes'] = '';
	} else {
		$update['days'] = lemonade_sna_pinterest_validate_interval( 'days', (int)trim( $_POST['update']['days'] ) ) ? (int)trim( $_POST['update']['days'] ) : '';
		$update['hours'] = lemonade_sna_pinterest_validate_interval( 'hours', (int)trim( $_POST['update']['hours'] ) ) ? (int)trim( $_POST['update']['hours'] ) : '';
		$update['minutes'] = lemonade_sna_pinterest_validate_interval( 'minutes', (int)trim( $_POST['update']['minutes'] ) ) ? (int)trim( $_POST['update']['minutes'] ) : '';
	}
	
	//Create rules array
	$rules = array(
		'post_types' => $post_types,
		'post_formats' => $post_formats,
		'post_cats' => $post_cats,
		'post_tags' => $post_tags,
		'post_authors' => $post_authors,
		'dates_filter' => $dates_filter,
		'image_default' => $image_default,
		'duplicate' => $duplicate,
		'freq' => $freq,
		'time_zone' => $time_zone,
		'posts_limit' => $posts_limit,
		'boards' => $boards,
		'publish_order' => $publish_order,
		'simultaneous' => $simultaneous,
		'when_finished' => $when_finished,
		'update' => $update		
	);
	
	/**
	 * Filters rules before to save.
	 *
	 * @since 1.0.0
	 *
	 * @param array $rules Rules array.
	 * @param array $_POST Post array.
	 */	
	$rules = apply_filters( 'lemonade_sna_pinterest_rules_filter', $rules, $_POST );

	$rules = json_encode( $rules );
	
	$is_active = 0;
	
	$date = lemonade_sna_pinterest_get_date_or_time_with_timezone( 0, 'Y-m-d H:i:s', 'UTC' );
	
	$new_cycle = isset( $_POST['start_new_cycle'] ) ? true : false;
	
	//Save information into the DB
	if( $edit ) {
	
		$update = $wpdb->update(
			$table,
			array(
				'title' => $name,
				'network' => 'pinterest',
				'rules' => $rules,
				'is_active' => 0,
				'date_created' => $date
			),
			array(
				'id' => $_POST['edit_template']
			),
			array(
				'%s',
				'%s',
				'%s',
				'%d',
				'%s'
			),
			array(
				'%d'
			)	
		);
		
		if( $update == 1 ) {
			$_SESSION['lsna_success'] = __( 'Template was updated.', 'lemonade_sna' );
			$template_info = array(
				'id' => (int)$_POST['edit_template'],
				'rules' => $rules
			);
			$new_template_id = (int)$_POST['edit_template'];
			$line_updated = lemonade_sna_pinterest_add_posts_to_line( $template_info, '', $new_cycle ); //Create a new line for autoposter
			if( false === $line_updated ) {
				$_SESSION['lsna_error'][] = __( 'A line was not updated. Please, try again.', 'lemonade_sna' );
			}
		} else {
			$_SESSION['lsna_error'][] = __( 'There was a mistake during template updating.', 'lemonade_sna' );		
		}
	
	} else {
	
		$insert = $wpdb->insert(
			$table,
			array(
				'title' => $name,
				'network' => 'pinterest',
				'rules' => $rules,
				'is_active' => 0,
				'date_created' => $date
			),
			array(
				'%s',
				'%s',
				'%s',
				'%d',
				'%s'
			)
		);		
		
		if( $insert == 1 ) {
			$_SESSION['lsna_success'] = __( 'Template was created.', 'lemonade_sna' );	
			$template_info = array(
				'id' => $wpdb->insert_id,
				'rules' => $rules
			);
			$new_template_id = $wpdb->insert_id;
			$line_updated = lemonade_sna_pinterest_add_posts_to_line( $template_info ); //Create a new line for Autoposter
			if( false === $line_updated ) {
				$_SESSION['lsna_error'][] = __( 'A line was not updated. Please, try again.', 'lemonade_sna' );
			}
				
		} else {
			$_SESSION['lsna_error'][] = __( 'There was an mistake during template creating.', 'lemonade_sna' );		
		}
	
	}
	
	/**
	 * Fires after template was saved.
	 *
	 * @since 1.0.0
	 *
	 * @param array $template_info Template information array.
	 */	
	do_action( 'lemonade_sna_pinterest_after_template_saved', $template_info );
	 	
	if( $edit ) {
		wp_redirect( $_SERVER['HTTP_REFERER'] );
	} elseif( !empty( $new_template_id ) ) {
		wp_redirect( add_query_arg( array( 'edit_template' => '1', 'template_id' => $new_template_id ), $_SERVER['HTTP_REFERER'] ) );
	} 

    exit();	

}

/**
 * Saves Pin template.
 *
 * Saves Pin template into 'lemonade_sna_pinterest_pin_templates' option.
 *
 * @since 1.0.0
 * @since 1.0.1 Modified to fix problem with editing and deleting templates. 
 * @since 2.0.0 Some data validation and sanitization issues fixed.
 */
function lemonade_sna_pinterest_save_pin_template() {

	$settings = get_option( 'lemonade_sna_pinterest_settings' );
	if( !empty( $settings ) ) {
		$cap_save = !empty( $settings['roles']['pin'] ) ? sanitize_text_field( $settings['roles']['pin'] ) : 'administrator';
		$cap_save = array_keys( get_role( $cap_save )->capabilities );
		$cap_save = $cap_save[0];
	} else {
		$cap_save = 'manage_options';
	}

	unset( $_SESSION['lsna_error'] );
	unset( $_SESSION['lsna_success'] );	
	
	if( !wp_verify_nonce( $_POST['_wpnonce'], 'lsna-pin-template' ) ) {
		$_SESSION['lsna_error'][] = __( 'Nonce was not verified.', 'lemonade_sna' );
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();			
	}
	
	if ( ! current_user_can( $cap_save ) ) {
		$_SESSION['lsna_error'][] = __( 'You have not capability to manage these settings.', 'lemonade_sna' );
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();
	}
	
	$edit = false;
	$edit = isset( $_POST['edit_template'] ) ? true : false;
	
	$templates = get_option( 'lemonade_sna_pinterest_pin_templates' );
	
	if( empty( $templates ) ) {
		$templates = array();
	}
	
	if( count( $templates ) > 1 && !$edit ) { //Check the number of existing templates
		$_SESSION['lsna_error'][] = __( 'You are not allowed to use more than 2 templates with the free version of plugin.', 'lemonade_sna' );
		wp_redirect( $_SERVER['HTTP_REFERER'] );		
		exit();
	}
	
	$template_name = !empty( $_POST['template_name'] ) ? sanitize_text_field( $_POST['template_name'] ) : '';
	
	if( empty( $template_name ) ) { //Check that a template name is not empty
		$_SESSION['lsna_error'][] = __( 'Template name can not be empty.', 'lemonade_sna' );
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();	
	}
	foreach( $templates as $tid => $template ) {
		$tid = (int)$tid;
		$names[] = $template['title'];
		$tids[$template['title']] = $tid;	
	}
	
	if( !empty( $names ) ) { // Check that the name is unique or that we are editing the template
		if( in_array( $template_name, $names ) ) {
			if( $edit ) {
				if( $tids[$template_name] != $_POST['edit_template'] ) {
					$_SESSION['lsna_error'][] = __( 'Template name should be unique. This name is already used for another template.', 'lemonade_sna' );
					wp_redirect( $_SERVER['HTTP_REFERER'] );
					exit();					
				}
			} else {
				$_SESSION['lsna_error'][] = __( 'Template name should be unique. This name already exists.', 'lemonade_sna' );
				wp_redirect( $_SERVER['HTTP_REFERER'] );
				exit();				
			}
		}
	}
	
	$post_types = array();
	if( !empty( $_POST['pin_template'] ) ) {
		foreach( $_POST['pin_template'] as $key => $rp_template ) {
			
			$key = sanitize_text_field( $key );
			if( isset( $rp_template['checked'] ) ) {
				$post_types[$key] = array();
				if( $key == 'post' ) {
					unset( $rp_template['checked'] );
					if( !empty( $rp_template ) ) {
						foreach( $rp_template as $ct => $terms ) {
							$ct = sanitize_text_field( $ct );
							 $terms = array_filter( $terms );
							array_walk( $terms, function( &$item ) {
								$item = (int)$item;
							} );
							$post_types[$key][$ct]['terms'] = $terms;
						}
					} else {
						unset( $post_types[$key] );
					}	
				}
			}
		}
	}	
	
	$pin_image = !empty( $_POST['pin_image'] ) ? esc_attr( $_POST['pin_image'] ) : '';
	$pin_link = !empty( $_POST['pin_link'] ) ? esc_attr( $_POST['pin_link'] ) : '';
	$pin_note = !empty( $_POST['pin_note'] ) ? esc_attr( $_POST['pin_note'] ) : '';
	$tags_sep = !empty( $_POST['separator'] ) ? esc_attr( $_POST['separator'] ) : '';
	$limit = !empty( $_POST['limit'] ) ? (int)$_POST['limit'] : '';
	$split = !empty( $_POST['split'] ) ? 1 : 0;
	
	//Create rules array
	$rules = array(
		'post_types' => $post_types,
		'pin_image' => $pin_image,
		'pin_link' => $pin_link,
		'pin_note' => $pin_note,
		'tags_sep' => $tags_sep,
		'text_limit' => $limit,
		'split' => $split
	);
	
	$date = lemonade_sna_pinterest_get_date_or_time_with_timezone( 0, 'Y-m-d H:i:s', 'UTC' );
	
	if( $edit ) {
		$templates[$_POST['edit_template']] = array(
			'title' => $template_name,
			'rules' => $rules,
			'date_created' => $date		
		);
		$new_template_id = (int)$_POST['edit_template'];
	} else {
		$last = !empty( $templates ) ? array_keys( $templates )[count( array_keys( $templates ) ) - 1] : 0;
		$new_key = $last + 1;
		$templates[$new_key] = array(
			'title' => $template_name,
			'rules' => $rules,
			'date_created' => $date
		);
		$new_template_id = $new_key;
	}
	
	update_option( 'lemonade_sna_pinterest_pin_templates', $templates );
	if( $edit ) {
		$_SESSION['lsna_success'] = __( 'Template was updated.', 'lemonade_sna' );		
	} else {
		$_SESSION['lsna_success'] = __( 'Template was created.', 'lemonade_sna' );
	}
	
	/**
	 * Fires after Lemonade SNA Pinterest Pin template saving.
	 *
	 * @since 1.0.0
	 *
	 * @param int $new_template_id New template ID.
	 * @param array $templates Array of saved templates.
	 */
	do_action( 'lemonade_sna_pinterest_after_pin_template_saving', $new_template_id, $templates );
	
	if( $edit ) {
		wp_redirect( $_SERVER['HTTP_REFERER'] );
	} elseif( !empty( $new_template_id ) ) {
		wp_redirect( add_query_arg( array( 'edit_template' => '1', 'template_id' => $new_template_id ), $_SERVER['HTTP_REFERER'] ) );
	} else {
		wp_redirect( $_SERVER['HTTP_REFERER'] );
	}
    exit();	

}

/**
 * Informs Autoposter Live Streamer about any changes. 
 *
 * Echoes "news" about Lemonade SNA Pinterest WP Cron jobs running: posted publications, 
 * updates and etc. Ajax action.
 *
 * @since 1.0.0
 * @since 2.0.0 Some data sanitization added.
 *
 * @see lemonade_sna_pinterest_get_published_posts()
 */

function lemonade_sna_pinterest_autoposter_stream() {

	$nonce = $_POST['nonce'];
	
	if( !wp_verify_nonce( $nonce, 'lemonade-sna-pinterest-ajax' ) ) {
		echo false;
		wp_die();	
	}	
	
	if( empty( $_POST['id'] ) || empty( $_POST['cycle'] ) ) {
		wp_die();
	}
	
	$templ_id = (int)$_POST['id'];
	
	if( function_exists( 'wp_next_scheduled' ) && function_exists( 'wp_schedule_event' ) ) {
		if( !wp_next_scheduled( 'lemonade_sna_cron', array( $templ_id ) ) ) {
			$responce['terminate'] = 1;
			echo wp_json_encode( $responce );
			wp_die();
		}
	}		
	
	$cycle = (int)$_POST['cycle'];
	$last_resp = !empty( $_POST['last_resp'] ) ? sanitize_text_field( $_POST['last_resp'] ) : '';	

	$lines = get_option( 'lemonade_sna_pinterest_autoposter_lines' );	
	$line = !empty( $lines[$templ_id]['line'] ) ? $lines[$templ_id]['line'] : array();
	$terminate = !empty( $lines[$templ_id]['terminate'] ) ? sanitize_text_field( $lines[$templ_id]['terminate'] ) : 'wait_new';
	$cur_cycle = !empty( $lines[$templ_id]['cycle'] ) ? (int)$lines[$templ_id]['cycle'] : 1;
	$updated = !empty( $lines[$templ_id]['updated'] ) ? sanitize_text_field( $lines[$templ_id]['updated'] ) : '';
	$timezone = !empty( $lines[$templ_id]['special']['time_zone'] ) ? sanitize_text_field( $lines[$templ_id]['special']['time_zone'] ) : 'UTC';
	$cur_date = lemonade_sna_pinterest_get_date_or_time_with_timezone( 0, 'Y-m-d', $timezone );
	$limit = !empty( $lines[$templ_id]['limit']['limit'] ) ? (int)$lines[$templ_id]['limit']['limit'] : '';
	$today_limit = !empty( $lines[$templ_id]['limit']['posted'][$cur_date] ) ? (int)$lines[$templ_id]['limit']['posted'][$cur_date] : 0;
	
	$responce = array();
	
	if( !empty( $updated ) && !empty( $last_resp ) ) {
		if( strtotime( $updated ) > $last_resp ) {			 
			$responce['updated'] = 1;		
		}
	}
	
	if( $cur_cycle != $cycle ) {
		$responce['new_cycle'] = $cur_cycle;
	}
	
	if( $today_limit >= $limit ) {
		$responce['limit_reached'] = 1;
	}
	
	if( !empty( $last_resp ) ) {
		$last_resp = $last_resp - 1;
		$compare_datetime = date( 'Y-m-d H:i:s', $last_resp );
		$published = lemonade_sna_pinterest_get_published_posts( $templ_id, $cur_cycle, '', $compare_datetime );
	} else {
		$published = lemonade_sna_pinterest_get_published_posts( $templ_id, $cur_cycle );	
	}	
	if( !empty( $published ) ) {
		foreach( $published as $publ ) {
			
			$post_id = (int)$publ->post_id;
			$board_id = lemonade_sna_pinterest_numbers_only( $publ->board_id ) ? $publ->board_id : '';		
			$datetime_conv = lemonade_sna_pinterest_get_date_or_time_with_timezone( $publ->when_done, 'Y-m-d H:i:s', $timezone );
			$date_format = get_option( 'date_format' ) != '' ? get_option( 'date_format' ) : 'Y-m-d';
			$date_format = sanitize_text_field( $date_format );
			$time_format = get_option( 'time_format' ) != '' ? get_option( 'time_format' ) : 'H:i';
			$time_format = sanitize_text_field( $time_format );
			$datetime = date( $date_format, strtotime( $datetime_conv ) );
			if( $datetime ) {
				$datetime .= ' ' . date( $time_format, strtotime( $datetime_conv ) );
				$datetime .= '<br>' . __( 'by', 'lemonade_sna' ) . ' ' . $timezone;
			} else {
				$datetime = '';
			}
			$responce['published'][] = array( 'post_id' => $post_id, 'board_id' => $board_id, 'datetime' => $datetime );
		
		}
	}	
	if( !empty( $line ) ) {
		foreach( $line as $key => $to_post ) {
			foreach( $to_post as $post_id => $board_id ) {
				$post_id = (int)$post_id;
				if( lemonade_sna_pinterest_numbers_only( $board_id ) ) {
					$responce['next'][] = array( 'post_id' => $post_id, 'board_id' => $board_id );
					break 2;
				}
			}
		}
	} else {
		switch( $terminate ) {
			case 'wait_new' :
				$responce['check_for_new'] = 1;
				break;
		}
	}
	$responce['resp_time'] = time();
	
	/**
	 * Filters a responce to Live Streamer.
	 *
	 * @since 1.0.0
	 *
	 * @param array $responce Responce array.
	 * @param int $templ_id The ID of Autoposter template for which Live Stream is created.
	 * @param int $cycle The serial number of Autoposter template's cycle.
	 * @param array $line Array with Autoposter's line parameters.
	 */
	$responce = apply_filters( 'lemonade_sna_pinterest_live_stream_responce', $responce, $templ_id, $cycle, $line );
	
	echo wp_json_encode( $responce );
	wp_die();

}

/**
 * Gets Autoposter Live Streamer html content.
 *
 * Echoes html for Autoposter Live Streamer. Ajax action.
 *
 * @since 2.0.0
 */
function lemonade_sna_pinterest_live_stream_list() {

	$nonce = $_POST['nonce'];
	
	if( !wp_verify_nonce( $nonce, 'lemonade-sna-pinterest-ajax' ) ) {
		wp_die();	
	}		
	
	$id = (int)$_POST['id'];
	if( empty( $id ) ) {
		wp_die();
	}
	
	$html = '';
	
	/**
	 * Gets list of user's Pinterest boards with DirkGroenen Pinterest API wrapper.
	 */	
	include( LEMONADE_SNA_PINTEREST_EXT_PATH . '/tiles/lemonade_sna_pinterest_show_boards.php' );
	
	$lines = get_option( 'lemonade_sna_pinterest_autoposter_lines' );
	$template = lemonade_sna_pinterest_get_autoposter_template( $id );
	ob_start();
	
	/**
	 * Creates html for Live Streamer.
	 */	
	include( LEMONADE_SNA_PINTEREST_EXT_PATH . '/tiles/lemonade_sna_pinterest_streamer.php' );
	
	$html = ob_get_contents();
	if( $html ) {
		ob_end_clean();
		$html = wp_kses_post( $html );
	}
	
	/**
	 * Filters html of Live Streamer.
	 *
	 * @since 1.0.0
	 *
	 * @param string $html Html to filter.
	 */
	echo apply_filters( 'lemonade_sna_pintereste_live_streamer_html', $html );
	
	wp_die();

}

/**
 * Tests the Pin template.
 *
 * Transforms the pin template into the information about a real image, description and 
 * link for a given post. 
 *
 * @since 2.0.0 
 *
 * @see lemonade_sna_pinterest_shortcodes()
 */
function lemonade_sna_pin_template_test() {

	if( empty( $_POST['pid'] ) || empty( $_POST['rules'] ) ) {
		echo 0;
		wp_die();
	}
	
	$pid = (int)trim( $_POST['pid'] );
	$rules = json_decode( wp_unslash( $_POST['rules'] ) );
	$pin_image = !empty( $rules->pin_image ) ? esc_attr( $rules->pin_image ) : '';
	$pin_link = !empty( $rules->pin_link ) ? esc_attr( $rules->pin_link ) : '';
	$pin_note = !empty( $rules->pin_note ) ? esc_attr( $rules->pin_note ) : '';
	
	$sep = !empty( $rules->tags_sep ) ? esc_attr( $rules->tags_sep ) : '';
	$limit = !empty( $rules->text_limit ) ? (int)$rules->text_limit : '';
	$split = !empty( $rules->split ) ? true : false;
	
	$pin_image_text = lemonade_sna_pinterest_shortcodes( $pin_image, $pid, $sep, $limit, $split );
	if( wp_http_validate_url( $pin_image_text ) !== false ) {
		$pin_image_text = '<img src="' . esc_url( $pin_image_text ) . '">';
	}
	$pin_link_text = lemonade_sna_pinterest_shortcodes( $pin_link, $pid, $sep, $limit, $split );
	if( wp_http_validate_url( $pin_link_text ) !== false ) {
		$pin_link_text = '<a href="' . esc_url( $pin_link_text ) . '" target="_blank">' . $pin_link_text . '</a>';
	}
	$pin_note_text = lemonade_sna_pinterest_shortcodes( $pin_note, $pid, $sep, $limit, $split );
	
	$responce = array(
		'pin_image' => $pin_image_text,
		'pin_link' => $pin_link_text,
		'pin_note' => $pin_note_text
	);
	echo wp_json_encode( $responce );
	wp_die();
	
}

/**
 * Deletes a Pin template.
 *
 * Deletes the information about template from 'lemonade_sna_pinterest_pin_templates' option.
 *
 * @since 1.0.0
 * @since 2.0.0 Some data sanitization added.
 */
function lemonade_sna_delete_pin_template() {
	
	$nonce = $_POST['nonce'];
	
	$settings = get_option( 'lemonade_sna_pinterest_settings' );
	if( !empty( $settings ) ) {
		$cap_read = !empty( $settings['roles']['pin'] ) ? sanitize_text_field( $settings['roles']['pin'] ) : 'administrator';
		$cap_read = array_keys( get_role( $cap_read )->capabilities );
		$cap_read = $cap_read[0];
	} else {
		$cap_read = 'manage_options';
	}	
	
	if( !wp_verify_nonce( $nonce, 'lemonade-sna-pinterest-ajax' ) ) {
		wp_die();	
	}

	if ( !current_user_can( $cap_read ) ) {
		$_SESSION['lsna_error'][] = __( 'You have not capability to manage these settings.', 'lemonade_sna' );
		wp_redirect( $_SERVER['HTTP_REFERER'] );
		exit();
	}	
	
	if( !isset( $_POST['id'] ) ) {
		echo 0;
		wp_die();
	}
	
	$id = (int)$_POST['id'];
	$templates = get_option( 'lemonade_sna_pinterest_pin_templates' );
	
	if( !empty( $templates ) ) {
		if( isset( $templates[$id] ) ) {
			unset( $templates[$id] );
			$templates = array_values( $templates );
			update_option( 'lemonade_sna_pinterest_pin_templates', $templates );
			$_SESSION['lsna_success'] = __( 'Template was deleted', 'lemonade_sna' );
		} else {
			echo 0;
		}		
	} else {
		echo 0;
	}
	wp_die();
}

?>