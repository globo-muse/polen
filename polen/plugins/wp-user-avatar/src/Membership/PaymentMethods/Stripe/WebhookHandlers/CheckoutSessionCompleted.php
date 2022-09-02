<?php

namespace ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\PaymentMethods\Stripe\APIClass;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;

class CheckoutSessionCompleted implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        if ( ! in_array($event_data['mode'], ['subscription', 'payment'], true)) return;

        $order = OrderFactory::fromOrderKey($event_data['client_reference_id']);

        $subscription = SubscriptionFactory::fromId($order->subscription_id);

        if ($event_data['mode'] == 'subscription') {

            $stripe_subscription = APIClass::stripeClient()->subscriptions->retrieve($event_data['subscription'], [
                'expand' => ['latest_invoice']
            ])->toArray();

            $transaction_id = $stripe_subscription['latest_invoice']['payment_intent'];

        } else {

            $transaction_id = $event_data['payment_intent'];
        }

        $order->complete_order($transaction_id);

        if ( ! $subscription->is_active()) {

            if ($event_data['mode'] == 'subscription') {

                $subscription->profile_id = $event_data['subscription'];

                if ($subscription->has_trial()) {
                    $subscription->enable_subscription_trial();
                } else {
                    $subscription->activate_subscription();
                }

            } else {
                $subscription->activate_subscription();
            }
        }
    }
}
