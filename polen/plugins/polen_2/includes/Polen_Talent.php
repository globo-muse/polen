<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Polen\Includes;

use Exception;
use Polen\Includes\Cart\Polen_Cart_Item_Factory;
use Polen\Includes\Module\Polen_Order_Module;
use Polen\Includes\Polen_Order;
use Polen\Includes\v2\Polen_Order_V2;
use WP_Query;
use WP_User;

class Polen_Talent {

    const ROLE_SLUG = 'user_talent';
    const FLAG_FIRST_ORDER_DONE = 'first_order_done';
    public $tallent_slug;
    
    public function __construct($static = false) {
        $this->video_time = 45;
        if ($static) {
            $this->tallent_slug = 'talent';
            
            add_action('add_meta_boxes', array($this, 'choose_talent_metabox'));
            add_filter('save_post', array($this, 'save_talent_on_product'));
            add_action('rest_api_init', array($this, 'tallent_rest_itens'));
            add_action('user_register', array($this, 'create_talent_product'));
            add_action('admin_menu', array($this, 'talent_submenu'));
            add_filter('manage_users_columns', array($this, 'talent_filter_column'), 10, 1);
            add_filter('manage_edit-product_columns', array($this, 'talent_filter_product_column'), 10, 1);
            add_filter('manage_users_custom_column', array($this, 'talent_custom_users_value'), 10, 3);
            add_action('init', array($this, 'talent_taxonomy'));
            add_filter('manage_edit-shop_order_columns', array($this, 'show_talent_order_column'), 20);
            add_action('manage_shop_order_posts_custom_column', array($this, 'talent_column_content'), 20, 2);

            /**
             * Modifcar o texto do botão comprar
             */
            add_filter('woocommerce_product_single_add_to_cart_text', array($this, 'change_single_add_to_cart_text'));  // Single de produtos
            add_filter('woocommerce_product_add_to_cart_text', array($this, 'change_custom_product_add_to_cart_text')); // Archive (listagem) de produtos

            /**
             * Define que o usuário só pode comprar um produto por vez
             */
            add_filter('woocommerce_is_sold_individually', array($this, 'sold_individually'), 10, 2);

            /**
             * Remove o notice ao adicionar o produto no carrinho.
             */
            add_filter('wc_add_to_cart_message_html', '__return_false');

            /**
             * No login do talento redirecionamos para my-account/orders/ e não para o wp_admin
             */
            add_filter('login_redirect', array($this, 'login_redirect'), 11, 3);

            /**
             * Busca por talento
             */
            add_filter( 'posts_where', array($this, 'polen_include_tags_categories_in_search' ), 10, 2 );

            add_action( 'init', array( $this, 'my_account_send_video' ) );
            add_action( 'woocommerce_account_send-video_endpoint', array( $this, 'my_account_send_video_content' ) );
            add_action( 'woocommerce_account_success-upload_endpoint', array( $this, 'my_account_success_upload_content' ) );

            add_filter( 'woocommerce_get_availability_text', array( $this, 'remove_stock_text' ) );
        }

        global $wpdb;
        $this->table_talent = $wpdb->base_prefix . 'polen_talents';
    }

    /**
     * Add choose tallent metabox
     */
    function choose_talent_metabox() {
        add_meta_box('storeselect', __('Talento', 'polen'), array($this, 'talent_select'), 'product', 'normal', 'core');
    }

    /**
     * Metabox to select tallent on product edit
     */
    function talent_select( $post )
    {
        global $user_ID;

        $tallents = get_users(array('role' => self::ROLE_SLUG ));
        $current_tallent = empty($post->ID) ? '' : $post->post_author;
        ?>
        <select name="polen_choose_talent" id="polen_choose_talent">
            <?php
            foreach ($tallents as $key => $user) :
                $selected = '';
                if ($current_tallent == $user->ID) {
                    $selected = "selected='selected'";
                }
                ?>
                <option value="<?php echo esc_attr($user->ID) ?>" <?php echo $selected; ?> ><?php echo $user->display_name; ?></option>
            <?php endforeach ?>
        </select>
        <label for="subscribed-instagram">
            Quantidade de seguidores no instagram
            <input type="text" id="talent_subscribed_instagram"
                   name="talent_subscribed_instagram"
                   value="<?php echo esc_attr(get_post_meta(get_the_ID(), 'talent_subscribed_instagram', true)); ?>">
        </label>
        <?php
    }

    /**
     * Save tallent as product author
     */
    function save_talent_on_product($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['post_type']) && ( $_POST['post_type'] == 'product' )) {
            $chosen_tallent = isset($_POST['polen_choose_talent']) ? $_POST['polen_choose_talent'] : '';
            $post_author = sanitize_text_field($chosen_tallent);

            update_post_meta($post_id, 'talent_subscribed_instagram', sanitize_text_field($_POST['talent_subscribed_instagram']));

            if (!$post_author) {
                return;
            }

            $this->set_product_to_talent($post_id, $post_author);
            $this->update_talent_alias($post_id);
        }
    }

    public function create_talent_product($user_id) {
        if( $user_id && ! is_null( $user_id ) && ! empty( $user_id ) ) {
            $user_data = get_userdata( $user_id );
            $user_roles = $user_data->roles;
            $aa = new Polen_Update_Fields();
            $vendor_data = $aa->get_vendor_data($user_id);

            // verify if user is a talent
            if ( in_array( self::ROLE_SLUG, $user_roles, true ) ) {
                update_user_meta( $user_id, 'talent_enabled', '0' );
                $sku = ( isset( $vendor_data->talent_alias ) && ! is_null( $vendor_data->talent_alias ) && ! empty( $vendor_data->talent_alias ) ) ? $vendor_data->talent_alias : $user_data->user_nicename;
                // verify if the talent has a product
                Polen_Talent::set_first_order_status_by_talent_id($user_id, false);
                $user_product = new \WP_Query( array( 'author' => $user_id ) );
                if( ! $user_product->have_posts() ) {
                    $polen_update_field = new Polen_Update_Fields();
                    $vendor_data = $polen_update_field->get_vendor_data( $user_id );
                    $talent_alias = ( isset( $vendor_data->talent_alias ) && ! is_null( $vendor_data->talent_alias ) && ! empty( $vendor_data->talent_alias ) ) ? $vendor_data->talent_alias : sanitize_title( $user_data->first_name . ' ' . $user_data->last_name );
                    $product = new \WC_Product_Simple();
                    $product->set_name( $user_data->first_name . ' ' . $user_data->last_name );
                    $product->set_status( 'draft' );
                    $product->set_slug( $talent_alias );
                    $product->set_sku( $sku );
                    $product->set_virtual( true );
                    $product->set_sold_individually( 'yes' );
                    $product->save();
                    $id = $product->get_id();

                    if( $id && ! is_null( $id ) && ! empty( $id ) && $id > 0 ) {
                        $this->set_product_to_talent( $id, $user_id );
                    }

                    if( $id <= 0 ) {
                        trigger_error("Falha ao criar produto do usuário");
                    }
                }
            }
        }
    }

    public function tallent_rest_itens() {
        register_rest_field('talent_category', 'meta',
                array(
                    'get_callback' => function($object) {
                        return get_term_meta($object['id']);
                    },
                    'schema' => null,
                )
        );
    }

    public function talent_filter_product_column($columns) {
        unset($columns['is_in_stock']);
        return $columns;
    }

    public function talent_filter_column($columns) {
        unset($columns['posts']);
        $columns['status'] = 'Status';
        return $columns;
    }

    public function talent_submenu() {
        if (current_user_can('list_users')) {
            add_submenu_page('users.php', 'Talento', 'Talento', 'manage_options', 'users.php?role=' . self::ROLE_SLUG);
            add_submenu_page('users.php', 'Categoria Talento', 'Categoria Talento', 'manage_options', 'edit-tags.php?taxonomy=talent_category');
        }
    }

    public function talent_custom_users_value($value, $column, $user_id) {
        switch ($column) {
            case 'status' :
                $talent_enabled = get_the_author_meta('talent_enabled', $user_id);
                $str_status = '-';
                if ($talent_enabled == '0') {
                    $str_status = 'Desativado';
                } else if ($talent_enabled == '1') {
                    $str_status = 'Ativo';
                }
                return $str_status;
            default:
        }
        return $value;
    }

    public function talent_taxonomy() {
        if (!taxonomy_exists('talent_category')) {
            register_taxonomy(
                    'talent_category',
                    'user',
                    array(
                        'public' => true,
                        'show_ui' => true,
                        'show_in_menu' => true,
                        'query_var' => true,
                        'show_in_rest' => true,
                        'labels' => array(
                            'name' => __('Categoria de Talento', 'polen'),
                            'singular_name' => __('Categoria de Talento', 'polen'),
                            'menu_name' => __('Categoria de Talento', 'polen'),
                            'search_items' => __('Pesquisar Categoria de Talento', 'polen'),
                            'all_items' => __('Todas Categorias de Talento', 'polen'),
                            'edit_item' => __('Editar Categoria de Talento', 'polen'),
                            'update_item' => __('Atualizar Categoria de Talento', 'polen'),
                            'add_new_item' => __('Nova Categoria de Talento', 'polen'),
                        ),
                        'update_count_callback' => function() {
                            return;
                        }
                    )
            );
        }
    }

    public function set_product_to_talent($product_id, $talent_id) {
        global $wpdb;

        $result = $wpdb->update(
                'wp_posts',
                ['post_author' => $talent_id],
                ['ID' => $product_id]
        );

        if (is_wp_error($result)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Modifcar o texto do botão comprar na Single
     */
    public function change_single_add_to_cart_text() {
        global $post;
        $product = wc_get_product($post->ID);
        $social = social_product_is_social($product, social_get_category_base());
        if( $product->get_price() == 0 ) {
            $label = __( 'Pedir vídeo grátis' );
        } else {
            $text = $social ? "Doar" : "Pedir vídeo";
            $label = __($text, 'polen'); // . $product->get_price_html();
            // $label = __($text, 'polen') . number_format((float) $product->get_price(), 2, ',', '.');
        }
        return $label;
    }
    

    /**
     * Modifcar o texto do botão comprar no archive (listagem)
     */
    public function change_custom_product_add_to_cart_text() {
        global $post;
        $product = wc_get_product($post->ID);
        if( $product->get_price() == 0 ) {
            $label = __( 'Pedir vídeo grátis' );
        } else {
            $label = __('Pedir vídeo R$ ', 'polen') . number_format((float) $product->get_price(), 2, ',', '.');
        }
        return $label;
    }
    

    /**
     * Define que o usuário só pode comprar um produto por vez
     */
    public function sold_individually($return, $product) {
        $return = true;
        return $return;
    }
    

    public function show_talent_order_column($columns) {
        $new_columns = array();
        foreach ($columns as $key => $column) {
            $new_columns[$key] = $column;
            if ($key == 'order_date') {
                $new_columns['talent_product'] = __('Talento', 'polen');
            }

            if ($key == 'shipping_address') {
                $new_columns['expiration_invite'] = __('Data Limite', 'polen');
            }
        }
        return $new_columns;
    }

    /**
     * Calcular diferença entre data do pedido e prazo total para envio do vídeo
     *
     * @param $order_date
     * @param int $days
     * @return false|float
     */
    private function calculate_diff_date($order_date, $days = 7)
    {
        $day = date('Y-m-d');
        $date_start = date('Y-m-d', strtotime($order_date));
        $deadline = date('Y-m-d', strtotime("$days days", strtotime($order_date)));
        $interval = strtotime($deadline) - strtotime($day);

        return floor($interval / (60 * 60 * 24));
    }

    /**
     * Retornar prazo limite para envio do vídeo
     *
     * @param $order
     * @return string
     */
    private function get_messenger($order): string
    {
        $status = $order->get_status();
        $status_fail = [
            'talent-rejected',
            'cancelled',
            'failed',
            'payment-rejected',
            'order-expired'
        ];

        if (in_array($status, $status_fail)) {
            return '-';
        }

        $deadline_timestamp = $order->get_meta( Polen_Order::META_KEY_DEADLINE, true );
        $deadline_date = \WC_DateTime::createFromFormat( 'U', $deadline_timestamp );
        if( empty( $deadline_date ) ) {
            return '';
        }
        $deadline = $deadline_date->format('d/m/Y');
        $msg = "{$deadline}";

        return $msg;
    }

    public function talent_column_content($column, $post_id) {

        $order = wc_get_order($post_id);
        $message = 'Enviado';

        if (!$order->has_status('completed')) {
            $message = $this->get_messenger($order);
        }

        switch ($column) {
            case 'talent_product' :
                if ($order) {
                    foreach ($order->get_items() as $item_id => $item) {
                        echo $name = $item->get_name();
                    }
                } else {
                    echo '-';
                }
                break;
            case 'expiration_invite' :
                echo $message;
                break;
        }
    }
    

    /**
     * Pega Orders por Talento por Status
     * $status pode ser uma string separada por virgula "'wc-payment-approved', 'wc-talent-accepted'"
     * Se count for TRUE vai retornar o total
     * 
     * @param int
     * @param string
     * @param bool
     * @return array
     */
    public function get_talent_orders($talent_id, $status = false, $count = false ) {
        if ($talent_id) {
            global $wpdb;

            $sql_product = " SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'product' and post_author = " . $talent_id;
            $talent_products = $wpdb->get_results($sql_product);

            if( !$status ){
                $status = "'wc-payment-approved', 'wc-talent-accepted' " ;
            }else{
                $status = "'".$status."'";
            }

            $select = 'order_items.order_id'; 
            $limit = '';
            if( $count ){
                $select = "count( order_items.order_id ) as qtd"; 
            } else {
                $limit = ' LIMIT 50 ';
            }

            if (is_countable($talent_products) && count($talent_products) > 0) {
                $first_product = reset($talent_products);

                if (is_object($first_product) && isset($first_product->ID)) {
                    $sql = " SELECT {$select}
                    FROM {$wpdb->prefix}woocommerce_order_items as order_items
                    LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
                    LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
                    WHERE posts.post_type = 'shop_order'
                        AND posts.post_status IN ( ". $status ." )
                        AND order_items.order_item_type = 'line_item'
                        AND order_item_meta.meta_key = '_product_id'
                        AND order_item_meta.meta_value = '$first_product->ID'
                        {$limit};";

                        $order_list = $wpdb->get_results($sql);

                    if (is_countable($order_list) && count($order_list) == 0) {
                        return false;
                    } else {
                        $obj = array();

                        if( $count ){
                            foreach ($order_list as $obj_order):
                                $obj['qtd'] = $obj_order->qtd;
                            endforeach;   
                            return $obj; 
                        }

                        foreach ($order_list as $obj_order):
                            $obj['order_id'] = $obj_order->order_id;
                            $order = wc_get_order($obj_order->order_id);
                            $obj['origin'] = get_origin_order($order);
                            $obj['status'] = $order->get_status();

                            $obj['total'] = $order->get_formatted_order_total();
                            $obj['total_value'] = $order->get_total();
                            $obj['total_raw'] = $order->get_subtotal();
                            foreach ($order->get_items() as $item_id => $item) {
                                $obj['email'] = $item->get_meta('email_to_video', true);
                                $obj['instructions'] = $item->get_meta('instructions_to_video', true);
                                $obj['name'] = $item->get_meta('name_to_video', true);
                                $obj['from'] = $item->get_meta('offered_by', true);
                                $obj['category'] = $item->get_meta('video_category', true);
                            }

                            $robj[] = $obj;
                        endforeach;
                        return $robj;
                    }
                }
            }

            return false;
        }
    }

    
    /**
     * Se um talento tentar logar pelo wp-login.php sera redirecionado para /my-account/orders/
     * 
     * @param string $redirect_to
     * @param string $requested_redirect_to
     * @param WP_User | WP_Error $user
     * @return string
     */
    public function login_redirect($redirect_to, $requested_redirect_to, $user) {
        if (is_wp_error($user)) {
            return $requested_redirect_to;
        }

        if ($this->is_user_talent($user)) {
            $redirect_to = site_url('/my-account/orders/');
        }

        return $redirect_to;
    }
    
    /**
     * Verifica se um usuário é um talento
     * @param \WP_User $user
     * @return boolean
     */
    public function is_user_talent(\WP_User $user) {
        $roles = $user->roles;
        if (in_array( self::ROLE_SLUG, $roles ) !== false) {
            return true;
        }
        return false;
    }

    
    /**
     * Funcao Estática Verifica se um usuário é um talento
     * @param \WP_User $user
     * @return boolean
     */
    static public function static_is_user_talent(\WP_User $user = null) {
        if(empty($user)) {
            return false;
        }
        $roles = $user->roles;
        if (in_array( self::ROLE_SLUG, $roles ) !== false) {
            return true;
        }
        return false;
    }
    
    public function polen_include_tags_categories_in_search( $where, $query ) {
        if( $query->is_search() ) {
          global $wpdb;
          $term = $_REQUEST['s'] ?? '';
          $sql_terms = "
              SELECT DISTINCT( P.ID )
              FROM " . $wpdb->posts . " P
              INNER JOIN " . $wpdb->term_relationships . " TR ON TR.object_id = P.ID
              INNER JOIN " . $wpdb->term_taxonomy . " TT ON TT.term_taxonomy_id = TR.term_taxonomy_id
              INNER JOIN " . $wpdb->terms . " T ON T.term_id = TT.term_id
              WHERE
                P.post_status = 'publish'
                AND P.post_type = 'product'
                AND TT.taxonomy IN ( 'product_tag', 'product_cat' )
                AND UPPER( T.name ) LIKE UPPER( '" . esc_sql($term) . "' )
                ";
          $res = $wpdb->get_results( $sql_terms, ARRAY_A );
          if( $res && ! is_null( $res ) && ! is_wp_error( $res ) && is_array( $res ) && count( $res ) > 0 ) {
            $values = array_column( $res, 'ID' );
            $where .= " OR " . $wpdb->posts . ".ID IN ( " . implode( ", ", $values ) . " )";
          }
        }
      
        return $where;
      }

    /**
     * Totalizador dos pedidos do talento
     * @param int $talent_id
     * @param string|array default array('wc-payment-approved', 'wc-talent-accepted')
     * @return string wc_price()
     */
    public function get_total_by_order_status( $talent_id, $status = false ){
        if ($talent_id) {
            global $wpdb;

            $sql_product = " SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'product' and post_author = " . $talent_id;
            $talent_products = $wpdb->get_results($sql_product);

            if( !$status ){
                $status = array( 'wc-payment-approved', 'wc-talent-accepted' );
            }

            if( is_string( $status )) {
                $status = array( $status );
            }
            if( is_array( $status )) {
                $status = '"' . implode( '","' , $status ) . '"';
            }

            if (is_countable($talent_products) && count($talent_products) > 0) {
                $first_product = reset($talent_products);

                if (is_object($first_product) && isset($first_product->ID)) {
                    $sql = "SELECT SUM( oim_line_total.meta_value ) as order_total 
                        FROM {$wpdb->posts} AS posts
                        INNER JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id
                        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim_line_total ON (order_items.order_item_id = oim_line_total.order_item_id)
                            AND (oim_line_total.meta_key = '_line_subtotal')
                        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim_product ON order_items.order_item_id = oim_product.order_item_id 
                        WHERE posts.post_type IN ( 'shop_order' )
                        AND posts.post_status IN ( {$status} ) AND ( ( oim_product.meta_key IN ('_product_id','_variation_id') 
                        AND oim_product.meta_value IN ('{$first_product->ID}') ) );";
                    $total_sales = $wpdb->get_var( $sql );

                    return wc_price( $total_sales );
                }
            }

            return false;
        }
    }

    /**
     * Totalizador dos pedidos do talento
     * @param int $talent_id
     * @param string|array default array('wc-payment-approved', 'wc-talent-accepted')
     * @return string wc_price()
     */
    public function get_total_by_order_status_return_raw( $talent_id, $status = false ){
        if ($talent_id) {
            global $wpdb;

            $sql_product = " SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'product' and post_author = " . $talent_id;
            $talent_products = $wpdb->get_results($sql_product);

            if( !$status ){
                $status = array( 'wc-payment-approved', 'wc-talent-accepted' );
            }

            if( is_string( $status )) {
                $status = array( $status );
            }
            if( is_array( $status )) {
                $status = '"' . implode( '","' , $status ) . '"';
            }

            if (is_countable($talent_products) && count($talent_products) > 0) {
                $first_product = reset($talent_products);

                if (is_object($first_product) && isset($first_product->ID)) {
                    $sql = "SELECT SUM( oim_line_total.meta_value ) as order_total 
                        FROM {$wpdb->posts} AS posts
                        INNER JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id
                        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim_line_total ON (order_items.order_item_id = oim_line_total.order_item_id)
                            AND (oim_line_total.meta_key = '_line_subtotal')
                        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim_product ON order_items.order_item_id = oim_product.order_item_id 
                        WHERE posts.post_type IN ( 'shop_order' )
                        AND posts.post_status IN ( {$status} ) AND ( ( oim_product.meta_key IN ('_product_id','_variation_id') 
                        AND oim_product.meta_value IN ('{$first_product->ID}') ) );";
                    $total_sales = $wpdb->get_var( $sql );

                    return $total_sales;
                }
            }

            return false;
        }
    }


    /**
     * 
     */
    public function get_time_to_videos( $user ){
        if( $this->is_user_talent( $user ) ) {
            $pending = $this->get_talent_orders( $user->ID, false, true );
            if( is_array( $pending ) && isset( $pending['qtd'] ) && (int) $pending['qtd'] > 0  ){
                $time_to_spend = (int) $pending['qtd'] * (int) $this->video_time;
                $total_time = $time_to_spend;
                
                if( $time_to_spend >= 45 ){
                    $hours = floor($total_time/3600);
                    $minutes = floor(($total_time/60) % 60);
                    $seconds = $total_time % 60;
                    
                    if( !empty( $hours ) ){
                        $total_time = str_pad( $hours, 2, 0, STR_PAD_LEFT ).':'.str_pad( $minutes, 2, 0, STR_PAD_LEFT ).':'.str_pad( $seconds, 2, 0, STR_PAD_LEFT ).' horas ';
                    }

                    if( empty( $hours ) && !empty( $minutes ) ){
                        $total_time = str_pad( $minutes, 2, 0, STR_PAD_LEFT ).':'.str_pad( $seconds, 2, 0, STR_PAD_LEFT ).' minutos ';
                    }

                    if( empty( $minutes ) && !empty( $seconds ) ){
                        $total_time = str_pad( $seconds, 2, 0, STR_PAD_LEFT ).' segundos ';
                    } 
                }
                return $total_time;
            }
        }
        return false;
    }

    /**
     * Cálculo para tempo de expiração do pedido
     */
    public function video_expiration_time( $user, $order_id, $social = false ){
        if( $this->is_user_talent( $user ) && !empty( $order_id )) {
            $order = wc_get_order( $order_id );
            // $first_dateTime = new \DateTime($order->post_date); 
            $last_dateTime = new \WC_DateTime($order->get_date_created()); 

            $current_date = new \WC_DateTime( "now" );

            //Produto Social do crianca esperanca é 15dias
            if( social_order_is_social( $order ) ) {
                $interval_days = 'P15D';
            } elseif ( event_promotional_order_is_event_promotional( $order ) ) {
                $interval_days = 'P30D';
            } else {
                $interval_days = 'P7D';
            }
            
            $last_dateTime->add(new \DateInterval( $interval_days ));
            // $fomattedDate = $last_dateTime->format('Y-m-d H:i:s');

            $interval = $current_date->diff($last_dateTime);
            if( $interval->format('%D') > 1 && $interval->format('%R') == '+' ){
                return $interval->format('%D dias');
            }

            if( $interval->format('%D') == 1 && $interval->format('%R') == '+' ){
                return $interval->format('%D dia e %H:%ih');
            }    

            if( $interval->format('%D') < 1 && $interval->format('%R') == '+' ){
                return $interval->format('%H:%ih');
            }    

            if( $interval->format('%R') == '-' ){
                return 'Expirado!';
            }            

        }    
        return false;
    }

    /**
     * Retorna os vídeos pelo id do talento
     */
    public function videos_by_talent_id( $talent_id ){
        $arr_video_url = array();
        $arr_completed_video_info = Polen_Video_Info::select_by_talent_id( $talent_id );
        if( is_array( $arr_completed_video_info ) && !empty( $arr_completed_video_info ) ){
            foreach( $arr_completed_video_info as $video_info ):
                $arr_video_url[] = $video_info->vimeo_link;
            endforeach;
        }
        return $arr_video_url;
    }

    /**
     * Criando as Rotas dentro do My-Account
     */
    public function my_account_send_video(){
        add_rewrite_endpoint( 'send-video', EP_PAGES );
        add_rewrite_endpoint( 'success-upload', EP_PAGES );
        add_rewrite_endpoint( 'watch-video', EP_PAGES );
    }
    

    public function my_account_send_video_content()
    {
        $order = $this->get_safe_order_in_param_get();
        $cart_item = Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
        $talent_id = $cart_item->get_talent_id();

        if( intval( $talent_id ) !== intval( get_current_user_id() ) ) {
            wp_safe_redirect( site_url( 'my-account/orders' ) );
            exit;
        }
        require_once PLUGIN_POLEN_DIR . '/publics/partials/polen_talent_send_video_form.php'; 
    }
    
    
    /**
     * Apresentação da tela de Video enviado com Sucesso
     */
    public function my_account_success_upload_content()
    {
        $user = wp_get_current_user();
        if( !$this->is_user_talent( $user ) ) {
            wp_safe_redirect(site_url('my-account/orders'));
            exit;
        }
        $order = $this->get_safe_order_in_param_get();
        require_once PLUGIN_POLEN_DIR . '/publics/partials/polen_talent_success_upload.php';
    }
    
    /**
     * Pegar o Object Order baseado no Parametro GET entro @param
     * @param string $param_name Parametro no $_GET
     * @return \WC_Order
     */
    private function get_safe_order_in_param_get( string $param_name = 'order_id' )
    {
        $order_id = filter_input( INPUT_GET, $param_name, FILTER_SANITIZE_NUMBER_INT );
        $order = wc_get_order( $order_id );
        if( empty( $order ) ) {
            wp_safe_redirect( site_url( 'my-account/orders?no_order' ) );
            exit;
        }
        return $order;
    }

    /**
     * Pegar o talento pelo ID do Produto
     * @param int $product_id
     * @return stdClass Polen\Includes\Polen_Talent
     */
    public function get_talent_from_product( $product_id )
    {
        global $wpdb;
        $sql = "
            SELECT U.`ID`, U.`display_name` AS `name`, U.`user_email` AS `email`, P.`post_title` AS `product`
            FROM `" . $wpdb->users . "` U
            LEFT JOIN `" . $wpdb->posts . "` P ON P.`post_author` = U.`ID`
            WHERE P.`ID`=" . $product_id;
        $res = $wpdb->get_results( $sql );
        if( $res && ! is_null( $res ) && is_array( $res ) && ! empty( $res ) ) {
            return $res[0];
        }
    }

    /**
     * Remove texto de quantidade em estoque e de indisponível no estoque
     */
    public function remove_stock_text( $text ){
        return '';
    }

    /**
     * Salva o slug do produto no talento
     */
    public function update_talent_alias( $post_id ) {
        if( $post_id && ! is_null( $post_id ) && $post_id > 0 ) {
            $product = get_post( $post_id );
            global $wpdb;
            $wpdb->update(
                $this->table_talent,
                array(
                    'talent_alias' => $product->post_name,
                    'talent_url'   => get_permalink( $post_id ),
                ),
                array(
                    'user_id' => $product->post_author,
                )
            );

            $wpdb->update(
                $wpdb->users,
                array(
                    'user_nicename' => $product->post_name,
                ),
                array(
                    'ID' => $product->post_author,
                )
            );
        }
    }


    /**
     * Pega o produto pelo User_ID
     * @param int
     */
    static public function get_product_by_user_id( $user_id )
    {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'any',
            'author' => $user_id,
        );
        $posts = get_posts( $args );
        if( empty( $posts ) ) {
            return null;
        }
        $post = $posts[ 0 ];
        return wc_get_product( $post );
    }

    /**
     * Enviar Request para webwook quando um talento for cadastrado
     *
     * @param $user_id
     * @param $userdata
     */
    function action_user_talent_register($user_id, $userdata)
    {
        if ($userdata['role'] != 'user_talent') {
            return;
        }

        $url = 'https://hooks.zapier.com/hooks/catch/10583855/bucjtyy/';
        wp_remote_post($url, array(
                'method' => 'POST',
                'timeout' => 45,
                'headers' => array(),
                'body' => array(
                    'name' => $userdata['first_name'],
                    'email' => $userdata['email'],
                ),
            )
        );
    }


    /**
     * Pegar todas as orders pelo o ID e status
     *
     * @param integer $product_id (required)
     * @param string orderby
     * @param array $order_status (optional)
     *
     * @return array
     */
    public function get_orders_ids_by_product_id(
        int $product_id,
        string $orderby = 'ASC',
        array $order_status = ['wc-payment-approved', 'wc-talent-accepted']
    ): array
    {
        global $wpdb;

        return $wpdb->get_col("
            SELECT order_items.order_id
                FROM {$wpdb->prefix}woocommerce_order_items as order_items
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
                AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
                AND order_items.order_item_type = 'line_item'
                AND order_item_meta.meta_key = '_product_id'
                AND order_item_meta.meta_value = $product_id
                AND 0 = (SELECT COUNT(*) FROM {$wpdb->prefix}video_info AS vi WHERE vi.order_id = posts.ID)
                ORDER BY order_items.order_id {$orderby};
        ");
    }

    /**
     * Retornar as Orders de acordo com os filtros
     *
     * @param filter
     * @param Timestamp
     */
    public function get_order_ids($products_id, $filter = [])
    {
        $data = $this->get_orders_ids_by_product_id($products_id[0], $filter['orderby'], $filter['status']);

        if (isset($filter['deadline']) && !empty($filter['deadline'])) {
            $response = Polen_Order_V2::get_orders_by_products_id_deadline($products_id, $filter['status'], $filter['orderby']);
            $data = $this->get_ids_in_array($response);
        }

        return $data;
    }

    /**
     * Pega Orders por Talento por Status
     * $status pode ser uma array separada por virgula ['wc-payment-approved', 'wc-talent-accepted']
     *
     * @param int $talent_id
     * @return array
     */
    // public function get_talent_orders_v2(int $talent_id): array
    // {
    //     $product_id = $this->get_product_id_by_talent_id($talent_id);
    //     $orders_ids = $this->get_orders_ids_by_product_id($product_id, ['wc-payment-approved', 'wc-talent-accepted']);

    //     return $this->get_orders_info($orders_ids);

    // }



    /**
     * Retornar ID do produto pelo o ID do talento
     *
     * @param int $talent_id
     * @return int
     */
    public function get_product_id_by_talent_id(int $talent_id): int
    {
        $args = [
            'post_type' => 'product',
            'author' => $talent_id,
        ];

        $query = new WP_Query($args);
        $talent_product = $query->get_posts();

        return $talent_product[0]->ID;
    }


    /**
     * Retorna as informacoes de uma order
     * @param int $order_id
     * @return array
     */
    public function get_order_info_v2($order_id)
    {
        $obj['order_id']     = $order_id;
        $wc_order            = wc_get_order($order_id);
        $order               = new Polen_Order_Module($wc_order);
        $raw_instruction     = $order->get_instructions_to_video();
        $instruction         = Polen_Utils::remove_sanitize_xss_br_escape($raw_instruction, 'edit');
        $instruction         = Polen_Utils::remove_sanitize_xss_br_escape($instruction, 'edit');
        $obj['status']       = $order->get_status();
        // $obj['total']        = $order->get_formatted_order_total();
        // $obj['total_value']  = $order->get_total();
        $obj['total_value'] = $order->get_total_for_talent();
        // $obj['total_raw']    = $order->get_subtotal();
        $obj['instructions'] = $instruction;;
        $obj['name']         = $order->get_name_to_video();
        $obj['from']         = $order->get_offered_by();
        $obj['to_myself']    = $order->get_video_to_is_to_my_self();
        $obj['category']     = $order->get_video_category();
        $obj['deadline']     = $order->get_deadline();

        return $obj;
    }

    /**
     * Retornar numero de orders com expiração para o dia atual
     *
     * @param array $orders_id
     * @return int|null
     */
    public function get_products_deadline_today_count(array $orders_id): ?int
    {
        return Polen_Order_V2::get_qty_orders_by_products_id_deadline($orders_id, date('Y/m/d'));;
    }

    /**
     * Formatar resultado na consulta com get_results (ARRAY_A)
     *
     * @param array $orders_id
     * @return array
     */
    private function get_ids_in_array(array $orders_id): array
    {
        $data = [];
        foreach ($orders_id as $order_id) {
            $data[] = $order_id['order_id'];
        }

        return $data;
    }

    /**
     * Retornar informações basicas de um array de orders
     *
     * @param array $orders_id
     * @return array
     */
    public function get_orders_info(array $orders_id): array
    {
        $objects = [];

        foreach ($orders_id as $order_id) {
            $objects[] = $this->get_order_info_v2($order_id);
        }

        return $objects;
    }



    /**
     * Recalcula o tempo médio que o talento atende o vídeo
     * @param int user_id
     */
    public function average_video_response( $talent_id ){
        if( !empty( $talent_id ) ){
            global $wpdb;

            $sql = "SELECT TIMESTAMPDIFF(HOUR, p.post_date, vi.updated_at) as average_time 
                    FROM {$wpdb->prefix}video_info vi 
                    INNER JOIN {$wpdb->posts} p on p.id = order_id 
                    WHERE vi.talent_id = {$talent_id} and vi.vimeo_process_complete = 1 AND vi.updated_at is not null
                    ORDER BY vi.created_at DESC LIMIT 2 ";
            $all_times = $wpdb->get_results( $sql );

            if( $all_times ){
                try{
                    if( count($all_times) == 2 ){
                        $average_hour = 0;
                        foreach( $all_times as $this_time ){
                            $average_hour += $this_time->average_time;
                        }

                        $average_time = ceil( $average_hour/2 );
                        if( floor( $average_time ) < 48 ){
                            $average_time = 48;
                        }else if( floor( $average_time ) > 72 ){
                            $average_time = ceil( $average_time );
                        }
                    }  

                    if( count( $all_times ) == 1 ){
                        if( isset( $all_times[0] ) ){
                            if( isset( $all_times[0]->average_time ) ){
                                $average_time = ceil( $all_times[0]->average_time );
                            }
                        }
                    }

                    $update = $wpdb->update(
                        $wpdb->base_prefix . 'polen_talents',
                        array( 'tempo_resposta' => $average_time ),
                        array( 'user_id' => $talent_id ),
                    );
                }catch( Exception $e ){}
            }
        }
    }

    /**
     * 
     */
    public static function set_first_order_status_by_talent_id($user_id, $done = false) {
        return update_user_meta($user_id, self::FLAG_FIRST_ORDER_DONE, $done ? '1' : '0');
    }


    /**
     * 
     */
    public static function get_first_order_status_by_talent_id($user_id) {
        return get_user_meta($user_id, self::FLAG_FIRST_ORDER_DONE, true);
    }


    /**
     * 
     */
    public static function get_first_order_id_by_talent_id($user_id) {
        $product = self::get_product_by_user_id($user_id);
        if(empty($product) || is_wp_error($product)) {
            return false;
        }
        return Polen_Order::get_first_order_by_product_id($product->get_id());
    }

}
    
