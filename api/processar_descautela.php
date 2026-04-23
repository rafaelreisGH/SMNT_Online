<?php
// api/processar_descautela.php
header('Content-Type: application/json');
ob_start();

require_once 'middleware_auth.php';
require_once '../components/DAO/CautelaDAO.php';
require_once '../components/DAO/ViaturaDAO.php'; // Adicionado para validação RBAC
require_once '../components/DAO/RegistroAvariaDAO.php';
require_once '../components/Log/LoggerService.php';

$response = ['success' => false, 'message' => ''];
$data = $_POST;

try {
    $cDAO = new CautelaDAO();
    $rDAO = new RegistroAvariaDAO();
    $vDAO = new ViaturaDAO();

    // 1. VALIDAÇÃO DE SEGURANÇA (RBAC)
    // O getViaturaById já aplica o escopo por unidade no SQL
    $viatura = $vDAO->getViaturaById((int)$data['viatura_id']);
    if (!$viatura) {
        throw new Exception("Acesso Negado ou Viatura não encontrada para esta unidade.");
    }

    $cautela = $cDAO->getCautelaById((int)$data['id']);
    if (!$cautela) throw new Exception("Cautela não encontrada.");

    // 2. VALIDAÇÃO DE ODÔMETRO
    if ($data['odometro_retorno'] < $cautela['odometro_saida']) {
        throw new Exception("Erro: O odômetro de retorno (" . $data['odometro_retorno'] . ") é menor que o de saída (" . $cautela['odometro_saida'] . ").");
    }

    $checklist = json_decode($data['checklist_json'] ?? '[]', true);
    $observacoes_avaria = "";
    $houve_extravio = false;

    foreach ($checklist as $item) {
        if ($item['status_retorno'] == 0) {
            $houve_extravio = true;
            $nome = $item['nome'] ?? 'Acessório';
            $just = $item['justificativa'] ?? 'Não informada';
            $observacoes_avaria .= "ALTERAÇÃO: $nome | Justificativa: $just\n";
        }
    }

    // Adiciona observações gerais do textarea de extravio, se houver
    if ($houve_extravio && !empty($data['obs_extravio'])) {
        $observacoes_avaria .= "OBSERVAÇÕES GERAIS: " . $data['obs_extravio'] . "\n";
    }

    // 4. Executa a finalização da cautela
    if ($cDAO->finalizarDescautela($data, $checklist)) {

        $avId = null;
        // Se houve extravio, cria UM registro de avaria automático (Tipo 15)
        if ($houve_extravio) {
            $avId = $rDAO->inserir([
                'viatura_id'         => $cautela['viatura_id'],
                'tipo_avaria_id'     => 15, // FIXO: ACESSÓRIOS
                'condutor_id'        => $cautela['condutor_id'],
                'cautela_id'         => $cautela['id'],
                'data_registro'      => date('Y-m-d H:i:s'),
                'observacoes_avaria' => $observacoes_avaria,
                'requer_apuracao'    => 1
            ]);
        }

        // 5. Processamento de fotos de extravio
        if ($avId && isset($_FILES['fotos_extravio'])) {
            $dir = "../assets/uploads/avarias/";
            foreach ($_FILES['fotos_extravio']['tmp_name'] as $key => $tmp) {
                if ($key >= 5) break;
                if ($_FILES['fotos_extravio']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['fotos_extravio']['name'][$key], PATHINFO_EXTENSION);
                    $nomeArq = "ext_" . $avId . "_" . time() . "_" . uniqid() . "." . $ext;
                    if (move_uploaded_file($tmp, $dir . $nomeArq)) {
                        $rDAO->inserirFoto($avId, $nomeArq);
                    }
                }
            }
        }

        LoggerService::registrar("Descautela concluída (Vtr " . $cautela['prefixo'] . ")", "cautelas", $data['id']);
        $response['success'] = true;
        $response['message'] = "Recebimento finalizado!";
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

ob_clean();
echo json_encode($response);
exit;
