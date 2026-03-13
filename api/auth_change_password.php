<?php
// api/auth_change_password.php
header('Content-Type: application/json');
require_once 'middleware_auth.php'; // Protegido!
require_once '../components/DAO/UsuarioDAO.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if (strlen($novaSenha) < 6) {
        $response['message'] = "A senha deve ter pelo menos 6 caracteres.";
    } elseif ($novaSenha !== $confirmarSenha) {
        $response['message'] = "As senhas não conferem.";
    } else {
        try {
            $userDAO = new UsuarioDAO();
            // USER_AUTH vem do middleware_auth.php (dados extraídos do JWT)
            if ($userDAO->atualizarSenha(USER_AUTH['id'], $novaSenha)) {
                $response['success'] = true;
                $response['message'] = "Senha atualizada com sucesso!";
            }
        } catch (Exception $e) {
            $response['message'] = "Erro ao atualizar senha.";
        }
    }
}
echo json_encode($response);