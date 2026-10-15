<?php
// api/test_token.php
header('Content-Type: application/json');

// Este arquivo interrompe a execução se o token for inválido
require_once 'middleware_auth.php'; 

// Se chegou aqui, é porque o token é válido e a constante USER_AUTH existe
echo json_encode([
    'success' => true,
    'message' => 'Você está autenticado!',
    'seu_perfil_extraido_do_jwt' => USER_AUTH
]);