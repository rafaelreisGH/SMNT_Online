document.addEventListener('DOMContentLoaded', function () {
    const mainContentArea = document.getElementById('main-content-area');
    const pageLinks = document.querySelectorAll('#accordionSidebar a[href]:not([href="#"])');

    function updateActiveSidebar(clickedLink) {
        document.querySelectorAll('#accordionSidebar .nav-item.active').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelectorAll('#accordionSidebar .collapse-item.active').forEach(item => {
            item.classList.remove('active');
        });

        const parentLi = clickedLink.closest('.nav-item');
        if (parentLi) {
            parentLi.classList.add('active');
        }
        if (clickedLink.classList.contains('collapse-item')) {
            clickedLink.classList.add('active');
        }
    }

    /**
     * Função para encontrar e executar scripts de um bloco de HTML.
     * @param {HTMLElement} containerElement - O elemento que recebeu o novo HTML.
     */
    function executeScriptsInElement(containerElement) {
        const scripts = containerElement.querySelectorAll('script');
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            // Copia o conteúdo do script original para o novo
            newScript.innerHTML = script.innerHTML;
            // Remove o script antigo do DOM injetado
            script.parentNode.removeChild(script);
            // Adiciona o novo script ao final do 'head' ou 'body' para que ele seja executado pelo navegador.
            document.head.appendChild(newScript);
        });
    }

    // Função para carregar o conteúdo via AJAX (ATUALIZADA)
    function loadPage(url) {
        mainContentArea.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i> Carregando...</div>';

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.text();
            })
            .then(html => {
                // 1. Injeta o HTML na área de conteúdo
                mainContentArea.innerHTML = html;

                // 2. CHAMA A NOVA FUNÇÃO PARA EXECUTAR OS SCRIPTS
                // Isso vai encontrar o <script> em 'pesquisar_viatura.php' e executá-lo.
                executeScriptsInElement(mainContentArea);
            })
            .catch(error => {
                mainContentArea.innerHTML = '<div class="alert alert-danger p-4">Erro ao carregar a página.</div>';
                console.error('AJAX Load Error:', error);
            });
    }

    pageLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            loadPage(url);
            updateActiveSidebar(this);
        });
    });

    // Opcional: Carregar a página inicial na primeira visita
    // loadPage('pages/home.php'); // Descomente se a área de conteúdo começar vazia
});