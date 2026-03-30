<?php
// pages/gestao_usuarios.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/UsuarioDAO.php';
require_once __DIR__ . '/../components/DAO/UnidadeDAO.php';

$uDAO = new UsuarioDAO();
$unidDAO = new UnidadeDAO();

$usuarios = $uDAO->listarParaGestao();
$unidadesAgrupadas = $unidDAO->listarAgrupadasPorComando();

// Busca perfis para o modal de acesso
$db = Conexao::getConexao();
$perfis = $db->query("SELECT id, nome FROM perfis ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas para os Cards
$totalUsers = count($usuarios);
$pendentesUnidade = count(array_filter($usuarios, fn($u) => is_null($u['unidade_id'])));
$inativos = count(array_filter($usuarios, fn($u) => $u['ativo'] == 0));
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 mb-0 text-gray-800">Gestão de Usuários</h1>
        <button class="btn btn-sm btn-primary shadow-sm" onclick="loadPage('pages/cadastrar_usuario.php')">
            <i class="fas fa-user-plus fa-sm me-1"></i> Novo Usuário
        </button>
    </div>

    <!-- Cards de Resumo -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total de Usuários</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalUsers ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pendentes de Lotação</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendentesUnidade ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-map-marker-alt fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Usuários Bloqueados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $inativos ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-slash fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela DataTables -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="tableUsuarios" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Militar</th>
                            <th>CPF</th>
                            <th>Unidade (UBM)</th>
                            <th>Perfil</th>
                            <th>Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($u['nome']) ?></div>
                                    <small class="text-muted">Acesso: <?= $u['ultimo_login'] ? date('d/m/Y H:i', strtotime($u['ultimo_login'])) : 'Nunca logou' ?></small>
                                </td>
                                <td class="small"><?= htmlspecialchars($u['cpf']) ?></td>
                                <td>
                                    <?php if ($u['unidade_nome']): ?>
                                        <?= htmlspecialchars($u['unidade_nome']) ?>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle me-1"></i> DEFINIR UBM</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= $u['perfil_nome'] ?? 'Sem Perfil' ?></span></td>
                                <td>
                                    <span class="badge <?= $u['ativo'] ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $u['ativo'] ? 'Ativo' : 'Bloqueado' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <!-- Botão Gerenciar Acesso (Perfil/Unidade) -->
                                    <button class="btn btn-sm btn-outline-primary" title="Vincular Perfil/Unidade"
                                        onclick='abrirModalAcesso(<?= json_encode($u) ?>)'>
                                        <i class="fas fa-user-shield"></i>
                                    </button>

                                    <!-- Botão Reset Senha -->
                                    <button class="btn btn-sm btn-outline-info" title="Resetar Senha"
                                        onclick="abrirModalSenha(<?= $u['id'] ?>, '<?= $u['nome'] ?>')">
                                        <i class="fas fa-key"></i>
                                    </button>

                                    <!-- Botão Toggle Status -->
                                    <button class="btn btn-sm <?= $u['ativo'] ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                        onclick="toggleStatusUsuario(this, <?= $u['id'] ?>, <?= $u['ativo'] ?>)"
                                        title="<?= $u['ativo'] ? 'Bloquear' : 'Ativar' ?>">
                                        <i class="fas <?= $u['ativo'] ? 'fa-user-times' : 'fa-user-check' ?>"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gerenciar Acesso -->
<div class="modal fade" id="modalAcesso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="formVincular">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Configurações de Acesso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="acesso_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Unidade de Lotação (UBM)</label>
                    <select class="form-select" name="unidade_id" id="acesso_unidade" required>
                        <?php foreach ($unidadesAgrupadas as $cmd => $grupo): ?>
                            <optgroup label="<?= $cmd ?>">
                                <?php foreach ($grupo as $un): ?>
                                    <option value="<?= $un['id'] ?>"><?= $un['unidade_nome'] ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Perfil de Sistema</label>
                    <select class="form-select" name="perfil_id" id="acesso_perfil" <?= USER_AUTH['perfil_id'] != 1 ? 'disabled' : '' ?>>
                        <?php foreach ($perfis as $pf): ?>
                            <option value="<?= $pf['id'] ?>"><?= $pf['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (USER_AUTH['perfil_id'] != 1): ?>
                        <small class="text-muted">Apenas o Administrador pode alterar perfis.</small>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Salvar Vínculos</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reset Senha -->
<div class="modal fade" id="modalSenha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form class="modal-content" id="formResetSenha">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Resetar Senha</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="senha_id">
                <p id="senha_nome_usuario" class="small fw-bold mb-3"></p>
                <label class="form-label">Nova Senha Temporária</label>
                <input type="password" name="senha" class="form-control" required minlength="6" placeholder="Mínimo 6 caracteres">
                <small class="text-muted">O usuário será obrigado a trocar esta senha no próximo login.</small>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info text-white">Confirmar Reset</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        // 1. Inicializa DataTables
        const table = $('#tableUsuarios').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
            },
            pageLength: 25,
            order: [
                [0, 'asc']
            ]
        });

        // 2. Lógica de Modal de Acesso
        window.abrirModalAcesso = function(u) {
            document.getElementById('acesso_id').value = u.id;
            document.getElementById('acesso_unidade').value = u.unidade_id || '';
            document.getElementById('acesso_perfil').value = u.perfil_id || '';
            new bootstrap.Modal(document.getElementById('modalAcesso')).show();
        };

        // 3. Lógica de Modal de Senha
        window.abrirModalSenha = function(id, nome) {
            document.getElementById('senha_id').value = id;
            document.getElementById('senha_nome_usuario').innerText = nome;
            new bootstrap.Modal(document.getElementById('modalSenha')).show();
        };

        // 4. Ações via AJAX
        const token = localStorage.getItem('smnt_token');

        // Submit Vínculos
        document.getElementById('formVincular').onsubmit = function(e) {
            e.preventDefault();
            fetch('api/admin_usuario_acoes.php?acao=vincular', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                body: new FormData(this)
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    showToast(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('modalAcesso')).hide();
                    loadPage('pages/gestao_usuarios.php');
                } else {
                    showToast(data.message, 'danger');
                }
            });
        };

        // Submit Reset Senha
        document.getElementById('formResetSenha').onsubmit = function(e) {
            e.preventDefault();
            fetch('api/admin_usuario_acoes.php?acao=reset-senha', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                body: new FormData(this)
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    showToast(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('modalSenha')).hide();
                } else {
                    showToast(data.message, 'danger');
                }
            });
        };

        // Toggle Ativo/Inativo
        window.toggleStatusUsuario = function(btn, id, statusAtual) {
            const novoStatus = statusAtual ? 0 : 1;
            const msg = novoStatus ? "Deseja ativar este usuário?" : "Deseja bloquear o acesso deste militar?";
            if (!confirm(msg)) return;

            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', novoStatus);

            fetch('api/admin_usuario_acoes.php?acao=status', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                body: fd
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    showToast(data.message);
                    loadPage('pages/gestao_usuarios.php');
                }
            });
        };
    })();
</script>