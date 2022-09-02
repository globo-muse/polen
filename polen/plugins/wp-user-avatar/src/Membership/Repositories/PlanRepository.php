<?php

namespace ProfilePress\Core\Membership\Repositories;

use ProfilePress\Core\Base;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\ModelInterface;
use ProfilePress\Core\Membership\Models\Plan\PlanEntity;

class PlanRepository extends BaseRepository
{
    protected $table;

    public function __construct()
    {
        $this->table = Base::subscription_plans_db_table();
    }

    /**
     * @param PlanEntity $data
     *
     * @return false|int
     */
    public function add(ModelInterface $data)
    {
        global $wpdb;

        $result = $wpdb->insert(
            $this->table,
            array(
                'name'                => $data->name,
                'description'         => $data->description,
                'price'               => $data->price,
                'billing_frequency'   => $data->billing_frequency,
                'subscription_length' => $data->subscription_length,
                'total_payments'      => $data->total_payments,
                'signup_fee'          => $data->signup_fee,
                'free_trial'          => $data->free_trial
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%d',
            )
        );

        return ! $result ? false : $wpdb->insert_id;
    }

    /**
     * @param PlanEntity $data
     *
     * @return false|int
     */
    public function update(ModelInterface $data)
    {
        global $wpdb;

        $result = $wpdb->update(
            $this->table,
            [
                'name'                => $data->name,
                'description'         => $data->description,
                'price'               => $data->price,
                'billing_frequency'   => $data->billing_frequency,
                'subscription_length' => $data->subscription_length,
                'total_payments'      => $data->total_payments,
                'signup_fee'          => $data->signup_fee,
                'free_trial'          => $data->free_trial
            ],
            ['id' => $data->id],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
            ],
            ['%d']
        );

        return $result === false ? false : $data->id;
    }

    /**
     * @param $id
     *
     * @return int|false
     */
    public function delete($id)
    {
        global $wpdb;

        return $wpdb->delete($this->table, ['id' => $id], ['%d']);
    }

    /**
     * @param $id
     *
     * @return PlanEntity
     */
    public function retrieve($id)
    {
        global $wpdb;

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $this->table WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        if ( ! $result) $result = [];

        return PlanFactory::make($result);
    }

    /**
     * @param int $limit
     * @param int $current_page
     *
     * @return PlanEntity[]|array
     */
    public function retrieveAll($limit = 0, $current_page = 1)
    {
        global $wpdb;

        $replacement = [1];
        $sql         = "SELECT * FROM $this->table";
        $sql         .= " WHERE 1=%d"; // fixes Notice: wpdb::prepare was called incorrectly. The query argument of wpdb::prepare() must have a placeholder
        $sql         .= " ORDER BY id DESC";
        if ($limit > 0) {
            $sql           .= " LIMIT %d";
            $replacement[] = $limit;
        }

        if ($current_page > 1) {
            $sql           .= "  OFFSET %d";
            $replacement[] = ($current_page - 1) * $limit;
        }

        $result = $wpdb->get_results($wpdb->prepare($sql, $replacement), 'ARRAY_A');

        if (is_array($result) && ! empty($result)) {
            return array_map([PlanFactory::class, 'make'], $result);
        }

        return [];
    }
}