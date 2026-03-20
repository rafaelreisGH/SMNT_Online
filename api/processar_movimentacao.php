<?php
// api/processar_movimentacao.php
header('Content-Type: application/json');

// Desativa exibição de erros para não corromper o JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'middleware_auth.php';
require_once '../components/DAO/MovimentacaoDAO.php';
require_once '../components/Log/LoggerService.php';

$response = ['success' => false, 'message' => ''];
$acao = $_GET['acao'] ?? '';

try {
    $dao = new MovimentacaoDAO();

    if ($acao === 'transferir') {
        if (!in_array(USER_AUTH['perfil'], ['Administrador', 'Gestor Central'])) {
            throw new Exception("Acesso Negado: Apenas gestores centrais autorizam transferências.");
        }

        // Validação básica
        if (empty($_POST['viatura_id']) || empty($_POST['unidade_destino_id'])) {
            throw new Exception("Selecione a viatura e o destino.");
        }

        if ($dao->solicitarTransferencia($_POST)) {
            LoggerService::registrar("Transferiu viatura ID " . $_POST['viatura_id'], "viaturas", $_POST['viatura_id']);
            $response['success'] = true;
            $response['message'] = "Transferência registrada com sucesso!";
        }
    } elseif ($acao === 'confirmar') {
        if ($dao->confirmarRecebimento((int)$_POST['id'], USER_AUTH['id'])) {
            LoggerService::registrar("Confirmou recebimento de viatura", "movimentacoes_lotacao", $_POST['id']);
            $response['success'] = true;
            $response['message'] = "Viatura recebida e integrada à frota local.";
        }
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (Throwable $t) {
    $response['message'] = "Erro crítico no servidor.";
}

echo json_encode($response);
exit;
