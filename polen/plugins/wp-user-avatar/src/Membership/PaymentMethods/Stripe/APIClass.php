<?php

namespace ProfilePress\Core\Membership\PaymentMethods\Stripe;

use ProfilePressVendor\Stripe\Stripe as Stripe;
use ProfilePressVendor\Stripe\StripeClient;
use WP_Error;

class APIClass
{
    /**
     * Configures the Stripe API before each request.
     */
    public static function _setup()
    {
        /**
         * Sets application info for all proceeding requests.
         * @link https://stripe.com/docs/building-plugins#setappinfo
         */
        Stripe::setAppInfo(
            'ProfilePress',
            PPRESS_VERSION_NUMBER,
            'https://profilepress.com',
            'pp_partner_LLG1ywQG7y6Ogw'
        );

        /**
         * Sets API version for all proceeding requests.
         * @link https://stripe.com/docs/building-plugins#set-api-version
         */
        Stripe::setApiVersion(PPRESS_STRIPE_API_VERSION);

        $secret_key = Helpers::get_secret_key();

        Stripe::setApiKey(trim($secret_key));

        Stripe::setMaxNetworkRetries(2);

        Stripe::setEnableTelemetry(false);

        return new StripeClient(trim($secret_key));
    }

    /**
     * @return StripeClient
     */
    public static function stripeClient()
    {
        return self::_setup();
    }

    /**
     * @param $account_id
     *
     * @return array|WP_Error
     *
     * @throws \Exception
     */
    public function get_account($account_id)
    {
        try {

            return self::stripeClient()->accounts->retrieve($account_id)->toArray();

        } catch (\Exception $e) {

            ppress_log_error(__METHOD__ . '(): ' . $e->getMessage());

            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}
