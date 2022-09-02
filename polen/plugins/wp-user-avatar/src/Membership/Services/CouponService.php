<?php

namespace ProfilePress\Core\Membership\Services;

use ProfilePress\Core\Membership\Models\Coupon\CouponFactory;
use ProfilePress\Core\Membership\Models\Coupon\CouponUnit;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Order\OrderType;
use ProfilePress\Core\Membership\Repositories\OrderRepository;

class CouponService
{
    public function get_coupon_percentage_fee($percentage, $subtotal)
    {
        return Calculator::init($percentage)->
        dividedBy('100')->
        multipliedBy($subtotal)->
        val();
    }

    /**
     * Checks whether a customer has used a particular discount code.
     *
     * This is used to prevent users from spamming discount codes.
     *
     * @param int $customer_id
     * @param string $code The discount code to check against the customer ID.
     *
     * @return bool
     */
    function customer_has_used_discount($customer_id, $code)
    {
        $result = OrderRepository::init()->retrieveBy([
            'coupon_code' => $code,
            'status'      => [OrderStatus::COMPLETED, OrderStatus::REFUNDED],
            'customer_id' => (int)$customer_id,
            'number'      => 1
        ]);

        return ! empty($result);
    }

    /**
     * Returns a formatted discount amount with a '%' sign appended (percentage-based) or with the
     * currency sign added to the amount (flat discount rate).
     *
     * @param string $amount Discount amount.
     * @param string $type Discount amount - either 'percentage' or 'flat'.
     *
     * @return string
     */
    function format_discount_display($amount, $type)
    {
        $discount = '';
        if ($type == CouponUnit::PERCENTAGE) {
            $discount = $amount . '%';
        } elseif ($type == CouponUnit::FLAT) {
            $discount = ppress_display_amount($amount);
        }

        return $discount;
    }

    public function validate_discount($code, $plan_id = 0, $order_type = OrderType::NEW_ORDER)
    {
        return CouponFactory::fromCode($code)->is_valid($plan_id, $order_type);
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