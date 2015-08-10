<?php
/**
 * Created by PhpStorm.
 * User: viniciusthiengo
 * Date: 8/9/15
 * Time: 10:57 PM
 */

    class PushMessage {

        const METHOD_PUSH_MESSAGE = 'push-message-user';

        public $title;
        public $message;
        public $regTime;


        public function __construct( $title=null, $message=null, $regTime=0 )
        {
            $this->title = $title;
            $this->message = $message;
            $this->regTime = empty($regTime) ? time() : $regTime;
        }
    }