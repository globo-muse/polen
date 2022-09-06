<?php

namespace ProfilePress\Core\Membership\Repositories;

use ProfilePress\Core\Base;
use ProfilePress\Core\Membership\Models\Coupon\CouponEntity;
use ProfilePress\Core\Membership\Models\Coupon\CouponFactory;
use ProfilePress\Core\Membership\Models\ModelInterface;

class CouponRepository extends BaseRepository
{
    protected $table;

    public function __construct()
    {
        $this->table = Base::coupons_db_table();
    }

    /**
     * @param CouponEntity $data
     *
     * @return false|int
     */
    public function add(ModelInterface $data)
    {
        $result = $this->wpdb()->insert(
            $this->table,
            array(
                'code'               => $data->code,
                'description'        => $data->description,
                'coupon_type'        => $data->coupon_type,
                'coupon_application' => $data->coupon_application,
                'amount'             => $data->amount,
                'unit'               => $data->unit,
                'plan_ids'           => $data->plan_ids,
                'usage_limit'        => $data->usage_limit,
                'status'             => $data->status,
                'start_date'         => $data->start_date,
                'end_date'           => $data->end_date
            ),
            array(
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
            )
        );

        return ! $result ? false : $this->wpdb()->insert_id;
    }

    /**
     * @param CouponEntity $data
     *
     * @return false|int
     */
    public function update(ModelInterface $data)
    {
        $result = $this->wpdb()->update(
            $this->table,
            [
                'code'               => $data->code,
                'description'        => $data->description,
                'coupon_type'        => $data->coupon_type,
                'coupon_application' => $data->coupon_application,
                'amount'             => $data->amount,
                'unit'               => $data->unit,
                'plan_ids'           => $data->plan_ids,
                'usage_limit'        => $data->usage_limit,
                'status'             => $data->status,
                'start_date'         => $data->start_date,
                'end_date'           => $data->end_date
            ],
            ['id' => $data->id],
            [
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
     * @param $code
     *
     * @return CouponEntity
     */
    public function retrieveByCode($code)
    {
        $result = $this->wpdb()->get_row(
            $this->wpdb()->prepare(
                "SELECT * FROM $this->table WHERE code = %s",
                $code
            ),
            ARRAY_A
        );

        if ( ! $result) $result = [];

        return CouponFactory::make($result);
    }

    /**
     * @param $id
     *
     * @return CouponEntity
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

        return CouponFactory::make($result);
    }

    /**
     * @param int $limit
     * @param int $current_page
     *
     * @return CouponEntity[]|array
     */
    public function retrieveAll($limit = 0, $current_page = 1)
    {
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

        $result = $this->wpdb()->get_results($this->wpdb()->prepare($sql, $replacement), 'ARRAY_A');

        if (is_array($result) && ! empty($result)) {
            return array_map([CouponFactory::class, 'make'], $result);
        }

        return [];
    }
}