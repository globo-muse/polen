<?php

use ProfilePress\Core\Admin\SettingsPages\Membership\OrdersPage\OrderWPListTable;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;

/** @global OrderEntity[] $sub_orders */

?>


<div id="ppress-submetabox-items">
    <div class="ppress-submetabox-items-table-wrapper">
        <table cellpadding="0" cellspacing="0" class="ppress-submetabox-items-table">
            <thead>
            <tr>
                <th class="ppress-submetabox-item-column-order-number"><?= esc_html__('Order Number', 'wp-user-avatar') ?></th>
                <th class="ppress-submetabox-item-column-date"><?= esc_html__('Date', 'wp-user-avatar') ?></th>
                <th class="ppress-submetabox-item-column-status"><?= esc_html__('Status', 'wp-user-avatar') ?></th>
                <th class="ppress-submetabox-item-column-order-total"><?= esc_html__('Total', 'wp-user-avatar') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($sub_orders as $order) : ?>
                <tr class="ppress-submetabox-item-row">
                    <td class="ppress-submetabox-item-column-order-number">
                        <a target="_blank" href="<?= esc_url(OrderWPListTable::view_edit_order_url($order->id)) ?>">#<?= $order->id ?></a>
                    </td>
                    <td class="ppress-submetabox-item-column-date"><?= ppress_format_date_time($order->date_created); ?></td>
                    <td class="ppress-submetabox-item-column-status"><?= OrderWPListTable::get_order_status_badge($order->status); ?></td>
                    <td class="ppress-submetabox-item-column-order-total"><?= ppress_display_amount($order->get_total(), $order->currency); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>

    </div>
</div>
