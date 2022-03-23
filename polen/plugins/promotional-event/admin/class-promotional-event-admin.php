<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://polen.me
 * @since      1.0.0
 *
 * @package    Promotional_Event
 * @subpackage Promotional_Event/admin
 */

use Polen\Admin\Polen_Admin_Event_Promotional_Event_Fields;
use Polen\Api\Api_Checkout;
use Polen\Includes\Debug;
use Polen\Includes\Module\Polen_Product_Module;
use Polen\Includes\Polen_Checkout_Create_User;
use Polen\Includes\Polen_Order;
use Polen\Includes\Polen_WooCommerce;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Promotional_Event
 * @subpackage Promotional_Event/admin
 * @author     Polen.me <glaydson.queiroz@polen.me>
 */
class Promotional_Event_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	const ORDER_METAKEY = 'promotional_event';
	const PRODUCT_METAKEY = '_promotional_event';
    // const SESSION_KEY_CUPOM_CODE = 'event_promotion_cupom_code';
    // const SESSION_KEY_SUCCESS_ORDER_ID = 'event_promotion_order_id';
    const NONCE_ACTION = 'promotional_event_2hj3g42jhg43';
    const NONCE_ACTION_CUPOM_VALIDATION = 'check-coupon';
    const TAG_SLUG = 'event-promotional';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version )
    {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
    {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/promotional-event-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
    {
        wp_enqueue_script( 'bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js', $this->version, false );
        wp_enqueue_script( 'dataTables-script', plugin_dir_url( __FILE__ ) . 'js/jquery.dataTables.min.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/promotional-event-admin.js', array( 'jquery' ), $this->version, true );
        wp_localize_script( 'ajax-script', 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    }

    /**
     * Adiciona os menus no dashboard do wordpress
     *
     * @since    1.0.0
     */
    public function add_menu()
    {
        add_menu_page('Evento Promocional',
            'Evento Promocional',
            'manage_options',
            'promotional-event',
            array($this, 'show_promotions')
        );

        add_submenu_page('promotional-event',
            'Configurações',
            'Configurar Cupons',
            'manage_options',
            'options_event',
            array($this, 'show_options'),
        );

        add_submenu_page('promotional-event',
            'De porta em porta',
            'De porta em porta',
            'manage_options',
            'porta_em_porta_event',
            array($this, 'show_de_porta_em_porta'),
        );

        add_submenu_page('promotional-event',
            'Rebeldes',
            'Rebeldes',
            'manage_options',
            'rebeldes_event',
            array($this, 'show_rebeldes_event'),
        );

        add_submenu_page('promotional-event',
            'Campanha Lacta',
            'Campanha Lacta',
            'manage_options',
            'campanha_lacta_event',
            array($this, 'show_campanha_lacta_event'),
        );
    }

    /**
     * View página principal do plugin
     *
     * @since    1.0.0
     */
    public function show_promotions()
    {
        $sql = new Coupons();
        $values_code = $sql->get_all_codes();
        require 'partials/promotional-event-admin-display.php';
    }

    /**
     * View página de opções do plugin
     *
     * @since    1.0.0
     */
    public function show_options()
    {
        $sql = new Coupons();
        $count = $sql->count_rows();
        require 'partials/promotional-event-options.php';
    }

    /**
     * View página de opções do plugin para listagem de cupons de porta em porta
     *
     * @since    1.0.0
     */
    public function show_de_porta_em_porta()
    {
        $sql = new Coupons();
        $values_code = $sql->get_codes();
        require 'partials/promotional-event-admin-display.php';
    }

    /**
     * View página de opções do plugin para listagem de cupons de rebeldes
     *
     * @since    1.0.0
     */
    public function show_rebeldes_event()
    {
        $sql = new Coupons();
        $values_code = $sql->get_codes(2);
        require 'partials/promotional-event-admin-display.php';
    }

    /**
     * View página de opções do plugin para listagem de cupons de rebeldes
     *
     * @since    1.0.0
     */
    public function show_campanha_lacta_event()
    {
        $sql = new Coupons();
        $values_code = $sql->get_codes(3);
        require 'partials/promotional-event-admin-display.php';
    }

    /**
     * Criar cupons
     *
     * @throws Exception
     */
    public function create_coupons()
    {
        $qty = addslashes($_POST['qty']);
        $event_id = addslashes($_POST['event_id']);
        $sql = new Coupons();
        $sql->insert_coupons($qty, $event_id);
        wp_send_json_success( 'ok', 200 );
        wp_die();
    }

    /**
     * Criar uma nova order e salvar cupom
     */
    function create_orders_video_autograph()
    {
        try{
            $coupon_code = !empty($_POST['coupon']) ? sanitize_text_field($_POST['coupon']) : null;
            $name = sanitize_text_field($_POST['name']) ?? null;
            $email = sanitize_text_field($_POST['email']) ?? null;
            $city = sanitize_text_field($_POST['city']) ?? null;
            $term = sanitize_text_field( $_POST['terms'] ) ?? null;
            $nonce = $_POST['security'] ?? null;

            $city = substr( $city, 0, 50 );
            $name = substr( $name, 0, 50 );
            
            $product_sku = filter_input( INPUT_POST, 'product', FILTER_SANITIZE_STRING );
            $product_id = wc_get_product_id_by_sku( $product_sku );
            $product = wc_get_product( $product_id );

            if( !wp_verify_nonce( $nonce, self::NONCE_ACTION )) {
                throw new Exception('Erro na verificação de segurança', 422);
            }

            if( !filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL ) ) {
                throw new Exception('Email inválido', 422);
            }

            if( empty( trim( $name ) ) ) {
                throw new Exception('Nome é obrigatório', 422);
            }

            if( empty( trim( $city ) ) ) {
                throw new Exception('Cidade é obrigatório', 422);
            }
            
            if( empty( $term ) || $term !== 'on' ) {
                throw new Exception('Aceite os termos e condições', 422);
            }

            if( empty( $product ) ) {
                throw new Exception('Produto inválido', 404);
            }

            $address = array(
                'first_name' => $name,
                'email' => $email,
                'city' => '',
                'state' => '',
                'country' => 'Brasil',
                // 'phone' => sanitize_text_field($_POST['phone']),
            );

            $coupon = new Coupons();
            $check = $coupon->check_coupoun_exist($coupon_code);
            $check_is_used = $coupon->check_coupoun_is_used($coupon_code);

            if (empty($coupon_code)) {
                throw new Exception('Cupom é obrigatório', 422);
                wp_die();
            }

            if (empty($check)) {
                throw new Exception('Cupom está incorreto ou não existe', 404);
                wp_die();
            }

            if ($check_is_used == 1) {
                throw new Exception('Cupom já foi utilizado', 401);
                wp_die();
            }

            if (!$product->is_in_stock()) {
                throw new Exception('Produto sem Estoque', 422);
            }
            
            $args = array();
            if( !empty(get_current_user_id())) {
                $args['customer_id'] = get_current_user_id();
            } else {
                $user_c = get_user_by('email', $email);
                if(!empty($user_c)) {
                    $args['customer_id'] = $user_c->ID;
                } else {
                    $user_email = $email;
                    $user_password = wp_generate_password( 5, false ) . random_int( 0, 99 );
                    $id_registered = wc_create_new_customer( $user_email, $user_email, $user_password );
                    $user = get_user_by( 'ID', $id_registered );
                    add_user_meta( $user->ID, Polen_Checkout_Create_User::META_KEY_CREATED_BY, 'checkout', true );
                    $polen_product = new Polen_Product_Module( $product );
                    add_user_meta( $user->ID, Polen_Admin_Event_Promotional_Event_Fields::FIELD_NAME_SLUG_CAMPAIGN, $polen_product->get_campaign_slug(), true );
                    $args['customer_id'] = $user->ID;
                }
            }

            $polen_product = new Polen_Product_Module( $product );
            $order = wc_create_order( $args );
            $order->set_customer_id( $args['customer_id'] );
            $coupon->update_coupoun($coupon_code, $order->get_id());
            $order->update_meta_data( '_polen_customer_email', $email );
            $order->add_meta_data( Polen_Admin_Event_Promotional_Event_Fields::FIELD_NAME_IS, 'yes', true);
            $order->add_meta_data( Polen_Admin_Event_Promotional_Event_Fields::FIELD_NAME_SLUG_CAMPAIGN, $polen_product->get_campaign_slug(), true);
            $order->add_meta_data( Api_Checkout::ORDER_METAKEY, $polen_product->get_campaign_slug(), true );

            // $order->update_status('wc-payment-approved');

            // ID Product
            // global $Polen_Plugin_Settings;
            $product_id = $product->get_id();
            // $product_id = $Polen_Plugin_Settings['promotional-event-text'];

            $quantity = 1;
            // $product = wc_get_product($product_id);
            $order_item_id = $order->add_product( $product, $quantity );
            $order->set_address($address, 'billing');

            // $order_item_id = wc_add_order_item( $order->get_id(), array(
            //     'order_item_name' => $product->get_title(),
            //     'order_item_type' => 'line_item',
            // ));

            $instruction = "
            Olá {$name} de {$city},<br>

            Eu sou {$product->get_title()} e junto com a Lacta estou aqui para te 
            desejar um ótimo Natal, com muita saúde, amor e paz, porque com a Lacta
            você divide mais que chocolate, você cria laços. Um grande beijo e feliz
            natal para toda família.";

            wc_add_order_item_meta( $order_item_id, '_qty', $quantity, true );
            wc_add_order_item_meta( $order_item_id, '_product_id', $product->get_id(), true );
            wc_add_order_item_meta( $order_item_id, '_line_subtotal', '0', true );
            wc_add_order_item_meta( $order_item_id, '_line_total', '0', true );
            //Polen Custom Meta Order_Item
            wc_add_order_item_meta( $order_item_id, 'offered_by'            , '', true );

            wc_add_order_item_meta( $order_item_id, 'video_to'              , 'to_myself', true );
            wc_add_order_item_meta( $order_item_id, 'name_to_video'         , $name, true );
            wc_add_order_item_meta( $order_item_id, 'email_to_video'        , $email, true );
            wc_add_order_item_meta( $order_item_id, 'video_category'        , 'Vídeo-Lacta', true );
            wc_add_order_item_meta( $order_item_id, 'instructions_to_video' , $instruction, true );

            wc_add_order_item_meta( $order_item_id, 'allow_video_on_page'   , 'on', true );
            wc_add_order_item_meta( $order_item_id, '_fee_amount'           , 0, true );
            wc_add_order_item_meta( $order_item_id, '_line_total'           , 0, true );

            $interval  = Polen_Order::get_interval_order_event();
            $timestamp = Polen_Order::get_deadline_timestamp( $order, $interval );
            $order->add_meta_data( Polen_Order::META_KEY_DEADLINE, $timestamp, true );
            $order->save();
            
            $email = WC_Emails::instance();
            $order->update_status( Polen_WooCommerce::ORDER_STATUS_PAYMENT_APPROVED, 'order_note', true );
            
            wc_reduce_stock_levels($order->get_id());

            $order = new \WC_Order($order->get_id());
            $order->calculate_totals();

            $url_redirect = event_promotional_url_success( $product, $order->get_id(), $order->get_order_key() );
            $result = array(
                'url' => $url_redirect,
                'order_id' => $order->get_id(),
                'compra_success_code' => $order->get_order_key(),
            );

            wp_send_json_success( $result, 200 );
            wp_die();

        } catch (\Exception $e) {
            wp_send_json_error(array('Error' => $e->getMessage()), 422);
            wp_die();
        }
    }

    /**
     * Verificar cupon
     */
    function check_coupon()
    {
        try{
            $coupon_code = !empty($_POST['coupon']) ? sanitize_text_field($_POST['coupon']) : null;
            $product_sku = filter_input( INPUT_POST, 'product', FILTER_SANITIZE_STRING );
            $product_id = wc_get_product_id_by_sku( $product_sku );
            $product = wc_get_product( $product_id );

            $coupon = new Coupons();
            $check = $coupon->check_coupoun_exist($coupon_code);
            $check_is_used = $coupon->check_coupoun_is_used($coupon_code);
            $nonce = $_POST['security'];

            if( !wp_verify_nonce( $nonce, self::NONCE_ACTION_CUPOM_VALIDATION )) {
                throw new Exception('Erro na verificação de segurança', 422);
            }

            if (empty($coupon_code)) {
                throw new Exception('Cupom é obrigatório', 422);
                wp_die();
            }

            if (empty($check)) {
                throw new Exception('Cupom está incorreto ou não existe', 404);
                wp_die();
            }

            if ($check_is_used == 1) {
                throw new Exception('Cupom já foi utilizado', 401);
                wp_die();
            }

            $result = array(
                'url' => event_promotional_url_order( $product, $coupon_code ),
                'cupom_code' => $coupon_code,
            );
            wp_send_json_success( $result, 200 );
            wp_die();

        } catch (\Exception $e) {
            wp_send_json_error(array('Error' => $e->getMessage()), 422);
            wp_die();
        }
    }
}
