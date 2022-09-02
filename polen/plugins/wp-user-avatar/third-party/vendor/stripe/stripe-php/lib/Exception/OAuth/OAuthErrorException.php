<?php

namespace ProfilePressVendor\Stripe\Exception\OAuth;

/**
 * Implements properties and methods common to all (non-SPL) Stripe OAuth
 * exceptions.
 */
abstract class OAuthErrorException extends \ProfilePressVendor\Stripe\Exception\ApiErrorException
{
    protected function constructErrorObject()
    {
        if (null === $this->jsonBody) {
            return null;
        }
        return \ProfilePressVendor\Stripe\OAuthErrorObject::constructFrom($this->jsonBody);
    }
}
