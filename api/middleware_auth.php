<?php
// api/middleware_auth.php
require_once __DIR__ . '/../components/Auth/JwtHandler.php';

// Recupera os cabeçalhos HTTP recebidos na requisição
$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$authHeader = $headers['authorization'] ?? '';

if (preg_match('/bearer\s(\S+)/i', $authHeader, $matches)) {
    $token = $matches[1];
    $jwt = new JwtHandler();
    $userData = $jwt->validarToken($token);

    if (!$userData) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token inválido ou expirado.']);
        exit;
    }

    define('USER_AUTH', $userData);
} else {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Cabeçalho Authorization não encontrado ou malformatado.'
    ]);
    exit;
}

