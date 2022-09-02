<?php

use ProfilePress\Core\Admin\SettingsPages\Membership\CustomersPage\CustomerWPListTable;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePress\Core\Membership\Services\TaxService;

/** @global SubscriptionEntity $subscription_data */
/** @global int $subscription_id */

$customer                          = CustomerFactory::fromId($subscription_data->customer_id);
$parent_order_data                 = OrderFactory::fromId($subscription_data->parent_order_id);
$parent_order_data_currency_symbol = ppress_get_currency_symbol($parent_order_data->currency);
$planInstance                      = PlanFactory::fromId($subscription_data->plan_id);

$payment_method_title = '';
$profile_id           = $subscription_data->profile_id;

if ( ! empty($parent_order_data->payment_method)) {
    $payment_method_instance = PaymentMethods::get_instance()->get_by_id($parent_order_data->payment_method);
    $profile_id              = $payment_method_instance->link_profile_id($profile_id);
    $payment_method_title    = $payment_method_instance->get_method_title();
}

$payment_method_string = '';

if ( ! empty($payment_method_title)) {
    $payment_method_string .= sprintf(__('Payment via %s', 'wp-user-avatar'), esc_html($payment_method_title));
}

if ( ! empty($profile_id)) {
    $payment_method_string .= ' (' . wp_kses_post($profile_id) . ')';
}

echo '<div class="ppress-membership-subscription-details">';

printf('<h2 class="ppress-metabox-data-heading">' . esc_html__('Subscription #%s', 'wp-user-avatar') . '</h2>', $subscription_id);

if ( ! empty($payment_method_string)) {
    echo '<p class="ppress-metabox-meta-data">';
    echo $payment_method_string;
    echo '</p>';
}
?>
    <div class="ppress-metabox-data-column-container">
        <div class="ppress-metabox-data-column">

            <p class="mb-form-field sub_plan">
                <strong><?php _e('Subscription Plan:', 'wp-user-avatar'); ?></strong>
                <a href="<?= $planInstance->get_edit_plan_url() ?>"><?= $planInstance->get_name() ?></a>
            </p>

            <p class="mb-form-field sub_terms">
                <strong><?php _e('Terms:', 'wp-user-avatar'); ?></strong>
                <?php echo $subscription_data->get_subscription_terms(); ?>
            </p>

            <?php if ($subscription_data->get_total_payments() > 0) : ?>
                <p class="mb-form-field completed_payments">
                    <strong><?php _e('Completed Payments:', 'wp-user-avatar'); ?></strong>
                    <?php printf('%s / %s', $subscription_data->get_completed_order_count(), $subscription_data->get_total_payments()); ?>
                </p>
            <?php endif; ?>

            <?php if (TaxService::init()->is_tax_enabled()) : ?>
                <p class="mb-form-field sub_initial_amount">
                    <label for="sub_initial_amount"><?php printf(esc_html__('Initial Amount (%s):', 'wp-user-avatar'), $parent_order_data_currency_symbol); ?></label>
                    <input id="sub_initial_amount" type="text" name="sub_initial_amount" value="<?php echo esc_attr(ppress_sanitize_amount($subscription_data->initial_amount)); ?>"/>
                </p>

                <p class="mb-form-field sub_initial_tax">
                    <label for="sub_initial_tax"><?php printf(esc_html__('Initial Tax Amount (%s):', 'wp-user-avatar'), $parent_order_data_currency_symbol); ?></label>
                    <input id="sub_initial_tax" type="text" name="sub_initial_tax" value="<?php echo esc_attr(ppress_sanitize_amount($subscription_data->initial_tax)); ?>"/>
                </p>

                <p class="mb-form-field sub_initial_tax_rate">
                    <label for="sub_initial_tax_rate"><?php printf(esc_html__('Initial Tax Rate (%s):', 'wp-user-avatar'), '%'); ?></label>
                    <input id="sub_initial_tax_rate" type="text" name="sub_initial_tax_rate" value="<?php echo esc_attr($subscription_data->initial_tax_rate); ?>"/>
                </p>

                <p class="mb-form-field sub_recurring_amount">
                    <label for="sub_recurring_amount"><?php printf(esc_html__('Recurring Amount (%s):', 'wp-user-avatar'), $parent_order_data_currency_symbol); ?></label>
                    <input id="sub_recurring_amount" type="text" name="sub_recurring_amount" value="<?php echo esc_attr(ppress_sanitize_amount($subscription_data->recurring_amount)); ?>"/>
                </p>

                <p class="mb-form-field sub_recurring_tax">
                    <label for="sub_recurring_tax"><?php printf(esc_html__('Recurring Tax Amount (%s):', 'wp-user-avatar'), $parent_order_data_currency_symbol); ?></label>
                    <input id="sub_recurring_tax" type="text" name="sub_recurring_tax" value="<?php echo esc_attr(ppress_sanitize_amount($subscription_data->recurring_tax)); ?>"/>
                </p>

                <p class="mb-form-field sub_recurring_tax_rate">
                    <label for="sub_recurring_tax_rate"><?php printf(esc_html__('Recurring Tax Rate (%s):', 'wp-user-avatar'), '%'); ?></label>
                    <input id="sub_recurring_tax_rate" type="text" name="sub_recurring_tax_rate" value="<?php echo esc_attr($subscription_data->recurring_tax_rate); ?>"/>
                </p>
            <?php endif; ?>
        </div>
        <div class="ppress-metabox-data-column">

            <p class="mb-form-field sub_status">
                <label for="sub_status"><?php _e('Status:', 'wp-user-avatar'); ?></label>
                <select id="sub_status" name="sub_status">
                    <?php foreach (SubscriptionStatus::get_all() as $id => $label) : ?>
                        <option value="<?= $id ?>" <?php selected($id, $subscription_data->status) ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p class="mb-form-field customer_user">
                <label for="sub_customer_user">
                    <?php _e('Customer:', 'wp-user-avatar'); ?>
                    <a href="<?= esc_url(CustomerWPListTable::view_customer_url($subscription_data->customer_id)) ?>"><?= esc_html__('Profile &rarr;', 'wp-user-avatar') ?></a>
                    <a href="<?= add_query_arg(['by_ci' => $subscription_data->customer_id], PPRESS_MEMBERSHIP_SUBSCRIPTIONS_SETTINGS_PAGE) ?>"><?= esc_html__('View all subscriptions &rarr;', 'wp-user-avatar') ?></a>
                </label>
                <select id="sub_customer_user" name="sub_customer_user" class="ppress-select2-field sub_customer_user">
                    <option value="<?= $subscription_data->customer_id ?>" selected>
                        <?php printf(esc_html__('%1$s (%2$s)', 'wp-user-avatar'), $customer->get_name(), $customer->get_email()) ?>
                    </option>
                </select>
            </p>

            <p class="mb-form-field sub_date_created">
                <label for="sub_created_date"><?php _e('Date created:', 'wp-user-avatar'); ?></label>
                <input id="sub_created_date" type="text" class="ppress_datepicker" name="sub_created_date" value="<?php echo esc_attr(ppress_format_date($subscription_data->created_date, 'Y-m-d')); ?>"/>
            </p>

            <p class="mb-form-field sub_expiration_date">
                <label for="sub_expiration_date"><?php _e('Renewal Date:', 'wp-user-avatar'); ?></label>
                <?php if ($subscription_data->is_lifetime()) : ?>
                    <input id="sub_expiration_date" type="text" value="<?= esc_attr(esc_html__('Lifetime', 'wp-user-avatar')) ?>" readonly/>
                <?php else : ?>
                    <input id="sub_expiration_date" type="text" class="ppress_datepicker" name="sub_expiration_date" value="<?php echo esc_attr(ppress_format_date($subscription_data->expiration_date, 'Y-m-d')); ?>"/>
                <?php endif; ?>
            </p>

            <p class="mb-form-field sub_profile_id">
                <label for="sub_profile_id"><?php printf(esc_html__('%sSubscription ID:', 'wp-user-avatar'), $payment_method_title . ' '); ?></label>
                <input id="sub_profile_id" type="text" name="sub_profile_id" value="<?= $subscription_data->get_profile_id() ?>">
            </p>
        </div>

    </div>
<?php

echo '</div>';