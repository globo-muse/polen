<?php
namespace Polen\Admin;

class Polen_Admin_Social_Base_Product_Fields
{
    const ACTION_NAME      = 'polen_custom_fields_social_base';
    const TAB_NAME         = 'polen_social_base_tab';
    const TAB_CONTENT_NAME = 'polen_social_base_tab_data';
    
    const FIELD_NAME_IS            = '_is_social_base';
    const FIELD_NAME_SLUG_CAMPAIGN = '_social_base_slug_campaign';
    const FIELD_NAME_URL_VIDEO     = '_social_base_video_testimonial';
    const FIELD_NAME_URL_WATERMARK = '_social_base_watermark';

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
            'label'    => 'Base Social',
            'target'   => self::TAB_CONTENT_NAME,
            'class'    => array(),
            'priority' => 90,
        );
        return $array;
    }



    public function tab_content() {
        global $product_object;
        ?>
            <div id="<?= self::TAB_CONTENT_NAME; ?>" class="panel woocommerce_options_panel hidden">
                <div class="options_group">
                    <?php
                    woocommerce_wp_checkbox(
                        array(
                            'id'      => self::FIELD_NAME_IS,
                            'value'   => $product_object->get_meta( self::FIELD_NAME_IS ) == 'yes' ? 'yes' : 'no',
                            'label'   => 'Produto é Social',
                            'cbvalue' => 'yes',
                        )
                    );
                    ?>
                </div>

                <div class="options_group">
                    <?php
                    woocommerce_wp_text_input(
                        array(
                            'id'          => self::FIELD_NAME_SLUG_CAMPAIGN,
                            'value'       => $product_object->get_meta( self::FIELD_NAME_SLUG_CAMPAIGN ),
                            'label'       => 'Slug da Campanha',
                            'desc_tip'    => true,
                            'description' => 'Slug da companha que este produto é parte',
                            'type'        => 'text',
                        )
                    );
                    ?>
                </div>

                <div class="options_group">
                    <?php
                    woocommerce_wp_text_input(
                        array(
                            'id'          => self::FIELD_NAME_URL_VIDEO,
                            'value'       => $product_object->get_meta( self::FIELD_NAME_URL_VIDEO ),
                            'label'       => 'Link do video',
                            'desc_tip'    => true,
                            'description' => 'URL do Video com o video de talento',
                            'type'        => 'text',
                        )
                    );
                    ?>
                </div>

                <div class="options_group">
                    <?php
                    woocommerce_wp_text_input(
                        array(
                            'id'          => self::FIELD_NAME_URL_WATERMARK,
                            'value'       => $product_object->get_meta( self::FIELD_NAME_URL_WATERMARK ),
                            'label'       => 'URL da marca d`áqua',
                            'desc_tip'    => true,
                            'description' => 'URL da marca d`áqua',
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
                $product                       = wc_get_product( $product_id );
                $is_social_base                = strip_tags(filter_input(INPUT_POST, self::FIELD_NAME_IS));
                $social_base_slug_campaign     = strip_tags(filter_input(INPUT_POST, self::FIELD_NAME_SLUG_CAMPAIGN));
                $social_base_video_testimonial = strip_tags(filter_input(INPUT_POST, self::FIELD_NAME_URL_VIDEO));
                $social_base_watermark         = strip_tags(filter_input(INPUT_POST, self::FIELD_NAME_URL_WATERMARK));


                $this->save_meta($product, $is_social_base, self::FIELD_NAME_IS );
                $this->save_meta($product, $social_base_slug_campaign, self::FIELD_NAME_SLUG_CAMPAIGN );
                $this->save_meta($product, $social_base_video_testimonial, self::FIELD_NAME_URL_VIDEO );
                $this->save_meta($product, $social_base_watermark, self::FIELD_NAME_URL_WATERMARK );

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