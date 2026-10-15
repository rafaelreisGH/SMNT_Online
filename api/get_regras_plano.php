<?php
// api/get_regras_plano.php
header('Content-Type: text/html'); // Retorna HTML para o modal
require_once 'middleware_auth.php';
require_once '../components/DAO/PlanoManutencaoDAO.php';

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
$dao = new PlanoManutencaoDAO();
$regras = $dao->buscarRegrasDoPlano($id);

if (empty($regras)): ?>
    <div class="alert alert-info">Este plano não possui regras cadastradas.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead class="table-dark">
                <tr>
                    <th>Item/Serviço</th>
                    <th>Periodicidade (KM)</th>
                    <th>Periodicidade (Horas)</th>
                    <th>Periodicidade (Tempo)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($regras as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['item_nome']) ?></td>
                        <td><?= $r['periodicidade_km'] ? number_format($r['periodicidade_km'], 0, ',', '.') . ' km' : '-' ?></td>
                        <td><?= $r['periodicidade_horas'] ? $r['periodicidade_horas'] . ' h' : '-' ?></td>
                        <td><?= $r['periodicidade_meses'] ? $r['periodicidade_meses'] . ' meses' : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>