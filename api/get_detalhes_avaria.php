<?php
// api/get_detalhes_avaria.php

// 1. Inclui as classes necessárias
require_once 'middleware_auth.php';
require_once '../components/DAO/RegistroAvariaDAO.php';

// Verifica se o ID da avaria foi passado via GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo "<div class='alert alert-danger'>ID do registro não fornecido.</div>";
    exit();
}

$id_avaria = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($id_avaria === false) {
    http_response_code(400); // Bad Request
    echo "<div class='alert alert-danger'>ID do registro inválido.</div>";
    exit();
}

try {
    // 2. Instancia o DAO e busca os dados
    $registroAvariaDAO = new RegistroAvariaDAO();
    $avaria = $registroAvariaDAO->buscarDetalhesPorId($id_avaria);

    // 3. Verifica o resultado
    if (!$avaria) {
        http_response_code(404); // Not Found
        echo "<div class='alert alert-warning'>Registro de avaria não encontrado.</div>";
        exit();
    }

    // 4. GERA O HTML PARA O MODAL
?>

    <!-- Seção de Identificação da Viatura -->
    <h5 class="mb-3 text-primary"><i class="fas fa-car me-2"></i>Viatura Relacionada</h5>
    <dl class="row">
        <dt class="col-sm-4">Viatura:</dt>
        <dd class="col-sm-8"><strong><?php echo htmlspecialchars($avaria['prefixo'] . ' ' . $avaria['numero']); ?></strong></dd>

        <dt class="col-sm-4">Placa / Modelo:</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($avaria['placa'] . ' - ' . $avaria['modelo']); ?></dd>
    </dl>

    <hr>

    <!-- Seção do Relato -->
    <h5 class="mb-3 text-primary"><i class="fas fa-clipboard-list me-2"></i>Detalhes do Problema</h5>
    <dl class="row">
        <dt class="col-sm-4">Data do Registro:</dt>
        <dd class="col-sm-8"><?php echo date('d/m/Y H:i', strtotime($avaria['data_registro'])); ?></dd>

        <dt class="col-sm-4">Relatado por:</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($avaria['nome_condutor']); ?></dd>

        <dt class="col-sm-4">Componente:</dt>
        <dd class="col-sm-8">
            <span class="badge bg-secondary"><?php echo htmlspecialchars($avaria['categoria']); ?></span><br>
            <?php echo htmlspecialchars($avaria['nome_componente']); ?>
        </dd>

        <dt class="col-sm-4 mt-2">Observações:</dt>
        <dd class="col-sm-8 mt-2">
            <div class="p-2 border rounded bg-light">
                <?php echo nl2br(htmlspecialchars($avaria['observacoes_avaria'] ?? 'Nenhuma observação detalhada.')); ?>
            </div>
        </dd>
    </dl>

    <hr>

    <!-- Situação da Manutenção -->
    <h5 class="mb-3 text-primary"><i class="fas fa-tools me-2"></i>Situação Atual</h5>
    <dl class="row">
        <dt class="col-sm-4">Status:</dt>
        <dd class="col-sm-8">
            <?php if ($avaria['status_manutencao']): ?>
                <span class="badge <?php 
                    echo ($avaria['status_manutencao'] == 'Concluída') ? 'bg-success' : 'bg-info'; 
                ?>">
                    Manutenção <?php echo htmlspecialchars($avaria['status_manutencao']); ?>
                </span>
            <?php else: ?>
                <span class="badge bg-danger">Aguardando Início de Manutenção</span>
            <?php endif; ?>
        </dd>
    </dl>

<?php
} catch (Exception $e) {
    http_response_code(500);
    echo "<div class='alert alert-danger'>Erro ao carregar detalhes: " . $e->getMessage() . "</div>";
}
?>