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
    $userPerfil = USER_AUTH['perfil'];

    // --- BLOQUEIO GLOBAL ---
    // Perfis 'Condutor' e 'Consulta' não podem realizar NENHUMA movimentação
    if (in_array($userPerfil, ['Condutor', 'Consulta'])) {
        throw new Exception("Acesso Negado: Seu perfil não tem permissão para movimentar viaturas.");
    }

    if ($acao === 'transferir') {
        // Apenas Gestão Central/Admin pode INICIAR uma transferência
        if (!in_array($userPerfil, ['Administrador', 'Gestor Central'])) {
            throw new Exception("Acesso Negado: Apenas gestores centrais autorizam transferências.");
        }

        if (empty($_POST['viatura_id']) || empty($_POST['unidade_destino_id'])) {
            throw new Exception("Selecione a viatura e o destino.");
        }

        if ($dao->solicitarTransferencia($_POST)) {
            // LOG DETALHADO: Registra para onde a viatura foi enviada
            LoggerService::registrar(
                "Solicitou transferência da Vtr ID {$_POST['viatura_id']} para Unidade ID {$_POST['unidade_destino_id']}",
                "viaturas",
                $_POST['viatura_id']
            );
            $response['success'] = true;
            $response['message'] = "Transferência registrada com sucesso!";
        }
    } elseif ($acao === 'confirmar') {
        // Passamos o objeto USER_AUTH inteiro para o DAO fazer a checagem RBAC
        if ($dao->confirmarRecebimento((int)$_POST['id'], USER_AUTH)) {
            LoggerService::registrar("Confirmou recebimento de viatura", "movimentacoes_lotacao", $_POST['id']);
            $response['success'] = true;
            $response['message'] = "Viatura recebida e integrada à frota local.";
        } else {
            // Se retornar false, pode ser ID inexistente ou tentativa de burlar o RBAC
            throw new Exception("Não foi possível confirmar. Verifique se a movimentação é destinada à sua unidade.");
        }
    } else {
        throw new Exception("Ação desconhecida.");
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (Throwable $t) {
    $response['message'] = "Erro crítico no servidor.";
}

echo json_encode($response);
exit;
