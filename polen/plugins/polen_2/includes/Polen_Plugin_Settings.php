<?php

namespace Polen\Includes;

use Polen\Includes\Sendgrid\Polen_Sendgrid_Redux;
use WP_Term_Query;

class Polen_Plugin_Settings
{

    public function __construct($static=false)
    {
        if( $static ) {
            $this->init();
            add_action( 'redux/loaded', array( $this, 'remove_demo' ) );
            add_action('redux/options/Polen_Plugin_Settings/saved', array($this, 'save'), 10, 2);
            add_action('redux/options/Polen_Plugin_Settings/settings/change', array($this, 'save'), 10, 2);
        }
    }

    public static function init() {
        if ( ! class_exists( '\Redux' ) ) {
            return;
        }

        $args = array(
            'taxonomy' => 'product_cat',
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => false,
        );

        $term_query = new WP_Term_Query($args);

        $categories = [];
        foreach ($term_query->get_terms() as $term) {
            $categories[$term->term_id] = $term->name;
        }

        // This is your option name where all the Redux data is stored.
        $opt_name = "Polen_Plugin_Settings";

        $theme = wp_get_theme(); // For use with some settings. Not necessary.

        $args = array(
            // TYPICAL -> Change these values as you need/desire
            'opt_name'             => $opt_name,
            // This is where your data is stored in the database and also becomes your global variable name.
            'display_name'         => 'Configurações do Site',
            // Name that appears at the top of your panel
            'display_version'      => '1.0.0',
            // Version that appears at the top of your panel
            'menu_type'            => 'menu',
            //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
            'allow_sub_menu'       => true,
            // Show the sections below the admin menu item or not
            'menu_title'           => esc_html__( 'Config. do Site', 'polen' ),
            'page_title'           => esc_html__( 'Configurações do Site', 'polen' ),
            // You will need to generate a Google API key to use this feature.
            // Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
            'google_api_key'       => '',
            // Set it you want google fonts to update weekly. A google_api_key value is required.
            'google_update_weekly' => true,
            // Must be defined to add google fonts to the typography module
            'async_typography'     => false,
            // Use a asynchronous font on the front end or font string
            'disable_google_fonts_link' => true,
            // Disable this in case you want to create your own google fonts loader
            'admin_bar'            => true,
            // Show the panel pages on the admin bar
            'admin_bar_icon'       => 'dashicons-admin-generic',
            // Choose an icon for the admin bar menu
            'admin_bar_priority'   => 55,
            // Choose an priority for the admin bar menu
            'global_variable'      => 'Polen_Plugin_Settings',
            // Set a different name for your global variable other than the opt_name
            'dev_mode'             => false,
            // Show the time the page took to load, etc
            'update_notice'        => true,
            // If dev_mode is enabled, will notify developer of updated versions available in the GitHub Repo
            'customizer'           => true,
            // Enable basic customizer support
            //'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
            //'disable_save_warn' => true,                    // Disable the save warning when a user changes a field

            // OPTIONAL -> Give you extra features
            'page_priority'        => null,
            // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
            'page_parent'          => 'options.php',
            // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
            'page_permissions'     => 'manage_options',
            // Permissions needed to access the options panel.
            'menu_icon'            => '',
            // Specify a custom URL to an icon
            'last_tab'             => '',
            // Force your panel to always open to a specific tab (by id)
            'page_icon'            => 'icon-themes',
            // Icon displayed in the admin panel next to your menu_title
            'page_slug'            => 'polen-site-settings',
            // Page slug used to denote the panel, will be based off page title then menu title then opt_name if not provided
            'save_defaults'        => true,
            // On load save the defaults to DB before user clicks save or not
            'default_show'         => false,
            // If true, shows the default value next to each field that is not the default value.
            'default_mark'         => '',
            // What to print by the field's title if the value shown is default. Suggested: *
            'show_import_export'   => true,
            // Shows the Import/Export panel when not used as a field.

            // CAREFUL -> These options are for advanced use only
            'transient_time'       => '3600',
            'output'               => true,
            // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
            'output_tag'           => true,
            // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
            'footer_credit'     => '',                   // Disable the footer credit of Redux. Please leave if you can help it.

            // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
            // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
            'use_cdn'              => true,
            // If you prefer not to use the CDN for Select2, Ace Editor, and others, you may download the Redux Vendor Support plugin yourself and run locally or embed it in your code.

            // HINTS
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

        \Redux::set_args( $opt_name, $args );

        global $wpdb;
        $sql_pages = "SELECT `ID`, `post_title` FROM `" . $wpdb->posts . "` WHERE `post_type`='page' AND `post_status`='publish'";
        $res_pages = $wpdb->get_results( $sql_pages );
        $array_pages = array();
        if( $res_pages && ! is_null( $res_pages ) && is_array( $res_pages ) && count( $res_pages ) )
        foreach( $res_pages as $k => $page ) {
            $array_pages[ $page->ID ] = $page->post_title;
        }
        // Section: Geral
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'Geral', 'polen' ),
            'id'               => 'general',
            'icon'             => 'el el-align-justify',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'admin_bar',
                    'type'     => 'switch',
                    'title'    => esc_html__('Desativar a barra administrativa do Wordpress', 'polen'),
                    'desc'     => 'Desativa a barra administrativa do Wordpress (adminbar).',
                    'default'  => true,
                ),
                array(
                    'id'       => 'register_page',
                    'type'     => 'select',
                    'title'    => esc_html__('Página de cadastro padrão', 'polen'),
                    'desc'     => 'Defina qual é a página de cadastro padrão do site.',
                    'placeholder' => 'Selecione',
                    'options'  => $array_pages,
                    'default'  => '',
                ),
                array(
                    'id'       => 'search_bar',
                    'type'     => 'switch',
                    'title'    => esc_html__('Ativar Barra de Busca', 'polen'),
                    'default'  => 0,
                ),
            ),
        ) );

        // Section: SMTP Settings
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'SMTP', 'polen' ),
            'id'               => 'polen_smtp',
            'icon'             => 'el el-envelope',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'polen_smtp_on',
                    'type'     => 'switch',
                    'title'    => esc_html__('Ativar o envio de e-mail através de serviço de SMTP', 'polen'),
                    'desc'     => 'Ative o envio de e-mails através de um serviço de SMTP.',
                    'default'  => 0,
                ),
                array(
                    'id'       => 'polen_smtp_host',
                    'type'     => 'text',
                    'title'    => esc_html__('Endereço do servidor SMTP', 'polen'),
                    'desc'     => 'Informe o endereço do servidor SMTP.',
                    'default'  => 'email-smtp.us-east-2.amazonaws.com',
                    'required' => array( 'polen_smtp_on', '=', '1' ),
                ),
                array(
                    'id'       => 'polen_smtp_port',
                    'type'     => 'text',
                    'title'    => esc_html__('Porta do servidor SMTP', 'polen'),
                    'desc'     => 'Informe a porta do servidor SMTP.',
                    'default'  => '587',
                    'required' => array( 'polen_smtp_on', '=', '1' ),
                ),
                array(
                    'id'       => 'polen_smtp_user',
                    'type'     => 'text',
                    'title'    => esc_html__('Usuário do servidor SMTP', 'polen'),
                    'desc'     => 'Informe o usuário do servidor SMTP.',
                    'default'  => 'AKIASWGKUEIQNIMMOAID',
                    'required' => array( 'polen_smtp_on', '=', '1' ),
                ),
                array(
                    'id'       => 'polen_smtp_pass',
                    'type'     => 'password',
                    'title'    => esc_html__('Senha do usuário do servidor SMTP', 'polen'),
                    'desc'     => 'Informe a senha do usuário do servidor SMTP.',
                    'default'  => 'BI1e3yLlNCIzJVvNOMt7LTXpUDirxVuzlU39UlWvNLuv',
                    'required' => array( 'polen_smtp_on', '=', '1' ),
                ),
                array(
                    'id'       => 'polen_smtp_from_name',
                    'type'     => 'text',
                    'title'    => esc_html__('Nome do remetente', 'polen'),
                    'desc'     => 'Informe o nome que deverá aparecer no remetente do e-mail.',
                    'default'  => 'Polen',
                    'required' => array( 'polen_smtp_on', '=', '1' ),
                ),
                array(
                    'id'       => 'polen_smtp_from_email',
                    'type'     => 'text',
                    'title'    => esc_html__('E-mail do remetente', 'polen'),
                    'desc'     => 'Informe o e-mail que deverá aparecer como remetente.',
                    'default'  => 'polen@c9t.pw',
                    'required' => array( 'polen_smtp_on', '=', '1' ),
                ),
            ),
        ) );
        
        
        //Credenciais do Vimeo
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'Vimeo', 'polen' ),
            'id'               => 'vimeo',
            'icon'             => 'el el-envelope',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'polen_vimeo_on',
                    'type'     => 'switch',
                    'title'    => esc_html__('Ativar a API do Vimeo como processador de videos', 'polen'),
                    'desc'     => 'Ativar a API do Vimeo como processador de videos',
                    'default'  => 0,
                ),
                array(
                    'id'       => 'polen_vimeo_client_id',
                    'type'     => 'text',
                    'title'    => esc_html__('Vimeo ClientID', 'polen'),
                    'desc'     => 'Informe do ClientID da Vimeo API.',
                    'default'  => '',
                    'required' => array( 'polen_vimeo_on', '=', '1' ),
                ),
                array(
                    'id'       => 'polen_vimeo_client_secret',
                    'type'     => 'text',
                    'title'    => esc_html__('Vimeo Client Secret', 'polen'),
                    'desc'     => 'Informe o Vimeo Client Secret da Vimeo API.',
                    'default'  => '',
                    'required' => array( 'polen_vimeo_on', '=', '1' ),
                ),
                array(
                    'id'       => 'polen_vimeo_access_token',
                    'type'     => 'text',
                    'title'    => esc_html__('Access Token', 'polen'),
                    'desc'     => 'Informe o Vimeo Access Token da Vimeo API.',
                    'default'  => '',
                    'required' => array( 'polen_vimeo_on', '=', '1' ),
                ),
                array(
                    'id'       => 'polen_histories_on',
                    'type'     => 'switch',
                    'title'    => esc_html__('Ativar player em formato Histórias para todos', 'polen'),
                    'default'  => 0,
                    'required' => array( 'polen_vimeo_on', '=', '1' ),
                ),
            ),
        ) );

        // Prazo para expirar pedidos
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'Cancelamento', 'polen' ),
            'id'               => 'order_void',
            'icon'             => 'el el-credit-card',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'order_expires',
                    'type'     => 'spinner',
                    'title'    => esc_html__('Validade do pedido', 'polen'),
                    'subtitle' => esc_html__('Informe o prazo de validade de um pedido em dias.', 'polen'),
                    'desc'     => esc_html__('Utilize essa opção para que os pedidos possam expirar automaticamente e ter o valor estornado.', 'polen'),
                    'default'  => '7',
                    'min'      => '0',
                    'max'      => '30',
                ),
            )
        ) );

        // Recaptcha do google
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'Google Recapcha Key', 'polen' ),
            'id'               => 'g_recaptcha',
            'icon'             => 'el el-credit-card',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'polen_recaptcha_secret_key',
                    'type'     => 'text',
                    'title'    => esc_html__('Secret Key', 'polen'),
                    'desc'     => 'Informa a Secret do google.',
                    'default'  => '',
                ),
                array(
                    'id'       => 'polen_recaptcha_site_key',
                    'type'     => 'text',
                    'title'    => esc_html__('Site Key', 'polen'),
                    'desc'     => 'Informe o site key do google',
                    'default'  => '',
                ),
            )
        ) );

        // Key Analitics
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'Analitics Keys', 'polen' ),
            'id'               => 'analitcs_keys',
            'icon'             => 'el el-credit-card',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'polen_google_analitics_key',
                    'type'     => 'text',
                    'title'    => esc_html__('Google Analitics Keys', 'polen'),
                    'desc'     => 'Informa o Google Analitics Keys.',
                    'default'  => '',
                ),
                array(
                    'id'       => 'polen_ca_pub_key',
                    'type'     => 'text',
                    'title'    => esc_html__('Google Ad Sense', 'polen'),
                    'desc'     => 'Informa o Google Ad Sense Keys.',
                    'default'  => '',
                ),
                array(
                    'id'       => 'polen_google_analitics_universal_key',
                    'type'     => 'text',
                    'title'    => esc_html__('Google Analitics Universal Keys', 'polen'),
                    'desc'     => 'Informa o Google Analitics Universal Keys.',
                    'default'  => '',
                ),
                array(
                    'id'       => 'polen_google_optimize_key',
                    'type'     => 'text',
                    'title'    => esc_html__('Google Optmize Keys', 'polen'),
                    'desc'     => 'Informa o Google Optmize Keys.',
                    'default'  => '',
                ),
                array(
                    'id'       => 'polen_google_tagmanager_key',
                    'type'     => 'text',
                    'title'    => esc_html__('Tag Manager Key', 'polen'),
                    'desc'     => 'Informe o Tag Manager Key',
                    'default'  => '',
                ),
                array(
                    'id'       => 'polen_heapio_key',
                    'type'     => 'text',
                    'title'    => esc_html__('Heap.IO Key', 'polen'),
                    'desc'     => 'Informe o Heap.IO Key',
                    'default'  => '',
                ),
                array(
                    'id'       => 'polen_hotjar_key',
                    'type'     => 'text',
                    'title'    => esc_html__('Hotjar Site Key', 'polen'),
                    'desc'     => 'Informe o Hotjar Site Key',
                    'default'  => '',
                ),
                array(
                    'id'       => 'polen_facebookpixel_key',
                    'type'     => 'text',
                    'title'    => esc_html__('FB Pixel Code', 'polen'),
                    'desc'     => 'Informe o Facebook Pixel Code',
                    'default'  => '',
                ),
            )
        ) );


        // Key ChatBot
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'ChatBot Keys', 'polen' ),
            'id'               => 'chatbot_keys',
            'icon'             => 'el el-mic-alt',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'polen_chatport_key',
                    'type'     => 'text',
                    'title'    => esc_html__('ChatPort AppID', 'polen'),
                    'desc'     => 'Informe o AppID que o ChatPort Entregou',
                    'default'  => '',
                ),
            )
        ) );

        // Telefone do Atendimento Online
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'Whatsapp', 'polen' ),
            'id'               => 'polen_whatsapp_number',
            'icon'             => 'el el-mic-alt',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'polen_whastsapp_phone',
                    'type'     => 'text',
                    'title'    => esc_html__('Numero do Telefone', 'polen'),
                    'subtitle' => esc_html__('Formato: 5521911111111 (codigo do pais, ddd, e telefone)', 'polen'),
                    'desc'     => 'Informe o Numero de telefone do Whastaspp',
                    'default'  => '',
                ),
                array(
                    'id'       => 'polen_whastsapp_text',
                    'type'     => 'text',
                    'title'    => esc_html__('Mensagem Inicial', 'polen'),
                    'subtitle' => esc_html__('Mensagem inicial para começar o chat', 'polen'),
                    'desc'     => 'Informe a Mensagem inicial para começar o chat',
                    'default'  => '',
                ),
                array(
                    'id'       => 'polen_whatsapp_form',
                    'type'     => 'switch',
                    'title'    => esc_html__('Exibir formulário para usuário', 'polen'),
                    'subtitle'     => 'Exibe um formulário para o usuário adicionar seu whatsapp',
                    'default'  => false,
                ),
            )
        ) );

        // Política de cookies
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'Políticas de Cookies', 'polen' ),
            'id'               => 'polen_cookies_policities',
            'icon'             => 'el el-mic-alt',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'polen_cookies_policities_text',
                    'type'     => 'editor',
                    'title'    => esc_html__('Texto com o link para as Políticas', 'polen'),
                    'desc'     => 'Texto que aparece no box de aceite de cookies',
                    'default'  => '',
                ),
            )
        ) );

        // Política de cookies
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'Integrações Zapier', 'polen' ),
            'id'               => 'polen_zapier_integrations',
            'icon'             => 'el el-mic-alt',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'polen_zapier_new_order',
                    'type'     => 'switch',
                    'title'    => esc_html__('Integração para novas compras', 'polen'),
                    'desc'     => 'Envio de informações de marketig para novas compras',
                    'default'  => '',
                ),
                array(
                    'id'       => 'polen_url_zapier_b2b_hotspot',
                    'type'     => 'text',
                    'title'    => esc_html__('URL Request para Zapier cadastrar no Hobspot', 'polen'),
                    'subtitle' => esc_html__('URL Request para Zapier cadastrar no Hobspot', 'polen'),
                    'desc'     => 'URL Request para Zapier cadastrar no Hobspot',
                    'default'  => '',
                ),
            )
        ) );
        
         // Configurar produto promocional
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'Configurar produto promocional', 'polen' ),
            'id'               => 'promotional-event',
            'icon'             => 'dashicons-edit-page',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'promotional-event-text',
                    'type'     => 'text',
                    'title'    => esc_html__('Adicionar produto promocional', 'polen'),
                    'desc'     => 'APENAS O ID DO PRODUTO',
                    'default'  => '',
                ),
                array(
                    'id'       => 'promotional-event-luccas-neto',
                    'type'     => 'text',
                    'title'    => esc_html__('Adicionar ID do Luccas Neto', 'polen'),
                    'desc'     => 'APENAS O ID DO LUCCAS NETO',
                    'default'  => '',
                ),
            )
        ) );


        // Configurar Emails
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'Configurar emails', 'polen' ),
            'id'               => 'polen_email_expire_order',
            'icon'             => 'el el-envelope',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'email_emails_order_expire_tomorrow',
                    'type'     => 'text',
                    'title'    => esc_html__('Emails Expirar Pedidos', 'polen'),
                    'desc'     => 'Emails serarados por Virgura',
                    'default'  => '',
                ),
                array(
                    'id'       => 'recipient_email_polen_company',
                    'type'     => 'text',
                    'title'    => esc_html__('Configurar email de destinatário polen empresas', 'polen'),
                    'desc'     => 'Emails serarados por Virgura',
                    'default'  => '',
                ),
                array(
                    'id'       => 'recipient_email_polen_help',
                    'type'     => 'text',
                    'title'    => esc_html__('Configurar email de destinatário polen ajuda', 'polen'),
                    'desc'     => 'Emails serarados por Virgura',
                    'default'  => '',
                ),
            )
        ) );

        // Configurar HOME
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'Configuração Home', 'polen' ),
            'id'               => 'home_config',
            'icon'             => 'el el-home',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'highlight_categories',
                    'type'     => 'select',
                    'multi'    => true,
                    'title'    => esc_html__('Destacar categoria', 'polen'),
                    'desc'     => 'Escolha as categorias para serem destacada',
                    'options'  => $categories,
                    'default'  => '',
                ),
            )
        ) );

        // Configurar API REST
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'Configuração API REST', 'polen' ),
            'id'               => 'api_rest_config',
            'icon'             => '',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'polen_api_rest_type_keys',
                    'type'     => 'select',
                    'title'    => esc_html__('Configurar tipo de chave', 'polen'),
                    'placeholder' => 'Escolha tipo da configuração',
                    'options'  => [
                        'production' => 'Produção',
                        'sandbox' => 'Sandbox',
                    ],
                    'default'  => '',
                ),
                array(
                    'id'       => 'polen_api_rest_partner_key',
                    'type'     => 'text',
                    'title'    => esc_html__('Adicionar chave partner key', 'polen'),
                    'desc'     => 'Cole aqui chave partner key do TUNA',
                    'default'  => '',
                ),
                array(
                    'id'       => 'polen_api_rest_account',
                    'type'     => 'text',
                    'title'    => esc_html__('Adicionar chave account', 'polen'),
                    'desc'     => 'Cole aqui chave partner key do TUNA',
                    'default'  => '',
                ),
            )
        ) );



        // Configurar API REST
        \Redux::set_section( $opt_name, array(
            'title'            => esc_html__( 'Dados Sendgrid Templates', 'polen' ),
            'id'               => 'sendgrid_data',
            'icon'             => '',
            'subsection'       => false,
            'fields'           => array(
                array(
                    'id'       => 'sendgrid_apikey',
                    'type'     => 'text',
                    'title'    => esc_html__('Sendgrid Apikey', 'polen'),
                    'placeholder' => 'Sendgrid Apikey',
                    'desc'     => 'Sendgrid Apikey',
                    'default'  => '',
                ),
                array(
                    'id'       => 'sendgrid_theme_galo_help',
                    'type'     => 'text',
                    'title'    => esc_html__('ThemeID Galo Help', 'polen'),
                    'desc'     => 'ThemeID Galo Help',
                    'default'  => '',
                ),
                array(
                    'id'       => Polen_Sendgrid_Redux::THEME_ID_POLEN_TALENT_ACCEPTED,
                    'type'     => 'text',
                    'title'    => esc_html__('ThemeID Polen Talento Aceitou', 'polen'),
                    'desc'     => 'ThemeID Polen Talento Aceitou',
                    'default'  => '',
                ),
                array(
                    'id'       => Polen_Sendgrid_Redux::THEME_ID_POLEN_TALENT_REJECT,
                    'type'     => 'text',
                    'title'    => esc_html__('ThemeID Polen Talento Rejeitou', 'polen'),
                    'desc'     => 'ThemeID Polen Talento Rejeitou',
                    'default'  => '',
                ),
                array(
                    'id'       => Polen_Sendgrid_Redux::THEME_ID_POLEN_PAYMENT_APPROVED,
                    'type'     => 'text',
                    'title'    => esc_html__('ThemeID Polen Pagamento Aprovado', 'polen'),
                    'desc'     => 'ThemeID Polen Pagamento Aprovado',
                    'default'  => '',
                ),
                array(
                    'id'       => Polen_Sendgrid_Redux::THEME_ID_POLEN_ORDER_COMPLETED,
                    'type'     => 'text',
                    'title'    => esc_html__('ThemeID Polen Pedido Completo', 'polen'),
                    'desc'     => 'ThemeID Polen Pedido Completo',
                    'default'  => '',
                ),
                array(
                    'id'       => Polen_Sendgrid_Redux::THEME_ID_POLEN_B2B_FORM_TO_CLIENT,
                    'type'     => 'text',
                    'title'    => esc_html__('ThemeID Polen B2B Form', 'polen'),
                    'desc'     => 'ThemeID Polen BB2 Form email para o Cliente',
                    'default'  => '',
                ),
                // array(
                //     'id'       => 'polen_api_rest_account',
                //     'type'     => 'text',
                //     'title'    => esc_html__('Adicionar chave account', 'polen'),
                //     'desc'     => 'Cole aqui chave partner key do TUNA',
                //     'default'  => '',
                // ),
            )
        ) );
    }

    public function save( $args ) {
        // Ações a serem executadas após o salvamento das configurações.
    }

    function remove_demo() {
        if ( class_exists( '\ReduxFrameworkPlugin' ) ) {
            remove_filter( 'plugin_row_meta', array( \ReduxFrameworkPlugin::instance(), 'plugin_metalinks' ), null, 2 );
            remove_action( 'admin_notices', array( \ReduxFrameworkPlugin::instance(), 'admin_notices' ) );
        }
    }

}
