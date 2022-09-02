<?php

namespace ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers;

use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;
use ProfilePress\Core\Membership\PaymentMethods\Stripe\PaymentHelpers;
use ProfilePress\Core\Membership\PaymentMethods\WebhookHandlerInterface;
use ProfilePress\Core\Membership\Repositories\SubscriptionRepository;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePressVendor\Carbon\CarbonImmutable;

class CustomerSubscriptionUpdated implements WebhookHandlerInterface
{
    public function handle($event_data)
    {
        $subscription_profile_id = $event_data['id'];

        $subscription = SubscriptionRepository::init()->retrieveBy([
            'profile_id' => $subscription_profile_id
        ]);

        if (empty($subscription)) return;

        $subscription = $subscription[0];

        if ($event_data['cancel_at_period_end'] === true) {
            // This is a subscription that has been cancelled but not deleted until period end
            $subscription->add_note(
                sprintf(
                    esc_html__('Subscription is scheduled for cancellation on %s', 'wp-user-avatar'),
                    ppress_format_date($event_data['cancel_at'])
                )
            );

            // cancelling early so subscription can be set back to active down below if this isn't an immediate cancellation
            $subscription->cancel();
        } else {
            $subscription->delete_cancellation_requested();
        }

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
                $subscription->cancel();
                break;
            case 'past_due':
                $subscription->add_note(
                    sprintf(
                        esc_html__('Stripe payment failed (payment is past due)', 'wp-user-avatar'),
                        ppress_format_date($event_data['cancel_at'])
                    )
                );
                break;
        }

        do_action('ppress_stripe_subscription_status_change', $subscription->status, $event_data, $subscription);

        $old_amount = $subscription->recurring_amount;
        $new_amount = $event_data['plan']['amount'];

        if ( ! PaymentHelpers::is_zero_decimal_currency($event_data['plan']['currency'])) {
            $new_amount = Calculator::init($new_amount)->dividedBy('100')->val();
        }

        $old_amount = ppress_sanitize_amount($old_amount);
        $new_amount = ppress_sanitize_amount($new_amount);

        if ($new_amount !== $old_amount) {

            $subscription->recurring_amount = $new_amount;

            $subscription->add_note(sprintf(__('Recurring amount changed from %s to %s in Stripe.', 'wp-user-avatar'), $old_amount, $new_amount));
        }

        $subscription->save();
    }
}
