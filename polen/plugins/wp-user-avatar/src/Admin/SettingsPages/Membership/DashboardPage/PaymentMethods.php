<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\DashboardPage;

use ProfilePress\Core\Membership\Models\Order\OrderStatus;

class PaymentMethods extends AbstractReport
{
    public function get_where_clause($groupBy = false, $start_date = '', $end_date = '')
    {
        $start_date = $this->start_date_carbon;
        $end_date   = $this->end_date_carbon;

        $sql = "date_created >= %s AND date_created <= %s";

        $variables = [
            $start_date->utc()->toDateTimeString(),
            $end_date->utc()->toDateTimeString()
        ];

        if ( ! empty($this->plan_id)) {
            $sql         .= " AND plan_id = %d";
            $variables[] = $this->plan_id;
        }

        $sql .= " GROUP BY payment_method ORDER BY sales DESC LIMIT 10";

        return $this->wpdb()->prepare($sql, $variables);
    }

    protected function get_query_data()
    {
        static $cache = null;

        if (is_null($cache)) {
            $cache = $this->wpdb()->get_results(
                $this->wpdb()->prepare(
                    "SELECT payment_method, SUM(total) as earnings, COUNT(id) as sales FROM {$this->db_table} WHERE status = %s AND {$this->get_where_clause()}",
                    OrderStatus::COMPLETED
                ),
                'ARRAY_A'
            );
        }

        return $cache;
    }

    public function get_interval_data($bucket, $payment_method)
    {
        return wp_list_filter($bucket, ['payment_method' => $payment_method]);
    }

    public function get_total($start_date = '', $end_date = '')
    {
        return null;
    }

    public function get_trend()
    {
        return 0;
    }

    public function get_labels()
    {
        static $labels = null;

        if (is_null($labels)) {

            $labels = [];

            $query_data = $this->get_query_data();

            if (is_array($query_data) && ! empty($query_data)) {
                foreach ($query_data as $datum) {
                    $payment_method          = $datum['payment_method'];
                    $labels[$payment_method] = ppress_get_payment_method($payment_method)->get_method_title();
                }
            }
        }

        return $labels;
    }

    public function get_data()
    {
        $query_data = $this->get_query_data();

        $sales_dataset    = [];
        $earnings_dataset = [];

        if (is_array($query_data) && ! empty($query_data)) {

            foreach ($this->get_labels() as $payment_method => $label) {

                $data = $this->get_interval_data($query_data, $payment_method);

                $data = reset($data);

                $sales_dataset[]    = $data['sales'];
                $earnings_dataset[] = ppress_sanitize_amount($data['earnings']);
            }
        }

        return [
            [
                'label'           => esc_html__('Sales', 'wp-user-avatar'),
                'data'            => $sales_dataset,
                'borderColor'     => 'rgb(54, 162, 235)',
                'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                'stack'           => 'combined',
                'type'            => 'bar',
                'yAxisID'         => 'y'
            ],
            [
                'label'           => esc_html__('Earnings', 'wp-user-avatar'),
                'data'            => $earnings_dataset,
                'borderColor'     => 'rgb(255, 99, 132)',
                'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                'stack'           => 'combined',
                'yAxisID'         => 'y1'
            ]
        ];
    }
}