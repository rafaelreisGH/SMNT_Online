<?php
// pages/pesquisar_viatura.php
// Inclua seu arquivo de conexão com o banco de dados

require_once '../api/middleware_auth.php';
require_once '../components/DAO/ViaturaDAO.php';
$conn = Conexao::getConexao(); // Use $conn para a conexão PDO

// Lógica de pesquisa
$results = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coleta dos filtros do formulário
    $tipo_operacional = $_POST['tipo_operacional'] ?? '';
    $prefixo = $_POST['prefixo'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $placa = $_POST['placa'] ?? '';
    $renavam = $_POST['renavam'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $ano = $_POST['ano'] ?? '';
    $pbt = $_POST['pbt'] ?? '';
    $tipo_aquisicao = $_POST['tipo_aquisicao'] ?? '';
    $status_da_viatura = $_POST['status_da_viatura'] ?? '';
    $lotacao_atual_id = $_POST['lotacao_atual_id'] ?? '';
    $cilindradas = $_POST['cilindradas'] ?? '';
    $cavalos_forca = $_POST['cavalos_forca'] ?? '';
    $torque = $_POST['torque'] ?? '';
    $garantia_fim = $_POST['garantia_fim'] ?? '';
    $tipo_cambio = $_POST['tipo_cambio'] ?? '';
    $numero_marchas = $_POST['numero_marchas'] ?? '';

    // Constrói a consulta SQL dinamicamente
    // A consulta seleciona apenas as colunas necessárias para exibição na tabela e para as ações.
    $sql = "SELECT
                v.id,
                v.prefixo,
                v.numero,
                v.marca,
                v.modelo,
                v.status_da_viatura,
                u.nome AS unidade_lotacao
            FROM
                controle_viaturas.viaturas v
            LEFT JOIN
                controle_viaturas.unidades u ON v.lotacao_atual_id = u.id
            WHERE 1=1";

    $params = [];

    if (!empty($tipo_operacional)) {
        $sql .= " AND v.tipo_operacional = :tipo_operacional";
        $params[':tipo_operacional'] = $tipo_operacional;
    }
    if (!empty($prefixo)) {
        $sql .= " AND v.prefixo LIKE :prefixo";
        $params[':prefixo'] = '%' . $prefixo . '%';
    }
    if (!empty($numero)) {
        $sql .= " AND v.numero = :numero";
        $params[':numero'] = $numero;
    }
    if (!empty($placa)) {
        $sql .= " AND v.placa LIKE :placa";
        $params[':placa'] = '%' . $placa . '%';
    }
    if (!empty($renavam)) {
        $sql .= " AND v.renavam LIKE :renavam";
        $params[':renavam'] = '%' . $renavam . '%';
    }
    if (!empty($marca)) {
        $sql .= " AND v.marca LIKE :marca";
        $params[':marca'] = '%' . $marca . '%';
    }
    if (!empty($modelo)) {
        $sql .= " AND v.modelo LIKE :modelo";
        $params[':modelo'] = '%' . $modelo . '%';
    }
    if (!empty($ano)) {
        $sql .= " AND v.ano = :ano";
        $params[':ano'] = $ano;
    }
    if (!empty($pbt)) {
        $sql .= " AND v.pbt = :pbt";
        $params[':pbt'] = $pbt;
    }
    if (!empty($tipo_aquisicao)) {
        $sql .= " AND v.tipo_aquisicao = :tipo_aquisicao";
        $params[':tipo_aquisicao'] = $tipo_aquisicao;
    }
    if (!empty($status_da_viatura)) {
        $sql .= " AND v.status_da_viatura = :status_da_viatura";
        $params[':status_da_viatura'] = $status_da_viatura;
    }
    if (!empty($lotacao_atual_id)) {
        $sql .= " AND v.lotacao_atual_id = :lotacao_atual_id";
        $params[':lotacao_atual_id'] = $lotacao_atual_id;
    }
    if (!empty($cilindradas)) {
        $sql .= " AND v.cilindradas = :cilindradas";
        $params[':cilindradas'] = $cilindradas;
    }
    if (!empty($cavalos_forca)) {
        $sql .= " AND v.cavalos_forca = :cavalos_forca";
        $params[':cavalos_forca'] = $cavalos_forca;
    }
    if (!empty($torque)) {
        $sql .= " AND v.torque LIKE :torque";
        $params[':torque'] = '%' . $torque . '%';
    }

    if (!empty($garantia_fim)) {
        $sql .= " AND v.garantia_fim >= :garantia_fim"; // Buscar viaturas com garantia igual ou maior que a data informada
        $params[':garantia_fim'] = $garantia_fim;
    }

    if (!empty($tipo_cambio)) {
        $sql .= " AND v.tipo_cambio = :tipo_cambio";
        $params[':tipo_cambio'] = $tipo_cambio;
    }
    if (!empty($numero_marchas)) {
        $sql .= " AND v.numero_marchas = :numero_marchas";
        $params[':numero_marchas'] = $numero_marchas;
    }

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Erro ao consultar o banco de dados: " . $e->getMessage() . "</div>";
    }
}

// 1. Inclui o DAO de Unidades
require_once '../components/DAO/UnidadeDAO.php';

// 2. Busca as unidades AGRUPADAS (como no cadastro)
$unidadeDAO = new UnidadeDAO();
$unidadesAgrupadas = $unidadeDAO->listarAgrupadasPorComando();

?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Pesquisa de viatura</h1>

    <div class="row">

        <div class="col-lg-10 mx-auto">

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="ms-3 font-weight-bold text-primary">Formulário de pesquisa</h6>
                </div>
                <div class="card-body mx-3 my-3">
                    <form id="form-pesquisa-viatura" action="#" method="POST">
                        <ul class="nav nav-tabs mb-4" id="viaturaSearchTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="filtros-basicos-tab" data-bs-toggle="tab" data-bs-target="#filtros-basicos-pane" type="button" role="tab" aria-controls="filtros-basicos-pane" aria-selected="true">Filtros Básicos</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="filtros-motorizacao-tab" data-bs-toggle="tab" data-bs-target="#filtros-motorizacao-pane" type="button" role="tab" aria-controls="filtros-motorizacao-pane" aria-selected="false">Filtros de Motorização</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="filtros-aquisicao-tab" data-bs-toggle="tab" data-bs-target="#filtros-aquisicao-pane" type="button" role="tab" aria-controls="filtros-aquisicao-pane" aria-selected="false">Filtros de Aquisição e Garantia</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="viaturaSearchTabContent">
                            <div class="tab-pane fade show active" id="filtros-basicos-pane" role="tabpanel" aria-labelledby="filtros-basicos-tab" tabindex="0">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="tipo_operacional" class="form-label">Tipo Operacional</label>
                                        <select class="form-select" id="tipo_operacional" name="tipo_operacional">
                                            <option value="">Todos</option>
                                            <?php
                                            $tiposOperacionais = ['Operacional Geral', 'Combate a incendio', 'Administrativa'];
                                            foreach ($tiposOperacionais as $tipo) {
                                                $selected = (isset($_POST['tipo_operacional']) && $_POST['tipo_operacional'] == $tipo) ? 'selected' : '';
                                                echo "<option value=\"$tipo\" $selected>$tipo</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="prefixo" class="form-label">Prefixo</label>
                                        <input type="text" class="form-control" id="prefixo" name="prefixo" maxlength="5" value="<?= htmlspecialchars($_POST['prefixo'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="numero" class="form-label">Número</label>
                                        <input type="number" class="form-control" id="numero" name="numero" value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="placa" class="form-label">Placa</label>
                                        <input type="text" class="form-control" id="placa" name="placa" maxlength="7" placeholder="Ex: ABC1234" value="<?= htmlspecialchars($_POST['placa'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="renavam" class="form-label">RENAVAM</label>
                                        <input type="text" class="form-control" id="renavam" name="renavam" maxlength="11" value="<?= htmlspecialchars($_POST['renavam'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="marca" class="form-label">Marca</label>
                                        <input type="text" class="form-control" id="marca" name="marca" maxlength="50" value="<?= htmlspecialchars($_POST['marca'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="modelo" class="form-label">Modelo</label>
                                        <input type="text" class="form-control" id="modelo" name="modelo" maxlength="50" value="<?= htmlspecialchars($_POST['modelo'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="ano" class="form-label">Ano</label>
                                        <input type="number" class="form-control" id="ano" name="ano" min="1900" max="<?= date('Y') + 1 ?>" value="<?= htmlspecialchars($_POST['ano'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="status_da_viatura" class="form-label">Status da Viatura</label>
                                        <select class="form-select" id="status_da_viatura" name="status_da_viatura">
                                            <option value="">Todos</option>
                                            <?php
                                            $statusViatura = ['Operante', 'Baixada', 'Descarregada', 'Indisponível'];
                                            foreach ($statusViatura as $status) {
                                                $selected = (isset($_POST['status_da_viatura']) && $_POST['status_da_viatura'] == $status) ? 'selected' : '';
                                                echo "<option value=\"$status\" $selected>$status</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lotacao_atual_id" class="form-label">Lotação Atual</label>
                                        <select class="form-select" id="lotacao_atual_id" name="lotacao_atual_id">
                                            <option value="">Todas as Unidades</option>

                                            <?php foreach ($unidadesAgrupadas as $comando => $unidades_do_grupo): ?>
                                                <optgroup label="<?= htmlspecialchars($comando) ?>">
                                                    <?php foreach ($unidades_do_grupo as $unidade): ?>
                                                        <?php
                                                        // Formata o nome: "Unidade - Município"
                                                        $labelUnidade = htmlspecialchars($unidade['unidade_nome']);
                                                        if (!empty($unidade['municipio_nome'])) {
                                                            $labelUnidade .= " - " . htmlspecialchars($unidade['municipio_nome']);
                                                        }

                                                        // Verifica se esta unidade foi a selecionada na pesquisa anterior
                                                        $selected = (isset($_POST['lotacao_atual_id']) && $_POST['lotacao_atual_id'] == $unidade['id']) ? 'selected' : '';
                                                        ?>
                                                        <option value="<?= $unidade['id'] ?>" <?= $selected ?>>
                                                            <?= $labelUnidade ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="filtros-motorizacao-pane" role="tabpanel" aria-labelledby="filtros-motorizacao-tab" tabindex="0">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="cilindradas" class="form-label">Cilindradas (cc)</label>
                                        <input type="number" class="form-control" id="cilindradas" name="cilindradas" value="<?= htmlspecialchars($_POST['cilindradas'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="cavalos_forca" class="form-label">Cavalos de Força (cv)</label>
                                        <input type="number" class="form-control" id="cavalos_forca" name="cavalos_forca" value="<?= htmlspecialchars($_POST['cavalos_forca'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="torque" class="form-label">Torque</label>
                                        <input type="text" class="form-control" id="torque" name="torque" maxlength="50" value="<?= htmlspecialchars($_POST['torque'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="tipo_cambio" class="form-label">Tipo de Câmbio</label>
                                        <select class="form-select" id="tipo_cambio" name="tipo_cambio">
                                            <option value="">Todos</option>
                                            <?php
                                            $tiposCambio = ['Manual', 'Automático'];
                                            foreach ($tiposCambio as $cambio) {
                                                $selected = (isset($_POST['tipo_cambio']) && $_POST['tipo_cambio'] == $cambio) ? 'selected' : '';
                                                echo "<option value=\"$cambio\" $selected>$cambio</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="numero_marchas" class="form-label">Número de Marchas</label>
                                        <input type="number" class="form-control" id="numero_marchas" name="numero_marchas" value="<?= htmlspecialchars($_POST['numero_marchas'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="filtros-aquisicao-pane" role="tabpanel" aria-labelledby="filtros-aquisicao-tab" tabindex="0">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="pbt" class="form-label">Peso Bruto Total (PBT)</label>
                                        <input type="number" class="form-control" id="pbt" name="pbt" value="<?= htmlspecialchars($_POST['pbt'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tipo_aquisicao" class="form-label">Tipo de Aquisição</label>
                                        <select class="form-select" id="tipo_aquisicao" name="tipo_aquisicao">
                                            <option value="" <?= (!isset($display_data['tipo_aquisicao']) || $display_data['tipo_aquisicao'] === '') ? 'selected' : '' ?> disabled>Selecione...</option>
                                            <?php
                                            $tiposAquisicao = ['Própria', 'Doação', 'Fiel Depositário', 'Locada'];
                                            foreach ($tiposAquisicao as $tipo) {
                                                $selected = (isset($display_data['tipo_aquisicao']) && $display_data['tipo_aquisicao'] == $tipo) ? 'selected' : '';
                                                echo "<option value=\"$tipo\" $selected>$tipo</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="garantia_fim" class="form-label">Garantia Vence Após</label>
                                        <input type="date" class="form-control" id="garantia_fim" name="garantia_fim" value="<?= htmlspecialchars($_POST['garantia_fim'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                        </div>
                        <hr class="my-4">
                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search me-2"></i>Pesquisar Viatura</button>
                        <button type="button" id="btn-limpar-filtros" class="btn btn-outline-secondary ms-2"><i class="fas fa-redo me-2"></i>Limpar Filtros</button>
                    </form>
                </div>
            </div>

            <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

            <!-- Circle Buttons -->
            <div id="resultado-pesquisa">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Resultado</h6>
                    </div>
                    <div class="card-body">
                        <p>Abaixo são listados os resultados da pesquisa.</p>
                        <div class="mb-2">
                            <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($results)): ?>
                                <div class="col-12 col-md-8 col-lg-6 mt-4">
                                    <div class="alert alert-warning" role="alert">
                                        Nenhuma viatura encontrada com os filtros especificados.
                                    </div>
                                </div>
                            <?php elseif (!empty($results)): ?>
                                <div class="container-fluid">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Prefixo</th>
                                                    <th>Número</th>
                                                    <th>Marca</th>
                                                    <th>Modelo</th>
                                                    <th>Status</th>
                                                    <th>Lotação</th>
                                                    <th>Detalhes</th>
                                                    <th>Editar</th>
                                                    <th>Reativar</th>
                                                    <th>Baixar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($results as $viatura): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($viatura['prefixo']) ?></td>
                                                        <td><?= htmlspecialchars($viatura['numero']) ?></td>
                                                        <td><?= htmlspecialchars($viatura['marca']) ?></td>
                                                        <td><?= htmlspecialchars($viatura['modelo']) ?></td>
                                                        <td class="viatura-status"><?= htmlspecialchars($viatura['status_da_viatura']) ?></td>
                                                        <td><?= htmlspecialchars($viatura['unidade_lotacao'] ?? 'Não informada') ?></td>
                                                        <td>
                                                            <!-- ANTES ERA UM <a>, AGORA É UM <button> -->
                                                            <button type="button" class="btn btn-outline-info btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#detalhesModal"
                                                                data-viatura-id="<?= $viatura['id'] ?>"
                                                                title="Ver Detalhes da Viatura">
                                                                <i class="bi bi-list-check"></i>
                                                            </button>
                                                        </td>
                                                        <td>
                                                            <a href="pages/editar_viatura.php?id=<?= $viatura['id'] ?>" class="btn btn-outline-warning btn-sm" title="Editar Dados da Viatura">
                                                                <i class="bi bi-pen"></i>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <form class="form-reativa-viatura" data-id-viatura="<?= $viatura['id'] ?>" style="display:inline;">
                                                                <input type="hidden" name="id_viatura" value="<?= $viatura['id'] ?>">
                                                                <button type="submit" class="btn btn-sm <?= ($viatura['status_da_viatura'] == 'Operante') ? 'btn-secondary' : 'btn-outline-success' ?>" title="Reativar Viatura" <?= ($viatura['status_da_viatura'] == 'Operante') ? 'disabled' : '' ?>>
                                                                    <i class="bi bi-check-lg"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                        <td>
                                                            <form class="form-baixa-viatura" data-id-viatura="<?= $viatura['id'] ?>" style="display:inline;">
                                                                <input type="hidden" name="id_viatura" value="<?= $viatura['id'] ?>">
                                                                <button type="submit" class="btn btn-sm <?= ($viatura['status_da_viatura'] != 'Operante') ? 'btn-secondary' : 'btn-outline-danger' ?>" title="Baixar Viatura" <?= ($viatura['status_da_viatura'] != 'Operante') ? 'disabled' : '' ?>>
                                                                    <i class="bi bi-x-lg"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div> <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
    <!-- ... (fim do seu conteúdo HTML) ... -->

    <!-- ... Final do seu HTML ... -->
</div>
</div>


<!-- ... Final do seu HTML ... -->
</div>
</div>

<!-- Modal Genérico para Detalhes -->
<div class="modal fade" id="detalhesModal" tabindex="-1" aria-labelledby="detalhesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalhesModalLabel">Detalhes da Viatura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detalhesModalBody">
                <!-- O conteúdo será carregado aqui via AJAX -->
                <div class="text-center p-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i> Carregando...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT CORRIGIDO E COMPLETO PARA A PÁGINA -->
<script>
    function initializePesquisaViaturaScripts() {
        const formPesquisa = document.getElementById('form-pesquisa-viatura');
        const areaResultados = document.getElementById('resultado-pesquisa');

        // Adiciona listener ao formulário de pesquisa (se ainda não tiver)
        if (formPesquisa && !formPesquisa.hasAttribute('data-ajax-initialized')) {
            formPesquisa.setAttribute('data-ajax-initialized', 'true');
            formPesquisa.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(formPesquisa);
                areaResultados.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i> Pesquisando...</div>';

                fetch('pages/pesquisar_viatura.php', {
                        method: 'POST',
                        headers: {
                            // ADICIONE ESTA LINHA:
                            'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                        },
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const novosResultados = doc.getElementById('resultado-pesquisa');

                        if (novosResultados) {
                            areaResultados.innerHTML = novosResultados.innerHTML;
                            // Re-inicializa os scripts para a nova tabela de resultados
                            initializePageSpecificActions();
                        } else {
                            areaResultados.innerHTML = '<div class="alert alert-danger">Erro ao processar a resposta.</div>';
                        }
                    })
                    .catch(error => console.error('Erro na pesquisa AJAX:', error));
            });
        }

        // Adiciona listener ao botão de limpar (se ainda não tiver)
        const btnLimpar = document.getElementById('btn-limpar-filtros');
        if (btnLimpar && !btnLimpar.hasAttribute('data-ajax-initialized')) {
            btnLimpar.setAttribute('data-ajax-initialized', 'true');
            btnLimpar.addEventListener('click', () => formPesquisa?.reset());
        }

        // =======================================================================
        // AQUI ESTÁ O CÓDIGO QUE FALTAVA
        // Esta função anexa os listeners para Toasts, Baixar, Reativar, etc.
        // =======================================================================
        function initializePageSpecificActions() {

            // Lógica para Baixar Viatura
            document.querySelectorAll('.form-baixa-viatura').forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    if (!confirm('Tem certeza que deseja baixar esta viatura?')) return;

                    const idViatura = this.dataset.idViatura;
                    const formData = new FormData();
                    formData.append('id_viatura', idViatura);

                    fetch('../api/processar_baixar_viatura.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast(data.message, 'success');
                                const row = form.closest('tr');
                                row.querySelector('.viatura-status').textContent = 'Baixada';
                                form.querySelector('button').disabled = true;
                                form.querySelector('button').classList.replace('btn-outline-danger', 'btn-secondary');
                                const reativarBtn = row.querySelector('.form-reativa-viatura button');
                                reativarBtn.disabled = false;
                                reativarBtn.classList.replace('btn-secondary', 'btn-outline-success');
                            } else {
                                showToast(data.message, 'danger');
                            }
                        })
                        .catch(error => {
                            console.error('Erro AJAX ao baixar:', error);
                            showToast('Erro de comunicação ao baixar viatura.', 'danger');
                        });
                });
            });

            // Lógica para Reativar Viatura
            document.querySelectorAll('.form-reativa-viatura').forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    if (!confirm('Tem certeza que deseja reativar esta viatura?')) return;

                    const idViatura = this.dataset.idViatura;
                    const formData = new FormData();
                    formData.append('id_viatura', idViatura);

                    fetch('../api/processar_reativacao_viatura.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast(data.message, 'success');
                                const row = form.closest('tr');
                                row.querySelector('.viatura-status').textContent = 'Operante';
                                form.querySelector('button').disabled = true;
                                form.querySelector('button').classList.replace('btn-outline-success', 'btn-secondary');
                                const baixarBtn = row.querySelector('.form-baixa-viatura button');
                                baixarBtn.disabled = false;
                                baixarBtn.classList.replace('btn-secondary', 'btn-outline-danger');
                            } else {
                                showToast(data.message, 'danger');
                            }
                        })
                        .catch(error => {
                            console.error('Erro AJAX ao reativar:', error);
                            showToast('Erro de comunicação ao reativar viatura.', 'danger');
                        });
                });
            });
            // NOVA LÓGICA PARA O MODAL DE DETALHES
            const detalhesModal = document.getElementById('detalhesModal');
            if (detalhesModal) {
                detalhesModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget; // O botão que acionou o modal
                    const viaturaId = button.getAttribute('data-viatura-id'); // Pega o ID do atributo data

                    const modalBody = detalhesModal.querySelector('#detalhesModalBody');

                    // Mostra o spinner de carregamento
                    modalBody.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i> Carregando...</div>';

                    // Busca os detalhes da viatura na nossa nova API
                    fetch(`api/get_detalhes_viatura.php?id=${viaturaId}`)
                        .then(response => response.text())
                        .then(html => {
                            // Preenche o corpo do modal com o HTML recebido
                            modalBody.innerHTML = html;
                        })
                        .catch(error => {
                            modalBody.innerHTML = '<div class="alert alert-danger">Erro ao carregar os detalhes.</div>';
                            console.error('Erro ao buscar detalhes da viatura:', error);
                        });
                });
            }
        }

        // Chama a função pela primeira vez para os elementos que já estão na página
        initializePageSpecificActions();
    }

    // Finalmente, chamamos a função principal para inicializar tudo nesta carga.
    initializePesquisaViaturaScripts();
</script>