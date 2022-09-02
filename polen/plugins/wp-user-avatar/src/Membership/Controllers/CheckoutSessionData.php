<?php

namespace ProfilePress\Core\Membership\Controllers;

class CheckoutSessionData
{
    const COUPON_CODE = 'ppress_checkout_coupon_code';
    const TAX_RATE = 'ppress_checkout_tax_rate';
    const EU_VAT_NUMBER = 'ppress_checkout_eu_vat_number';

    /**
     * @param $plan_id
     *
     * @return false|string
     */
    public static function get_coupon_code($plan_id)
    {
        $val = ppress_session()->get(CheckoutSessionData::COUPON_CODE);

        if (isset($val['plan_id']) && $plan_id == $val['plan_id']) {
            return sanitize_text_field($val['coupon_code']);
        }

        return false;
    }

    public static function get_tax_rate($plan_id)
    {
        $val = ppress_session()->get(CheckoutSessionData::TAX_RATE);

        if (isset($val['plan_id']) && $plan_id == $val['plan_id']) {
            return sanitize_text_field($val['tax_rate']);
        }

        return '0';
    }

    public static function get_tax_country($plan_id)
    {
        $val = ppress_session()->get(CheckoutSessionData::TAX_RATE);

        if (isset($val['plan_id']) && $plan_id == $val['plan_id']) {
            return sanitize_text_field($val['country']);
        }

        return '';
    }

    public static function get_tax_state($plan_id)
    {
        $val = ppress_session()->get(CheckoutSessionData::TAX_RATE);

        if (isset($val['plan_id']) && $plan_id == $val['plan_id']) {
            return sanitize_text_field($val['state']);
        }

        return '';
    }

    /**
     * @param $plan_id
     * @param $vat_number
     *
     * @return mixed
     */
    public static function get_eu_vat_number_details($plan_id, $vat_number)
    {
        $val = ppress_session()->get(CheckoutSessionData::EU_VAT_NUMBER);

        if (isset($val['plan_id'], $val['vat_number']) && $plan_id == $val['plan_id'] && $vat_number == $val['vat_number']) {
            return $val;
        }

        return false;
    }
}