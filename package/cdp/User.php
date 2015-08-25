<?php
/**
 * Created by PhpStorm.
 * User: viniciusthiengo
 * Date: 8/9/15
 * Time: 10:17 PM
 */

    class User {

        const METHOD_SAVE = 'save-user';
        const METHOD_UPDATE = 'update-nickname';
        const METHOD_GET_USERS = 'get-users';

        public $id;
        public $registrationId;
        public $nickname;
        public $numberNewMessages;
        public $isTyping;
        public $regTime;


        public function __construct( $id=0,
                                     $registrationId=null,
                                     $nickname=null,
                                     $numberNewMessages=0,
                                     $isTyping=false,
                                     $regTime=0 )
        {
            $this->id = $id;
            $this->registrationId = $registrationId;
            $this->nickname = $nickname;
            $this->numberNewMessages = $numberNewMessages;
            $this->isTyping = $isTyping;
            $this->regTime = empty($regTime) ? time() : $regTime;
        }
    }