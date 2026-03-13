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