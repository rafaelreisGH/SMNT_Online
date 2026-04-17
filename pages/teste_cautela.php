<?php
// pages/teste_cautela.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/ConexaoDB/conexao.php'; // ADICIONADO

try {
    $db = Conexao::getConexao(); // Inicializa a variável $db

    // Consulta completa unindo todas as tabelas
    $sql = "SELECT c.id, v.prefixo, v.numero, cond.nome as condutor, 
                   u.nome as unidade, c.status, c.odometro_saida,
                   (SELECT GROUP_CONCAT(CONCAT(av.nome, ':', ci.entregue_saida) SEPARATOR ' | ')
                    FROM cautela_itens ci 
                    JOIN acessorios_viatura av ON ci.acessorio_id = av.id 
                    WHERE ci.cautela_id = c.id) as itens_conferidos
            FROM cautelas c
            JOIN viaturas v ON c.viatura_id = v.id
            JOIN unidades u ON v.lotacao_atual_id = u.id
            JOIN condutor cond ON c.condutor_id = cond.id
            ORDER BY c.id DESC";

    $dados = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("<div class='alert alert-danger'>Erro na depuração: " . $e->getMessage() . "</div>");
}
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Depuração de Cautelas (Logs Brutos)</h1>
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm small">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Vtr</th>
                            <th>Condutor</th>
                            <th>Status</th>
                            <th>KM Saída</th>
                            <th>Itens no Banco (1=Sim, 0=Não)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dados as $d): ?>
                            <tr>
                                <td><?= $d['id'] ?></td>
                                <td><?= $d['prefixo'] ?> <?= $d['numero'] ?></td>
                                <td><?= $d['condutor'] ?></td>
                                <td><?= $d['status'] ?></td>
                                <td><?= $d['odometro_saida'] ?></td>
                                <td><?= $d['itens_conferidos'] ?: 'Nenhum item gravado' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>