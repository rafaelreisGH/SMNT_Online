<?php
// pages/notificacoes.php
require_once __DIR__ . '/../api/middleware_auth.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 mb-0 text-gray-800">Centro de Notificações</h1>
        <button class="btn btn-sm btn-outline-secondary" onclick="atualizarListaNotificacoes()">
            <i class="fas fa-sync-alt me-1"></i> Atualizar
        </button>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Histórico Recente</h6>
                </div>
                <div class="card-body p-0">
                    <div id="lista-historico-notificacoes" class="list-group list-group-flush">
                        <div class="text-center p-5">
                            <i class="fas fa-spinner fa-spin fa-2x text-secondary"></i>
                            <br><small class="text-muted">Buscando histórico...</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // Função para carregar a lista de notificações
        window.atualizarListaNotificacoes = function() {
            const container = document.getElementById('lista-historico-notificacoes');
            if (!container) return;

            container.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x text-secondary"></i><br><small class="text-muted">Buscando histórico...</small></div>';

            fetch('api/get_notificacoes.php?todas=true', {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (response.status === 401) {
                        window.location.href = 'login.php';
                        return;
                    }
                    return response.json();
                })
                .then(data => {
                    // Debug para você ver o que está voltando no console (F12)
                    console.log("Dados recebidos:", data);

                    if (!data.notificacoes || data.notificacoes.length === 0) {
                        container.innerHTML = `
                            <div class="text-center p-5">
                                <i class="fas fa-bell-slash fa-3x text-gray-200 mb-3"></i>
                                <p class="text-muted">Você ainda não possui nenhuma notificação no seu histórico.</p>
                            </div>`;
                        return;
                    }

                    let html = '';
                    data.notificacoes.forEach(n => {
                        const lidaClass = n.lida == 1 ? 'opacity-50 bg-white' : 'bg-light border-left-danger fw-bold';
                        const iconClass = n.tipo === 'avaria' ? 'bg-danger' : (n.tipo === 'vencimento' ? 'bg-warning' : 'bg-info');
                        const icon = n.tipo === 'avaria' ? 'fa-exclamation-triangle' : 'fa-wrench';

                        html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center ${lidaClass}">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle ${iconClass} text-white me-3 p-2 rounded-circle shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas ${icon}"></i>
                                </div>
                                <div>
                                    <div class="small text-gray-500">${new Date(n.criada_em).toLocaleString('pt-BR')}</div>
                                    <div class="text-dark">${htmlspecialchars(n.titulo ?? '')}</div>
                                    <div class="small text-muted">${htmlspecialchars(n.mensagem ?? '')}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                ${n.link_id ? `<button class="btn btn-sm btn-primary" onclick="clicarNotificacao(${n.id}, '${n.link_id}')">Acessar</button>` : ''}
                                ${n.lida == 0 ? `<button class="btn btn-sm btn-light border" onclick="marcarComoLidaSimples(${n.id})" title="Marcar como lida"><i class="fas fa-check"></i></button>` : ''}
                            </div>
                        </div>`;
                    });
                    container.innerHTML = html;
                })
                .catch(err => {
                    console.error("Erro no histórico:", err);
                    container.innerHTML = '<div class="alert alert-danger m-3">Erro ao carregar notificações. Verifique o console.</div>';
                });
        };

        // Função local para marcar como lida sem sair da página
        window.marcarComoLidaSimples = function(id) {
            const fd = new FormData();
            fd.append('id', id);
            fd.append('marcar_lida', 1);

            fetch('api/get_notificacoes.php', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                },
                body: fd
            }).then(() => {
                atualizarListaNotificacoes();
                if (typeof window.atualizarNotificacoes === 'function') window.atualizarNotificacoes(); // Atualiza o sino na sidebar
            });
        };

        // Helper para segurança contra XSS
        function htmlspecialchars(str) {
            if (typeof str !== 'string') return '';
            return str.replace(/[&<>"']/g, function(m) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [m];
            });
        }

        // Carrega os dados assim que a página é aberta
        atualizarListaNotificacoes();
    })();
</script>

<style>
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }

    .border-left-danger {
        border-left: 4px solid #e74a3b !important;
    }

    .opacity-50 {
        opacity: 0.6;
    }
</style>