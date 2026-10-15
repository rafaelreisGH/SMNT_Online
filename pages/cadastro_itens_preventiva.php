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
    /**
     * Esta página é um "miolo". Ela não precisa carregar o main-ajax.js 
     * porque a "casca" (index/footer) já o carregou para nós.
     */

    // Função de Alterar Status
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
                if (data.success) {
                    // Chama a função que você definiu no main-ajax.js
                    window.showToast(data.message, 'success');

                    // Recarrega a página após um curto delay
                    setTimeout(() => {
                        loadPage('pages/cadastro_itens_preventiva.php');
                    }, 150);
                } else {
                    window.showToast(data.message, 'danger');
                }
            });
    };

    // Função de Exclusão
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
                    window.showToast(data.message, 'success');
                    setTimeout(() => loadPage('pages/cadastro_itens_preventiva.php'), 150);
                } else {
                    alert(data.message); // Mensagens de erro de banco (FK) costumam ser longas
                }
            });
    };

    // Lógica do Formulário de Cadastro
    (function() {
        const form = document.getElementById('formNovoItem');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = this.querySelector('button[type="submit"]');
                btn.disabled = true;

                fetch('api/processar_item_preventiva.php?acao=cadastrar', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                        },
                        body: new FormData(this)
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            window.showToast(data.message, 'success');
                            loadPage('pages/cadastro_itens_preventiva.php');
                        } else {
                            window.showToast(data.message, 'danger');
                            btn.disabled = false;
                        }
                    })
                    .catch(() => {
                        window.showToast("Erro na comunicação", "danger");
                        btn.disabled = false;
                    });
            });
        }
    })();
</script>