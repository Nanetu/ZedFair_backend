<?php

require_once __DIR__ . '/../config/database.php';

class Database{
    private $host = DB_HOST;
    private $user = DB_USER;
    private $password = DB_PASS;
    private $port = DB_PORT;
    private $name = DB_NAME;

    private $dbh;
    private $stmt;
    private $error;

    public function __construct() {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->name . ';port=' . $this->port;

        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->password, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            echo $this->error;
        }
    }

    public function query($sql){
        $this->stmt = $this->dbh->prepare($sql);
    }

    public function execute(){
        return $this->stmt->execute();
    }

    public function results(){
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function result() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function bind($param, $value){
        $this->stmt->bindValue($param, $value);
    }
}

?>