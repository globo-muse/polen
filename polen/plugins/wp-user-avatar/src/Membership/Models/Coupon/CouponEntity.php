<?php

namespace ProfilePress\Core\Membership\Models\Coupon;

use ProfilePress\Core\Membership\Models\AbstractModel;
use ProfilePress\Core\Membership\Models\ModelInterface;
use ProfilePress\Core\Membership\Models\Order\OrderStatus;
use ProfilePress\Core\Membership\Models\Order\OrderType;
use ProfilePress\Core\Membership\Repositories\CouponRepository;
use ProfilePress\Core\Membership\Repositories\OrderRepository;
use ProfilePressVendor\Carbon\Carbon;
use ProfilePressVendor\Carbon\CarbonImmutable;

/**
 * @property int $id
 * @property string $code
 * @property string $description
 * @property string $coupon_type
 * @property string $coupon_application
 * @property string $amount
 * @property string $unit
 * @property array $plan_ids
 * @property int $usage_limit
 * @property string $status
 * @property string $start_date
 * @property string $end_date
 */
class CouponEntity extends AbstractModel implements ModelInterface
{
    protected $id = 0;

    protected $code = '';

    protected $description = '';

    protected $coupon_type = CouponType::RECURRING;

    protected $coupon_application = CouponApplication::NEW_PURCHASE;

    protected $amount = 0;

    protected $unit = CouponUnit::PERCENTAGE;

    protected $plan_ids = [];

    protected $usage_limit = '';

    protected $status = 'true';

    protected $start_date = '';

    protected $end_date = '';

    public function __construct($data = [])
    {
        if (is_array($data) && ! empty($data)) {

            foreach ($data as $key => $value) {
                $this->$key = $value;

                if ($key == 'plan_ids') {
                    $this->plan_ids = ! empty($value) && ppress_is_json($value) ? \json_decode($value, true) : [];
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return ! empty($this->id);
    }

    public function is_active()
    {
        return $this->status == 'true';
    }

    public function is_recurring()
    {
        return $this->get_coupon_type() == CouponType::RECURRING;
    }

    public function is_expired()
    {
        $end_date = $this->get_end_date();

        if ( ! empty($end_date) && time() > CarbonImmutable::parse($end_date, 'UTC')->endOfDay()->getTimestamp()) {
            return true;
        }

        return false;
    }

    protected function set_coupon_application($val)
    {
        $valid_options = (new \ReflectionClass(CouponApplication::class))->getConstants();

        if (in_array($val, $valid_options)) {
            $this->coupon_application = $val;
        }
    }

    public function get_coupon_type()
    {
        return ! empty($this->coupon_type) ? $this->coupon_type : CouponType::RECURRING;
    }

    public function get_coupon_application()
    {
        return ! empty($this->coupon_application) ? $this->coupon_application : CouponApplication::NEW_PURCHASE;
    }

    public function get_id()
    {
        return absint($this->id);
    }

    /**
     * @return string
     */
    public function get_description()
    {
        return sanitize_textarea_field($this->description);
    }

    /**
     * @return string
     */
    public function get_amount()
    {
        return ! empty($this->amount) ? (float)$this->amount : '';
    }

    protected function set_plan_ids($value)
    {
        $this->plan_ids = is_array($value) ? \wp_json_encode($value) : $value;
    }

    public function get_plan_ids()
    {
        return $this->plan_ids;
    }

    public function get_usage_limit()
    {
        return ! empty($this->usage_limit) ? absint($this->usage_limit) : '';
    }

    public function get_start_date()
    {
        return ! empty($this->start_date) && (string)$this->start_date != '0000-00-00' ? (string)$this->start_date : '';
    }

    public function get_end_date()
    {
        return ! empty($this->end_date) && (string)$this->end_date != '0000-00-00' ? (string)$this->end_date : '';
    }

    /**
     * Check if a coupon is valid
     *
     * @param int $plan_id
     * @param string $order_type
     *
     * @return bool
     */
    public function is_valid($plan_id = 0, $order_type = OrderType::NEW_ORDER)
    {
        if ( ! $this->is_active()) return false;

        if (
            $order_type == OrderType::NEW_ORDER &&
            $this->get_coupon_application() == CouponApplication::EXISTING_PURCHASE
        ) {
            return false;
        }

        if (
            $order_type != OrderType::NEW_ORDER &&
            $this->get_coupon_application() == CouponApplication::NEW_PURCHASE
        ) {
            return false;
        }

        $start_date = ! empty($this->get_start_date()) ? $this->get_start_date() . ' 00:00:00' : '';
        $end_date   = ! empty($this->get_end_date()) ? $this->get_end_date() . ' 23:59:59' : '';

        if ( ! empty($start_date) && empty($end_date)) {
            $result = (new Carbon($start_date, wp_timezone()))
                ->lessThanOrEqualTo(Carbon::now(wp_timezone()));

            if ( ! $result) return false;
        }

        if ( ! empty($end_date) && empty($start_date)) {
            $result = (new Carbon($end_date, wp_timezone()))
                ->greaterThanOrEqualTo(Carbon::now(wp_timezone()));

            if ( ! $result) return false;
        }

        if ( ! empty($start_date) && ! empty($end_date)) {
            $result = Carbon::now(wp_timezone())->isBetween(
                new Carbon($start_date, wp_timezone()),
                new Carbon($end_date, wp_timezone())
            );

            if ( ! $result) return false;
        }

        if ( ! empty($plan_id) && ! empty($this->get_plan_ids()) && ! in_array($plan_id, $this->get_plan_ids())) {
            return false;
        }

        $usage_limit = absint($this->get_usage_limit());
        $usage_count = absint($this->get_usage_count());

        if ($usage_limit > 0 && $usage_count >= $usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Return how many times coupon has been used. update this after every successful order
     *
     * @return int
     */
    public function get_usage_count()
    {
        return OrderRepository::init()->retrieveBy([
            'status'      => [OrderStatus::COMPLETED, OrderStatus::REFUNDED],
            'coupon_code' => $this->code,
            'number'      => 0
        ], true);
    }

    /**
     * @return false|int
     */
    public function save()
    {
        if ($this->exists()) {

            $result = CouponRepository::init()->update($this);

            do_action('ppress_membership_update_coupon', $result, $this);

            return $result;
        }

        $result = CouponRepository::init()->add($this);

        do_action('ppress_membership_add_coupon', $result, $this);

        return $result;
    }

    /**
     * @return false|int
     */
    public function activate()
    {
        return CouponRepository::init()->updateColumn($this->id, 'status', 'true');
    }

    /**
     * @return false|int
     */
    public function deactivate()
    {
        return CouponRepository::init()->updateColumn($this->id, 'status', 'false');
    }
}