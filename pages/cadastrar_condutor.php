<?php

include_once '../templates/sidebar.php';

?>

<div class="d-flex min-vh-100 w-100" style="padding-bottom: 3rem;">
    <div class="row justify-content-center w-100" style="padding-top: 3rem;">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="content-area">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <h1 class="mb-3">Cadastro de Condutor</h1>
                        <p class="lead">Preencha os campos abaixo para registrar um novo condutor no sistema.</p>
                        <p>As informações estão organizadas em diferentes abas para facilitar o preenchimento.</p>
                        <hr class="mb-4">
                    </div>
                </div>

                <form action="#" method="POST">
                    <ul class="nav nav-tabs mb-4" id="condutorTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="dados-pessoais-tab" data-bs-toggle="tab" data-bs-target="#dados-pessoais-pane" type="button" role="tab" aria-controls="dados-pessoais-pane" aria-selected="true">Dados Pessoais</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="cnh-tab" data-bs-toggle="tab" data-bs-target="#cnh-pane" type="button" role="tab" aria-controls="cnh-pane" aria-selected="false">CNH</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="outras-info-tab" data-bs-toggle="tab" data-bs-target="#outras-info-pane" type="button" role="tab" aria-controls="outras-info-pane" aria-selected="false">Outras Informações</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="condutorTabContent">

                        <div class="tab-pane fade show active" id="dados-pessoais-pane" role="tabpanel" aria-labelledby="dados-pessoais-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="nome" class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control" id="nome" name="nome" maxlength="100" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="cpf" class="form-label">CPF</label>
                                    <input type="text" class="form-control" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                                    <input type="date" class="form-control" id="data_nascimento" name="data_nascimento">
                                </div>
                                <div class="col-md-6">
                                    <label for="genero" class="form-label">Gênero</label>
                                    <select class="form-select" id="genero" name="genero">
                                        <option value="" disabled selected>Selecione...</option>
                                        <?php
                                        $generos = ['M' => 'Masculino', 'F' => 'Feminino', 'Outro' => 'Outro'];
                                        foreach ($generos as $value => $label) {
                                            echo "<option value=\"$value\">$label</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="cnh-pane" role="tabpanel" aria-labelledby="cnh-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="numero_cnh" class="form-label">Número da CNH</label>
                                    <input type="text" class="form-control" id="numero_cnh" name="numero_cnh" maxlength="20" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="categoria_cnh" class="form-label">Categoria CNH</label>
                                    <input type="text" class="form-control" id="categoria_cnh" name="categoria_cnh" maxlength="10" placeholder="Ex: AB, C, D" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="data_validade_cnh" class="form-label">Data de Validade CNH</label>
                                    <input type="date" class="form-control" id="data_validade_cnh" name="data_validade_cnh" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="data_primeira_habilitacao" class="form-label">Data da 1ª Habilitação</label>
                                    <input type="date" class="form-control" id="data_primeira_habilitacao" name="data_primeira_habilitacao">
                                </div>
                                <div class="col-md-6">
                                    <label for="registro_cnh" class="form-label">Registro CNH</label>
                                    <input type="text" class="form-control" id="registro_cnh" name="registro_cnh" maxlength="20">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" value="1" id="ear_observacao" name="ear_observacao">
                                        <label class="form-check-label" for="ear_observacao">
                                            Possui EAR (Exerce Atividade Remunerada)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="outras-info-pane" role="tabpanel" aria-labelledby="outras-info-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="identidade_militar" class="form-label">Identidade Militar</label>
                                    <input type="text" class="form-control" id="identidade_militar" name="identidade_militar" maxlength="50">
                                </div>
                                <div class="col-md-6">
                                    <label for="posto_graduacao" class="form-label">Posto/Graduação</label>
                                    <select class="form-select" id="posto_graduacao" name="posto_graduacao">
                                        <option value="" disabled selected>Selecione...</option>
                                        <?php
                                        // Simulação de postos/graduações
                                        $postosGraduacoes = ['Soldado', 'Cabo', 'Sargento', 'Tenente', 'Capitão', 'Major', 'Tenente-Coronel', 'Coronel'];
                                        foreach ($postosGraduacoes as $posto) {
                                            echo "<option value=\"$posto\">$posto</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="unidade_id" class="form-label">Lotação (Unidade)</label>
                                    <select class="form-select" id="unidade_id" name="unidade_id">
                                        <option value="" disabled selected>Selecione a Unidade</option>
                                        <?php
                                        // Simulação de dados de unidades para o exemplo:
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
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status do Condutor</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="" disabled selected>Selecione...</option>
                                        <?php
                                        $statusCondutor = ['Ativo', 'Inativo', 'Afastado'];
                                        foreach ($statusCondutor as $status) {
                                            echo "<option value=\"$status\">$status</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                    <hr class="my-4">
                    <button class="btn btn-outline-success btn-lg" type="submit"><i class="fas fa-save me-2"></i>Cadastrar Condutor</button>
                    <button class="btn btn-outline-secondary btn-lg ms-2" type="reset"><i class="fas fa-eraser me-2"></i>Limpar Campos</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="b-example-divider b-example-vr"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous"></script>
<script>
    // Inicializa as tabs do Bootstrap
    var triggerTabList = [].slice.call(document.querySelectorAll('#condutorTab button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)

        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    })

    // Adicionar formatação para CPF (000.000.000-00)
    document.addEventListener('DOMContentLoaded', function() {
        const cpfInput = document.getElementById('cpf');
        if (cpfInput) {
            cpfInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
                if (value.length > 9) {
                    value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                } else if (value.length > 6) {
                    value = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3');
                } else if (value.length > 3) {
                    value = value.replace(/(\d{3})(\d{3})/, '$1.$2');
                }
                e.target.value = value;
            });
        }
    });
</script>

<?php

include_once '../templates/footer.php';

?>