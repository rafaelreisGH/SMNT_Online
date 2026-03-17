<?php
// pages/pesquisar_condutor.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/CondutorDAO.php';
require_once __DIR__ . '/../components/DAO/UnidadeDAO.php';

$condutorDAO = new CondutorDAO();
$results = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $results = $condutorDAO->buscarCondutores($_POST);
}

$uDAO = new UnidadeDAO();
$unidadesAgrupadas = $uDAO->listarAgrupadasPorComando();
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Pesquisa de Condutores</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
                </div>
                <div class="card-body">
                    <form id="formPesquisaCondutor">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nome</label>
                                <input type="text" class="form-control" name="nome">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">Todos</option>
                                    <option value="ativo">Ativo</option>
                                    <option value="inativo">Inativo</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Unidade</label>
                                <select class="form-select" name="ubm_id">
                                    <option value="">Todas</option>
                                    <?php foreach ($unidadesAgrupadas as $cmd => $ubms): ?>
                                        <optgroup label="<?= htmlspecialchars($cmd) ?>">
                                            <?php foreach ($ubms as $u): ?>
                                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['unidade_nome']) ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-search me-1"></i> Pesquisar</button>
                    </form>
                </div>
            </div>

            <div id="resultado-pesquisa-condutores">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <?php if (empty($results)): ?>
                                <div class="alert alert-warning">Nenhum condutor encontrado.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Nome / Posto</th>
                                                <th>Cat. CNH</th>
                                                <th>Unidade</th>
                                                <th>Status</th>
                                                <th class="text-center">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($results as $c): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($c['nome'] ?? '') ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($c['posto_grad'] ?? 'N/A') ?></small>
                                                    </td>
                                                    <td><?= htmlspecialchars($c['categoria_cnh'] ?? 'N/A') ?></td>
                                                    <td><?= htmlspecialchars($c['unidade_lotacao'] ?? 'N/A') ?></td>
                                                    <td>
                                                        <span class="badge <?= ($c['status'] ?? 'ativo') == 'ativo' ? 'bg-success' : 'bg-secondary' ?>">
                                                            <?= ucfirst(htmlspecialchars($c['status'] ?? 'ativo')) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalDetCondutor" data-id="<?= $c['id'] ?>" title="Detalhes">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="pages/editar_condutor.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button class="btn btn-sm <?= ($c['status'] ?? 'ativo') == 'ativo' ? 'btn-outline-secondary' : 'btn-outline-success' ?>"
                                                            onclick="toggleStatusCondutor(this, <?= $c['id'] ?>)"
                                                            data-status="<?= htmlspecialchars($c['status'] ?? 'ativo') ?>"
                                                            title="<?= ($c['status'] ?? 'ativo') == 'ativo' ? 'Desativar' : 'Ativar' ?>">
                                                            <i class="fas <?= ($c['status'] ?? 'ativo') == 'ativo' ? 'fa-pause' : 'fa-play' ?>"></i>
                                                        </button>
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

<div class="modal fade" id="modalDetCondutor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalhes do Condutor</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bodyModalCondutor"></div>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('formPesquisaCondutor');
        const area = document.getElementById('resultado-pesquisa-condutores');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            area.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
            fetch('pages/pesquisar_condutor.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: new FormData(this)
                })
                .then(r => r.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    area.innerHTML = doc.getElementById('resultado-pesquisa-condutores').innerHTML;
                });
        });

        const modal = document.getElementById('modalDetCondutor');
        if (modal) {
            modal.addEventListener('show.bs.modal', function(event) {
                const id = event.relatedTarget.getAttribute('data-id');
                const body = document.getElementById('bodyModalCondutor');
                body.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
                fetch(`api/get_detalhes_condutor.php?id=${id}`, {
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                        }
                    })
                    .then(r => r.text())
                    .then(html => body.innerHTML = html);
            });
        }

        window.toggleStatusCondutor = function(btn, id) {
            const currentStatus = btn.getAttribute('data-status');
            const nextStatus = currentStatus === 'ativo' ? 'inativo' : 'ativo';
            if (!confirm(`Alterar status para ${nextStatus}?`)) return;
            btn.disabled = true;
            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', nextStatus);
            fetch('api/processar_cadastro_condutor.php?acao=status', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: fd
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        const tr = btn.closest('tr');
                        const badge = tr.querySelector('.badge');
                        btn.setAttribute('data-status', nextStatus);
                        if (nextStatus === 'ativo') {
                            badge.className = 'badge bg-success';
                            badge.innerText = 'Ativo';
                            btn.className = 'btn btn-sm btn-outline-secondary';
                            btn.querySelector('i').className = 'fas fa-pause';
                        } else {
                            badge.className = 'badge bg-secondary';
                            badge.innerText = 'Inativo';
                            btn.className = 'btn btn-sm btn-outline-success';
                            btn.querySelector('i').className = 'fas fa-play';
                        }
                    } else {
                        showToast(data.message, 'danger');
                    }
                })
                .finally(() => btn.disabled = false);
        };
    })();
</script>