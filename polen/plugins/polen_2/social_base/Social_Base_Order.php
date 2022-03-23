<?php
namespace Polen\Social_Base;

class Social_Base_Order
{
    const ORDER_META_KEY_SOCIAL = 'social_base';
    const ORDER_META_KEY_campaign = 'social_base_campaign';

    public static function is_social( $order )
    {
        if( $order->get_meta( self::ORDER_META_KEY_SOCIAL, true ) == '1' ) {
            return true;
        }
        return false;
    }
}