<?php
/**
 * All HubSpot needed general settings.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/admin/templates/
 */

global $hubwoo;
?>
<?php $hubwoo_groups = HubWooContactProperties::get_instance()->_get( 'groups' ); ?>
<?php $final_groups = $hubwoo->hubwoo_get_final_groups(); ?>
<?php $hubwoo_required_groups = $hubwoo->hubwoo_workflows_and_list_groups(); ?>
<?php $hubwoo_required_props = $hubwoo->hubwoo_workflows_and_list_properties(); ?>
<?php
$final_properties = array_map(
	function( $property ) {
		return str_replace( "'", '', $property );
	},
	get_option( 'hubwoo-properties-created', array() )
);
?>
<?php $field_setup = $hubwoo->is_field_setup_completed(); ?>
<?php $list_setup = $hubwoo->is_list_setup_completed(); ?>
<?php $hubwoo_lists = $hubwoo->hubwoo_get_final_lists(); ?>
<?php $hubwoo_lists_desc = $hubwoo->get_lists_description(); ?>
<?php $portal_id = get_option( 'hubwoo_pro_hubspot_id', '' ); ?>
<div class="hubwoo-gs-wrap hubwoo-gs-wrap--gen">
	<h3 class="hubwoo-setting-heading"><?php esc_html_e( 'Basic Settings', 'hubspot-for-woocommerce' ); ?></h3>
	<div id="hubwoo_create_groups" class="hubwoo-box-card hubwoo-box-n-card">
		<div class="hubwoo-box-n-card">
			<div class="hubwoo-box-n-card__content">
				<div class="hubwoo-fields-header hubwoo-common-header">
					<h2><a target='_blank' href="<?php echo esc_attr( 'https://app.hubspot.com/property-settings/' . $portal_id . '/properties' ); ?>"><?php esc_html_e( 'Groups & Properties', 'hubspot-for-woocommerce' ); ?></a></h2>
				</div>
				<div class="hubwoo-box-card__subtitle">
					<?php
						esc_html_e( 'Groups & properties are used to store data on certain objects in HubSpot, such as contacts, companies, deals, and tickets. You can have up to 1,000 properties per object, including the default HubSpot properties.', 'hubspot-for-woocommerce' );
					?>
				</div>
			</div>
			<div class="hubwoo-box-n-card__btn">
				<div class="hubwoo-btn-cshow__btn">
					<a href="javascript:;" class="hubwoo__btn"><?php esc_html_e( 'Manage', 'hubspot-for-woocommerce' ); ?></a>
				</div>
			</div>
		</div>
		<div class="hubwoo-btn-cshow">
			<div class="hubwoo-btn-cshow__content">
			<?php if ( ! $field_setup ) { ?>
				<form action="" method="post" id="hub-gr-props-form">
					<div class="hubwoo-group-wrap__glist">
						<div class="mwb-woo__accordian-main-wrapper" id="mwb-woo__accordian-main-wrapper">
							<?php

							if ( count( $hubwoo_groups ) ) {

								foreach ( $hubwoo_groups as $key => $single_group ) {
									?>
									<div class="mwb-woo__accordion-wrapper">
										<div class="mwb__accordian-heading-wrap">
											<?php $group_required = in_array( $single_group['name'], $hubwoo_required_groups ) ? 'yes' : 'no'; ?>
											<?php
												$check_gr_class = 'mwb-hub-custom-checkbox';
											if ( 'yes' == $group_required ) {
												$check_gr_class .= ' hub-req-checkbox';
											}
											?>
											<label class="<?php echo esc_attr( $check_gr_class ); ?>">
												<input checked="checked" name="selectedGroups[]" type="checkbox" class="hub-group" value="<?php echo esc_attr( $single_group['name'] ); ?>" data-group="<?php echo esc_attr( $single_group['name'] ); ?>" data-req="<?php echo esc_attr( $group_required ); ?>">
												<?php if ( 'yes' == $group_required ) { ?>
													<span class="hub-woo__checkmark"><?php esc_html_e( 'Required Group for Lists & Workflows', 'hubspot-for-woocommerce' ); ?></span>
												<?php } ?>
											</label>
											<a class="mwb-woo__accordian-heading <?php echo esc_attr( $single_group['name'] ); ?>" data-name="<?php echo esc_attr( $single_group['name'] ); ?>" href="javascript:;"><?php esc_html( $single_group['displayName'] ); ?></a>
											<i id="fa-drag-<?php echo esc_attr( $single_group['name'] ); ?>" class=" fa fa-plus grToCreate"></i>																						
										</div>
										<div class="mwb-woo__accordion-content" id="<?php echo esc_attr( $single_group['name'] ); ?>" style="display: none;">
											<ul class="mwb-woo__custom-prop">
												<?php $hubwoo_groups_properties = HubWooContactProperties::get_instance()->_get( 'properties', '', true ); ?>
												<?php

												foreach ( $hubwoo_groups_properties as $group => $hubwoo_properties ) {
													if ( $group == $single_group['name'] ) {
														if ( count( $hubwoo_properties ) ) {
															foreach ( $hubwoo_properties as $single_property ) {

																$prop_required = in_array( $single_property['name'], $hubwoo_required_props ) ? 'yes' : 'no';

																$check_pr_class = 'mwb-hub-custom-checkbox';
																if ( 'yes' == $prop_required ) {
																	$check_pr_class .= ' hub-req-checkbox';
																}
																?>

																<li class="mwb-woo__custom">
																	<label class="<?php echo esc_attr( $check_pr_class ); ?>">
																		<input checked="checked" name="selectedProps[]" data-req="<?php echo esc_attr( $prop_required ); ?>" value="<?php echo esc_attr( $single_property['name'] ); ?>" type="checkbox" class="hub-prop">
																		<?php if ( 'yes' == $prop_required ) { ?>
																			<span class="hub-woo__checkmark"><?php esc_html_e( 'Required Property for Lists & Workflows', 'hubspot-for-woocommerce' ); ?></span>
																		<?php } ?>
																	</label>	
																	<div>
																		<?php esc_html( $single_property['label'] ); ?>
																	</div>
																</li>
																<?php
															}
														}
													}
												}
												?>
											</ul>
										</div>
									</div>
									<?php
								}
							}
							?>
						</div>
					</div>
				</form>
			<?php } ?>
			<?php if ( $field_setup ) { ?>
				<div class="hubwoo-group-wrap__glist">
					<div class="mwb-woo__accordian-main-wrapper" id="mwb-woo__accordian-main-wrapper">
						<?php
						if ( count( $final_groups ) ) {
							foreach ( $final_groups as $single_group ) {

								?>
									<div class="mwb-woo__accordion-wrapper">
										<div class="mwb__accordian-heading-wrap">
										<?php
										if ( 'created' == $single_group['status'] ) {
											$res_class = 'gr_created';
											?>
											<i id="<?php echo 'fa-' . esc_attr( $single_group['detail']['name'] ); ?>" class="fa fa-chevron-right hubwoo-font-icon"></i>
											<a data-name="<?php echo esc_attr( $single_group['detail']['name'] ); ?>" class="mwb-woo__accordian-heading <?php echo esc_attr( $single_group['detail']['name'] ); ?> <?php echo esc_attr( $res_class ); ?>" href="javascript:;"><?php echo esc_textarea( $single_group['detail']['displayName'] ); ?></a>
											<?php
										} else {
											$res_class = 'gr_uncreated';
											?>
										<i id="<?php echo 'fa--' . esc_attr( $single_group['detail']['name'] ); ?>" class="fa fa-chevron-right hubwoo-font-icon"></i>
												<a class="mwb-woo__accordian-heading <?php echo esc_attr( $single_group['detail']['name'] ); ?> <?php echo esc_attr( $res_class ); ?>" href="javascript:;"><?php echo esc_attr( $single_group['detail']['displayName'] ); ?></a>
												<?php
										}
										?>
											<?php
												$acc_class = 'mwb-woo__accordion-content';
											if ( 'created' == $single_group['status'] ) {
												?>
														<span class="grSuccess">Created</span>
													<?php
											} else {
												$acc_class .= '-disable';
												?>
													<span data-name="<?php echo esc_attr( $single_group['detail']['name'] ); ?>" class="hubwoo-create-single-group grCreateNew hubwoo-cr-btn hubwoo-crd-btn grSuccess"><?php esc_html_e( 'Create', 'hubspot-for-woocommerce' ); ?></span>
														<?php
											}
											?>
											</div>
											<div class="<?php echo esc_attr( $acc_class ); ?>" id="<?php echo esc_attr( $single_group['detail']['name'] ); ?>" style="display: none;">
												<ul class="mwb-woo__custom-prop">
												<?php $hubwoo_groups_properties = HubWooContactProperties::get_instance()->_get( 'properties', '', true ); ?>
												<?php
												foreach ( $hubwoo_groups_properties as $group => $hubwoo_properties ) {
													if ( $group == $single_group['detail']['name'] ) {

														if ( count( $hubwoo_properties ) ) {

															foreach ( $hubwoo_properties as $single_property ) {
																?>
																	<li class="mwb-woo__custom">
																		<?php
																		if ( in_array( $single_property['name'], $final_properties ) ) {
																			?>
																				<label class="hub-pr-created">
																					<span class=""></span>
																					<i class="fa fa-check"></i>
																				</label>
																			<?php
																		} else {
																			?>
																				<label class="hub-pr-create">
																					<span data-group ="<?php echo esc_attr( $single_group['detail']['name'] ); ?>" data-name="<?php echo esc_attr( $single_property['name'] ); ?>" class="prCreateNew hubwoo-create-single-field">
																						<i class="fa fa-plus pr-<?php echo esc_attr( $single_property['name'] ); ?>" aria-hidden="true"></i>
																				</label>
																				<?php
																		}
																		?>
																			<div>
																			<?php echo esc_textarea( $single_property['label'] ); ?>
																			</div>
																		 </li>
																	<?php
															}
														}
													}
												}
												?>
												</ul>
											</div>
										</div>
									<?php
							}
						}
						?>
					</div>
				</div>
			<?php } ?>
			</div>
		</div>
	</div>

	<div id="hubwoo_create_lists" class="hubwoo-box-card">

		<div class="hubwoo-box-n-card">
			<div class="hubwoo-box-n-card__content">
				<div class="hubwoo-fields-header hubwoo-common-header">
					<h2><a a target='_blank' href="<?php echo esc_attr( 'https://app.hubspot.com/contacts/' . $portal_id . '/lists' ); ?>"><?php esc_html_e( 'Lists', 'hubspot-for-woocommerce' ); ?></a></h2>
				</div>
				<div class="hubwoo-box-card__subtitle">
					<?php
					esc_html_e( 'Lists are HubSpot lists that update automatically, enrolling the contacts who meet the membership criteria and removing those who no longer meet it.', 'hubspot-for-woocommerce' );
					?>
				</div>
			</div>
			<div class="hubwoo-box-n-card__btn">
				<div class="hubwoo-btn-cshow__btn">
					<a href="javascript:;" class="hubwoo__btn"><?php esc_html_e( 'Manage', 'hubspot-for-woocommerce' ); ?></a>			
				</div>
			</div>
		</div>
		<div class="hubwoo-btn-cshow">
			<div class="hubwoo-btn-cshow__content">
		<?php if ( ! $list_setup ) { ?>
			<form action="" method="post" id="hub-lists-form">
				<div class="hubwoo-fields-created">
					<?php
					if ( count( $hubwoo_lists ) ) {

						foreach ( $hubwoo_lists as $key => $single_list ) {

							?>
							<div class="hubwoo_groups">
								<label class="hubwoo-custom-chheckbox">
									<input checked="checked" name="selectedLists[]" type="checkbox" class="hub-lists" value="<?php echo esc_html( $single_list['detail']['name'] ); ?>">
								</label>
								<span class=""><?php echo esc_attr( $single_list['detail']['name'] ); ?></span>
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
										<span class="hubwoo-cr-btn">Created</span>
									</label>
									
								<?php } else { ?>
									<label class="hubwoo-list-create">
										<span data-name="<?php echo esc_attr( $single_list['detail']['name'] ); ?>" class="hubwoo-create-single-list hubwoo-cr-btn hubwoo-crd-btn">Create</span>
									</label>
									
								<?php } ?>
								<span class=""><?php echo esc_attr( $single_list['detail']['name'] ); ?></span>
									<div class="hubwoo_groups-content">
									<?php echo esc_html( $hubwoo_lists_desc[ $single_list['detail']['name'] ] ); ?>
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
	<h3 class="hubwoo-setting-heading"><?php esc_html_e( 'Advanced Settings', 'hubspot-for-woocommerce' ); ?></h3>
	<div class="hubwoo-form-wizard-content hubwoo-box-card show" data-tab-content="plugin-settings">
		<div class="hubwoo-group-wrap__general_settings">
			<div class="hubwoo-settings-container">
				<div class="hubwoo-general-settings hubwoo-adv-settingg">
					<div class="hubwoo-adv-settingg__wrapper">
						<div class="hubwoo-adv-settingg__con">
							<h2 class="hubwoo-adv-settingg__heading">
								<?php esc_html_e( 'Plugin Settings', 'hubspot-for-woocommerce' ); ?>
							</h2>
							<div class="hubwoo-adv-settingg__content">
								<p>
									<?php
									esc_html_e( "Manage all your HubSpot plugin's additional settings from here.", 'hubspot-for-woocommerce' );
									?>
																		
								</p>
							</div>
						</div>
						<div class="hubwoo-adv-settingg__btn">							
							<a href="javascript:;" class="hubwoo__btn">
								<?php esc_html_e( 'Manage', 'hubspot-for-woocommerce' ); ?>
							</a>
						</div>
					</div>
					<form action="" method="post" id="plugin-settings-gen-adv" class="hubwoo-adv-settingg__form">
						<?php
						if ( empty( get_option( 'hubwoo-order-statuses', '' ) ) ) {
							update_option( 'hubwoo-order-statuses', array_keys( wc_get_order_statuses() ) );
						}

						if ( empty( get_option( 'hubwoo-selected-user-roles', '' ) ) ) {
							update_option( 'hubwoo-selected-user-roles', array_keys( Hubwoo_Admin::get_all_user_roles() ) );
						}

						?>
						<?php woocommerce_admin_fields( Hubwoo_Admin::hubwoo_get_plugin_settings() ); ?>
					</form>
				</div>
			</div>				
		</div>			
	</div>

	<div class="hubwoo-form-wizard-content hubwoo-box-card" data-tab-content="rfm-settings">
		<div class="hubwoo-group-wrap__rfm-settings hubwoo-adv-settingg">
		<?php

			$rfm_settings = new Hubwoo_RFM_Configuration();
			$rfm_settings->prepare_items();
		?>
			<div class="hubwoo-adv-settingg__wrapper">
				<div class="hubwoo-adv-settingg__con">
					<h2 class="hubwoo-adv-settingg__heading">
						<?php esc_html_e( 'RFM Settings( Manage ROI Tracking )', 'hubspot-for-woocommerce' ); ?>
					</h2>
					<div class="hubwoo-adv-settingg__content">
						<p>
							<?php
								esc_html_e( 'RFM (Recency, Frequency and Monetary) segmentation allows marketers to target specific clusters of customers with communications that are much more relevant for their particular behavior – and thus generate much higher rates of response, plus increased loyalty and customer lifetime value.', 'hubspot-for-woocommerce' );
							?>
								
						</p>
					</div>
				</div>

				<div class="hubwoo-adv-settingg__btn">
					<a href="javascript:;" class="hubwoo__btn">
						<?php esc_html_e( 'Manage', 'hubspot-for-woocommerce' ); ?>
					</a>
				</div>
			</div>
			<form action="" method="post" id="hubwoo-rfm-form" class="hubwoo-adv-settingg__form">
				<div class="hubwoo_rfm_settings">
					<?php
						$rfm_settings->display();
					?>
				</div>
			</form>				
		</div>			
	</div>
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
						<a href="javascript:void(0);" class="hubwoo_manage_screen" data-tab="hubwoo_tab" data-process="skip-list-creation"><?php esc_html_e( 'Skip this step', 'hubspot-for-woocommerce' ); ?></a>
					</div>
				</div>
			</div>
			<div class="hubwoo_pop_up_wrap--image">
				<div class="hubwoo_pop_up_wrap--image--inner-content">
					<h2>
						<?php esc_html_e( 'Connect with MakeWebBetter to learn more about HubSpot’s plans', 'hubspot-for-woocommerce' ); ?>
					</h2>
					<p>
						<?php esc_html_e( 'MakeWebBetter is a HubSpot Gold Solutions Partner. Schedule a meeting with our experts to learn more about HubSpot', 'hubspot-for-woocommerce' ); ?>
					</p>
					<a href="https://meetings.hubspot.com/makewebbetter/free-hubspot-consultation"><?php esc_html_e( 'Schedule meeting', 'hubspot-for-woocommerce' ); ?></a>
				</div>
			</div>
		</div>
	</div>
</div>
