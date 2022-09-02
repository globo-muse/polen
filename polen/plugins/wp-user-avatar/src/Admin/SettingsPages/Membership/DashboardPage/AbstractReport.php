<?php

namespace ProfilePress\Core\Admin\SettingsPages\Membership\DashboardPage;

use ProfilePress\Core\Base;
use ProfilePress\Core\Membership\Services\Calculator;
use ProfilePressVendor\Carbon\Carbon;
use ProfilePressVendor\Carbon\CarbonImmutable;
use ProfilePressVendor\Carbon\CarbonInterval;

abstract class AbstractReport
{
    protected $db_table;

    protected $start_date;
    protected $end_date;
    protected $plan_id;

    protected $start_date_carbon;
    protected $end_date_carbon;

    protected $interval;

    /**
     * @param ReportFilterData $filterData
     */
    public function __construct($filterData)
    {
        $this->db_table = Base::orders_db_table();

        $this->start_date = $filterData->start_date;
        $this->end_date   = $filterData->end_date;
        $this->plan_id    = $filterData->plan_id;

        $this->start_date_carbon = CarbonImmutable::createFromFormat('Y-m-d', $this->start_date, wp_timezone())->startOfDay();
        $this->end_date_carbon   = CarbonImmutable::createFromFormat('Y-m-d', $this->end_date, wp_timezone())->endOfDay();

        $diff = $this->start_date_carbon->diff($this->end_date_carbon);

        $this->interval = ReportInterval::HOURLY;

        // not using ->d here cos sometimes it can be 0
        // but days always have a value showing the difference between intervals in days
        if ($diff->days >= 1) {
            $this->interval = ReportInterval::DAILY;
        }

        if ($diff->m >= 2) {
            $this->interval = ReportInterval::MONTHLY;
        }

        if ($diff->y >= 3) {
            $this->interval = ReportInterval::YEARLY;
        }
    }

    public function wpdb()
    {
        global $wpdb;

        return $wpdb;
    }

    /**
     * @param bool $groupBy
     * @param Carbon $start_date
     * @param Carbon $end_date
     *
     * @return string|void
     */
    public function get_where_clause($groupBy = false, $start_date = '', $end_date = '')
    {
        $start_date = ! empty($start_date) ? $start_date : $this->start_date_carbon;
        $end_date   = ! empty($end_date) ? $end_date : $this->end_date_carbon;

        $sql = "date_created >= %s AND date_created <= %s";

        $variables = [
            $start_date->utc()->toDateTimeString(),
            $end_date->utc()->toDateTimeString()
        ];

        if ( ! empty($this->plan_id)) {
            $sql         .= " AND plan_id = %d";
            $variables[] = $this->plan_id;
        }

        if($groupBy) {
            $sql .= " GROUP BY order_time ORDER BY order_time ASC";
        }

        return $this->wpdb()->prepare($sql, $variables);
    }

    public function get_date_column()
    {
        $timezone_offset = (new \DateTime('now', wp_timezone()))->format('P');

        switch ($this->interval) {
            case ReportInterval::DAILY:
                $date_format = "DATE(CONVERT_TZ(date_created,'+00:00','$timezone_offset'))";
                break;
            case ReportInterval::MONTHLY:
                $date_format = "DATE_FORMAT(CONVERT_TZ(date_created,'+00:00','$timezone_offset'), '%Y-%m')";
                break;
            case ReportInterval::YEARLY:
                $date_format = "YEAR(CONVERT_TZ(date_created,'+00:00','$timezone_offset'))";
                break;
            default:
                $date_format = "DATE_FORMAT(CONVERT_TZ(date_created,'+00:00','$timezone_offset'), '%k')";
                break;
        }

        return "$date_format AS order_time";
    }

    public function get_interval_data($bucket, $datetime)
    {
        switch ($this->interval) {
            case $this->interval == ReportInterval::DAILY:
                $data = wp_list_filter($bucket, ['order_time' => CarbonImmutable::parse($datetime, wp_timezone())->toDateString()]);
                break;
            case $this->interval == ReportInterval::MONTHLY:
                $data = wp_list_filter($bucket, ['order_time' => CarbonImmutable::parse($datetime, wp_timezone())->rawFormat('Y-m')]);
                break;
            default:
                $data = wp_list_filter($bucket, ['order_time' => CarbonImmutable::parse($datetime, wp_timezone())->hour]);
        }

        return $data;
    }

    public function get_labels()
    {
        static $labels = null;

        if (is_null($labels)) {

            $start_date_carbon = $this->start_date_carbon;
            $end_date_carbon   = $this->end_date_carbon;

            switch ($this->interval) {
                case ReportInterval::DAILY:
                    $carbonInterval = CarbonInterval::day();
                    $dateFormat     = 'M j';
                    break;
                case ReportInterval::MONTHLY:
                    $carbonInterval = CarbonInterval::month();
                    $dateFormat     = 'M Y';
                    break;
                case ReportInterval::YEARLY:
                    $carbonInterval = CarbonInterval::year();
                    $dateFormat     = 'Y';
                    break;
                default:
                    $carbonInterval = CarbonInterval::hours();
                    $dateFormat     = 'g A';
                    break;
            }

            $labels = [];

            while ($start_date_carbon->lessThan($end_date_carbon)) {
                $labels[$start_date_carbon->toDateTimeString()] = $start_date_carbon->format($dateFormat);
                $start_date_carbon                              = $start_date_carbon->add($carbonInterval);
            }
        }

        // ensures labels are sorted from earliest to latest
        uasort($labels, function ($a, $b) {
            return (strtotime($a) > strtotime($b));
        });

        return $labels;
    }

    public function get_trend()
    {
        $interval = $this->start_date_carbon->diff($this->end_date_carbon);

        $prevStart = $this->start_date_carbon->sub($interval);

        $prevEnd = $this->start_date_carbon;

        $previous_total = $this->get_total($prevStart, $prevEnd);

        if (Calculator::init($previous_total)->isNegativeOrZero()) return 0;

        return Calculator::init($this->get_total())
                         ->minus($previous_total)
                         ->dividedBy($previous_total)
                         ->multipliedBy('100')
                         ->toScale(1)
                         ->val();
    }

    public function val()
    {
        return [
            'dataset' => $this->get_data(),
            'label'   => array_values($this->get_labels()),
            'total'   => $this->get_total(),
            'trend'   => $this->get_trend()
        ];
    }
}