<?php
// pages/confirmar_recebimento.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/MovimentacaoDAO.php';

$mDAO = new MovimentacaoDAO();
$pendentes = $mDAO->listarTransferenciasPendentes(USER_AUTH);
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Confirmar Recebimento de Viatura</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Viaturas em Trânsito para sua Unidade</h6>
                    <span class="badge bg-warning text-dark"><?= count($pendentes) ?> pendente(s)</span>
                </div>
                <div class="card-body">
                    <?php if (empty($pendentes)): ?>
                        <div class="text-center p-5">
                            <i class="fas fa-truck-loading fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">Não há viaturas aguardando confirmação de chegada para seu perfil.</p>
                            <a href="pages/home.php" class="btn btn-sm btn-outline-primary">Voltar ao Início</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Viatura</th>
                                        <th>Origem</th>
                                        <th>Data Envio</th>
                                        <th>Documento</th>
                                        <th class="text-center">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendentes as $p): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold"><?= htmlspecialchars($p['prefixo'] . ' ' . $p['numero']) ?></span><br>
                                                <small class="text-muted"><?= htmlspecialchars($p['placa']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($p['unidade_origem']) ?></td>
                                            <td class="small"><?= date('d/m/Y H:i', strtotime($p['data_movimentacao'])) ?></td>
                                            <td class="small text-truncate" style="max-width: 150px;"><?= htmlspecialchars($p['documento_autorizacao']) ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-success btn-sm fw-bold"
                                                    onclick="confirmarChegada(<?= $p['id'] ?>, '<?= $p['prefixo'] . ' ' . $p['numero'] ?>')">
                                                    <i class="fas fa-check-double me-1"></i> Confirmar Chegada
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
        </div>
    </div>
</div>

<script>
    (function() {
        window.confirmarChegada = function(id, vtr) {
            if (!confirm(`Confirma que a viatura ${vtr} chegou fisicamente à unidade?`)) return;

            const fd = new FormData();
            fd.append('id', id);

            fetch('api/processar_movimentacao.php?acao=confirmar', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: fd
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        // Recarrega a página para limpar a lista
                        loadPage('pages/confirmar_recebimento.php');
                        // Atualiza o sino se houver função global
                        if (typeof window.atualizarNotificacoes === 'function') window.atualizarNotificacoes();
                    } else {
                        showToast(data.message, 'danger');
                    }
                })
                .catch(err => showToast("Erro ao processar recebimento", "danger"));
        };
    })();
</script>