<?php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/ViaturaDAO.php';
require_once __DIR__ . '/../components/DAO/ManutencaoDAO.php';
require_once __DIR__ . '/../components/DAO/ItemPreventivaDAO.php';

$vDAO = new ViaturaDAO();
$mDAO = new ManutencaoDAO();
$iDAO = new ItemPreventivaDAO();

try {
    // Carrega apenas o que o usuário pode ver (RBAC aplicado nos métodos)
    $viaturas = $vDAO->listarTodasParaSelect();

    // CORREÇÃO: Agora chamando via DAO em vez de variável $conn inexistente
    $oficinas = $mDAO->listarOficinasAtivas();

    $itensPrev = $iDAO->listarTodos();
} catch (Exception $e) {
    error_log("Erro ao carregar dados de manutenção: " . $e->getMessage());
    $viaturas = [];
    $oficinas = [];
    $itensPrev = [];
}
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Abrir Ordem de Manutenção</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Entrada em Oficina</h6>
                </div>
                <div class="card-body">
                    <form id="formManutencao">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Viatura</label>
                                <select class="form-select" name="viatura_id" required>
                                    <option value="">Selecione a viatura...</option>
                                    <?php foreach ($viaturas as $v): ?>
                                        <option value="<?= $v['id'] ?>"><?= $v['prefixo'] . ' ' . $v['numero'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Oficina</label>
                                <select class="form-select" name="oficina_id" required>
                                    <option value="">Selecione a oficina...</option>
                                    <?php foreach ($oficinas as $o): ?>
                                        <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nome_fantasia']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nº da OS (Prime)</label>
                                <input type="number" name="ordem_de_servico" class="form-control" placeholder="Ex: 1234">
                            </div>


                            <div class="col-md-4">
                                <label class="form-label">KM / Horas de Entrada</label>
                                <input type="number" name="quilometragem" class="form-control" required placeholder="Valor do odômetro">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tipo de Serviço</label>
                                <select class="form-select" name="tipo_manutencao" id="tipo_manutencao">
                                    <option value="Corretiva">Corretiva (Conserto de Falha)</option>
                                    <option value="Preventiva">Preventiva (Revisão Periódica)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Status Inicial</label>
                                <select class="form-select" name="status">
                                    <option value="Pendente">Aguardando Início</option>
                                    <option value="Em Orçamento">Em Orçamento</option>
                                    <option value="Em Execução">Em Execução</option>
                                </select>
                            </div>

                            <!-- Se for Preventiva, mostra Itens -->
                            <div class="col-12 d-none" id="row_preventiva">
                                <div class="alert alert-success d-flex align-items-center">
                                    <div class="w-100">
                                        <label class="form-label fw-bold">Item do Plano de Manutenção sendo executado:</label>
                                        <select class="form-select" name="item_preventiva_id">
                                            <option value="">Selecione o item (ex: Óleo, Bateria, Bomba)...</option>
                                            <?php foreach ($itensPrev as $it): if ($it['status'] == 'Ativo'): ?>
                                                    <option value="<?= $it['id'] ?>"><?= htmlspecialchars($it['nome']) ?></option>
                                            <?php endif;
                                            endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Descrição do Problema / Observações de Entrada</label>
                                <textarea class="form-control" name="descricao_problema" rows="3"></textarea>
                            </div>
                        </div>

                        <hr class="my-4">
                        <button type="submit" class="btn btn-outline-primary px-4">
                            <i class="fas fa-file-medical me-2"></i> Abrir Ordem de Manutenção
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const tipo = document.getElementById('tipo_manutencao');
        const rowPrev = document.getElementById('row_preventiva');

        tipo.addEventListener('change', function() {
            if (this.value === 'Preventiva') rowPrev.classList.remove('d-none');
            else rowPrev.classList.add('d-none');
        });

        document.getElementById('formManutencao').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const formData = new FormData(this);

            btn.disabled = true;

            fetch('api/processar_manutencao.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Notifica o sucesso
                        if (window.showToast) window.showToast(data.message, 'success');

                        // Pequeno delay para o usuário ver o que aconteceu antes de mudar de tela
                        setTimeout(() => {
                            loadPage('pages/pesquisar_manutencoes.php');
                        }, 800);
                    } else {
                        if (window.showToast) window.showToast(data.message, 'danger');
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    if (window.showToast) window.showToast("Erro na comunicação com o servidor", 'danger');
                    btn.disabled = false;
                });
        });
    })();
</script>