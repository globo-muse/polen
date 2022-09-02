<?php

namespace ProfilePress\Core\Membership\PaymentMethods\Stripe;

use ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers\ChargeRefunded;
use ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers\CheckoutSessionCompleted;
use ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers\CustomerSubscriptionCreated;
use ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers\CustomerSubscriptionDeleted;
use ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers\CustomerSubscriptionUpdated;
use ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers\InvoicePaymentSucceeded;
use ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers\PaymentIntentSucceeded;

class WebhookHelpers
{
    public static function valid_events()
    {
        return apply_filters('ppress_stripe_webhooks_whitelist', [
            'checkout.session.completed'    => new CheckoutSessionCompleted(),
            'customer.subscription.created' => new CustomerSubscriptionCreated(),
            'customer.subscription.updated' => new CustomerSubscriptionUpdated(),
            'customer.subscription.deleted' => new CustomerSubscriptionDeleted(), //triggers when sub is cancelled
            'invoice.payment_succeeded'     => new InvoicePaymentSucceeded(),
            'payment_intent.succeeded'      => new PaymentIntentSucceeded(),
            'charge.refunded'               => new ChargeRefunded()
        ]);
    }

    public static function webhook_url()
    {
        return add_query_arg(['ppress-listener' => 'stripe'], home_url('/'));
    }

    public static function add_update_endpoint()
    {
        try {

            $endpoint = self::exists();

            if ( ! $endpoint) {
                self::create();

                return;
            }

            if (false === self::is_valid($endpoint)) {
                self::update($endpoint);

                return;
            }

        } catch (\Exception $e) {
            // Fail silently.
        }
    }

    /**
     * Determines if the current payment mode has an up-to-date webhook endpoint.
     *
     * @return mixed|bool
     */
    public static function exists()
    {
        $mode = ppress_is_test_mode() ? 'test' : 'live';

        $endpoint_id = ppress_get_payment_method_setting('stripe_' . $mode . '_webhook_endpoint_id', '');

        if (empty($endpoint_id)) return false;

        try {
            $endpoint = self::get($endpoint_id);
        } catch (\Exception $e) {
            return false;
        }

        return $endpoint;
    }

    /**
     * Determines if a Stripe webhook endpoint is still valid.
     *
     * @param mixed $endpoint
     *
     * @return bool True if the webhook does not need to be updated.
     *
     */
    public static function is_valid($endpoint)
    {
        $enabled_events = $endpoint['enabled_events'];

        // Enabled events are not * and do not match the defined whitelist.
        if ( ! in_array('*', $enabled_events, true) && $enabled_events != self::get_event_whitelist()) {
            return false;
        }

        if ($endpoint['url'] !== self::webhook_url()) return false;

        if ('enabled' !== $endpoint['status']) return false;

        return true;
    }

    /**
     * Retrieves a Stripe webhook endpoint.
     *
     * @param string $endpoint_id Stripe endpoint ID.
     *
     * @return array
     *
     * @throws \Exception
     */
    public static function get($endpoint_id)
    {
        return APIClass::stripeClient()->webhookEndpoints->retrieve($endpoint_id)->toArray();
    }

    /**
     * Creates a Stripe webhook endpoint with the current plugin and site settings.
     *
     * @return false|mixed
     *
     * @throws \Exception
     */
    public static function create()
    {
        // Use an existing endpoint if one already exists from a previous setup.
        $endpoint = self::get_manual_endpoint();

        if ( ! $endpoint) {

            $endpoint = APIClass::stripeClient()->webhookEndpoints->create([
                'url'            => self::webhook_url(),
                'enabled_events' => self::get_event_whitelist(),
                'connect'        => false,
                'api_version'    => PPRESS_STRIPE_API_VERSION,
                'description'    => sprintf(
                    'ProfilePress (WordPress plugin) endpoint (%s Mode)',
                    ppress_is_test_mode() ? 'Test' : 'Live'
                ),
            ])->toArray();
        }

        self::persist($endpoint);

        return $endpoint;
    }

    /**
     * Creates a Stripe webhook endpoint with the current plugin and site settings.
     *
     * @return array|false|mixed
     * @throws \Exception
     */
    public static function delete($endpoint_id)
    {
        $endpoint = APIClass::stripeClient()->webhookEndpoints->delete($endpoint_id);

        self::persist($endpoint, true);
    }

    /**
     * Updates a Stripe webhook endpoint with the current site and plugin settings.
     *
     * @param $endpoint
     *
     * @return array
     * @throws \Exception
     */
    public static function update($endpoint)
    {
        $enabled_events = $endpoint['enabled_events'];

        // Existing webhook does not accept all events. Merge the new whitelist.
        if ( ! in_array('*', $enabled_events, true)) {
            $enabled_events = array_values(
                array_unique(
                    array_merge(
                        $enabled_events,
                        self::get_event_whitelist()
                    )
                )
            );
        }

        $endpoint = APIClass::stripeClient()->webhookEndpoints->update(
            $endpoint['id'],
            [
                'url'            => self::webhook_url(),
                'enabled_events' => $enabled_events,
                'disabled'       => false,
            ]
        )->toArray();

        self::persist($endpoint);

        return $endpoint;
    }

    /**
     * Returns a list of Stripe webhook events that are supported by the plugin.
     *
     * @return array
     *
     */
    public static function get_event_whitelist()
    {
        return array_keys(self::valid_events());
    }

    /**
     * Persists a Stripe webhook endpoint's information.
     *
     * @param $endpoint
     *
     */
    private static function persist($endpoint, $clearOutVal = false)
    {
        $mode = ppress_is_test_mode() ? 'test' : 'live';

        ppress_update_payment_method_setting(
            'stripe_' . $mode . '_webhook_endpoint_id',
            $clearOutVal ? '' : $endpoint['id']
        );

        ppress_update_payment_method_setting(
            "stripe_{$mode}_webhook_endpoint_events",
            $clearOutVal ? '' : $endpoint['enabled_events']
        );

        ppress_update_payment_method_setting(
            "stripe_{$mode}_webhook_endpoint_url",
            $clearOutVal ? '' : $endpoint['url']
        );

        // Secret is only returned on initial creation.
        if (isset($endpoint['secret']) || $clearOutVal) {
            ppress_update_payment_method_setting(
                "stripe_{$mode}_webhook_secret",
                $clearOutVal ? '' : $endpoint['secret']
            );

            // Clear out from a previous connection.
        } else {
            ppress_update_payment_method_setting(
                "stripe_{$mode}_webhook_secret",
                ''
            );
        }

        ppress_update_payment_method_setting(
            "stripe_{$mode}_webhook_api_version",
            $clearOutVal ? '' : PPRESS_STRIPE_API_VERSION
        );
    }

    /**
     * Attempts to locate an endpoint that was manually created.
     *
     * If we find an endpoint with an exact URL match, persist it and return it.
     *
     * @return bool|mixed
     *
     * @throws \Exception
     */
    private static function get_manual_endpoint()
    {
        $endpoints = APIClass::stripeClient()->webhookEndpoints->all(['limit' => 100])->toArray();

        foreach ($endpoints['data'] as $endpoint) {

            if ($endpoint['url'] === self::webhook_url()) {

                $remote_events = $endpoint['enabled_events'];
                $local_events  = self::get_event_whitelist();

                if (count($remote_events) < count($local_events)) {
                    $endpoint = self::update($endpoint);
                }

                self::persist($endpoint);

                return $endpoint;
            }
        }

        return false;
    }
}