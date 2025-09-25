document.addEventListener('DOMContentLoaded', function () {
    const mainContentArea = document.getElementById('main-content-area');
    // Alteramos o seletor para pegar TODOS os links que carregam páginas, 
    // não apenas os 'collapse-item'. Isso inclui "Página Inicial", "Mapa de viaturas", etc.
    const pageLinks = document.querySelectorAll('#accordionSidebar a[href]:not([href="#"])');

    /**
     * Função para atualizar a classe 'active' no menu lateral.
     * @param {HTMLElement} clickedLink - O elemento <a> que foi clicado.
     */
    function updateActiveSidebar(clickedLink) {
        // 1. Remove a classe 'active' de todos os itens de navegação (<li>)
        document.querySelectorAll('#accordionSidebar .nav-item.active').forEach(item => {
            item.classList.remove('active');
        });

        // 2. Remove a classe 'active' de todos os sub-itens de menu (<a>)
        document.querySelectorAll('#accordionSidebar .collapse-item.active').forEach(item => {
            item.classList.remove('active');
        });

        // 3. Adiciona a classe 'active' ao <li> pai do link clicado
        const parentLi = clickedLink.closest('.nav-item');
        if (parentLi) {
            parentLi.classList.add('active');
        }

        // 4. Se o link clicado for um sub-item (dentro de um menu collapse),
        // adiciona a classe 'active' a ele também para o destaque visual.
        if (clickedLink.classList.contains('collapse-item')) {
            clickedLink.classList.add('active');
        }
    }

    // Função para carregar o conteúdo via AJAX (sem alterações)
    function loadPage(url) {
        mainContentArea.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i> Carregando...</div>';

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                mainContentArea.innerHTML = html;
            })
            .catch(error => {
                mainContentArea.innerHTML = '<div class="alert alert-danger p-4">Erro ao carregar a página.</div>';
                console.error('AJAX Load Error:', error);
            });
    }

    // Intercepta os cliques nos links
    pageLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault(); // Impede o recarregamento completo da página

            const url = this.getAttribute('href');
            
            // Carrega o conteúdo da página via AJAX
            loadPage(url);
            
            // ATUALIZA O MENU: Chama a nova função para atualizar o estado 'active'
            updateActiveSidebar(this);
        });
    });

    // Opcional: Garante que o item ativo na carga inicial da página esteja correto.
    // O seu HTML já define "Página Inicial" como ativa, então está tudo bem para o primeiro carregamento.
});