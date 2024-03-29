<?php
namespace Polen\Includes\Module;

use Polen\Admin\Polen_Admin_Event_Promotional_Event_Fields as Event_Promotional;
use Polen\Includes\Polen_Talent;
use Polen\Includes\Polen_Video_Info;
use Polen\Admin\Polen_Admin_B2B_Product_Fields as Adm_B2B_Fields;
use Polen\Includes\Polen_Order_Review;

class Polen_Product_Module
{

    const TAXONOMY_SLUG_CAMPAIGN = 'campaigns';
    
    public $object;

    /**
     * 
     * @param WC_Product
     */
    public function __construct( $product )
    {
        if( empty( $product ) ) {
            return null;
        }
        $this->object = $product;
    }

    public function get_is_campaign()
    {
        $product = $this->object;
        $campaign_taxonomies = wp_get_post_terms( $product->get_id(), self::TAXONOMY_SLUG_CAMPAIGN );
        if( empty( $campaign_taxonomies ) || is_wp_error( $campaign_taxonomies ) ) {
            return false;
        }
        return true;
    }

    public function get_campaign_slug()
    {
        if( !$this->get_is_campaign() ) {
            return '';
        }
        $product = $this->object;
        $campaign_taxonomies = wp_get_post_terms( $product->get_id(), self::TAXONOMY_SLUG_CAMPAIGN );
        if( !empty( $campaign_taxonomies[ 0 ]->parent ) ) {
            $campaign_taxonomies[ 0 ] = $this->get_term_top_most_parent( $campaign_taxonomies[ 0 ]->parent, self::TAXONOMY_SLUG_CAMPAIGN );
        }
        return $campaign_taxonomies[ 0 ]->slug;
    }

    public function get_term_top_most_parent( $term, $taxonomy ) {
        // Start from the current term
        $parent  = get_term( $term, $taxonomy );
        // Climb up the hierarchy until we reach a term with parent = '0'
        while ( $parent->parent != '0' ) {
            $term_id = $parent->parent;
            $parent  = get_term( $term_id, $taxonomy);
        }
        return $parent;
    }

    public function get_campaign_name()
    {
        if( !$this->get_is_campaign() ) {
            return '';
        }
        $product = $this->object;
        $campaign_taxonomies = wp_get_post_terms( $product->get_id(), self::TAXONOMY_SLUG_CAMPAIGN );
        return $campaign_taxonomies[ 0 ]->name;
    }


    /**
     * Get All orders IDs by product ID
     * *<Important>COLOCAR O wc- no inicio do STATUS</Important>
     *
     * @param  integer  $product_id (required)
     * @param  array    $order_status (optional) Default is 'wc-completed' precisa colocar o wc- no inicio
     *
     * @return array
     */
    public static function get_orders_ids_by_product_id( int $product_id, $order_status = array( 'wc-completed' ) ){
        global $wpdb;
        $results = $wpdb->get_col("
            SELECT order_items.order_id
            FROM {$wpdb->prefix}woocommerce_order_items as order_items
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
            AND order_items.order_item_type = 'line_item'
            AND order_item_meta.meta_key = '_product_id'
            AND order_item_meta.meta_value = '$product_id';
        ");
        return $results;
    }


    /**
     * Pega o nome da primeira Categoria do produto
     */
    public function get_category_name()
    {
        $categories_ids = $this->object->get_category_ids();
        $category_id = $categories_ids[ 0 ];
        $category = get_term_by( 'id', $category_id, 'product_cat' );
        if( empty( $category ) ) {
            return '';
        }
        return $category->name;
    }


    /**
     * Pega o slug da primeira Categoria do produto
     */
    public function get_category_slug()
    {
        $categories_ids = $this->object->get_category_ids();
        $category_id = $categories_ids[ 0 ];
        $category = get_term_by( 'id', $category_id, 'product_cat' );
        if( empty( $category ) ) {
            return '';
        }
        return $category->slug;
    }


    /**
     * Pega o slug da primeira Categoria do produto
     */
    public function get_categories()
    {
        $categories_ids = $this->object->get_category_ids();
        if( empty( $categories_ids ) ) {
            return [];
        }
        $categories = [];
        foreach( $categories_ids as $category_id) {
            $category = get_term_by( 'id', $category_id, 'product_cat' );
            $categories[] = $category;
        }
        return $categories;
    }

    /**
     * 
     */
    public function get_videos($json = false)
    {
        $items = array();
        $talent_user_id = $this->get_user_talent_id();
        $items_raw = Polen_Video_Info::select_by_talent_id($talent_user_id);
        foreach ($items_raw as $item) {
            $order = wc_get_order($item->order_id);
            $cart_item = \Polen\Includes\Cart\Polen_Cart_Item_Factory::polen_cart_item_from_order($order);
            $items[] = [
                'title' => '',
                'name' => $this->get_title(),
                'thumb' => polen_get_avatar($talent_user_id, 'polen-square-crop-lg'),
                'cover' =>  $item->vimeo_thumbnail,
                'video' => $item->vimeo_file_play,
                'hash' => $item->hash,
                'first_order' => $item->first_order,
                'initials' => $item->order_id == "4874"?'':polen_get_initials_name($cart_item->get_name_to_video()),
            ];
        }

        return $json ? json_encode($items) : $items;
    }


    /**
     * Funcao que retorna o ID do usuário dono do produto
     * @return int
     */
    public function get_user_talent_id()
    {
        $product_post = get_post($this->get_id());
        $talent       = get_user_by('id', $product_post->post_author);
        return $talent->ID;
    }

    public function get_is_charity()
    {
        return get_post_meta($this->get_id(), '_is_charity', true);
    }

    public function get_donate_name()
    {
        return get_post_meta($this->get_id(), '_charity_name', true);
    }


    public function get_donate_img_url()
    {
        return get_post_meta($this->get_id(), '_url_charity_logo', true);
    }


    public function get_donate_text()
    {
        return get_post_meta($this->get_id(), '_description_charity', true);
    }

    /**
     * Retorna uma HTML com os botões de compartilhar nas Redes Sociais
     */
    public function get_share_button()
    {
        polen_get_share_button();
    }


    public function get_in_stock()
    {
        if( 'instock' == $this->object->get_stock_status() ) {
            return true;
        } else {
            return false;
        }
    }

    public function get_deadline_in_days()
    {
        $days_pattern = '+7days';
        return date( "d/m/y", strtotime($days_pattern) );
    }

    /**
     * Retorna uma string padrão do badge na funcao 
     * Polen_Product_Module::get_donate_badge_html()
     * @return string
     */
    public function get_donate_base_text()
    {
        return "100% DO CACHÊ DOADO PARA ";
    }


    /**
     * Retorna uma HTML com o badge se o produto é para doação ou não
     * @return string HTML
     */
    public function get_donate_badge_html()
    {
        if(!$this->get_is_charity()) {
            return;
        }
        $text = $this->get_donate_text();
        if (empty(trim($text))) {
            return;
        }
        $result = <<<HTML
        <div class="row">
            <div class="col-md-12 mb-4">
                <span class="donate-badge">
                    <strong>
                        {$this->get_donate_base_text()}
                        {$this->get_donate_name()}
                    </strong>
                </span>
            </div>
        </div>
        HTML;
        return $result;
    }

    /**
     * Retorna o mesmo conteudo da funcao do Woocommerce
     * woocommerce_template_single_add_to_cart
     * @return string HTML
     */
    public function template_single_add_to_cart()
    {
        ob_start();
        woocommerce_template_single_add_to_cart();
        $result_html = ob_get_contents();
        ob_end_clean();
        return $result_html;
    }


    public function template_button_buy_b2b($inputs)
    {
        ob_start();
        $inputs->material_button_link("btn-b2b", "Pedir vídeo", enterprise_url_home() . '?talent='.$this->get_title().'#faleconosco', false, "", array(), $this->get_is_charity() ? "donate" : "");
        $result_html = ob_get_contents();
        ob_end_clean();
        return $result_html;
    }

    public function template_buy_buttons($inputs)
    {
        ob_start();
        polen_buy_buttons_b2c_b2b($inputs, $this);
        $result_html = ob_get_contents();
        ob_end_clean();
        return $result_html;
    }

    public function template_button_others_talents($inputs)
    {
        ob_start();
        $inputs->material_button_link("todos", "Escolher outro artista", home_url( "shop" ), false, "", array(), $this->get_is_charity() ? "donate" : "");
        $result_html = ob_get_contents();
        ob_end_clean();
        return $result_html;
    }

    public function template_deadline_html_page_details()
    {
        ob_start();
        polen_talent_deadline($this->get_deadline_in_days());
        $result_html = ob_get_contents();
        ob_end_clean();
        return $result_html;
    }

    public function template_donation_box_page_details()
    {
        if($this->get_is_charity()) {
            polen_front_get_donation_box(
                $this->get_donate_img_url(),
                $this->get_donate_text()
            );
        }
    }

    public function template_review_box_page_details()
    {
        $reviews = Polen_Order_Review::get_order_reviews_by_talent_id(
            $this->get_user_talent_id()
        );
        polen_talent_review($reviews);
    }


    /**
     * Cria uma opcao no Combobox Advanced para ir para a Pagina B2B
     * no frontend para o arquivo content-single-product.php
     * @param Input classe do templete polen
     * @return string
     */
    public function b2b_combobox_advanced_item_html($inputs)
    {
        $disabled = !$this->is_b2b();

        $range = get_post_meta(get_the_ID(), 'polen_price_range_b2b', false);
        $price_range = $range[0] ? "À partir de R$ {$range[0]}" : 'Valor sob consulta';

        if ($disabled) {
            $checked = false;
        } else {
            $checked = true;
        }

        return $inputs->pol_combo_advanced_item(
            "Vídeo para meu negócio", 
            "{$price_range}",
            "Compre um Vídeo Polen para usar no seu negócio",
            "check-b2b",
            "b2b",
            $checked,
            $disabled
        );
    }


    /**
     * Cria uma opcao no Combobox Advanced de adicionar ao carrinho (B2C)
     * no frontend para o arquivo content-single-product.php
     * @param Input classe do templete polen
     * @return string
     */
    public function b2c_combobox_advanced_item_html($inputs)
    {
        $is_b2b = $this->is_b2b();

        $disabled = false;
        
        if($is_b2b) {
            $checked = false;
        } else {
            $checked = true;
        }

        return $inputs->pol_combo_advanced_item(
            "Vídeo para uso pessoal",
            $this->get_price_html(),
            "Compre um vídeo personalizado para você ou para presentar outra pessoa",
            "check-pessoal", "pessoal",
            $checked,
            $disabled
        );
    }

    /**
     * Verifica se um Produto está habilitado para o B2B
     * @return bool
     */
    public function is_b2b()
    {
        if( empty( $this->object ) ) {
            return false;
        }
    
        $meta_b2b_is_enabled = $this->object->get_meta( Adm_B2B_Fields::FIELD_NAME_ENABLED_B2B, true );
    
        if( 'yes' === $meta_b2b_is_enabled ) {
            return true;
        }
        return false;
    }

    /**
     * Retorna a URL da imagem principal do produto
     * 
     * @return string URL || ''
     */
    public function get_image_url($thub_size = 'polen-thumb-lg')
    {
        $image_id = $this->object->get_image_id();
        $image_data = wp_get_attachment_image_src($image_id, $thub_size);
        if(empty($image_data)) {
            return '';
        }
        return $image_data[0];
    }


    /**
     * Pega o WP_Term de todas as tags de um produtos
     * 
     * @return array
     */
    public function get_terms_tags()
    {
        $tags = wp_get_object_terms($this->get_id(), 'product_tag');
        return $tags;
    }


    /**
     * Pega o preço apartir de a aba B2B
     * 
     * @return currency
     */
    public function get_price_from_b2b()
    {
        return $this->object->get_meta('polen_price_range_b2b');
    }


    /**
     * Pega os videos que serão apresentados na pagina de detalhes do produto
     * 
     * @return array
     */
    public function get_vimeo_videos_page_details()
    {
        $videos_vimeo_meta = $this->object->get_meta('vimeo_videos', true);
        if(empty($videos_vimeo_meta)) {
            return '';
        }
        $videos = unserialize($videos_vimeo_meta);
        if(!is_array($videos)) {
            return '';
        }
        return $videos;
    }

    /**
     * Pegar o SEO info
     */
    public function get_seo_title()
    {
        return get_field('seo_title', $this->get_id());
    }

    /**
     * Pegar o SEO info
     */
    public function get_seo_meta_title()
    {
        return get_field('meta_title', $this->get_id());
    }

    /**
     * Pegar o SEO info
     */
    public function get_seo_meta_description()
    {
        return get_field('meta_description', $this->get_id());
    }

    /**
     * Pegar o SEO info
     */
    public function get_seo_image()
    {
        return get_field('seo_image', $this->get_id());
    }





    /** ***********************
     * 
     * Comportamento dos WC_Products
     * 
     * 
     * */
    public function get_title()
    {
        return $this->object->get_title();
    }

    public function get_id()
    {
        return $this->object->get_id();
    }

    public function get_sku()
    {
        return $this->object->get_sku();
    }

    public function get_permalink()
    {
        return $this->object->get_permalink();
    }

    public function get_description()
    {
        return $this->object->get_description();
    }

    public function get_price_html()
    {
        return $this->object->get_price_html();
    }

    public function get_price()
    {
        return $this->object->get_price();
    }

    public function get_sale_price()
    {
        return $this->object->get_sale_price();
    }

    public function get_regular_price()
    {
        return $this->object->get_regular_price();
    }

    public function get_image($size = 'woocommerce_thumbnail', $attr = array(), $placeholder = true)
    {
        return $this->object->get_image($size, $attr, $placeholder);
    }

    public function get_image_id()
    {
        return $this->object->get_image_id();
    }

    public function get_posts_blogs_ids()
    {
        $posts_blog = get_field('posts_blog', $this->get_id());
        if (empty($posts_blog)) {
            return [];
        }

        return $posts_blog;
    }

    /**
     * retornar influencia por estado e cidade
     *
     * @return mixed|null
     */
    public function get_influence_by_region()
    {
        $influence = get_field('metrics', $this->get_id());
        if (empty($influence)) {
            return null;
        }

        return $influence;
    }

    /**
     * Retornar listagem de faixa etária
     *
     * @return array|null
     */
    public function get_age_group(): ?array
    {
        $age_group = get_field('age_group', $this->get_id());
        if (empty($age_group)) {
            return null;
        }

        return $age_group;
    }

    /**
     * Retonar audiência por gênero
     *
     * @return array
     */
    public function get_audience(): array
    {
        return [
            'man' => get_field('man', $this->get_id()),
            'woman' => get_field('woman', $this->get_id()),
        ];
    }
}
