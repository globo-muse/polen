<?php

use ProfilePress\Core\Membership\CheckoutFields;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Services\TaxService;

/** @var OrderEntity $order_data */

$billing_country = $order_data->get_meta($order_data::EU_VAT_COUNTRY_CODE);
if (empty($billing_country)) {
    $billing_country = $order_data->billing_country;
}
$vat_number             = $order_data->get_customer_tax_id();
$vat_number_is_valid    = $order_data->get_meta($order_data::EU_VAT_NUMBER_IS_VALID) == 'true';
$vat_is_reverse_charged = $order_data->get_meta($order_data::EU_VAT_IS_REVERSE_CHARGED) == 'true';
?>
    <style>
        #ppress-membership-eu-vat .inside {
            margin: 0;
            padding: 0;
        }

        #ppress-membership-eu-vat p {
            margin: 12px !important;
        }

        .ppress-eu-vat-table {
            width: 100%;
            margin: 5px 0;
        }

        .ppress-eu-vat-table th, .ppress-eu-vat-table td {
            text-align: left;
            padding: 7px 6px 7px 14px;
        }

        .ppress-eu-vat-table td:last-of-type {
            padding-right: 0;
        }
    </style>
<?php if ( ! empty($billing_country) && TaxService::init()->is_eu_countries($billing_country)) : ?>
    <table class="ppress-eu-vat-table" cellspacing="0">
        <tbody>
        <?php if ( ! empty($vat_number)) : ?>
            <tr>
                <th><?php esc_html_e('VAT Number', 'wp-user-avatar'); ?></th>
                <td><?php echo esc_html($vat_number); ?>
                    <?php echo $vat_number_is_valid ? sprintf(' <span style="color:green" title="%s" class="dashicons dashicons-yes"></span>', esc_html__('Validated', 'wp-user-avatar')) : ''; ?>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Reverse Charged', 'wp-user-avatar'); ?></th>
                <td><?php echo $vat_is_reverse_charged ? '<span style="color:green" class="dashicons dashicons-yes"></span>' : '<span style="color:red" class="dashicons dashicons-no-alt"></span>'; ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <th><?php _e('IP Address', 'wp-user-avatar'); ?></th>
            <td><?php echo $order_data->ip_address ? esc_html($order_data->ip_address) : __('Unknown', 'wp-user-avatar'); ?></td>
        </tr>
        <tr>
            <th><?php _e('Billing Country', 'wp-user-avatar'); ?></th>
            <td><?php echo esc_html(ppress_var(ppress_array_of_world_countries(), $billing_country, __('Unknown', 'wp-user-avatar'))); ?></td>
        </tr>
        </tbody>
    </table>
<?php else : ?>
    <p><?php esc_html_e('This order is out of scope for EU VAT.', 'wp-user-avatar'); ?></p>
<?php endif; ?>