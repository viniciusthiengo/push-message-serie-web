<?php
/**
 * Created by PhpStorm.
 * User: viniciusthiengo
 * Date: 8/9/15
 * Time: 10:17 PM
 */

    class Message {

        const METHOD_SAVE = 'save-message';
        const METHOD_GET_MESSAGES = 'get-messages';

        const LIMIT = 10;

        public $id;
        public $userFrom;
        public $userTo;
        public $message;
        public $wasRead;
        public $regTime;


        public function __construct( $id=0,
                                     $userFrom=null,
                                     $userTo=null,
                                     $message=0,
                                     $wasRead=false,
                                     $regTime=0 )
        {
            $this->id = $id;
            $this->userFrom = $userFrom;
            $this->userTo = $userTo;
            $this->message = $message;
            $this->wasRead = $wasRead;
            $this->regTime = empty($regTime) ? time() : $regTime;
        }
    }