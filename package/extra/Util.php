<?php
/**
 * Created by PhpStorm.
 * User: viniciusthiengo
 * Date: 8/23/15
 * Time: 10:17 PM
 */

class Util {
    public static function generateFile($content, $mode='w'){
        $handle = fopen( '../../data.txt', $mode );
        fwrite($handle, $content);
        fclose($handle);
    }
}