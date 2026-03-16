<?php
header('Content-Type: application/json');
require_once 'middleware_auth.php';
require_once '../components/DAO/ManutencaoDAO.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);

    if (!$id) {
        $response['message'] = "ID da manutenção inválido.";
    } else {
        try {
            $mDAO = new ManutencaoDAO();
            if ($mDAO->updateManutencao($data)) {
                $response['success'] = true;
                $response['message'] = "Manutenção atualizada com sucesso!";
            } else {
                $response['message'] = "Nenhuma alteração foi detectada ou acesso negado.";
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
    }
}
echo json_encode($response);