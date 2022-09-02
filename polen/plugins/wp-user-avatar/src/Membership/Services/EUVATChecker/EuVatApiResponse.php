<?php

namespace ProfilePress\Core\Membership\Services\EUVATChecker;


class EuVatApiResponse
{
    const NO_VAT_NUMBER = 1;
    const NO_COUNTRY_CODE = 2;
    const INVALID_VAT_NUMBER = 3;
    const INVALID_COUNTRY_CODE = 4;
    const VAT_NUMBER_INVALID_FOR_COUNTRY = 5;
    const INVALID_INPUT = 6;
    const API_ERROR = 7;

    public $vat_number = '';

    public $country_code = '';

    public $valid = false;

    public $name = '';

    public $address = '';

    /**
     * The error code if there was an error.
     *
     * @var string
     */
    public $error;

    /**
     * Constructs a new result object for the supplied VAT number and country code.
     *
     * @param string $vat_number The VAT number the result applies to.
     * @param string $country_code The two letter country code.
     */
    public function __construct($vat_number, $country_code)
    {
        $this->vat_number   = $vat_number;
        $this->country_code = $country_code;
    }

    /**
     * Is the VAT number valid?
     */
    public function is_valid()
    {
        return (bool)$this->valid;
    }

    public function get_error_message()
    {
        switch ($this->error) {
            case self::NO_VAT_NUMBER:
                $error_message = __('Please enter a VAT number.', 'wp-user-avatar');
                break;
            case self::NO_COUNTRY_CODE:
                $error_message = __('Please select a country.', 'wp-user-avatar');
                break;
            case self::INVALID_VAT_NUMBER:
                $error_message = __('The VAT number is invalid.', 'wp-user-avatar');
                break;
            case self::INVALID_COUNTRY_CODE:
                $error_message = __('The VAT number applies to EU countries only.', 'wp-user-avatar');
                break;
            case self::VAT_NUMBER_INVALID_FOR_COUNTRY:
                $error_message = __('Your billing country must match the country for the VAT number.', 'wp-user-avatar');
                break;
            case self::INVALID_INPUT:
                $error_message = __('The country or VAT number is invalid.', 'wp-user-avatar');
                break;
            case self::API_ERROR:
                $error_message = __('We\'re having trouble checking your VAT number. Please try again or contact our support team.', 'wp-user-avatar');
                break;
            default:
                $error_message = $this->error;
        }

        return apply_filters('ppress_vat_error_code_to_string', $error_message, $this->error);
    }

    public function __toString()
    {
        $result = [$this->name, $this->address, $this->error];

        return implode("\r\n", array_filter($result));
    }

}
