<?php
namespace Polen\Social;

class Social_Order
{
    const ORDER_META_KEY_SOCIAL = 'social';
    const ORDER_META_KEY_campaign = 'campaign';

    static function is_social( $order )
    {
        if( $order->get_meta( self::ORDER_META_KEY_SOCIAL, true ) == '1' ) {
            return true;
        }
        return false;
    }
}