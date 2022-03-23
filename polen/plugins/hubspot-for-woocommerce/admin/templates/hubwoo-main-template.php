<?php
/**
 * Main Handler Template of the Plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/admin/templates/
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit(); // Exit if accessed directly.
}
?>

<?php

	global $hubwoo;
	$active_tab   = isset( $_GET['hubwoo_tab'] ) ? sanitize_key( $_GET['hubwoo_tab'] ) : 'hubwoo-overview';
	$default_tabs = $hubwoo->hubwoo_default_tabs();
?>

<?php $field_setup = $hubwoo->is_field_setup_completed(); ?>
<?php $list_setup = $hubwoo->is_list_setup_completed(); ?>
<?php $hubwoo_lists = $hubwoo->hubwoo_get_final_lists(); ?>
<?php

	$setup_tab = '';

if ( isset( $_GET['hubwoo_key'] ) ) {
	$setup_tab = sanitize_key( $_GET['hubwoo_key'] );
}

if ( 1 == get_option( 'hubwoo_connection_setup_established', 0 ) ) {

	?>
	<div class="hub-woo-main-wrapper">
		<div class="hubwoo-nav-wrap">
			<nav class="hubwoo-nav">
				<ul>
					<?php
					if ( is_array( $default_tabs ) && count( $default_tabs ) ) {

						foreach ( $default_tabs as $tab_key => $single_tab ) {

							$tab_classes = 'hubwoo-nav-tab ';

							$dependency = $single_tab['dependency'];

							if ( ! empty( $active_tab ) && $active_tab == $tab_key ) {

								$tab_classes .= 'nav-tab-active';
							}

							if ( ! empty( $dependency ) && ! $hubwoo->check_dependencies( $dependency ) ) {

								$tab_classes .= 'hubwoo-tab-disabled';
								?>
									<div class="hubwoo-tabs"><a title="<?php echo esc_attr( $single_tab['title'] ); ?>" class="<?php echo esc_attr( $tab_classes ); ?>" id="<?php echo esc_attr( $tab_key ); ?>" href="javascript:void(0);"><?php echo esc_html( $single_tab['name'] ); ?></a></div>
									<?php
							} else {

								?>
									<div class="hubwoo-tabs"><a title="<?php echo esc_attr( $single_tab['title'] ); ?>" class="<?php echo esc_attr( $tab_classes ); ?>" id="<?php echo esc_attr( $tab_key ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=hubwoo' ) . '&hubwoo_tab=' . $tab_key ); ?>"><?php echo esc_html( $single_tab['name'] ); ?></a></div>
									<?php
							}
						}
					}
					?>

				</ul>
			</nav>		
			<?php
			if ( empty( $active_tab ) ) {

				$active_tab = 'hubwoo-overview';
			}

			$users_to_sync = get_option( 'hubwoo_total_ocs_contact_need_sync', 1 );

			$is_last_process_completed = get_option( 'hubwoo_ocs_data_synced', false );

			$hubwoo_background_process_running = get_option( 'hubwoo_background_process_running', false );

			$contact_sync_status = 'no';

			if ( ! $is_last_process_completed && $hubwoo_background_process_running ) {
				$contact_sync_status = 'yes';
				?>
				<div class="hubwoo-ocs-notice-wrap">
					<div class="hubwoo-ocs-options">
						<?php
							$current_user_sync = get_option( 'hubwoo_ocs_contacts_synced', 0 );
							esc_html_e( 'Your contacts are syncing in the background so you can safely leave this page.', 'hubspot-for-woocommerce' );
							$perc 		  = round( $current_user_sync * 100 / $users_to_sync );
							$total_synced = $perc > 100 ? 100 : $perc;

						?>
					</div>
					<div class="manage-ocs-bar">						
						<div class="hubwoo-progress-wrap progress-cover deal-sync_progress" style="display: block">
							<div class="hubwoo-progress">
								<div class="hubwoo-progress-bar" data-sync-type = "contact" data-sync-status = "<?php echo esc_attr( $contact_sync_status ); ?>" role="progressbar" style="width: <?php echo esc_attr( $total_synced ); ?>%">
									<?php echo esc_textarea( $total_synced, 'hubspot-for-woocommerce' ); ?>%
								</div>
							</div> 
						</div>						
					  <a href="javascript:;" data-action="stop-contact" class="manage_contact_sync hubwoo__btn"><?php esc_html_e( 'Stop Sync', 'hubspot-for-woocommerce' ); ?></a>
					</div>					
				</div>	
				<?php
			}

			$tab_content_path = 'admin/templates/' . $active_tab . '.php';
			$hubwoo->load_template_view( $tab_content_path, $active_tab );
			?>
		</div>
	</div>
	<?php
} else {

	$display_keys = array_fill_keys( array( 'connection-setup', 'grp-pr-setup', 'list-setup', 'sync' ), '' );

	$display_keys['default_tab']      = 'connection-setup';
	$display_keys['connection-setup'] = 'active';

	if ( 'yes' == get_option( 'hubwoo_connection_complete', 'no' ) ) {
		$display_keys['connection-setup'] = 'completed';
		$display_keys['grp-pr-setup']     = 'completed';
		$display_keys['default_tab']      = 'grp-pr-setup';

		if ( 1 == get_option( 'hubwoo_fields_setup_completed', 0 ) ) {
			$display_keys['grp-pr-setup'] = 'completed';
			$display_keys['list-setup']   = 'completed';
			$display_keys['default_tab']  = 'list-setup';
		}

		if ( 1 == get_option( 'hubwoo_pro_lists_setup_completed', 0 ) ) {
			$display_keys['list-setup']  = 'completed';
			$display_keys['sync']        = 'completed';
			$display_keys['default_tab'] = 'sync';
		}
	}

	if ( empty( $setup_tab ) ) {

		$setup_tab = $display_keys['default_tab'];
	}

	$display_keys[ $setup_tab ] = 'active';

	if ( 'yes' == get_option( 'hubwoo_greeting_displayed_setup', 'no' ) ) {
		$display_keys[ $setup_tab ] = 'completed';
	}

	?>

	<div class="mwb-heb-wlcm-wrapper">
		<nav class="mwb-heb__nav">
			<ul class="mwb-heb__nav-list">
				<li class="mwb-heb__nav-list-item <?php echo esc_attr( $display_keys['connection-setup'] ); ?>">
					<a href="admin.php?page=hubwoo&hubwoo_tab=hubwoo-overview&hubwoo_key=connection-setup">
						<span class="mwb-heb__nav-count"><?php esc_html_e( '1', 'hubspot-for-woocommerce' ); ?></span> 
						<?php esc_html_e( 'Connection', 'hubspot-for-woocommerce' ); ?> 
					</a>
				</li>
				<li class="mwb-heb__nav-list-item <?php echo esc_attr( $display_keys['grp-pr-setup'] ); ?>">
					<a href="admin.php?page=hubwoo&hubwoo_tab=hubwoo-overview&hubwoo_key=grp-pr-setup">
						<span class="mwb-heb__nav-count"><?php esc_html_e( '2', 'hubspot-for-woocommerce' ); ?></span>
						<?php esc_html_e( 'Groups & Properties', 'hubspot-for-woocommerce' ); ?>						
					</a>
				</li>
				<li class="mwb-heb__nav-list-item <?php echo esc_attr( $display_keys['list-setup'] ); ?>">
					<a href="admin.php?page=hubwoo&hubwoo_tab=hubwoo-overview&hubwoo_key=list-setup">
						<span class="mwb-heb__nav-count"><?php esc_html_e( '3', 'hubspot-for-woocommerce' ); ?></span>
						<?php esc_html_e( 'Lists', 'hubspot-for-woocommerce' ); ?>
					</a>
				</li>
				<li class="mwb-heb__nav-list-item <?php echo esc_attr( $display_keys['sync'] ); ?>">
					<a href="admin.php?page=hubwoo&hubwoo_tab=hubwoo-overview&hubwoo_key=sync">
						<span class="mwb-heb__nav-count"><?php esc_html_e( '4', 'hubspot-for-woocommerce' ); ?></span> 
						<?php esc_html_e( 'Sync', 'hubspot-for-woocommerce' ); ?>
					</a>
				</li>
			</ul>
		</nav>
		<?php

			$tab_content_path = 'admin/templates/setup/hubwoo-' . $setup_tab . '.php';
			$hubwoo->load_template_view( $tab_content_path, $setup_tab );
		?>
	</div>
<?php } ?>
