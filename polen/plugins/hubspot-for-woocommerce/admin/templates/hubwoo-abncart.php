<?php
/**
 * Abandoned Cart settings template.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    hubspot-for-woocommerce
 * @subpackage hubspot-for-woocommerce/admin/templates/
 */

?>
<div class="hubwoo-form-wizard-wrapper">
	<div class="hubwoo-form-wizard-content-wrapper">
		<div class="hubwoo-form-wizard-content show" data-tab-content="abandon-cart-setup">
			<div class="hubwoo-settings-container">
				<div class="hubwoo-general-settings">
					<div class="hubwoo-group-wrap__abandon_cart_setup">
						<form action="" method="post" class="hubwoo-abncart-setup-form hubwoo-abncart-setup-form--d" >
							<?php woocommerce_admin_fields( Hubwoo_Admin::hubwoo_abncart_general_settings() ); ?>									
						</form>
					</div>
				</div>
			</div>		
		</div>
	</div>
</div>
