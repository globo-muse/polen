<?php

namespace ProfilePress\Core\Membership\Repositories;

use ProfilePress\Core\Base;
use ProfilePress\Core\Membership\Models\Order\OrderFactory;
use ProfilePress\Core\Membership\Models\ModelInterface;
use ProfilePress\Core\Membership\Models\Order\OrderEntity;
use ProfilePress\Core\Membership\Models\Order\OrderMode;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Order\OrderType;
use ProfilePress\Core\Membership\Services\OrderService;

class OrderRepository extends BaseRepository
{
    protected $table;

    public function __construct()
    {
        $this->table = Base::orders_db_table();
    }

    /**
     * @param OrderEntity $data
     *
     * @return false|int
     */
    public function add(ModelInterface $data)
    {
        $result = $this->wpdb()->insert(
            $this->table,
            [
                'order_key'        => ! empty($data->order_key) ? $data->order_key : OrderService::init()->generate_order_key(),
                'plan_id'          => $data->plan_id,
                'customer_id'      => $data->customer_id,
                'subscription_id'  => $data->subscription_id,
                'order_type'       => $data->order_type,
                'transaction_id'   => $data->transaction_id,
                'payment_method'   => $data->payment_method,
                'status'           => $data->status,
                'coupon_code'      => $data->coupon_code,
                'subtotal'         => $data->subtotal,
                'tax'              => $data->tax,
                'tax_rate'         => $data->tax_rate,
                'discount'         => $data->discount,
                'total'            => $data->total,
                'billing_address'  => $data->billing_address,
                'billing_city'     => $data->billing_city,
                'billing_state'    => $data->billing_state,
                'billing_country'  => $data->billing_country,
                'billing_postcode' => $data->billing_postcode,
                'billing_phone'    => $data->billing_phone,
                'mode'             => $data->mode,
                'currency'         => $data->currency,
                'ip_address'       => $data->ip_address,
                'date_created'     => empty($data->date_created) ? current_time('mysql', true) : $data->date_created
            ],
            [
                '%s',
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
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            ]
        );

        return ! $result ? false : $this->wpdb()->insert_id;
    }

    /**
     * @param OrderEntity $data
     *
     * @return false|int
     */
    public function update(ModelInterface $data)
    {
        $result = $this->wpdb()->update(
            $this->table,
            [
                'order_key'        => ! empty($data->order_key) ? $data->order_key : OrderService::init()->generate_order_key(),
                'plan_id'          => $data->plan_id,
                'customer_id'      => $data->customer_id,
                'subscription_id'  => $data->subscription_id,
                'order_type'       => $data->order_type,
                'transaction_id'   => $data->transaction_id,
                'payment_method'   => $data->payment_method,
                'status'           => $data->status,
                'coupon_code'      => $data->coupon_code,
                'subtotal'         => $data->subtotal,
                'tax'              => $data->tax,
                'tax_rate'         => $data->tax_rate,
                'discount'         => $data->discount,
                'total'            => $data->total,
                'billing_address'  => $data->billing_address,
                'billing_city'     => $data->billing_city,
                'billing_state'    => $data->billing_state,
                'billing_country'  => $data->billing_country,
                'billing_postcode' => $data->billing_postcode,
                'billing_phone'    => $data->billing_phone,
                'mode'             => $data->mode,
                'currency'         => $data->currency,
                'ip_address'       => $data->ip_address
            ],
            ['id' => $data->id],
            [
                '%s',
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
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
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

    public function delete_pending_orders($customer_id, $plan_id)
    {
        return $this->wpdb()->delete(
            $this->table,
            [
                'customer_id' => $customer_id,
                'plan_id'     => $plan_id,
                'status'      => OrderStatus::PENDING
            ],
            ['%d', '%d', '%s']
        );
    }

    /**
     * @param $id
     *
     * @return OrderEntity
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

        return OrderFactory::make($result);
    }

    /**
     * @param $order_key
     *
     * @return OrderEntity
     */
    public function retrieveByOrderKey($order_key)
    {
        $result = $this->wpdb()->get_row(
            $this->wpdb()->prepare(
                "SELECT * FROM $this->table WHERE order_key = %s",
                $order_key
            ),
            ARRAY_A
        );

        if ( ! $result) $result = [];

        return OrderFactory::make($result);
    }

    /**
     * @param $args
     * @param $count
     *
     * @return OrderEntity[]|string|int
     */
    public function retrieveBy($args = array(), $count = false)
    {
        $defaults = [
            'search'          => '',
            'number'          => 10,
            'offset'          => 0,
            'order_id'        => 0,
            'plan_id'         => 0,
            'customer_id'     => 0,
            'subscription_id' => 0,
            'transaction_id'  => 0,
            'order_type'      => '',
            'payment_method'  => '',
            'status'          => [],
            'coupon_code'     => '',
            'mode'            => '',
            'currency'        => '',
            'date_created'    => '',
            'date_completed'  => '',
            'start_date'      => '',
            'end_date'        => '',
            'date_compare'    => '=',
            'date_column'     => 'date_created',
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

        if ($args['order_id'] > 0) {
            $sql           .= " AND id = %d";
            $replacement[] = (int)$args['order_id'];
        }

        if ($args['plan_id'] > 0) {
            $sql           .= " AND plan_id = %d";
            $replacement[] = (int)$args['plan_id'];
        }

        if ($args['customer_id'] > 0) {
            $sql           .= " AND customer_id = %d";
            $replacement[] = (int)$args['customer_id'];
        }

        if ($args['subscription_id'] > 0) {
            $sql           .= " AND subscription_id = %d";
            $replacement[] = (int)$args['subscription_id'];
        }

        if ( ! empty($args['transaction_id'])) {
            $sql           .= " AND transaction_id = %s";
            $replacement[] = sanitize_text_field($args['transaction_id']);
        }

        $order_type = $args['order_type'];
        if ( ! empty($order_type) && in_array($order_type, array_keys(OrderType::get_all()))) {
            $sql           .= " AND order_type = %s";
            $replacement[] = $order_type;
        }

        if ( ! empty($args['payment_method'])) {
            $sql           .= " AND payment_method = %s";
            $replacement[] = $args['payment_method'];
        }

        $args['status'] = ! empty($args['status']) && is_string($args['status']) ? [$args['status']] : $args['status'];

        if (
            ! empty($args['status']) &&
            count(array_intersect($args['status'], array_keys(OrderStatus::get_all()))) == count($args['status'])
        ) {
            $sql         .= " AND status IN (" . implode(',', array_fill(0, count($args['status']), '%s')) . ") ";
            $replacement = array_merge($replacement, $args['status']);
        }

        if ( ! empty($args['coupon_code'])) {
            $sql           .= " AND coupon_code = %s";
            $replacement[] = sanitize_text_field($args['coupon_code']);
        }

        if ( ! empty($args['currency'])) {
            $sql           .= " AND currency = %s";
            $replacement[] = sanitize_text_field($args['currency']);
        }

        if ( ! empty($args['mode']) && in_array($args['mode'], array_keys(OrderMode::get_all()))) {
            $sql           .= " AND mode = %s";
            $replacement[] = $args['mode'];
        }

        if ( ! empty($args['date_created'])) {
            $sql           .= " AND date_created $date_compare %s";
            $replacement[] = gmdate('Y-m-d H:i:s', ppress_strtotime_utc($args['date_created']));
        }

        if ( ! empty($args['date_completed'])) {
            $sql           .= " AND date_created = %s";
            $replacement[] = gmdate('Y-m-d H:i:s', ppress_strtotime_utc($args['date_completed']));
        }

        $start_date  = $args['start_date'];
        $end_date    = $args['end_date'];
        $date_column = esc_sql($args['date_column']);

        if ( ! empty($start_date)) {
            $sql           .= " AND $date_column >= %s";
            $replacement[] = gmdate('Y-m-d H:i:s', ppress_strtotime_utc($start_date));
        }

        if ( ! empty($end_date)) {
            $sql           .= " AND $date_column <= %s";
            $replacement[] = gmdate('Y-m-d H:i:s', ppress_strtotime_utc($end_date));
        }

        if ( ! empty($search)) {

            if (is_numeric($search)) {
                $sql .= " AND (id = %d";
                $sql .= " OR plan_id = %d";
                $sql .= " OR customer_id = %d";
                $sql .= " OR subscription_id = %d)";

                $replacement[] = $search;
                $replacement[] = $search;
                $replacement[] = $search;
                $replacement[] = $search;
            } elseif (filter_var($search, FILTER_VALIDATE_EMAIL)) {
                $sql           .= " AND customer_id = (SELECT id FROM $customer_table WHERE user_id = (SELECT ID FROM $user_table WHERE user_email = %s))";
                $replacement[] = $search;
            } else {
                $sql .= " AND (order_key LIKE %s";
                $sql .= " OR transaction_id = %s";
                $sql .= " OR payment_method = %s";
                $sql .= " OR coupon_code = %s";
                $sql .= " OR ip_address = %s)";
                $sql .= " OR customer_id IN (SELECT id FROM $customer_table WHERE user_id IN (SELECT ID FROM $user_table WHERE user_nicename LIKE %s OR display_name LIKE %s))";

                $search_like = '%' . parent::wpdb()->esc_like(sanitize_text_field($search)) . '%';

                $replacement[] = parent::wpdb()->esc_like(sanitize_text_field($search)) . '%';
                $replacement[] = $search;
                $replacement[] = $search;
                $replacement[] = $search;
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
            return array_map([OrderFactory::class, 'make'], $result);
        }

        return [];
    }

    public function get_customer_total_spend($customer_id)
    {
        return $this->wpdb()->get_var(
            $this->wpdb()->prepare(
                "SELECT SUM(total) FROM $this->table WHERE customer_id = %d AND status = %s",
                $customer_id,
                OrderStatus::COMPLETED
            )
        );
    }

    /**
     * @param int $order_id
     * @param string $meta_key
     * @param string $meta_value
     * @param bool $unique
     *
     * @return int|false Meta ID on success, false on failure.
     */
    public function add_meta_data($order_id, $meta_key, $meta_value, $unique = false)
    {
        return add_metadata('ppress_order', $order_id, $meta_key, $meta_value, $unique);
    }

    /**
     * @param int $order_id
     * @param string $meta_key
     * @param string $meta_value
     * @param string $prev_value
     *
     * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
     */
    public function update_meta_data($order_id, $meta_key, $meta_value, $prev_value = '')
    {
        return update_metadata('ppress_order', $order_id, $meta_key, $meta_value, $prev_value);
    }

    /**
     * @param int $order_id
     * @param string $meta_key
     * @param string $meta_value
     * @param bool $delete_all
     *
     * @return bool True on success, false on failure.
     */
    public function delete_meta_data($order_id, $meta_key, $meta_value = '', $delete_all = false)
    {
        return delete_metadata('ppress_order', $order_id, $meta_key, $meta_value, $delete_all);
    }

    /**
     * @param $order_id
     *
     * @return bool
     */
    public function delete_all_meta_data($order_id)
    {
        return $this->wpdb()->delete(
            Base::order_meta_db_table(),
            array('ppress_order_id' => $order_id)
        );
    }

    /**
     * @param $order_id
     * @param string $meta_key
     * @param bool $single
     *
     * @return array|false|mixed
     */
    public function get_meta_data($order_id, $meta_key = '', $single = true)
    {
        return get_metadata('ppress_order', $order_id, $meta_key, $single);
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