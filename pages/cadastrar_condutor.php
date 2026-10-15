<?php
// pages/cadastrar_condutor.php
require_once '../api/middleware_auth.php';
require_once '../components/DAO/UnidadeDAO.php';

$uDAO = new UnidadeDAO();
$unidadesAgrupadas = $uDAO->listarAgrupadasPorComando();
?>
<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Cadastro de Condutor</h1>
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="ms-3 font-weight-bold text-primary">Formulário de Informações</h6>
                </div>
                <div class="card-body mx-3 my-3">
                    <form id="formCadastrarCondutor" novalidate>
                        <ul class="nav nav-tabs mb-4" id="condutorTab" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#dados-pessoais-pane" type="button">Dados Pessoais / CNH</button></li>
                            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#militar-pane" type="button">Informações Militares</button></li>
                        </ul>

                        <div class="tab-content" id="condutorTabContent">
                            <!-- Aba 1 -->
                            <div class="tab-pane fade show active" id="dados-pessoais-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Nome Completo</label>
                                        <input type="text" class="form-control" name="nome" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">CPF</label>
                                        <input type="text" class="form-control" id="cpf" name="cpf" placeholder="000.000.000-00" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Número da CNH</label>
                                        <input type="text" class="form-control" name="numero_cnh" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Categoria</label>
                                        <input type="text" class="form-control" name="categoria_cnh" maxlength="2" placeholder="Ex: AD" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Validade CNH</label>
                                        <input type="date" class="form-control" name="data_validade_cnh" required>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end pb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" name="anotacao_ear" id="ear">
                                            <label class="form-check-label" for="ear">Possui EAR?</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Aba 2 -->
                            <div class="tab-pane fade" id="militar-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Identidade Militar (RG)</label>
                                        <input type="text" class="form-control" name="rg_militar">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Posto/Graduação</label>
                                        <select class="form-select" name="posto_grad">
                                            <option value="">Selecione...</option>
                                            <?php
                                            $postos = ['Soldado', 'Cabo', 'Sargento', 'Subtenente', 'Tenente', 'Capitão', 'Major', 'Tenente-Coronel', 'Coronel'];
                                            foreach ($postos as $p) echo "<option value=\"$p\">$p</option>";
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Lotação (Unidade)</label>
                                        <select class="form-select" name="ubm_id" required>
                                            <option value="">Selecione a Unidade...</option>
                                            <?php foreach ($unidadesAgrupadas as $comando => $u_grupo): ?>
                                                <optgroup label="<?= htmlspecialchars($comando) ?>">
                                                    <?php foreach ($u_grupo as $u): ?>
                                                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['unidade_nome']) ?></option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status do Condutor</label>
                                        <select class="form-select" name="status">
                                            <option value="ativo">Ativo</option>
                                            <option value="inativo">Inativo</option>
                                            <option value="afastado">Afastado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <button class="btn btn-outline-success" type="submit" id="btnSalvarCondutor"><i class="fas fa-save me-2"></i>Cadastrar Condutor</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('formCadastrarCondutor');

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
            const btn = document.getElementById('btnSalvarCondutor');
            const formData = new FormData(this);

            btn.disabled = true;

            fetch('api/processar_cadastro_condutor.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        form.reset();
                    } else {
                        showToast(data.message, 'danger');
                    }
                })
                .finally(() => btn.disabled = false);
        });
    })();
</script>