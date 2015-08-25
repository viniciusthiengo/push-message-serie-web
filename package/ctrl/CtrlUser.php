<?php
    session_start();
    require_once('../../conf/conf.php');
    require_once('../../autoload.php');
    require_once('../../vendor/autoload.php');



    if( $_SERVER['REQUEST_METHOD'] === 'POST' ){
        $jsonObject = json_decode($_POST['jsonObject']);


        // REGISTER USER
        if( strcasecmp($jsonObject->method, User::METHOD_SAVE) == 0 ){

            $user = new User();
            $user->registrationId = $jsonObject->user->registrationId;
            $user->nickname = $jsonObject->user->nickname;
            $user->regTime = time();

            $result = AplUser::saveUser($user);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode( array('result'=>$result,
                                    'id'=>$user->id) );
        }


        // UPDATE NICKNAME
        else if( strcasecmp($jsonObject->method, User::METHOD_UPDATE) == 0 ){

            $user = new User();
            $user->id = $jsonObject->user->id;
            $user->nickname = $jsonObject->user->nickname;

            $result = AplUser::updateUserNickname($user);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode( array('result'=>$result,
                'id'=>$user->id) );
        }


        // GET USERS
        else if( strcasecmp($jsonObject->method, User::METHOD_GET_USERS) == 0 ){
            $user = new User();
            $user->id = $jsonObject->user->id;

            $userArray = AplUser::getUsersToChat( $user );

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode( array( 'users'=>$userArray ) );
        }


        // SAVE MESSAGE
        else if( strcasecmp($jsonObject->method, Message::METHOD_SAVE) == 0 ){

            $message = new Message();
            $message->message = $jsonObject->message->message;
            $message->regTime = time();

            $message->userFrom = new User();
            $message->userFrom->id = $jsonObject->message->userFrom->id;

            $message->userTo = new User();
            $message->userTo->id = $jsonObject->message->userTo->id;

            $result = AplUser::saveMessage( $message );

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode( array( 'result'=>$result ) );
        }


        // GET MESSAGES
        else if( strcasecmp($jsonObject->method, Message::METHOD_GET_MESSAGES) == 0 ){

            $userFrom = new User();
            $userFrom->id = $jsonObject->message->userFrom->id;

            $userTo = new User();
            $userTo->id = $jsonObject->message->userTo->id;

            $messageArray = AplUser::getMessages( $userFrom, $userTo );

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode( array( 'messages'=>$messageArray ) );
        }

    }


    else if( strcasecmp($_GET['method'], PushMessage::METHOD_PUSH_MESSAGE) == 0 ){

        $user = isset($_GET['registration_id']) ? new User(0, $_GET['registration_id']) : null;

        $pushMessage = new PushMessage('Title', 'Message area.');

        AplUser::sendPushMessage( $pushMessage, $user );
    }
