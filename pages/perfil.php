<?php
// pages/perfil.php
require_once __DIR__ . '/../api/middleware_auth.php'; // Proteção JWT
require_once __DIR__ . '/../components/DAO/UsuarioDAO.php'; // Importação do DAO

$uDAO = new UsuarioDAO();
$user = $uDAO->buscarDadosCompletos(USER_AUTH['id']);

if (!$user) {
    echo "<div class='alert alert-danger'>Erro ao carregar dados do perfil.</div>";
    exit;
}

// 1. Define o nome do arquivo padrão
$fotoNome = 'undraw_profile.svg';

// 2. Se o banco tiver um nome e o arquivo existir fisicamente, troca o nome
if (!empty($user['foto']) && file_exists(__DIR__ . "/../assets/uploads/perfil/" . $user['foto'])) {
    $fotoNome = $user['foto'];
}

// 3. Monta o caminho final para o HTML
$fotoPath = "assets/uploads/perfil/" . $fotoNome;
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Meu Perfil</h1>

    <div class="row">
        <!-- Coluna da Foto e Perfil -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <div class="mb-3 position-relative">
                        <img src="<?= $fotoPath ?>?v=<?= time() ?>" id="img-preview"
                            class="rounded-circle img-thumbnail shadow-sm"
                            style="width: 150px; height: 150px; object-fit: cover;">
                    </div>

                    <form id="formFoto" enctype="multipart/form-data">
                        <input type="file" name="foto_perfil" id="inputFoto" class="d-none" accept="image/png, image/jpeg, image/jpg">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="document.getElementById('inputFoto').click()">
                            <i class="fas fa-camera me-1"></i> Alterar Foto
                        </button>
                    </form>

                    <h5 class="mt-3 fw-bold text-dark mb-0"><?= htmlspecialchars($user['nome']) ?></h5>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="badge bg-danger px-3 py-2 rounded-pill"><?= htmlspecialchars($user['perfil_nome']) ?></span>
                </div>
            </div>
        </div>

        <!-- Coluna de Dados Detalhados -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <i class="fas fa-id-card text-primary me-2"></i>
                    <h6 class="m-0 font-weight-bold text-primary">Informações Institucionais</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted fw-bold">CPF:</div>
                        <div class="col-sm-8 text-dark"><?= htmlspecialchars($user['cpf']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted fw-bold">Unidade de Lotação:</div>
                        <div class="col-sm-8 text-dark"><?= htmlspecialchars($user['unidade_nome']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted fw-bold">Data de Cadastro:</div>
                        <div class="col-sm-8 text-dark"><?= date('d/m/Y', strtotime($user['criado_em'])) ?></div>
                    </div>

                    <hr>

                    <div class="alert alert-light border small">
                        <i class="fas fa-info-circle me-1"></i>
                        Para alterar seus dados cadastrais ou sua lotação, entre em contato com o <strong>Gestor Central</strong>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Lógica para upload automático ao selecionar a foto
    document.getElementById('inputFoto').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const formData = new FormData(document.getElementById('formFoto'));

            // Feedback visual de carregamento
            showToast("Enviando foto...", "info");

            fetch('api/processar_perfil.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Atualiza o nome da foto no navegador para o Topbar enxergar
                        localStorage.setItem('user_foto', data.nova_foto);

                        showToast(data.message, "success");
                        // Agora chama a função global para atualizar o Topbar imediatamente
                        if (typeof updateUIUserInfo === 'function') updateUIUserInfo();

                        // Recarrega a página de perfil
                        window.loadPage('pages/perfil.php');
                    } else {
                        showToast(data.message, "danger");
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("Erro ao processar imagem", "danger");
                });
        }
    });
</script>