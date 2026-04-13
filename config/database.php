<?php

class Database {
    private $host = "127.0.0.1";
    private $port = "3307";
    private $db_name = "parkingsure";
    private $username = "root";
    private $password = "";
    public $conn;

    public function conectar(){

        $this->conn = null;

        try{
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}";

            $this->conn = new PDO($dsn,$this->username,$this->password);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }catch(PDOException $e){

            die("Error de conexión: " . $e->getMessage());
        }
        return $this->conn;
    }

}