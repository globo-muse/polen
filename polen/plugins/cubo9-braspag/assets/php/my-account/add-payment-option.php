<div class="u-columns woocommerce-Payment-Options col2-set payment-options">
    <h3>Adicionar Cartão</h3>
    <!-- Dados do cartão -->
    <div class="col col-12">
        <div class="row">
            <div class="col col-12">
                <label for="braspag_creditcardNumber"><?php echo __( 'Número do cartão', 'cubo9' ); ?></label>
                <input type="text" class="form-control form-control-lg" name="braspag_creditcardNumber" id="braspag_creditcardNumber" aria-describedby="<?php echo __( 'Número do cartão de crédito', 'cubo9' ); ?>">
            </div>
            <div class="col col-12">
                <label for="braspag_creditcardName"><?php echo __( 'Nome impresso no cartão de crédito', 'cubo9' ); ?></label>
                <input type="text" class="form-control form-control-lg" name="braspag_creditcardName" id="braspag_creditcardName" aria-describedby="<?php echo __( 'Nome impresso no cartão de crédito', 'cubo9' ); ?>" maxlength="50">
            </div>
            <div class="col col-6">
                <label for="braspag_creditcardValidity"><?php echo __( 'Validade', 'cubo9' ); ?></label>
                <input type="text" class="form-control form-control-lg" name="braspag_creditcardValidity" id="braspag_creditcardValidity" aria-describedby="<?php echo __( 'Validade', 'cubo9' ); ?>">
            </div>
            <div class="col col-6">
                <label for="braspag_creditcardCvv"><?php echo __( 'Código de segurança', 'cubo9' ); ?></label>
                <input type="text" class="form-control form-control-lg" name="braspag_creditcardCvv" id="braspag_creditcardCvv" aria-describedby="<?php echo __( 'Código de segurança', 'cubo9' ); ?>">
            </div>
            <div class="col col-12">
                <p>&nbsp;</p>
                <a class="woocommerce-Button btn btn-primary btn-lg btn-block braspag_SaveMyCard" href="#">Adicionar cartão</a>
            </div>
        </div>
    </div>
</div>