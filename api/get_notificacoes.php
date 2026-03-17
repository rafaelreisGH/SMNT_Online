<?php
header('Content-Type: application/json');
require_once 'middleware_auth.php';
require_once '../components/DAO/DashboardDAO.php';

$dao = new DashboardDAO();
$avarias = $dao->getAvariasPendentes();
$vencidos = $dao->getAlertasVencidos();

$total = count($avarias) + count($vencidos);

echo json_encode([
    'total' => $total,
    'detalhes' => array_slice($avarias, 0, 5) // Envia as 5 mais recentes
]);
