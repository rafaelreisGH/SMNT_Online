<?php
// pages/editar_avaria.php

require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/ViaturaDAO.php';
require_once __DIR__ . '/../components/DAO/CondutorDAO.php';
require_once __DIR__ . '/../components/DAO/RegistroAvariaDAO.php';
require_once __DIR__ . '/../components/DAO/ApuracaoDAO.php'; // NOVO
require_once __DIR__ . '/../components/Log/LoggerService.php'; // Para logs

$vDAO = new ViaturaDAO();
$cDAO = new CondutorDAO();
$aDAO = new RegistroAvariaDAO();
$apuracaoDAO = new ApuracaoDAO();

$perfilUsuario = USER_AUTH['perfil'];
$ehGestorMaster = in_array($perfilUsuario, ['Administrador', 'Gestor Central']);

// --- Lógica para processar a atualização (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => '', 'errors' => []];
    $data = $_POST;

    // Validação básica da Avaria
    if (empty($data['viatura_id'])) $response['errors']['viatura_id'] = "Obrigatório.";
    if (empty($data['condutor_id'])) $response['errors']['condutor_id'] = "Obrigatório.";

    if (empty($response['errors'])) {
        try {
            $db = Conexao::getConexao();
            $db->beginTransaction();

            // 1. Atualiza os dados principais da Avaria (incluindo o checkbox requer_apuracao)
            // Se o usuário não for master, mantemos o valor que já estava no banco
            if (!$ehGestorMaster) {
                $avariaAtual = $aDAO->buscarDetalhesPorId($data['id']);
                $data['requer_apuracao'] = $avariaAtual['requer_apuracao'];
            } else {
                $data['requer_apuracao'] = isset($data['requer_apuracao']) ? 1 : 0;
            }

            $sucessoAvaria = $aDAO->atualizar($data);

            // 2. Se a avaria requer apuração ou já possui dados de apuração enviados
            $sucessoApuracao = true;
            if (isset($data['numero_portaria']) || isset($data['encarregado'])) {
                $data['avaria_id'] = $data['id']; // Mapeia para o DAO de apuração
                $sucessoApuracao = $apuracaoDAO->salvar($data);
            }

            if ($sucessoAvaria || $sucessoApuracao) {
                $db->commit();
                LoggerService::registrar("Atualizou avaria e dados de apuração", "registro_avaria", $data['id']);
                $response['success'] = true;
                $response['message'] = "Dados atualizados com sucesso!";
            } else {
                $db->rollBack();
                $response['message'] = "Nenhuma alteração detectada.";
            }
        } catch (Exception $e) {
            if (isset($db)) $db->rollBack();
            $response['message'] = "Erro: " . $e->getMessage();
        }
    } else {
        $response['message'] = "Verifique os campos obrigatórios.";
    }

    echo json_encode($response);
    exit();
}

// --- Lógica para carregar os dados (GET) ---
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    echo "<div class='container-fluid'><div class='alert alert-danger'>ID inválido.</div></div>";
    exit();
}

$display_data = $aDAO->buscarDetalhesPorId($id);
$dados_apuracao = $apuracaoDAO->buscarPorAvariaId($id);

if (!$display_data) {
    echo "<div class='container-fluid'><div class='alert alert-warning'>Registro não encontrado.</div></div>";
    exit();
}

$viaturas = $vDAO->listarTodasParaSelect();
$condutores = $cDAO->listarAtivos();
$tiposAvaria = $aDAO->listarTiposAvaria();
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Editar Registro de Avaria</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form id="formEditarAvaria" novalidate>
                <input type="hidden" name="id" value="<?= $display_data['id'] ?>">

                <!-- Card da Avaria -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Dados do Problema Técnico</h6>
                        <?php if ($ehGestorMaster): ?>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="requer_apuracao" name="requer_apuracao" value="1" <?= ($display_data['requer_apuracao'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold text-danger" for="requer_apuracao">Exigir Apuração Administrativa</label>
                            </div>
                        <?php else: ?>
                            <?php if ($display_data['requer_apuracao']): ?>
                                <span class="badge bg-danger p-2"><i class="fas fa-gavel me-1"></i> Apuração Exigida pela Gestão Central</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Viatura</label>
                                <select class="form-select" name="viatura_id" <?= !$ehGestorMaster ? 'disabled' : '' ?>>
                                    <?php foreach ($viaturas as $v): ?>
                                        <option value="<?= $v['id'] ?>" <?= ($v['id'] == $display_data['viatura_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($v['prefixo'] . ' ' . $v['numero']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Condutor Relatante</label>
                                <select class="form-select" name="condutor_id">
                                    <?php foreach ($condutores as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= ($c['id'] == $display_data['condutor_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['nome'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Componente Afetado</label>
                                <select class="form-select" name="tipo_avaria_id">
                                    <?php
                                    $catAtual = "";
                                    foreach ($tiposAvaria as $t):
                                        if ($catAtual != $t['categoria']):
                                            if ($catAtual != "") echo "</optgroup>";
                                            $catAtual = $t['categoria'];
                                            echo "<optgroup label='" . htmlspecialchars($catAtual) . "'>";
                                        endif;
                                    ?>
                                        <option value="<?= $t['id'] ?>" <?= ($t['id'] == $display_data['tipo_avaria_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['nome_componente'] ?? '') ?>
                                        </option>
                                    <?php endforeach; echo "</optgroup>"; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Data do Relato</label>
                                <input type="datetime-local" class="form-control" name="data_registro" value="<?= date('Y-m-d\TH:i', strtotime($display_data['data_registro'])) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Descrição</label>
                                <textarea class="form-control" name="observacoes_avaria" rows="3"><?= htmlspecialchars($display_data['observacoes_avaria'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção de Apuração (Visível apenas se requer_apuracao for 1) -->
                <div class="card shadow mb-4 <?= ($display_data['requer_apuracao'] == 0) ? 'd-none' : '' ?>" id="card_apuracao">
                    <div class="card-header py-3 bg-dark">
                        <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-gavel me-2"></i>Procedimento Administrativo de Apuração</h6>
                    </div>
                    <div class="card-body border-left-dark">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Nº da Portaria</label>
                                <input type="text" class="form-control" name="numero_portaria" value="<?= htmlspecialchars($dados_apuracao['numero_portaria'] ?? '') ?>" placeholder="Ex: 001/2026-UBM">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Data de Abertura</label>
                                <input type="date" class="form-control" name="data_abertura" value="<?= $dados_apuracao['data_abertura'] ?? '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Militar Encarregado</label>
                                <input type="text" class="form-control" name="encarregado" value="<?= htmlspecialchars($dados_apuracao['encarregado'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Status da Apuração</label>
                                <select class="form-select" name="status_apuracao">
                                    <option value="Em andamento" <?= ($dados_apuracao['status_apuracao'] ?? '') == 'Em andamento' ? 'selected' : '' ?>>Em andamento</option>
                                    <option value="Concluído" <?= ($dados_apuracao['status_apuracao'] ?? '') == 'Concluído' ? 'selected' : '' ?>>Concluído</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Resultado / Conclusão</label>
                                <textarea class="form-control" name="resultado" rows="2"><?= htmlspecialchars($dados_apuracao['resultado'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-5">
                    <button type="submit" class="btn btn-outline-success px-4" id="btnSalvar">
                        <i class="fas fa-save me-2"></i>Salvar Tudo
                    </button>
                    <a href="pages/pesquisar_avaria.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar para Pesquisa
                    </a>
                </div>
            </form>
        </div>
    </div>
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
</div>

<script>
    (function() {
        const form = document.getElementById('formEditarAvaria');
        const checkApuracao = document.getElementById('requer_apuracao');
        const cardApuracao = document.getElementById('card_apuracao');

        // Lógica para mostrar/esconder campos de apuração dinamicamente (para Admin)
        if (checkApuracao) {
            checkApuracao.addEventListener('change', function() {
                if (this.checked) {
                    cardApuracao.classList.remove('d-none');
                } else {
                    cardApuracao.classList.add('d-none');
                }
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSalvar');
            const formData = new FormData(this);

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';

            fetch('pages/editar_avaria.php', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + localStorage.getItem('smnt_token') },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'danger');
            })
            .catch(err => {
                console.error(err);
                showToast("Erro de comunicação com o servidor.", "danger");
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-2"></i>Salvar Tudo';
            });
        });
    })();
</script>