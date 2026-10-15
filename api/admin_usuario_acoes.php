<?php
// api/admin_usuario_acoes.php
header('Content-Type: application/json');
require_once 'middleware_auth.php';
require_once '../components/DAO/UsuarioDAO.php';
require_once '../components/Log/LoggerService.php';

// Proteção RBAC: Apenas Admin (1) ou Gestor Central (2) acessam esta API
if (!in_array(USER_AUTH['perfil_id'], [1, 2])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

$dao = new UsuarioDAO();
$acao = $_GET['acao'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    switch ($acao) {
        case 'status':
            $id = $_POST['id'];
            $status = $_POST['status']; // 0 ou 1
            if ($dao->alternarStatus($id, $status)) {
                $msg = $status ? "Ativou" : "Bloqueou";
                LoggerService::registrar("$msg acesso do usuário ID $id", "usuarios", $id);
                $response = ['success' => true, 'message' => "Usuário atualizado!"];
            }
            break;

        case 'vincular':
            // Apenas Admin (ID 1) pode trocar o PERFIL. Gestor Central só troca a UNIDADE.
            $perfilId = (USER_AUTH['perfil_id'] == 1) ? $_POST['perfil_id'] : null;
            $unidadeId = $_POST['unidade_id'];
            $idAlvo = $_POST['id'];

            if ($dao->atualizarVinculosAdmin($idAlvo, $perfilId, $unidadeId)) {
                LoggerService::registrar("Alterou lotação/perfil do usuário ID $idAlvo", "usuarios", $idAlvo);
                $response = ['success' => true, 'message' => "Vínculos atualizados com sucesso!"];
            }
            break;

        case 'reset-senha':
            $id = $_POST['id'];
            $novaSenha = $_POST['senha'];
            if (strlen($novaSenha) < 6) throw new Exception("Mínimo de 6 caracteres.");

            if ($dao->resetarSenha($id, password_hash($novaSenha, PASSWORD_DEFAULT))) {
                LoggerService::registrar("Resetou senha do usuário ID $id", "usuarios", $id);
                $response = ['success' => true, 'message' => "Senha resetada. O usuário deverá trocá-la no próximo acesso."];
            }
            break;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
