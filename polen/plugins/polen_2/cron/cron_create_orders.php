<?php

include_once dirname( __FILE__ ) . '/init.php';
include_once dirname( __FILE__ ) . '/_create_order.php';

// $email = 'rodolfoneto@gmail.com';
// $cidade = 'Recife';
// $nome = 'Rodolfo';
$file_path = dirname( __FILE__ ) . '/data.csv';
if( ( $handler = fopen( $file_path, 'r' ) ) ) {
    while(( $data = fgetcsv( $handler, 1000, ',' ) ) !== false ) {
        $ranking = $data[0];
        $name = $data[1];
        $email = $data[2];
        $cidade = $data[3];
        // var_dump( $data );
        var_dump( create_social_order( $email, $cidade, $name ) );
    }
}

