<?php
namespace Polen\Api;

use Exception;
use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Debug;
use Polen\Includes\Polen_Talent;
use Polen\Includes\Polen_Video_Info;
use WC_Order;
use WC_Coupon;
use WP_REST_Response;
use WP_REST_Request;

class Api_Controller{

    private $checkout;

    public function __construct( bool $static = false )
    {
        if( $static ) {
            $this->checkout = new Api_Checkout();
        }
    }

    /**
     * Endpoint talent
     *
     * Retorar todos os talentos
     * @param WP_REST_Request $request
     */
    public function get_talents( WP_REST_Request $request ): WP_REST_Response
    {
        try{
            $api_product = new Api_Product();
            $params = $request->get_params();

            $slug = $params['campaign'] ?? '';
            $slug = !empty($params['campaign_category']) ? $params['campaign_category'] : $slug;

            $products = $api_product->polen_get_products_by_campagins($params, $slug);

            $items = array();
            foreach ($products->products as $product) {
                $image_object = $this->get_object_image($product->get_id());
                $items[] = array(
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'slug' => $product->get_slug(),
                    'image' => $image_object,
                    'categories' => wp_get_object_terms($product->get_id() , 'product_cat'),
                    'stock' => $product->is_in_stock() ? $this->check_stock($product) : 0,
                    'price' => $product->get_price(),
                    'regular_price' => $product->get_regular_price(),
                    'sale_price' => $product->get_sale_price(),
                    'createdAt' => get_the_date('Y-m-d H:i:s', $product->get_id()),
                );
            }

            $data = array(
                'items' => $items,
                'total' => $products->total,//$api_product->get_products_count($params, $slug),
                'current_page' => $request->get_param('paged') ?? 1,
                'per_page' => count($items),
            );

            return api_response($data, 200);

        } catch (\Exception $e) {
            return api_response(
                array('message' => $e->getMessage()),
                $e->getCode()
            );
        }
    }
    
    /**
     * Endpoint talent
     *
     * Retorar todos os talentos
     * @param $request
     */
    public function talent($request): WP_REST_Response
    {
        try{
            $talent_slug = $request->get_param('slug');
            if (empty($talent_slug)) {
                throw new Exception('slug é obrigatório', 422);
            }

            $campaign_slug = $request->get_param('campaign');
            if (empty($campaign_slug)) {
                throw new Exception('Campanha é obrigatório', 422);
            }

            $product_obj = get_page_by_path($talent_slug, OBJECT, 'product');

            if (empty($product_obj->ID)) {
                throw new Exception('Talento não encontrado', 404);
            }

            $tax_product = get_the_terms($product_obj->ID, 'campaigns');

            if (!isset($tax_product[0]) || $tax_product[0]->taxonomy !== 'campaigns') {
                throw new Exception('Talento não encontrado', 404);
            }

            $product = wc_get_product($product_obj->ID);
            $image_object = $this->get_object_image($product_obj->ID);

            $items = array(
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'slug' => $product->get_slug(),
                'b2b' => get_post_meta($product_obj->ID, 'polen_is_b2b', true),
                'image' => $image_object,
                'categories' => wp_get_object_terms($product->get_id() , 'campaigns'),
                'stock' => $product->is_in_stock() ? $this->check_stock($product) : 0,
                'price' => $product->get_price(),
                'regular_price' => $product->get_regular_price(),
                'sale_price' => $product->get_sale_price(),
                'createdAt' => get_the_date('Y-m-d H:i:s', $product->get_id()),
            );

            return api_response($items, 200);

        } catch (\Exception $e) {
            return api_response(
                array('message' => $e->getMessage()),
                $e->getCode()
            );
        }
    }

    /**
     * Validar requisição para carrinho
     *
     * @param $request
     */
    public function cart($request)
    {
        try{
            $product_id = $request->get_param('product_id');
            if (empty($product_id)) {
                throw new Exception('Parametro product_id é obrigatório', 422);
            }

            $product = wc_get_product($product_id);
            if (empty($product)) {
                throw new Exception('Produto não encontrado', 404);
            }

            if ($product->get_stock_status() !== 'instock') {
                throw new Exception('Produto sem estoque', 422);
            }

            $add_cart_item = WC()->cart->add_to_cart( $product_id, 1 );
            if( !$add_cart_item ) {
                throw new Exception('Produto não pode ser comprado', 422 );
            }

        } catch (\Exception $e) {
            return api_response( $e->getMessage(), 422 );
        }
    }

    /**
     * Endpoint que receberá o request e criará uma order através da class Api_Checkout
     *
     * @param $request
     */
    public function payment($request)
    {
        return $this->checkout->create_order($request);
    }

    /**
     * Rota para resetar a senha do usuario
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    function forgot_password(WP_REST_Request $request): WP_REST_Response
    {
        try {
            $email = $request->get_param('email') ?? null;

            if (null === $email) {
                throw new Exception('E-mail é obrigatório.', 422);
            }

            $user_wp = get_user_by('email', $email);
            if (!$user_wp) {
                throw new Exception('Usuário não encontrado', 503);
            }

            $new_password = wp_generate_password();

            $to = $email;
            $subject = 'Recuperação de Senha';
            $headers = array('Content-Type: text/html; charset=UTF-8');

            $args = array(
                'ID' => $user_wp->ID,
                'user_pass' => $new_password,
            );
            wp_update_user($args);

            $body = "<p>Segue abaixo sua nova senha para acesso ao Polen</p>";
            $body .= "<p><strong>Nova Senha: {$new_password}</strong></p>";

            wp_mail($to, $subject, $body, $headers);

            return api_response(null, 204);

        } catch (\Exception $e) {
            return api_response(
                array('message' => $e->getMessage()),
                $e->getCode()
            );
        }
    }

    /**
     * Endpoint para validação de cupom
     *
     * @param $request
     */
    public function check_coupon($request)
    {
        try {
            $coupon_code = $request->get_param('coupon_code');
            $product_id = $request->get_param('product_id');
            $value_cart = $request->get_param('cart_value');

            if (empty($coupon_code)) {
                return api_response('Cupom obrigatorio', 422);
            }

            if (empty($value_cart)) {
                return api_response('Valor atual do carrinho é obrigatório', 422);
            }

            $this->checkout->coupon_rules( $coupon_code, $product_id );

            $coupon = new WC_Coupon($coupon_code);

            $value_discount = $coupon->get_discount_amount($value_cart);

            $response = [
                'discounted_amount' => $value_cart - $value_discount,
                'discount_amount' => (int) $coupon->get_amount(),
                'discount_type' => $coupon->get_discount_type(),
            ];

            return api_response($response);
        } catch ( Exception $e ) {
            return api_response( $e->getMessage(), $e->getCode() );
        }
    }

    /**
     * Rotornar status e informações basicas de um pedido
     *
     * @param $order_id
     * @return WP_REST_Response
     */
    public function get_order_status($request): WP_REST_Response
    {
        try {
            $order_id = $request->get_param('order_id');
            $order = new WC_Order($order_id);

            $data = array(
                'id' => $order_id,
                'status_order' => $order->get_status(),
                'customer_email' => $order->get_billing_email(),
            );

            return api_response($data);
        } catch (\Exception $e) {
            return api_response(
                array('message' => $e->getMessage()),
                $e->getCode()
            );
        }
    }

    private function error($e)
    {
        wp_send_json_error($e, 422);
    }

    /**
     * Retornar meta dados da imagem
     *
     * @param int $talent_id
     * @return array
     */
    private function get_object_image(int $talent_id): array
    {
        $attachment = get_post(get_post_thumbnail_id($talent_id));
        if( empty( $attachment ) ) {
            return [];
        }
        return array(
            'id' => $attachment->ID,
            'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
            'caption' => $attachment->post_excerpt,
            'description' => $attachment->post_content,
            'src' => wp_get_attachment_image_src($attachment->ID, 'polen-thumb-lg')[0],
            'title' => $attachment->post_title,
        );
    }

    /**
     * Handler para pegar Videos por Produto por Camapanha
     */
    public function get_product_videos( $request )
    {
        $slug = $request[ 'slug' ];
        $campaign = $request->get_param( 'campaign' );
        if( empty( $campaign ) ) {
            return api_response( 'Campanha em branco', 403 );
        }
        $product_id = wc_get_product_id_by_sku( $slug );
        if( empty( $product_id ) ) {
            return api_response( 'Produto não encontrado', 404 );
        }
        $Polen_Talent = new Polen_Talent();
        $talent = $Polen_Talent->get_talent_from_product( $product_id );
        $videos = Polen_Video_Info::select_by_talent_id_and_campaign( $talent->ID, $campaign, 5 );

        $data = [];
        foreach( $videos as $video ) {
            $data[] = $this->propare_item_video( $video );
        }
        return api_response( $data, 200 );
    }

    public function get_payment_status($request)
    {
        $order_id = $request['id'];

        $current_status = $this->checkout->get_status($order_id);

        return api_response(
            [
                'paid' => $current_status == 'payment-approved',
                'payment_status' => $current_status
            ]
        );
    }

    /**
     * 
     * @param Video_Info
     */
    public function propare_item_video( $video )
    {
        $order = wc_get_order( $video->order_id );
        $cart_item = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
        $product = $cart_item->get_product();
        $categories = $this->get_categories_object_by_product( $product );
        return [
            'video_info_id'    => $video->ID,
            'talent_id'        => $video->talent_id,
            'order_id'         => $video->order_id,
            'vimeo_id'         => $video->vimeo_id,
            'hash'             => $video->hash,
            'talent_thumb'     => polen_get_avatar_src( $video->talent_id, 'polen-square-crop-lg' ),
            'talent_slug'      => $product->get_sku(),
            'talent_name'      => $product->get_title(),
            'talent_category'  => $categories,
            'cover'            => $video->vimeo_thumbnail,
            'video_url'        => $video->vimeo_file_play,
            'initials'         => polen_get_initials_name( $cart_item->get_name_to_video() ),
        ];
    }


    /**
     * Cria um Array de objectos para o Front-end
     * export interface Category {
     *   id: number;
     *   name: string;
     *   slug: string;
     * }
     * @param WC_Product
     * @return Array
     */
    protected function get_categories_object_by_product( $product )
    {
        $categories_ids = $product->get_category_ids();
        $categories = [];
        foreach( $categories_ids as $category_id ) {
            $term = get_term_by( 'id', $category_id, 'product_cat' );
            $categories[] = [
                'id'   => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
            ];
        }
        return  $categories;
    }

    /**
     * Gera um nonce para o checkout nao ficar 100% expostos
     */
    public function create_nonce( \WP_REST_Request $request )
    {
        // Debug::def( $_SERVER['HTTP_USER_AGENT'] , $_SERVER['REMOTE_ADDR'] );
        // return api_response( wp_create_nonce( $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] ) );
        return api_response( '1d13b5e353' );
    }

    /**
     * Verificar se o produto contem estoque e retornar a quantidade
     */
    private function check_stock($product)
    {
        $stock = $product->get_stock_quantity();
        if ($stock === null && $product->is_in_stock()) {
            $stock = true;
        }

        return $stock;
    }
}