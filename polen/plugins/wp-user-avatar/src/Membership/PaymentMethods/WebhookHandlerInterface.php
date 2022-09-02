<?php

namespace ProfilePress\Core\Membership\PaymentMethods;

interface WebhookHandlerInterface
{
    public function handle($event_data);
}
