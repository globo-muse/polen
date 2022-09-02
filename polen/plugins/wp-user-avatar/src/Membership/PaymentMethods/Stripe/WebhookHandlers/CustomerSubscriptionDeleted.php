<?php

namespace ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;

class CustomerSubscriptionDeleted implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $subscription_profile_id = $event_data['id'];

        $subscription = SubscriptionRepository::init()->retrieveBy([
            'profile_id' => $subscription_profile_id
        ]);

        if (empty($subscription)) return;

        $subscription = $subscription[0];

        if ( ! in_array($subscription->status, [SubscriptionStatus::CANCELLED, SubscriptionStatus::COMPLETED], true)) {
            $subscription->cancel();
        }
    }
}
