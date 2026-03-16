<?php
// api/processar_oficina.php
header('Content-Type: application/json');
require_once 'middleware_auth.php'; // Proteção JWT
require_once '../components/DAO/OficinaDAO.php';

$response = ['success' => false, 'message' => ''];

// Apenas Gestor Central ou Admin gerenciam oficinas do estado
if (!in_array(USER_AUTH['perfil'], ['Administrador', 'Gestor Central'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado para o seu perfil.']);
    exit;
}

$dao = new OficinaDAO();
$acao = $_GET['acao'] ?? '';

try {
    if ($acao === 'cadastrar') {
        if (empty($_POST['nome_fantasia'])) {
            $response['message'] = "O Nome Fantasia é obrigatório.";
        } else {
            $res = $dao->inserir($_POST);
            if ($res === true) {
                $response['success'] = true;
                $response['message'] = "Oficina cadastrada com sucesso!";
            } elseif ($res === "duplicado") {
                $response['message'] = "Erro: Este CNPJ já está cadastrado no sistema.";
            }
        }
    } elseif ($acao === 'status') {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $novoStatus = $_POST['status']; // 'ativa' ou 'inativa'

        if ($dao->alterarStatus($id, $novoStatus)) {
            $response['success'] = true;
            $response['message'] = "Status da oficina atualizado!";
        }
    }
} catch (Exception $e) {
    $response['message'] = "Erro processamento: " . $e->getMessage();
}

echo json_encode($response);
