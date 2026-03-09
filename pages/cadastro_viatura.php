<?php
// pages/cadastro_viatura.php
// CRÍTICO: session_start() DEVE ser a primeira coisa no arquivo PHP,
// antes de qualquer HTML ou includes que gerem HTML.
session_start();

// Inclui os arquivos de dependência. Eles também não devem gerar saída HTML antes de sidebar.php.
require_once '../components/DAO/ViaturaDAO.php'; // Inclui a classe ViaturaDAO
require_once '../components/DAO/UnidadeDAO.php'; // Inclui a classe UnidadeDAO para buscar as unidades (se necessário)
require_once '../api/validate_functions_editar_viatura.php'; // Inclui a função de validação

// 2. Busca as unidades REAIS do banco de dados
$unidadeDAO = new UnidadeDAO();
$unidadesAgrupadas = $unidadeDAO->listarAgrupadasPorComando(); // Agora temos um array multidimensional agrupado por comando

// Variáveis para mensagens e erros, populadas a partir da sessão (se houver)
$toast_message = $_SESSION['toast_message'] ?? '';
$toast_type = $_SESSION['toast_type'] ?? '';
$validation_errors = $_SESSION['validation_errors'] ?? [];

// Limpa as mensagens de sessão e erros de validação após recuperá-los
unset($_SESSION['toast_message']);
unset($_SESSION['toast_type']);
unset($_SESSION['validation_errors']);

// Inicializa $display_data. Em um formulário de cadastro, ele começa vazio
// e será preenchido com $_POST em caso de erro de validação para manter os dados preenchidos.
$display_data = [];

// --- Lógica para processar a requisição POST (submissão do formulário) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Copia os dados do POST para um array mutável, que será validado e filtrado
    $data_from_form = $_POST;

    // Realiza a validação usando a função externa.
    // Esta função agora também chama o ViaturaDAO para verificar a unicidade.
    $validation_errors = validateViaturaData($data_from_form);

    // No caso de cadastro (INSERT), o ID é AUTO_INCREMENT e não é fornecido pelo formulário.
    // A função validateViaturaData pode gerar um erro para 'id' se ele for obrigatório para edição.
    // Removemos esse erro específico, pois é esperado em um formulário de cadastro.
    unset($validation_errors['id']);

    // Importante: Os campos 'tipo_carroceria' e 'equipamentos_especificos' presentes no HTML
    // não foram especificados no seu schema de tabela 'viaturas'.
    // Se eles não existirem na tabela, eles PRECISAM ser removidos do array $data_from_form
    // antes de tentar inseri-los no banco para evitar um erro de "Coluna inválida".
    // Ou, você deve adicionar essas colunas à sua tabela 'viaturas' e ao seu método insertViatura no ViaturaDAO.
    unset($data_from_form['tipo_carroceria']);
    unset($data_from_form['equipamentos_especificos']);


    if (!empty($validation_errors)) {
        // Se houver erros de validação, define as mensagens de toast e usa os dados do formulário
        // (que já foram filtrados pela função de validação) para re-preencher o formulário.
        $toast_message = "Houve erros no preenchimento do formulário. Por favor, corrija os campos indicados.";
        $toast_type = "danger";
        $display_data = $data_from_form; // Mantém os dados validados/filtrados para re-preenchimento
    } else {
        // Se a validação passar, os dados em $data_from_form já estão limpos e prontos para o DAO.
        // Remove explicitamente o 'id' do array, garantindo que o DAO não tente inseri-lo,
        // confiando no AUTO_INCREMENT do banco de dados.
        unset($data_from_form['id']);

        try {
            // Instancia o DAO
            $viaturaDAO = new ViaturaDAO();

            // Tenta inserir a viatura usando o método insertViatura
            if ($viaturaDAO->insertViatura($data_from_form)) {
                // Mensagem de sucesso e LIMPA o formulário
                $toast_message = "Viatura cadastrada com sucesso!";
                $toast_type = "success";
                $display_data = []; // Limpa o formulário após o sucesso para um novo cadastro
            } else {
                // Caso o insertViatura retorne false (erro interno no DAO que não lançou exceção)
                $toast_message = "Erro ao cadastrar a viatura no banco de dados. Verifique os logs para detalhes ou tente novamente.";
                $toast_type = "danger";
                $display_data = $data_from_form; // Mantém os dados submetidos para re-preenchimento
            }
        } catch (PDOException $e) {
            // Captura erros específicos do PDO
            error_log("Exceção PDO no cadastro da viatura: " . $e->getMessage()); // Loga o erro detalhado no servidor
            $toast_message = "Erro interno no banco de dados (PDO): " . $e->getMessage();
            $toast_type = "danger";
            $display_data = $data_from_form; // Mantém os dados submetidos
        } catch (Exception $e) {
            // Captura outras exceções genéricas (como as relançadas pelo ViaturaDAO durante a verificação de unicidade ou inserção)
            error_log("Exceção geral no cadastro da viatura: " . $e->getMessage()); // Loga o erro detalhado no servidor
            $toast_message = "Ocorreu um erro inesperado: " . $e->getMessage();
            $toast_type = "danger";
            $display_data = $data_from_form; // Mantém os dados submetidos
        }
    }
}
// --- Lógica para requisição GET (carregar formulário pela primeira vez) ---
// Quando a página é acessada via GET, $display_data já está vazio, que é o comportamento desejado para um novo formulário.
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h4 mb-4 text-gray-800">Cadastro de Viatura</h1>

    <div class="row">

        <div class="col-lg-10 mx-auto">

            <!-- Brand Buttons -->

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="ms-3 font-weight-bold text-primary">Formulário de cadastro</h6>
                </div>
                <div class="card-body mx-3 my-3">
                    <!-- O 'action' deve apontar para a própria página de cadastro para processamento POST -->
                    <!-- 'novalidate' impede a validação HTML5 padrão do navegador, permitindo controle total via PHP/JS -->
                    <form action="cadastro.php" method="POST" id="cadastrarViaturaForm" novalidate>
                        <!-- Não há campo 'id' oculto para INSERT, pois é AUTO_INCREMENT -->

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
                                            <!-- Removido 'disabled' para que a validação PHP possa atuar. 'selected' ainda funciona. -->
                                            <option value="" <?= (!isset($display_data['tipo_operacional']) || $display_data['tipo_operacional'] === '') ? 'selected' : '' ?>>Selecione...</option>
                                            <?php
                                            // Corrigido 'Operacional General' para 'Operacional Geral' conforme seu schema
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
                                        <input type="number" min="1" class="form-control <?= isset($validation_errors['numero']) ? 'is-invalid' : '' ?>" id="numero" name="numero" value="<?= htmlspecialchars($display_data['numero'] ?? '') ?>">
                                        <?php if (isset($validation_errors['numero'])): ?>
                                            <div class="invalid-feedback">
                                                <?= htmlspecialchars($validation_errors['numero']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="placa" class="form-label">Placa</label>
                                        <input type="text" class="form-control <?= isset($validation_errors['placa']) ? 'is-invalid' : '' ?>" id="placa" name="placa" maxlength="7" placeholder="Ex: ABC1234" value="<?= htmlspecialchars($display_data['placa'] ?? '') ?>">
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
                                        <input type="number" min="1950" class="form-control <?= isset($validation_errors['ano']) ? 'is-invalid' : '' ?>" id="ano" name="ano" min="1900" max="<?= date('Y') + 1 ?>" value="<?= htmlspecialchars($display_data['ano'] ?? '') ?>">
                                        <?php if (isset($validation_errors['ano'])): ?>
                                            <div class="invalid-feedback">
                                                <?= htmlspecialchars($validation_errors['ano']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="pbt" class="form-label">PBT (kg)</label>
                                        <input type="number" min="1" class="form-control <?= isset($validation_errors['pbt']) ? 'is-invalid' : '' ?>" id="pbt" name="pbt" value="<?= htmlspecialchars($display_data['pbt'] ?? '') ?>">
                                        <?php if (isset($validation_errors['pbt'])): ?>
                                            <div class="invalid-feedback">
                                                <?= htmlspecialchars($validation_errors['pbt']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tipo_aquisicao" class="form-label">Tipo de Aquisição</label>
                                        <select class="form-select <?= isset($validation_errors['tipo_aquisicao']) ? 'is-invalid' : '' ?>" id="tipo_aquisicao" name="tipo_aquisicao">
                                            <option value="" <?= (!isset($display_data['tipo_aquisicao']) || $display_data['tipo_aquisicao'] === '') ? 'selected' : '' ?>>Selecione...</option>
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
                                            <option value="" <?= (!isset($display_data['status_da_viatura']) || $display_data['status_da_viatura'] === '') ? 'selected' : '' ?>>Selecione...</option>
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
                                            <option value="" <?= (!isset($display_data['lotacao_atual_id']) || $display_data['lotacao_atual_id'] === '') ? 'selected' : '' ?>>Selecione a Unidade</option>

                                            <?php foreach ($unidadesAgrupadas as $comando => $unidades): ?>
                                                <optgroup label="<?= htmlspecialchars($comando) ?>">
                                                    <?php foreach ($unidades as $unidade): ?>
                                                        <?php
                                                        // Formata o nome: "Nome da Unidade - Município" (se o município existir)
                                                        $labelUnidade = htmlspecialchars($unidade['unidade_nome']);
                                                        if (!empty($unidade['municipio_nome'])) {
                                                            $labelUnidade .= " - " . htmlspecialchars($unidade['municipio_nome']);
                                                        }

                                                        $selected = (isset($display_data['lotacao_atual_id']) && $display_data['lotacao_atual_id'] == $unidade['id']) ? 'selected' : '';
                                                        ?>
                                                        <option value="<?= $unidade['id'] ?>" <?= $selected ?>>
                                                            <?= $labelUnidade ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endforeach; ?>
                                        </select>

                                        <?php if (isset($validation_errors['lotacao_atual_id'])): ?>
                                            <div class="invalid-feedback">
                                                <?= htmlspecialchars($validation_errors['lotacao_atual_id']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Estes campos 'tipo_carroceria' e 'equipamentos_especificos' NÃO estão no seu schema da tabela 'viaturas'.
                                     Eles foram removidos do array $data_from_form no PHP para evitar erros de coluna.
                                     Se você pretende salvá-los, precisará ADICIONÁ-LOS à sua tabela 'viaturas' e ao DAO. -->
                                    <div class="col-12 mt-4">
                                        <h6><strong>Características Adicionais</strong></h6>
                                        <hr>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tipo_carroceria" class="form-label">Tipo de Carroceria</label>
                                        <input type="text" class="form-control <?= isset($validation_errors['tipo_carroceria']) ? 'is-invalid' : '' ?>" id="tipo_carroceria" name="tipo_carroceria" maxlength="100" value="<?= htmlspecialchars($display_data['tipo_carroceria'] ?? '') ?>">
                                        <?php if (isset($validation_errors['tipo_carroceria'])): ?>
                                            <div class="invalid-feedback">
                                                <?= htmlspecialchars($validation_errors['tipo_carroceria']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="equipamentos_especificos" class="form-label">Equipamentos Específicos</label>
                                        <textarea class="form-control <?= isset($validation_errors['equipamentos_especificos']) ? 'is-invalid' : '' ?>" id="equipamentos_especificos" name="equipamentos_especificos" rows="3"><?= htmlspecialchars($display_data['equipamentos_especificos'] ?? '') ?></textarea>
                                        <?php if (isset($validation_errors['equipamentos_especificos'])): ?>
                                            <div class="invalid-feedback">
                                                <?= htmlspecialchars($validation_errors['equipamentos_especificos']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="motorizacao-pane" role="tabpanel" aria-labelledby="motorizacao-tab" tabindex="0">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="cilindradas" class="form-label">Cilindradas (cc)</label>
                                        <input type="number" min="1" class="form-control <?= isset($validation_errors['cilindradas']) ? 'is-invalid' : '' ?>" id="cilindradas" name="cilindradas" value="<?= htmlspecialchars($display_data['cilindradas'] ?? '') ?>">
                                        <?php if (isset($validation_errors['cilindradas'])): ?>
                                            <div class="invalid-feedback">
                                                <?= htmlspecialchars($validation_errors['cilindradas']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cavalos_forca" class="form-label">Cavalos de Força (cv)</label>
                                        <input type="number" min="1" class="form-control <?= isset($validation_errors['cavalos_forca']) ? 'is-invalid' : '' ?>" id="cavalos_forca" name="cavalos_forca" value="<?= htmlspecialchars($display_data['cavalos_forca'] ?? '') ?>">
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
                                            <option value="" <?= (!isset($display_data['tipo_cambio']) || $display_data['tipo_cambio'] === '') ? 'selected' : '' ?>>Selecione...</option>
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
                                        <input type="number" class="form-control <?= isset($validation_errors['numero_marchas']) ? 'is-invalid' : '' ?>" id="numero_marchas" name="numero_marchas" min="1" value="<?= htmlspecialchars($display_data['numero_marchas'] ?? '') ?>">
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
                                        <input type="number" min="0" class="form-control <?= isset($validation_errors['quilometragem']) ? 'is-invalid' : '' ?>" id="quilometragem" name="quilometragem" value="<?= htmlspecialchars($display_data['quilometragem'] ?? '') ?>">
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
                        <button class="btn btn-outline-success" type="submit"><i class="fas fa-plus-circle me-2"></i>Cadastrar Viatura</button>
                        <!-- Botão para limpar o formulário (recarrega a página) -->
                        <button type="button" onclick="limparFormulario()" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-redo me-2"></i>Limpar Formulário
                        </button>
                    </form>
                </div>
            </div>

            <!-- Toast Container para mensagens -->
            <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

        </div>
    </div>

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

            // Usando Font Awesome para os ícones, já que o Bootstrap Icons CSS não está explicitamente incluído.
            if (type === 'success') {
                bgColorClass = 'text-bg-success';
                iconClass = 'fas fa-check-circle'; // Ícone de sucesso Font Awesome
                toastHeaderContent = `<i class="${iconClass} me-2"></i><strong class="me-auto">Sucesso!</strong>`;
            } else if (type === 'danger') {
                bgColorClass = 'text-bg-danger';
                iconClass = 'fas fa-times-circle'; // Ícone de erro Font Awesome
                toastHeaderContent = `<i class="${iconClass} me-2"></i><strong class="me-auto">Erro!</strong>`;
            } else if (type === 'warning') {
                bgColorClass = 'text-bg-warning';
                iconClass = 'fas fa-exclamation-triangle'; // Ícone de aviso Font Awesome
                toastHeaderContent = `<i class="${iconClass} me-2"></i><strong class="me-auto">Atenção!</strong>`;
            } else {
                bgColorClass = 'text-bg-info';
                iconClass = 'fas fa-info-circle'; // Ícone de informação Font Awesome
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

        function limparFormulario() {
            // Seleciona o formulário pelo ID
            const form = document.getElementById('cadastrarViaturaForm');

            // Reseta todos os campos para o valor inicial
            form.reset();

            // Opcional: Remove as classes de erro (is-invalid) se houver
            const invalidInputs = form.querySelectorAll('.is-invalid');
            invalidInputs.forEach(input => input.classList.remove('is-invalid'));

            // Opcional: Esconde as mensagens de feedback de erro
            const feedbacks = form.querySelectorAll('.invalid-feedback');
            feedbacks.forEach(div => div.style.display = 'none');

            // Opcional: Focar no primeiro campo
            document.getElementById('tipo_operacional').focus();
        }
    </script>