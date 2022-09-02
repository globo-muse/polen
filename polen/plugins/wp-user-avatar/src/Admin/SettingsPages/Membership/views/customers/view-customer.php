<?php

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;

$customer_id = absint(ppressGET_var('id'));

$customer_data = CustomerFactory::fromId($customer_id);

if (ppressGET_var('ppress_customer_action') == 'edit' && ! $customer_data->exists()) {
    ppress_content_http_redirect(PPRESS_MEMBERSHIP_CUSTOMERS_SETTINGS_PAGE);

    return;
}

add_action('add_meta_boxes', function () use ($customer_data) {

    add_meta_box(
        'ppress-membership-customer-content',
        sprintf(esc_html__('Customer #%s', 'wp-user-avatar'), $customer_data->id),
        function () use ($customer_data) {
            require __DIR__ . '/data-metabox.php';
        },
        'ppmembershipcustomer'
    );

    $subscriptions = SubscriptionRepository::init()->retrieveBy([
        'customer_id' => $customer_data->id,
        'status'      => [SubscriptionStatus::ACTIVE],
        'number' => 0
    ]);

    if ( ! empty($subscriptions)) {

        add_meta_box(
            'ppress-membership-customer-subscriptions',
            esc_html__('Active Subscriptions', 'wp-user-avatar'),
            function () use ($subscriptions) {
                require __DIR__ . '/subcriptions-metabox.php';
            },
            'ppmembershipcustomer'
        );
    }

    add_meta_box(
        'submitdiv',
        __('Publish', 'wp-user-avatar'),
        function () use ($customer_data) {
            require __DIR__ . '/submit-sidebar.php';
        },
        'ppmembershipcustomer',
        'sidebar'
    );

    add_meta_box(
        'ppress-membership-customer-private-note',
        __('Private Note', 'wp-user-avatar'),
        function () use ($customer_data) {
            printf(
                '<textarea name="private_note" style="%s">%s</textarea>',
                'min-height:100px;width:100% !important;max-width:100% !important;',
                sanitize_textarea_field($customer_data->private_note)
            );
        },
        'ppmembershipcustomer',
        'sidebar'
    );
});

do_action('add_meta_boxes', 'ppmembershipcustomer', new WP_Post(new stdClass()));
?>

<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">

        <form method="post">
            <div id="postbox-container-1" class="postbox-container">
                <?php do_meta_boxes('ppmembershipcustomer', 'sidebar', ''); ?>
            </div>
            <div id="postbox-container-2" class="postbox-container">
                <?php do_meta_boxes('ppmembershipcustomer', 'advanced', ''); ?>
            </div>
            <?php echo ppress_nonce_field(); ?>
        </form>
    </div>
    <br class="clear">
</div>