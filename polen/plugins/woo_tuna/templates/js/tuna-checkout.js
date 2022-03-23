var isTunaSubmissionReady, tuna;
var newCreditCardFieldIDs = [];
var $;

startTuna = async (tunaSessionToken, allowBoletoPayment, installmentOptions, tunaForceAuthentication,tunaAllowPixPayment, $) => {
    this.$ = $;
    $('#tuna_boleto_document').mask('000.000.000-00', {
        reverse: true
    });
    if (allowBoletoPayment === "no")
        $(".boletoPaymentBtn").remove();
    else
        $(".boletoPaymentBtn").css("display", "initial");
    if (tunaAllowPixPayment === "no")
        $(".pixPaymentBtn").remove();
    else
        $(".pixPaymentBtn").css("display", "initial");   
    if (tunaSessionToken) {
        tuna = Tuna(tunaSessionToken);

        let pieceManager = tuna.pieceManager();

        pieceManager.forge("#DOCUMENT", "document", {
            title: "CPF do títular do cartão", buyerDocumentFormatter: tuna.getBuyerDocumentFormatter("pt-BR")
        });

        if (installmentOptions)
            pieceManager.forge("#INSTALLMENT", "installment", {
                title: "Parcelamento",
                options: installmentOptions
            });

        let savedCardsCount = await tuna.useSavedCardSelector("#savedCardPaymentDiv",
            {
                title: "Cartões salvos",
                cardCvv: { placeholder: "cvv" },
                onSelectionBegins: _ => removeNewCardFields()
            });

        if (savedCardsCount == 0) {
            useNewCard();
            $("#creditCardPaymentBtn").remove();
        }
    } else {
        $("#mainPaymentDiv").css("display", "none");

        $("#loggedOfPaymentDiv").css("display", "block");
    }

    var checkout_form = $('form.checkout');
    checkout_form.unbind('checkout_place_order');
    checkout_form.on('checkout_place_order', _ => {
        if ($("input[name='payment_method']:checked").val() === "tuna_payment") {
            if ($("#tuna_is_boleto_payment").val() !== "true" && $("#tuna_is_pix_payment").val() !== "true") {

                if (isTunaSubmissionReady) {
                    isTunaSubmissionReady = false;
                    return true;
                } else {
                    checkout();
                    return false;
                }
            } else {
                let isDocumentValid;
                // Case does not exists, create a dummy tuna object only to use the formatter
                if (!tuna) {
                    let tuna = Tuna("");
                    isDocumentValid = tuna.getBuyerDocumentFormatter("pt-BR").validationFunction($("#tuna_boleto_document").val());
                } else
                    isDocumentValid = tuna.getBuyerDocumentFormatter("pt-BR").validationFunction($("#tuna_boleto_document").val());

                if (isDocumentValid || $("#tuna_is_pix_payment").val() === "true") {
                    $("#tuna_document").remove();
                    return true;
                }
                $("#boleto_document_invalid_message").css("display", "block");
                return false;
            }
        }
    });
}

goToLogin = _ => {
    if ($(".showlogin").length > 0) {
        $('html, body').animate({ scrollTop: $(".showlogin").position().top + 200 }, 'slow');
        if ($("form.woocommerce-form.woocommerce-form-login.login").css("display") === "none")
            $(".showlogin")[0].click();
    } else {
        var loginurl = $('#tuna_wp_login_url').val();
        window.location.href = loginurl;
    }
}

loggedOffUseCreditCard = _ => {
    $("#loggedOffCreditCardDiv").css("display", "block");
    $("#boletoPaymentDocumentDiv").css("display", "none");
    $("#commonFields").css("display", "block");
    $(".boletoPaymentBtn").removeClass("selected");    
    $(".pixPaymentBtn").removeClass("selected");    
    $("#loggedOfCreditCard").addClass("selected");
    $("#loggedOfCreditCard").blur();
    $("#tuna_is_boleto_payment").val("false");
    $("#tuna_is_pix_payment").val("false");
}

checkout = async _ => {
    try {
        let response = await tuna.directCheckout();
        if (response && response.success && response.tokenData && response.tokenData.code == 1) {
            $("#tuna_card_token").val(response.tokenData.token);
            $("#tuna_card_brand").val(response.tokenData.brand ?? response.tokenData.cardBrand);
            $("#tuna_document").val(response.cardData.document);
            $("#tuna_expiration_year").val(response.cardData.expirationYear);
            $("#tuna_expiration_month").val(response.cardData.expirationMonth);
            $("#tuna_card_holder_name").val(response.cardData.cardHolderName);
            $("#tuna_installments").val(response.cardData.installment);
            $("#tuna_boleto_document").remove();

            isTunaSubmissionReady = true;

            tuna.pieceManager().executeOnPieces(piece => {
                if(piece.getGroup() === "sensitive")
                    piece.setValue("");
            });

            setTimeout(_ => { $('button#place_order').click(); }, 200);
        }
    } catch (e) {
        console.log(e);
    }
}

useSavedCreditCard = () => {
    $("#creditCardPaymentDiv").css("display", "none");
    $("#boletoPaymentDocumentDiv").css("display", "none");
    $("#savedCardPaymentDiv").css("display", "block");
    $("#commonFields").css("display", "block");
    $("#creditCardPaymentBtn").addClass("selected");
    $("#newCardBtn").removeClass("selected");
    $(".boletoPaymentBtn").removeClass("selected");
    $(".pixPaymentBtn").removeClass("selected");
    $("#creditCardPaymentBtn").blur();

    $("#tuna_is_boleto_payment").val("false");
    $("#tuna_is_pix_payment").val("false");
}

useBoletoPayment = () => {
    // if tuna exists, then the user is logged in, otherwise it is not
    if (tuna) {
        tuna.clearSavedCardSelector();
        $("#commonFields").css("display", "none");
        $("#creditCardPaymentDiv").css("display", "none");
        $("#savedCardPaymentDiv").css("display", "none");
        $(".pixPaymentBtn").removeClass("selected");
        $(".boletoPaymentBtn").addClass("selected");
        $("#newCardBtn").removeClass("selected");
        $("#creditCardPaymentBtn").removeClass("selected");
        $(".boletoPaymentBtn").blur();
    } else {
        $("#loggedOffCreditCardDiv").css("display", "none");
        $(".boletoPaymentBtn").addClass("selected");
        $("#loggedOfCreditCard").removeClass("selected");
        $(".pixPaymentBtn").removeClass("selected");
        $(".boletoPaymentBtn").blur();
    }
    $("#boletoPaymentDocumentDiv").css("display", "block");
    $("#lblCPFBoleto").css("display", "block");
    $("#tuna_boleto_document").css("display", "block");
    $("#lblTunaTipo").html("Boleto.");
    $("#lblTunaTipo2").html("boleto");
    $("#tuna_is_boleto_payment").val("true");
    $("#tuna_is_pix_payment").val("false");
}
usePixPayment = () => {
    // if tuna exists, then the user is logged in, otherwise it is not
    if (tuna) {
        tuna.clearSavedCardSelector();
        $("#commonFields").css("display", "none");
        $("#creditCardPaymentDiv").css("display", "none");
        $("#savedCardPaymentDiv").css("display", "none");
        $(".pixPaymentBtn").addClass("selected");
        $(".boletoPaymentBtn").removeClass("selected");
        $("#newCardBtn").removeClass("selected");
        $("#creditCardPaymentBtn").removeClass("selected");
        $(".boletoPaymentBtn").blur();
    } else {
        $("#loggedOffCreditCardDiv").css("display", "none");
        $(".pixPaymentBtn").addClass("selected");
        $(".boletoPaymentBtn").removeClass("selected");
        $("#loggedOfCreditCard").removeClass("selected");
        $(".pixPaymentBtn").blur();
    }
    $("#boletoPaymentDocumentDiv").css("display", "block");
    $("#lblCPFBoleto").css("display", "none");
    $("#tuna_boleto_document").css("display", "none");
    $("#lblTunaTipo2").html("código");
    $("#lblTunaTipo").html("Pix!");
    $("#tuna_is_boleto_payment").val("false");
    $("#tuna_is_pix_payment").val("true");
}
removeNewCardFields = _ => {
    newCreditCardFieldIDs.forEach(id => tuna.pieceManager().destroy(id));
    newCreditCardFieldIDs = [];
}

useNewCard = () => {
    tuna.clearSavedCardSelector();
    $("#savedCardPaymentDiv").css("display", "none");
    $("#boletoPaymentDocumentDiv").css("display", "none");
    $("#creditCardPaymentDiv").css("display", "block");

    $("#newCardBtn").addClass("selected");
    $("#creditCardPaymentBtn").removeClass("selected");
    $(".pixPaymentBtn").removeClass("selected");
    $(".boletoPaymentBtn").removeClass("selected");
    $("#newCardBtn").blur();

    $("#commonFields").css("display", "block");

    $("#tuna_is_boleto_payment").val("false");
    $("#tuna_is_pix_payment").val("false");

    removeNewCardFields();
    let pieceManager = tuna.pieceManager();

    newCreditCardFieldIDs.push(pieceManager.forge("#CARD_HOLDER_NAME", "cardHolderName", {
        title: "Nome Impresso no cartão", placeholder: "Nome Impresso no cartão", validationMessage: "Nome inválido"
    }));

    newCreditCardFieldIDs.push(pieceManager.forge("#CREDIT_CARD", "cardNumber", {
        title: "Número do Cartão", placeholder: "Número do Cartão", validationMessage: "Número inválido"
    }, "sensitive"));
    newCreditCardFieldIDs.push(pieceManager.forge("#VALIDITY", "cardValidity", {
        title: "Validade", placeholder: "MM/YYYY", validationMessage: "Validade inválido"
    }));
    newCreditCardFieldIDs.push(pieceManager.forge("#CREDIT_CARD_CVV", "cardCvv", {
        title: "CVV", placeholder: "CVV", validationMessage: "CVV inválido"
    }, "sensitive"));
    newCreditCardFieldIDs.push(pieceManager.forge("#SINGLE_USE_FIELD", "saveCard", {
        title: "Salvar Cartão"
    }));
}