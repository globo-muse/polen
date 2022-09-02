<?php

namespace ProfilePress\Core\ShortcodeParser;

use ProfilePress\Core\Membership\Controllers\CheckoutSessionData;
use ProfilePress\Core\Membership\Models\Coupon\CouponFactory;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;

class MembershipShortcodes
{
    public function __construct()
    {
        add_shortcode('profilepress-checkout', [$this, 'checkout_page']);
        add_shortcode('profilepress-receipt', [$this, 'success_page']);

        add_filter('the_content', [$this, 'filter_success_page_content'], 99999);
    }

    function filter_success_page_content($content)
    {
        if (isset($_GET['order_key'], $_GET['payment_method']) && ppress_is_success_page()) {
            $order = OrderFactory::fromOrderKey(sanitize_key($_GET['order_key']));
            if ($order->exists() && $order->is_pending()) {
                ob_start();
                ppress_render_view('order-processing', [
                    'order_success_page' => ppress_get_success_url($order->order_key)
                ]);

                $content = ob_get_clean();
            }
        }

        return $content;
    }

    public function checkout_page()
    {
        ob_start();

        echo '<div class="ppress-checkout__form">';

        if ( ! isset($_GET['plan']) || ! is_numeric($_GET['plan'])) {

            do_action('ppress_membership_checkout_empty_cart');

            echo '<p>';
            printf(
                __('Your cart is currently empty. Click <a href="%s">here</a> to get started.', 'wp-user-avatar'),
                /** @todo add setting to set url to redirect to when cart is empty */
                apply_filters('ppress_membership_checkout_empty_cart_url', home_url())
            );
            echo '</p>';

        } else {

            $planObj = ppress_get_plan(absint($_GET['plan']));

            if (
                is_user_logged_in() &&
                CustomerFactory::fromUserId(get_current_user_id())->has_active_subscription($planObj->id)) {
                echo '<p>';
                printf(
                    esc_html__('It looks like you’re already subscribed to this plan. Please go to %syour account%s to manage your subscription.', 'wp-user-avatar'),
                    '<a href="' . ppress_my_account_url() . '">', '</a>'
                );
                echo '</p>';
            } else {

                if ($planObj->is_active()) {

                    add_filter('ppress_logout_url_enable_redirect_get_query', '__return_true');

                    if ( ! empty($_GET['coupon'])) {

                        $coupon = CouponFactory::fromCode(sanitize_text_field($_GET['coupon']));

                        if ($coupon->exists()) {

                            ppress_session()->set(CheckoutSessionData::COUPON_CODE, [
                                'plan_id'     => $planObj->id,
                                'coupon_code' => $coupon->code,
                            ]);
                        }
                    }

                    ppress_render_view('checkout/form-checkout', [
                        'planObj' => $planObj
                    ]);
                } else {
                    do_action('ppress_membership_checkout_invalid_plan');
                    echo '<p>' . esc_html__('Invalid subscription plan.', 'wp-user-avatar') . '</p>';
                }
            }
        }
        echo '</div>';

        return ob_get_clean();
    }

    public function success_page()
    {
        ob_start();
        require apply_filters('ppress_order_receipt_template', dirname(__FILE__) . '/MyAccount/view-order.tmpl.php');

        return ob_get_clean();
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}
