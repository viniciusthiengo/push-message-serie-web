<?php

	class Database {
        private $pdo;
		
		
		public function __construct(){}
		public function __destruct(){
            $this->pdo = null;
        }
		
		
		public function getConn(){
            if( is_object($this->pdo) ){
                return($this->pdo);
            }
            try{
                require('../../conf/conf.php');
                $this->pdo = new PDO( sprintf('%s:host=%s;dbname=%s;port=%s;charset=%s',
                    $settingsDatabase['type'],
                    $settingsDatabase['host'],
                    $settingsDatabase['name'],
                    $settingsDatabase['port'],
                    $settingsDatabase['charset']),
                    $settingsDatabase['username'],
                    $settingsDatabase['password'] );
            }
            catch(PDOException $e){
                exit(false);
            }
            return($this->pdo);
		}
		public function setConn($pdo){
			$this->pdo = $pdo;
		}
		
		
		public function removeBreakLine($query){
			$query = str_replace("\n", " ", $query);
			$query = str_replace("\r", "", $query);
			$query = str_replace("\t", "", $query);
			$query = trim($query);
			return($query);
		}
		
		
		public function fileQuery($query, $type=null){
            $type = is_null($type) ? 'w' : $type;
			$file = fopen('ARQUIVO.txt', $type);
			fwrite($file, $query);
			fclose($file);
		}
	}
