(function ($) {

    $(document).on('ready', function () {

        var template = wp.template('ppress-checkout-field-item');

        $(document).on('ppress_update_dropdown', function () {
            var cache = $('.ppress-checkout-field-list option');
            cache.removeAttr("disabled");
            $('.ppress-checkout-fields .widget').each(function () {
                $('.ppress-checkout-field-list option[value="' + $(this).data('field-id') + '"]').attr("disabled", true);
            });

            // move selected option to first/default option after a field is added.
            cache.eq(0).prop('selected', true)
        });

        if (typeof ppress_account_info_fields !== 'undefined') {
            $.each(ppress_account_info_fields, function (key, value) {
                $('#ppress-account-info-fields .ppress-checkout-fields').append(
                    template({
                        'fieldGroup': 'accountInfo',
                        'field_key': key,
                        'label': value.label,
                        'width': value.width,
                        'required': value.required,
                        'logged_in_hide': value.logged_in_hide,
                        'deletable': value.deletable
                    })
                );
            });
        }

        if (typeof ppress_blling_address_fields !== 'undefined') {
            $.each(ppress_blling_address_fields, function (key, value) {
                $('#ppress-billing-address-fields .ppress-checkout-fields').append(
                    template({
                        'fieldGroup': 'billing',
                        'field_key': key,
                        'label': value.label,
                        'width': value.width,
                        'required': value.required,
                        'logged_in_hide': value.logged_in_hide,
                        'deletable': value.deletable
                    })
                );
            });
        }

        $('.ppress-checkout-fields').sortable({
            containment: "#ppress-checkout-field-manager-form",
            items: ".widget",
            placeholder: "sortable-placeholder"
        });

        $(document).trigger('ppress_update_dropdown');

        $(document).on('click', '.ppress-checkout-fields .widget-title-action', function (e) {
            e.preventDefault();
            $(this).parents('.ppress-checkout-fields .widget').toggleClass('open');
        });

        $(document).on('click', '.ppress-checkout-fields .widget-control-remove', function (e) {
            e.preventDefault();
            $(this).parents('.ppress-checkout-fields .widget').slideUp("normal", function () {
                $(this).remove();
                $(document).trigger('ppress_update_dropdown');
            });
        });

        $(document).on('change', '.ppress-checkout-fields .widget-content input[name$="[label]"]', function () {
            $(this).parents('.ppress-checkout-fields .widget').find('.widget-title h3').text($(this).val());
        });

        $(document).on('click', '#ppress-cfm-submit-btn', function (e) {
            e.preventDefault();
            document.getElementById("ppress-checkout-field-manager-form").submit();
        });

        $(document).on('click', '#ppress-account-info-fields .ppress-checkout-add-field button', function (e) {
            e.preventDefault();
            var selectedField = $(this).prev().val();

            if ("" !== selectedField) {

                var bucket = typeof ppress_standard_acc_info_fields[selectedField] != 'undefined' ?
                    ppress_standard_acc_info_fields[selectedField] :
                    ppress_custom_fields[selectedField];

                $('#ppress-account-info-fields .ppress-checkout-fields').append(
                    template({
                        'fieldGroup': 'accountInfo',
                        'field_key': selectedField,
                        'label': bucket['label'],
                        'width': bucket['width'],
                        'required': bucket['required'],
                        'logged_in_hide': bucket['logged_in_hide'],
                        'deletable': bucket['deletable']
                    })
                );

                $(document).trigger('ppress_update_dropdown');
            }
        });

        $(document).on('click', '#ppress-billing-address-fields .ppress-checkout-add-field button', function (e) {
            e.preventDefault();
            var selectedField = $(this).prev().val();

            if ("" !== selectedField) {

                var bucket = typeof ppress_standard_billing_fields[selectedField] != 'undefined' ?
                    ppress_standard_billing_fields[selectedField] :
                    ppress_custom_fields[selectedField];

                $('#ppress-billing-address-fields .ppress-checkout-fields').append(
                    template({
                        'fieldGroup': 'billing',
                        'field_key': selectedField,
                        'label': bucket['label'],
                        'width': bucket['width'],
                        'required': bucket['required'],
                        'logged_in_hide': bucket['logged_in_hide'],
                        'deletable': bucket['deletable']
                    })
                );

                $(document).trigger('ppress_update_dropdown');
            }
        });
    });
})(jQuery);