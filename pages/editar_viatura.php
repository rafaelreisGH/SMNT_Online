<?php
// Inclui o sidebar do template
include_once '../templates/sidebar.php';

// Inclui a conexão com o banco de dados (ajuste o caminho se necessário)
// Certifique-se de que este arquivo contém a sua instância PDO, por exemplo: $pdo = new PDO(...)
// include_once '../config/db.php'; 

// Variável para armazenar os dados da viatura
$viatura = [];
$error_message = '';

// Verifica se um ID foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $viatura_id = $_GET['id'];

    try {
        // --- SIMULAÇÃO DE BANCO DE DADOS ---
        // Em um ambiente real, você faria uma consulta PDO aqui:
        // $stmt = $pdo->prepare("SELECT v.*, u.nome as nome_unidade FROM viaturas v LEFT JOIN unidades u ON v.lotacao_atual_id = u.id WHERE v.id = :id");
        // $stmt->bindParam(':id', $viatura_id, PDO::PARAM_INT);
        // $stmt->execute();
        // $viatura = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se a viatura não for encontrada
        // if (!$viatura) {
        //     $error_message = "Viatura não encontrada.";
        //     // Opcional: redirecionar para a página de gerenciamento
        //     // header('Location: gerencia_viaturas.php?status=notfound');
        //     // exit;
        // }
        // --- FIM DA SIMULAÇÃO ---

        // SIMULAÇÃO: Dados de viatura para teste
        $simulated_viaturas = [
            1 => [
                'id' => 1, 'tipo_operacional' => 'Combate a incendio', 'prefixo' => 'VTR01', 'numero' => 101, 'placa' => 'ABC1234',
                'renavam' => '12345678901', 'marca' => 'Mercedes-Benz', 'modelo' => 'Atego 1718', 'ano' => 2010,
                'pbt' => 17000, 'tipo_aquisicao' => 'Própria', 'status_da_viatura' => 'Operante',
                'lotacao_atual_id' => 1, 'cilindradas' => 4800, 'cavalos_forca' => 180, 'torque' => '670 Nm',
                'quilometragem' => 150000, 'garantia_fim' => '2025-12-31', 'tipo_cambio' => 'Manual',
                'numero_marchas' => 6, 'tipo_carroceria' => 'Caminhão Bombeiro', 'equipamentos_especificos' => 'Bomba de alta vazão, Escada articulada'
            ],
            2 => [
                'id' => 2, 'tipo_operacional' => 'Administrativa', 'prefixo' => 'VTR02', 'numero' => 202, 'placa' => 'DEF5678',
                'renavam' => '23456789012', 'marca' => 'Ford', 'modelo' => 'Ka', 'ano' => 2020,
                'pbt' => 1500, 'tipo_aquisicao' => 'Locada', 'status_da_viatura' => 'Indisponível',
                'lotacao_atual_id' => 2, 'cilindradas' => 1000, 'cavalos_forca' => 85, 'torque' => '105 Nm',
                'quilometragem' => 50000, 'garantia_fim' => '2024-06-30', 'tipo_cambio' => 'Automático',
                'numero_marchas' => 6, 'tipo_carroceria' => 'Hatchback', 'equipamentos_especificos' => 'Nenhum'
            ],
            // Adicione mais viaturas simuladas conforme necessário
        ];

        $viatura = $simulated_viaturas[$viatura_id] ?? [];

        if (!$viatura) {
            $error_message = "Viatura com ID '{$viatura_id}' não encontrada na simulação.";
        }

    } catch (PDOException $e) {
        $error_message = "Erro ao carregar dados da viatura: " . $e->getMessage();
        // Em produção: error_log($e->getMessage());
    }
} else {
    $error_message = "ID da viatura não fornecido ou inválido.";
    // Opcional: redirecionar para a página de gerenciamento
    // header('Location: gerencia_viaturas.php?status=invalid_id');
    // exit;
}

// Lógica para processar o formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $viatura_id_post = $_POST['id'];

    // Coleta os dados do formulário
    $tipo_operacional = $_POST['tipo_operacional'] ?? null;
    $prefixo = $_POST['prefixo'] ?? null;
    $numero = $_POST['numero'] ?? null;
    $placa = $_POST['placa'] ?? null;
    $renavam = $_POST['renavam'] ?? null;
    $marca = $_POST['marca'] ?? null;
    $modelo = $_POST['modelo'] ?? null;
    $ano = $_POST['ano'] ?? null;
    $pbt = $_POST['pbt'] ?? null;
    $tipo_aquisicao = $_POST['tipo_aquisicao'] ?? null;
    $status_da_viatura = $_POST['status_da_viatura'] ?? null;
    $lotacao_atual_id = $_POST['lotacao_atual_id'] ?? null;
    $cilindradas = $_POST['cilindradas'] ?? null;
    $cavalos_forca = $_POST['cavalos_forca'] ?? null;
    $torque = $_POST['torque'] ?? null;
    $quilometragem = $_POST['quilometragem'] ?? null;
    $garantia_fim = $_POST['garantia_fim'] ?? null;
    $tipo_cambio = $_POST['tipo_cambio'] ?? null;
    $numero_marchas = $_POST['numero_marchas'] ?? null;
    // Campos que estão no HTML mas não na tabela viaturas
    $tipo_carroceria = $_POST['tipo_carroceria'] ?? null; 
    $equipamentos_especificos = $_POST['equipamentos_especificos'] ?? null;


    // Validação básica (adicione mais validações conforme necessário)
    if (empty($viatura_id_post) || !is_numeric($viatura_id_post)) {
        $error_message = "Erro: ID da viatura para atualização inválido.";
    } else {
        try {
            // --- SIMULAÇÃO DE BANCO DE DADOS PARA UPDATE ---
            // Em um ambiente real, você faria um UPDATE PDO aqui:
            // $stmt = $pdo->prepare("UPDATE viaturas SET
            //     tipo_operacional = :tipo_operacional, prefixo = :prefixo, numero = :numero,
            //     placa = :placa, renavam = :renavam, marca = :marca, modelo = :modelo,
            //     ano = :ano, pbt = :pbt, tipo_aquisicao = :tipo_aquisicao,
            //     status_da_viatura = :status_da_viatura, lotacao_atual_id = :lotacao_atual_id,
            //     cilindradas = :cilindradas, cavalos_forca = :cavalos_forca, torque = :torque,
            //     quilometragem = :quilometragem, garantia_fim = :garantia_fim,
            //     tipo_cambio = :tipo_cambio, numero_marchas = :numero_marchas
            //     WHERE id = :id");
            // $stmt->execute([
            //     ':tipo_operacional' => $tipo_operacional,
            //     ':prefixo' => $prefixo,
            //     ':numero' => $numero,
            //     // ... continue com todos os campos ...
            //     ':id' => $viatura_id_post
            // ]);

            // SIMULAÇÃO: Atualiza os dados na variável simulada
            if (isset($simulated_viaturas[$viatura_id_post])) {
                $simulated_viaturas[$viatura_id_post] = array_merge($simulated_viaturas[$viatura_id_post], [
                    'tipo_operacional' => $tipo_operacional,
                    'prefixo' => $prefixo,
                    'numero' => $numero,
                    'placa' => $placa,
                    'renavam' => $renavam,
                    'marca' => $marca,
                    'modelo' => $modelo,
                    'ano' => $ano,
                    'pbt' => $pbt,
                    'tipo_aquisicao' => $tipo_aquisicao,
                    'status_da_viatura' => $status_da_viatura,
                    'lotacao_atual_id' => $lotacao_atual_id,
                    'cilindradas' => $cilindradas,
                    'cavalos_forca' => $cavalos_forca,
                    'torque' => $torque,
                    'quilometragem' => $quilometragem,
                    'garantia_fim' => $garantia_fim,
                    'tipo_cambio' => $tipo_cambio,
                    'numero_marchas' => $numero_marchas,
                    'tipo_carroceria' => $tipo_carroceria, // Mantido aqui para demonstração do preenchimento
                    'equipamentos_especificos' => $equipamentos_especificos // Mantido aqui
                ]);
                $viatura = $simulated_viaturas[$viatura_id_post]; // Atualiza a variável $viatura para refletir as mudanças
                echo "<script>alert('Viatura atualizada com sucesso!'); window.location.href='gerencia_viaturas.php';</script>";
                exit; // Termina a execução para evitar re-submissão
            } else {
                $error_message = "Erro na simulação: Viatura com ID '{$viatura_id_post}' não encontrada para atualização.";
            }

        } catch (PDOException $e) {
            $error_message = "Erro ao atualizar viatura: " . $e->getMessage();
            // Em produção: error_log($e->getMessage());
        }
    }
}

// Simulação de dados de unidades para os selects (repetido do cadastro.php)
$unidadesExemplo = [
    ['id' => 1, 'nome' => '1ª CIBM'],
    ['id' => 2, 'nome' => '2ª CIBM'],
    ['id' => 3, 'nome' => 'QCG'],
];

?>

<div class="d-flex min-vh-100 w-100" style="padding-bottom: 3rem;">
    <div class="row justify-content-center w-100" style="padding-top: 3rem;">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="content-area">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <h1 class="mb-3">Editar Viatura</h1>
                        <p class="lead">Atualize os dados da viatura.</p>
                        <hr class="mb-4">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= htmlspecialchars($error_message) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <form action="" method="POST" id="editViaturaForm">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($viatura['id'] ?? '') ?>">

                    <ul class="nav nav-tabs mb-4" id="viaturaTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="dados-basicos-tab" data-bs-toggle="tab" data-bs-target="#dados-basicos-pane" type="button" role="tab" aria-controls="dados-basicos-pane" aria-selected="true">Dados Básicos</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="motorizacao-tab" data-bs-toggle="tab" data-bs-target="#motorizacao-pane" type="button" role="tab" aria-controls="motorizacao-pane" aria-selected="false">Motorização</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="diversos-tab" data-bs-toggle="tab" data-bs-target="#diversos-pane" type="button" role="tab" aria-controls="diversos-pane" aria-selected="false">Diversos</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="viaturaTabContent">

                        <div class="tab-pane fade show active" id="dados-basicos-pane" role="tabpanel" aria-labelledby="dados-basicos-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="tipo_operacional" class="form-label">Tipo Operacional</label>
                                    <select class="form-select" id="tipo_operacional" name="tipo_operacional">
                                        <option value="" disabled>Selecione...</option>
                                        <?php
                                        $tiposOperacionais = ['Operacional Geral', 'Combate a incendio', 'Administrativa'];
                                        foreach ($tiposOperacionais as $tipo) {
                                            $selected = (isset($viatura['tipo_operacional']) && $viatura['tipo_operacional'] == $tipo) ? 'selected' : '';
                                            echo "<option value=\"$tipo\" $selected>$tipo</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="prefixo" class="form-label">Prefixo</label>
                                    <input type="text" class="form-control" id="prefixo" name="prefixo" maxlength="5" value="<?= htmlspecialchars($viatura['prefixo'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="numero" class="form-label">Número</label>
                                    <input type="number" class="form-control" id="numero" name="numero" value="<?= htmlspecialchars($viatura['numero'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="placa" class="form-label">Placa</label>
                                    <input type="text" class="form-control" id="placa" name="placa" maxlength="7" placeholder="Ex: ABC1234" value="<?= htmlspecialchars($viatura['placa'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="renavam" class="form-label">Renavam</label>
                                    <input type="text" class="form-control" id="renavam" name="renavam" maxlength="11" placeholder="Somente números" value="<?= htmlspecialchars($viatura['renavam'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="marca" class="form-label">Marca</label>
                                    <input type="text" class="form-control" id="marca" name="marca" maxlength="50" value="<?= htmlspecialchars($viatura['marca'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="modelo" class="form-label">Modelo</label>
                                    <input type="text" class="form-control" id="modelo" name="modelo" maxlength="50" value="<?= htmlspecialchars($viatura['modelo'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="ano" class="form-label">Ano</label>
                                    <input type="number" class="form-control" id="ano" name="ano" min="1900" max="<?= date('Y') + 1 ?>" value="<?= htmlspecialchars($viatura['ano'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="pbt" class="form-label">PBT (kg)</label>
                                    <input type="number" class="form-control" id="pbt" name="pbt" value="<?= htmlspecialchars($viatura['pbt'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="tipo_aquisicao" class="form-label">Tipo de Aquisição</label>
                                    <select class="form-select" id="tipo_aquisicao" name="tipo_aquisicao">
                                        <option value="" disabled>Selecione...</option>
                                        <?php
                                        $tiposAquisicao = ['Própria', 'Doação', 'Fiel Depositário', 'Locada'];
                                        foreach ($tiposAquisicao as $tipo) {
                                            $selected = (isset($viatura['tipo_aquisicao']) && $viatura['tipo_aquisicao'] == $tipo) ? 'selected' : '';
                                            echo "<option value=\"$tipo\" $selected>$tipo</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="status_da_viatura" class="form-label">Status da Viatura</label>
                                    <select class="form-select" id="status_da_viatura" name="status_da_viatura">
                                        <option value="" disabled>Selecione...</option>
                                        <?php
                                        $statusViatura = ['Operante', 'Baixada', 'Descarregada', 'Indisponível'];
                                        foreach ($statusViatura as $status) {
                                            $selected = (isset($viatura['status_da_viatura']) && $viatura['status_da_viatura'] == $status) ? 'selected' : '';
                                            echo "<option value=\"$status\" $selected>$status</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="lotacao_atual_id" class="form-label">Lotação Atual</label>
                                    <select class="form-select" id="lotacao_atual_id" name="lotacao_atual_id">
                                        <option value="" disabled>Selecione a Unidade</option>
                                        <?php
                                        foreach ($unidadesExemplo as $unidade) { // Usando a simulação de unidades
                                            $selected = (isset($viatura['lotacao_atual_id']) && $viatura['lotacao_atual_id'] == $unidade['id']) ? 'selected' : '';
                                            echo "<option value=\"{$unidade['id']}\" $selected>{$unidade['nome']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-12 mt-4">
                                    <h4>Características Adicionais</h4>
                                    <hr>
                                </div>
                                <div class="col-md-6">
                                    <label for="tipo_carroceria" class="form-label">Tipo de Carroceria</label>
                                    <input type="text" class="form-control" id="tipo_carroceria" name="tipo_carroceria" maxlength="100" value="<?= htmlspecialchars($viatura['tipo_carroceria'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="equipamentos_especificos" class="form-label">Equipamentos Específicos</label>
                                    <textarea class="form-control" id="equipamentos_especificos" name="equipamentos_especificos" rows="3"><?= htmlspecialchars($viatura['equipamentos_especificos'] ?? '') ?></textarea>
                                </div>
                                </div>
                        </div>

                        <div class="tab-pane fade" id="motorizacao-pane" role="tabpanel" aria-labelledby="motorizacao-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="cilindradas" class="form-label">Cilindradas (cc)</label>
                                    <input type="number" class="form-control" id="cilindradas" name="cilindradas" value="<?= htmlspecialchars($viatura['cilindradas'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="cavalos_forca" class="form-label">Cavalos de Força (cv)</label>
                                    <input type="number" class="form-control" id="cavalos_forca" name="cavalos_forca" value="<?= htmlspecialchars($viatura['cavalos_forca'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="torque" class="form-label">Torque</label>
                                    <input type="text" class="form-control" id="torque" name="torque" maxlength="50" placeholder="Ex: 250 Nm" value="<?= htmlspecialchars($viatura['torque'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="tipo_cambio" class="form-label">Tipo de Câmbio</label>
                                    <select class="form-select" id="tipo_cambio" name="tipo_cambio">
                                        <option value="" disabled>Selecione...</option>
                                        <?php
                                        $tiposCambio = ['Manual', 'Automático'];
                                        foreach ($tiposCambio as $tipo) {
                                            $selected = (isset($viatura['tipo_cambio']) && $viatura['tipo_cambio'] == $tipo) ? 'selected' : '';
                                            echo "<option value=\"$tipo\" $selected>$tipo</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="numero_marchas" class="form-label">Número de Marchas</label>
                                    <input type="number" class="form-control" id="numero_marchas" name="numero_marchas" min="1" value="<?= htmlspecialchars($viatura['numero_marchas'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="diversos-pane" role="tabpanel" aria-labelledby="diversos-tab" tabindex="0">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="quilometragem" class="form-label">Quilometragem (km)</label>
                                    <input type="number" class="form-control" id="quilometragem" name="quilometragem" value="<?= htmlspecialchars($viatura['quilometragem'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="garantia_fim" class="form-label">Fim da Garantia</label>
                                    <input type="date" class="form-control" id="garantia_fim" name="garantia_fim" value="<?= htmlspecialchars($viatura['garantia_fim'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                    </div>
                    <hr class="my-4">
                    <button class="btn btn-outline-success btn-lg" type="submit">Atualizar Viatura</button>
                    <a href="editar_viatura.php" class="btn btn-outline-secondary btn-lg">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="b-example-divider b-example-vr"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous"></script>
<script>
    // Inicializa as tabs do Bootstrap
    var triggerTabList = [].slice.call(document.querySelectorAll('#viaturaTab button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)

        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
        })
    })

    // Opcional: Adicionar formatação para placa e renavam
    document.addEventListener('DOMContentLoaded', function() {
        const placaInput = document.getElementById('placa');
        if (placaInput) {
            placaInput.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                if (value.length > 3) {
                    value = value.substring(0, 3) + '-' + value.substring(3);
                }
                e.target.value = value.substring(0, 8); // Limita ao formato AAA-0000
            });
        }

        const renavamInput = document.getElementById('renavam');
        if (renavamInput) {
            renavamInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^0-9]/g, '').substring(0, 11);
            });
        }
    });
</script>

<?php
include_once '../templates/footer.php';
?>