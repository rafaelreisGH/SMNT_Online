<?php
// api/get_dados_viatura.php
header('Content-Type: application/json');
require_once 'middleware_auth.php';
require_once '../components/DAO/ViaturaDAO.php';

$id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$id) {
    echo json_encode(['success' => false]);
    exit;
}

try {
    $vDAO = new ViaturaDAO();
    $viatura = $vDAO->buscarDetalhesViaturaPorId($id);

    echo json_encode($viatura);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
