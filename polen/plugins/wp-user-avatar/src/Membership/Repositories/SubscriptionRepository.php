<?php

namespace ProfilePress\Core\Membership\Repositories;

use ProfilePress\Core\Base;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionFactory;
use ProfilePress\Core\Membership\Models\ModelInterface;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionEntity;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;

class SubscriptionRepository extends BaseRepository
{
    protected $table;

    public function __construct()
    {
        $this->table = Base::subscriptions_db_table();
    }

    /**
     * @param SubscriptionEntity $data
     *
     * @return false|int
     */
    public function add(ModelInterface $data)
    {
        $result = $this->wpdb()->insert(
            $this->table,
            array(
                'parent_order_id'    => $data->parent_order_id,
                'plan_id'            => $data->plan_id,
                'customer_id'        => $data->customer_id,
                'billing_frequency'  => $data->billing_frequency,
                'initial_amount'     => $data->initial_amount,
                'initial_tax'        => $data->initial_tax,
                'initial_tax_rate'   => $data->initial_tax_rate,
                'recurring_amount'   => $data->recurring_amount,
                'recurring_tax'      => $data->recurring_tax,
                'recurring_tax_rate' => $data->recurring_tax_rate,
                'total_payments'     => $data->total_payments,
                'trial_period'       => $data->trial_period,
                'status'             => $data->status,
                'profile_id'         => $data->profile_id,
                'created_date'       => empty($data->created_date) ? current_time('mysql', true) : $data->created_date,
                'expiration_date'    => $data->expiration_date
            ),
            array(
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );

        return ! $result ? false : $this->wpdb()->insert_id;
    }

    /**
     * @param SubscriptionEntity $data
     *
     * @return false|int
     */
    public function update(ModelInterface $data)
    {
        $result = $this->wpdb()->update(
            $this->table,
            [
                'parent_order_id'    => $data->parent_order_id,
                'plan_id'            => $data->plan_id,
                'customer_id'        => $data->customer_id,
                'billing_frequency'  => $data->billing_frequency,
                'initial_amount'     => $data->initial_amount,
                'initial_tax'        => $data->initial_tax,
                'initial_tax_rate'   => $data->initial_tax_rate,
                'recurring_amount'   => $data->recurring_amount,
                'recurring_tax'      => $data->recurring_tax,
                'recurring_tax_rate' => $data->recurring_tax_rate,
                'total_payments'     => $data->total_payments,
                'trial_period'       => $data->trial_period,
                'status'             => $data->status,
                'profile_id'         => $data->profile_id,
                'created_date'       => empty($data->created_date) ? current_time('mysql', true) : $data->created_date,
                'expiration_date'    => $data->expiration_date
            ],
            ['id' => $data->id],
            [
                '%d',
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
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
        return $this->wpdb()->delete($this->table, ['id' => $id], ['%d']);
    }

    public function delete_pending_subs($customer_id, $plan_id)
    {
        return $this->wpdb()->delete(
            $this->table,
            [
                'customer_id' => $customer_id,
                'plan_id'     => $plan_id,
                'status'      => SubscriptionStatus::PENDING
            ],
            ['%d', '%d', '%s']
        );
    }

    /**
     * @param $id
     *
     * @return SubscriptionEntity
     */
    public function retrieve($id)
    {
        $result = $this->wpdb()->get_row(
            $this->wpdb()->prepare(
                "SELECT * FROM $this->table WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        if ( ! $result) $result = [];

        return SubscriptionFactory::make($result);
    }

    /**
     * @param $args
     * @param $count
     *
     * @return SubscriptionEntity[]|string|int
     */
    public function retrieveBy($args = array(), $count = false)
    {
        $defaults = [
            'subscription_id' => 0,
            'search'          => '',
            'number'          => 10,
            'offset'          => 0,
            'parent_order_id' => 0,
            'plan_id'         => 0,
            'customer_id'     => 0,
            'profile_id'      => 0,
            'status'          => [],
            'created_date'    => '',
            'expiration_date' => '',
            'start_date'      => '',
            'end_date'        => '',
            'date_compare'    => '=',
            'date_column'     => 'created_date',
            'order'           => 'DESC',
            'orderby'         => 'id'
        ];

        $args = wp_parse_args($args, $defaults);

        $limit = absint($args['number']);

        $offset = $args['offset'];
        $search = $args['search'];

        $sql = "SELECT * FROM $this->table";

        if ($count === true) {
            $sql = "SELECT COUNT(id) FROM $this->table";
        }

        $user_table     = $this->wpdb()->users;
        $customer_table = Base::customers_db_table();

        $date_compare = ! empty($args['date_compare']) ? esc_sql($args['date_compare']) : '=';

        $replacement = [1];
        $sql         .= " WHERE 1=%d"; // fixes Notice: wpdb::prepare was called incorrectly. The query argument of wpdb::prepare() must have a placeholder

        if ($args['subscription_id'] > 0) {
            $sql           .= " AND id = %d";
            $replacement[] = (int)$args['subscription_id'];
        }

        if ($args['plan_id'] > 0) {
            $sql           .= " AND plan_id = %d";
            $replacement[] = (int)$args['plan_id'];
        }

        if ($args['customer_id'] > 0) {
            $sql           .= " AND customer_id = %d";
            $replacement[] = (int)$args['customer_id'];
        }

        if ($args['parent_order_id'] > 0) {
            $sql           .= " AND parent_order_id = %d";
            $replacement[] = (int)$args['parent_order_id'];
        }

        if ( ! empty($args['profile_id'])) {
            $sql           .= " AND profile_id = %s";
            $replacement[] = sanitize_text_field($args['profile_id']);
        }

        if (
            ! empty($args['status']) &&
            count(array_intersect($args['status'], array_keys(SubscriptionStatus::get_all()))) == count($args['status'])
        ) {
            $sql         .= " AND status IN (" . implode(',', array_fill(0, count($args['status']), '%s')) . ") ";
            $replacement = array_merge($replacement, $args['status']);
        }

        if ( ! empty($args['created_date'])) {
            $sql           .= " AND DATE(created_date) $date_compare %s";
            $replacement[] = wp_date('Y-m-d', ppress_date_to_utc_timestamp($args['created_date']));
        }

        if ( ! empty($args['expiration_date'])) {
            $sql           .= " AND DATE(expiration_date) $date_compare %s";
            $replacement[] = wp_date('Y-m-d', ppress_date_to_utc_timestamp($args['expiration_date']));
        }

        $start_date  = $args['start_date'];
        $end_date    = $args['end_date'];
        $date_column = esc_sql($args['date_column']);

        if ( ! empty($start_date)) {
            $sql           .= " AND DATE($date_column) >= %s";
            $replacement[] = wp_date('Y-m-d', ppress_date_to_utc_timestamp($start_date), new \DateTimeZone('UTC'));
        }

        if ( ! empty($end_date)) {
            $sql           .= " AND DATE($date_column) <= %s";
            $replacement[] = wp_date('Y-m-d', ppress_date_to_utc_timestamp($end_date), new \DateTimeZone('UTC'));
        }

        if ( ! empty($search)) {

            if (is_numeric($search)) {
                $sql .= " AND (id = %d";
                $sql .= " OR plan_id = %d";
                $sql .= " OR customer_id = %d)";

                $replacement[] = $search;
                $replacement[] = $search;
                $replacement[] = $search;
            } elseif (filter_var($search, FILTER_VALIDATE_EMAIL)) {
                $sql           .= " AND customer_id = (SELECT id FROM $customer_table WHERE user_id = (SELECT ID FROM $user_table WHERE user_email = %s))";
                $replacement[] = $search;
            } else {
                $sql .= " AND profile_id = %s";
                $sql .= " OR customer_id IN (SELECT id FROM $customer_table WHERE user_id IN (SELECT ID FROM $user_table WHERE user_nicename LIKE %s OR display_name LIKE %s))";

                $search_like = '%' . parent::wpdb()->esc_like(sanitize_text_field($search)) . '%';

                $replacement[] = $search;
                $replacement[] = $search_like;
                $replacement[] = $search_like;
            }
        }

        $sql .= sprintf(" ORDER BY %s %s", esc_sql($args['orderby']), esc_sql($args['order']));

        if ($count === false) {
            if ($limit > 0) {
                $sql           .= " LIMIT %d";
                $replacement[] = $limit;
            }

            if ($offset > 0) {
                $sql           .= "  OFFSET %d";
                $replacement[] = $offset;
            }
        }

        if ($count === true) {
            return $this->wpdb()->get_var($this->wpdb()->prepare($sql, $replacement));
        }

        $result = $this->wpdb()->get_results($this->wpdb()->prepare($sql, $replacement), 'ARRAY_A');

        if (is_array($result) && ! empty($result)) {
            return array_map([SubscriptionFactory::class, 'make'], $result);
        }

        return [];
    }

    public function get_count_by_status($status)
    {
        return $this->wpdb()->get_var(
            $this->wpdb()->prepare(
                "SELECT COUNT(id) FROM $this->table WHERE status = %s",
                $status
            )
        );
    }
}