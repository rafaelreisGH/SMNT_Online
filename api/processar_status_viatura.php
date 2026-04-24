<?php
// api/processar_status_viatura.php
ob_start(); // Inicia o buffer para evitar sujeira no JSON
header('Content-Type: application/json');

// Desativa exibição de erros na tela
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once 'middleware_auth.php';

    // --- BLOQUEIO DE SEGURANÇA (RBAC) ---
    // Apenas Administradores e Gestores podem alterar o status operacional
    $perfisAutorizados = ['Administrador', 'Gestor Central', 'Gestor Regional', 'Gestor Local'];

    if (!in_array(USER_AUTH['perfil'], $perfisAutorizados)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Acesso Negado: Seu perfil não tem permissão para alterar o status de viaturas.'
        ]);
        exit;
    }
    // ------------------------------------

    require_once '../components/DAO/ViaturaDAO.php';
    require_once '../components/Log/LoggerService.php';

    $response = ['success' => false, 'message' => ''];
    $conn = Conexao::getConexao();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método não permitido.");
    }

    $id_viatura = filter_var($_POST['id_viatura'] ?? null, FILTER_VALIDATE_INT);
    $acao = $_GET['acao'] ?? ''; // 'baixar' ou 'reativar'

    if (!$id_viatura) {
        throw new Exception("ID da viatura inválido.");
    }

    $vDAO = new ViaturaDAO();
    $viatura = $vDAO->getViaturaById($id_viatura);

    if (!$viatura) {
        throw new Exception("Viatura não encontrada ou você não tem permissão.");
    }

    // Define novo status
    $novoStatus = ($acao === 'baixar') ? 'Baixada' : 'Operante';
    $termoLog = ($acao === 'baixar') ? "Baixou" : "Reativou";

    // Executa o Update
    $stmt = $conn->prepare("UPDATE viaturas SET status_da_viatura = :status WHERE id = :id");
    $stmt->bindValue(':status', $novoStatus);
    $stmt->bindValue(':id', $id_viatura, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Registra no Log (Monolog)
        LoggerService::registrar(
            "$termoLog viatura {$viatura['prefixo']} {$viatura['numero']}",
            "viaturas",
            $id_viatura,
            "De: {$viatura['status_da_viatura']} Para: $novoStatus"
        );

        $response['success'] = true;
        $response['message'] = "Viatura atualizada para $novoStatus!";
    } else {
        throw new Exception("Erro ao atualizar banco de dados.");
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

ob_clean(); // Limpa qualquer Warning/Notice que tenha saído antes
echo json_encode($response);
exit;
