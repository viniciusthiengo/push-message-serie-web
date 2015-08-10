<?php
    session_start();
    require_once('../../conf/conf.php');
    require_once('../../autoload.php');
    require_once('../../vendor/autoload.php');



    if( $_SERVER['REQUEST_METHOD'] === 'POST' ){
        $jsonObject = json_decode($_POST['jsonObject']);


        if( strcasecmp($jsonObject->method, User::METHOD_SAVE) == 0 ){

            $user = new User( 0, $jsonObject->user->registrationId );
            $result = AplUser::saveUser($user);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode( array('result'=>$result) );
        }

    }


    else if( strcasecmp($_GET['method'], PushMessage::METHOD_PUSH_MESSAGE) == 0 ){

        $user = isset($_GET['registration_id']) ? new User(0, $_GET['registration_id']) : null;

        $pushMessage = new PushMessage('Title', 'Message area.');

        AplUser::sendPushMessage( $pushMessage, $user );
    }
