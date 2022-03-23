<?php
$WC_Cubo9_Braspag_Helper = new WC_Cubo9_Braspag_Helper;
$brands = $WC_Cubo9_Braspag_Helper->active_credit_card_brands;
global $woocommerce, $WC_Cubo9_BraspagReduxSettings;
$amount = $woocommerce->cart->total;
if( is_user_logged_in() ) {
    //$braspag_card_saved_data = get_user_meta( get_current_user_id(), 'braspag_card_saved_data', true );
    $c9_braspag = new Cubo9_Braspag( false, false );
    $braspag_card_saved_data = $c9_braspag->list_user_cards( get_current_user_id() );
} else {
    $braspag_card_saved_data = false;
}
?>

<div class="form-group" id="div_braspag_payment">
    <?php if( (int) $WC_Cubo9_BraspagReduxSettings['enable_installments'] === (int) 1 ) : ?>
		<!-- Formas de Pagamento Parcelado -->
		<div class="row">
			<div class="col-12">
				<div class="row">
					<div class="col col-12">
						<label for="braspag_creditcardInstallments"><?php echo __( 'Parcelas', 'cubo9-marketplace' ); ?></label>
						<select class="form-control form-control-lg" name="braspag_creditcardInstallments" id="braspag_creditcardInstallments" aria-describedby="<?php echo __( 'Parcelas', 'cubo9' ); ?>">
							<?php
							$installments = $WC_Cubo9_Braspag_Helper->calculate_installments( $amount );
							if( count( $installments ) > 0 ) {
								foreach( $installments as $installment => $value ) {
									$label = ( $installment == 1 ) ? ' parcela' : ' parcelas';
							?>
								<option value="<?php echo $installment; ?>"><?php echo $installment . ' ' . $label; ?> de R$ <?php echo $value; ?></option>
							<?php
								}
							}
							?>
						</select>
					</div>
					<div class="col-md-6 col-xs-12">
					</div>
				</div>
			</div>
		</div>
    <?php endif; ?>

    <?php if( $braspag_card_saved_data && ! is_null( $braspag_card_saved_data ) && ! empty( $braspag_card_saved_data ) && is_array( $braspag_card_saved_data ) && count( $braspag_card_saved_data ) > 0 ) : ?>
    <!-- Cartões Salvos -->
    <div class="row" id="div_brasapag_creditcard_saved">
        <div class="col col-12">
            <h4>Pagar utilizando o </h4>
        </div>
        <div class="col col-12" style="padding-bottom: 15px;">
            <select class="form-control form-control-lg custom-select" name="brasapag_creditcard_saved" id="brasapag_creditcard_saved">
                <?php foreach( $braspag_card_saved_data as $data ) : ?>
                	<option value="<?php echo $data['prefix'] . md5( time() . $amount . $data['prefix'] ) . $data['id']; ?>"><?php echo $data['brand']; ?> <?php echo __( 'final', 'cubo9' ); ?> <?php echo $data['sufix']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col col-12">
            <button type="button" class="btn btn-outline-light btn-lg" name="braspag_pay_with_new_card" id="braspag_pay_with_new_card">
                Pagar utilizando outro cartão
            </button>
        </div>
        <input type="hidden" name="braspag_use_saved_card" id="braspag_use_saved_card" value="1">
    </div>
    <?php endif; ?>

    <!-- Cartão Avulso ou Novo Cartão -->
    <div class="row" id="div_brasapag_creditcard_data">
        <?php if( ! is_null( $braspag_card_saved_data ) && ! empty( $braspag_card_saved_data ) && is_array( $braspag_card_saved_data ) && count( $braspag_card_saved_data ) > 0 ) : ?>
        <div class="col col-12" style="padding-bottom: 15px;">
            <button type="button" class="btn btn-outline-light btn-lg" name="braspag_pay_with_saved_cards" id="braspag_pay_with_saved_cards">
                Pagar utilizando um dos cartões salvos
            </button>
        </div>
        <?php endif; ?>

        <!-- Alertas -->
        <div id="braspag_alerts" class="col col-12 alert alert-danger"></div>

        <!-- Dados do cartão -->
        <div class="col-12">
            <div class="row">
				<div class="col-12">
                    <label for="braspag_creditcardNumber"><?php echo __( 'Número do cartão', 'cubo9' ); ?></label>
                    <input type="text" placeholder="<?php echo __( 'Número do cartão', 'cubo9' ); ?>" class="form-control form-control-lg" name="braspag_creditcardNumber" id="braspag_creditcardNumber" aria-describedby="<?php echo __( 'Número do cartão de crédito', 'cubo9' ); ?>">
                </div>
				<div class="col-12 mt-3">
                    <label for="braspag_creditcardName"><?php echo __( 'Nome impresso no cartão de crédito', 'cubo9' ); ?></label>
                    <input type="text" placeholder="<?php echo __( 'Nome impresso no cartão de crédito', 'cubo9' ); ?>" class="form-control form-control-lg" name="braspag_creditcardName" id="braspag_creditcardName" aria-describedby="<?php echo __( 'Nome impresso no cartão de crédito', 'cubo9' ); ?>" maxlength="50">
                </div>
                <!--
                <div class="col col-12">
                    <label for="braspag_creditcardCpf"><?php echo __( 'CPF do titular do cartão de crédito', 'cubo9' ); ?></label>
                    <input type="text" class="form-control form-control-lg" name="braspag_creditcardCpf" id="braspag_creditcardCpf" aria-describedby="<?php echo __( 'CPF do titular do cartão de crédito', 'cubo9' ); ?>">
                </div>
                -->
                <div class="col-6 mt-3">
                    <label for="braspag_creditcardValidity"><?php echo __( 'Validade', 'cubo9' ); ?></label>
                    <input type="text" placeholder="<?php echo __( 'Validade', 'cubo9' ); ?>" class="form-control form-control-lg" name="braspag_creditcardValidity" id="braspag_creditcardValidity" aria-describedby="<?php echo __( 'Validade', 'cubo9' ); ?>">
                </div>
                <div class="col-6 mt-3">
                    <label for="braspag_creditcardCvv"><?php echo __( 'CVV', 'cubo9' ); ?></label>
                    <input type="text" placeholder="<?php echo __( 'CVV', 'cubo9' ); ?>" class="form-control form-control-lg" name="braspag_creditcardCvv" id="braspag_creditcardCvv" aria-describedby="<?php echo __( 'Código de segurança', 'cubo9' ); ?>" maxlength="4">
                </div>
                <input type="hidden" name="braspag_creditcardBrand" id="braspag_creditcardBrand" value="">
                <?php if( is_user_logged_in() ) : ?>
                <div class="col col-12 mt-3">
                    <p>
                        <input class="input-checkbox" type="checkbox" name="braspag_saveCreditCard" id="braspag_saveCreditCard" value="true" checked="checked"> Salvar este cartão de crédito
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
