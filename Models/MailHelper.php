<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

class MailHelper {
    public static function sendEmail($to, $subject, $body, $isHTML = true) {
        $host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $port = (int)(getenv('SMTP_PORT') ?: 587);
        $smtpUsername = getenv('SMTP_USERNAME') ?: 'erikcarrasco455@gmail.com';
        $smtpPassword = getenv('SMTP_PASSWORD') ?: 'dkgkbxgmenizalsi';
        $encryption = getenv('SMTP_ENCRYPTION') ?: 'tls';
        $fromEmail = getenv('SMTP_FROM_EMAIL') ?: 'erikcarrasco455@gmail.com';
        $fromName = 'Tienda La Caserita';

        // 1. Verificar si existe archivo Config/mail.php
        $configFile = __DIR__ . '/../Config/mail.php';
        if (file_exists($configFile)) {
            $cfg = include $configFile;
            if (!empty($cfg['smtp_username']) && !empty($cfg['smtp_password'])) {
                $smtpUsername = $cfg['smtp_username'];
                $smtpPassword = $cfg['smtp_password'];
                $host         = $cfg['smtp_host'] ?? $host;
                $port         = (int)($cfg['smtp_port'] ?? $port);
                $encryption   = $cfg['smtp_encryption'] ?? $encryption;
                $fromEmail    = $cfg['from_email'] ?? $fromEmail;
                $fromName     = $cfg['from_name'] ?? $fromName;
            }
        }

        // 2. Si aún no hay credenciales, buscar en la tabla configuracion_correo
        if (empty($smtpUsername) || empty($smtpPassword)) {
            try {
                require_once __DIR__ . '/../Core/dataBase.php';
                $db = (new DataBase())->getConnection();
                $stmt = $db->query("SELECT * FROM configuracion_correo WHERE activo = 1 LIMIT 1");
                $dbConfig = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
                if ($dbConfig && !empty($dbConfig['smtp_user']) && !empty($dbConfig['smtp_pass'])) {
                    $smtpUsername = $dbConfig['smtp_user'];
                    $smtpPassword = $dbConfig['smtp_pass'];
                    if (!empty($dbConfig['smtp_host'])) $host = $dbConfig['smtp_host'];
                    if (!empty($dbConfig['smtp_port'])) $port = (int)$dbConfig['smtp_port'];
                    if (!empty($dbConfig['smtp_seguridad'])) $encryption = $dbConfig['smtp_seguridad'];
                    if (!empty($dbConfig['correo_soporte'])) $fromEmail = $dbConfig['correo_soporte'];
                    if (!empty($dbConfig['nombre_empresa'])) $fromName = $dbConfig['nombre_empresa'];
                }
            } catch (Throwable $e) {
                // Silencioso si falla la conexión a BD
            }
        }

        // Guardar copia de respaldo en disco para visualización inmediata en entorno local
        $backupDir = __DIR__ . '/../scratch/emails_enviados';
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0777, true);
        }
        $backupFile = $backupDir . '/email_' . time() . '_' . bin2hex(random_bytes(3)) . ($isHTML ? '.html' : '.txt');
        @file_put_contents($backupFile, "<!-- Para: $to | Asunto: $subject -->\n" . $body);

        // Si tenemos credenciales SMTP, enviar por PHPMailer
        if (!empty($smtpUsername) && !empty($smtpPassword)) {
            $mail = new PHPMailer(true);
            try {
                $mail->SMTPDebug  = 0;
                $mail->isSMTP();
                $mail->Host       = $host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtpUsername;
                $mail->Password   = $smtpPassword;
                $mail->SMTPSecure = ($encryption === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $port;
                $mail->Timeout    = 10;

                $mail->setFrom($fromEmail ?: $smtpUsername, $fromName);
                $mail->addAddress($to);

                $mail->isHTML($isHTML);
                $mail->Subject = $subject;
                $mail->Body    = $body;
                if ($isHTML) {
                    $mail->AltBody = strip_tags($body);
                }
                $mail->CharSet = 'UTF-8';

                return $mail->send();
            } catch (Exception $e) {
                error_log("Error enviando correo con PHPMailer: " . $mail->ErrorInfo . " (" . $e->getMessage() . ")");
                // Intentar fallback con mail() nativo
            }
        }

        // Fallback a mail() de PHP nativo si está disponible
        $headers = [
            'MIME-Version: 1.0',
            'From: ' . $fromName . ' <' . $fromEmail . '>',
            'Reply-To: ' . $fromEmail,
            'X-Mailer: PHP/' . phpversion()
        ];
        if ($isHTML) {
            $headers[] = 'Content-type: text/html; charset=UTF-8';
        } else {
            $headers[] = 'Content-type: text/plain; charset=UTF-8';
        }

        return @mail($to, $subject, $body, implode("\r\n", $headers));
    }
}
