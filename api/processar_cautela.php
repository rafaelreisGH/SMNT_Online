<?php
// api/processar_cautela.php
ob_start();
header('Content-Type: application/json');
ini_set('display_errors', 0); // Desativa avisos para não "sujar" o JSON

require_once 'middleware_auth.php';
require_once '../components/DAO/CautelaDAO.php';
require_once '../components/DAO/CondutorDAO.php';
require_once '../components/Log/LoggerService.php';

$response = ['success' => false, 'message' => ''];
$data = $_POST;
$acao = $_GET['acao'] ?? '';

try {
    if (USER_AUTH['perfil'] === 'Condutor') {
        throw new Exception("Acesso Negado: Condutores não podem realizar esta operação.");
    }

    $dao = new CautelaDAO();

    // AÇÃO A: CANCELAR
    if ($acao === 'cancelar') {
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) throw new Exception("ID inválido.");
        if ($dao->atualizarStatus($id, 'Cancelada')) {
            LoggerService::registrar("Cancelou cautela ID $id", "cautelas", $id);
            $response['success'] = true;
            $response['message'] = "Cautela cancelada com sucesso!";
        }
    }
    // AÇÃO B: EDITAR
    elseif ($acao === 'editar') {
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) throw new Exception("ID inválido para edição.");

        if (empty($data['viatura_id']) || empty($data['condutor_id'])) {
            throw new Exception("Viatura e Condutor são obrigatórios.");
        }

        // VALIDAÇÃO DO ODÔMETRO (Trava de Negativos)
        if (!isset($data['odometro_saida']) || !is_numeric($data['odometro_saida']) || $data['odometro_saida'] < 0) {
            throw new Exception("O odômetro de saída não pode ser negativo.");
        }

        $checklist = json_decode($data['checklist_json'] ?? '[]', true);
        if (empty($checklist)) throw new Exception("Checklist não recebido.");

        $data['status'] = $data['status'] ?? 'Pendente';

        if ($dao->atualizarCautela($data, $checklist)) {
            LoggerService::registrar("Editou rascunho ID $id", "cautelas", $id);
            $response['success'] = true;
            $response['message'] = "Alterações salvas com sucesso!";
        } else {
            throw new Exception("Nenhuma alteração feita ou cautela já foi assinada.");
        }
    }
    // AÇÃO C: CRIAR (Novo Registro)
    else {
        if (empty($data['viatura_id']) || empty($data['condutor_id']) || empty($data['odometro_saida'])) {
            throw new Exception("Preencha todos os campos obrigatórios.");
        }

        // VALIDAÇÃO DO ODÔMETRO (Trava de Negativos)
        if (!is_numeric($data['odometro_saida']) || $data['odometro_saida'] < 0) {
            throw new Exception("O odômetro de saída não pode ser negativo.");
        }

        $checklist = json_decode($data['checklist_json'] ?? '[]', true);
        if (empty($checklist)) throw new Exception("Erro: Checklist obrigatório.");

        $idCautela = $dao->abrirCautela($data, $checklist);

        if ($idCautela) {
            LoggerService::registrar("Criou cautela ID $idCautela", "cautelas", $idCautela);
            $response['success'] = true;
            $response['message'] = "Cautela registrada com sucesso!";
        }
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

ob_clean(); // Descarta qualquer lixo (Warnings/Notices) antes do JSON
echo json_encode($response);
exit;
