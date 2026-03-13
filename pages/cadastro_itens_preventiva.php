<?php
// pages/cadastro_itens_preventiva.php

require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/ItemPreventivaDAO.php';
$dao = new ItemPreventivaDAO();
$itens = $dao->listarTodos();
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Itens de Manutenção Preventiva</h1>

    <div class="row">
        <!-- Coluna de Cadastro -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Novo Item</h6>
                </div>
                <div class="card-body">
                    <form id="formNovoItem">
                        <div class="mb-3">
                            <label class="form-label">Nome do Item/Serviço</label>
                            <input type="text" name="nome" class="form-control" placeholder="Ex: Troca de Óleo de Motor" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Adicionar Item</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Coluna da Lista -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Itens Cadastrados</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Status</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($itens as $it): ?>
                                    <tr>
                                        <td><?= $it['id'] ?></td>
                                        <td><?= htmlspecialchars($it['nome']) ?></td>
                                        <td>
                                            <span class="badge <?= $it['status'] === 'Ativo' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $it['status'] ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm <?= $it['status'] === 'Ativo' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                                onclick="mudarStatusItem(<?= $it['id'] ?>, '<?= $it['status'] === 'Ativo' ? 'Inativo' : 'Ativo' ?>')"
                                                title="<?= $it['status'] === 'Ativo' ? 'Desativar' : 'Ativar' ?>">
                                                <i class="fas <?= $it['status'] === 'Ativo' ? 'fa-pause' : 'fa-play' ?>"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="excluirItem(<?= $it['id'] ?>)">
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
    </div>
</div>

<script>
    (function() {
        // Cadastro
        document.getElementById('formNovoItem').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('api/processar_item_preventiva.php?acao=cadastrar', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        loadPage('pages/gestao_itens_preventiva.php');
                    } else {
                        showToast(data.message, 'danger');
                    }
                });
        });

        // Exclusão
        window.excluirItem = function(id) {
            if (!confirm("Deseja realmente remover este item?")) return;

            const formData = new FormData();
            formData.append('id', id);

            fetch('api/processar_item_preventiva.php?acao=excluir', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        loadPage('pages/gestao_itens_preventiva.php');
                    } else {
                        alert(data.message); // Usando alert aqui para mensagens longas de erro de integridade
                    }
                });
        }
    })();

    window.mudarStatusItem = function(id, status) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', status);

        fetch('api/processar_item_preventiva.php?acao=status', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                showToast(data.message);
                loadPage('pages/gestao_itens_preventiva.php');
            });
    }
</script>