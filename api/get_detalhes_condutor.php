<?php
// api/get_detalhes_condutor.php
require_once 'middleware_auth.php';
require_once '../components/DAO/CondutorDAO.php';

$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

if (!$id) {
    echo "<div class='alert alert-danger'>ID inválido.</div>";
    exit;
}

try {
    $dao = new CondutorDAO();
    $c = $dao->getCondutorById($id);

    if (!$c) {
        echo "<div class='alert alert-warning'>Condutor não encontrado.</div>";
        exit;
    }
?>
    <div class="row">
        <div class="col-md-6">
            <label class="small fw-bold">Nome Completo:</label>
            <p><?= htmlspecialchars($c['nome'] ?? 'N/A') ?></p>
        </div>
        <div class="col-md-6">
            <label class="small fw-bold">CPF:</label>
            <p><?= htmlspecialchars($c['cpf'] ?? 'N/A') ?></p>
        </div>
        <div class="col-md-4">
            <label class="small fw-bold">RG Militar:</label>
            <p><?= htmlspecialchars($c['rg_militar'] ?? 'N/A') ?></p>
        </div>
        <div class="col-md-4">
            <label class="small fw-bold">Posto:</label>
            <p><?= htmlspecialchars($c['posto_grad'] ?? 'N/A') ?></p>
        </div>
        <div class="col-md-4">
            <label class="small fw-bold">Unidade:</label>
            <p><?= htmlspecialchars($c['nome_unidade'] ?? 'N/A') ?></p>
        </div>
    </div>
    <hr>
    <h6 class="text-primary fw-bold">CNH</h6>
    <div class="row">
        <div class="col-md-4"><label class="small">Número:</label>
            <p><?= htmlspecialchars($c['numero_cnh'] ?? 'N/A') ?></p>
        </div>
        <div class="col-md-4"><label class="small">Categoria:</label>
            <p><?= htmlspecialchars($c['categoria_cnh'] ?? 'N/A') ?></p>
        </div>
        <div class="col-md-4"><label class="small">Validade:</label>
            <p><?= !empty($c['data_validade_cnh']) ? date('d/m/Y', strtotime($c['data_validade_cnh'])) : 'N/A' ?></p>
        </div>
    </div>
<?php
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
