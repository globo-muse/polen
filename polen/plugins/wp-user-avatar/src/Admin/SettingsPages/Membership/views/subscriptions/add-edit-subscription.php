<?php

use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;

$subscription_id = absint(ppressGET_var('id'));

$subscription_data = SubscriptionFactory::fromId($subscription_id);

if (ppressGET_var('ppress_subscription_action') == 'edit' && ! $subscription_data->exists()) {
    ppress_content_http_redirect(PPRESS_MEMBERSHIP_SUBSCRIPTION_PLANS_SETTINGS_PAGE);

    return;
}

add_action('add_meta_boxes', function () use ($subscription_data, $subscription_id) {
    add_meta_box(
        'ppress-membership-subscription-content',
        sprintf(esc_html__('Subscription #%s', 'wp-user-avatar'), $subscription_id),
        function () use ($subscription_data, $subscription_id) {
            require __DIR__ . '/data-metabox.php';
        },
        'ppmembershipsubscription'
    );


    $sub_orders = $subscription_data->get_all_orders();

    if ( ! empty($sub_orders)) {

        add_meta_box(
            'ppress-membership-subscription-payments',
            esc_html__('Subscription Orders', 'wp-user-avatar'),
            function () use ($sub_orders) {
                require __DIR__ . '/sub-payments-metabox.php';
            },
            'ppmembershipsubscription'
        );
    }

    add_meta_box(
        'submitdiv',
        __('Publish', 'wp-user-avatar'),
        function () use ($subscription_data, $subscription_id) {
            require __DIR__ . '/submit-sidebar.php';
        },
        'ppmembershipsubscription',
        'sidebar'
    );

    $sub_notes = $subscription_data->get_notes();

    if ( ! empty($sub_notes)) {
        add_meta_box(
            'ppress-membership-subscription-notes',
            __('Subscription Notes', 'wp-user-avatar'),
            function () use ($sub_notes) {
                require __DIR__ . '/subscription-notes-sidebar.php';
            },
            'ppmembershipsubscription',
            'sidebar'
        );
    }
});

do_action('add_meta_boxes', 'ppmembershipsubscription', new WP_Post(new stdClass()));
?>

<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">

        <form method="post">
            <div id="postbox-container-1" class="postbox-container">
                <?php do_meta_boxes('ppmembershipsubscription', 'sidebar', ''); ?>
            </div>
            <div id="postbox-container-2" class="postbox-container">
                <?php do_meta_boxes('ppmembershipsubscription', 'advanced', ''); ?>
            </div>
            <?php echo ppress_nonce_field(); ?>
        </form>
    </div>
    <br class="clear">
</div>