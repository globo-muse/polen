<?php

use ProfilePress\Core\Admin\SettingsPages\Membership\CustomersPage\CustomerWPListTable;
use ProfilePress\Core\Membership\CheckoutFields as CF;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Order\OrderType;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\Services\OrderService;

/** @global OrderEntity $order_data */
/** @global int $order_id */

$payment_method_title = '';
$transaction_id       = $order_data->transaction_id;


if ( ! empty($order_data->payment_method)) {
    $payment_method_instance = PaymentMethods::get_instance()->get_by_id($order_data->payment_method);
    $transaction_id          = $payment_method_instance->link_transaction_id($transaction_id);
    $payment_method_title    = $payment_method_instance->get_method_title();
}

$customer = CustomerFactory::fromId($order_data->customer_id);

$sb = CF::standard_billing_fields();

$meta_list = [];

$payment_method_string = '';

if ( ! empty($payment_method_title)) {
    $payment_method_string .= sprintf(__('Payment via %s', 'wp-user-avatar'), esc_html($payment_method_title));
}

if ( ! empty($transaction_id)) {
    $payment_method_string .= ' (' . wp_kses_post($transaction_id) . ')';
}

if ( ! empty($payment_method_string)) $meta_list[] = $payment_method_string;

if ($order_data->date_completed) {
    /* translators: 1: date 2: time */
    $meta_list[] = sprintf(
        __('Paid on %1$s @ %2$s', 'wp-user-avatar'),
        wp_date(get_option('date_format'), ppress_date_to_utc_timestamp($order_data->date_completed)),
        wp_date(get_option('time_format'), ppress_date_to_utc_timestamp($order_data->date_completed))
    );
}

if ($ip_address = $order_data->ip_address) {
    /* translators: %s: IP address */
    $meta_list[] = sprintf(
        __('Customer IP: %s', 'wp-user-avatar'),
        '<span class="ppress-order-customer-ip">' . esc_html($ip_address) . '</span>'
    );
}

echo '<div class="ppress-membership-order-details">';
$badge = '';
if ($order_data->is_completed()) {
    $badge = sprintf('<span class="ppress-order-badge">%s</span>', OrderType::get_label($order_data->order_type));
}
printf('<h2 class="ppress-metabox-data-heading">' . esc_html__('Order #%s', 'wp-user-avatar') . ' %s</h2>', $order_id, $badge);

echo '<p class="ppress-metabox-meta-data">';
echo wp_kses_post(implode('. ', $meta_list));
echo '</p>';
?>
    <div class="ppress-metabox-data-column-container">
        <div class="ppress-metabox-data-column">
            <h3><?php esc_html_e('General', 'wp-user-avatar'); ?></h3>

            <p class="mb-form-field order_date">
                <label for="order_date"><?php _e('Date created:', 'wp-user-avatar'); ?></label>
                <input id="order_date" type="text" class="ppress_datetime_picker" name="order_date" value="<?php echo esc_attr(ppress_format_date_time($order_data->date_created, 'Y-m-d H:i')); ?>"/>
            </p>

            <p class="mb-form-field order_status">
                <label for="order_status"><?php _e('Status:', 'wp-user-avatar'); ?></label>
                <select id="order_status" name="order_status">
                    <?php foreach (OrderStatus::get_all() as $id => $label) : ?>
                        <option value="<?= $id ?>" <?php selected($id, $order_data->status) ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p class="mb-form-field customer_user">
                <label for="customer_user">
                    <?php _e('Customer:', 'wp-user-avatar'); ?>
                    <a href="<?= esc_url(CustomerWPListTable::view_customer_url($customer->id)) ?>"><?= esc_html__('Profile &rarr;', 'wp-user-avatar') ?></a>
                    <a href="<?= OrderService::init()->get_customer_orders_url($customer->id) ?>"><?= esc_html__('View other orders &rarr;', 'wp-user-avatar') ?></a>
                </label>
                <select id="customer_user" name="customer_user" class="ppress-select2-field customer_user">
                    <option value="<?= $order_data->customer_id ?>" selected>
                        <?php printf(esc_html__('%1$s (%2$s)', 'wp-user-avatar'), $customer->get_name(), $customer->get_email()) ?>
                    </option>
                </select>
            </p>

            <p class="mb-form-field order_key">
                <label for="order_key"><?php _e('Order Key:', 'wp-user-avatar'); ?></label>
                <input type="text" name="order_key" value="<?= $order_data->order_key ?>" readonly>
            </p>
        </div>

        <div class="ppress-metabox-data-column">
            <h3>
                <?php esc_html_e('Billing Address', 'wp-user-avatar'); ?>
                <a href="#" class="edit_address"><?php esc_html_e('Edit', 'wp-user-avatar'); ?></a>
            </h3>
            <div class="ppress-billing-details">
                <?php foreach (CF::standard_billing_fields() as $field_id => $field) {

                    $method_name = str_replace('ppress_', '', $field_id);

                    $detail = $order_data->$method_name;

                    if ( ! empty($detail)) {

                        if ($field_id == CF::BILLING_COUNTRY) {
                            $detail = ppress_array_of_world_countries($detail);
                        }

                        if ($field_id == CF::BILLING_STATE) {
                            $state  = ppress_array_of_world_states($order_data->billing_country);
                            $detail = ppress_var($state, $detail, $detail, true);
                        }

                    } else {
                        $detail = esc_html__('Not Set', 'wp-user-avatar');
                    }

                    echo '<p>';
                    printf('<strong>%s</strong>: %s', esc_html($field['label']), esc_html($detail));
                    echo '</p>';
                }
                ?>
            </div>

            <div class="ppress_edit_address_wrap">
                <?php foreach (CF::standard_billing_fields() as $field_id => $field) {

                    $method_name = str_replace('ppress_', '', $field_id);

                    $detail = $order_data->$method_name;

                    echo '<p class="ppress-metabox-form-field">';
                    printf('<label for="%s">%s</label>', $field_id, $field['label']);
                    echo do_shortcode(sprintf('[edit-profile-cpf id="%1$s" key="%1$s" value="%2$s" billing_country="%3$s"]', $field_id, $detail, $order_data->billing_country), true);
                    echo '</p>';
                }
                ?>
            </div>
            <?php do_action('ppress_admin_order_data_after_billing_address', $order_id, $order_data); ?>
        </div>
    </div>
<?php

echo '</div>';