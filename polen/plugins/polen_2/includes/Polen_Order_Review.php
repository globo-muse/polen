<?php

namespace Polen\Includes;

class Polen_Order_Review
{
    const COMMENT_TYPE = 'order_review';
    
    private $comment_id;
    private $user_id;
    private $comment_agent;
    private $comment_content;
    private $comment_date;
    private $comment_karma;
    private $comment_type;
    private $comment_meta;
    private $comment_approved;
    private $comment_author;
    private $comment_author_email;
    private $comment_author_IP;
    /**
     * Rate que sera um metadata
     */
    private $rate;
    
    /**
     * Para nao ficar indo buscar no banco para várias validacoes
     * @var type 
     */
    private $_order;
    
    /**
     * É o order_id
     * @var int
     */
    private $comment_post_ID;
    
    /**
     * Vai ser salvo no metadata
     * @var int
     */
    private $talent_id;
    
    public function set_comment_id( $comment_id ): void
    {
        $this->comment_id = $comment_id;
    }

    public function set_user_id( $user_id ): void
    {
        $this->user_id = $user_id;
    }

    public function set_comment_agent( $comment_agent ): void
    {
        $this->comment_agent = $comment_agent;
    }
    
    public function set_comment_content( $comment_content ): void
    {
        $this->comment_content = $comment_content;
    }

    public function set_comment_date( $comment_date ): void
    {
        $this->comment_date = $comment_date;
    }

    public function set_comment_karma( $comment_karma ): void
    {
        $this->comment_karma = $comment_karma;
    }

    public function set_comment_post_ID( $comment_post_ID ): void
    {
        $this->comment_post_ID = $comment_post_ID;
    }
    
    public function set_order_id( $comment_post_ID ): void
    {
        $this->set_comment_post_ID( $comment_post_ID );
    }

    public function set_comment_type( $comment_type ): void
    {
        $this->comment_type = $comment_type;
    }

    public function set_comment_meta( $comment_meta ): void
    {
        $this->comment_meta = $comment_meta;
    }

    public function set_comment_approved( $comment_approved ): void
    {
        $this->comment_approved = $comment_approved;
    }

    public function set_talent_id( $talent_id ): void
    {
        $this->talent_id = $talent_id;
    }
    
    public function set_rate( $rate )
    {
        $this->rate = $rate;
    }

    public function __construct( bool $static = false )
    {
        if( $static ) {
            add_action( 'wp_set_comment_status', array( $this, 'update_data_order_review_of_product' ), 10, 2);
        }
    }


    /**
     * Handler do Add_Action para edição de comentário do tipo order_review
     */
    public function update_data_order_review_of_product( $comment_id, $comment_status )
    {
        $comment = get_comment( $comment_id );
        if( $comment->comment_type != self::COMMENT_TYPE ) {
            return;
        }  
        $this->update_order_review_data_of_product( $comment_id, $comment );
    }
    

    /**
     * Atualiza os dados no Product Qtd total de Reviews e Somatorio das Notas
     * Quando vai para a Lixeira
     * 
     * @param int $comment_id
     * @param WP_Comment $comment
     */
    public function update_order_review_data_of_product( $comment_id, $comment )
    {
        $talent_id = get_comment_meta( $comment_id, 'talent_id', true );
        $number_total_reviews = self::get_number_total_reviews_by_talent_id( $talent_id );
        $sum_rate_talent = self::get_sum_rate_by_talent( $talent_id );
        $order = wc_get_order( $comment->comment_post_ID );
        $cart_item = Cart\Polen_Cart_Item_Factory::polen_cart_item_from_order( $order );
        $product = $cart_item->get_product_parent_if_has();
        $product->update_meta_data( 'total_review', $number_total_reviews );
        $product->update_meta_data( 'sum_rate', $sum_rate_talent );
        $product->save();
    }
    
    
    /**
     * Prepara o talent_id para entrar no Meta que é um array
     * @return type
     */
    private function prepare_metadata_insert_db()
    {
        return array(
            'talent_id' => $this->talent_id,
            'rate'      => $this->rate,
        );
    }
    
    
    /**
     * Validacao antes do insert
     */
    protected function validate_comment()
    {
        $this->validate_unique_comment();
        $this->validate_order_is_complete();
        $this->validate_same_user_by_order();
    }
    
    
    /**
     * Validação para ver se já existe o comentário baseado no user_id e 
     * comment_post_ID que é o order_id
     * 
     * @throws \Exception
     */
    protected function validate_unique_comment()
    {
        if( self::review_alredy_exist( $this->user_id, $this->_order ) ) {
            throw new \Exception( 'Esse comentário já existe', 403 );
        }
    }
    
    
    /**
     * Validacao se o usuário e o mesmo da order
     * @param type $param
     */
    protected function validate_same_user_by_order()
    {
        $order = $this->_order;
        if( !self::user_order_same_user_id( $this->user_id, $order ) ) {
            throw new \Exception( 'O usuário do pedido não é o mesmo', 403 );
        }
    }
    
    
    /**
     * Valida se a order está complta para poder criar um review
     * @throws \Exception
     */
    protected function validate_order_is_complete()
    {
        if( !self::verify_order_is_complete( $this->_order ) ) {
            throw new \Exception( 'O pedido não está completo, não pode criar review antes do talento enviar o video', 403 );
        }
    }
    
    
    /**
     * Pegar um comentário pelo user_id e order_id
     * necessário para saber se o comentátio é unico
     * @param int $user_id
     * @param int $order_id
     * @return array [WP_Comment]
     */
    static public function get_comment_by_user_id_order_id( $user_id, $order_id )
    {
        return get_comments( array(
            'user_id' => $user_id,
            'post_id' => $order_id,
            'type'    => self::COMMENT_TYPE,
        ) );
    }
    
    
    static public function get_number_total_reviews_by_talent_id( int $talent_id )
    {
        return get_comments( array(
            'meta_key' => 'talent_id',
            'meta_value' => $talent_id,
            'status' => 'approve',
            'type' => self::COMMENT_TYPE,
            'count' => true
        ));
    }
    
    /**
     * Devolve o somatorio das notas por talent_id
     * @param int $talent_id
     * @return int
     */
    static public function get_sum_rate_by_talent( int $talent_id )
    {
        $comments = get_comments( array(
            'meta_key' => 'talent_id',
            'meta_value' => $talent_id,
            'status' => 'approve',
            'type' => self::COMMENT_TYPE,
        ));
        $total_rate = 0;
        foreach( $comments as $comment ) {
            $value = get_comment_meta( $comment->comment_ID, 'rate', true);
            $total_rate += intval( $value );
        }
        return $total_rate;
    }
    
    
    /**
     * 
     * @return boolean|$this
     */
    public function save()
    {
        $commentdata = $this->prepare_data_db();
        
        //data for validation
        $this->_order = wc_get_order( $this->comment_post_ID );
        $this->validate_comment();
        
        $comment_id = wp_insert_comment( $commentdata );
        if( $comment_id === false ) {
            global $wpdb;
            $msg = empty( $wpdb->last_error ) ? 'error into insert comment' : $wpdb->last_error;
            throw new \Exception( $msg , 500 );
        }
        
        $comment = get_comment( $comment_id );
        $this->comment_id = $comment_id;
        return $this;
    }
    
    
    /**
     * Prepara a estrutura em array para o insert
     * @return array
     */
    public function prepare_data_db()
    {
        $user = wp_get_current_user();
        $ip = filter_input( INPUT_SERVER, 'REMOTE_ADDR' );
        return array(
            'comment_agent'         => $this->talent_id,
            'comment_approved'      => $this->comment_approved,
            'comment_author'        => $user->display_name,
            'comment_author_email'  => $user->user_email,
            'comment_author_IP'     => $ip,
            'comment_author_url'    => '',
            'comment_content'       => $this->comment_content,
            'comment_karma'         => $this->comment_karma,
            'comment_parent'        => '',
            'comment_post_ID'       => $this->comment_post_ID,
            'comment_type'          => self::COMMENT_TYPE,
            'comment_meta'          => $this->prepare_metadata_insert_db(),
            'user_id'               => $this->user_id
        );
    }


    /**
     * Pega os reviews pelo talent_id
     * @param int $talent_id
     */
    static public function get_order_reviews_by_talent_id( int $talent_id )
    {
        $query = array(
            'meta_key' => 'talent_id',
            'meta_value' => $talent_id,
            'type' => 'order_review',
            'status' => 'approve',
        );
        $reviews = get_comments( $query );
        return $reviews;
    }


    /**
     * Verifica se o usuário já fez um review para o pedido
     * @param int $user_id
     * @param WC_Order
     * @return bool
     */
    static public function review_alredy_exist( $user_id, \WC_Order $order )
    {
        $order_id = $order->get_id();
        return !empty( self::get_comment_by_user_id_order_id( $user_id, $order_id ) );
    }


    /**
     * Varifica se o fã é o mesmo que fez a order
     * @param int $user_id
     * @param WC_Order
     * @return bool
     */
    static public function user_order_same_user_id( $user_id, $order )
    {
        $order_id = $order->get_id();
        if( empty( $user_id ) || empty( $order_id ) ) {
            return false;
        }

        if( $order->get_user_id() == $user_id ) {
            return true;
        }
        return false;
    }


    /**
     * Valida se a order está completa para poder criar um review
     * @param WC_Order
     * @return bool
     */
    static public function verify_order_is_complete( \WC_Order $order )
    {
        if( $order->get_status() === Polen_Order::SLUG_ORDER_COMPLETE ) {
            return true;
        }
        return false;
    }


    /**
     * Verifica se o usuário pode fazer uma review
     * @param int $user_id
     * @param int $order_id
     * @return bool
     */
    static public function can_make_review( $user_id, $order_id )
    {
        $order = wc_get_order( $order_id );
        if( !self::review_alredy_exist( $user_id, $order ) &&
            self::user_order_same_user_id( $user_id, $order ) &&
            self::verify_order_is_complete( $order ) ) {
            return true;
        }
        return false;
    }
}
