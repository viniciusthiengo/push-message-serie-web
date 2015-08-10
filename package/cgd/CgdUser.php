<?php
    class CgdUser {
        public function __construct(){}


        static public function saveUser( User $user )
        {
            $query = <<<SQL
                insert into
                  tcc_user (registration_id,
                            reg_time)
                  values (:registration_id,
                          :reg_time)
SQL;
            //exit( $query );
            $database = (new Database())->getConn();
            $statement = $database->prepare($query);
            $statement->bindValue(':registration_id', $user->registrationId, PDO::PARAM_INT);
            $statement->bindValue(':reg_time', $user->regTime, PDO::PARAM_INT);
            $statement->execute();
            $database = null;

            return( $statement->rowCount() > 0 );
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
    }