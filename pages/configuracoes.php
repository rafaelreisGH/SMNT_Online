<?php
// pages/configuracoes.php
require_once __DIR__ . '/../api/middleware_auth.php';
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Configurações de Conta</h1>

    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white border-bottom-danger">
                    <h6 class="m-0 font-weight-bold text-danger"><i class="fas fa-shield-alt me-2"></i>Segurança e Acesso</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Altere sua senha de acesso abaixo. Evite usar senhas óbvias.</p>

                    <form id="formAlterarSenha" novalidate>
                        <!-- Nova Senha -->
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="nova_senha" name="nova_senha"
                                placeholder="Nova Senha" required minlength="6">
                            <label for="nova_senha">Nova Senha (Mínimo 6 caracteres)</label>
                            <div class="invalid-feedback" id="error-nova_senha"></div>
                        </div>

                        <!-- Confirmar Senha -->
                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha"
                                placeholder="Confirmar Senha" required minlength="6">
                            <label for="confirmar_senha">Confirmar Nova Senha</label>
                            <div class="invalid-feedback" id="error-confirmar_senha"></div>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 py-2 fw-bold" id="btnSalvarSenha">
                            <span id="txtBtn">Atualizar Senha</span>
                            <span id="spinnerBtn" class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('formAlterarSenha');
        const btn = document.getElementById('btnSalvarSenha');
        const txtBtn = document.getElementById('txtBtn');
        const spinnerBtn = document.getElementById('spinnerBtn');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // 1. Reset visual e trava o botão
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            btn.disabled = true;
            txtBtn.textContent = 'Processando...';
            spinnerBtn.classList.remove('d-none');

            fetch('api/auth_change_password.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        form.reset();
                    } else {
                        showToast(data.message, 'danger');
                        // Exibe erros nos campos específicos
                        if (data.errors) {
                            Object.keys(data.errors).forEach(key => {
                                const input = document.getElementById(key);
                                const errorDiv = document.getElementById(`error-${key}`);
                                if (input) input.classList.add('is-invalid');
                                if (errorDiv) errorDiv.textContent = data.errors[key];
                            });
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("Erro de comunicação com o servidor.", "danger");
                })
                .finally(() => {
                    // 2. O SEGREDO ESTÁ AQUI: Este bloco SEMPRE executa
                    // Reabilita o botão para que o usuário possa tentar novamente
                    btn.disabled = false;
                    txtBtn.textContent = 'Atualizar Senha';
                    spinnerBtn.classList.add('d-none');
                });
        });
    })();
</script>