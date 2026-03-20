<?php
// pages/transferir_viatura.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/ViaturaDAO.php';
require_once __DIR__ . '/../components/DAO/UnidadeDAO.php';

$vDAO = new ViaturaDAO();
$uDAO = new UnidadeDAO();

$viaturas = $vDAO->listarTodasParaSelect();
$unidades = $uDAO->listarAgrupadasPorComando();
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Movimentação de Frota</h1>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary">
                    <h6 class="m-0 font-weight-bold text-white">Transferir Viatura entre Unidades</h6>
                </div>
                <div class="card-body">
                    <form id="formTransferir">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Selecione a Viatura</label>
                                <select class="form-select" name="viatura_id" required id="vtr_id">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($viaturas as $v): ?>
                                        <option value="<?= $v['id'] ?>" data-origem="<?= $v['lotacao_atual_id'] ?? '' ?>">
                                            <?= htmlspecialchars(($v['prefixo'] ?? '') . ' ' . ($v['numero'] ?? '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="unidade_origem_id" id="unidade_origem_id">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label text-danger fw-bold">Unidade de Destino</label>
                                <select class="form-select border-danger" name="unidade_destino_id" required>
                                    <option value="">Para onde a viatura será enviada?</option>
                                    <?php foreach ($unidades as $cmd => $grupo): ?>
                                        <optgroup label="<?= htmlspecialchars($cmd) ?>">
                                            <?php foreach ($grupo as $u): ?>
                                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['unidade_nome']) ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Documento de Autorização</label>
                                <input type="text" name="documento" class="form-control" placeholder="Ofício, Boletim ou Portaria" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" id="btnExecutar">
                            <i class="fas fa-exchange-alt me-2"></i> Executar Transferência
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('formTransferir');
        const selectVtr = document.getElementById('vtr_id');
        const hiddenOrigem = document.getElementById('unidade_origem_id');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Garante que a origem seja capturada antes de enviar
            const selectedOption = selectVtr.options[selectVtr.selectedIndex];
            hiddenOrigem.value = selectedOption.getAttribute('data-origem');

            if (!confirm("Esta ação mudará a lotação da viatura. Confirmar?")) return;

            const btn = document.getElementById('btnExecutar');
            btn.disabled = true;

            fetch('api/processar_movimentacao.php?acao=transferir', {
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
                        form.reset();
                        // Opcional: Redirecionar para o dashboard após sucesso
                        setTimeout(() => window.loadPage('pages/dashboard.php'), 1500);
                    } else {
                        showToast(data.message, 'danger');
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    showToast("Erro na comunicação com o servidor", "danger");
                    btn.disabled = false;
                });
        });
    })();
</script>