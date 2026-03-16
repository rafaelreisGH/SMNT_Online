<?php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/ManutencaoDAO.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$mDAO = new ManutencaoDAO();
$m = $mDAO->getManutencaoById($id);

if (!$m || $m['status'] == 'Concluída') {
    echo "<div class='alert alert-warning'>Manutenção já finalizada ou não encontrada.</div>";
    exit;
}
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Finalizar Manutenção (Saída de Oficina)</h1>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Resumo da Entrada: <?= htmlspecialchars($m['viatura_prefixo']) ?></h6>
                </div>
                <div class="card-body">
                    <!-- Informações de Entrada (Apenas leitura) -->
                    <div class="row mb-4 small text-muted">
                        <div class="col-md-4"><strong>Entrada:</strong> <?= date('d/m/Y H:i', strtotime($m['data_entrada'])) ?></div>
                        <div class="col-md-4"><strong>KM Entrada:</strong> <?= number_format($m['quilometragem'], 0, ',', '.') ?></div>
                        <div class="col-md-4"><strong>Oficina:</strong> <?= htmlspecialchars($m['oficina_nome']) ?></div>
                    </div>

                    <form id="formFinalizarManutencao">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">

                        <div class="alert alert-info border-left-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Atenção:</strong> Informe a data e hora em que a viatura <strong>efetivamente saiu</strong> da oficina para que o cálculo de tempo de indisponibilidade seja exato.
                        </div>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label text-danger">Data e Hora da Saída Real</label>
                                <input type="datetime-local" name="data_saida" class="form-control form-control border-danger"
                                    id="data_saida" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Custo Total (R$)</label>
                                <input type="text" name="custo" class="form-control" placeholder="0,00" required id="inputCusto">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Serviço Efetivamente Realizado</label>
                                <textarea class="form-control" name="servico_realizado" rows="3"
                                    placeholder="Descreva as peças trocadas e serviços executados..." required></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Observações Adicionais</label>
                                <textarea class="form-control" name="observacoes" rows="2"></textarea>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-success px-5">
                                <i class="fas fa-check-circle me-2"></i> Finalizar e Liberar Viatura
                            </button>
                            <a href="pages/pesquisar_manutencoes.php" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // Máscara simples para moeda (opcional, pode usar bibliotecas se preferir)
        const inputCusto = document.getElementById('inputCusto');
        inputCusto.addEventListener('blur', function() {
            let val = this.value.replace(/\D/g, "");
            if (val === "") return;
            val = (val / 100).toFixed(2).replace(".", ",");
            this.value = val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        });

        document.getElementById('formFinalizarManutencao').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;

            fetch('api/processar_finalizar_manutencao.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: new FormData(this)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => loadPage('pages/pesquisar_manutencoes.php'), 1000);
                    } else {
                        showToast(data.message, 'danger');
                        btn.disabled = false;
                    }
                });
        });
    })();
</script>