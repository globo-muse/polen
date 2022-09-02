<?php

namespace ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;
use ProfilePressVendor\Carbon\CarbonImmutable;

class CustomerSubscriptionCreated implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $subscription_profile_id = $event_data['id'];

        $subscription = SubscriptionRepository::init()->retrieveBy([
            'profile_id' => $subscription_profile_id
        ]);

        if (empty($subscription)) return;

        $subscription = $subscription[0];

        switch ($event_data['status']) {
            case 'active':
                if ( ! $subscription->is_active()) {
                    $subscription->activate_subscription($subscription_profile_id);
                } else {
                    $subscription->status = SubscriptionStatus::ACTIVE;
                }
                // ensures expiration date is in sync with Stripe
                $subscription->expiration_date = CarbonImmutable::createFromTimestampUTC($event_data['current_period_end'])->toDateTimeString();
                break;
            case 'trialing':
                if ( ! $subscription->is_active()) {
                    $subscription->enable_subscription_trial($subscription_profile_id);
                } else {
                    $subscription->status = SubscriptionStatus::TRIALLING;
                }
                // ensures expiration date is in sync with Stripe
                $subscription->expiration_date = CarbonImmutable::createFromTimestampUTC($event_data['trial_end'])->toDateTimeString();
                break;
            case 'unpaid':
            case 'canceled':
                $subscription->status = SubscriptionStatus::CANCELLED;
                break;
        }

        $subscription->save();
    }
}
