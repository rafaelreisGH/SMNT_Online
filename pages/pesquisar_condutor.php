    <?php
    // pages/pesquisar_condutor.php
    require_once '../components/DAO/CondutorDAO.php'; // Usa o DAO

    $results = [];
    // Este bloco só será executado quando o JavaScript fizer uma requisição POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            $condutorDAO = new CondutorDAO();
            // Passa todos os dados do POST para o método de busca
            $results = $condutorDAO->buscarCondutores($_POST);
        } catch (Exception $e) {
            // Em caso de erro, podemos retornar um erro que o JS possa tratar
            // (neste caso, simplesmente não haverá resultados)
            error_log($e->getMessage());
        }
    }

    // Lógica para popular os selects (ex: unidades) pode ser colocada aqui se necessário
    // $unidades = ...
    ?>

    <!-- Begin Page Content -->
    <div class="container-fluid">
        <h1 class="h4 mb-4 text-gray-800">Pesquisa de condutor</h1>
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <!-- Formulário de Pesquisa -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="ms-3 font-weight-bold text-primary">Formulário de pesquisa</h6>
                    </div>
                    <div class="card-body mx-3 my-3">

                        <form id="formPesquisaCondutor" action="#" method="POST">
                            <ul class="nav nav-tabs mb-4" id="condutorSearchTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="filtros-pessoais-tab" data-bs-toggle="tab" data-bs-target="#filtros-pessoais-pane" type="button" role="tab" aria-controls="filtros-pessoais-pane" aria-selected="true">Dados Pessoais</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="filtros-cnh-tab" data-bs-toggle="tab" data-bs-target="#filtros-cnh-pane" type="button" role="tab" aria-controls="filtros-cnh-pane" aria-selected="false">Dados CNH</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="filtros-militares-tab" data-bs-toggle="tab" data-bs-target="#filtros-militares-pane" type="button" role="tab" aria-controls="filtros-militares-pane" aria-selected="false">Dados Militares</button>
                                </li>
                            </ul>

                            <div class="tab-content" id="condutorSearchTabContent">

                                <div class="tab-pane fade show active" id="filtros-pessoais-pane" role="tabpanel" aria-labelledby="filtros-pessoais-tab" tabindex="0">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="nome" class="form-label">Nome</label>
                                            <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="cpf" class="form-label">CPF</label>
                                            <input type="text" class="form-control" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="genero" class="form-label">Gênero</label>
                                            <select class="form-select" id="genero" name="genero">
                                                <option value="">Todos</option>
                                                <?php
                                                $generos = ['M', 'F', 'Outro'];
                                                foreach ($generos as $g) {
                                                    $selected = (isset($_POST['genero']) && $_POST['genero'] == $g) ? 'selected' : '';
                                                    echo "<option value=\"$g\" $selected>" . ($g == 'M' ? 'Masculino' : ($g == 'F' ? 'Feminino' : 'Outro')) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="">Todos</option>
                                                <?php
                                                $statusCondutor = ['Ativo', 'Inativo', 'Afastado'];
                                                foreach ($statusCondutor as $s) {
                                                    $selected = (isset($_POST['status']) && $_POST['status'] == $s) ? 'selected' : '';
                                                    echo "<option value=\"$s\" $selected>$s</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="filtros-cnh-pane" role="tabpanel" aria-labelledby="filtros-cnh-tab" tabindex="0">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="numero_cnh" class="form-label">Número CNH</label>
                                            <input type="text" class="form-control" id="numero_cnh" name="numero_cnh" maxlength="20" value="<?= htmlspecialchars($_POST['numero_cnh'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="categoria_cnh" class="form-label">Categoria CNH</label>
                                            <input type="text" class="form-control" id="categoria_cnh" name="categoria_cnh" maxlength="10" placeholder="Ex: AB, C, D" value="<?= htmlspecialchars($_POST['categoria_cnh'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="data_validade_cnh" class="form-label">Validade CNH</label>
                                            <input type="date" class="form-control" id="data_validade_cnh" name="data_validade_cnh" value="<?= htmlspecialchars($_POST['data_validade_cnh'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="ear_observacao" class="form-label">EAR (Exerce Atividade Remunerada)</label>
                                            <select class="form-select" id="ear_observacao" name="ear_observacao">
                                                <option value="">Todos</option>
                                                <option value="1" <?= (isset($_POST['ear_observacao']) && $_POST['ear_observacao'] === '1') ? 'selected' : '' ?>>Sim</option>
                                                <option value="0" <?= (isset($_POST['ear_observacao']) && $_POST['ear_observacao'] === '0') ? 'selected' : '' ?>>Não</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="filtros-militares-pane" role="tabpanel" aria-labelledby="filtros-militares-tab" tabindex="0">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="posto_graduacao" class="form-label">Posto/Graduação</label>
                                            <input type="text" class="form-control" id="posto_graduacao" name="posto_graduacao" maxlength="50" value="<?= htmlspecialchars($_POST['posto_graduacao'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="unidade_id" class="form-label">Unidade de Lotação</label>
                                            <select class="form-select" id="unidade_id" name="unidade_id">
                                                <option value="">Todas as Unidades</option>
                                                <?php
                                                /*
                                        // A variável $unidades viria do PHP comentado acima
                                        foreach ($unidades as $unidade) {
                                            $selected = (isset($_POST['unidade_id']) && $_POST['unidade_id'] == $unidade['id']) ? 'selected' : '';
                                            echo "<option value=\"{$unidade['id']}\" $selected>{$unidade['nome']}</option>";
                                        }
                                        */
                                                // Para demonstração sem backend, adicione algumas opções de exemplo:
                                                echo "<option value='1'>1º Batalhão</option>";
                                                echo "<option value='2'>2º Companhia</option>";
                                                echo "<option value='3'>Quartel General</option>";
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <hr class="my-4">
                            <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search me-2"></i>Pesquisar Condutor</button>
                            <button type="button" class="btn btn-outline-secondary ms-2" id="btnLimparFiltrosCondutor"><i class="fas fa-redo me-2"></i>Limpar Filtros</button>
                        </form>
                    </div>
                </div>

                <!-- Área de Resultados -->
                <div id="resultado-pesquisa-condutor">
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): // Se foi uma requisição, mostra a seção de resultados 
                    ?>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Resultado</h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($results)): ?>
                                    <div class="alert alert-warning" role="alert">
                                        Nenhum condutor encontrado com os filtros especificados.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nome</th>
                                                    <th>CPF</th>
                                                    <th>CNH</th>
                                                    <th>Status</th>
                                                    <th>Unidade</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($results as $condutor): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($condutor['nome']) ?></td>
                                                        <td><?= htmlspecialchars($condutor['cpf']) ?></td>
                                                        <td><?= htmlspecialchars($condutor['numero_cnh']) ?></td>
                                                        <td><?= htmlspecialchars(ucfirst($condutor['status'])) ?></td>
                                                        <td><?= htmlspecialchars($condutor['unidade_lotacao'] ?? 'N/A') ?></td>
                                                        <td>
                                                            <a href="pages/editar_condutor.php?id=<?= $condutor['id'] ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                                            <!-- Adicionar outros botões (detalhes, excluir) se necessário -->
                                                        </td>
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

    <script>
        function initializePesquisaCondutorScripts() {
            const form = document.getElementById('formPesquisaCondutor');
            const areaResultados = document.getElementById('resultado-pesquisa-condutor');
            const btnLimpar = document.getElementById('btnLimparFiltrosCondutor');

            if (!form || form.hasAttribute('data-ajax-initialized')) return;
            form.setAttribute('data-ajax-initialized', 'true');

            // Inicializa as tabs do Bootstrap
            const triggerTabList = Array.from(document.querySelectorAll('#condutorSearchTab button'));
            triggerTabList.forEach(triggerEl => {
                const tabTrigger = new bootstrap.Tab(triggerEl);
                triggerEl.addEventListener('click', event => {
                    event.preventDefault();
                    tabTrigger.show();
                });
            });

            // Máscara de CPF
            const cpfInput = document.getElementById('cpf');
            if (cpfInput) {
                cpfInput.addEventListener('input', e => {
                    // ... (sua lógica de máscara de CPF)
                });
            }

            // Listener para o botão de limpar
            if (btnLimpar) {
                btnLimpar.addEventListener('click', () => form.reset());
            }

            // Listener para a submissão do formulário
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(form);

                // Feedback de carregamento
                areaResultados.innerHTML = `<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i> Pesquisando...</div>`;

                fetch('pages/pesquisar_condutor.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        // Extrai apenas a div de resultados da resposta
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const novosResultados = doc.getElementById('resultado-pesquisa-condutor');

                        if (novosResultados) {
                            areaResultados.innerHTML = novosResultados.innerHTML;
                        } else {
                            areaResultados.innerHTML = '<div class="alert alert-danger">Erro ao processar a resposta da pesquisa.</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Erro na pesquisa AJAX:', error);
                        areaResultados.innerHTML = '<div class="alert alert-danger">Erro de comunicação ao realizar a pesquisa.</div>';
                    });
            });
        }

        // Executa a inicialização
        initializePesquisaCondutorScripts();
    </script>