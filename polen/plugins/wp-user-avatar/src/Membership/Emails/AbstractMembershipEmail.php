<?php

namespace ProfilePress\Core\Membership\Emails;

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\Services\OrderService;
use ProfilePress\Core\Membership\Services\SubscriptionService;

abstract class AbstractMembershipEmail
{
    use EmailDataTrait;

    /**
     * @param OrderEntity $order
     *
     * @return mixed
     */
    public function get_order_placeholders_values($order, $adminview = false)
    {
        $customer = CustomerFactory::fromId($order->customer_id);

        return apply_filters('ppress_order_placeholders_values', [
            '{{email}}'                => $customer->get_email(),
            '{{first_name}}'           => $customer->get_first_name(),
            '{{last_name}}'            => $customer->get_last_name(),
            '{{billing_address}}'      => $order->get_customer_full_address(),
            '{{billing_phone}}'        => $order->billing_phone,
            '{{customer_tax_id}}'      => $order->get_customer_tax_id(),
            '{{transaction_id}}'       => $order->get_transaction_id(),
            '{{order_id}}'             => $order->get_order_id(),
            '{{order_url}}'            => $adminview === true ?
                OrderService::init()->admin_view_order_url($order->id) :
                OrderService::init()->frontend_view_order_url($order->order_key),
            '{{plan_name}}'            => ppress_get_plan($order->plan_id)->name,
            '{{order_subtotal}}'       => ppress_display_amount($order->subtotal, $order->currency),
            '{{order_tax}}'            => ppress_display_amount($order->tax, $order->currency),
            '{{order_total}}'          => ppress_display_amount($order->total, $order->currency),
            '{{order_date}}'           => ppress_format_date($order->date_created),
            '{{order_payment_method}}' => PaymentMethods::get_instance()->get_by_id($order->payment_method)->get_method_title(),
            '{{site_title}}'           => ppress_site_title(),
            '{{business_name}}'        => ppress_business_name(),
            '{{business_address}}'     => ppress_business_full_address(),
            '{{business_tax_id}}'      => ppress_business_tax_id(),
        ]);
    }

    /**
     * @param SubscriptionEntity $subscription
     *
     * @return mixed
     */
    public function get_subscription_placeholders_values($subscription, $adminview = false)
    {
        $customer = CustomerFactory::fromId($subscription->customer_id);

        $parent_order = OrderFactory::fromId($subscription->parent_order_id);

        return apply_filters('ppress_subscription_placeholders_values', [
            '{{email}}'                   => $customer->get_email(),
            '{{first_name}}'              => $customer->get_first_name(),
            '{{last_name}}'               => $customer->get_last_name(),
            '{{subscription_profile_id}}' => $subscription->profile_id,
            '{{subscription_id}}'         => $subscription->id,
            '{{subscription_url}}'        => $adminview === true ?
                SubscriptionService::init()->admin_view_sub_url($subscription->id) :
                SubscriptionService::init()->frontend_view_sub_url($subscription->id),
            '{{renew_subscription_url}}'  => ppress_plan_checkout_url($subscription->plan_id),
            '{{plan_name}}'               => ppress_get_plan($subscription->plan_id)->name,
            '{{amount}}'                  => ppress_display_amount($subscription->recurring_amount, $parent_order->currency),
            '{{expiration_date}}'         => ppress_format_date($subscription->expiration_date),
            '{{site_title}}'              => ppress_site_title(),
        ]);
    }

    /**
     * @param string $content
     * @param array $placeholders
     *
     * @return array|string|string[]
     */
    public function parse_placeholders($content, $placeholders)
    {
        return str_replace(array_keys($placeholders), array_values($placeholders), $content);
    }

    /**
     * @return static
     */
    public static function init()
    {
        return new static();
    }
}