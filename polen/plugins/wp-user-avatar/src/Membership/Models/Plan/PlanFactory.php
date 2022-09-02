<?php

namespace ProfilePress\Core\Membership\Models\Plan;

use ProfilePress\Core\Membership\Models\FactoryInterface;
use ProfilePress\Core\Membership\Repositories\PlanRepository;

class PlanFactory implements FactoryInterface
{
    /**
     * @param $data
     *
     * @return PlanEntity
     */
    public static function make($data)
    {
        return new PlanEntity($data);
    }

    /**
     * @param $id
     *
     * @return PlanEntity
     */
    public static function fromId($id)
    {
        return PlanRepository::init()->retrieve(absint($id));
    }
}