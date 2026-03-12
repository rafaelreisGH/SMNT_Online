<?php
// api/processar_registro_avaria.php
header('Content-Type: application/json');
require_once '../components/DAO/RegistroAvariaDAO.php';

$response = ['success' => false, 'message' => '', 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;

    // Validação Básica
    if (empty($data['viatura_id'])) $response['errors']['viatura_id'] = "Selecione a viatura.";
    if (empty($data['condutor_id'])) $response['errors']['condutor_id'] = "Selecione o condutor.";
    if (empty($data['tipo_avaria_id'])) $response['errors']['tipo_avaria_id'] = "Selecione o componente afetado.";
    if (empty($data['data_registro'])) $response['errors']['data_registro'] = "Informe a data do ocorrido.";

    if (empty($response['errors'])) {
        try {
            $dao = new RegistroAvariaDAO();
            if ($dao->inserir($data)) {
                $response['success'] = true;
                $response['message'] = "Avaria registrada com sucesso! A viatura agora pode ser encaminhada para manutenção.";
            }
        } catch (Exception $e) {
            $response['message'] = "Erro ao salvar: " . $e->getMessage();
        }
    } else {
        $response['message'] = "Preencha os campos obrigatórios.";
    }
}

echo json_encode($response);