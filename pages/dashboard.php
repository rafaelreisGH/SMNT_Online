<?php
// pages/dashboard.php
require_once __DIR__ . '/../api/middleware_auth.php';
?>
<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Painel de Controle Operacional</h1>

    <!-- Row de Cards de Resumo Numérico -->
    <div class="row" id="row-cards-resumo">
        <!-- Preenchido via JS -->
    </div>

    <div class="row">
        <!-- 1. Preventiva Vencida -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 border-bottom-danger">
                <div class="card-header py-3 bg-danger d-flex align-items-center">
                    <h6 class="m-0 font-weight-bold text-white small">PREVENTIVA VENCIDA</h6>
                </div>
                <div class="card-body p-0" id="lista-vencidos" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i></div>
                </div>
            </div>
        </div>

        <!-- 2. Avarias Pendentes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 border-bottom-warning">
                <div class="card-header py-3 bg-warning d-flex align-items-center">
                    <h6 class="m-0 font-weight-bold text-dark small">AVARIAS PENDENTES</h6>
                </div>
                <div class="card-body p-0" id="lista-avarias" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i></div>
                </div>
            </div>
        </div>

        <!-- 3. Na Oficina -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 border-bottom-info">
                <div class="card-header py-3 bg-info d-flex align-items-center">
                    <h6 class="m-0 font-weight-bold text-white small">NA OFICINA / ANDAMENTO</h6>
                </div>
                <div class="card-body p-0" id="lista-andamento" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i></div>
                </div>
            </div>
        </div>

        <!-- 4. Em Trânsito (NOVO) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 border-bottom-secondary">
                <div class="card-header py-3 bg-secondary d-flex align-items-center">
                    <h6 class="m-0 font-weight-bold text-white small">VIATURAS EM TRÂNSITO</h6>
                </div>
                <div class="card-body p-0" id="lista-transito" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        function initDashboard() {
            fetch('api/get_dashboard_full.php', {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    renderCards(data.resumo);
                    renderVencidos(data.vencidos);
                    renderAvarias(data.avarias);
                    renderAndamento(data.andamento);
                    renderTransito(data.transito); // Chama a nova função
                })
                .catch(err => console.error("Erro dashboard:", err));
        }

        function renderCards(res) {
            document.getElementById('row-cards-resumo').innerHTML = `
                <div class="col-md-4 mb-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Viaturas Baixadas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${res.baixadas || 0}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Em Manutenção</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${res.em_manutencao || 0}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Viaturas Operantes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">${res.operantes || 0}</div>
                        </div>
                    </div>
                </div>`;
        }

        function renderVencidos(lista) {
            const div = document.getElementById('lista-vencidos');
            if (!lista || lista.length === 0) {
                div.innerHTML = '<p class="p-3 text-muted text-center">Tudo em dia.</p>';
                return;
            }

            let html = '<div class="list-group list-group-flush">';
            lista.forEach(v => {
                html += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-dark">${v.prefixo} ${v.numero}</span><br>
                        <small class="text-muted">${v.item}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" 
                            onclick="loadPage('pages/registrar_manutencao.php?viatura_id=${v.viatura_id}&item_id=${v.item_id}')">
                        Resolver
                    </button>
                </div>`;
            });
            div.innerHTML = html + '</div>';
        }

        function renderAvarias(lista) {
            const div = document.getElementById('lista-avarias');
            if (!lista || lista.length === 0) {
                div.innerHTML = '<p class="p-3 text-muted text-center">Sem avarias pendentes.</p>';
                return;
            }

            let html = '<div class="list-group list-group-flush">';
            lista.forEach(a => {
                html += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">${a.prefixo} ${a.numero}</span><br>
                        <small class="text-truncate d-inline-block" style="max-width: 120px;">${a.nome_componente}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-warning fw-bold" title="Iniciar Ordem de Serviço"
                            onclick="loadPage('pages/registrar_manutencao.php?avaria_id=${a.id}')">
                        Abrir OS
                    </button>
                </div>`;
            });
            div.innerHTML = html + '</div>';
        }

        function renderAndamento(lista) {
            const div = document.getElementById('lista-andamento');
            if (!lista || lista.length === 0) {
                div.innerHTML = '<p class="p-3 text-muted text-center">Nenhuma viatura na oficina.</p>';
                return;
            }

            let html = '<div class="list-group list-group-flush">';
            lista.forEach(m => {
                html += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">${m.prefixo} ${m.numero}</span><br>
                        <small class="text-primary">${m.oficina || 'Oficina...'}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-info fw-bold" title="Finalizar Saída"
                            onclick="loadPage('pages/finalizar_manutencao.php?id=${m.id}')">
                        Finalizar
                    </button>
                </div>`;
            });
            div.innerHTML = html + '</div>';
        }

        // NOVA FUNÇÃO: Renderiza Viaturas em Trânsito
        function renderTransito(lista) {
            const div = document.getElementById('lista-transito');
            if (!lista || lista.length === 0) {
                div.innerHTML = '<p class="p-3 text-muted text-center">Nenhuma viatura em trânsito.</p>';
                return;
            }

            let html = '<div class="list-group list-group-flush">';
            lista.forEach(t => {
                html += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">${t.prefixo} ${t.numero}</span><br>
                        <small class="text-muted">Destino: ${t.unidade_destino}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" title="Confirmar Chegada"
                            onclick="loadPage('pages/confirmar_recebimento.php')">
                        <i class="fas fa-truck-moving"></i>
                    </button>
                </div>`;
            });
            div.innerHTML = html + '</div>';
        }

        initDashboard();
    })();
</script>