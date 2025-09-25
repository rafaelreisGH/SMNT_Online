<?php
// pages/editar_condutor.php
require_once '../components/DAO/CondutorDAO.php';

// --- LÓGICA POST (API PARA ATUALIZAÇÃO) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    $data = $_POST;

    // AQUI você pode adicionar validações mais robustas, se necessário
    // Ex: $errors = validateCondutorUpdateData($data);

    try {
        $condutorDAO = new CondutorDAO();
        if ($condutorDAO->updateCondutor($data)) {
            $response['success'] = true;
            $response['message'] = "Dados do condutor atualizados com sucesso!";
        } else {
            $response['message'] = "Nenhuma alteração foi realizada ou houve um erro. Verifique se o CPF ou CNH já existem.";
        }
    } catch (Exception $e) {
        error_log("Erro ao atualizar condutor: " . $e->getMessage());
        $response['message'] = "Erro interno no servidor.";
    }
    
    echo json_encode($response);
    exit();
}

// --- LÓGICA GET (EXIBIÇÃO DO FORMULÁRIO) ---
$id_condutor = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_condutor) {
    echo "<div class='alert alert-danger'>ID de condutor inválido.</div>";
    exit();
}

try {
    $condutorDAO = new CondutorDAO();
    $condutorData = $condutorDAO->getCondutorById($id_condutor);
    if (!$condutorData) {
        echo "<div class='alert alert-warning'>Condutor não encontrado.</div>";
        exit();
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Erro ao buscar dados do condutor.</div>";
    exit();
}
// Simulação de unidades para o select
$unidadesExemplo = [['id' => 1, 'nome' => '1ª CIBM'], ['id' => 2, 'nome' => '2ª CIBM'], ['id' => 3, 'nome' => 'QCG']];
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Editar Condutor: <span class="text-primary"><?= htmlspecialchars($condutorData['nome']) ?></span></h1>
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="ms-3 font-weight-bold text-primary">Formulário de Edição</h6>
                </div>
                <div class="card-body mx-3 my-3">
                    <form action="#" method="POST" id="formEditarCondutor">
                        <input type="hidden" name="id" value="<?= $condutorData['id'] ?>">
                        
                        <ul class="nav nav-tabs mb-4" id="condutorEditTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="edit-pessoais-tab" data-bs-toggle="tab" data-bs-target="#edit-pessoais-pane" type="button" role="tab">Dados Pessoais</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="edit-cnh-tab" data-bs-toggle="tab" data-bs-target="#edit-cnh-pane" type="button" role="tab">CNH</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="edit-militares-tab" data-bs-toggle="tab" data-bs-target="#edit-militares-pane" type="button" role="tab">Dados Militares</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="condutorEditTabContent">
                            <!-- Aba de Dados Pessoais -->
                            <div class="tab-pane fade show active" id="edit-pessoais-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label for="nome" class="form-label">Nome Completo</label>
                                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($condutorData['nome'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="cpf" class="form-label">CPF</label>
                                        <input type="text" class="form-control" id="cpf" name="cpf" value="<?= htmlspecialchars($condutorData['cpf'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Aba de CNH -->
                            <div class="tab-pane fade" id="edit-cnh-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="numero_cnh" class="form-label">Número da CNH</label>
                                        <input type="text" class="form-control" id="numero_cnh" name="numero_cnh" value="<?= htmlspecialchars($condutorData['numero_cnh'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="categoria_cnh" class="form-label">Categoria CNH</label>
                                        <input type="text" class="form-control" id="categoria_cnh" name="categoria_cnh" value="<?= htmlspecialchars($condutorData['categoria_cnh'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="data_validade_cnh" class="form-label">Data de Validade CNH</label>
                                        <input type="date" class="form-control" id="data_validade_cnh" name="data_validade_cnh" value="<?= htmlspecialchars($condutorData['data_validade_cnh'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="form-check">
                                            <?php $checked = !empty($condutorData['anotacao_ear']) ? 'checked' : ''; ?>
                                            <input class="form-check-input" type="checkbox" value="1" id="anotacao_ear" name="anotacao_ear" <?= $checked ?>>
                                            <label class="form-check-label" for="anotacao_ear">
                                                Possui EAR (Exerce Atividade Remunerada)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Aba de Dados Militares -->
                            <div class="tab-pane fade" id="edit-militares-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="rg_militar" class="form-label">Identidade Militar (RG)</label>
                                        <input type="text" class="form-control" id="rg_militar" name="rg_militar" value="<?= htmlspecialchars($condutorData['rg_militar'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="posto_grad" class="form-label">Posto/Graduação</label>
                                        <select class="form-select" id="posto_grad" name="posto_grad">
                                            <option value="">Selecione...</option>
                                            <?php
                                            $postosGraduacoes = ['Soldado', 'Cabo', 'Sargento', 'Tenente', 'Capitão', 'Major', 'Tenente-Coronel', 'Coronel'];
                                            foreach ($postosGraduacoes as $posto) {
                                                $selected = ($condutorData['posto_grad'] == $posto) ? 'selected' : '';
                                                echo "<option value=\"$posto\" $selected>$posto</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="ubm_id" class="form-label">Lotação (Unidade)</label>
                                        <select class="form-select" id="ubm_id" name="ubm_id">
                                            <option value="">Selecione a Unidade</option>
                                            <?php
                                            foreach ($unidadesExemplo as $unidade) {
                                                $selected = ($condutorData['ubm_id'] == $unidade['id']) ? 'selected' : '';
                                                echo "<option value=\"{$unidade['id']}\" $selected>{$unidade['nome']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="status" class="form-label">Status do Condutor</label>
                                        <select class="form-select" id="status" name="status">
                                            <?php
                                            $statusCondutor = ['ativo', 'inativo', 'afastado'];
                                            foreach ($statusCondutor as $s) {
                                                $selected = ($condutorData['status'] == $s) ? 'selected' : '';
                                                echo "<option value=\"$s\" $selected>" . ucfirst($s) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <button class="btn btn-outline-success" type="submit"><i class="fas fa-save me-2"></i>Salvar Alterações</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function initializeEditCondutorScripts() {
        const form = document.getElementById('formEditarCondutor');
        if (!form || form.hasAttribute('data-ajax-initialized')) return;
        form.setAttribute('data-ajax-initialized', 'true');

        // Inicializa as tabs do Bootstrap
        const triggerTabList = Array.from(document.querySelectorAll('#condutorEditTab button'));
        triggerTabList.forEach(triggerEl => {
            const tabTrigger = new bootstrap.Tab(triggerEl);
            triggerEl.addEventListener('click', event => {
                event.preventDefault();
                tabTrigger.show();
            });
        });

        // Máscara de CPF (se precisar)
        // ...

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>Salvando...`;

            fetch('pages/editar_condutor.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                window.showToast(data.message, data.success ? 'success' : 'danger');
            })
            .catch(error => {
                console.error('Erro:', error);
                window.showToast('Erro de comunicação com o servidor.', 'danger');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = `<i class="fas fa-save me-2"></i>Salvar Alterações`;
            });
        });
    }
    
    // Executa a inicialização dos scripts
    initializeEditCondutorScripts();
</script>