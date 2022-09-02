<?php

namespace ProfilePress\Core\Membership\Models\Coupon;

use ProfilePress\Core\Membership\Models\FactoryInterface;
use ProfilePress\Core\Membership\Repositories\CouponRepository;

class CouponFactory implements FactoryInterface
{
    /**
     * @param $data
     *
     * @return CouponEntity
     */
    public static function make($data)
    {
        return new CouponEntity($data);
    }

    /**
     * @param $id
     *
     * @return CouponEntity
     */
    public static function fromId($id)
    {
        return CouponRepository::init()->retrieve(absint($id));
    }

    /**
     * @param $code
     *
     * @return CouponEntity
     */
    public static function fromCode($code)
    {
        return CouponRepository::init()->retrieveByCode($code);
    }
}