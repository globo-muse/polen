<?php

namespace ProfilePress\Core\Membership\PaymentMethods\Stripe;

use WP_Error;

class Helpers
{
    public static function check_keys_exist()
    {
        $secret_key      = self::get_secret_key();
        $publishable_key = self::get_publishable_key();

        if ( ! empty($secret_key) && ! empty($publishable_key)) {
            return true;
        }

        return false;
    }

    public static function get_publishable_key()
    {
        $mode = ppress_is_test_mode() ? 'test' : 'live';

        return ppress_var(
            get_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, []),
            'stripe_' . $mode . '_publishable_key',
            false,
            true
        );
    }

    public static function get_secret_key()
    {
        $mode = ppress_is_test_mode() ? 'test' : 'live';

        return ppress_var(
            get_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, []),
            'stripe_' . $mode . '_secret_key',
            false,
            true
        );
    }

    public static function get_webhook_secret()
    {
        $mode = ppress_is_test_mode() ? 'test' : 'live';

        return ppress_var(
            get_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, []),
            'stripe_' . $mode . '_webhook_secret',
            false,
            true
        );
    }

    public static function get_account_user_id()
    {
        $mode = ppress_is_test_mode() ? 'test' : 'live';

        return ppress_var(
            get_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, []),
            'stripe_' . $mode . '_user_id',
            false,
            true
        );
    }

    public static function get_connect_url($redirect_url = '')
    {
        if (empty($redirect_url)) {
            $redirect_url = PPRESS_SETTINGS_SETTING_PAGE;
        }

        return add_query_arg(
            [
                'mode'         => ppress_is_test_mode() ? 'test' : 'live',
                'ppnonce'      => wp_create_nonce('ppress_stripe_auth'),
                'redirect_url' => urlencode($redirect_url),
            ],
            'https://auth.profilepress.com/stripe'
        );
    }

    public static function get_disconnect_url($admin_url)
    {
        return add_query_arg(
            array(
                'ppress-stripe-disconnect' => 'true',
                'ppnonce'                  => wp_create_nonce('ppress_stripe_disconnect')
            ),
            $admin_url
        );
    }

    public static function get_connect_button($redirect_url = '')
    {
        $url = self::get_connect_url($redirect_url);

        ob_start();
        ?>
        <a href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr__('Connect with Stripe', 'wp-user-avatar'); ?>" class="ppress-stripe-connect">
            <span><?php esc_html_e('Connect with', 'wp-user-avatar'); ?></span>
            <svg width="49" height="20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M48.4718 10.3338c0-3.41791-1.6696-6.11484-4.8607-6.11484-3.2045 0-5.1434 2.69693-5.1434 6.08814 0 4.0187 2.289 6.048 5.5743 6.048 1.6023 0 2.8141-.3604 3.7296-.8678v-2.6702c-.9155.4539-1.9658.7343-3.2987.7343-1.3061 0-2.464-.4539-2.6121-2.0294h6.5841c0-.1735.0269-.8678.0269-1.1882Zm-6.6514-1.26838c0-1.50868.929-2.13618 1.7773-2.13618.8213 0 1.6965.6275 1.6965 2.13618h-3.4738Zm-8.5499-4.84646c-1.3195 0-2.1678.61415-2.639 1.04139l-.1751-.82777h-2.9621V20l3.3661-.7076.0134-3.7784c.4847.3471 1.1984.8411 2.3832.8411 2.4102 0 4.6048-1.9225 4.6048-6.1548-.0134-3.87186-2.235-5.98134-4.5913-5.98134Zm-.8079 9.19894c-.7944 0-1.2656-.2804-1.5888-.6275l-.0134-4.95328c.35-.38719.8348-.65421 1.6022-.65421 1.2253 0 2.0735 1.36182 2.0735 3.11079 0 1.7891-.8347 3.1242-2.0735 3.1242Zm-9.6001-9.98666 3.3796-.72096V0l-3.3796.70761v2.72363Zm0 1.01469h3.3796V16.1282h-3.3796V4.44593Zm-3.6219.98798-.2154-.98798h-2.9083V16.1282h3.3661V8.21095c.7944-1.02804 2.1408-.84112 2.5582-.69426V4.44593c-.4309-.16022-2.0062-.45394-2.8006.98798Zm-6.7322-3.88518-3.2853.69426-.01346 10.69421c0 1.976 1.49456 3.4313 3.48726 3.4313 1.1041 0 1.912-.2003 2.3563-.4406v-2.7103c-.4309.1736-2.5583.7877-2.5583-1.1882V7.28972h2.5583V4.44593h-2.5583l.0135-2.8972ZM3.40649 7.83712c0-.5207.43086-.72096 1.14447-.72096 1.0233 0 2.31588.30707 3.33917.85447V4.83311c-1.11755-.44059-2.22162-.61415-3.33917-.61415C1.81769 4.21896 0 5.63418 0 7.99733c0 3.68487 5.11647 3.09747 5.11647 4.68627 0 .6141-.53858.8144-1.29258.8144-1.11755 0-2.54477-.4539-3.675782-1.0681v3.1776c1.252192.534 2.517842.761 3.675782.761 2.80059 0 4.72599-1.3752 4.72599-3.765-.01346-3.97867-5.14339-3.27106-5.14339-4.76638Z" fill="#fff"/>
            </svg>
        </a>

        <style>
            .ppress-stripe-connect {
                color: #fff;
                font-size: 15px;
                font-weight: bold;
                text-decoration: none;
                line-height: 1;
                background-color: #635bff;
                border-radius: 3px;
                padding: 10px 20px;
                display: inline-flex;
                align-items: center;
            }

            .ppress-stripe-connect:focus,
            .ppress-stripe-connect:hover {
                color: #fff;
                background-color: #0a2540;
            }

            .ppress-stripe-connect:focus {
                outline: 0;
                box-shadow: inset 0 0 0 1px #fff, 0 0 0 1.5px #0a2540;
            }

            .ppress-stripe-connect svg {
                margin-left: 5px;
            }
        </style>

        <?php
        return ob_get_clean();
    }

    public static function get_account_information($redirect_url)
    {
        $unknown_error = esc_html__('Unable to retrieve account information.', 'wp-user-avatar');

        $connect = sprintf(
            '<div style="margin-top: 8px;">%s</div>',
            Helpers::get_connect_button($redirect_url)
        );

        $access_string = __('You cannot manage this account in Stripe.', 'wp-user-avatar');

        $dev_account_error = sprintf(
                             /* translators: %1$s Opening strong tag, do not translate. %2$s Closing anchor tag, do not translate. */
                                 __(
                                     'You are currently connected to a %1$stemporary%2$s Stripe account, which can only be used for testing purposes.',
                                     'wp-user-avatar'
                                 ),
                                 '<strong>',
                                 '</strong>'
                             ) . ' ' . $access_string;

        $account_id = Helpers::get_account_user_id();

        $secret_key      = Helpers::get_secret_key();
        $publishable_key = Helpers::get_publishable_key();
        $key_errors      = new WP_Error();

        // Publishable Key being used for Secret Key.
        if ('pk_' === substr($secret_key, 0, 3)) {
            $key_errors->add(
                'ppress_stripe_sk_mismatch',
                __(
                    'Invalid Secret Key. Secret Key should begin with <code>sk_</code>.',
                    'wp-user-avatar'
                )
            );
        }

        // Secret Key being used for Publishable Key.
        if ('sk_' === substr($publishable_key, 0, 3)) {
            $key_errors->add(
                'ppress_stripe_pk_mismatch',
                __(
                    'Invalid Publishable Key. Publishable Key should begin with <code>pk_</code>.',
                    'wp-user-avatar'
                )
            );
        }

        if (ppress_is_test_mode()) {
            // Live Mode Publishable Key used in Test Mode Publishable Key.
            if ('pk_live_' === substr($publishable_key, 0, 8)) {
                $key_errors->add(
                    'ppress_stripe_pk_mode_mismatch',
                    __(
                        'Invalid Publishable Key for current mode. Publishable Key should begin with <code>pk_test_</code>.',
                        'wp-user-avatar'
                    )
                );
            }

            // Live Mode Secret Key used in Test Mode Secret Key.
            if ('sk_live_' === substr($secret_key, 0, 8)) {
                $key_errors->add(
                    'ppress_stripe_sk_mode_mismatch',
                    __(
                        'Invalid Secret Key for current mode. Secret Key should begin with <code>sk_test_</code>.',
                        'wp-user-avatar'
                    )
                );
            }
        } else {
            // Test Mode Secret Key used in Live Mode Secret Key.
            if ('pk_test_' === substr($publishable_key, 0, 8)) {
                $key_errors->add(
                    'ppress_stripe_pk_mode_mismatch',
                    __(
                        'Invalid Publishable Key for current mode. Publishable Key should begin with <code>pk_live_</code>.',
                        'wp-user-avatar'
                    )
                );
            }

            // Test Mode Secret Key used in Live Mode Secret Key.
            if ('sk_test_' === substr($secret_key, 0, 8)) {
                $key_errors->add(
                    'ppress_stripe_sk_mode_mismatch',
                    __(
                        'Invalid Secret Key for current mode. Secret Key should begin with <code>sk_live_</code>.',
                        'wp-user-avatar'
                    )
                );
            }
        }

        if ( ! empty($key_errors->errors)) {
            return sprintf(
                '<span style="color: red;">%s</span> %s %s',
                $key_errors->get_error_message(),
                __(
                    'If you have manually modified these values after connecting your account, please reconnect below or update your API keys manually.',
                    'wp-user-avatar'
                ),
                $connect
            );
        }

        // Stripe Connect.
        if ( ! empty($account_id)) {

            try {

                $account = (new APIClass())->get_account($account_id);

                if ( ! ppress_get_payment_method_setting('stripe_connect_account_country')) {
                    ppress_update_payment_method_setting('stripe_connect_account_country', $account['country']);
                }

                $email        = isset($account['email']) ? $account['email'] : '';
                $display_name = isset($account['display_name']) ? $account['display_name'] : '';

                if (empty($display_name)) {
                    if (isset($account['settings']['dashboard']['display_name'])) {
                        $display_name = $account['settings']['dashboard']['display_name'];
                    }
                }

                if (empty($email) && empty($display_name)) {
                    return $dev_account_error;
                }

                if ( ! empty($display_name)) {
                    $display_name = '<strong>' . $display_name . '</strong>';
                }

                if ( ! empty($email) || ! empty($display_name)) {
                    $email_string = ! empty($email) ? $email . ' &mdash; ' : '';
                    $email        = '<br>' . $email_string . esc_html__('Administrator (Owner)', 'wp-user-avatar');
                }

                $message = $display_name . $email;

                $fee_message = (PaymentHelpers::has_application_fee())
                    ? '</p><p>' . sprintf(
                        esc_html__(
                            'Pay as you go pricing: 2%% per-transaction fee + Stripe fees. Remove the 2%% fee by %supgrading to premium%s.',
                            'wp-user-avatar'
                        ),
                        '<a target="_blank" href="https://profilepress.com/pricing/?utm_source=wp_dashboard&utm_medium=upgrade&utm_campaign=stripe-gateway-method">',
                        '</a>'
                    )
                    : '';

                return apply_filters('ppress_stripe_connect_account_message', $message . $fee_message);

            } catch (\Exception $e) {
                return $unknown_error;
            }
        }
    }
}