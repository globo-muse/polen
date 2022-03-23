<?php
namespace Polen\Api;

use Polen\Includes\API\Polen_Api_Video_Info;
use WP_REST_Server;

class Api_Routers
{

    protected $base;

    public function __construct( bool $static = false )
    {
        $this->base = 'polen/v1';
        if( $static ) {
            add_action('rest_api_init', [ $this, 'init_routers' ]);
        }
    }

    function init_routers()
    {
        $controller = new Api_Controller( true );
        /**
         * ROTA: Listar Talentos
         *
         * @param s Filtrar por string (opcional)
         * @param paged Exibir a página atual (opcional)
         * @param per_page Número de post por página (opcional)
         * @param campaign Filtrar pela a  campanha - ID (opcional)
         * @param orderby ordernar resultados de posts (opcional)
         *      Values [
         *           Ordenar por popularidade = popularity
         *           Ordenar por media de classificação = rating
         *           Ordenar do mais antigo para o mais novo = date-asc
         *           Ordenar do mais novo para o mais antigo = date-desc
         *           Ordenar menor preço para maior: price-asc
         *           Ordenar maior preço para menor: price-desc
         *      ]
         * @param campaign_category Filtrar por categoria - slug (opcional)
         */
        register_rest_route($this->base, '/talents', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$controller, 'get_talents'],
                'permission_callback' => '__return_true',
                'args' => [
                    's' => [],
                    'per_page' => [],
                    'paged' => [],
                    'orderby' => [],
                    'campaign' => [],
                    'campaign_category' => [],
                ],
            ),
        ));

        /**
         * ROTA: Verificar cupom
         *
         * @param cupom Verificar cupom
         */
        register_rest_route($this->base, '/coupon', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'args' => array(
                    'coupon' => [],
                ),
                'callback' => [$controller, 'check_coupon'],
                'permission_callback' => '__return_true',
            )
        ));

        /**
         * ROTA: Descrição do talento
         *
         * @param slug slug do talento (required)
         * @param campaign slug da campanha do talento (required)
         */
        register_rest_route($this->base, '/talent', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'args' => array(
                    'slug' => [],
                    'campaign' => [],
                ),
                'callback' => [$controller, 'talent'],
                'permission_callback' => '__return_true',
            )
        ));

        /**
         * ROTA: Para pegar os videos já feitos pelo talento da pagina
         *
         * @param int id talento
         */
        register_rest_route($this->base, '/talent/(?P<slug>[^/]*)/videos', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'args' => array(
                    'slug' => [],
                ),
                'callback' => [$controller, 'get_product_videos'],
                'permission_callback' => '__return_true',
            )
        ));

        /**
         * ROTA: metodo de pagamento
         *
         * @param name Nome cliente (required)
         * @param cpf CPF do cliente (required)
         * @param phone Telefone do cliente (required)
         * @param email Email do cliente (required)
         * @param product_id ID do produto que será comprado (required)
         * @param coupon Cupom que será utilizado na compra (opcional)
         */
        register_rest_route($this->base, '/payment', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'args' => array(
                    'name' => [],
                    'cpf' => [],
                    'phone' => [],
                    'email' => [],
                    'product_id' => [],
                    'coupon' => [],
                ),
                'callback' => [$controller, 'payment'],
                'permission_callback' => '__return_true',
            )
        ));

        /**
         * ROTA: Verificar se existe stock
         *
         * @param product_id ID do produto que será comprado (required)
         */
        register_rest_route($this->base, '/cart', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'args' => array(
                    'product_id' => [],
                ),
                'callback' => [$controller, 'cart'],
                'permission_callback' => '__return_true',
            )
        ));

        /**
         * ROTA: Recuperar a senha do usuario pelo o email
         *
         * @param email email do usuario para recuperar senha (required)
         */
        register_rest_route($this->base, '/forgot_password', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'args' => array(
                    'email' => [],
                ),
                'callback' => [$controller, 'forgot_password'],
                'permission_callback' => '__return_true',
            )
        ));

        register_rest_route($this->base, '/get_payment_status/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'args' => array(
                    'id' => [],
                ),
                'callback' => [ $controller, 'get_payment_status' ],
                'permission_callback' => '__return_true',
            )
        ));

        $api_fan_order = new Api_Fan_Order();
        /**
         * ROTA: Responsavel pelo fan Logado
         * Order.
         */
        register_rest_route($this->base, '/fan/orders', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'args' => array(
                    'user_id' => [],
                ),
                'callback' => [ $api_fan_order, 'get_items' ],
                'permission_callback' => [ $api_fan_order, 'check_permission_get_items' ],
            )
        ));
        register_rest_route($this->base, '/fan/orders/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'args' => array(
                    'id' => [],
                ),
                'callback' => [ $api_fan_order, 'get_item' ],
                'permission_callback' => [ $api_fan_order, 'check_permission_get_item' ],
            )
        ));
        register_rest_route($this->base, '/fan/orders/(?P<hash>[\d]+)/reviews', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'args' => array(
                    'id' => [],
                ),
                'callback' => [ $api_fan_order, 'create_order_review' ],
                'permission_callback' => [ $api_fan_order, 'check_permission_get_item_review' ],
            )
        ));
        register_rest_route($this->base, '/fan/orders/(?P<hash>[\d]+)/reviews', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'args' => array(
                    'id' => [],
                ),
                'callback' => [ $api_fan_order, 'get_can_order_review' ],
                'permission_callback' => [ $api_fan_order, 'check_permission_get_item_review' ],
            )
        ));


        /** ********************************
         * Orders Endpoints
         */
        $api_order = new Api_Order();
        register_rest_route($this->base, '/orders/(?P<id>[\d]+)/flow', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'args' => array(
                    'id' => [],
                ),
                'callback' => [ $api_order, 'get_flow_by_order' ],
                'permission_callback' => '__return_true',
            )
        ));
        
        /**
         * Endpoint de cadastro de usuário
         */
        $api_user = new Api_User();
            register_rest_route($this->base, '/create_user', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'args' => array(
                    'email'            => [],
                    'password'         => [],
                    'terms_conditions' => [],
                    'user_name'        => [],
                    'campaing'         => [],
                ),
                'callback' => [ $api_user, 'sign_on' ],
                'permission_callback' => [ $api_user, 'check_permission_create_item' ],
            )
        ));

        /**
         * Atualizar usuario
         */
        register_rest_route($this->base, '/users', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'args' => array(
                    'email'         => [],
                    'user_name'     => [],
                    'last_name'     => [],
                    'display_name'  => [],
                    'phone'         => [],
                ),
                'callback' => [ $api_user, 'update_account' ],
                'permission_callback' => [ $api_user, 'check_permission_create_item' ],
            )
        ));

        register_rest_route($this->base, '/users', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'args' => array(
                    'email'            => [],
                ),
                'callback' => [ $api_user, 'my_account' ],
                'permission_callback' => [ $api_user, 'check_permission_create_item' ],
            )
        ));

        /**
         * Exibir comentários
         */
        register_rest_route($this->base, '/users/(?P<slug>[^/]*)/comments', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'args' => array(
                    'slug'            => [],
                ),
                'callback' => [ $api_user, 'commments' ],
                'permission_callback' => [ $api_user, 'check_permission_create_item' ],
            )
        ));

        register_rest_route($this->base, '/update_pass', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'args' => array(
                    'email'            => [],
                    'current_pass'     => [],
                    'new_pass'         => [],
                ),
                'callback' => [ $api_user, 'update_pass' ],
                'permission_callback' => [ $api_user, 'check_permission_create_item' ],
            )
        ));

        $api_video = new Api_Video();
        $polen_api_videos = new Polen_Api_Video_Info();
        register_rest_route( $this->base, '/videos/hash/(?P<id>[\d]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $api_video, 'get_item_by_hash' ),
                'permission_callback' => '__return_true',
            ),
            'schema' => array( $polen_api_videos, 'get_item_schema' )
        ) );

        register_rest_route( $this->base, '/videos/hash/(?P<id>[\d]+)/download', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $api_video, 'get_download_link_by_hash' ),
                'permission_callback' => '__return_true',
            ),
            'schema' => array( $polen_api_videos, 'get_item_schema' )
        ) );

        
        /**
         * Criacao do endpoint de Nonce para acesso ao checkout
         */
        register_rest_route( $this->base, '/nonce', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $controller, 'create_nonce' ),
                'permission_callback' => '__return_true',
            ),
            'schema' => array( $polen_api_videos, 'get_item_schema' )
        ) );
    }
}
