<?php
/**
 * The admin-facing file for groups and properties setup.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/admin/templates/setup
 */

global $hubwoo;
$hubwoo_groups          = HubWooContactProperties::get_instance()->_get( 'groups' );
$final_groups           = $hubwoo->hubwoo_get_final_groups();
$hubwoo_required_groups = $hubwoo->hubwoo_workflows_and_list_groups();
$hubwoo_required_props  = $hubwoo->hubwoo_workflows_and_list_properties();
$final_properties       = array_map(
	function( $property ) {
		return str_replace( "'", '', $property );
	},
	get_option( 'hubwoo-properties-created', array() )
);
$field_setup            = $hubwoo->is_field_setup_completed();

if ( 1 == get_option( 'hubwoo_fields_setup_completed', 0 ) ) {
	$cta['will_create'] = 'none';
	$cta['did_created'] = 'inline-block';
	$cta['gen_text']    = 'none';

} else {
	$cta['will_create'] = 'inline-block';
	$cta['did_created'] = 'none';
	$cta['gen_text']    = 'block';
}
?>
<div class="mwb-heb-welcome hubwoo-wrap--groups">
	<div class="hubwoo-box">
		<div class="mwb-heb-wlcm__title">			
			<h2 class="grp-pr-heading">
				<?php esc_html_e( 'Set up groups & properties in HubSpot', 'hubspot-for-woocommerce' ); ?>
			</h2>
		</div>
		<div class="mwb-heb-wlcm__content">
			<div class="hubwoo-content__para">
				<p>
					<?php
						esc_html_e( 'In order to view your WooCommerce data correctly in HubSpot, you need to set up groups and properties in your HubSpot account.', 'hubspot-for-woocommerce' );
					?>
				</p>
				<p>
					<?php esc_html_e( 'Once you set up groups and properties, you can easily see the following information about your contacts and customers:', 'hubspot-for-woocommerce' ); ?>
				</p>
				<ul class="grp-pr-list">
					<li><?php esc_html_e( 'Order information', 'hubspot-for-woocommerce' ); ?></li>
					<li><?php esc_html_e( 'Previous purchases', 'hubspot-for-woocommerce' ); ?></li>
					<li><?php esc_html_e( 'Abandoned cart details', 'hubspot-for-woocommerce' ); ?></li>
					<li><?php esc_html_e( 'And more', 'hubspot-for-woocommerce' ); ?></li>
				</ul>				
			</div>
			<div class="mwb-heb-wlcm__btn-wrap hubwoo-btn-list">
				<a href="javascript:void;" id="hubwoo_create_group_prop_setup" class="hubwoo-btn--primary hubwoo-btn-data" data-action="group_setup" style="display: <?php echo esc_attr( $cta['will_create'] ); ?>"><?php esc_html_e( 'Create Groups & Properties ', 'hubspot-for-woocommerce' ); ?></a>
				<a href="javascript:void;" id="hubwoo-manage-setup" class="hubwoo-btn--primary hubwoo-btn-data" data-action="group_manage_setup" style="display: <?php echo esc_attr( $cta['did_created'] ); ?>"><?php esc_html_e( 'View Created Properties and Groups ', 'hubspot-for-woocommerce' ); ?></a>
			</div>
			<div class="hubwoo-sub-content">
				<div class="hubwoo-group__progress" style="display: none;">
					<p>
						<strong><?php esc_html_e( 'Group and property creation is in progress. This should only take a few moments. Thanks for your patience!', 'hubspot-for-woocommerce' ); ?></strong>
					</p>
					<div class="hubwoo-progress">
						<div class="hubwoo-progress-bar" role="progressbar" style="width:0"></div>
					</div>
				</div>

				<div class="hubwoo-group__manage">
					<div class="hubwoo_groups_container">
						<?php if ( ! $field_setup ) { ?>						

							<div class="hubwoo-group-desc" style="display: none;">
								<p>
									<?php
										esc_html_e(
											'Select the Groups and Properties that you need on HubSpot.<br>
									Click on a Group to Select/De-Select its Group Properties.',
											'hubspot-for-woocommerce'
										);
									?>
																	
								</p>
							</div>
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
															<input checked="checked" name="selectedGroups[]" type="checkbox" class="hub-group" value="<?php echo esc_html( $single_group['name'] ); ?>" data-group="<?php echo esc_attr( $single_group['name'] ); ?>" data-req="<?php echo esc_attr( $group_required ); ?>">
															<?php if ( 'yes' == $group_required ) { ?>
																<span class="hub-woo__checkmark"><?php esc_html_e( 'Required Group for Lists & Workflows', 'hubspot-for-woocommerce' ); ?></span>
															<?php } ?>
														</label>
														<a class="mwb-woo__accordian-heading <?php echo esc_attr( $single_group['name'] ); ?>" data-name="<?php echo esc_attr( $single_group['name'] ); ?>" href="javascript:void;"><?php echo esc_html( $single_group['displayName'] ); ?></a>
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
																					<input checked="checked"  name="selectedProps[]" data-req="<?php echo esc_attr( $prop_required ); ?>" value="<?php echo esc_attr( $single_property['name'] ); ?>" type="checkbox" class="hub-prop">
																					<?php if ( 'yes' == $prop_required ) { ?>
																						<span class="hub-woo__checkmark"><?php esc_html_e( 'Required Property for Lists & Workflows', 'hubspot-for-woocommerce' ); ?></span>
																					<?php } ?>
																				</label>	
																				<div>
																					<?php echo esc_html( $single_property['label'] ); ?>
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
								<div class="hubwoo-full-wdth clearfix">
									<button type="submit" class="hubwoo-form-wizard-setup-btn hubwoo__btn"><?php esc_html_e( 'Create Now', 'hubspot-for-woocommerce' ); ?></button>
								</div>
							</form>
						<?php } ?>
						<?php if ( $field_setup ) { ?>
							<div class="hubwoo-group-wrap__glist">
								<span>
									<?php esc_html_e( 'All of the created Groups and Properties are below.', 'hubspot-for-woocommerce' ); ?>
								</span>
								<div class="mwb-woo__accordian-main-wrapper" id="mwb-woo__accordian-main-wrapper">
									<?php
									if ( count( $final_groups ) ) {

										foreach ( $final_groups as $single_group ) {

											?>
												<div class="mwb-woo__accordion-wrapper">
													<div class="mwb__accordian-heading-wrap">
													<?php
													if ( 'created' == $single_group['status'] ) {
														$anc_class = 'gr_created';
														?>
														<i id="<?php echo esc_attr( 'fa-' . $single_group['detail']['name'] ); ?>" class="fa fa-chevron-right hubwoo-font-icon"></i>
														<a data-name="<?php echo esc_attr( $single_group['detail']['name'], 'hubspot-for-woocommerce' ); ?>" class="mwb-woo__accordian-heading <?php echo esc_attr( $single_group['detail']['name'] ); ?> <?php echo esc_attr( $anc_class ); ?>" href="javascript:void;"><?php echo esc_html( $single_group['detail']['displayName'] ); ?></a>
														<?php
													} else {
														$anc_class = 'gr_uncreated';
														?>
													<i id="<?php echo esc_attr( 'fa--' . $single_group['detail']['name'] ); ?>" class="fa fa-chevron-right hubwoo-font-icon"></i>
															<a class="mwb-woo__accordian-heading <?php echo esc_attr( $single_group['detail']['name'] ); ?> <?php echo esc_attr( $anc_class ); ?>" href="javascript:void;"><?php echo esc_html( $single_group['detail']['displayName'] ); ?></a>
															<?php
													}
													?>
														<?php
															$acc_class = 'mwb-woo__accordion-content';
														if ( 'created' == $single_group['status'] ) {
															?>
																<span class="grSuccess"><?php esc_html_e( 'Created', 'hubspot-for-woocommerce' ); ?></span>
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
																						<?php echo esc_html( $single_property['label'] ); ?>
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
		</div>
	</div>
</div>
