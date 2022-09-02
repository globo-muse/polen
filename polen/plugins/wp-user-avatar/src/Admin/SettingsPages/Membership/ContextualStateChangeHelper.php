<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership;

class ContextualStateChangeHelper
{
    public static function init()
    {
        add_action('admin_footer', [__CLASS__, 'css_js_script']);
    }

    public static function css_js_script()
    {
        ?>
        <script type="text/html" id="tmpl-ppress-billing-state-input">
            <input name="ppress_billing_state" type="text" id="ppress_billing_state" value="">
        </script>

        <script type="text/html" id="tmpl-ppress-billing-state-select">
            <select name="ppress_billing_state" id="ppress_billing_state">
                <option value="">&mdash;&mdash;&mdash;</option>
                <# jQuery.each(data.options, function(index, value) { #>
                <option value="{{index}}">{{value}}</option>
                <# }); #>
            </select>
        </script>

        <script type="text/javascript">
            (function ($) {
                $(function () {

                    var ppress_countries_states = <?php echo wp_json_encode(array_filter(ppress_array_of_world_states())); ?>,
                        country_state_select_tmpl = wp.template('ppress-billing-state-select'),
                        country_state_input_tmpl = wp.template('ppress-billing-state-input');

                    $(document).on('change', '#ppress_billing_country', function () {

                        var val = $(this).val(), field;

                        if (val in ppress_countries_states) {

                            field = country_state_select_tmpl({
                                options: ppress_countries_states[val]
                            });

                        } else {

                            field = country_state_input_tmpl();
                        }

                        $('#ppress_billing_state').replaceWith(field);
                    });
                })
            })(jQuery);
        </script>
        <?php
    }
}