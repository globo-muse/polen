<?php
namespace Polen\Admin;

class Polen_Admin_Event_Promotional_Event_Fields
{
    const ACTION_NAME      = 'polen_custom_fields_promotional_event';
    const TAB_NAME         = 'polen_promotional_event_tab';
    const TAB_CONTENT_NAME = 'polen_promotional_event_tab_data';
    
    const FIELD_NAME_IS             = '_promotional_event';
    const FIELD_NAME_SLUG_CAMPAIGN  = '_promotional_event_slug_campaign';
    const FIELD_NAME_PAGES_QUANTITY = '_promotional_event_pages_quantity';
    const FIELD_NAME_LANGUAGE       = '_promotional_event_language';
    const FIELD_NAME_PUBLISHING     = '_promotional_event_publishing';
    const FIELD_NAME_PUBLISHED_IN   = '_promotional_event_published_in';
    const FIELD_NAME_RATING         = '_promotional_event_rating';
    const FIELD_NAME_LINK_BUY       = '_promotional_event_link_buy';
    const FIELD_NAME_AUTHOR         = '_promotional_event_author';
    const FIELD_NAME_URL_WATERMARK  = '_promotional_event_wartermark';

    public function __construct( bool $static = false )
    {
        if( $static ) {
            add_filter( 'woocommerce_product_data_tabs', array( $this, 'tabs' ) );
            add_filter( 'woocommerce_product_data_panels', array( $this, 'tab_content' ) );
            
            //Action Customizada pois é preciso desabilitar a action padrao
            //woocommerce_update_product pois ela entra em Loop
            //por isso dessa action: Includes\Polen_Woocommerce
            add_action( self::ACTION_NAME, array( $this, 'on_product_save' ) );
        }
    }


    public function tabs( $array ){
        $array[ self::TAB_NAME ] = array(
            'label'    => 'Video de Campanha',
            'target'   => self::TAB_CONTENT_NAME,
            'class'    => array(),
            'priority' => 90,
        );
        return $array;
    }



    public function tab_content()
    {
        global $product_object; ?>

        <div id="<?php echo self::TAB_CONTENT_NAME; ?>" class="panel woocommerce_options_panel hidden">
            <div class='options_group'>
            <?php
                woocommerce_wp_checkbox(
                    array(
                        'id'      => self::FIELD_NAME_IS,
                        'value'   => $product_object->get_meta( self::FIELD_NAME_IS ) == 'yes' ? 'yes' : 'no',
                        'label'   => 'É um Vídeo de Campanha',
                        'cbvalue' => 'yes',
                    )
                );
            ?>
            </div>

            
            <div class="options_group">
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'                => self::FIELD_NAME_SLUG_CAMPAIGN,
                        'value'             => $product_object->get_meta( self::FIELD_NAME_SLUG_CAMPAIGN ),
                        'label'             => 'Slug da campanha',
                        'desc_tip'          => true,
                        'description'       => 'Slug da campanha',
                        'type'              => 'text',
                    )
                );
                ?>
            </div>


            <div class="options_group">
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'                => self::FIELD_NAME_PAGES_QUANTITY,
                        'value'             => $product_object->get_meta( self::FIELD_NAME_PAGES_QUANTITY ),
                        'label'             => 'Qtd de Paginas',
                        'desc_tip'          => true,
                        'description'       => 'Quantidade de pagidas do Livro',
                        'type'              => 'text',
                    )
                );
                ?>
            </div>
            
            <div class="options_group">
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'                => self::FIELD_NAME_LANGUAGE,
                        'value'             => $product_object->get_meta( self::FIELD_NAME_LANGUAGE ),
                        'label'             => 'Idioma',
                        'desc_tip'          => true,
                        'description'       => 'Idioma do Livro',
                        'type'              => 'text',
                    )
                );
                ?>
            </div>
            
            <div class="options_group">
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'          => self::FIELD_NAME_PUBLISHING,
                        'value'       => $product_object->get_meta( self::FIELD_NAME_PUBLISHING ),
                        'label'       => 'Editora',
                        'desc_tip'    => true,
                        'description' => 'Editora do livro.',
                        'type'        => 'text',
                    )
                );
                ?>
            </div>
            
            <div class="options_group">
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'          => self::FIELD_NAME_PUBLISHED_IN,
                        'value'       => $product_object->get_meta( self::FIELD_NAME_PUBLISHED_IN ),
                        'label'       => 'Publicado em',
                        'desc_tip'    => true,
                        'description' => 'Data de publicação.',
                        'type'        => 'date',
                    )
                );
                ?>
            </div>
            
            <div class="options_group">
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'          => self::FIELD_NAME_RATING,
                        'value'       => $product_object->get_meta( self::FIELD_NAME_RATING ),
                        'label'       => 'Score do Livro ex: <b>4.2</b>',
                        'desc_tip'    => true,
                        'description' => 'Nota dos leitores do livro.',
                        'type'        => 'text',
                    )
                );
                ?>
            </div>

            <div class="options_group">
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'          => self::FIELD_NAME_LINK_BUY,
                        'value'       => $product_object->get_meta( self::FIELD_NAME_LINK_BUY ),
                        'label'       => 'Link de compra',
                        'desc_tip'    => true,
                        'description' => 'Link para comprar o livro.',
                        'type'        => 'text',
                    )
                );
                ?>
            </div>

            <div class="options_group">
                <?php
                woocommerce_wp_text_input(
                    array(
                        'id'          => self::FIELD_NAME_AUTHOR,
                        'value'       => $product_object->get_meta( self::FIELD_NAME_AUTHOR ),
                        'label'       => 'Autor',
                        'desc_tip'    => true,
                        'description' => 'Autor do livro.',
                        'type'        => 'text',
                    )
                );
                ?>
            </div>            
        </div>
        <?php
    }


    /**
     * 
     */
    public function on_product_save( $product_id )
    {
        if( is_admin() ) {
            $screen = get_current_screen();
            if ( $screen->base == 'post' && $screen->post_type == 'product' ) {
                $product = wc_get_product( $product_id );

                $promotional_event                = strip_tags(filter_input(INPUT_POST, self::FIELD_NAME_IS));
                $promotional_event_campaign_slug  = strip_tags(filter_input(INPUT_POST, self::FIELD_NAME_SLUG_CAMPAIGN));
                $promotional_event_pages_quantity = strip_tags(filter_input(INPUT_POST, self::FIELD_NAME_PAGES_QUANTITY));
                $promotional_event_language       = strip_tags(filter_input(INPUT_POST, self::FIELD_NAME_LANGUAGE));
                $promotional_event_publishing     = strip_tags(filter_input(INPUT_POST, self::FIELD_NAME_PUBLISHING));
                $promotional_event_published_in   = strip_tags(filter_input(INPUT_POST, self::FIELD_NAME_PUBLISHED_IN));
                $promotional_event_rating         = strip_tags(filter_input(INPUT_POST, self::FIELD_NAME_RATING));
                $promotional_event_link_buy       = strip_tags(filter_input(INPUT_POST, self::FIELD_NAME_LINK_BUY));
                $promotional_event_author         = strip_tags(filter_input(INPUT_POST, self::FIELD_NAME_AUTHOR));

                $this->save_meta($product, $promotional_event, self::FIELD_NAME_IS );
                $this->save_meta($product, $promotional_event_campaign_slug, self::FIELD_NAME_SLUG_CAMPAIGN );
                $this->save_meta($product, $promotional_event_pages_quantity, self::FIELD_NAME_PAGES_QUANTITY );
                $this->save_meta($product, $promotional_event_language, self::FIELD_NAME_LANGUAGE );
                $this->save_meta($product, $promotional_event_publishing, self::FIELD_NAME_PUBLISHING );
                $this->save_meta($product, $promotional_event_published_in, self::FIELD_NAME_PUBLISHED_IN );
                $this->save_meta($product, $promotional_event_rating, self::FIELD_NAME_RATING );
                $this->save_meta($product, $promotional_event_link_buy, self::FIELD_NAME_LINK_BUY );
                $this->save_meta($product, $promotional_event_author, self::FIELD_NAME_AUTHOR );

                remove_action( self::ACTION_NAME, array( $this, 'on_product_save' ) );
                $product->save();
                add_action( self::ACTION_NAME, array( $this, 'on_product_save' ) );
            }
        }
    }


    /**
     * 
     */
    private function save_meta( &$product, $value, $key )
    {
        if( !empty( $value ) ) {
            $product->update_meta_data( $key, $value );
        } else {
            $product->delete_meta_data( $key );
        }
    }
}