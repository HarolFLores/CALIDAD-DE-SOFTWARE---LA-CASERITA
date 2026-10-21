<?php
require_once __DIR__ . '/../Core/dataBase.php';

class ConfiguracionModel {
    private $conn;

    public function __construct() {
        $db = new DataBase();
        $this->conn = $db->getConnection();
    }

    private function asegurarTablaOperativa() {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS configuracion_tienda (
            id_config INT(11) NOT NULL AUTO_INCREMENT,
            horario_apertura TIME NULL,
            horario_cierre TIME NULL,
            delivery_activo TINYINT(1) NOT NULL DEFAULT 1,
            delivery_costo DECIMAL(10,2) NOT NULL DEFAULT 0,
            delivery_gratis_desde DECIMAL(10,2) NOT NULL DEFAULT 0,
            impuesto_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0,
            stock_minimo_global DECIMAL(10,2) NOT NULL DEFAULT 5,
            actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id_config)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $total = (int)$this->conn->query("SELECT COUNT(*) FROM configuracion_tienda")->fetchColumn();
        if ($total === 0) {
            $this->conn->exec("INSERT INTO configuracion_tienda
                (horario_apertura, horario_cierre, delivery_activo, delivery_costo, delivery_gratis_desde, impuesto_porcentaje, stock_minimo_global)
                VALUES ('08:00:00', '21:00:00', 1, 0, 0, 0, 5)");
        }
    }

    public function obtenerTienda() {
        $stmt = $this->conn->query("SELECT * FROM configuracion_correo ORDER BY activo DESC, id_config DESC LIMIT 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$config) {
            return [
                'id_config' => 0,
                'nombre_empresa' => 'La Caserita',
                'logo_url' => 'Images/logo.png',
                'cabecera_correo_url' => '',
                'telefono_whatsapp' => '',
                'correo_soporte' => '',
                'enlace_seguimiento' => 'index.php?action=mi_cuenta',
                'direccion_fisica' => '',
                'color_primario' => '#0d5f6e',
                'color_secundario' => '#ff7f00',
                'smtp_host' => '',
                'smtp_port' => 587,
                'smtp_user' => '',
                'smtp_pass' => '',
                'smtp_seguridad' => 'tls',
                'activo' => 1
            ];
        }

        return $config;
    }

    public function obtenerOperativa() {
        $this->asegurarTablaOperativa();
        $stmt = $this->conn->query("SELECT * FROM configuracion_tienda ORDER BY id_config ASC LIMIT 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        return $config ?: [
            'id_config' => 0,
            'horario_apertura' => '08:00:00',
            'horario_cierre' => '21:00:00',
            'delivery_activo' => 1,
            'delivery_costo' => 0,
            'delivery_gratis_desde' => 0,
            'impuesto_porcentaje' => 0,
            'stock_minimo_global' => 5
        ];
    }

    public function guardarTienda(array $datos) {
        $actual = $this->obtenerTienda();
        $idConfig = (int)($actual['id_config'] ?? 0);
        $password = trim((string)($datos['smtp_pass'] ?? ''));
        if ($password === '') {
            $password = (string)($actual['smtp_pass'] ?? '');
        }

        $tomar = function ($clave, $default = '') use ($datos, $actual) {
            if (array_key_exists($clave, $datos)) {
                return trim((string)$datos[$clave]);
            }
            return (string)($actual[$clave] ?? $default);
        };
        $seguridad = $tomar('smtp_seguridad', 'tls');

        $valores = [
            'nombre_empresa' => $tomar('nombre_empresa', 'La Caserita'),
            'logo_url' => $tomar('logo_url', 'Images/logo.png'),
            'cabecera_correo_url' => $tomar('cabecera_correo_url'),
            'telefono_whatsapp' => $tomar('telefono_whatsapp'),
            'correo_soporte' => $tomar('correo_soporte'),
            'enlace_seguimiento' => $tomar('enlace_seguimiento', 'index.php?action=mi_cuenta'),
            'direccion_fisica' => $tomar('direccion_fisica'),
            'color_primario' => $tomar('color_primario', '#0d5f6e'),
            'color_secundario' => $tomar('color_secundario', '#ff7f00'),
            'smtp_host' => $tomar('smtp_host'),
            'smtp_port' => max(1, (int)$tomar('smtp_port', '587')),
            'smtp_user' => $tomar('smtp_user'),
            'smtp_pass' => $password,
            'smtp_seguridad' => in_array($seguridad, ['tls', 'ssl', 'none'], true) ? $seguridad : 'tls',
            'activo' => array_key_exists('activo', $datos) ? (!empty($datos['activo']) ? 1 : 0) : (int)($actual['activo'] ?? 0)
        ];

        if ($idConfig > 0) {
            $sql = "UPDATE configuracion_correo SET
                nombre_empresa = :nombre_empresa,
                logo_url = :logo_url,
                cabecera_correo_url = :cabecera_correo_url,
                telefono_whatsapp = :telefono_whatsapp,
                correo_soporte = :correo_soporte,
                enlace_seguimiento = :enlace_seguimiento,
                direccion_fisica = :direccion_fisica,
                color_primario = :color_primario,
                color_secundario = :color_secundario,
                smtp_host = :smtp_host,
                smtp_port = :smtp_port,
                smtp_user = :smtp_user,
                smtp_pass = :smtp_pass,
                smtp_seguridad = :smtp_seguridad,
                activo = :activo
                WHERE id_config = :id_config";
            $valores['id_config'] = $idConfig;
        } else {
            $sql = "INSERT INTO configuracion_correo
                (nombre_empresa, logo_url, cabecera_correo_url, telefono_whatsapp, correo_soporte, enlace_seguimiento, direccion_fisica, color_primario, color_secundario, smtp_host, smtp_port, smtp_user, smtp_pass, smtp_seguridad, activo)
                VALUES
                (:nombre_empresa, :logo_url, :cabecera_correo_url, :telefono_whatsapp, :correo_soporte, :enlace_seguimiento, :direccion_fisica, :color_primario, :color_secundario, :smtp_host, :smtp_port, :smtp_user, :smtp_pass, :smtp_seguridad, :activo)";
        }

        $stmt = $this->conn->prepare($sql);
        foreach ($valores as $clave => $valor) {
            $stmt->bindValue(':' . $clave, $valor);
        }
        return $stmt->execute();
    }

    public function guardarOperativa(array $datos) {
        $this->asegurarTablaOperativa();
        $actual = $this->obtenerOperativa();
        $idConfig = (int)($actual['id_config'] ?? 0);

        $valores = [
            'horario_apertura' => trim((string)($datos['horario_apertura'] ?? '')) ?: null,
            'horario_cierre' => trim((string)($datos['horario_cierre'] ?? '')) ?: null,
            'delivery_activo' => !empty($datos['delivery_activo']) ? 1 : 0,
            'delivery_costo' => max(0, (float)($datos['delivery_costo'] ?? 0)),
            'delivery_gratis_desde' => max(0, (float)($datos['delivery_gratis_desde'] ?? 0)),
            'impuesto_porcentaje' => min(100, max(0, (float)($datos['impuesto_porcentaje'] ?? 0))),
            'stock_minimo_global' => max(0, (float)($datos['stock_minimo_global'] ?? 5))
        ];

        if ($idConfig > 0) {
            $sql = "UPDATE configuracion_tienda SET
                horario_apertura = :horario_apertura,
                horario_cierre = :horario_cierre,
                delivery_activo = :delivery_activo,
                delivery_costo = :delivery_costo,
                delivery_gratis_desde = :delivery_gratis_desde,
                impuesto_porcentaje = :impuesto_porcentaje,
                stock_minimo_global = :stock_minimo_global
                WHERE id_config = :id_config";
            $valores['id_config'] = $idConfig;
        } else {
            $sql = "INSERT INTO configuracion_tienda
                (horario_apertura, horario_cierre, delivery_activo, delivery_costo, delivery_gratis_desde, impuesto_porcentaje, stock_minimo_global)
                VALUES
                (:horario_apertura, :horario_cierre, :delivery_activo, :delivery_costo, :delivery_gratis_desde, :impuesto_porcentaje, :stock_minimo_global)";
        }

        $stmt = $this->conn->prepare($sql);
        foreach ($valores as $clave => $valor) {
            $stmt->bindValue(':' . $clave, $valor);
        }
        return $stmt->execute();
    }
}
