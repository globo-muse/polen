<?php
global $Polen_Plugin_Settings;
$site_key = $Polen_Plugin_Settings['polen_recaptcha_site_key'];
$inputs = new Material_Inputs();
?>
<div class="woocommerce">
	<div class="row justify-content-md-center talent-login">
		<div class="col-12 col-md-6 mx-md-auto" id="customer_register">
			<div class="row">
				<div class="col-12 col-md-12">
					<h1><?php esc_html_e('Register', 'woocommerce'); ?></h1>
				</div>
			</div>
			<?php do_action('woocommerce_before_customer_login_form'); ?>
			<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag'); ?>>

				<?php //do_action('woocommerce_register_form_start'); ?>

				<div class="row">
					<div class="col-12 col-md-12">
						<?php if ('no' === get_option('woocommerce_registration_generate_username')) : ?>

							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide mb-4">
								<input type="text" placeholder="<?php esc_html_e('Username', 'woocommerce'); ?>" class="woocommerce-Input woocommerce-Input--text input-text form-control form-control-lg" name="username" id="reg_username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" /><?php // @codingStandardsIgnoreLine ?>
							</p>

						<?php endif; ?>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide mb-4">
              <?php $inputs->material_input(Material_Inputs::TYPE_EMAIL, "reg_email", "email", "Endereço de e-mail", true, "", array("value" => (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : '')); ?>
						</p>

						<?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>

							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide mb-4">
                <?php $inputs->material_input(Material_Inputs::TYPE_PASSWORD, "reg_password", "password", "Senha", true); ?>
							</p>

						<?php else : ?>

							<p class="mb-4"><?php esc_html_e('A password will be sent to your email address.', 'woocommerce'); ?></p>

						<?php endif; ?>

						<?php do_action('woocommerce_register_form'); ?>
						<p class="form-row validate-required woocommerce-invalid woocommerce-invalid-required-field">
							<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox d-flex">
							<input type="checkbox" class="form-control form-control-lg" name="terms" id="terms" value="1" >
								<span class="woocommerce-terms-and-conditions-checkbox-text ml-2"><?= wc_terms_and_conditions_checkbox_text(); ?>*</span>
							</label>
							<input type="hidden" name="terms-field" value="1">
						</p>

						<p class="woocommerce-form-row form-row">
							<?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
              <?php $inputs->material_button(Material_Inputs::TYPE_SUBMIT, "register", "Cadastre-se", "woocommerce-button btn-login woocommerce-form-register__submit g-recaptcha", array("data-sitekey" => $site_key, "data-callback" => "polen_onSubmit", "data-action", "submit", "name" => "register")); ?>
							<input type="hidden" name="register" value="Cadastre-se" />
              <input type="hidden" name="zapier" value="2" />
						</p>
					</div>
				</div>

				<?php do_action('woocommerce_register_form_end'); ?>
			</form>
		</div>
	</div>
</div>
