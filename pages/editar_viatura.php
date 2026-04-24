<?php
// pages/editar_viatura.php

// 1. Proteção e Dependências
require_once '../api/middleware_auth.php';
require_once '../components/ConexaoDB/conexao.php';
require_once '../components/DAO/ViaturaDAO.php';
require_once '../components/DAO/UnidadeDAO.php';
require_once '../components/DAO/PlanoManutencaoDAO.php';
require_once '../components/DAO/CombustivelDAO.php';
require_once '../api/validate_functions_editar_viatura.php';
require_once '../components/Log/LoggerService.php';

// bloqueio de acesso por perfil
// BLOQUEIO DE ACESSO POR PERFIL
$permissaoEdicao = ['Administrador', 'Gestor Central', 'Gestor Regional', 'Gestor Local'];

if (!in_array(USER_AUTH['perfil'], $permissaoEdicao)) {
    // Se for uma tentativa de salvamento (POST), responde com JSON
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Acesso negado: seu perfil não tem permissão para editar.']);
        exit;
    }

    // Se for apenas o carregamento da página (GET), exibe o alerta HTML
    echo "<div class='container-fluid mt-4'>
            <div class='alert alert-danger shadow'>
                <i class='fas fa-lock me-2'></i> <strong>Acesso Negado:</strong> 
                Seu perfil não possui permissão para editar dados de viaturas.
            </div>
          </div>";
    exit;
}

// --- Lógica para processar a atualização (POST - AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_clean();
    header('Content-Type: application/json');
    ini_set('display_errors', 0);

    $response = ['success' => false, 'message' => '', 'errors' => []];
    $data_from_form = $_POST;

    $validation_errors = validateViaturaData($data_from_form);

    if (!empty($validation_errors)) {
        $response['message'] = "Houve erros no preenchimento.";
        $response['errors'] = $validation_errors;
    } else {
        try {
            $viaturaDAO = new ViaturaDAO();
            if ($viaturaDAO->updateViatura($data_from_form)) {

                // REGISTRO DE LOG PARA AUDITORIA
                LoggerService::registrar(
                    "Editou dados da viatura: " . $data_from_form['prefixo'] . " " . $data_from_form['numero'],
                    "viaturas",
                    $data_from_form['id'],
                    "Alteração técnica efetuada pelo gestor da unidade."
                );

                $response['success'] = true;
                $response['message'] = "Viatura atualizada com sucesso!";
            } else {
                $response['message'] = "Nenhuma alteração foi feita.";
            }
        } catch (Exception $e) {
            $response['message'] = "Erro: " . $e->getMessage();
        }
    }
    echo json_encode($response);
    exit();
}

// --- Lógica para carregar os dados da página (GET) ---
$id_viatura_get = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_viatura_get) {
    echo "<div class='container-fluid'><div class='alert alert-danger'>ID inválido.</div></div>";
    exit();
}

// Inicializamos as variáveis para o HTML não dar erro caso o try falhe
$display_data = [];
$tanquesAtuais = [];
$combustiveis = [];
$unidadesAgrupadas = [];
$listaPlanos = [];

try {
    // 1. Instanciamos os DAOs necessários
    $viaturaDAO = new ViaturaDAO();
    $unidadeDAO = new UnidadeDAO();
    $combDAO = new CombustivelDAO();
    $pDAO = new PlanoManutencaoDAO();

    // 2. Buscamos os dados básicos
    $display_data = $viaturaDAO->getViaturaById($id_viatura_get);

    if (!$display_data) {
        echo "<div class='container-fluid'><div class='alert alert-warning'>Viatura não encontrada.</div></div>";
        exit();
    }

    // 3. Buscamos as listas para os selects e checkboxes
    $unidadesAgrupadas = $unidadeDAO->listarAgrupadasPorComando();
    $listaPlanos = $pDAO->listarTodos();
    $combustiveis = $combDAO->listarTodos();

    // 4. Buscamos os tanques (combustíveis) vinculados a esta viatura específica
    $tanquesAtuais = $viaturaDAO->buscarTanquesViatura($id_viatura_get);
} catch (Exception $e) {
    error_log("Erro ao carregar editar_viatura: " . $e->getMessage());
    echo "<div class='container-fluid'><div class='alert alert-danger'>Erro crítico ao carregar dados do servidor.</div></div>";
    exit();
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edição de viatura</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="ms-3 font-weight-bold text-primary">Formulário de edição</h6>
                </div>
                <div class="card-body mx-3 my-3">
                    <?php if (!empty($display_data)): ?>
                        <form id="editViaturaForm" novalidate>
                            <input type="hidden" name="id" value="<?= htmlspecialchars($display_data['id'] ?? '') ?>">

                            <ul class="nav nav-tabs mb-4" id="viaturaTab" role="tablist">
                                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#dados-basicos-pane" type="button">Dados Básicos</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#motorizacao-pane" type="button">Motorização</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#diversos-pane" type="button">Diversos</button></li>
                            </ul>

                            <div class="tab-content" id="viaturaTabContent">
                                <div class="tab-pane fade show active" id="dados-basicos-pane" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Tipo Operacional</label>
                                            <select class="form-select" name="tipo_operacional">
                                                <option value="Operacional" <?= ($display_data['tipo_operacional'] == 'Operacional') ? 'selected' : '' ?>>Operacional</option>
                                                <option value="Administrativo" <?= ($display_data['tipo_operacional'] == 'Administrativo') ? 'selected' : '' ?>>Administrativo</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Prefixo</label>
                                            <input type="text" class="form-control" name="prefixo" maxlength="5" value="<?= htmlspecialchars($display_data['prefixo'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Número</label>
                                            <input type="number" min="1" class="form-control" name="numero" value="<?= htmlspecialchars($display_data['numero'] ?? '') ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Placa</label>
                                            <input type="text" class="form-control" name="placa" maxlength="8" value="<?= htmlspecialchars($display_data['placa'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Renavam</label>
                                            <input type="text" class="form-control" name="renavam" maxlength="11" value="<?= htmlspecialchars($display_data['renavam'] ?? '') ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Marca</label>
                                            <input type="text" class="form-control" name="marca" value="<?= htmlspecialchars($display_data['marca'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Modelo</label>
                                            <input type="text" class="form-control" name="modelo" value="<?= htmlspecialchars($display_data['modelo'] ?? '') ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Status da Viatura</label>
                                            <select class="form-select" name="status_da_viatura">
                                                <?php
                                                $statusList = ['Operante', 'Baixada', 'Descarregada', 'Indisponível', 'Em fabricação', 'Reserva', 'Senasp'];
                                                foreach ($statusList as $s) {
                                                    $sel = ($display_data['status_da_viatura'] == $s) ? 'selected' : '';
                                                    echo "<option value='$s' $sel>$s</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Lotação Atual</label>
                                            <select class="form-select" name="lotacao_atual_id">
                                                <?php foreach ($unidadesAgrupadas as $comando => $unidades): ?>
                                                    <optgroup label="<?= htmlspecialchars($comando) ?>">
                                                        <?php foreach ($unidades as $u): ?>
                                                            <option value="<?= $u['id'] ?>" <?= ($display_data['lotacao_atual_id'] == $u['id']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($u['unidade_nome']) . " - " . htmlspecialchars($u['municipio_nome'] ?? '') ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Plano de Manutenção</label>
                                            <select class="form-select" name="plano_id">
                                                <option value="">Nenhum plano associado</option>
                                                <?php foreach ($listaPlanos as $plano): ?>
                                                    <option value="<?= $plano['id'] ?>" <?= ($display_data['plano_id'] == $plano['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($plano['descricao']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="motorizacao-pane" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-4"><label class="form-label">Cilindradas (cc)</label><input type="number" class="form-control" name="cilindradas" value="<?= $display_data['cilindradas'] ?>"></div>
                                        <div class="col-md-4"><label class="form-label">Cavalos (cv)</label><input type="number" class="form-control" name="cavalos_forca" value="<?= $display_data['cavalos_forca'] ?>"></div>
                                        <div class="col-md-4"><label class="form-label">Número de Marchas</label><input type="number" class="form-control" name="numero_marchas" value="<?= $display_data['numero_marchas'] ?>"></div>
                                        <div class="col-12 mt-4">
                                            <h6 class="fw-bold text-primary"><i class="fas fa-gas-pump me-2"></i>Tanques e Combustíveis</h6>
                                            <div class="row g-2">
                                                <?php foreach ($combustiveis as $cb):
                                                    $estaVinculado = isset($tanquesAtuais[$cb['id']]);
                                                    $litragemAtual = $estaVinculado ? $tanquesAtuais[$cb['id']]['litragem'] : '';
                                                ?>
                                                    <div class="col-md-6">
                                                        <div class="border rounded px-5 d-flex align-items-center justify-content-between <?= $estaVinculado ? 'border-primary bg-white' : 'bg-light' ?>">
                                                            <div class="form-check form-switch mb-0">
                                                                <input class="form-check-input" type="checkbox" name="tanques[<?= $cb['id'] ?>][ativo]" value="1"
                                                                    id="cb_<?= $cb['id'] ?>" <?= $estaVinculado ? 'checked' : '' ?>
                                                                    onchange="document.getElementById('litros_<?= $cb['id'] ?>').disabled = !this.checked">
                                                                <label class="form-check-label small fw-bold" for="cb_<?= $cb['id'] ?>"><?= htmlspecialchars($cb['tipo']) ?></label>
                                                            </div>
                                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                                <input type="number" step="1" min="1" name="tanques[<?= $cb['id'] ?>][litragem]" id="litros_<?= $cb['id'] ?>"
                                                                    class="form-control" value="<?= $litragemAtual ?>" <?= $estaVinculado ? '' : 'disabled' ?>>
                                                                <span class="input-group-text">L</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="diversos-pane" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="form-label">Quilometragem (km)</label><input type="number" class="form-control" name="quilometragem" value="<?= $display_data['quilometragem'] ?>"></div>
                                        <div class="col-md-6"><label class="form-label">Fim da Garantia</label><input type="date" class="form-control" name="garantia_fim" value="<?= $display_data['garantia_fim'] ?>"></div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <button class="btn btn-outline-success" type="submit" id="btnSalvarVtr">
                                <i class="fas fa-save me-2"></i>Atualizar Viatura
                            </button>

                            <!-- NOVO BOTÃO DE RETORNO -->
                            <button type="button" class="btn btn-outline-secondary ms-2" onclick="loadPage('pages/pesquisar_viatura.php')">
                                <i class="fas fa-arrow-left me-2"></i>Voltar para Pesquisa
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('editViaturaForm');
            if (!form) return;

            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const btn = document.getElementById('btnSalvarVtr');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';

                fetch('pages/editar_viatura.php', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                        },
                        body: new FormData(form)
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Remove marcações de erro anteriores
                        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

                        if (data.success) {
                            showToast(data.message, 'success');
                        } else {
                            // Se houverem erros específicos detalhados pelo PHP
                            if (data.errors) {
                                let listaErros = "<strong>Campos pendentes:</strong><br>";

                                // Percorre o JSON de erros
                                Object.keys(data.errors).forEach(key => {
                                    // 1. Monta a mensagem para exibir no Toast
                                    listaErros += `- ${data.errors[key]}<br>`;

                                    // 2. Tenta encontrar o campo no formulário e marca de vermelho
                                    const input = form.querySelector(`[name="${key}"]`);
                                    if (input) {
                                        input.classList.add('is-invalid');
                                    }
                                });

                                // Exibe o Toast com a lista completa do que falta
                                showToast(listaErros, 'danger');
                            } else {
                                // Mensagem genérica se o PHP não detalhou os campos
                                showToast(data.message, 'danger');
                            }
                        }
                    })
                    .catch((err) => {
                        console.error(err);
                        showToast('Erro de conexão ou resposta inválida.', 'danger');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save me-2"></i>Atualizar Viatura';
                    });
            });
        })();
    </script>
</div>