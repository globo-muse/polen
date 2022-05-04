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

<span style="display: none;" id="tuna_allow_cartao_payment">
    <?php echo $allow_cartao_payment ?>
</span>

<span style="display: none;" id="tuna_allow_boleto_payment">
    <?php echo $allow_boleto_payment ?>
</span>

<span style="display: none;" id="tuna_allow_pix_payment">
    <?php echo $allow_pix_payment ?>
</span>

<span style="display: none;" id="tuna_allow_crypto_payment">
    <?php echo $allow_crypto_payment ?>
</span>

<span style="display: none;" id="tuna_installment_options">
    <?php
    
        
    
    $installment_params = array($max_parcels_number, $min_parcel_value);
    if (WC()->cart!=null && get_query_var('order-pay')==null)
    {
        $order_subtotal = floatval(WC()->cart->get_total('total'));
        echo get_installment_options($order_subtotal, $installment_fees, $installment_params);
    }else
    {
        
        $order = wc_get_order(get_query_var('order-pay')); 
        $nameJuros = "Acréscimo de juros";
		$nameDiscount = "Desconto";		 
		$fees = $order->get_fees();
        foreach($fees as $key=>$fee)
        {
            if($fee['name']==$nameJuros || $fee['name']==$nameDiscount){
                wc_delete_order_item($key);		
                echo 'Carregando pedido para novo pagamento. Aguarde.';
                echo '<meta http-equiv="Refresh" content="0">';
                echo '<style>#order_review {display:none} #payment{display:none}</style>';                
            }
        } 
        $order->calculate_taxes();
        $order->calculate_totals();
        $order->save();
        $order_subtotal = floatval($order->get_total());
        echo get_installment_options($order_subtotal, $installment_fees, $installment_params);       
    }
    ?>
</span>

<span style="display: none;" id="tuna_order_subtotal">
    <?php echo $order_subtotal ?>
</span>
 

<div id="mainPaymentDiv">
    <div class="tabs tuna-tabs">
        <div onclick="useSavedCreditCard()" id="creditCardPaymentBtn" class="tab selected">Cartão salvo</div>
        <div onclick="useNewCard()" id="newCardBtn" class="tab">Novo cartão</div>
        <div type="button" onclick="useBoletoPayment()" style="display: none;" class="tab boletoPaymentBtn">Boleto</div>
        <div type="button" onclick="usePixPayment()" style="display: none;" class="tab pixPaymentBtn">Pix</div>
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
    <div id="loggedOffCreditCardDiv">
        <p>Não foi possível iniciar o pagamento. Solicite ao administrador que verifique suas configurações.</p>
    </div>
</div>

<div id="boletoPaymentDocumentDiv" style="display: none;">
    Pague de forma segura com o <span id="lblTunaTipo">Boleto</span><br />
    Ao finalizar o pedido, você verá o <span id="lblTunaTipo2">boleto</span> para efetuar o pagamento.<br />
    <label id="lblCPFBoleto" class="defaultTunaLabel">CPF</label>
    <input class="defaultTunaInputText" type="text" name="tuna_document" id="tuna_boleto_document" required />
    <span id="boleto_document_invalid_message" class="defaultTunaValidation" style="display: none;">Por favor, insira um CPF válido</span>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        var tunaTkn;
        var tunaOrderSubTotal;
        var tunaShippingAmount;
        var tunaAllowCartaoPayment
        var tunaAllowBoletoPayment;
        var tunaAllowPixPayment;
        var tunaAllowCryptoPayment;
        var tunaInstallmentOptions;
        var installmentsOptions = [];

        if ($("#tuna_token_session_id").val())
            tunaTkn = $("#tuna_token_session_id").val();

        if ($("#tuna_order_subtotal").html())
            tunaOrderSubTotal = parseFloat($("#tuna_order_subtotal").html().trim(), 10);

         if ($("#tuna_allow_cartao_payment").html())
            tunaAllowCartaoPayment = $("#tuna_allow_cartao_payment").html().trim();
        
        if ($("#tuna_allow_boleto_payment").html())
            tunaAllowBoletoPayment = $("#tuna_allow_boleto_payment").html().trim();

        if ($("#tuna_allow_pix_payment").html())
            tunaAllowPixPayment = $("#tuna_allow_pix_payment").html().trim();
        
        if ($("#tuna_allow_crypto_payment").html())
            tunaAllowCryptoPayment = $("#tuna_allow_crypto_payment").html().trim();

        if ($("#tuna_installment_options").html()) {
            tunaInstallmentOptions = $("#tuna_installment_options").html().trim();
            tunaInstallmentOptions = tunaInstallmentOptions.slice(2, tunaInstallmentOptions.length - 2).split('","');
        }

        if (!tunaAllowCartaoPayment)
            tunaAllowCartaoPayment = "yes";

        if (!tunaAllowBoletoPayment)
            tunaAllowBoletoPayment = "no";

        if (!tunaAllowPixPayment)
            tunaAllowCryptoPayment = "no";

        if (!tunaAllowCryptoPayment)
            tunaAllowCryptoPayment = "no";

        for (var key = 1; key <= tunaInstallmentOptions.length; key++) {
            installmentsOptions.push({
                key: key,
                value: tunaInstallmentOptions[key - 1]
            });
        }

        var tunaOrderUpdate = { 
          oldOrderTotal: tunaOrderSubTotal
        };

        var handleTunaPaymentLoad = () => {
          if ($("#payment_method_tuna_payment").prop("checked")) {
              refreshOrderInfo(tunaOrderUpdate);
          } else {
              resetOrderInfo(tunaOrderUpdate);
          }
        };
        
        var tunaCallback = handleTunaPaymentLoad;
        
        startTuna(
          tunaTkn,
          tunaAllowCartaoPayment,
          tunaAllowBoletoPayment,
          installmentsOptions, 
          tunaAllowPixPayment,
          tunaAllowCryptoPayment,
          tunaCallback,
          tunaOrderUpdate,
          $
        );

        var refreshCallback = () => {
          refreshOrderInfo(tunaOrderUpdate);
        };

        var resetCallback = () => {
          resetOrderInfo(tunaOrderUpdate);
        };

        $('#INSTALLMENT').on('change', refreshCallback);
        $('#creditCardPaymentBtn').on('click', refreshCallback);
        $('#newCardBtn').on('click', refreshCallback);
        $('.boletoPaymentBtn').on('click', resetCallback);
        $('.pixPaymentBtn').on('click', resetCallback);
        $('.cryptoPaymentBtn').on('click', resetCallback);

        $('#payment').on('change', handleTunaPaymentLoad); 
    });
</script>