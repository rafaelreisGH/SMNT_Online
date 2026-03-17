<?php
// api/get_dashboard_full.php
header('Content-Type: application/json');
require_once 'middleware_auth.php'; // Proteção JWT
require_once '../components/DAO/DashboardDAO.php';

try {
    $dao = new DashboardDAO();

    $response = [
        'success'   => true,
        'resumo'    => $dao->getResumoStatus(),      // Cards numéricos
        'vencidos'  => $dao->getAlertasVencidos(),   // Garantias/Planos expirados
        'avarias'   => $dao->getAvariasPendentes(),  // Avarias sem OS ainda
        'andamento' => $dao->getManutencoesAndamento() // Manutenções em curso
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
