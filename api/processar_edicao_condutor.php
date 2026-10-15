<?php
// api/processar_edicao_condutor.php
header('Content-Type: application/json');
ob_start();

require_once 'middleware_auth.php'; // Proteção JWT
require_once '../components/DAO/CondutorDAO.php';
require_once '../components/Log/LoggerService.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);

    // 1. Validação de campos obrigatórios vazios
    if (!$id) {
        $response['message'] = "ID inválido.";
    } elseif (empty(trim($data['nome'] ?? '')) || empty(trim($data['cpf'] ?? ''))) {
        $response['message'] = "Nome e CPF são campos obrigatórios.";
    } else {
        try {
            $dao = new CondutorDAO();

            // 2. Tenta atualizar
            if ($dao->updateCondutor($data)) {
                // 3. Registro de Log se houver sucesso
                LoggerService::registrar("Editou condutor: " . $data['nome'], "condutor", $id);

                $response['success'] = true;
                $response['message'] = "Dados atualizados com sucesso!";
            } else {
                // Caso o rowCount seja 0 (dados idênticos)
                $response['message'] = "Nenhuma alteração detectada nos dados informados.";
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
    }
}

ob_clean();
echo json_encode($response);
exit;
