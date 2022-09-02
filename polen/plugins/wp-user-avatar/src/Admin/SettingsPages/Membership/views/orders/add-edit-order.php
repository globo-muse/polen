<?php

use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\Services\TaxService;

$order_id = absint(ppressGET_var('id'));

$order_data = OrderFactory::fromId($order_id);

if (ppressGET_var('ppress_order_action') == 'edit' && ! $order_data->exists()) {
    ppress_content_http_redirect(PPRESS_MEMBERSHIP_ORDERS_SETTINGS_PAGE);

    return;
}

add_action('add_meta_boxes', function () use ($order_data, $order_id) {
    add_meta_box(
        'ppress-membership-order-content',
        sprintf(esc_html__('Order #%s', 'wp-user-avatar'), $order_id),
        function () use ($order_data, $order_id) {
            require __DIR__ . '/order-data-metabox.php';
        },
        'ppmembershiporder'
    );

    add_meta_box(
        'ppress-membership-item-content',
        sprintf(esc_html__('Order Item', 'wp-user-avatar'), $order_id),
        function () use ($order_data, $order_id) {
            require __DIR__ . '/order-item-metabox.php';
        },
        'ppmembershiporder'
    );

    add_meta_box(
        'submitdiv',
        __('Publish', 'wp-user-avatar'),
        function () use ($order_data, $order_id) {
            require __DIR__ . '/order-submit-sidebar.php';
        },
        'ppmembershiporder',
        'sidebar'
    );

    $subscriptionObj = SubscriptionFactory::fromId(
        $order_data->get_subscription_id()
    );

    if ($subscriptionObj->exists()) {
        add_meta_box(
            'ppress-membership-order-subscription',
            __('Subscriptions', 'wp-user-avatar'),
            function () use ($order_data, $subscriptionObj) {
                require __DIR__ . '/order-subscription-sidebar.php';
            },
            'ppmembershiporder',
            'sidebar'
        );
    }

    if (TaxService::init()->is_tax_enabled() && TaxService::init()->is_eu_vat_enabled()) {
        add_meta_box(
            'ppress-membership-eu-vat',
            __('EU VAT', 'wp-user-avatar'),
            function () use ($order_data) {
                require __DIR__ . '/eu-vat-sidebar.php';
            },
            'ppmembershiporder',
            'sidebar'
        );
    }

    $order_notes = $order_data->get_notes();

    if ( ! empty($order_notes)) {
        add_meta_box(
            'ppress-membership-order-notes',
            __('Order Notes', 'wp-user-avatar'),
            function () use ($order_notes) {
                require __DIR__ . '/order-notes-sidebar.php';
            },
            'ppmembershiporder',
            'sidebar'
        );
    }
});

do_action('add_meta_boxes', 'ppmembershiporder', new WP_Post(new stdClass()));
?>

<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">

        <form method="post">
            <div id="postbox-container-1" class="postbox-container">
                <?php do_meta_boxes('ppmembershiporder', 'sidebar', ''); ?>
            </div>
            <div id="postbox-container-2" class="postbox-container">
                <?php do_meta_boxes('ppmembershiporder', 'advanced', ''); ?>
            </div>
            <?php echo ppress_nonce_field(); ?>
        </form>
    </div>
    <br class="clear">
</div>
<script type="text/javascript">
    var ppress_order_replace_modal_title = '<?php esc_html_e('Add or Replace Order Item', 'wp-user-avatar'); ?>';
    var ppress_modal_empty_plan_error = '<?php esc_html_e('Please select a subscription plan', 'wp-user-avatar'); ?>';
</script>
<script type="text/html" id="tmpl-add-replace-order-template">
    <div class="ppress-order-item-modal-wrap">
        <p>
            <label for="subscription-plans"><?php esc_html_e('Plan', 'wp-user-avatar'); ?></label>
            <select id="subscription-plans" class="ppress-order-change-modal">
                <option value=""><?php esc_html_e('Select Plan', 'wp-user-avatar'); ?></option>
            </select>
        </p>

        <p>
            <label for="sub_plan_price"><?php esc_html_e('Plan Price', 'wp-user-avatar'); ?></label>
            <input id="sub_plan_price" type="text" placeholder="<?php echo ppress_display_amount('0.00') ?>">
        </p>

        <?php if (TaxService::init()->is_tax_enabled()) : ?>
            <p>
                <label for="tax-amount"><?php esc_html_e('Tax/VAT Fee', 'wp-user-avatar'); ?></label>
                <input id="tax-amount" type="text" placeholder="<?php echo ppress_display_amount('0.00') ?>">
            </p>
        <?php endif; ?>
        <p>
            <label for="order_coupon"><?php esc_html_e('Coupon', 'wp-user-avatar'); ?></label>
            <select id="order_coupon" class="ppress-order-change-modal">
                <option value=""><?php esc_html_e('Select Coupon', 'wp-user-avatar'); ?></option>
            </select>
        </p>
        <?php if (TaxService::init()->is_tax_enabled()): ?>
            <?php if (TaxService::init()->is_price_inclusive_tax()): ?>
                <p><?php esc_html_e('Price is inclusive of VAT/Sales Tax', 'wp-user-avatar'); ?></p>
            <?php else : ?>
                <p><?php esc_html_e('Price is exclusive of VAT/Sales Tax', 'wp-user-avatar'); ?></p>
            <?php endif; ?>
        <?php endif; ?>
        <p>
            <button id="save-order-change" type="button" class="button button-primary"><?php esc_html_e('Save', 'wp-user-avatar'); ?></button>
        </p>
    </div>
</script>