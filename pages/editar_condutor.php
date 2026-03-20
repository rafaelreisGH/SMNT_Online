<?php
// pages/editar_condutor.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/CondutorDAO.php';
require_once __DIR__ . '/../components/DAO/UnidadeDAO.php';

$condutorDAO = new CondutorDAO();
$unidadeDAO = new UnidadeDAO();

$id_condutor = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_condutor) {
    echo "<div class='container-fluid'><div class='alert alert-danger'>ID inválido.</div></div>";
    exit();
}

$condutorData = $condutorDAO->getCondutorById($id_condutor);
$unidadesAgrupadas = $unidadeDAO->listarAgrupadasPorComando();

if (!$condutorData) {
    echo "<div class='container-fluid'><div class='alert alert-warning'>Condutor não encontrado.</div></div>";
    exit();
}
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Editar Condutor: <span class="text-primary"><?= htmlspecialchars($condutorData['nome'] ?? '') ?></span></h1>
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="ms-3 font-weight-bold text-primary">Formulário de Edição</h6>
                </div>
                <div class="card-body mx-3 my-3">
                    <form id="formEditarCondutor" novalidate>
                        <input type="hidden" name="id" value="<?= $condutorData['id'] ?>">

                        <ul class="nav nav-tabs mb-4" id="condutorEditTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#edit-pessoais-pane" type="button">Dados Pessoais</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#edit-cnh-pane" type="button">CNH</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#edit-militares-pane" type="button">Dados Militares</button></li>
                        </ul>

                        <div class="tab-content" id="condutorEditTabContent">
                            <!-- Aba Pessoais -->
                            <div class="tab-pane fade show active" id="edit-pessoais-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Nome Completo</label>
                                        <input type="text" class="form-control" name="nome" value="<?= htmlspecialchars($condutorData['nome'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">CPF</label>
                                        <input type="text" class="form-control" id="cpf" name="cpf" value="<?= htmlspecialchars($condutorData['cpf'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Aba CNH -->
                            <div class="tab-pane fade" id="edit-cnh-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Número da CNH</label>
                                        <input type="text" class="form-control" name="numero_cnh" value="<?= htmlspecialchars($condutorData['numero_cnh'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Categoria</label>
                                        <input type="text" class="form-control" name="categoria_cnh" value="<?= htmlspecialchars($condutorData['categoria_cnh'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Validade</label>
                                        <input type="date" class="form-control" name="data_validade_cnh" value="<?= htmlspecialchars($condutorData['data_validade_cnh'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end pb-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" name="anotacao_ear" id="ear" <?= (!empty($condutorData['anotacao_ear'])) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="ear">Possui EAR?</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Aba Militares -->
                            <div class="tab-pane fade" id="edit-militares-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">RG Militar</label>
                                        <input type="text" class="form-control" name="rg_militar" value="<?= htmlspecialchars($condutorData['rg_militar'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Posto/Graduação</label>
                                        <select class="form-select" name="posto_grad">
                                            <?php
                                            $postos = ['Soldado', 'Cabo', 'Sargento', 'Subtenente', 'Tenente', 'Capitão', 'Major', 'Tenente-Coronel', 'Coronel'];
                                            foreach ($postos as $p) {
                                                $sel = ($condutorData['posto_grad'] == $p) ? 'selected' : '';
                                                echo "<option value=\"$p\" $sel>$p</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Lotação (Unidade)</label>
                                        <select class="form-select" name="ubm_id">
                                            <?php foreach ($unidadesAgrupadas as $comando => $unidades): ?>
                                                <optgroup label="<?= htmlspecialchars($comando) ?>">
                                                    <?php foreach ($unidades as $u): ?>
                                                        <option value="<?= $u['id'] ?>" <?= ($condutorData['ubm_id'] == $u['id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($u['unidade_nome']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="ativo" <?= ($condutorData['status'] == 'ativo') ? 'selected' : '' ?>>Ativo</option>
                                            <option value="inativo" <?= ($condutorData['status'] == 'inativo') ? 'selected' : '' ?>>Inativo</option>
                                            <option value="afastado" <?= ($condutorData['status'] == 'afastado') ? 'selected' : '' ?>>Afastado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-outline-success" type="submit" id="btnSalvar"><i class="fas fa-save me-2"></i>Salvar Alterações</button>
                            <a href="pages/pesquisar_condutor.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Voltar para Pesquisa
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('formEditarCondutor');

        // Máscara CPF
        document.getElementById('cpf').addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g, '');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = v.substring(0, 14);
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSalvar');
            btn.disabled = true;

            // APONTANDO PARA A NOVA API
            fetch('api/processar_edicao_condutor.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: new FormData(this)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                    } else {
                        showToast(data.message, 'danger');
                    }
                })
                .catch(err => showToast("Erro de comunicação", "danger"))
                .finally(() => btn.disabled = false);
        });
    })();
</script>