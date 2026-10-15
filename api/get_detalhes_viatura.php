<?php
// api/get_detalhes_viatura.php

// 1. Inclui a classe ViaturaDAO (ajuste o caminho se necessário)
require_once 'middleware_auth.php';
require_once '../components/DAO/ViaturaDAO.php';

// Verifica se o ID da viatura foi passado via GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo "<div class='alert alert-danger'>ID da viatura não fornecido.</div>";
    exit();
}

$id_viatura = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($id_viatura === false) {
    http_response_code(400); // Bad Request
    echo "<div class='alert alert-danger'>ID da viatura inválido.</div>";
    exit();
}

try {
    // 2. REAPROVEITA A LÓGICA DO DAO
    $viaturaDAO = new ViaturaDAO();
    $viatura = $viaturaDAO->buscarDetalhesViaturaPorId($id_viatura);

    // 3. VERIFICA O RESULTADO E GERA O HTML PARA O MODAL
    if (!$viatura) {
        http_response_code(404); // Not Found
        echo "<div class='alert alert-warning'>Viatura não encontrada.</div>";
        exit();
    }

    // 4. GERA O HTML "ENXUTO" - SEM PÁGINA COMPLETA, SÓ O CONTEÚDO
    // Estamos basicamente copiando e colando a parte interna dos seus cards.
?>

    <!-- Seção de Informações Básicas -->
    <h5 class="mb-3">Informações Básicas</h5>
    <dl class="row">
        <dt class="col-sm-4">Prefixo:</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($viatura['prefixo'] ?? 'Não informado'); ?></dd>

        <dt class="col-sm-4">Número:</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($viatura['numero'] ?? 'Não informado'); ?></dd>

        <dt class="col-sm-4">Placa:</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($viatura['placa'] ?? 'Não informada'); ?></dd>

        <dt class="col-sm-4">RENAVAM:</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($viatura['renavam'] ?? 'Não informado'); ?></dd>

        <dt class="col-sm-4">Marca/Modelo:</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($viatura['marca'] . '/' . $viatura['modelo']); ?></dd>

        <dt class="col-sm-4">Status:</dt>
        <dd class="col-sm-8">
            <span class="badge <?php
                                if (($viatura['status_da_viatura'] ?? '') == 'Operante') echo 'bg-success';
                                elseif (($viatura['status_da_viatura'] ?? '') == 'Indisponível') echo 'bg-warning text-dark';
                                else echo 'bg-danger';
                                ?>">
                <?php echo htmlspecialchars($viatura['status_da_viatura'] ?? 'N/A'); ?>
            </span>
        </dd>
    </dl>

    <hr>

    <!-- Seção de Detalhes Técnicos -->
    <h5 class="mb-3 mt-4">Detalhes Técnicos</h5>
    <dl class="row">
        <dt class="col-sm-4">Ano:</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($viatura['ano'] ?? 'Não informado'); ?></dd>

        <dt class="col-sm-4">Quilometragem:</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars(number_format($viatura['quilometragem'] ?? 0, 0, ',', '.')); ?> km</dd>

        <dt class="col-sm-4">PBT (kg):</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($viatura['pbt'] ?? 'Não informado'); ?></dd>

        <dt class="col-sm-4">Câmbio:</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($viatura['tipo_cambio'] ?? 'Não informado'); ?> - <?php echo htmlspecialchars($viatura['numero_marchas'] ?? 'N/A'); ?> marchas</dd>

        <dt class="col-sm-4">Garantia Fim:</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($viatura['garantia_fim'] ? date('d/m/Y', strtotime($viatura['garantia_fim'])) : 'Não informada'); ?></dd>
    </dl>

<?php
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    // Em produção, você pode querer logar o erro em vez de exibi-lo
    echo "<div class='alert alert-danger'>Erro ao conectar com o banco de dados.</div>";
    // echo "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>"; // Para depuração
}
?>