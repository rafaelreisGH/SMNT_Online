<?php
// Inclui o template da barra lateral
include_once '../templates/sidebar.php';

// Inclui a classe ViaturaDAO
// Ajuste o caminho se seu ViaturaDAO.php estiver em um diretório diferente
require_once '../components/DAO/ViaturaDAO.php';

// Cria uma instância da ViaturaDAO
// A conexão PDO será obtida internamente pelo construtor da ViaturaDAO
$viaturaDAO = new ViaturaDAO();

$viatura = false; // Inicializa $viatura como false por padrão

// Verifica se o ID da viatura foi passado via GET
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_viatura = filter_var($_GET['id'], FILTER_VALIDATE_INT); // Valida e filtra o ID

    if ($id_viatura === false) {
        // Se o ID não for um inteiro válido, redireciona
        header('Location: pesquisar_viatura.php?message=ID da viatura inválido.&type=danger');
        exit();
    }

    // Usa o método da DAO para buscar a viatura com todos os detalhes
    $viatura = $viaturaDAO->buscarDetalhesViaturaPorId($id_viatura);

    // Se a viatura não for encontrada, redirecione ou mostre uma mensagem de erro
    if (!$viatura) {
        header('Location: pesquisar_viatura.php?message=Viatura não encontrada.&type=danger');
        exit();
    }
} else {
    // Se o ID não foi fornecido na URL, redirecione para a página de pesquisa
    header('Location: pesquisar_viatura.php?message=ID da viatura não fornecido.&type=warning');
    exit();
}
?>

<div class="d-flex min-vh-100 w-100" style="padding-bottom: 3rem;">
    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Detalhes da Viatura: <span class="text-primary"><?php echo htmlspecialchars($viatura['prefixo'] ?? 'N/A'); ?></span></h2>
            <a href="pesquisar_viatura.php" class="btn btn-secondary">Voltar para Pesquisa</a>
        </div>

        <hr>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Informações Básicas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Prefixo:</strong> <?php echo htmlspecialchars($viatura['prefixo'] ?? 'Não informado'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Número:</strong> <?php echo htmlspecialchars($viatura['numero'] ?? 'Não informado'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Placa:</strong> <?php echo htmlspecialchars($viatura['placa'] ?? 'Não informada'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>RENAVAM:</strong> <?php echo htmlspecialchars($viatura['renavam'] ?? 'Não informado'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Marca:</strong> <?php echo htmlspecialchars($viatura['marca'] ?? 'Não informada'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Modelo:</strong> <?php echo htmlspecialchars($viatura['modelo'] ?? 'Não informado'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Ano:</strong> <?php echo htmlspecialchars($viatura['ano'] ?? 'Não informado'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Tipo Operacional:</strong> <?php echo htmlspecialchars($viatura['tipo_operacional'] ?? 'Não informado'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status da Viatura:</strong>
                        <span class="badge
                        <?php
                        if (($viatura['status_da_viatura'] ?? '') == 'Operante') {
                            echo 'bg-success';
                        } elseif (($viatura['status_da_viatura'] ?? '') == 'Indisponível') {
                            echo 'bg-warning text-dark';
                        } elseif (($viatura['status_da_viatura'] ?? '') == 'Baixada' || ($viatura['status_da_viatura'] ?? '') == 'Descarregada') {
                            echo 'bg-danger';
                        } else {
                            echo 'bg-info';
                        }
                        ?>">
                            <?php echo htmlspecialchars($viatura['status_da_viatura'] ?? 'Não informado'); ?>
                        </span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Tipo de Aquisição:</strong> <?php echo htmlspecialchars($viatura['tipo_aquisicao'] ?? 'Não informado'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Quilometragem:</strong> <?php echo htmlspecialchars(number_format($viatura['quilometragem'] ?? 0, 0, ',', '.')); ?> km
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Detalhes Técnicos</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>PBT (Peso Bruto Total):</strong> <?php echo htmlspecialchars($viatura['pbt'] ?? 'Não informado'); ?> kg
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Tipo de Câmbio:</strong> <?php echo htmlspecialchars($viatura['tipo_cambio'] ?? 'Não informado'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Número de Marchas:</strong> <?php echo htmlspecialchars($viatura['numero_marchas'] ?? 'Não informado'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Cilindradas:</strong> <?php echo htmlspecialchars($viatura['cilindradas'] ?? 'Não informado'); ?> cc
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Cavalos de Força (HP):</strong> <?php echo htmlspecialchars($viatura['cavalos_forca'] ?? 'Não informado'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Torque:</strong> <?php echo htmlspecialchars($viatura['torque'] ?? 'Não informado'); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Garantia Fim:</strong> <?php echo htmlspecialchars($viatura['garantia_fim'] ? date('d/m/Y', strtotime($viatura['garantia_fim'])) : 'Não informada'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info" role="alert">
            Alguns campos como "Tipo de Combustível", "Capacidade do Tanque", "Última/Próxima Revisão" e "Observações" não foram exibidos, pois não foram encontrados na estrutura da tabela `viaturas` fornecida.
            Se esses dados estiverem em outras tabelas, a query SQL precisará ser ajustada para incluí-los.
        </div>

    </div>
</div>

<div class="b-example-divider b-example-vr"></div>

<?php
include_once '../templates/footer.php';
?>