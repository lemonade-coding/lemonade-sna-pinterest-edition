jQuery( document ).ready( function( $ ) {

	lemonade_sna_pinterest_schedule_heartbeat();
	
} );

//Scrolls window 
function lemonade_sna_pinterest_schedule_scroll( li ) {
	var first = li.find( '.waiting' ).first();
	if( 0 === first.length ) {
		first = li.find( '.posted' ).last();
	}
	if( 0 !== first.length ) {
		var offset = first.offset().top - 3 * ( jQuery( '.lsna-schedule-el' ).height() );
		if( offset > 0 ) {
			jQuery( 'html, body' ).animate( {
				scrollTop: offset
			}, 1000 );
		}
	}	
}

//Controls Pinterest Autoposter Live Stream
function lemonade_sna_pinterest_schedule_heartbeat() {
	var list = jQuery( '.lsna-template-schedule' );
	
	list.each( function() {
		var li = jQuery( this ),
		id = jQuery( this ).data( 'templateId' ),
		wrapper = jQuery( '.lsna-streamer-wrapper[data-template-id="' + id + '"]' ),
		last_resp = '',
		check_for_new = '',
		terminate = '';
		
		lemonade_sna_pinterest_schedule_scroll( li );
		
		setInterval( function( id ) {			
			var li = wrapper.find( '.lsna-template-schedule' ),
			cycle = li.data( 'templateCycle' );
			
			jQuery.post(
				ajaxurl,
				{
					action: 'lemonade_sna_pinterest_autoposter_stream',
					id: id,
					cycle: cycle,
					last_resp: last_resp,
					nonce: lemonade_sna_heartbeat.nonce
				},
				function( responce ) {
					if( responce && responce.length !== 0 ) {
						responce = JSON.parse( responce );
						if( terminate == 1 && !responce.terminate ) {
							wrapper.removeClass( 'inactive' );
							terminate = 0;
						}				
						if( responce.new_cycle ) {
							jQuery.post(
								ajaxurl,
								{
									action: 'lemonade_sna_pinterest_live_stream_list',
									id: id,
									nonce: lemonade_sna_heartbeat.nonce
								},
								function( html ) {
									if( html && html.length !== 0 ) {
										wrapper.html( html ).css( { 'animation': 'lsna-opacity 1.5s linear', 'webkit-animation': 'lsna-opacity 1.5s linear' } );
									}
									lemonade_sna_pinterest_schedule_scroll( wrapper.find( '.lsna-template-schedule' ) );
								}
							);
						} else if( check_for_new == 1 && ( ( responce.published && responce.published.length !== 0 ) || ( responce.next && responce.next.length !== 0 ) ) ) {
							jQuery.post(
								ajaxurl,
								{
									action: 'lemonade_sna_pinterest_live_stream_list',
									id: id,
									nonce: lemonade_sna_heartbeat.nonce
								},
								function( html ) {
									if( html && html.length !== 0 ) {
										wrapper.html( html ).css( { 'animation': 'lsna-opacity 1.5s linear', 'webkit-animation': 'lsna-opacity 1.5s linear' } );
									}
									if( responce.check_for_new ) {
									} else {
										check_for_new = 0;
									}
									lemonade_sna_pinterest_schedule_scroll( wrapper.find( '.lsna-template-schedule' ) );
								}
							);						
						} else if( responce.check_for_new ) {
							check_for_new = 1;
						} else if( responce.terminate ) {
							var to_be_posted = li.find( '.next_to_post' ),
							term = li.find( '.terminate' );
							
							to_be_posted.each( function() {
								jQuery( this ).removeClass( 'next_to_post' );
							} );
							term.each( function() {
								jQuery( this ).addClass( 'inactive' );
							} );	
							wrapper.addClass( 'inactive' );
							terminate = 1;
						} else {
							if( responce.updated ) {
									jQuery.post(
									ajaxurl,
									{
										action: 'lemonade_sna_pinterest_live_stream_list',
										id: id,
										nonce: lemonade_sna_heartbeat.nonce
									},
									function( html ) {
										if( html && html.length !== 0 ) {
											wrapper.html( html ).css( { 'animation': 'lsna-opacity 1.5s linear', 'webkit-animation': 'lsna-opacity 1.5s linear' } );
										}
									}
								);
							}	
							if( responce.published && responce.published.length !== 0 ) {
								jQuery.each( responce.published, function( i, published ) {
									var pi = published.post_id,
									bi = published.board_id,
									d = published.datetime,
									items = li.find( "[data-schedule-el-post-id='" + pi + "'][data-schedule-el-board-id='" + bi + "']" );
									
									if( items && items.length !== 0 ) {
										items.each( function() {
											if( jQuery( this ).hasClass( 'posted' ) == false ) {
												jQuery( this ).find( '.lsna-posted-time' ).html( '<span></span>' + d );
												jQuery( this ).find( '.lsna-was-will-published' ).html( lemonade_sna_heartbeat.was );
												jQuery( this ).removeClass( 'next_to_post' ).removeClass( 'waiting' ).addClass( 'posted' ).css( { 'animation': 'lsna-opacity 1.5s linear', '-webkit-animation': 'lsna-opacity 1.5s linear' } );
											}
										} );
									}
								} );
							}
							if( responce.next && responce.next.length !== 0 ) {
								jQuery.each( responce.next, function( i, next ) {
									var pi = next.post_id,
									bi = next.board_id,
									items = li.find( "[data-schedule-el-post-id='" + pi + "'][data-schedule-el-board-id='" + bi + "']" );
									
									if( items && items.length !== 0 ) {
										items.each( function() {
											last_posted = li.find( '.posted' ).last();
											if( last_posted.length !== 0 ) {
												jQuery( this ).insertAfter( last_posted );
											}
											if( jQuery( this ).hasClass( 'next_to_post' ) == false ) {		
												jQuery( this ).addClass( 'next_to_post' ).css( { 'animation': 'lsna-opacity 1.5s linear', 'webkit-animation': 'lsna-opacity 1.5s linear' } );
											}
										} )
									}
								} );
							}
							if( responce.limit_reached ) {
								li.find( '.posted' ).last().find( '.lsna-day-limit' ).removeClass( 'lsna-invisible' );
							} else {
								li.find( '.posted' ).last().find( '.lsna-day-limit' ).addClass( 'lsna-invisible' );
							}
							lemonade_sna_pinterest_schedule_scroll( li );
							if( responce.resp_time && responce.resp_time.length !== 0 ) {
								last_resp = responce.resp_time;
							}
						}
						if( li.find( '.posted' ).last().is( li.find( '.lsna-schedule-el' ).eq( -2 ) ) && terminate != 1 ) {
							jQuery( '.lsna-schedule-el.terminate' ).removeClass( 'inactive' );
						}						
					}
				}
			);
		}, 60000, id );
	} );
}