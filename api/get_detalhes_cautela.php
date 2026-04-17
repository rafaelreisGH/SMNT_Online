<?php
require_once 'middleware_auth.php';
require_once '../components/DAO/CautelaDAO.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$dao = new CautelaDAO();
$c = $dao->buscarDadosCompletosDocumento($id);

if (!$c) {
    echo "<div class='alert alert-danger'>Dados não encontrados.</div>";
    exit;
}

$itens = $dao->buscarChecklist($id); // [id => status]
$avarias = $dao->buscarAvariasAtivas($c['viatura_id']); // Reuso do seu método existente

// Busca nomes dos acessórios para o checklist
$db = Conexao::getConexao();
$nomesItens = $db->query("SELECT id, nome FROM acessorios_viatura")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="container-fluid small">
    <!-- Seção 1: Identificação -->
    <h6 class="text-primary fw-bold border-bottom pb-1"><i class="fas fa-id-card me-2"></i>Identificação</h6>
    <div class="row mb-3">
        <div class="col-6"><strong>Viatura:</strong> <?= htmlspecialchars($c['prefixo'] . ' ' . $c['numero'] ?? '') ?></div>
        <div class="col-6"><strong>Placa:</strong> <?= htmlspecialchars($c['placa'] ?? '') ?></div>
        <div class="col-12"><strong>Condutor:</strong> <?= htmlspecialchars($c['condutor_nome'] ?? '') ?></div>
    </div>

    <!-- Seção 2: Estado de Saída -->
    <h6 class="text-primary fw-bold border-bottom pb-1"><i class="fas fa-tachometer-alt me-2"></i>Estado de Saída</h6>
    <div class="row mb-3">
        <div class="col-4"><strong>Odômetro:</strong> <?= number_format($c['odometro_saida'] ?? 0, 0, ',', '.') ?> km</div>
        <div class="col-4"><strong>Combustível:</strong> <?= $c['combustivel_saida'] ?></div>
        <div class="col-4"><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($c['data_inicio'])) ?></div>
    </div>

    <!-- Seção 3: Checklist -->
    <h6 class="text-primary fw-bold border-bottom pb-1"><i class="fas fa-tasks me-2"></i>Checklist de Acessórios</h6>
    <div class="row row-cols-2 row-cols-md-3 g-2 mb-3">
        <?php foreach ($nomesItens as $idAcc => $nomeAcc):
            $entregue = (isset($itens[$idAcc]) && $itens[$idAcc] == 1);
        ?>
            <div class="col">
                <i class="fas <?= $entregue ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' ?> me-1"></i>
                <?= htmlspecialchars($nomeAcc) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Seção 4: Avarias Preexistentes -->
    <h6 class="text-danger fw-bold border-bottom pb-1"><i class="fas fa-exclamation-triangle me-2"></i>Danos na Saída</h6>
    <div class="bg-light p-2 border rounded mb-3">
        <?php if (empty($avarias)): ?>
            <span class="text-muted">Nenhuma avaria registrada.</span>
        <?php else: ?>
            <ul class="list-unstyled mb-0">
                <?php foreach ($avarias as $av): ?>
                    <li>• <?= htmlspecialchars($av['nome_componente']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Assinatura -->
    <?php if ($c['assinatura_saida_data']): ?>
        <div class="alert alert-secondary py-2 text-center mb-0">
            <i class="fas fa-user-check me-2"></i>
            Assinado digitalmente por <strong><?= htmlspecialchars($c['condutor_nome']) ?></strong> em <?= date('d/m/Y H:i:s', strtotime($c['assinatura_saida_data'])) ?>
        </div>
    <?php endif; ?>
</div>