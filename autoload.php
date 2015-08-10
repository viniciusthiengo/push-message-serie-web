<?php
    spl_autoload_register(function ($class) {
        $paths = array('../../package/apl/',
            '../../package/cdp/',
            '../../package/cgd/',
            '../../package/ctrl/',
            '../../package/extra/');

        $auxPath = substr_count(__PATH__, '.') > 0 ? __PATH__ : '';
        for($i = 0, $tamI = count($paths); $i < $tamI; $i++){
            if(  file_exists( $auxPath . $paths[$i] . $class . '.php' ) ){
                require_once( $auxPath . $paths[$i] . $class . '.php');
            }
        }
    });