<?php

namespace ProfilePress\Core\Membership\Services;

use ProfilePress\Core\Base;
use ProfilePress\Core\Membership\Models\Coupon\CouponFactory;
use ProfilePress\Core\Membership\Models\Coupon\CouponUnit;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\CartEntity;
use ProfilePress\Core\Membership\Models\Order\OrderEntity as OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Order\OrderType;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionTrialPeriod;
use ProfilePress\Core\Membership\Repositories\OrderRepository;
use ProfilePress\Core\ShortcodeParser\MyAccount\MyAccountTag;

class OrderService
{
    const ORDER_NOTE_META_KEY = 'order_notes';

    /**
     * @param CartEntity $cart_vars
     *
     * @return bool
     */
    public function is_free_checkout($cart_vars)
    {
        return Calculator::init($cart_vars->total)->isNegativeOrZero() &&
               Calculator::init($cart_vars->recurring_amount)->isNegativeOrZero();
    }

    public function customer_has_trialled($plan_id = '')
    {
        if (ppress_settings_by_key('one_time_trial') == 'true' && is_user_logged_in()) {

            $customer = CustomerFactory::fromUserId(get_current_user_id());

            if ($customer->exists()) {

                $cache_key = sprintf('ppress_has_trialled_%s_%s', $customer->id, $plan_id);

                $trials_count = wp_cache_get($cache_key);

                if (false === $trials_count) {

                    global $wpdb;

                    $table = Base::subscriptions_db_table();

                    if (apply_filters('ppress_checkout_global_onetime_free_trial', false, $customer, $plan_id)) {

                        $trials_count = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT COUNT(*) FROM $table WHERE status != %s AND customer_id = %d AND trial_period != %s",
                                SubscriptionStatus::PENDING,
                                $customer->id,
                                SubscriptionTrialPeriod::DISABLED
                            )
                        );

                    } else {

                        $trials_count = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT COUNT(*) FROM $table WHERE status != %s AND customer_id = %d AND plan_id = %d AND trial_period != %s",
                                SubscriptionStatus::PENDING,
                                $customer->id,
                                absint($plan_id),
                                SubscriptionTrialPeriod::DISABLED
                            )
                        );
                    }

                    wp_cache_set($cache_key, $trials_count, '', MINUTE_IN_SECONDS);
                }

                if (absint($trials_count) > 0) return true;
            }
        }

        return false;
    }

    /**
     * @param $args
     *
     * @return CartEntity
     */
    public function checkout_order_calculation($args)
    {
        $defaults = [
            'plan_id'     => 0,
            'coupon_code' => '',
            'tax_rate'    => '0'
        ];

        $args = wp_parse_args($args, $defaults);

        $tax_rate = $args['tax_rate'];

        $coupon_code = ! empty($args['coupon_code']) ? $args['coupon_code'] : '';

        $planObj = ppress_get_plan(absint($args['plan_id']));

        $sub_total = $planObj->has_free_trial() ? '0' : $planObj->price;

        if ($planObj->is_recurring() && ! empty($planObj->signup_fee) && Calculator::init($planObj->signup_fee)->isGreaterThan('0')) {
            $sub_total = Calculator::init($sub_total)->plus($planObj->signup_fee)->val();
        }

        $recurring_amount = $planObj->is_recurring() ? $planObj->price : '0';

        $discount_percentage  = '';
        $tax_amount           = '0';
        $recurring_tax_amount = '0';
        $discount_amount      = '0';
        $tax_rate_decimal     = '0';

        $couponObj = CouponFactory::fromCode($coupon_code);

        if ($couponObj->exists()) {

            $discount_amount = $couponObj->amount;

            $recurring_discount_amount = $discount_amount;

            if ($couponObj->unit == CouponUnit::PERCENTAGE) {

                $discount_percentage = $couponObj->amount;

                $discount_amount = Calculator::init($discount_amount)
                                             ->dividedBy('100')
                                             ->multipliedBy($sub_total)
                                             ->val();

                $recurring_discount_amount = Calculator::init($recurring_discount_amount)
                                                       ->dividedBy('100')
                                                       ->multipliedBy($recurring_amount)
                                                       ->val();
            }

            if ($planObj->is_recurring() && $couponObj->is_recurring()) {
                $recurring_amount = Calculator::init($recurring_amount)->minus($recurring_discount_amount)->val();
            }
        }

        if (
            apply_filters('ppress_checkout_is_tax_enabled', TaxService::init()->is_tax_enabled(), $args) &&
            ! empty($tax_rate) &&
            ! Calculator::init($tax_rate)->isNegativeOrZero()
        ) {

            $tax_rate_decimal = Calculator::init($tax_rate)->dividedBy('100')->val();

            if (TaxService::init()->is_price_inclusive_tax() === true) {

                $gross_amount = Calculator::init($sub_total)->minus($discount_amount)->val();

                $sub_total = Calculator::init($gross_amount)->dividedBy(
                    Calculator::init('1')->plus($tax_rate_decimal)->val()
                )->val();

                $tax_amount = Calculator::init($gross_amount)->minus($sub_total)->val();

                if ($planObj->is_recurring()) {

                    $old_recurring_amount = $recurring_amount;

                    $recurring_amount = Calculator::init($recurring_amount)->dividedBy(
                        Calculator::init('1')->plus($tax_rate_decimal)->val()
                    )->val();

                    $recurring_tax_amount = Calculator::init($old_recurring_amount)->minus($recurring_amount)->val();
                }

            } else {

                $tax_amount = Calculator::init($sub_total)->minus($discount_amount)->multipliedBy($tax_rate_decimal)->val();

                $recurring_tax_amount = Calculator::init($recurring_amount)->multipliedBy($tax_rate_decimal)->val();
            }
        }

        $total = Calculator::init($sub_total)->minus($discount_amount)->plus($tax_amount)->val();

        $recurring_amount = Calculator::init($recurring_tax_amount)->plus($recurring_amount)->val();

        $cart                      = new CartEntity();
        $cart->sub_total           = $sub_total;
        $cart->coupon_code         = $coupon_code;
        $cart->discount_amount     = $discount_amount;
        $cart->discount_percentage = $discount_percentage;
        $cart->tax_rate            = $tax_rate;
        $cart->tax_rate_decimal    = $tax_rate_decimal;
        $cart->tax_amount          = $tax_amount;
        $cart->total               = $total;

        // recurring vars
        $cart->initial_amount     = $total;
        $cart->initial_tax        = $tax_amount;
        $cart->initial_tax_rate   = $tax_rate;
        $cart->recurring_tax_rate = $tax_rate;
        $cart->recurring_amount   = $recurring_amount;
        $cart->recurring_tax      = $recurring_tax_amount;
        $cart->expiration_date    = SubscriptionService::init()->get_plan_expiration_datetime($planObj->id);

        return $cart;
    }

    public function get_customer_orders_url($customer_id, $order_status = false)
    {
        $args = ['by_ci' => $customer_id];
        if ($order_status) $args['status'] = $order_status;

        return add_query_arg($args, PPRESS_MEMBERSHIP_ORDERS_SETTINGS_PAGE);
    }

    /**
     * Generate an order key
     *
     * @return string The order key.
     */
    public function generate_order_key()
    {
        $key = strtolower(md5(uniqid('ppress', true)));

        return apply_filters('ppress_generate_order_key', $key);
    }

    /**
     * @param $order_id
     *
     * @return false|int
     */
    public function delete_order($order_id)
    {
        $result = OrderRepository::init()->delete($order_id);

        if ($result) {
            OrderRepository::init()->delete_all_meta_data($order_id);
        }

        do_action('ppress_order_deleted', $order_id);

        return $result;
    }

    /**
     * @param $order_id
     *
     * @return array
     */
    public function get_order_notes($order_id)
    {
        global $wpdb;

        $table = Base::order_meta_db_table();

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_id, meta_value FROM $table WHERE meta_key = %s AND ppress_order_id = %d",
                self::ORDER_NOTE_META_KEY,
                $order_id
            ),
            ARRAY_A
        );
    }

    public function add_order_note($order_id, $note)
    {
        $note = ppress_format_date_time(time()) . '|' . $note;

        return OrderRepository::init()->add_meta_data(
            $order_id,
            self::ORDER_NOTE_META_KEY,
            $note
        );
    }

    /**
     * @param int $meta_id Note meta ID
     *
     * @return bool
     */
    public function delete_order_note_by_id($meta_id)
    {
        return delete_metadata_by_mid('ppress_order', $meta_id);
    }

    public function delete_all_order_notes($order_id)
    {
        return OrderRepository::init()->delete_meta_data(
            $order_id,
            self::ORDER_NOTE_META_KEY
        );
    }

    /**
     * @param array $args
     * @param SubscriptionEntity $subscription
     *
     * @return false|int
     */
    public function record_subscription_renewal_order($args, $subscription)
    {
        $args = wp_parse_args($args, array(
            'total_amount'   => '', // This is the full amount that was charged at the gateway, INCLUDING tax.
            'transaction_id' => '',
            'payment_method' => '',
        ));

        $orders = OrderRepository::init()->retrieveBy([
            'transaction_id' => $args['transaction_id'],
            'number'         => 1
        ]);

        if ( ! empty($orders)) return false;

        $parent_order = OrderFactory::fromId($subscription->parent_order_id);

        $new_order                   = new OrderEntity();
        $new_order->subscription_id  = $subscription->id;
        $new_order->customer_id      = $subscription->customer_id;
        $new_order->plan_id          = $parent_order->plan_id;
        $new_order->billing_address  = $parent_order->billing_address;
        $new_order->billing_city     = $parent_order->billing_city;
        $new_order->billing_state    = $parent_order->billing_state;
        $new_order->billing_country  = $parent_order->billing_country;
        $new_order->billing_postcode = $parent_order->billing_postcode;
        $new_order->billing_phone    = $parent_order->billing_phone;
        $new_order->order_key        = OrderService::init()->generate_order_key();
        $new_order->order_type       = OrderType::RENEWAL_ORDER;
        $new_order->transaction_id   = $args['transaction_id'];
        $new_order->payment_method   = ! empty($args['payment_method']) ? $args['payment_method'] : $parent_order->payment_method;
        $new_order->status           = OrderStatus::COMPLETED;
        $new_order->mode             = $parent_order->mode;
        $new_order->date_completed   = current_time('mysql', true);
        $new_order->currency         = $parent_order->currency;

        // Force the renewal to have no discount codes.
        $new_order->discount    = '0';
        $new_order->coupon_code = '';

        if ( ! empty($subscription->recurring_tax)) {
            $new_order->tax = $subscription->recurring_tax;
        }

        if ( ! empty($subscription->recurring_tax_rate)) {
            $new_order->tax_rate = $subscription->recurring_tax_rate;
        }

        $new_order->subtotal = ppress_sanitize_amount(Calculator::init($args['total_amount'])->minus($new_order->tax)->val());
        $new_order->total    = ppress_sanitize_amount($args['total_amount']);

        $order_id = $new_order->save();

        $new_order->id = $order_id;

        // ensures after completed order actions are triggered
        $new_order->complete_order();

        do_action('ppress_subscription_renewed', $subscription->id, $order_id);

        return $order_id;
    }

    public function frontend_view_order_url($order_key)
    {
        return add_query_arg(['order_key' => $order_key], MyAccountTag::get_endpoint_url('list-orders'));
    }

    public function admin_view_order_url($order_id_or_key)
    {
        $order_id = $order_id_or_key;
        if ( ! is_numeric($order_id_or_key)) {
            $order_id = OrderFactory::fromOrderKey($order_id_or_key)->id;
        }

        return add_query_arg(['ppress_order_action' => 'edit', 'id' => $order_id], PPRESS_MEMBERSHIP_ORDERS_SETTINGS_PAGE);
    }

    /**
     * @return self
     */
    public static function init()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}