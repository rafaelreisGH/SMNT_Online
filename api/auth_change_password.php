<?php
// api/auth_change_password.php
header('Content-Type: application/json');

require_once 'middleware_auth.php';
require_once '../components/DAO/UsuarioDAO.php';
require_once '../components/Log/LoggerService.php';

$response = ['success' => false, 'message' => '', 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';
    $userId = USER_AUTH['id'];

    if (strlen($novaSenha) < 6) {
        $response['errors']['nova_senha'] = "A senha deve ter no mínimo 6 caracteres.";
    }

    if ($novaSenha !== $confirmarSenha) {
        $response['errors']['confirmar_senha'] = "As senhas não conferem.";
    }

    if (empty($response['errors'])) {
        try {
            $uDAO = new UsuarioDAO();

            // BUSCA A SENHA ATUAL PARA COMPARAR
            $dadosUsuario = $uDAO->buscarDadosCompletos($userId);

            if (password_verify($novaSenha, $dadosUsuario['senha_hash'])) {
                $response['message'] = "A nova senha não pode ser igual à senha atual.";
                $response['errors']['nova_senha'] = "Escolha uma senha diferente da anterior.";
            } else {
                if ($uDAO->atualizarSenha($userId, $novaSenha)) {
                    LoggerService::registrar("Alterou a própria senha", "usuarios", $userId);
                    $response['success'] = true;
                    $response['message'] = "Senha atualizada com sucesso!";
                }
            }
        } catch (Exception $e) {
            $response['message'] = "Erro: " . $e->getMessage();
        }
    } else {
        $response['message'] = "Verifique os erros apontados.";
    }
}
echo json_encode($response);
exit;
