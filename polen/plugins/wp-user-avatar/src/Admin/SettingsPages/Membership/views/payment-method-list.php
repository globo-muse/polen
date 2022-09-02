<?php

$payment_methods = ProfilePress\Core\Membership\PaymentMethods\PaymentMethods::get_instance()->get_all(); ?>
<div class="ppress-payment-methods-wrap">
    <table cellspacing="0" class="widefat">
        <thead>
        <tr>
            <th class="ppress-payment-method-table-sort"></th>
            <th class="ppress-payment-method-table-title"><?php esc_html_e('Method', 'wp-user-avatar') ?></th>
            <th class="ppress-payment-method-table-enabled"><?php esc_html_e('Enabled', 'wp-user-avatar') ?></th>
            <th class="ppress-payment-method-table-description"><?php esc_html_e('Description', 'wp-user-avatar') ?></th>
            <th class="ppress-payment-method-table-subscription-support"><?php esc_html_e('Subscription Support', 'wp-user-avatar') ?></th>
            <th class="ppress-payment-method-table-actions"></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($payment_methods as $payment_method) : ?>
            <?php $config_url = esc_url(add_query_arg('method', $payment_method->get_id())); ?>
            <tr>
                <td class="ppress-payment-method-table-sort">
                 <span class="gateway-sort"><span class="dashicons dashicons-menu"></span>
                    <input type="hidden" name="payment_gateways_order[]" value="<?php echo $payment_method->get_id() ?>"></span>
                </td>
                <td class="ppress-payment-method-table-title">
                    <a href="<?= $config_url ?>"><?php echo $payment_method->get_method_title() ?></a>
                </td>
                <td class="ppress-payment-method-table-enabled">
                    <?php echo $payment_method->is_enabled() ?
                        '<span class="ppress-payment-method-icon ico-yes"><span class="dashicons dashicons-yes"></span></span>' :
                        '<span class="ppress-payment-method-icon"><span class="dashicons dashicons-no-alt"></span></span>'
                    ?>
                </td>
                <td class="ppress-payment-method-table-description">
                    <?php echo $payment_method->get_method_description() ?>
                </td>
                <td class="ppress-payment-method-table-subscription-support">
                    <?php echo $payment_method->supports($payment_method::SUBSCRIPTIONS) ?
                        '<span class="ppress-payment-method-icon ico-yes"><span class="dashicons dashicons-yes"></span></span>' :
                        '<span class="ppress-payment-method-icon"><span class="dashicons dashicons-no-alt"></span></span>'
                    ?>
                </td>
                <td class="ppress-payment-method-table-actions">
                    <a href="<?= $config_url ?>" class="button"><?php esc_html_e('Configure', 'wp-user-avatar'); ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ( ! ProfilePress\Core\Classes\ExtensionManager::is_premium()) : $paypal_upsell_url = 'https://profilepress.com/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=paypal-gateway-method'; ?>
            <tr>
                <td class="ppress-payment-method-table-sort">
                    <span class="gateway-sort"><span class="dashicons dashicons-menu"></span>
                </td>
                <td class="ppress-payment-method-table-title">
                    <a target="_blank" href="https://profilepress.com/addons/paypal/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=paypal-gateway-method">PayPal</a>
                </td>
                <td class="ppress-payment-method-table-enabled">
                    <span class="ppress-payment-method-icon"><span class="dashicons dashicons-no-alt"></span></span>
                </td>
                <td class="ppress-payment-method-table-description">
                    <?php echo sprintf(
                        esc_html__('Wish to accept payments and sell memberships via PayPal? %supgrade to premium%s.', 'wp-user-avatar'),
                        '<a target="_blank" href="' . $paypal_upsell_url . '">', '</a>'
                    ) ?>
                </td>
                <td class="ppress-payment-method-table-subscription-support">
                    <span class="ppress-payment-method-icon ico-yes"><span class="dashicons dashicons-yes"></span></span>
                </td>
                <td class="ppress-payment-method-table-actions">
                    <a target="_blank" href="<?= $paypal_upsell_url ?>" class="button"><?php esc_html_e('Configure', 'wp-user-avatar'); ?></a>
                </td>
            </tr>
        <?php endif ?>
        </tbody>
    </table>
</div>