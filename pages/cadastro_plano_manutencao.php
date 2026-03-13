<?php
// pages/cadastro_plano_manutencao.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/PlanoManutencaoDAO.php';

$dao = new PlanoManutencaoDAO();
$itens = $dao->listarItensPreventiva();
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Novo Plano de Manutenção</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Definição de Periodicidades Técnicas</h6>
                </div>
                <div class="card-body">
                    <form id="formPlano">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Descrição do Plano</label>
                            <input type="text" name="descricao" class="form-control" placeholder="Ex: Plano Padrão Hilux 2021/2024" required>
                            <small class="text-muted">Este nome será usado para vincular o plano às viaturas.</small>
                        </div>

                        <h6 class="fw-bold border-bottom pb-2 mb-3">Regras do Plano</h6>
                        <div id="container-regras">
                            <!-- Template de uma regra -->
                            <div class="row g-2 align-items-end mb-3 regra-item border-bottom pb-3">
                                <div class="col-md-4">
                                    <label class="form-label small">Item / Serviço</label>
                                    <select class="form-select select-item" required>
                                        <option value="">Selecione...</option>
                                        <?php foreach($itens as $i): ?>
                                            <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">KM</label>
                                    <input type="number" class="form-control input-km" placeholder="A cada X km">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Horas</label>
                                    <input type="number" class="form-control input-horas" placeholder="Horímetro">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Meses</label>
                                    <input type="number" class="form-control input-meses" placeholder="Tempo">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 btn-remover-regra" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="btnAdicionarRegra" class="btn btn-sm btn-outline-primary mb-4">
                            <i class="fas fa-plus me-1"></i> Adicionar Outro Item
                        </button>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-success px-5">Salvar Plano Completo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const container = document.getElementById('container-regras');
    const btnAdd = document.getElementById('btnAdicionarRegra');

    // Função para clonar a linha de regra
    btnAdd.addEventListener('click', () => {
        const primeiraRegra = document.querySelector('.regra-item');
        const novaRegra = primeiraRegra.cloneNode(true);
        
        // Limpa os campos clonados
        novaRegra.querySelectorAll('input').forEach(i => i.value = '');
        novaRegra.querySelector('select').value = '';
        novaRegra.querySelector('.btn-remover-regra').disabled = false;
        
        container.appendChild(novaRegra);
    });

    // Delegar evento de remover
    container.addEventListener('click', (e) => {
        if (e.target.closest('.btn-remover-regra')) {
            e.target.closest('.regra-item').remove();
        }
    });

    // Submissão
    document.getElementById('formPlano').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const data = {
            descricao: this.descricao.value,
            regras: []
        };

        // Captura todas as linhas de regras
        document.querySelectorAll('.regra-item').forEach(el => {
            data.regras.push({
                item_id: el.querySelector('.select-item').value,
                km: el.querySelector('.input-km').value,
                horas: el.querySelector('.input-horas').value,
                meses: el.querySelector('.input-meses').value
            });
        });

        // Envia para a API (usando o fetch configurado no main-ajax.js)
        fetch('api/processar_plano_manutencao.php', {
            method: 'POST',
            headers: { 
                'Authorization': 'Bearer ' + localStorage.getItem('smnt_token'),
                'Content-Type': 'application/x-www-form-urlencoded' 
            },
            body: new URLSearchParams({
                descricao: data.descricao,
                regras: JSON.stringify(data.regras) // Enviamos como string JSON para facilitar
            })
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                showToast(res.message, 'success');
                loadPage('pages/gestao_planos_lista.php'); // Redireciona para a lista
            } else {
                showToast(res.message, 'danger');
            }
        });
    });
})();
</script>