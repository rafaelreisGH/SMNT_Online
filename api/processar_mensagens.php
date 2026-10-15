<?php
// api/processar_mensagens.php
header('Content-Type: application/json');
require_once 'middleware_auth.php';
require_once '../components/DAO/MensagemDAO.php';

$dao = new MensagemDAO();
$userId = USER_AUTH['id'];
$acao = $_GET['acao'] ?? '';
$response = ['success' => false];

try {
    switch ($acao) {
        case 'enviar':
            $data = $_POST;
            $data['remetente_id'] = $userId;
            $unidadeId = !empty($_POST['unidade_id']) ? $_POST['unidade_id'] : null;
            $response['success'] = $dao->enviar($data, $unidadeId);
            $response['message'] = "Mensagem enviada!";
            break;

        case 'listar':
            $filtros = [
                'pasta'       => $_GET['pasta'] ?? 'entrada',
                'termo'       => $_GET['termo'] ?? '',
                'viatura_id'  => $_GET['viatura_id'] ?? '',
                'data_inicio' => $_GET['data_inicio'] ?? '',
                'data_fim'    => $_GET['data_fim'] ?? '',
                'lida'        => isset($_GET['lida']) ? $_GET['lida'] : null
            ];
            $response['mensagens'] = $dao->buscar($userId, $filtros);
            $response['success'] = true;
            break;

        case 'ler':
            $response['success'] = $dao->marcarLida((int)$_POST['id'], $userId);
            break;

        case 'favoritar':
            $response['success'] = $dao->favoritar((int)$_POST['id'], $userId, $_POST['status']);
            break;

        case 'editar':
            if ($dao->editar((int)$_POST['id'], $userId, $_POST['conteudo'])) {
                $response['success'] = true;
            } else {
                $response['message'] = "Tempo para edição expirado (10 min).";
            }
            break;

        case 'excluir':
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                $response['message'] = "ID da mensagem não fornecido.";
            } else {
                // O método excluir agora retorna true apenas se alterou o registro
                if ($dao->excluir($id, $userId)) {
                    $response['success'] = true;
                    $response['message'] = "Mensagem removida da sua caixa.";
                } else {
                    $response['message'] = "Não foi possível excluir. A mensagem pode já ter sido apagada.";
                }
            }
            break;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
