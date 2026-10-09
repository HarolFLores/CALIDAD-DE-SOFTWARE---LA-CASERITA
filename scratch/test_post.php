<?php
header('Content-Type: application/json');
echo json_encode([
    'post' => $_POST,
    'get' => $_GET,
    'raw' => file_get_contents('php://input'),
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? ''
]);
