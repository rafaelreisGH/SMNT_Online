<?php
// api/buscar_cautelas_concluidas.php
require_once __DIR__ . '/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/RegistroAvariaDAO.php';

header('Content-Type: application/json');

$viatura_id = filter_var($_GET['viatura_id'] ?? null, FILTER_VALIDATE_INT);

if (!$viatura_id) {
    echo json_encode(['success' => false, 'message' => 'Viatura não informada.']);
    exit;
}

try {
    $dao = new RegistroAvariaDAO();
    $cautelas = $dao->listarCautelasConcluidasPorViatura($viatura_id);

    // Formata a data para exibição no frontend
    foreach ($cautelas as &$c) {
        $c['data_fim_formatada'] = $c['data_fim'] ? date('d/m/Y H:i', strtotime($c['data_fim'])) : 'N/A';
    }

    echo json_encode([
        'success' => true, 
        'cautelas' => $cautelas
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}