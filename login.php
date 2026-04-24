<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMnt Online - Acesso</title>
    <!-- Usando a versão do Bootstrap solicitada -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <!-- Font Awesome para ícones -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body class="d-flex align-items-center min-vh-100 bg-light">

    <div class="container">
        <div class="row w-100">
            <div class="col-md-5 mx-auto text-center">
                <!-- Logo do CBM-MT -->
                <img class="img-fluid mx-auto d-none d-sm-block mb-3" src="assets/images/CBMMT_00.png" alt="Logo CBM-MT" width="150">

                <h1 class="mb-3 fw-semibold lh-1">
                    <p>CSM Online</p>
                    <p class="h4 text-secondary">Sistema de Gestão de Manutenção</p>
                </h1>

                <p class="lead mb-4 small text-secondary">
                    Plataforma centralizada para o controle e gestão de manutenções do CBMMT. Tecnologia para garantir a segurança e a continuidade das operações.
                </p>

                <main class="form-signin w-75 m-auto">
                    <form id="loginForm">
                        <br>
                        <!-- Campo CPF com Floating Label -->
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="cpf" name="cpf" placeholder="00011122233" required autocomplete="username">
                            <label for="cpf"><i class="fas fa-user me-2"></i>CPF (apenas números)</label>
                        </div>

                        <!-- Campo Senha com Floating Label -->
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required autocomplete="current-password">
                            <label for="senha"><i class="fas fa-lock me-2"></i>Senha</label>
                        </div>

                        <div class="form-check text-start my-3">
                            <input class="form-check-input" type="checkbox" value="remember-me" id="checkDefault">
                            <label class="form-check-label text-secondary" for="checkDefault">Lembrar-me</label>
                        </div>

                        <button class="btn btn-outline-danger w-100 py-2 fw-bold" type="submit" id="btnAcessar">
                            <span id="btnText">Acessar</span>
                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>

                        <!-- Área para Mensagens de Erro/Alerta -->
                        <div id="mensagem" class="mt-3 small"></div>

                        <p class="mt-5 mb-3 text-body-secondary small">STIC/DAI &copy; <?= date('Y') ?></p>
                    </form>
                </main>
            </div>
        </div>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = document.getElementById('btnAcessar');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            const msgDiv = document.getElementById('mensagem');
            const formData = new FormData(this);

            // Estado de carregamento
            btn.disabled = true;
            btnText.textContent = 'Autenticando...';
            btnSpinner.classList.remove('d-none');
            msgDiv.innerHTML = '';

            fetch('api/auth_login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 1. Armazena o Token JWT e info básica
                        localStorage.setItem('smnt_token', data.token);
                        localStorage.setItem('user_nome', data.user.nome);
                        localStorage.setItem('user_perfil', data.user.perfil);
                        localStorage.setItem('user_foto', data.user.foto);

                        msgDiv.innerHTML = `<div class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Acesso autorizado! Redirecionando...</div>`;

                        // 2. Redirecionamento baseado na necessidade de trocar senha
                        setTimeout(() => {
                            if (data.require_new_password) {
                                window.location.href = 'pages/primeiro_acesso.php';
                            } else {
                                window.location.href = 'principal.php';
                            }
                        }, 1000);

                    } else {
                        // Erro de autenticação
                        btn.disabled = false;
                        btnText.textContent = 'Acessar';
                        btnSpinner.classList.add('d-none');
                        msgDiv.innerHTML = `<div class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i> ${data.message}</div>`;
                    }
                })
                .catch(err => {
                    console.error("Erro no login:", err);
                    btn.disabled = false;
                    btnText.textContent = 'Acessar';
                    btnSpinner.classList.add('d-none');
                    msgDiv.innerHTML = `<div class="text-danger fw-bold">Erro de comunicação com o servidor.</div>`;
                });
        });
    </script>
</body>

</html>