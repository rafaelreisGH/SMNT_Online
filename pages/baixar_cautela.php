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
            <form id="formDescautela">
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
                    </div>

                    <div class="mt-4 p-3 bg-light border rounded">
                        <label class="form-label fw-bold text-danger">Relatar Novo Dano / Avaria</label>
                        <small class="text-muted d-block mb-2">Se a viatura retornou com algum dano que não estava presente na saída ou na lista de acessórios, descreva aqui e anexe fotos, se necessário.</small>
                        <textarea name="novo_dano_obs" class="form-control mb-2" rows="2"></textarea>
                        <input type="file" name="fotos[]" class="form-control" multiple accept="image/*">
                    </div>

                    <div id="containerOcorrencias"></div>

                    <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="adicionarOcorrencia()">
                        <i class="fas fa-plus"></i> Adicionar nova ocorrência/dano
                    </button>

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
        // Função para alternar visibilidade do campo de justificativa
        window.toggleJust = function(sel) {
            const input = sel.closest('.row').querySelector('.input-justifica');
            if (sel.value === '0') {
                input.classList.remove('d-none');
                input.required = true;
            } else {
                input.classList.add('d-none');
                input.value = '';
                input.required = false;
            }
        };

        document.getElementById('formDescautela').addEventListener('submit', function(e) {
            e.preventDefault();

            // Coleta os dados do checklist
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

            // Executa o envio
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
                        window.showToast(data.message, 'success');

                        // Redirecionamento Garantido
                        setTimeout(() => {
                            if (typeof window.loadPage === 'function') {
                                window.loadPage('pages/pesquisar_cautelas.php');
                            } else {
                                window.location.href = 'pesquisar_cautelas.php';
                            }
                        }, 1200);
                    } else {
                        window.showToast(data.message, 'danger');
                    }
                })
                .catch(err => {
                    console.error("Erro na requisição:", err);
                    window.showToast("Erro ao processar o servidor.", "danger");
                });
        });
    })();
</script>
<script>
    function adicionarOcorrencia() {
        const container = document.getElementById('containerOcorrencias');
        const index = document.querySelectorAll('.ocorrencia-item').length;

        const html = `
        <div class="card mb-3 p-3 ocorrencia-item">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="ocorrencias[${index}][tipo]" class="form-select form-select-sm" required>
                        <option value="Dano">Dano na Viatura</option>
                        <option value="Extravio">Acessório Faltante</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <input type="text" name="ocorrencias[${index}][desc]" class="form-control form-control-sm" placeholder="Descrição curta" required>
                </div>
                <div class="col-md-4">
                    <input type="file" name="fotos_${index}[]" class="form-control form-control-sm" multiple accept="image/*">
                </div>
            </div>
        </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }
</script>