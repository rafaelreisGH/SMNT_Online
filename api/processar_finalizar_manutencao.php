<?php
// api/processar_finalizar_manutencao.php
header('Content-Type: application/json');
require_once 'middleware_auth.php';
require_once '../components/DAO/ManutencaoDAO.php';
require_once '../components/DAO/ApuracaoDAO.php'; // Necessário para a checagem
require_once '../components/Log/LoggerService.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;

    // 1. Validação de campos obrigatórios básicos
    if (empty($data['id']) || empty($data['data_saida']) || empty($data['servico_realizado']) || !isset($data['custo'])) {
        $response['message'] = "Data de saída, serviço realizado e custo são obrigatórios.";
    } else {
        try {
            $mDAO = new ManutencaoDAO();

            // --- INÍCIO DA TRAVA DE SEGURANÇA (REGRA DE OURO) ---

            // Busca os dados da manutenção para identificar a avaria vinculada
            $manutencao = $mDAO->getManutencaoById((int)$data['id']);

            if ($manutencao && !empty($manutencao['avaria_id'])) {
                $avariaId = $manutencao['avaria_id'];

                // Instanciamos o DAO de Apuração para verificar o status administrativo
                $apuracaoDAO = new ApuracaoDAO();
                $dadosApuracao = $apuracaoDAO->buscarPorAvariaId($avariaId);

                // Buscamos se a avaria requer apuração (está no RegistroAvariaDAO ou via JOIN aqui)
                // Usaremos uma consulta simples para verificar a flag requer_apuracao
                $db = Conexao::getConexao();
                $checkStmt = $db->prepare("SELECT requer_apuracao FROM registro_avaria WHERE id = :id");
                $checkStmt->execute([':id' => $avariaId]);
                $requerApuracao = $checkStmt->fetchColumn();

                // SE a gestão central exigiu apuração E não há número de portaria registrado
                if ($requerApuracao == 1 && (empty($dadosApuracao) || empty($dadosApuracao['numero_portaria']))) {
                    $response['message'] = "BLOQUEIO ADMINISTRATIVO: Esta manutenção está vinculada a uma avaria que exige Apuração Administrativa. Informe o número da Portaria na tela de Edição de Avaria antes de finalizar.";
                    echo json_encode($response);
                    exit;
                }
            }
            // --- FIM DA TRAVA DE SEGURANÇA ---

            // Tratamento do custo para o formato do banco (ex: 1.250,00 -> 1250.00)
            $data['custo'] = str_replace(['.', ','], ['', '.'], $data['custo']);

            if ($mDAO->finalizarManutencao($data)) {
                LoggerService::registrar("Finalizou manutenção ID " . $data['id'], "manutencoes", $data['id']);

                $response['success'] = true;
                $response['message'] = "Manutenção finalizada com sucesso! Viatura disponível.";
            } else {
                $response['message'] = "Erro ao finalizar ou acesso negado.";
            }
        } catch (Exception $e) {
            $response['message'] = "Erro: " . $e->getMessage();
        }
    }
}
echo json_encode($response);
