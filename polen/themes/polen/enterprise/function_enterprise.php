<?php

use Polen\Enterprise\Enterprise_Rewrite;


function enterprise_url_home()
{
	return site_url( Enterprise_Rewrite::BASE_URL );
}

function enterprise_url_success()
{
	return enterprise_url_home() . '/sucesso';
}

function enterprise_is_home()
{
	$is_set = isset( $GLOBALS[ Enterprise_Rewrite::QUERY_VARS_MASTER_CLASS_IS_HOME ] );
	if( $is_set && $GLOBALS[ Enterprise_Rewrite::QUERY_VARS_MASTER_CLASS_IS_HOME ] == '1' ) {
		return true;
	}

	return false;
}

function enterprise_is_app()
{
	$is_set = isset( $GLOBALS[ Enterprise_Rewrite::QUERY_VARS_MASTER_CLASS_IS_HOME ] );
	if( $is_set && $GLOBALS[ Enterprise_Rewrite::QUERY_VARS_MASTER_CLASS_IS_HOME ] == '1' ) {
		return true;
	}

	$is_sucess = isset( $GLOBALS[ Enterprise_Rewrite::QUERY_VARS_MASTER_CLASS_SUCCESS ] );
	if( $is_sucess && $GLOBALS[ Enterprise_Rewrite::QUERY_VARS_MASTER_CLASS_SUCCESS ] == '1' ) {
		return true;
	}

	return false;
}
