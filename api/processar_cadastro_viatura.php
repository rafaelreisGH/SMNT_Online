<?php
// api/processar_cadastro_viatura.php
header('Content-Type: application/json');

// O Middleware deve vir primeiro para validar o Token
require_once 'middleware_auth.php'; 
require_once '../components/DAO/ViaturaDAO.php';
require_once 'validate_functions_editar_viatura.php';

$response = ['success' => false, 'message' => '', 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_from_form = $_POST;

    // Realiza a validação usando a sua função externa
    $validation_errors = validateViaturaData($data_from_form);

    // No cadastro, o ID não é necessário
    unset($validation_errors['id']);
    
    // Remove campos que não pertencem ao schema da tabela conforme sua regra
    unset($data_from_form['tipo_carroceria']);
    unset($data_from_form['equipamentos_especificos']);

    if (!empty($validation_errors)) {
        $response['message'] = "Houve erros no preenchimento do formulário. Por favor, corrija os campos indicados.";
        $response['errors'] = $validation_errors;
    } else {
        try {
            $viaturaDAO = new ViaturaDAO();
            // Garante que o ID não seja enviado para o insert (AUTO_INCREMENT)
            unset($data_from_form['id']);

            if ($viaturaDAO->insertViatura($data_from_form)) {
                $response['success'] = true;
                $response['message'] = "Viatura cadastrada com sucesso!";
            } else {
                $response['message'] = "Erro ao cadastrar a viatura no banco de dados.";
            }
        } catch (Exception $e) {
            error_log("Erro no cadastro: " . $e->getMessage());
            $response['message'] = "Erro interno: " . $e->getMessage();
        }
    }
} else {
    $response['message'] = "Método de requisição inválido.";
}

echo json_encode($response);
exit();