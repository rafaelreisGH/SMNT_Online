<?php
// api/processar_cadastro_viatura.php
ob_start(); // Inicia o buffer
header('Content-Type: application/json');

require_once 'middleware_auth.php';
require_once '../components/DAO/ViaturaDAO.php';
require_once 'validate_functions_editar_viatura.php';
require_once '../components/Log/LoggerService.php';

$response = ['success' => false, 'message' => '', 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_from_form = $_POST;
    $validation_errors = validateViaturaData($data_from_form);

    if (!empty($validation_errors)) {
        $response['message'] = "Houve erros no preenchimento do formulário.";
        $response['errors'] = $validation_errors;
    } else {
        try {
            $viaturaDAO = new ViaturaDAO();
            if ($viaturaDAO->insertViatura($data_from_form)) {
                LoggerService::registrar("Cadastrou viatura " . $data_from_form['prefixo'], "viaturas");
                $response['success'] = true;
                $response['message'] = "Viatura cadastrada com sucesso!";
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
    }
}

ob_clean(); // Limpa qualquer lixo de saída (Warnings/Notices)
echo json_encode($response);
exit;
