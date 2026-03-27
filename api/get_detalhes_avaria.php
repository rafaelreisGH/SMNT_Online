<?php
// api/get_detalhes_avaria.php

require_once 'middleware_auth.php';
require_once '../components/DAO/RegistroAvariaDAO.php';
require_once '../components/DAO/ApuracaoDAO.php';

$id_avaria = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$id_avaria) {
    http_response_code(400);
    echo "<div class='alert alert-danger'>ID inválido.</div>";
    exit;
}

try {
    $aDAO = new RegistroAvariaDAO();
    $apuracaoDAO = new ApuracaoDAO();

    $avaria = $aDAO->buscarDetalhesPorId($id_avaria);

    if (!$avaria) {
        http_response_code(404);
        echo "<div class='alert alert-warning'>Registro não encontrado.</div>";
        exit;
    }

    // Busca dados da apuração caso existam
    $dados_apuracao = $apuracaoDAO->buscarPorAvariaId($id_avaria);
?>

    <!-- Identificação da Viatura -->
    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-truck me-2"></i>Viatura</h6>
    <div class="row mb-3">
        <div class="col-sm-6">
            <label class="small text-muted mb-0">Viatura:</label>
            <p class="fw-bold mb-0"><?= htmlspecialchars($avaria['prefixo'] . ' ' . $avaria['numero']) ?> (<?= htmlspecialchars($avaria['placa']) ?>)</p>
        </div>
        <div class="col-sm-6">
            <label class="small text-muted mb-0">Modelo:</label>
            <p class="mb-0"><?= htmlspecialchars($avaria['modelo']) ?></p>
        </div>
    </div>

    <hr>

    <!-- Detalhes do Relato -->
    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-clipboard-list me-2"></i>Relato da Avaria</h6>
    <div class="row">
        <div class="col-sm-6">
            <label class="small text-muted mb-0">Componente:</label>
            <p class="mb-2"><?= htmlspecialchars($avaria['nome_componente']) ?> (<?= htmlspecialchars($avaria['categoria']) ?>)</p>
        </div>
        <div class="col-sm-6">
            <label class="small text-muted mb-0">Data do Relato:</label>
            <p class="mb-2"><?= date('d/m/Y H:i', strtotime($avaria['data_registro'])) ?></p>
        </div>
        <div class="col-12">
            <label class="small text-muted mb-0">Descrição do Problema:</label>
            <div class="p-2 bg-light border rounded small"><?= nl2br(htmlspecialchars($avaria['observacoes_avaria'] ?? 'Nenhuma observação detalhada.')) ?></div>
        </div>
    </div>

    <hr>

    <!-- NOVA SEÇÃO: Apuração Administrativa -->
    <h6 class="text-primary fw-bold mb-3"><i class="fas fa-gavel me-2"></i>Apuração Administrativa</h6>
    <div class="row">
        <div class="col-sm-6">
            <label class="small text-muted mb-0">Exige Apuração?</label>
            <p class="mb-2">
                <?php if ($avaria['requer_apuracao'] == 1): ?>
                    <span class="badge bg-danger">SIM - Exigida pela Gestão Central</span>
                <?php else: ?>
                    <span class="badge bg-secondary">NÃO</span>
                <?php endif; ?>
            </p>
        </div>

        <?php if ($avaria['requer_apuracao'] == 1): ?>
            <div class="col-sm-6">
                <label class="small text-muted mb-0">Status do Procedimento:</label>
                <p class="mb-2">
                    <span class="badge <?= ($dados_apuracao['status_apuracao'] ?? '') == 'Concluído' ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= htmlspecialchars($dados_apuracao['status_apuracao'] ?? 'Pendente de Abertura') ?>
                    </span>
                </p>
            </div>

            <?php if (!empty($dados_apuracao['numero_portaria'])): ?>
                <div class="col-sm-4">
                    <label class="small text-muted mb-0">Nº Portaria:</label>
                    <p class="mb-2"><?= htmlspecialchars($dados_apuracao['numero_portaria'] ?? 'Não informada') ?></p>
                </div>
                <div class="col-sm-4">
                    <label class="small text-muted mb-0">Data Abertura:</label>
                    <p class="mb-2"><?= !empty($dados_apuracao['data_abertura']) ? date('d/m/Y', strtotime($dados_apuracao['data_abertura'])) : 'Não informada' ?></p>
                </div>
                <div class="col-sm-4">
                    <label class="small text-muted mb-0">Encarregado:</label>
                    <p class="mb-2"><?= htmlspecialchars($dados_apuracao['encarregado'] ?? 'Não informado') ?></p>
                </div>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-warning py-1 small">
                        Aguardando lançamento dos dados da Portaria pela Unidade Responsável.
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <hr>

    <!-- Situação da Manutenção -->
    <div class="row">
        <div class="col-sm-12 text-end">
            <label class="small text-muted me-2">Situação Técnica:</label>
            <span class="badge <?= $avaria['status_manutencao'] == 'Concluída' ? 'bg-success' : 'bg-info' ?>">
                <?= $avaria['status_manutencao'] ? 'Manutenção ' . $avaria['status_manutencao'] : 'Manutenção não iniciada' ?>
            </span>
        </div>
    </div>

<?php
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar detalhes: " . $e->getMessage() . "</div>";
}
