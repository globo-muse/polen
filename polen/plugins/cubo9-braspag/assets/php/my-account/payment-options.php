<?php 
$braspag_card_saved_data = get_user_meta( get_current_user_id(), 'braspag_card_saved_data', true ); 
$braspag_default_payment = get_user_meta( get_current_user_id(), 'braspag_default_payment', true );
?>
<div class="u-columns woocommerce-Payment-Options col2-set payment-options">
    <h3>Meus Cartões</h3>
    <div id="cards-accordion" class="panel-group" role="tablist" aria-multiselectable="true">
        <?php
        if( ! is_null( $braspag_card_saved_data ) && ! empty( $braspag_card_saved_data ) && is_array( $braspag_card_saved_data ) && count( $braspag_card_saved_data ) > 0 ) {
            foreach( $braspag_card_saved_data as $p => $data ) {
                $prefix = md5( $p );
        ?>
        <div id="payment-<?php echo $prefix; ?>" class="panel panel-default">
            <div class="panel-heading" role="tab" id="heading-<?php echo $prefix; ?>">
                <h4 class="panel-title">
                    <a class="collapsed" role="button" data-parent="#cards-accordion" data-toggle="collapse" data-target="#collapse-<?php echo $prefix; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $prefix; ?>">
                        <div class="row">
                            <div class="col-md-8">
                                <strong id="braspag-brand-name-<?php echo $prefix; ?>"><?php echo $data['brand']; ?></strong><?php echo ( $braspag_default_payment == $prefix ) ? ' (Padrão)' : ''; ?>
                                <span class="badge badge-primary badge-pill"><?php echo __( 'Final:', 'cubo9' ); ?> <?php echo $data['sufix']; ?></span>
                            </div>
                            <div class="col-md-4 text-center">
                                <strong>Expira em</strong>:&nbsp;<?php echo $data['expiration_date']; ?>
                            </div>
                        </div>
                    </a>
                </h4>
            </div>

            <div id="collapse-<?php echo $prefix; ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-<?php echo $prefix; ?>">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p class="my-account-card-content">
                                <strong>Nome no cartão:</strong>&nbsp;<?php echo $data['holder']; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <p class="my-account-card-content">
                                <?php
                                    if( $braspag_default_payment && ! is_null( $braspag_default_payment ) && ! empty( $braspag_default_payment ) && $prefix == $braspag_default_payment ) {
                                        $class = 'glyphicon-star';
                                        $title = 'Desmarcar padrão';
                                    } else {
                                        $class = 'glyphicon-ok';
                                        $title = 'Definir como padrão';
                                    }
                                ?>
                                <a href="#" title="<?php echo $title; ?>" class="braspag-make-default-payment" default-id="<?php echo $prefix; ?>" brand-name="<?php echo $data['brand']; ?>">
                                    <span class="glyphicon <?php echo $class; ?>" aria-hidden="true"></span>
                                </a>
                                &nbsp;
                                <a href="#" title="Remover" class="braspag-remove-payment" remove-id="<?php echo $prefix; ?>">
                                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
            } 
        } else {
        ?>
        <div class="row">
            <div class="col-md-12 text-center">
                <h4>Nenhuma opção de pagamento cadastrada.</h4>
            </div>
        </div>
        <?php } ?>
    </div>
</div>