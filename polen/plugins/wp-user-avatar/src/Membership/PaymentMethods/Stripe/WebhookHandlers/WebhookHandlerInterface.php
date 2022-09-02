<?php

namespace ProfilePress\Core\Membership\PaymentMethods\Stripe\WebhookHandlers;

interface WebhookHandlerInterface
{
    public function handle($event_data);
}
