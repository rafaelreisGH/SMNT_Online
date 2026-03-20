<?php
// pages/mensagens.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/UnidadeDAO.php';
require_once __DIR__ . '/../components/DAO/ViaturaDAO.php';

// NOVO: Captura o ID da mensagem se vier do Topbar
$idParaAbrir = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$db = Conexao::getConexao();
$usuarios = $db->query("SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

$uDAO = new UnidadeDAO();
$unidades = $uDAO->listarAgrupadasPorComando();

$vDAO = new ViaturaDAO();
$viaturas = $vDAO->listarTodasParaSelect();
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 mb-0 text-gray-800">Centro de Mensagens</h1>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNovaMensagem">
            <i class="fas fa-edit fa-sm text-white-50 me-1"></i> Nova Mensagem
        </button>
    </div>

    <div class="row">
        <!-- Sidebar Interna -->
        <div class="col-xl-3 col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-body p-2">
                    <div class="list-group list-group-flush" id="menu-mensagens">
                        <button class="list-group-item list-group-item-action border-0 active" onclick="filtrarMensagens('entrada', this)">
                            <i class="fas fa-inbox me-2"></i> Caixa de Entrada
                        </button>
                        <button class="list-group-item list-group-item-action border-0" onclick="filtrarMensagens('enviadas', this)">
                            <i class="fas fa-paper-plane me-2"></i> Enviadas
                        </button>
                        <button class="list-group-item list-group-item-action border-0" onclick="filtrarMensagens('favoritas', this)">
                            <i class="fas fa-star me-2"></i> Favoritas
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Mensagens -->
        <div class="col-xl-9 col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <h6 class="m-0 font-weight-bold text-primary" id="titulo-caixa">Caixa de Entrada</h6>
                        </div>
                        <div class="col-md-4">
                            <input type="text" id="filtroTermo" class="form-control form-control-sm" placeholder="Buscar no texto ou assunto...">
                        </div>
                        <div class="col-md-3">
                            <select id="filtroViatura" class="form-select form-select-sm">
                                <option value="">Todas as Viaturas</option>
                                <?php foreach ($viaturas as $v): ?>
                                    <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['prefixo'] . ' ' . $v['numero']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-sm btn-outline-secondary" onclick="carregarLista()" title="Atualizar">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover mb-0" id="tabela-mensagens">
                            <!-- Conteúdo via AJAX -->
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Mensagem -->
<div class="modal fade" id="modalNovaMensagem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" id="formEnviarMensagem">
            <div class="modal-header">
                <h5 class="modal-title">Compor Mensagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Para (Militar)</label>
                        <select class="form-select" name="destinatario_id" id="sel_destinatario">
                            <option value="">Selecione...</option>
                            <?php foreach ($usuarios as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-primary">Ou Para (Toda a Unidade)</label>
                        <select class="form-select border-primary" name="unidade_id">
                            <option value="">Escolher UBM...</option>
                            <?php foreach ($unidades as $comando => $u_grupo): ?>
                                <optgroup label="<?= htmlspecialchars($comando) ?>">
                                    <?php foreach ($u_grupo as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['unidade_nome']) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Viatura Relacionada (Opcional)</label>
                        <select class="form-select" name="viatura_id">
                            <option value="">Nenhuma</option>
                            <?php foreach ($viaturas as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['prefixo'] . ' ' . $v['numero']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Assunto</label>
                        <input type="text" name="assunto" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Conteúdo</label>
                        <textarea name="conteudo" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger"><i class="fas fa-paper-plane me-2"></i>Enviar Mensagem</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Leitura -->
<div class="modal fade" id="modalLerMensagem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="ler-assunto"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="ler-info" class="mb-3 p-2 border-bottom"></div>
                <div id="ler-conteudo" class="p-3" style="white-space: pre-wrap; background: #fdfdfd; min-height: 150px;"></div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        let abaAtual = 'entrada';
        let timeoutBusca = null;

        // NOVO: Pega o ID que o PHP capturou da URL
        let autoOpenId = <?= json_encode($idParaAbrir) ?>;

        function htmlspecialchars(str) {
            if (!str) return '';
            return str.replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [m]));
        }

        window.carregarLista = function() {
            const termo = document.getElementById('filtroTermo')?.value || '';
            const viatura = document.getElementById('filtroViatura')?.value || '';
            const table = document.getElementById('tabela-mensagens');

            if (!table) return;
            table.innerHTML = '<tr><td class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></td></tr>';

            const params = new URLSearchParams({
                acao: 'listar',
                pasta: abaAtual,
                termo: termo,
                viatura_id: viatura
            });

            fetch(`api/processar_mensagens.php?${params.toString()}`, {
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    }
                })
                .then(r => r.json())
                .then(data => {
                    const mensagens = data.mensagens || [];
                    renderizarTabela(mensagens);

                    // LOGICA PARA ABRIR AUTOMATICAMENTE
                    if (autoOpenId) {
                        const msgParaAbrir = mensagens.find(m => m.id == autoOpenId);
                        if (msgParaAbrir) {
                            abrirMensagem(msgParaAbrir);
                        }
                        autoOpenId = null; // Limpa para não abrir de novo se atualizar a lista
                    }
                });
        };

        function renderizarTabela(mensagens) {
            const table = document.getElementById('tabela-mensagens');
            if (mensagens.length === 0) {
                table.innerHTML = '<tr><td class="text-center p-5 text-muted">Nenhuma mensagem encontrada.</td></tr>';
                return;
            }

            let html = '';
            mensagens.forEach(m => {
                const isEnviada = (abaAtual === 'enviadas');
                const nomeExibir = isEnviada ? `Para: ${m.destinatario_nome}` : m.remetente_nome;
                const isNaoLida = (m.lida == 0 && abaAtual === 'entrada');
                const rowClass = isNaoLida ? 'fw-bold table-light' : '';
                const starClass = m.favorita == 1 ? 'fas text-warning' : 'far text-gray-300';
                const vtrBadge = m.prefixo ? `<span class="badge bg-danger-subtle text-danger ms-2">${m.prefixo} ${m.numero}</span>` : '';
                const mJson = JSON.stringify(m).replace(/'/g, "&apos;");

                html += `
            <tr class="${rowClass}" style="cursor:pointer">
                <td style="width: 40px" onclick="event.stopPropagation(); toggleFavorito(${m.id}, ${m.favorita})">
                    <i class="${starClass} fa-star"></i>
                </td>
                <td onclick='abrirMensagem(${mJson})'>${htmlspecialchars(nomeExibir)}</td>
                <td onclick='abrirMensagem(${mJson})'>${htmlspecialchars(m.assunto)} ${vtrBadge}</td>
                <td class="text-end" style="width: 150px">
                    <span class="small text-muted me-2">${new Date(m.criada_em).toLocaleDateString('pt-BR')}</span>
                    <button class="btn btn-sm btn-link text-danger p-0" onclick="event.stopPropagation(); excluirMensagem(${m.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
            });
            table.innerHTML = html;
        }

        window.abrirMensagem = function(m) {
            document.getElementById('ler-assunto').innerText = m.assunto;
            const sub = abaAtual === 'enviadas' ? `Para: <b>${m.destinatario_nome}</b>` : `De: <b>${m.remetente_nome}</b>`;
            document.getElementById('ler-info').innerHTML = `${sub} <br> <small class="text-muted">Enviado em: ${new Date(m.criada_em).toLocaleString('pt-BR')}</small>`;
            document.getElementById('ler-conteudo').innerText = m.conteudo;

            new bootstrap.Modal(document.getElementById('modalLerMensagem')).show();

            if (m.lida == 0 && abaAtual === 'entrada') {
                const fd = new FormData();
                fd.append('id', m.id);
                fetch('api/processar_mensagens.php?acao=ler', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: fd
                }).then(() => {
                    carregarLista();
                    window.atualizarMensagensTopbar();
                });
            }
        };

        window.excluirMensagem = function(id) {
            if (!confirm("Deseja remover esta mensagem?")) return;
            const fd = new FormData();
            fd.append('id', id);
            fetch('api/processar_mensagens.php?acao=excluir', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                },
                body: fd
            }).then(r => r.json()).then(data => {
                showToast(data.message);
                carregarLista();
                window.atualizarMensagensTopbar();
            });
        };

        window.toggleFavorito = function(id, atual) {
            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', atual == 1 ? 0 : 1);
            fetch('api/processar_mensagens.php?acao=favoritar', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                },
                body: fd
            }).then(() => carregarLista());
        };

        window.filtrarMensagens = function(aba, btn) {
            abaAtual = aba;
            document.querySelectorAll('#menu-mensagens button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('titulo-caixa').innerText = btn.innerText;
            carregarLista();
        };

        document.getElementById('filtroTermo').addEventListener('input', () => {
            clearTimeout(timeoutBusca);
            timeoutBusca = setTimeout(carregarLista, 400);
        });

        document.getElementById('filtroViatura').addEventListener('change', carregarLista);

        document.getElementById('formEnviarMensagem').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;

            fetch('api/processar_mensagens.php?acao=enviar', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: new FormData(this)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        bootstrap.Modal.getInstance(document.getElementById('modalNovaMensagem')).hide();
                        this.reset();
                        carregarLista();
                    } else {
                        showToast(data.message, 'danger');
                    }
                })
                .finally(() => btn.disabled = false);
        });

        carregarLista();
    })();
</script>