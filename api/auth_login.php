<?php
// api/auth_login.php
header('Content-Type: application/json');
require_once '../components/ConexaoDB/conexao.php';
require_once '../components/Auth/JwtHandler.php';
require_once '../components/Log/LoggerService.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($cpf) || empty($senha)) {
        $response['message'] = "Preencha todos os campos.";
    } else {
        try {
            $db = Conexao::getConexao();
            $sql = "SELECT u.id, u.nome, u.senha_hash, u.unidade_id, u.trocar_senha, u.foto,
                           p.id as perfil_id, p.nome as perfil_nome,
                           un.comando_regional_id
                    FROM usuarios u
                    JOIN usuario_perfis up ON u.id = up.usuario_id
                    JOIN perfis p ON up.perfil_id = p.id
                    JOIN unidades un ON u.unidade_id = un.id
                    WHERE u.cpf = :cpf AND u.ativo = 1";

            $stmt = $db->prepare($sql);
            $stmt->execute([':cpf' => $cpf]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (password_verify($senha, $user['senha_hash'])) {
                    $jwt = new JwtHandler();
                    $token = $jwt->gerarToken($user);

                    LoggerService::registrar("Realizou login no sistema", "usuarios", $user['id']);

                    // --- Registrar o último login do usuário ---
                    $db->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id")
                        ->execute([':id' => $user['id']]);
                    // ---------------------------------

                    $response['success'] = true;
                    $response['token'] = $token;
                    // Informa ao frontend se o usuário deve ser enviado para trocar senha
                    $response['require_new_password'] = (bool)$user['trocar_senha'];
                    $response['user'] = [
                        'nome' => $user['nome'],
                        'perfil' => $user['perfil_nome'],
                        'foto' => $user['foto']
                    ];
                } else {
                    error_log("Falha de senha para o CPF: " . $cpf);
                    $response['message'] = "Senha incorreta.";
                }
            } else {
                error_log("CPF nao encontrado ou inativo: " . $cpf);
                $response['message'] = "Usuário não encontrado.";
            }
        } catch (Exception $e) {
            $response['message'] = "Erro no servidor: " . $e->getMessage();
        }
    }
} else {
    $response['message'] = "Método não permitido.";
}

echo json_encode($response);
exit;
