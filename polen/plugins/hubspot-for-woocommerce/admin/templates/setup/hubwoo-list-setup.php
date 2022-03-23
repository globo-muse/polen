<?php
/**
 * The admin-facing file for list setup.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/admin/templates/setup
 */

global $hubwoo;
$hubwoo->is_automation_enabled();
$list_setup        = $hubwoo->is_list_setup_completed();
$hubwoo_lists      = $hubwoo->hubwoo_get_final_lists();
$hubwoo_lists_desc = $hubwoo->get_lists_description();
$dynamic_lists     = HubWooConnectionMananager::get_instance()->get_dynamic_lists();

if ( 1 == get_option( 'hubwoo_pro_lists_setup_completed', 0 ) ) {
	$cta['create']   = 'none';
	$cta['created']  = 'inline-block';
	$cta['gen-text'] = 'none';
} else {
	$cta['create']   = 'inline-block';
	$cta['created']  = 'none';
	$cta['gen-text'] = 'block';
}

?>
<div id="hubwoo-lists-setup" class="mwb-heb-welcome hubwoo-wrap--list">
	<div class="hubwoo_pop_up_wrap" style="display: none">
		<div class="pop_up_sub_wrap">
			<div class="hubwoo_pop_up_wrap--content">
				<div class="hubwoo_pop_up_wrap--inner-content">
					<h2>
						<?php esc_html_e( 'Create more dynamic / active lists', 'hubspot-for-woocommerce' ); ?>
					</h2>
					<p style="text-align: center;font-size: 17px;">
						<?php esc_html_e( 'You have reached maximum limit of Dynamic / Active Lists creation as per your CRM plan.', 'hubspot-for-woocommerce' ); ?>
					</p>
					<div class="button_wrap">
						<a href="https://www.hubspot.com/pricing/marketing" target="_blank" class="upgrade_hubspot_plan"><?php esc_html_e( 'Upgrade plan', 'hubspot-for-woocommerce' ); ?></a>
						<a href="javascript:void(0);" class = "hubwoo_manage_screen" data-process="skip-list-creation"><?php esc_html_e( 'Skip this step', 'hubspot-for-woocommerce' ); ?></a>
					</div>
				</div>
			</div>
			<div class="hubwoo_pop_up_wrap--image">
				<div class="hubwoo_pop_up_wrap--image--inner-content">
					<h2>
						<?php esc_html_e( 'Connect with MakeWebBetter to learn more about HubSpot’s plans', 'hubspot-for-woocommerce' ); ?>
					</h2>
					<p>
						<?php esc_html_e( 'MakeWebBetter is a HubSpot Gold Solutions Partner. Schedule a meeting with our experts to learn more about HubSpot.', 'hubspot-for-woocommerce' ); ?>
					</p>
					<a href="https://meetings.hubspot.com/makewebbetter/free-hubspot-consultation" target="_blank" ><?php esc_html_e( 'Schedule meeting', 'hubspot-for-woocommerce' ); ?></a>
				</div>
			</div>
		</div>
	</div>
	<div class="hubwoo-box">
		<div class="mwb-heb-wlcm__title">			
			<h2 class="list-setup-heading">
				<?php esc_html_e( 'Create lists in HubSpot', 'hubspot-for-woocommerce' ); ?>
			</h2>
		</div>
		<div class="mwb-heb-wlcm__content">
			<div class="hubwoo-content__para">
				<p>
					<?php
					esc_html_e( 'Set up lists to segment your contacts and customers based on their previous actions and behaviors.', 'hubspot-for-woocommerce' );
					?>

				</p>
				<div>				
					<p>
						<?php
						esc_html_e( 'You can set up the following lists:', 'hubspot-for-woocommerce' );
						?>
						<ul class="connect-list">
							<li><?php esc_html_e( 'Leads: contacts that have not yet made any orders', 'hubspot-for-woocommerce' ); ?></li>
							<li><?php esc_html_e( 'Customers: contacts that have made at least one order', 'hubspot-for-woocommerce' ); ?></li>
							<li><?php esc_html_e( 'Abandoned Cart: contacts that have added products to their carts, but have not purchased', 'hubspot-for-woocommerce' ); ?></li>
						</ul>
					</p>
				</div>				
			</div>	
			<div class="mwb-heb-wlcm__btn-wrap hubwoo-btn-list">
				<a href="#" class="hubwoo-btn--primary hubwoo-btn-data" data-action="lists_setup" style="display: <?php echo esc_attr( $cta['create'] ); ?>";><?php esc_html_e( 'Create Lists', 'hubspot-for-woocommerce' ); ?></a>
				<a href="#" class="hubwoo-btn--primary hubwoo-btn-data" data-action="lists_setup_manage" style="display: <?php echo esc_attr( $cta['created'] ); ?>"; ><?php esc_html_e( 'View All Lists', 'hubspot-for-woocommerce' ); ?></a>
			</div>
			<div class="hubwoo-sub-content">
				<div class="hubwoo-list__progress list-progress-bar" style="display: none;">
					<p>
						<strong><?php esc_html_e( 'List creation is in progress. This should only take a few moments. Thanks for your patience!', 'hubspot-for-woocommerce' ); ?></strong>
					</p>	
					<div class="hubwoo-progress">							
						<div class="hubwoo-progress-bar" role="progressbar" style="width:0">
							
						</div>
					</div>
				</div>
				<div class="hubwoo-list__manage" style="display: none;">
					<?php if ( ! $list_setup ) { ?>
						<div class="hubwoo-list-desc" style="display: none;">
							<div class="hubwoo-list-desc__notice-sec">
								<p>
									<?php
									esc_html_e( 'Select the Groups and Properties that you need on HubSpot.', 'hubspot-for-woocommerce' );
									?>
								</p>
							</div>
						</div>								
						<form action="" method="post" id="hub-lists-form">
							<div class="hubwoo-fields-created">
								<?php
								if ( count( $hubwoo_lists ) ) {

									foreach ( $hubwoo_lists as $key => $single_list ) {

										?>
										<div class="hubwoo_groups">
											<label class="hubwoo-custom-chheckbox">
												<input <?php echo esc_attr( $hubwoo->required_lists_to_create( $single_list['detail']['name'] ) ); ?> name="selectedLists[]" type="checkbox" class="hub-lists" value="<?php echo esc_html( $single_list['detail']['name'] ); ?>">
											</label>
											<span class=""><?php echo esc_html( $single_list['detail']['name'] ); ?></span>
											<div class="hubwoo_groups-content hubwoo_groups-content--p">
												<?php
												echo esc_html( $hubwoo_lists_desc[ $single_list['detail']['name'] ] );
												?>
											</div>
										</div>
										<?php
									}
								}
								?>
							</div>
						</form>
					<?php } else { ?>
						<div class="hubwoo-fields-created">
							<?php
							if ( count( $hubwoo_lists ) ) {

								foreach ( $hubwoo_lists as $key => $single_list ) {

									?>
									<div class="hubwoo_groups">
										<?php if ( 'created' == $single_list['status'] ) { ?>
											<label class="hubwoo-list-created">
												<span class="hubwoo-cr-btn"><?php esc_html_e( 'Created', 'hubspot-for-woocommerce' ); ?></span>
											</label>

										<?php } else { ?>
											<label class="hubwoo-list-create">
												<span data-name="<?php echo esc_attr( $single_list['detail']['name'] ); ?>" class="hubwoo-create-single-list hubwoo-cr-btn hubwoo-crd-btn"><?php esc_html_e( 'Create', 'hubspot-for-woocommerce' ); ?></span>
											</label>

										<?php } ?>
										<span class=""><?php echo esc_html( $single_list['detail']['name'] ); ?></span>
										<div class="hubwoo_groups-content">
											<?php
											echo esc_html( $hubwoo_lists_desc[ $single_list['detail']['name'] ] );
											?>
										</div>
									</div>
									<?php
								}
							}
							?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
