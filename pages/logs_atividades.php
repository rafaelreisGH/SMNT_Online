<?php
// pages/logs_atividades.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/LogDAO.php';

// TRAVA DE SEGURANÇA RBAC
if (!in_array(USER_AUTH['perfil'], ['Administrador', 'Gestor Central'])) {
    echo "<div class='container-fluid'><div class='alert alert-danger'>
            <i class='fas fa-lock me-2'></i>Acesso Negado: Você não tem permissão para visualizar os logs de auditoria.
          </div></div>";
    exit; // Interrompe o carregamento da página
}

$logDAO = new LogDAO();
$logs = $logDAO->buscarLogs(USER_AUTH);
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Log de Atividades</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Auditoria do Sistema</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
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
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <!-- CORREÇÃO AQUI: de criada_em para criado_em -->
                                <td class="small"><?= date('d/m/Y H:i:s', strtotime($l['criado_em'])) ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($l['usuario_nome'] ?? '') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($l['perfil_nome'] ?? '') ?></div>
                                </td>
                                <td class="small"><?= htmlspecialchars($l['unidade_nome'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= htmlspecialchars($l['acao'] ?? '') ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= htmlspecialchars($l['ip'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>