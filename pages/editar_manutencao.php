<?php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/ManutencaoDAO.php';
require_once __DIR__ . '/../components/DAO/ViaturaDAO.php';
require_once __DIR__ . '/../components/DAO/ItemPreventivaDAO.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$mDAO = new ManutencaoDAO();
$vDAO = new ViaturaDAO();
$iDAO = new ItemPreventivaDAO();

$manutencao = $mDAO->buscarDetalhesPorId($id); // Usamos o método de detalhes que já traz nomes
$viaturas = $vDAO->listarTodasParaSelect();
$oficinas = $mDAO->listarOficinasAtivas();
$itensPrev = $iDAO->listarTodos();

if (!$manutencao) {
    echo "<div class='alert alert-danger'>Manutenção não encontrada ou acesso negado.</div>";
    exit;
}
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Editar Ordem de Manutenção</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Alterar Dados da OS: <?= $manutencao['ordem_de_servico'] ?: $manutencao['id'] ?></h6>
                    <a href="pages/pesquisar_manutencoes.php" class="btn btn-sm btn-secondary">Voltar</a>
                </div>
                <div class="card-body">
                    <form id="formEditarManutencao">
                        <input type="hidden" name="id" value="<?= $manutencao['id'] ?>">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nº Ordem de Serviço (OS)</label>
                                <input type="number" name="ordem_de_servico" class="form-control" value="<?= $manutencao['ordem_de_servico'] ?>">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Viatura</label>
                                <select class="form-select" name="viatura_id" required>
                                    <?php foreach ($viaturas as $v): ?>
                                        <option value="<?= $v['id'] ?>" <?= $v['id'] == $manutencao['viatura_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($v['prefixo'] . ' ' . $v['numero']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Oficina</label>
                                <select class="form-select" name="oficina_id" required>
                                    <?php foreach ($oficinas as $o): ?>
                                        <option value="<?= $o['id'] ?>" <?= $o['id'] == $manutencao['oficina_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($o['nome_fantasia']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status da Manutenção</label>
                                <select class="form-select" name="status">
                                    <?php
                                    $statusList = ['Pendente', 'Em Orçamento', 'Aguardando Peça', 'Em Execução', 'Concluída', 'Cancelada'];
                                    foreach ($statusList as $s): ?>
                                        <option value="<?= $s ?>" <?= $manutencao['status'] == $s ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">KM / Horas</label>
                                <input type="number" name="quilometragem" class="form-control" value="<?= $manutencao['quilometragem'] ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" name="tipo_manutencao">
                                    <option value="Corretiva" <?= $manutencao['tipo_manutencao'] == 'Corretiva' ? 'selected' : '' ?>>Corretiva</option>
                                    <option value="Preventiva" <?= $manutencao['tipo_manutencao'] == 'Preventiva' ? 'selected' : '' ?>>Preventiva</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Data de Entrada</label>
                                <input type="datetime-local" name="data_entrada" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($manutencao['data_entrada'])) ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Descrição do Problema</label>
                                <textarea class="form-control" name="descricao_problema" rows="3"><?= htmlspecialchars($manutencao['descricao_problema']) ?></textarea>
                            </div>
                        </div>

                        <hr class="my-4">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i> Salvar Alterações
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        document.getElementById('formEditarManutencao').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;

            fetch('api/processar_edicao_manutencao.php', {
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
                    btn.disabled = false;
                });
        });
    })();
</script>