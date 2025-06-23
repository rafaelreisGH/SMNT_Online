<?php

include_once '../templates/sidebar.php';

/*
// Inclua seu arquivo de conexão com o banco de dados
include_once '../config/database.php'; // Ajuste o caminho conforme necessário

// Lógica de pesquisa para manutenções
$results = [];
// Arrays para popular os selects
$viaturas = [];
$avarias = []; // Novo array para carregar os tipos de avarias


// Popula os selects (isso seria feito antes da lógica de pesquisa principal)
try {
    // Carregar viaturas (ID e Placa/Prefixo/Número para facilitar a identificação)
    $stmtViaturas = $pdo->query('SELECT id, placa, prefixo, numero FROM controle_viaturas.viaturas ORDER BY placa ASC, prefixo ASC, numero ASC');
    $viaturas = $stmtViaturas->fetchAll(PDO::FETCH_ASSOC);

    // Carregar avarias
    $stmtAvarias = $pdo->query('SELECT id, tipo_avaria, descricao FROM controle_viaturas.avarias ORDER BY tipo_avaria ASC, descricao ASC');
    $avarias = $stmtAvarias->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar dados para os filtros: " . $e->getMessage() . "</div>";
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coleta dos filtros do formulário
    $viatura_id = $_POST['viatura_id'] ?? '';
    $avaria_id = $_POST['avaria_id'] ?? ''; // Novo filtro
    $data_entrada_min = $_POST['data_entrada_min'] ?? ''; // Renomeado
    $data_entrada_max = $_POST['data_entrada_max'] ?? ''; // Renomeado
    $data_saida_min = $_POST['data_saida_min'] ?? '';
    $data_saida_max = $_POST['data_saida_max'] ?? '';
    $data_retorno_operacao_min = $_POST['data_retorno_operacao_min'] ?? '';
    $data_retorno_operacao_max = $_POST['data_retorno_operacao_max'] ?? '';
    $descricao_servico = $_POST['descricao_servico'] ?? '';
    $tempo_oficina_min = $_POST['tempo_oficina_min'] ?? '';
    $tempo_oficina_max = $_POST['tempo_oficina_max'] ?? '';


    // Constrói a consulta SQL dinamicamente
    $sql = "SELECT
                m.id,
                m.viatura_id,
                v.placa AS viatura_placa,
                v.prefixo AS viatura_prefixo,
                v.numero AS viatura_numero,
                m.avaria_id,
                a.tipo_avaria AS avaria_tipo,
                a.descricao AS avaria_descricao,
                m.data_entrada,
                m.data_saida,
                m.data_retorno_operacao,
                m.descricao_servico,
                m.tempo_oficina
            FROM
                controle_viaturas.manutencoes m
            LEFT JOIN
                controle_viaturas.viaturas v ON m.viatura_id = v.id
            LEFT JOIN
                controle_viaturas.avarias a ON m.avaria_id = a.id
            WHERE 1=1"; // 'WHERE 1=1' facilita a adição de cláusulas AND

    $params = [];

    if (!empty($viatura_id)) {
        $sql .= " AND m.viatura_id = :viatura_id";
        $params[':viatura_id'] = $viatura_id;
    }
    if (!empty($avaria_id)) {
        $sql .= " AND m.avaria_id = :avaria_id";
        $params[':avaria_id'] = $avaria_id;
    }
    if (!empty($data_entrada_min)) {
        $sql .= " AND m.data_entrada >= :data_entrada_min";
        $params[':data_entrada_min'] = $data_entrada_min;
    }
    if (!empty($data_entrada_max)) {
        $sql .= " AND m.data_entrada <= :data_entrada_max";
        $params[':data_entrada_max'] = $data_entrada_max;
    }
    if (!empty($data_saida_min)) {
        $sql .= " AND m.data_saida >= :data_saida_min";
        $params[':data_saida_min'] = $data_saida_min;
    }
    if (!empty($data_saida_max)) {
        $sql .= " AND m.data_saida <= :data_saida_max";
        $params[':data_saida_max'] = $data_saida_max;
    }
    if (!empty($data_retorno_operacao_min)) {
        $sql .= " AND m.data_retorno_operacao >= :data_retorno_operacao_min";
        $params[':data_retorno_operacao_min'] = $data_retorno_operacao_min;
    }
    if (!empty($data_retorno_operacao_max)) {
        $sql .= " AND m.data_retorno_operacao <= :data_retorno_operacao_max";
        $params[':data_retorno_operacao_max'] = $data_retorno_operacao_max;
    }
    if (!empty($descricao_servico)) {
        $sql .= " AND m.descricao_servico LIKE :descricao_servico";
        $params[':descricao_servico'] = '%' . $descricao_servico . '%';
    }
    if (!empty($tempo_oficina_min)) {
        $sql .= " AND m.tempo_oficina >= :tempo_oficina_min";
        $params[':tempo_oficina_min'] = (float)str_replace(',', '.', $tempo_oficina_min);
    }
    if (!empty($tempo_oficina_max)) {
        $sql .= " AND m.tempo_oficina <= :tempo_oficina_max";
        $params[':tempo_oficina_max'] = (float)str_replace(',', '.', $tempo_oficina_max);
    }


    $sql .= " ORDER BY m.data_entrada DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Em um ambiente de produção, logue o erro e mostre uma mensagem genérica
        echo "<div class='alert alert-danger'>Erro ao consultar o banco de dados: " . $e->getMessage() . "</div>";
    }
}
*/
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
                    <button class="btn btn-outline-primary btn-lg" type="submit"><i class="fas fa-search me-2"></i>Pesquisar Manutenções</button>
                    <a href="pesquisar_manutencoes.php" class="btn btn-outline-secondary btn-lg ms-2"><i class="fas fa-redo me-2"></i>Limpar Filtros</a>
                </form>

                <?php
                // Comentei a exibição de resultados, pois o PHP de pesquisa está comentado.
                // Você pode descomentar esta parte quando o backend estiver ativo e $results for populado.
                /*
                if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($results)): ?>
                    <div class="alert alert-warning mt-4" role="alert">
                        Nenhuma manutenção encontrada com os filtros especificados.
                    </div>
                <?php elseif (!empty($results)): ?>
                    <h2 class="mt-5 mb-3">Resultados da Pesquisa</h2>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Viatura</th>
                                    <th>Avaria</th>
                                    <th>Data Entrada</th>
                                    <th>Data Saída</th>
                                    <th>Retorno Operação</th>
                                    <th>Descrição Serviço</th>
                                    <th>Tempo Oficina (h)</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $manutencao): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($manutencao['id']) ?></td>
                                        <td><?= htmlspecialchars($manutencao['viatura_placa'] ?: ($manutencao['viatura_prefixo'] . ' - ' . $manutencao['viatura_numero'])) ?></td>
                                        <td><?= htmlspecialchars($manutencao['avaria_tipo'] . (empty($manutencao['avaria_descricao']) ? '' : ' - ' . $manutencao['avaria_descricao'])) ?></td>
                                        <td><?= htmlspecialchars($manutencao['data_entrada'] ? date('d/m/Y H:i', strtotime($manutencao['data_entrada'])) : 'N/A') ?></td>
                                        <td><?= htmlspecialchars($manutencao['data_saida'] ? date('d/m/Y H:i', strtotime($manutencao['data_saida'])) : 'N/A') ?></td>
                                        <td><?= htmlspecialchars($manutencao['data_retorno_operacao'] ? date('d/m/Y H:i', strtotime($manutencao['data_retorno_operacao'])) : 'N/A') ?></td>
                                        <td><?= htmlspecialchars($manutencao['descricao_servico']) ?></td>
                                        <td><?= htmlspecialchars(number_format($manutencao['tempo_oficina'], 1, ',', '.')) ?></td>
                                        <td>
                                            <a href="detalhes_manutencao.php?id=<?= $manutencao['id'] ?>" class="btn btn-sm btn-info" title="Ver Detalhes"><i class="fas fa-eye"></i></a>
                                            <a href="editar_manutencao.php?id=<?= $manutencao['id'] ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                            <a href="excluir_manutencao.php?id=<?= $manutencao['id'] ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta manutenção?')"><i class="fas fa-trash"></i></a>
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