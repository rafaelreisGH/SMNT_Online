<?php require_once __DIR__ . '/../api/middleware_auth.php'; ?>
<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Configurações de Conta</h1>
    <div class="card shadow mb-4 col-lg-6 mx-auto">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger">Alterar Senha de Acesso</h6>
        </div>
        <div class="card-body">
            <form id="formUpdatePass">
                <div class="mb-3"><label class="form-label">Nova Senha</label><input type="password" name="nova_senha" class="form-control" required minlength="6"></div>
                <div class="mb-3"><label class="form-label">Confirmar Senha</label><input type="password" name="confirmar_senha" class="form-control" required minlength="6"></div>
                <button type="submit" class="btn btn-danger w-100">Atualizar Senha</button>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('formUpdatePass').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('api/auth_change_password.php', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
            },
            body: new FormData(this)
        }).then(r => r.json()).then(data => showToast(data.message, data.success ? 'success' : 'danger'));
    });
</script>