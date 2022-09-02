<?php

namespace ProfilePress\Core\Membership\Controllers;

use ProfilePress\Core\Membership\Models\Plan\PlanEntity;
use ProfilePress\Core\Membership\Repositories\PlanRepository;

class SubscriptionPlanController extends BaseController
{
    /**
     * @param int|PlanEntity $id_or_obj
     */
    public function activate_plan($id_or_obj)
    {
        $plan = is_int($id_or_obj) ? ppress_get_plan($id_or_obj) : $id_or_obj;

        $result = $plan->activate();

        do_action('ppress_membership_activate_plan', $plan);

        return $result;
    }

    /**
     * @param int|PlanEntity $id_or_obj
     */
    public function deactivate_plan($id_or_obj)
    {
        $plan = is_int($id_or_obj) ? ppress_get_plan($id_or_obj) : $id_or_obj;

        $result = $plan->deactivate();

        do_action('ppress_membership_deactivate_plan', $plan);

        return $result;
    }

    /**
     * @param int|PlanEntity $id_or_obj
     */
    public function delete_plan($id_or_obj)
    {
        $plan = is_int($id_or_obj) ? ppress_get_plan($id_or_obj) : $id_or_obj;

        $result = PlanRepository::init()->delete($plan->get_id());

        do_action('ppress_membership_delete_plan', $plan);

        return $result;
    }

    /**
     * @param int|PlanEntity $id_or_obj
     */
    public function duplicate_plan($id_or_obj)
    {
        $plan = is_int($id_or_obj) ? ppress_get_plan($id_or_obj) : $id_or_obj;

        $plan->name .= ' – Copy';

        $result = PlanRepository::init()->add($plan);

        do_action('ppress_membership_duplicate_plan', $plan);

        return $result;
    }
}