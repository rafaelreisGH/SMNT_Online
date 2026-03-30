<?php
// pages/primeiro_acesso.php
?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMnt Online - Primeiro Acesso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body class="d-flex align-items-center min-vh-100 bg-light">

    <div class="container">
        <div class="row w-100">
            <div class="col-md-5 mx-auto text-center">
                <img class="img-fluid mx-auto d-none d-sm-block mb-3" src="../assets/images/CBMMT_00.png" alt="Logo CBM-MT" width="120">
                
                <h1 class="h3 mb-3 fw-semibold">Primeiro Acesso</h1>
                <p class="text-secondary mb-4">
                    Olá, <strong id="nomeUsuario">Usuário</strong>. <br>
                    Por razões de segurança, você deve cadastrar uma nova senha pessoal para continuar.
                </p>

                <main class="form-signin w-75 m-auto">
                    <form id="formTrocaSenha">
                        <div class="form-floating mb-3"> 
                            <input type="password" class="form-control" id="nova_senha" name="nova_senha" placeholder="Nova Senha" required minlength="6"> 
                            <label for="nova_senha"><i class="fas fa-key me-2"></i>Nova Senha</label> 
                        </div>
                        
                        <div class="form-floating mb-3"> 
                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" placeholder="Confirmar Senha" required minlength="6"> 
                            <label for="confirmar_senha"><i class="fas fa-check-double me-2"></i>Confirmar Nova Senha</label> 
                        </div>

                        <button class="btn btn-danger w-100 py-2 fw-bold" type="submit" id="btnSalvar">
                            Atualizar Senha e Acessar
                        </button>

                        <div id="mensagem" class="mt-3 small"></div>
                    </form>
                </main>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // 1. Verifica se o usuário tem o token. Se não tiver, volta pro login.
        const token = localStorage.getItem('smnt_token');
        const nome = localStorage.getItem('user_nome');

        if (!token) {
            window.location.href = '../login.php';
        }

        if (nome) {
            document.getElementById('nomeUsuario').textContent = nome.split(' ')[0]; // Pega só o primeiro nome
        }

        document.getElementById('formTrocaSenha').addEventListener('submit', function(e) {
            e.preventDefault();

            const nSenha = document.getElementById('nova_senha').value;
            const cSenha = document.getElementById('confirmar_senha').value;
            const msgDiv = document.getElementById('mensagem');
            const btn = document.getElementById('btnSalvar');

            // Validação simples de igualdade no frontend
            if (nSenha !== cSenha) {
                msgDiv.innerHTML = `<div class="text-danger fw-bold">As senhas não conferem.</div>`;
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';

            const formData = new FormData(this);

            // 2. Envia para a API enviando o TOKEN no cabeçalho Authorization
            fetch('../api/auth_change_password.php', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    msgDiv.innerHTML = `<div class="text-success fw-bold">Senha atualizada! Redirecionando...</div>`;
                    setTimeout(() => {
                        window.location.href = '../index.php';
                    }, 1500);
                } else {
                    btn.disabled = false;
                    btn.textContent = 'Atualizar Senha e Acessar';
                    msgDiv.innerHTML = `<div class="text-danger fw-bold">${data.message}</div>`;
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = 'Atualizar Senha e Acessar';
                msgDiv.innerHTML = `<div class="text-danger fw-bold">Erro ao processar solicitação.</div>`;
            });
        });
    </script>
</body>
</html>