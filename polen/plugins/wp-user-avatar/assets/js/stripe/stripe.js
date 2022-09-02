/* global ppress_stripe_vars */
/* global ppressCheckoutForm */
/* global pp_ajax_form */
(function ($) {

    function PPressStripe() {

        var _this = this,
            cardElement = false,
            $checkout_form = $('form#ppress_mb_checkout_form'),
            stripe = Stripe(ppress_stripe_vars.publishable_key, {
                'locale': ppress_stripe_vars.locale
            });

        this.init = function () {

            window.processCheckoutFlag = false;

            $(document).on('ppress_updated_checkout', _this.mountCardElement);

            $(document).on('ppress_update_checkout', _this.unmountCardElement);

            $(document).on('click', '#ppress-checkout-button', function () {
                window.processCheckoutFlag = true;
            });

            $checkout_form.on('ppress_checkout_place_order_stripe', _this.tokenRequest);

            $checkout_form.on('ppress_process_checkout_stripe', _this.processCheckout);
        };

        this.mountCardElement = function () {

            if ($('#ppress-stripe-card-element').length === 0) return;

            cardElement = stripe.elements().create('card', ppress_stripe_vars.createCardOptions);

            cardElement.mount('#ppress-stripe-card-element');
        };

        this.unmountCardElement = function () {

            if ($('#ppress-stripe-card-element').length === 0) return;

            if (typeof cardElement.unmount !== 'undefined') {
                cardElement.destroy();
            }
        };

        this.tokenRequest = function () {

            if (window.processCheckoutFlag === true) {

                $('#ppress_stripe_payment_method').remove();

                stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                    billing_details: {
                        name: $('#stripe-card_name').val(),
                        email: $('#ppmb_email').val(),
                        address: {
                            city: $('#stripe_ppress_billing_city').val(),
                            country: $('#stripe_ppress_billing_country').val(),
                            line1: $('#stripe_ppress_billing_address').val(),
                            postal_code: $('#stripe_ppress_billing_postcode').val(),
                            state: $('#stripe_ppress_billing_state').val(),
                        }
                    },
                }).then(function (result) {

                    if ('error' in result && typeof result.error.message !== 'undefined') {
                        ppressCheckoutForm.createAlertMessage(result.error.message);
                    } else {

                        window.processCheckoutFlag = false;

                        $checkout_form.append('<input id="ppress_stripe_payment_method" type="hidden" name="ppress_stripe_payment_method" value="' + result.paymentMethod.id + '">');
                        $checkout_form.submit();
                    }
                });

                return false;
            }
        };

        this.processCheckout = function (e, response, payment_method) {

            if (ppressCheckoutForm.is_var_defined(response.gateway_response) === true) {

                if (
                    (
                        // for subscription payments
                        ppressCheckoutForm.is_var_defined(response.gateway_response.latest_invoice) === true &&
                        ppressCheckoutForm.is_var_defined(response.gateway_response.latest_invoice.payment_intent) === true &&
                        ppressCheckoutForm.is_var_defined(response.gateway_response.latest_invoice.payment_intent.status) === true &&
                        response.gateway_response.latest_invoice.payment_intent.status === 'requires_action'
                    )
                    ||
                    (
                        // for one-time payments
                        ppressCheckoutForm.is_var_defined(response.gateway_response.status) === true &&
                        response.gateway_response.status === 'requires_action'
                    )
                ) {

                    var client_secret;

                    if (ppressCheckoutForm.is_var_defined(response.gateway_response.client_secret)) {
                        client_secret = response.gateway_response.client_secret;
                    } else {
                        client_secret = response.gateway_response.latest_invoice.payment_intent.client_secret;
                    }

                    stripe.confirmCardPayment(client_secret).then(function (result) {

                        if (result.error) {
                            ppressCheckoutForm.createAlertMessage(result.error.message);
                        } else {

                            if (result.paymentIntent.status === 'succeeded') {

                                $(document.body).trigger('ppress_checkout_success', [response, payment_method]);

                                window.location.assign(response.order_success_url);
                            }
                        }
                    });

                    return false;
                }
            }
        };
    }

    (new PPressStripe()).init();

})(jQuery);