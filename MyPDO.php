<?php
class MyPDO extends PDO {


    private static $instance = null;

    public function __construct() {

        try {

            parent::__construct("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
            self::$instance = $this;
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->exec("set names utf8");
        } catch (PDOException $ex) {

            $error = new MyError();
            $error->display("System Error Code : " , MyError::$ERROR_MYPDO_SQL);
        }
    }

    private function __clone() {

    }

    public static function getInstance() {

        if (self::$instance == null) {
            //creat a new object


            try {


                self::$instance = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->exec("set names utf8");
            } catch (PDOException $ex) {

                $error = new MyError();
                $error->display("System Error Code : " . __LINE__, MyError::$ERROR_MYPDO_SQL);
            }
        }


        return self::$instance;
    }

    public static function getRowCount($stmt){
        return $stmt->rowCount();
    }

    public static function getLastID($conn){


        return $conn->lastInsertId();
    }

}