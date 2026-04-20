<?php
// pages/pesquisar_avaria.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/ViaturaDAO.php';
require_once __DIR__ . '/../components/DAO/CondutorDAO.php';
require_once __DIR__ . '/../components/DAO/RegistroAvariaDAO.php';

$vDAO = new ViaturaDAO();
$cDAO = new CondutorDAO();
$aDAO = new RegistroAvariaDAO();

$results = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $results = $aDAO->buscarAvarias($_POST);
}

// Filtros respeitando o RBAC (métodos já ajustados anteriormente)
$viaturas = $vDAO->listarTodasParaSelect();
$condutores = $cDAO->listarAtivos();
$tiposAvaria = $aDAO->listarTiposAvaria();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Pesquisa de Avarias</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Formulário de Pesquisa -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Filtros de Pesquisa</h6>
                </div>
                <div class="card-body">
                    <form id="form-pesquisa-avaria" action="#" method="POST">
                        <ul class="nav nav-tabs mb-4" id="avariaSearchTab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="filtros-gerais-tab" data-bs-toggle="tab" data-bs-target="#filtros-gerais-pane" type="button">Geral</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="filtros-periodo-tab" data-bs-toggle="tab" data-bs-target="#filtros-periodo-pane" type="button">Período</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="avariaSearchTabContent">
                            <!-- Aba Geral -->
                            <div class="tab-pane fade show active" id="filtros-gerais-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Viatura</label>
                                        <select class="form-select" name="viatura_id">
                                            <option value="">Todas</option>
                                            <?php foreach ($viaturas as $v): ?>
                                                <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['prefixo'] . ' ' . $v['numero']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Condutor</label>
                                        <select class="form-select" name="condutor_id">
                                            <option value="">Todos</option>
                                            <?php foreach ($condutores as $c): ?>
                                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Componente</label>
                                        <select class="form-select" name="tipo_avaria_id">
                                            <option value="">Todos</option>
                                            <?php foreach ($tiposAvaria as $ta): ?>
                                                <option value="<?= $ta['id'] ?>"><?= htmlspecialchars($ta['nome_componente']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Filtro Administrativo</label>
                                        <select class="form-select" name="pendencia_adm" id="filtro_pendencia_adm">
                                            <option value="">Todas as Avarias</option>
                                            <option value="1">Com Pendência de Portaria</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Aba Período -->
                            <div class="tab-pane fade" id="filtros-periodo-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Data Início</label>
                                        <input type="date" class="form-control" name="data_inicio">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Data Fim</label>
                                        <input type="date" class="form-control" name="data_fim">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search me-2"></i>Pesquisar</button>
                        <button type="button" id="btn-limpar-filtros" class="btn btn-outline-secondary ms-2"><i class="fas fa-redo me-2"></i>Limpar Filtros</button>
                    </form>
                </div>
            </div>

            <!-- Tabela de Resultados -->
            <div id="resultado-pesquisa">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Avarias Registradas</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($results)): ?>
                                <div class="alert alert-warning">Nenhuma avaria encontrada com os filtros selecionados.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Data</th>
                                                <th>Viatura</th>
                                                <th>Componente</th>
                                                <th>Condutor</th>
                                                <th>Manutenção</th>
                                                <th>Detalhes</th>
                                                <th>Editar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($results as $row): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y H:i', strtotime($row['data_registro'])) ?></td>
                                                    <!-- Correção de $viatura para $row -->
                                                    <td><strong><?= htmlspecialchars(($row['prefixo'] ?? '') . ' ' . ($row['numero'] ?? '')) ?></strong></td>
                                                    <!-- Correção do nome do componente -->
                                                    <td><?= htmlspecialchars($row['nome_componente'] ?? 'Não classificado') ?></td>
                                                    <!-- Correção de $condutor para $row['nome_condutor'] -->
                                                    <td><?= htmlspecialchars($row['nome_condutor'] ?? 'N/A') ?></td>
                                                    <td>
                                                        <?php if (!empty($row['status_manutencao'])): ?>
                                                            <span class="badge bg-info"><?= htmlspecialchars($row['status_manutencao']) ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Não iniciada</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <!-- Coluna Detalhes (Modal) -->
                                                    <td>
                                                        <button type="button" class="btn btn-outline-info btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalDetalhesAvaria"
                                                            data-id="<?= $row['id'] ?>"
                                                            title="Ver Detalhes">
                                                            <i class="bi bi-list-check"></i>
                                                        </button>
                                                    </td>
                                                    <!-- Coluna Editar (Link para nova página) -->
                                                    <td>
                                                        <a href="pages/editar_avaria.php?id=<?= $row['id'] ?>"
                                                            class="btn btn-outline-warning btn-sm"
                                                            title="Editar Registro de Avaria">
                                                            <i class="bi bi-pen"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes -->
<div class="modal fade" id="modalDetalhesAvaria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Registro de Avaria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="conteudo-modal-avaria" class="modal-body">
                <!-- AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
    function initializePesquisaAvaria() {
        const form = document.getElementById('form-pesquisa-avaria');
        const areaResultados = document.getElementById('resultado-pesquisa');

        if (!form || form.hasAttribute('data-initialized')) return;
        form.setAttribute('data-initialized', 'true');

        // Escuta o envio do formulário
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);

            // O SPINNER SÓ APARECE SE O SCRIPT NÃO QUEBRAR
            areaResultados.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i> Pesquisando...</div>';

            fetch('pages/pesquisar_avaria.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: formData
                })
                .then(r => r.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const novoConteudo = doc.getElementById('resultado-pesquisa');
                    if (novoConteudo) {
                        areaResultados.innerHTML = novoConteudo.innerHTML;
                    }
                })
                .catch(err => {
                    areaResultados.innerHTML = '<div class="alert alert-danger">Erro de conexão.</div>';
                });
        });

        // Lógica do Modal (Sempre enviar Token)
        const modal = document.getElementById('modalDetalhesAvaria');
        if (modal) {
            modal.addEventListener('show.bs.modal', function(event) {
                const id = event.relatedTarget.getAttribute('data-id');
                const body = document.getElementById('conteudo-modal-avaria');
                body.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i></div>';

                fetch(`api/get_detalhes_avaria.php?id=${id}`, {
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                        }
                    })
                    .then(r => r.text())
                    .then(html => body.innerHTML = html);
            });
        }

        // === LÓGICA DE REDIRECIONAMENTO (MOVIDA PARA DENTRO DA FUNÇÃO) ===
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('pendencia') === '1') {
            const selectPendencia = document.getElementById('filtro_pendencia_adm');
            if (selectPendencia) {
                selectPendencia.value = '1';
                // Dispara o evento de submit que acabamos de configurar acima
                form.dispatchEvent(new Event('submit'));
            }
        }
    }

    // Inicializa
    initializePesquisaAvaria();
</script>