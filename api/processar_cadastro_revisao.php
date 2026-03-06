<?php
// api/processar_cadastro_revisao.php

header('Content-Type: application/json');
require_once '../components/DAO/RevisaoDAO.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

$response = ['success' => false, 'message' => '', 'errors' => []];
$data = $_POST;

// --- 1. Validação de Campos Vazios (Obrigatórios) ---
if (empty($data['viatura_id'])) {
    $response['errors']['viatura_id'] = "Selecione uma viatura.";
}

if (empty(trim($data['tipo_revisao']))) {
    $response['errors']['tipo_revisao'] = "O tipo de serviço é obrigatório.";
}

if (empty($data['data_revisao'])) {
    $response['errors']['data_revisao'] = "A data da manutenção é obrigatória.";
}

// --- 2. Validação de Números (Quilometragem) ---
// Verifica se foi preenchido e se é um número válido e positivo
if (!empty($data['quilometragem'])) {
    if (!is_numeric($data['quilometragem']) || $data['quilometragem'] < 0) {
        $response['errors']['quilometragem'] = "A quilometragem deve ser um número positivo.";
    }
}

if (!empty($data['proxima_revisao_km'])) {
    if (!is_numeric($data['proxima_revisao_km']) || $data['proxima_revisao_km'] < 0) {
        $response['errors']['proxima_revisao_km'] = "A KM da próxima revisão deve ser um número positivo.";
    }
}

// --- 3. Validação de Lógica de Datas ---
// Exemplo: A data da próxima revisão não pode ser anterior à data da revisão atual
if (!empty($data['data_revisao']) && !empty($data['proxima_revisao_data'])) {
    if (strtotime($data['proxima_revisao_data']) < strtotime($data['data_revisao'])) {
        $response['errors']['proxima_revisao_data'] = "A próxima revisão não pode ser antes da data atual.";
    }
}

// --- 4. Validação de Tamanho de Texto (Segurança/Banco) ---
if (strlen($data['tipo_revisao']) > 100) {
    $response['errors']['tipo_revisao'] = "O nome do serviço é muito longo (máx 100 caracteres).";
}

// --- Retorno em caso de erro de validação ---
if (!empty($response['errors'])) {
    $response['message'] = "Existem erros no formulário. Verifique os campos em vermelho.";
    echo json_encode($response);
    exit();
}

// --- Se passou nas validações, prepara para o DAO ---
$revisaoData = [
    'viatura_id'           => (int)$data['viatura_id'], // Cast para inteiro
    'data_revisao'         => $data['data_revisao'],
    'tipo_revisao'         => htmlspecialchars(trim($data['tipo_revisao'])), // Limpeza de XSS
    'quilometragem'        => !empty($data['quilometragem']) ? (int)$data['quilometragem'] : null,
    'itens_substituidos'   => htmlspecialchars($data['itens_substituidos']),
    'observacoes'          => htmlspecialchars($data['observacoes']),
    'status_revisao'       => $data['status_revisao'],
    'proxima_revisao_km'   => !empty($data['proxima_revisao_km']) ? (int)$data['proxima_revisao_km'] : null,
    'proxima_revisao_data' => !empty($data['proxima_revisao_data']) ? $data['proxima_revisao_data'] : null
];

try {
    $revisaoDAO = new RevisaoDAO();
    if ($revisaoDAO->inserirRevisao($revisaoData)) {
        $response['success'] = true;
        $response['message'] = "Manutenção registrada com sucesso!";
    } else {
        $response['message'] = "Erro ao salvar no banco de dados.";
    }
} catch (Exception $e) {
    $response['message'] = "Erro no servidor: " . $e->getMessage();
}

echo json_encode($response);
exit();