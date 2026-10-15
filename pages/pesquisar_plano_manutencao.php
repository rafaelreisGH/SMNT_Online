<?php
// pages/pesquisar_plano_manutencao.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/PlanoManutencaoDAO.php';

$pDAO = new PlanoManutencaoDAO();
$planos = $pDAO->listarTodos();
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 mb-0 text-gray-800">Planos de Manutenção</h1>
        <button class="btn btn-sm btn-primary shadow-sm" onclick="loadPage('pages/cadastro_plano_manutencao.php')">
            <i class="fas fa-plus fa-sm text-white-50 me-1"></i> Criar Novo Plano
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Planos Cadastrados</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Descrição do Plano</th>
                            <th>Qtd. de Regras</th>
                            <th class="text-center">Ver Regras</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($planos as $p): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($p['descricao']) ?></td>
                            <td><?= $p['total_regras'] ?> itens configurados</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-info" 
                                        onclick="verRegras(<?= $p['id'] ?>, '<?= htmlspecialchars($p['descricao']) ?>')">
                                    <i class="fas fa-list-ul"></i>
                                </button>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning" title="Editar Plano" disabled>
                                    <i class="fas fa-edit"></i>
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

<!-- Modal para Visualizar Regras -->
<div class="modal fade" id="modalRegras" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title text-primary" id="modalTitulo">Regras do Plano</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBodyRegras">
                <!-- Conteúdo via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
window.verRegras = function(id, titulo) {
    const modal = new bootstrap.Modal(document.getElementById('modalRegras'));
    const body = document.getElementById('modalBodyRegras');
    document.getElementById('modalTitulo').innerText = "Regras: " + titulo;
    
    body.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
    modal.show();

    fetch(`api/get_regras_plano.php?id=${id}`, {
        headers: { 'Authorization': 'Bearer ' + localStorage.getItem('smnt_token') }
    })
    .then(r => r.text())
    .then(html => body.innerHTML = html);
}
</script>