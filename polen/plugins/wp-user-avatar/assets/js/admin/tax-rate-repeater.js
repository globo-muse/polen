(function ($) {

    $(function () {

        var tax_rate_row_tmpl = wp.template('ppress-tax-rate-row');
        var empty_state_tmpl = wp.template('ppress-tax-rate-empty-row');

        var country_state_select_tmpl = wp.template('ppress-tax-rate-state-select');
        var country_state_input_tmpl = wp.template('ppress-tax-rate-state-input');

        $(document).on('click', '.ppress_add_tax_rate', function (e) {
            e.preventDefault();

            $('.ppress-tax-rate-row--is-empty').remove();

            var index = 0,
                cache = $('.ppress-tax-rates-wrap table tbody tr').not('.ppress-tax-rate-row--is-empty');

            if (cache.length > 0) {
                index = cache.last().data('row-index') + 1;
            }

            $('.ppress-tax-rates-wrap table tbody').append(
                tax_rate_row_tmpl({
                    'index': index
                })
            );
        });

        $(document).on('click', '.ppress-tax-rate-table-actions .ppress-tax-rate-icon', function (e) {
            e.preventDefault();
            $(this).parents('.ppress-tax-rate-table-actions').parent().remove();

            if ($('.ppress-tax-rates-wrap table tbody tr').length === 0) {

                $('.ppress-tax-rates-wrap table tbody').append(empty_state_tmpl());
            }
        });

        $(document).on('change', '.ppress-tax-rate-table-country select', function () {

            var val = $(this).val(),
                parent = $(this).parent().parent(),
                field;

            if (val in ppress_countries_states) {

                field = country_state_select_tmpl({
                    index: parent.data('row-index'),
                    options: ppress_countries_states[val]
                });

            } else {

                field = country_state_input_tmpl({
                    index: parent.data('row-index')
                });
            }

            parent.find('.ppress-tax-rate-table-state').html(field);

        });
    });
})(jQuery);