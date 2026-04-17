<?php
// api/get_avarias_viaturas.php
require_once 'middleware_auth.php';
require_once '../components/DAO/CautelaDAO.php';

$vId = filter_var($_GET['viatura_id'] ?? 0, FILTER_VALIDATE_INT);
$dao = new CautelaDAO();
$avarias = $dao->buscarAvariasAtivas($vId);

if (empty($avarias)) {
    echo '<p class="text-success small mb-0"><i class="fas fa-check-circle me-1"></i> Nenhuma avaria preexistente.</p>';
} else {
    echo '<ul class="list-group list-group-flush">';
    foreach ($avarias as $a) {
        echo '<li class="list-group-item p-1 small bg-light text-danger border-0">
                <i class="fas fa-exclamation-triangle me-1"></i> ' . htmlspecialchars($a['nome_componente']) . '
              </li>';
    }
    echo '</ul>';
}
