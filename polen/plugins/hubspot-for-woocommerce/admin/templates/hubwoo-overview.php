<?php
/**
 * Dashoard of all of the plugin features.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/admin/templates/
 */

// check if the license is entered and have valid license.

$hubspot_url  = Hubwoo::hubwoo_get_auth_url();
$display_data = Hubwoo::hubwoo_setup_overview();

if ( isset( $_GET['task'] ) && 'install-plugin' == $_GET['task'] ) {
	Hubwoo::hubwoo_setup_overview( true );
}
$portal_id = get_option( 'hubwoo_pro_hubspot_id', '' );
?>
<div class="hubwoo_pop_up_wrap" style="display: none">
	<div class="pop_up_sub_wrap">
		<div class="hubwoo-disconnect-wrapper">		
			<h2><?php esc_html_e( 'Disconnect your WooCommerce store from HubSpot', 'hubspot-for-woocommerce' ); ?></h2>			
			<form class="hubwoo-disconnect-form">
				<label for='delete_meta'><input name="delete_meta" type="checkbox" name="hubwoo-acc-meta"><?php esc_html_e( 'Do you want to delete all your WooCommerce data from HubSpot? If yes, check the box.' ); ?>
				</label>
			</form>
			<div class="hubwoo-discon-spinner">
				<span class="fa fa-spin fa-spinner"></span>				
			</div>
			<div class="hubwoo-discon-btn">
				<a href="javascript:void(0);" data-type='disconnect' class="hubwoo-btn--primary hubwoo-btn--disconnect hubwoo-manage-account"><?php esc_html_e( 'Confirm and Disconnect', 'hubspot-for-woocommerce' ); ?></a>
				<a href="javascript:void(0);" data-type='cancel' class="hubwoo-btn--primary hubwoo-btn--disconnect hubwoo-btn--secondary hubwoo-manage-account"><?php esc_html_e( 'Cancel and Go Back', 'hubspot-for-woocommerce' ); ?></a>
			</div>
		</div>
	</div>
</div>
<div class="hubwoo-db-wrap">
	<div class="hubwoo-db">
		<div class="hubwoo-db__row">
			<div class="hubwoo-db__column">
				<div class="hubwoo-db__box-full">
					<div class="hubwoo-db__box-title">
						<h4><?php esc_html_e( 'Connected Hubspot Account', 'hubspot-for-woocommerce' ); ?></h4>
						<p>
							<?php echo esc_textarea( $portal_id, 'hubspot-for-woocommerce' ); ?> 
							<a id ="hubwoo-re-auth" href="<?php echo esc_url( $hubspot_url ); ?>" class="hubwoo-discon">
								<?php esc_html_e( 'Re-Authorize your Account', 'hubspot-for-woocommerce' ); ?>
							</a>
						</p>
					</div>
					<div class="hubwoo-db__box-full-content">
						<a data-type="disconnect-form" href="javascript:void(0);" class="hubwoo-btn--dashboard hubwoo-discon hubwoo-manage-account hubwoo-btn--primary hubwoo-btn--secondary">
							<?php esc_html_e( 'Disconnect Store', 'hubspot-for-woocommerce' ); ?>           
						</a>
						<a target="_blank" href='<?php echo esc_attr( 'https://app.hubspot.com/home-beta?portalId=' . $portal_id ); ?>' class="hubwoo-btn--dashboard hubwoo-btn--primary">
							<?php esc_html_e( 'Open HubSpot', 'hubspot-for-woocommerce' ); ?>           
						</a>			
					</div>
				</div>
			</div>
		</div>
		<div class="hubwoo-db__counter">
			<div class="hubwoo-db__row">
				<div class="hubwoo-db__counter-column">
					<p class="hubwoo-db__counter-title"><?php esc_html_e( 'CONTACTS SYNCED', 'hubspot-for-woocommerce' ); ?></p>
					<h6 class="hubwoo-db__counter-number"><a target="_blank" href=" <?php echo esc_url( 'https://app.hubspot.com/contacts/' . $portal_id . '/contacts/list/' ); ?> "><?php echo esc_textarea( $display_data['reg_users'] + $display_data['guest_users'], 'hubspot-for-woocommerce' ); ?></a></h6>
					<span class="hubwoo-db__counter-desc"><?php echo esc_textarea( $display_data['contacts_left'], 'hubspot-for-woocommerce' ); ?></span>
				</div>
				<div class="hubwoo-db__counter-column">
					<p class="hubwoo-db__counter-title"><?php esc_html_e( 'DEALS SYNCED', 'hubspot-for-woocommerce' ); ?></p>
					<h6 class="hubwoo-db__counter-number"><a target="_blank" href=" <?php echo esc_url( 'https://app.hubspot.com/contacts/' . $portal_id . '/deals/board/' ); ?> "><?php echo esc_textarea( $display_data['deal'], 'hubspot-for-woocommerce' ); ?></a></h6>
					<span class="hubwoo-db__counter-desc"><a href="<?php echo esc_attr( admin_url( 'admin.php?page=hubwoo&hubwoo_tab=hubwoo-deals&trn=shDls' ) ); ?>"><?php echo esc_textarea( $display_data['deals_left'], 'hubspot-for-woocommerce' ); ?></a></span>
				</div>
				<div class="hubwoo-db__counter-column">
					<p class="hubwoo-db__counter-title"><?php esc_html_e( 'PRODUCTS SYNCED', 'hubspot-for-woocommerce' ); ?></p>
					<h6 class="hubwoo-db__counter-number"><a target="_blank" href=" <?php echo esc_url( 'https://app.hubspot.com/settings/' . $portal_id . '/sales/products' ); ?> "><?php echo esc_textarea( $display_data['product'], 'hubspot-for-woocommerce' ); ?></a></h6>
					<span class="hubwoo-db__counter-desc"><?php echo esc_textarea( $display_data['products_left'], 'hubspot-for-woocommerce' ); ?></span>
				</div>
			</div>
			<div class="hubwoo-db__counter-button--wrap">
				<a href="javascript:void" class="hubwoo-db__counter-button"><?php echo esc_textarea( $display_data['last_sync'] ); ?></a>
			</div>
		</div>
		<div class="hubwoo-db__row hubwoo-db__row--info">
			<div class="hubwoo-db__box hubwoo-db__box--info">
				<div class="hubwoo-db__box--infoCon">
					<div class="hubwoo-db__box-title">
						<?php
						esc_html_e( 'Sync WooCommerce orders with HubSpot eCommerce pipeline', 'hubspot-for-woocommerce' );
						?>
																		
					</div>
					<div class="hubwoo-db__box-row-content">
						<p>
							<?php
							esc_html_e( 'Automatically create deals in your HubSpot sales pipeline when new WooCommerce orders are created.', 'hubspot-for-woocommerce' );
							?>

						</p>
					</div>
					<div class="mwb-heb-wlcm__btn-wrap">
						<a href="<?php echo esc_attr( admin_url( 'admin.php?page=hubwoo&hubwoo_tab=hubwoo-deals' ) ); ?>" class="hubwoo-btn--primary hubwoo-btn--dashboard"><?php esc_html_e( 'Deals Settings', 'hubspot-for-woocommerce' ); ?></a>
					</div>
				</div>
			</div>
			<div class="hubwoo-db__box hubwoo-db__box--info">
				<div class="hubwoo-db__box--infoCon">
					<div class="hubwoo-db__box-title">
						<?php esc_html_e( 'Set up abandoned cart capture', 'hubspot-for-woocommerce' ); ?> 
					</div>
					<div class="hubwoo-db__box-row-content">
						<p>
							<?php
							esc_html_e( 'Automatically track people that have added products to their cart and did not check out.', 'hubspot-for-woocommerce' );
							?>
						</p>
					</div>
					<div class="mwb-heb-wlcm__btn-wrap">
						<a href="<?php echo esc_attr( admin_url( 'admin.php?page=hubwoo&hubwoo_tab=hubwoo-abncart' ) ); ?>" class="hubwoo-btn--primary hubwoo-btn--dashboard"><?php esc_html_e( 'Abandoned Cart Settings', 'hubspot-for-woocommerce' ); ?></a>
					</div>
				</div>
			</div>
			<div class="hubwoo-db__box hubwoo-db__box--info">
				<div class="hubwoo-db__box--infoCon">
					<div class="hubwoo-db__box-title">
						<?php
						esc_html_e( 'Automate your sales, marketing and support', 'hubspot-for-woocommerce' );
						?>

					</div>
					<div class="hubwoo-db__box-row-content">
						<p>
							<?php
							esc_html_e( 'Convert more leads into customers, drive more sales, and scale your support.', 'hubspot-for-woocommerce' );
							?>
						</p>
						<p>
							<?php esc_html_e( 'This requires a ', 'hubspot-for-woocommerce' ); ?>
							<a class="redirect-link" href="http://www.hubspot.com/pricing" target="_blank">
								<?php
								esc_html_e( 'HubSpot Professional or Enterprise plan.', 'hubspot-for-woocommerce' );
								?>

							</a>					
						</p>
					</div>
					<div class="mwb-heb-wlcm__btn-wrap">
						<a href="<?php echo esc_attr( admin_url( 'admin.php?page=hubwoo&hubwoo_tab=hubwoo-automation' ) ); ?>" class="hubwoo-btn--primary hubwoo-btn--dashboard"><?php esc_html_e( 'Automation Settings', 'hubspot-for-woocommerce' ); ?></a>
					</div>
				</div>
			</div>
			<div class="hubwoo-db__box hubwoo-db__box--info">
				<div class="hubwoo-db__box--infoCon">
					<div class="hubwoo-db__box-title">
						<?php
						esc_html_e( 'Manage basic & advanced settings', 'hubspot-for-woocommerce' );
						?>

					</div>
					<div class="hubwoo-db__box-row-content">
						<p>
							<?php
							esc_html_e( 'Modify any of your basic & advanced settings to make sure the HubSpot for WooCommerce integration is set up for your business needs.', 'hubspot-for-woocommerce' );
							?>
						</p>
					</div>
					<div class="mwb-heb-wlcm__btn-wrap">
						<a href="<?php echo esc_attr( admin_url( 'admin.php?page=hubwoo&hubwoo_tab=hubwoo-general-settings' ) ); ?>" class="hubwoo-btn--primary hubwoo-btn--dashboard"><?php esc_html_e( 'View Basic & Advanced Settings', 'hubspot-for-woocommerce' ); ?></a>
					</div>
				</div>
			</div>
			<div class="hubwoo-db__box hubwoo-db__box--info">
				<div class="hubwoo-db__box--infoCon">
					<div class="hubwoo-db__box-title">
						<?php
						esc_html_e( 'Download HubSpot’s WordPress plugin', 'hubspot-for-woocommerce' );
						?>

					</div>
					<div class="hubwoo-db__box-row-content">
						<p>
							<?php
							esc_html_e( 'Do even more for your online store with HubSpot’s official WordPress plugin. Download the plugin to easily manage your HubSpot account without navigating away from the WordPress backend.', 'hubspot-for-woocommerce' );
							?>
						</p>
					</div>
					<div class="mwb-heb-wlcm__btn-wrap">
						<a href="<?php echo esc_attr( $display_data['plugin-install']['href'] ); ?>" class="hubwoo-btn--primary hubwoo-btn--dashboard"><?php echo esc_textarea( $display_data['plugin-install']['label'], 'hubspot-for-woocommerce' ); ?></a>
					</div>
				</div>
			</div>
			<div class="hubwoo-db__box hubwoo-db__box--info">
				<div class="hubwoo-db__box--infoCon">
					<div class="hubwoo-db__box-title">
						<?php
						esc_html_e( 'User guide documentation', 'hubspot-for-woocommerce' );
						?>

					</div>
					<div class="hubwoo-db__box-row-content">
						<p>
							<?php
							esc_html_e( 'To get the most out of the WooCommerce HubSpot integration, check out the user guide documentation for more details.', 'hubspot-for-woocommerce' );
							?>
						</p>
					</div>
					<div class="mwb-heb-wlcm__btn-wrap">
						<a target="_blank" href="https://docs.makewebbetter.com/hubspot-integration-for-woocommerce/?utm_source=MWB-HubspotFree-backend&utm_medium=MWB-backend&utm_campaign=backend" class="hubwoo-btn--primary hubwoo-btn--dashboard"><?php esc_html_e( 'View User Guides', 'hubspot-for-woocommerce' ); ?></a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
