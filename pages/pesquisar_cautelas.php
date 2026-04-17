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
                                    <!-- NOVO: Visualizar Detalhes (Sempre disponível) -->
                                    <button class="btn btn-sm btn-outline-info"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetCautela"
                                        data-id="<?= $c['id'] ?>"
                                        title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <!-- 2. Botão PDF (Consolidado) -->
                                    <?php if (in_array($c['status'], ['Ativa', 'Concluída'])): ?>
                                        <a href="api/gerar_pdf_cautela.php?id=<?= $c['id'] ?>"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-danger ms-1 btn-pdf-download"
                                            title="Baixar PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- 1. Ação de EDITAR: Apenas Gestores e se estiver Pendente -->
                                    <?php if ($c['status'] == 'Pendente' && USER_AUTH['perfil'] != 'Condutor'): ?>
                                        <button class="btn btn-sm btn-outline-warning ms-1" title="Editar Rascunho"
                                            onclick="loadPage('pages/editar_cautela.php?id=<?= $c['id'] ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <?php endif; ?>

                                    <!-- 2. Ação de ASSINAR: Apenas se o status for Disponível E o usuário for o Condutor -->
                                    <?php if ($c['status'] == 'Disponível' && USER_AUTH['perfil'] == 'Condutor'): ?>
                                        <button class="btn btn-sm btn-success fw-bold ms-1" title="Assinar Recebimento"
                                            onclick="loadPage('pages/assinar_cautela.php?id=<?= $c['id'] ?>')">
                                            <i class="fas fa-pen-fancy me-1"></i> Assinar
                                        </button>
                                    <?php endif; ?>

                                    <!-- 3. Ação de CANCELAR: Apenas Gestores -->
                                    <?php if (in_array($c['status'], ['Pendente', 'Disponível']) && USER_AUTH['perfil'] != 'Condutor'): ?>
                                        <button class="btn btn-sm btn-outline-danger ms-1" title="Cancelar Cautela"
                                            onclick="cancelarCautela(<?= $c['id'] ?>)">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    <?php endif; ?>

                                    <!-- 4. Informativo para o Gestor: Disponibilizado -->
                                    <?php if ($c['status'] == 'Disponível' && USER_AUTH['perfil'] != 'Condutor'): ?>
                                        <button class="btn btn-sm btn-outline-warning ms-1" title="Aguardando Assinatura" disabled>
                                            <i class="fas fa-hourglass-half me-1"></i> Aguardando
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
<div class="modal fade" id="modalDetCautela" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detalhes da Cautela</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bodyModalDetCautela">
                <!-- Conteúdo carregado via AJAX -->
            </div>
        </div>
    </div>
</div>
<script>
    (function() {
        const token = localStorage.getItem('smnt_token');

        // AJUSTE DO PDF: Insere o token no link no momento do clique
        document.querySelectorAll('.btn-pdf-download').forEach(link => {
            link.addEventListener('click', function(e) {
                const originalHref = this.getAttribute('href').split('&token=')[0];
                this.setAttribute('href', `${originalHref}&token=${token}`);
            });
        });

        // AJUSTE DO MODAL: Garante que o conteúdo seja injetado
        const modalDet = document.getElementById('modalDetCautela');
        if (modalDet) {
            modalDet.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const body = document.getElementById('bodyModalDetCautela');

                body.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>';

                fetch(`api/get_detalhes_cautela.php?id=${id}`, {
                        headers: {
                            'Authorization': 'Bearer ' + token
                        }
                    })
                    .then(r => {
                        if (r.status === 401) return "<div class='alert alert-danger'>Sessão expirada.</div>";
                        return r.text();
                    })
                    .then(html => {
                        body.innerHTML = html;
                    })
                    .catch(err => {
                        body.innerHTML = "<div class='alert alert-danger'>Erro ao carregar dados.</div>";
                    });
            });
        }
    })();
</script>