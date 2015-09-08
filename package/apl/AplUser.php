<?php
    /*@include_once('conf/conf.php');
    @include_once('../../conf/conf.php');
    require_once(__PATH__.'/autoload.php');*/

use Endroid\Gcm\Client;

//use Gcm\Xmpp\Daemon;
//use Gcm\Message;

class AplUser {
    public function __construct(){}
    public function __destruct(){}


    static public function saveUser( User $user )
    {
        $result = CgdUser::saveUser( $user );

        // IF USER WAS REGISTERED CORRECTLY, SO WE TAKE HIS DATABASE ID
        if( $result ){
            $user->id = CgdUser::getUserId( $user );
        }

        return( $result );
    }

    static public function updateUserNickname( User $user )
    {
        $result = CgdUser::updateUserNickname( $user );
        return( $result );
    }

    static public function getUsers( User $user=null )
    {
        return( CgdUser::getUsers( $user ) );
    }

    static public function sendPushMessage( PushMessage $pushMessage, User $user=null )
        {
            $userArray = AplUser::getUsers( $user );

            // GCM SENDER
                $client = new Client(__API_KEY__);

                // REGISTRATION IDS IN ARRAY
                    $registrationIds = [];
                    foreach( $userArray as $userItem  ){
                        $registrationIds[] = $userItem->registrationId;
                    }

                $data = array(
                    'title' => $pushMessage->title,
                    'message' => $pushMessage->message,
                );


                $notification = array(
                    'title'=>'Nova mensagem',
                    'body'=>'Essa é uma mensagem de tests, Essa é uma mensagem de tests, Essa é uma mensagem de tests',
                    'large_icon'=>'http://www.thiengo.com.br/img/system/logo/logo-thiengo-calopsita-70x70.png',
                    'big_picture'=>'http://s3.amazonaws.com/rapgenius/1372211201_Huvafen-Fushi-Maldives-Deluxe-mare.jpg'
                );

                $options = [
                    'collapse_key'=>'myTestPushMessage',
                    'delay_while_idle'=>false,
                    'time_to_live'=>(4 * 7 * 24 * 60 * 60),
                    'restricted_package_name'=>'br.com.thiengo.gcmexample',
                    'dry_run'=>false
                ];

                $client->send( $notification, $registrationIds, $options ); // ENVIA A PUSH MESSAGE
                $responses = $client->getResponses();
                var_dump($responses);

                // ACESSA A ÚNICA POSIÇÃO POSSÍVEL, PRIMEIRA POSIÇÃO
                foreach( $responses as $response ){
                    $response = json_decode( $response->getContent() );

                    // VERIFICA SE HÁ ALGUM CANONICAL_ID, QUE INDICA QUE AO MENOS UM REGISTRATION_ID DEVE SER ATUALIZADO
                    if( $response->canonical_ids > 0 || $response->failure > 0 ){

                        // PERCORRE TODOS OS RESULTADOS VERIFICANDO SE HÁ UM REGISTRATION_ID PARA SER ALTERADO
                        for( $i = 0, $tamI = count( $response->results ); $i < $tamI; $i++ ){

                            if( !empty( $response->results[$i]->canonical_id ) ){

                                // SE HÁ UM NOVO REGISTRATION_ID, ENTÃO ALTERANO BD
                                $userArray[$i]->registrationId = $response->results[$i]->canonical_id;
                                CgdUser::updateRegistrationId( $userArray[$i] );
                            }
                            else if( strcasecmp( $response->results[$i]->error, "NotRegistered" ) == 0 ){

                                // DELETE REGISTRO DO BD
                                CgdUser::deleteUser( $userArray[$i] );
                            }
                        }
                    }
                }
        }

    static public function getUsersToChat( User $user )
    {
        $userArray = CgdUser::getUsersToChat( $user );

        for( $i = 0, $tamI = count($userArray); $i < $tamI; $i++ ){
            $userArray[ $i ]->numberNewMessages = CgdUser::getNumberNewMessages( $userArray[ $i ], $user );
            $userArray[ $i ]->notificationConf = CgdUser::getNotificationConf( $userArray[ $i ], $user );
        }

        return( $userArray );
    }

    static public function updateNotificationConf( User $userFrom, User $userTo ){
        return( CgdUser::updateNotificationConf( $userFrom, $userTo ) );
    }


    // MESSAGE
        static public function saveMessage( Message $message, $daemon=null )
        {
            $result = CgdUser::saveMessage( $message );

            // SAVE LAST INTERACTION
            if( $result ){
                CgdUser::updateLastInteraction( $message->userFrom, $message->userTo, $message->regTime );
                CgdUser::updateLastInteraction( $message->userTo, $message->userFrom, $message->regTime );

                //$message->id = CgdUser::getMessageId( $message );
                //Util::generateFile("LAST ID: ".$message->id, 'a');

                $message->userTo = CgdUser::getUser($message->userTo);
                $message->userTo->notificationConf = CgdUser::getNotificationConf( $message->userFrom, $message->userTo );
                $message->userTo->messages = AplUser::getNewMessagesSummary( $message->userTo );

                $message->userFrom = CgdUser::getUser($message->userFrom);

                //AplUser::sendPushMessageNewMessage( $message, $message->userFrom, $message->userTo );

                AplUser::sendPushMessageNewMessage_XMPP( $daemon, $message, $message->userFrom, $message->userTo );
                AplUser::sendPushMessageNewMessage_XMPP( $daemon, $message, $message->userFrom, $message->userTo, true );

            }

            return( $result );
        }

        static public function sendPushMessageNewMessage_XMPP( $daemon, Message $msg, User $userFrom, User $userTo, $isUserFromMain=false )
        {
            $userToRegId = $isUserFromMain ? $userFrom->registrationId : $userTo->registrationId;
            $payload = array(
                'type' => 1,
                'id' => $msg->id,
                'title' => 'Mensagem de: '.$userFrom->nickname,
                'message' => $msg->message,
                'regTime' => $msg->regTime * 1000,
                'userFrom_id' => $userFrom->id,
                'userFrom_nickname' => $userFrom->nickname,
                'userTo_id' => $userTo->id,
                'userTo_nickname' => $userTo->nickname,
                'userTo_notification_status' => $userTo->notificationConf->status,
                'userTo_notification_time' => ($userTo->notificationConf->time * 1000),
                'userTo_new_messages' => json_encode(["messages"=>$userTo->getMessages() ]),
                'userTo_amount_new_messages' => count($userTo->messages),
            );

            Util::generateFile("SEND TO: ".$userToRegId, 'a');
            Util::generateFile("ID MSG: ".$msg->id, 'a');

            $message = new \Gcm\Message( $userToRegId, $payload, "newMessage");
            $message->setMessageId( $isUserFromMain ? '' : $msg->ackId );
            $message->setTimeToLive( 60 );
            $message->setDeliveryReceiptRequested( ! $isUserFromMain );

            $daemon->send($message);
        }

        static public function sendPushMessageNewMessage( Message $message, User $userFrom, User $userTo )
        {
            // GCM SENDER
            $client = new Client(__API_KEY__);

            // REGISTRATION IDS IN ARRAY
            $registrationIds = [];
            $registrationIds[] = $userFrom->registrationId;
            //$registrationIds[] = $userTo->registrationId;

            $data = array(
                'type' => 1,
                'id' => $message->id,
                'title' => 'Mensagem de: '.$userFrom->nickname,
                'message' => $message->message,
                'regTime' => $message->regTime * 1000,
                'userFrom_id' => $userFrom->id,
                'userFrom_nickname' => $userFrom->nickname,
                'userTo_id' => $userTo->id,
                'userTo_nickname' => $userTo->nickname,
                'userTo_notification_status' => $userTo->notificationConf->status,
                'userTo_notification_time' => ($userTo->notificationConf->time * 1000),
                'userTo_new_messages' => json_encode(["messages"=>$userTo->getMessages() ]),
                'userTo_amount_new_messages' => count($userTo->messages),
            );

            $options = [
                'collapse_key'=>'newMessage',
                'delay_while_idle'=>false,
                'time_to_live'=>(4 * 7 * 24 * 60 * 60),
                'restricted_package_name'=>'br.com.thiengo.gcmexample',
                'dry_run'=>false
            ];

            $client->send( $data, $registrationIds, $options ); // ENVIA A PUSH MESSAGE
            $responses = $client->getResponses();

            // ACESSA A ÚNICA POSIÇÃO POSSÍVEL, PRIMEIRA POSIÇÃO
            foreach( $responses as $response ){
                $response = json_decode( $response->getContent() );

                // VERIFICA SE HÁ ALGUM CANONICAL_ID, QUE INDICA QUE AO MENOS UM REGISTRATION_ID DEVE SER ATUALIZADO
                if( $response->canonical_ids > 0 || $response->failure > 0 ){

                    // PERCORRE TODOS OS RESULTADOS VERIFICANDO SE HÁ UM REGISTRATION_ID PARA SER ALTERADO
                    for( $i = 0, $tamI = count( $response->results ); $i < $tamI; $i++ ){

                        if( !empty( $response->results[$i]->canonical_id ) ){

                            // SE HÁ UM NOVO REGISTRATION_ID, ENTÃO ALTERANO BD
                            if( $i == 0 ){
                                $userFrom->registrationId = $response->results[$i]->canonical_id;
                                CgdUser::updateRegistrationId( $userFrom );
                            }
                            else{
                                $userTo->registrationId = $response->results[$i]->canonical_id;
                                CgdUser::updateRegistrationId( $userTo );
                            }

                        }
                        else if( strcasecmp( $response->results[$i]->error, "NotRegistered" ) == 0 ){

                            // DELETE REGISTRO DO BD
                            if( $i == 0 ){
                                CgdUser::deleteUser( $userFrom );
                            }
                            else{
                                CgdUser::deleteUser( $userTo );
                            }
                        }
                    }
                }
            }
        }

        static public function getNewMessagesSummary( $user ){
            $messages = CgdUser::getNewMessagesSummary( $user );
            return($messages);
        }

        static public function getMessages( Message $message )
        {
            $message->userFrom = CgdUser::getUser($message->userFrom);
            $message->userTo = CgdUser::getUser($message->userTo);

            $messageArray = CgdUser::getMessages( $message );

            for( $i = 0, $tamI = count($messageArray); $i < $tamI; $i++ ){

                if( $messageArray[$i]->userFrom->id == $message->userFrom->id ){
                    $messageArray[$i]->userFrom = $message->userFrom;
                    $messageArray[$i]->userTo = $message->userTo;
                }
                else{
                    $messageArray[$i]->userFrom = $message->userTo;
                    $messageArray[$i]->userTo = $message->userFrom;
                }
            }

            return( $messageArray );
        }

        static public function updateMessages( $messages, $daemon=null )
        {
            foreach( $messages as $value ){
                $value->wasRead = 1;
                $result = CgdUser::updateMessageWasRead( $value );

                if( $result ){
                    $value->userFrom = CgdUser::getUser($value->userFrom);

                    if( !is_null($daemon) ){
                        Util::generateFile( 'FAKE ID: '.$value->ackId, 'a' );

                        $recievedMessage = new \Gcm\RecievedMessage( '',
                            '',
                            0,
                            'dr2:'.$value->ackId,
                            $value->userFrom->registrationId,
                            '');

                        $daemon->sendAck( $recievedMessage);
                    }

                    AplUser::sendPushMessageWasRead( $value,
                        $value->userFrom,
                        $value->userTo );
                }
            }
        }

        static public function sendPushMessageWasRead( $message, $userFrom, $userTo )
        {
            // GCM SENDER
            $client = new Client(__API_KEY__);

            // REGISTRATION IDS IN ARRAY
            $registrationIds = [];
            $registrationIds[] = $userFrom->registrationId;

            $data = array(
                'type' => 2,
                'id' => $message->id,
                'userFrom_id' => $userFrom->id,
                'userTo_id' => $userTo->id
            );

            $options = [
                'collapse_key'=>'messageWasRead',
                'delay_while_idle'=>false,
                'time_to_live'=>(4 * 7 * 24 * 60 * 60),
                'restricted_package_name'=>'br.com.thiengo.gcmexample',
                'dry_run'=>false
            ];
            Util::generateFile( 'SEND: '.$userFrom->registrationId, 'a' );

            $client->send( $data, $registrationIds, $options ); // ENVIA A PUSH MESSAGE
            $responses = $client->getResponses();
            Util::generateFile( 'SENT: '.$userFrom->registrationId, 'a' );
            // ACESSA A ÚNICA POSIÇÃO POSSÍVEL, PRIMEIRA POSIÇÃO
            foreach( $responses as $response ){
                $response = json_decode( $response->getContent() );

                // VERIFICA SE HÁ ALGUM CANONICAL_ID, QUE INDICA QUE AO MENOS UM REGISTRATION_ID DEVE SER ATUALIZADO
                if( $response->canonical_ids > 0 || $response->failure > 0 ){

                    // PERCORRE TODOS OS RESULTADOS VERIFICANDO SE HÁ UM REGISTRATION_ID PARA SER ALTERADO
                    for( $i = 0, $tamI = count( $response->results ); $i < $tamI; $i++ ){

                        if( !empty( $response->results[$i]->canonical_id ) ){

                            // SE HÁ UM NOVO REGISTRATION_ID, ENTÃO ALTERANO BD
                            $userFrom->registrationId = $response->results[$i]->canonical_id;
                            CgdUser::updateRegistrationId( $userFrom );

                        }
                        else if( strcasecmp( $response->results[$i]->error, "NotRegistered" ) == 0 ){

                            // DELETE REGISTRO DO BD
                            CgdUser::deleteUser( $userFrom );
                        }
                    }
                }
            }
        }

        static public function removeMessage( $message )
        {
            $result = CgdUser::removeMessage( $message );

            if( $result ){
                $message->userFrom = CgdUser::getUser( $message->userFrom );
                $message->userTo = CgdUser::getUser( $message->userTo );

                AplUser::sendMessageRemoved( $message,
                    $message->userFrom,
                    $message->userTo );
            }

            return( $result );
        }

        static public function sendMessageRemoved( $message, $userFrom, $userTo )
        {
            // GCM SENDER
            $client = new Client(__API_KEY__);

            // REGISTRATION IDS IN ARRAY
            $registrationIds = [];
            $registrationIds[] = $userFrom->registrationId;
            $registrationIds[] = $userTo->registrationId;

            $data = array(
                'type' => 3,
                'id' => $message->id,
                'userFrom_id' => $userFrom->id,
                'userTo_id' => $userTo->id
            );

            $options = [
                'collapse_key'=>'messageRemoved',
                'delay_while_idle'=>false,
                'time_to_live'=>(4 * 7 * 24 * 60 * 60),
                'restricted_package_name'=>'br.com.thiengo.gcmexample',
                'dry_run'=>false
            ];

            $client->send( $data, $registrationIds, $options ); // ENVIA A PUSH MESSAGE
            $responses = $client->getResponses();

            // ACESSA A ÚNICA POSIÇÃO POSSÍVEL, PRIMEIRA POSIÇÃO
            foreach( $responses as $response ){
                $response = json_decode( $response->getContent() );

                // VERIFICA SE HÁ ALGUM CANONICAL_ID, QUE INDICA QUE AO MENOS UM REGISTRATION_ID DEVE SER ATUALIZADO
                if( $response->canonical_ids > 0 || $response->failure > 0 ){

                    // PERCORRE TODOS OS RESULTADOS VERIFICANDO SE HÁ UM REGISTRATION_ID PARA SER ALTERADO
                    for( $i = 0, $tamI = count( $response->results ); $i < $tamI; $i++ ){

                        if( !empty( $response->results[$i]->canonical_id ) ){

                            // SE HÁ UM NOVO REGISTRATION_ID, ENTÃO ALTERANO BD
                            if( $i == 0 ){
                                $userFrom->registrationId = $response->results[$i]->canonical_id;
                                CgdUser::updateRegistrationId( $userFrom );
                            }
                            else{
                                $userTo->registrationId = $response->results[$i]->canonical_id;
                                CgdUser::updateRegistrationId( $userTo );
                            }

                        }
                        else if( strcasecmp( $response->results[$i]->error, "NotRegistered" ) == 0 ){

                            // DELETE REGISTRO DO BD
                            if( $i == 0 ){
                                CgdUser::deleteUser( $userFrom );
                            }
                            else{
                                CgdUser::deleteUser( $userTo );
                            }
                        }
                    }
                }
            }
        }

        static public function getMessageUser( $message, $isUserFrom=true ){
            return( CgdUser::getMessageUser( $message, $isUserFrom ) );
        }
}