<?php

namespace ProfilePressVendor\Stripe\ApiOperations;

/**
 * Trait for searchable resources.
 *
 * This trait should only be applied to classes that derive from StripeObject.
 */
trait Search
{
    /**
     * @param string $searchUrl
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \Stripe\SearchResult of ApiResources
     */
    protected static function _searchResource($searchUrl, $params = null, $opts = null)
    {
        self::_validateParams($params);
        list($response, $opts) = static::_staticRequest('get', $searchUrl, $params, $opts);
        $obj = \ProfilePressVendor\Stripe\Util\Util::convertToStripeObject($response->json, $opts);
        if (!$obj instanceof \ProfilePressVendor\Stripe\SearchResult) {
            throw new \ProfilePressVendor\Stripe\Exception\UnexpectedValueException('Expected type ' . \ProfilePressVendor\Stripe\SearchResult::class . ', got "' . \get_class($obj) . '" instead.');
        }
        $obj->setLastResponse($response);
        $obj->setFilters($params);
        return $obj;
    }
}
