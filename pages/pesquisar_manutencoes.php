<?php
// pages/pesquisar_manutencoes.php

require_once '../api/middleware_auth.php';
require_once '../components/DAO/ManutencaoDAO.php';

$results = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $manutencaoDAO = new ManutencaoDAO();
        $results = $manutencaoDAO->buscarManutencoes($_POST);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

// Lógica para popular selects pode vir aqui
// Ex: $viaturas = (new ViaturaDAO())->getViaturasParaSelect();
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Pesquisa de Manutenções</h1>
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Formulário de Pesquisa -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="ms-3 font-weight-bold text-primary">Formulário de pesquisa</h6>
                </div>
                <div class="card-body mx-3 my-3">
                    <!-- ID CORRETO ADICIONADO AO FORMULÁRIO -->
                    <form action="#" method="POST" id="formPesquisaManutencoes">
                        <ul class="nav nav-tabs mb-4" id="manutencaoSearchTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="filtros-gerais-tab" data-bs-toggle="tab" data-bs-target="#filtros-gerais-pane" type="button">Filtros Gerais</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="filtros-datas-tab" data-bs-toggle="tab" data-bs-target="#filtros-datas-pane" type="button">Datas e Custos</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="manutencaoSearchTabContent">
                            <!-- Painel 1: Filtros Gerais -->
                            <div class="tab-pane fade show active" id="filtros-gerais-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="viatura_id" class="form-label">Viatura</label>
                                        <select class="form-select" id="viatura_id" name="viatura_id">
                                            <option value="">Todas</option>
                                            <option value='1'>VTR1 - ABC1234</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tipo_manutencao" class="form-label">Tipo de Manutenção</label>
                                        <select class="form-select" id="tipo_manutencao" name="tipo_manutencao">
                                            <option value="">Todos</option>
                                            <option value="Preventiva">Preventiva</option>
                                            <option value="Corretiva">Corretiva</option>
                                            <option value="Preditiva">Preditiva</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="oficina_nome" class="form-label">Nome da Oficina</label>
                                        <input type="text" class="form-control" id="oficina_nome" name="oficina_nome" placeholder="Pesquisar por nome...">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="avaria_id" class="form-label">Avaria Relacionada</label>
                                        <select class="form-select" id="avaria_id" name="avaria_id">
                                            <option value="">Qualquer Avaria</option>
                                            <option value='1'>Elétrica - Curto Circuito</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Painel 2: Datas e Custos -->
                            <div class="tab-pane fade" id="filtros-datas-pane" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6"><label for="data_entrada_min" class="form-label">Data de Entrada (de)</label><input type="date" class="form-control" name="data_entrada_min"></div>
                                    <div class="col-md-6"><label for="data_entrada_max" class="form-label">Data de Entrada (até)</label><input type="date" class="form-control" name="data_entrada_max"></div>
                                    <div class="col-md-6"><label for="data_saida_min" class="form-label">Data de Saída (de)</label><input type="date" class="form-control" name="data_saida_min"></div>
                                    <div class="col-md-6"><label for="data_saida_max" class="form-label">Data de Saída (até)</label><input type="date" class="form-control" name="data_saida_max"></div>
                                    <div class="col-md-6"><label for="custo_min" class="form-label">Custo Mínimo (R$)</label><input type="number" class="form-control" name="custo_min" step="0.01" placeholder="Ex: 50.00"></div>
                                    <div class="col-md-6"><label for="custo_max" class="form-label">Custo Máximo (R$)</label><input type="number" class="form-control" name="custo_max" step="0.01" placeholder="Ex: 500.00"></div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search me-2"></i>Pesquisar</button>
                        <button type="button" class="btn btn-outline-secondary ms-2" id="btnLimparManutencoes"><i class="fas fa-redo me-2"></i>Limpar</button>
                    </form>
                </div>
            </div>

            <!-- Área de Resultados -->
            <div id="resultado-pesquisa-manutencoes">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Resultado</h6></div>
                        <div class="card-body">
                            <?php if (empty($results)): ?>
                                <div class="alert alert-warning">Nenhum registro encontrado.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead><tr><th>Viatura</th><th>Data Entrada</th><th>Data Saída</th><th>Tipo</th><th>Oficina</th><th>Custo</th><th>Ações</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($results as $manutencao): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($manutencao['viatura_prefixo'] . ' / ' . $manutencao['viatura_placa']) ?></td>
                                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($manutencao['data_entrada']))) ?></td>
                                                    <td><?= !empty($manutencao['data_saida']) ? htmlspecialchars(date('d/m/Y', strtotime($manutencao['data_saida']))) : 'Em aberto' ?></td>
                                                    <td><?= htmlspecialchars($manutencao['tipo_manutencao']) ?></td>
                                                    <td><?= htmlspecialchars($manutencao['oficina_nome']) ?></td>
                                                    <td>R$ <?= htmlspecialchars(number_format($manutencao['custo'], 2, ',', '.')) ?></td>
                                                    <td><a href="pages/editar_manutencao.php?id=<?= $manutencao['id'] ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a></td>
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

<!-- ===================================================================== -->
<!-- SCRIPT COMPLETO E CORRIGIDO                                             -->
<!-- ===================================================================== -->
<script>
    function initializePesquisaManutencoesScripts() {
        const form = document.getElementById('formPesquisaManutencoes');
        const areaResultados = document.getElementById('resultado-pesquisa-manutencoes');
        const btnLimpar = document.getElementById('btnLimparManutencoes');

        // Previne reinicialização
        if (!form || form.hasAttribute('data-ajax-initialized')) return;
        form.setAttribute('data-ajax-initialized', 'true');

        // Inicializa as tabs do Bootstrap
        const triggerTabList = Array.from(document.querySelectorAll('#manutencaoSearchTab button'));
        triggerTabList.forEach(triggerEl => {
            const tabTrigger = new bootstrap.Tab(triggerEl);
            triggerEl.addEventListener('click', event => { 
                event.preventDefault(); 
                tabTrigger.show(); 
            });
        });

        // Adiciona funcionalidade ao botão de limpar
        if (btnLimpar) {
            btnLimpar.addEventListener('click', () => form.reset());
        }

        // Adiciona o listener de submit para fazer a busca via AJAX
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(form);
            areaResultados.innerHTML = `<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i> Pesquisando...</div>`;

            fetch('pages/pesquisar_manutencoes.php', { method: 'POST', body: formData })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const novosResultados = doc.getElementById('resultado-pesquisa-manutencoes');
                    if (novosResultados) {
                        areaResultados.innerHTML = novosResultados.innerHTML;
                    } else {
                        areaResultados.innerHTML = '<div class="alert alert-danger">Erro ao processar a resposta da pesquisa.</div>';
                    }
                })
                .catch(error => {
                    console.error('Erro na pesquisa AJAX:', error);
                    areaResultados.innerHTML = '<div class="alert alert-danger">Erro de comunicação com o servidor.</div>';
                });
        });
    }
    
    // Executa a inicialização
    initializePesquisaManutencoesScripts();
</script>