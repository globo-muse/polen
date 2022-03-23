<?php
namespace Polen\Includes;

use Exception;

class Polen_Cupom_Create
{

    const TYPE_POST = 'shop_coupon';

    const DISCOUNT_TYPE_FIXED_CART      = "fixed_cart";
    const DISCOUNT_TYPE_PERCENT         = "percent";
    const DISCOUNT_TYPE_FIXED_PRODUCT   = "fixed_product";
    const DISCOUNT_TYPE_PERCENT_PRODUCT = "percent_product";

    const DISCOUNT_TYPES = [ 
        self::DISCOUNT_TYPE_FIXED_CART,
        self::DISCOUNT_TYPE_PERCENT,
        self::DISCOUNT_TYPE_FIXED_PRODUCT,
        self::DISCOUNT_TYPE_PERCENT_PRODUCT,
    ];

    public function __construct( $static = false )
    {
        if( $static ) {
            add_action( 'ajax_admin_polen_create_cupom', [ $this, 'create_cupom' ] );
        }
    }


    public function create_cupom_batch( $qtd, $data_cupons )
    {
        for( $i = 0; $i < $qtd; $i++ ) {
            $this->create_cupom( $data_cupons );
        }
    }


    public function create_cupom( $data )
    {
        $this->validate_unique_cupom( $data );
        $amount = $this->validate_amount( $data );
        $discount_type = $this->validate_discount_type( $data );
        $description = $data[ 'description' ];
        $product_ids = $data['product_ids'];
        $expiry_date = isset( $data[ 'expiry_date' ] ) ? $this->treat_expiry_date( $data[ 'expiry_date' ] ) : '';
        $usage_limit = $data[ 'usage_limit' ];
        $coupon_data = array(
            'post_title' => $this->set_cupom_code( $data ),
            'post_content' => '',
            'post_status' => 'publish',
            'post_excerpt' => $description,
            'post_author' => get_current_user_id(),
            'post_type' => self::TYPE_POST
        );
        $new_coupon_id = wp_insert_post( $coupon_data );

        update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
        update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
        update_post_meta( $new_coupon_id, 'individual_use', 'no' );
        update_post_meta( $new_coupon_id, 'product_ids', $product_ids );
        update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
        update_post_meta( $new_coupon_id, 'exclude_sale_items', 'no' );
        update_post_meta( $new_coupon_id, 'usage_limit', $usage_limit );
        update_post_meta( $new_coupon_id, 'expiry_date', $expiry_date );
        update_post_meta( $new_coupon_id, 'free_shipping', 'no' );
    }


    public function complete_if_empty( $data )
    {
        if( !isset( $data[ 'cupom_code' ] ) || empty( $data[ 'cupom_code' ] ) ) {
            return $this->create_code( 8 );
        }
        return $data[ 'cupom_code' ];
    }


    public function create_code( $length = 10 )
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    public function treat_expiry_date( $data )
    {
        if( empty( $data ) ) {
            return '';
        }
        $date_time = \DateTime::createFromFormat( 'd/m/Y', $data );
        if( $date_time === false ) {
            throw new Exception( 'Data inválida', 500 );
        }
        return strtotime( $date_time->format( 'Y-m-d' ) );
    }


    public function treat_prefix_name( $data )
    {
        if( empty( $data['prefix_name'] ) ) {
            return '';
        }
        return $data['prefix_name'];
    }



    public function set_cupom_code( $data )
    {
        $prefix_name = $this->treat_prefix_name( $data );
        $coupon_code = $this->complete_if_empty( $data );
        $name = $prefix_name . $coupon_code;
        return $name;
    }


    public function validate_unique_cupom( $data )
    {
        global $wpdb;
        $code = $this->set_cupom_code( $data );
        $result = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_title = '%s' AND post_status <> 'trash' AND post_type = 'shop_coupon';", $wpdb->esc_like( $code  )) );
        if( !empty( $result ) ) {
            throw new Exception( 'Código do Cupom já Existe', 500 );
        }
        return true;
    }


    public function validate_discount_type( $data )
    {
        if( !in_array( $data[ 'discount_type' ], self::DISCOUNT_TYPES ) ) {
            throw new Exception( 'Tipo do desconto é inválido', 500 );
        }
        return $data[ 'discount_type' ];
    }


    public function validate_amount( $data ) 
    {
        if( !isset( $data[ 'amount' ] ) 
            || empty( $data[ 'amount' ] )
            || !filter_var( $data[ 'amount' ], FILTER_VALIDATE_FLOAT )
        ) {
            throw new Exception( 'Valor do desconto inválido', 500 );
        }
        return $data[ 'amount' ];
    }
}
