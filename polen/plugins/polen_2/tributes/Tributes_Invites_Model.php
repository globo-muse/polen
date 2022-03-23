<?php
namespace Polen\Tributes;

class Tributes_Invites_Model
{

    const TABLE_NAME = 'tributes_invites';

    const ERROR_EMAIL_UNIQUE = 'tribute_email_unique';
    const ERROR_HASH_UNIQUE = 'tribute_hash_unique';

    /**
     * Pega o nome da Tabela
     */
     public static function table_name()
     {
         global $wpdb;
         return $wpdb->base_prefix . self::TABLE_NAME;
     }


    /**
     * Insere um Invite
     * 
     * @param array
     */
    public static function insert( $data )
    {
        global $wpdb;
        $table_name = self::table_name();
        $result_insert = $wpdb->insert(
            $table_name,
            $data
        );
        if( $result_insert === false ) {
            throw new \Exception( $wpdb->last_error, 401 );
        }
        return $wpdb->insert_id;
    }

    /**
     * Update em um Registro baseado no ID
     * 
     * @param array
     */
    public static function update( $data )
    {
        global $wpdb;
        $table_name = self::table_name();

        $result_update = $wpdb->update(
            $table_name,
            $data,
            array( 'ID' => $data[ 'ID' ])
        );
        if( $result_update === false ) {
            throw new \Exception( $wpdb->last_error, 401 );
        }
        return $data[ 'ID' ];
    }


    /**
     * Deletar um registro
     * @param array
     */
    public static function delete( $data )
    {
        global $wpdb;
        $table_name = self::table_name();

        $result_delete = $wpdb->delete(
            $table_name,
            array( 'ID' => $data[ 'ID' ])
        );
        if( $result_delete === false ) {
            throw new \Exception( $wpdb->last_error, 401 );
        }
        return $data[ 'ID' ];
    }


    /**
     * Seta um Invite como Email_Lido 
     * 
     * @param int
     * @return bool
     */
    public static function set_invite_email_opened( $invite_id )
    {
        $data_update = array(
            'ID' => $invite_id,
            'email_opened' => '1'
        );
        self::update( $data_update );
        return true;
    }


    /**
     * Seta um Invite como Email_Lido 
     * 
     * @param int
     * @return bool
     */
    public static function set_invite_email_clicked( $invite_id )
    {
        $data_update = array(
            'ID' => $invite_id,
            'email_clicked' => '1'
        );
        self::update( $data_update );
        return true;
    }


    /**
     * Pegar Invite pelo ID
     * 
     * @param int
     */
    public static function get_by_id( $id )
    {
        global $wpdb;
        $table_name = self::table_name();
        $result = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM `{$table_name}` WHERE `ID` = %s", $id )
        );
        return $result;
    }


    /**
     * Pega todos so invites pelo tribute_id
     * 
     * @param int
     * @return array stdClass[] wp_tributes_invites
     */
    public static function get_all_by_tribute_id( $tribute_id )
    {
        global $wpdb;
        $table_name = self::table_name();
        $result = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$table_name} WHERE tribute_id = %d;", $tribute_id )
        );
        return $result;
    }



    /**
     * Pega a qtd de invites pelo TributeID
     * 
     * @param int
     * @return int
     */
    public static function get_count_by_tribute_id( $tribute_id )
    {
        global $wpdb;
        $table_name = self::table_name();
        $result = $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(*) total FROM {$table_name} WHERE tribute_id = %d;", $tribute_id )
        );
        return $result;
    }


    /**
     * Pega todos so invites que enviaram o video pelo tribute_id
     * 
     * @param int
     * @return array stdClass[] wp_tributes_invites
     */
    public static function get_all_video_sent_by_tribute_id( $tribute_id )
    {
        global $wpdb;
        $table_name = self::table_name();
        $result = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$table_name} WHERE video_sent = 1 AND tribute_id = %d;", $tribute_id )
        );
        return $result;
    }
     

    /**
     * Pega um Invite pega Hash Unica
     * 
     * @param string
     */
    public static function get_by_hash( $hash )
    {
        global $wpdb;
        $table_name = self::table_name();
        $result = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM `{$table_name}` WHERE `hash` = %s", $hash )
        );
        return $result;
    }


    public static function get_vimeo_not_processed_yet()
    {
        global $wpdb;
        $table_name = self::table_name();
        $result = $wpdb->get_results( "SELECT * FROM `{$table_name}` WHERE `video_sent` = 1 AND `vimeo_process_complete` = 0;" );
        return $result;
    }


    public static function get_vimeo_processed_by_trubute_id( $tribute_id )
    {
        global $wpdb;
        $table_name = self::table_name();
        $result = $wpdb->get_results( 
            $wpdb->prepare( "SELECT * FROM `{$table_name}` WHERE `vimeo_process_complete` = 1 AND tribute_id = %d;", $tribute_id )
        );
        return $result;
    }


    /**
     * Pega o total de videos enviados 
     * 
     * @param string
     */
    public static function get_videos_sent_and_not( $tribute_id )
    {
        global $wpdb;
        $table_name = self::table_name();
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT ( SELECT COUNT( * ) FROM `{$table_name}` WHERE tribute_id = %s AND video_sent = '1' ) video_sent,
                        ( SELECT COUNT( * ) FROM `{$table_name}` WHERE tribute_id = %s AND video_sent = '0' ) video_not_sent;",
                        $tribute_id,
                        $tribute_id
            )
        );
        return $result;
    }


    /**
     * Criar um Hash unica para o amigo criar um video
     * 
     * @param int
     */
    public static function create_hash( $tribute_id )
    {
        return md5( $tribute_id . rand( 0, 100000 ) . date( 'Y-m-d H:i:s' ) );
    }

}