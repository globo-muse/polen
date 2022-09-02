<?php use ProfilePress\Core\Admin\SettingsPages\Membership\CheckoutFieldsManager; ?>
<div id="ppress-account-info-fields" class="widget-liquid-left">
    <div class="wp-clearfix" id="widgets-right">
        <div class="sidebars-column-3">
            <div class="widgets-holder-wrap">
                <div class="widgets-sortables">
                    <div class="sidebar-name">
                        <h2><?php esc_html_e('Account Information', 'wp-user-avatar') ?></h2>
                    </div>
                    <div class="sidebar-description">
                        <p class="description"><?php esc_html_e('Add, remove and re-order account information fields displayed on the checkout page.', 'wp-user-avatar') ?></p>
                    </div>
                    <div class="ppress-checkout-fields">
                    </div>
                    <div class="ppress-checkout-add-field">
                        <p style="text-align:right">
                            <?php CheckoutFieldsManager::checkout_field_addition_dropdown(); ?>
                            <button class="button"><?php esc_html_e('Add Field', 'wp-user-avatar') ?></button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="ppress-billing-address-fields" class="widget-liquid-left">
    <div class="wp-clearfix" id="widgets-right">
        <div class="sidebars-column-3">
            <div class="widgets-holder-wrap">
                <div class="widgets-sortables">
                    <div class="sidebar-name">
                        <h2><?php esc_html_e('Billing Address', 'wp-user-avatar') ?></h2>
                    </div>
                    <div class="sidebar-description">
                        <p class="description"><?php esc_html_e('Add, remove and re-order billing address fields displayed on the checkout page.', 'wp-user-avatar') ?></p>
                    </div>
                    <div class="ppress-checkout-fields">
                    </div>
                    <div class="ppress-checkout-add-field">
                        <p style="text-align:right">
                            <?php CheckoutFieldsManager::checkout_field_addition_dropdown('billing'); ?>
                            <button class="button"><?php esc_html_e('Add Field', 'wp-user-avatar') ?></button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>