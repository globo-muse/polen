<?php

namespace ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\PaymentMethods\Stripe\PaymentHelpers;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;

class InvoicePaymentSucceeded implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $subs = SubscriptionRepository::init()->retrieveBy([
            'profile_id' => $event_data['subscription'],
            'number'     => 1
        ]);

        if (empty($subs)) return;

        $subscription = $subs[0];

        if ($event_data['billing_reason'] == 'subscription_create') {

            $parent_order = OrderFactory::fromId($subscription->parent_order_id);

            if ($parent_order->exists() && ! $parent_order->is_completed()) {
                $parent_order->complete_order($event_data['payment_intent']);
            }
        }

        if ($event_data['billing_reason'] == 'subscription_cycle') {

            // This is a renewal charge
            $order_id = $subscription->add_renewal_order([
                'transaction_id' => $event_data['payment_intent'],
                'total_amount'   => PaymentHelpers::stripe_amount_to_ppress_amount($event_data['amount_paid']),
            ]);

            if ( ! empty($order_id)) {
                // we are not changing the expiration date because it is always in sync with stripe via
                // CustomerSubscriptionUpdated event.
                $subscription->renew(false);
            }
        }
    }
}
