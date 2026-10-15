<?php
// pages/gestao_acessorios.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/AcessorioDAO.php';
require_once __DIR__ . '/../components/DAO/ViaturaDAO.php'; // Para pegar categorias

$db = Conexao::getConexao();
$dao = new AcessorioDAO();

$acessorios = $dao->listarTodos();
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Gestão de Acessórios e Equipamentos</h1>

    <div class="row">
        <!-- Coluna de Cadastro (Esquerda) -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Novo Item</h6>
                </div>
                <div class="card-body">
                    <form id="formNovoAcessorio">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nome do Acessório</label>
                            <input type="text" name="nome" class="form-control" placeholder="Ex: Macaco Hidráulico" required>
                        </div>
                        <!-- AJUSTE DE ALINHAMENTO AQUI -->
                        <div class="mb-3 ps-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="eh_universal" id="checkUniversal" value="1">
                                <label class="form-check-label small" for="checkUniversal">Item presente em TODAS as viaturas</label>
                            </div>
                        </div>
                        <button class="btn btn-primary w-100 py-2 shadow-sm" type="submit" id="btnSalvarAcessorio">
                            <i class="fas fa-plus me-2"></i>Cadastrar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Coluna de Listagem Geral (Direita) -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Itens Cadastrados no Sistema</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome do Acessório</th>
                                    <th>Tipo / Configuração</th>
                                    <th class="text-end">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($acessorios as $a): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($a['nome']) ?></td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                    <?= $a['eh_universal'] ? 'checked' : '' ?>
                                                    onclick="toggleUniversal(<?= $a['id'] ?>, this.checked ? 1 : 0)">
                                                <label class="form-label small mb-0">
                                                    <?= $a['eh_universal'] ? '<span class="text-primary">Universal</span>' : '<span class="text-muted">Específico</span>' ?>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <!-- Botão de exclusão (opcional) -->
                                            <button class="btn btn-sm btn-outline-danger border-0" title="Excluir" onclick="excluirAcessorio(<?= $a['id'] ?>)">
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
        const form = document.getElementById('formNovoAcessorio');
        const token = localStorage.getItem('smnt_token');

        // 1. Cadastro de Novo Item
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('btnSalvarAcessorio');
                btn.disabled = true;

                fetch('api/processar_acessorios.php?acao=cadastrar', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + token
                        },
                        body: new FormData(this)
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            loadPage('pages/gestao_acessorios.php');
                        } else {
                            showToast(data.message, 'danger');
                            btn.disabled = false;
                        }
                    })
                    .catch(() => {
                        showToast("Erro na comunicação", "danger");
                        btn.disabled = false;
                    });
            });
        }

        // 2. Função para alternar Universal/Específico (Disponível no clique)
        window.toggleUniversal = function(id, status) {
            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', status);

            fetch('api/processar_acessorios.php?acao=toggle_universal', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    },
                    body: fd
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        loadPage('pages/gestao_acessorios.php');
                    } else {
                        showToast(data.message, 'danger');
                    }
                });
        };

        // 3. Função para Excluir
        window.excluirAcessorio = function(id) {
            if (!confirm("Deseja realmente excluir este acessório da lista mestra?")) return;

            const fd = new FormData();
            fd.append('id', id);

            fetch('api/processar_acessorios.php?acao=excluir', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    },
                    body: fd
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        loadPage('pages/gestao_acessorios.php');
                    } else {
                        // Se cair aqui, provavelmente o banco impediu por haver vínculo (RESTRICT)
                        showToast("Não é possível excluir: este item já está em uso em cautelas ou registros.", "danger");
                    }
                });
        };
    })();
</script>