<?php

use Polen\Master_class\Master_Class_Rewrite;


function master_class_url_home()
{
	return site_url( Master_Class_Rewrite::BASE_URL . '/ronnie-von/beaba-do-vinho' );
}

function master_class_url_success()
{
	return master_class_url_home() . '/sucesso/';
}

function master_class_is_home()
{
	$is_set = isset( $GLOBALS[ Master_Class_Rewrite::QUERY_VARS_MASTER_CLASS_IS_HOME ] );
	if( $is_set && $GLOBALS[ Master_Class_Rewrite::QUERY_VARS_MASTER_CLASS_IS_HOME ] == '1' ) {
		return true;
	}

	return false;
}

function master_class_is_app()
{
	$is_set = isset( $GLOBALS[ Master_Class_Rewrite::QUERY_VARS_MASTER_CLASS_IS_HOME ] );
	if( $is_set && $GLOBALS[ Master_Class_Rewrite::QUERY_VARS_MASTER_CLASS_IS_HOME ] == '1' ) {
		return true;
	}

	$is_sucess = isset( $GLOBALS[ Master_Class_Rewrite::QUERY_VARS_MASTER_CLASS_SUCCESS ] );
	if( $is_sucess && $GLOBALS[ Master_Class_Rewrite::QUERY_VARS_MASTER_CLASS_SUCCESS ] == '1' ) {
		return true;
	}

	return false;
}
