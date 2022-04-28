<?php
namespace Polen\Admin;
defined( 'ABSPATH' ) || die;

use Polen\Admin\Polen_Admin_Export_Order_Campaign;
use Polen\Includes\Ajax\Polen_Cupom_Create_Controller;
use Polen\Tributes\Tributes_Admin;
use Polen\Tributes\Tributes_Details_Admin;
use WC_Emails;

class Polen_Admin {


	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        
		$this->actions( true );
        if( is_admin() ) {
            new Tributes_Admin( true );
            new Tributes_Details_Admin( true );
        }
	}
        
  public function actions()
  {
      add_action( 'admin_init', [ $this, 'init_classes' ], 10 );
  }

  public function init_classes( bool $static = true )
  {
          new Polen_Admin_DisableMetabox( $static );
          // new Polen_Update_Fields( $static );
          new Polen_Admin_RedirectTalentAccess();
          new Polen_Admin_Order_Custom_Fields( $static );
          new Polen_Cupom_Create_Controller( $static );
          new Polen_Admin_Video_Info( $static );
          new Polen_Admin_B2B_Product_Fields( $static );
          new Polen_Admin_Social_Base_Product_Fields( $static );
          new Polen_Admin_Order_Custom_Fields_Deadline_BulkActions( $static );
          new Polen_Admin_Event_Promotional_Event_Fields( $static );
          new Polen_Admin_Export_Order_Campaign( $static );
          new Polen_Admin_Order_B2B($static);
  }

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/polen-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
        wp_register_script( 'vuejs', plugin_dir_url( __FILE__ ) . 'js/vendor/' . get_assets_folder() . 'vue.js', array(), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/polen-admin.js', array( 'jquery', 'vuejs' ), $this->version, false );
	}
        
        
    /**
     * Retorna o caminho do arquivo de um metabox
     * @param type $file
     * @return string
     */
    public static function get_metabox_path( string $file )
    {
        $dir_admin = PLUGIN_POLEN_DIR . 'admin/partials/metaboxes/' . $file;
        return $dir_admin;
    }


    /**
     * Retorna a url do arquivo de um JS
     * @param string $file
     * @return string
     */
    public static function get_js_url( string $file )
    {
        return PLUGIN_POLEN_URL . 'admin/js/' . $file;
    }

    /**
     * Integração com zapier via backend
     */
    public function zapier_mail()
    {
        try {
            $urls = [
                'newsletter'   => 'https://hooks.zapier.com/hooks/catch/10583855/b252jhj/',
                'new_account'  => 'https://hooks.zapier.com/hooks/catch/10583855/b25uia6/',
                'landing_page' => 'https://hooks.zapier.com/hooks/catch/10583855/b25u8xz/',
                'pushcharse'   => 'https://hooks.zapier.com/hooks/catch/10583855/buaf22k/',
            ];

            $zapier = $_POST['zapier'];

            switch ($zapier) {
                case 1:
                    $url = $urls['newsletter'];
                    break;
                case 2:
                    $url = $urls['new_account'];
                    break;
                case 3:
                    $url = $urls['landing_page'];
                    break;
                case 4:
                    $url = $urls['pushcharse'];
                    break;
                default:
                    wp_send_json_error('ID Zapier nulo ou incorreto', 422);
                    wp_die();
            }

            $fields = $_POST;
            unset($fields['zapier']);

            $data = [];
            foreach ($fields as $key => $field) {
                $data[$key] = sanitize_text_field($field);
            }

            $response = wp_remote_post($url, array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'headers' => array(),
                    'body' => $data,
                )
            );

            if (is_wp_error($response)) {
                wp_send_json_error('Sistema indisponível. Por favor entre em contato com o suporte', 503);
                wp_die();
            }

            wp_send_json_success('ok', 200);

        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage(), 422);
            wp_die();
        }
    }

}
