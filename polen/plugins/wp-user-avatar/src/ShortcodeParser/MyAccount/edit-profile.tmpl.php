<?php

use ProfilePress\Core\Admin\ProfileCustomFields;
use ProfilePress\Core\Classes\EditUserProfile;
use ProfilePress\Core\Classes\ExtensionManager;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Core\Classes\UserAvatar;
use ProfilePress\Core\Membership\CheckoutFields;

if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$current_user_id = get_current_user_id();

$contact_infos = [];
$custom_fields = [];

if (ExtensionManager::is_enabled(ExtensionManager::CUSTOM_FIELDS)) {

    $contact_infos = PROFILEPRESS_sql::get_contact_info_fields();

    $custom_fields = PROFILEPRESS_sql::get_profile_custom_fields();
}

$success_message = EditUserProfile::get_success_message();

$custom_edit_profile = ppress_settings_by_key('myac_account_details_form', 'default', true);

$sub_menus = apply_filters('ppress_my_account_settings_sub_menus', ['general' => esc_html__('General', 'wp-user-avatar')]);
?>
    <div class="profilepress-myaccount-edit-profile">

        <h2><?= esc_html__('Account Settings', 'wp-user-avatar') ?></h2>

        <?php if (is_array($sub_menus) && count($sub_menus) > 1) : ?>
            <div class="profilepress-myaccount-submenus-wrap">

                <?php foreach ($sub_menus as $menu_id => $sub_menu) : ?>

                    <?php $is_active = ( ! isset($_GET['epview']) && $menu_id == 'general') || (isset($_GET['epview']) && $_GET['epview'] == $menu_id) ? ' ppsubmenu-active' : ''; ?>

                    <div class="profilepress-myaccount-submenu-wrap">
                        <a href="<?= esc_url(remove_query_arg('edit', add_query_arg('epview', $menu_id))) ?>" class="profilepress-myaccount-submenu-item<?= $is_active ?>">
                            <?= $sub_menu ?>
                        </a>
                    </div>

                <?php endforeach; ?>

            </div>
        <?php endif; ?>

        <?php if (isset($_GET['edit']) && $_GET['edit'] == 'true') : ?>
            <?= $success_message ?>
        <?php endif; ?>

        <?php if ( ! empty($this->edit_profile_form_error)) : ?>

            <?php if (strpos($this->edit_profile_form_error, 'profilepress-edit-profile-status') !== false) : ?>
                <?= $this->edit_profile_form_error ?>
            <?php else : ?>
                <div class="profilepress-edit-profile-status">
                    <?= $this->edit_profile_form_error ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

        <?php

        if ('default' !== $custom_edit_profile) {
            echo do_shortcode(sprintf('[profilepress-edit-profile id="%s"]', absint($custom_edit_profile)), true);
        } elseif ( ! empty($_GET['epview']) && $_GET['epview'] != 'general') {
            do_action('ppress_myaccount_edit_profile_submenu_content', sanitize_text_field($_GET['epview']));
        } else {

            $cover_image_url = ppress_get_cover_image_url();

            ob_start(); ?>
            [pp-edit-profile-form]

            <div class="profilepress-myaccount-form-wrap">

                <div class="profilepress-myaccount-form-field pp-cover-image">
                    <div class="ppmyac-custom-file">
                        <?= '[edit-profile-cover-image id="pp-cover-image" class="ppmyac-custom-file-input"]' ?>
                        <label for="pp-cover-image" class="ppmyac-custom-file-label" data-browse="<?= esc_html__('Browse', 'wp-user-avatar'); ?>">
                            <?= esc_html__('Cover photo (min. width: 1000px)', 'wp-user-avatar') ?>
                        </label>
                    </div>
                </div>

                <div class="profilepress-myaccount-form-field pp-user-cover-image">
                    <div class="profilepress-myaccount-delete-cover-image-wrap">
                        <div class="profilepress-myaccount-cover-image">
                            <div class="profilepress-myaccount-has-cover-image" style="<?= ! $cover_image_url ? 'display:none' : '' ?>">
                                <?= '[pp-user-cover-image]'; ?>
                            </div>
                            <?= sprintf('[pp-remove-cover-image-button label="%s" class="ppmyac-remove-avatar"]', __('Remove', 'wp-user-avatar')); ?>
                            <div class="profilepress-myaccount-cover-image-empty" style="<?= $cover_image_url ? 'display:none' : '' ?>"></div>
                        </div>
                    </div>
                </div>

                <div class="profilepress-myaccount-form-field edit-profile-avatar">
                    <div class="ppmyac-custom-file">
                        <?= '[edit-profile-avatar id="pp-avatar" class="ppmyac-custom-file-input"]' ?>
                        <label for="pp-avatar" class="ppmyac-custom-file-label" data-browse="<?= esc_html__('Browse', 'wp-user-avatar'); ?>">
                            <?= esc_html__('Profile picture', 'wp-user-avatar') ?>
                        </label>
                    </div>
                </div>

                <div class="profilepress-myaccount-form-field delete-avatar">
                    <div class="profilepress-myaccount-delete-avatar-wrap">
                        <div class="profilepress-myaccount-delete-avatar">
                            <?= UserAvatar::get_avatar_img($current_user_id); ?>
                            <?= sprintf('[pp-remove-avatar-button label="%s" class="ppmyac-remove-avatar"]', __('Remove', 'wp-user-avatar')); ?>
                        </div>
                    </div>
                </div>

                <div class="profilepress-myaccount-form-field edit-profile-email">
                    <label for="edit-profile-email"><?= esc_html__('Email address', 'wp-user-avatar') ?></label>
                    <?= '[edit-profile-email id="edit-profile-email" class="profilepress-myaccount-form-control"]'; ?>
                </div>

                <div class="profilepress-myaccount-form-field edit-profile-first-name">
                    <label for="edit-profile-first-name"><?= esc_html__('First name', 'wp-user-avatar') ?></label>
                    <?= '[edit-profile-first-name id="edit-profile-first-name" class="profilepress-myaccount-form-control"]'; ?>
                </div>

                <div class="profilepress-myaccount-form-field edit-profile-last-name">
                    <label for="edit-profile-last-name"><?= esc_html__('Last name', 'wp-user-avatar') ?></label>
                    <?= '[edit-profile-last-name id="edit-profile-last-name" class="profilepress-myaccount-form-control"]'; ?>
                </div>

                <div class="profilepress-myaccount-form-field edit-profile-nickname">
                    <label for="edit-profile-nickname"><?= esc_html__('Nickname', 'wp-user-avatar') ?></label>
                    <?= '[edit-profile-nickname id="edit-profile-nickname" class="profilepress-myaccount-form-control"]'; ?>
                </div>

                <div class="profilepress-myaccount-form-field eup_display_name">
                    <label for="eup_display_name"><?= esc_html__('Display name publicly as', 'wp-user-avatar') ?></label>
                    <?php $this->display_name_select_dropdown(); ?>
                </div>

                <div class="profilepress-myaccount-form-field edit-profile-website">
                    <label for="edit-profile-website"><?= esc_html__('Website', 'wp-user-avatar') ?></label>
                    <?= '[edit-profile-website id="edit-profile-website" class="profilepress-myaccount-form-control"]'; ?>
                </div>

                <div class="profilepress-myaccount-form-field edit-profile-bio">
                    <label for="edit-profile-bio"><?= esc_html__('About yourself', 'wp-user-avatar') ?></label>
                    <?= '[edit-profile-bio id="edit-profile-bio" class="profilepress-myaccount-form-control"]'; ?>
                </div>

                <?php $billing_fields = CheckoutFields::standard_billing_fields() ?>

                <?php if (is_array($contact_infos) && ! empty($contact_infos)) : ?>

                    <?php foreach ($contact_infos as $field_key => $label) : ?>
                        <?php if (in_array($field_key, array_keys($billing_fields))) continue; ?>
                        <div class="profilepress-myaccount-form-field <?= $field_key ?>">
                            <label for="<?= $field_key ?>"><?= $label ?></label>
                            <?= sprintf('[edit-profile-cpf key="%1$s" id="%1$s" type="%2$s" class="profilepress-myaccount-form-control"]', $field_key, 'text'); ?>
                        </div>
                    <?php endforeach; ?>

                <?php endif;

                if (is_array($custom_fields) && ! empty($custom_fields)) : ?>

                    <?php foreach ($custom_fields as $custom_field) :

                        $field_key = $custom_field['field_key'];

                        if (in_array($field_key, array_keys($billing_fields))) continue;

                        // skip woocommerce core billing / shipping fields added to wordpress profile admin page.
                        if (in_array($field_key, ppress_woocommerce_billing_shipping_fields())) continue;
                        ?>
                        <div class="profilepress-myaccount-form-field <?= $field_key ?>">
                            <?php if ($custom_field['type'] !== 'agreeable') : ?>
                                <label for="<?= $field_key ?>"><?= $custom_field['label_name'] ?></label>
                            <?php endif; ?>
                            <?= sprintf('[edit-profile-cpf id="%1$s" key="%1$s" type="%2$s" class="profilepress-myaccount-form-control"]', $field_key, $custom_field['type']) ?>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>

                <div class="profilepress-myaccount-form-field edit-profile-submit">
                    <?= '[edit-profile-submit]'; ?>
                </div>
            </div>

            <input type="hidden" name="ppmyac_form_action" value="updateProfile">

            [/pp-edit-profile-form]

            <?= do_shortcode(ob_get_clean(), true);
        }
        ?>
    </div>
<?php

do_action('ppress_myaccount_edit_profile');