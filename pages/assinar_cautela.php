<?php
// pages/assinar_cautela.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/CautelaDAO.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$dao = new CautelaDAO();
$cautela = $dao->getCautelaById($id);

if (!$cautela || $cautela['status'] !== 'Disponível') {
    echo "<div class='alert alert-warning'>Esta cautela não está disponível para assinatura.</div>";
    exit;
}
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Assinatura de Cautela</h1>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow mb-4 border-left-danger">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-file-contract me-2"></i>Termo de Responsabilidade</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border small text-dark mb-4">
                        <strong>Viatura:</strong> <?= htmlspecialchars($cautela['prefixo'].' '.$cautela['numero']) ?><br>
                        <strong>Odômetro de Saída:</strong> <?= $cautela['odometro_saida'] ?> km<br>
                        <strong>Combustível:</strong> <?= $cautela['combustivel_saida'] ?>
                    </div>

                    <h6><strong>CONDIÇÕES GERAIS DO FORNECIMENTO:</strong></h6>
                    <p class="small text-muted" style="text-align: justify;">
                        a) Utilizar o veículo dentro das normas de boa condução e leis de trânsito;<br>
                        b) Zelar pela manutenção, estado e conservação do veículo e acessórios;<br>
                        c) Devolver o veículo no mesmo estado em que recebeu, ressalvado o desgaste natural;<br>
                        d) Assumir inteira responsabilidade por infrações ou danos durante a condução.
                    </p>

                    <hr>

                    <form id="formAssinarCautela">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Confirme sua senha para assinar:</label>
                            <input type="password" name="senha_assinatura" class="form-control form-control-lg border-danger" required>
                        </div>
                        <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold">
                            <i class="fas fa-pen-fancy me-2"></i>ACEITAR E ASSINAR CAUTELA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        document.getElementById('formAssinarCautela').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;

            fetch('api/processar_cautela.php?acao=assinar', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + localStorage.getItem('smnt_token') },
                body: new FormData(this)
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    window.showToast(data.message, 'success');
                    setTimeout(() => loadPage('pages/pesquisar_cautelas.php'), 1200);
                } else {
                    window.showToast(data.message, 'danger');
                    btn.disabled = false;
                }
            });
        });
    })();
</script>