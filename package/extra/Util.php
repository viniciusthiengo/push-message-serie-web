<?php
/**
 * Created by PhpStorm.
 * User: viniciusthiengo
 * Date: 8/23/15
 * Time: 10:17 PM
 */

class Util {
    public static function generateFile($content, $mode='w', $filename='data.txt'){
        $handle = fopen( '../../'.$filename, $mode );
        fwrite($handle, $content."\n");
        fclose($handle);
    }
}