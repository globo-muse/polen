<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/admin/partials
 */

?>
<?php
if ( isset( $_GET['action'] ) && 'hubwoo-osc-schedule-sync' == $_GET['action'] ) {
	Hubwoo_Admin::hubwoo_schedule_sync_listener( true );
}
?>
<div class="hubwoo-m-wrap-c">

	<form action="" method="post" id="hubwoo-ocs-form">
		<?php
		if ( empty( get_option( 'hubwoo_customers_role_settings', array() ) ) ) {
			update_option( 'hubwoo_customers_role_settings', array_keys( Hubwoo_Admin::get_all_user_roles() ) );
		}
			woocommerce_admin_fields( Hubwoo_Admin::hubwoo_customers_sync_settings() );
		?>
		<div>
			<div class="hubwoo-user-notice" style="margin-bottom: 20px;">
				<span class="hubwoo-ocs-btn-notice"><?php esc_html_e( 'Fetching all of the recently updated and un-synced users / orders', 'hubspot-for-woocommerce' ); ?></span> <span id='hubwoo-usr-spin' class="fa fa-spin fa-spinner"></span>
			</div>
			<a href="javascript:void(0);" style="display: none;" id = "hubwoo-osc-instant-sync" class="hubwoo-osc-instant-sync hubwoo__btn" data-total_users=""><?php esc_html_e( 'Sync Now', 'hubspot-for-woocommerce' ); ?></a>
			<a href="?page=hubwoo&hubwoo_tab=hubwoo-sync-contacts&action=hubwoo-osc-schedule-sync" id = "hubwoo-osc-schedule-sync" style="display: none;" class="hubwoo-osc-schedule-sync hubwoo__btn"><?php esc_html_e( 'Schedule Sync', 'hubspot-for-woocommerce' ); ?></a>			
		</div>		
	</form>	

	<div class="hubwoo-progress-wrap progress-cover" style="display: none;">
		<div>
			<h2><?php esc_html_e( 'Contact sync is in progress.', 'hubspot-for-woocommerce' ); ?></h2>
			<span class="psync_desc"> <?php esc_html_e( 'This should only take a few moments. Thanks for your patience!', 'hubspot-for-woocommerce' ); ?></span>
		</div>
		<div class="hubwoo-progress">
			<div class="hubwoo-progress-bar" role="progressbar" style="width:0"></div>
		</div>
	</div>				
</div>
