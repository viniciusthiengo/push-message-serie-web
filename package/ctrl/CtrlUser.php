<?php
    session_start();
    require_once('../../conf/conf.php');
    require_once('../../autoload.php');
    require_once('../../vendor/autoload.php');

use Gcm\Xmpp\Daemon;

/*if (!ini_get('display_errors')) {
    ini_set('display_errors', 1);
}*/




    if( $_SERVER['REQUEST_METHOD'] === 'POST' ){
        $jsonObject = json_decode($_POST['jsonObject']);


        // REGISTER USER
        if( strcasecmp($jsonObject->method, User::METHOD_SAVE) == 0 ){

            $user = new User();
            $user->registrationId = $jsonObject->user->registrationId;
            $user->nickname = $jsonObject->user->nickname;
            $user->regTime = time();

            // AWS SNS
                $awsSns = new AwsSns();
                $awsSns->getEndpointArn( $user->registrationId );

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


        // UPDATE NOTIFICATION CONF
        else if( strcasecmp($jsonObject->method, NotificationConf::METHOD_UPDATE) == 0 ){

            $userTo = new User();
            $userTo->id = $jsonObject->user->id;
            $userTo->notificationConf = new NotificationConf();
            $userTo->notificationConf->status = $jsonObject->userFrom->notificationConf->status;
            $userTo->notificationConf->setTime( $jsonObject->userFrom->notificationConf->time );

            $userFrom = new User();
            $userFrom->id = $jsonObject->userFrom->id;

            $result = AplUser::updateNotificationConf($userFrom, $userTo);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode( array('result'=>$result) );
        }


        // SAVE MESSAGE
        else if( strcasecmp($jsonObject->method, Message::METHOD_SAVE) == 0 ){

            $message = new Message();
            $message->message = $jsonObject->message->message;
            $message->regTime = time();
            $message->ackId = '0';

            $message->userFrom = new User();
            $message->userFrom->id = $jsonObject->message->userFrom->id;

            $message->userTo = new User();
            $message->userTo->id = $jsonObject->message->userTo->id;

            $result = AplUser::saveMessage( $message );

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode( array( 'result'=>$result ) );
        }


        // GET / LOAD MORE MESSAGES
        else if( strcasecmp($jsonObject->method, Message::METHOD_GET_MESSAGES) == 0
            || strcasecmp($jsonObject->method, Message::METHOD_LOAD_OLD_MESSAGES) == 0){

            $message = new Message(  );
            $message->id = $jsonObject->message->id;

            $message->userFrom = new User();
            $message->userFrom->id = $jsonObject->message->userFrom->id;

            $message->userTo = new User();
            $message->userTo->id = $jsonObject->message->userTo->id;

            $messageArray = AplUser::getMessages( $message );

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode( array( 'messages'=>$messageArray ) );
        }


        // UPDATED MESSAGES ALREADY READ
        else if( strcasecmp($jsonObject->method, Message::METHOD_UPDATE_MESSAGES) == 0 ){

            $messages = $jsonObject->messages;
            AplUser::updateMessages( $messages );

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode( array( 'result'=>true ) );
        }


        // REMOVE MESSAGE
        else if( strcasecmp($jsonObject->method, Message::METHOD_REMOVE) == 0 ){

            $message = $jsonObject->message;

            $result = AplUser::removeMessage( $message );

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode( array( 'result'=>$result ) );
        }
    }


    else if( strcasecmp($_GET['method'], PushMessage::METHOD_PUSH_MESSAGE) == 0 ){

        $user = isset($_GET['registration_id']) ? new User(0, $_GET['registration_id']) : null;

        $pushMessage = new PushMessage('Title', 'Message area.');

        AplUser::sendPushMessage( $pushMessage, $user );
    }

    // XMPP
    else if( strcasecmp($_GET['method'], 'DAEMON') == 0 ){

        $daemon = new Daemon(__SENDER_ID__, __API_KEY__, $testMode = false);

        $daemon->onReady[] = function(Daemon $daemon) {
            Util::generateFile( "1 - Ready / Auth success. Waiting for Messages", 'a' );
        };
        $daemon->onAuthFailure[] = function(Daemon $daemon, $reason) {
            Util::generateFile( "1 - Auth failure (reason $reason)", 'a' );
        };
        $daemon->onStop[] = function(Daemon $daemon) {
            Util::generateFile( "1 - Daemon has stopped by", 'a' );
        };
        $daemon->onDisconnect[] = function(Daemon $daemon) {
            Util::generateFile( "1 - Daemon has been disconected", 'a' );
            header("Location: http://localhost:8888/PushMessageSerie/package/ctrl/CtrlUser.php?method=DAEMON");
        };
        $daemon->onAllSent[] = function(Gcm\Xmpp\Daemon $daemon, $countMessages) {
            Util::generateFile( "1 - Has been sent all of $countMessages", 'a');
        };
        $daemon->onMessage[] = function(Daemon $daemon, \Gcm\RecievedMessage $message) {
            Util::generateFile( "ON MESSAGE", 'a' );
            Util::generateFile( "1 - Recieved message from GCM -".$message->getMessageId().' - '.$message->getMessageType().' - '.json_encode($message->getData()).' - '.$message->getFrom(), 'a' );

            if( strcasecmp( $message->getMessageType(), 'control' ) == 0 ){
                // OPEN NEW CONNECTION
            }

            // WAS READ MESSAGE ON TIME MESSAGE WAS SENT
            else if( strcasecmp( $message->getMessageType(), 'receipt' ) == 0 ){

                if( strcasecmp( $message->getData()->message_status, 'MESSAGE_SENT_TO_DEVICE' ) == 0 ){
                    Util::generateFile( "ACK SCRIPT", 'a' );
                    Util::generateFile( "ID: ".$message->getMessageId(), 'a' );

                    $id = explode('-', $message->getData()->original_message_id);
                    $m = new Message( $id[ count($id) - 1 ] );

                    $m->userFrom = AplUser::getMessageUser( $m, true );
                    $m->userTo = AplUser::getMessageUser( $m, false );

                    $m->ackId = $message->getData()->original_message_id;

                    Util::generateFile( "USER FROM: ".$m->userFrom->id, 'a' );
                    Util::generateFile( "USER TO: ".$m->userTo->id, 'a' );

                    AplUser::updateMessages( array( $m ), $daemon );
                }
            }
            else{
                $jsonObject = json_decode( $message->getData()->jsonObject );

                // WAS READ MESSAGE - LATER IN LIST
                if( strcasecmp($jsonObject->method, Message::METHOD_UPDATE_MESSAGES) == 0 ){
                    Util::generateFile( "UPDATE MESSAGES - WAS READ", 'a' );

                    Util::generateFile( "AMOUNT: ".count($jsonObject->messages), 'a' );
                    $messages = $jsonObject->messages;

                    AplUser::updateMessages( $messages, $daemon );
                }

                // NEW MESSAGE
                else{
                    Util::generateFile( "NEW MESSAGE", 'a' );
                    Util::generateFile( "ID: ".$message->getMessageId(), 'a' );

                    $message = new Message( $message->getMessageId() );
                    $message->message = $jsonObject->message->message;
                    $message->regTime = time();
                    $message->ackId = (string) microtime().'-'.$message->id;

                    $message->userFrom = new User();
                    $message->userFrom->id = $jsonObject->message->userFrom->id;
                    Util::generateFile( "USER FROM: ".$jsonObject->message->userFrom->id, 'a' );

                    $message->userTo = new User();
                    $message->userTo->id = $jsonObject->message->userTo->id;
                    Util::generateFile( "USER TO: ".$jsonObject->message->userTo->id, 'a' );

                    AplUser::saveMessage( $message, $daemon );
                }
            }
        };

        $daemon->run();
    }


    else if( strcasecmp($_GET['method'], 'AWSSNS') == 0 ){

        /*$sns = Util::getAwsSns();

        $aux = $sns->createPlatformApplication( array(
            // Name is required
            'Name' => 'PushMessageSerie',
            // Platform is required
            'Platform' => 'GCM',
            // Attributes is required
            'Attributes' => array(
                // Associative array of custom 'String' key names
                'PlatformCredential' => __API_KEY__,
                // ... repeated
            ),
        ) );

        echo $aux->get('PlatformApplicationArn').'<br>';

        $aux = $sns->createPlatformEndpoint(array(
            // PlatformApplicationArn is required
            'PlatformApplicationArn' => $aux->get('PlatformApplicationArn'),
            // Token is required
            'Token' => 'dp836ywq5ME:APA91bGhHu-imZjWrGQZoOqt-N5vthihQquLy0dQm6ENSDlNvFlk6nWnJwjPYnymF0PGmGiKNTWdIkpVo1GqEwZVMfZ4skjmYlngMo9k8iPuQVjTr4zUg7LSKUKKFmTQQJv9P9viWGqk',
            //'CustomUserData' => 'string',
            'Attributes' => array(
                // Associative array of custom 'String' key names
                'Enabled' => 'true'
            ),
        ));

        echo $aux->get('EndpointArn').'<br>';*/


        $sns = new AwsSns();
        echo $sns->getEndpointArn( 'dp836ywq5ME:APA91bGhHu-imZjWrGQZoOqt-N5vthihQquLy0dQm6ENSDlNvFlk6nWnJwjPYnymF0PGmGiKNTWdIkpVo1GqEwZVMfZ4skjmYlngMo9k8iPuQVjTr4zUg7LSKUKKFmTQQJv9P9viWGqk' );
        echo '<br><br>';

        // Get and display the platform applications
        print("List All Platform Applications:\n");
        $Model1 = $sns->listPlatformApplications();
        foreach ($Model1['PlatformApplications'] as $App)
        {
            print($App['PlatformApplicationArn'] . "\n");
        }
        print("\n");

        // Get the Arn of the first application
        $AppArn = $Model1['PlatformApplications'][0]['PlatformApplicationArn'];

        // Get the application's endpoints
        $Model2 = $sns->listEndpointsByPlatformApplication(array('PlatformApplicationArn' => $AppArn));

        // Display all of the endpoints for the first application
        print("List All Endpoints for First App:\n");
        //$Endpoint = [];
        foreach ($Model2['Endpoints'] as $Endpoint)
        {
            $EndpointArn = $Endpoint['EndpointArn'];
            print($EndpointArn . "\n");
        }
        print("\n");

        // Send a message to each endpoint
        print("Send Message to all Endpoints:\n");
        foreach ($Model2['Endpoints'] as $Endpoint)
        {
            $EndpointArn = $Endpoint['EndpointArn'];
            try
            {
                $sns->publish(array('Message' => 'Hello from PHP',
                    'TargetArn' => $EndpointArn));
                print($EndpointArn . " - Succeeded!\n");
            }
            catch (Exception $e)
            {
                print($EndpointArn . " - Failed: " . $e->getMessage() . "!\n");
            }
        }
    }
