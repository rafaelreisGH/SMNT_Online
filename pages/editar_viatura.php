<?php
// pages/editar_viatura.php

// Inclui os arquivos de dependência
require_once '../api/middleware_auth.php';
require_once '../components/ConexaoDB/conexao.php';
require_once '../components/DAO/ViaturaDAO.php';
require_once '../components/DAO/UnidadeDAO.php'; // ADICIONADO
require_once '../api/validate_functions_editar_viatura.php';
require_once '../components/DAO/PlanoManutencaoDAO.php';
$pDAO = new PlanoManutencaoDAO();
$listaPlanos = $pDAO->listarTodos();

// --- Lógica para processar a requisição POST (AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => '', 'errors' => []];
    $data_from_form = $_POST;

    $validation_errors = validateViaturaData($data_from_form);

    if (!empty($validation_errors)) {
        $response['message'] = "Houve erros no preenchimento. Por favor, corrija os campos indicados.";
        $response['errors'] = $validation_errors;
    } else {
        try {
            $viaturaDAO = new ViaturaDAO();
            if ($viaturaDAO->updateViatura($data_from_form)) {
                $response['success'] = true;
                $response['message'] = "Viatura atualizada com sucesso!";
            } else {
                $response['message'] = "Erro: Nenhuma alteração foi feita ou a viatura não foi encontrada.";
            }
        } catch (Exception $e) {
            error_log("Erro na atualização: " . $e->getMessage());
            $response['message'] = "Erro interno no servidor.";
        }
    }
    echo json_encode($response);
    exit();
}

// --- Lógica para requisição GET ---
$id_viatura_get = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_viatura_get) {
    echo "<div class='container-fluid'><div class='alert alert-danger'>ID inválido.</div></div>";
    exit();
}

try {
    $viaturaDAO = new ViaturaDAO();
    $unidadeDAO = new UnidadeDAO(); // INSTANCIADO

    $display_data = $viaturaDAO->getViaturaById($id_viatura_get);
    $unidadesAgrupadas = $unidadeDAO->listarAgrupadasPorComando(); // BUSCA REAL DO BD

    if (!$display_data) {
        echo "<div class='container-fluid'><div class='alert alert-warning'>Viatura não encontrada.</div></div>";
        exit();
    }
} catch (Exception $e) {
    echo "<div class='container-fluid'><div class='alert alert-danger'>Erro ao carregar dados.</div></div>";
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
                        <form action="#" method="POST" id="editViaturaForm" novalidate>
                            <input type="hidden" name="id" value="<?= htmlspecialchars($display_data['id'] ?? '') ?>">

                            <ul class="nav nav-tabs mb-4" id="viaturaTab" role="tablist">
                                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#dados-basicos-pane" type="button">Dados Básicos</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#motorizacao-pane" type="button">Motorização</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#diversos-pane" type="button">Diversos</button></li>
                            </ul>

                            <div class="tab-content" id="viaturaTabContent">
                                <div class="tab-pane fade show active" id="dados-basicos-pane" role="tabpanel">
                                    <div class="row g-3">
                                        <!-- Tipo Operacional -->
                                        <div class="col-md-6">
                                            <label for="tipo_operacional" class="form-label">Tipo Operacional</label>
                                            <select class="form-select" id="tipo_operacional" name="tipo_operacional">
                                                <option value="Operacional" <?= ($display_data['tipo_operacional'] == 'Operacional') ? 'selected' : '' ?>>Operacional</option>
                                                <option value="Administrativo" <?= ($display_data['tipo_operacional'] == 'Administrativo') ? 'selected' : '' ?>>Administrativo</option>
                                            </select>
                                        </div>

                                        <!-- Prefixo e Número -->
                                        <div class="col-md-3">
                                            <label class="form-label">Prefixo</label>
                                            <input type="text" class="form-control" name="prefixo" maxlength="5" value="<?= htmlspecialchars($display_data['prefixo'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Número</label>
                                            <input type="number" min="1" class="form-control" id="numero" name="numero" value="<?= htmlspecialchars($display_data['numero'] ?? '') ?>">
                                            <div class="invalid-feedback" id="error-numero"></div>
                                        </div>

                                        <!-- Placa e Renavam -->
                                        <div class="col-md-6">
                                            <label class="form-label">Placa</label>
                                            <input type="text" class="form-control" name="placa" maxlength="8" value="<?= htmlspecialchars($display_data['placa'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Renavam</label>
                                            <input type="text" class="form-control" name="renavam" maxlength="11" value="<?= htmlspecialchars($display_data['renavam'] ?? '') ?>">
                                        </div>

                                        <!-- Marca e Modelo -->
                                        <div class="col-md-6">
                                            <label class="form-label">Marca</label>
                                            <input type="text" class="form-control" name="marca" value="<?= htmlspecialchars($display_data['marca'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Modelo</label>
                                            <input type="text" class="form-control" name="modelo" value="<?= htmlspecialchars($display_data['modelo'] ?? '') ?>">
                                        </div>

                                        <!-- Ano e PBT -->
                                        <div class="col-md-3">
                                            <label class="form-label">Ano</label>
                                            <input type="number" min="1900" max="<?= date('Y') + 1 ?>" class="form-control" name="ano" value="<?= htmlspecialchars($display_data['ano'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">PBT (kg)</label>
                                            <input type="number" min="1" class="form-control" id="pbt" name="pbt" value="<?= htmlspecialchars($display_data['pbt'] ?? '') ?>">
                                            <div class="invalid-feedback" id="error-pbt"></div>
                                        </div>

                                        <!-- Tipo Aquisição e Status -->
                                        <div class="col-md-6">
                                            <label class="form-label">Tipo de Aquisição</label>
                                            <select class="form-select" name="tipo_aquisicao">
                                                <?php
                                                $tipos = ['Própria', 'Doação', 'Fiel Depositário', 'Locada'];
                                                foreach ($tipos as $t) {
                                                    echo "<option value='$t' " . ($display_data['tipo_aquisicao'] == $t ? 'selected' : '') . ">$t</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Status da Viatura</label>
                                            <select class="form-select" name="status_da_viatura">
                                                <?php
                                                $status = ['Operante', 'Baixada', 'Descarregada', 'Indisponível', 'Em fabricação', 'Reserva', 'Senasp'];
                                                foreach ($status as $s) {
                                                    echo "<option value='$s' " . ($display_data['status_da_viatura'] == $s ? 'selected' : '') . ">$s</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Lotação Atual (CORRIGIDO PARA BUSCA REAL E AGRUPADA) -->
                                        <div class="col-md-6">
                                            <label for="lotacao_atual_id" class="form-label">Lotação Atual</label>
                                            <select class="form-select" id="lotacao_atual_id" name="lotacao_atual_id">
                                                <option value="">Selecione a Unidade...</option>
                                                <?php foreach ($unidadesAgrupadas as $comando => $unidades): ?>
                                                    <optgroup label="<?= htmlspecialchars($comando) ?>">
                                                        <?php foreach ($unidades as $unidade): ?>
                                                            <?php
                                                            $label = htmlspecialchars($unidade['unidade_nome']);
                                                            if (!empty($unidade['municipio_nome'])) {
                                                                $label .= " - " . htmlspecialchars($unidade['municipio_nome']);
                                                            }
                                                            $selected = ($display_data['lotacao_atual_id'] == $unidade['id']) ? 'selected' : '';
                                                            ?>
                                                            <option value="<?= $unidade['id'] ?>" <?= $selected ?>>
                                                                <?= $label ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Aba Motorização -->
                                <div class="tab-pane fade" id="motorizacao-pane" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Cilindradas (cc)</label>
                                            <input type="number" min="1" class="form-control" name="cilindradas" value="<?= htmlspecialchars($display_data['cilindradas'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Cavalos de Força (cv)</label>
                                            <input type="number" min="1" class="form-control" name="cavalos_forca" value="<?= htmlspecialchars($display_data['cavalos_forca'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Torque</label>
                                            <input type="text" class="form-control" name="torque" maxlength="50" value="<?= htmlspecialchars($display_data['torque'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tipo de Câmbio</label>
                                            <select class="form-select" name="tipo_cambio">
                                                <option value="">Selecione...</option>
                                                <option value="Manual" <?= ($display_data['tipo_cambio'] == 'Manual') ? 'selected' : '' ?>>Manual</option>
                                                <option value="Automático" <?= ($display_data['tipo_cambio'] == 'Automático') ? 'selected' : '' ?>>Automático</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Número de Marchas</label>
                                            <input type="number" min="1" class="form-control" name="numero_marchas" value="<?= htmlspecialchars($display_data['numero_marchas'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Aba Diversos -->
                                <div class="tab-pane fade" id="diversos-pane" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Quilometragem (km)</label>
                                            <input type="number" min="0" class="form-control" name="quilometragem" value="<?= htmlspecialchars($display_data['quilometragem'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Fim da Garantia</label>
                                            <input type="date" class="form-control" name="garantia_fim" value="<?= htmlspecialchars($display_data['garantia_fim'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="plano_id" class="form-label">Plano de Manutenção Preventiva</label>
                                    <select class="form-select" id="plano_id" name="plano_id">
                                        <option value="">Nenhum plano associado</option>
                                        <?php foreach ($listaPlanos as $plano): ?>
                                            <option value="<?= $plano['id'] ?>" <?= ($display_data['plano_id'] == $plano['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($plano['descricao']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <hr class="my-4">
                            <button class="btn btn-outline-success" type="submit" id="btnSalvar">
                                <i class="fas fa-save me-2"></i>Atualizar Viatura
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <script>
        function initializeEditFormScripts() {
            const form = document.getElementById('editViaturaForm');
            if (!form) return;

            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(form);
                const btn = document.getElementById('btnSalvar');

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Atualizando...';

                fetch('pages/editar_viatura.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                        if (data.success) {
                            showToast(data.message, 'success');
                        } else {
                            showToast(data.message, 'danger');
                            if (data.errors) {
                                for (const field in data.errors) {
                                    const input = form.querySelector(`[name="${field}"]`);
                                    if (input) input.classList.add('is-invalid');
                                }
                            }
                        }
                    })
                    .catch(() => showToast('Erro de conexão.', 'danger'))
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save me-2"></i>Atualizar Viatura';
                    });
            });
        }

        function showToast(message, type) {
            const container = document.querySelector('.toast-container');
            const color = type === 'success' ? 'text-bg-success' : 'text-bg-danger';
            const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle';
            const html = `
                <div class="toast ${color}" role="alert">
                    <div class="toast-header">
                        <i class="${icon} me-2"></i><strong class="me-auto">Sistema</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">${message}</div>
                </div>`;
            container.insertAdjacentHTML('beforeend', html);
            const toastEl = container.lastElementChild;
            new bootstrap.Toast(toastEl, {
                delay: 4000
            }).show();
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }

        initializeEditFormScripts();
    </script>
</div>