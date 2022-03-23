<?php
/**
 * Automation settings of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/admin/templates/
 */

global $hubwoo;
$final_workflows        = $hubwoo->hubwoo_get_final_workflows();
$workflows_dependencies = $hubwoo->hubwoo_workflows_dependency();
$hubwoo_workflow_desc   = $hubwoo->get_workflow_description();
$automation_enabled     = $hubwoo->is_automation_enabled();
$popup_display          = $automation_enabled ? 'true' : 'false';
?>

<div class="hubwoo-fields-created">
	<input type="hidden" id="get_workflow_scope" value="<?php echo esc_attr( $popup_display ); ?>">	
	<div class="hubwoo_pop_up_wrap" style="display: none">
		<div class="pop_up_sub_wrap">
			<div class="hubwoo_pop_up_wrap--content">
				<div class="hubwoo_pop_up_wrap--inner-content">
					<h2>
						<?php esc_html_e( 'Automate your marketing, sales, and services', 'hubspot-for-woocommerce' ); ?>
					</h2>
					<p style="text-align: center;font-size: 17px;">
						<?php esc_html_e( 'Upgrade your HubSpot plan to create automated workflows.', 'hubspot-for-woocommerce' ); ?>
					</p>
					<div class="button_wrap">
						<a href="https://www.hubspot.com/pricing/marketing" target="_blank" class="upgrade_hubspot_plan"><?php esc_html_e( 'Upgrade plan', 'hubspot-for-woocommerce' ); ?></a>

						<a href="javascript:void(0);" class="hubwoo_manage_screen" data-process="move-to-dashboard" data-tab="hubwoo_tab"><?php esc_html_e( 'Skip this step', 'hubspot-for-woocommerce' ); ?></a>
					</div>
				</div>
			</div>
			<div class="hubwoo_pop_up_wrap--image">
				<div class="hubwoo_pop_up_wrap--image--inner-content">
					<h2>
						<?php esc_html_e( 'Connect with MakeWebBetter to learn more about HubSpot’s plans', 'hubspot-for-woocommerce' ); ?>
					</h2>
					<p>
						<?php esc_html_e( 'MakeWebBetter is a HubSpot Platinum Solutions Partner. Schedule a meeting with our experts to learn more about HubSpot.', 'hubspot-for-woocommerce' ); ?>
					</p>
					<a target="_blank" href="https://meetings.hubspot.com/makewebbetter/free-hubspot-consultation"><?php esc_html_e( 'Schedule meeting', 'hubspot-for-woocommerce' ); ?></a>
				</div>
			</div>
		</div>
	</div>
	<div class="hubwoo-fields-created-list">
		<table>
			<thead>
				<tr>
					<th ><?php esc_html_e( 'Workflow', 'hubspot-for-woocommerce' ); ?></th>
					<th class="hubwoo-field-heading-col hubwoo-align-class"><?php esc_html_e( 'Status', 'hubspot-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php

				if ( is_array( $final_workflows ) && count( $final_workflows ) ) {

					foreach ( $final_workflows as $single_workflow ) {

						if ( isset( $single_workflow['detail'] ) && ! empty( $single_workflow['status'] ) && 'created' === $single_workflow['status'] ) {
							?>
							<tr>
								<td>
									<?php echo esc_textarea( $single_workflow['detail']['name'] ); ?>
									<div class="hubwoo-wf-text">
										<?php
											echo esc_textarea( $hubwoo_workflow_desc[ $single_workflow['detail']['name'] ] );
										?>
									</div>
								</td>
								<td class="hubwoo-align-class">
									<div class="hubwoo-field-checked">
										<span class="hubwoo-cr-btn">Created</span>
									</div>
								</td>
							</tr>
							<?php
						} else {

							if ( $hubwoo->is_workflow_dependent( $single_workflow['detail']['name'] ) ) {
								if ( $automation_enabled ) {
									$class = '';
								} else {
									$class = 'hubwoo-disabled';
								}
								?>
								<tr class="<?php echo esc_attr( $class ); ?>">
									<td>
										<?php echo esc_textarea( $single_workflow['detail']['name'] ); ?>
										<div class="hubwoo-wf-text">
											<?php
												echo esc_textarea( $hubwoo_workflow_desc[ $single_workflow['detail']['name'] ] );
											?>
										</div>
									</td>
									<td class="hubwoo-field-text-col hubwoo-align-class">
										<span class=" hubwoo-create-single-workflow-data hubwoo-cr-btn hubwoo-crd-btn" data-name="<?php echo esc_attr( $single_workflow['detail']['name'] ); ?>"><?php esc_html_e( 'Create', 'hubspot-for-woocommerce' ); ?></span>
									</td>
								</tr>
								<?php
							} else {

								?>
								<tr class="workflow-tab hubwoo-disabled" data-name="<?php echo esc_attr( $single_workflow['detail']['name'] ); ?>">
									<td>
										<?php echo esc_textarea( $single_workflow['detail']['name'] ); ?>
										<div class="hubwoo-wf-text">
											<?php
												echo esc_textarea( $hubwoo_workflow_desc[ $single_workflow['detail']['name'] ] );
											?>
										</div>
									</td>
									<td class="hubwoo-field-text-col hubwoo-align-class">
										<span class="hubwoo-create-single-workflow-data hubwoo-cr-btn hubwoo-crd-btn" data-name="<?php echo esc_attr( $single_workflow['detail']['name'] ); ?>"><?php esc_html_e( 'Create', 'hubspot-for-woocommerce' ); ?></span>														
									</td>
								</tr>
								<?php
							}
						}
					}
				}
				?>
			</tbody>
		</table>
	</div>
</div>
