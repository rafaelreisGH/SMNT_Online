<?php
// pages/cadastro_revisao.php
session_start();

require_once '../components/DAO/ViaturaDAO.php'; 

$viaturaDAO = new ViaturaDAO();

// Busca viaturas para o select inicial
try {
    $listaViaturas = $viaturaDAO->listarTodasParaSelect(); 
} catch (Exception $e) {
    $listaViaturas = [];
    error_log("Erro ao carregar viaturas: " . $e->getMessage());
}
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Cadastro de Revisão / Manutenção</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="ms-3 font-weight-bold text-primary">Formulário de Manutenção</h6>
                </div>
                <div class="card-body mx-3 my-3">
                    <form id="cadastrarRevisaoForm" novalidate>
                        
                        <ul class="nav nav-tabs mb-4" id="revisaoTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="dados-basicos-tab" data-bs-toggle="tab" data-bs-target="#dados-basicos-pane" type="button" role="tab">Dados Básicos</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="detalhes-tab" data-bs-toggle="tab" data-bs-target="#detalhes-pane" type="button" role="tab">Itens e Observações</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="proxima-tab" data-bs-toggle="tab" data-bs-target="#proxima-pane" type="button" role="tab">Próxima Revisão</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="revisaoTabContent">
                            <!-- ABA 1: Dados Básicos -->
                            <div class="tab-pane fade show active" id="dados-basicos-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="viatura_id" class="form-label">Viatura</label>
                                        <select class="form-select" id="viatura_id" name="viatura_id">
                                            <option value="">Selecione a Viatura...</option>
                                            <?php foreach ($listaViaturas as $v): ?>
                                                <option value="<?= $v['id'] ?>">
                                                    <?= htmlspecialchars($v['prefixo'] . " - " . $v['modelo'] . " (" . $v['placa'] . ")") ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-viatura_id"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tipo_revisao" class="form-label">Tipo de Revisão/Serviço</label>
                                        <input type="text" class="form-control" id="tipo_revisao" name="tipo_revisao" placeholder="Ex: Troca de óleo">
                                        <div class="invalid-feedback" id="error-tipo_revisao"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="data_revisao" class="form-label">Data da Realização</label>
                                        <input type="date" class="form-control" id="data_revisao" name="data_revisao">
                                        <div class="invalid-feedback" id="error-data_revisao"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="quilometragem" class="form-label">KM no Momento do Serviço</label>
                                        <input type="number" class="form-control" id="quilometragem" name="quilometragem">
                                        <div class="invalid-feedback" id="error-quilometragem"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="status_revisao" class="form-label">Status</label>
                                        <select class="form-select" id="status_revisao" name="status_revisao">
                                            <option value="Realizada">Realizada</option>
                                            <option value="Programada">Programada</option>
                                            <option value="Cancelada">Cancelada</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- ABA 2: Itens e Observações -->
                            <div class="tab-pane fade" id="detalhes-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="itens_substituidos" class="form-label">Itens Substituídos</label>
                                        <textarea class="form-control" id="itens_substituidos" name="itens_substituidos" rows="3"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="observacoes" class="form-label">Observações Gerais</label>
                                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- ABA 3: Próxima Revisão -->
                            <div class="tab-pane fade" id="proxima-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="proxima_revisao_km" class="form-label">Próxima Revisão (KM)</label>
                                        <input type="number" class="form-control" id="proxima_revisao_km" name="proxima_revisao_km">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="proxima_revisao_data" class="form-label">Próxima Revisão (Data)</label>
                                        <input type="date" class="form-control" id="proxima_revisao_data" name="proxima_revisao_data">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <button class="btn btn-outline-success" type="submit" id="btnSalvar">
                            <i class="fas fa-save me-2"></i>Salvar Manutenção
                        </button>
                        <button type="reset" class="btn btn-outline-secondary ms-2"><i class="fas fa-redo me-2"></i>Limpar</button>
                    </form>
                </div>
            </div>

            <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
        </div>
    </div>

    <script>
        document.getElementById('cadastrarRevisaoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const btn = document.getElementById('btnSalvar');
            const formData = new FormData(form);

            // Resetar erros visuais
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            btn.disabled = true;

            fetch('../api/processar_cadastro_revisao.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    form.reset();
                } else {
                    showToast(data.message, 'danger');
                    if (data.errors) {
                        // Mapeia os erros para os campos
                        Object.keys(data.errors).forEach(key => {
                            const input = form.querySelector(`[name="${key}"]`);
                            const errorDiv = document.getElementById(`error-${key}`);
                            if (input) input.classList.add('is-invalid');
                            if (errorDiv) errorDiv.textContent = data.errors[key];
                        });
                    }
                }
            })
            .catch(error => {
                showToast('Erro interno no servidor.', 'danger');
            })
            .finally(() => {
                btn.disabled = false;
            });
        });

        function showToast(message, type = 'success') {
            const toastContainer = document.querySelector('.toast-container');
            const bgColor = type === 'success' ? 'text-bg-success' : 'text-bg-danger';
            const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle';
            
            const toastId = `toast-${Date.now()}`;
            const html = `
                <div id="${toastId}" class="toast ${bgColor}" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header">
                        <i class="${icon} me-2"></i><strong class="me-auto">Sistema</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">${message}</div>
                </div>`;
            
            toastContainer.insertAdjacentHTML('beforeend', html);
            const toastEl = document.getElementById(toastId);
            const bsToast = new bootstrap.Toast(toastEl, { delay: 5000 });
            bsToast.show();
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }
    </script>
</div>