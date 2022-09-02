<?php

namespace ProfilePress\Core\Membership\Models\Order;


class CartEntity
{
    public $sub_total;

    public $coupon_code;

    public $discount_amount;

    public $discount_percentage;

    public $tax_rate;

    public $tax_rate_decimal;

    public $tax_amount;

    public $total;

    // recurring vars

    public $initial_amount;

    public $initial_tax;

    public $initial_tax_rate;

    public $recurring_tax_rate;

    public $recurring_amount;

    public $recurring_tax;

    public $expiration_date;
}