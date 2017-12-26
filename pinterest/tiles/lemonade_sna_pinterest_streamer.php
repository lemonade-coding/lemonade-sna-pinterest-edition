<?php

/**
 * Html output template for Lemonade SNA Pinterest Live Streamer
 *
 * @package LemonadeSNAPinterest
 * @since 1.0.0
 * @since 2.0.0 Some data sanitization added.
 * 
 * @param array $lines Array of lines got from 'lemonade_sna_pinterest_autoposter_lines' option.
 * @param object $template A template object.
 */

//Get template information
$cycle = !empty( $lines[$template->id]['cycle'] ) ? (int)$lines[$template->id]['cycle'] : 1;
$terminate = !empty( $lines[$template->id]['terminate'] ) ? esc_attr( $lines[$template->id]['terminate'] ) : 'wait_new';
$publ_before = lemonade_sna_pinterest_get_published_posts( (int)$template->id, $cycle );
$line = !empty( $lines[$template->id]['line'] ) ? $lines[$template->id]['line'] : array();
$timezone = !empty( $lines[$template->id]['special']['time_zone'] ) ? sanitize_text_field( $lines[$template->id]['special']['time_zone'] ) : 'UTC';
$limit = !empty( $lines[$template->id]['limit']['limit'] ) ? (int)$lines[$template->id]['limit']['limit'] : '';

$date_format = get_option( 'date_format' ) != '' ? sanitize_text_field( get_option( 'date_format' ) ) : 'Y-m-d';
$time_format = get_option( 'time_format' ) != '' ? sanitize_text_field( get_option( 'time_format' ) ) : 'H:i';
$datetime_format = $date_format . ' ' . $time_format;

$common_array = array();
$common_array_control =  array();
$next = array();
if( !empty( $publ_before ) ) {
	foreach( $publ_before as $publ ) {
		$format_time = lemonade_sna_pinterest_get_date_or_time_with_timezone( sanitize_text_field( $publ->when_done ), $datetime_format, $timezone );
		$format_time .= '<br><small>' . __( 'by', 'lemonade_sna' ) . ' ' . $timezone . '</small>';
		if( lemonade_sna_pinterest_numbers_only( $publ->board_id ) ) {
			$common_array[] = array(
				(int)$publ->post_id => $publ->board_id,
				'time' => $format_time
			);	
			$common_array_control[] = array( // Sometimes information in the DB updates earlier than the option. So, we control that to remove duplicates of already posted items.
				$publ->post_id => $publ->board_id
			);	
		}	
	}
}
if( !empty( $line ) ) {
	foreach( $line as $key => $to_post ) {
		foreach( $to_post as $post_id => $board_id ) {
			
			if( !lemonade_sna_pinterest_numbers_only( $board_id ) ) {
				continue;
			}
			$post_id = (int)$post_id;
			if( !in_array( array( $post_id => $board_id ), $common_array_control ) ) { // Remove duplicates
				$common_array[] = array( $post_id => $board_id );
 				if( !empty( $prev_id ) ) {
					continue 2;
				}				
				$next[] = array( $post_id => $board_id );
				$prev_id = $post_id;				
			}
		}
	}
}
if( !empty( $common_array ) ) :
?>
<ul class="lsna-template-schedule" data-template-id="<?php echo (int)$template->id; ?>" data-template-cycle="<?php echo $cycle; ?>">
	<?php
	foreach( $common_array as $ar_key => $line_el ) :
		$time = '';
		if( !empty( $line_el['time'] ) ) {
			$time = $line_el['time'];
			unset( $line_el['time'] );		
		}
		if( is_array( $line_el ) ) :
			foreach( $line_el as $post_id => $board_id ) :
				$class_success = !empty( $time ) ? 'posted' : 'waiting';
				$class_next = in_array( array( $post_id => $board_id ), $next ) ? 'next_to_post' : '';
				$post_type = get_post_type( $post_id );
				$post_type_obj = get_post_type_object( $post_type );
				$post_type_name = is_object( $post_type_obj ) ? $post_type_obj->labels->singular_name : '';
				$post_type_icon = is_object( $post_type_obj ) ? $post_type_obj->menu_icon : '';				
				if( !empty( $user_boards ) && is_array( $user_boards ) ) {
					foreach( $user_boards as $board ) {
						if( is_object( $board ) && $board->id == $board_id ) {
							$board_name = $board->name;
							$board_url = $board->url;
						}
					}
				}				
				?>
				<li class="bg-white padding10 lsna-schedule-el <?php echo $class_success; ?> <?php echo $class_next; ?>" data-schedule-el-post-id="<?php echo $post_id; ?>" data-schedule-el-board-id="<?php echo $board_id; ?>">
					<div class="clear-float">
						<div class="place-left">
						<?php
						echo '<a href="' . get_permalink( $post_id ) . '" target="_blank">';	
						echo ( get_the_title( $post_id ) != '' ) ? get_the_title( $post_id ) : __( 'Post ID:', 'lemonade_sna' ) . ' ' . $post_id; 
						echo '</a>';
						echo ( !empty( $time ) ) ? ' <span class="lsna-was-will-published">' . __( 'was published', 'lemonade_sna' ) . '</span>' : ' <span class="lsna-was-will-published">' . __( 'will be published', 'lemonade_sna' ) . '</span>';
						echo ' ' . __( 'on board', 'lemonade_sna' ) . ' ' . ( !empty( $board_name ) ? ( !empty( $board_url ) ? '<a href="' . $board_url . '" target="_blank">' : '' ) . $board_name . ( !empty( $board_url ) ? '</a>' : '' ) : ( !empty( $board_url ) ? '<a href="' . $board_url . '" target="_blank">' : '' ) . $board_id . ( !empty( $board_url ) ? '</a>' : '' ) );
						?>						
						</div>
						<div class="lsna-posted-time place-right align-right">
						<?php echo !empty( $time ) ? '<span></span>' . $time . '' : '<span></span>' . __( 'Next to be posted...', 'lemonade_sna' ); ?>
						</div>
						<div class="place-left">
							<div><small><?php echo !empty( $post_type_name ) ? __( 'Post type', 'lemonade_sna' ) . ': ' . ( !empty( $post_type_icon ) ? '<img src="' . $post_type_icon . '">' : '' ) . $post_type_name : ''; ?></small></div>
							<?php if( !empty( $limit ) ) : ?>
							<div>
								<small><?php echo __( 'Day limit', 'lemonade_sna' ) . ': ' . $limit; ?></small>	
							</div>
							<div class="lsna-day-limit align-center lsna-invisible"><b><?php echo __( 'Day limit reached', 'lemonade_sna' ); ?></b></div>
							<?php endif; ?>
						</div>						
					</div>
				</li>
				<?php
			endforeach;
		endif;
	endforeach;
	$inactive = !empty( $class_success ) && $class_success == 'posted' ? '' : 'inactive';
	switch( $terminate ) {
		case 'wait_new' :
			$text = __( 'Waiting for new posts', 'lemonade_sna' ) . '...';
			break;
		case 'stop_work' :
			$text = __( 'Template will be deactivated', 'lemonade_sna' ) . '...';
			break;
		case 'start_new' :
			$text = __( 'New cycle will be started', 'lemonade_sna' ) . '...';
			break;
	}	
	?>
	<li class="bg-white padding10 lsna-schedule-el terminate waiting <?php echo $inactive; ?>">
		<div class="clear-float">
			<div class="place-left">
				<span></span><span><?php echo $text; ?></span>
			</div>
		</div>
	</li>
</ul>
<?php endif; ?>