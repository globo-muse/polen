<?php

namespace Polen\Includes\Db;

abstract class Polen_DB
{
    
    private $wpdb;
    public $table_name;
    public $valid = false;
    
    function __construct()
    {
        global $wpdb;
        
        $this->wpdb = $wpdb;
        $this->table_name = $this->table_name();
    }
    
    public function __get( $param ) {
        if( $param == 'wpdb') {
            return $this->wpdb;
        }
    }
    
    
    /*
     * Retuna o nome da Tabela
     * Esse metodo precisa ser sobrescrito
     * 
     */
    public function table_name()
    {
        throw new \Exception('Esse metodo tem que ser sobreecrito', 500);
    }
    
    public function get_data_insert()
    {
        throw new \Exception('Esse metodo tem que ser sobreecrito', 500);
    }
    
    public function get_data_update()
    {
        throw new \Exception('Esse metodo tem que ser sobreecrito', 500);
    }
    
    public function insert()
    {
        $this->pre_insert();
        
        $this->wpdb->insert(
                $this->table_name,
                $this->get_data_insert()
            );
        if( empty( $this->wpdb->last_error ) ) {
            return $this->wpdb->insert_id;
        } else {
            throw new \Exception( $this->wpdb->last_error, 500 );
        }
    }

    
    /**
     * Para validação ou ações antes do insert no DB
     * @throws Exception
     */
    public function pre_insert()
    {
        throw new \Exception( 'Esse metodo tem que ser sobreecrito', 500);
    }
    
    
    /**
     * Fazer Update de um registro no tabela
     * @param array $where ['ID' => 1]
     */
    public function update( array $where )
    {
        $this->pre_update();
        
        $this->wpdb->update(
                $this->table_name,
                $this->get_data_update(),
                $where
            );
        if( empty( $this->wpdb->last_error ) ) {
            return $this->wpdb->insert_id;
        } else {
            throw new \Exception( $this->wpdb->last_error, 500 );
        }
    }
    
    
    /**
     * Para validação ou ações antes do insert no DB
     * @throws Exception
     */
    public function pre_update(){}
    
    
    /**
     * 
     * @param array $where
     * @return type
     * @throws \Exception
     */
    public function delete( array $where )
    {
        $this->wpdb->delete(
                $this->table_name,
                $where
            );
        if( empty( $this->wpdb->last_error ) ) {
            return $this->wpdb->insert_id;
        } else {
            throw new \Exception( $this->wpdb->last_error, 500 );
        }
    }
    
    
    /**
     * Retona uma linha do banco de dados, buscando pelo ID
     * @param int $id
     * @return stdClass
     */
    public function get_by_id( int $id )
    {
        return static::create_instance_one( $this->get( 'ID', $id, '%d' ) );
    }


    /**
     * Retona uma linha do banco de dados, buscando pelo ID
     * @param int $id
     * @return stdClass
     */
    public static function get_by_id_static( int $id )
    {
        if( empty( static::get_static( 'ID', $id, '%d' ) ) ) {
            return null;
        }
        return static::create_instance_one( static::get_static( 'ID', $id, '%d' ) );
    }
    
    
    /**
     * 
     * @param type $field
     * @param type $value
     * @param type $format
     * @return type
     */
    public function get( $field, $value, $format = "%s" )
    {
        return self::create_instance_one( $this->wpdb->get_row(
            $this->wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE {$field} = {$format} ORDER BY ID DESC;", $value )
        ) );
    }


    /**
     * 
     * @param type $field
     * @param type $value
     * @param type $format
     * @return type
     */
    public static function get_static( $field, $value, $format = "%s" )
    {
        global $wpdb;
        $obj = new static();
        return self::create_instance_one( $obj->wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$obj->table_name} WHERE {$field} = {$format};", $value )
        ) );
    }
    
    
    /**
     * 
     * @param type $field
     * @param type $value
     * @param type $format
     * @param int $limit
     * @param type $order
     * @return type
     * @throws \Exception
     */
    public function get_results( $field, $value, $format = '%s', int $limit = 0, $order = '' )
    {
        $limit_format = ( !empty( $limit ) ) ? ' LIMIT %d' : ' -- %d';
        $sql_prepared = $this->wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE {$field} = {$format} {$order} {$limit_format};", $value, $limit );
        $result = $this->wpdb->get_results( $sql_prepared );
        if( !empty( $this->wpdb->last_error ) ) {
            throw new \Exception( $this->wpdb->last_error, 500 );
        }
        $result_converted_into_object = static::create_instance_many( $result );
        return $result_converted_into_object;
    }
    
    
    /**
     * Pega um resultado passadno varias colunas no WHERE
     * @param array $fields_values ['id'=>XX, 'date'=>'xxxx-xx-xx']
     * @param string $limit string
     * @param string $order completa do order "ORDER BY ID ASC"
     * @return type
     */
    static public function get_result_multi_fields( array $fields_values, string $limit = null, string $order = "" )
    {
        $class = new static();
        
        $fields = $class->treat_fields( $fields_values );
        $values = $class->treat_values( $fields_values );
        
        if( !empty( $limit ) ) {
            $values[] = $limit;
            $limit = $class->treat_limit_field( $limit );
        }
        
        $sql = "SELECT * FROM {$class->table_name} WHERE $fields {$order} {$limit};";
        $result = $class->wpdb->get_results(
                $class->wpdb->prepare( $sql, $values )
            );
        $result_converted = static::create_instance_many($result);
        
        return $result_converted;
    }
    
    
    /**
     * 
     * @param array $fields_values
     * @return type
     */
    public function treat_fields( array $fields_values )
    {
        return implode( ' = %s AND ', array_keys( $fields_values ) ) . ' = %s';
    }
    
    
    /**
     * 
     * @param array $fields_values
     * @return type
     */
    public function treat_values( array $fields_values )
    {
        return array_values( $fields_values );
    }
    
    
    /**
     * 
     * @param type $limit
     * @return string
     */
    public function treat_limit_field( $limit = null )
    {
        if( !empty( $limit ) ) {
            return "LIMIT %d";
        }
        return "";
    }
    
    
    /**
     * 
     * @param type $limit
     * @return type
     */
    public function treat_limit_value( $limit )
    {
        if( !empty( $limit ) ) {
            return $limit;
        }
    }
    
    
    /**
     * 
     * @param type $data
     * @return type
     */
    static public function create_instance_one( $data )
    {
        return $data;
    }
    
    /**
     * 
     * @param type $data
     * @return type
     */
    static public function create_instance_many( $data )
    {
        $result_objects = array();
        foreach ( $data as $item ) {
            $result_objects[] = static::create_instance_one( $item );
        }
        return $result_objects;
    }
    
    
}
