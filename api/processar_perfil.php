<?php
// api/processar_perfil.php
header('Content-Type: application/json');
require_once 'middleware_auth.php';
require_once '../components/ConexaoDB/conexao.php';
require_once '../components/Log/LoggerService.php';

$response = ['success' => false, 'message' => ''];
$userId = USER_AUTH['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Conexao::getConexao();

        // --- LÓGICA DE UPLOAD DA FOTO ---
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['foto_perfil'];
            $extensoesPermitidas = ['jpg', 'jpeg', 'png'];
            $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $tamanhoMax = 2 * 1024 * 1024; // 2MB

            if (!in_array($extensao, $extensoesPermitidas)) {
                throw new Exception("Extensão inválida. Use JPG ou PNG.");
            }
            if ($file['size'] > $tamanhoMax) {
                throw new Exception("A foto deve ter no máximo 2MB.");
            }

            // Nome único para evitar conflito
            $novoNome = "user_" . $userId . "_" . time() . "." . $extensao;
            $destino = "../assets/uploads/perfil/" . $novoNome;

            if (move_uploaded_file($file['tmp_name'], $destino)) {
                // Atualiza o banco com o novo nome da foto
                $sql = "UPDATE usuarios SET foto = :foto WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute([':foto' => $novoNome, ':id' => $userId]);

                LoggerService::registrar("Atualizou foto de perfil", "usuarios", $userId);
                $response['success'] = true;
                $response['message'] = "Foto atualizada!";
                $response['nova_foto'] = $novoNome;
            }
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
