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
        const METHOD_LOAD_OLD_MESSAGES = 'load-old-messages';
        const METHOD_UPDATE_MESSAGES = 'update-messages-read';
        const METHOD_REMOVE = 'remove-message';

        const LIMIT = 10;

        public $id;
        public $userFrom;
        public $userTo;
        public $message;
        public $wasRead;
        public $regTime;
        public $ackId;


        public function __construct( $id=0,
                                     $userFrom=null,
                                     $userTo=null,
                                     $message=0,
                                     $wasRead=0,
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