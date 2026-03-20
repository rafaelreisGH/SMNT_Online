// ARQUIVO: main-ajax.js

/**
 * Sistema Global de Toasts (Versão Minimalista)
 */
window.showToast = function (message, type = 'success') {
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }

    // Define a cor de fundo e garante contraste do texto (text-bg-*)
    const bgColorClass = {
        success: 'text-bg-success',
        danger: 'text-bg-danger',
        warning: 'text-bg-warning',
        info: 'text-bg-info'
    }[type] || 'text-bg-primary';

    const toastId = `toast-${Date.now()}`;

    // Estrutura simplificada: Texto à esquerda e botão de fechar à direita, sem ícones.
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center ${bgColorClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>`;

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastEl = document.getElementById(toastId);

    const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
    toast.show();

    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

/**
 * Função para atualizar visualmente o Menu Lateral (Sidebar)
 */
window.updateActiveSidebar = function (url) {
    document.querySelectorAll('#accordionSidebar .nav-item').forEach(li => li.classList.remove('active'));
    document.querySelectorAll('#accordionSidebar .collapse-item').forEach(a => a.classList.remove('active'));

    const cleanUrl = url.split('?')[0];

    document.querySelectorAll('#accordionSidebar a').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.includes(cleanUrl)) {
            if (link.classList.contains('collapse-item')) {
                link.classList.add('active');
                const collapseDiv = link.closest('.collapse');
                if (collapseDiv) {
                    const bsCollapse = new bootstrap.Collapse(collapseDiv, { toggle: false });
                    bsCollapse.show();
                    const parentLi = collapseDiv.closest('.nav-item');
                    if (parentLi) parentLi.classList.add('active');
                }
            } else {
                const parentLi = link.closest('.nav-item');
                if (parentLi) parentLi.classList.add('active');
            }
        }
    });
};

/**
 * Função de Navegação Global (SPA)
 */
window.loadPage = function (url) {
    const token = localStorage.getItem('smnt_token');
    const mainContentArea = document.getElementById('main-content-area');

    if (!mainContentArea) return;

    window.updateActiveSidebar(url);

    mainContentArea.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x text-danger"></i><br><small>Carregando...</small></div>';

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

            const scripts = mainContentArea.querySelectorAll('script');
            scripts.forEach(script => {
                const newScript = document.createElement('script');
                newScript.textContent = script.textContent;
                document.body.appendChild(newScript).parentNode.removeChild(newScript);
            });
        })
        .catch(error => {
            console.error('Erro ao carregar página:', error);
            mainContentArea.innerHTML = '<div class="alert alert-danger p-4">Erro de rede ao carregar a página.</div>';
        });
};

/**
 * Atualiza o ícone do SINO (Notificações de Sistema)
 */
window.atualizarNotificacoes = function () {
    const token = localStorage.getItem('smnt_token');
    const contador = document.getElementById('notificacao-contador');
    const container = document.getElementById('container-notificacoes-itens');
    if (!token || !contador || !container) return;

    fetch('api/get_notificacoes.php', {
        headers: { 'Authorization': 'Bearer ' + token }
    })
        .then(r => r.json())
        .then(data => {
            if (data.total > 0) {
                contador.innerText = data.total > 9 ? '9+' : data.total;
                contador.classList.remove('d-none');
                let html = '';
                data.notificacoes.forEach(n => {
                    const iconClass = n.tipo === 'avaria' ? 'bg-danger' : 'bg-warning';
                    html += `
                <a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="clicarNotificacao(${n.id}, '${n.link_id}')">
                    <div class="mr-3"><div class="icon-circle ${iconClass} text-white"><i class="fas fa-exclamation-triangle"></i></div></div>
                    <div><div class="small text-gray-500">${new Date(n.criada_em).toLocaleString()}</div><span class="font-weight-bold">${n.titulo}</span></div>
                </a>`;
                });
                container.innerHTML = html;
            } else {
                contador.classList.add('d-none');
                container.innerHTML = '<p class="small text-center p-3 text-muted m-0">Sem notificações.</p>';
            }
        });
};

/**
 * Atualiza o ícone da CARTA (Mensagens entre usuários)
 */
window.atualizarMensagensTopbar = function () {
    const token = localStorage.getItem('smnt_token');
    const contador = document.getElementById('msg-contador');
    const container = document.getElementById('container-mensagens-itens');
    if (!token || !contador || !container) return;

    fetch('api/processar_mensagens.php?acao=listar&lida=0&pasta=entrada', {
        headers: { 'Authorization': 'Bearer ' + token }
    })
        .then(r => r.json())
        .then(data => {
            const mensagens = data.mensagens || [];
            const total = mensagens.length;
            if (total > 0) {
                contador.innerText = total > 9 ? '9+' : total;
                contador.classList.remove('d-none');
                let html = '';
                mensagens.slice(0, 4).forEach(m => {
                    html += `
                <a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="window.loadPage('pages/mensagens.php?id=${m.id}')">
                    <div class="dropdown-list-image mr-3">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width:40px; height:40px"><i class="fas fa-user"></i></div>
                    </div>
                    <div class="font-weight-bold">
                        <div class="text-truncate">${m.assunto || '(Sem assunto)'}</div>
                        <div class="small text-gray-500">${m.remetente_nome || 'Desconhecido'}</div>
                    </div>
                </a>`;
                });
                container.innerHTML = html;
            } else {
                contador.classList.add('d-none');
                container.innerHTML = '<p class="small text-center p-3 text-muted m-0">Nenhuma mensagem nova.</p>';
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
        if (link) window.loadPage(link);
        atualizarNotificacoes();
    });
};

/**
 * Item 2: Atualiza o nome e a foto do usuário no Topbar
 */
window.updateUIUserInfo = function () {
    const nome = localStorage.getItem('user_nome');
    const perfil = localStorage.getItem('user_perfil');
    const foto = localStorage.getItem('user_foto');

    const displayNome = document.getElementById('user-name-display');
    const displayFoto = document.getElementById('user-img-topbar');

    if (displayNome && nome) {
        const primeiroNome = nome.split(' ')[0];
        displayNome.innerText = `${primeiroNome} (${perfil})`;
    }

    if (displayFoto) {
        if (foto && foto !== 'null' && foto !== '' && foto !== 'undraw_profile.svg') {
            displayFoto.src = `assets/uploads/perfil/${foto}?v=${Date.now()}`;
        } else {
            displayFoto.src = `assets/uploads/perfil/undraw_profile.svg`;
        }
    }
}

window.handleLogout = function () {
    localStorage.clear();
    window.location.href = 'login.php';
};

/**
 * Inicialização ao carregar o DOM
 */
document.addEventListener('DOMContentLoaded', function () {
    window.updateUIUserInfo();
    atualizarNotificacoes();
    atualizarMensagensTopbar();
    if (typeof initGlobalSearch === 'function') initGlobalSearch();

    setInterval(atualizarNotificacoes, 120000);
    setInterval(atualizarMensagensTopbar, 120000);

    document.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (link && link.getAttribute('href')) {
            const href = link.getAttribute('href');
            if (href.startsWith('#') || href.startsWith('javascript:') || link.dataset.toggle === 'collapse' || link.dataset.bsToggle) return;

            if (link.target !== '_blank') {
                e.preventDefault();
                window.loadPage(href);
            }
        }
    });
});