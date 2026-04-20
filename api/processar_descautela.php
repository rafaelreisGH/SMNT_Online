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
    $cautela = $cDAO->getCautelaById((int)$data['id']);

    if (!$cautela) throw new Exception("Cautela não encontrada.");
    if ($data['odometro_retorno'] < $cautela['odometro_saida']) throw new Exception("KM de retorno inválida.");

    $checklist = json_decode($data['checklist_json'], true);

    if ($cDAO->finalizarDescautela($data, $checklist)) {

        // 1. Processar ITENS FALTANTES (Gera Avaria Automática)
        foreach ($checklist as $item) {
            if ($item['status_retorno'] == 0) {
                $rDAO->inserir([
                    'viatura_id' => $cautela['viatura_id'],
                    'tipo_avaria_id' => 15, // ID da categoria Patrimonial
                    'condutor_id' => $cautela['condutor_id'],
                    'cautela_id' => $cautela['id'],
                    'data_registro' => date('Y-m-d H:i:s'),
                    'observacoes_avaria' => "EXTRAVIO: " . $item['nome'] . ". Justificativa: " . $item['justificativa'],
                    'requer_apuracao' => 1
                ]);
            }
        }

        // 2. Processar NOVOS DANOS com UPLOAD DE FOTOS
        if (!empty($data['novo_dano_obs'])) {
            $avId = $rDAO->inserir([
                'viatura_id' => $cautela['viatura_id'],
                'tipo_avaria_id' => null,
                'condutor_id' => $cautela['condutor_id'],
                'cautela_id' => $cautela['id'],
                'data_registro' => date('Y-m-d H:i:s'),
                'observacoes_avaria' => "NOVO DANO RELATADO: " . $data['novo_dano_obs'],
                'requer_apuracao' => 1
            ]);

            if ($avId && isset($_FILES['fotos'])) {
                $dir = "../assets/uploads/avarias/";
                foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp) {
                    if ($_FILES['fotos']['error'][$key] === UPLOAD_ERR_OK) {
                        $nomeArquivo = "av_" . $avId . "_" . uniqid() . "." . pathinfo($_FILES['fotos']['name'][$key], PATHINFO_EXTENSION);
                        if (move_uploaded_file($tmp, $dir . $nomeArquivo)) {
                            $rDAO->inserirFoto($avId, $nomeArquivo); // SALVA APENAS O NOME
                        }
                    }
                }
            }
        }

        LoggerService::registrar("Finalizou descautela da Vtr " . $cautela['prefixo'], "cautelas", $data['id']);
        $response['success'] = true;
        $response['message'] = "Recebimento concluído com sucesso!";
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

ob_clean();
echo json_encode($response);
