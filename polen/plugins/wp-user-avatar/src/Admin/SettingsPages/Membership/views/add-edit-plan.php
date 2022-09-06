<?php

use ProfilePress\Core\Admin\SettingsPages\Membership\SettingsFieldsParser;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionTrialPeriod;

$plan_details = [
    [
        'id'    => 'name',
        'type'  => 'text',
        'label' => esc_html__('Plan Name', 'wp-user-avatar')
    ],
    [
        'id'          => 'description',
        'type'        => 'wp_editor',
        'label'       => esc_html__('Plan Description', 'wp-user-avatar'),
        'description' => esc_html__('A description of this plan. This will be displayed on the checkout page.', 'wp-user-avatar')
    ],
    [
        'id'          => 'order_note',
        'type'        => 'textarea',
        'label'       => esc_html__('Purchase Note', 'wp-user-avatar'),
        'description' => esc_html__('Enter an optional note or special instructions to send the customer after purchase. These will be added to the order receipt.', 'wp-user-avatar')
    ],
    [
        'id'          => 'price',
        'type'        => 'price',
        'label'       => esc_html__('Price', 'wp-user-avatar') . sprintf(' (%s)', ppress_get_currency_symbol()),
        'description' => esc_html__('The price of this membership plan. Enter 0 to make this plan free.', 'wp-user-avatar')
    ]
];

$subscription_settings = [
    [
        'id'      => 'billing_frequency',
        'type'    => 'select',
        'label'   => esc_html__('Billing Frequency', 'wp-user-avatar'),
        'options' => SubscriptionBillingFrequency::get_all()
    ],
    [
        'id'      => 'subscription_length',
        'type'    => 'select',
        'label'   => esc_html__('Subscription Length', 'wp-user-avatar'),
        'options' => [
            'renew_indefinitely' => esc_html__('Renew indefinitely until member cancels', 'wp-user-avatar'),
            'fixed'              => esc_html__('Fixed number of payments', 'wp-user-avatar')
        ]
    ],
    [
        'id'          => 'total_payments',
        'type'        => 'number',
        'label'       => esc_html__('Total Payments', 'wp-user-avatar'),
        'description' => esc_html__('The total number of recurring billing cycles including the trial period (if applicable).  Keep in mind that once a member has completed the last payment, the subscription will not expire — essentially giving them lifetime access.', 'wp-user-avatar')
    ],
    [
        'id'          => 'signup_fee',
        'type'        => 'price',
        'label'       => esc_html__('Signup Fee', 'wp-user-avatar') . sprintf(' (%s)', ppress_get_currency_symbol()),
        'description' => esc_html__('Optional signup fee to charge subscribers for the first billing cycle.', 'wp-user-avatar')
    ],
    [
        'id'          => 'free_trial',
        'type'        => 'select',
        'options'     => SubscriptionTrialPeriod::get_all(),
        'label'       => esc_html__('Free Trial', 'wp-user-avatar'),
        'description' => esc_html__('Allow members free access for a specified duration of time before charging them.', 'wp-user-avatar')
    ]
];

$plan_data = ppress_get_plan(absint(ppressGET_var('id')));

if (ppressGET_var('ppress_subp_action') == 'edit' && ! $plan_data->exists()) {
    ppress_content_http_redirect(PPRESS_MEMBERSHIP_PLANS_SETTINGS_SLUG);

    return;
}

add_action('add_meta_boxes', function () use ($subscription_settings, $plan_details, $plan_data) {
    add_meta_box(
        'ppress-membership-plan-content',
        esc_html__('Plan Details', 'wp-user-avatar'),
        function () use ($plan_details, $plan_data) {
            echo '<div class="ppress-membership-plan-details">';
            (new SettingsFieldsParser($plan_details, $plan_data))->build();
            echo '</div>';
        },
        'ppmembershipplan'
    );

    add_meta_box(
        'ppress-subscription-plan-settings',
        esc_html__('Subscription Settings', 'wp-user-avatar'),
        function () use ($subscription_settings, $plan_data) {
            echo '<div class="ppress-subscription-plan-settings">';
            (new SettingsFieldsParser($subscription_settings, $plan_data))->build();
            echo '</div>';
        },
        'ppmembershipplan'
    );

    add_meta_box(
        'submitdiv',
        __('Publish', 'wp-user-avatar'),
        function () {
            require dirname(__FILE__) . '/plans-page-sidebar.php';
        },
        'ppmembershipplan',
        'sidebar'
    );

    add_meta_box(
        'ppress-subscription-plan-summary',
        __('Summary', 'wp-user-avatar'),
        function () {
            ?>
            <div class="ppress-subscription-plan-summary-content">
            </div>
            <?php
        },
        'ppmembershipplan',
        'sidebar'
    );

    if ($plan_data->exists()) {
        add_meta_box(
            'ppress-subscription-plan-links',
            __('Order Links', 'wp-user-avatar'),
            function () use ($plan_data) {
                $checkout_url = $plan_data->get_checkout_url();
                ?>
                <div class="ppress-subscription-plan-payment-links">
                    <p>
                        <label><?php _e('Checkout link:', 'wp-user-avatar'); ?>
                            <input type="text" onfocus="this.select();" readonly="readonly" value="<?= esc_url($checkout_url) ?>"/>
                        </label>
                    </p>
                </div>
                <?php
            },
            'ppmembershipplan',
            'sidebar'
        );
    }
});

do_action('add_meta_boxes', 'ppmembershipplan', new WP_Post(new stdClass()));
?>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="postbox-container-1" class="postbox-container">
                <?php do_meta_boxes('ppmembershipplan', 'sidebar', ''); ?>
            </div>
            <div id="postbox-container-2" class="postbox-container">
                <?php do_meta_boxes('ppmembershipplan', 'advanced', ''); ?>
            </div>
        </div>
        <br class="clear">
    </div>

<?php add_action('admin_footer', function () { ?>
    <script type="text/javascript">
        (function ($) {

            $('#billing_frequency').on('change', function () {

                if ($(this).val() !== 'lifetime') {

                    $('#field-role-signup_fee').show();
                    $('#field-role-free_trial').show();

                    $('#field-role-subscription_length').show()
                        .find('.ppress-plan-control').change();
                } else {
                    $('#field-role-subscription_length').hide();
                    $('#field-role-total_payments').hide();
                    $('#field-role-signup_fee').hide();
                    $('#field-role-free_trial').hide();
                }
            });

            $('#subscription_length').on('change', function () {
                $('#field-role-total_payments').toggle($(this).val() === 'fixed');
            });

            $('#billing_frequency').change();

            $(window).on('load', function () {
                var tmpl = wp.template('ppress-plan-summary');

                $('.ppress-plan-control').on('change', function () {
                    $('#ppress-subscription-plan-summary .ppress-subscription-plan-summary-content').html(
                        tmpl({
                            'price': $('.form-field #price').val(),
                            'billing_frequency': $('.form-field #billing_frequency').val(),
                            'total_payments': $('.form-field #total_payments').val(),
                            'signup_fee': $('.form-field #signup_fee').val(),
                            'subscription_length': $('.form-field #subscription_length').val(),
                            'free_trial': $('.form-field #free_trial').val(),
                        })
                    );
                }).change();

            });
        })(jQuery);
    </script>
    <?php
});