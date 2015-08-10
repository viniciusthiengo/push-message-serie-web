<?php
/**
 * Created by PhpStorm.
 * User: viniciusthiengo
 * Date: 8/9/15
 * Time: 10:17 PM
 */

    class User {

        const METHOD_SAVE = 'save-user';

        public $id;
        public $registrationId;
        public $regTime;


        public function __construct( $id=0, $registrationId=null, $regTime=0 )
        {
            $this->id = $id;
            $this->registrationId = $registrationId;
            $this->regTime = empty($regTime) ? time() : $regTime;
        }
    }