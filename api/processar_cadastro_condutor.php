<?php
// api/processar_cadastro_condutor.php
header('Content-Type: application/json');
require_once 'middleware_auth.php';
require_once '../components/DAO/CondutorDAO.php';
require_once '../components/Log/LoggerService.php';

$response = ['success' => false, 'message' => ''];
$acao = $_GET['acao'] ?? '';

try {
    $dao = new CondutorDAO();

    // 1. LÓGICA DE ALTERAÇÃO DE STATUS (VIA AJAX NA TABELA)
    if ($acao === 'status') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? null;
        if ($id && $status) {
            if ($dao->alterarStatus($id, $status)) {
                // Registra o LOG
                LoggerService::registrar("Alterou status do condutor ID $id para $status", "condutor", $id);

                $response['success'] = true;
                $response['message'] = "Status atualizado com sucesso!";
            }
        } else {
            $response['message'] = "Dados inválidos para alteração de status.";
        }
    }
    // 2. LÓGICA DE CADASTRO NOVO
    else {
        $data = $_POST;
        if (empty(trim($data['nome'])) || empty($data['cpf']) || empty($data['ubm_id'])) {
            $response['message'] = "Nome, CPF e Unidade são obrigatórios.";
        } else {
            $condutorData = [
                'nome' => trim($data['nome']),
                'cpf' => preg_replace('/\D/', '', $data['cpf']),
                'numero_cnh' => $data['numero_cnh'] ?? null,
                'categoria_cnh' => $data['categoria_cnh'] ?? null,
                'data_validade_cnh' => $data['data_validade_cnh'] ?? null,
                'anotacao_ear' => isset($data['anotacao_ear']) ? 1 : 0,
                'rg_militar' => $data['rg_militar'] ?? null,
                'posto_grad' => $data['posto_grad'] ?? null,
                'ubm_id' => (int)$data['ubm_id'],
                'status' => $data['status'] ?? 'ativo'
            ];

            if ($dao->inserirCondutor($condutorData)) {
                LoggerService::registrar("Cadastrou novo condutor: " . $condutorData['nome'], "condutor");

                $response['success'] = true;
                $response['message'] = "Condutor cadastrado com sucesso!";
            }
        }
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
