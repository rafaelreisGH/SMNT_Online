<?php
// pages/registrar_avaria.php

// 1. Caminho robusto para o middleware e proteção da página
require_once __DIR__ . '/../api/middleware_auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui as dependências necessárias
require_once __DIR__ . '/../components/DAO/ViaturaDAO.php';
require_once __DIR__ . '/../components/DAO/CondutorDAO.php';
require_once __DIR__ . '/../components/DAO/RegistroAvariaDAO.php';

// Instancia os DAOs
$vDAO = new ViaturaDAO();
$cDAO = new CondutorDAO();
$aDAO = new RegistroAvariaDAO();

// Busca os dados de forma segura através dos métodos dos DAOs
try {
    $viaturas = $vDAO->listarTodasParaSelect();
    $condutores = $cDAO->listarAtivos();
    $tiposAvaria = $aDAO->listarTiposAvaria();
} catch (Exception $e) {
    error_log("Erro ao carregar dados da página de registro de avaria: " . $e->getMessage());
    $viaturas = [];
    $condutores = [];
    $tiposAvaria = [];
}
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Registrar Avaria / Problema</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informar problema técnico na viatura</h6>
                </div>
                <div class="card-body">
                    <form id="formRegistrarAvaria" novalidate>
                        <div class="row g-3">
                            <!-- Viatura -->
                            <div class="col-md-6">
                                <label for="viatura_id" class="form-label">Viatura Afetada</label>
                                <select class="form-select" name="viatura_id" id="viatura_id">
                                    <option value="">Selecione a viatura...</option>
                                    <?php foreach ($viaturas as $v): ?>
                                        <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['prefixo'] . ' ' . $v['numero']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback" id="error-viatura_id"></div>
                            </div>

                            <!-- Condutor -->
                            <div class="col-md-6">
                                <label for="condutor_id" class="form-label">Condutor Responsável/Relatante</label>
                                <select class="form-select" name="condutor_id" id="condutor_id">
                                    <option value="">Selecione o condutor...</option>
                                    <?php foreach ($condutores as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback" id="error-condutor_id"></div>
                            </div>

                            <!-- Tipo de Avaria -->
                            <div class="col-md-6">
                                <label for="tipo_avaria_id" class="form-label">Componente com Defeito</label>
                                <select class="form-select" name="tipo_avaria_id" id="tipo_avaria_id">
                                    <option value="">Selecione o componente...</option>
                                    <?php
                                    $catAtual = "";
                                    foreach ($tiposAvaria as $t):
                                        if ($catAtual != $t['categoria']):
                                            if ($catAtual != "") echo "</optgroup>";
                                            $catAtual = $t['categoria'];
                                            echo "<optgroup label='" . htmlspecialchars($catAtual) . "'>";
                                        endif;
                                    ?>
                                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome_componente'] ?? '') ?></option>
                                    <?php endforeach;
                                    echo "</optgroup>"; ?>
                                </select>
                                <div class="invalid-feedback" id="error-tipo_avaria_id"></div>
                            </div>

                            <!-- Data -->
                            <div class="col-md-6">
                                <label for="data_registro" class="form-label">Data do Relato</label>
                                <input type="datetime-local" class="form-control" name="data_registro" id="data_registro" value="<?= date('Y-m-d\TH:i') ?>">
                                <div class="invalid-feedback" id="error-data_registro"></div>
                            </div>

                            <!-- Descrição -->
                            <div class="col-12">
                                <label for="observacoes_avaria" class="form-label">Descrição Detalhada do Problema</label>
                                <textarea class="form-control" name="observacoes_avaria" id="observacoes_avaria" rows="4" placeholder="Descreva os sintomas, barulhos ou circunstâncias do defeito..."></textarea>
                            </div>
                        </div>

                        <hr class="my-4">
                        <button type="submit" class="btn btn-outline-success" id="btnSalvarAvaria">
                            <i class="fas fa-plus-circle me-2"></i>Registrar Avaria
                        </button>
                        <button type="button" onclick="limparFormulario()" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-redo me-2"></i>Limpar Formulário
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function limparFormulario() {
        const form = document.getElementById('formRegistrarAvaria');
        form.reset();
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.getElementById('viatura_id').focus();
    }

    document.getElementById('formRegistrarAvaria').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSalvarAvaria');
        const formData = new FormData(this);

        this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        btn.disabled = true;

        // CORREÇÃO: Adicionado o header Authorization no fetch
        fetch('api/processar_registro_avaria.php', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                },
                body: formData
            })
            .then(r => {
                if (r.status === 401) {
                    showToast("Sessão expirada. Faça login novamente.", "danger");
                    window.location.href = 'login.php';
                    return;
                }
                return r.json();
            })
            .then(data => {
                if (data && data.success) {
                    showToast(data.message, 'success');
                    limparFormulario();
                } else if (data) {
                    showToast(data.message, 'danger');
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            const input = this.querySelector(`[name="${key}"]`);
                            if (input) input.classList.add('is-invalid');
                        });
                    }
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Erro ao processar requisição", "danger");
            })
            .finally(() => btn.disabled = false);
    });
</script>