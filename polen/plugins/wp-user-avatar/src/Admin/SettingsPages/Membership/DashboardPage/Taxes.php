<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\DashboardPage;

use ProfilePress\Core\Membership\Models\Order\OrderStatus;

class Taxes extends AbstractReport
{
    public function get_total($start_date = '', $end_date = '')
    {
        static $bucket = [];

        $cache_key = md5('taxes_get_total' . $start_date . $end_date);

        if ( ! isset($bucket[$cache_key])) {
            $start_date = ! empty($start_date) ? $start_date : '';
            $end_date   = ! empty($end_date) ? $end_date : '';

            $where_clause = $this->get_where_clause(false, $start_date, $end_date);

            $bucket[$cache_key] = $this->wpdb()->get_var(
                $this->wpdb()->prepare(
                    "SELECT SUM(tax) FROM {$this->db_table} WHERE status = %s AND $where_clause", OrderStatus::COMPLETED
                )
            );
        }

        return ! $bucket[$cache_key] ? 0 : $bucket[$cache_key];
    }

    public function get_data()
    {
        $result = $this->wpdb()->get_results(
            $this->wpdb()->prepare(
                "SELECT SUM(tax) AS order_total, {$this->get_date_column()} FROM {$this->db_table} WHERE status = %s AND {$this->get_where_clause($groupBy=true)}",
                OrderStatus::COMPLETED
            ),
            'ARRAY_A'
        );

        $dataset_data = [];

        foreach ($this->get_labels() as $datetime => $label) {

            $data = $this->get_interval_data($result, $datetime);

            $dataset_data[] = ! empty($data) ? ppress_sanitize_amount(reset($data)['order_total']) : 0;
        }

        return [
            [
                'data'            => $dataset_data,
                'borderColor'     => 'rgb(54, 162, 235)',
                'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                'fill'            => true
            ]
        ];
    }
}