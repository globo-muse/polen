<?php

use ProfilePress\Core\Membership\CheckoutFields as CF;
use ProfilePress\Core\Membership\Models\Customer\CustomerEntity;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Services\OrderService;

/** @global CustomerEntity $customer_data */

$customer_id = $customer_data->id;
$user_id     = $customer_data->user_id;

$billing_fields = CF::billing_fields();
$sb             = CF::standard_billing_fields();

echo '<div class="ppress-membership-customer-details">';

?>
    <div class="ppress-metabox-data-column-container">

        <div class="ppress-metabox-data-column" style="width:65%">
            <div class="ppress-customer-info">

                <div class="ppress-avatar-wrap">
                    <?php echo get_avatar($customer_data->user_id, 150); ?>

                    <?php if ($customer_data->user_exists()) : ?>
                        <div class="ppres-customer-edit-link">
                            <a href="<?= get_edit_user_link($customer_data->user_id) ?>" class="button-secondary">
                                <?php esc_html_e('Edit Profile', 'wp-user-avatar'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="ppress-customer-main-wrapper">

                    <div class="ppress-customer-main-header">

                        <span class="customer-name"><?php echo $customer_data->get_name() ?></span>

                        <span class="customer-email"><?php echo $customer_data->get_email() ?></span>

                        <span class="customer-since">
						<?php printf('Customer since %s', $customer_data->get_date_created()) ?>
                    </span>

                        <?php if ( ! empty($customer_data->get_last_login())) : ?>
                            <span class="last-login">
						    <?php printf('Last login on %s', $customer_data->get_last_login()) ?>
                        </span>
                        <?php endif; ?>

                        <p class="mb-form-field">

                            <label for="customer_wp_user">
                                <?php esc_html_e('WordPress User', 'wp-user-avatar') ?>:
                                <?php if ($customer_data->user_exists()) : ?>
                                    <a href="<?= get_edit_user_link($customer_data->user_id); ?>"><?= esc_html__('View profile &rarr;', 'wp-user-avatar') ?></a>
                                <?php endif; ?>
                            </label>

                            <select id="customer_wp_user" name="customer_wp_user" class="ppress-select2-field customer_wp_user">
                                <?php if ($customer_data->user_exists()) : ?>
                                    <option value="<?= $customer_data->user_id ?>" selected>
                                        <?php printf(
                                            esc_html__('%1$s (%2$s)', 'wp-user-avatar'),
                                            $customer_data->get_wp_user()->user_login,
                                            $customer_data->get_wp_user()->user_email
                                        ); ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                        </p>
                    </div>
                </div>

            </div>
        </div>

        <div class="ppress-metabox-data-column" style="width:35%">
            <h3>
                <?php esc_html_e('Billing Address', 'wp-user-avatar'); ?>
                <a href="#" class="edit_address"><?php esc_html_e('Edit', 'wp-user-avatar'); ?></a>
            </h3>
            <div class="ppress-billing-details">
                <?php if ( ! empty($billing_fields)) {

                    $billing_country = sanitize_text_field(get_user_meta($user_id, CF::BILLING_COUNTRY, true));

                    foreach ($billing_fields as $field_id => $field) {

                        $detail = wp_kses_post(get_user_meta($user_id, $field_id, true));

                        if ($field_id == CF::BILLING_COUNTRY) {
                            $detail = ppress_array_of_world_countries($detail);
                            $detail = is_array($detail) ? '' : $detail;
                        }

                        if ($field_id == CF::BILLING_STATE) {
                            $state  = ! empty($billing_country) ? ppress_array_of_world_states($billing_country) : [];
                            $detail = ppress_var($state, $detail, $detail, true);
                        }

                        echo '<p>';
                        printf('<strong>%s</strong>: %s', esc_html($field['label']), $detail);
                        echo '</p>';
                    }
                } else {
                    echo '<p class="none_set">' . __('No billing address set.', 'wp-user-avatar') . '</p>';
                }
                ?>
            </div>

            <div class="ppress_edit_address_wrap">
                <?php

                if ( ! empty($billing_fields)) {
                    $found = [];
                    if (in_array(CF::BILLING_ADDRESS, array_keys($billing_fields))) {
                        $found[] = CF::BILLING_ADDRESS;
                        echo '<p class="ppress-metabox-form-field">';
                        printf('<label for="%s">%s</label>', CF::BILLING_ADDRESS, $sb[CF::BILLING_ADDRESS]['label']);
                        echo do_shortcode(
                            sprintf(
                                '[edit-profile-cpf id="%1$s" key="%1$s" value="%2$s"]',
                                CF::BILLING_ADDRESS,
                                get_user_meta($user_id, CF::BILLING_ADDRESS, true)
                            ),
                            true
                        );
                        echo '</p>';
                    }

                    if (in_array(CF::BILLING_CITY, array_keys($billing_fields))) {
                        $found[] = CF::BILLING_CITY;
                        echo '<p class="ppress-metabox-form-field">';
                        printf('<label for="%s">%s</label>', CF::BILLING_CITY, $sb[CF::BILLING_CITY]['label']);
                        echo do_shortcode(
                            sprintf(
                                '[edit-profile-cpf id="%1$s" key="%1$s" value="%2$s"]',
                                CF::BILLING_CITY,
                                get_user_meta($user_id, CF::BILLING_CITY, true)
                            ),
                            true
                        );
                        echo '</p>';
                    }

                    if (in_array(CF::BILLING_COUNTRY, array_keys($billing_fields))) {
                        $found[] = CF::BILLING_COUNTRY;
                        echo '<p class="ppress-metabox-form-field">';
                        printf('<label for="%s">%s</label>', CF::BILLING_COUNTRY, $sb[CF::BILLING_COUNTRY]['label']);
                        echo do_shortcode(
                            sprintf(
                                '[edit-profile-cpf id="%1$s" key="%1$s" value="%2$s"]',
                                CF::BILLING_COUNTRY,
                                get_user_meta($user_id, CF::BILLING_COUNTRY, true)
                            ),
                            true
                        );
                        echo '</p>';
                    }

                    if (in_array(CF::BILLING_STATE, array_keys($billing_fields))) {
                        $found[] = CF::BILLING_STATE;
                        echo '<p class="ppress-metabox-form-field">';
                        printf('<label for="%s">%s</label>', CF::BILLING_STATE, $sb[CF::BILLING_STATE]['label']);
                        echo do_shortcode(
                            sprintf(
                                '[edit-profile-cpf id="%1$s" key="%1$s" value="%2$s"]',
                                CF::BILLING_STATE,
                                get_user_meta($user_id, CF::BILLING_STATE, true)
                            ),
                            true
                        );
                        echo '</p>';
                    }

                    if (in_array(CF::BILLING_POST_CODE, array_keys($billing_fields))) {
                        $found[] = CF::BILLING_POST_CODE;
                        echo '<p class="ppress-metabox-form-field">';
                        printf('<label for="%s">%s</label>', CF::BILLING_POST_CODE, $sb[CF::BILLING_POST_CODE]['label']);
                        echo do_shortcode(
                            sprintf(
                                '[edit-profile-cpf id="%1$s" key="%1$s" value="%2$s"]',
                                CF::BILLING_POST_CODE,
                                get_user_meta($user_id, CF::BILLING_POST_CODE, true)
                            ),
                            true
                        );
                        echo '</p>';
                    }

                    if (in_array(CF::BILLING_PHONE_NUMBER, array_keys($billing_fields))) {
                        $found[] = CF::BILLING_PHONE_NUMBER;
                        echo '<p class="ppress-metabox-form-field">';
                        printf('<label for="%s">%s</label>', CF::BILLING_PHONE_NUMBER, $sb[CF::BILLING_PHONE_NUMBER]['label']);
                        echo do_shortcode(
                            sprintf(
                                '[edit-profile-cpf id="%1$s" key="%1$s" value="%2$s"]',
                                CF::BILLING_PHONE_NUMBER,
                                get_user_meta($user_id, CF::BILLING_PHONE_NUMBER, true)
                            ),
                            true
                        );
                        echo '</p>';
                    }

                    foreach ($billing_fields as $field_id => $field) {
                        if ( ! in_array($field_id, $found)) {
                            $found[] = $field_id;
                            echo '<p class="ppress-metabox-form-field">';
                            printf('<label for="%s">%s</label>', $field_id, $field['label']);
                            echo do_shortcode(
                                sprintf(
                                    '[edit-profile-cpf id="%1$s" key="%1$s" value="%2$s"]',
                                    $field_id,
                                    get_user_meta($user_id, $field_id, true)
                                ),
                                true
                            );
                            echo '</p>';
                        }
                    }
                }
                ?>
            </div>

            <?php do_action('ppress_admin_customer_data_after_billing_address', $customer_id, $customer_data); ?>
        </div>
    </div>

    <div class="ppress-customer-stats-wrapper">
        <ul>
            <li>
                <a href="<?= OrderService::init()->get_customer_orders_url($customer_id, OrderStatus::COMPLETED) ?>">
                    <span class="dashicons dashicons-cart"></span>
                    <span class="ppress_purchase_count"><?= $customer_data->purchase_count ?></span> <?php esc_html_e('Completed Sales', 'wp-user-avatar') ?>
                </a>
            </li>
            <li>
                <span class="dashicons dashicons-chart-area"></span>
                <span class="ppress_total_spend"><?= ppress_display_amount($customer_data->total_spend) ?></span> <?php esc_html_e('Total Spend', 'wp-user-avatar') ?>
            </li>
        </ul>
    </div>
<?php

echo '</div>';