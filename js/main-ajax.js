// ARQUIVO: main-ajax.js (VERSÃO FINAL CORRIGIDA)

/**
 * Torna a função showToast global para que outros scripts possam usá-la.
 * Assim, qualquer página carregada pode chamar window.showToast(...)
 */
window.showToast = function (message, type = 'success') {
    // Tenta encontrar um container de toast na página. Se não houver, cria um.
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

    /**
     * Inicializa componentes do Bootstrap (como Tooltips) em um container específico.
     * @param {HTMLElement} container O elemento dentro do qual os componentes serão procurados.
     */
    function initializeBootstrapComponents(container) {
        // Inicializa Tooltips
        const tooltipTriggerList = Array.from(container.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        // Adicione outros inicializadores aqui se precisar (Popovers, etc.)
    }

    function loadPage(url) {
        mainContentArea.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i> Carregando...</div>';

        fetch(url)
            .then(response => response.ok ? response.text() : Promise.reject(response.statusText))
            .then(html => {
                mainContentArea.innerHTML = html;

                // FLUXO CORRETO DE INICIALIZAÇÃO
                executeScriptsInElement(mainContentArea);
                initializeBootstrapComponents(mainContentArea); // <<-- ADICIONADO AQUI
            })
            .catch(error => {
                mainContentArea.innerHTML = '<div class="alert alert-danger p-4">Erro ao carregar a página.</div>';
                console.error('AJAX Load Error:', error);
            });
    }

    // Listener de clique global (Delegação de Eventos)
    document.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (link && link.href && !link.href.endsWith('#') && !link.dataset.bsToggle && link.target !== '_blank') {
            e.preventDefault();
            if (link.closest('#accordionSidebar')) {
                updateActiveSidebar(link);
            }
            loadPage(link.getAttribute('href'));
        }
    });

    // Inicializa os componentes na página principal quando ela carrega pela primeira vez
    initializeBootstrapComponents(document.body); // <<-- ADICIONADO AQUI
});