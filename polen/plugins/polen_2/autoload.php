<?php


spl_autoload_register (function ( $class_name ) {
    
    $prefix = 'Polen\\';
    $php_ds = DIRECTORY_SEPARATOR;
    
    $base_dir = $custom_base_dir ?? __DIR__ . $php_ds;

    $len = strlen($prefix);
    $relative_class = substr($class_name, $len);
    $arrayNames = explode('\\', $relative_class);
    if(sizeOf($arrayNames) > 1) {
        for($i = 0; $i < sizeOf($arrayNames) - 1; $i++) {
            $base_dir .= strtolower($arrayNames[$i]) . $php_ds;
        }
    }
    $file = rtrim($base_dir, $php_ds) . $php_ds . str_replace('\\', $php_ds, $arrayNames[sizeOf($arrayNames) - 1]) . '.php';
    if(file_exists( $file )) {
        require_once $file ;
    }
});