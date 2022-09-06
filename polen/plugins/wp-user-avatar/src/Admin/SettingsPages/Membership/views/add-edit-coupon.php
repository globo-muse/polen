<?php

use ProfilePress\Core\Admin\SettingsPages\Membership\SettingsFieldsParser;
use ProfilePress\Core\Membership\Models\Coupon\CouponFactory;
use ProfilePress\Core\Membership\Models\Plan\PlanEntity;
use ProfilePress\Core\Membership\Repositories\PlanRepository;

add_action('admin_footer', function () {
    ?>
    <script type="text/javascript">
        jQuery(function () {
            jQuery('#plan_ids').select2();
            jQuery('#start_date, #end_date').flatpickr({dateFormat: "Y-m-d", allowInput: true});
        });
    </script>
    <?php
});

$coupon_details = [
    [
        'id'          => 'code',
        'type'        => 'text',
        'label'       => esc_html__('Coupon Code', 'wp-user-avatar'),
        'description' => esc_html__('Enter a unique coupon code. Leave empty to generate a code for you on form submission.', 'wp-user-avatar')
    ],
    [
        'id'          => 'description',
        'type'        => 'textarea',
        'label'       => esc_html__('Description', 'wp-user-avatar'),
        'description' => esc_html__('A description of this coupon.', 'wp-user-avatar')
    ],
    [
        'id'    => 'discount',
        'type'  => 'discount',
        'label' => esc_html__('Discount', 'wp-user-avatar'),
    ],
    [
        'id'          => 'coupon_type',
        'type'        => 'select',
        'label'       => esc_html__('Coupon Type', 'wp-user-avatar'),
        'options'     => [
            'recurring' => esc_html__('Recurring', 'wp-user-avatar'),
            'one_time'  => esc_html__('One-time', 'wp-user-avatar')
        ],
        'description' => esc_html__('Selecting "One-time" applies the coupon discount only to the first payment while "Recurring" applies the discount to all payments.', 'wp-user-avatar')
    ],
    [
        'id'          => 'coupon_application',
        'type'        => 'radio',
        'label'       => esc_html__('Coupon Application', 'wp-user-avatar'),
        'options'     => [
            'acquisition' => esc_html__('To new purchases only', 'wp-user-avatar'),
            'retention'   => esc_html__('To existing purchase (upgrades)', 'wp-user-avatar'),
            'any'         => esc_html__('To both new and existing purchases', 'wp-user-avatar')
        ],
        /** @todo add support for when upgrade/downgrade is live */
        'description' => esc_html__('Select where members can apply this coupon.', 'wp-user-avatar')
    ]
];

$redemption_settings = [
    [
        'id'          => 'plan_ids',
        'type'        => 'select',
        'multiple'    => true,
        'label'       => esc_html__('Membership Plans', 'wp-user-avatar'),
        'options'     => array_reduce(PlanRepository::init()->retrieveAll(), function ($carry, PlanEntity $plan) {
            $carry[$plan->get_id()] = $plan->get_name();

            return $carry;
        }, []),
        'description' => esc_html__('Select membership plans this coupon can only be applied to. Leave blank for all plans.', 'wp-user-avatar')
    ],
    [
        'id'          => 'start_date',
        'type'        => 'text',
        'placeholder' => 'yyyy-mm-dd',
        'label'       => esc_html__('Start Date', 'wp-user-avatar'),
        'description' => sprintf(
            esc_html__('Enter the date that this coupon will be valid from. Leave blank for no start date. (UTC %s)', 'wp-user-avatar'),
            (new DateTime())->setTimeZone(wp_timezone())->format('P')
        )
    ],
    [
        'id'          => 'end_date',
        'type'        => 'text',
        'placeholder' => 'yyyy-mm-dd',
        'label'       => esc_html__('End Date', 'wp-user-avatar'),
        'description' => sprintf(
            esc_html__('Enter the date that this coupon will expire on. Leave blank for no end date. (UTC %s)', 'wp-user-avatar'),
            (new DateTime())->setTimeZone(wp_timezone())->format('P')
        )
    ],
    [
        'id'          => 'usage_limit',
        'type'        => 'text',
        'placeholder' => esc_html__('Unlimited', 'wp-user-avatar'),
        'label'       => esc_html__('Maximum Redemptions ', 'wp-user-avatar'),
        'description' => esc_html__('Limit the total amount of redemptions for this coupon. Leave blank for unlimited.', 'wp-user-avatar')
    ],
];

$coupon_data = CouponFactory::fromId(absint(ppressGET_var('id')));

if (ppressGET_var('ppress_coupon_action') == 'edit' && ! $coupon_data->exists()) {
    ppress_content_http_redirect(PPRESS_MEMBERSHIP_COUPONS_SETTINGS_PAGE);

    return;
}

add_action('add_meta_boxes', function () use ($coupon_details, $redemption_settings, $coupon_data) {

    add_meta_box(
        'ppress-membership-coupon-content',
        esc_html__('Coupon Details', 'wp-user-avatar'),
        function () use ($coupon_details, $coupon_data) {
            echo '<div class="ppress-membership-coupon-details">';
            (new SettingsFieldsParser($coupon_details, $coupon_data, 'ppress-coupon-control'))->build();
            echo '</div>';
        },
        'ppmembershipcoupon'
    );

    add_meta_box(
        'ppress-subscription-coupon-settings',
        esc_html__('Redemption Settings', 'wp-user-avatar'),
        function () use ($redemption_settings, $coupon_data) {
            echo '<div class="ppress-subscription-coupon-settings">';
            (new SettingsFieldsParser($redemption_settings, $coupon_data))->build();
            echo '</div>';
        },
        'ppmembershipcoupon'
    );

    add_meta_box(
        'submitdiv',
        __('Publish', 'wp-user-avatar'),
        function () {
            require dirname(__FILE__) . '/coupons-page-sidebar.php';
        },
        'ppmembershipcoupon',
        'sidebar'
    );
});

do_action('add_meta_boxes', 'ppmembershipcoupon', new WP_Post(new stdClass()));
?>
<style type="text/css">
    .ppview .postbox .ppress-amount-type-wrapper select {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        width: 50px !important;
        max-width: none !important;
    }

    .ppview .postbox .ppress-amount-type-wrapper input#amount {
        width: 300px !important;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        margin-right: -2px;
        padding: 0 8px;
        max-width: 125px !important;
    }

    .ppview .postbox .ppress-amount-type-wrapper {
        position: relative;
        display: flex
    }

    .ppview .postbox .ppress-amount-type-wrapper input:focus {
        z-index: 2
    }

    .ppview #field-role-coupon_application label {
        display: block;
        margin: 10px 0;
    }
</style>
<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">

        <div id="postbox-container-1" class="postbox-container">
            <?php do_meta_boxes('ppmembershipcoupon', 'sidebar', ''); ?>
        </div>
        <div id="postbox-container-2" class="postbox-container">
            <?php do_meta_boxes('ppmembershipcoupon', 'advanced', ''); ?>
        </div>
    </div>
    <br class="clear">
</div>