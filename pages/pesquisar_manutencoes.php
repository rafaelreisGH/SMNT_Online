<?php
// pages/pesquisar_manutencoes.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/ManutencaoDAO.php';
require_once __DIR__ . '/../components/DAO/ViaturaDAO.php';

$mDAO = new ManutencaoDAO();
$vDAO = new ViaturaDAO();

$results = [];
// Processa se houver POST (Vindo do AJAX)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $results = $mDAO->buscarManutencoes($_POST);
}

// Carrega dados reais para os filtros
$viaturas = $vDAO->listarTodasParaSelect();
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Pesquisa de Manutenções</h1>
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Filtros de Pesquisa</h6>
                </div>
                <div class="card-body">
                    <form id="formPesquisaManutencoes">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Viatura</label>
                                <select class="form-select" name="viatura_id">
                                    <option value="">Todas</option>
                                    <?php foreach ($viaturas as $v): ?>
                                        <option value="<?= $v['id'] ?>"><?= $v['prefixo'] . ' ' . $v['numero'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo</label>
                                <select class="form-select" name="tipo_manutencao">
                                    <option value="">Todos</option>
                                    <option value="Preventiva">Preventiva</option>
                                    <option value="Corretiva">Corretiva</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">Todos</option>
                                    <option value="Pendente">Pendente / Aguardando</option>
                                    <option value="Em Orçamento">Em Orçamento</option>
                                    <option value="Aguardando Peça">Aguardando Peça</option>
                                    <option value="Em Execução">Em Execução</option>
                                    <option value="Concluída">Concluída</option>
                                    <option value="Cancelada">Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nome da Oficina</label>
                                <input type="text" class="form-control" name="oficina_nome" placeholder="Busca por nome...">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search me-1"></i> Pesquisar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="resultado-pesquisa-manutencoes">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <?php if (empty($results)): ?>
                                <div class="alert alert-warning">Nenhum registro encontrado.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>O.S.</th>
                                                <th>Viatura</th>
                                                <th>Entrada</th>
                                                <th>Tipo</th>
                                                <th>Oficina</th>
                                                <th>Status</th>
                                                <th class="text-center">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($results as $m): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-light text-dark border">
                                                            <?= htmlspecialchars($m['ordem_de_servico'] ?? 'N/A') ?>
                                                        </span>
                                                    </td>
                                                    <td><strong><?= htmlspecialchars($m['viatura_prefixo'] . ' ' . $m['viatura_numero']) ?></strong></td>
                                                    <td><?= date('d/m/Y', strtotime($m['data_entrada'])) ?></td>
                                                    <td><?= $m['tipo_manutencao'] ?></td>
                                                    <td><?= htmlspecialchars($m['oficina_nome']) ?></td>
                                                    <td>
                                                        <span class="badge <?= $m['status'] == 'Concluída' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                            <?= $m['status'] ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-outline-info"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalDetalhesManutencao"
                                                            data-id="<?= $m['id'] ?>"
                                                            title="Detalhes">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <!-- Botão de Edição (Lápis) -->
                                                        <a href="pages/editar_manutencao.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-warning" title="Editar Manutenção">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($m['status'] != 'Concluída'): ?>
                                                            <a href="pages/finalizar_manutencao.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-success" title="Finalizar Manutenção">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalDetalhesManutencao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalhes da Ordem de Manutenção</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="conteudo-modal-manutencao">
                <!-- AJAX carrega aqui -->
            </div>
        </div>
    </div>
</div>
<script>
    (function() {
        const form = document.getElementById('formPesquisaManutencoes');
        const area = document.getElementById('resultado-pesquisa-manutencoes');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            area.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';

            fetch('pages/pesquisar_manutencoes.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    }, // TOKEN ADICIONADO
                    body: formData
                })
                .then(r => r.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    area.innerHTML = doc.getElementById('resultado-pesquisa-manutencoes').innerHTML;
                });
        });
        // NOVA FUNÇÃO PARA O MODAL
        const modal = document.getElementById('modalDetalhesManutencao');
        if (modal) {
            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const body = document.getElementById('conteudo-modal-manutencao');

                body.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';

                fetch(`api/get_detalhes_manutencao.php?id=${id}`, {
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                        }
                    })
                    .then(r => r.text())
                    .then(html => body.innerHTML = html);
            });
        }
    })();
</script>