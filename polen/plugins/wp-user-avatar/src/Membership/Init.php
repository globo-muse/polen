<?php

namespace ProfilePress\Core\Membership;

use ProfilePress\Core\Membership\Controllers\CheckoutController;
use ProfilePress\Core\Membership\Controllers\FrontendController;
use ProfilePress\Core\Membership\Emails\NewOrderAdminNotification;
use ProfilePress\Core\Membership\Emails\NewOrderReceipt;
use ProfilePress\Core\Membership\Emails\RenewalOrderReceipt;
use ProfilePress\Core\Membership\Emails\SubscriptionAfterExpiredNotification;
use ProfilePress\Core\Membership\Emails\SubscriptionCancelledNotification;
use ProfilePress\Core\Membership\Emails\SubscriptionCompletedNotification;
use ProfilePress\Core\Membership\Emails\SubscriptionExpirationReminder;
use ProfilePress\Core\Membership\Emails\SubscriptionExpiredNotification;
use ProfilePress\Core\Membership\Emails\SubscriptionRenewalReminder;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;
use ProfilePress\Core\Membership\Repositories\CustomerRepository;

class Init
{
    public static function init()
    {
        PaymentMethods::get_instance();

        FrontendController::get_instance();
        CheckoutController::get_instance();

        NewOrderReceipt::init();
        NewOrderAdminNotification::init();
        RenewalOrderReceipt::init();
        SubscriptionCancelledNotification::init();
        SubscriptionExpiredNotification::init();
        SubscriptionCompletedNotification::init();
        SubscriptionRenewalReminder::init();
        SubscriptionExpirationReminder::init();
        SubscriptionAfterExpiredNotification::init();

        StatSync::init();

        add_action('ppress_before_login_redirect', function ($username) {
            self::log_last_login($username);
        });

        add_action('ppress_before_auto_login_redirect', function ($login_id, $user_id) {
            self::log_last_login($user_id);
        }, 10, 2);

        add_action('wp_login', function ($user_login) {
            self::log_last_login($user_login);
        }, 10, 2);
    }

    public static function log_last_login($user_login_or_id)
    {
        static $flag = false;

        if ($flag === true) return;

        if (is_numeric($user_login_or_id)) {
            $user = get_user_by('id', $user_login_or_id);
        } elseif (is_email($user_login_or_id)) {
            $user = get_user_by('email', $user_login_or_id);
        } else {
            $user = get_user_by('login', $user_login_or_id);
        }

        if ($user instanceof \WP_User) {

            CustomerRepository::init()->updateColumn(
                CustomerFactory::fromUserId($user->ID)->id,
                'last_login',
                gmdate('Y-m-d H:i:s')
            );

            $flag = true;
        }
    }
}
