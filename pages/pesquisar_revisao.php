<?php
// pages/pesquisar_revisao.php
require_once '../components/DAO/RevisaoDAO.php';
require_once '../components/DAO/ViaturaDAO.php';

$revisaoDAO = new RevisaoDAO();
$viaturaDAO = new ViaturaDAO();

$results = [];
$listaViaturas = [];

// Busca lista de viaturas para o filtro select
try {
    $listaViaturas = $viaturaDAO->listarTodasParaSelect(); // Método que adicionamos anteriormente
} catch (Exception $e) {
    error_log("Erro ao carregar viaturas: " . $e->getMessage());
}

// Lógica de Processamento da Pesquisa (AJAX POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // O método buscarRevisoes do seu DAO já aceita o array $_POST como filtro
        $results = $revisaoDAO->buscarRevisoes($_POST);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Pesquisa de Manutenções</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            
            <!-- Formulário de Pesquisa -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="ms-3 font-weight-bold text-primary">Filtros de Pesquisa</h6>
                </div>
                <div class="card-body mx-3 my-3">
                    <form id="formPesquisaRevisao" action="#" method="POST">
                        <ul class="nav nav-tabs mb-4" id="revisaoSearchTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="filtros-gerais-tab" data-bs-toggle="tab" data-bs-target="#filtros-gerais-pane" type="button" role="tab">Dados do Serviço</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="filtros-data-tab" data-bs-toggle="tab" data-bs-target="#filtros-data-pane" type="button" role="tab">Período e Status</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="revisaoSearchTabContent">
                            <!-- Aba 1: Dados Gerais -->
                            <div class="tab-pane fade show active" id="filtros-gerais-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="viatura_id" class="form-label">Viatura</label>
                                        <select class="form-select" id="viatura_id" name="viatura_id">
                                            <option value="">Todas as Viaturas</option>
                                            <?php foreach ($listaViaturas as $v): ?>
                                                <option value="<?= $v['id'] ?>" <?= (isset($_POST['viatura_id']) && $_POST['viatura_id'] == $v['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($v['prefixo'] . " - " . $v['modelo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tipo_revisao" class="form-label">Tipo de Revisão (Termo de busca)</label>
                                        <input type="text" class="form-control" id="tipo_revisao" name="tipo_revisao" placeholder="Ex: Óleo, Suspensão..." value="<?= htmlspecialchars($_POST['tipo_revisao'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Aba 2: Datas e Status -->
                            <div class="tab-pane fade" id="filtros-data-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="status_revisao" class="form-label">Status</label>
                                        <select class="form-select" id="status_revisao" name="status_revisao">
                                            <option value="">Todos</option>
                                            <option value="Realizada" <?= (isset($_POST['status_revisao']) && $_POST['status_revisao'] == 'Realizada') ? 'selected' : '' ?>>Realizada</option>
                                            <option value="Programada" <?= (isset($_POST['status_revisao']) && $_POST['status_revisao'] == 'Programada') ? 'selected' : '' ?>>Programada</option>
                                            <option value="Cancelada" <?= (isset($_POST['status_revisao']) && $_POST['status_revisao'] == 'Cancelada') ? 'selected' : '' ?>>Cancelada</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="data_inicio" class="form-label">De (Data Revisão)</label>
                                        <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($_POST['data_inicio'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="data_fim" class="form-label">Até (Data Revisão)</label>
                                        <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?= htmlspecialchars($_POST['data_fim'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search me-2"></i>Pesquisar</button>
                        <button type="button" class="btn btn-outline-secondary ms-2" id="btnLimparFiltrosRevisao"><i class="fas fa-redo me-2"></i>Limpar</button>
                    </form>
                </div>
            </div>

            <!-- Área de Resultados -->
            <div id="resultado-pesquisa-revisao">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Manutenções Encontradas</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($results)): ?>
                                <div class="alert alert-warning">Nenhuma revisão encontrada para estes filtros.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Viatura</th>
                                                <th>Data</th>
                                                <th>Tipo</th>
                                                <th>KM</th>
                                                <th>Status</th>
                                                <th class="text-center">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($results as $rev): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($rev['prefixo'] . " - " . $rev['numero']) ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($rev['placa']) ?></small>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($rev['data_revisao'])) ?></td>
                                                    <td><?= htmlspecialchars($rev['tipo_revisao']) ?></td>
                                                    <td><?= number_format($rev['quilometragem'], 0, ',', '.') ?> km</td>
                                                    <td>
                                                        <span class="badge <?= $rev['status_revisao'] == 'Realizada' ? 'bg-success' : ($rev['status_revisao'] == 'Programada' ? 'bg-info' : 'bg-danger') ?>">
                                                            <?= $rev['status_revisao'] ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <!-- Botão Ver Resumo (Modal) -->
                                                        <button type="button" class="btn btn-outline-info btn-sm btn-view-revisao" 
                                                                title="Ver Resumo"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalResumoRevisao"
                                                                data-json='<?= json_encode($rev) ?>'>
                                                            <i class="bi bi-list-check"></i>
                                                        </button>

                                                        <!-- Botão Editar (Link para página) -->
                                                        <a href="pages/editar_revisao.php?id=<?= $rev['id'] ?>" class="btn btn-sm btn-outline-warning" title="Editar">
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

<!-- Modal de Resumo da Manutenção -->
<div class="modal fade" id="modalResumoRevisao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-tools me-2"></i>Resumo da Manutenção</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-content-revisao">
                <!-- Conteúdo preenchido via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function initializePesquisaRevisaoScripts() {
        const form = document.getElementById('formPesquisaRevisao');
        const areaResultados = document.getElementById('resultado-pesquisa-revisao');
        const btnLimpar = document.getElementById('btnLimparFiltrosRevisao');

        if (!form || form.hasAttribute('data-ajax-initialized')) return;
        form.setAttribute('data-ajax-initialized', 'true');

        // Limpar Filtros
        if (btnLimpar) {
            btnLimpar.addEventListener('click', () => {
                form.reset();
                areaResultados.innerHTML = '';
            });
        }

        // Submissão AJAX (Igual ao exemplo do condutor)
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(form);

            areaResultados.innerHTML = `<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><br>Buscando registros...</div>`;

            fetch('pages/pesquisar_revisao.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const novosResultados = doc.getElementById('resultado-pesquisa-revisao');

                if (novosResultados) {
                    areaResultados.innerHTML = novosResultados.innerHTML;
                    // Re-inicializa os eventos dos botões de modal após injetar HTML novo
                    attachModalEvents();
                }
            })
            .catch(error => {
                areaResultados.innerHTML = '<div class="alert alert-danger">Erro ao pesquisar.</div>';
            });
        });

        // Função para injetar dados no Modal
        function attachModalEvents() {
            document.querySelectorAll('.btn-view-revisao').forEach(btn => {
                btn.addEventListener('click', function() {
                    const data = JSON.parse(this.getAttribute('data-json'));
                    const content = document.getElementById('modal-content-revisao');
                    
                    content.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Viatura:</strong> ${data.prefixo} - ${data.numero} (${data.placa})</p>
                                <p><strong>Data:</strong> ${new Date(data.data_revisao).toLocaleDateString('pt-BR')}</p>
                                <p><strong>KM:</strong> ${data.quilometragem} km</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status:</strong> ${data.status_revisao}</p>
                                <p><strong>Tipo:</strong> ${data.tipo_revisao}</p>
                            </div>
                            <hr>
                            <div class="col-12 mb-3">
                                <h6 class="text-primary font-weight-bold">Itens Substituídos:</h6>
                                <div class="p-2 bg-light border rounded">${data.itens_substituidos || '<em>Nenhum item listado</em>'}</div>
                            </div>
                            <div class="col-12 mb-3">
                                <h6 class="text-primary font-weight-bold">Observações:</h6>
                                <div class="p-2 bg-light border rounded">${data.observacoes || '<em>Sem observações</em>'}</div>
                            </div>
                            <hr>
                            <div class="col-md-6">
                                <p><strong>Próxima Revisão (KM):</strong> ${data.proxima_revisao_km || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Próxima Revisão (Data):</strong> ${data.proxima_revisao_data ? new Date(data.proxima_revisao_data).toLocaleDateString('pt-BR') : 'N/A'}</p>
                            </div>
                        </div>
                    `;
                });
            });
        }

        // Inicializa na primeira carga (se já houver resultados via POST direto)
        attachModalEvents();
    }

    // Executa a inicialização
    initializePesquisaRevisaoScripts();
</script>