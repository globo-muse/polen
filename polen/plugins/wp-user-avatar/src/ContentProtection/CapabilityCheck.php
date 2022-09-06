<?php

namespace ProfilePress\Core\ContentProtection;

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;

class CapabilityCheck
{
    public function __construct()
    {
        add_filter('user_has_cap', array($this, 'user_has_cap'), 99, 3);
    }

    public function user_has_cap($all_caps, $caps, $args)
    {
        if (isset($caps[0])) {

            if (false !== strpos($caps[0], 'ppress_plan_')) {

                preg_match('/ppress_plan_([0-9]+)/', $caps[0], $matches);

                $user_id = (int)$args[1];

                if (isset($matches[1])) {

                    $plan_id = (int)$matches[1];

                    if (ppress_has_active_subscription($user_id, $plan_id)) {
                        $all_caps[$caps[0]] = true;
                    }
                }
            }
        }

        return $all_caps;
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}
