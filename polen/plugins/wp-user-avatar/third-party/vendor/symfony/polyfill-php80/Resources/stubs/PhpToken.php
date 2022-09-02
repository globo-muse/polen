<?php

namespace ProfilePressVendor;

if (\PHP_VERSION_ID < 80000 && \extension_loaded('tokenizer')) {
    class PhpToken extends \ProfilePressVendor\Symfony\Polyfill\Php80\PhpToken
    {
    }
}
