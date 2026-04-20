<?php
// api/processar_descautela.php
header('Content-Type: application/json');
ob_start();
require_once 'middleware_auth.php';
require_once '../components/DAO/CautelaDAO.php';
require_once '../components/DAO/RegistroAvariaDAO.php';
require_once '../components/Log/LoggerService.php';

$response = ['success' => false, 'message' => ''];
$data = $_POST;

try {
    $cDAO = new CautelaDAO();
    $rDAO = new RegistroAvariaDAO();

    // 1. Validação de KM
    $cautela = $cDAO->getCautelaById($data['id']);
    if ($data['odometro_retorno'] < $cautela['odometro_saida']) {
        throw new Exception("Odômetro de retorno não pode ser inferior ao de saída.");
    }

    $checklist = json_decode($data['checklist_json'], true);

    // 2. Processa a finalização
    if ($cDAO->finalizarDescautela($data, $checklist)) {

        // 3. SE HOUVER ITENS FALTANTES -> GERA AVARIA AUTOMÁTICA
        foreach ($checklist as $item) {
            if ($item['status_retorno'] == 0) {
                $rDAO->inserir([
                    'viatura_id' => $cautela['viatura_id'],
                    'tipo_avaria_id' => 15, // Ajuste para o ID de "Acessórios/Perdas"
                    'condutor_id' => $cautela['condutor_id'],
                    'cautela_id' => $cautela['id'],
                    'data_registro' => date('Y-m-d H:i:s'),
                    'observacoes_avaria' => "ITEM EXTRAVIADO NA CAUTELA #" . $cautela['id'] . ": " . $item['nome'] . ". Justificativa: " . $item['justificativa'],
                    'requer_apuracao' => 1
                ]);
            }
        }

        // 4. SE HOUVER NOVO DANO RELATADO (Upload de fotos ficaria aqui)
        if (!empty($data['novo_dano_desc'])) {
            // Lógica para criar avaria com fotos...
        }

        LoggerService::registrar("Finalizou descautela ID " . $data['id'], "cautelas", $data['id']);
        $response['success'] = true;
        $response['message'] = "Viatura recebida e integrada à frota!";
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

ob_clean();
echo json_encode($response);
