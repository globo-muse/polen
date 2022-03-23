<?php

if( ! defined( 'ABSPATH' ) ) {
    die( 'Silence is golden.');
}

class WC_Cubo9_BraspagReduxSettings {

    public function __construct( $static = false ) {       
        if($static) {
            $this->init();
            add_action( 'redux/options/WC_Cubo9_BraspagReduxSettings/saved', array($this, 'save'), 10, 2);
            add_action( 'redux/options/WC_Cubo9_BraspagReduxSettings/settings/change', array($this, 'save'), 10, 2);
        }
    }

    public function save( $args ) {
        // Ações após salvar as configurações no Redux
        if( isset ( $args['card_brand'] ) && is_array( $args['card_brand'] ) && count( $args['card_brand'] ) > 0 ) {
            global $wpdb;
            $cards = $args['card_brand'];
            
            $cards_slugs = array();
            foreach ( $cards['slug'] as $k => $slug ) {
                if ( ! is_null( $slug ) && ! empty( trim( $slug ) ) ) {
                    $cards_slugs[] = $slug;
                }
            }
            $slugs = implode( "', '", $cards_slugs );
            $sql_delete = "DELETE FROM `" . $wpdb->base_prefix . "c9_braspag_cards` WHERE `slug` NOT IN ( '" . $slugs . "' )";
            $wpdb->query( $sql_delete );

            foreach ( $cards['brand'] as $k => $brand ) {
                if ( ! is_null( $brand ) && ! empty( trim( $brand ) ) ) {
                    $args_sql = array(
                        'active' => $cards['active'][$k],
                        'brand' => $brand,
                        'slug'  => $cards['slug'][$k],
                        'icon'  => ( empty( $cards['icon'][$k] ) ) ? null : $cards['icon'][$k],
                        'debit_tax'  => ( empty( $cards['debit_tax'][$k] ) ) ? null : (float) $cards['debit_tax'][$k],
                        'installment_1'  => ( empty( $cards['installments'][1][$k] ) ) ? null : (float) $cards['installments'][1][$k],
                        'installment_2'  => ( empty( $cards['installments'][2][$k] ) ) ? null : (float) $cards['installments'][2][$k],
                        'installment_3'  => ( empty( $cards['installments'][3][$k] ) ) ? null : (float) $cards['installments'][3][$k],
                        'installment_4'  => ( empty( $cards['installments'][4][$k] ) ) ? null : (float) $cards['installments'][4][$k],
                        'installment_5'  => ( empty( $cards['installments'][5][$k] ) ) ? null : (float) $cards['installments'][5][$k],
                        'installment_6'  => ( empty( $cards['installments'][6][$k] ) ) ? null : (float) $cards['installments'][6][$k],
                        'installment_7'  => ( empty( $cards['installments'][7][$k] ) ) ? null : (float) $cards['installments'][7][$k],
                        'installment_8'  => ( empty( $cards['installments'][8][$k] ) ) ? null : (float) $cards['installments'][8][$k],
                        'installment_9'  => ( empty( $cards['installments'][9][$k] ) ) ? null : (float) $cards['installments'][9][$k],
                        'installment_10'  => ( empty( $cards['installments'][10][$k] ) ) ? null : (float) $cards['installments'][10][$k],
                        'installment_11'  => ( empty( $cards['installments'][11][$k] ) ) ? null : (float) $cards['installments'][11][$k],
                        'installment_12'  => ( empty( $cards['installments'][12][$k] ) ) ? null : (float) $cards['installments'][12][$k],
                    );

                    $sql = "SELECT `ID` FROM `" . $wpdb->base_prefix . "c9_braspag_cards` WHERE `slug`='" . sanitize_title( trim( $cards['slug'][$k] ) ) . "'";
                    $res = $wpdb->get_results( $sql );

                    if( count( $res ) > 0 ) {
                        $where = array(
                            'ID' => $res[0]->ID,
                        );
                        $update = $wpdb->update(
                            $wpdb->base_prefix . 'c9_braspag_cards',
                            $args_sql,
                            $where
                        );
                    } else {
                        $insert = $wpdb->insert(
                            $wpdb->base_prefix . 'c9_braspag_cards',
                            $args_sql
                        );
                    }
                }
            }
        }
    }

    public static function init() {
        if ( ! class_exists( 'Redux' ) ) {
            return;
        }

        $opt_name = "WC_Cubo9_BraspagReduxSettings";

        $args = array(
            'opt_name'             => $opt_name,
            'display_name'         => esc_html__( 'Configurações Braspag', 'cubo9' ),
            'display_version'      => '1.0.0',
            'menu_type'            => 'menu',
            'allow_sub_menu'       => true,
            'menu_title'           => esc_html__( 'Conf. Braspag', 'cubo9' ),
            'page_title'           => esc_html__( 'Configurações Braspag', 'cubo9' ),
            'google_api_key'       => '',
            'google_update_weekly' => true,
            'async_typography'     => false,
            'disable_google_fonts_link' => true,
            'admin_bar'            => false,
            'admin_bar_icon'       => 'dashicons-admin-settings',
            'admin_bar_priority'   => 51,
            'global_variable'      => 'WC_Cubo9_BraspagReduxSettings',
            'dev_mode'             => false,
            'update_notice'        => true,
            'customizer'           => false,
            'page_priority'        => null,
            'page_parent'          => 'options.php',
            'page_permissions'     => 'manage_options',
            'menu_icon'            => 'dashicons-admin-settings',
            'last_tab'             => '',
            'page_icon'            => 'dashicons-admin-settings',
            'page_slug'            => 'cubo9-braspag-settings',
            'save_defaults'        => true,
            'default_show'         => false,
            'default_mark'         => '',
            'show_import_export'   => true,
            'transient_time'       => '3600',
            'output'               => true,
            'output_tag'           => true,
            'footer_credit'     => '',
            'use_cdn'              => true,
            'hints'                => array(
                'icon'          => 'el el-question-sign',
                'icon_position' => 'right',
                'icon_color'    => 'lightgray',
                'icon_size'     => 'normal',
                'tip_style'     => array(
                    'color'   => 'red',
                    'shadow'  => true,
                    'rounded' => false,
                    'style'   => '',
                ),
                'tip_position'  => array(
                    'my' => 'top left',
                    'at' => 'bottom right',
                ),
                'tip_effect'    => array(
                    'show' => array(
                        'effect'   => 'slide',
                        'duration' => '500',
                        'event'    => 'mouseover',
                    ),
                    'hide' => array(
                        'effect'   => 'slide',
                        'duration' => '500',
                        'event'    => 'click mouseleave',
                    ),
                ),
            )
        );

        Redux::set_args( $opt_name, $args );
        if ( get_current_blog_id() == 1 ) {
            // Section Geral
            Redux::set_section( $opt_name, array(
                'title'   => esc_html__( 'Geral', 'cubo9' ),
                'id'      => 'general',
                'icon'    => 'el el-dashboard',
                'fields'  => array(
                    array(
                        'id'           => 'braspag_checkout_title',
                        'type'         => 'text',
                        'title'        => __( 'Título da caixa de pagamento', 'cubo9' ),
                        'subtitle'     => __( 'Informe o título que deverá ser exibido na caixa de pagamento do checkout.', 'cubo9' ),
                        'desc'         => esc_html__( 'Informe o título que deverá ser exibido na caixa de pagamento do checkout (Ex: Pagamento com cartão).', 'cubo9' ),
                        'default'      => 'Pagamento com cartão',
                    ),
                    array(
                        'id'           => 'braspag_checkout_description',
                        'type'         => 'text',
                        'title'        => __( 'Descrição do meio de pagamento', 'cubo9' ),
                        'subtitle'     => __( 'Informe a descrição do meio de pagamento.', 'cubo9' ),
                        'desc'         => esc_html__( 'Informe a descrição do meio de pagamento (Ex: Pagamento com cartões de crédito e débito).', 'cubo9' ),
                        'default'      => 'Pagamento com cartões de crédito e débito',
                    ),
                    array(
                        'id'           => 'braspag_checkout_instructions',
                        'type'         => 'text',
                        'title'        => __( 'Instruções após efetuar o pagamento', 'cubo9' ),
                        'subtitle'     => __( 'Informe as instruções após efetuar o pagamento.', 'cubo9' ),
                        'desc'         => esc_html__( 'Informe as instruções após efetuar o pagamento (Ex: Pagamento efetuado com sucesso!).', 'cubo9' ),
                        'default'      => 'Pagamento efetuado com sucesso!',
                    ),
                    array(
                        'id'           => 'braspag_softdescriptor',
                        'type'         => 'text',
                        'title'        => __( 'Nome na Fatura', 'cubo9' ),
                        'subtitle'     => __( 'Informe o nome que deverá aparecer na fatura.', 'cubo9' ),
                        'desc'         => esc_html__( 'Informe o nome que deverá aparecer na fatura do cartão do comprador (Ex: Loja C9).', 'cubo9' ),
                        'default'      => 'C9 Shop',
                    ),
                    array(
                        'id'           => 'enable_braspag_sandbox',
                        'type'         => 'switch',
                        'title'        => __( 'Utilizar Sandbox Braspag', 'cubo9' ),
                        'subtitle'     => __( 'Habilitar a sandbox da Braspag para ambiente de DESENVOLVIMENTO e TESTES.', 'cubo9' ),
                        'desc'         => esc_html__( 'Habilite esta chave para testar transações apenas em seu ambiente de DESENVOLVIMENTO e/ou TESTES.', 'cubo9' ),
                        'default'      => 0,
                    ),

                    // Ambiente de produção
                    array(
                        'id'           => 'master_org_id',
                        'type'         => 'text',
                        'title'        => __( 'Produção: ORG ID', 'cubo9' ),
                        'subtitle'     => __( 'Informe o "ORG ID".', 'cubo9' ),
                        'desc'         => esc_html__( 'Informe o "ORG ID" no ambiente de produção.', 'cubo9' ),
                        'default'      => '1snn5n9w',
                        'required'     => array( 
                                            array( 'enable_braspag_sandbox', 'equals', '0' ) 
                                        ),
                    ),
                    array(
                        'id'           => 'master_session_id_prefix',
                        'type'         => 'text',
                        'title'        => __( 'Produção: Prefixo do "Session ID"', 'cubo9' ),
                        'subtitle'     => __( 'Informe o prefixo do "Session ID".', 'cubo9' ),
                        'desc'         => esc_html__( 'Informe o prefixo do "Session ID" no ambiente de produção.', 'cubo9' ),
                        'default'      => 'braspag_split_cubonove',
                        'required'     => array( 
                                            array( 'enable_braspag_sandbox', 'equals', '0' ) 
                                        ),
                    ),
                    array(
                        'id'           => 'master_subordinate_merchant_id',
                        'type'         => 'text',
                        'title'        => __( 'Produção: Subordinate Merchant ID (Master)', 'cubo9' ),
                        'subtitle'     => __( 'Informe o subordinate merchant ID do usuário Master.', 'cubo9' ),
                        'desc'         => esc_html__( 'Informe o subordinate merchant ID do usuário Master no ambiente de produção.', 'cubo9' ),
                        'default'      => false,
                        'required'     => array( 
                                            array( 'enable_braspag_sandbox', 'equals', '0' ) 
                                        ),
                    ),
                    array(
                        'id'           => 'master_client_secret',
                        'type'         => 'text',
                        'title'        => __( 'Produção: Client Secret (Master)', 'cubo9' ),
                        'subtitle'     => __( 'Informe o client secret do usuário Master.', 'cubo9' ),
                        'desc'         => esc_html__( 'Informe o client secret do usuário Master no ambiente de produção.', 'cubo9' ),
                        'default'      => false,
                        'required'     => array( 
                                            array( 'enable_braspag_sandbox', 'equals', '0' ) 
                                        ),
                    ),
                    array(
                        'id'           => 'master_merchant_key',
                        'type'         => 'text',
                        'title'        => __( 'Produção: Merchant Key (Master)', 'cubo9' ),
                        'subtitle'     => __( 'Informe o Merchant Key do usuário Master.', 'cubo9' ),
                        'desc'         => esc_html__( 'Informe o Merchant Key do usuário Master no ambiente de produção.', 'cubo9' ),
                        'default'      => false,
                        'required'     => array( 
                                            array( 'enable_braspag_sandbox', 'equals', '0' ) 
                                        ),
                    ),

                    // Ambiente de Sandbox
                    array(
                        'id'           => 'sandbox_master_org_id',
                        'type'         => 'text',
                        'title'        => __( 'Sandbox: ORG ID', 'cubo9' ),
                        'subtitle'     => __( 'Informe o "ORG ID".', 'cubo9' ),
                        'desc'         => esc_html__( 'Informe o "ORG ID" no ambiente de sandbox.', 'cubo9' ),
                        'default'      => '1snn5n9w',
                        'required'     => array( 
                                            array( 'enable_braspag_sandbox', 'equals', '1' ) 
                                        ),
                    ),
                    array(
                        'id'           => 'sandbox_master_session_id_prefix',
                        'type'         => 'text',
                        'title'        => __( 'Sandbox: Prefixo do "Session ID"', 'cubo9' ),
                        'subtitle'     => __( 'Informe o prefixo do "Session ID".', 'cubo9' ),
                        'desc'         => esc_html__( 'Informe o prefixo do "Session ID" no ambiente de sandbox.', 'cubo9' ),
                        'default'      => 'braspag_split_sandbox_cubonove',
                        'required'     => array( 
                                            array( 'enable_braspag_sandbox', 'equals', '1' ) 
                                        ),
                    ),
                    array(
                        'id'           => 'sandbox_master_subordinate_merchant_id',
                        'type'         => 'text',
                        'title'        => __( 'Sandbox: Subordinate Merchant ID (Master)', 'cubo9' ),
                        'subtitle'     => __( 'Informe o subordinate merchant ID do usuário Master.', 'cubo9' ),
                        'desc'         => esc_html__( 'Informe o subordinate merchant ID do usuário Master no ambiente de sandbox.', 'cubo9' ),
                        'default'      => false,
                        'required'     => array( 
                                            array( 'enable_braspag_sandbox', 'equals', '1' ) 
                                        ),
                    ),
                    array(
                        'id'           => 'sandbox_master_client_secret',
                        'type'         => 'text',
                        'title'        => __( 'Sandbox: Client Secret (Master)', 'cubo9' ),
                        'subtitle'     => __( 'Informe o client secret do usuário Master.', 'cubo9' ),
                        'desc'         => esc_html__( 'Informe o client secret do usuário Master no ambiente de sandbox.', 'cubo9' ),
                        'default'      => false,
                        'required'     => array( 
                                            array( 'enable_braspag_sandbox', 'equals', '1' ) 
                                        ),
                    ),
                    array(
                        'id'           => 'sandbox_master_merchant_key',
                        'type'         => 'text',
                        'title'        => __( 'Sandbox: Merchant Key (Master)', 'cubo9' ),
                        'subtitle'     => __( 'Informe o Merchant Key do usuário Master.', 'cubo9' ),
                        'desc'         => esc_html__( 'Informe o Merchant Key do usuário Master no ambiente de sandbox.', 'cubo9' ),
                        'default'      => false,
                        'required'     => array( 
                                            array( 'enable_braspag_sandbox', 'equals', '1' ) 
                                        ),
                    ),
                )
            ) );

            // Section: Taxas
            Redux::set_section( $opt_name, array(
                'title'            => esc_html__( 'Taxas', 'cubo9' ),
                'id'               => 'taxes',
                'icon'             => 'el el-usd',
                'subsection'       => false,
                'fields'           => array(
                    array(
                        'id'       => 'fee_gateway',
                        'type'     => 'text',
                        'title'    => esc_html__('Fee Gateway', 'cubo9'),
                        'subtitle' => esc_html__('Informe o valor do fee do gateway em cenvatos.', 'cubo9'),
                        'desc'     => esc_html__('Informe o valor do fee do gateway em centavos (Ex: para R$ 0,25, informe 25)', 'cubo9'),
                        'default'  => '25',
                    ),
                    array(
                        'id'       => 'fee_antifraude',
                        'type'     => 'text',
                        'title'    => esc_html__( 'Fee Antifraude', 'cubo9' ),
                        'subtitle' => esc_html__( 'Informe o valor do fee do antifraude em cenvatos.', 'cubo9' ),
                        'desc'     => esc_html__( 'Informe o valor do fee do antifraude em centavos (Ex: para R$ 0,52, informe 52)', 'cubo9' ),
                        'default'  => '52',
                    ),
                    array(
                        'id'       => 'pass_rates',
                        'type'     => 'switch',
                        'title'    => esc_html__( 'Taxas de Fee pagas pela loja', 'cubo9' ),
                        'subtitle' => esc_html__( 'Habilite esta opção caso as taxas de Fee do Gateway e Antifraude sejam responsabilidade da loja.', 'cubo9' ),
                        'desc'     => esc_html__( 'Habilite esta opção caso as taxas de Fee do Gateway e Antifraude por transação sejam descontadas da loja.', 'cubo9' ),
                        'default'  => 1,
                    ),
                    array(
                        'id'       => 'default_mdr',
                        'type'     => 'text',
                        'title'    => esc_html__( 'MDR Padrão', 'cubo9' ),
                        'subtitle' => esc_html__( 'Informe a comissão (MDR) padrão sobre venda negociada com a loja.', 'cubo9' ),
                        'desc'     => esc_html__( 'Informe a comissão (MDR) padrão sobre venda negociada com a loja utilizando casa decimal separada por ponto. (Ex: 2.50)', 'cubo9' ),
                        'default'  => '2.00',
                    ),
                    array(
                        'id'       => 'default_fee',
                        'type'     => 'text',
                        'title'    => esc_html__( 'Fee Padrão', 'cubo9' ),
                        'subtitle' => esc_html__( 'Informe o valor do fee padrão em cenvatos.', 'cubo9' ),
                        'desc'     => esc_html__( 'Informe o valor do fee padrão (taxa por venda) negociado com a loja em centaos. (Ex: R$ 40,00, informe 40)', 'cubo9' ),
                        'default'  => '0',
                    ),
                )
            ) );

            // Section: Parcelamento
            $terms = get_categories( 'product_cat', array(
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
            ) );

            // var_dump( $terms );die;

            $product_categories = array();
            if( count( $terms ) > 0 ) {
                foreach( $terms as $k => $term ) {
                    $product_categories[ $term->slug ] = $term->name;
                }
            }
            
            Redux::set_section( $opt_name, array(
                'title'            => esc_html__( 'Parcelamento', 'cubo9' ),
                'id'               => 'installments',
                'icon'             => 'el el-credit-card',
                'subsection'       => false,
                'fields'           => array(
                    array(
                        'id'       => 'enable_installments',
                        'type'     => 'switch',
                        'title'    => esc_html__( 'Habilitar parcelamento de compras.', 'cubo9' ),
                        'subtitle' => esc_html__( 'Ative ou desative o parcelamento de compras na plataforma.', 'cubo9' ),
                        'desc'     => esc_html__( 'Ative ou desative o parcelamento de compras para os usuários na plataforma.', 'cubo9' ),
                        'default'  => '0',
                    ),
                    array(
                        'id'       => 'max_installments',
                        'type'     => 'spinner',
                        'title'    => esc_html__('Quantidade máxima de parcelas', 'cubo9'),
                        'subtitle' => esc_html__('Informe a quantidade máxima de parcelas aceita pela loja.', 'cubo9'),
                        'desc'     => esc_html__('Informe a quantidade máxima de parcelas que a loja aceita (Ex: 10).', 'cubo9'),
                        'min'      => 1,
                        'max'      => 12,
                        'default'  => 12,
                    ),
                    array(
                        'id'       => 'min_installment_value',
                        'type'     => 'text',
                        'title'    => esc_html__( 'Valor mínimo da parcela', 'cubo9' ),
                        'subtitle' => esc_html__( 'Informe o valor mínimo da parcela para compras parceladas.', 'cubo9' ),
                        'desc'     => esc_html__( 'Informe o valor mínimo da parcela para compras parceladas, utilizando "." para separar as casas decimais (Ex: para R$ 50,00, informe 50.00)', 'cubo9' ),
                        'default'  => '0',
                    ),
                    array(
                        'id'       => 'categories_withtout_installment',
                        'type'     => 'select',
                        'multi'    => true,
                        'placeholder' => 'Categoria...',
                        'title'    => esc_html__( 'Categorias sem parcelamento', 'cubo9' ),
                        'subtitle' => esc_html__( 'Informe as categorias que não permitem compras parceladas.', 'cubo9' ),
                        'desc'     => esc_html__( 'Informe as categorias que não permitem compras parceladas. Categorias filhas deverão herdar dos pais.', 'cubo9' ),
                        'default'  => '',
                        'options'  => $product_categories,
                    ),
                )
            ) );

            $installments_rates_to_clients = array(
                array(
                    'id'       => 'pass_installment_rates_to_client',
                    'type'     => 'switch',
                    'title'    => esc_html__( 'Repassar juros aos clientes', 'cubo9' ),
                    'subtitle' => esc_html__( 'Habilite esta opção caso as taxas de parcelamento sejam repassadas aos clientes.', 'cubo9' ),
                    'desc'     => esc_html__( 'Habilite esta opção caso as taxas de parcelamento sejam repassadas aos clientes.', 'cubo9' ),
                    'default'  => 0,
                ),
            );

            $WC_Cubo9_Braspag_Helper = new WC_Cubo9_Braspag_Helper();
            for( $i=1; $i<12; $i++ ) {
                $installment = ($i+1);
                $label = $installment . 'x '; // ' ( $installment > 1 ) ? ' parcelas' : 'parcela';
                $range = $WC_Cubo9_Braspag_Helper->get_installment_rates_range( $installment );
                $installments_rates_to_clients[] = array(
                    'id'       => 'pass_installment_rates_to_client_' . $installment,
                    'type'     => 'switch',
                    'title'    => esc_html__( $label, 'cubo9' ),
                    'subtitle' => esc_html__( 'Repassar os juros dessa parcela para o cliente (' . $range->min . '% à ' . $range->max . '%)', 'cubo9' ),
                    'desc'     => esc_html__( 'Habilite esta opção caso as taxas dessa modalidade de parcelamento devam ser repassadas aos clientes.', 'cubo9' ),
                    'default'  => 0,
                    'required' => array( 
                        array( 'pass_installment_rates_to_client', 'equals', '1' ) 
                    ),
                );
            }

            Redux::set_section( $opt_name, array(
                'title'            => esc_html__( 'Taxas de Parcelamento', 'cubo9' ),
                'id'               => 'installments_rates',
                'icon'             => 'el el-credit-card',
                'subsection'       => true,
                'fields'           => $installments_rates_to_clients
            ) );

            Redux::set_section( $opt_name, array(
                'title'            => esc_html__( 'Cartões', 'cubo9' ),
                'id'               => 'card_brands',
                'icon'             => 'el el-credit-card',
                'subsection'       => false,
                'fields'           => array(
                    array(
                        'id'       => 'pass_card_rates',
                        'type'     => 'switch',
                        'title'    => esc_html__( 'Taxas do cartão pagas pela loja', 'cubo9' ),
                        'subtitle' => esc_html__( 'Habilite esta opção caso as taxas do Cartão sejam responsabilidade da loja.', 'cubo9' ),
                        'desc'     => esc_html__( 'Habilite esta opção caso as taxas do Cartão por transação sejam descontadas da loja.', 'cubo9' ),
                        'default'  => 1,
                    ),
                    array(
                        'id'       => 'card_brand',
                        'type'     => 'credit_card_installments',
                        'title'    => esc_html__('Cartão', 'cubo9'),
                        'subtitle' => esc_html__('Informe o nome da bandeira, slug, ícone e taxas.', 'cubo9'),
                        'add_text' => 'Adicionar novo',
                        'show_empty' => false,
                    ),
                )
            ) );
        }
    }
}

function WC_Cubo9_Braspag_Redux_Init() {
    $WC_Cubo9_BraspagReduxSettings = new WC_Cubo9_BraspagReduxSettings(true);
}
add_action( 'plugins_loaded', 'WC_Cubo9_Braspag_Redux_Init' );