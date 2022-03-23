<?php

namespace Polen\Enterprise;

class Enterprise{

    public function __construct( bool $static = false )
    {
        new Enterprise_Rewrite( $static );
        new Enterprise_Router($static);
    }
}