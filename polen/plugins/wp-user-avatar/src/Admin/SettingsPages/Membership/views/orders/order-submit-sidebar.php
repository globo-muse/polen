<?php

use ProfilePress\Core\Admin\SettingsPages\Membership\OrdersPage\OrderWPListTable;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;

/** @var int $order_id */
/** @var OrderEntity $order_data */
?>
<div class="submitbox" id="submitpost">

    <div id="major-publishing-actions">

        <div id="delete-action">
            <?php if (ppressGET_var('ppress_order_action') == 'edit') : ?>
                <a class="submitdelete deletion pp-confirm-delete" href="<?= OrderWPListTable::delete_order_url($order_id); ?>">
                    <?= esc_html__('Delete', 'wp-user-avatar') ?>
                </a>
            <?php endif; ?>
        </div>

        <div id="publishing-action">
            <input type="submit" name="ppress_save_order" class="button button-primary button-large" value="<?= esc_html__('Save Order', 'wp-user-avatar') ?>">
        </div>
        <div class="clear"></div>
    </div>

</div>