<?php

use ProfilePress\Core\Admin\SettingsPages\Membership\CustomersPage\CustomerWPListTable;
use ProfilePress\Core\Membership\Models\Customer\CustomerEntity;

/** @var CustomerEntity $customer_data */
?>
<div class="submitbox" id="submitpost">

    <div id="major-publishing-actions">

        <div id="delete-action">
            <?php if (ppressGET_var('ppress_customer_action') == 'view') : ?>
                <a class="submitdelete deletion pp-confirm-delete" href="<?= CustomerWPListTable::delete_customer_url($customer_data->id); ?>">
                    <?= esc_html__('Delete', 'wp-user-avatar') ?>
                </a>
            <?php endif; ?>
        </div>

        <div id="publishing-action">
            <input type="submit" name="ppress_save_customer" class="button button-primary button-large" value="<?= esc_html__('Save Customer', 'wp-user-avatar') ?>">
        </div>
        <div class="clear"></div>
    </div>

</div>