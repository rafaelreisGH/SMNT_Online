<?php
// api/processar_cadastro_condutor.php

// Inclui as dependências
require_once 'middleware_auth.php';
require_once '../components/ConexaoDB/conexao.php';
require_once '../components/DAO/CondutorDAO.php'; // <<-- Você precisará criar este arquivo!

// Apenas requisições POST são permitidas
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

header('Content-Type: application/json');
$response = ['success' => false, 'message' => '', 'errors' => []];
$data = $_POST;

// --- Validação Server-Side ---
// (Esta é uma validação básica. Você pode criar um arquivo de funções como fez para viaturas)
if (empty(trim($data['nome']))) $response['errors']['nome'] = "O nome completo é obrigatório.";
if (empty($data['cpf']) || !preg_match('/^\d{11}$/', preg_replace('/\D/', '', $data['cpf']))) $response['errors']['cpf'] = "CPF inválido.";
if (empty($data['numero_cnh'])) $response['errors']['numero_cnh'] = "O número da CNH é obrigatório.";
if (empty($data['categoria_cnh'])) $response['errors']['categoria_cnh'] = "A categoria da CNH é obrigatória.";
if (empty($data['data_validade_cnh'])) $response['errors']['data_validade_cnh'] = "A data de validade da CNH é obrigatória.";

// Se houver erros de validação, retorna o JSON com os erros
if (!empty($response['errors'])) {
    $response['message'] = "Existem erros no formulário. Por favor, corrija-os.";
    echo json_encode($response);
    exit();
}

// --- Preparação dos Dados para o Banco ---
// Mapeia os nomes do formulário para os nomes das colunas do BD
$condutorData = [
    'nome' => $data['nome'],
    'cpf' => preg_replace('/\D/', '', $data['cpf']), // Salva apenas os números
    'numero_cnh' => $data['numero_cnh'],
    'categoria_cnh' => $data['categoria_cnh'],
    'data_validade_cnh' => $data['data_validade_cnh'],
    'anotacao_ear' => isset($data['ear_observacao']) ? 1 : 0, // Converte checkbox para 1 ou 0
    'rg_militar' => $data['identidade_militar'] ?? null,
    'posto_grad' => $data['posto_graduacao'] ?? null,
    'ubm_id' => $data['unidade_id'] ?? null,
    'status' => $data['status'] ?? 'ativo'
];

// Campos do seu formulário que NÃO existem na tabela:
// 'data_nascimento', 'genero', 'data_primeira_habilitacao', 'registro_cnh'
// Você precisa decidir se quer adicioná-los à tabela ou removê-los do formulário.

try {
    $condutorDAO = new CondutorDAO();
    if ($condutorDAO->inserirCondutor($condutorData)) {
        $response['success'] = true;
        $response['message'] = "Condutor cadastrado com sucesso!";
    } else {
        // Isso pode acontecer se houver um erro de chave única (CPF/CNH duplicado)
        $response['message'] = "Não foi possível cadastrar o condutor. Verifique se o CPF ou CNH já existem.";
    }
} catch (Exception $e) {
    error_log("Erro ao cadastrar condutor: " . $e->getMessage());
    $response['message'] = "Ocorreu um erro interno no servidor.";
}

echo json_encode($response);
exit();
