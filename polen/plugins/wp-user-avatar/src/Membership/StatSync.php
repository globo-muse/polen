<?php

namespace ProfilePress\Core\Membership;

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;

class StatSync
{
    public function __construct()
    {
        add_action('ppress_customer_updated', function ($customer_id) {
            $this->core_actions(false, $customer_id);
        });

        add_action('ppress_order_completed', function (OrderEntity $order) {
            $this->core_actions(false, $order->customer_id);
        });

        add_action('ppress_order_added', function ($result, OrderEntity $order) {
            $this->core_actions(false, $order->customer_id);
        }, 10, 2);

        add_action('ppress_order_updated', function ($result, OrderEntity $order) {
            $this->core_actions(false, $order->customer_id);
        }, 10, 2);

        add_action('ppress_order_deleted', function ($order_id) {
            $this->core_actions(
                false,
                OrderFactory::fromId($order_id)->get_customer_id()
            );
        });

        add_action('wp_login', function ($user_login, $user) {

            if ($user instanceof \WP_User) {
                $user_id = $user->ID;
            } else {
                $user    = get_user_by('login', $user_login);
                $user_id = $user->exists() ? $user->ID : get_current_user_id();
            }

            self::core_actions($user_id);

        }, 10, 2);
    }

    public function core_actions($user_id = false, $customer_id = false)
    {
        $user_id = false !== $user_id ? absint($user_id) : get_current_user_id();

        if ($customer_id) {
            CustomerFactory::fromId($customer_id)->recalculate_stats();
        } else {
            if ($user_id) {
                CustomerFactory::fromUserId($user_id)->recalculate_stats();
            }
        }
    }

    /**
     * @return self
     */
    public static function init()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}
