<?php

namespace Polen\Master_class;

class Master_Class{

    public function __construct( bool $static = false )
    {
        new Master_Class_Rewrite( $static );
        new Master_Class_Router($static);
    }
}