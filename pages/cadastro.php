<?php

include_once '../templates/sidebar.php';

?>

<!-- <div class="d-flex align-items-center min-vh-100 w-100"> -->
<div class="d-flex min-vh-100 w-100" style="padding-bottom: 3rem;">
    <div class="row justify-content-center w-100" style="padding-top: 3rem;">
        <div class=" col-12 col-md-8 col-lg-6">
            <div class="content-area">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <h1 class="mb-3">Cadastro de Viatura</h1>
                        <p class="lead">Preencha os campos abaixo para registrar uma nova viatura no sistema.</p>
                        <p>As informações estão organizadas em diferentes abas para facilitar o preenchimento.</p>
                        <hr class="mb-4">
                    </div>
                </div>

                <form action="#" method="POST">
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
                                    <select class="form-select" id="tipo_operacional" name="tipo_operacional">
                                        <option value="" disabled selected>Selecione...</option>
                                        <?php
                                        $tiposOperacionais = ['Operacional General', 'Combate a incendio', 'Administrativa'];
                                        foreach ($tiposOperacionais as $tipo) {
                                            echo "<option value=\"$tipo\">$tipo</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="prefixo" class="form-label">Prefixo</label>
                                    <input type="text" class="form-control" id="prefixo" name="prefixo" maxlength="5">
                                </div>
                                <div class="col-md-3">
                                    <label for="numero" class="form-label">Número</label>
                                    <input type="number" class="form-control" id="numero" name="numero">
                                </div>
                                <div class="col-md-6">
                                    <label for="placa" class="form-label">Placa</label>
                                    <input type="text" class="form-control" id="placa" name="placa" maxlength="7" placeholder="Ex: ABC1234">
                                </div>
                                <div class="col-md-6">
                                    <label for="renavam" class="form-label">Renavam</label>
                                    <input type="text" class="form-control" id="renavam" name="renavam" maxlength="11" placeholder="Somente números">
                                </div>
                                <div class="col-md-6">
                                    <label for="marca" class="form-label">Marca</label>
                                    <input type="text" class="form-control" id="marca" name="marca" maxlength="50">
                                </div>
                                <div class="col-md-6">
                                    <label for="modelo" class="form-label">Modelo</label>
                                    <input type="text" class="form-control" id="modelo" name="modelo" maxlength="50">
                                </div>
                                <div class="col-md-3">
                                    <label for="ano" class="form-label">Ano</label>
                                    <input type="number" class="form-control" id="ano" name="ano" min="1900" max="<?= date('Y') + 1 ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="pbt" class="form-label">PBT (kg)</label>
                                    <input type="number" class="form-control" id="pbt" name="pbt">
                                </div>
                                <div class="col-md-6">
                                    <label for="tipo_aquisicao" class="form-label">Tipo de Aquisição</label>
                                    <select class="form-select" id="tipo_aquisicao" name="tipo_aquisicao">
                                        <option value="" disabled selected>Selecione...</option>
                                        <?php
                                        $tiposAquisicao = ['Própria', 'Doação', 'Fiel Depositário', 'Locada'];
                                        foreach ($tiposAquisicao as $tipo) {
                                            echo "<option value=\"$tipo\">$tipo</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="status_da_viatura" class="form-label">Status da Viatura</label>
                                    <select class="form-select" id="status_da_viatura" name="status_da_viatura">
                                        <option value="" disabled selected>Selecione...</option>
                                        <?php
                                        $statusViatura = ['Operante', 'Baixada', 'Descarregada', 'Indisponível'];
                                        foreach ($statusViatura as $status) {
                                            echo "<option value=\"$status\">$status</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                 <div class="col-md-6">
                                    <label for="lotacao_atual_id" class="form-label">Lotação Atual</label>
                                    <select class="form-select" id="lotacao_atual_id" name="lotacao_atual_id">
                                        <option value="" disabled selected>Selecione a Unidade</option>
                                        <?php
                                        // Exemplo de como buscar unidades do banco de dados (substitua com sua lógica real)
                                        // include_once 'db.php'; // Inclua sua conexão PDO
                                        // $stmt = $pdo->query('SELECT id, nome FROM unidades ORDER BY nome');
                                        // while ($unidade = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        //     echo "<option value=\"{$unidade['id']}\">{$unidade['nome']}</option>";
                                        // }
                                        // Simulando dados de unidades para o exemplo:
                                        $unidadesExemplo = [
                                            ['id' => 1, 'nome' => '1ª CIBM'],
                                            ['id' => 2, 'nome' => '2ª CIBM'],
                                            ['id' => 3, 'nome' => 'QCG'],
                                        ];
                                        foreach ($unidadesExemplo as $unidade) {
                                            echo "<option value=\"{$unidade['id']}\">{$unidade['nome']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-12 mt-4">
                                    <h4>Características Adicionais</h4>
                                    <hr>
                                </div>
                                <div class="col-md-6">
                                    <label for="tipo_carroceria" class="form-label">Tipo de Carroceria</label>
                                    <input type="text" class="form-control" id="tipo_carroceria" name="tipo_carroceria" maxlength="100">
                                </div>
                                <div class="col-md-6">
                                    <label for="equipamentos_especificos" class="form-label">Equipamentos Específicos</label>
                                    <textarea class="form-control" id="equipamentos_especificos" name="equipamentos_especificos" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="motorizacao-pane" role="tabpanel" aria-labelledby="motorizacao-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="cilindradas" class="form-label">Cilindradas (cc)</label>
                                    <input type="number" class="form-control" id="cilindradas" name="cilindradas">
                                </div>
                                <div class="col-md-6">
                                    <label for="cavalos_forca" class="form-label">Cavalos de Força (cv)</label>
                                    <input type="number" class="form-control" id="cavalos_forca" name="cavalos_forca">
                                </div>
                                <div class="col-md-6">
                                    <label for="torque" class="form-label">Torque</label>
                                    <input type="text" class="form-control" id="torque" name="torque" maxlength="50" placeholder="Ex: 250 Nm">
                                </div>
                                <div class="col-md-6">
                                    <label for="tipo_cambio" class="form-label">Tipo de Câmbio</label>
                                    <select class="form-select" id="tipo_cambio" name="tipo_cambio">
                                        <option value="" disabled selected>Selecione...</option>
                                        <?php
                                        $tiposCambio = ['Manual', 'Automático'];
                                        foreach ($tiposCambio as $tipo) {
                                            echo "<option value=\"$tipo\">$tipo</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="numero_marchas" class="form-label">Número de Marchas</label>
                                    <input type="number" class="form-control" id="numero_marchas" name="numero_marchas" min="1">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="diversos-pane" role="tabpanel" aria-labelledby="diversos-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="quilometragem" class="form-label">Quilometragem (km)</label>
                                    <input type="number" class="form-control" id="quilometragem" name="quilometragem">
                                </div>
                                <div class="col-md-6">
                                    <label for="garantia_fim" class="form-label">Fim da Garantia</label>
                                    <input type="date" class="form-control" id="garantia_fim" name="garantia_fim">
                                </div>
                            </div>
                        </div>

                    </div>
                    <hr class="my-4">
                    <button class="btn btn-outline-success btn-lg" type="submit">Cadastrar Viatura</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="b-example-divider b-example-vr"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous"></script>
<script>
    // Inicializa as tabs do Bootstrap
    var triggerTabList = [].slice.call(document.querySelectorAll('#viaturaTab button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)

        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    })

    // Opcional: Adicionar formatação para placa e renavam
    document.addEventListener('DOMContentLoaded', function() {
        const placaInput = document.getElementById('placa');
        if (placaInput) {
            placaInput.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                if (value.length > 3) {
                    value = value.substring(0, 3) + '-' + value.substring(3);
                }
                e.target.value = value.substring(0, 8); // Limita ao formato AAA-0000
            });
        }

        const renavamInput = document.getElementById('renavam');
        if (renavamInput) {
            renavamInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^0-9]/g, '').substring(0, 11);
            });
        }
    });
</script>

<?php

include_once '../templates/footer.php';

?>