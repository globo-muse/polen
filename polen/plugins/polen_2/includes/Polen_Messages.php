<?php

namespace Polen\Includes;

abstract class Polen_Messages
{
    const SESSION_NAME = 'polen-message';
    const SESSION_NAME_MESSAGE = 'message';
    const SESSION_NAME_TYPE = 'type';

    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'error';

    // add
    static public function set_message( $message, $type = self::TYPE_SUCCESS )
    {
        $_SESSION[ self::SESSION_NAME ][ self::SESSION_NAME_TYPE ] = $type;
        $_SESSION[ self::SESSION_NAME ][ self::SESSION_NAME_MESSAGE ] = $message;
    }


    // get type
    static public function get_type()
    {
        if( !self::has_message() ) {
            return null;
        }
        return $_SESSION[ self::SESSION_NAME ][ self::SESSION_NAME_TYPE ];
    }


    // get message
    static public function get_message()
    {
        if( !self::has_message() ) {
            return null;
        }
        return $_SESSION[ self::SESSION_NAME ][ self::SESSION_NAME_MESSAGE ];
    }


    // clear
    static public function clear_messages()
    {
        if( isset( $_SESSION[ self::SESSION_NAME ] ) ) {
            unset( $_SESSION[ self::SESSION_NAME ] );
        }
    }


    // has
    static public function has_message()
    {
        if( isset( $_SESSION[ self::SESSION_NAME ] ) && !empty( $_SESSION[ self::SESSION_NAME ] ) ) {
            return true;
        }
        return false;
    }


    // has_sucess
    static public function has_message_success()
    {
        if( self::has_message() && self::TYPE_SUCCESS === self::get_type() ) {
            return true;
        }
        return false;
    }


    // has_error
    static public function has_message_error()
    {
        if( self::has_message() && self::TYPE_ERROR === self::get_type() ) {
            return true;
        }
        return false;
    }
}