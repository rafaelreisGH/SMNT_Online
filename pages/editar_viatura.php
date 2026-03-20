<?php
// pages/editar_viatura.php

// 1. Proteção e Dependências
require_once '../api/middleware_auth.php';
require_once '../components/ConexaoDB/conexao.php';
require_once '../components/DAO/ViaturaDAO.php';
require_once '../components/DAO/UnidadeDAO.php';
require_once '../api/validate_functions_editar_viatura.php';
require_once '../components/DAO/PlanoManutencaoDAO.php';

$pDAO = new PlanoManutencaoDAO();
$listaPlanos = $pDAO->listarTodos();

// --- Lógica para processar a requisição POST (AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // LIMPEZA: Impede que qualquer erro de texto do PHP "suje" o JSON de saída
    ob_clean();
    header('Content-Type: application/json');
    ini_set('display_errors', 0);

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
                $response['message'] = "Nenhuma alteração foi feita ou a viatura não foi encontrada.";
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
    $unidadeDAO = new UnidadeDAO();

    $display_data = $viaturaDAO->getViaturaById($id_viatura_get);
    $unidadesAgrupadas = $unidadeDAO->listarAgrupadasPorComando();

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
                            // CORREÇÃO: Enviar o Token JWT
                            'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                        },
                        body: new FormData(form)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                        } else {
                            showToast(data.message, 'danger');
                        }
                    })
                    .catch(() => showToast('Erro de conexão.', 'danger'))
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save me-2"></i>Atualizar Viatura';
                    });
            });
        })();
    </script>
</div>