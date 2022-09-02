<?php

namespace ProfilePress\Core\Membership\Models;

abstract class AbstractModel
{
    abstract public function exists();

    public function __set($key, $value)
    {
        if (method_exists($this, "set_{$key}")) {
            call_user_func([$this, "set_{$key}"], $value);
        } else {
            $this->$key = $value;
        }
    }

    public function __get($key)
    {
        $value = false;

        if (method_exists($this, "get_{$key}")) {
            $value = call_user_func([$this, "get_{$key}"]);
        } elseif (isset($this->$key)) {
            $value = $this->$key;
        }

        return $value;
    }

    public function __isset($key)
    {
        return $this->__get($key);
    }
}