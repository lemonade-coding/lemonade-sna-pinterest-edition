<?php
/**
 * Plugin functions
 *
 * @package LemonadeSNAPinterest
 * @since 1.0.0
 */

////////////////
//DB FUNCTIONS//
////////////////

/**
 * Adds column into existing DB.
 *
 * Checks if column is not existing and adds then it into a DB.
 *
 * @since 2.0.0
 * @global object $wpdb WordPress Database object.
 *
 * @param string $column_name A name for column to add.
 * @param string $db A DB table name.
 * @param string $column_attr The colum attributes.
 * @return bool False if nothing has changed, true if the column has been added.
 */
function lemonade_sna_pinterest_add_column_to_db( $column_name, $db, $column_attr ) {

	global $wpdb;
	
	$result = false;
	
	$column_name = esc_sql( $column_name );
	$db = esc_sql( $db );
	$column_attr = esc_sql( $column_attr );
	
	$existing_columns = $wpdb->get_col( "show columns from `$db`" );
	
	if( is_array( $existing_columns ) and !empty( $existing_columns ) ) {
		foreach( $existing_columns as $col ) {
			if( $col == $column_name ) {
				return false; //Stop if the column name exists
			}
		}
	}
	
	$result = $wpdb->query( "ALTER TABLE `$db` ADD `$column_name` $column_attr" );

	return $result; 
	
} 
 
//////////////////// 
//HELPER FUNCTIONS//
////////////////////

/**
 * Validates a string from numbers only.
 *
 * Checks if a string contains digits only.
 *
 * @since 2.0.0
 *
 * @param string $num String to validate.
 * @return bool If valid returns true. Otherwise false.
 */
function lemonade_sna_pinterest_numbers_only( $num ) {
	return preg_match( '/^([0-9])+$/', $num );
}

/**
 * Validates a string from digits and letters.
 *
 * Checks if a string contains digits and letters only.
 *
 * @since 2.0.0
 *
 * @param sring $str String to validate.
 * @return bool If valid returns true. Otherwise false. 
 */
function lemonade_sna_pinterest_numbers_letters_only( $str ) {
	return preg_match( '/^[a-zA-Z\d]+$/', $str );
}

/**
 * Validates time interval.
 *
 * Checks if a given time interval has a correct value: 'days' is a positive integer, 
 * 'hours' is a positive integer not more than 24, etc.
 *
 * @since 2.0.0
 *
 * @param string $type Type of the interval: 'days', 'hours', 'minutes', 'limit'.
 * @param int $value Value of the interval.
 * @return bool True if valid, false if invalid.
 */
function lemonade_sna_pinterest_validate_interval( $type, $value ) {

	$valid = false;
	
	switch( $type ) {
		case 'days' :
		case 'limit' :
			$valid = (int)$value > 0 ? true : false;
			break;
		case 'hours' :
			$valid = ( (int)$value > 0 && (int)$value <= 24 ) ? true : false;
			break;
		case 'minutes' :
			$valid = ( (int)$value > 0 && (int)$value <= 60 ) ? true : false;
			break;
	}
	
	return $valid;

}

/**
 * Checks Internet connection.
 *
 * Checks if it is possible to connect to a host.
 *
 * @since 2.0.0
 * 
 * @param string $host Host url (without http://) for test.
 * @return bool True if connected, false if not connected.
 */
function lemonade_sna_pinterest_check_internet_connection( $host ) {
    return ( bool ) @fsockopen( $host, 80, $i, $s, 5 );
}

/**
 * Gets array of weekdays with names.
 *
 * Gets array of weekdays names numbered from 1 (Monday) to 7 (Sunday).
 *
 * @since 2.0.0
 *
 * @return array Weekdays with serial numbers and names.
 */
function lemonade_sna_pinterest_days_of_week() {

	$days = array(
		'1' => 'Monday',
		'2' => 'Tuesday',
		'3' => 'Wednesday',
		'4' => 'Thursday',
		'5' => 'Friday',
		'6' => 'Saturday',
		'0' => 'Sunday'
	);
	
	return $days;
	
}

///////////////////////////
//DATE AND TIME FUNCTIONS//
///////////////////////////

/**
 * Converts given datetime.
 *
 * Converts given datetime into given format for given timezone. 
 *
 * @since 2.0.0
 *
 * @link http://php.net/manual/en/datetime.formats.php
 * @link http://php.net/manual/en/function.date.php
 * @link http://php.net/manual/en/timezones.php 
 *
 * @param string|int $datetime Datetime in the valid format. Default 0.
 * @param string $format Datetime format for the output. Example: 'Y-m-d'. Default 'Y-m-d H:i:s'.
 * @param string $tz Timezone formatted like like 'Africa/Abidjan' or '-11:30'. Default 'UTC'.
 */

function lemonade_sna_pinterest_get_date_or_time_with_timezone( $datetime = 0, $format = 'Y-m-d H:i:s', $tz = 'UTC' ) {

	if( empty( $datetime ) ) { //Get datetime object
		$str_datetime = new DateTime();
	} else {
		try {
			$str_datetime = new DateTime( $datetime );
		} catch( Exception $e ) {
			return false;		
		} 
	}
	
	if( !empty( $tz ) ) {
		try {
			$c_tz = new DateTimeZone( $tz );
			$str_datetime->setTimeZone( $c_tz );
		} catch( Exception $e ) {
			if( empty( $datetime ) ) {
				$result = time() + $tz * 3600;
			} else {
				$result = strtotime( $datetime ) + $tz * 3600;
			}
			return @date( $format, $result );
		} 	
	}
	
	return $str_datetime->format( $format );

} 

//////////////
//SHORTCODES//
//////////////

/**
 * Gets available shortcodes for posts templates.
 *
 * Gets array of shortcodes, which can be used in posts templates, and its descriptions. 
 *
 * @since 2.0.0
 *
 * @param string $special Optional. Allows to get recommended shortcodes for special template fields. Possible values are: 'link', 'image_url', 'og_url', 'og_title', 'og_site_name', 'og_image', 'article_author', 'article_published_time'.
 * @return array Array of shortcodes.
 */
function lemonade_sna_pinterest_built_in_shortcodes( $special = '' ) {

	//Array of built-in shortcodes
	$shortcodes = array(
		'url' => array( 'desc' => __( 'Url of the post.', 'lemonade_sna' ), 'special' => array( 'link', 'og_url' ) ),
		'slug' => array( 'desc' => __( 'Slug of the posts URL.', 'lemonade_sna' ), 'special' => array( 'link', 'og_url' ) ),
		'pid'=> array( 'desc' => __( 'Post ID.', 'lemonade_sna' ) ),
		'title' => array( 'desc' => __( 'Post title.', 'lemonade_sna' ), 'special' => array( 'og_title' ) ),
		'content' => array( 'desc' => __( 'Processed post content.', 'lemonade_sna' ) ),
		'announce' => array( 'desc' => __( 'Processed content of the post till the <!–more–> tag. If the tag is missed, than cuts after the last word in a segment limited by amount of symbols, which you can set up.', 'lemonade_sna' ) ),
		'excerpt' => array( 'desc' => __( 'Processed excerpt of the post.', 'lemonade_sna' ) ),
		'featured_img' => array( 'desc' => __( 'Url to featured image.', 'lemonade_sna' ), 'special' => array( 'image_url', 'og_image' ) ),
		'tags' => array( 'desc' => __( 'Tags of the post.', 'lemonade_sna' ) ),
		'categories' => array( 'desc' => __( 'Categories of the post.', 'lemonade_sna' ) ),
		'author_display' => array( 'desc' => __( 'Post author display name.', 'lemonade_sna' ), 'special' => array( 'article_author' ) ),
		'author_login' => array( 'desc' => __( 'Post author login.', 'lemonade_sna' ), 'special' => array( 'article_author' ) ),
		'author_email' => array( 'desc' => __( 'Post author email.', 'lemonade_sna' ) ),
		'post_date' => array( 'desc' => __( 'Date when the post was published converted to the site local timezone.', 'lemonade_sna' ), 'special' => array( 'article_published_time' ) ),
		'post_date_gmt' => array( 'desc' => __( 'Date when the post was published converted to GMT timezone.', 'lemonade_sna' ), 'special' => array( 'article_published_time' ) ),
		'post_modified' => array( 'desc' => __( 'Date when the post was last modified converted to the site local timezone.', 'lemonade_sna' ), 'special' => array( 'article_published_time' ) ),
		'post_modified_gmt' => array( 'desc' => __( 'Date when the post was last modified converted to GMT timezone.', 'lemonade_sna' ), 'special' => array( 'article_published_time' ) ),
		'post_type' => array( 'desc' => __( 'Post type.', 'lemonade_sna' ) ),
		'post_mime_type' => array( 'desc' => __( 'Post mime type.', 'lemonade_sna' ) ),
		'comment_count' => array( 'desc' => __( 'Number of comments.', 'lemonade_sna' ) ),
		'site_name' => array( 'desc' => __( 'Site name.', 'lemonade_sna' ), 'special' => array( 'og_site_name' ) ),
		'site_url' => array( 'desc' => __( 'Site url.', 'lemonade_sna' ), 'special' => array( 'link', 'og_url' ) ),
		'site_desc' => array( 'desc' => __( 'Site description.', 'lemonade_sna' ) ),
	);
	
	if( $special != '' ) { //Select shortcodes which satisfies $special parameter
		foreach( $shortcodes as $key => $shortcode ) {
			if( !empty( $shortcode['special'] ) && in_array( $special, $shortcode['special'] ) ) {
				$shortcodes_to_return[$key] = $shortcode;
			}
		}
	} else {
		$shortcodes_to_return = $shortcodes;
	}
	
	return $shortcodes_to_return;

}

/**
 * Interprets shortcodes.
 *
 * For each post places a corresponding string instead of a given shortcode.
 *
 * @since 2.0.0
 *
 * @param string $shortcode A shortcode for interpretation.
 * @param int $post_id Post ID.
 * @param string $sep Optional. Separator between tags, categories, etc., if there are more than one item in a list. Default ' '.
 * @param int $limit Optional. Limit of symbols for interpretation of '%announce%' shortcode. Default 200.
 * @param bool $split Optional. If it is true, then for shortcodes with the '#' at the beginning, like '#%tags%', for each item which contains more than one word before each word the '#' hashtag will be added. Otherwise all the words will be glued together, like '#MySuperHasgtag'. Default true.
 * @return string Interpreted shortcode or empty value if Post ID is incorrect.
 */
function lemonade_sna_pinterest_shortcodes_interp( $shortcode, $post_id, $sep = ' ', $limit = 200, $split = true ) {

	//Validate function params
	$post_id = (int)$post_id;
	$sep = esc_attr( $sep );
	$limit = (int)$limit;
	$split = (bool)$split;

	$post = get_post( $post_id ); //Get post object
	if( null === $post ){
		return '';
	}
	
	if( empty( $sep ) ) {
		$sep = ' ';
	}
	
	$return = ''; 
		
	switch( $shortcode ) {
		case '%url%' :
			$return = get_permalink( $post_id );
			if( false === $return ) {
				$return = '';
			}
			break;
		case '%slug%' :
			$return = sanitize_title( $post->post_name );
			break;
		case '%pid%' :
			$return = (int)$post_id;
			break;
		case '#%title%' :
			$title = $post->post_title;
			if( !empty( $title ) ) {
				if( str_word_count( $title ) > 1 ) { //If a title contains more than one word
					$title = str_word_count( $title, 1 );				
					foreach( $title as $word ) {
						if( $split === true ) {
							$return .= ' #' . $word;
						} else {
							$return .= ucfirst( $word );
						}
					}
					if( strstr( $return, '#' ) === false ) {
						$return = '#' . $return;
					}					
					$return = trim( $return );
				} else {
					$return = '#' . $title ;
				}
			}
			break;			
		case '%title%' :
			$return = $post->post_title;
			break;
		case '#%content%' :
			$content = strip_shortcodes( wp_strip_all_tags( $post->post_content ) ); //Strip all shortcodes and tags from the content
			$content = preg_replace( '!\s+!', " ", preg_replace( '/\r|\n/', " ", str_replace( ']]>', ']]&gt;', $content ) ) ); //Replace all line breaks, multi spaces and CDATA close tags
			if( !empty( $content ) ) {
				if( str_word_count( $content ) > 1 ) {
					$content = str_word_count( $content, 1 );
					foreach( $content as $word ) {
						if( $split === true ) {
							$return .= ' #' . $word;
						} else {
							$return .= ucfirst( $word ); //Create one hashtag like '#MySuperLongHashtag' from all the content
						}
					}
					if( strstr( $return, '#' ) === false ) {
						$return = '#' . $return;
					}					
					$return = trim( $return );
				} else {
					$return = '#' . $content;
				}
			}
			break;			
		case '%content%' :
			$content = strip_shortcodes( wp_strip_all_tags( $post->post_content ) );
			$return = preg_replace( '!\s+!', " ", preg_replace( '/\r|\n/', " ", str_replace( ']]>', ']]&gt;', $content ) ) );
			break;	
		case '#%announce%' :
			$content = $post->post_content;
			if( !empty( $content ) ) {
				if ( preg_match( '/<!--more(.*?)?-->/', $content, $matches ) ) { //Check if there is <<!--more-->> tag
					$content = explode( $matches[0], $content, 2 );
					$return = preg_replace( '!\s+!', " ", preg_replace( '/\r|\n/', " ", str_replace( ']]>', ']]&gt;', strip_shortcodes( wp_strip_all_tags( $content[0] ) ) ) ) ); //Process the content before the More tag
				} else {
					$content = preg_replace( '!\s+!', " ", preg_replace( '/\r|\n/', " ", str_replace( ']]>', ']]&gt;', strip_shortcodes( wp_strip_all_tags( $content ) ) ) ) ); //Process the content
					$str_len = strlen( $content ); 
					if( $str_len <= $limit ) { //If a length of the content is less then setted up with $limit parameter
						$return = $content;
					} else {
						$limit = $limit - 1;	
						$where_cut = strrpos( $content, ' ', $limit - $str_len ); //Find a position after the last word in the segment of symbols limited with $limit parameter
						if( $where_cut !== false ) {
							$return = substr( $content, 0, $where_cut ) . '...';
						} else {
							$return = substr( $content, 0, $limit ) . '...';
						}
					}
				}
			}	
			if( !empty( $return ) ) {
				if( str_word_count( $return ) > 1 ) { //If the processed announce contains more than 1 word
					$words = str_word_count( $return, 1 ); 
					$string = '';
					foreach( $words as $word ) {
						if( $split === true ) {
							$string .= ' #' . $word;
						} else {
							$string .= ucfirst( $word );
						}
					}
					if( false === strpos( $string, '#' ) ) {
						$string = '#' . $string;
					}						
					$return = trim( $string );
				} else {
					$return = '#' . $return;
				}
			}	
			break;			
		case '%announce%' :
			$content = $post->post_content;
			$return = '';
			if( !empty( $content ) ) {
				if ( preg_match( '/<!--more(.*?)?-->/', $content, $matches ) ) {
					$content = explode( $matches[0], $content, 2 );
					$return = preg_replace( '!\s+!', " ", preg_replace( '/\r|\n/', " ", str_replace( ']]>', ']]&gt;', strip_shortcodes( wp_strip_all_tags( $content[0] ) ) ) ) );
				} else {
					$content = preg_replace( '!\s+!', " ", preg_replace( '/\r|\n/', " ", str_replace( ']]>', ']]&gt;', strip_shortcodes( wp_strip_all_tags( $content ) ) ) ) );
					$str_len = strlen( $content );
					if( $str_len < $limit ) {
						$return = $content;
					} else {
						$limit = $limit - 1;					
						$where_cut = strrpos( $content, ' ', $limit - $str_len );
						if( $where_cut !== false ) {
							$return = substr( $content, 0, $where_cut ) . '...';
						} else {
							$return = substr( $content, 0, $limit ) . '...';
						}
					}
				}
			}
			break;
		case '#%excerpt%' :
			$excerpt = $post->post_excerpt;
			if( !empty( $excerpt ) ) {
				if( str_word_count( $excerpt ) > 1 ) {
					$excerpt = str_word_count( $excerpt, 1 );
					foreach( $excerpt as $word ) {
						if( $split === true ) {
							$return .= ' #' . $word;
						} else {
							$return .= ucfirst( $word );
						}
					}
					if( strstr( $return, '#' ) === false ) {
						$return = '#' . $return;
					}					
					$return = trim( $return );
				} else {
					$return = '#' . $excerpt;
				}
			}
			break;			
		case '%excerpt%' :
			$return = get_the_excerpt( $post );
			break;
		case '%featured_img%' :
			$return = get_the_post_thumbnail_url( $post );
			if( !$return ) {
				$return = '';
			}
			break;
		case '#%tags%' :
			$tags = wp_get_post_tags( $post_id, array( 'fields' => 'names' ) );
			if( !empty( $tags ) ) {
				array_walk(
					$tags,
					function( &$i, $key, $split ) {
						if( str_word_count( $i ) > 1 ) {
							$tr = str_word_count( $i, 1 );
							$str = '';
							foreach( $tr as $k => $t ) {
								if( $split === true ) {
									$str .= ' #' . $t;
								} else {
									$str .= ucfirst( $t );		
								}
							}
							if( false === strpos( $str, '#' ) ) {								
								$str = '#' . $str;
							}							
							$i = trim( $str );
						} else {
							$i = '#' . $i;
						}
					},
					$split
				);
				$return = trim( implode( $sep, $tags ), $sep );
			}		
			break;			
		case '%tags%' :
			$tags = wp_get_post_tags( $post_id, array( 'fields' => 'names' ) );
			if( !empty( $tags ) ) {
				array_walk(
					$tags,
					function( &$i, $key, $split ) {
						if( str_word_count( $i ) > 1 ) {
							$tr = str_word_count( $i, 1 );
							$str = '';
							foreach( $tr as $k => $t ) {
								if( $split === true ) {
									$str .= ' ' . $t;
								} else {
									$str .= ucfirst( $t );								
								}
							}							
							$i = trim( $str );
						}
					},
					$split
				);			
				$return = trim( implode( $sep, $tags ), $sep );
			}
			break;
		case '#%categories%' :
			$categories = wp_get_post_categories( $post_id, array( 'fields' => 'names' ) );
			if( !empty( $categories ) ) {
				array_walk( $categories, 
					function( &$i, $key, $split ) { 
						if( str_word_count( $i ) > 1 ) {
							$tr = str_word_count( $i, 1 );
							$str = '';
							foreach( $tr as $t ) {
								if( $split === true ) {
									$str .= ' #' . $t;
								} else {
									$str .= ucfirst( $t );
								}
							}
							if( false === strpos( $str, '#' ) ) {
								$str = '#' . $str;
							}								
							$i = trim( $str );
						} else {
							$i = '#' . $i;
						}
					},
					$split	
				);
				$return = trim( implode( $sep, $categories ), $sep );
			}		
			break;			
		case '%categories%' :
			$categories = wp_get_post_categories( $post_id, array( 'fields' => 'names' ) );
			if( !empty( $categories ) ) {
				array_walk( $categories, 
					function( &$i, $key, $split ) { 
						if( str_word_count( $i ) > 1 ) {
							$tr = str_word_count( $i, 1 );
							$str = '';
							foreach( $tr as $k => $t ) {
								if( $split === true ) {
									$str .= ' ' . $t;
								} else {
									$str .= ucfirst( $t );
								}
							}								
							$i = trim( $str );
						}
					},
					$split	
				);				
				$return = trim( implode( $sep, $categories ), $sep );
			}		
			break;	
		case '#%author_display%' :
			$author_id = $post->post_author;
			$author = get_the_author_meta( 'display_name', $author_id );	
			if( !empty( $author ) ) {
				if( str_word_count( $author ) > 1 ) {
					$author = str_word_count( $author, 1 );
					foreach( $author as $word ) {
						if( $split === true ) {
							$return .= ' #' . $word;
						} else {
							$return .= ucfirst( $word );
						}
					}
					if( strstr( $return, '#' ) === false ) {
						$return = '#' . $return;
					}					
					$return = trim( $return );
				} else {
					$return = '#' . $author;
				}
			}			
			break;			
		case '%author_display%' :
			$author_id = $post->post_author;
			$return = get_the_author_meta( 'display_name', $author_id );
			if( !empty( $author ) ) {
				if( str_word_count( $author ) > 1 ) {
					$author = str_word_count( $author, 1 );
					foreach( $author as $word ) {
						if( $split === true ) {
							$return .= ' ' . $word;
						} else {
							$return .= ucfirst( $word );
						}
					}				
					$return = trim( $return );
				}
			}				
			break;
		case '%author_login%' :
			$author_id = $post->post_author;
			$return = get_the_author_meta( 'user_login', $author_id );
			break;
		case '%author_email%' :
			$author_id = $post->post_author;
			$return = get_the_author_meta( 'user_email', $author_id );
			break;	
		case '%post_date%' :
			$date = date( 'Y-m-d', strtotime( $post->post_date ) );
			
			/**
			 * Filters date output.
			 *
			 * Filters a returned by a shortcode date output. 
			 *
			 * @since 2.0.0
			 *
			 * @param string $date Date string formatted '0000-00-00'.
			 */			 
			$return  = apply_filters( 'lemonade_sna_pinterest_shortcode_date_output', $date );
			break;
		case '%post_date_gmt%' :
			$date = date( 'Y-m-d', strtotime( $post->post_date_gmt ) );
			
			/** This filter is documented in lemonade_sna_core_pinterest_functions.php */
			
			$return  = apply_filters( 'lemonade_sna_pinterest_shortcode_date_output', $date );
			break;
		case '%post_modified%' :
			if( !empty( $post->post_modified ) ) {
				$date = date( 'Y-m-d', strtotime( $post->post_modified ) );
				
				/** This filter is documented in lemonade_sna_core_pinterest_functions.php */
				
				$return  = apply_filters( 'lemonade_sna_pinterest_shortcode_date_output', $date );			
			}
			break;
		case '%post_modified_gmt%' :
			if( !empty( $post->post_modified_gmt ) ) {
				$date = date( 'Y-m-d', strtotime( $post->post_modified_gmt ) );
				
				/** This filter is documented in lemonade_sna_core_pinterest_functions.php */
				
				$return  = apply_filters( 'lemonade_sna_pinterest_shortcode_date_output', $date );						
			}
			break;
		case '%post_type%' :
			$post_type = get_post_type_object( $post->post_type );
			if( null !== $post_type ) {
				$return = $post_type->labels->singular_name;
			}
			break;
		case '%post_mime_type%' :
			$return = !empty( $post->post_mime_type ) ? $post->post_mime_type : '';
			break;
		case '%comment_count%' :
			$return = !empty( $post->comment_count ) ? $post->comment_count : 0;
			break;
		case '%site_url%' :
			$return = get_site_url();
			break;
		case '#%site_desc%' :
			$desc = get_bloginfo( 'description' );
			if( !empty( $desc ) ) {
				if( str_word_count( $desc ) > 1 ) {
					$desc = str_word_count( $desc, 1 );
					foreach( $desc as $word ) {
						if( $split === true ){
							$return .= ' #' . $word;
						} else {
							$return .= ucfirst( $word );						
						}
					}
					if( strstr( $return, '#' ) === false ) {
						$return = '#' . $return;
					}						
					$return = trim( $return );
				} else {
					$return = '#' . $desc;
				}
			}
			break;		
		case '%site_desc%' :
			$return = get_bloginfo( 'description' );
			break;
		case '#%site_name%' :
			$name = get_bloginfo( 'name' );
			if( !empty( $name ) ) {
				if( str_word_count( $name ) > 1 ) {
					$name = str_word_count( $name, 1 );
					foreach( $name as $word ) {
						if( $split === true ){
							$return .= ' #' . $word;
						} else {
							$return .= ucfirst( $word );						
						}
					}
					if( strstr( $return, '#' ) === false ) {
						$return = '#' . $return;
					}						
					$return = trim( $return );
				} else {
					$return = '#' . $name;
				}
			}			
			break;
		case '%site_name%' :
			$return = get_bloginfo( 'name' );
			break;
		default :
			$return = $shortcode;
			break;
	}
	
	$return = esc_html( $return ); //Add sanitization for output
	
	/**
	 * Fires after shortcode was interpreted.
	 *
	 * @since 2.0.0
	 *
	 * @param string $shortcode Shortcode.
	 * @param int $post_id Post ID.
	 * @param string $sep Separator between shorcodes replacements, like post tags, which contain more than one item.
	 * @param int $limit Limit of announce (ammount of symbols).
	 * @param bool $split If it is true, then for shortcodes with the '#' at the beginning, like '#%tags%', for each item which contains more than one word before each word the '#' hashtag will be added. Otherwise all the words will be glued together, like '#MySuperHasgtag'.
	 * @param string $return Interpreted shortcode.
	 */		
	do_action( 'lemonade_sna_pinterest_after_shortcode_interpretation', $shortcode, $post_id, $sep, $limit, $split, $return );

	/**
	 * Filters interpreted shortcode.
	 *
	 * Filters the result of shortcode interpretation.
	 *
	 * @since 2.0.0
	 * 
	 * @param string $return Interpreted shortcode.		
	 * @param string $shortcode Shortcode.
	 * @param int $post_id Post ID.
	 * @param string $sep Separator between shorcodes replacements, like post tags, which contain more than one item.
	 * @param int $limit Limit of announce (ammount of symbols).
	 * @param bool $split If it is true, then for shortcodes with the '#' at the beginning, like '#%tags%', for each item which contains more than one word before each word the '#' hashtag will be added. Otherwise all the words will be glued together, like '#MySuperHasgtag'.
	 */		
	apply_filters( 'lemonade_sna_pinterest_filter_shortcode_interpretation', $return, $shortcode, $post_id, $sep, $limit, $split );
	
	return $return;

}

/**
 * Replaces shotcodes in a given string.
 *
 * Changes all known shortcodes in a string to their interpretation.
 *
 * @since 2.0.0
 *
 * @param string $string A string with shortcodes.
 * @param int $post_id Post ID.
 * @param string $sep Optional. Separator between tags, categories, etc., if there are more than one item in a list. Default ' '.
 * @param int $limit Optional. Limit of symbols for interpretation of '%announce%' shortcode. Default 200.
 * @param bool $split Optional. If it is true, then for shortcodes with the '#' at the beginning, like '#%tags%', for each item which contains more than one word before each word the '#' hashtag will be added. Otherwise all the words will be glued together, like '#MySuperHasgtag'. Default true.
 * @return string The string with replaced shortcodes.
 */
function lemonade_sna_pinterest_shortcodes( $string, $post_id, $sep = ' ', $limit = 200, $split = true ) {	
	
	$search = array();
	$search2 = array();
	$replace = array();

	$search[] = '#%title%';
	$search[] = '#%content%';
	$search[] = '#%announce%';
	$search[] = '#%excerpt%';
	$search[] = '#%site_desc%';
	$search[] = '#%author_display%';	
	foreach( array_keys( lemonade_sna_pinterest_built_in_shortcodes() ) as $item ) {
		$search[] = '#%' . $item . '%';
		$search[] = '%' . $item . '%';	
	}
	
 	foreach( $search as $to_replace ) {

		if( stripos( $string, $to_replace ) !== false ) {
			$search2[] = $to_replace;
			$replace[] = lemonade_sna_pinterest_shortcodes_interp( $to_replace, $post_id, $sep, $limit, $split );
		}		

	}

	return str_replace( $search2, $replace, $string );

}

//////////////////////
//TEMPLATE FUNCTIONS//
//////////////////////

/**
 * Gets an Lemonade SNA Autoposter template.
 *
 * Gets an Lemonade SNA Autoposter template for a given ID.
 *
 * @since 2.0.0
 *
 * @global object $wpdb WPDB object.
 *
 * @param int $id Template ID.
 * @return object|null Autoposter template object from DB or null if nothing found.
 */
function lemonade_sna_pinterest_get_autoposter_template( $id ) {

	global $wpdb;
	
	$table = $wpdb->prefix . 'lemonade_autoposter_templates';
	
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE `id`=%d", $id ) );

}

/**
 * Gets a list of Autoposter templates.
 *
 * Gets a list of templates from the DB, for a special network, if setted up.
 * 
 * @since 2.0.0
 *
 * @global object $wpdb WPDB object.
 *
 * @param string $network Optional. A network name. Available: 'Pinterest'.
 * @return array|null Array of templates or null if MySql query was incorrect. 
 */
function lemonade_sna_pinterest_get_list_of_templates( $network = '' ) {

	global $wpdb;
	
	$table = $wpdb->prefix . 'lemonade_autoposter_templates';
	
	$select = "SELECT * FROM $table";
	
	if( !empty($network ) ) {
		$select .= " WHERE network=%s";
		$templates = $wpdb->get_results( $wpdb->prepare( $select, $network ) );
	} else {
		$templates = $wpdb->get_results( $select );
	}
	
	return $templates;

}

/**
 * Gets a list of active templates.
 *
 * Gets templates which are active at the moment from the DB. 
 *
 * @since 2.0.0
 *
 * @global object $wpdb WPDB object.
 *
 * @param string $network Optional. A network for which to check. Available: 'Pinterest'.
 * @return array|null Array of active templates or null if MySql query is incorrect.
 */
function lemonade_sna_pinterest_get_list_of_active_templates( $network = '' ) {

	global $wpdb;
	
	$table = $wpdb->prefix . 'lemonade_autoposter_templates';
	
	$select = "SELECT * FROM $table WHERE is_active=%d";
	
	$prepare[] = 1;
	
	if( !empty( $network ) ) {
		$select .= " AND network=%s";
		$prepare[] = $network;
	}
	
	$templates = $wpdb->get_results( $wpdb->prepare( $select, $prepare ) );
	
	return $templates;

}

/**
 * Gets a list of inactive templates.
 *
 * Selects from the DB templates which are not active at the moment.
 *
 * @since 2.0.0
 *
 * @global object $wpdb WPDB object.
 *
 * @param string $network A social network. Available: 'Pinterest'.
 * @return array|null Array of inactive templates entries or null if MySql query is incorrect
 */
function lemonade_sna_pinterest_get_list_of_unactive_templates( $network = '' ) {

	global $wpdb;
	
	$table = $wpdb->prefix . 'lemonade_autoposter_templates';
	
	$query = "SELECT * FROM $table WHERE NOT `is_active`=1";
	
	if( empty( $network ) ) {	
		$templates = $wpdb->get_results( $query );
	} else {
		$query .= " AND network=%s";
		$templates = $wpdb->get_results( $wpdb->prepare( $query, $network ) );
	}
	
	return $templates;

}

/**
 * Deletes template.
 *
 * Deletes a template with given ID.
 *
 * @since 2.0.0
 *
 * @global object $wpdb WPDB object.
 *
 * @param int $templ_id ID of a template to delete.
 * @return bool True if deleted or false if an error occured.
 */
function lemonade_sna_pinterest_delete_template( $templ_id ) {
	
	global $wpdb;
	
	$lines = get_option( 'lemonade_sna_pinterest_autoposter_lines' );
	
	if( !empty( $lines[$templ_id] ) ) {
		unset( $lines[$templ_id] );
		update_option( 'lemonade_sna_pinterest_autoposter_lines', $lines );
	}
	
	$posted_table = $wpdb->prefix . 'lemonade_autoposter_posts_published';
	$posted = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM $posted_table WHERE template_id=%d", $templ_id ) );
	$update = true;
	
	if( NULL !== $posted && !empty( $posted ) ) {
		$update = $wpdb->update(
			$posted_table,
			array(
				'template_id' => 0
			),
			array(
				'template_id' => $templ_id
			),
			array(
				'%d'
			),
			array(
				'%d'
			)
		);
	}
	
	if( false !== $update ) {	
		$templates_table = $wpdb->prefix . 'lemonade_autoposter_templates';
		$template = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM $templates_table WHERE id=%d", $templ_id ) );
		if( NULL === $template ) {
			return false;
		}
		$delete = $wpdb->delete(
			$templates_table,
			array(
				'id' => $templ_id
			),
			array(
				'%d'
			)
		);
		if( false === $delete ) {
			return false;
		}
	} else {
		return false;
	}
	
	/**
	 * Fires after Autoposter template was deleted from the Data Base.
	 *
	 * @since 2.0.0
	 *
	 * @param int $templ_id ID of the template which was deleted.
	 */	
	do_action( 'lemonade_sna_pinterest_after_autoposter_template_deleted', $templ_id );
	
	return true;
	
}

/**
 * Gets posts for an Autoposter template.
 *
 * Gets ids of all the posts which correspond to special rules setted up in an Autoposter template.
 *
 * @since 2.0.0
 *
 * @param object $rules Object of template rules.
 * @return array Array of posts ids.
 */
function lemonade_sna_pinterest_get_template_posts( $rules ) {
	
	$query = array();
	
	$post_types_def = get_post_types( array( 'public' => true ), 'objects' ); //Get all public post types
	foreach( $post_types_def as $post_type => $v ) {
		$post_types_def_keys[] = $post_type; //Get keys of post types
	}	
	
	if( empty( $rules->post_types->selected ) ) { //If post types are not setted up by the template, get all post types
			
		$query['post_type'] = $post_types_def_keys;
		
	} else {
			
			foreach( $rules->post_types->selected as $k => $post_type ) {
				if( !empty( $post_type ) ) {
					$query['post_type'][] = sanitize_text_field( $post_type );
				}				
			}
			
			if( !isset( $rules->post_types->include ) ) { //If we want to exclude somepost types		
				$query['post_type'] = array_diff( $post_types_def_keys, $query['post_type'] ); //Get all post types which are not for to exclude
			}
			
	}
	
	if( !empty( $rules->post_formats ) ) { //Check posts formats
		
		if( !empty( $rules->post_formats->selected ) ) {
			
			$post_format_slugs = array();
			
			foreach( $rules->post_formats->selected as $k => $post_format ) {
				if( !empty( $post_format ) ) {
					$post_format_slugs[] = 'post-format-' . sanitize_text_field( $post_format );
				}
			}
			
			if( isset( $rules->post_formats->include ) ) {
				$operator = 'IN';
			} else {
				$operator = 'NOT IN';			
			}
			
			$query['tax_query'][0] = array(
				array(
					'taxonomy' => 'post_format',
					'field' => 'slug',
					'terms' => $post_format_slugs,
					'operator' => $operator,
					'include_children' => false
				)
			);
			
			$query['tax_query']['relation'] = 'AND';			
		
		}
		
	}
	
	if( !empty( $rules->post_cats ) ) { //Check posts relation to categories
		
		if( !empty( $rules->post_cats->selected ) ) {
			
			if( isset( $rules->post_cats->include ) ) {
				if( !empty( $rules->post_cats->relation ) && $rules->post_cats->relation == 'and' ) {
					$operator = 'category__and';
				} else {
					$operator = 'category__in';
				}
			} else {
				$operator = 'category__not_in';
			}
			
			foreach( $rules->post_cats->selected as $k => $post_cat ) {
				if( !empty( $post_cat ) ) {
					$query[$operator][] = (int)$post_cat;
				}
			}
			
		}
		
	}
	
	if( !empty( $rules->post_tags ) ) { //Check posts relation to tags
	
		if( !empty( $rules->post_tags->selected ) ) {
			
			if( isset( $rules->post_tags->include ) ) {
				if( !empty( $rules->post_tags->relation ) && $rules->post_tags->relation == 'and' ) {
					$operator = 'tag__and';
				} else {
					$operator = 'tag__in';
				}
			} else {
				$operator = 'tag__not_in';
			}
			
			foreach( $rules->post_tags->selected as $post_tag ) {
			
				if( !empty( $post_tag ) ) {
					$query[$operator][] = (int)$post_tag;
				}
			
			}
			
		}
	
	}
	
	if( !empty( $rules->post_authors ) ) { //Check posts relation to authors
	
		if( !empty( $rules->post_authors->selected ) ) {
		
			if( isset( $rules->post_authors->include ) ) {
				$operator = 'author__in';
			} else {
				$operator = 'author__not_in';			
			}
			
			foreach( $rules->post_authors->selected as $post_author ) {
				
				if( !empty( $post_author ) ) {
					$query[$operator][] = (int)$post_author;
				}
				
			}
		
		}
	
	}
	
	if( !empty( $rules->dates_filter ) ) { //Take posts which were published in-between these dates
	
		if( !empty( $rules->dates_filter->from ) ) {
			$after = date( 'Y-m-d', strtotime( $rules->dates_filter->from ) );
			if( $after ) {
				$query['date_query'][0][0]['after'] = $after;
			}
		}
		
		if( !empty( $rules->dates_filter->to ) ) {
			$before = date( 'Y-m-d', strtotime( $rules->dates_filter->to ) );
			if( $before ) {
				$query['date_query'][0][0]['before'] = $before;
			}
		}
		
		if( !empty( $query['date_query'][0] ) ) {	
			$query['date_query'][0]['inclusive'] = true;		
		}
	
	}
	
	$order = !empty( $rules->publish_order ) ? $rules->publish_order : 'new_to_old';
	
	switch( $order ) { //An order of posts
		case 'new_to_old' :
			$query['orderby'] = 'date';
			$query['order'] = 'DESC';
			break;
		case 'old_to_new' :
			$query['orderby'] = 'date';
			$query['order'] = 'ASC';
			break;
		case 'random' :
			$query['orderby'] = 'rand';
			break;
	}
	
	$query['posts_per_page'] = -1; //Get all posts without pagination
	$query['post_status'] = 'publish'; //Get only published posts
	$query['fields'] = 'ids'; //Get only 'ids'
	
	/**
	 * Filters WP_Query query.
	 *
	 * @since 2.0.0
	 *
	 * @param array $query Query array.
	 * @param object $rules Rules object.
	 */	
	$query = apply_filters( 'lemonade_sna_pinterest_get_posts_query_filter', $query, $rules );

	$q = new WP_Query;
	
 	$result = $q->query( $query ); //Make query

	if( in_array( 'attachment', $query['post_type'] ) && isset( $rules->post_types->include ) ) { //For attachment need another query with some different parameters
		$query['post_type'] = array( 'attachment' );
		$query['post_status'] = 'inherit';
		$result2 = $q->query( $query ); //Make second query
		$result = array_merge( $result, $result2 );
	}	

	/**
	 * Filters a result of the query.
	 *
	 * @since 2.0.0
	 *
	 * @param array $result Result of query array.
	 * @param object $rules Template rules object.
	 * @param array $query WP_Query query array.
	 */	
	$result = apply_filters( 'lemonade_sna_pinterest_get_posts_result_filter', $result, $rules, $query );
	
	return $result;

}

/**
 * Gets information about posts published on a network.
 * 
 * Returns array of DB entries about posts published by a given template
 * during a cycle with a given number on a given network after a given datetime.
 *
 * @since 2.0.0
 *
 * @global object $wpdb WPDB object.
 *
 * @param int $templ_id Optional. Template ID.
 * @param int $cycle Optional. A number of a cycle (a post can be published several times in different cycles).
 * @param string $network Optional. A network name, like 'Pinterest'.
 * @param string $datetime Optional. A datetime after which we want to check was a post published or not.
 * @return array|null Array of entries from the DB about published posts or null if MySql query was incorrect.
 */
function lemonade_sna_pinterest_get_published_posts( $templ_id = '', $cycle = '', $network = '', $datetime = '' ) {
	global $wpdb;
	if( !empty( $network ) ) {
		$table1 = $wpdb->prefix . 'lemonade_autoposter_templates';	
		$table2 = $wpdb->prefix . 'lemonade_autoposter_posts_published';
		$query = "SELECT tp.* FROM $table1 as tt INNER JOIN $table2 as tp ON tt.id=tp.template_id WHERE tt.network=%s";
		$where[] = $network;
		if( !empty( $templ_id ) ) {
			$query .= " AND tp.template_id=%d";
			$where[] = $templ_id;
		}	
		if( !empty( $cycle ) ) {
			$query .= " AND tp.cycle=%d";
			$where[] = $cycle;
		}	
		if( !empty( $datetime ) ) {
			$query .= " AND tp.when_done >= %s";
			$where[] = $datetime;
		}
		$query .= " ORDER BY when_done ASC";
		$result = $wpdb->get_results( $wpdb->prepare( $query, $where ) );
	} else {
		$table = $wpdb->prefix . 'lemonade_autoposter_posts_published';	
		$query = "SELECT * FROM $table";
		$where = array();
		if( !empty( $templ_id ) ) {
			$query .= " WHERE template_id=%d";
			$where[] = $templ_id;
			if( !empty( $cycle ) ) {
				$query.= " AND cycle_count=%d";
				$where[] = $cycle;
			}
			if( !empty( $datetime ) ) {
				$query .= " AND when_done >= %s";
				$where[] = $datetime;
			}
		} else {
			if( !empty( $cycle ) ) {				
				$query.= " WHERE cycle_count=%d";
				$where[] = $cycle;				
				if( !empty( $datetime ) ) {
					$query .= " AND when_done >= %s";
					$where[] = $datetime;
				}				
			} elseif( !empty( $datetime ) ) {
				$query .= " WHERE when_done >= %s";
				$where[] = $datetime;
			}	
		}
		$query .= " ORDER BY when_done ASC";
		$result = ( !empty( $where ) ) ? $wpdb->get_results( $wpdb->prepare( $query, $where ) ) : $wpdb->get_results( $query );
	}
	
	return $result;

}

?>