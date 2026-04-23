<?php
// pages/gerenciar_combustiveis.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/CombustivelDAO.php';

if (!in_array(USER_AUTH['perfil'], ['Administrador', 'Gestor Central'])) {
    echo "<div class='alert alert-danger'>Acesso restrito à Gestão Central.</div>";
    exit;
}

$dao = new CombustivelDAO();
$lista = $dao->listarTodos();
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 mb-0 text-gray-800">Gerenciar Tipos de Combustível</h1>
        <button class="btn btn-primary btn-sm" onclick="abrirModalCombustivel()">
            <i class="fas fa-plus me-1"></i> Novo Combustível
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo / Nome</th>
                            <th width="150" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lista as $c): ?>
                            <tr>
                                <td><?= $c['id'] ?></td>
                                <td><?= htmlspecialchars($c['tipo']) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-warning" onclick="abrirModalCombustivel(<?= $c['id'] ?>, '<?= htmlspecialchars($c['tipo']) ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="excluirCombustivel(<?= $c['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalCombustivel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCombustivel">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Novo Combustível</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="comb_id">
                    <div class="mb-3">
                        <label class="form-label">Nome do Combustível / Aditivo</label>
                        <input type="text" name="tipo" id="comb_tipo" class="form-control" placeholder="Ex: Diesel S10, Gasolina, ARLA 32..." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function abrirModalCombustivel(id = null, tipo = '') {
        document.getElementById('comb_id').value = id || '';
        document.getElementById('comb_tipo').value = tipo;
        document.getElementById('modalTitulo').innerText = id ? 'Editar Combustível' : 'Novo Combustível';
        new bootstrap.Modal(document.getElementById('modalCombustivel')).show();
    }

    document.getElementById('formCombustivel').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('api/processar_combustivel.php?acao=salvar', {
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
                    bootstrap.Modal.getInstance(document.getElementById('modalCombustivel')).hide();
                    loadPage('pages/gerenciar_combustiveis.php');
                } else {
                    showToast(data.message, 'danger');
                }
            });
    });

    function excluirCombustivel(id) {
        if (!confirm("Deseja excluir este combustível?")) return;
        const formData = new FormData();
        formData.append('id', id);

        fetch('api/processar_combustivel.php?acao=excluir', {
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
                    loadPage('pages/gerenciar_combustiveis.php');
                } else {
                    showToast(data.message, 'danger');
                }
            });
    }
</script>