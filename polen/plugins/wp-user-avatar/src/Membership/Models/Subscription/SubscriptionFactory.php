<?php

namespace ProfilePress\Core\Membership\Models\Subscription;

use ProfilePress\Core\Membership\Models\FactoryInterface;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;

class SubscriptionFactory implements FactoryInterface
{
    /**
     * @param $data
     *
     * @return SubscriptionEntity
     */
    public static function make($data)
    {
        return new SubscriptionEntity($data);
    }

    /**
     * @param $id
     *
     * @return SubscriptionEntity
     */
    public static function fromId($id)
    {
        return SubscriptionRepository::init()->retrieve(absint($id));
    }
}