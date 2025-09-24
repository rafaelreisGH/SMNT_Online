document.addEventListener('DOMContentLoaded', function () {
    const mainContentArea = document.getElementById('main-content-area');
    const sidebarLinks = document.querySelectorAll('#accordionSidebar a.collapse-item');

    // Função para carregar o conteúdo via AJAX
    function loadPage(url) {
        // Exibe um ícone de carregamento para melhor experiência do usuário
        mainContentArea.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i> Carregando...</div>';

        // Usa a API Fetch para fazer a requisição AJAX
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text(); // Recebe o HTML como texto
            })
            .then(html => {
                // Substitui o conteúdo da área principal com o HTML recebido
                mainContentArea.innerHTML = html;
            })
            .catch(error => {
                // Exibe uma mensagem de erro em caso de falha
                mainContentArea.innerHTML = '<div class="alert alert-danger p-4">Erro ao carregar a página.</div>';
                console.error('AJAX Load Error:', error);
            });
    }

    // Intercepta os cliques nos links da sidebar
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const url = this.getAttribute('href');

            // Verifica se é um link de página e não um link de colapso
            if (url && url !== '#') {
                e.preventDefault(); // Impede o recarregamento completo da página
                loadPage(url);
            }
        });
    });
});
