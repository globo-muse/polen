<?php

namespace Polen\Tributes;

use Polen\Admin\Partials\Tributes_Display;
use Polen\Includes\Debug;
use Polen\Includes\Vimeo\Polen_Vimeo_Response;
use Vimeo\Vimeo;

class Tributes_Details_Admin
{
    public function __construct( $static = false )
    {
        if( $static ) {
            add_action( 'admin_menu', array( $this, 'tributes_add_admin_menu' ) );

            add_action( 'wp_ajax_tributes_download_video', [ $this, 'get_link_download' ] );
            add_action( 'wp_ajax_complete_tribute', [ $this, 'complete_tribute' ] );
        }
    }

    /**
     * Admin Menu
     */
    public function tributes_add_admin_menu(){
        add_submenu_page( 
            null,
            'My Custom Submenu Page',
            'My Custom Submenu Page',
            'manage_options',
            'tributes_details',
            array( $this, 'show_tribute_details' ),
        );
    }

    public function show_tribute_details()
    {
        $tribute_id = filter_input( INPUT_GET, 'tribute_id', FILTER_VALIDATE_INT );
        $tribute = Tributes_Model::get_by_id( $tribute_id );
        $tribute_success = tributes_tax_success_tribute( $tribute_id );
        $invites = Tributes_Invites_Model::get_all_by_tribute_id( $tribute_id );
        $deadline = date( 'd/m/Y', strtotime( $tribute->deadline ) );
        ?>
        <div class="wrap">
            <h2>Detalhes do Colab </h2>

            <div>
                <table class="wp-list-table widefat fixed table-view-list">
                    <tr>
                        <th>Criador</th>
                        <th>Email</th>
                        <th>Tx sucesso</th>
                        <th>Link</th>
                        <th>Prazo</th>
                        <th>Concluido</th>
                    </tr>
                    <tr>
                        <td><?= $tribute->creator_name;?></td>
                        <td><?= $tribute->creator_email;?></td>
                        <td><?= $tribute_success;?></td>
                        <td><a href="<?= tribute_get_url_tribute_detail( $tribute->hash );?>" target="_blank">Ir para o Colab</a></td>
                        <td><?= $deadline;?></td>
                        <td><?= $this->show_icon_if_row_table_is_1( $tribute->completed );?></td>
                    </tr>
                </table>
            </div>

            <div>
                <h4>Lista de convites</h4>
                <table class="wp-list-table widefat fixed striped table-view-list">
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Abriu Email</th>
                        <th>Clicou Email</th>
                        <th>Enviado</th>
                        <th>Completo</th>
                        <th>Link download</th>
                    </tr>
                <?php foreach( $invites as $invite ) : ?>
                    <?php $download_element = ($invite->vimeo_process_complete == '1' ) ? '<a href="#" class="download_vimeo_link" data="'.$invite->ID.'">Download</a>' : ''; ?>
                    <tr>
                        <td><?= $invite->name_inviter;?></td>
                        <td><?= $invite->email_inviter;?></td>
                        <td><?= $this->show_icon_if_row_table_is_1( $invite->email_opened );?></td>
                        <td><?= $this->show_icon_if_row_table_is_1( $invite->email_clicked );?></td>
                        <td><?= $this->show_icon_if_row_table_is_1( $invite->video_sent );?></td>
                        <td><?= $this->show_icon_if_row_table_is_1( $invite->vimeo_process_complete );?></td>
                        <td><?= $download_element; ?></td>
                    </tr>
                <?php endforeach; ?>
                </table>
            </div>
        <?php if( $tribute->completed == '0' ) : ?>
            <div>
                <form method="post" id="tribute-complete-tribute" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
                    <div id="universal-message-container">
                        <h2>Completar o Colab</h2>
                        <div class="options">
                            <p>
                                <label>Depois de editar o video final e subir no Vimeo, adicionar o VimeoID aqui: ex /videos/572785228:</label>
                                <br />
                                <input type="text" name="vimeo_id" size="50" placeholder="" value=""/>
                                <input type="hidden" name="tribute_id" value="<?= $tribute_id; ?>"/>
                            </p>
                        </div><!-- #universal-message-container -->
                        <?php
                            wp_nonce_field( 'complete_tribute', 'security' );
                            submit_button( 'Finalizar Colab' );
                        ?>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div>
                    <div id="universal-message-container">
                        <h2>Colab Finalizado</h2>
                        <div class="options">
                            <p>
                                Video final: <a href="http://vimeo.com<?= $tribute->vimeo_id; ?>" target="_blank"><?= $tribute->vimeo_id; ?></a>
                            </p>
                        </div><!-- #universal-message-container -->
                    </div>
            </div>
        <?php endif; ?>
        </div>

        <script>
            jQuery(function(){
                jQuery('.download_vimeo_link').click(function(evt){
                    evt.preventDefault();
                    let invite_id = evt.currentTarget.getAttribute('data');
                    <?php $url_admin = admin_url('admin-ajax.php') . '?action=tributes_download_video'; ?>
                    jQuery.post("<?= $url_admin; ?>", {invite_id}, function(data){
                        if( data.success == true ) {
                            window.location.href = data.data;
                        }
                    });
                });
                jQuery('#tribute-complete-tribute').submit(function(evt){
                    evt.preventDefault()
                    <?php $url_admin_ct = admin_url('admin-ajax.php') . '?action=complete_tribute'; ?>
                    jQuery.post("<?= $url_admin_ct; ?>", jQuery(evt.currentTarget).serialize(), function(data){
                        if( data.success == true ) {
                            alert('Colab encerrado');
                            document.location.reload(true);
                        } else {
                            alert( data.data );
                        }
                    });
                });
            });
        </script>
        <?php
    }

    private function show_icon_if_row_table_is_1( $param )
    {
        if( $param == '1' ) {
            return '<span class="dashicons dashicons-yes"></span>';
        } else {
            return '<span class="dashicons dashicons-no-alt"></span>';
        }
    }
    
    
    public function complete_tribute()
    {
        global $Polen_Plugin_Settings;

        $client_id = $Polen_Plugin_Settings['polen_vimeo_client_id'];
        $client_secret = $Polen_Plugin_Settings['polen_vimeo_client_secret'];
        $token = $Polen_Plugin_Settings['polen_vimeo_access_token'];

        $vimeo_id = filter_input( INPUT_POST, 'vimeo_id' );
        $tribute_id = filter_input( INPUT_POST, 'tribute_id' );
        $nonce = filter_input( INPUT_POST, 'security' );
        $lib = new Vimeo( $client_id, $client_secret, $token );
        try {
            $response = new Polen_Vimeo_Response( $lib->request( $vimeo_id ) );
            if( $response->is_error() ) {
                throw new \Exception( 'Erro no Vimeo', 500 );
            }
            $data_update = array(
                'ID' => $tribute_id,
                'vimeo_id' => $vimeo_id,
                'vimeo_thumbnail' => $response->get_image_url_640(),
                'vimeo_link' => $response->get_vimeo_link(),
                'vimeo_url_file_play' => $response->get_play_link(),
                'completed' => '1',
                'completed_at' => date( 'Y-m-d H:i:s' ),
            );
            Tributes_Model::update( $data_update );

            // if( !$this->resend_email_complete_success( $tribute_id ) ) {
            //     throw new \Exception( 'Erro no envio do email mas o Colab foi concluído. Reenvie o email', 500 );
            // }

            
            $this->resend_email_complete_success( $tribute_id );
            wp_send_json_success( 'success', 200 );
        } catch ( \Exception $e ) {
            wp_send_json_error( $e->getMessage(), $e->getCode() );
        }
        wp_die();
    }


    public function resend_email_complete_success( $tribute_id )
    {
        $tribute = Tributes_Model::get_by_id( $tribute_id );
        $email_content = tributes_email_content_complete_tribute( $tribute );
        tributes_send_email( $email_content, $tribute->creator_name, $tribute->creator_email );
        $invites = Tributes_Invites_Model::get_all_video_sent_by_tribute_id( $tribute_id );
        foreach( $invites as $invite ) {
            $email_content_invites = tributes_email_content_complete_tribute_to_invites( $tribute, $invite );
            tributes_send_email( $email_content_invites, $invite->name_inviter, $invite->email_inviter );
        }
        return true;
    }


    /**
     * 
     */
    public function get_link_download()
    {
        global $Polen_Plugin_Settings;

        $client_id = $Polen_Plugin_Settings['polen_vimeo_client_id'];
        $client_secret = $Polen_Plugin_Settings['polen_vimeo_client_secret'];
        $token = $Polen_Plugin_Settings['polen_vimeo_access_token'];

        $invite_id = filter_input( INPUT_POST, 'invite_id' );
        $invite = Tributes_Invites_Model::get_by_id( $invite_id );

        $lib = new Vimeo( $client_id, $client_secret, $token );
        try {
            $response = new Polen_Vimeo_Response( $lib->request( $invite->vimeo_id ) );
            wp_send_json_success( $response->get_download_source_url(), 200 );
        } catch ( \Exception $e ) {
            \wp_send_json_error( $e->getMessage(), $e->getCode() );
        }
        wp_die();
    }
}