<?php

/**
 * Functions, hooks and filters which create Pinterest Lemonade SNA dashboard output
 *
 * @package LemonadeSNAPinterest
 * @since 1.0.0
 */
 
///////////////////
//ADMIN DASHBOARD//
///////////////////

add_action( 'admin_menu', 'lemonade_sna_pinterest_admin_menu', 10 ); //Create dashboard menu pages

/////////////
//FUNCTIONS//
/////////////

/**
 * Creates Pinterest Admin Menu.
 *
 * Creates pages in the WP Dashboard menu.
 *
 * @since 1.0.0
 * @since 2.0.0 Some data sanitization added.
 */
function lemonade_sna_pinterest_admin_menu() {

	$settings = get_option( 'lemonade_sna_pinterest_settings' );
 	if( !empty( $settings ) ) {
		$cap_read = !empty( $settings['roles']['dashboard'] ) ? sanitize_text_field( $settings['roles']['dashboard'] ) : 'administrator';
		$cap_read = array_keys( get_role( $cap_read )->capabilities );
		$cap_read = $cap_read[0];
	} else {
		$cap_read = 'manage_options';
	}
	
	add_menu_page( __( 'Lemonade SNA Pinterest', 'lemonade_sna' ), __( 'Lemonade SNA Pinterest', 'lemonade_sna' ), $cap_read, 'lemonade_sna_pinterest1', 'lemonade_sna_pinterest_admin_panel' ); //Main settings
	add_submenu_page( 'lemonade_sna_pinterest1', __( 'API Settings - Lemonade SNA Pinterest', 'lemonade_sna' ), __( 'API Settings', 'lemonade_sna' ), $cap_read, 'lemonade_sna_pinterest2', 'lemonade_sna_pinterest_admin_panel_api' ); //API settings
	add_submenu_page( 'lemonade_sna_pinterest1', __( 'Pins - Lemonade SNA Pinterest', 'lemonade_sna' ), __( 'Pins', 'lemonade_sna' ), $cap_read, 'lemonade_sna_pinterest3', 'lemonade_sna_pinterest_admin_panel_pins' ); //Pin templates settings
	add_submenu_page( 'lemonade_sna_pinterest1', __( 'Pinterest Autoposter - Lemonade SNA Pinterest', 'lemonade_sna' ), __( 'Autoposter', 'lemonade_sna' ), $cap_read, 'lemonade_sna_pinterest5', 'lemonade_sna_pinterest_admin_panel_autoposter' ); //Autoposter settings
	add_submenu_page( 'lemonade_sna_pinterest1', __( 'Pinterest Autoposter Live Stream - Lemonade SNA Pinterest', 'lemonade_sna' ), __( 'Live Stream', 'lemonade_sna' ), $cap_read, 'lemonade_sna_pinterest6', 'lemonade_sna_pinterest_admin_panel_streamer' ); //Live Stream
	add_submenu_page( 'lemonade_sna_pinterest1', __( 'Getting PRO - Lemonade SNA Pinterest', 'lemonade_sna' ), __( 'Getting PRO', 'lemonade_sna' ), $cap_read, 'lemonade_sna_pinterest7', 'lemonade_sna_pinterest_admin_panel_pro' ); //Live Stream
	
}

/**
 * Outputs Lemonade SNA Pinterest Settings page html content.
 *
 * Creates html for the main settings page.
 *
 * @since 1.0.0
 * @since 2.0.0 Some data sanitization added.
 */
function lemonade_sna_pinterest_admin_panel() {

	$roles = array_keys( get_editable_roles() );
	$settings = get_option( 'lemonade_sna_pinterest_settings' );
	?>
	<div class="wrap">
		<h1 class="lsna-admin-page-title"><?php echo esc_attr( __( 'Lemonade SNA Pinterest Settings - Lemonade SNA Pinterest', 'lemonade_sna' ) ); ?></h1>
		<?php 
		settings_errors(); 
		if( isset( $_SESSION['lsna_error'] ) && !empty( $_SESSION['lsna_error'] ) ) {
			foreach( $_SESSION['lsna_error'] as $error ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo esc_attr( $error ); ?></p>
			</div>
			<?php
			}
		}		
		if( isset( $_SESSION['lsna_success'] ) && !empty( $_SESSION['lsna_success'] ) ) {
			?>
			<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
				<p><?php echo esc_attr( $_SESSION['lsna_success'] ); ?></p>
			</div>
			<?php
		}		
		?>
		<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
			<input type="hidden" value="lemonade_sna_pinterest_save_settings" name="action">
			<?php wp_nonce_field( 'lsna-settings' ); ?>
			<div class="accordion" data-role="accordion">
				<?php ob_start(); ?>
				<div class="frame">
					<div class="heading"><?php echo __( 'User Privileges', 'lemonade_sna' ); ?></div>
					<div class="content">
						<h4><?php echo __( 'Who can see Lemonade SNA admin dashboard?', 'lemonade_sna' ); ?></h4>
						<div class="input-control full-size" data-role="select" data-placeholder="<?php echo __( 'Select Roles...', 'lemonade_sna' ); ?>">
							<select name="roles[dashboard]">
							<?php
							foreach( $roles as $role ) {
								$selected = isset( $settings['roles']['dashboard'] ) && $role == $settings['roles']['dashboard'] ? 'selected="selected"' : '';
								?>
								<option value="<?php echo esc_attr( $role ); ?>" <?php echo $selected; ?>><?php echo esc_attr( $role ); ?></option>
								<?php
							}
							?>
							</select>
						</div>
						<h4><?php echo __( 'Who can set up Pinterest API?', 'lemonade_sna' ); ?></h4>
						<div class="input-control full-size" data-role="select" data-placeholder="<?php echo __( 'Select Roles...', 'lemonade_sna' ); ?>">
							<select name="roles[api]">
							<?php
							foreach( $roles as $role ) {
								$selected = isset( $settings['roles']['api'] ) && $role == $settings['roles']['api'] ? 'selected="selected"' : '';
								?>
								<option value="<?php echo esc_attr( $role ); ?>" <?php echo $selected; ?>><?php echo esc_attr( $role ); ?></option>
								<?php
							}
							?>
							</select>
						</div>					
						<h4><?php echo __( 'Who can set up Pinterest Pin templates?', 'lemonade_sna' ); ?></h4>
						<div class="input-control full-size" data-role="select" data-placeholder="<?php echo __( 'Select Roles...', 'lemonade_sna' ); ?>">
							<select name="roles[pin]">
							<?php
							foreach( $roles as $role ) {
								$selected = isset( $settings['roles']['pin'] ) && $role == $settings['roles']['pin'] ? 'selected="selected"' : '';
								?>
								<option value="<?php echo esc_attr( $role ); ?>" <?php echo $selected; ?>><?php echo esc_attr( $role ); ?></option>
								<?php
							}
							?>
							</select>
						</div>									
						<h4><?php echo __( 'Who can set up Autoposter templates?', 'lemonade_sna' ); ?></h4>
						<div class="input-control full-size" data-role="select" data-placeholder="<?php echo __( 'Select Roles...', 'lemonade_sna' ); ?>">
							<select name="roles[autoposter]">
							<?php
							foreach( $roles as $role ) {
								$selected = isset( $settings['roles']['autoposter'] ) && $role == $settings['roles']['autoposter'] ? 'selected="selected"' : '';
								?>
								<option value="<?php echo esc_attr( $role ); ?>" <?php echo $selected; ?>><?php echo esc_attr( $role ); ?></option>
								<?php
							}
							?>
							</select>
						</div>				
					</div>
				</div>
				<div class="frame">
					<div class="heading"><?php echo esc_attr( __( 'Reports Settings', 'lemonade_sna' ) ); ?></div>
					<div class="content">
						<h4><?php echo __( 'Swith on/off reports?', 'lemonade_sna' ); ?></h4>
						<div>
							<span><?php echo __( 'Off', 'lemonade_sna' ); ?></span>
							<label class="switch lsna-inc-ex">
								<input type="checkbox" value="1" name="switch_reports" <?php echo isset( $settings['reports'] ) && true === $settings['reports'] ? 'checked="checked"' : ''; ?>> 
								<span class="check"></span>							
							</label>
							<span><?php echo __( 'On', 'lemonade_sna' ); ?></span>
						</div>							
						<small><?php echo __( 'If you switch on the option, reports will be written down to lemonade_sna_pinterest_log.log file.', 'lemonade_sna' ); ?></small>
					</div>
				</div>		
				<?php
				$html = ob_get_contents();
				if( $html ) {
					ob_end_clean();
				}
				
				/**
				 * Filters the Lemonade SNA Pinterest Settings page content inside the accordion tabs.
				 *
				 * @since 1.0.0
				 *
				 * @param string $html Html to filter.
				 * @param array $roles Array of editable user's roles names.
				 */				 
				echo apply_filters( 'lemonade_sna_pinterest_settings_filter', $html, $roles );
				?>	
			</div>
			<p></p>
			<p>
				<input type="submit" class="button button-primary" value="<?php echo __( 'Save', 'lemonade_sna' ); ?>">
			</p>
		</form>
	</div>
	<?php	
	unset( $_SESSION['lsna_error'] );
	unset( $_SESSION['lsna_success'] );	
}

/**
 * Creates content for API Settings page.
 *
 * Creates html which is shown on API Settings page.
 *
 * @since 1.0.0
 * @since 2.0.0 Some data sanitization added. New fields html for two-steps API authorazation added.
 */
function lemonade_sna_pinterest_admin_panel_api() {
	?>
	<div class="wrap">
		<h1 class="lsna-admin-page-title"><?php echo esc_attr( __( 'API Settings - Lemonade SNA Pinterest', 'lemonade_sna' ) ); ?></h1>
		<?php settings_errors(); 		
		if( isset( $_SESSION['lsna_error'] ) && !empty( $_SESSION['lsna_error'] ) ) {
			foreach( $_SESSION['lsna_error'] as $error ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo sanitize_text_field( $error ); ?></p>
			</div>
			<?php
			}
		}
		
		if( isset( $_SESSION['lsna_success'] ) && !empty( $_SESSION['lsna_success'] ) ) {
			?>
			<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
				<p><?php echo sanitize_text_field( $_SESSION['lsna_success'] ); ?></p>
			</div>
			<?php
		}
		ob_start();
		?>
		<section>
			<div class="panel lsna-collapsible collapsed" data-role="panel">
				<div class="heading bg-mauve">
					<span class="title"><?php echo __( 'Help', 'lemonade_sna' ); ?></span>
				</div>
				<div class="content padding10 bg-white">
					<h5><b><?php echo __( 'What is Pinterest API?', 'lemonade_sna' ); ?></b></h5>
					<p><?php echo __( 'Pinterest API is a set of key phrases (combinations of symbols), which allows to indentify you and confirm your ownership for a Pinterest account when the programm makes some requests to Pinterest social network.', 'lemonade_sna' ); ?></p>
					<p><?php echo __( 'A process of authorization (getting API keys) needs two steps. First you get your App ID and App secret key, second you authorize for to get an Access token.', 'lemonade_sna' ); ?></p>
					<h5><b><?php echo __( 'How to get App ID and App secret?', 'lemonade_sna' ); ?></b></h5>
					<p><?php echo __( 'For to get your App ID and App secret you have to create a Pinterest application.', 'lemonade_sna' ); ?></p>
					<ul>
						<li><?php echo __( 'Log into your Pinterest account.', 'lemonade_sna' );?></li>
						<li><?php echo __( 'Navigate to', 'lemonade_sna' ); ?> <a href="https://developers.pinterest.com/" target="_blank"><?php echo __( 'Pinterest developers', 'lemonade_sna' ); ?></a>.</li>
						<li><?php echo __( 'Click to the Apps link in the main menu.', 'lemonade_sna' ); ?></li>
						<li><?php echo __( 'Push Create app button.', 'lemonade_sna' ); ?></li>
						<li><?php echo __( 'Fill fields in the form of a new application and press Create app.', 'lemonade_sna' ); ?></li>
						<li><?php echo __( 'Now you have a screen with App ID and App secret codes. Scroll the screen down.', 'lemonade_sna' ); ?></li>
						<li><?php echo __( 'In the Platform section set up Site URL and Redirect URIs. For the site url put your current site link in. In the field Redirect URIs put link to the current Wordpress admin page (where you are reading all this stuff now), starting with "https://". Press Enter and after that do not forget to save changes.', 'lemonade_sna' ); ?></li>
					</ul>
					<p><?php echo __( 'Now you have your Pinterest app setted up.', 'lemonade_sna' ); ?></p>
					<p><?php echo __( 'Put App ID and App secret into the fields of the form below and save changes.', 'lemonade_sna' ); ?></p>
					<h5><b><?php echo __( 'How to get Access Token?', 'lemonade_sna' ); ?></b></h5>
					<p><?php echo __( 'If your App ID and App secret are setted up properly, you will see the "Now you can authorize Pinterest" link below the API settings form.', 'lemonade_sna' ); ?></p>
					<p><?php echo __( 'Click the link and accept the query for permissions from your App.', 'lemonade_sna' ); ?></p>
					<p><?php echo __( 'If everything is O`k, you will be redirected to the same admin page, below the link there will be an Access token.', 'lemonade_sna' ); ?></p>
					<p><?php echo __( 'Copy the Access token and paste it into the field of the form with API settings. Save changes.', 'lemonade_sna' ); ?></p>
					<p><?php echo __( 'If your API is setted up, in the section Test your API you will see some information about your Pinterest profile.', 'lemonade_sna' ); ?></p>
				</div>
			</div>
		</section>	
		<section>
			<?php
			$app_id = get_option( 'lemonade_sna_pinterest_app_id' ) != '' ? esc_attr( get_option( 'lemonade_sna_pinterest_app_id' ) ) : '';
			$app_secret = get_option( 'lemonade_sna_pinterest_app_secret' ) != '' ? esc_attr( get_option( 'lemonade_sna_pinterest_app_secret' ) ) : '';
			$cur_url = str_replace( 'http://', 'https://', menu_page_url( 'lemonade_sna_pinterest2', false ) );
			?>
			<p>
			<?php echo __( 'Please, read the Help section above or the documentation for to know how to set up Pinterest API.', 'lemonade_sna' ); ?>
			</p>
			<form id="lsna-pinterest-api-settings-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
				<?php wp_nonce_field( 'lsna-api' ); ?>
				<input type="hidden" name="action" value="lemonade_sna_pinterest_save_api">
				<label for="[name='app_id']"><?php echo __( 'App ID', 'lemonade_sna' ); ?></label>
				<div class="input-control text full-size">
					<input type="text" name="app_id" value="<?php echo $app_id; ?>" placeholder="<?php echo __( 'Insert here App ID...', 'lemonade_sna' ); ?>"  data-validate-hint="<?php echo __( 'Field can contain numbers only.', 'lemonade_sna' ); ?>"/>
					<span class="input-state-error mif-warning"></span>
				</div>
				<small><?php echo __( 'Read about how to get App ID in the Help section.', 'lemonade_sna' ); ?></small>
				<p></p>		
				<label for="[name='app_secret']"><?php echo __( 'App secret', 'lemonade_sna' ); ?></label>
				<div class="input-control text full-size">
					<input type="text" name="app_secret" value="<?php echo $app_secret; ?>" placeholder="<?php echo __( 'Insert here App Secret...', 'lemonade_sna' ); ?>"  data-validate-hint="<?php echo __( 'Field can contain numbers and letters only.', 'lemonade_sna' ); ?>"/>
					<span class="input-state-error mif-warning"></span>
				</div>
				<small><?php echo __( 'Read about how to get App secret in the Help section.', 'lemonade_sna' ); ?></small>
				<p></p>				
				<label for="[name='access_token']"><?php echo __( 'Access Token', 'lemonade_sna' ); ?></label>
				<div class="input-control text full-size">
					<input type="text" name="access_token" value="<?php echo esc_attr( get_option( 'lemonade_sna_access_token' ) ); ?>" placeholder="<?php echo __( 'Insert here Access Token...', 'lemonade_sna' ); ?>"/>
					<span class="input-state-error mif-warning"></span>
				</div>
				<small><?php echo __( 'Read about how to get Access Token in the Help section.', 'lemonade_sna' ); ?></small>
				<p></p>
				<p>
					<button class="button button-primary" onclick="lemonade_sna_validate_pinterest_api_settings_form();">
					<?php echo __( 'Save Settings', 'lemonade_sna' ); ?>
					</button>
				</p>
			</form>
			<?php
			if( !empty( $app_id ) && !empty( $app_secret ) ) {
				include( LEMONADE_SNA_PINTEREST_EXT_PATH . '/tiles/lemonade_sna_get_pinterest_access_token.php' );
			}
			?>
		</section>
		<section>
			<div class="lsna-pinterest-test clearfix">
				<h4><?php echo __( 'Test your API', 'lemonade_sna' ); ?></h4>
				<?php
				include( LEMONADE_SNA_PINTEREST_EXT_PATH . '/tiles/lemonade_sna_test_pinterest_api.php' );
				?>
			</div>
		</section>
		<?php
		$html = ob_get_contents();
		if( $html ) {
			ob_end_clean();
		}
		
		/**
		 * Filters Pinterest API settings page content.
		 *
		 * @since 1.0.0
		 *
		 * @param string $html Html to filter.
		 */
		echo apply_filters( 'lemonade_sna_pinterest_api_settings_page_content', $html );
		?>
	</div>
	<?php
	unset( $_SESSION['lsna_error'] );
	unset( $_SESSION['lsna_success'] );	
}

/**
 * Creates content of Pinterest Pins Settings page.
 *
 * Creates html for Pin templates admin page.
 * 
 * @since 1.0.0
 * @since 1.0.1 Modified to fix problem with editing and deleting templates. 
 * Also a data attribute 'data-backurl' was added to Delete Template dialog 
 * to fix some problems with redirecting after deleting.
 * @since 2.0.0 Some data sanitization added. Issue with used by templates post 
 * categories fixed. Card layout for list of templates instead of a table layout.
 */
function lemonade_sna_pinterest_admin_panel_pins() {
	?>
	<div class="wrap">
		<h1 class="lsna-admin-page-title"><?php echo __( 'Pinterest Pin Settings - Lemonade SNA Pinterest', 'lemonade_sna' ); ?></h1>
		<?php
		settings_errors();
		//Show plugin custom error messages
		if( isset( $_SESSION['lsna_error'] ) && !empty( $_SESSION['lsna_error'] ) ) {
			foreach( $_SESSION['lsna_error'] as $error ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo sanitize_text_field( $error ); ?></p>
			</div>
			<?php
			}
		}
		if( isset( $_SESSION['lsna_success'] ) && !empty( $_SESSION['lsna_success'] ) ) {
			?>
			<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
				<p><?php echo sanitize_text_field( $_SESSION['lsna_success'] ); ?></p>
			</div>
			<?php
		}	
		//List of shortcodes	
		?>
		<section>
			<div class="panel lsna-collapsible collapsed" data-role="panel">
				<div class="heading bg-mauve">
					<span class="title"><?php echo __( 'List of shortcodes', 'lemonade_sna' ); ?></span>
				</div>
				<div class="content padding10 bg-white">
					<?php
					$list = lemonade_sna_pinterest_built_in_shortcodes();
					if( !empty( $list ) ) {
						?>
						<dl class="default">
						<?php
						foreach( $list as $shortcode => $info ) {
							?>
							<dt>
							<?php echo '%' . $shortcode . '%'; ?>
							</dt>
							<dd>
							<?php echo $info['desc']; ?>
							</dd>
							<?php
						}
						?>
						</dl>
						<?php
					}
					?>
				</div>	
			</div>
		</section>
		<section>
			<?php
			$editmode = false;
			$templates = get_option( 'lemonade_sna_pinterest_pin_templates' );
			$template = array();
			$rules = array();
			if( isset( $_REQUEST['edit_template'] ) && $_REQUEST['edit_template'] == 1 && $_REQUEST['template_id'] !== '' ) {
				$editmode = true;				
				$template = $templates[$_REQUEST['template_id']];
				$rules = $template['rules'];
			}
			if( !empty( $templates ) ) { //Get the post types and taxonomies which are used by other templates
				foreach( $templates as $id => $rp_template ) {
					if( $editmode && $id == $_REQUEST['template_id'] ) {
						continue;
					}
					if( !empty( $rp_template['rules']['post_types'] ) ) {
						foreach( $rp_template['rules']['post_types'] as $pt => $tax ) {
							$rp_used[$pt] = !empty( $rp_used[$pt] ) ? $rp_used[$pt] : array();;
							if( !empty( $tax ) ) {
								foreach( $tax as $tn => $ta ) {
									if( !empty( $ta['terms'] ) ) {
										foreach( $ta['terms'] as $t ) {
											$rp_used[$pt][$tn][] = (int)$t;
										}
									}
								}
							}
						}
					}
				}
			}			
			?>			
			<h4><?php echo __( 'Create Pin Template', 'lemonade_sna' ); ?></h4>
			<form id="lsna-pinterest-pin-template-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
				<input type="hidden" value="lemonade_sna_pinterest_save_pin_template" name="action">
				<?php if( $editmode ) : ?>
					<input type="hidden" name="edit_template" value="<?php echo isset( $_REQUEST['template_id'] ) ? (int)$_REQUEST['template_id'] : ''; ?>">
				<?php endif; ?>
				<?php wp_nonce_field( 'lsna-pin-template' ); ?>
				<div class="input-control full-size text lsna-template-name" data-placeholder="<?php echo __( 'Create a name for a template', 'lemonade_sna' ); ?>">
					<input type="text" name="template_name" value="<?php echo !empty( $template['title'] ) ? esc_attr( $template['title'] ) : ''; ?>" placeholder="<?php echo __( 'Create a name for template', 'lemonade_sna' ); ?>" data-validate-hint="<?php echo __( 'The field can not be empty', 'lemonade_sna' ); ?>"/>
					<span class="input-state-error mif-warning"></span>
				</div>	
				<div class="accordion" data-role="accordion">
					<div class="frame">
						<div class="heading"><?php echo __( 'Create Template', 'lemonade_sna' ); ?></div>
						<div class="content">
							<?php ob_start(); ?>
							<h4><?php echo __( 'Choose Post Types', 'lemonade_sna' ); ?></h4>
							<?php
							$post_types = get_post_types( array( 'public' => true ), 'objects' );
							?>
							<div class="grid">
							<?php
							if( !empty( $post_types ) ) {
								foreach( $post_types as $k => $post_type ) {
									$disabled = '';
									if( isset( $rp_used[$k] ) && empty( $rp_used[$k] ) ) {
										$disabled = 'disabled="disabled"';
									}
									$checked = isset( $rules['post_types'][$k] ) ? 'checked="checked"' : '';
									?>
									<div class="row cells3">
										<div class="cell">
											<label class="input-control checkbox small-check">
												<input type="checkbox" name="pin_template[<?php echo esc_attr( $k ); ?>][checked]" value="1" <?php echo $disabled; ?> <?php echo $checked; ?>>
												<span class="check"></span>
												<span class="caption"><?php echo $post_type->label; ?></span>									
											</label>
											<?php if( $disabled ) echo '<small>' . __( 'Is being used by another template', 'lemonade_sna' ) . '</small>'; ?>
										</div>
										<div class="cell colspan2">
											<?php 
											if( $k == 'post' ) {
												$terms = get_terms( array(
													'taxonomy' => 'category',
													'hide_empty' => false
												) );
												if( !empty( $terms ) ) {
												?>
												<div class="input-control full-size" data-role="select" data-multiple="true" data-placeholder="<?php echo __( 'Select Categories...', 'lemonade_sna' ); ?>">
													<select name="pin_template[post][category][]" multiple="multiple">
													<?php
													foreach( $terms as $t_obj ) {
														$selected = 'selected="selected"';
														$disabled = '';
														if( !empty( $rules['post_types'][$k]['category']['terms'] ) ) {
															if( in_array( $t_obj->term_id, $rules['post_types'][$k]['category']['terms'] ) ) {
																$selected = 'selected="selected"';
															} else {
																$selected = '';
															}
														}	
														if( !empty( $rp_used['post']['category'] ) && in_array( $t_obj->term_id, $rp_used['post']['category'] ) ) {
															$disabled = 'disabled="disabled"';
															$selected = '';
														}
														?>
														<option value="<?php echo (int)$t_obj->term_id; ?>" <?php echo $selected; ?> <?php echo $disabled; ?>><?php echo esc_attr( $t_obj->name ); if( $disabled ) echo ' - ' . __( 'Is being used by another template', 'lemonade_sna' ); ?></option>
														<?php
													}
													?>
													</select>
												</div>
												<?php
												}
											}	
											?>
										</div>
									</div>
									<?php
								}
							}
							?>							
							</div>
							<?php			
							$content = ob_get_contents();
							if( $content ) {
								ob_end_clean();
							}
							
							/**
							 * Filters posts filter html output for Pin template settings.
							 *
							 * @since 1.0.0
							 *
							 * @param string $content Html to filter.
							 */							
							echo apply_filters( 'lemonade_sna_pinterest_pin_template_posts_filter', $content );
							
							ob_start(); ?>
							<h4><?php echo __( 'Template settings', 'lemonade_sna' ); ?></h4>
							<div class="input-control text full-size">
								<input type="text" name="pin_image" value="<?php echo !empty( $rules['pin_image'] ) ? esc_attr( $rules['pin_image'] ) : ''; ?>" placeholder="<?php echo __( 'Image url', 'lemonade_sna' ); ?>">
							</div>
							<?php
							$recommended = '<span class="tag">%' . trim( implode( "%</span>, <span class='tag'>%", array_keys( lemonade_sna_pinterest_built_in_shortcodes( 'image_url' ) ) ), ", " ) . '%</span>';
							?>							
							<small><?php echo __( 'The link to the image that you want to Pin. Recommended shortcodes:', 'lemonade_sna' ) . ' ' . $recommended; ?></small>	
							<div class="input-control text full-size">
								<input type="text" name="pin_link" value="<?php echo !empty( $rules['pin_link'] ) ? esc_attr( $rules['pin_link'] ) : ''; ?>" placeholder="<?php echo __( 'Link', 'lemonade_sna' ); ?>">
							</div>
							<?php
							$recommended = '<span class="tag">%' . trim( implode( "%</span>, <span class='tag'>%", array_keys( lemonade_sna_pinterest_built_in_shortcodes( 'link' ) ) ), ", " ) . '%</span>';
							?>
							<small><?php echo __( 'The URL the Pin will link to when you click through. Recommended shortcodes:', 'lemonade_sna' ) . ' ' . $recommended; ?></small>
							<div class="input-control textarea full-size">
								<textarea name="pin_note" class="" placeholder="<?php echo __( 'Note', 'lemonade_sna' ); ?>"><?php echo !empty( $rules['pin_note'] ) ? esc_attr( $rules['pin_note'] ) : ''; ?></textarea>
							</div>
							<?php
							$recommended = '<span class="tag">%' . trim( implode( "%</span>, <span class='tag'>%", array_keys( lemonade_sna_pinterest_built_in_shortcodes() ) ), ", " ) . '%';
							?>							
							<small><?php echo __( 'The Pin`s description. Use all available shortcodes. If you would like to get hashtags just put "#" before a shortcode. Recommended shortcodes:', 'lemonade_sna' ) . ' ' . $recommended; ?></small>
							<h4><?php echo __( 'Shortcodes settings', 'lemonade_sna' ); ?></h4>
							<h5><?php echo __( 'Separator', 'lemonade_sna' ); ?></h5>
							<div class="input-control text">
								<input type="text" name="separator" value="<?php echo !empty( $rules['tags_sep'] ) ? esc_attr( $rules['tags_sep'] ) : ''; ?>" placeholder="<?php echo __( 'Tags separator', 'lemonade_sna' ); ?>">
							</div>
							<small><?php echo __( 'Which separator to use if shortcodes like %tags%, %categories%, etc. contain more than one item.', 'lemonade_sna' ); ?></small>
							<h5><?php echo __( 'Announce characters limit', 'lemonade_sna' ); ?></h5>
							<div class="input-control text">
								<input type="text" name="limit" value="<?php echo !empty( $rules['text_limit'] ) ? (int)$rules['text_limit'] : ''; ?>" placeholder="<?php echo __( 'Characters limit', 'lemonade_sna' ); ?>">
							</div>
							<small><?php echo __( "Limit of characters after which announce (if &lt;!--more--&gt tag was not found) should be cuted. The plugin would try to find the last whole word after which to insert a break. Leave empty if do not want to cut (Pinterest will use the maximum possible amount of symbols).", 'lemonade_sna' ); ?></small>
							<h5><?php echo __( 'Split words','lemonade_sna' ); ?></h5>
							<?php
							$checked = !empty( $rules ) ? ( !empty( $rules['split'] ) ? 'checked="checked"' : '' ) : 'checked="checked"';
							?>
							<div>
								<span><?php echo __( 'Off', 'lemonade_sna' ); ?></span>
								<label class="switch lsna-inc-ex">
									<input type="checkbox" value="1" name="split" <?php echo $checked; ?>>
									<span class="check"></span>							
								</label>
								<span><?php echo __( 'On', 'lemonade_sna' ); ?></span>
							</div>
							<small><?php echo __( 'If "Split words" option is switched off, then content for such shortcodes, like "%tags%" or "%categories%", which has more than one word, will be like "OneSingleBigTagFromSeveralWords".','lemonade_sna' ); ?></small>
							<?php
							$content = ob_get_contents();
							ob_get_clean();
							
							/**
							 * Filters Pin template fields output.
							 *
							 * @since 1.0.0
							 *
							 * @param string $content Html to filter.
							 */							 
							echo apply_filters( 'lemonade_sna_pinterest_pin_template_template_filter', $content );
							?>
						</div>
					</div>
				</div>
				<p></p>
				<p>
					<input type="submit" class="button button-primary" value="<?php echo __( 'Save template', 'lemonade_sna' ); ?>">
					<?php if( $editmode ) : ?>
						<a class="button bg-cyan fg-white lsna-cancel-edit" href="<?php echo remove_query_arg( array( 'edit_template', 'template_id' ) ); ?>"><i class="mif-cancel"></i> <?php echo __( 'Cancel edit', 'lemonade_sna' ); ?></a>
					<?php endif; ?>				
				</p>
			</form>
		</section>
		<?php if( $editmode ) : //Test template section
		ob_start();
		?>
		<section>
			<h4><?php echo __( 'Test template', 'lemonade_sna' ); ?></h4>
			<input type="hidden" id="test_pin_template_rules" value='<?php echo wp_json_encode( $rules ); ?>'>
			<div class="input-control text">
				<input type="text" name="test_pin_template" id="test_pin_template" value="" placeholder="<?php echo __( 'Insert ID', 'lemonade_sna' ); ?>" onchange="lemonade_sna_test_pin_tmpl_permalink(this);">
			</div>
			<small><?php echo __( 'To test a template insert here an ID of any publication.', 'lemonade_sna' ); ?></small>
			<div id="lsna-pin-template-test-result" class="lsna-invisible">
				<h5><b><?php echo __( 'Pin image', 'lemonade_sna' ); ?></b></h5>
				<div id="lsna-pin-template-test-image-link">			
				</div>
				<h5><b><?php echo __( 'Pin link', 'lemonade_sna' ); ?></b></h5>
				<div id="lsna-pin-template-test-link">			
				</div>
				<h5><b><?php echo __( 'Pin note', 'lemonade_sna' ); ?></b></h5>	
				<div id="lsna-pin-template-test-note">
				</div>	
			</div>	
		</section>
		<?php
		$html = ob_get_contents();
		if( $html ) {
			ob_end_clean();
		}
		
		/**
		 * Filters Test Pin Template section html on Pin template settings page.
		 *
		 * @since 1.0.0
		 *
		 * @param string $html Html to filter.
		 */
		echo apply_filters( 'lemonade_sna_pinterest_test_pin_template_html', $html );
		endif; 
		//List of templates
		ob_start();
		?>
		<section>
			<h4><?php echo __( 'List of templates', 'lemonade_sna' ); ?></h4>
			<?php
			$templates = get_option( 'lemonade_sna_pinterest_pin_templates' );
			if( !empty( $templates ) ) {
				$i = 1;
			?>
			<div class="grid lsna-templates-grid">
				<?php foreach( $templates as $id => $template ) : 
					$rules = !empty( $template['rules'] ) ? $template['rules'] : array();
					if( ( $i % 3 ) != 0 ) : ?>
					<div class="row cells3">
					<?php endif; ?>
						<div class="cell">
							<div class="lsna-template-card">
								<div class="lsna-template-card-header <?php echo ( $editmode && (int)$_REQUEST['template_id'] == (int)$id ) ? 'lsna-template-editing' : '';?>">
									<h5><a href="<?php echo esc_url( add_query_arg( array( 'edit_template' => '1', 'template_id' => (int)$id ) ) ); ?>"><?php echo esc_attr( $template['title'] ); ?></a></h5>
									<?php if( !$editmode || $editmode && (int)$_REQUEST['template_id'] != (int)$id ) : ?>
									<div class="lsna-template-delete"><a class="lsna-delete" href="#" title="<?php echo __( 'Delete template', 'lemonade_sna' ); ?>" onclick="lemonade_sna_delete_pin_template_dialog(event,this,<?php echo (int)$id; ?>)" data-title="<?php echo esc_attr( $template['title'] ); ?>"><span class="mif-cross text-shadow"></span></a></div>
									<?php endif; ?>
								</div>
								<div class="lsna-template-card-inside">
									<p>
										<b><?php echo __( 'Pin image', 'lemonade_sna' ); ?></b><br>
										<?php echo !empty( $rules['pin_image'] ) ? esc_attr( $rules['pin_image'] ) : ''; ?>
									</p>
									<p>
										<b><?php echo __( 'Pin link', 'lemonade_sna' ); ?></b><br>
										<?php echo !empty( $rules['pin_link'] ) ? esc_attr( $rules['pin_link'] ) : ''; ?>
									</p>		
									<p>
										<b><?php echo __( 'Pin note', 'lemonade_sna' ); ?></b><br>
										<?php echo !empty( $rules['pin_note'] ) ? esc_attr( $rules['pin_note'] ) : ''; ?>
									</p>									
								</div>
								<div class="lsna-template-card-footer">
									<span class="text-secondary"><?php echo __( 'Separator', 'lemonade_sna' ); ?></span>
									<?php
									if( !empty( $rules['tags_sep'] ) ) {
										?>
										<span class="tag">
										<?php
										echo esc_attr( $rules['tags_sep'] );
										?>
										</span>
										<?php
									}
									?>
									<span class="text-secondary"><?php echo __( 'Text limit', 'lemonade_sna' ); ?></span>
									<?php
									if( !empty( $rules['text_limit'] ) ) {
										?>
										<span class="tag">
										<?php echo (int)$rules['text_limit']; ?>
										</span>
										<?php
									}
									?>
									<span class="text-secondary"><?php echo __( 'Split', 'lemonade_sna' ); ?></span>
									<?php
									if( !empty( $rules['split'] ) ) {
									?>
										<span class="tag">
										<?php echo $rules['split'] == 1 ? __( 'ON', 'lemonade_sna' ) : __( 'OFF', 'lemonade_sna' ); ?>
										</span>								
									<?php
									}
									if( !empty( $rules['post_types'] ) ) {
										$pt_keys = array_keys( $rules['post_types'] );									
										?>
										<span class="text-secondary"><?php echo __( 'Post types', 'lemonade_sna' ); ?></span>
										<?php
										if( !empty( $pt_keys ) ) {
											foreach( $pt_keys as $pt_key ) {
												?>
												<span class="tag">
												<?php
												echo ucfirst( sanitize_text_field( $pt_key ) );
												if( !empty( $rules['post_types'][$pt_key]['category']['terms'] ) ) {
													echo ': ';
													$list_cats = '';
													foreach( $rules['post_types'][$pt_key]['category']['terms'] as $cat_id ) {
														$category = sanitize_text_field( get_cat_name( (int)$cat_id ) );
														if( !empty( $category ) ) {
															$list_cats .= $category . ', ';
														}
													}
													$list_cats = trim( $list_cats, ', ' );
													echo $list_cats;
												}
												?>
												</span>
												<?php
											}
										}
									}
									?>
								</div>
								<div class="lsna-template-card-footer bottom-shadow">
								<?php
								if( !empty( $template['date_created'] ) ) {
									$timezone_str = get_option( 'timezone_string' );
									if( empty( $timezone_str ) ) {
										$timezone = get_option( 'gmt_offset' );
										$timezone_str = 'GMT+' . $timezone;
									}
									if( get_option( 'date_format' ) != '' ) {
										$date_format = get_option( 'date_format' );
									} else {
										$date_format = 'Y-m-d';
									}
									if( get_option( 'time_format' ) != '' ) {
										$time_format = get_option( 'time_format' );
									} else {
										$time_format = 'H:i:s';
									}
									$formatted_date = lemonade_sna_pinterest_get_date_or_time_with_timezone( sanitize_text_field( $template['date_created'] ), $date_format, $timezone );
									$formatted_time = lemonade_sna_pinterest_get_date_or_time_with_timezone( sanitize_text_field( $template['date_created'] ), $time_format, $timezone );;									
									?>
									<div class="text-secondary">											
										<span class="mif-calendar" style="vertical-align: top;"></span>
										<span class="lsna-pin-template-created" data-when="<?php echo esc_attr( $template['date_created'] ); ?>"><?php
										echo $formatted_date . ' ' . __( 'at', 'lemonade_sna' ) . ' ' . $formatted_time . ' ' . __( 'by', 'lemonade_sna' ) . ' ' . ( !empty( $timezone_str ) ? $timezone_str : 'GMT' ) . ' (' . __( 'Site local time', 'lemonade_sna' ) . ')';
										?></span>
									</div>
									<?php
								}
								?>	
								</div>
							</div>
						</div>
					<?php if( ( $i % 3 ) == 0 || count( $templates ) == $i ) : ?>	
					</div>
					<?php endif;
					$i++;
					?>
				<?php endforeach; ?>
			</div>
			<?php
			} else {
				?>
				<p>
					<span class="mif-not"></span>
					<?php
					echo __( 'No Pin templates were found!', 'lemonade_sna' );
					?>
				</p>
			<?php	
			}
			?>
		</section>
		<?php
		$html = ob_get_contents();
		if( $html ) {
			ob_end_clean();
		}
		
		/**
		 * Filters Pin templates table html.
		 *
		 * @since 1.0.0
		 *
		 * @param string $html Html to filter.
		 */
		echo apply_filters( 'lemonade_sna_pinterest_pin_templates_table_html', $html );
		
		//Dialogs
		?>
		<div data-role="dialog" id="lsna-dialog-delete-pin-template" class="padding20 dialog warning" data-close-button="true" data-type="warning" data-width="300" data-overlay-click-close="true" data-overlay="true" data-overlay-color="op-grayDark">
			<h1 class="fg-white"><?php echo __( 'Template delete', 'lemonade_sna' ); ?></h1>
			<p>
			<?php echo __( "You are going to delete ", 'lemonade_sna' ) . '<span class="lsna-dialog-delete-template-title text-accent">a template</span>. ' . __( 'All the data will be removed. After that you will not be able to restore the template.', 'lemonade_sna' ); ?>
			</p>
			<p>
				<button class="button fg-white bg-darkRed bg-hover-crimson lsna-delete-template-button" onclick="lemonade_sna_delete_pin_template(this);" data-template-id="" data-backurl="<?php menu_page_url( 'lemonade_sna_pinterest3' ); ?>"><?php echo __( 'Delete', 'lemonade_sna' ); ?> <span class="lsna-delete-spinner lsna-invisible"><i class="mif-spinner mif-ani-spin"></i></span></button>
				<button class="button bg-white bg-hover-grayLighter" onclick="metroDialog.close('#lsna-dialog-delete-pin-template');"><?php echo __( 'Cancel', 'lemonade_sna' ); ?></button>
			</p>
		</div>	
		<div data-role="dialog" id="lsna-dialog-delete-pin-template-error" class="padding20 dialog alert" data-close-button="true" data-type="alert" data-width="300" data-overlay-click-close="true">
			<h1 class="fg-white"><?php echo __( 'Template delete error', 'lemonade_sna' ); ?></h1>
			<p>
			<?php echo __( 'An error occured while deleting the template', 'lemonade_sna' ); ?>
			</p>
		</div>
	</div>
	<?php
	unset( $_SESSION['lsna_error'] );
	unset( $_SESSION['lsna_success'] );	
}

/**
 * Creates Pinterest Autoposter Settings page html content.
 *
 * Html content for Pinterest Autoposter Settings page.
 *
 * @since 1.0.0
 * @since 2.0.0 Some data sanitization added. Card layout for list of templates instead of a table layout. 
 *
 * @see lemonade_sna_pinterest_get_autoposter_template()
 * @see lemonade_sna_pinterest_get_list_of_templates()
 */
function lemonade_sna_pinterest_admin_panel_autoposter() {
	?>
	<div class="wrap">
		<h1 class="lsna-admin-page-title"><?php echo __( 'Pinterest Autoposter Settings - Lemonade SNA Pinterest', 'lemonade_sna' ); ?></h1>
		<?php settings_errors(); ?>
		<?php
		if( isset( $_SESSION['lsna_error'] ) && !empty( $_SESSION['lsna_error'] ) ) {
			foreach( $_SESSION['lsna_error'] as $error ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo sanitize_text_field( $error ); ?></p>
			</div>
			<?php
			}
		}		
		if( isset( $_SESSION['lsna_success'] ) && !empty( $_SESSION['lsna_success'] ) ) {
			?>
			<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
				<p><?php echo sanitize_text_field( $_SESSION['lsna_success'] ); ?></p>
			</div>				
		<?php
		}
		$editmode = false;
		$rules = (object)[];		
		if( isset( $_REQUEST['edit_template'] ) && $_REQUEST['edit_template'] == 1 && !empty( $_REQUEST['template_id'] ) ) {
			$editmode = true;
			$ts = lemonade_sna_pinterest_get_autoposter_template( (int)$_REQUEST['template_id'] );
			$t = array();
			if( !empty( $ts ) ) {
				$t = $ts;
				$rules = json_decode( $t->rules );
			}
		}
		?>
		<section>
			<?php if( $editmode ) : ?>
				<h4><?php echo __( 'Edit Autoposter Template', 'lemonade_sna' ); ?></h4>
			<?php else: ?>
				<h4><?php echo __( 'Create Autoposter Template', 'lemonade_sna' ); ?></h4>
			<?php endif; ?>
			<form id="lsna-pinterest-autoposter-template-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
				<input type="hidden" value="lemonade_sna_pinterest_save_template" name="action">
				<?php wp_nonce_field( 'lsna-template' ); ?>
				<?php if( $editmode ) : ?>
				<input type="hidden" value="<?php echo (int)$_REQUEST['template_id']; ?>" name="edit_template">
				<?php endif; ?>
				<div class="input-control full-size text lsna-template-name" data-placeholder="<?php echo __( 'Create a name for a template', 'lemonade_sna' ); ?>">
					<input type="text" name="template-name" value="<?php echo !empty( $t ) ? esc_attr( $t->title ) : ''; ?>" placeholder="<?php echo __( 'Create a name for template', 'lemonade_sna' ); ?>" data-validate-hint="<?php echo __( 'The field can not be empty', 'lemonade_sna' ); ?>"/>
					<span class="input-state-error mif-warning"></span>
				</div>
				<div class="accordion" data-role="accordion">
					<div class="frame">
						<div class="heading"><?php echo esc_attr( __( 'Filter posts', 'lemonade_sna' ) ); ?></div>
						<div class="content">
							<?php ob_start(); ?>
							<h4><?php echo esc_attr( __( 'Choose Post Types', 'lemonade_sna' ) ); ?></h4>
							<div>
								<span><?php echo __( 'Exclude', 'lemonade_sna' ); ?></span>
								<label class="switch lsna-inc-ex">
									<?php $checked = isset( $rules->post_types->include ) ? 'checked="checked"' : ''; ?>
									<input type="checkbox" value="1" name="post_types[include]" <?php echo $checked; ?>>
									<span class="check"></span>							
								</label>
								<span><?php echo __( 'Include', 'lemonade_sna' ); ?></span>
							</div>
							<?php
							$post_types_v = ( !empty( $rules->post_types->selected ) && is_array( $rules->post_types->selected ) ) ? $rules->post_types->selected : array();
							?>	
							<div class="input-control full-size" data-role="select" data-multiple="true" data-placeholder="Select Post Types...">
								<select name="post_types[selected][]" multiple="multiple">
								<option value=""><?php echo '...'; ?></option>
								<?php
								$post_types = get_post_types( array( 'public' => true ), 'objects' );					
								if( !empty( $post_types ) ) {
									foreach( $post_types as $k => $t ) {
										$selected = '';
										if( in_array( $k, $post_types_v ) ) {
											$selected = 'selected="selected"';
										}
										?>
										<option value="<?php echo $k; ?>" <?php echo $selected; ?>><?php echo esc_attr( $t->label ); ?></option>
										<?php
									}	
								}
								?>
								</select>
							</div>
							<small><?php echo __( 'Leave the field above empty for to include all posts.', 'lemonade_sna' ); ?></small>
							<h4><?php echo __( 'Choose Post Formats', 'lemonade_sna' ); ?></h4>
							<div>
								<span><?php echo __( 'Exclude', 'lemonade_sna' ); ?></span>
								<label class="switch lsna-inc-ex">
									<?php $checked = isset( $rules->post_formats->include ) ? 'checked="checked"' : ''; ?>
									<input type="checkbox" value="1" name="post_formats[include]" <?php echo $checked; ?>>
									<span class="check"></span>							
								</label>
								<span><?php echo __( 'Include', 'lemonade_sna' ); ?></span>
							</div>					
							<div class="input-control full-size" data-role="select" data-multiple="true" data-placeholder="Select Post Formats...">
								<?php
								$formats = ['aside','gallery','link','image','quote','status','video','audio','chat'];
								$post_formats_v = ( !empty( $rules->post_formats->selected ) && is_array( $rules->post_formats->selected ) ) ? $rules->post_formats->selected : array();
								?>
								<select name="post_formats[selected][]" multiple="multiple">
								<option value=""><?php echo '...'; ?></option>
								<?php
								foreach( $formats as $f ) {
									$selected = '';
									if( in_array( $f, $post_formats_v ) ) {
										$selected = 'selected="selected"';
									}
									?>
									<option value="<?php echo $f; ?>" <?php echo $selected; ?>><?php echo ucfirst( esc_attr( $f ) ); ?></option>
									<?php
								}
								?>
								</select>
							</div>
							<small><?php echo __( 'Leave the field above empty for to include all posts.', 'lemonade_sna' ); ?></small>					
							<h4><?php echo __( 'Choose Categories', 'lemonade_sna' ); ?></h4>
							<div>
								<span><?php echo __( 'Exclude', 'lemonade_sna' ); ?></span>
								<label class="switch lsna-inc-ex">
									<?php $checked = isset( $rules->post_cats->include ) ? 'checked="checked"' : ''; ?>
									<input type="checkbox" value="1" name="post_cats[include]" <?php echo $checked; ?>>
									<span class="check"></span>							
								</label>
								<span><?php echo __( 'Include', 'lemonade_sna' ); ?></span>
							</div>	
							<?php
							$cats_v = ( !empty( $rules->post_cats->selected ) && is_array( $rules->post_cats->selected ) ) ? $rules->post_cats->selected : array();	
							?>	
							<div class="input-control full-size" data-role="select" data-multiple="true" data-placeholder="<?php echo __( 'Select Post Categories...', 'lemonade_sna' ); ?>">
								<select name="post_cats[selected][]" multiple="multiple">
									<option value=""><?php echo '...'; ?></option>
									<?php
									$cats = get_categories(  array( 'hide_empty' => false )  );
									if( !empty( $cats ) ) {
										foreach( $cats as $c ) { 
											$selected = '';
											if( in_array( $c->term_id, $cats_v ) ) {
												$selected = 'selected="selected"';
											}
											?>
											<option value="<?php echo (int)$c->term_id; ?>" <?php echo $selected; ?>><?php echo esc_attr( $c->name ); ?></option>
											<?php 
										} 
									}
									?>
								</select>
							</div>
							<small><?php echo __( 'Leave the field above empty for to include all posts.', 'lemonade_sna' ); ?></small>					
							<?php echo __( 'Relation: ', 'lemonade_sna' ); ?>
							<label class="input-control radio small-check">
								<input type="radio" name="post_cats[relation]" value="or" <?php echo !empty( $rules->post_cats->relation ) ? ( ( $rules->post_cats->relation == 'or' ) ? 'checked="checked"' : '' ) : 'checked="checked"'; ?>>
								<span class="check"></span>
								<span class="caption"><?php echo __( 'OR', 'lemonade_sna' ); ?></span>
							</label>
							<label class="input-control radio small-check">
								<input type="radio" name="post_cats[relation]" value="and" <?php echo !empty( $rules->post_cats->relation ) ? ( ( $rules->post_cats->relation == 'and' ) ? 'checked="checked"' : '' ) : ''; ?>>
								<span class="check"></span>
								<span class="caption"><?php echo __( 'AND', 'lemonade_sna' ); ?></span>
							</label><br>
							<small><?php echo __( 'Choose relation AND if you want to filter only that posts which fit all categories from a list, leave relation OR if you want to get posts which fit at least one category from a list.', 'lemonade_sna' ); ?></small>	
							<h4><?php echo esc_attr( __( 'Choose Tags', 'lemonade_sna' ) ); ?></h4>
							<div>
								<span><?php echo __( 'Exclude', 'lemonade_sna' ); ?></span>
								<label class="switch lsna-inc-ex">
									<?php $checked = isset( $rules->post_tags->include ) ? 'checked="checked"' : '';?>
									<input type="checkbox" value="1" name="post_tags[include]" <?php echo $checked; ?>>
									<span class="check"></span>							
								</label>
								<span><?php echo __( 'Include', 'lemonade_sna' ); ?></span>
							</div>
							<?php
							$post_tags_v = ( !empty( $rules->post_tags->selected ) && is_array( $rules->post_tags->selected ) ) ? $rules->post_tags->selected : array();
							?>	
							<div class="input-control full-size" data-role="select" data-multiple="true" data-placeholder="<?php echo __( 'Select Post Tags...', 'lemonade_sna' ); ?>">					
								<select name="post_tags[selected][]" multiple="multiple">
									<option value=""><?php echo '...'; ?></option>	
									<?php
									$tags = get_tags( array( 'hide_empty' => false ) );
									if( !empty( $tags ) ) {
										foreach( $tags as $t ) { 
											$selected = '';
											if( in_array( $t->term_id, $post_tags_v ) ) {
												$selected = 'selected="selected"';
											}
											?>
											<option value="<?php echo (int)$t->term_id; ?>" <?php echo $selected; ?>><?php echo esc_attr( $t->name ); ?></option>
											<?php 
										} 
									}
									?>
								</select>					
							</div>
							<small><?php echo __( 'Leave the field above empty for to include all posts.', 'lemonade_sna' ); ?></small>
							<?php echo __( 'Relation: ', 'lemonade_sna' ); ?>
							<label class="input-control radio small-check">
								<input type="radio" name="post_tags[relation]" value="or" <?php echo !empty( $rules->post_tags->relation ) ? ( ( $rules->post_tags->relation == 'or' ) ? 'checked="checked"' : '' ) : 'checked="checked"'; ?>>
								<span class="check"></span>
								<span class="caption"><?php echo __( 'OR', 'lemonade_sna' ); ?></span>
							</label>
							<label class="input-control radio small-check">
								<input type="radio" name="post_tags[relation]" value="and" <?php echo !empty( $rules->post_tags->relation ) ? ( ( $rules->post_tags->relation == 'and' ) ? 'checked="checked"' : '' ) : ''; ?>>
								<span class="check"></span>
								<span class="caption"><?php echo __( 'AND', 'lemonade_sna' ); ?></span>
							</label><br>
							<small><?php echo __( 'Choose relation AND if you want to filter only that posts which fit all tags from a list, leave relation OR if you want to get posts which fit at least one tag from a list.', 'lemonade_sna' ); ?></small>	
							<h4><?php echo esc_attr( __( 'Choose Authors', 'lemonade_sna' ) ); ?></h4>
							<div>
								<span><?php echo __( 'Exclude', 'lemonade_sna' ); ?></span>
								<label class="switch lsna-inc-ex">
									<?php $checked = isset( $rules->post_authors->include ) ? 'checked="checked"' : ''; ?>
									<input type="checkbox" value="1" name="post_authors[include]" <?php echo $checked; ?>>
									<span class="check"></span>							
								</label>
								<span><?php echo __( 'Include', 'lemonade_sna' ); ?></span>
							</div>
							<?php
							$post_authors_v = ( !empty( $rules->post_authors->selected ) && is_array( $rules->post_authors->selected ) ) ? $rules->post_authors->selected : array();
							?>
							<div class="input-control full-size" data-role="select" data-multiple="true" data-placeholder="<?php echo __( 'Select Post Authors...', 'lemonade_sna' ); ?>">
								<select name="post_authors[selected][]" multiple="multiple">					
									<option value=""><?php echo '...'; ?></option>
									<?php
									$users = get_users( array( 'who' => 'authors', 'orderby' => 'login' ) );
									if( !empty( $users ) ) {
										foreach( $users as $u ) {											
											$data = $u->data;
											$selected = '';
											if( in_array( $data->ID, $post_authors_v ) ) {
												$selected = 'selected="selected"';
											}
											?>
											<option value="<?php echo (int)$data->ID; ?>" <?php echo $selected; ?>><?php echo esc_attr( $data->user_login ) . ' : ' . esc_attr( $data->display_name ); ?></option>
											<?php
										}
									}
									?>
								</select>
							</div>	
							<small><?php echo __( 'Leave the field above empty for to include all posts.', 'lemonade_sna' ); ?></small>
							<h4><?php echo __( 'Choose Custom Meta', 'lemonade_sna' ); ?></h4>
							<div class="fg-blue"><span class="mif-notification"></span> <?php echo __( 'This filter is available only with PRO version.', 'lemonade_sna' ); ?></div>							
							<div class="lsna-custom-meta-wrapper">
								<div>
									<span><?php echo __( 'Exclude', 'lemonade_sna' ); ?></span>
									<label class="switch lsna-inc-ex">
										<input type="checkbox" value="" name="" disabled>
										<span class="check"></span>							
									</label>
									<span><?php echo __( 'Include', 'lemonade_sna' ); ?></span>
								</div>					
								<div class="input-control custom-meta select">
									<select name="" disabled>	
										<option value=""><?php echo '...'; ?></option>
									</select>
								</div>					
								<div class="input-control text">
									<input type="text" name="" value="" disabled>
								</div>
							</div>							
							<small><?php echo __( 'Leave the field above empty for to include all posts.', 'lemonade_sna' ); ?></small><br>
							<?php echo __( 'Relation: ', 'lemonade_sna' ); ?>
							<label class="input-control radio small-check">
								<input type="radio" name="" value="or" disabled>
								<span class="check"></span>
								<span class="caption"><?php echo __( 'OR', 'lemonade_sna' ); ?></span>
							</label>
							<label class="input-control radio small-check">
								<input type="radio" name="" value="and" disabled>
								<span class="check"></span>
								<span class="caption"><?php echo __( 'AND', 'lemonade_sna' ); ?></span>
							</label><br>
							<small><?php echo __( 'Choose relation AND if you want to filter only that posts which fit all custom metas from a list, leave relation OR if you want to get posts which fit at least one custom meta from a list.', 'lemonade_sna' ); ?></small>	
							<h4><?php echo __( 'Choose Custom Taxonomies', 'lemonade_sna' ); ?></h4>	
							<div class="fg-blue"><span class="mif-notification"></span> <?php echo __( 'This filter is available only with PRO version.', 'lemonade_sna' ); ?></div>
							<h5><?php echo __( 'Relation', 'lemonade_sna' );?></h5>
							<label class="input-control radio small-check">
								<input type="radio" name="tax_relation" value="AND" disabled>
								<span class="check"></span>
								<span class="caption"><?php echo __( 'AND', 'lemonade_sna' ); ?></span>
							</label>	
							<label class="input-control radio small-check">
								<input type="radio" name="tax_relation" value="OR" disabled>
								<span class="check"></span>
								<span class="caption"><?php echo __( 'OR', 'lemonade_sna' ); ?></span>
							</label><br>
							<small><?php echo __( 'Check OR if you want to include all posts which fit at least on of the conditions under to the filter or AND - for to include only that posts which fit all the conditions under', 'lemonade_sna' ); ?></small>
							<?php
							$taxonomies = get_taxonomies( array( 'public'   => true, '_builtin' => false ), 'object', 'and' );
							foreach( $taxonomies as $k => $tax ) { ?>						
								<?php
								$terms = get_terms( array( 'taxonomy' => $k ) );
								if( !empty( $terms ) ) {
									?>
									<h5><?php echo esc_attr( $tax->labels->name ); ?></h5>
									<div>
										<span><?php echo __( 'Exclude', 'lemonade_sna' ); ?></span>
										<label class="switch lsna-inc-ex">
											<input type="checkbox" value="1" name="" disabled>
											<span class="check"></span>							
										</label>
										<span><?php echo __( 'Include', 'lemonade_sna' ); ?></span>
									</div>	
									<div class="input-control full-size" data-role="select" data-multiple="true" data-placeholder="<?php echo __( 'Select', 'lemonade_sna' ) . ' ' . $tax->labels->name; ?>">										<select name="" disabled>
										<select>	
											<option value=""><?php echo '...'; ?></option>
										</select>
									</div>
									<small><?php echo __( 'Leave the field above empty for to include all posts.', 'lemonade_sna' ); ?></small>
									<div>
										<span><?php echo __( 'Relation: ', 'lemonade_sna' ); ?></span>
										<label class="input-control radio small-check">
											<input type="radio" name="" value="or" disabled>
											<span class="check"></span>
											<span class="caption"><?php echo __( 'OR', 'lemonade_sna' ); ?></span>
										</label>
										<label class="input-control radio small-check">
											<input type="radio" name="" value="and" disabled>
											<span class="check"></span>
											<span class="caption"><?php echo __( 'AND', 'lemonade_sna' ); ?></span>
										</label>
									</div>
									<small><?php echo __( 'Choose relation AND if you want to filter only that posts which fit all', 'lemonade_sna' ) . ' ' . __( $tax->labels->name, 'lemonade_sna' ) . ' ' . __( 'from a list, leave relation OR if you want to get posts which fit at least one', 'lemonade_sna' ) . ' ' . __( $tax->labels->name, 'lemonade_sna' ) . ' ' . __( 'from a list.', 'lemonade_sna' ); ?></small>									
									<?php
								}
							}
							
							/**
							 * Fires after Custom Taxonomies posts filter 
							 * in the Autoposter Pinterest Settings Posts Filter section.
							 *
							 * @since 1.0.0
							 *
							 * @param object $rules Rules object.
							 */							
							do_action( 'lemonade_sna_pinterest_after_custom_taxonomies_posts_filter', $rules );
							?>
							<h4><?php echo __( 'Dates', 'lemonade_sna' ); ?></h4>
							<?php echo __( 'From: ', 'lemonade_sna' ); ?>
							<div class="input-control text" data-role="datepicker" data-week-start="1" <?php echo !empty( $rules->dates_filter->from ) ? 'data-preset="' . esc_attr( $rules->dates_filter->from ). '"' : ''; ?> data-format="mmmm d, yyyy">
								<input type="text" name="dates_filter[from]">
								<button class="button"><span class="mif-calendar"></span></button>
							</div>
							<div class="input-control">
								<div class="clear-date" style="line-height:2;" onclick="lemonade_sna_pinterest_clear_dates(this);"><span class="mif-bin"></span></div>
							</div>	
							<?php echo __( 'to: ', 'lemonade_sna' ); ?>
							<div class="input-control text" data-role="datepicker" <?php echo !empty( $rules->dates_filter->to ) ? 'data-preset="' . esc_attr( $rules->dates_filter->to ) . '"' : ''; ?> data-format="mmmm d, yyyy">
								<input type="text" name="dates_filter[to]">
								<button class="button"><span class="mif-calendar"></span></button>
							</div>
							<div class="input-control">
								<div class="clear-date" style="line-height:2;" onclick="lemonade_sna_pinterest_clear_dates(this);"><span class="mif-bin"></span></div>
							</div>							
							<br>
							<small><?php echo __( 'Choose dates range if you need or leave the fields above empty.', 'lemonade_sna' ); ?></small>	
							<h4><?php echo __( 'Posts without featured images', 'lemonade_sna' ); ?></h4>
							<div>
								<span><?php echo __( 'Exclude', 'lemonade_sna' ); ?></span>
								<label class="switch lsna-inc-ex">
									<?php $checked = !empty( $rules->image_default ) ? 'checked="checked"' : ''; ?>
									<input type="checkbox" value="1" name="featured_images[checked]" <?php echo $checked; ?>>
									<span class="check"></span>							
								</label>
								<span><?php echo __( 'Include', 'lemonade_sna' ); ?></span>
							</div>
							<div class="input-control text full-size">
								<input type="text" name="featured_images[default]" value="<?php echo !empty( $rules->image_default ) ? esc_attr( $rules->image_default ) : ''; ?>">
							</div>							
							<small><?php echo __( 'If a post has no featured image, you can exclude it from list or set up a default image for it.', 'lemonade_sna' ); ?></small>
							<h4><?php echo __( 'Duplicate posts', 'lemonade_sna' ); ?></h4>
							<?php echo __( 'It happens that a publication is posted on the same Pinterest board several times. What should Autoposter do if it meets a post which is already published on a board?', 'lemonade_sna' ); ?><br>
							<label class="input-control radio small-check">
								<input type="radio" name="duplicate" value="no" <?php echo !empty( $rules->duplicate ) ? ( ( $rules->duplicate == 'no' ) ? 'checked="checked"' : '' ) : 'checked="checked"'; ?>>
								<span class="check"></span>
								<span class="caption"><?php echo __( 'Do not post', 'lemonade_sna' ); ?></span>
							</label>							
							<label class="input-control radio small-check">
								<input type="radio" name="duplicate" value="yes" <?php echo !empty( $rules->duplicate ) ? ( ( $rules->duplicate == 'yes' ) ? 'checked="checked"' : '' ) : ''; ?>>
								<span class="check"></span>
								<span class="caption"><?php echo __( 'Post nevertheless', 'lemonade_sna' ); ?></span>
							</label><br>
							<small><?php echo __( 'Choose an option.', 'lemonade_sna' ); ?></small>	
							<?php 
							$echo = ob_get_contents();
							if( $echo ) {
								ob_end_clean();
							}
							
							/**
							 * Filter for the main content of the Pinterest Autoposter Settings Filter Posts section.
							 *
							 * @since 1.0.0
							 *
							 * @param string $echo The html content. 
							 * @param object $rules Rules object.
							 */							
							echo apply_filters( 'lemonade_sna_pinterest_filter_posts_section_content', $echo, $rules );
							
							/**
							 * Fires after main content in the Pinterest Autoposter Settings Filter Posts section.
							 *
							 * @since 1.0.0
							 *
							 * @param object $rules Rules object.
							 */ 							 
							do_action( 'lemonade_sna_pinterest_after_filter_posts_section', $rules );
							?>
						</div>					
					</div>
					<div class="frame">
						<div class="heading"><?php echo __( 'WP Cron Schedule', 'lemonade_sna' ); ?></div>
						<div class="content">
							<?php ob_start(); ?>
							<h4><?php echo esc_attr( __( 'Frequency', 'lemonade_sna' ) ); ?></h4>
							<span><?php echo __( 'Do not publish more than one post every', 'lemonade_sna' ); ?></span>
							<?php
							$empty_freq = ( empty( $rules->freq->days ) && empty( $rules->freq->hours ) && empty( $rules->freq->minutes ) ) ? true : false;
							?>
							<div class="input-control text input-number days">
								<input type="text" name="freq[days]" value="<?php echo ( !empty( $rules->freq->days ) && lemonade_sna_pinterest_validate_interval( 'days', (int)$rules->freq->days ) ) ? (int)$rules->freq->days : ''; ?>" placeholder="0" data-validate-hint="<?php echo __( 'Days should be positive integer.', 'lemonade_sna' ); ?>"/>
								<span class="input-state-error mif-warning"></span>
							</div><span> <?php echo __( 'days', 'lemonade_sna' ); ?></span>	
							<div class="input-control text input-number hours">
								<input type="text" name="freq[hours]" value="<?php echo ( !empty( $rules->freq->hours ) && lemonade_sna_pinterest_validate_interval( 'hours', (int)$rules->freq->hours ) ) ? (int)$rules->freq->hours : ( $empty_freq ? 1 : '' ); ?>" placeholder="0" data-validate-hint="<?php echo __( 'Hours should be positive integer not more than 24.', 'lemonade_sna' ); ?>"/>
								<span class="input-state-error mif-warning"></span>
							</div><span> <?php echo __( 'hours', 'lemonade_sna' ); ?></span>	
							<div class="input-control text input-number minutes">	
								<input type="text" name="freq[minutes]" value="<?php echo ( !empty( $rules->freq->minutes ) && lemonade_sna_pinterest_validate_interval( 'minutes', (int)$rules->freq->minutes ) ) ? (int)$rules->freq->minutes : ''; ?>" placeholder="0" data-validate-hint="<?php echo __( 'Minutes should be positive integer not more than 60.', 'lemonade_sna' ); ?>"/>
								<span class="input-state-error mif-warning"></span>
							</div><span> <?php echo __( 'minutes', 'lemonade_sna' ); ?></span><br/>
							<small><?php echo __( 'Low frequency is recommended for busy sites.', 'lemonade_sna' ); ?></small><br/>
							<h4><?php echo __( 'Post at special time', 'lemonade_sna' ); ?></h4>
							<div>
								<?php
								$time_zones = timezone_identifiers_list();
								?>
								<span><?php echo __( 'Time zone', 'lemonade_sna' ); ?></span>
								<div class="input-control time-zone" data-role="select">
									<select name="time-zone">
									<?php
									foreach( $time_zones as $zone ) {
										$selected = !empty( $rules->time_zone ) ? ( ( $rules->time_zone == $zone ) ? 'selected="selected"' : '' ) : ( ( $zone == 'UTC' ) ? 'selected="selected"' : '' );
										?>	
										<option value="<?php echo esc_attr( $zone ); ?>" <?php echo $selected; ?>><?php echo esc_attr( $zone ); ?></option>
										<?php
									}
									?>
									</select>
								</div>
								<small><?php echo __( 'All dates and time will be taken for given timezone.', 'lemonade_sna' ); ?></small>	
							</div>
							<div class="fg-blue"><span class="mif-notification"></span> <?php echo __( 'This filter is available only with PRO version.', 'lemonade_sna' ); ?></div>							
							<div>
								<span><?php echo __( 'From', 'lemonade_sna' ); ?></span>
								<div class="input-control select">
									<select name="" disabled>
									</select>
								</div>
								<span><?php echo __( 'to', 'lemonade_sna' ); ?></span>
								<div class="input-control select">
									<select name="" disabled>
									</select>
								</div>	
							</div>
							<small><?php echo __( 'Choose time interval when to make posting.', 'lemonade_sna' ); ?></small>
							<div>
								<label class="input-control radio small-check">
									<input type="radio" name="" value="daily" disabled>
									<span class="check"></span>
									<span class="caption"><?php echo __( 'On each day', 'lemonade_sna' ); ?></span>
								</label>
							</div>
							<small><?php echo __( 'Posting will be made on each day.', 'lemonade_sna' ); ?></small>
							<div>	
								<label class="input-control radio small-check">
									<input type="radio" name="" value="weekdays" disabled>
									<span class="check"></span>
									<span class="caption"><?php echo __( 'On these weekdays', 'lemonade_sna' ); ?></span>
								</label>
							</div>
							<div class="input-control full-size select" data-placeholder="<?php echo __( 'Select Days...', 'lemonade_sna' ); ?>">
								<select name="" disabled>
									<option value=""><?php echo '...'; ?></option>
								</select>
							</div>
							<small><?php echo __( 'Posting will be made only on these weekdays.', 'lemonade_sna' ); ?></small>	
							<div>
								<label class="input-control radio small-check">
									<input type="radio" name="" value="dates" disabled>
									<span class="check"></span>
									<span class="caption"><?php echo __( 'On these dates', 'lemonade_sna' ); ?></span>
								</label>
							</div>
							<input type="hidden" value="">
							<div style="width: 220px;">
								<div id="special-calendar" class="white calendar" data-role="calendar" data-week-start="1" data-multi-select="true" data-day-click="">
								</div>	
							</div>
							<small><?php echo __( 'Posting will be made only on chosen dates.', 'lemonade_sna' ); ?></small>
							<h4><?php echo __( 'Day limits', 'lemonade_sna' ); ?></h4>
							<?php echo __( 'Do not publish more than', 'lemonade_sna' ) . ' '; ?>
							<div class="input-control text input-number limit">
								<input type="text" name="posts_limit" value="<?php echo !empty( $rules->posts_limit ) && lemonade_sna_pinterest_validate_interval( 'limit', (int)$rules->posts_limit ) ? (int)$rules->posts_limit : ''; ?>" placeholder="0" data-validate-hint="<?php echo __( 'Number of posts should be positive integer.', 'lemonade_sna' ); ?>"/>
								<span class="input-state-error mif-warning"></span>
							</div>
							<?php echo __( 'posts per day', 'lemonade_sna' ); ?><br/>
							<small><?php echo __( 'Autoposter will stop when it reaches a day limit.', 'lemonade_sna' ); ?></small><br/><?php echo __( 'Publications which are over day limit will be posted on the next available day.', 'lemonade_sna' ); ?></small>
							<h4><?php echo __( 'Boards', 'lemonade_sna' ); ?></h4>
							<div class="fg-blue"><span class="mif-notification"></span> <?php echo __( 'This free version of plugin allows to choose only one board. With PRO version you can choose unlimited number of boards.', 'lemonade_sna' ); ?></div>							
							<?php
							
							/**
							 * Shows user's Pinterest boards using DirkGroenen Pinterest API wrapper
							 */							 
							include( LEMONADE_SNA_PINTEREST_EXT_PATH . '/tiles/lemonade_sna_pinterest_show_boards.php' );
							
							$boards_v = ( !empty( $rules->boards ) && is_array( $rules->boards ) ) ? $rules->boards : array();
							?>
							<div class="input-control full-size lsna-template-boards" data-role="select" data-placeholder="<?php echo __( 'Select Boards...', 'lemonade_sna' ); ?>">
								<select name="boards" data-validate-hint="<?php echo __( 'Select at least one board where to publish.', 'lemonade_sna' ); ?>">
									<option value="">...</option>
									<?php
									if( !empty( $user_boards ) ) {
										foreach( $user_boards as $board ) {
											$selected = '';
											if( in_array( $board->id, $boards_v ) ) {
												$selected = 'selected="selected"';
											}
											if( lemonade_sna_pinterest_numbers_only( $board->id ) ) {
												?>
												<option value="<?php echo $board->id; ?>" <?php echo $selected; ?>><?php echo esc_attr( $board->name ); ?></option>
												<?php
											}
										}
									} else {
										foreach( $boards_v as $board_id ) {											
											if( lemonade_sna_pinterest_numbers_only( $board->id ) ) {
												?>
												<option value="<?php echo $board_id; ?>" selected="selected"><?php echo $board_id; ?></option>
												<?php
											}
										}
									}
									?>
								</select>
								<span class="input-state-error mif-warning"></span>
							</div>
							<small><?php echo __( 'Select boards where you would like to publish posts. It is not recommended to use more than 2-3.', 'lemonade_sna' ); ?></small>
							<h4><?php echo __( 'Order to publish', 'lemonade_sna' ); ?></h4>
							<div>
								<label class="input-control radio small-check">
									<input type="radio" name="publish_order" value="new_to_old" <?php echo !empty( $rules->publish_order ) ? ( ( $rules->publish_order == 'new_to_old' ) ? 'checked="checked"' : '' ) : 'checked="checked"'; ?>>
									<span class="check"></span>
									<span class="caption"><?php echo __( 'First new, then old', 'lemonade_sna' ); ?></span>
								</label>
							</div>
							<small><?php echo __( 'Autoposter will every time publish the oldest post in a list', 'lemonade_sna' ); ?></small>					
							<div>
								<label class="input-control radio small-check">
									<input type="radio" name="publish_order" value="old_to_new" <?php echo !empty( $rules->publish_order ) ? ( ( $rules->publish_order == 'old_to_new' ? 'checked="checked"' : '' ) ) : ''; ?>>
									<span class="check"></span>
									<span class="caption"><?php echo __( 'First old, then new', 'lemonade_sna' ); ?></span>
								</label>
							</div>
							<small><?php echo __( 'Autoposter will every time choose the newest post in a list to publish', 'lemonade_sna' ); ?></small>
							<div>
								<label class="input-control radio small-check">
									<input type="radio" name="publish_order" value="random" <?php echo !empty( $rules->publish_order ) ? ( ( $rules->publish_order == 'random' ? 'checked="checked"' : '' ) ) : ''; ?>>
									<span class="check"></span>
									<span class="caption"><?php echo __( 'Random', 'lemonade_sna' ); ?></span>
								</label>
							</div>
							<small><?php echo __( 'Autoposter will publish posts in random order', 'lemonade_sna' ); ?></small>						
							<h4><?php echo __( 'Simultaneous posting', 'lemonade_sna' ); ?></h4>
							<span><?php echo __( 'If you have several Pinterest boards in a list, Autoposter can work with them on different ways.', 'lemonade_sna' ); ?></span>
							<div>
								<label class="input-control radio small-check">
									<input type="radio" name="simultaneous" value="simultaneous" <?php echo !empty( $rules->simultaneous ) ? ( ( $rules->simultaneous == 'simultaneous' ? 'checked="checked"' : '' ) ) : 'checked="checked"'; ?>>
									<span class="check"></span>
									<span class="caption"><?php echo __( 'Post simultaneously', 'lemonade_sna' ); ?></span>
								</label>
							</div>						
							<small><?php echo __( 'Publication will be posted on all boards from a list simultaneously. For example, you want to pin a post on boards 1, 2, 3. Autoposter will send it to these boards at one time.', 'lemonade_sna' ); ?></small>
							<div>
								<label class="input-control radio small-check">
									<input type="radio" name="simultaneous" value="step" <?php echo !empty( $rules->simultaneous ) ? ( ( $rules->simultaneous == 'step' ? 'checked="checked"' : '' ) ) : ''; ?>>
									<span class="check"></span>
									<span class="caption"><?php echo __( 'Post step-by-step', 'lemonade_sna' ); ?></span>
								</label>
							</div>
							<small><?php echo __( 'Autoposter will pin a publication on one board each time. For example, you want to publish a post on boards 1, 2, 3. First time autoposter will send it to board 1, next time - to board 2 and the last time - to board 3.', 'lemonade_sna' ); ?></small>
							<div>
								<label class="input-control radio small-check">
									<input type="radio" name="simultaneous" value="randomize" <?php echo !empty( $rules->simultaneous ) ? ( ( $rules->simultaneous == 'randomize' ? 'checked="checked"' : '' ) ) : ''; ?>>
									<span class="check"></span>
									<span class="caption"><?php echo __( 'Randomize boards', 'lemonade_sna' ); ?></span>
								</label>
							</div>							
							<small><?php echo __( 'Each time Autoposter will choose just one board from a list which to publish on. Next time it will go to next post from a list. Finelly all publications will be pinned on all boards. For example, you want to pin a post on boards 1, 2, 3. First time Autoposter will pin a post to board 2. Next time when it meets with the same publication it will post it on board 1 and etc.', 'lemonade_sna' ); ?></small>
							<h4><?php echo __( 'When a cycle is finished', 'lemonade_sna' ); ?></h4>	
							<div>
								<label class="input-control radio small-check">
									<input type="radio" name="when_finished" value="wait_new" <?php echo !empty( $rules->when_finished ) ? ( ( $rules->when_finished == 'wait_new' ) ? 'checked="checked"' : '' ) : 'checked="checked"'; ?>>
									<span class="check"></span>
									<span class="caption"><?php echo __( 'Wait for new posts', 'lemonade_sna' ); ?></span>
								</label>
							</div>	
							<small><?php echo __( 'Autoposter will continue to work as usual only when there will be new publications on a list', 'lemonade_sna' ); ?></small>							
							<div>
								<label class="input-control radio small-check">
									<input type="radio" name="when_finished" value="start_new" <?php echo !empty( $rules->when_finished ) ? ( ( $rules->when_finished == 'start_new' ? 'checked="checked"' : '' ) ) : ''; ?>>
									<span class="check"></span>
									<span class="caption"><?php echo __( 'Start new cycle', 'lemonade_sna' ); ?></span>
								</label>
							</div>
							<small><?php echo __( 'Some posts could be published several times in a loop', 'lemonade_sna' ); ?></small>	
							<div>
								<label class="input-control radio small-check">
									<input type="radio" name="when_finished" value="stop_work" <?php echo !empty( $rules->when_finished ) ? ( ( $rules->when_finished == 'stop_work' ) ? 'checked="checked"' : '' ) : ''; ?>>
									<span class="check"></span>
									<span class="caption"><?php echo __( 'Finish autoposter', 'lemonade_sna' ); ?></span>
								</label>
							</div>
							<small><?php echo __( 'Autoposter will be deactivated', 'lemonade_sna' ); ?></small>
							<h4><?php echo __( 'Template update', 'lemonade_sna' ); ?></h4>		
							<span><?php echo __( 'Once per', 'lemonade_sna' ); ?></span>
							<?php
							$default = false;
							if( empty( $rules->update->days ) && empty( $rules->update->hours ) && empty( $rules->update->minutes ) ) {
								$default = true;
							}
							?>
							<div class="input-control text input-number days">
								<input type="text" name="update[days]" value="<?php echo ( !empty( $rules->update->days ) && lemonade_sna_pinterest_validate_interval( 'days', (int)$rules->update->days ) ) ? (int)$rules->update->days : ''; ?>" placeholder="0" data-validate-hint="<?php echo __( 'Days should be positive integer.', 'lemonade_sna' ); ?>"/>
								<span class="input-state-error mif-warning"></span>	
							</div><span> <?php echo __( 'days', 'lemonade_sna' ); ?></span>	
							<div class="input-control text input-number hours">
								<input type="text" name="update[hours]" value="<?php echo ( !empty( $rules->update->hours ) && lemonade_sna_pinterest_validate_interval( 'hours', (int)$rules->update->hours ) ) ? (int)$rules->update->hours : ( $default ? 24 : '' ); ?>" placeholder="0" data-validate-hint="<?php echo __( 'Hours should be positive integer not more than 24.', 'lemonade_sna' ); ?>"/>
								<span class="input-state-error mif-warning"></span>
							</div><span> <?php echo __( 'hours', 'lemonade_sna' ); ?></span>	
							<div class="input-control text input-number minutes">	
								<input type="text" name="update[minutes]" value="<?php echo ( !empty( $rules->update->minutes ) && lemonade_sna_pinterest_validate_interval( 'minutes', (int)$rules->update->minutes ) ) ? (int)$rules->update->minutes : ''; ?>" placeholder="0" data-validate-hint="<?php echo __( 'Minutes should be positive integer not more than 60.', 'lemonade_sna' ); ?>"/>
								<span class="input-state-error mif-warning"></span>
							</div><span> <?php echo __( 'minutes', 'lemonade_sna' ); ?></span><br/>	
							<small><?php echo __( 'How often to make scanning for to add new posts on a list to publish. Low frequency is recommended for busy sites.', 'lemonade_sna' ); ?></small>	
							<?php
							$html = ob_get_contents();
							if( $html ) {
								ob_end_clean();
							}
							
							/**
							 * Filters Autoposter schedule settings section html.
							 *
							 * @since 1.0.0
							 *
							 * @param string $html Html to filter.
							 * @param object $rules Rules of template if it is Edit mode.
							 */
							echo apply_filters( 'lemonade_sna_pinterest_autoposter_schedule_section_content', $html, $rules );
							?>
						</div>
					</div>
				</div>
				<p></p>			
				<p>
					<input type="submit" class="button button-primary" value="<?php echo __( 'Save template', 'lemonade_sna' ); ?>" onclick="lemonade_sna_pinterest_validate_autoposter_template_form();">
					<?php if( $editmode ) : ?>
					<button class="button bg-mauve fg-white" name="start_new_cycle">
						<i class="mif-redo"></i> <?php echo __( 'Start new cycle', 'lemonade_sna' ); ?>
					</button>
					<?php endif; ?>
					<?php if( $editmode ) : ?>
						<a class="button bg-cyan fg-white lsna-cancel-edit" href="<?php echo remove_query_arg( array( 'edit_template', 'template_id' ) ); ?>"><i class="mif-cancel"></i> <?php echo __( 'Cancel edit', 'lemonade_sna' ); ?></a>
					<?php endif; ?>
				</p>
			</form>	
		</section>	
		<?php if( $editmode ) : 
		ob_start();?>
		<section>
			<h4><?php echo __( 'Posts to be published', 'lemonade_sna' ); ?></h4>
			<?php
			$lines = get_option( 'lemonade_sna_pinterest_autoposter_lines' );
			$line = !empty( $lines[(int)$_REQUEST['template_id']] ) ? $lines[(int)$_REQUEST['template_id']] : array();
			$to_post = !empty( $line['line'] ) ? $line['line'] : array();	
				if( !empty( $to_post ) && is_array( $to_post ) ) {
					?>
					<div class="lsna-line-list-wrapper">
						<ul class="lsna-lin-list">
							<?php 
							foreach( $to_post as $tp ) : 
								foreach( $tp as $p_id => $b_id ) :
									$post_type = get_post_type( $p_id );
									$post_type_obj = get_post_type_object( $post_type );
									$post_type_name = sanitize_text_field( $post_type_obj->labels->singular_name );
									$post_type_icon = sanitize_text_field( $post_type_obj->menu_icon );									
									$b_name = $b_id;
									if( !empty( $user_boards ) && is_array( $user_boards ) ) {
										foreach( $user_boards as $board ) {
											if( $b_id == $board->id ) {
												$b_name = sanitize_text_field( $board->name );
												$b_url = esc_url( $board->url );
											}
										}
									}								
									?>
									<li class="bg-white padding10 lsna-line-el">
										<div class="clear-float">
											<div class="place-left"><?php echo __( 'Post', 'lemonade_sna' ) . ' <a href="' .get_permalink( $p_id ) . ' " target="_blank">' . get_the_title( $p_id ) . '</a> (Post ID:' . (int)$p_id . ') ' . __( 'will be published', 'lemonade_sna' ) . ' ' . __( 'on board', 'lemonade_sna' ) . ' ' . ( !empty( $b_url ) ? '<a href="' . esc_url( $b_url ) . '" target="_blank">' : '' ) . $b_name . ( !empty( $b_url ) ? '</a>' : '' ); ?></div>
											<div class="place-left">
												<div><small><?php echo !empty( $post_type_name ) ? __( 'Post type', 'lemonade_sna' ) . ': ' . ( !empty( $post_type_icon ) ? '<img src="' . $post_type_icon . '">' : '' ) . $post_type_name : ''; ?></small></div>
											</div>
										</div>
									</li>
									<?php 
								endforeach;
							endforeach; 
							?>
						</ul>
					</div>
					<?php
				} else {
					echo '<p>' . __( 'There are no posts to be published at the moment', 'lemonade_sna' ) . '...</p>';
				}
			endif;
			?>
		</section>
		<?php
		$html = ob_get_contents();
		if( $html ) {
			ob_end_clean();
		}
		
		$templ_id = !empty( $_REQUEST['template_id'] ) ? (int)$_REQUEST['template_id'] : '';
		
		/**
		 * Filters line preview section html.
		 *
		 * @since 1.0.0
		 *
		 * @param string $html Html to filter.
		 * @param int $_REQUEST['template_id'] ID of template.
		 */
		echo apply_filters( 'lemonade_sna_pinterest_autoposter_line_section_html', $html, $templ_id );
		
		$templates = lemonade_sna_pinterest_get_list_of_templates( 'pinterest' );	
		ob_start();
		?>
		<section>
			<h4><?php echo __( 'List of templates', 'lemonade_sna' ); ?></h4>
			<?php
			if( !empty( $templates ) ) {
				$i = 1;
				?>
				<div class="grid lsna-templates-grid">
				<?php
				foreach( $templates as $template ) {
					$id = (int)$template->id;
					if( ( $i % 3 ) == 1 || $i == 1 ) {
						?>
						<div class="row cells3">
						<?php
					}
					?>
					<div class="cell">
						<div class="lsna-template-card lsna-template-autoposter">
							<div class="lsna-template-card-header <?php echo ( $editmode && (int)$_REQUEST['template_id'] == (int)$id ) ? 'lsna-template-editing' : '';?>">
								<h5><a href="<?php echo esc_url( add_query_arg( array( 'edit_template' => '1', 'template_id' => (int)$id ) ) ); ?>"><?php echo esc_attr( $template->title ); ?></a></h5>
								<?php if( !$editmode || ( $editmode && (int)$_REQUEST['template_id'] != (int)$id ) ) : ?>
								<div class="lsna-template-delete"><a class="lsna-delete" href="#" title="<?php echo __( 'Delete template', 'lemonade_sna' ); ?>" onclick="lemonade_sna_pinterest_delete_template_dialog(event,this);" data-id="<?php echo $id; ?>" data-title="<?php echo esc_attr( $template->title ); ?>"><span class="mif-cross text-shadow"></span></a></div>
								<?php endif; ?>
							</div>
							<div class="lsna-template-card-inside">
								<div>
									<?php
									if( $editmode && $_REQUEST['template_id'] == $id ) {
										?>
										<span class="lsna-lock"><i class="mif-lock"></i></span>
										<?php	
									}
									?>								
									<span><?php echo __( 'OFF', 'lemonade_sna' ); ?></span>
									<label class="switch lsna-inc-ex">
										<input type="checkbox" value="1" <?php echo ( $editmode && $_REQUEST['template_id'] == $id ) ? 'disabled="disabled"' : ''; ?> class="lsna-template-activate" data-template-id="<?php echo $id; ?>" name="activate[<?php echo $id; ?>]" <?php echo ( $template->is_active == 1 ) ? 'checked="checked"' : ''; ?> onchange="lemonade_sna_pinterest_activate_template(this);">
										<span class="check"></span>							
									</label>
									<span><?php echo __( 'ON', 'lemonade_sna' ); ?></span>
									<span class="lsna-activate-spinner lsna-invisible"><i class="mif-spinner mif-ani-spin"></i></span>							
								</div>
							</div>
							<div class="lsna-template-card-footer">
							<?php
							if( !empty( $template->date_created ) ) {
								$timezone_str = get_option( 'timezone_string' );
								if( empty( $timezone_str ) ) {
									$timezone = get_option( 'gmt_offset' );
									$timezone_str = 'GMT+' . $timezone;
								}
								if( get_option( 'date_format' ) != '' ) {
									$date_format = get_option( 'date_format' );
								} else {
									$date_format = 'Y-m-d';
								}
								if( get_option( 'time_format' ) != '' ) {
									$time_format = get_option( 'time_format' );
								} else {
									$time_format = 'H:i:s';
								}
								$formatted_date = lemonade_sna_pinterest_get_date_or_time_with_timezone( sanitize_text_field( $template->date_created ), $date_format, $timezone );
								$formatted_time = lemonade_sna_pinterest_get_date_or_time_with_timezone( sanitize_text_field( $template->date_created ), $time_format, $timezone );;									
								?>
								<div class="text-secondary">											
									<span class="mif-calendar" style="vertical-align: top;"></span>
									<span class="lsna-pin-template-created" data-when="<?php echo esc_attr( $template->date_created ); ?>"><?php
									echo $formatted_date . ' ' . __( 'at', 'lemonade_sna' ) . ' ' . $formatted_time . ' ' . __( 'by', 'lemonade_sna' ) . ' ' . ( !empty( $timezone_str ) ? $timezone_str : 'GMT' ) . ' (' . __( 'Site local time', 'lemonade_sna' ) . ')';
									?></span>
								</div>
							<?php
							}
							?>
							</div>
							<div class="lsna-template-card-footer bottom-shadow">
							<?php
							if( !empty( $template->next_update ) ) {
								$timezone_str = get_option( 'timezone_string' );
								if( empty( $timezone_str ) ) {
									$timezone = get_option( 'gmt_offset' );
									$timezone_str = 'GMT+' . $timezone;
								}
								if( get_option( 'date_format' ) != '' ) {
									$date_format = get_option( 'date_format' );
								} else {
									$date_format = 'Y-m-d';
								}
								if( get_option( 'time_format' ) != '' ) {
									$time_format = get_option( 'time_format' );
								} else {
									$time_format = 'H:i:s';
								}
								$formatted_date = lemonade_sna_pinterest_get_date_or_time_with_timezone( sanitize_text_field( $template->next_update ), $date_format, $timezone );
								$formatted_time = lemonade_sna_pinterest_get_date_or_time_with_timezone( sanitize_text_field( $template->next_update ), $time_format, $timezone );;									
								?>
								<div class="text-secondary">											
									<span class="mif-calendar" style="vertical-align: top;"></span><i><?php echo __( 'Next update:', 'lemonade_sna' ); ?> </i>
									<span class="lsna-pin-template-created" data-when="<?php echo esc_attr( $template->next_update ); ?>"><?php
									echo $formatted_date . ' ' . __( 'at', 'lemonade_sna' ) . ' ' . $formatted_time . ' ' . __( 'by', 'lemonade_sna' ) . ' ' . ( !empty( $timezone_str ) ? $timezone_str : 'GMT' ) . ' (' . __( 'Site local time', 'lemonade_sna' ) . ')';
									?></span>
								</div>
							<?php
							}
							?>
							</div>							
						</div>
					</div>
					<?php
					if( ( $i % 3 ) == 0 || count( $templates ) == $i ) {
						?>
						</div>
						<?php
					}
					$i++;
				}
				?>
				</div>
			<?php	
			} else {
			?>
			<p>
				<span class="mif-not"></span><?php echo __( 'No templates were found.', 'lemonade_sna' ); ?>
			</p>
			<?php
			}
			?>
		</section>
		<?php
		$html = ob_get_contents();
		if( $html ) {
			ob_end_clean();
		}
		
		/**
		 * Filters Autoposter templates table html.
		 *
		 * @since 2.0.0
		 *
		 * @param string $html Html to filter.
		 */
		echo apply_filters( 'lemonade_sna_pinterest_autoposter_list_of_templates_section', $html );
		?>
	</div>
	<div data-role="dialog" id="lsna-dialog-activated" class="padding20 dialog success" data-close-button="true" data-type="success" data-width="300" data-overlay-click-close="true">
		<h1 class="fg-white"><?php echo __( 'Template is active', 'lemonade_sna' ); ?></h1>
		<p>
		<?php echo __( 'Template was activated. New WP Cron job will be running after you renew any page on the site.', 'lemonade_sna' ); ?>
		</p>
	</div>	
	<div data-role="dialog" id="lsna-dialog-deactivated" class="padding20 dialog info" data-close-button="true" data-type="info" data-width="300" data-overlay-click-close="true">
		<h1 class="fg-white"><?php echo __( 'Template is inactive', 'lemonade_sna' ); ?></h1>
		<p>
		<?php echo __( 'Template was deactivated. WP Cron job will be removed from schedule after you renew any page on the site.', 'lemonade_sna' ); ?>
		</p>
	</div>	
	<div data-role="dialog" id="lsna-dialog-error" class="padding20 dialog alert" data-close-button="true" data-type="alert" data-width="300" data-overlay-click-close="true">
		<h1 class="fg-white"><?php echo __( 'An error occured', 'lemonade_sna' ); ?></h1>
		<p>
		<?php echo __( "Template couldn't be activated/deactivated due to an error.", 'lemonade_sna' ); ?>
		</p>
	</div>	
	<div data-role="dialog" id="lsna-dialog-delete-template" class="padding20 dialog warning" data-close-button="true" data-type="warning" data-width="300" data-overlay-click-close="true" data-overlay="true" data-overlay-color="op-grayDark">
		<h1 class="fg-white"><?php echo __( 'Template delete', 'lemonade_sna' ); ?></h1>
		<p>
		<?php echo __( "You are going to delete ", 'lemonade_sna' ) . '<span class="lsna-dialog-delete-template-title text-accent">a template</span>. ' . __( 'All the data will be removed. After that you will not be able to restore the template.', 'lemonade_sna' ); ?>
		</p>
		<p>
			<button class="button fg-white bg-darkRed bg-hover-crimson lsna-delete-template-button" onclick="lemonade_sna_pinterest_delete_template(this);" data-template-id=""><?php echo __( 'Delete', 'lemonade_sna' ); ?> <span class="lsna-delete-spinner lsna-invisible"><i class="mif-spinner mif-ani-spin"></i></span></button>
			<button class="button bg-white bg-hover-grayLighter" onclick="metroDialog.close('#lsna-dialog-delete-template');"><?php echo __( 'Cancel', 'lemonade_sna' ); ?></button>
		</p>
	</div>	
	<div data-role="dialog" id="lsna-dialog-delete-template-error" class="padding20 dialog alert" data-close-button="true" data-type="alert" data-width="300" data-overlay-click-close="true">
		<h1 class="fg-white"><?php echo __( 'Template delete error', 'lemonade_sna' ); ?></h1>
		<p>
		<?php echo __( 'An error occured while deleting the template', 'lemonade_sna' ); ?>
		</p>
	</div>		
	<?php
	unset( $_SESSION['lsna_error'] );
	unset( $_SESSION['lsna_success'] );	
}

/**
 * Creates Lemonade SNA Autoposter Live Streamer page content.
 *
 * Html for Lemonade SNA Autoposter Live Streamer page.
 *
 * @since 1.0.0
 *
 * @see lemonade_sna_pinterest_get_list_of_active_templates()
 */
function lemonade_sna_pinterest_admin_panel_streamer() {
	?>
	<div class="wrap">
		<h1 class="lsna-admin-page-title"><?php echo esc_attr( __( 'Pinterest Autoposter Live Stream - Lemonade SNA Pinterest', 'lemonade_sna' ) ); ?></h1>
		<?php 
		settings_errors();
		if( isset( $_SESSION['lsna_error'] ) && !empty( $_SESSION['lsna_error'] ) ) {
			foreach( $_SESSION['lsna_error'] as $error ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo sanitize_text_field( $error ); ?></p>
			</div>
			<?php
			}
		}		
		if( isset( $_SESSION['lsna_success'] ) && !empty( $_SESSION['lsna_success'] ) ) {
			?>
			<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
				<p><?php echo sanitize_text_field( $_SESSION['lsna_success'] ); ?></p>
			</div>
			<?php
		}
		
		/**
		 * Gets list of user's Pinterest boards using DirkGroenen Pinterest API wrapper
		 */		 
		include( LEMONADE_SNA_PINTEREST_EXT_PATH . '/tiles/lemonade_sna_pinterest_show_boards.php' );
		
		$active = lemonade_sna_pinterest_get_list_of_active_templates( 'pinterest' );
		if( !empty( $active ) && is_array( $active ) ) {
			$lines = get_option( 'lemonade_sna_pinterest_autoposter_lines' );
			?>
			<h2 class="nav-tab-wrapper lsna-tab-wrapper">
			<?php
			$query_template_id = !empty( $_REQUEST['tab_template_id'] ) ? (int)$_REQUEST['tab_template_id'] : (int)$active[0]->id;			
			foreach( $active as $template ) {
				$active_class = $template->id == $query_template_id ? 'lsna-tab-active' : '';
				?>
				<a href="<?php echo esc_url( add_query_arg( array( 'tab_template_id' => (int)$template->id ) ) ); ?>" class="nav-tab <?php echo $active_class ?>"><?php echo esc_attr( $template->title ); ?></a>
				<?php
			}
			?>
			</h2>
			<?php
			foreach( $active as $template ) {
				if( $template->id == $query_template_id ) {
					?>	
					<div class="lsna-streamer-wrapper" data-template-id="<?php echo (int)$template->id; ?>">
					<?php
					
					/**
					 * Gets html for Live Streamer
					 */					
					include( LEMONADE_SNA_PINTEREST_EXT_PATH . '/tiles/lemonade_sna_pinterest_streamer.php' );
					?>
					</div>
					<?php
				}
			}
		} else {
		?>
		<p><span class="mif-not"></span><?php echo __( 'There are no templates active', 'lemonade_sna' ) . '.'; ?></p>
		<?php
		}
		?>
	</div>
	<?php
}

/**
 * Creates Lemonade SNA Getting PRO page content.
 *
 * Html for Lemonade SNA Getting PRO page.
 *
 * @since 2.0.0
 *
 */
function lemonade_sna_pinterest_admin_panel_pro() {
	?>
	<div class="wrap">
		<h1 class="lsna-admin-page-title"><?php echo esc_attr( __( 'Getting PRO - Lemonade SNA Pinterest', 'lemonade_sna' ) ); ?></h1>
		<h4><?php echo __( 'Do I need a', 'lemonade_sna' ); ?> <span class="tag warning">PRO</span> <?php echo __( 'version of the plugin?', 'lemonade_sna' );?></h4>
		<p class="text-default">
			<b><?php echo __( 'Yes, you do, if you want to get a perfect working tool which extends your capabilities and gives:','lemonade_sna' ); ?></b>
		</p>
		<ul class="simple-list">
			<li><?php echo __( 'UNLIMITED number of templates of any type (Pin, Rich Pin, Autoposter).', 'lemonade_sna' ); ?></li>
			<li><?php echo __( 'EXTENDED version of shortcodes for Pin and Rich Pin templates.', 'lemonade_sna' ); ?></li>
			<li><?php echo __( 'RICH PIN meta support.', 'lemonade_sna' ); ?></li>
			<li><?php echo __( 'INDIVIDUAL Pin and Rich Pin templates for posts (pages, cpt).', 'lemonade_sna' ); ?></li>
			<li><?php echo __( 'UNLIMITED number of Pinterest boards used by one Autoposter templates.', 'lemonade_sna' ); ?></li>
			<li><?php echo __( 'MORE FLEXIBLE Autoposter schedule settings: set up special dates, weekdays and time for re-posting.', 'lemonade_sna' ); ?></li>
			<li><?php echo __( 'CUSTOM TAXONOMIES filter supported.', 'lemonade_sna' ); ?></li>
			<li><?php echo __( 'CUSTOM META-FIELDS filter supported.', 'lemonade_sna' ); ?></li>
		</ul>		
		<p class="text-default notify">
			<b><span class="tag warning text-accent"><?php echo __( 'EXCLUSIVE OFFER!', 'lemonade_sna' ); ?></span></b><br>
			<?php
			echo __( 'After you purchase our product, we will help you to set up custom filters and shortcodes for to make the tool more convinient to your purposes.', 'lemonade_sna' );
			?>
		</p>
		<a href="https://codecanyon.net/item/lemonade-social-networks-autoposter-pinterest/20775520" target="_blank">
			<button class="command-button warning">
				<span class="mif-dollar2 mif-2x mif-ani-shuttle"></span>
				<?php echo __( 'Buy PRO now', 'lemonade_sna' ); ?>
			</button>
		</a>
	</div>
	<?php
}

?>