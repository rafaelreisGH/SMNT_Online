<?php
header('Content-Type: application/json');
require_once 'middleware_auth.php';
require_once '../components/DAO/ManutencaoDAO.php';
require_once '../components/Log/LoggerService.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;

    // Validação de campos obrigatórios para o fechamento
    if (empty($data['id']) || empty($data['data_saida']) || empty($data['servico_realizado']) || !isset($data['custo'])) {
        $response['message'] = "Data de saída, serviço realizado e custo são obrigatórios.";
    } else {
        try {
            $mDAO = new ManutencaoDAO();

            // Tratamento do custo para o formato do banco (ex: 1.250,00 -> 1250.00)
            $data['custo'] = str_replace(['.', ','], ['', '.'], $data['custo']);

            if ($mDAO->finalizarManutencao($data)) {
                LoggerService::registrar("Finalizou manutenção ID " . $data['id'], "manutencoes", $data['id']);

                $response['success'] = true;
                $response['message'] = "Manutenção finalizada com sucesso! Viatura disponível.";
            } else {
                $response['message'] = "Erro ao finalizar ou acesso negado.";
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
    }
}
echo json_encode($response);
