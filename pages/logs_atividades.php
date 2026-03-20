<?php
// pages/logs_atividades.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/LogDAO.php';

$logDAO = new LogDAO();
$logs = $logDAO->buscarLogs();
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Log de Atividades</h1>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Auditoria do Sistema</h6>
                    <small class="text-muted">Mostrando as últimas 100 ações</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle" id="tabelaLogs">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Militar</th>
                                    <th>Unidade</th>
                                    <th>Ação</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center p-4">Nenhum log registrado até o momento.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $l): ?>
                                        <tr>
                                            <td class="small"><?= date('d/m/Y H:i:s', strtotime($l['criada_em'])) ?></td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($l['usuario_nome']) ?></div>
                                                <div class="small text-muted"><?= htmlspecialchars($l['perfil_nome']) ?></div>
                                            </td>
                                            <td class="small"><?= htmlspecialchars($l['unidade_nome']) ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($l['acao']) ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small"><?= htmlspecialchars($l['ip'] ?? 'N/A') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Se precisar de alguma inicialização específica para esta página
    console.log("Página de logs carregada.");
</script>