<?php
// Conexão com o banco de dados (descomente e configure conforme necessário)
// include_once '../config/database.php';

// Lógica de pesquisa (comentada, pois o foco é o layout visual)
// $results = [];
// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     $data_inicial = $_POST['data_inicial'] ?? '';
//     $data_final = $_POST['data_final'] ?? '';
//     $viatura_id = $_POST['viatura_id'] ?? '';
//     // $observacoes = $_POST['observacoes'] ?? ''; // Removido

//     $sql = "SELECT h.id, v.placa, h.data_registro, h.quilometragem // , h.observacoes // Removido
//             FROM historico_pneus h
//             JOIN viaturas v ON h.viatura_id = v.id
//             WHERE 1=1";
//     $params = [];

//     if (!empty($data_inicial)) {
//         $sql .= " AND h.data_registro >= :data_inicial";
//         $params[':data_inicial'] = $data_inicial;
//     }
//     if (!empty($data_final)) {
//         $sql .= " AND h.data_registro <= :data_final";
//         $params[':data_final'] = $data_final;
//     }
//     if (!empty($viatura_id)) {
//         $sql .= " AND h.viatura_id = :viatura_id";
//         $params[':viatura_id'] = $viatura_id;
//     }
//     // if (!empty($observacoes)) { // Removido
//     //     $sql .= " AND h.observacoes LIKE :observacoes";
//     //     $params[':observacoes'] = '%' . $observacoes . '%';
//     // }

//     try {
//         $stmt = $pdo->prepare($sql);
//         $stmt->execute($params);
//         $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
//     } catch (PDOException $e) {
//         echo "<div class='alert alert-danger'>Erro ao consultar o banco de dados: " . $e->getMessage() . "</div>";
//     }
// }

// // Simulação de dados para viaturas (substituir por consulta real ao BD)
// $viaturas = [
//     ['id' => 1, 'placa' => 'ABC1234'],
//     ['id' => 2, 'placa' => 'DEF5678'],
//     ['id' => 3, 'placa' => 'GHI9012'],
// ];
?>


<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">Histórico de Pneus</h1>

    <div class="row">

        <div class="col-lg-10 mx-auto">

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="ms-3 font-weight-bold text-primary">Formulário de pesquisa</h6>
                </div>
                <div class="card-body mx-3 my-3">

                    <form action="#" method="POST">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="viatura_id" class="form-label">Viatura</label>
                                <select class="form-select" id="viatura_id" name="viatura_id">
                                    <option value="">Todas as Viaturas</option>
                                    <?php
                                    // Descomente e use $viaturas reais do banco de dados quando a conexão estiver ativa
                                    /*
                                foreach ($viaturas as $viatura) {
                                    $selected = (isset($_POST['viatura_id']) && $_POST['viatura_id'] == $viatura['id']) ? 'selected' : '';
                                    echo "<option value=\"{$viatura['id']}\" $selected>{$viatura['placa']}</option>";
                                }
                                */
                                    // Simulação para fins de layout
                                    $simulated_viaturas = [
                                        ['id' => 1, 'placa' => 'ABC1234'],
                                        ['id' => 2, 'placa' => 'DEF5678'],
                                        ['id' => 3, 'placa' => 'GHI9012'],
                                    ];
                                    foreach ($simulated_viaturas as $viatura) {
                                        $selected = (isset($_POST['viatura_id']) && $_POST['viatura_id'] == $viatura['id']) ? 'selected' : '';
                                        echo "<option value=\"{$viatura['id']}\" $selected>{$viatura['placa']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="data_inicial" class="form-label">Data Inicial</label>
                                <input type="date" class="form-control" id="data_inicial" name="data_inicial" value="<?= htmlspecialchars($_POST['data_inicial'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="data_final" class="form-label">Data Final</label>
                                <input type="date" class="form-control" id="data_final" name="data_final" value="<?= htmlspecialchars($_POST['data_final'] ?? '') ?>">
                            </div>
                        </div>
                        <hr class="my-4">
                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search me-2"></i>Pesquisar Histórico</button>
                        <a href="pesquisar_historico_pneus.php" class="btn btn-outline-secondary ms-2"><i class="fas fa-redo me-2"></i>Limpar Filtros</a>
                    </form>

                    <?php
                    // Resultados da pesquisa (comentado, pois o foco é o layout visual)
                    /*
                if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($results)): ?>
                    <div class="alert alert-warning mt-4" role="alert">
                        Nenhum histórico de pneu encontrado com os filtros especificados.
                    </div>
                <?php elseif (!empty($results)): ?>
                    <h2 class="mt-5 mb-3">Resultados da Pesquisa</h2>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Placa da Viatura</th>
                                    <th>Data Registro</th>
                                    <th>Quilometragem</th>
                                    <th>Ações</th> // Observações removido daqui
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $registro): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($registro['id']) ?></td>
                                        <td><?= htmlspecialchars($registro['placa']) ?></td>
                                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($registro['data_registro']))) ?></td>
                                        <td><?= htmlspecialchars($registro['quilometragem']) ?> km</td>
                                        <td>
                                            <a href="detalhes_historico_pneus.php?id=<?= $registro['id'] ?>" class="btn btn-sm btn-info" title="Ver Detalhes"><i class="fas fa-eye"></i></a>
                                            <a href="editar_historico_pneus.php?id=<?= $registro['id'] ?>" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                            <a href="excluir_historico_pneus.php?id=<?= $registro['id'] ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este registro?')"><i class="fas fa-trash"></i></a>
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

    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>