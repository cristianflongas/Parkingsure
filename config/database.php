<?php

date_default_timezone_set('America/Bogota');

class Database {
    
    
    private $host = "127.0.0.1";      // Servidor de base de datos (localhost)
    private $port = "3307";           // Puerto MySQL (3307 en lugar del 3306 estándar)
    private $db_name = "parkingsure"; // Nombre de la base de datos
    private $username = "root";       // Usuario de MySQL
    private $password = "";           // Contraseña (vacía para desarrollo local)
    
    public function getHost() {
        return $this->host;
    }
    
    public function getPort() {
        return $this->port;
    }
    
    public function getDbName() {
        return $this->db_name;
    }

    public $conn;

    // =================================================================
    // MÉTODO: conectar() - Establece conexión con la base de datos
    // =================================================================
    public function conectar(){

        // Línea 12: Inicializa la conexión como nula
        $this->conn = null;

        // Línea 15: Inicia bloque try-catch para manejar errores de conexión
        try{
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Sincronizar zona horaria MySQL con PHP (America/Bogota = UTC-5)
            $this->conn->exec("SET time_zone = '-05:00'");
        }catch(PDOException $e){
            // Línea 23: Captura cualquier excepción de PDO y muestra mensaje de error
            die("Error de conexión: " . $e->getMessage());
        }
        
        // Línea 26: Retorna el objeto de conexión PDO
        return $this->conn;
    }
}

// Función helper global para obtener conexión PDO de forma directa
function getDB() {
    $db = new Database();
    return $db->conectar();
}