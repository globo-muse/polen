<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */
defined( 'ABSPATH' ) || exit;

use \Polen\includes\Polen_Talent;
use \Polen\includes\Polen_Account;

$user = wp_get_current_user();
$polen_talent = new Polen_Talent;
if( $polen_talent->is_user_talent( $user ) ) {
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    get_template_part( 404 );
    exit();
}else{

do_action( 'woocommerce_before_edit_account_form' ); ?>
<div class="row mb-3">
	<div class="col-md-12">
		<h1>Meus Dados</h1>
	</div>
</div>

<?php $inputs = new Material_Inputs(); ?>

<form class="woocommerce-EditAccountForm edit-account mt-3" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> enctype="multipart/form-data" >
  <?php
  $inputs->input_hidden("wpua_action", "update");
  $inputs->input_hidden("user_id", esc_attr($user->ID));
  wp_nonce_field('update-user_'.$user->ID);
  ?>

	<?php
	if (is_plugin_active('wp-user-avatar/wp-user-avatar.php')) {
		$polen_account = new Polen_Account;

		$wpuavatar = new WP_User_Avatar();
		$wpuavatar->wpua_media_upload_scripts();
		$polen_account->polen_core_show_user_profile($user);
	}
	?>

	<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

  <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide mb-4">
    <?php $inputs->material_input(Material_Inputs::TYPE_PHONE, "billing_phone", "billing_phone", "Celular", false, "", array("value" => esc_attr( $user->billing_phone ))); ?>
  </p>
	<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first mb-4">
    <?php $inputs->material_input(Material_Inputs::TYPE_TEXT, "account_first_name", "account_first_name", "Nome", false, "", array("value" => esc_attr( $user->first_name ), "autocomplete" => "given-name")); ?>
	</p>
	<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last mb-4">
    <?php $inputs->material_input(Material_Inputs::TYPE_TEXT, "account_last_name", "account_last_name", "Sobrenome", false, "", array("value" => esc_attr( $user->last_name ), "autocomplete" => "family-name")); ?>
	</p>
	<div class="clear"></div>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide mb-4">
    <?php $inputs->material_input(Material_Inputs::TYPE_TEXT, "account_display_name", "account_display_name", "Nome de exibição", false, "", array("value" => esc_attr( $user->display_name ))); ?>
		<?php $inputs->material_input_helper(esc_html_e( 'This will be how your name will be displayed in the account section and in reviews', 'woocommerce' )); ?>
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <?php $inputs->material_input(Material_Inputs::TYPE_EMAIL, "account_email", "account_email", "Endereço de e-mail", false, "", array("value" => esc_attr( $user->user_email ), "autocomplete" => "email")); ?>
	</p>

	<fieldset class="mt-4">
		<legend class="col-form-label mb-2"><?php esc_html_e( 'Password change', 'woocommerce' ); ?></legend>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide mb-4">
      <?php $inputs->material_input(Material_Inputs::TYPE_PASSWORD, "password_current", "password_current", "Senha atual", false, "", array("autocomplete" => "off")); ?>
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide mb-4">
      <?php $inputs->material_input(Material_Inputs::TYPE_PASSWORD, "password_1", "password_1", "Nova senha", false, "", array("autocomplete" => "off")); ?>
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
      <?php $inputs->material_input(Material_Inputs::TYPE_PASSWORD, "password_2", "password_2", "Confirmar nova senha", false, "", array("autocomplete" => "off")); ?>
		</p>
	</fieldset>

	<?php do_action( 'woocommerce_edit_account_form' ); ?>

	<p class="mt-4">
		<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
    <?php $inputs->material_button(Material_Inputs::TYPE_SUBMIT, "save_account_details", "Salvar alterações"); ?>
		<?php $inputs->input_hidden("action", "save_account_details"); ?>
	</p>

	<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>

<?php
}
?>
