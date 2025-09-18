<?php
// api/processar_baixar_viatura.php

// Inclua seu arquivo de conexão com o banco de dados.
// Adapte o caminho conforme a sua estrutura de diretórios.
require_once '../components/ConexaoDB/conexao.php';
$conn = Conexao::getConexao(); // Use $conn para a conexão PDO

header('Content-Type: application/json'); // Define o cabeçalho para JSON

$response = [
    'success' => false,
    'message' => 'Ocorreu um erro desconhecido.'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_viatura = $_POST['id_viatura'] ?? null;

    if ($id_viatura === null || !is_numeric($id_viatura)) {
        $response['message'] = 'ID da viatura inválido.';
        echo json_encode($response);
        exit;
    }

    try {
        // Primeiro, verifique o status atual da viatura
        $stmt = $conn->prepare("SELECT status_da_viatura FROM controle_viaturas.viaturas WHERE id = :id");
        $stmt->bindParam(':id', $id_viatura, PDO::PARAM_INT);
        $stmt->execute();
        $viatura_atual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$viatura_atual) {
            $response['message'] = 'Viatura não encontrada.';
            echo json_encode($response);
            exit;
        }

        if ($viatura_atual['status_da_viatura'] != 'Operante') {
            $response['message'] = 'Esta viatura já está Baixada ou possui outro status que impede a baixa.';
            // Opcional: Você pode querer um código de status HTTP diferente aqui para "não permitido"
            // http_response_code(409); // Conflict
            echo json_encode($response);
            exit;
        }

        // Se a viatura estiver operante, proceda com a baixa
        $stmt = $conn->prepare("UPDATE controle_viaturas.viaturas SET status_da_viatura = 'Baixada' WHERE id = :id");
        $stmt->bindParam(':id', $id_viatura, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Viatura baixada com sucesso! Status alterado para Baixada.';
        } else {
            $response['message'] = 'Erro ao baixar a viatura. Tente novamente.';
        }

    } catch (PDOException $e) {
        // Em um ambiente de produção, logue o erro em vez de exibi-lo diretamente.
        $response['message'] = 'Erro no banco de dados: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Método de requisição inválido. Apenas POST é permitido.';
    // Opcional: Definir um código de status HTTP 405 Method Not Allowed
    // http_response_code(405);
}

echo json_encode($response);
exit;
?>