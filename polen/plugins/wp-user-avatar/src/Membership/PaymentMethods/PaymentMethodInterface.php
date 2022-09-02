<?php

namespace ProfilePress\Core\Membership\PaymentMethods;

interface PaymentMethodInterface
{
    public function get_id();

    public function get_title();

    public function get_description();

    public function get_method_title();

    public function get_method_description();

    public function is_enabled();

    public function supports($feature);

    public function get_icon();

    public function has_fields();

    public function payment_fields();
}