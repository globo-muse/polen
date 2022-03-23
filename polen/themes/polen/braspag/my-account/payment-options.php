<?php

require_once PLUGIN_CUBO9_BRASPAG_DIR . 'classes/class.Cubo9_Braspag.php';
$braspag = new Cubo9_Braspag( false, false );
$braspag_card_saved_data = $braspag->list_user_cards();
$braspag_default_payment = get_user_meta( get_current_user_id(), 'braspag_default_payment', true );

use Polen\Includes\Polen_Talent;

$polen_talent = new Polen_Talent();
$current_user = wp_get_current_user();

if ($polen_talent->is_user_talent($current_user)) {
	require get_template_directory() . '/braspag/my-account/payment-talent.php';
} else {
?>
	<div class="row mb-3">
		<div class="col-md-12">
			<h1>Meus Cartões</h1>
		</div>
	</div>
	<div class="woocommerce-Payment-Options payment-options">
		<div class="row">
			<div class="col-md-12">
				<?php if (!is_null($braspag_card_saved_data) && !empty($braspag_card_saved_data) && is_array($braspag_card_saved_data) && count($braspag_card_saved_data) > 0) : ?>
					<?php foreach ($braspag_card_saved_data as $p => $data) : $prefix = md5($data['id']); ?>
						<div id="#payment-<?php echo $prefix; ?>" class="<?php echo $data['id']; ?> box-round d-flex justify-content-between align-items-center px-3 py-4 mb-3 payment-method-item">
							<div class="d-flex align-items-center">
								<?php Icon_Class::polen_icon_card( strtolower( $data['brand'] ) ); ?>
								<span class="sufix">****<?php echo $data['sufix']; ?></span>
							</div>
							<div>
								<?php
								if ($braspag_default_payment && !is_null($braspag_default_payment) && !empty($braspag_default_payment) && $prefix == $braspag_default_payment) {
									$title = 'Desmarcar padrão';
								} else {
									$title = 'Definir como padrão';
								}
								?>
								<?php /* <a href="#" title="<?php echo $title; ?>" class="braspag-make-default-payment" default-id="<?php echo $prefix; ?>" brand-name="<?php echo $data['brand']; ?>">
									<span class="glyphicon <?php echo $class; ?>" aria-hidden="true">padrao</span>
								</a> */ ?>
								<a href="#" title="Remover" class="text braspag-remove-payment" remove-id="<?php echo $prefix; ?>">
									<?php Icon_Class::polen_icon_trash(); ?>
								</a>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="row">
						<div class="col-12 text-center my-3">
							<?php polen_box_image_message(TEMPLATE_URI . "/assets/img/cards.png", "Você ainda não adicionou nenhuma<br />forma de pagamento"); ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<a class="woocommerce-Button btn btn-primary btn-lg btn-block" href="<?php echo wc_get_account_endpoint_url( 'add-payment-option'); ?>">Adicionar cartão</a>
			</div>
		</div>
	</div>
<?php
}
?>
