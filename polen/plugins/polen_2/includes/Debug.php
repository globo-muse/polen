<?php

namespace Polen\Includes;

class Debug
{
    static public function def(...$data) {
        ob_start();
        
        foreach ($data as $dt) {
            var_dump($dt);
            echo "<br>\r\n";
        }
        $dataFlush = ob_get_contents();
        ob_end_clean();
    
        $myfile = fopen(ABSPATH . "debug.html", "w") or die("Unable to open file!");
        fwrite($myfile, '<pre>'.$dataFlush . "\n");
        fclose($myfile);
    }
}
