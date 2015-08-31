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
        public $notificationConf;
        public $regTime;
        public $messages;


        public function __construct( $id=0,
                                     $registrationId=null,
                                     $nickname=null,
                                     $numberNewMessages=0,
                                     $isTyping=false,
                                     NotificationConf $notificationConf = null,
                                     $regTime=0 )
        {
            $this->id = $id;
            $this->registrationId = $registrationId;
            $this->nickname = $nickname;
            $this->numberNewMessages = $numberNewMessages;
            $this->isTyping = $isTyping;
            $this->notificationConf = $notificationConf;
            $this->regTime = empty($regTime) ? time() : $regTime;
        }


        public function getMessages(){
            $array = [];

            for( $i = 0, $tamI = min( count($this->messages), 20 ); $i < $tamI; $i++ ){
                $array[] = $this->messages[$i];
            }
            return($array);
        }

    }