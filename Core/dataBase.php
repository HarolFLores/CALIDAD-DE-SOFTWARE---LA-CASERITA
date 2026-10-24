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
        $this->driver = getenv('DB_DRIVER') ?: 'mysql';
        $this->host = getenv('DB_HOST') ?: 'db.fr-roub1.bengt.wasmernet.com';
        $this->port = getenv('DB_PORT') ?: '20184'; 
        $this->dbname = getenv('DB_NAME') ?: 'laCaserita';
        $this->user = getenv('DB_USER') ?: 'user_38af82ca';
        $this->password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'pw_6EnptySM6AkgDnT6Z1FmRgJqAaOuo2i4';
    }

    public function getConnection() {
        if (self::$conexion === null) {
            try {
                if ($this->driver === 'pgsql') {
                    $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4";
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
