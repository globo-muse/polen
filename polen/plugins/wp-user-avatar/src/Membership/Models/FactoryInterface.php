<?php

namespace ProfilePress\Core\Membership\Models;

interface FactoryInterface
{
    public static function make($data);

    public static function fromId($id);
}