<?php
// pages/cadastro_viatura.php

// 1. Proteção e Dependências
require_once '../api/middleware_auth.php';
require_once '../components/DAO/ViaturaDAO.php';
require_once '../components/DAO/UnidadeDAO.php';
require_once '../components/DAO/PlanoManutencaoDAO.php'; // ADICIONADO
require_once '../api/validate_functions_editar_viatura.php';
require_once '../components/DAO/CombustivelDAO.php';

$combDAO = new CombustivelDAO();
$combustiveis = $combDAO->listarTodos();

// 2. Busca de dados para os Selects
$unidadeDAO = new UnidadeDAO();
$unidadesAgrupadas = $unidadeDAO->listarAgrupadasPorComando();

$pDAO = new PlanoManutencaoDAO();
$listaPlanos = $pDAO->listarTodos(); // ADICIONADO: Agora os planos aparecerão no select
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Cadastro de Viatura</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="ms-3 font-weight-bold text-primary">Formulário de cadastro</h6>
                </div>
                <div class="card-body mx-3 my-3">
                    <form id="cadastrarViaturaForm" novalidate>

                        <ul class="nav nav-tabs mb-4" id="viaturaTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="dados-basicos-tab" data-bs-toggle="tab" data-bs-target="#dados-basicos-pane" type="button" role="tab">Dados Básicos</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="motorizacao-tab" data-bs-toggle="tab" data-bs-target="#motorizacao-pane" type="button" role="tab">Motorização</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="diversos-tab" data-bs-toggle="tab" data-bs-target="#diversos-pane" type="button" role="tab">Diversos</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="viaturaTabContent">
                            <!-- ABA 1: DADOS BÁSICOS -->
                            <div class="tab-pane fade show active" id="dados-basicos-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="tipo_operacional" class="form-label">Tipo Operacional</label>
                                        <select class="form-select" id="tipo_operacional" name="tipo_operacional">
                                            <option value="" selected>Selecione...</option>
                                            <option value="Operacional">Operacional</option>
                                            <option value="Administrativo">Administrativo</option>
                                        </select>
                                        <div class="invalid-feedback" id="error-tipo_operacional"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="prefixo" class="form-label">Prefixo</label>
                                        <input type="text" class="form-control" id="prefixo" name="prefixo" maxlength="5">
                                        <div class="invalid-feedback" id="error-prefixo"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="numero" class="form-label">Número</label>
                                        <input type="number" min="1" class="form-control" id="numero" name="numero">
                                        <div class="invalid-feedback" id="error-numero"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="placa" class="form-label">Placa</label>
                                        <input type="text" class="form-control" id="placa" name="placa" maxlength="7" placeholder="Ex: ABC1234">
                                        <div class="invalid-feedback" id="error-placa"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="renavam" class="form-label">Renavam</label>
                                        <input type="text" class="form-control" id="renavam" name="renavam" maxlength="11">
                                        <div class="invalid-feedback" id="error-renavam"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="marca" class="form-label">Marca</label>
                                        <input type="text" class="form-control" id="marca" name="marca">
                                        <div class="invalid-feedback" id="error-marca"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="modelo" class="form-label">Modelo</label>
                                        <input type="text" class="form-control" id="modelo" name="modelo">
                                        <div class="invalid-feedback" id="error-modelo"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="ano" class="form-label">Ano</label>
                                        <input type="number" min="1950" max="2026" class="form-control" id="ano" name="ano">
                                        <div class="invalid-feedback" id="error-ano"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="pbt" class="form-label">PBT (kg)</label>
                                        <input type="number" min="1" class="form-control" id="pbt" name="pbt">
                                        <div class="invalid-feedback" id="error-pbt"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tipo_aquisicao" class="form-label">Tipo de Aquisição</label>
                                        <select class="form-select" id="tipo_aquisicao" name="tipo_aquisicao">
                                            <option value="" selected>Selecione...</option>
                                            <option value="Própria">Própria</option>
                                            <option value="Doação">Doação</option>
                                            <option value="Fiel Depositário">Fiel Depositário</option>
                                            <option value="Locada">Locada</option>
                                        </select>
                                        <div class="invalid-feedback" id="error-tipo_aquisicao"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="status_da_viatura" class="form-label">Status da Viatura</label>
                                        <select class="form-select" id="status_da_viatura" name="status_da_viatura">
                                            <option value="" selected>Selecione...</option>
                                            <option value="Operante">Operante</option>
                                            <option value="Baixada">Baixada</option>
                                            <option value="Descarregada">Descarregada</option>
                                            <option value="Indisponível">Indisponível</option>
                                        </select>
                                        <div class="invalid-feedback" id="error-status_da_viatura"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lotacao_atual_id" class="form-label">Lotação Atual</label>
                                        <select class="form-select" id="lotacao_atual_id" name="lotacao_atual_id">
                                            <option value="" selected>Selecione a Unidade</option>
                                            <?php foreach ($unidadesAgrupadas as $comando => $unidades): ?>
                                                <optgroup label="<?= htmlspecialchars($comando) ?>">
                                                    <?php foreach ($unidades as $unidade): ?>
                                                        <option value="<?= $unidade['id'] ?>">
                                                            <?= htmlspecialchars($unidade['unidade_nome']) . (!empty($unidade['municipio_nome']) ? " - " . htmlspecialchars($unidade['municipio_nome']) : "") ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback" id="error-lotacao_atual_id"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="plano_id" class="form-label text-primary fw-bold">Plano de Manutenção Preventiva</label>
                                        <select class="form-select border-primary" id="plano_id" name="plano_id">
                                            <option value="">Nenhum plano associado</option>
                                            <?php foreach ($listaPlanos as $plano): ?>
                                                <option value="<?= $plano['id'] ?>">
                                                    <?= htmlspecialchars($plano['descricao']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Define as regras de trocas e revisões para este modelo.</small>
                                    </div>
                                </div>
                            </div>

                            <!-- ABA 2: MOTORIZAÇÃO -->
                            <div class="tab-pane fade" id="motorizacao-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="cilindradas" class="form-label">Cilindradas (cc)</label>
                                        <input type="number" min="1" class="form-control" id="cilindradas" name="cilindradas">
                                        <div class="invalid-feedback" id="error-cilindradas"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cavalos_forca" class="form-label">Cavalos de Força (cv)</label>
                                        <input type="number" min="1" class="form-control" id="cavalos_forca" name="cavalos_forca">
                                        <div class="invalid-feedback" id="error-cavalos_forca"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="torque" class="form-label">Torque</label>
                                        <input type="text" class="form-control" id="torque" name="torque" placeholder="Ex: 250 Nm">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tipo_cambio" class="form-label">Tipo de Câmbio</label>
                                        <select class="form-select" id="tipo_cambio" name="tipo_cambio">
                                            <option value="" selected>Selecione...</option>
                                            <option value="Manual">Manual</option>
                                            <option value="Automático">Automático</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="numero_marchas" class="form-label">Número de Marchas</label>
                                        <input type="number" min="1" class="form-control" id="numero_marchas" name="numero_marchas">
                                    </div>
                                    <div class="col-12 mt-4">
                                        <h6 class="fw-bold text-primary"><i class="fas fa-gas-pump me-2"></i>Tanques e Combustíveis</h6>
                                        <div class="row g-2">
                                            <?php foreach ($combustiveis as $cb): ?>
                                                <div class="col-md-6">
                                                    <div class="border rounded p-2 d-flex align-items-center justify-content-between bg-light">
                                                        <div class="col">
                                                            <div class="form-check form-switch px-5  d-flex align-items-center" style="min-height: 50px;">
                                                                <input class="form-check-input check-item" type="checkbox" name="tanques[<?= $cb['id'] ?>][ativo]" value="1" id="cb_<?= $cb['id'] ?>"
                                                                    onchange="document.getElementById('litros_<?= $cb['id'] ?>').disabled = !this.checked">
                                                                <label class="form-check-label small fw-bold" for="cb_<?= $cb['id'] ?>"><?= htmlspecialchars($cb['tipo']) ?></label>
                                                            </div>
                                                        </div>
                                                        <div class="input-group input-group-sm" style="width: 120px;">
                                                            <input type="number" min="1" name="tanques[<?= $cb['id'] ?>][litragem]" id="litros_<?= $cb['id'] ?>" class="form-control" placeholder="Lts" disabled>
                                                            <span class="input-group-text">L</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ABA 3: DIVERSOS -->
                            <div class="tab-pane fade" id="diversos-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="quilometragem" class="form-label">Quilometragem (km)</label>
                                        <input type="number" min="0" class="form-control" id="quilometragem" name="quilometragem">
                                        <div class="invalid-feedback" id="error-quilometragem"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="garantia_fim" class="form-label">Fim da Garantia</label>
                                        <input type="date" class="form-control" id="garantia_fim" name="garantia_fim">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <button class="btn btn-outline-success" type="submit" id="btnSalvar">
                            <i class="fas fa-plus-circle me-2"></i>Cadastrar Viatura
                        </button>
                        <button type="button" onclick="limparFormulario()" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-redo me-2"></i>Limpar Formulário
                        </button>
                        <!-- NOVO BOTÃO DE RETORNO -->
                        <button type="button" class="btn btn-outline-secondary ms-2" onclick="loadPage('pages/pesquisar_viatura.php')">
                            <i class="fas fa-arrow-left me-2"></i>Voltar para Pesquisa
                        </button>
                    </form>
                </div>
            </div>

            <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('cadastrarViaturaForm');
            const btn = document.getElementById('btnSalvar');

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // 1. Reset visual inicial
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
                form.querySelectorAll('.nav-link').forEach(el => el.classList.remove('text-danger'));

                // Feedback visual de carregamento
                btn.disabled = true;
                const originalBtnContent = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Validando...';

                const formData = new FormData(form);

                fetch('api/processar_cadastro_viatura.php', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                        },
                        body: formData
                    })
                    .then(async response => {
                        // Tratamento para Sessão Expirada
                        if (response.status === 401) {
                            window.location.href = 'login.php';
                            return;
                        }

                        // Tenta ler como texto primeiro para diagnosticar erros do PHP
                        const text = await response.text();
                        try {
                            return JSON.parse(text);
                        } catch (err) {
                            console.error("Resposta do servidor não é um JSON válido:", text);
                            throw new Error("Erro crítico no servidor. Verifique o console (F12).");
                        }
                    })
                    .then(data => {
                        if (!data) return;

                        if (data.success) {
                            // CASO DE SUCESSO
                            showToast(data.message, 'success');
                            form.reset();
                            // Volta para a primeira aba
                            const firstTabEl = document.querySelector('#viaturaTab button:first-child');
                            const firstTab = new bootstrap.Tab(firstTabEl);
                            firstTab.show();
                        } else {
                            // CASO DE ERRO DE VALIDAÇÃO OU BANCO
                            showToast(data.message || "Houve erros no preenchimento.", 'danger');

                            if (data.errors) {
                                let primeiraAbaComErro = null;

                                Object.keys(data.errors).forEach(key => {
                                    // Tenta encontrar o campo (por ID ou Name)
                                    const input = document.getElementById(key) || form.querySelector(`[name="${key}"]`);
                                    const errorDiv = document.getElementById(`error-${key}`);

                                    if (input) {
                                        input.classList.add('is-invalid');

                                        // Identifica a aba onde o erro está
                                        const tabPane = input.closest('.tab-pane');
                                        if (tabPane) {
                                            const tabId = tabPane.id;
                                            const tabButton = document.querySelector(`button[data-bs-target="#${tabId}"]`);
                                            if (tabButton) {
                                                tabButton.classList.add('text-danger');
                                                if (!primeiraAbaComErro) primeiraAbaComErro = tabButton;
                                            }
                                        }
                                    }

                                    if (errorDiv) {
                                        errorDiv.textContent = data.errors[key];
                                        errorDiv.style.display = 'block';
                                    }
                                });

                                // Se houver erro em uma aba escondida, pula para a primeira delas
                                if (primeiraAbaComErro) {
                                    const tab = new bootstrap.Tab(primeiraAbaComErro);
                                    tab.show();
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Erro AJAX:', error);
                        showToast(error.message, 'danger');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = originalBtnContent;
                    });
            });

            // Função de Limpar Formulário
            window.limparFormulario = function() {
                if (!confirm("Deseja realmente limpar todos os campos?")) return;
                form.reset();
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
                form.querySelectorAll('.nav-link').forEach(el => el.classList.remove('text-danger'));
                document.getElementById('tipo_operacional').focus();
            };

            // Função de Toast local
            function showToast(message, type = 'success') {
                if (window.showToast) {
                    window.showToast(message, type);
                    return;
                }
                const container = document.querySelector('.toast-container');
                if (!container) return;

                const bgColor = type === 'success' ? 'text-bg-success' : 'text-bg-danger';
                const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle';
                const id = `toast-${Date.now()}`;
                const html = `
                <div id="${id}" class="toast ${bgColor} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="${icon} me-2"></i> ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>`;
                container.insertAdjacentHTML('beforeend', html);
                const toastEl = document.getElementById(id);
                const bsToast = new bootstrap.Toast(toastEl, {
                    delay: 5000
                });
                bsToast.show();
                toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
            }
        })();
    </script>