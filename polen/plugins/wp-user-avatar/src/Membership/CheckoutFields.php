<?php

namespace ProfilePress\Core\Membership;

use ProfilePress\Core\Classes\ExtensionManager as EM;
use ProfilePress\Core\Classes\FormRepository;
use ProfilePress\Core\Classes\PROFILEPRESS_sql as PROFILEPRESS_sql;
use ProfilePress\Core\Membership\Controllers\CheckoutSessionData;
use ProfilePress\Core\ShortcodeParser\Builder\FieldsShortcodeCallback;

class CheckoutFields
{
    const DB_OPTION_NAME = 'ppress_checkout_fields';

    const ACCOUNT_EMAIL_ADDRESS = 'ppmb_email';
    const ACCOUNT_CONFIRM_EMAIL_ADDRESS = 'ppmb_email2';
    const ACCOUNT_USERNAME = 'ppmb_username';
    const ACCOUNT_PASSWORD = 'ppmb_password';
    const ACCOUNT_CONFIRM_PASSWORD = 'ppmb_password2';
    const ACCOUNT_WEBSITE = 'ppmb_website';
    const ACCOUNT_NICKNAME = 'ppmb_nickname';
    const ACCOUNT_DISPLAY_NAME = 'ppmb_display_name';
    const ACCOUNT_FIRST_NAME = 'ppmb_first_name';
    const ACCOUNT_LAST_NAME = 'ppmb_last_name';
    const ACCOUNT_BIO = 'ppmb_bio';

    /** we using ppress because the constants is the usermeta key/id */
    const BILLING_ADDRESS = 'ppress_billing_address';
    const BILLING_CITY = 'ppress_billing_city';
    const BILLING_COUNTRY = 'ppress_billing_country';
    const BILLING_STATE = 'ppress_billing_state';
    const BILLING_POST_CODE = 'ppress_billing_postcode';
    const BILLING_PHONE_NUMBER = 'ppress_billing_phone';
    const VAT_NUMBER = 'ppress_vat_number';

    public static function logged_in_hidden_fields()
    {
        return apply_filters('ppress_checkout_logged_in_hidden_fields', [
            CheckoutFields::ACCOUNT_EMAIL_ADDRESS,
            CheckoutFields::ACCOUNT_CONFIRM_EMAIL_ADDRESS,
            CheckoutFields::ACCOUNT_PASSWORD,
            CheckoutFields::ACCOUNT_CONFIRM_PASSWORD,
            CheckoutFields::ACCOUNT_USERNAME
        ]);
    }

    public static function standard_account_info_fields()
    {
        return [
            self::ACCOUNT_EMAIL_ADDRESS         => [
                'label'          => esc_html__('Email Address', 'wp-user-avatar'),
                'required'       => 'true',
                'field_type'     => 'email',
                'logged_in_hide' => 'false',
                'deletable'      => 'false'
            ],
            self::ACCOUNT_CONFIRM_EMAIL_ADDRESS => [
                'label'          => esc_html__('Confirm Email Address', 'wp-user-avatar'),
                'required'       => 'true',
                'field_type'     => 'email',
                'logged_in_hide' => 'true',
                'deletable'      => 'true'
            ],
            self::ACCOUNT_FIRST_NAME            => [
                'label'          => esc_html__('First Name', 'wp-user-avatar'),
                'required'       => 'true',
                'field_type'     => 'text',
                'logged_in_hide' => 'false',
                'deletable'      => 'false'
            ],
            self::ACCOUNT_LAST_NAME             => [
                'label'          => esc_html__('Last Name', 'wp-user-avatar'),
                'required'       => 'false',
                'field_type'     => 'text',
                'logged_in_hide' => 'false',
                'deletable'      => 'true'
            ],
            self::ACCOUNT_USERNAME              => [
                'label'          => esc_html__('Username', 'wp-user-avatar'),
                'required'       => 'true',
                'field_type'     => 'text',
                'logged_in_hide' => 'true',
                'deletable'      => 'true'
            ],
            self::ACCOUNT_PASSWORD              => [
                'label'          => esc_html__('Password', 'wp-user-avatar'),
                'required'       => 'true',
                'field_type'     => 'password',
                'logged_in_hide' => 'true',
                'deletable'      => 'false'
            ],
            self::ACCOUNT_CONFIRM_PASSWORD      => [
                'label'          => esc_html__('Confirm Password', 'wp-user-avatar'),
                'required'       => 'true',
                'field_type'     => 'password',
                'logged_in_hide' => 'true',
                'deletable'      => 'true'
            ],
            self::ACCOUNT_WEBSITE               => [
                'label'          => esc_html__('Website', 'wp-user-avatar'),
                'required'       => 'false',
                'field_type'     => 'text',
                'logged_in_hide' => 'true',
                'deletable'      => 'true'
            ],
            self::ACCOUNT_NICKNAME              => [
                'label'          => esc_html__('Nickname', 'wp-user-avatar'),
                'required'       => 'false',
                'field_type'     => 'text',
                'logged_in_hide' => 'true',
                'deletable'      => 'true'
            ],
            self::ACCOUNT_DISPLAY_NAME          => [
                'label'          => esc_html__('Display Name', 'wp-user-avatar'),
                'required'       => 'false',
                'field_type'     => 'text',
                'logged_in_hide' => 'true',
                'deletable'      => 'true'
            ],
            self::ACCOUNT_BIO                   => [
                'label'          => esc_html__('Biographical Info', 'wp-user-avatar'),
                'required'       => 'false',
                'field_type'     => 'textarea',
                'logged_in_hide' => 'true',
                'deletable'      => 'true'
            ],
        ];
    }

    public static function standard_billing_fields()
    {
        return [
            self::BILLING_ADDRESS      => [
                'label'          => esc_html__('Street Address', 'wp-user-avatar'),
                'required'       => 'true',
                'field_type'     => 'text',
                'logged_in_hide' => 'false',
                'deletable'      => 'true',
                'width'          => 'full'
            ],
            self::BILLING_CITY         => [
                'label'          => esc_html__('City', 'wp-user-avatar'),
                'required'       => 'true',
                'field_type'     => 'text',
                'logged_in_hide' => 'false',
                'deletable'      => 'false',
                'width'          => 'half'
            ],
            self::BILLING_COUNTRY      => [
                'label'          => esc_html__('Country', 'wp-user-avatar'),
                'required'       => 'true',
                'field_type'     => 'country',
                'logged_in_hide' => 'false',
                'deletable'      => 'false',
                'width'          => 'half'
            ],
            self::BILLING_STATE        => [
                'label'          => esc_html__('State / Province', 'wp-user-avatar'),
                'required'       => 'true',
                'field_type'     => 'text',
                'logged_in_hide' => 'false',
                'deletable'      => 'true',
                'width'          => 'one-third'
            ],
            self::BILLING_POST_CODE    => [
                'label'          => esc_html__('Zip / Postal Code', 'wp-user-avatar'),
                'required'       => 'true',
                'field_type'     => 'text',
                'logged_in_hide' => 'false',
                'deletable'      => 'false',
                'width'          => 'one-third'
            ],
            self::BILLING_PHONE_NUMBER => [
                'label'          => esc_html__('Phone', 'wp-user-avatar'),
                'required'       => 'true',
                'field_type'     => 'tel',
                'logged_in_hide' => 'false',
                'deletable'      => 'true',
                'width'          => 'one-third'
            ]
        ];
    }

    public static function standard_custom_fields()
    {
        $fields = [];

        if (EM::is_premium()) {

            $db_custom_fields = PROFILEPRESS_sql::get_profile_custom_fields();
            $db_contact_infos = PROFILEPRESS_sql::get_contact_info_fields();

            if ( ! empty($db_contact_infos)) {
                foreach ($db_contact_infos as $key => $value) {
                    $fields[$key] = [
                        'label'          => $value,
                        'required'       => 'false',
                        'field_type'     => 'text',
                        'logged_in_hide' => 'true',
                        'deletable'      => 'true'
                    ];
                }
            }

            if ( ! empty($db_custom_fields)) {

                foreach ($db_custom_fields as $db_custom_field) {

                    if (in_array($db_custom_field['type'], ['hidden'])) continue;

                    $field_key  = $db_custom_field['field_key'];
                    $field_type = sanitize_text_field($db_custom_field['type']);

                    $fields[$field_key] = [
                        'label'          => ppress_woocommerce_field_transform($field_key, htmlspecialchars_decode($db_custom_field['label_name'])),
                        'required'       => 'false',
                        'field_type'     => $field_type,
                        'logged_in_hide' => 'true',
                        'deletable'      => 'true'
                    ];

                }
            }
        }

        return $fields;
    }

    public static function account_info_fields()
    {
        static $data = false;

        if (false === $data) {

            $standard_fields = self::standard_account_info_fields();

            $custom_fields = self::standard_custom_fields();

            $fields = [
                self::ACCOUNT_EMAIL_ADDRESS => [
                    'label'          => $standard_fields[self::ACCOUNT_EMAIL_ADDRESS]['label'],
                    'required'       => $standard_fields[self::ACCOUNT_EMAIL_ADDRESS]['required'],
                    'field_type'     => $standard_fields[self::ACCOUNT_EMAIL_ADDRESS]['field_type'],
                    'logged_in_hide' => $standard_fields[self::ACCOUNT_EMAIL_ADDRESS]['logged_in_hide'],
                    'deletable'      => $standard_fields[self::ACCOUNT_EMAIL_ADDRESS]['deletable'],
                    'width'          => 'full'
                ],
                self::ACCOUNT_USERNAME      => [
                    'label'          => $standard_fields[self::ACCOUNT_USERNAME]['label'],
                    'required'       => $standard_fields[self::ACCOUNT_USERNAME]['required'],
                    'field_type'     => $standard_fields[self::ACCOUNT_USERNAME]['field_type'],
                    'logged_in_hide' => $standard_fields[self::ACCOUNT_USERNAME]['logged_in_hide'],
                    'deletable'      => $standard_fields[self::ACCOUNT_USERNAME]['deletable'],
                    'width'          => 'half'
                ],
                self::ACCOUNT_PASSWORD      => [
                    'label'          => $standard_fields[self::ACCOUNT_PASSWORD]['label'],
                    'required'       => $standard_fields[self::ACCOUNT_PASSWORD]['required'],
                    'field_type'     => $standard_fields[self::ACCOUNT_PASSWORD]['field_type'],
                    'logged_in_hide' => $standard_fields[self::ACCOUNT_PASSWORD]['logged_in_hide'],
                    'deletable'      => $standard_fields[self::ACCOUNT_PASSWORD]['deletable'],
                    'width'          => 'half'
                ],
                self::ACCOUNT_FIRST_NAME    => [
                    'label'          => $standard_fields[self::ACCOUNT_FIRST_NAME]['label'],
                    'required'       => $standard_fields[self::ACCOUNT_FIRST_NAME]['required'],
                    'field_type'     => $standard_fields[self::ACCOUNT_FIRST_NAME]['field_type'],
                    'logged_in_hide' => $standard_fields[self::ACCOUNT_FIRST_NAME]['logged_in_hide'],
                    'deletable'      => $standard_fields[self::ACCOUNT_FIRST_NAME]['deletable'],
                    'width'          => 'half'
                ],
                self::ACCOUNT_LAST_NAME     => [
                    'label'          => $standard_fields[self::ACCOUNT_LAST_NAME]['label'],
                    'required'       => $standard_fields[self::ACCOUNT_LAST_NAME]['required'],
                    'field_type'     => $standard_fields[self::ACCOUNT_LAST_NAME]['field_type'],
                    'logged_in_hide' => $standard_fields[self::ACCOUNT_LAST_NAME]['logged_in_hide'],
                    'deletable'      => $standard_fields[self::ACCOUNT_LAST_NAME]['deletable'],
                    'width'          => 'half'
                ],
            ];

            $collection = ppress_var(get_option(self::DB_OPTION_NAME, []), 'accountInfo', []);

            if ( ! empty($collection)) {

                /** @see https://stackoverflow.com/a/13036310/2648410 */
                array_walk($collection, function (&$v, $k) use ($standard_fields, $custom_fields) {
                    $v['label']          = stripslashes(wp_kses_post($v['label'] ?? ($standard_fields[$k]['label'] ?? ppress_var($custom_fields[$k], 'label', ''))));
                    $v['width']          = sanitize_text_field($v['width'] ?? ($standard_fields[$k]['width'] ?? ppress_var($custom_fields[$k], 'width', 'full')));
                    $v['required']       = sanitize_text_field($v['required'] ?? ($standard_fields[$k]['required'] ?? ppress_var($custom_fields[$k], 'required', 'false')));
                    $v['logged_in_hide'] = sanitize_text_field($v['logged_in_hide'] ?? ($standard_fields[$k]['logged_in_hide'] ?? ppress_var($custom_fields[$k], 'logged_in_hide', 'true')));
                    $v['field_type']     = $standard_fields[$k]['field_type'] ?? ppress_var($custom_fields[$k], 'field_type', 'text');
                    $v['deletable']      = $standard_fields[$k]['deletable'] ?? ppress_var($custom_fields[$k], 'deletable', 'true');
                });

                $fields = $collection;
            }

            $data = apply_filters('ppress_checkout_account_info_fields', $fields);
        }

        return $data;
    }

    public static function billing_fields()
    {
        static $data = false;

        if (false === $data) {

            $billing_fields = self::standard_billing_fields();

            $collection = ppress_var(get_option(self::DB_OPTION_NAME, []), 'billing', []);

            if ( ! empty($collection)) {

                array_walk($collection, function (&$v, $k) use ($billing_fields) {
                    $v['label']          = wp_kses_post($v['label'] ?? ($billing_fields[$k]['label'] ?? ''));
                    $v['width']          = sanitize_text_field($v['width'] ?? ($billing_fields[$k]['width'] ?? 'full'));
                    $v['required']       = sanitize_text_field($v['required'] ?? ($billing_fields[$k]['required'] ?? 'false'));
                    $v['logged_in_hide'] = sanitize_text_field($v['logged_in_hide'] ?? ($billing_fields[$k]['logged_in_hide'] ?? 'true'));
                    $v['field_type']     = $billing_fields[$k]['field_type'] ?? 'text';
                    $v['deletable']      = $billing_fields[$k]['deletable'] ?? 'true';
                });

                $billing_fields = $collection;
            }

            $data = apply_filters('ppress_checkout_billing_fields', $billing_fields);
        }

        return $data;
    }

    public static function get_field_id($field_id, $payment_method = '')
    {
        if (empty($payment_method)) return $field_id;

        return $payment_method . '_' . $field_id;
    }

    public static function render_field($field_id, $is_required = false, $extra_attr = [], $payment_method = '')
    {
        $html_field = '';

        $standard_billing_fields = self::standard_billing_fields();
        $custom_fields           = self::standard_custom_fields();

        $field_type = $standard_billing_fields[$field_id]['field_type'] ?? ($custom_fields[$field_id]['field_type'] ?? 'text');
        $form_type  = is_user_logged_in() ? FormRepository::EDIT_PROFILE_TYPE : FormRepository::REGISTRATION_TYPE;

        $instance = new FieldsShortcodeCallback($form_type, 'checkout_field', 'ppmb');

        $standard_billing_fields_keys = array_keys($standard_billing_fields);
        // add vat number field so it is recognized
        $standard_billing_fields_keys[] = self::VAT_NUMBER;

        if (in_array($field_id, $standard_billing_fields_keys) || in_array($field_id, array_keys($custom_fields))) {

            $args = [
                'key'      => $payment_method . '_' . $field_id,
                'type'     => $field_type,
                'id'       => self::get_field_id($field_id, $payment_method),
                'class'    => 'ppress-checkout-field__input ' . $field_id,
                'required' => $is_required
            ];

            $args['value'] = get_user_meta(get_current_user_id(), $field_id, true);

            $plan_id = isset($_GET['plan']) ? $_GET['plan'] : 0;

            $session_country = CheckoutSessionData::get_tax_country(absint($plan_id));

            $session_state = CheckoutSessionData::get_tax_state(absint($plan_id));

            if ($field_id == self::BILLING_COUNTRY && ! empty($session_country)) {
                $args['value'] = $session_country;
            }

            if ($field_id == self::BILLING_STATE) {

                $state_country = ! empty($_POST['country']) ? $_POST['country'] : $session_country;

                $states = ! empty($state_country) ? ppress_array_of_world_states(sanitize_text_field($state_country)) : [];

                if ( ! empty($states)) {
                    $args['type']              = 'select';
                    $args['key_value_options'] = ['' => '&mdash;&mdash;&mdash;'] + $states;
                }

                if ( ! empty($session_state)) {
                    $args['value'] = $session_state;
                }
            }

            $html_field = $instance->custom_profile_field($args + $extra_attr);

        } else {

            $args = [
                        'id'          => self::get_field_id($field_id, $payment_method),
                        'placeholder' => '',
                        'class'       => 'ppress-checkout-field__input ' . $field_id,
                        'required'    => $is_required
                    ] + $extra_attr;

            switch ($field_id) {
                case self::ACCOUNT_USERNAME:
                    $html_field = $instance->username($args);
                    break;
                case self::ACCOUNT_EMAIL_ADDRESS:
                    $html_field = $instance->email($args);
                    break;
                case self::ACCOUNT_CONFIRM_EMAIL_ADDRESS:
                    $html_field = $instance->confirm_email($args);
                    break;
                case self::ACCOUNT_PASSWORD:
                    $html_field = $instance->password($args);
                    break;
                case self::ACCOUNT_CONFIRM_PASSWORD:
                    $html_field = $instance->confirm_password($args);
                    break;
                case self::ACCOUNT_FIRST_NAME:
                    $html_field = $instance->first_name($args);
                    break;
                case self::ACCOUNT_LAST_NAME:
                    $html_field = $instance->last_name($args);
                    break;
                case self::ACCOUNT_DISPLAY_NAME:
                    $html_field = $instance->display_name($args);
                    break;
                case self::ACCOUNT_NICKNAME:
                    $html_field = $instance->nickname($args);
                    break;
                case self::ACCOUNT_WEBSITE:
                    $html_field = $instance->website($args);
                    break;
                case self::ACCOUNT_BIO:
                    $html_field = $instance->bio($args);
                    break;
            }
        }

        return apply_filters('ppress_checkout_field_render', $html_field, $field_id, $field_type);
    }
}
