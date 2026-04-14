<?php
// api/processar_acessorios.php
header('Content-Type: application/json');
require_once 'middleware_auth.php';
require_once '../components/DAO/AcessorioDAO.php';
require_once '../components/Log/LoggerService.php';

// Apenas Gestor Central ou Admin
if (!in_array(USER_AUTH['perfil'], ['Administrador', 'Gestor Central'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

$dao = new AcessorioDAO();
$acao = $_GET['acao'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    if ($acao === 'cadastrar') {
        $nome = trim($_POST['nome'] ?? '');
        $uni = isset($_POST['eh_universal']) ? 1 : 0;
        $res = $dao->inserir($nome, $uni);
        if ($res === true) {
            LoggerService::registrar("Cadastrou acessório: $nome", "acessorios_viatura");
            $response = ['success' => true, 'message' => 'Item adicionado à lista mestra!'];
        } elseif ($res === "duplicado") {
            $response['message'] = "Este item já existe.";
        }
    }
    // Removidos os blocos 'vincular' e 'remover_vinculo' que causavam erro
    elseif ($acao === 'toggle_universal') {
        $id = $_POST['id'];
        $status = $_POST['status'];
        if ($dao->alterarStatusUniversal($id, $status)) {
            $response = ['success' => true, 'message' => 'Configuração de item universal atualizada!'];
        }
    } elseif ($acao === 'excluir') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            $response['message'] = "ID inválido.";
        } else {
            $res = $dao->excluir($id);
            if ($res === true) {
                LoggerService::registrar("Excluiu acessório ID $id", "acessorios_viatura", $id);
                $response['success'] = true;
                $response['message'] = "Item removido com sucesso!";
            } elseif ($res === "vinculado") {
                $response['message'] = "Não é possível excluir: este item já foi registrado em cautelas anteriores.";
            }
        }
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
