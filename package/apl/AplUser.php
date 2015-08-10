<?php
    /*@include_once('conf/conf.php');
    @include_once('../../conf/conf.php');
    require_once(__PATH__.'/autoload.php');*/

    use Endroid\Gcm\Client;

    class AplUser {
        public function __construct(){}
        public function __destruct(){}


        static public function saveUser( $user )
        {
            return( CgdUser::saveUser( $user ) );
        }


        static public function getUsers( $user=null )
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

                $options = [
                    'collapse_key'=>'myTestPushMessage',
                    'delay_while_idle'=>false,
                    'time_to_live'=>(4 * 7 * 24 * 60 * 60),
                    'restricted_package_name'=>'br.com.thiengo.gcmexample',
                    'dry_run'=>false
                ];

                $client->send($data, $registrationIds, $options); // ENVIA A PUSH MESSAGE
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
    }