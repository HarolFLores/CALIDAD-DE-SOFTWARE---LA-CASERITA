<?php
require_once __DIR__ . '/Env.php';
Env::load();

class DataBase {
    private static $conexion = null;

    private $driver;
    private $host;
    private $port;
    private $dbname;
    private $user;
    private $password;

    public function __construct() {
        // Obtenemos variables priorizando el entorno de Wasmer, pero asegurando los valores reales
        $this->driver   = self::obtenerVar('DB_DRIVER', 'mysql');
        $this->host     = self::obtenerVar('DB_HOST', 'db.fr-roub1.bengt.wasmernet.com');
        $this->port     = self::obtenerVar('DB_PORT', '20184');
        $this->dbname   = self::obtenerVar('DB_NAME', 'laCaserita');
        $this->user     = self::obtenerVar('DB_USER', 'user_90e56536');
        $this->password = self::obtenerVar('DB_PASS', 'pw_fED4H0scKjrfk72izDXfuB9KuxthylJa');

        // Si por alguna razón llegó 'root', forzamos el usuario de producción de Wasmer
        if ($this->user === 'root' || empty($this->user)) {
            $this->user = 'user_90e56536';
            $this->password = 'pw_fED4H0scKjrfk72izDXfuB9KuxthylJa';
        }
    }

    private static function obtenerVar($clave, $defecto = '') {
        $val = null;
        if (class_exists('Env') && method_exists('Env', 'get')) {
            $val = Env::get($clave);
        }
        if (empty($val) && isset($_ENV[$clave])) {
            $val = $_ENV[$clave];
        }
        if (empty($val) && isset($_SERVER[$clave])) {
            $val = $_SERVER[$clave];
        }
        if (empty($val)) {
            $val = getenv($clave);
        }

        return (!empty($val) && $val !== false) ? $val : $defecto;
    }

    public function getConnection() {
        if (self::$conexion === null) {
            try {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4";

                $opciones = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 10
                ];

                self::$conexion = new PDO($dsn, $this->user, $this->password, $opciones);
            } catch (PDOException $e) {
                error_log('DB connection error: ' . $e->getMessage());
                if (PHP_SAPI !== 'cli') {
                    http_response_code(500);
                }
                die('Error de conexión a la base de datos: ' . $e->getMessage());
            }
        }
        return self::$conexion;
    }
}
