<?php

namespace ProfilePress\Core\ContentProtection;

use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Repositories\PlanRepository;

class NavMenuProtection
{
    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'init'], 20);
    }

    public function init()
    {
        if (class_exists('\Nav_Menu_Roles')) {
            add_filter('nav_menu_roles', [$this, 'new_roles']);
            add_filter('nav_menu_roles_item_visibility', [$this, 'item_visibility'], 10, 2);
        }
    }

    /*
     * Add custom roles to Nav Menu Roles menu options
     *
     * @param array $roles An array of all available roles, by default is global $wp_roles
     * @return array
     */
    function new_roles($roles)
    {
        return array_merge($this->get_roles_wrapper(), $roles);
    }


    /*
     * Change visibility of each menu item.
     *
     * NMR settings can be "in" (all logged in), "out" (all logged out) or an array of specific roles
     *
     * @param bool $visible
     * @param object $item The menu item object. Nav Menu Roles adds its info to $item->roles
     * @return boolean
     */
    function item_visibility($visible, $item)
    {
        if ( ! $visible && isset($item->roles) && is_array($item->roles)) {

            // Get the plugin-specific roles for this menu item.
            $roles = $this->get_relevant_roles_wrapper($item->roles);

            if (count($roles) > 0) {

                // Only need to look through the relevant roles.
                foreach ($roles as $role) {

                    // Test if the current user has the specific plan membership.
                    if ($this->current_user_can_wrapper($role)) {
                        $visible = true;
                        break;
                    } else {
                        $visible = false;
                    }
                }

            }

        }

        return $visible;
    }

    /*-----------------------------------------------------------------------------------*/
    /* Helper Functions */
    /*-----------------------------------------------------------------------------------*/

    /*
     * Get the plugin-specific "roles" returned in an array, with ID => Name key pairs
     *
     * @return array
     */
    function get_roles_wrapper()
    {
        $roles = array();

        $plans = PlanRepository::init()->retrieveAll();

        if ( ! empty($plans)) {

            foreach ($plans as $plan) {
                $roles['ppress_membership_' . $plan->id] = sprintf('%s (ProfilePress %s)', $plan->name, esc_html__('Plan', 'wp-user-avatar'));
            }
        }

        return $roles;
    }

    /*
     * Get the plugin-specific "roles" relevant to this menu item
     *
     * @return array
     */
    function get_relevant_roles_wrapper($roles = array())
    {
        return preg_grep('/^ppress_membership_*/', $roles);
    }

    /*
     * Check the current user has plugin-specific level capability
     *
     * @param string $role_id | The ID of the "role" with a plugin-specific prefix
     *
     * @return bool
     */
    function current_user_can_wrapper($role_id = false)
    {
        $user_id = get_current_user_id();

        if ( ! $user_id || ! $role_id) return false;

        $role_id = str_replace('ppress_membership_', '', $role_id);

        return CustomerFactory::fromUserId($user_id)->has_active_subscription($role_id);
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
