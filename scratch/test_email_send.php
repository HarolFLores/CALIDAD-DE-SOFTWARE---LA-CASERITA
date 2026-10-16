<?php
require_once __DIR__ . '/../Models/MailHelper.php';

echo "Probando envío de correo...\n";
$res = MailHelper::sendEmail('erikcarrasco455@gmail.com', 'Prueba La Caserita', '<p>Hola, esta es una prueba de correo.</p>', true);
var_dump($res);
