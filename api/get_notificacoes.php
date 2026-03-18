<?php
// api/get_notificacoes.php
header('Content-Type: application/json');

// Desativa exibição de erros na tela para não corromper o JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once 'middleware_auth.php'; // Define USER_AUTH
    require_once '../components/DAO/NotificacaoDAO.php';

    $dao = new NotificacaoDAO();
    $usuarioId = USER_AUTH['id'];

    // --- LÓGICA 1: MARCAR COMO LIDA (POST) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id) {
            $dao->marcarComoLida($id, $usuarioId);
            echo json_encode(['success' => true, 'message' => 'Lida']);
            exit; // PARA A EXECUÇÃO AQUI
        }
        echo json_encode(['success' => false, 'message' => 'ID invalido']);
        exit;
    }

    // --- LÓGICA 2: LISTAR NOTIFICAÇÕES (GET) ---
    // Se a URL tiver ?todas=true, chama o método de histórico
    if (isset($_GET['todas']) && $_GET['todas'] === 'true') {
        $lista = $dao->listarTodasPorUsuario($usuarioId);
    } else {
        // Caso contrário, apenas as novas para o sino
        $lista = $dao->listarNaoLidasPorUsuario($usuarioId);
    }

    // Retorna o JSON limpo
    echo json_encode([
        'success' => true,
        'total' => count($lista),
        'notificacoes' => $lista
    ]);
    exit; // PARA A EXECUÇÃO AQUI

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno na API',
        'debug' => $e->getMessage()
    ]);
    exit;
}