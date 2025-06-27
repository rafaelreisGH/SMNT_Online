<?php

include_once '../templates/sidebar.php';

/*
// Inclua seu arquivo de conexão com o banco de dados
include_once '../config/database.php'; // Ajuste o caminho conforme necessário

// Lógica para buscar os dados das viaturas e suas localizações
$dados_localizacao = [];

try {
    // Exemplo de consulta SQL para buscar os dados necessários.
    // Você precisará de tabelas para 'comandos_regionais', 'municipios', 'unidades', 'viaturas'.
    // Esta consulta é uma sugestão e pode precisar de ajustes conforme seu esquema de BD real.
    $sql = "SELECT
                cr.nome AS comando_regional_nome,
                m.nome AS municipio_nome,
                u.nome AS unidade_nome,
                v.placa,
                v.prefixo,
                v.numero,
                v.modelo,
                v.ano_fabricacao,
                v.latitude, -- Assumindo que você tem latitude e longitude na tabela viaturas
                v.longitude
            FROM
                controle_viaturas.viaturas v
            JOIN
                controle_viaturas.unidades u ON v.unidade_id = u.id
            JOIN
                controle_viaturas.municipios m ON u.municipio_id = m.id
            JOIN
                controle_viaturas.comandos_regionais cr ON m.comando_regional_id = cr.id
            ORDER BY
                cr.nome, m.nome, u.nome, v.placa";

    $stmt = $pdo->query($sql);
    $raw_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organizar os dados em uma estrutura hierárquica para facilitar a exibição
    foreach ($raw_results as $row) {
        $comando_regional = $row['comando_regional_nome'];
        $municipio = $row['municipio_nome'];
        $unidade = $row['unidade_nome'];

        if (!isset($dados_localizacao[$comando_regional])) {
            $dados_localizacao[$comando_regional] = [];
        }
        if (!isset($dados_localizacao[$comando_regional][$municipio])) {
            $dados_localizacao[$comando_regional][$municipio] = [];
        }
        if (!isset($dados_localizacao[$comando_regional][$municipio][$unidade])) {
            $dados_localizacao[$comando_regional][$municipio][$unidade] = [];
        }
        $dados_localizacao[$comando_regional][$municipio][$unidade][] = $row;
    }

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar dados de localização: " . $e->getMessage() . "</div>";
}
*/
?>

<div class="d-flex min-vh-100 w-100" style="padding-bottom: 3rem;">
    <div class="row justify-content-center w-100" style="padding-top: 3rem;">
        <div class=" col-12 col-md-8 col-lg-6">
            <div class="content-area">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <h1 class="mb-3">Localização das Viaturas</h1>
                        <p class="lead">Visualize a distribuição das viaturas por Comando Regional, Município e Unidade.</p>
                        <hr class="mb-4">
                    </div>
                </div>

                <?php
                // Dados de exemplo para demonstração SEM o backend PHP ativo
                // Remova este bloco quando o PHP de backend estiver funcional
                $dados_localizacao = [
                    'Comando Regional I' => [
                        'Cuiabá' => [
                            '1º Batalhão' => [
                                [
                                    'placa' => 'ABC1234',
                                    'prefixo' => 'VTR01',
                                    'numero' => '001',
                                    'modelo' => 'Toyota Hilux',
                                    'ano_fabricacao' => '2023',
                                    'latitude' => '-15.6015', // Exemplo Cuiabá
                                    'longitude' => '-56.0979',
                                    'comando_regional_nome' => 'Comando Regional I',
                                    'municipio_nome' => 'Cuiabá',
                                    'unidade_nome' => '1º Batalhão'
                                ],
                                [
                                    'placa' => 'DEF5678',
                                    'prefixo' => 'VTR02',
                                    'numero' => '002',
                                    'modelo' => 'Chevrolet S10',
                                    'ano_fabricacao' => '2022',
                                    'latitude' => '-15.5975',
                                    'longitude' => '-56.0950',
                                    'comando_regional_nome' => 'Comando Regional I',
                                    'municipio_nome' => 'Cuiabá',
                                    'unidade_nome' => '1º Batalhão'
                                ]
                            ],
                            '2º Batalhão' => [
                                [
                                    'placa' => 'GHI9012',
                                    'prefixo' => 'VTR03',
                                    'numero' => '003',
                                    'modelo' => 'Renault Duster',
                                    'ano_fabricacao' => '2024',
                                    'latitude' => '-15.5990',
                                    'longitude' => '-56.0965',
                                    'comando_regional_nome' => 'Comando Regional I',
                                    'municipio_nome' => 'Cuiabá',
                                    'unidade_nome' => '2º Batalhão'
                                ]
                            ]
                        ],
                        'Várzea Grande' => [
                            '3º Batalhão' => [
                                [
                                    'placa' => 'JKL3456',
                                    'prefixo' => 'VTR04',
                                    'numero' => '004',
                                    'modelo' => 'Fiat Cronos',
                                    'ano_fabricacao' => '2021',
                                    'latitude' => '-15.6540', // Exemplo Várzea Grande
                                    'longitude' => '-56.1280',
                                    'comando_regional_nome' => 'Comando Regional I',
                                    'municipio_nome' => 'Várzea Grande',
                                    'unidade_nome' => '3º Batalhão'
                                ]
                            ]
                        ]
                    ],
                    'Comando Regional II' => [
                        'Rondonópolis' => [
                            '4º Batalhão' => [
                                [
                                    'placa' => 'MNO7890',
                                    'prefixo' => 'VTR05',
                                    'numero' => '005',
                                    'modelo' => 'Volkswagen Virtus',
                                    'ano_fabricacao' => '2023',
                                    'latitude' => '-16.4673', // Exemplo Rondonópolis
                                    'longitude' => '-54.6369',
                                    'comando_regional_nome' => 'Comando Regional II',
                                    'municipio_nome' => 'Rondonópolis',
                                    'unidade_nome' => '4º Batalhão'
                                ]
                            ]
                        ]
                    ]
                ];
                // Fim do bloco de dados de exemplo
                ?>

                <?php if (empty($dados_localizacao)): ?>
                    <div class="alert alert-info" role="alert">
                        Nenhuma viatura encontrada ou dados de localização não disponíveis.
                    </div>
                <?php else: ?>
                    <?php foreach ($dados_localizacao as $comando_regional_nome => $municipios): ?>
                        <div class="card mb-4 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h3 class="mb-0">Comando Regional: <?= htmlspecialchars($comando_regional_nome) ?></h3>
                            </div>
                            <div class="card-body">
                                <?php foreach ($municipios as $municipio_nome => $unidades): ?>
                                    <div class="card mb-3 border-secondary">
                                        <div class="card-header bg-secondary text-white">
                                            <h4 class="mb-0">Município: <?= htmlspecialchars($municipio_nome) ?></h4>
                                        </div>
                                        <div class="card-body">
                                            <?php foreach ($unidades as $unidade_nome => $viaturas): ?>
                                                <div class="card mb-3 border-info">
                                                    <div class="card-header bg-info text-white">
                                                        <h5 class="mb-0">Unidade: <?= htmlspecialchars($unidade_nome) ?></h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row row-cols-1 row-cols-md-2 g-4">
                                                            <?php foreach ($viaturas as $viatura): ?>
                                                                <div class="col">
                                                                    <div class="card h-100 shadow-sm">
                                                                        <div class="card-body">
                                                                            <h6 class="card-title text-primary">Viatura:
                                                                                <?= htmlspecialchars($viatura['placa'] ?: ($viatura['prefixo'] . ' - ' . $viatura['numero'])) ?>
                                                                            </h6>
                                                                            <ul class="list-unstyled mb-0">
                                                                                <li>**Placa:** <?= htmlspecialchars($viatura['placa'] ?? 'N/A') ?></li>
                                                                                <?php if (!empty($viatura['prefixo'])): ?>
                                                                                    <li>**Prefixo:** <?= htmlspecialchars($viatura['prefixo']) ?></li>
                                                                                <?php endif; ?>
                                                                                <?php if (!empty($viatura['numero'])): ?>
                                                                                    <li>**Número:** <?= htmlspecialchars($viatura['numero']) ?></li>
                                                                                <?php endif; ?>
                                                                                <li>**Modelo:** <?= htmlspecialchars($viatura['modelo'] ?? 'N/A') ?></li>
                                                                                <li>**Ano:** <?= htmlspecialchars($viatura['ano_fabricacao'] ?? 'N/A') ?></li>
                                                                                <?php if (!empty($viatura['latitude']) && !empty($viatura['longitude'])): ?>
                                                                                    <li>**Localização:** <a href="https://www.google.com/maps/search/?api=1&query=<?= htmlspecialchars($viatura['latitude']) ?>,<?= htmlspecialchars($viatura['longitude']) ?>" target="_blank" class="text-decoration-none">Ver no Mapa <i class="fas fa-external-link-alt ms-1"></i></a></li>
                                                                                <?php endif; ?>
                                                                            </ul>
                                                                        </div>
                                                                        <?php if (!empty($viatura['latitude']) && !empty($viatura['longitude'])): ?>
                                                                            <div class="card-footer bg-light">
                                                                                <small class="text-muted">Coordenadas: <?= htmlspecialchars($viatura['latitude']) ?>, <?= htmlspecialchars($viatura['longitude']) ?></small>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<div class="b-example-divider b-example-vr"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<?php

include_once '../templates/footer.php';

?>