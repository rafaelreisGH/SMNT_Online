<?php
// api/processar_combustivel.php
header('Content-Type: application/json');
require_once 'middleware_auth.php';
require_once '../components/DAO/CombustivelDAO.php';
require_once '../components/Log/LoggerService.php';

// Acesso reservado
if (!in_array(USER_AUTH['perfil'], ['Administrador', 'Gestor Central'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

$dao = new CombustivelDAO();
$acao = $_GET['acao'] ?? '';
$data = $_POST;

try {
    switch ($acao) {
        case 'salvar':
            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            $tipo = trim($data['tipo'] ?? '');

            if (empty($tipo)) throw new Exception("O nome do combustível é obrigatório.");

            if ($id) {
                $dao->atualizar($id, $tipo);
                LoggerService::registrar("Atualizou combustível ID $id para $tipo", "combustiveis", $id);
                $msg = "Combustível atualizado!";
            } else {
                $dao->inserir($tipo);
                $msg = "Combustível cadastrado!";
            }
            echo json_encode(['success' => true, 'message' => $msg]);
            break;

        case 'excluir':
            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
            $dao->excluir($id);
            LoggerService::registrar("Excluiu combustível ID $id", "combustiveis", $id);
            echo json_encode(['success' => true, 'message' => 'Combustível removido!']);
            break;

        default:
            throw new Exception("Ação inválida.");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
