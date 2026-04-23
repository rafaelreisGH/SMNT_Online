<?php
// pages/baixar_cautela.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/CautelaDAO.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// REGRA DE SEGURANÇA: Se o ID não veio ou não é um número, para o script aqui
if (!$id) {
    echo "<div class='container-fluid'><div class='alert alert-danger'>
            <i class='fas fa-exclamation-circle me-2'></i>Erro: ID da cautela inválido ou não fornecido.
          </div></div>";
    exit;
}

$dao = new CautelaDAO();
$c = $dao->getCautelaById($id);

// No topo do baixar_cautela.php, após buscar a variável $c:
if ($c['status'] !== 'Ativa') {
    echo "<div class='container-fluid'><div class='alert alert-warning'>
            <i class='fas fa-exclamation-triangle me-2'></i>Esta cautela não está em estado de 'Ativa'. 
            O recebimento só pode ser feito após a assinatura do condutor.
          </div></div>";
    exit;
}

$itens = $dao->buscarItensEntreguesNaSaida($id);

// Verifica se a cautela existe no banco
if (!$c) {
    echo "<div class='container-fluid'><div class='alert alert-warning'>Cautela não encontrada ou acesso negado.</div></div>";
    exit;
}
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Descautela (Retorno de Viatura)</h1>
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form id="formDescautela" novalidate>
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="viatura_id" value="<?= $c['viatura_id'] ?>">

                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light text-primary">
                        <h6 class="m-0 font-weight-bold">Conferência de Retorno: <?= $c['prefixo'] ?> <?= $c['numero'] ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Odômetro de Retorno</label>
                                <input type="number" name="odometro_retorno" class="form-control" required min="<?= $c['odometro_saida'] ?>">
                                <small class="text-muted">Saída: <?= $c['odometro_saida'] ?> km</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Combustível no Retorno</label>
                                <select class="form-select" name="combustivel_retorno" required>
                                    <option value="Vazio">Vazio</option>
                                    <option value="1/4">1/4</option>
                                    <option value="1/2">1/2</option>
                                    <option value="3/4">3/4</option>
                                    <option value="Cheio">Cheio</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="mt-4 fw-bold border-bottom pb-2">Conferência de Acessórios</h6>
                        <?php foreach ($itens as $it): ?>
                            <div class="row mb-2 border-bottom pb-2 align-items-center">
                                <div class="col-md-4 small fw-bold"><?= htmlspecialchars($it['nome']) ?></div>
                                <div class="col-md-3">
                                    <select class="form-select form-select-sm sel-retorno" data-id="<?= $it['acessorio_id'] ?>" data-nome="<?= $it['nome'] ?>" onchange="toggleJust(this)">
                                        <option value="1">Devolvido OK</option>
                                        <option value="0">FALTANTE / EXTRAVIADO</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control form-control-sm d-none input-justifica" placeholder="Por que o item não voltou?">
                                </div>
                            </div>
                        <?php endforeach; ?>




                        <!-- Área de Extravio: Só aparece se algum item for marcado como faltante -->
                        <div id="area_extravio" class="mt-4 p-3 border-left-warning bg-light border rounded shadow-sm d-none">
                            <h6 class="text-warning fw-bold"><i class="fas fa-boxes me-2"></i>Relato de Extravio de Acessórios</h6>
                            <p class="small text-muted">Utilize este campo para observações gerais sobre os itens faltantes e anexe fotos se necessário.</p>

                            <textarea name="obs_extravio" id="obs_extravio" class="form-control mb-2" rows="2" placeholder="Observações complementares sobre os itens faltantes..."></textarea>

                            <label class="form-label small fw-bold">Fotos dos Itens Faltantes (Máx. 5)</label>
                            <input type="file" name="fotos_extravio[]" class="form-control" multiple accept="image/*" onchange="validarLimiteFotos(this)">
                        </div>
                    </div>


                    <div id="containerOcorrencias"></div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-outline-primary w-100 py-2 fw-bold">FINALIZAR RECEBIMENTO</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        // 1. Alternar visibilidade da justificativa por acessório
        window.toggleJust = function(sel) {
            const row = sel.closest('.row');
            const input = row.querySelector('.input-justifica');

            if (sel.value === '0') {
                input.classList.remove('d-none');
                // Exibe a área de fotos/obs gerais de extravio
                document.getElementById('area_extravio').classList.remove('d-none');
            } else {
                input.classList.add('d-none');
                input.value = '';

                // Esconde a área de extravio apenas se não houver NENHUM outro item faltante
                const temOutroFaltante = document.querySelectorAll('.sel-retorno[value="0"]').length > 0;
                if (!temOutroFaltante) {
                    document.getElementById('area_extravio').classList.add('d-none');
                }
            }
        };

        // 2. Alternar visibilidade da área de avaria técnica
        window.toggleAreaAvaria = function(checkbox) {
            const area = document.getElementById('area_detalhes_avaria');
            const obs = document.getElementById('obs_avaria');
            if (checkbox.checked) {
                area.classList.remove('d-none');
                obs.required = true;
            } else {
                area.classList.add('d-none');
                obs.required = false;
                obs.value = '';
                document.getElementById('fotos_avaria').value = '';
            }
        };

        // 3. Validação do limite de 5 fotos
        window.validarLimiteFotos = function(input) {
            if (input.files.length > 5) {
                showToast("Máximo de 5 fotos permitido.", "danger");
                input.value = "";
            }
        };

        // 4. LISTENER ÚNICO para o envio do formulário
        document.getElementById('formDescautela').addEventListener('submit', function(e) {
            e.preventDefault();

            // Coleta os dados do checklist de acessórios
            const checklist = [];
            document.querySelectorAll('.sel-retorno').forEach(sel => {
                checklist.push({
                    acessorio_id: sel.dataset.id,
                    nome: sel.dataset.nome,
                    status_retorno: sel.value,
                    justificativa: sel.closest('.row').querySelector('.input-justifica').value
                });
            });

            const formData = new FormData(this);
            formData.append('checklist_json', JSON.stringify(checklist));

            const btn = this.querySelector('button[type="submit"]');
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>PROCESSANDO...';

            fetch('api/processar_descautela.php', {
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

                        // REDIRECIONAMENTO IDENTICO AO EDITAR_CAUTELA (Padrão SPA)
                        setTimeout(() => loadPage('pages/pesquisar_cautelas.php'), 1200);

                    } else {
                        showToast(data.message, 'danger');
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                    }
                })
                .catch(err => {
                    console.error("Erro na descautela:", err);
                    showToast("Erro de comunicação com o servidor", "danger");
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                });
        });
    })();
</script>