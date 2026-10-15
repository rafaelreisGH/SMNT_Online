<?php
// api/processar_item_preventiva.php

header('Content-Type: application/json');

// 1. Proteção de Autenticação (Middleware já define USER_AUTH)
require_once 'middleware_auth.php';
require_once '../components/DAO/ItemPreventivaDAO.php';

$response = ['success' => false, 'message' => ''];

// 2. Verificação de Perfil (RBAC)
if (!isset(USER_AUTH['perfil']) || !in_array(USER_AUTH['perfil'], ['Administrador', 'Gestor Central'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'ACESSO NEGADO: Apenas gestores centrais podem gerenciar itens.']);
    exit;
}

$dao = new ItemPreventivaDAO();
$acao = $_GET['acao'] ?? '';

try {
    // --- LÓGICA DE CADASTRO ---
    if ($acao === 'cadastrar') {
        $nome = trim($_POST['nome'] ?? '');

        if (empty($nome)) {
            $response['message'] = "O nome do item é obrigatório.";
        } else {
            $res = $dao->inserir($nome);

            if ($res === true) {
                $response['success'] = true;
                $response['message'] = "Item '$nome' cadastrado com sucesso!";
            } elseif ($res === "duplicado") {
                // Tratamento específico para o erro UNIQUE do Banco de Dados
                $response['message'] = "O item '$nome' já existe no sistema.";
            } else {
                $response['message'] = "Não foi possível cadastrar o item no momento.";
            }
        }
    }

    // --- LÓGICA DE EXCLUSÃO ---
    elseif ($acao === 'excluir') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) {
            $response['message'] = "ID inválido para exclusão.";
        } else {
            $res = $dao->excluir($id);

            if ($res === true) {
                $response['success'] = true;
                $response['message'] = "Item removido com sucesso.";
            } elseif ($res === "vínculo_existente") {
                // Tratamento para erro de Chave Estrangeira (Integridade)
                $response['message'] = "Este item não pode ser excluído pois já está sendo utilizado em Planos de Manutenção ou registros históricos.";
            } else {
                $response['message'] = "Erro ao tentar excluir o item.";
            }
        }
    } elseif ($acao === 'status') {
        $id = $_POST['id'];
        $novoStatus = $_POST['status']; // 'Ativo' ou 'Inativo'
        $response['success'] = $dao->alterarStatus($id, $novoStatus);
        $response['message'] = $response['success'] ? "Status atualizado!" : "Erro ao mudar status.";
    } else {
        $response['message'] = "Ação não reconhecida.";
    }
} catch (Exception $e) {
    error_log("Erro na API ItemPreventiva: " . $e->getMessage());
    $response['message'] = "Ocorreu um erro interno no servidor.";
}

echo json_encode($response);
exit;
