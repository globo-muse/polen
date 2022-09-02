<?php

namespace ProfilePress\Core\Membership\Controllers;

abstract class BaseController
{
    /**
     * @return static
     */
    public static function get_instance()
    {
        return new static();
    }
}