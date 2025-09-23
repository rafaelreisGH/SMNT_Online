<?php

include_once '../templates/sidebar.php';

/*
// Inclua seu arquivo de conexão com o banco de dados
include_once '../config/database.php'; // Ajuste o caminho conforme necessário

// Lógica de pesquisa para condutores
$results = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coleta dos filtros do formulário
    $nome = $_POST['nome'] ?? '';
    $cpf = $_POST['cpf'] ?? '';
    $numero_cnh = $_POST['numero_cnh'] ?? '';
    $categoria_cnh = $_POST['categoria_cnh'] ?? '';
    $data_validade_cnh = $_POST['data_validade_cnh'] ?? '';
    $posto_graduacao = $_POST['posto_graduacao'] ?? '';
    $unidade_id = $_POST['unidade_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $ear_observacao = $_POST['ear_observacao'] ?? ''; // Pode ser '1', '0' ou vazio

    // Constrói a consulta SQL dinamicamente
    $sql = "SELECT
                c.id,
                c.nome,
                c.cpf,
                c.data_nascimento,
                c.genero,
                c.numero_cnh,
                c.categoria_cnh,
                c.data_validade_cnh,
                c.data_primeira_habilitacao,
                c.registro_cnh,
                c.ear_observacao,
                c.identidade_militar,
                c.posto_graduacao,
                u.nome AS unidade_lotacao,
                c.data_cadastro,
                c.status
            FROM
                controle_viaturas.condutores c
            LEFT JOIN
                controle_viaturas.unidades u ON c.unidade_id = u.id
            WHERE 1=1"; // 'WHERE 1=1' facilita a adição de cláusulas AND

    $params = [];

    if (!empty($nome)) {
        $sql .= " AND c.nome LIKE :nome";
        $params[':nome'] = '%' . $nome . '%';
    }
    if (!empty($cpf)) {
        $sql .= " AND c.cpf LIKE :cpf";
        $params[':cpf'] = '%' . str_replace(['.', '-'], '', $cpf) . '%'; // Remove pontos e traços do CPF
    }
    if (!empty($numero_cnh)) {
        $sql .= " AND c.numero_cnh LIKE :numero_cnh";
        $params[':numero_cnh'] = '%' . $numero_cnh . '%';
    }
    if (!empty($categoria_cnh)) {
        $sql .= " AND c.categoria_cnh = :categoria_cnh";
        $params[':categoria_cnh'] = $categoria_cnh;
    }
    if (!empty($data_validade_cnh)) {
        $sql .= " AND c.data_validade_cnh = :data_validade_cnh";
        $params[':data_validade_cnh'] = $data_validade_cnh;
    }
    if (!empty($posto_graduacao)) {
        $sql .= " AND c.posto_graduacao LIKE :posto_graduacao";
        $params[':posto_graduacao'] = '%' . $posto_graduacao . '%';
    }
    if (!empty($unidade_id)) {
        $sql .= " AND c.unidade_id = :unidade_id";
        $params[':unidade_id'] = $unidade_id;
    }
    if (!empty($status)) {
        $sql .= " AND c.status = :status";
        $params[':status'] = $status;
    }
    if (!empty($genero)) {
        $sql .= " AND c.genero = :genero";
        $params[':genero'] = $genero;
    }
    // Para 'ear_observacao', precisamos verificar '0', '1' ou vazio (null)
    if ($ear_observacao !== '') { // Verifica se foi selecionado algo
        $sql .= " AND c.ear_observacao = :ear_observacao";
        $params[':ear_observacao'] = (int)$ear_observacao; // Converte para inteiro (0 ou 1)
    }

    $sql .= " ORDER BY c.nome ASC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Em um ambiente de produção, logue o erro e mostre uma mensagem genérica
        echo "<div class='alert alert-danger'>Erro ao consultar o banco de dados: " . $e->getMessage() . "</div>";
    }
}

// Para popular os selects de Unidades
// Requer conexão PDO ativa ($pdo)
$unidades = [];

try {
    $stmtUnidades = $pdo->query('SELECT id, nome FROM controle_viaturas.unidades ORDER BY nome');
    $unidades = $stmtUnidades->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar dados para os filtros: " . $e->getMessage() . "</div>";
}
*/
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h4 mb-4 text-gray-800">Pesquisa de condutor</h1>

    <div class="row">

        <div class="col-lg-10 mx-auto">

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="ms-3 font-weight-bold text-primary">Formulário de pesquisa</h6>
                </div>
                <div class="card-body mx-3 my-3">

                    <form action="#" method="POST">
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
                        <a href="pesquisar_condutores.php" class="btn btn-outline-secondary ms-2"><i class="fas fa-redo me-2"></i>Limpar Filtros</a>
                    </form>

                    <?php
                    // Comentei a exibição de resultados, pois o PHP de pesquisa está comentado.
                    // Você pode descomentar esta parte quando o backend estiver ativo e $results for populado.
                    /*
                if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($results)): ?>
                    <div class="alert alert-warning mt-4" role="alert">
                        Nenhum condutor encontrado com os filtros especificados.
                    </div>
                <?php elseif (!empty($results)): ?>
                    <h2 class="mt-5 mb-3">Resultados da Pesquisa</h2>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>CPF</th>
                                    <th>CNH</th>
                                    <th>Cat. CNH</th>
                                    <th>Validade CNH</th>
                                    <th>Posto/Graduação</th>
                                    <th>Unidade Lotação</th>
                                    <th>Status</th>
                                    <th>EAR</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $condutor): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($condutor['id']) ?></td>
                                        <td><?= htmlspecialchars($condutor['nome']) ?></td>
                                        <td><?= htmlspecialchars($condutor['cpf']) ?></td>
                                        <td><?= htmlspecialchars($condutor['numero_cnh']) ?></td>
                                        <td><?= htmlspecialchars($condutor['categoria_cnh']) ?></td>
                                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($condutor['data_validade_cnh']))) ?></td>
                                        <td><?= htmlspecialchars($condutor['posto_graduacao'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($condutor['unidade_lotacao'] ?? 'Não informada') ?></td>
                                        <td><?= htmlspecialchars($condutor['status']) ?></td>
                                        <td><?= $condutor['ear_observacao'] ? 'Sim' : 'Não' ?></td>
                                        <td>
                                            <a href="detalhes_condutor.php?id=<?= $condutor['id'] ?>" class="btn btn-sm btn-info" title="Ver Detalhes"><i class="fas fa-eye"></i></a>
                                            <a href="editar_condutor.php?id=<?= $condutor['id'] ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                            <a href="excluir_condutor.php?id=<?= $condutor['id'] ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este condutor?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif;
                */
                    ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        // Inicializa as tabs do Bootstrap
        var triggerTabList = [].slice.call(document.querySelectorAll('#condutorSearchTab button'))
        triggerTabList.forEach(function(triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl)

            triggerEl.addEventListener('click', function(event) {
                event.preventDefault()
                tabTrigger.show()
            })
        })

        // Opcional: Adicionar formatação para CPF
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