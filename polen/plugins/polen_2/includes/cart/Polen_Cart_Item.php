<?php

namespace Polen\Includes\Cart;

class Polen_Cart_Item
{
    
    /**
     * Contate com valor para o video que é pedido para outra pessoa
     */
    const VIDEO_FOR_OTHER_ONE = 'other_one';
    
    /**
     * Contato com valor que repesenta o video para a propria pessoa
     */
    const VIDEO_FOR_TO_MY_SELF= 'to_myself';
    
    /**
     * Item que está o carrinho padrao do WC
     * @var \WC_Order_Item_Product
     */
    public $item;
    
    public function __construct( \WC_Order_Item_Product $item )
    {
        $this->item = $item;
    }
    
    
    /**
     * Retorna o nome da pessoa que está oferencendo o video
     * @return string
     */
    public function get_offered_by()
    {
        return $this->item->get_meta( 'offered_by' );
    }
    
    
    /**
     * Retorna o nome da pessoa para quem o video foi feito
     * @return string
     */
    public function get_name_to_video()
    {
        return $this->item->get_meta( 'name_to_video' );
    }
    
    
    /**
     * Retorna o email de acompanhamento do pedido
     * @return string
     */
    public function get_email_to_video()
    {
        return $this->item->get_meta( 'email_to_video' );
    }
    
    
    /**
     * Retorna um dos 2 valores other_one | to_myself
     * other_one - para outra pessoa
     * to_myself - para a pessoa mesmo que pediu o video
     * @return string
     */
    public function get_video_to()
    {
        return $this->item->get_meta( 'video_to' );
    }
    
    
    /**
     * Nome da ocorrencia do video
     * @return string
     */
    public function get_video_category()
    {
        return $this->item->get_meta( 'video_category' );
    }

    /**
     * Pega a quantidade de que o talento fica de venda (0 ~ 1)
     * @return float
     */
    public function get_talent_fee()
    {
        return $this->item->get_meta('talent_fee', true);
    }
    
    
    /**
     * Retorna as instruções escritas pelo comprador
     * @return string
     */
    public function get_instructions_to_video()
    {
        return $this->item->get_meta( 'instructions_to_video' );
    }

    /**
     * Quando a Order é B2B tem uma questão de Licença que o comprador pode
     * usar durante X dias 30|60|90
     * @return int
     */
    public function get_licence_in_days()
    {
        return $this->item->get_meta( 'licence_in_days' );
    }


    /**
     * Quando a Order é B2B pega o nome fantasia da empresa
     * @return int
     */
    public function get_company_name()
    {
        return $this->item->get_meta( 'company_name' );
    }


    /**
     * Pega o Item Order da Compra
     */
    public function get_item_order()
    {
        return $this->item;
    }
    
    
    /**
     * Retorna se a compra é referente a primeira compra
     * @return string
     */
    public function get_first_order()
    {
        return $this->item->get_meta( 'first_order' );
    }
    
    
    /**
     * Pega se o video é permitido ser apresentado na pagina de detalhe do talento
     * @return type
     */
    public function get_public_in_detail_page()
    {
        return $this->item->get_meta( 'allow_video_on_page' ) == 'on' ? '1' : '0';
    }
    
    
    /**
     * Pega o talent_id já tratando o tipo de produto \WC_Product_Variation ou
     * \WC_Product_Variable ou WC_Product_Simple
     * @return int
     */
    public function get_talent_id()
    {
        if( $this->item->get_product() instanceof \WC_Product_Variation ) {
            return $this->get_talent_id_in_product_variation();
        } elseif( $this->item->get_product() instanceof \WC_Product_Variable ) {
            return $this->get_talent_id_in_product_variable();
        } else {
            return $this->get_talent_id_in_product_simple();
        }
    }

    /**
     * Pega o talent_id quando um produto é Simple
     * @return int
     */
    public function get_talent_id_in_product_simple()
    {
        $product_id = $this->item->get_product()->get_id();
        $product_post = get_post( $product_id );
        return $product_post->post_author;
    }


    /**
     * Pega o talent_id quando um produto é um Produto Variavel
     * @return int
     */
    public function get_talent_id_in_product_variable()
    {
        $product_id = $this->item->get_product()->get_id();
        $product_post = get_post( $product_id );
        return $product_post->post_author;
    }


    /**
     * Pega o talent_id quando um produto é uma Variação
     * o talent_id é o do Produto pai
     * @return int
     */
    public function get_talent_id_in_product_variation()
    {
        $product_id = $this->item->get_product()->get_parent_id();
        $product_post = get_post( $product_id );
        return $product_post->post_author;
    }
    
    
    /**
     * Pegar um product ID
     */
    public function get_product_id()
    {
        $product_id = $this->item->get_product()->get_id();
        return $product_id;
    }

    /**
     * Retornar numero maximo de parcelas
     */
    public function get_installments()
    {
        return $this->item->get_meta('installments');
    }

    /**
     * Get o object product
     * @return WC_Product
     */
    public function get_product()
    {
        return $this->item->get_product();
    }

    /**
     * Adiciona um Meta data AO Item_Cart
     * Igual as Instrucoes do Video ou Para quem é
     * @param String
     * @param string|array
     * @param bool
     * @return int
     */
    public function add_meta_data( $key, $value, $unique = false )
    {
        $this->item->add_meta_data( $key, $value, $unique );
        return $this->item->save();
    }


    /**
     * Get o object product parent if tiver para produtos variaveis é para retornar o pai
     * @return WC_Product
     */
    public function get_product_parent_if_has()
    {
        if( $this->item->get_product() instanceof \WC_Product_Variation ) {
            return wc_get_product( $this->item->get_product()->get_parent_id() );
        } elseif( $this->item->get_product() instanceof \WC_Product_Variable ) {
            return $this->item->get_product();
        } else {
            return $this->item->get_product();
        }
    }
}
