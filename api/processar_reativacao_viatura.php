<?php
// Define o cabeçalho para indicar que a resposta será em JSON
header('Content-Type: application/json');

// Inclui o arquivo de conexão com o banco de dados
// Certifique-se de que o caminho para o arquivo Conexao.php está correto.
// Se este arquivo estiver em 'api/' e a conexão em 'components/ConexaoDB/', o caminho relativo está ok.
require_once '../components/ConexaoDB/conexao.php';

// Inicializa a resposta padrão como falha
$response = ['success' => false, 'message' => ''];

// Verifica se a requisição é do tipo POST e se o ID da viatura foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_viatura'])) {
    $idViatura = $_POST['id_viatura'];

    try {
        // Obtém a conexão PDO
        $conn = Conexao::getConexao();

        // Prepara a consulta SQL para atualizar o status da viatura para 'Operante'
        $stmt = $conn->prepare("UPDATE controle_viaturas.viaturas SET status_da_viatura = 'Operante' WHERE id = :id_viatura");

        // Associa o parâmetro ID da viatura
        $stmt->bindParam(':id_viatura', $idViatura, PDO::PARAM_INT);

        // Executa a consulta
        if ($stmt->execute()) {
            // Verifica se alguma linha foi afetada (ou seja, se a atualização ocorreu)
            if ($stmt->rowCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Viatura reativada com sucesso para "Operante"!';
            } else {
                // Se nenhuma linha foi afetada, pode ser que a viatura já estivesse "Operante"
                $response['message'] = 'Nenhuma viatura encontrada com o ID fornecido ou ela já está com o status "Operante".';
            }
        } else {
            // Se a execução falhou, obtém informações sobre o erro
            $errorInfo = $stmt->errorInfo();
            $response['message'] = 'Erro ao executar a atualização no banco de dados: ' . $errorInfo[2];
            // Para depuração, você pode registrar o erro completo:
            // error_log("Erro PDO na reativação: " . $errorInfo[2]);
        }
    } catch (PDOException $e) {
        // Captura exceções de PDO (erros de conexão ou consulta)
        $response['message'] = 'Erro de banco de dados na reativação: ' . $e->getMessage();
        // Para depuração, você pode registrar o erro completo:
        // error_log("Exceção PDO na reativação: " . $e->getMessage());
    }
} else {
    // Se a requisição não for POST ou o ID não for fornecido
    $response['message'] = 'Requisição inválida. O ID da viatura não foi fornecido.';
}

// Retorna a resposta em formato JSON
echo json_encode($response);
?>