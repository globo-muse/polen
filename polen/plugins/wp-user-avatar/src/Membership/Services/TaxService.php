<?php

namespace ProfilePress\Core\Membership\Services;

use ProfilePress\Core\Membership\Models\Order\OrderFactory;

class TaxService
{
    protected $tax_options = [];

    public function __construct()
    {
        $this->tax_options = get_option(PPRESS_TAXES_OPTION_NAME, []);
    }

    public function is_tax_enabled()
    {
        return ppress_var($this->tax_options, 'enable_tax') == 'true';
    }

    public function get_tax_label($country)
    {
        return $this->is_eu_countries($country) ? esc_html__('VAT', 'wp-user-avatar') : esc_html__('Tax', 'wp-user-avatar');
    }

    public function is_eu_vat_enabled()
    {
        return ppress_var($this->tax_options, 'enable_eu_vat') == 'true';
    }

    public function is_price_inclusive_tax()
    {
        return ppress_var($this->tax_options, 'prices_include_tax', 'no') == 'yes';
    }

    public function is_vat_number_validation_active()
    {
        return ppress_var($this->tax_options, 'eu_vat_disable_validation', 'false') == 'false';
    }

    public function calculate_tax_based_on_setting()
    {
        return ppress_var($this->tax_options, 'tax_based_on', 'billing');
    }

    /**
     * @return string
     */
    public function eu_vat_same_country_rule_setting()
    {
        return ppress_var($this->tax_options, 'eu_vat_same_country_rule', 'excl');
    }

    public function get_tax_rates()
    {
        return ppress_var($this->tax_options, 'tax_rates', []);
    }

    public function get_eu_countries()
    {
        return array_keys(TaxService::init()->get_eu_vat_rates());
    }

    public function is_eu_countries($country)
    {
        return in_array($country, $this->get_eu_countries(), true);
    }

    public function get_eu_vat_rates()
    {
        static $cache = null;

        if (is_null($cache)) {

            $rates = file_get_contents(PROFILEPRESS_SRC . 'eu-vat-rates.json');

            if ( ! $rates) return [];

            $rates = json_decode($rates, true);

            unset($rates['GB']);

            $cache = $rates;
        }

        return $cache;
    }

    public function get_fallback_tax_rate()
    {
        return ppress_var($this->tax_options, 'fallback_tax_rate');
    }

    public function get_vat_number_field_label($default = '')
    {
        return ppress_var($this->tax_options, 'eu_vat_number_label', $default, true);
    }

    /**
     * @param $country
     * @param $state
     *
     * @return float|int|string
     */
    public function get_country_tax_rate($country, $state = '')
    {
        $rate = self::get_fallback_tax_rate();

        // get eu rate if found
        if (self::is_eu_vat_enabled() && in_array($country, self::get_eu_countries(), true)) {
            $rate = ppress_var(self::get_eu_vat_rates(), $country);
        }

        // get global country rate to override eu rate where possible
        $result = wp_list_filter(self::get_tax_rates(), ['country' => $country, 'global' => '1']);

        // if state is specified, find state specific rate and override global country rate where possible
        if ( ! empty($state)) {
            $result2 = wp_list_filter(self::get_tax_rates(), ['country' => $country, 'state' => $state, 'global' => '0']);
            if ( ! empty($result2)) $result = $result2;
        }

        if ( ! empty($result)) {
            $rate = ppress_var(reset($result), 'rate');
        }

        if ( ! is_numeric($rate)) $rate = 0;

        return $rate;
    }

    public function is_reverse_charged($order_id)
    {
        $order_data = OrderFactory::fromId($order_id);

        return $order_data->get_meta($order_data::EU_VAT_IS_REVERSE_CHARGED) == 'true';
    }

    /**
     * @return self
     */
    public static function init()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}