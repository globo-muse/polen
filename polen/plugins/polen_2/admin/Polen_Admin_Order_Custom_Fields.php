<?php

namespace Polen\Admin;

use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Polen_Cart;
use Polen\Includes\Polen_Order;
use Polen\Includes\Polen_Utils;
use WC_DateTime;

class Polen_Admin_Order_Custom_Fields
{

    const NONCE_ACTION = 'polen_edit_order_custom_fields';

    public function __construct( $static = false )
    {
        add_action( 'wp_ajax_polen_edit_order_custom_fields', [ $this, 'edit_order_custom_fields' ] );
        add_action( 'wp_ajax_polen_edit_order_custom_fields_deadline', [ $this, 'edit_order_custom_fields_deadline' ] );

        if ($static) {
            add_action( 'woocommerce_admin_order_data_after_order_details', [ $this, 'checkbox_no_send_notification' ] );
        }

        // add_action( 'wp_ajax_nopriv_polen_edit_order_custom_fields', [ $this, 'edit_order_custom_fields' ] );
    }


    /**
     *
     */
    public function edit_order_custom_fields()
    {
        $field = filter_input( INPUT_POST, 'field' );
        $this->validate_field_is_valid( $field );
        $new_value = filter_input( INPUT_POST, 'value' );

        $nonce = filter_input( INPUT_POST, 'security' );
        $this->validate_nonce( $nonce, self::NONCE_ACTION );

        $order_id = filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );
        $order = wc_get_order( $order_id );
        $this->validate_order_valid( $order );

        $item_cart = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
        $item_order = $item_cart->get_item_order();

        $old_value = $item_order->get_meta( $field, true );
        $new_value = Polen_Utils::sanitize_xss_br_escape( $new_value );
        $order->add_order_note( "{$field} alterado via wp-admin: Valor antigo: {$old_value}", 0, true );
        $item_order->update_meta_data( $field, $new_value, true );
        $item_order->save();
        wp_send_json_success( 'editado com sucesso', 200 );
        wp_die();
    }



    /**
     *
     */
    public function edit_order_custom_fields_deadline()
    {
        $field = filter_input( INPUT_POST, 'field' );
        $this->validate_deadline_field_is_valid( $field );
        $new_value = filter_input( INPUT_POST, 'value' );

        try{
            $date = WC_DateTime::createFromFormat( 'd/m/Y H:i:s', $new_value . ' 23:59:59' );
            $nonce = filter_input( INPUT_POST, 'security' );
            $this->validate_dateTime( $date );
            $this->validate_nonce( $nonce, self::NONCE_ACTION );

            $order_id = filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );
            $order = wc_get_order( $order_id );
            $this->validate_order_valid( $order );

            $current_deadline = $order->get_meta( Polen_Order::META_KEY_DEADLINE, true );
            $old_deadline = \WC_DateTime::createFromFormat( 'U', $current_deadline );

            if( !empty( $old_deadline ) ) {
                $order->add_order_note( "Deadline alterada. Antiga: {$old_deadline->format('d/m/Y')}.", 0, true );
            } else {
                $order->add_order_note( "Deadline CRIADA (já era para existir verificar o problema).", 0, true );
            }
            $order->update_meta_data( $field, $date->getTimestamp(), true );
            $order->save();
            wp_send_json_success( 'deadline editada com sucesso', 200 );
        } catch ( \Exception $e ) {
            wp_send_json_error( $e->getMessage(), 401 );
        }
        wp_die();
    }


    /**
     * Retona um error no WP_SEND_JSON_ERROR
     */
    public function return_error( $msg, $error_no = 500 )
    {
        wp_send_json_error( $msg, $error_no );
        wp_die();
    }

    /**
     * Validacao DateTime
     */
    public function validate_dateTime( $date )
    {
        if( empty( $date ) ) {
            $this->return_error( 'Data informada é inválida', 403 );
        }
    }


    /**
     * Validacao nonce Correto
     */
    public function validate_nonce( $nonce, $action )
    {
        if( !wp_verify_nonce( $nonce, $action ) ) {
            $this->return_error( 'Validação nonce inválida', 403 );
        }
    }


    /**
     * Validacao Order existe
     */
    public function validate_order_valid( $order )
    {
        if( empty( $order ) ) {
            $this->return_error( 'Order inválida', 404 );
        }
    }


    /**
     * Validar se o fields a ser editado existe nos custom Fields
     */
    public function validate_field_is_valid( $field )
    {
        if( !in_array( $field, Polen_Cart::ALLOWED_ITEM ) ) {
            $this->return_error( 'field é inválido', 403 );
        }
    }


    public function validate_deadline_field_is_valid( $field )
    {
        if( $field != Polen_Order::META_KEY_DEADLINE ) {
            $this->return_error( 'field deadline é inválido', 403 );
        }
    }


    function checkbox_no_send_notification($order)
    {  ?>
        <label for="send_email">
            <h4><?php echo "Enviar e-mail de atualização do status"; ?></h4>
            <input type="checkbox" name="send_email" id="send_email">
        </label>
    <?php }
}
