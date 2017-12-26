<?php

/**
 * Functions used by Pinterest part of the Lemonade SNA plugin
 *
 * @package LemonadeSNAPinterest
 * @since 1.0.0 
 */
 
/**
 * Checks if a post is published.
 *
 * Checks if a post was already published on Pinterest by the plugin.
 *
 * @since 1.0.0
 *
 * @global object $wpdb WPDB object. 
 *
 * @param int $post_id ID of post to check.
 * @param string $board_id Optional. ID of a Pinterest board on which the post might be published.
 * @param int $templ_id Optinal. ID of a template by which the post might be published.
 * @param int $cycle Optional. Number of a cycle during which the post might be published.
 * @return int|bool ID of publication made by Autoposter or false if an error occured.
 */
function lemonade_sna_is_post_published_on_pinterest( $post_id, $board_id = '', $templ_id = '', $cycle = '' ) {
	
	global $wpdb;
	$table1 = $wpdb->prefix . 'lemonade_autoposter_templates';
	$table2 = $wpdb->prefix . 'lemonade_autoposter_posts_published';
	$prepare = array();
	$query = "SELECT t1.id FROM $table2 as t1 INNER JOIN $table1 as t2 ON t1.template_id=t2.id WHERE t2.network='pinterest' AND t1.post_id=%d";
	$prepare[] = $post_id;
	if( !empty( $board_id ) ) {
		$query .= " AND t1.board_id=%s";
		$prepare[] = $board_id;
	}
	if( !empty( $templ_id ) ) {
		$query .= " AND t1.template_id=%d";
		$prepare[] = $templ_id;
	}
	if( !empty( $cycle ) ) {
		$query .= " AND t1.cycle_count=%d";
		$prepare[] = $cycle;
	}
	$result = $wpdb->get_row( $wpdb->prepare( $query, $prepare ) );
	if( null === $result ) {
		return false;
	} else {
		return $result->id;
	}
}

/**
 * Creates a line of posts for Autoposter.
 *
 * Creates a line of publications to post for a Lemonade SNA Pinterest template.
 * Saves it to 'lemonade_sna_pinterest_autoposter_lines' option.
 *
 * @since 1.0.0
 * @since 2.0.0 Some data validation and sanitization added.
 *
 * @see lemonade_sna_pinterest_get_autoposter_template()
 * @see lemonade_sna_pinterest_get_template_posts()
 * @see lemonade_sna_is_post_published_on_pinterest()
 * 
 * @param array $template_info Array with template information.
 * @param int $templ_id Optional. Template ID, if template info array is absent. Default empty.
 * @param bool $new_cycle Optional. To start a new cycle or not. Default false.
 * @return bool True if success, false otherwise.
 */
function lemonade_sna_pinterest_add_posts_to_line( $template_info = array(), $templ_id = '', $new_cycle = false ) {

	if( empty( $template_info ) ) { //Get template info and template ID, if absent
		if( empty( $templ_id ) ) {
			return false;
		} else {
			$templ_id = (int)$templ_id;
			$template = lemonade_sna_pinterest_get_autoposter_template( $templ_id );
			if( null === $template ) {
				return false;
			}
			$rules = json_decode( $template->rules );
		}
	} else {
		$rules = json_decode( $template_info['rules'] );
		$templ_id = (int)$template_info['id'];
	}
	
	$lines = get_option( 'lemonade_sna_pinterest_autoposter_lines' ); //Get old option value
	$cycle = !empty( $lines[$templ_id]['cycle'] ) ? (int)$lines[$templ_id]['cycle'] : 1; //Get a number of the current cycle
	$posts = lemonade_sna_pinterest_get_template_posts( $rules );	//Get posts for Autoposter template
	$image_default = !empty( $rules->image_default ) ? esc_url( $rules->image_default ) : '';
	$duplicate = !empty( $rules->duplicate ) ? sanitize_text_field( $rules->duplicate ) : 'yes'; //If a post was published already, should it be duplicated?
	
	$line = array();
	
	if( !empty( $posts ) && is_array( $posts ) ) {
		$boards = $rules->boards; //Get boards to which to post
		if( !empty( $boards ) && is_array( $boards ) ) {
			$sim = !empty( $rules->simultaneous ) ? $rules->simultaneous : ''; 

			foreach( $posts as $post ) {
				$post = (int)$post;
				foreach( $boards as $board ) {
					if( lemonade_sna_pinterest_numbers_only( $board ) ) {
						if( $duplicate == 'no' ) {
							$is_published = lemonade_sna_is_post_published_on_pinterest( $post, $board ); //Check if the post was already published on the board
							if( $is_published ) {
								continue; //Do not add to the line already published posts
							}
						}
						if( !$new_cycle ) {
							$is_published = lemonade_sna_is_post_published_on_pinterest( $post, $board, $templ_id, $cycle ); //Check if the post was already published on the board by the template during the current cycle
							if( $is_published ) {
								continue;
							}						
						}
						$has_feat_img = has_post_thumbnail( $post );
						if( $has_feat_img ) {
							$line[] = array( $post => $board ); //Add the post with the board to the line array
						} else {
							if( $image_default ) {
								$line[] = array( $post => $board );
							}
						}
					}
				}
			}
			
		}
	}
	
	//Add special information, like when (which days, time) to post
	$special = array();
	$special['time_zone'] = !empty( $rules->time_zone ) ? sanitize_text_field( $rules->time_zone ) : 'UTC';
	if( !empty( $rules->time ) ) {
		if( $rules->time->from == '00:00' && $rules->time->to == '00:00' ) {
		} else {
			if( strtotime( $rules->time->from ) ) {
				$special['time']['from'] = $rules->time->from;
			}
			if( strtotime( $rules->time->to ) ) {
				$special['time']['to'] = $rules->time->to;	
			}
		}		
	}	
	
	$when_post = !empty( $rules->when_post ) ? $rules->when_post : 'daily';
	
	if( $when_post == 'weekdays' ) {
		$weekdays = array_filter( $rules->special->weekdays );
		if( !empty( $weekdays ) ) {
			array_walk( $weekdays, function( &$item ) {
				$item = (int)$item;
			} );
			$special['weekdays'] = $weekdays;
		} else {
			$special['weekdays'] = array();
		}		
	} else if( $when_post == 'dates' ) {
		$special['dates'] = !empty( $rules->special->dates ) ? sanitize_text_field( $rules->special->dates ) : '';
	}
	
	$terminate = !empty( $rules->when_finished ) ? esc_attr( $rules->when_finished ) : 'wait_new'; //What to do if the line is empty
	
	$limit = array();
	
	if( !empty( $rules->posts_limit ) ) {
		$limit['limit'] = (int)$rules->posts_limit;
		$limit['posted'] = !empty( $lines[$templ_id]['limit']['posted'] ) ? $lines[$templ_id]['limit']['posted'] : array();
	}
	
	$sim = !empty( $rules->simultaneous ) ? sanitize_text_field( $rules->simultaneous ) : 'simultaneous';
	
	if( $new_cycle ) {
		$cycle = $cycle + 1;
	}
	
	$cur_datetime = lemonade_sna_pinterest_get_date_or_time_with_timezone( 0, 'Y-m-d H:i:s', 'UTC' );
	
	$lines[$templ_id] = array();	
	$lines[$templ_id]['line'] = $line;
	$lines[$templ_id]['simultaneous'] = $sim;
	if( !empty( $special ) ) { $lines[$templ_id]['special'] = $special; }
	$lines[$templ_id]['image_default'] = $image_default;
	$lines[$templ_id]['duplicate'] = $duplicate;
	if( !empty( $limit ) ) { $lines[$templ_id]['limit'] = $limit; }
	$lines[$templ_id]['terminate'] = $terminate;
	$lines[$templ_id]['cycle'] = $cycle;
	$lines[$templ_id]['updated'] = $cur_datetime;

	/**
	 * Filters lines array before saving.
	 *
	 * @since 1.0.0
	 *
	 * @param array $lines Lines to save.	 
	 * @param int $templ_id Autoposter template's ID.
	 */
	$lines = apply_filters( 'lemonade_sna_pinterest_line_array', $lines, $templ_id );
	
	$option_updated = update_option( 'lemonade_sna_pinterest_autoposter_lines', $lines );

	/**
	 * Fires after new lines array was saved.
	 *
	 * @since 1.0.0
	 * 
	 * @param int $templ_id Autoposter template's ID.	 
	 * @param array $lines Saved lines array.
	 */
	do_action( 'lemonade_sna_pinterest_after_autoposter_line_saving', $templ_id, $lines );

	return $option_updated;

}

?>