<?php

namespace ProfilePress\Core\Membership\Controllers;

class CheckoutResponse
{
    public $is_success = false;

    public $redirect_url = '';

    public $gateway_response = '';

    public $error_message = '';

    public function set_is_success($val)
    {
        $this->is_success = $val;

        return $this;
    }

    public function set_redirect_url($val)
    {
        $this->redirect_url = $val;

        return $this;
    }

    public function set_gateway_response($val)
    {
        $this->gateway_response = $val;

        return $this;
    }

    public function set_error_message($val)
    {
        $this->error_message = $val;

        return $this;
    }

    public function get_generic_error_message()
    {
        return esc_html__('Unable to complete checkout. Please try again.', 'wp-user-avatar');
    }
}