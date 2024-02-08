<?php

/**
 * CREATE DATABASE <database_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
 */
class DB{
	private static $db = null;
	private static $q = null;
  private static $STRICT_ERROR_EXCEPTION = false;

	public static function get($config){
    $host = $config['host'];
		$user = $config['user'];
		$pass = $config['pass'];
    $dbname = $config['dbname'];

    if(($dbname === '' || $dbname === null) && self::$db === null){
      echo "[!] No database given.";
      exit();
    }

		$dsn = "mysql:host=$host;charset=utf8;dbname=$dbname";

		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => true,
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
		];

		if(self::$db === null){
			try{
				self::$db = new PDO($dsn, $user, $pass, $options);
			}
			catch (\PDOException $e){

        if(ENV !== 'prodcution'){
          echo "[!] Could not connect to database.";
          exit();
        }

				throw new \PDOException($e->getMessage(), (int)$e->getCode());
			}
		}

		return self::$db;
	}

  public static function setStrictExceptionMode(){
    self::$STRICT_ERROR_EXCEPTION = true;
  }

  public static function beginTransaction(){

    self::$db->beginTransaction();

  }

  public static function commit(){

    self::$db->commit();

  }

  public static function rollBack(){

    self::$db->rollBack();

  }

  public static function lastInsertId(){
    return self::$db->lastInsertId();
  }

	public static function execute($query, $parameters=[]){

    if(self::$db === null){
      self::get();
    }

		self::$q = $query;

    try{
    	self::$q = self::$db->prepare($query);

      if(!self::$q->execute($parameters)){
        var_dump(self::$q);
        var_dump(self::$q->errorCode());
        var_dump(self::$q->errorInfo());
        exit("Error");
      }
    }
    catch(Exception $e){

      return self::dbError($e, self::$q);

    }

    return [
      'success'=>'true',
      // 'query'=>self::$q
    ];
	}

  public static function fetchAll(){
    return self::$q->fetchAll();
  }

  public static function rowCount(){
    return self::$q->rowCount();
  }

  public static function reset(){
    self::$q = null;
  }

  public static function dbError(Exception $e, $q){
    $error = [
      'error'=>[
        'error' => 'Something went wrong.',
        'errorCode'=>$q->errorCode(),
        'errorInfo'=>$q->errorInfo(),
      ]
    ];

    if(self::$STRICT_ERROR_EXCEPTION){
      var_dump(self::$q);
      var_dump($error);
      echo '<pre>';
      print_r($error);
      echo '</pre>';
      exit();
    }

    return $error;
  }
}
