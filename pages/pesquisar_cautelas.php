<?php
// pages/pesquisar_cautelas.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/CautelaDAO.php';

$dao = new CautelaDAO();
$cautelas = $dao->buscarCautelas();
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 mb-0 text-gray-800">Gestão de Cautelas</h1>
        <button class="btn btn-primary btn-sm shadow-sm" onclick="loadPage('pages/abrir_cautela.php')">
            <i class="fas fa-plus fa-sm me-1"></i> Nova Cautela
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Viatura</th>
                            <th>Condutor</th>
                            <th>Início</th>
                            <th>Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cautelas as $c): ?>
                            <tr>
                                <td><strong><?= $c['prefixo'] . ' ' . $c['numero'] ?></strong></td>
                                <td><?= htmlspecialchars($c['condutor_nome']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($c['data_inicio'])) ?></td>
                                <td>
                                    <span class="badge <?= $c['status'] == 'Ativa' ? 'bg-success' : ($c['status'] == 'Pendente' ? 'bg-warning' : 'bg-info') ?>">
                                        <?= $c['status'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <!-- Botão EDITAR (Apenas se Pendente e for Gestor) -->
                                    <?php if ($c['status'] == 'Pendente' && USER_AUTH['perfil'] != 'Condutor'): ?>
                                        <button class="btn btn-sm btn-outline-warning" title="Editar Rascunho"
                                            onclick="loadPage('pages/editar_cautela.php?id=<?= $c['id'] ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <?php endif; ?>

                                    <!-- Botão CANCELAR (Se Pendente ou Disponível e for Gestor) -->
                                    <?php if (in_array($c['status'], ['Pendente', 'Disponível']) && USER_AUTH['perfil'] != 'Condutor'): ?>
                                        <button class="btn btn-sm btn-outline-danger ms-1" title="Cancelar Cautela"
                                            onclick="cancelarCautela(<?= $c['id'] ?>)">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    <?php endif; ?>

                                    <!-- Botão ASSINAR (Se Disponível e for o Condutor da cautela) -->
                                    <?php if ($c['status'] == 'Disponível' && USER_AUTH['perfil'] == 'Condutor'): ?>
                                        <button class="btn btn-sm btn-success" onclick="loadPage('pages/assinar_cautela.php?id=<?= $c['id'] ?>')">
                                            <i class="fas fa-pen-fancy me-1"></i> Assinar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    window.cancelarCautela = function(id) {
        if (!confirm("Deseja realmente cancelar esta cautela? Ela não poderá mais ser assinada.")) return;

        const fd = new FormData();
        fd.append('id', id);

        fetch('api/processar_cautela.php?acao=cancelar', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                },
                body: fd
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.showToast(data.message, 'success');
                    loadPage('pages/pesquisar_cautelas.php'); // Recarrega a lista
                } else {
                    window.showToast(data.message, 'danger');
                }
            });
    };
</script>