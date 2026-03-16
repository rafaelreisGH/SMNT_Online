<?php
header('Content-Type: application/json');
require_once 'middleware_auth.php'; // Proteção JWT
require_once '../components/DAO/ManutencaoDAO.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;

    $data['ordem_de_servico'] = filter_var($data['ordem_de_servico'] ?? null, FILTER_VALIDATE_INT) ?: null;

    // Validação de entrada obrigatória para métricas
    if (empty($data['viatura_id']) || empty($data['quilometragem']) || empty($data['oficina_id'])) {
        $response['message'] = "Viatura, KM e Oficina são obrigatórios para abrir a manutenção.";
    } else {
        try {
            $mDAO = new ManutencaoDAO();
            if ($mDAO->inserirManutencao($data)) {
                $response['success'] = true;
                $response['message'] = "Ordem de Manutenção aberta com sucesso!";
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
    }
}
echo json_encode($response);
