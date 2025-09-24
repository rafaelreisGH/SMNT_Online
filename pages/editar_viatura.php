<?php
session_start(); // Inicia a sessão para usar mensagens de toast/alert

// Configurações para exibição de erros (APENAS PARA DESENVOLVIMENTO!)
// Remova ou comente estas linhas em ambiente de produção para evitar expor detalhes.
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Inclui os arquivos de dependência
require_once '../components/ConexaoDB/conexao.php'; // Inclui o arquivo de conexão (usado pelo ViaturaDAO)
require_once '../components/DAO/ViaturaDAO.php'; // Inclui a classe ViaturaDAO
require_once '../api/validate_functions_editar_viatura.php'; // Inclui o arquivo com a função de validação

// Definição das unidades de exemplo (substitua por dados reais do seu banco se tiver um DAO de Unidades)
$unidadesExemplo = [
    ['id' => 1, 'nome' => '1ºBBM'],
    ['id' => 2, 'nome' => '2ºBBM'],
    ['id' => 3, 'nome' => '3ºBBM'],
    ['id' => 4, 'nome' => '4ºBBM'],
    ['id' => 5, 'nome' => '5ºBBM'],
];

// Variáveis para mensagens e erros, populadas a partir da sessão (se houver)
$toast_message = $_SESSION['toast_message'] ?? '';
$toast_type = $_SESSION['toast_type'] ?? '';
$validation_errors = $_SESSION['validation_errors'] ?? [];

// Limpa as mensagens de sessão e erros de validação após recuperá-los
unset($_SESSION['toast_message']);
unset($_SESSION['toast_type']);
unset($_SESSION['validation_errors']);

// Inicializa $display_data com os dados que preencherão o formulário
$display_data = [];

// --- Lógica para processar a requisição POST (submissão do formulário) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // A função validateViaturaData espera um array com todos os dados do POST
    // Ela própria fará a filtragem e validação dos dados passados
    $data_from_form = $_POST; // Copia os dados do POST para um array mutável

    // Realiza a validação usando a função externa.
    // A função pode modificar o array $data_from_form in-place com os valores filtrados.
    $validation_errors = validateViaturaData($data_from_form);

    if (!empty($validation_errors)) {
        // Se houver erros de validação, define as mensagens e usa os dados do formulário
        // (que já foram filtrados pela função de validação) para re-preencher o formulário.
        $toast_message = "Houve erros no preenchimento do formulário. Por favor, corrija os campos indicados.";
        $toast_type = "danger";
        $display_data = $data_from_form; // Mantém os dados validados/filtrados para re-preenchimento
    } else {
        // Se a validação passar, os dados em $data_from_form já estão limpos e prontos para o DAO
        try {
            // Instancia o DAO (assumimos que ele obtém a conexão PDO internamente)
            $viaturaDAO = new ViaturaDAO();

            // Tenta atualizar a viatura.
            // Os dados já estão filtrados e validados por validateViaturaData
            if ($viaturaDAO->updateViatura($data_from_form)) {
                $toast_message = "Viatura atualizada com sucesso!";
                $toast_type = "success";
                // Após o sucesso, recarrega os dados do banco para mostrar os valores atualizados
                // O ID já foi validado em validateViaturaData
                $display_data = $viaturaDAO->getViaturaById($data_from_form['id']);
            } else {
                // Caso o updateViatura retorne false (erro interno no DAO ou nenhuma linha afetada)
                $toast_message = "Erro ao atualizar a viatura no banco de dados. Verifique os logs para detalhes ou tente novamente.";
                $toast_type = "danger";
                $display_data = $data_from_form; // Mantém os dados que foram submetidos para re-preenchimento
            }
        } catch (PDOException $e) {
            // Captura erros de conexão ou SQL
            error_log("Exceção PDO na atualização da viatura: " . $e->getMessage()); // Loga o erro detalhado
            $toast_message = "Erro interno no banco de dados: " . $e->getMessage();
            $toast_type = "danger";
            $display_data = $data_from_form; // Mantém os dados submetidos
        } catch (Exception $e) {
            // Captura outras exceções genéricas
            error_log("Exceção geral na atualização da viatura: " . $e->getMessage()); // Loga o erro detalhado
            $toast_message = "Ocorreu um erro inesperado: " . $e->getMessage();
            $toast_type = "danger";
            $display_data = $data_from_form; // Mantém os dados submetidos
        }
    }
}
// --- Lógica para requisição GET (carregar formulário pela primeira vez ou após redefinição) ---
else {
    // O ID da viatura deve vir da URL para carregar o formulário
    $id_viatura_get = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    // Se o ID não foi especificado ou é inválido na URL, redireciona
    if (!$id_viatura_get) {
        $_SESSION['toast_message'] = "ID da viatura não especificado ou inválido para edição.";
        $_SESSION['toast_type'] = "danger";
        header('Location: home.php'); // Redireciona para uma página inicial ou de erro
        exit();
    }

    try {
        $viaturaDAO = new ViaturaDAO(); // Instancia o DAO
    } catch (PDOException $e) {
        $_SESSION['toast_message'] = "Erro ao conectar ao banco de dados: " . $e->getMessage();
        $_SESSION['toast_type'] = "danger";
        header('Location: home.php');
        exit();
    }

    // Busca os dados da viatura pelo ID para preencher o formulário
    // Usando getViaturaById, que assumimos que retorna todos os campos (*)
    $viatura_from_db = $viaturaDAO->getViaturaById($id_viatura_get);

    // Se a viatura não for encontrada no banco, redireciona
    if (!$viatura_from_db) {
        $_SESSION['toast_message'] = "Viatura com o ID " . htmlspecialchars($id_viatura_get) . " não encontrada.";
        $_SESSION['toast_type'] = "danger";
        header('Location: home.php');
        exit();
    }
    // Preenche $display_data com os dados da viatura recém-buscados para exibição no formulário
    $display_data = $viatura_from_db;
}
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Edição de viatura</h1>

    <div class="row">

        <div class="col-lg-10 mx-auto">

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="ms-3 font-weight-bold text-primary">Formulário de pesquisa</h6>
                </div>
                <div class="card-body mx-3 my-3">


                    <?php if (!empty($display_data) || !empty($validation_errors)): // Verifica se há dados para exibir ou erros para indicar o formulário 
                    ?>
                        <form action="editar_viatura.php" method="POST" id="editViaturaForm">
                            <!-- O ID é crucial e deve ser mantido no formulário -->
                            <input type="hidden" name="id" value="<?= htmlspecialchars($display_data['id'] ?? '') ?>">

                            <ul class="nav nav-tabs mb-4" id="viaturaTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="dados-basicos-tab" data-bs-toggle="tab" data-bs-target="#dados-basicos-pane" type="button" role="tab" aria-controls="dados-basicos-pane" aria-selected="true">Dados Básicos</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="motorizacao-tab" data-bs-toggle="tab" data-bs-target="#motorizacao-pane" type="button" role="tab" aria-controls="motorizacao-pane" aria-selected="false">Motorização</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="diversos-tab" data-bs-toggle="tab" data-bs-target="#diversos-pane" type="button" role="tab" aria-controls="diversos-pane" aria-selected="false">Diversos</button>
                                </li>
                            </ul>

                            <div class="tab-content" id="viaturaTabContent">

                                <div class="tab-pane fade show active" id="dados-basicos-pane" role="tabpanel" aria-labelledby="dados-basicos-tab" tabindex="0">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="tipo_operacional" class="form-label">Tipo Operacional</label>
                                            <select class="form-select <?= isset($validation_errors['tipo_operacional']) ? 'is-invalid' : '' ?>" id="tipo_operacional" name="tipo_operacional">
                                                <option value="" <?= (!isset($display_data['tipo_operacional']) || $display_data['tipo_operacional'] === '') ? 'selected' : '' ?> disabled>Selecione...</option>
                                                <?php
                                                $tiposOperacionais = ['Operacional Geral', 'Combate a incendio', 'Administrativa'];
                                                foreach ($tiposOperacionais as $tipo) {
                                                    $selected = (isset($display_data['tipo_operacional']) && $display_data['tipo_operacional'] == $tipo) ? 'selected' : '';
                                                    echo "<option value=\"$tipo\" $selected>$tipo</option>";
                                                }
                                                ?>
                                            </select>
                                            <?php if (isset($validation_errors['tipo_operacional'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['tipo_operacional']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="prefixo" class="form-label">Prefixo</label>
                                            <input type="text" class="form-control <?= isset($validation_errors['prefixo']) ? 'is-invalid' : '' ?>" id="prefixo" name="prefixo" maxlength="5" value="<?= htmlspecialchars($display_data['prefixo'] ?? '') ?>">
                                            <?php if (isset($validation_errors['prefixo'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['prefixo']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="numero" class="form-label">Número</label>
                                            <input type="number" class="form-control <?= isset($validation_errors['numero']) ? 'is-invalid' : '' ?>" id="numero" name="numero" value="<?= htmlspecialchars($display_data['numero'] ?? '') ?>">
                                            <?php if (isset($validation_errors['numero'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['numero']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="placa" class="form-label">Placa</label>
                                            <input type="text" class="form-control <?= isset($validation_errors['placa']) ? 'is-invalid' : '' ?>" id="placa" name="placa" maxlength="8" placeholder="Ex: ABC1234" value="<?= htmlspecialchars($display_data['placa'] ?? '') ?>">
                                            <?php if (isset($validation_errors['placa'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['placa']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="renavam" class="form-label">Renavam</label>
                                            <input type="text" class="form-control <?= isset($validation_errors['renavam']) ? 'is-invalid' : '' ?>" id="renavam" name="renavam" maxlength="11" placeholder="Somente números" value="<?= htmlspecialchars($display_data['renavam'] ?? '') ?>">
                                            <?php if (isset($validation_errors['renavam'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['renavam']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="marca" class="form-label">Marca</label>
                                            <input type="text" class="form-control <?= isset($validation_errors['marca']) ? 'is-invalid' : '' ?>" id="marca" name="marca" maxlength="50" value="<?= htmlspecialchars($display_data['marca'] ?? '') ?>">
                                            <?php if (isset($validation_errors['marca'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['marca']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="modelo" class="form-label">Modelo</label>
                                            <input type="text" class="form-control <?= isset($validation_errors['modelo']) ? 'is-invalid' : '' ?>" id="modelo" name="modelo" maxlength="50" value="<?= htmlspecialchars($display_data['modelo'] ?? '') ?>">
                                            <?php if (isset($validation_errors['modelo'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['modelo']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="ano" class="form-label">Ano</label>
                                            <input type="number" class="form-control <?= isset($validation_errors['ano']) ? 'is-invalid' : '' ?>" id="ano" name="ano" min="1900" max="<?= date('Y') + 1 ?>" value="<?= htmlspecialchars($display_data['ano'] ?? '') ?>">
                                            <?php if (isset($validation_errors['ano'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['ano']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="pbt" class="form-label">PBT (kg)</label>
                                            <input type="number" class="form-control <?= isset($validation_errors['pbt']) ? 'is-invalid' : '' ?>" id="pbt" name="pbt" value="<?= htmlspecialchars($display_data['pbt'] ?? '') ?>">
                                            <?php if (isset($validation_errors['pbt'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['pbt']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="tipo_aquisicao" class="form-label">Tipo de Aquisição</label>
                                            <select class="form-select <?= isset($validation_errors['tipo_aquisicao']) ? 'is-invalid' : '' ?>" id="tipo_aquisicao" name="tipo_aquisicao">
                                                <option value="" <?= (!isset($display_data['tipo_aquisicao']) || $display_data['tipo_aquisicao'] === '') ? 'selected' : '' ?> disabled>Selecione...</option>
                                                <?php
                                                $tiposAquisicao = ['Própria', 'Doação', 'Fiel Depositário', 'Locada'];
                                                foreach ($tiposAquisicao as $tipo) {
                                                    $selected = (isset($display_data['tipo_aquisicao']) && $display_data['tipo_aquisicao'] == $tipo) ? 'selected' : '';
                                                    echo "<option value=\"$tipo\" $selected>$tipo</option>";
                                                }
                                                ?>
                                            </select>
                                            <?php if (isset($validation_errors['tipo_aquisicao'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['tipo_aquisicao']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="status_da_viatura" class="form-label">Status da Viatura</label>
                                            <select class="form-select <?= isset($validation_errors['status_da_viatura']) ? 'is-invalid' : '' ?>" id="status_da_viatura" name="status_da_viatura">
                                                <option value="" <?= (!isset($display_data['status_da_viatura']) || $display_data['status_da_viatura'] === '') ? 'selected' : '' ?> disabled>Selecione...</option>
                                                <?php
                                                $statusViatura = ['Operante', 'Baixada', 'Descarregada', 'Indisponível'];
                                                foreach ($statusViatura as $status) {
                                                    $selected = (isset($display_data['status_da_viatura']) && $display_data['status_da_viatura'] == $status) ? 'selected' : '';
                                                    echo "<option value=\"$status\" $selected>$status</option>";
                                                }
                                                ?>
                                            </select>
                                            <?php if (isset($validation_errors['status_da_viatura'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['status_da_viatura']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="lotacao_atual_id" class="form-label">Lotação Atual</label>
                                            <select class="form-select <?= isset($validation_errors['lotacao_atual_id']) ? 'is-invalid' : '' ?>" id="lotacao_atual_id" name="lotacao_atual_id">
                                                <option value="" <?= (!isset($display_data['lotacao_atual_id']) || $display_data['lotacao_atual_id'] === '') ? 'selected' : '' ?> disabled>Selecione...</option>
                                                <?php
                                                foreach ($unidadesExemplo as $unidade) { // Usando a simulação de unidades
                                                    $selected = (isset($display_data['lotacao_atual_id']) && $display_data['lotacao_atual_id'] == $unidade['id']) ? 'selected' : '';
                                                    echo "<option value=\"{$unidade['id']}\" $selected>{$unidade['nome']}</option>";
                                                }
                                                ?>
                                            </select>
                                            <?php if (isset($validation_errors['lotacao_atual_id'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['lotacao_atual_id']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="motorizacao-pane" role="tabpanel" aria-labelledby="motorizacao-tab" tabindex="0">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="cilindradas" class="form-label">Cilindradas (cc)</label>
                                            <input type="number" class="form-control <?= isset($validation_errors['cilindradas']) ? 'is-invalid' : '' ?>" id="cilindradas" name="cilindradas" value="<?= htmlspecialchars($display_data['cilindradas'] ?? '') ?>">
                                            <?php if (isset($validation_errors['cilindradas'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['cilindradas']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cavalos_forca" class="form-label">Cavalos de Força (cv)</label>
                                            <input type="number" class="form-control <?= isset($validation_errors['cavalos_forca']) ? 'is-invalid' : '' ?>" id="cavalos_forca" name="cavalos_forca" value="<?= htmlspecialchars($display_data['cavalos_forca'] ?? '') ?>">
                                            <?php if (isset($validation_errors['cavalos_forca'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['cavalos_forca']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="torque" class="form-label">Torque</label>
                                            <input type="text" class="form-control <?= isset($validation_errors['torque']) ? 'is-invalid' : '' ?>" id="torque" name="torque" maxlength="50" placeholder="Ex: 250 Nm" value="<?= htmlspecialchars($display_data['torque'] ?? '') ?>">
                                            <?php if (isset($validation_errors['torque'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['torque']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="tipo_cambio" class="form-label">Tipo de Câmbio</label>
                                            <select class="form-select <?= isset($validation_errors['tipo_cambio']) ? 'is-invalid' : '' ?>" id="tipo_cambio" name="tipo_cambio">
                                                <option value="" <?= (!isset($display_data['tipo_cambio']) || $display_data['tipo_cambio'] === '') ? 'selected' : '' ?> disabled>Selecione...</option>
                                                <?php
                                                $tiposCambio = ['Manual', 'Automático'];
                                                foreach ($tiposCambio as $tipo) {
                                                    $selected = (isset($display_data['tipo_cambio']) && $display_data['tipo_cambio'] == $tipo) ? 'selected' : '';
                                                    echo "<option value=\"$tipo\" $selected>$tipo</option>";
                                                }
                                                ?>
                                            </select>
                                            <?php if (isset($validation_errors['tipo_cambio'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['tipo_cambio']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="numero_marchas" class="form-label">Número de Marchas</label>
                                            <input type="number" class="form-control <?= isset($validation_errors['numero_marchas']) ? 'is-invalid' : '' ?>" id="numero_marchas" name="numero_marchas" min="0" value="<?= htmlspecialchars($display_data['numero_marchas'] ?? '') ?>">
                                            <?php if (isset($validation_errors['numero_marchas'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['numero_marchas']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="diversos-pane" role="tabpanel" aria-labelledby="diversos-tab" tabindex="0">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="quilometragem" class="form-label">Quilometragem (km)</label>
                                            <input type="number" class="form-control <?= isset($validation_errors['quilometragem']) ? 'is-invalid' : '' ?>" id="quilometragem" name="quilometragem" value="<?= htmlspecialchars($display_data['quilometragem'] ?? '') ?>">
                                            <?php if (isset($validation_errors['quilometragem'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['quilometragem']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="garantia_fim" class="form-label">Fim da Garantia</label>
                                            <input type="date" class="form-control <?= isset($validation_errors['garantia_fim']) ? 'is-invalid' : '' ?>" id="garantia_fim" name="garantia_fim" value="<?= htmlspecialchars($display_data['garantia_fim'] ?? '') ?>">
                                            <?php if (isset($validation_errors['garantia_fim'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= htmlspecialchars($validation_errors['garantia_fim']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <hr class="my-4">
                            <button class="btn btn-outline-success" type="submit"><i class="fas fa-plus-circle me-2"></i>Atualizar Viatura</button>
                            <!-- O ID é crucial para o reset do formulário para esta viatura específica -->
                            <a href="editar_viatura.php?id=<?= htmlspecialchars($display_data['id'] ?? '') ?>" class="btn btn-outline-secondary"><i class="fas fa-redo me-2"></i> Formulário</a>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info" role="alert">
                            Nenhum dado de viatura para exibir ou editar. Por favor, selecione uma viatura válida.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="b-example-divider b-example-vr"></div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <!-- Toasts serão injetados aqui pelo JavaScript -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Se você quer ícones como bi bi-check-circle-fill, precisa incluir o Bootstrap Icons CSS: -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> -->

    <script>
        // Função para exibir Toasts (a mesma que você já tem)
        function showToast(message, type = 'success') {
            const toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                console.error('Toast container not found! Make sure you have <div class="toast-container"> in your HTML.');
                return;
            }

            let bgColorClass = '';
            let iconClass = '';
            let toastHeaderContent = '';

            if (type === 'success') {
                bgColorClass = 'text-bg-success';
                iconClass = 'bi bi-check-circle-fill';
                toastHeaderContent = `<i class="${iconClass} me-2"></i><strong class="me-auto">Sucesso!</strong>`;
            } else if (type === 'danger') {
                bgColorClass = 'text-bg-danger';
                iconClass = 'bi bi-x-circle-fill';
                toastHeaderContent = `<i class="${iconClass} me-2"></i><strong class="me-auto">Erro!</strong>`;
            } else if (type === 'warning') {
                bgColorClass = 'text-bg-warning';
                iconClass = 'bi bi-exclamation-triangle-fill';
                toastHeaderContent = `<i class="${iconClass} me-2"></i><strong class="me-auto">Atenção!</strong>`;
            } else {
                bgColorClass = 'text-bg-info';
                iconClass = 'bi bi-info-circle-fill';
                toastHeaderContent = `<i class="${iconClass} me-2"></i><strong class="me-auto">Informação</strong>`;
            }

            const toastId = `liveToast-${Date.now()}`;
            const toastHtml = `
            <div id="${toastId}" class="toast ${bgColorClass}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    ${toastHeaderContent}
                    <small>Agora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;

            toastContainer.insertAdjacentHTML('beforeend', toastHtml);

            const toastEl = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 5000 // Toast some após 5 segundos
            });
            toast.show();

            toastEl.addEventListener('hidden.bs.toast', () => {
                toastEl.remove();
            });
        }

        // --- Exibir toasts com base nas mensagens PHP da sessão ---
        <?php if (!empty($toast_message)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?= htmlspecialchars($toast_message, ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($toast_type, ENT_QUOTES, 'UTF-8') ?>');
            });
        <?php endif; ?>

        // Formatação do renavam (a formatação da placa foi removida para evitar conflitos/comportamento indesejado)
        document.addEventListener('DOMContentLoaded', function() {
            const renavamInput = document.getElementById('renavam');
            if (renavamInput) {
                renavamInput.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/[^0-9]/g, '').substring(0, 11); // Limita a 11 dígitos
                });
            }
        });
    </script>