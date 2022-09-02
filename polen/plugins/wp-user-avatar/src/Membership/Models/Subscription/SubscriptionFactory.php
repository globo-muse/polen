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

    /**
     * @param $profile_id
     *
     * @return SubscriptionEntity|false
     */
    public static function fromProfileId($profile_id)
    {
        $subs = SubscriptionRepository::init()->retrieveBy([
            'profile_id' => $profile_id
        ]);

        if ( ! empty($subs)) return $subs[0];

        return false;
    }
}