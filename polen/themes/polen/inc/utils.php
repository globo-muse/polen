<?php

/**
 * Saber se o dispositivo é mobile
 * @return bool
 */
function polen_is_mobile() {
    $detect = new Mobile_Detect();
    return $detect->isMobile();
}


function polen_get_initials_name_by_user( $user )
{
    $name = $user->first_name . ' ' . $user->last_name;
    if( empty( trim( $name ) ) ) {
        $name = $user->display_name;
    }
    if( empty( trim( $name ) ) ) {
        $name = $user->nickname;
    }
    return polen_get_initials_name( $name );
}

/**
 * Generate initials from a name
 * https://chrisblackwell.me/generate-perfect-initials-using-php/
 *
 * @param string $name
 * @return string
 */
function polen_get_initials_name( $name )
{
    $words = explode( ' ', $name );
    if (count($words) >= 2) {
        return strtoupper( substr( $words[ 0 ], 0, 1 ) . substr( end( $words ), 0, 1 ) );
    }
    return _polen_makeInitialsFromSingleWord( $name );
}

/**
 * Make initials from a word with no spaces
 * https://chrisblackwell.me/generate-perfect-initials-using-php/
 *
 * @param string $name
 * @return string
 */
function _polen_makeInitialsFromSingleWord( $name )
{
    preg_match_all( '#([A-Z]+)#', $name, $capitals );
    if ( count( $capitals[ 1 ] ) >= 2 ) {
        return substr( implode( '', $capitals[ 1 ] ), 0, 2 );
    }
    return strtoupper( substr( $name, 0, 2 ) );
}


function polen_get_protocol()
{
	return (!empty($_SERVER['HTTPS']) &&
				$_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)
				? "https:"
				: "http:";
}


function polen_is_landingpage()
{
	global $lp_sigin_lead;
	return isset($lp_sigin_lead) && $lp_sigin_lead === true;
}

function polen_get_thumbnail($post_id)
{
	$attach_id = get_post_thumbnail_id($post_id);
	$image = wp_get_attachment_image_src($attach_id, 'polen-thumb-lg')[0];
	$image_alt = get_post_meta( $attach_id, '_wp_attachment_image_alt', true);
	return array("image" => $image, "alt" => $image_alt);
}

function polen_queried_object()
{
	$queried_object = get_queried_object();
    if( empty( $queried_object ) )
    {
        return null;
    }
	return in_array($queried_object->taxonomy, array("product_cat", "product_tag")) ? $queried_object : null;
}
