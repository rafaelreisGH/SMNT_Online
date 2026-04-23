<?php
// api/deletar_foto_avaria.php
header('Content-Type: application/json');
require_once 'middleware_auth.php'; // Protege a rota
require_once '../components/ConexaoDB/conexao.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $arquivo = $_POST['arquivo'] ?? '';

    if ($id) {
        try {
            $db = Conexao::getConexao();

            // 1. Buscamos o nome do arquivo se ele não foi enviado corretamente
            if (empty($arquivo)) {
                $stmtNome = $db->prepare("SELECT caminho_arquivo FROM avaria_fotos WHERE id = :id");
                $stmtNome->execute([':id' => $id]);
                $arquivo = $stmtNome->fetchColumn();
            }

            // 2. Deleta do Banco de Dados
            $stmt = $db->prepare("DELETE FROM avaria_fotos WHERE id = :id");
            if ($stmt->execute([':id' => $id])) {

                // 3. Deleta o Arquivo Físico do Servidor
                // Caminho relativo: sobe um nível (..) para sair de api/ e entra em assets/
                $caminhoFisico = __DIR__ . '/../assets/uploads/avarias/' . $arquivo;

                if (!empty($arquivo) && file_exists($caminhoFisico)) {
                    if (!unlink($caminhoFisico)) {
                        // Se falhou ao apagar o arquivo, avisamos no log mas o banco já foi limpo
                        error_log("Erro ao deletar arquivo físico: " . $caminhoFisico);
                    }
                }

                $response['success'] = true;
                $response['message'] = "Foto removida com sucesso.";
            } else {
                throw new Exception("Não foi possível excluir o registro do banco.");
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
    } else {
        $response['message'] = "ID da foto inválido.";
    }
}

echo json_encode($response);
exit;
