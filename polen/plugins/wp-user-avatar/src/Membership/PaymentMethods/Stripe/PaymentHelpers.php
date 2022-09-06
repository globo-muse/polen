<?php

namespace ProfilePress\Core\Membership\PaymentMethods\Stripe;

use ProfilePress\Core\Classes\ExtensionManager;
use ProfilePress\Core\Membership\CheckoutFields;
use ProfilePress\Core\Membership\Models\Customer\CustomerEntity;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionBillingFrequency;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionTrialPeriod;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePressVendor\Carbon\CarbonImmutable;

class PaymentHelpers
{
    public static function add_coupon_to_bucket($stripe_coupon_id)
    {
        $data                    = \get_option('ppress_stripe_coupon_bucket', []);
        $data[$stripe_coupon_id] = time() + (24 * HOUR_IN_SECONDS);
        \update_option('ppress_stripe_coupon_bucket', $data);
    }

    public static function empty_coupon_bucket()
    {
        $data = \get_option('ppress_stripe_coupon_bucket', []);

        if ( ! empty($data)) {

            foreach ($data as $coupon_id => $time) {
                if (time() >= $time) {
                    self::delete_coupon($coupon_id);
                    unset($data[$coupon_id]);
                }
            }

            \update_option('ppress_stripe_coupon_bucket', $data);
        }
    }

    public static function delete_coupon($stripe_coupon_id)
    {
        try {
            APIClass::stripeClient()->coupons->delete($stripe_coupon_id);
        } catch (\Exception $e) {
        }
    }

    /**
     * @return string
     */
    public static function get_signup_fee_label()
    {
        return apply_filters('ppress_stripe_signup_fee_label', __('Setup Fee', 'wp-user-avatar'));
    }

    public static function stripe_amount_to_ppress_amount($amount, $currency = '')
    {
        if ( ! self::is_zero_decimal_currency($currency)) {
            $amount = ppress_sanitize_amount(Calculator::init($amount)->dividedBy('100')->val());
        }

        return $amount;
    }

    /**
     * @param string $freeTrial
     *
     * @return int
     */
    public static function free_trial_days_count($freeTrial)
    {
        switch ($freeTrial) {
            case SubscriptionTrialPeriod::THREE_DAYS:
                return 3;
            case SubscriptionTrialPeriod::FIVE_DAYS:
                return 5;
            case SubscriptionTrialPeriod::ONE_WEEK:
                return 7;
            case SubscriptionTrialPeriod::TWO_WEEKS:
                return 14;
            case SubscriptionTrialPeriod::THREE_WEEKS:
                return 21;
            case SubscriptionTrialPeriod::ONE_MONTH:
                return CarbonImmutable::now('UTC')->diffInDays(
                    CarbonImmutable::now('UTC')->addMonth()
                );
        }

        return 0;
    }

    /**
     * @param SubscriptionEntity $subscription
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public static function get_product_price($subscription, $statement_descriptor)
    {
        switch ($subscription->billing_frequency) {

            case SubscriptionBillingFrequency::DAILY :
                $frequency = 1;
                $period    = 'day';
                break;
            case SubscriptionBillingFrequency::MONTHLY :
                $frequency = 1;
                $period    = 'month';
                break;
            case SubscriptionBillingFrequency::WEEKLY :
                $frequency = 1;
                $period    = 'week';
                break;
            case SubscriptionBillingFrequency::QUARTERLY :
                $frequency = 3;
                $period    = 'month';
                break;
            case SubscriptionBillingFrequency::EVERY_6_MONTHS :
                $frequency = 6;
                $period    = 'month';
                break;
            case SubscriptionBillingFrequency::YEARLY :
                $frequency = 1;
                $period    = 'year';
                break;
            default :
                $frequency = 1;
                $period    = $subscription->billing_frequency;
                break;
        }

        $stripe_product_id = 'ppress_prod_' . ppress_md5($subscription->plan_id);

        $plan = PlanFactory::fromId($subscription->plan_id);

        $is_recurring = $subscription->is_recurring();

        try {

            APIClass::stripeClient()->products->retrieve($stripe_product_id);

        } catch (\Exception $e) {

            $create_product_args = [
                'id'          => $stripe_product_id,
                'name'        => $plan->name,
                'description' => strip_tags(sanitize_textarea_field($plan->description)),
                'metadata'    => [
                    'plan_id' => $plan->id,
                    'caller'  => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . PPRESS_VERSION_NUMBER,
                ]
            ];

            if ( ! empty($statement_descriptor)) {
                $create_product_args['statement_descriptor'] = $statement_descriptor;
            }

            $create_product_args = array_filter(apply_filters('ppress_stripe_create_product_args', $create_product_args, $subscription));

            try {
                APIClass::stripeClient()->products->create($create_product_args)->toArray();
            } catch (\Exception $e) {


            }
        }

        $price_args = [
            'currency'    => ppress_get_currency(),
            'product'     => $stripe_product_id,
            'unit_amount' => self::process_amount($subscription->get_recurring_amount()),
        ];

        if ( ! $subscription->is_recurring()) {
            $price_args['unit_amount'] = self::process_amount($subscription->initial_amount);
        }

        if ($is_recurring === true) {

            $price_args['recurring'] = array(
                'interval'       => $period,
                'interval_count' => $frequency
            );
        }

        $price_args = apply_filters('ppress_stripe_create_price_args', $price_args, $subscription, $plan);

        $stripe_price_id = ppress_md5(wp_json_encode($price_args));

        $price_args['metadata'] = [
            'ppress_price_id' => $stripe_price_id,
            'caller'          => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . PPRESS_VERSION_NUMBER,
        ];

        $price_search_args = [
            'product' => $stripe_product_id,
            'type'    => $is_recurring ? 'recurring' : 'one_time',
        ];

        if ($is_recurring) {
            $price_search_args['recurring'] = array('interval' => $period);
        }

        $stripe_prices = APIClass::stripeClient()->prices->all($price_search_args)->toArray();

        if ( ! empty($stripe_prices['data']) && is_array($stripe_prices['data'])) {

            foreach ($stripe_prices['data'] as $price) {
                if (isset($price['metadata']['ppress_price_id']) && $price['metadata']['ppress_price_id'] == $stripe_price_id) {
                    return [
                        'stripe_product_id' => $stripe_product_id,
                        'stripe_price_id'   => $price['id']
                    ];
                }
            }
        }

        try {
            $created_price = APIClass::stripeClient()->prices->create($price_args)->toArray();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        return [
            'stripe_product_id' => $stripe_product_id,
            'stripe_price_id'   => $created_price['id']
        ];
    }

    /**
     * @param CustomerEntity $customer
     *
     * @return int
     *
     * @throws \Exception
     */
    public static function get_stripe_customer_id($customer)
    {
        $search_result = APIClass::stripeClient()->customers->search([
            'query' => sprintf('email:\'%s\' AND metadata[\'ppress_customer_id\']:\'%s\'', $customer->get_email(), $customer->id)
        ])->toArray();

        if ( ! empty($search_result['data']) && isset($search_result['data'][0]['id'])) {
            return $search_result['data'][0]['id'];
        }

        $pp_customer_billing = $customer->get_billing_details();

        $create_customer_args = [
            'email' => $customer->get_email(),
            'name'  => $customer->get_name()
        ];

        if ( ! empty($pp_customer_billing)) {

            $create_customer_args['address'] = [];

            if ( ! empty($pp_customer_billing[CheckoutFields::BILLING_CITY])) {
                $create_customer_args['address']['city'] = $pp_customer_billing[CheckoutFields::BILLING_CITY];
            }

            if ( ! empty($pp_customer_billing[CheckoutFields::BILLING_COUNTRY])) {
                $create_customer_args['address']['country'] = $pp_customer_billing[CheckoutFields::BILLING_COUNTRY];
            }

            if ( ! empty($pp_customer_billing[CheckoutFields::BILLING_ADDRESS])) {
                $create_customer_args['address']['line1'] = $pp_customer_billing[CheckoutFields::BILLING_ADDRESS];
            }

            if ( ! empty($pp_customer_billing[CheckoutFields::BILLING_POST_CODE])) {
                $create_customer_args['address']['postal_code'] = $pp_customer_billing[CheckoutFields::BILLING_POST_CODE];
            }

            if ( ! empty($pp_customer_billing[CheckoutFields::BILLING_STATE])) {
                $create_customer_args['address']['state'] = $pp_customer_billing[CheckoutFields::BILLING_STATE];
            }
        }

        $create_customer_args['metadata'] = [
            'ppress_customer_id' => $customer->id,
            'caller'             => __CLASS__ . '|' . __METHOD__ . '|' . __LINE__ . '|' . PPRESS_VERSION_NUMBER,
        ];

        $create_customer_args = apply_filters('ppress_create_customer_args', $create_customer_args, $customer);

        $created_customer = APIClass::stripeClient()->customers->create($create_customer_args)->toArray();

        $stripe_customer_id = ppress_var($created_customer, 'id');

        $customer->update_meta('stripe_customer_id', $stripe_customer_id);

        return $stripe_customer_id;
    }

    public static function is_zero_decimal_currency($currency = '')
    {
        if (empty($currency)) {
            $currency = ppress_get_currency();
        }

        $currency = strtolower($currency);

        $currencies = [
            'bif',
            'clp',
            'djf',
            'gnf',
            'jpy',
            'kmf',
            'krw',
            'mga',
            'pyg',
            'rwf',
            'ugx',
            'vnd',
            'vuv',
            'xaf',
            'xof',
            'xpf',
        ];

        return in_array($currency, $currencies, true);
    }

    public static function process_amount($price, $currency = '')
    {
        if ( ! self::is_zero_decimal_currency($currency)) {
            $price = Calculator::init($price)->toScale(2)->multipliedBy(100)->val();
        }

        return Calculator::init($price)->toScale(0)->val();
    }

    public static function application_fee_percent()
    {
        return 2;
    }

    public static function application_fee_amount($order_total)
    {
        return self::process_amount(
            Calculator::init($order_total)->multipliedBy('0.02')->val()
        );
    }

    public static function has_application_fee()
    {
        if (ExtensionManager::is_premium()) return false;

        $account_country = ppress_business_country('US');

        /**
         * Do not add a fee if account country does not support application fees.
         * @see https://stripe.com/docs/connect/direct-charges#collecting-fees
         */
        if (in_array(strtolower($account_country), ['br'], true)) return false;

        return true;
    }
}