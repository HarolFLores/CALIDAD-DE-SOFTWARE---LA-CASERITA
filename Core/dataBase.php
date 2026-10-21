<?php
require_once __DIR__ . '/Env.php';
Env::load();

class DataBase {
    private static $conexion = null;

    private $driver   = null;
    private $host     = null;
    private $port     = null;
    private $dbname   = null;
    private $user     = null;
    private $password = null;

    public function __construct() {
        // Soporte por defecto para MySQL en XAMPP local
        $this->driver = getenv('DB_DRIVER') ?: 'mysql';
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->port = getenv('DB_PORT') ?: '3306'; 
        $this->dbname = getenv('DB_NAME') ?: 'lacaserita_db';
        $this->user = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    }

    public function getConnection() {
        if (self::$conexion === null) {
            try {
                if ($this->driver === 'pgsql') {
                    $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname};sslmode=require";
                } else {
                    $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4";
                }

                $opciones = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 8
                ];

                self::$conexion = new PDO($dsn, $this->user, $this->password, $opciones);
            } catch (PDOException $e) {
                error_log('DB connection error: ' . $e->getMessage());
                if (PHP_SAPI !== 'cli') {
                    http_response_code(500);
                }
                die('No se pudo conectar a la base de datos.');
            }
        }
        return self::$conexion;
    }
}
