import $ from 'jquery';

export default function () {

    let _this = this;

    window.ppressCheckoutForm = this;

    this.init = function () {

        if (pp_ajax_form.is_checkout === '0' || $('#ppress_checkout_main_form').length === 0) return;

        $(document).on('click', '.ppress-checkout-show-login-form', this.toggle_login_form);
        $(document).on('click', '.ppress-login-submit-btn input[type="submit"]', this.process_login);
        $(document).on('click', '.ppress-coupon-code-link', this.toggle_discount_code_reveal);
        $(document).on('click', '.ppress-apply-discount-btn', this.apply_discount_code);
        $(document).on('click', '#ppress-remove-applied-coupon', this.remove_applied_discount_code);
        $(document).on('submit', '#ppress_mb_checkout_form', this.process_checkout);

        $(document).on('click', '.ppress-terms-and-conditions-link', function (e) {
            var cache = $('.ppress-checkout-form__terms_condition__content');
            if (cache.length > 0) {
                e.preventDefault();
                cache.slideToggle();
            }
        });

        $(document).on('ppress_update_checkout', this.update_checkout);

        if (pp_ajax_form.is_checkout_tax_enabled === '1') {
            $(document).on('change', '#ppress_mb_checkout_form .ppress_billing_country, #ppress_mb_checkout_form .ppress_billing_state, #ppress_mb_checkout_form .ppress_vat_number', _this.debounce(function () {
                $(document.body).trigger('ppress_update_checkout');
            }, 200));
        } else {

            $(document).on('change', '#ppress_mb_checkout_form .ppress_billing_country', _this.contextual_state_field);
        }

        // Update on page load.
        $(document.body).trigger('ppress_update_checkout');

        $(document).ajaxError(function () {
            _this.remove_spinner();
        });
    };

    this.debounce = function (fun, mil) {
        let timer;
        mil = mil || 600;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                fun();
            }, mil);
        };
    };

    this.contextual_state_field = function () {

        let state_field = $('.ppress_billing_state');

        let data = {
            'action': 'ppress_contextual_state_field',
            'country': $(this).val(),
            'name': state_field.prop('name'),
            'id': state_field.prop('id'),
            'class': state_field.prop('class'),
            'csrf': $('#ppress_checkout_nonce').val()
        };

        $.post(pp_ajax_form.ajaxurl, data, function (response) {
            state_field.replaceWith(response.data);
        });
    };

    this.scroll_to_notices = function (scrollElement) {
        scrollElement = scrollElement || $('.ppress-checkout-alert');
        if (scrollElement.length) {
            $('html, body').animate({
                scrollTop: (scrollElement.offset().top - 100)
            }, 1000);
        }
    };

    this.update_checkout = function () {

        _this.removeAllAlerts();

        _this.add_spinner();

        let data = {
            'action': 'ppress_update_order_review',
            'plan_id': $('#ppress-checkout-plan-id').val(),
            'csrf': $('#ppress_checkout_nonce').val(),
            'address': $('.ppress-checkout-form__payment_method.ppress-active .ppress_billing_address').val(),
            'city': $('.ppress-checkout-form__payment_method.ppress-active .ppress_billing_city').val(),
            'country': $('.ppress-checkout-form__payment_method.ppress-active .ppress_billing_country').val(),
            'state': $('.ppress-checkout-form__payment_method.ppress-active .ppress_billing_state').val(),
            'postcode': $('.ppress-checkout-form__payment_method.ppress-active .ppress_billing_postcode').val(),
            'phone': $('.ppress-checkout-form__payment_method.ppress-active .ppress_billing_phone').val(),
            'vat_number': $('#ppress_checkout_main_form .ppress_vat_number').val(),
            'post_data': $('#ppress_mb_checkout_form').serialize()
        };

        $.post(pp_ajax_form.ajaxurl, data, function (response) {

            // Save payment details to a temporary object
            let paymentDetails = {};

            $('.ppress-checkout-form__payment_method :input').each(function () {

                let ID = $(this).attr('id');

                if (ID) {
                    if ($.inArray($(this).attr('type'), ['checkbox', 'radio']) !== -1) {
                        paymentDetails[ID] = $(this).prop('checked');
                    } else {
                        paymentDetails[ID] = $(this).val();
                    }
                }
            });

            // Always update the fragments
            if ('data' in response && typeof response.data.fragments !== 'undefined') {
                $.each(response.data.fragments, function (key, value) {
                    if (!_this.fragments || _this.fragments[key] !== value) {
                        $(key).replaceWith(value);
                    }
                });
                _this.fragments = data.fragments;
            }

            // Fill in the payment details if possible without overwriting data if set.
            if (!$.isEmptyObject(paymentDetails)) {
                $('.ppress-checkout-form__payment_method :input').each(function () {
                    let ID = $(this).attr('id');
                    if (ID) {
                        if ($.inArray($(this).attr('type'), ['checkbox', 'radio']) !== -1) {
                            $(this).prop('checked', paymentDetails[ID]);
                        } else if ($.inArray($(this).attr('type'), ['select']) !== -1) {
                            $(this).val(paymentDetails[ID]);
                        } else {
                            $(this).val(paymentDetails[ID]);
                        }
                    }
                });
            }

            // Check for error
            if ('success' in response && false === response.success) {

                let $checkout_form_section = $('#ppress_checkout_main_form');

                if (response.data) {
                    $checkout_form_section.prepend(response.data);
                }

                // Lose focus for all fields
                $checkout_form_section.find('.input-text, select, input:checkbox').trigger('blur');
            }

            // Fire updated_checkout event.
            $(document.body).trigger('ppress_updated_checkout', [response]);

            let scrollToSelector = $('.ppress-checkout_order_summary__bottom_details'), cache;

            if ((cache = $('.ppress-checkout-alert')).length > 0) {
                scrollToSelector = cache;
            }

            _this.scroll_to_notices(scrollToSelector);

            _this.remove_spinner();
        });
    };

    this.toggle_login_form = function (e) {
        e.preventDefault();
        $('#ppress_checkout_account_info .ppress-main-checkout-form__login_form_wrap').slideToggle();
    };

    this.toggle_discount_code_reveal = function (e) {
        e.preventDefault();
        $('#ppress-checkout-coupon-code-wrap').slideToggle();
    };

    this.apply_discount_code = function (e) {
        e.preventDefault();

        _this.removeAllAlerts();

        _this.add_spinner();

        let data = {
            'action': 'ppress_checkout_apply_discount',
            'plan_id': $('#ppress-checkout-plan-id').val(),
            'coupon_code': $('#apply-discount').val(),
            'ppress_checkout_nonce': $('#ppress_checkout_nonce').val(),
        };

        $.post(pp_ajax_form.ajaxurl, data, function (response) {
            if ('success' in response && response.success === true) {
                $(document.body).trigger('ppress_update_checkout');
            } else {
                $('.ppress-checkout_order_summary-wrap').before(response.data);

                _this.remove_spinner();
            }
        });
    };

    this.remove_applied_discount_code = function (e) {
        e.preventDefault();

        _this.removeAllAlerts();

        _this.add_spinner();

        let data = {
            'action': 'ppress_checkout_remove_discount',
            'plan_id': $('#ppress-checkout-plan-id').val(),
            'ppress_checkout_nonce': $('#ppress_checkout_nonce').val(),
        };

        $.post(pp_ajax_form.ajaxurl, data, function (response) {
            if ('success' in response && response.success === true) {
                $(document.body).trigger('ppress_update_checkout');
            } else {
                $('.ppress-checkout_order_summary-wrap').before(response.data);

                _this.remove_spinner();
            }
        });
    };

    this.process_login = function (e) {

        e.preventDefault();

        _this.removeAllAlerts();

        _this.add_spinner();

        let data = {
            'action': 'ppress_process_checkout_login',
            'ppmb_user_login': $('#ppress_mb_checkout_form #ppmb_user_login').val(),
            'ppmb_user_pass': $('#ppress_mb_checkout_form #ppmb_user_pass').val(),
            'ppress_checkout_nonce': $('#ppress_checkout_nonce').val(),
        };

        $.post(pp_ajax_form.ajaxurl, data, function (response) {
            if ('success' in response) {
                if (response.success === true) {
                    window.location.reload();
                } else if ('data' in response) {
                    $('#ppress_mb_checkout_form .ppress-login-submit-btn').prepend(response.data);
                }
            }

            _this.remove_spinner();
        });
    };

    this.process_checkout = function (e) {

        if (typeof this.checkValidity === 'function' && false === this.checkValidity()) {
            return;
        }

        e.preventDefault();

        _this.removeAllAlerts();

        _this.add_spinner();

        var $form = $(this), $payment_method = _this.get_payment_method();

        if ($form.triggerHandler('ppress_checkout_place_order_' + $payment_method) !== false) {

            let formData = new FormData(this);
            formData.append("action", "ppress_process_checkout");
            formData.append("ppress_checkout_nonce", $('#ppress_checkout_nonce').val());

            $.post({
                url: pp_ajax_form.ajaxurl,
                data: formData,
                cache: false,
                contentType: false,
                enctype: 'multipart/form-data',
                processData: false,
                dataType: 'json',
                success: function (response) {

                    if ('success' in response) {

                        if (response.success === true) {

                            if ($form.triggerHandler('ppress_process_checkout_' + $payment_method, [response, $payment_method]) !== false) {

                                if ('redirect_url' in response && typeof response.redirect_url !== 'undefined' && response.redirect_url.length > 0) {
                                    window.location.assign(response.redirect_url);
                                } else {

                                    $(document.body).trigger('ppress_checkout_success', [response, $payment_method]);

                                    window.location.assign(response.order_success_url);
                                }
                            }

                            return;

                        }

                        if ('error_message' in response) {
                            return _this.createAlertMessage(response.error_message);
                        }

                        if ('data' in response && typeof response.data == 'string') {
                            return _this.createAlertMessage(response.data);
                        }

                        return;
                    }

                    _this.remove_spinner();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    _this.createAlertMessage(errorThrown);
                }
            }, 'json',);
        }
    };

    this.get_payment_method = function () {
        return $('#ppress_mb_checkout_form').find('input[name="ppress_payment_method"]:checked').val();
    };

    this.createAlertMessage = function (message, type) {
        type = type || 'error';
        var is_marked_up = typeof message.indexOf !== 'undefined' && message.indexOf('ppress-checkout-alert') !== -1,
            msg = '';

        if (!is_marked_up) msg = '<div class="ppress-checkout-alert ppress-' + type + '"><p>';

        msg += message;

        if (!is_marked_up) msg += '</p></div>';

        $('#ppress_checkout_main_form').prepend(msg);

        ppressCheckoutForm.scroll_to_notices();
        ppressCheckoutForm.remove_spinner();

        $(document.body).trigger('ppress_checkout_error', [message]);
    };

    this.removeAllAlerts = function () {
        $('.ppress-checkout-alert').remove();
    };

    this.add_spinner = function () {
        $('.ppress-checkout__form').prepend('<div class="ppress-checkout__form__preloader"><div class="ppress-checkout__form__spinner"></div></div>')
    };

    this.remove_spinner = function () {
        $('.ppress-checkout__form .ppress-checkout__form__preloader').remove()
    };

    this.is_var_defined = function (val) {
        return typeof val !== 'undefined' && val !== null;
    };
}