<?php
// api/processar_cautela.php
ob_start();
header('Content-Type: application/json');
ini_set('display_errors', 0); // Desativa avisos para não "sujar" o JSON

require_once 'middleware_auth.php';
require_once '../components/DAO/CautelaDAO.php';
require_once '../components/DAO/CondutorDAO.php';
require_once '../components/Log/LoggerService.php';

$response = ['success' => false, 'message' => ''];
$data = $_POST;

// 1. RBAC: Perfis que podem MANIPULAR cautelas (Criar, Editar, Cancelar)
$perfisGestores = ['Administrador', 'Gestor Central', 'Gestor Regional', 'Gestor Local'];
$acao = $_GET['acao'] ?? '';

// Regra Especial: O Condutor SÓ pode acessar se a ação for 'assinar'
if (USER_AUTH['perfil'] === 'Condutor' && $acao !== 'assinar') {
    die(json_encode(['success' => false, 'message' => 'Acesso negado: Condutores não podem criar ou cancelar cautelas.']));
}

// Regra Geral: Perfil 'Consulta' não faz nada
if (USER_AUTH['perfil'] === 'Consulta') {
    die(json_encode(['success' => false, 'message' => 'Acesso negado: Perfil apenas de leitura.']));
}

try {
    $dao = new CautelaDAO();

    if (USER_AUTH['perfil'] === 'Condutor' && $acao !== 'assinar') {
        throw new Exception("Acesso Negado: Condutores não podem criar, editar ou cancelar cautelas.");
    }

    // AÇÃO A: CANCELAR
    if ($acao === 'cancelar') {
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) throw new Exception("ID inválido.");
        if ($dao->atualizarStatus($id, 'Cancelada')) {
            LoggerService::registrar("Cancelou cautela ID $id", "cautelas", $id);
            $response['success'] = true;
            $response['message'] = "Cautela cancelada com sucesso!";
        }
    }
    // AÇÃO B: EDITAR
    elseif ($acao === 'editar') {
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) throw new Exception("ID inválido para edição.");

        if (empty($data['viatura_id']) || empty($data['condutor_id'])) {
            throw new Exception("Viatura e Condutor são obrigatórios.");
        }

        // VALIDAÇÃO DO ODÔMETRO (Trava de Negativos)
        if (!isset($data['odometro_saida']) || !is_numeric($data['odometro_saida']) || $data['odometro_saida'] < 0) {
            throw new Exception("O odômetro de saída não pode ser negativo.");
        }

        $checklist = json_decode($data['checklist_json'] ?? '[]', true);
        if (empty($checklist)) throw new Exception("Checklist não recebido.");

        $data['status'] = $data['status'] ?? 'Pendente';

        if ($dao->atualizarCautela($data, $checklist)) {
            LoggerService::registrar("Editou rascunho ID $id", "cautelas", $id);
            $response['success'] = true;
            $response['message'] = "Alterações salvas com sucesso!";
        } else {
            throw new Exception("Nenhuma alteração feita ou cautela já foi assinada.");
        }
    } elseif ($acao === 'assinar') {
        $idCautela = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        $senha = $data['senha_assinatura'] ?? '';

        if (!$idCautela || empty($senha)) {
            throw new Exception("Dados insuficientes para assinatura.");
        }

        $dao = new CautelaDAO();
        // 1. Busca os dados da cautela para conferir o condutor
        $cautela = $dao->getCautelaById($idCautela);

        if (!$cautela) throw new Exception("Cautela não encontrada.");

        // 2. VERIFICAÇÃO RBAC: O usuário logado é o condutor da cautela?
        // Buscamos o usuario_id vinculado ao condutor daquela cautela
        $db = Conexao::getConexao();
        $stmt = $db->prepare("SELECT usuario_id FROM condutor WHERE id = :c_id");
        $stmt->execute([':c_id' => $cautela['condutor_id']]);
        $idUsuarioResponsavel = $stmt->fetchColumn();

        if (USER_AUTH['id'] != $idUsuarioResponsavel) {
            throw new Exception("Acesso Negado: Apenas o condutor designado pode assinar esta cautela.");
        }

        // 3. VALIDAÇÃO DA SENHA (Assinatura Digital)
        $stmtUser = $db->prepare("SELECT senha_hash FROM usuarios WHERE id = :u_id");
        $stmtUser->execute([':u_id' => USER_AUTH['id']]);
        $hash = $stmtUser->fetchColumn();

        if (!password_verify($senha, $hash)) {
            throw new Exception("Senha de assinatura incorreta.");
        }

        // 4. EXECUTA A ASSINATURA
        if ($dao->assinarCautela($idCautela, USER_AUTH['id'])) {
            LoggerService::registrar("Condutor assinou a cautela ID: $idCautela", "cautelas", $idCautela);
            $response['success'] = true;
            $response['message'] = "Cautela assinada e ativa! Boa jornada.";
        } else {
            throw new Exception("Não foi possível assinar. Verifique se a cautela ainda está disponível.");
        }
    }
    // AÇÃO C: CRIAR (Novo Registro)
    else {
        if (empty($data['viatura_id']) || empty($data['condutor_id']) || empty($data['odometro_saida'])) {
            throw new Exception("Preencha todos os campos obrigatórios.");
        }

        // VALIDAÇÃO DO ODÔMETRO (Trava de Negativos)
        if (!is_numeric($data['odometro_saida']) || $data['odometro_saida'] < 0) {
            throw new Exception("O odômetro de saída não pode ser negativo.");
        }

        $checklist = json_decode($data['checklist_json'] ?? '[]', true);
        if (empty($checklist)) throw new Exception("Erro: Checklist obrigatório.");

        $idCautela = $dao->abrirCautela($data, $checklist);

        if ($idCautela) {
            LoggerService::registrar("Criou cautela ID $idCautela", "cautelas", $idCautela);
            $response['success'] = true;
            $response['message'] = "Cautela registrada com sucesso!";
        }
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

ob_clean(); // Descarta qualquer lixo (Warnings/Notices) antes do JSON
echo json_encode($response);
exit;
