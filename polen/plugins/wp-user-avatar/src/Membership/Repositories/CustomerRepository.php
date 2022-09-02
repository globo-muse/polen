<?php


namespace ProfilePress\Core\Membership\Repositories;

use ProfilePress\Core\Base;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Models\Customer\CustomerEntity;
use ProfilePress\Core\Membership\Models\Customer\CustomerStatus;
use ProfilePress\Core\Membership\Models\ModelInterface;
use ProfilePress\Core\Membership\Models\Subscription\SubscriptionStatus;

class CustomerRepository extends BaseRepository
{
    protected $table;

    public function __construct()
    {
        $this->table = Base::customers_db_table();
    }

    /**
     * @param CustomerEntity $data
     *
     * @return false|int
     */
    public function add(ModelInterface $data)
    {
        $result = $this->wpdb()->insert(
            $this->table,
            array(
                'user_id'        => $data->user_id,
                'private_note'   => $data->private_note,
                'total_spend'    => $data->total_spend,
                'purchase_count' => $data->purchase_count,
                'date_created'   => empty($data->date_created) ? current_time('mysql', true) : $data->date_created,
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%d',
                '%s'
            )
        );

        return ! $result ? false : $this->wpdb()->insert_id;
    }

    /**
     * @param CustomerEntity $data
     *
     * @return false|int
     */
    public function update(ModelInterface $data)
    {
        $result = $this->wpdb()->update(
            $this->table,
            [
                'user_id'        => $data->user_id,
                'private_note'   => $data->private_note,
                'total_spend'    => $data->total_spend,
                'purchase_count' => $data->purchase_count
            ],
            ['id' => $data->id],
            [
                '%d',
                '%s',
                '%s',
                '%d'
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

    /**
     * @param $id
     *
     * @return false|CustomerEntity
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

        return CustomerFactory::make($result);
    }

    /**
     * @param $user_id
     *
     * @return CustomerEntity
     */
    public function retrieveByUserID($user_id)
    {
        $result = $this->wpdb()->get_row(
            $this->wpdb()->prepare(
                "SELECT * FROM $this->table WHERE user_id = %d",
                $user_id
            ),
            ARRAY_A
        );

        if ( ! $result) $result = [];

        return CustomerFactory::make($result);
    }


    /**
     * @param $args
     * @param $count
     *
     * @return CustomerEntity[]|string
     */
    public function retrieveBy($args = array(), $count = false)
    {
        $defaults = [
            'customer_id'  => 0,
            'search'       => '',
            'number'       => 10,
            'offset'       => 0,
            'user_id'      => 0,
            'status'       => '',
            'date_created' => '',
            'start_date'   => '',
            'end_date'     => '',
            'date_compare' => '=',
            'date_column'  => 'date_created',
            'order'        => 'DESC',
            'orderby'      => 'id'
        ];

        $args = wp_parse_args($args, $defaults);

        $limit  = absint($args['number']);
        $offset = $args['offset'];
        $search = $args['search'];

        $sql = "SELECT DISTINCT customers.* FROM $this->table AS customers";

        if ($count === true) {
            $sql = "SELECT COUNT(DISTINCT customers.id) FROM $this->table AS customers";
        }

        if ( ! empty($args['status']) && in_array($args['status'], array_keys(CustomerStatus::get_all()))) {
            $subscriptions_table = Base::subscriptions_db_table();
            $sql                 .= " INNER JOIN $subscriptions_table subs ON customers.id = subs.customer_id";
        }

        $user_table = $this->wpdb()->users;

        $date_compare = ! empty($args['date_compare']) ? esc_sql($args['date_compare']) : '=';

        $replacement = [1];
        $sql         .= " WHERE 1=%d"; // fixes Notice: wpdb::prepare was called incorrectly. The query argument of wpdb::prepare() must have a placeholder


        if ($args['customer_id'] > 0) {
            $sql           .= " AND id = %d";
            $replacement[] = (int)$args['customer_id'];
        }

        if ( ! empty($args['status']) && in_array($args['status'], array_keys(CustomerStatus::get_all()))) {

            if (CustomerStatus::ACTIVE == $args['status']) {
                $sql .= " AND subs.status IN (%s, %s, %s)";
            } else {
                $sql .= " AND customers.id NOT IN (SELECT customer_id FROM $subscriptions_table WHERE status IN (%s, %s, %s))";
            }

            $replacement[] = SubscriptionStatus::ACTIVE;
            $replacement[] = SubscriptionStatus::TRIALLING;
            $replacement[] = SubscriptionStatus::COMPLETED;
        }

        if ($args['user_id'] > 0) {
            $sql           .= " AND user_id = %d";
            $replacement[] = (int)$args['user_id'];
        }

        if ( ! empty($args['date_created'])) {
            $sql           .= " AND date_created $date_compare %s";
            $replacement[] = gmdate('Y-m-d H:i:s', ppress_strtotime_utc($args['date_created']));
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
                $sql .= " OR user_id = %d)";

                $replacement[] = $search;
                $replacement[] = $search;

            } elseif (filter_var($search, FILTER_VALIDATE_EMAIL)) {
                $sql           .= " AND user_id = (SELECT ID FROM $user_table WHERE user_email = %s)";
                $replacement[] = $search;
            } else {
                $sql .= " AND user_id IN (SELECT ID FROM $user_table WHERE user_nicename LIKE %s OR display_name LIKE %s)";

                $search = '%' . parent::wpdb()->esc_like(sanitize_text_field($search)) . '%';

                $replacement[] = $search;
                $replacement[] = $search;
            }
        }

        $sql .= sprintf(" ORDER BY customers.%s %s", esc_sql($args['orderby']), esc_sql($args['order']));


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
            return array_map([CustomerFactory::class, 'make'], $result);
        }

        return [];
    }

    public function get_count_by_status($status)
    {
        return $this->retrieveBy(['status' => $status], true);
    }
}