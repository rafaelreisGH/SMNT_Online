<?php
// api/middleware_auth.php
require_once __DIR__ . '/../components/Auth/JwtHandler.php';

// Debug: Pegar todos os cabeçalhos de forma robusta
$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$authHeader = $headers['authorization'] ?? '';

if (preg_match('/bearer\s(\S+)/i', $authHeader, $matches)) {
    $token = $matches[1];
    $jwt = new JwtHandler();
    $userData = $jwt->validarToken($token);

    if (!$userData) {
        http_response_code(401);
        // Mudamos a mensagem para ajudar no debug
        echo json_encode(['success' => false, 'message' => 'Token invalido ou expirado. Verifique o log do servidor.']);
        exit;
    }
    
    define('USER_AUTH', $userData); 
} else {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'Cabecalho Authorization nao encontrado ou malformatado.',
        'debug_header_recebido' => $authHeader // Isso nos dira se o JS enviou certo
    ]);
    exit;
}