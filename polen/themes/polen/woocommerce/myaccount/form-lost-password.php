<?php

/**
 * Lost password form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-lost-password.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.2
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_lost_password_form');
$inputs = new Material_Inputs();
?>

<div class="row">
	<div class="col-12 col-md-6 m-md-auto">
		<h1>Esqueci minha senha</h1>
	</div>
</div>
<div class="row justify-content-md-center">
	<div class="col-md-6 mt-4">
		<form method="post" class="woocommerce-ResetPassword lost_reset_password">

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide mb-3">
        <?php $inputs->material_input(Material_Inputs::TYPE_TEXT, "user_login", "user_login", "Nome de usuário ou e-mail", true, "", array("autocomplete" => "username")); ?>
			</p>

			<div class="clear"></div>

			<p class="text-center"><?php echo apply_filters('woocommerce_lost_password_message', esc_html__('Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'woocommerce')); ?></p>

			<?php do_action('woocommerce_lostpassword_form'); ?>

			<p class="woocommerce-form-row form-row">
				<input type="hidden" name="wc_reset_password" value="true" />
        <?php $inputs->material_button(Material_Inputs::TYPE_SUBMIT, "btn-submit", "Redefinir senha"); ?>
			</p>

			<?php wp_nonce_field('lost_password', 'woocommerce-lost-password-nonce'); ?>

		</form>
	</div>
</div>
<?php
do_action('woocommerce_after_lost_password_form');
