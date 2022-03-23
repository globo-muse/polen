<input type="hidden" name="tuna_card_token" id="tuna_card_token" />
<input type="hidden" name="tuna_token_session_id" id="tuna_token_session_id" value="<?php echo $token_session_id ?>" />
<input type="hidden" name="tuna_tmpuser_id" id="tuna_tmpuser_id" value="<?php echo $tmpUserID ?>" />
<input type="hidden" name="tuna_internal_session_id" id="tuna_internal_session_id" value="<?php echo $internal_session_id ?>" />
<input type="hidden" name="tuna_installments" id="tuna_installments" value="" />
<input type="hidden" name="tuna_document" id="tuna_document" />
<input type="hidden" name="tuna_expiration_year" id="tuna_expiration_year" />
<input type="hidden" name="tuna_expiration_month" id="tuna_expiration_month" />
<input type="hidden" name="tuna_card_holder_name" id="tuna_card_holder_name" />
<input type="hidden" name="tuna_card_brand" id="tuna_card_brand" />
<input type="hidden" name="tuna_is_boleto_payment" value="false" id="tuna_is_boleto_payment" />
<input type="hidden" name="tuna_is_pix_payment" value="false" id="tuna_is_pix_payment" />
<input type="hidden" name="tuna_is_crypto_payment" value="false" id="tuna_is_crypto_payment" />
<input type="hidden" name="tuna_wp_login_url" id="tuna_wp_login_url" value="<?php echo wp_login_url('admin-ajax.php') ?>" />

<span style="display: none;" id="tuna_allow_boleto_payment">
    <?php echo $allow_boleto_payment ?>
</span>

<span style="display: none;" id="tuna_allow_pix_payment">
    <?php echo $allow_pix_payment ?>
</span>

<span style="display: none;" id="tuna_allow_crypto_payment">
    <?php echo $allow_crypto_payment ?>
</span>

<span style="display: none;" id="tuna_force_authentication">
    <?php echo $force_authentication ?>
</span>

<span style="display: none;" id="tuna_installment_options">
    <?php
    $installment_params = array($max_parcels_number, $min_parcel_value);
    $order_total = floatval(WC()->cart->total);
    echo get_installment_options($order_total, $installment_fees, $installment_params);
    ?>
</span>

<div id=" mainPaymentDiv">
    <div class="tabs tuna-tabs">
        <div onclick="useSavedCreditCard()" id="creditCardPaymentBtn" class="tab selected">Cartão salvo</div>
        <div onclick="useNewCard()" id="newCardBtn" class="tab">
            <img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/icon-card.png'; ?>" alt="Cartão de Crédito" />
            <h5>Cartão de<br>Crédito</h5>
        </div>
        <div type="button" onclick="useBoletoPayment()" style="display: none;" class="tab boletoPaymentBtn">
            <img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/icon-billet.png'; ?>" alt="Boleto" />
            <h5>Boleto</h5>
        </div>
        <div type="button" onclick="usePixPayment()" style="display: none;" class="tab pixPaymentBtn">
            <img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/icon-pix.png'; ?>" alt="Pix" class="pix-icon"/>    
        </div>
        <div type="button" onclick="useCryptoPayment()" style="display: none;" class="tab cryptoPaymentBtn">Bitcoin</div>
    </div>

    <div id="savedCardPaymentDiv"> </div>
    <div id="creditCardPaymentDiv" class="piece_div" style="display: none;">
        <div id="CARD_HOLDER_NAME"></div>
        <div id="CREDIT_CARD"></div>
        <div id="CREDIT_CARD_CVV"></div>
        <div id="VALIDITY"> </div>
        <div id="SINGLE_USE_FIELD"></div>
    </div>

    <div id="commonFields">
        <div id="INSTALLMENT"></div>
        <div id="DOCUMENT"></div>
    </div>
</div>

<div id="loggedOfPaymentDiv" style="display: none;">
    <div class="tabs tuna-tabs">
        <div onclick="loggedOffUseCreditCard()" id="loggedOfCreditCard" class="tab selected">Cartão de crédito</div>
        <div onclick="useBoletoPayment()" style="display: none;" class="tab boletoPaymentBtn">Boleto</div>
    </div>

    <div id="loggedOffCreditCardDiv">
        <p>Faça o <a href="#" onclick="goToLogin()">login</a> para pagar com cartão de crédito</p>
    </div>
</div>

<div id="boletoPaymentDocumentDiv" style="display: none;">
    Pague de forma segura com o <span id="lblTunaTipo">Boleto</span><br />
    <div id="pix-instruction">
        <div class="row">
            <div class="col-sm-12 mb-3">
                <h5>Imediato</h5>
                <p>Faça seu pagamento em poucos segundos sem custos adicionais.</p>
            </div>
            <div class="col-sm-12 mb-3">
                <h5>Simples</h5>
                <p>Para pagar basta abrir o aplicativo do seu banco, procurar pelo PIX e escanear o QRcode.</p>
            </div>
            <div class="col-sm-12">
                <h5>Seguro</h5>
                <p>O pagamento com PIX foi desenvolvido pelo Banco Central para facilitar suas compras.</p>
            </div>
        </div>
    </div>
    Ao finalizar o pedido, você verá o <span id="lblTunaTipo2">boleto</span> para efetuar o pagamento.<br />
    <label id="lblCPFBoleto" class="defaultTunaLabel">CPF</label>
    <input class="defaultTunaInputText" type="text" name="tuna_document" id="tuna_boleto_document" required />
    <span id="boleto_document_invalid_message" class="defaultTunaValidation" style="display: none;">Por favor, insira um CPF válido</span>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        var tunaTkn, tunaAllowBoletoPayment, tunaAllowPixPayment, tunaAllowCryptoPayment, tunaForceAuthentication;

        if ($("#tuna_token_session_id").val())
            tunaTkn = $("#tuna_token_session_id").val();

        if ($("#tuna_allow_boleto_payment").html())
            tunaAllowBoletoPayment = $("#tuna_allow_boleto_payment").html().trim();

        if ($("#tuna_allow_pix_payment").html())
            tunaAllowPixPayment = $("#tuna_allow_pix_payment").html().trim();
        
        if ($("#tuna_allow_crypto_payment").html())
            tunaAllowCryptoPayment = $("#tuna_allow_crypto_payment").html().trim();

        if ($("#tuna_force_authentication").html())
            tunaForceAuthentication = $("#tuna_force_authentication").html().trim();

        if ($("#tuna_installment_options").html()) {
            tunaInstallmentOptions = $("#tuna_installment_options").html().trim();
            tunaInstallmentOptions = tunaInstallmentOptions.slice(2, tunaInstallmentOptions.length - 2).split('","');
        }

        if (!tunaAllowBoletoPayment)
            tunaAllowBoletoPayment = "no";

        if (!tunaAllowPixPayment)
            tunaAllowCryptoPayment = "no";

        if (!tunaAllowCryptoPayment)
            tunaAllowCryptoPayment = "no";

        if (!tunaForceAuthentication)
            tunaForceAuthentication = "yes";

        var installmentsOptions = [];
        for (var key = 1; key <= tunaInstallmentOptions.length; key++) {
            installmentsOptions.push({
                key: key,
                value: tunaInstallmentOptions[key - 1]
            });
        }

        var tunaCallback = refreshOrderInfo;

        startTuna(
          tunaTkn,
          tunaAllowBoletoPayment,
          installmentsOptions,
          tunaForceAuthentication,
          tunaAllowPixPayment,
          tunaAllowCryptoPayment,
          tunaCallback,
          $
        );

        $('#INSTALLMENT').on('change', refreshOrderInfo);
        $('#creditCardPaymentBtn').on('click', refreshOrderInfo);
        $('#newCardBtn').on('click', refreshOrderInfo);
        $('.boletoPaymentBtn').on('click', resetOrderInfo);
        $('.pixPaymentBtn').on('click', resetOrderInfo);
        $('.cryptoPaymentBtn').on('click', resetOrderInfo);
    });
</script>