<?php
include_once __DIR__ . '/templates/sidebar.php';
?>

<!-- AQUI COMEÇA A ÁREA DE CONTEÚDO DINÂMICO -->
<!-- Esta div será o alvo do JavaScript para carregar as páginas -->
<div id="main-content-area" class="container-fluid">
    <?php
    // Inclui a página inicial padrão. A lógica de negócio deve estar dentro dela.
    include_once __DIR__ . '/pages/home.php';
    ?>
</div>
<!-- FIM DA ÁREA DE CONTEÚDO DINÂMICO -->

<?php require_once __DIR__ . '/templates/footer.php'; ?>