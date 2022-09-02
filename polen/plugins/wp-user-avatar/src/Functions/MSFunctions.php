<?php

use ProfilePress\Core\Base;
use ProfilePress\Core\Classes\PPRESS_Session;
use ProfilePress\Core\Membership\CurrencyFormatter;
use ProfilePress\Core\Membership\Models\Order\OrderMode;
use ProfilePress\Core\Membership\Models\Plan\PlanFactory;
use ProfilePress\Core\Membership\Models\Plan\PlanEntity;
use ProfilePress\Core\Membership\PaymentMethods\PaymentMethods;

/**
 * @param $plan_id
 *
 * @return PlanEntity
 */
function ppress_get_plan($plan_id)
{
    return PlanFactory::fromId($plan_id);
}

function ppress_get_payment_method($payment_method_id)
{
    return PaymentMethods::get_instance()->get_by_id($payment_method_id);
}

function ppress_is_any_active_plan()
{
    global $wpdb;

    $table = Base::subscription_plans_db_table();

    return absint($wpdb->get_var("SELECT COUNT(id) FROM $table WHERE status = 'true'")) > 0;
}

function ppress_is_any_active_coupon()
{
    global $wpdb;

    $table = Base::coupons_db_table();

    return absint($wpdb->get_var("SELECT COUNT(id) FROM $table WHERE status = 'true'")) > 0;
}

function ppress_get_currency()
{
    return apply_filters('ppress_currency', ppress_settings_by_key('payment_currency', 'USD', true));
}

/**
 * Get full list of currency codes.
 *
 * Currency symbols and names should follow the Unicode CLDR recommendation (http://cldr.unicode.org/translation/currency-names)
 *
 * @return array
 */
function ppress_get_currencies()
{
    static $currencies;

    if ( ! isset($currencies)) {
        $currencies = array_unique(
            apply_filters(
                'ppress_currencies',
                [
                    'USD' => __('United States (US) dollar', 'wp-user-avatar'),
                    'EUR' => __('Euro', 'wp-user-avatar'),
                    'GBP' => __('Pound sterling', 'wp-user-avatar'),
                    'AED' => __('United Arab Emirates dirham', 'wp-user-avatar'),
                    'AFN' => __('Afghan afghani', 'wp-user-avatar'),
                    'ALL' => __('Albanian lek', 'wp-user-avatar'),
                    'AMD' => __('Armenian dram', 'wp-user-avatar'),
                    'ANG' => __('Netherlands Antillean guilder', 'wp-user-avatar'),
                    'AOA' => __('Angolan kwanza', 'wp-user-avatar'),
                    'ARS' => __('Argentine peso', 'wp-user-avatar'),
                    'AUD' => __('Australian dollar', 'wp-user-avatar'),
                    'AWG' => __('Aruban florin', 'wp-user-avatar'),
                    'AZN' => __('Azerbaijani manat', 'wp-user-avatar'),
                    'BAM' => __('Bosnia and Herzegovina convertible mark', 'wp-user-avatar'),
                    'BBD' => __('Barbadian dollar', 'wp-user-avatar'),
                    'BDT' => __('Bangladeshi taka', 'wp-user-avatar'),
                    'BGN' => __('Bulgarian lev', 'wp-user-avatar'),
                    'BHD' => __('Bahraini dinar', 'wp-user-avatar'),
                    'BIF' => __('Burundian franc', 'wp-user-avatar'),
                    'BMD' => __('Bermudian dollar', 'wp-user-avatar'),
                    'BND' => __('Brunei dollar', 'wp-user-avatar'),
                    'BOB' => __('Bolivian boliviano', 'wp-user-avatar'),
                    'BRL' => __('Brazilian real', 'wp-user-avatar'),
                    'BSD' => __('Bahamian dollar', 'wp-user-avatar'),
                    'BTC' => __('Bitcoin', 'wp-user-avatar'),
                    'BTN' => __('Bhutanese ngultrum', 'wp-user-avatar'),
                    'BWP' => __('Botswana pula', 'wp-user-avatar'),
                    'BYR' => __('Belarusian ruble (old)', 'wp-user-avatar'),
                    'BYN' => __('Belarusian ruble', 'wp-user-avatar'),
                    'BZD' => __('Belize dollar', 'wp-user-avatar'),
                    'CAD' => __('Canadian dollar', 'wp-user-avatar'),
                    'CDF' => __('Congolese franc', 'wp-user-avatar'),
                    'CHF' => __('Swiss franc', 'wp-user-avatar'),
                    'CLP' => __('Chilean peso', 'wp-user-avatar'),
                    'CNY' => __('Chinese yuan', 'wp-user-avatar'),
                    'COP' => __('Colombian peso', 'wp-user-avatar'),
                    'CRC' => __('Costa Rican col&oacute;n', 'wp-user-avatar'),
                    'CUC' => __('Cuban convertible peso', 'wp-user-avatar'),
                    'CUP' => __('Cuban peso', 'wp-user-avatar'),
                    'CVE' => __('Cape Verdean escudo', 'wp-user-avatar'),
                    'CZK' => __('Czech koruna', 'wp-user-avatar'),
                    'DJF' => __('Djiboutian franc', 'wp-user-avatar'),
                    'DKK' => __('Danish krone', 'wp-user-avatar'),
                    'DOP' => __('Dominican peso', 'wp-user-avatar'),
                    'DZD' => __('Algerian dinar', 'wp-user-avatar'),
                    'EGP' => __('Egyptian pound', 'wp-user-avatar'),
                    'ERN' => __('Eritrean nakfa', 'wp-user-avatar'),
                    'ETB' => __('Ethiopian birr', 'wp-user-avatar'),
                    'FJD' => __('Fijian dollar', 'wp-user-avatar'),
                    'FKP' => __('Falkland Islands pound', 'wp-user-avatar'),
                    'GEL' => __('Georgian lari', 'wp-user-avatar'),
                    'GGP' => __('Guernsey pound', 'wp-user-avatar'),
                    'GHS' => __('Ghana cedi', 'wp-user-avatar'),
                    'GIP' => __('Gibraltar pound', 'wp-user-avatar'),
                    'GMD' => __('Gambian dalasi', 'wp-user-avatar'),
                    'GNF' => __('Guinean franc', 'wp-user-avatar'),
                    'GTQ' => __('Guatemalan quetzal', 'wp-user-avatar'),
                    'GYD' => __('Guyanese dollar', 'wp-user-avatar'),
                    'HKD' => __('Hong Kong dollar', 'wp-user-avatar'),
                    'HNL' => __('Honduran lempira', 'wp-user-avatar'),
                    'HRK' => __('Croatian kuna', 'wp-user-avatar'),
                    'HTG' => __('Haitian gourde', 'wp-user-avatar'),
                    'HUF' => __('Hungarian forint', 'wp-user-avatar'),
                    'IDR' => __('Indonesian rupiah', 'wp-user-avatar'),
                    'ILS' => __('Israeli new shekel', 'wp-user-avatar'),
                    'IMP' => __('Manx pound', 'wp-user-avatar'),
                    'INR' => __('Indian rupee', 'wp-user-avatar'),
                    'IQD' => __('Iraqi dinar', 'wp-user-avatar'),
                    'IRR' => __('Iranian rial', 'wp-user-avatar'),
                    'IRT' => __('Iranian toman', 'wp-user-avatar'),
                    'ISK' => __('Icelandic kr&oacute;na', 'wp-user-avatar'),
                    'JEP' => __('Jersey pound', 'wp-user-avatar'),
                    'JMD' => __('Jamaican dollar', 'wp-user-avatar'),
                    'JOD' => __('Jordanian dinar', 'wp-user-avatar'),
                    'JPY' => __('Japanese yen', 'wp-user-avatar'),
                    'KES' => __('Kenyan shilling', 'wp-user-avatar'),
                    'KGS' => __('Kyrgyzstani som', 'wp-user-avatar'),
                    'KHR' => __('Cambodian riel', 'wp-user-avatar'),
                    'KMF' => __('Comorian franc', 'wp-user-avatar'),
                    'KPW' => __('North Korean won', 'wp-user-avatar'),
                    'KRW' => __('South Korean won', 'wp-user-avatar'),
                    'KWD' => __('Kuwaiti dinar', 'wp-user-avatar'),
                    'KYD' => __('Cayman Islands dollar', 'wp-user-avatar'),
                    'KZT' => __('Kazakhstani tenge', 'wp-user-avatar'),
                    'LAK' => __('Lao kip', 'wp-user-avatar'),
                    'LBP' => __('Lebanese pound', 'wp-user-avatar'),
                    'LKR' => __('Sri Lankan rupee', 'wp-user-avatar'),
                    'LRD' => __('Liberian dollar', 'wp-user-avatar'),
                    'LSL' => __('Lesotho loti', 'wp-user-avatar'),
                    'LYD' => __('Libyan dinar', 'wp-user-avatar'),
                    'MAD' => __('Moroccan dirham', 'wp-user-avatar'),
                    'MDL' => __('Moldovan leu', 'wp-user-avatar'),
                    'MGA' => __('Malagasy ariary', 'wp-user-avatar'),
                    'MKD' => __('Macedonian denar', 'wp-user-avatar'),
                    'MMK' => __('Burmese kyat', 'wp-user-avatar'),
                    'MNT' => __('Mongolian t&ouml;gr&ouml;g', 'wp-user-avatar'),
                    'MOP' => __('Macanese pataca', 'wp-user-avatar'),
                    'MRU' => __('Mauritanian ouguiya', 'wp-user-avatar'),
                    'MUR' => __('Mauritian rupee', 'wp-user-avatar'),
                    'MVR' => __('Maldivian rufiyaa', 'wp-user-avatar'),
                    'MWK' => __('Malawian kwacha', 'wp-user-avatar'),
                    'MXN' => __('Mexican peso', 'wp-user-avatar'),
                    'MYR' => __('Malaysian ringgit', 'wp-user-avatar'),
                    'MZN' => __('Mozambican metical', 'wp-user-avatar'),
                    'NAD' => __('Namibian dollar', 'wp-user-avatar'),
                    'NGN' => __('Nigerian naira', 'wp-user-avatar'),
                    'NIO' => __('Nicaraguan c&oacute;rdoba', 'wp-user-avatar'),
                    'NOK' => __('Norwegian krone', 'wp-user-avatar'),
                    'NPR' => __('Nepalese rupee', 'wp-user-avatar'),
                    'NZD' => __('New Zealand dollar', 'wp-user-avatar'),
                    'OMR' => __('Omani rial', 'wp-user-avatar'),
                    'PAB' => __('Panamanian balboa', 'wp-user-avatar'),
                    'PEN' => __('Sol', 'wp-user-avatar'),
                    'PGK' => __('Papua New Guinean kina', 'wp-user-avatar'),
                    'PHP' => __('Philippine peso', 'wp-user-avatar'),
                    'PKR' => __('Pakistani rupee', 'wp-user-avatar'),
                    'PLN' => __('Polish z&#x142;oty', 'wp-user-avatar'),
                    'PRB' => __('Transnistrian ruble', 'wp-user-avatar'),
                    'PYG' => __('Paraguayan guaran&iacute;', 'wp-user-avatar'),
                    'QAR' => __('Qatari riyal', 'wp-user-avatar'),
                    'RON' => __('Romanian leu', 'wp-user-avatar'),
                    'RSD' => __('Serbian dinar', 'wp-user-avatar'),
                    'RUB' => __('Russian ruble', 'wp-user-avatar'),
                    'RWF' => __('Rwandan franc', 'wp-user-avatar'),
                    'SAR' => __('Saudi riyal', 'wp-user-avatar'),
                    'SBD' => __('Solomon Islands dollar', 'wp-user-avatar'),
                    'SCR' => __('Seychellois rupee', 'wp-user-avatar'),
                    'SDG' => __('Sudanese pound', 'wp-user-avatar'),
                    'SEK' => __('Swedish krona', 'wp-user-avatar'),
                    'SGD' => __('Singapore dollar', 'wp-user-avatar'),
                    'SHP' => __('Saint Helena pound', 'wp-user-avatar'),
                    'SLL' => __('Sierra Leonean leone', 'wp-user-avatar'),
                    'SOS' => __('Somali shilling', 'wp-user-avatar'),
                    'SRD' => __('Surinamese dollar', 'wp-user-avatar'),
                    'SSP' => __('South Sudanese pound', 'wp-user-avatar'),
                    'STN' => __('S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'wp-user-avatar'),
                    'SYP' => __('Syrian pound', 'wp-user-avatar'),
                    'SZL' => __('Swazi lilangeni', 'wp-user-avatar'),
                    'THB' => __('Thai baht', 'wp-user-avatar'),
                    'TJS' => __('Tajikistani somoni', 'wp-user-avatar'),
                    'TMT' => __('Turkmenistan manat', 'wp-user-avatar'),
                    'TND' => __('Tunisian dinar', 'wp-user-avatar'),
                    'TOP' => __('Tongan pa&#x2bb;anga', 'wp-user-avatar'),
                    'TRY' => __('Turkish lira', 'wp-user-avatar'),
                    'TTD' => __('Trinidad and Tobago dollar', 'wp-user-avatar'),
                    'TWD' => __('New Taiwan dollar', 'wp-user-avatar'),
                    'TZS' => __('Tanzanian shilling', 'wp-user-avatar'),
                    'UAH' => __('Ukrainian hryvnia', 'wp-user-avatar'),
                    'UGX' => __('Ugandan shilling', 'wp-user-avatar'),
                    'UYU' => __('Uruguayan peso', 'wp-user-avatar'),
                    'UZS' => __('Uzbekistani som', 'wp-user-avatar'),
                    'VEF' => __('Venezuelan bol&iacute;var', 'wp-user-avatar'),
                    'VES' => __('Bol&iacute;var soberano', 'wp-user-avatar'),
                    'VND' => __('Vietnamese &#x111;&#x1ed3;ng', 'wp-user-avatar'),
                    'VUV' => __('Vanuatu vatu', 'wp-user-avatar'),
                    'WST' => __('Samoan t&#x101;l&#x101;', 'wp-user-avatar'),
                    'XAF' => __('Central African CFA franc', 'wp-user-avatar'),
                    'XCD' => __('East Caribbean dollar', 'wp-user-avatar'),
                    'XOF' => __('West African CFA franc', 'wp-user-avatar'),
                    'XPF' => __('CFP franc', 'wp-user-avatar'),
                    'YER' => __('Yemeni rial', 'wp-user-avatar'),
                    'ZAR' => __('South African rand', 'wp-user-avatar'),
                    'ZMW' => __('Zambian kwacha', 'wp-user-avatar'),
                ]
            )
        );
    }

    return array_map('ucwords', $currencies);
}

/**
 * Get all available Currency symbols.
 *
 * Currency symbols and names should follow the Unicode CLDR recommendation (http://cldr.unicode.org/translation/currency-names)
 *
 * @return array
 */
function ppress_get_currency_symbols()
{
    $symbols = apply_filters(
        'ppress_currency_symbols',
        array(
            'AED' => '&#x62f;.&#x625;',
            'AFN' => '&#x60b;',
            'ALL' => 'L',
            'AMD' => 'AMD',
            'ANG' => '&fnof;',
            'AOA' => 'Kz',
            'ARS' => '&#36;',
            'AUD' => '&#36;',
            'AWG' => 'Afl.',
            'AZN' => 'AZN',
            'BAM' => 'KM',
            'BBD' => '&#36;',
            'BDT' => '&#2547;&nbsp;',
            'BGN' => '&#1083;&#1074;.',
            'BHD' => '.&#x62f;.&#x628;',
            'BIF' => 'Fr',
            'BMD' => '&#36;',
            'BND' => '&#36;',
            'BOB' => 'Bs.',
            'BRL' => '&#82;&#36;',
            'BSD' => '&#36;',
            'BTC' => '&#3647;',
            'BTN' => 'Nu.',
            'BWP' => 'P',
            'BYR' => 'Br',
            'BYN' => 'Br',
            'BZD' => '&#36;',
            'CAD' => '&#36;',
            'CDF' => 'Fr',
            'CHF' => '&#67;&#72;&#70;',
            'CLP' => '&#36;',
            'CNY' => '&yen;',
            'COP' => '&#36;',
            'CRC' => '&#x20a1;',
            'CUC' => '&#36;',
            'CUP' => '&#36;',
            'CVE' => '&#36;',
            'CZK' => '&#75;&#269;',
            'DJF' => 'Fr',
            'DKK' => 'DKK',
            'DOP' => 'RD&#36;',
            'DZD' => '&#x62f;.&#x62c;',
            'EGP' => 'EGP',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'EUR' => '&euro;',
            'FJD' => '&#36;',
            'FKP' => '&pound;',
            'GBP' => '&pound;',
            'GEL' => '&#x20be;',
            'GGP' => '&pound;',
            'GHS' => '&#x20b5;',
            'GIP' => '&pound;',
            'GMD' => 'D',
            'GNF' => 'Fr',
            'GTQ' => 'Q',
            'GYD' => '&#36;',
            'HKD' => '&#36;',
            'HNL' => 'L',
            'HRK' => 'kn',
            'HTG' => 'G',
            'HUF' => '&#70;&#116;',
            'IDR' => 'Rp',
            'ILS' => '&#8362;',
            'IMP' => '&pound;',
            'INR' => '&#8377;',
            'IQD' => '&#x62f;.&#x639;',
            'IRR' => '&#xfdfc;',
            'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
            'ISK' => 'kr.',
            'JEP' => '&pound;',
            'JMD' => '&#36;',
            'JOD' => '&#x62f;.&#x627;',
            'JPY' => '&yen;',
            'KES' => 'KSh',
            'KGS' => '&#x441;&#x43e;&#x43c;',
            'KHR' => '&#x17db;',
            'KMF' => 'Fr',
            'KPW' => '&#x20a9;',
            'KRW' => '&#8361;',
            'KWD' => '&#x62f;.&#x643;',
            'KYD' => '&#36;',
            'KZT' => '&#8376;',
            'LAK' => '&#8365;',
            'LBP' => '&#x644;.&#x644;',
            'LKR' => '&#xdbb;&#xdd4;',
            'LRD' => '&#36;',
            'LSL' => 'L',
            'LYD' => '&#x644;.&#x62f;',
            'MAD' => '&#x62f;.&#x645;.',
            'MDL' => 'MDL',
            'MGA' => 'Ar',
            'MKD' => '&#x434;&#x435;&#x43d;',
            'MMK' => 'Ks',
            'MNT' => '&#x20ae;',
            'MOP' => 'P',
            'MRU' => 'UM',
            'MUR' => '&#x20a8;',
            'MVR' => '.&#x783;',
            'MWK' => 'MK',
            'MXN' => '&#36;',
            'MYR' => '&#82;&#77;',
            'MZN' => 'MT',
            'NAD' => 'N&#36;',
            'NGN' => '&#8358;',
            'NIO' => 'C&#36;',
            'NOK' => '&#107;&#114;',
            'NPR' => '&#8360;',
            'NZD' => '&#36;',
            'OMR' => '&#x631;.&#x639;.',
            'PAB' => 'B/.',
            'PEN' => 'S/',
            'PGK' => 'K',
            'PHP' => '&#8369;',
            'PKR' => '&#8360;',
            'PLN' => '&#122;&#322;',
            'PRB' => '&#x440;.',
            'PYG' => '&#8370;',
            'QAR' => '&#x631;.&#x642;',
            'RMB' => '&yen;',
            'RON' => 'lei',
            'RSD' => '&#1088;&#1089;&#1076;',
            'RUB' => '&#8381;',
            'RWF' => 'Fr',
            'SAR' => '&#x631;.&#x633;',
            'SBD' => '&#36;',
            'SCR' => '&#x20a8;',
            'SDG' => '&#x62c;.&#x633;.',
            'SEK' => '&#107;&#114;',
            'SGD' => '&#36;',
            'SHP' => '&pound;',
            'SLL' => 'Le',
            'SOS' => 'Sh',
            'SRD' => '&#36;',
            'SSP' => '&pound;',
            'STN' => 'Db',
            'SYP' => '&#x644;.&#x633;',
            'SZL' => 'L',
            'THB' => '&#3647;',
            'TJS' => '&#x405;&#x41c;',
            'TMT' => 'm',
            'TND' => '&#x62f;.&#x62a;',
            'TOP' => 'T&#36;',
            'TRY' => '&#8378;',
            'TTD' => '&#36;',
            'TWD' => '&#78;&#84;&#36;',
            'TZS' => 'Sh',
            'UAH' => '&#8372;',
            'UGX' => 'UGX',
            'USD' => '&#36;',
            'UYU' => '&#36;',
            'UZS' => 'UZS',
            'VEF' => 'Bs F',
            'VES' => 'Bs.S',
            'VND' => '&#8363;',
            'VUV' => 'Vt',
            'WST' => 'T',
            'XAF' => 'CFA',
            'XCD' => '&#36;',
            'XOF' => 'CFA',
            'XPF' => 'Fr',
            'YER' => '&#xfdfc;',
            'ZAR' => '&#82;',
            'ZMW' => 'ZK',
        )
    );

    return apply_filters('ppress_currency_symbols', $symbols);
}

/**
 * Get Currency symbol.
 *
 * Currency symbols and names should follow the Unicode CLDR recommendation (http://cldr.unicode.org/translation/currency-names)
 *
 * @param string $currency Currency. (default: '').
 *
 * @return string
 */
function ppress_get_currency_symbol($currency = '')
{
    if ( ! $currency) {
        $currency = ppress_get_currency();
    }

    $symbols = ppress_get_currency_symbols();

    $currency_symbol = isset($symbols[$currency]) ? $symbols[$currency] : '';

    return apply_filters('ppress_currency_symbol', $currency_symbol, $currency);
}


/**
 * Get the name of a currency
 *
 * @param string $code The currency code
 *
 * @return string The currency's name
 */
function ppress_get_currency_name($code = '')
{
    if ( ! $code) {
        $code = ppress_get_currency();
    }

    $currencies = ppress_get_currencies();
    $name       = isset($currencies[$code]) ? $currencies[$code] : $code;

    return apply_filters('ppress_currency_name', $name);
}

/**
 * Accepts an amount (ideally from the database, unmodified) and formats it
 * for display. The amount itself is formatted and the currency prefix/suffix
 * is applied and positioned.
 *
 * @param string $amount
 * @param string $currency
 *
 * @return string
 *
 */
function ppress_display_amount($amount, $currency = '')
{
    return (new CurrencyFormatter($amount, $currency))->format()->apply_symbol()->val();
}

/**
 *
 * @param $amount
 *
 * @return string
 */
function ppress_sanitize_amount($amount)
{
    return (new CurrencyFormatter($amount))->sanitize()->val();
}

/**
 * Force https for urls.
 *
 * @param mixed $content
 *
 * @return string
 */
function ppress_force_https_url($content)
{
    if (is_ssl()) {
        if (is_array($content)) {
            $content = array_map('ppress_force_https_url', $content);
        } else {
            $content = str_replace('http:', 'https:', $content);
        }
    }

    return $content;
}

function ppress_is_test_mode()
{
    return ppress_var(get_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, []), 'test_mode') == 'true';
}

function ppress_get_payment_mode()
{
    return ppress_is_test_mode() ? OrderMode::TEST : OrderMode::LIVE;
}

/**
 * Converts a date/time to UTC
 *
 * @throws Exception
 */
function ppress_local_datetime_to_utc($date, $format = 'Y-m-d H:i:s')
{
    $a = new DateTime($date, wp_timezone());
    $a->setTimezone(new DateTimeZone('UTC'));

    return $a->format($format);
}

/**
 * Converts a mysql date/time to UTC timestamp
 *
 * @param $datetime
 *
 * @return false|int
 */
function ppress_date_to_utc_timestamp($datetime)
{
    return ppress_strtotime_utc($datetime);
}

/**
 * Formats date according to WordPress date/time format and using WordPress site timezone.
 *
 * @param $timestamp
 * @param string $format
 */
function ppress_format_date_time($timestamp, $format = '')
{
    /**
     * force strtotime to use date as UTC.
     * @see https://stackoverflow.com/a/6275660/2648410
     */
    $timestamp = ! is_numeric($timestamp) ? strtotime($timestamp . ' UTC') : $timestamp;

    $format = empty($format) ? get_option('date_format') . ' ' . get_option('time_format') : $format;

    return wp_date($format, $timestamp);
}

/**
 * Formats date according to WordPress date format and using WordPress site timezone.
 *
 * @param $timestamp
 * @param string $format
 */
function ppress_format_date($timestamp, $format = '')
{
    /**
     * force strtotime to use date as UTC.
     * @see https://stackoverflow.com/a/6275660/2648410
     */
    $timestamp = ! is_numeric($timestamp) ? strtotime($timestamp . ' UTC') : $timestamp;

    $format = empty($format) ? get_option('date_format') : $format;

    return wp_date($format, $timestamp);
}

function ppress_update_payment_method_setting($key, $value)
{
    $data       = get_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, []);
    $data[$key] = $value;
    update_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, $data);
}

function ppress_get_payment_method_setting($key = '', $default = false, $is_empty = false)
{
    static $data = null;

    if (is_null($data)) {
        $data = get_option(PPRESS_PAYMENT_METHODS_OPTION_NAME, []);
    }

    if ($is_empty === true) {
        return isset($data[$key]) && ( ! empty($data[$key]) || ppress_is_boolean($data[$key])) ? $data[$key] : $default;
    }

    return isset($data[$key]) ? $data[$key] : $default;
}

/**
 * @return PPRESS_Session
 */
function ppress_session()
{
    return PPRESS_Session::get_instance();
}

function ppress_is_checkout()
{
    $page_id = ppress_settings_by_key('checkout_page_id', 0, true);

    return ($page_id && is_page($page_id)) || ppress_post_content_has_shortcode('profilepress-checkout');
}

function ppress_is_success_page()
{
    $page_id = ppress_settings_by_key('payment_success_page_id', 0, true);

    return ($page_id && is_page($page_id));
}

/**
 * @param $order_key
 *
 * @return string
 */
function ppress_get_success_url($order_key = '')
{
    $url = get_permalink(
        absint(ppress_settings_by_key('payment_success_page_id'))
    );

    $url = ! $url ? home_url() : $url;

    if ( ! empty($order_key)) {
        $url = add_query_arg(['order_key' => $order_key], $url);
    }

    return apply_filters('ppress_get_success_url', esc_url_raw($url), $order_key);
}

/**
 * @param $order_key
 *
 * @return string
 */
function ppress_get_cancel_url($order_key = '')
{
    $url = get_permalink(
        absint(ppress_settings_by_key('payment_failure_page_id'))
    );

    $url = ! $url ? home_url() : $url;

    if ( ! empty($order_key)) {
        $url = add_query_arg(['order_key' => $order_key], $url);
    }

    return apply_filters('ppress_get_cancel_url', esc_url_raw($url), $order_key);
}

function ppress_business_name()
{
    return ppress_settings_by_key('business_name', '', true);
}

function ppress_business_address($default = '')
{
    return ppress_settings_by_key('business_address', $default, true);
}

function ppress_business_city($default = '')
{
    return ppress_settings_by_key('business_city', $default, true);
}

function ppress_business_country($default = '')
{
    return ppress_settings_by_key('business_country', $default, true);
}

function ppress_business_state($default = '')
{
    return ppress_settings_by_key('business_state', $default, true);
}

function ppress_business_postal_code($default = '')
{
    return ppress_settings_by_key('business_postal_code', $default, true);
}

function ppress_business_full_address()
{
    $billing_address = ppress_business_address();

    if (empty($billing_address)) return '';

    $business_country = ppress_business_country();

    $state = ppress_var(ppress_array_of_world_states($business_country), ppress_business_state(), ppress_business_state(), true);

    $address   = [trim($billing_address)];
    $address[] = trim(ppress_business_city() . ' ' . $state);
    $address[] = ppress_business_postal_code();
    $address[] = ppress_array_of_world_countries($business_country);

    return implode(', ', array_filter($address));
}

function ppress_business_tax_id($default = '')
{
    return ppress_settings_by_key('business_tin', $default, true);
}