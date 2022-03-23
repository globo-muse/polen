<?php
namespace Polen\Includes\Invite_Talent;

use Polen\Includes\Db\Polen_DB;

class Polen_Invite_Talent_Model extends Polen_DB
{

    public $ID;
    public $name;
    public $page_source;
    public $is_mobile;
    public $created_at;
    public $updated_at;

    /**
     * 
     */
    public function table_name()
    {
        return $this->wpdb->prefix . 'invite_talent';
    }


    /**
     * 
     */
    public function get_data_insert()
    {
        $return = [];
        
        $return[ 'name' ]        = $this->name;
        $return[ 'page_source' ] = $this->page_source;
        $return[ 'is_mobile' ]   = $this->is_mobile;

        if ( !empty( $this->updated_at ) ) {
            $return[ 'updated_at' ] = $this->updated_at;
        }

        return $return;
    }


    /**
     * Funcao com validacoes e acoes antes do insert
     */
    public function pre_insert()
    {
        return;
    }


    /**
     * Insere os dados que estão na instancia
     * @return int
     */
    public function insert()
    {
        $this->ID = parent::insert();
        $this->valid = true;
        return $this->ID;
    }


    /**
     * Deleta um item Passado pelo WHERE ou deleta a instancia do banco
     * @param array $where [ ID => 10 ]
     * @return int || false
     */
    public function delete( array $where = null ) {
        if( empty( $where ) ) {
            $where = array( 'ID' => $this->ID );
        } else if ( !$this->valid && empty( $where ) ) {
            return false;
        }
        return parent::delete( $where );
    }


    /**
     * Cria um array de objectos do Polen_Video_Info
     * 
     * @param array $data
     * @return array
     */
    static public function create_instance_many( $data )
    {
        $many_objects = array();
        foreach ( $data as $item ) {
            $many_objects[] = self::create_instance_one( $item );
        }
        return $many_objects;
    }
    
    
    /**
     * Cria um objeto apartir de um array, geralmente vindo do BD
     * ou seja transforma um resultado de DB para um Objecto
     * 
     * @param stdClass $data
     * @return Polen_Video_Info
     */
    static public function create_instance_one( $data, $valid = true )
    {
        $object              = new self();
        $object->ID          = $data->ID;
        $object->name        = $data->name;
        $object->page_source = $data->page_source;
        $object->is_mobile   = $data->is_mobile;
        $object->created_at  = $data->created_at;
        $object->updated_at  = $data->updated_at;
        return $object;
    }
}