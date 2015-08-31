<?php
/**
 * Created by PhpStorm.
 * User: viniciusthiengo
 * Date: 8/30/15
 * Time: 2:34 PM
 */

class NotificationConf {

    const METHOD_UPDATE = 'update-notification-conf';


    public $status;
    public $time;


    function __construct($status=0,
                         $time=0)
    {
        $this->status = $status;
        $this->time = $time;
    }


    public function setTime( $time ){
        $this->time = (int)($time / 1000);
    }
}