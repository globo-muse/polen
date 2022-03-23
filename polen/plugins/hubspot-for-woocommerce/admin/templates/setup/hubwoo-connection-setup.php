<?php
/**
 * The admin-facing file for setting up connection with HubSpot.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/admin/templates/setup
 */

$hubspot_url = Hubwoo::hubwoo_get_auth_url();

if ( 'yes' == get_option( 'hubwoo_connection_complete', 'no' ) ) {
	$connect['will_connect']  = 'none';
	$connect['did_connected'] = 'block';
} else {
	$connect['will_connect']  = 'block';
	$connect['did_connected'] = 'none';
}

if ( 'yes' == get_option( 'hubwoo_connection_issue', 'no' ) ) {
	$notice = esc_html( 'Failed to connect to HubSpot, Try to reconnect or please contact us!', 'hubspot-for-woocommerce' );
	global $hubwoo;
	$hubwoo->hubwoo_notice( $notice, 'hubwoo-notice' );
}
?>

<div class="mwb-heb-welcome" style="display: <?php echo esc_attr( $connect['will_connect'] ); ?>";>
	<div class="hubwoo-box">
		<div class="mwb-heb-wlcm__title">			
			<h2>
				<?php esc_html_e( 'Getting started with HubSpot and WooCommerce', 'hubspot-for-woocommerce' ); ?>
			</h2>
		</div>
		<div class="mwb-heb-wlcm__content">
			<div class="hubwoo-content__para">
				<p>
					<?php
						esc_html_e( 'With this WooCommerce HubSpot integration, you can automatically sync all your WooCommerce contacts and customers with HubSpot’s CRM and marketing platform.', 'hubspot-for-woocommerce' );
					?>
				</p>
				<p>
					<?php
						esc_html_e( 'Once you set up this integration, you will be able to:', 'hubspot-for-woocommerce' );
					?>
					<ul class="connect-list">
						<li><?php esc_html_e( 'See every action each contact has taken including their page views, past orders, abandoned carts, and more — in HubSpot CRM’s tidy timeline view', 'hubspot-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Segment contacts and customers into lists based on their previous interactions with your store', 'hubspot-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Easily create and send beautiful, responsive emails to drive sales', 'hubspot-for-woocommerce' ); ?></li>
						<li><?php esc_html_e( 'Measure your store’s performance with custom reports and dashboards', 'hubspot-for-woocommerce' ); ?></li>
					</ul>
				</p>
			</div>
			<p>
				<?php esc_html_e( 'To get started, connect your HubSpot account. If you don’t have a HubSpot account, create one then return to this window to connect it.', 'hubspot-for-woocommerce' ); ?>
			</p>

			<div class="mwb-heb-wlcm__btn-wrap">
				<a id="hubwoo-initiate-oauth" href="<?php echo esc_url( $hubspot_url ); ?>" class="hubwoo-btn--primary"><?php esc_html_e( 'Connect your Account', 'hubspot-for-woocommerce' ); ?></a>
				<a href="https://hubspot.sjv.io/c/2885574/1097648/12893" target="_blank" class="hubwoo-btn--primary hubwoo-btn--secondary"><?php esc_html_e( 'Create a free HubSpot Account', 'hubspot-for-woocommerce' ); ?></a>
			</div>
		</div>
	</div>
</div>

<div class="acc-connected mwb-heb-welcome" style="display: <?php echo esc_attr( $connect['did_connected'] ); ?>";>
	<div class="hubwoo-box">
		<div class="mwb-heb-wlcm__title">			
			<h2>
				<?php esc_html_e( 'Congratulation your account is now connected.', 'hubspot-for-woocommerce' ); ?>
			</h2>
		</div>
		<div class="mwb-heb-wlcm__content">
			<div class="hubwoo-content__para">
				<?php
				$connected_portal_id = get_option( 'hubwoo_pro_hubspot_id', '' );
				if ( ! empty( $connected_portal_id ) ) {
					?>
					<p>
					<?php esc_html_e( 'Connected Portal ID : ', 'hubspot-for-woocommerce' ); ?><strong><?php echo esc_html( $connected_portal_id ); ?><strong>
					</p>

					<?php
				}
				?>
				<p>
					<?php
						esc_html_e( 'Switch to Another Account -', 'hubspot-for-woocommerce' );
					?>
					<span class="changeAccount"><a class="hubwoo-manage-account" href="javascript:;" data-type="change-account" style="text-decoration: none; margin-left: 5px;"><?php esc_html_e( 'Click Here', 'hubspot-for-woocommerce' ); ?></a></span>
				</p>
			</div>
			<div class="mwb-heb-wlcm__btn-wrap">
				<a href="javascript:;" class="hubwoo-btn--primary hubwoo_manage_screen" data-process="moveToGrpPr">
					<?php esc_html_e( 'Continue', 'hubspot-for-woocommerce' ); ?></a>
			</div>
		</div>
	</div>
</div>

<?php

if ( 'no' == get_option( 'hubwoo_clear_previous_options', 'no' ) ) {

	global $hubwoo;
	$hubwoo->hubwoo_switch_account( false );
}
?>
