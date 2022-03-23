<?php

namespace Polen\Includes\Order_Review;

use Polen\Includes\Talent\Polen_Talent_Router;

class Polen_Order_Review_Router extends Polen_Talent_Router
{
    public function init_routes()
    {
        $this->add_route( 'create_order_review', 'create_order_review', true );
    }
}
