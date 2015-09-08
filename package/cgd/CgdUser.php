<?php
    class CgdUser {
        public function __construct(){}


        static public function saveUser( User $user )
        {
            $query = <<<SQL
                insert into
                  tcc_user (registration_id,
                            nickname,
                            reg_time)
                  values (:registration_id,
                          :nickname,
                          :reg_time)
                  on duplicate key
                    update
                      nickname = :nickname
SQL;
            //exit( $query );
            $database = (new Database())->getConn();
            $statement = $database->prepare($query);
            $statement->bindValue(':registration_id', $user->registrationId, PDO::PARAM_STR);
            $statement->bindValue(':nickname', $user->nickname, PDO::PARAM_STR);
            $statement->bindValue(':reg_time', $user->regTime, PDO::PARAM_INT);
            $statement->execute();
            $database = null;

            return( $statement->rowCount() > 0 );
        }

        static public function updateUserNickname( User $user )
        {
            $query = <<<SQL
                update
                  tcc_user
                  set
                    nickname = :nickname
                  where
                    id = :id
                  limit 1
SQL;
            //exit( $query );
            $database = (new Database())->getConn();
            $statement = $database->prepare($query);
            $statement->bindValue(':id', $user->id, PDO::PARAM_INT);
            $statement->bindValue(':nickname', $user->nickname, PDO::PARAM_STR);
            $statement->execute();
            $database = null;

            return( $statement->rowCount() > 0 );
        }

        static public function getUserId( User $user )
        {
            $query = <<<SQL
                select
                  id
                  from
                    tcc_user
                  where
                    registration_id like :registration_id
                  limit 1
SQL;
            //exit( $query );
            $database = (new Database())->getConn();
            $statement = $database->prepare($query);
            $statement->bindValue(':registration_id', $user->registrationId, PDO::PARAM_STR);
            $statement->execute();
            $database = null;

            return( $statement->fetchColumn(0) );
        }

        static public function getUsers( User $user = null )
        {
            $data = [];
            $data[0] = is_null($user) ? '' : 'where registration_id like :registration_id';
            $query = <<<SQL
                select
                  id,
                  registration_id,
                  reg_time
                  from
                    tcc_user
                    {$data[0]}
SQL;
            //exit( $query );
            $database = (new Database())->getConn();
            $statement = $database->prepare($query);
            if( is_object($user) ){
                $statement->bindValue(':registration_id', $user->registrationId, PDO::PARAM_INT);
            }
            $statement->execute();
            $database = null;

            $userArray = [];
            while( ($data = $statement->fetchObject()) !== false ){
                $userArray[] = new User( $data->id,
                                        $data->registration_id,
                                        $data->reg_time );
            }

            return( $userArray );
        }

        static public function getUsersToChat( User $user )
        {
            $query = <<<SQL
                select distinct
                  tu.id,
                  tu.nickname,
                  tuli.reg_time
                  from
                    tcc_user tu
                    left join
                    tcc_user_last_interaction tuli
                      on( tu.id = tuli.id_user_from )
                  where
                    tu.id != :id
                  order by
                    tuli.reg_time desc,
                    tu.id desc
SQL;
            //exit( $query );
            $database = (new Database())->getConn();
            $statement = $database->prepare($query);
            $statement->bindValue(':id', $user->id, PDO::PARAM_INT);
            $statement->execute();
            $database = null;

            $userArray = [];
            while( ($data = $statement->fetchObject()) !== false ){
                $aux = new User();
                $aux->id = $data->id;
                $aux->nickname = $data->nickname;

                $userArray[] = $aux;
            }

            return( $userArray );
        }

        static public function getUser( $user )
        {
            $query = <<<SQL
                select
                  id,
                  nickname,
                  registration_id
                  from
                    tcc_user
                  where
                    id = :id
                  limit 1
SQL;
            //exit( $query );
            $database = (new Database())->getConn();
            $statement = $database->prepare($query);
            $statement->bindValue(':id', $user->id, PDO::PARAM_INT);
            $statement->execute();
            $database = null;

            $aux = null;
            if( ($data = $statement->fetchObject()) !== false ){
                $aux = new User();
                $aux->id = $data->id;
                $aux->nickname = $data->nickname;
                $aux->registrationId = $data->registration_id;
            }

            return( $aux );
        }

        static public function updateRegistrationId( User $user )
        {
            $query = <<<SQL
                update
                  tcc_user
                  set
                    registration_id = :registration_id
                  where
                    id = :id
                  limit 1
SQL;
            //exit( $query );
            $database = (new Database())->getConn();
            $statement = $database->prepare($query);
            $statement->bindValue(':id', $user->id, PDO::PARAM_INT);
            $statement->bindValue(':registration_id', $user->registrationId, PDO::PARAM_INT);
            $statement->execute();
            $database = null;

            return( $statement->rowCount() > 0 );
        }

        static public function deleteUser( User $user )
        {
            $query = <<<SQL
                delete from
                  tcc_user
                  where
                    id = :id
                  limit 1
SQL;
            //exit( $query );
            $database = (new Database())->getConn();
            $statement = $database->prepare($query);
            $statement->bindValue(':id', $user->id, PDO::PARAM_INT);
            $statement->execute();
            $database = null;

            return( $statement->rowCount() > 0 );
        }

        static public function updateLastInteraction( User $userFrom, User $userTo, $regTime ){
            $query = <<<SQL
                insert into
                  tcc_user_last_interaction (id_user_from,
                                            id_user_to,
                                            reg_time)
                  values (:id_user_from,
                          :id_user_to,
                          :reg_time)
                  on duplicate key
                    update
                      reg_time = :reg_time;
SQL;
            //exit( $query );
            $database = (new Database())->getConn();
            $statement = $database->prepare($query);
            $statement->bindValue(':id_user_from', $userFrom->id, PDO::PARAM_INT);
            $statement->bindValue(':id_user_to', $userTo->id, PDO::PARAM_INT);
            $statement->bindValue(':reg_time', $regTime, PDO::PARAM_INT);
            $statement->execute();
            $database = null;

            return( $statement->rowCount() > 0 );
        }

        static public function updateNotificationConf( User $userFrom, User $userTo ){
            $query = <<<SQL
                insert into
                  tcc_user_last_interaction (id_user_from,
                                            id_user_to,
                                            notification_status,
                                            notification_time)
                  values (:id_user_from,
                          :id_user_to,
                          :notification_status,
                          :notification_time)
                  on duplicate key
                    update
                      notification_status = :notification_status,
                      notification_time = :notification_time
SQL;
            $database = (new Database())->getConn();
            $statement = $database->prepare($query);
            $statement->bindValue(':id_user_from', $userFrom->id, PDO::PARAM_INT);
            $statement->bindValue(':id_user_to', $userTo->id, PDO::PARAM_INT);
            $statement->bindValue(':notification_status', $userTo->notificationConf->status, PDO::PARAM_INT);
            $statement->bindValue(':notification_time', $userTo->notificationConf->time, PDO::PARAM_INT);
            $statement->execute();
            $database = null;

            return( $statement->rowCount() > 0 );
        }

        static public function getNotificationConf( User $userFrom, User $userTo ){
            $query = <<<SQL
                select
                  notification_status,
                  notification_time
                  from
                    tcc_user_last_interaction
                  where
                    id_user_from = :id_user_from
                    and
                    id_user_to = :id_user_to
                  limit 1
SQL;
            $database = (new Database())->getConn();
            $statement = $database->prepare($query);
            $statement->bindValue(':id_user_from', $userFrom->id, PDO::PARAM_INT);
            $statement->bindValue(':id_user_to', $userTo->id, PDO::PARAM_INT);
            $statement->execute();
            $database = null;

            $aux = new NotificationConf();
            if( ($data = $statement->fetchObject()) !== false ){
                $aux->status = $data->notification_status;
                $aux->time = $data->notification_time;
            }

            return( $aux );
        }


        // MESSAGE
            static public function saveMessage( Message $message )
            {
                $query = <<<SQL
                    insert into
                      tcc_message(id,
                                  id_user_from,
                                  id_user_to,
                                  message,
                                  id_ack,
                                  reg_time)
                      values (:id,
                              :id_user_from,
                              :id_user_to,
                              :message,
                              :id_ack,
                              :reg_time)
SQL;
                //exit( $query );
                $database = (new Database())->getConn();
                $statement = $database->prepare($query);
                $statement->bindValue(':id', $message->id, PDO::PARAM_INT);
                $statement->bindValue(':id_user_from', $message->userFrom->id, PDO::PARAM_INT);
                $statement->bindValue(':id_user_to', $message->userTo->id, PDO::PARAM_INT);
                $statement->bindValue(':message', $message->message, PDO::PARAM_STR);
                $statement->bindValue(':id_ack', $message->ackId, PDO::PARAM_STR);
                $statement->bindValue(':reg_time', $message->regTime, PDO::PARAM_INT);
                $statement->execute();
                $database = null;

                return( $statement->rowCount() > 0 );
            }

            static public function updateMessageWasRead( $message )
            {
                $query = <<<SQL
                        update
                          tcc_message
                          set
                            was_read = :was_read
                          where
                            id = :id
                            and
                            id_user_to = :id_user_to
                            and
                            id_user_from = :id_user_from
                          limit 1
SQL;
                //exit( $query );
                //Util::generateFile($message->id.' - '.$message->wasRead.' - '.$message->userTo->id.' - '.$message->ususerFromerTo->id, 'a');
                $database = (new Database())->getConn();
                $statement = $database->prepare($query);
                $statement->bindValue(':id', $message->id, PDO::PARAM_INT);
                $statement->bindValue(':was_read', $message->wasRead, PDO::PARAM_INT);
                $statement->bindValue(':id_user_to', $message->userTo->id, PDO::PARAM_INT);
                $statement->bindValue(':id_user_from', $message->userFrom->id, PDO::PARAM_INT);
                $statement->execute();
                $database = null;

                return( $statement->rowCount() > 0 );
            }

            static public function getNumberNewMessages( User $userFrom, User $userTo )
            {
                $query = <<<SQL
                        select
                          count(*) num
                          from
                          tcc_message
                          where
                            id_user_from = :id_user_from
                            and
                            id_user_to = :id_user_to
                            and
                            was_read = 0
SQL;
                //exit( $query );
                $database = (new Database())->getConn();
                $statement = $database->prepare($query);
                $statement->bindValue(':id_user_from', $userFrom->id, PDO::PARAM_INT);
                $statement->bindValue(':id_user_to', $userTo->id, PDO::PARAM_STR);
                $statement->execute();
                $database = null;

                return( $statement->fetchColumn(0) );
            }

            static public function getNewMessagesSummary( $user )
            {
                $query = <<<SQL
                                select
                                  tm.id,
                                  tm.message,
                                  tm.id_user_from,
                                  tu.nickname
                                  from
                                    tcc_message tm
                                    inner join
                                    tcc_user tu
                                      on( tm.id_user_from = tu.id )
                                  where
                                    tm.id_user_to = :id_user_to
                                    and
                                    tm.was_read = 0
                                  order by
                                    tm.reg_time desc
SQL;
                //exit( $query );
                $database = (new Database())->getConn();
                $statement = $database->prepare($query);
                $statement->bindValue(':id_user_to', $user->id, PDO::PARAM_INT);
                $statement->execute();
                $database = null;

                $messageArray = [];
                while( ( $data = $statement->fetchObject() ) !== false ){
                    $aux = new Message();
                    $aux->id = $data->id;
                    $aux->message = substr( $data->message, 0, 50);
                    $aux->userFrom = new User( $data->id_user_from );
                    $aux->userFrom->nickname = $data->nickname;

                    $messageArray[] = $aux;
                }

                return( $messageArray );
            }

            static public function getMessages( Message $message )
            {
                $data = [];
                $data[0] = empty( $message->id ) ? '' : 'id < :id and';
                $query = <<<SQL
                            select
                              id,
                              id_ack,
                              message,
                              reg_time,
                              was_read,
                              id_user_from,
                              id_user_to
                              from
                                tcc_message
                              where
                                {$data[0]}
                                (
                                    (
                                        id_user_from = :id_user_from
                                        and
                                        id_user_to = :id_user_to
                                    )
                                    or
                                    (
                                        id_user_from = :id_user_to
                                        and
                                        id_user_to = :id_user_from
                                    )
                                )
                              order by
                                reg_time desc
                              limit :limit
SQL;
                //exit( $query );
                $database = (new Database())->getConn();
                $statement = $database->prepare($query);
                if( !empty( $message->id ) ){
                    $statement->bindValue(':id', $message->id, PDO::PARAM_INT);
                }
                $statement->bindValue(':id_user_from', $message->userFrom->id, PDO::PARAM_INT);
                $statement->bindValue(':id_user_to', $message->userTo->id, PDO::PARAM_INT);
                $statement->bindValue(':limit', Message::LIMIT, PDO::PARAM_INT);
                $statement->execute();
                $database = null;

                $messageArray = [];
                while( ( $data = $statement->fetchObject() ) !== false ){
                    $aux = new Message();
                    $aux->id = $data->id;
                    $aux->ackId = $data->id_ack;
                    $aux->message = $data->message;
                    $aux->regTime = $data->reg_time;
                    $aux->wasRead = $data->was_read;
                    $aux->userFrom = new User( $data->id_user_from );
                    $aux->userTo = new User( $data->id_user_to );

                    $messageArray[] = $aux;
                }

                return( $messageArray );
            }

            static public function getMessageId( Message $message )
            {
                $query = <<<SQL
                                select
                                  id
                                  from
                                  tcc_message
                                  where
                                    id_user_from = :id_user_from
                                    and
                                    id_user_to = :id_user_to
                                    and
                                    reg_time = :reg_time
                                  limit 1
SQL;
                //exit( $query );
                $database = (new Database())->getConn();
                $statement = $database->prepare($query);
                $statement->bindValue(':id_user_from', $message->userFrom->id, PDO::PARAM_INT);
                $statement->bindValue(':id_user_to', $message->userTo->id, PDO::PARAM_STR);
                $statement->bindValue(':reg_time', $message->regTime, PDO::PARAM_INT);
                $statement->execute();
                $database = null;

                return( $statement->fetchColumn(0) );
            }

            static public function removeMessage( $message ){
                $query = <<<SQL
                    delete from
                      tcc_message
                      where
                        id = :id
                        and
                        id_user_from = :id_user_from
                        and
                        id_user_to = :id_user_to
                      limit 1
SQL;
                //exit( $query );
                $database = (new Database())->getConn();
                $statement = $database->prepare($query);
                $statement->bindValue(':id', $message->id, PDO::PARAM_INT);
                $statement->bindValue(':id_user_from', $message->userFrom->id, PDO::PARAM_INT);
                $statement->bindValue(':id_user_to', $message->userTo->id, PDO::PARAM_INT);
                $statement->execute();
                $database = null;

                return( $statement->rowCount() > 0 );
            }

            static public function getMessageUser( $message, $isUserFrom=true )
            {
                $data = [];
                $data[] = $isUserFrom ? 'id_user_from' : 'id_user_to';
                $query = <<<SQL
                    select
                      {$data[0]}
                      from
                        tcc_message
                      where
                        id = :id
                      limit 1
SQL;
                //exit( $query );
                $database = (new Database())->getConn();
                $statement = $database->prepare($query);
                $statement->bindValue(':id', $message->id, PDO::PARAM_INT);
                $statement->execute();
                $database = null;

                $user = null;
                if( $statement->rowCount() > 0 ){
                    $data = $statement->fetchColumn(0);
                    $user = new User( $data );
                }
                return( $user );
            }
    }