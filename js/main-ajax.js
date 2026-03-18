// ARQUIVO: main-ajax.js (VERSÃO COM SUPORTE A JWT E RBAC)

/**
 * Torna a função showToast global para que outros scripts possam usá-la.
 */
window.showToast = function (message, type = 'success') {
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }

    const bgColorClass = { success: 'text-bg-success', danger: 'text-bg-danger', warning: 'text-bg-warning' }[type] || 'text-bg-info';
    const iconClass = { success: 'bi-check-circle-fill', danger: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill' }[type] || 'bi-info-circle-fill';
    const toastId = `liveToast-${Date.now()}`;
    const toastHeader = { success: 'Sucesso!', danger: 'Erro!', warning: 'Atenção!' }[type] || 'Informação';

    const toastHtml = `
        <div id="${toastId}" class="toast ${bgColorClass}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi ${iconClass} me-2"></i>
                <strong class="me-auto">${toastHeader}</strong>
                <small>Agora</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">${message}</div>
        </div>`;

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastEl = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

document.addEventListener('DOMContentLoaded', function () {
    const mainContentArea = document.getElementById('main-content-area');

    function updateActiveSidebar(clickedLink) {
        document.querySelectorAll('#accordionSidebar .nav-item.active').forEach(item => item.classList.remove('active'));
        document.querySelectorAll('#accordionSidebar .collapse-item.active').forEach(item => item.classList.remove('active'));
        const parentLi = clickedLink.closest('.nav-item');
        if (parentLi) parentLi.classList.add('active');
        if (clickedLink.classList.contains('collapse-item')) clickedLink.classList.add('active');
    }

    function executeScriptsInElement(containerElement) {
        const scripts = containerElement.querySelectorAll('script');
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            newScript.textContent = script.textContent;
            document.body.appendChild(newScript).parentNode.removeChild(newScript);
        });
    }

    function initializeBootstrapComponents(container) {
        const tooltipTriggerList = Array.from(container.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }

    /**
     * Carrega uma página via AJAX enviando o Token JWT no cabeçalho
     */
    function loadPage(url) {
        // 1. Recupera o token do localStorage
        const token = localStorage.getItem('smnt_token');

        mainContentArea.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i> Carregando...</div>';

        // 2. Faz a requisição com o Header de Autorização
        fetch(url, {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                // 3. Verifica se o servidor barrou o acesso (Token inválido/ausente)
                if (response.status === 401) {
                    console.warn('Acesso não autorizado. Redirecionando para o login.');
                    localStorage.clear(); // Limpa tokens antigos
                    window.location.href = 'login.php'; // Redireciona para a raiz
                    return;
                }

                if (!response.ok) throw new Error(response.statusText);
                return response.text();
            })
            .then(html => {
                if (!html) return; // Caso tenha redirecionado

                mainContentArea.innerHTML = html;
                executeScriptsInElement(mainContentArea);
                initializeBootstrapComponents(mainContentArea);
            })
            .catch(error => {
                mainContentArea.innerHTML = '<div class="alert alert-danger p-4">Erro ao carregar a página. Verifique sua conexão ou faça login novamente.</div>';
                console.error('AJAX Load Error:', error);
            });

        // Definindo como window.loadPage ela fica visível para os "onclick" do HTML
        window.loadPage = function (url) {
            const token = localStorage.getItem('smnt_token');
            const mainContentArea = document.getElementById('main-content-area'); // Certifique-se que o ID está correto

            mainContentArea.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i> Carregando...</div>';

            fetch(url, {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (response.status === 401) {
                        localStorage.clear();
                        window.location.href = 'login.php';
                        return;
                    }
                    return response.text();
                })
                .then(html => {
                    if (!html) return;
                    mainContentArea.innerHTML = html;

                    // Re-executa os scripts da página carregada
                    const scripts = mainContentArea.querySelectorAll('script');
                    scripts.forEach(script => {
                        const newScript = document.createElement('script');
                        newScript.textContent = script.textContent;
                        document.body.appendChild(newScript).parentNode.removeChild(newScript);
                    });
                })
                .catch(error => {
                    mainContentArea.innerHTML = '<div class="alert alert-danger">Erro ao carregar a página.</div>';
                    console.error('Erro:', error);
                });
        };
    }

    // Listener de clique global (Delegação de Eventos)
    document.addEventListener('click', function (e) {
        const link = e.target.closest('a');

        if (link && link.getAttribute('href')) {
            const href = link.getAttribute('href');

            // Ignora links de âncora ou componentes Bootstrap (como o collapse do menu)
            if (href.startsWith('#') || link.dataset.toggle === 'collapse' || link.dataset.bsToggle) {
                return;
            }

            // Trata como link SPA apenas se não for link externo
            if (link.target !== '_blank') {
                e.preventDefault();
                if (link.closest('#accordionSidebar')) {
                    updateActiveSidebar(link);
                }
                loadPage(href);
            }
        }
    });
});

/**
 * Item 2: Atualiza o nome do usuário no Topbar
 */
function updateUIUserInfo() {
    const nome = localStorage.getItem('user_nome');
    const perfil = localStorage.getItem('user_perfil');
    const displayElement = document.getElementById('user-name-display');

    if (displayElement && nome) {
        // Exibe apenas o primeiro nome para não quebrar o layout, seguido do perfil
        const primeiroNome = nome.split(' ')[0];
        displayElement.innerText = `${primeiroNome} (${perfil})`;
    }
}

/**
 * Item 3: Função de Logout
 */
window.handleLogout = function () {
    // Apaga tudo do localStorage (Token, nome, perfil)
    localStorage.clear();
    // Redireciona para o login que está na raiz
    window.location.href = '../login.php';
};

// Executa a atualização do nome assim que o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    updateUIUserInfo();
});

/**
 * Atualiza o contador de notificações (Sino) no Topbar
 */
function atualizarNotificacoesSino() {
    const token = localStorage.getItem('smnt_token');
    const badgeSino = document.querySelector('#alertsDropdown .badge-counter');

    fetch('api/get_notificacoes_sino.php', {
        headers: { 'Authorization': 'Bearer ' + token }
    })
        .then(r => r.json())
        .then(data => {
            if (data.total > 0) {
                badgeSino.innerText = data.total > 9 ? '9+' : data.total;
                badgeSino.classList.remove('d-none');
            } else {
                badgeSino.classList.add('d-none');
            }
        })
        .catch(err => console.error("Erro ao carregar notificações"));
}

// Chama a cada 5 minutos ou ao trocar de página
setInterval(atualizarNotificacoesSino, 300000);
atualizarNotificacoesSino();

window.atualizarNotificacoes = function () {
    const token = localStorage.getItem('smnt_token');
    if (!token) return;

    fetch('api/get_notificacoes.php', {
        headers: { 'Authorization': 'Bearer ' + token }
    })
        .then(r => r.json())
        .then(data => {
            const contador = document.getElementById('notificacao-contador');
            const container = document.getElementById('container-notificacoes-itens');

            if (data.total > 0) {
                contador.innerText = data.total > 9 ? '9+' : data.total;
                contador.classList.remove('d-none');

                let html = '';
                data.notificacoes.forEach(n => {
                    const iconClass = n.tipo === 'avaria' ? 'bg-danger' : 'bg-warning';
                    const icon = n.tipo === 'avaria' ? 'fa-exclamation-triangle' : 'fa-wrench';

                    html += `
                <a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="clicarNotificacao(${n.id}, '${n.link_id}')">
                    <div class="mr-3">
                        <div class="icon-circle ${iconClass} text-white"><i class="fas ${icon}"></i></div>
                    </div>
                    <div>
                        <div class="small text-gray-500">${new Date(n.criada_em).toLocaleString()}</div>
                        <span class="font-weight-bold">${n.titulo}</span>
                        <div class="small text-truncate" style="max-width: 200px;">${n.mensagem}</div>
                    </div>
                </a>`;
                });
                container.innerHTML = html;
            } else {
                contador.classList.add('d-none');
                container.innerHTML = '<p class="small text-center p-3 text-muted">Nenhuma notificação nova.</p>';
            }
        });
};

window.clicarNotificacao = function (id, link) {
    const fd = new FormData();
    fd.append('id', id);
    fd.append('marcar_lida', 1);

    fetch('api/get_notificacoes.php', {
        method: 'POST',
        headers: { 'Authorization': 'Bearer ' + localStorage.getItem('smnt_token') },
        body: fd
    }).then(() => {
        if (link) loadPage(link);
        atualizarNotificacoes();
    });
};

// Inicia o pooling (ex: a cada 2 minutos)
setInterval(atualizarNotificacoes, 120000);
document.addEventListener('DOMContentLoaded', atualizarNotificacoes);