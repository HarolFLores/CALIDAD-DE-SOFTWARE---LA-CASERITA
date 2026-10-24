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
        // Leemos primero de Env::get, luego $_ENV, luego getenv y finalmente fallback
        $this->driver   = self::obtenerVar('DB_DRIVER', 'mysql');
        $this->host     = self::obtenerVar('DB_HOST', 'db.fr-roub1.bengt.wasmernet.com');
        $this->port     = self::obtenerVar('DB_PORT', '20184');
        $this->dbname   = self::obtenerVar('DB_NAME', 'laCaserita');
        $this->user     = self::obtenerVar('DB_USER', 'user_38af82ca');
        $this->password = self::obtenerVar('DB_PASS', 'pw_6EnptySM6AkgDnT6Z1FmRgJqAaOuo2i4');
    }

    private static function obtenerVar($clave, $defecto = '') {
        if (class_exists('Env') && method_exists('Env', 'get')) {
            $val = Env::get($clave);
            if ($val !== null && $val !== '') {
                return $val;
            }
        }
        if (isset($_ENV[$clave]) && $_ENV[$clave] !== '') {
            return $_ENV[$clave];
        }
        if (isset($_SERVER[$clave]) && $_SERVER[$clave] !== '') {
            return $_SERVER[$clave];
        }
        $valEnv = getenv($clave);
        if ($valEnv !== false && $valEnv !== '') {
            return $valEnv;
        }
        return $defecto;
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
                    PDO::ATTR_TIMEOUT            => 10
                ];

                self::$conexion = new PDO($dsn, $this->user, $this->password, $opciones);
            } catch (PDOException $e) {
                error_log('DB connection error: ' . $e->getMessage());
                if (PHP_SAPI !== 'cli') {
                    http_response_code(500);
                }
                // Muestra el motivo exacto para diagnosticar en pantalla
                die('Error de conexión a la base de datos: ' . $e->getMessage());
            }
        }
        return self::$conexion;
    }
}
