<?php
// api/get_detalhes_manutencao.php
require_once 'middleware_auth.php';
require_once '../components/DAO/ManutencaoDAO.php';

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
$mDAO = new ManutencaoDAO();
$m = $mDAO->buscarDetalhesPorId($id);

if (!$m) {
    echo "<div class='alert alert-danger'>Manutenção não encontrada.</div>";
    exit;
}
?>
<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary fw-bold">Viatura</h6>
        <p><?= htmlspecialchars($m['prefixo'] . ' ' . $m['numero']) ?> (<?= htmlspecialchars($m['placa']) ?>)<br>
            <small class="text-muted"><?= htmlspecialchars($m['modelo']) ?></small>
        </p>
    </div>
    <div class="col-md-6 text-end">
        <h6 class="text-primary fw-bold">Status Atual</h6>
        <span class="badge <?= $m['status'] == 'Concluída' ? 'bg-success' : 'bg-warning text-dark' ?> p-2">
            <?= $m['status'] ?>
        </span>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-4">
        <label class="small fw-bold">Data de Entrada:</label>
        <p><?= date('d/m/Y H:i', strtotime($m['data_entrada'])) ?></p>
    </div>
    <div class="col-md-4">
        <label class="small fw-bold">Tipo:</label>
        <p><?= $m['tipo_manutencao'] ?></p>
    </div>
    <div class="col-md-4">
        <label class="small fw-bold">KM na Entrada:</label>
        <p><?= number_format($m['quilometragem'], 0, ',', '.') ?> km</p>
    </div>
    <div class="col-md-12">
        <label class="small fw-bold">Oficina:</label>
        <p><?= htmlspecialchars($m['oficina_nome']) ?></p>
    </div>
</div>
<?php if ($m['item_preventiva_nome']): ?>
    <div class="alert alert-success p-2">
        <strong>Item Preventivo:</strong> <?= htmlspecialchars($m['item_preventiva_nome']) ?>
    </div>
<?php endif; ?>
<div class="bg-light p-2 border rounded">
    <label class="small fw-bold">Descrição do Problema:</label>
    <p class="mb-0"><?= nl2br(htmlspecialchars($m['descricao_problema'] ?? 'Não informada')) ?></p>
</div>