<?php
// api/search_global.php
header('Content-Type: application/json');
require_once 'middleware_auth.php'; // Proteção JWT obrigatória
require_once '../components/DAO/SearchGlobalDAO.php';

$termo = trim($_GET['q'] ?? '');

if (strlen($termo) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $dao = new SearchGlobalDAO();
    $resultados = $dao->buscarGeral($termo);
    echo json_encode($resultados);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
exit;
