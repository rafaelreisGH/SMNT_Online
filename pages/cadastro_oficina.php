<?php
// pages/cadastro_oficina.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/OficinaDAO.php';

$dao = new OficinaDAO();
$oficinas = $dao->listarTodas();
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Cadastro de Oficinas</h1>

    <div class="row">
        <!-- Coluna do Formulário -->
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Nova Oficina Credenciada</h6>
                </div>
                <div class="card-body">
                    <form id="formOficina">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold">Nome Fantasia (Obrigatório)</label>
                                <input type="text" name="nome_fantasia" class="form-control" placeholder="Ex: Oficina do João" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Razão Social</label>
                                <input type="text" name="razao_social" class="form-control" placeholder="Ex: João da Silva ME">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">CNPJ (apenas números)</label>
                                <input type="text" name="cnpj" class="form-control" maxlength="14" placeholder="00000000000000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Telefone</label>
                                <input type="text" name="telefone" class="form-control" placeholder="(65) 99999-9999">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">E-mail de Contato</label>
                                <input type="email" name="email" class="form-control" placeholder="oficina@exemplo.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Endereço Completo</label>
                                <textarea name="endereco" class="form-control" rows="2" placeholder="Rua, Número, Bairro, Cidade-UF"></textarea>
                            </div>
                        </div>
                        <hr class="my-4">
                        <button type="submit" class="btn btn-danger w-100 fw-bold">
                            <i class="fas fa-save me-2"></i>Cadastrar Oficina
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Coluna da Listagem -->
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Oficinas no Sistema</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Oficina</th>
                                    <th>Status</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($oficinas as $o): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($o['nome_fantasia']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($o['telefone'] ?? 'Sem telefone') ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $o['status'] === 'ativa' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= ucfirst($o['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <!-- Botão para Alternar Status -->
                                            <button class="btn btn-sm btn-status-toggle <?= $o['status'] === 'ativa' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                                onclick="mudarStatusOficina(this, <?= $o['id'] ?>)"
                                                data-status="<?= $o['status'] ?>"
                                                title="<?= $o['status'] === 'ativa' ? 'Inativar' : 'Ativar' ?>">
                                                <i class="fas <?= $o['status'] === 'ativa' ? 'fa-pause' : 'fa-play' ?>"></i>
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
        // 1. Envio do Formulário de Cadastro
        document.getElementById('formOficina').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const formData = new FormData(this);

            btn.disabled = true;

            fetch('api/processar_oficina.php?acao=cadastrar', {
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
                        loadPage('pages/cadastro_oficina.php'); // Recarrega o miolo
                    } else {
                        showToast(data.message, 'danger');
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    showToast("Erro na comunicação com o servidor", "danger");
                    btn.disabled = false;
                });
        });

        // 2. Função para Ativar/Desativar Oficina
        window.mudarStatusOficina = function(id, novoStatus) {
            const confirmMsg = novoStatus === 'ativa' ? 'Deseja reativar esta oficina?' : 'Deseja inativar esta oficina? Ela não aparecerá mais em novos registros.';
            if (!confirm(confirmMsg)) return;

            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', novoStatus);

            fetch('api/processar_oficina.php?acao=status', {
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
                        loadPage('pages/cadastro_oficina.php');
                    } else {
                        showToast(data.message, 'danger');
                    }
                });
        }
    })();

    window.mudarStatusOficina = function(btn, id) {
        // 1. Identifica o status atual e o novo status baseado no atributo data
        const statusAtual = btn.getAttribute('data-status');
        const novoStatus = (statusAtual === 'ativa') ? 'inativa' : 'ativa';

        const confirmMsg = novoStatus === 'ativa' ?
            'Deseja reativar esta oficina?' :
            'Deseja inativar esta oficina?';

        if (!confirm(confirmMsg)) return;

        // Desabilita o botão temporariamente para evitar cliques duplos
        btn.disabled = true;

        const fd = new FormData();
        fd.append('id', id);
        fd.append('status', novoStatus);

        fetch('api/processar_oficina.php?acao=status', {
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

                    // --- ATUALIZAÇÃO "MÁGICA" DA UI SEM RECARREGAR ---
                    const tr = btn.closest('tr'); // Pega a linha da tabela
                    const badge = tr.querySelector('.badge');
                    const icon = btn.querySelector('i');

                    if (novoStatus === 'ativa') {
                        // Ajusta o Badge
                        badge.className = 'badge bg-success';
                        badge.innerText = 'Ativa';

                        // Ajusta o Botão (vira o botão de pausa)
                        btn.className = 'btn btn-sm btn-status-toggle btn-outline-warning';
                        btn.title = 'Inativar';
                        btn.setAttribute('data-status', 'ativa');
                        icon.className = 'fas fa-pause';
                    } else {
                        // Ajusta o Badge
                        badge.className = 'badge bg-secondary';
                        badge.innerText = 'Inativa';

                        // Ajusta o Botão (vira o botão de play)
                        btn.className = 'btn btn-sm btn-status-toggle btn-outline-success';
                        btn.title = 'Ativar';
                        btn.setAttribute('data-status', 'inativa');
                        icon.className = 'fas fa-play';
                    }
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(err => showToast("Erro ao processar", "danger"))
            .finally(() => {
                btn.disabled = false; // Reabilita o botão
            });
    }
</script>