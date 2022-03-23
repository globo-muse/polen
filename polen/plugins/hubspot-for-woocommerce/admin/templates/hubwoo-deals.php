<?php
/**
 * Manage eCommerce Pipeline and Deals creation.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/admin/templates/
 */

global $hubwoo;
$deal_stages  = Hubwoo::get_all_deal_stages();
$sync_data    = Hubwoo::get_sync_status();
$display_data = Hubwoo::get_deals_presenter();
?>
<div class="hubwoo-form-wizard-wrapper">
	<div class="hubwoo-form-wizard-content-wrapper">

		<!--- eCommerce pipeline setup--->

		<div class="hubwoo-group-wrap__deal_notice deals-par" data-type='scope' style="display: <?php echo esc_attr( $display_data['scope_notice'] ); ?>">
			<p class="hubwoo_deals_message">
				<?php
					esc_html_e(
						'eCommerce scopes are missing, please Re-authorize with a Super Admin account from the dashboard to start eCommerce pipeline setup.',
						'hubspot-for-woocommerce'
					);
					?>
			</p>
		</div>
		<div class="hubwoo-form-wizard-content hubwoo-deal-wrap-con" data-tab-content="ecommerce-pipeline-setup" style="display: <?php echo esc_attr( $display_data['view_mapping'] ); ?>">
			<div class="hubwoo-deal-wrap-con-flex">
				<div class="hubwoo-deal-wrap-con__h-con">
					<div class="hubwoo-fields-header hubwoo-common-header">
						<h2 class="ecomm-head"><?php echo esc_textarea( $display_data['heading'], 'hubspot-for-woocommerce' ); ?></h2>
						<input type="hidden" class="hubwoo-info" data-products="<?php echo esc_attr( $display_data['total_products'] ); ?>">
					</div>
					<div class="hubwoo-deal-wrap-con__intro" style="display: <?php echo esc_attr( $display_data['h_sync'] ); ?>">
						<?php esc_html_e( 'Connect with HubSpot eCommerce pipeline to create deals for your WooCommerce orders. ', 'hubspot-for-woocommerce' ); ?>
					</div>
				</div>
				<div class="hubwoo-deal-wrap-con__h-btn">
					<a class="hubwoo__btn" style="display: <?php echo esc_attr( $display_data['view_button'] ); ?>">
						<?php esc_html_e( 'View', 'hubspot-for-woocommerce' ); ?>
					</a>
				</div>					
			</div>	
			<div class="hubwoo-general-settings hubwoo-group-wrap__map_deal_stage ecommerce-pipeline-setup hubwoo-deal-wrap-con__store" style="display: <?php echo esc_attr( $display_data['view_mapping'] ); ?>">
				<button class="hubwoo__btn manage_product_sync" data-action='run-ecomm-setup' style="display: <?php echo isset( $display_data['view_btn_mapping'] ) ? esc_attr( $display_data['view_btn_mapping'] ) : ''; ?>"> <?php esc_html_e( 'Run Setup', 'hubspot-for-woocommerce' ); ?></button>		
				<div class="hubwoo-progress-wrap progress-cover " style="display: <?php echo esc_attr( $display_data['p_run_sync'] ); ?>">
					<span class="psync_desc sync-desc" data-sync-type = "product" data-sync-eta = "<?php echo isset( $display_data['eta_product_sync'] ) ? esc_attr( $display_data['eta_product_sync'] ) : ''; ?>">					
						<?php
							echo esc_textarea(
								'Your products are syncing in the background so you can safely leave this page. It should take ' . $display_data['eta_product_sync'] . ' to complete.',
								'hubspot-for-woocommerce'
							);
							?>
					</span>	
					<button class="hubwoo__btn stop-ecomm-sync manage_product_sync" data-action="stop-product-sync"><?php esc_html_e( 'Stop', 'hubspot-for-woocommerce' ); ?></button>
					
					<div class="hubwoo-progress">
						<div class="hubwoo-progress-bar" data-sync-type = "product" data-sync-status = "<?php echo esc_attr( $display_data['is_psync_running'] ); ?>" role="progressbar" data-percentage="<?php echo isset( $display_data['percentage_done'] ) ? esc_attr( $display_data['percentage_done'] ) : 0; ?>" style="width: <?php echo isset( $display_data['percentage_done'] ) ? esc_attr( $display_data['percentage_done'] ) : 0; ?>%">
							<?php echo isset( $display_data['percentage_done'] ) ? esc_textarea( $display_data['percentage_done'] ) : 0; ?>%
						</div>
					</div> 	
				</div>
			</div>										
		</div>

		<!--- Order and Deal Stage Mappping  -->

		<div class="hubwoo-form-wizard-content hubwoo-deal-wrap-con" data-tab-content="map-deal-stage" style="display: <?php echo esc_attr( $display_data['view_all'] ); ?>">
			<div class="hubwoo-deal-wrap-con-flex">
				<div class="hubwoo-deal-wrap-con__h-con">
					<div class="hubwoo-fields-header hubwoo-common-header">
						<h2 class=""><?php esc_html_e( 'Map Deal Stages with eCommerce pipeline', 'hubspot-for-woocommerce' ); ?></h2>
					</div>
					<div class="hubwoo-deal-wrap-con__intro">
						<?php esc_html_e( 'Sync order statuses with deal stages so you can manage your eCommerce pipeline in HubSpot.', 'hubspot-for-woocommerce' ); ?>
					</div>
				</div>
				<div class="hubwoo-deal-wrap-con__h-btn">
					<a class="hubwoo__btn" style="display: <?php echo esc_attr( $display_data['view_button'] ); ?>">
						<?php esc_html_e( 'View', 'hubspot-for-woocommerce' ); ?>
					</a>
				</div>
			</div>

			<div class="hubwoo-general-settings hubwoo-group-wrap__map_deal_stage hubwoo-settings-container hubwoo-deal-wrap-con__store" style="display: <?php echo esc_attr( $display_data['view_mapping'] ); ?>">
				<form action="#" method="post" class="hubwoo_save_ecomm_mapping">
					<table class="hubwoo-deals-stages-conf-table form-table">
						<tr>
						<th class="hubwoo-deal-wrap-con__thead"><?php esc_html_e( 'WooCommerce Order Status', 'hubspot-for-woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Deal Stage', 'hubspot-for-woocommerce' ); ?></th>
						</tr>
						<?php $all_order_statuses = wc_get_order_statuses(); ?>
						<?php
						foreach ( $all_order_statuses as $order_key => $order_label ) {
							$stage = Hubwoo::get_selected_deal_stage( $order_key );
							?>
								<tr>
									<th class="hubwoo-deal-wrap-con__thead">
										<?php echo esc_html( $order_label ); ?>
										<input type="hidden" name="hubwoo_woo_order_statuses[]" value="<?php echo esc_html( $order_key ); ?>">
									</th>
									<td>
										<select class="hubwoo_ecomm_mapping" name="hubwoo_deal_stages[]">
											<?php
											if ( ! empty( $deal_stages ) ) {
												foreach ( $deal_stages as $single_deal_stage ) {

													if ( $single_deal_stage['stageId'] === $stage ) {
														?>
															<option value="<?php echo esc_attr( $single_deal_stage['stageId'] ); ?>" selected=""><?php echo esc_html( $single_deal_stage['label'] ); ?></option>
															<?php
													} else {
														?>
															<option value="<?php echo esc_attr( $single_deal_stage['stageId'] ); ?>"><?php echo esc_html( $single_deal_stage['label'] ); ?></option>
															<?php
													}
												}
											}
											?>
										</select>
									</td>
								</tr>
							<?php
						}
						?>
						<tr>
							<td></td>
							<td>
								<button id="reset-deal-stages" class="hubwoo__btn hubwoo-btn--primary hubwoo-btn--dashboard" style="display: <?php echo esc_attr( $display_data['view_all'] ); ?>"><?php esc_html_e( ' Reset to Default Mapping', 'hubspot-for-woocommerce' ); ?>
								</button>
							</td>
						</tr>
					</table>
				</form>				
			</div>									
		</div>

		<div class="hubwoo-form-wizard-content hubwoo-deal-wrap-con" data-tab-content="deal-settings" style="display: <?php echo esc_attr( $display_data['view_all'] ); ?>">
			<div class="hubwoo-group-wrap__deal_settings hubwoo-deal-wrap-con">
				<div class="hubwoo-deal-wrap-con-flex">
				<div class="hubwoo-deal-wrap-con__h-con">
					<div class="hubwoo-fields-header hubwoo-common-header">
						<h2 class=""><?php esc_html_e( 'Create Deals for New Orders', 'hubspot-for-woocommerce' ); ?></h2>
					</div>
					<div class="hubwoo-deal-wrap-con__intro">
						<?php esc_html_e( 'Create Deals in real time for the new orders that are mapped with winning deal stages.', 'hubspot-for-woocommerce' ); ?>
					</div>
				</div>
				<div class="hubwoo-deal-wrap-con__h-btn">
					<a href="javascript:;" class="hubwoo__btn">
						<?php esc_html_e( 'View', 'hubspot-for-woocommerce' ); ?>
					</a>
				</div>
			</div>			
				<div class="hubwoo-settings-container hubwoo-deal-wrap-con__store hubwoo-general-settings">
					<form method="POST" id="hubwoo_real_time_deal_settings" class="hubwoo_form_submitted">					
						<?php
						if ( empty( get_option( 'hubwoo_ecomm_won_stages', '' ) ) ) {
							$stages = array_keys( Hubwoo_Admin::hubwoo_ecomm_get_stages() );
							update_option( 'hubwoo_ecomm_won_stages', $stages );
						}
							woocommerce_admin_fields( Hubwoo_Admin::hubwoo_ecomm_general_settings() );
						?>

					</form>
				</div>			
			</div>		
		</div>

		<div class="hubwoo-form-wizard-content hubwoo-deal-wrap-con" data-tab-content="deal-ocs" style="display: <?php echo esc_attr( $display_data['view_all'] ); ?>">
			<div class="hubwoo-group-wrap__deal_ocs hubwoo-deal-wrap-con">
				<div class="hubwoo-deal-wrap-con-flex">
					<div class="hubwoo-deal-wrap-con__h-con">
						<div class="hubwoo-fields-header hubwoo-common-header">
							<h2 class=""><?php esc_html_e( 'Sync Historical Orders as Deals', 'hubspot-for-woocommerce' ); ?></h2>
						</div>
						<div class="hubwoo-deal-wrap-con__intro">
							<?php esc_html_e( 'Select Order status and the time frame and start syncing all of those orders as deals in HubSpot.', 'hubspot-for-woocommerce' ); ?>
						</div>
					</div>
					<div class="hubwoo-deal-wrap-con__h-btn">
						<a href="javascript:;" class="hubwoo__btn" style="display: <?php echo esc_attr( $display_data['button'] ); ?>" >
							<?php esc_html_e( 'View', 'hubspot-for-woocommerce' ); ?>						
						</a>
					</div>
				</div>						
				<div data-txn="ocs-form" class="hubwoo-group-wrap__deal_ocs hubwoo-deal-wrap-con__store hubwoo-general-settings" style="display:<?php echo esc_attr( $display_data['message'] ); ?>">
					<div class="hubwoo-group-wrap__deal_notice deals-par" data-type='pBar' style="display: <?php echo esc_attr( $display_data['message'] ); ?>">
						<p class="hubwoo_deals_message sync-desc" data-sync-type = "order" data-sync-eta = "<?php echo ( isset( $sync_data['eta_deals_sync'] ) && ! empty( $sync_data['eta_deals_sync'] ) ) ? esc_attr( $sync_data['eta_deals_sync'] ) : ''; ?>">
							<?php
								echo esc_textarea(
									'Your orders are syncing as deals in the background so you can safely leave this page. It should take ' . $sync_data['eta_deals_sync'] . ' to complete.',
									'hubspot-for-woocommerce'
								);
								?>

						</p>
						<div class="manage-ocs-bar" >						
							<div class="hubwoo-progress-wrap progress-cover deal-sync_progress" style="display: <?php echo esc_attr( $display_data['message'] ); ?>">
								<div class="hubwoo-progress">
									<div class="hubwoo-progress-bar" data-percentage= "<?php echo isset( $sync_data['deals_progress'] ) ? esc_attr( $sync_data['deals_progress'] ) : 0; ?>"  data-sync-type = "order" data-sync-status = "<?php echo esc_attr( $display_data['is_dsync'] ); ?>" role="progressbar" style="width: <?php echo isset( $sync_data['deals_progress'] ) ? esc_attr( $sync_data['deals_progress'] ) : 0; ?>%">
										<?php echo isset( $sync_data['deals_progress'] ) ? esc_textarea( $sync_data['deals_progress'] ) : 0; ?>%
									</div>
								</div> 
							</div>						
							<button class="hubwoo__btn manage_deals_ocs" data-action = "<?php echo esc_attr( $display_data['btn_data'] ); ?>"><?php echo esc_textarea( $display_data['btn_text'], 'hubspot-for-woocommerce' ); ?></button>
						</div>
					</div>				
					<form method="POST" id="hubwoo_deals_ocs_form" class="hubwoo_form_submitted">					
						<?php
						if ( empty( get_option( 'hubwoo_ecomm_order_ocs_status', '' ) ) ) {
							update_option( 'hubwoo_ecomm_order_ocs_status', array_keys( wc_get_order_statuses() ) );
						}
							woocommerce_admin_fields( Hubwoo_Admin::hubwoo_ecomm_order_ocs_settings() );
						?>
					</form>				
				</div>		
			</div>			
		</div>
	</div>
</div>
