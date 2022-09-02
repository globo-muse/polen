<?php

/**
 * Money Formatter
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 */

namespace ProfilePress\Core\Membership;

use ProfilePress\Core\Membership\Services\Calculator;

class CurrencyFormatter
{
    private $amount;

    /**
     * @var string Original, unmodified amount passed in via constructor.
     */
    private $original_amount;

    private $currency_code;

    private $symbol;

    private $decimal_separator;

    private $thousands_separator;

    private $number_decimals;

    private $currency_position;

    /**
     * @var string Symbol/text to display before amounts.
     */
    private $prefix = '';

    /**
     * @var string Symbol/text to display after amounts.
     */
    private $suffix = '';

    /**
     * CurrencyFormatter constructor.
     *
     * @param string $amount
     * @param string $currency_code
     */
    public function __construct($amount, $currency_code = '')
    {
        $this->amount = $this->original_amount = $amount;

        $this->currency_code = strtoupper(! empty($currency_code) ? $currency_code : ppress_get_currency());

        $this->symbol = html_entity_decode(ppress_get_currency_symbol($this->currency_code));

        $this->currency_position = apply_filters(
            'ppress_currency_position',
            ppress_get_setting('currency_position', 'left', true),
            $this->currency_code
        );

        $this->number_decimals = apply_filters(
            'ppress_currency_decimal_number',
            ppress_get_setting('currency_decimal_number', '2', true),
            $this->currency_code
        );

        $this->decimal_separator = apply_filters(
            'ppress_currency_decimal_separator',
            ppress_get_setting('currency_decimal_separator', '.', true),
            $this->currency_code
        );

        $this->thousands_separator = apply_filters(
            'ppress_currency_thousands_separator',
            ppress_get_setting('currency_thousand_separator', ',', true),
            $this->currency_code
        );

        switch ($this->currency_position) {
            case 'left_space':
                $this->prefix = $this->symbol . ' ';
                break;
            case 'left':
                $this->prefix = $this->symbol;
                break;
            case 'right_space':
                $this->suffix = ' ' . $this->symbol;
                break;
            case 'right':
                $this->suffix = $this->symbol;
                break;
        }

        $this->prefix = apply_filters('ppress_currency_prefix', $this->prefix, $this->currency_code);

        $this->suffix = apply_filters('ppress_currency_suffix', $this->suffix, $this->currency_code);
    }

    /**
     * Un-formats an amount.
     * This ensures the amount is put into a state where we can perform mathematical
     * operations on it --- that means using `.` as the decimal separator and no
     * thousands separator.
     *
     * @return string
     */
    private function unformat()
    {
        $amount = $this->amount;

        if (Calculator::init($amount)->isNegativeOrZero()) $amount = '0';

        $sep_found = strpos($amount, $this->decimal_separator);
        if (',' === $this->decimal_separator && false !== $sep_found) {
            $whole  = substr($amount, 0, $sep_found);
            $part   = substr($amount, $sep_found + 1, (strlen($amount) - 1));
            $amount = $whole . '.' . $part;
        }

        // Strip "," and " " from the amount (if set as the thousands separator).
        foreach ([',', ' '] as $thousands_separator) {
            if ($thousands_separator === $this->thousands_separator && false !== strpos($amount, $this->thousands_separator)) {
                $amount = str_replace($thousands_separator, '', $amount);
            }
        }

        // one last formatting check especially when amount contains currency
        $amount = preg_replace('/[^0-9\.]/', '', $amount);

        return $amount;
    }

    /**
     * Formats the amount for display.
     * Does not apply the currency code.
     *
     * @param bool $decimals
     *
     * @return self
     */
    public function format($decimals = true)
    {
        $amount = $this->unformat();

        if (empty($amount)) $amount = 0;

        $decimals = apply_filters('ppress_format_amount_decimals', $decimals ? $this->number_decimals : 0, $amount, $this->currency_code);

        // Format amount using decimals and separators (also rounds up or down)
        $formatted = number_format((float)$amount, $decimals, $this->decimal_separator, $this->thousands_separator);

        $this->amount = apply_filters('ppress_format_amount', $formatted, $amount, $decimals, $this->decimal_separator, $this->thousands_separator, $this->currency_code);

        return $this;
    }

    /**
     * Formats the amount for display.
     * Does not apply the currency code.
     *
     * @param bool $decimals
     *
     * @return self
     */
    public function sanitize($decimals = true)
    {
        $amount = $this->unformat();

        if (empty($amount)) $amount = 0;

        $decimals = (int)apply_filters('ppress_format_amount_decimals', $decimals ? $this->number_decimals : 0, $amount, $this->currency_code);

        $formatted = Calculator::init($amount)->toScale($decimals)->val();

        $this->amount = apply_filters('ppress_sanitize_amount', $formatted, $amount, $decimals, $this->decimal_separator, $this->thousands_separator, $this->currency_code);

        return $this;
    }

    /**
     * Applies the currency prefix/suffix to the amount.
     *
     * @return self
     */
    public function apply_symbol()
    {
        $amount      = $this->amount;
        $is_negative = is_numeric($this->amount) && $this->amount < 0;

        // Remove "-" from start.
        if ($is_negative) {
            $amount = substr($amount, 1);
        }

        $formatted = '';
        if ( ! empty($this->prefix)) {
            $formatted .= $this->prefix;
        }

        $formatted .= $amount;

        if ( ! empty($this->suffix)) {
            $formatted .= $this->suffix;
        }

        if ( ! empty($this->prefix)) {
            $formatted = apply_filters('ppress_' . strtolower($this->currency_code) . '_currency_filter_before', $formatted, $this->currency_code, $amount);
        }

        if ( ! empty($this->suffix)) {
            $formatted = apply_filters('ppress' . strtolower($this->currency_code) . '_currency_filter_after', $formatted, $this->currency_code, $amount);
        }

        // Add the "-" sign back to the start of the string.
        if ($is_negative) {
            $formatted = '-' . $formatted;
        }

        $this->amount = $formatted;

        return $this;
    }

    /**
     * Current working amount.
     *
     * @return mixed
     */
    public function val()
    {
        return (string)$this->amount;
    }
}
