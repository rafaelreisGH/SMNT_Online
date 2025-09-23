<?php
include_once '../templates/sidebar.php';

?>

<div class="d-flex min-vh-100 w-100" style="padding-bottom: 3rem;">
    <div class="row justify-content-center w-100" style="padding-top: 3rem;">
        <div class=" col-12 col-md-10 col-lg-6">
            <div class="content-area">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <h1 class="mb-3">Pesquisa de Manutenções</h1>
                        <p class="lead">Utilize os filtros abaixo para encontrar registros de manutenções no sistema.</p>
                        <p>Preencha um ou mais campos para refinar sua busca.</p>
                        <hr class="mb-4">
                    </div>
                </div>

                <form action="#" method="POST">
                    <ul class="nav nav-tabs mb-4" id="manutencaoSearchTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="filtros-gerais-tab" data-bs-toggle="tab" data-bs-target="#filtros-gerais-pane" type="button" role="tab" aria-controls="filtros-gerais-pane" aria-selected="true">Filtros Gerais</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="filtros-datas-tab" data-bs-toggle="tab" data-bs-target="#filtros-datas-pane" type="button" role="tab" aria-controls="filtros-datas-pane" aria-selected="false">Filtros de Datas</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="manutencaoSearchTabContent">

                        <div class="tab-pane fade show active" id="filtros-gerais-pane" role="tabpanel" aria-labelledby="filtros-gerais-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="viatura_id" class="form-label">Viatura (Placa/Prefixo)</label>
                                    <select class="form-select" id="viatura_id" name="viatura_id">
                                        <option value="">Todas as Viaturas</option>
                                        <?php
                                        /*
                                        // A variável $viaturas viria do PHP comentado acima
                                        foreach ($viaturas as $viatura) {
                                            $display = '';
                                            if (!empty($viatura['placa'])) {
                                                $display = $viatura['placa'];
                                            } elseif (!empty($viatura['prefixo'])) {
                                                $display = $viatura['prefixo'];
                                            } elseif (!empty($viatura['numero'])) {
                                                $display = 'Número: ' . $viatura['numero'];
                                            } else {
                                                $display = 'ID: ' . $viatura['id'];
                                            }
                                            $selected = (isset($_POST['viatura_id']) && $_POST['viatura_id'] == $viatura['id']) ? 'selected' : '';
                                            echo "<option value=\"{$viatura['id']}\" $selected>{$display}</option>";
                                        }
                                        */
                                        // Para demonstração sem backend, adicione algumas opções de exemplo:
                                        echo "<option value='1'>ABC1234 (Prefixo: VTR1)</option>";
                                        echo "<option value='2'>DEF5678 (Número: 001)</option>";
                                        echo "<option value='3'>GHI9012 (Prefixo: AUTO)</option>";
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="avaria_id" class="form-label">Tipo de Avaria</label>
                                    <select class="form-select" id="avaria_id" name="avaria_id">
                                        <option value="">Todos os Tipos</option>
                                        <?php
                                        /*
                                        // A variável $avarias viria do PHP comentado acima
                                        foreach ($avarias as $avaria) {
                                            $display = $avaria['tipo_avaria'] . (empty($avaria['descricao']) ? '' : ' - ' . $avaria['descricao']);
                                            $selected = (isset($_POST['avaria_id']) && $_POST['avaria_id'] == $avaria['id']) ? 'selected' : '';
                                            echo "<option value=\"{$avaria['id']}\" $selected>{$display}</option>";
                                        }
                                        */
                                        // Para demonstração sem backend, adicione algumas opções de exemplo:
                                        echo "<option value='1'>Elétrica - Curto Circuito</option>";
                                        echo "<option value='2'>Mecânica - Freios</option>";
                                        echo "<option value='3'>Pintura - Arranhão</option>";
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="descricao_servico" class="form-label">Descrição do Serviço</label>
                                    <input type="text" class="form-control" id="descricao_servico" name="descricao_servico" placeholder="Ex: troca de óleo, reparo no motor" value="<?= htmlspecialchars($_POST['descricao_servico'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="tempo_oficina_min" class="form-label">Tempo Oficina Mín (horas)</label>
                                    <input type="number" step="0.1" class="form-control" id="tempo_oficina_min" name="tempo_oficina_min" value="<?= htmlspecialchars($_POST['tempo_oficina_min'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="tempo_oficina_max" class="form-label">Tempo Oficina Máx (horas)</label>
                                    <input type="number" step="0.1" class="form-control" id="tempo_oficina_max" name="tempo_oficina_max" value="<?= htmlspecialchars($_POST['tempo_oficina_max'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="filtros-datas-pane" role="tabpanel" aria-labelledby="filtros-datas-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-12 mt-4">
                                    <h4 class="mb-3">Data de Entrada</h4>
                                    <hr>
                                </div>
                                <div class="col-md-6">
                                    <label for="data_entrada_min" class="form-label">Data Entrada (a partir de)</label>
                                    <input type="date" class="form-control" id="data_entrada_min" name="data_entrada_min" value="<?= htmlspecialchars($_POST['data_entrada_min'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="data_entrada_max" class="form-label">Data Entrada (até)</label>
                                    <input type="date" class="form-control" id="data_entrada_max" name="data_entrada_max" value="<?= htmlspecialchars($_POST['data_entrada_max'] ?? '') ?>">
                                </div>

                                <div class="col-12 mt-4">
                                    <h4 class="mb-3">Data de Saída</h4>
                                    <hr>
                                </div>
                                <div class="col-md-6">
                                    <label for="data_saida_min" class="form-label">Data Saída (a partir de)</label>
                                    <input type="date" class="form-control" id="data_saida_min" name="data_saida_min" value="<?= htmlspecialchars($_POST['data_saida_min'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="data_saida_max" class="form-label">Data Saída (até)</label>
                                    <input type="date" class="form-control" id="data_saida_max" name="data_saida_max" value="<?= htmlspecialchars($_POST['data_saida_max'] ?? '') ?>">
                                </div>

                                <div class="col-12 mt-4">
                                    <h4 class="mb-3">Data de Retorno à Operação</h4>
                                    <hr>
                                </div>
                                <div class="col-md-6">
                                    <label for="data_retorno_operacao_min" class="form-label">Retorno Operação (a partir de)</label>
                                    <input type="date" class="form-control" id="data_retorno_operacao_min" name="data_retorno_operacao_min" value="<?= htmlspecialchars($_POST['data_retorno_operacao_min'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="data_retorno_operacao_max" class="form-label">Retorno Operação (até)</label>
                                    <input type="date" class="form-control" id="data_retorno_operacao_max" name="data_retorno_operacao_max" value="<?= htmlspecialchars($_POST['data_retorno_operacao_max'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                    </div>
                    <hr class="my-4">
                    <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search me-2"></i>Pesquisar Manutenções</button>
                    <a href="pesquisar_manutencoes.php" class="btn btn-outline-secondary ms-2"><i class="fas fa-redo me-2"></i>Limpar Filtros</a>
                </form>

            </div>
        </div>
    </div>
</div>

<div class="b-example-divider b-example-vr"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
    // Inicializa as tabs do Bootstrap
    var triggerTabList = [].slice.call(document.querySelectorAll('#manutencaoSearchTab button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)

        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    })

    // Função para formatar números com casas decimais (para Tempo Oficina)
    document.addEventListener('DOMContentLoaded', function() {
        const tempoOficinaMinInput = document.getElementById('tempo_oficina_min');
        const tempoOficinaMaxInput = document.getElementById('tempo_oficina_max');

        function formatDecimal(input) {
            let value = input.value.replace(',', '.'); // Troca vírgula por ponto
            if (value.length === 0) {
                input.value = '';
                return;
            }
            // Garante que é um número e formata com uma casa decimal, se necessário
            value = parseFloat(value).toFixed(1);
            input.value = value.replace('.', ','); // Volta para vírgula para exibição
        }

        if (tempoOficinaMinInput) {
            tempoOficinaMinInput.addEventListener('blur', function(e) {
                formatDecimal(e.target);
            });
            tempoOficinaMinInput.addEventListener('focus', function(e) {
                e.target.value = e.target.value.replace(',', '.'); // Para edição
            });
        }
        if (tempoOficinaMaxInput) {
            tempoOficinaMaxInput.addEventListener('blur', function(e) {
                formatDecimal(e.target);
            });
            tempoOficinaMaxInput.addEventListener('focus', function(e) {
                e.target.value = e.target.value.replace(',', '.'); // Para edição
            });
        }
    });
</script>

<?php

include_once '../templates/footer.php';

?>