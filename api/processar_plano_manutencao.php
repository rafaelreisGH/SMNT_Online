<?php
// api/processar_plano_manutencao.php
header('Content-Type: application/json');
require_once 'middleware_auth.php'; // JWT Check
require_once '../components/DAO/PlanoManutencaoDAO.php';

$response = ['success' => false, 'message' => ''];

// Restrição de Perfil
if (!in_array(USER_AUTH['perfil'], ['Administrador', 'Gestor Central'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Apenas gestores centrais podem criar planos.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = $_POST['descricao'] ?? '';
    $regrasRaw = $_POST['regras'] ?? []; // Esperamos um array de objetos

    if (empty($descricao) || empty($regrasRaw)) {
        $response['message'] = "Informe a descrição e pelo menos uma regra.";
    } else {
        try {
            $dao = new PlanoManutencaoDAO();
            if ($dao->inserirPlanoComRegras($descricao, $regrasRaw)) {
                $response['success'] = true;
                $response['message'] = "Plano de Manutenção criado com sucesso!";
            }
        } catch (Exception $e) {
            $response['message'] = "Erro: " . $e->getMessage();
        }
    }
}

echo json_encode($response);