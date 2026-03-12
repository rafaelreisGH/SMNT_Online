<?php
// pages/pesquisar_avaria.php
require_once '../components/DAO/ViaturaDAO.php';
require_once '../components/DAO/CondutorDAO.php';
require_once '../components/DAO/RegistroAvariaDAO.php';

$conn = Conexao::getConexao();

// Lógica de pesquisa AJAX
$results = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $viatura_id = $_POST['viatura_id'] ?? '';
    $condutor_id = $_POST['condutor_id'] ?? '';
    $tipo_avaria_id = $_POST['tipo_avaria_id'] ?? '';
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim = $_POST['data_fim'] ?? '';

    $sql = "SELECT 
                ra.id, 
                ra.data_registro, 
                ra.observacoes_avaria,
                v.prefixo, 
                v.numero, 
                ta.nome_componente, 
                ta.categoria,
                c.nome AS nome_condutor,
                m.status AS status_manutencao
            FROM registro_avaria ra
            JOIN viaturas v ON ra.viatura_id = v.id
            JOIN tipo_avaria ta ON ra.tipo_avaria_id = ta.id
            JOIN condutor c ON ra.condutor_id = c.id
            LEFT JOIN manutencoes m ON m.avaria_id = ra.id
            WHERE 1=1";

    $params = [];

    if (!empty($viatura_id)) {
        $sql .= " AND ra.viatura_id = :viatura_id";
        $params[':viatura_id'] = $viatura_id;
    }
    if (!empty($condutor_id)) {
        $sql .= " AND ra.condutor_id = :condutor_id";
        $params[':condutor_id'] = $condutor_id;
    }
    if (!empty($tipo_avaria_id)) {
        $sql .= " AND ra.tipo_avaria_id = :tipo_avaria_id";
        $params[':tipo_avaria_id'] = $tipo_avaria_id;
    }
    if (!empty($data_inicio)) {
        $sql .= " AND ra.data_registro >= :data_inicio";
        $params[':data_inicio'] = $data_inicio . " 00:00:00";
    }
    if (!empty($data_fim)) {
        $sql .= " AND ra.data_registro <= :data_fim";
        $params[':data_fim'] = $data_fim . " 23:59:59";
    }

    $sql .= " ORDER BY ra.data_registro DESC";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

// Carregamento inicial de filtros via DAOs
$vDAO = new ViaturaDAO();
$cDAO = new CondutorDAO();
$aDAO = new RegistroAvariaDAO();

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
                                                    <td><strong><?= htmlspecialchars($row['prefixo'] . ' ' . $row['numero']) ?></strong></td>
                                                    <td><?= htmlspecialchars($row['nome_componente']) ?></td>
                                                    <td><?= htmlspecialchars($row['nome_condutor']) ?></td>
                                                    <td>
                                                        <?php if ($row['status_manutencao']): ?>
                                                            <span class="badge bg-info"><?= $row['status_manutencao'] ?></span>
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

        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            areaResultados.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i> Pesquisando...</div>';

            fetch('pages/pesquisar_avaria.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    areaResultados.innerHTML = doc.getElementById('resultado-pesquisa').innerHTML;
                    attachModalListener();
                });
        });

        document.getElementById('btn-limpar-filtros').addEventListener('click', () => {
            form.reset();
        });

        function attachModalListener() {
            const modal = document.getElementById('modalDetalhesAvaria');
            modal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const body = document.getElementById('conteudo-modal-avaria');

                body.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>';

                fetch(`api/get_detalhes_avaria.php?id=${id}`)
                    .then(r => r.text())
                    .then(html => body.innerHTML = html);
            });
        }

        attachModalListener();
    }

    initializePesquisaAvaria();
</script>