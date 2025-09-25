<?php
// Inclui os arquivos de dependência (sem alterações)
require_once '../components/ConexaoDB/conexao.php';
require_once '../components/DAO/ViaturaDAO.php';
require_once '../api/validate_functions_editar_viatura.php';

// --- Lógica para processar a requisição POST (submissão do formulário) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Definimos o cabeçalho para indicar que a resposta será em JSON
    header('Content-Type: application/json');

    $response = ['success' => false, 'message' => '', 'errors' => []];
    $data_from_form = $_POST;

    $validation_errors = validateViaturaData($data_from_form);

    if (!empty($validation_errors)) {
        $response['message'] = "Houve erros no preenchimento. Por favor, corrija os campos indicados.";
        $response['errors'] = $validation_errors;
    } else {
        try {
            $viaturaDAO = new ViaturaDAO();
            if ($viaturaDAO->updateViatura($data_from_form)) {
                $response['success'] = true;
                $response['message'] = "Viatura atualizada com sucesso!";
            } else {
                $response['message'] = "Erro: Nenhuma alteração foi feita ou a viatura não foi encontrada.";
            }
        } catch (Exception $e) {
            error_log("Erro na atualização da viatura: " . $e->getMessage());
            $response['message'] = "Ocorreu um erro interno no servidor ao tentar atualizar.";
        }
    }

    // Envia a resposta JSON e termina a execução do script.
    echo json_encode($response);
    exit();
}

// --- Lógica para requisição GET continua abaixo, quase sem alterações ---
$id_viatura_get = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Se o ID for inválido, em vez de redirecionar, mostramos um erro.
if (!$id_viatura_get) {
    echo "<div class='container-fluid'><div class='alert alert-danger'>ID da viatura inválido ou não fornecido.</div></div>";
    exit();
}

try {
    $viaturaDAO = new ViaturaDAO();
    $display_data = $viaturaDAO->getViaturaById($id_viatura_get);

    if (!$display_data) {
        echo "<div class='container-fluid'><div class='alert alert-warning'>Viatura não encontrada.</div></div>";
        exit();
    }
} catch (Exception $e) {
    echo "<div class='container-fluid'><div class='alert alert-danger'>Erro de banco de dados ao carregar a viatura.</div></div>";
    exit();
}

// Busca de unidades (se necessário)
$unidadesExemplo = [
    ['id' => 1, 'nome' => '1ºBBM'],
    // ... etc
];

?>

<!-- O restante do seu HTML (o formulário) continua aqui sem alterações -->

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
                        <form action="#" method="POST" id="editViaturaForm">
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
        // --- Lógica de submissão do formulário de edição via AJAX ---
        function initializeEditFormScripts() {
            const form = document.getElementById('editViaturaForm');
            if (!form) return;

            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Impede o recarregamento da página

                // Pega os dados do formulário
                const formData = new FormData(form);
                const submitButton = form.querySelector('button[type="submit"]');

                // Desabilita o botão para evitar cliques duplos
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Atualizando...';

                // Faz a requisição AJAX para a própria página (que agora sabe lidar com POST)
                fetch('pages/editar_viatura.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json()) // Espera uma resposta JSON
                    .then(data => {
                        // Remove mensagens de erro antigas
                        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                        if (data.success) {
                            // Sucesso! Mostra um toast de sucesso.
                            showToast(data.message, 'success');
                        } else {
                            // Erro de validação ou do servidor
                            showToast(data.message, 'danger');

                            // Se houver erros de validação específicos, exibe-os
                            if (data.errors) {
                                for (const fieldName in data.errors) {
                                    const input = document.getElementById(fieldName);
                                    const errorContainer = input ? input.nextElementSibling : null;
                                    if (input && input.classList.contains('form-control')) {
                                        input.classList.add('is-invalid');
                                    }
                                    if (errorContainer && errorContainer.classList.contains('invalid-feedback')) {
                                        errorContainer.textContent = data.errors[fieldName];
                                    }
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Erro na requisição AJAX:', error);
                        showToast('Ocorreu um erro de comunicação. Tente novamente.', 'danger');
                    })
                    .finally(() => {
                        // Reabilita o botão após a requisição
                        submitButton.disabled = false;
                        submitButton.innerHTML = '<i class="fas fa-plus-circle me-2"></i>Atualizar Viatura';
                    });
            });
        }

        // Inicializa os scripts quando a página é carregada
        document.addEventListener('DOMContentLoaded', function() {
            // Suas outras inicializações (renavam, etc) podem ficar aqui
            initializeEditFormScripts();
        });

        // É crucial chamar a função diretamente para o caso de carregamento via AJAX
        initializeEditFormScripts();
    </script>