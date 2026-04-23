<?php
// pages/editar_avaria.php

require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/ViaturaDAO.php';
require_once __DIR__ . '/../components/DAO/CondutorDAO.php';
require_once __DIR__ . '/../components/DAO/RegistroAvariaDAO.php';
require_once __DIR__ . '/../components/DAO/ApuracaoDAO.php'; // NOVO
require_once __DIR__ . '/../components/Log/LoggerService.php'; // Para logs

$vDAO = new ViaturaDAO();
$cDAO = new CondutorDAO();
$aDAO = new RegistroAvariaDAO();
$apuracaoDAO = new ApuracaoDAO();

$perfilUsuario = USER_AUTH['perfil'];
$ehGestorMaster = in_array($perfilUsuario, ['Administrador', 'Gestor Central']);

// --- Lógica para processar a atualização (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => '', 'errors' => []];
    $data = $_POST;

    // Validação básica da Avaria
    if (empty($data['viatura_id'])) $response['errors']['viatura_id'] = "Obrigatório.";
    if (empty($data['condutor_id'])) $response['errors']['condutor_id'] = "Obrigatório.";

    if (empty($response['errors'])) {
        try {
            $db = Conexao::getConexao();
            $db->beginTransaction();

            // 1. Atualiza os dados principais da Avaria (incluindo o checkbox requer_apuracao)
            // Se o usuário não for master, mantemos o valor que já estava no banco
            if (!$ehGestorMaster) {
                $avariaAtual = $aDAO->buscarDetalhesPorId($data['id']);
                $data['requer_apuracao'] = $avariaAtual['requer_apuracao'];
            } else {
                $data['requer_apuracao'] = isset($data['requer_apuracao']) ? 1 : 0;
            }

            $sucessoAvaria = $aDAO->atualizar($data);

            // 1.5 Processamento de NOVAS FOTOS
            if (isset($_FILES['novas_fotos'])) {
                $dir = __DIR__ . "/../assets/uploads/avarias/"; // Caminho absoluto é mais seguro

                // Verifica se a pasta existe, se não, tenta criar
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                foreach ($_FILES['novas_fotos']['tmp_name'] as $key => $tmp) {
                    if ($_FILES['novas_fotos']['error'][$key] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['novas_fotos']['name'][$key], PATHINFO_EXTENSION);
                        $nomeArquivo = "av_" . $data['id'] . "_" . time() . "_" . uniqid() . "." . $ext;

                        if (move_uploaded_file($tmp, $dir . $nomeArquivo)) {
                            $aDAO->inserirFoto((int)$data['id'], $nomeArquivo);
                        } else {
                            // Se falhar o move_uploaded_file, registra o erro mas não deixa o PHP travar
                            error_log("Falha ao mover arquivo: " . $_FILES['novas_fotos']['name'][$key]);
                        }
                    }
                }
            }

            // 2. Se a avaria requer apuração ou já possui dados de apuração enviados
            $sucessoApuracao = true;
            if (isset($data['numero_portaria']) || isset($data['encarregado'])) {
                $data['avaria_id'] = $data['id']; // Mapeia para o DAO de apuração
                $sucessoApuracao = $apuracaoDAO->salvar($data);
            }

            if ($sucessoAvaria || $sucessoApuracao) {
                $db->commit();
                LoggerService::registrar("Atualizou avaria e dados de apuração", "registro_avaria", $data['id']);
                $response['success'] = true;
                $response['message'] = "Dados atualizados com sucesso!";
            } else {
                $db->rollBack();
                $response['message'] = "Nenhuma alteração detectada.";
            }
        } catch (Exception $e) {
            if (isset($db)) $db->rollBack();
            $response['message'] = "Erro: " . $e->getMessage();
        }
    } else {
        $response['message'] = "Verifique os campos obrigatórios.";
    }

    echo json_encode($response);
    exit();
}

// --- Lógica para carregar os dados (GET) ---
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    echo "<div class='container-fluid'><div class='alert alert-danger'>ID inválido.</div></div>";
    exit();
}

$display_data = $aDAO->buscarDetalhesPorId($id);

// SEGURANÇA RBAC: Validar se o usuário pode editar esta avaria
if (!$ehGestorMaster && $display_data['lotacao_atual_id'] != USER_AUTH['unidade_id']) {
    echo "<div class='container-fluid'><div class='alert alert-danger'>Acesso Negado: Esta viatura pertence a outra unidade.</div></div>";
    exit();
}

$fotos = $aDAO->buscarFotos($id); // Carrega as fotos para a galeria

$dados_apuracao = $apuracaoDAO->buscarPorAvariaId($id);

if (!$display_data) {
    echo "<div class='container-fluid'><div class='alert alert-warning'>Registro não encontrado.</div></div>";
    exit();
}

$viaturas = $vDAO->listarTodasParaSelect();
$condutores = $cDAO->listarAtivos();
$tiposAvaria = $aDAO->listarTiposAvaria();
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Editar Registro de Avaria</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div id="formEditarAvaria" novalidate>
                <input type="hidden" name="id" value="<?= $display_data['id'] ?>">

                <!-- Card da Avaria -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Dados do Problema Técnico</h6>
                        <?php if ($ehGestorMaster): ?>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="requer_apuracao" name="requer_apuracao" value="1" <?= ($display_data['requer_apuracao'] == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold text-danger" for="requer_apuracao">Exigir Apuração Administrativa</label>
                            </div>
                        <?php else: ?>
                            <?php if ($display_data['requer_apuracao']): ?>
                                <span class="badge bg-danger p-2"><i class="fas fa-gavel me-1"></i> Apuração Exigida pela Gestão Central</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Viatura</label>
                                <select class="form-select" name="viatura_id" <?= !$ehGestorMaster ? 'disabled' : '' ?>>
                                    <?php foreach ($viaturas as $v): ?>
                                        <option value="<?= $v['id'] ?>" <?= ($v['id'] == $display_data['viatura_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($v['prefixo'] . ' ' . $v['numero']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Condutor Relatante</label>
                                <select class="form-select" name="condutor_id">
                                    <?php foreach ($condutores as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= ($c['id'] == $display_data['condutor_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['nome'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Componente Afetado</label>
                                <select class="form-select" name="tipo_avaria_id">
                                    <?php
                                    $catAtual = "";
                                    foreach ($tiposAvaria as $t):
                                        if ($catAtual != $t['categoria']):
                                            if ($catAtual != "") echo "</optgroup>";
                                            $catAtual = $t['categoria'];
                                            echo "<optgroup label='" . htmlspecialchars($catAtual) . "'>";
                                        endif;
                                    ?>
                                        <option value="<?= $t['id'] ?>" <?= ($t['id'] == $display_data['tipo_avaria_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['nome_componente'] ?? '') ?>
                                        </option>
                                    <?php endforeach;
                                    echo "</optgroup>"; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Data do Relato</label>
                                <input type="datetime-local" class="form-control" name="data_registro" value="<?= date('Y-m-d\TH:i', strtotime($display_data['data_registro'])) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Descrição</label>
                                <textarea class="form-control" name="observacoes_avaria" rows="3"><?= htmlspecialchars($display_data['observacoes_avaria'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção de Apuração (Visível apenas se requer_apuracao for 1) -->
                <div class="card shadow mb-4 <?= ($display_data['requer_apuracao'] == 0) ? 'd-none' : '' ?>" id="card_apuracao">
                    <div class="card-header py-3 bg-dark">
                        <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-gavel me-2"></i>Procedimento Administrativo de Apuração</h6>
                    </div>
                    <div class="card-body border-left-dark">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Nº da Portaria</label>
                                <input type="text" class="form-control" name="numero_portaria" value="<?= htmlspecialchars($dados_apuracao['numero_portaria'] ?? '') ?>" placeholder="Ex: 001/2026-UBM">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Data de Abertura</label>
                                <input type="date" class="form-control" name="data_abertura" value="<?= $dados_apuracao['data_abertura'] ?? '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Militar Encarregado</label>
                                <input type="text" class="form-control" name="encarregado" value="<?= htmlspecialchars($dados_apuracao['encarregado'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Status da Apuração</label>
                                <select class="form-select" name="status_apuracao">
                                    <option value="Em andamento" <?= ($dados_apuracao['status_apuracao'] ?? '') == 'Em andamento' ? 'selected' : '' ?>>Em andamento</option>
                                    <option value="Concluído" <?= ($dados_apuracao['status_apuracao'] ?? '') == 'Concluído' ? 'selected' : '' ?>>Concluído</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Resultado / Conclusão</label>
                                <textarea class="form-control" name="resultado" rows="2"><?= htmlspecialchars($dados_apuracao['resultado'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORA DA TAG </form> -->
            <div class="card shadow mb-4" id="modulo-fotos">
                <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-camera me-2"></i>Evidências (Fotos)</h6>
                    <span class="badge bg-secondary" id="contador-fotos"><?= count($fotos) ?> / 5</span>
                </div>
                <div class="card-body">
                    <!-- Galeria -->
                    <div class="row mb-4" id="galeria-fotos">
                        <?php foreach ($fotos as $f): ?>
                            <div class="col-md-2 col-6 mb-3 text-center" id="foto-wrapper-<?= $f['id'] ?>">
                                <div class="position-relative">
                                    <img src="assets/uploads/avarias/<?= $f['caminho_arquivo'] ?>" class="img-thumbnail shadow-sm" style="height: 100px; width: 100%; object-fit: cover;">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" onclick="excluirFoto(event, <?= $f['id'] ?>, '<?= $f['caminho_arquivo'] ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Controles de Upload (Independentes) -->
                    <div id="controles-upload" class="<?= count($fotos) >= 5 ? 'd-none' : '' ?>">
                        <div class="input-group">
                            <input type="file" id="input-fotos-independente" class="form-control" multiple accept="image/*">
                            <button class="btn btn-primary" type="button" id="btn-fazer-upload" onclick="uploadFotosIndependente()">
                                <i class="fas fa-upload me-2"></i>Fazer Upload das Fotos
                            </button>
                        </div>
                        <small class="text-muted">Selecione as imagens e clique em "Fazer Upload".</small>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação (Também fora do form) -->
            <div class="d-flex justify-content-between mb-5">
                <button type="button" class="btn btn-outline-success px-4" id="btnSalvarManual" onclick="executarSalvarAvaria()">
                    <i class="fas fa-save me-2"></i>Salvar Tudo
                </button>
                <a href="pages/pesquisar_avaria.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar para Pesquisa
                </a>
            </div>
        </div>
    </div>
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
</div>

<script>
    (function() {
        const checkApuracao = document.getElementById('requer_apuracao');
        const cardApuracao = document.getElementById('card_apuracao');

        // 1. Lógica para mostrar/esconder campos de apuração dinamicamente
        if (checkApuracao) {
            checkApuracao.addEventListener('change', function() {
                if (this.checked) {
                    cardApuracao.classList.remove('d-none');
                } else {
                    cardApuracao.classList.add('d-none');
                }
            });
        }

        // 2. SALVAMENTO DE TEXTO (Formulário Principal)
        window.executarSalvarAvaria = function() {
            const btn = document.getElementById('btnSalvarManual');
            const container = document.getElementById('formEditarAvaria'); // Agora é uma DIV
            const formData = new FormData();

            // Coleta manual para garantir que nenhum comportamento de 'form' interfira
            container.querySelectorAll('input, select, textarea').forEach(el => {
                if (el.type === 'checkbox') {
                    if (el.checked) formData.append(el.name, el.value);
                } else if (el.name) {
                    formData.append(el.name, el.value);
                }
            });

            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';

            fetch('pages/editar_avaria.php', {
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
                        setTimeout(() => window.loadPage('pages/pesquisar_avaria.php'), 1200);
                    } else {
                        showToast(data.message, 'danger');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    console.error("Erro no salvamento:", err);
                    showToast("Erro ao salvar dados técnicos.", "danger");
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        };

        // 3. UPLOAD INDEPENDENTE DE FOTOS
        window.uploadFotosIndependente = function() {
            const input = document.getElementById('input-fotos-independente');
            const btn = document.getElementById('btn-fazer-upload');
            const avariaId = document.querySelector('input[name="id"]').value;

            if (input.files.length === 0) {
                showToast("Selecione ao menos uma foto.", "warning");
                return;
            }

            const formData = new FormData();
            formData.append('avaria_id', avariaId);
            for (let i = 0; i < input.files.length; i++) {
                formData.append('fotos[]', input.files[i]);
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

            fetch('api/processar_upload_foto_avaria.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, "success");

                        const galeria = document.getElementById('galeria-fotos');
                        data.fotos.forEach(foto => {
                            const html = `
        <div class="col-md-2 col-6 mb-3 text-center border border-success rounded p-1" id="foto-wrapper-${foto.id}" style="background-color: #f0fff0;">
            <div class="position-relative">
                <span class="position-absolute top-0 start-0 badge rounded-pill bg-success m-1 shadow">
                    <i class="fas fa-check"></i>
                </span>
                <img src="assets/uploads/avarias/${foto.caminho}" class="img-thumbnail shadow-sm" style="height: 100px; width: 100%; object-fit: cover;">
                
                <!-- O ID passado aqui agora será o correto (ex: 85) -->
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 shadow" 
                        onclick="excluirFoto(event, ${foto.id}, '${foto.caminho}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>`;
                            galeria.insertAdjacentHTML('beforeend', html);
                        });

                        input.value = '';
                        atualizarContadorFotos();
                    } else {
                        showToast(data.message, "danger");
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("Erro ao subir fotos. Verifique o tamanho dos arquivos.", "danger");
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-upload me-2"></i>Fazer Upload das Fotos';
                });
        };

        // 4. EXCLUSÃO DE FOTO INDEPENDENTE
        window.excluirFoto = function(event, fotoId, arquivo) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            if (!confirm("Deseja realmente excluir esta foto?")) return;

            const formData = new FormData();
            formData.append('id', fotoId);
            formData.append('arquivo', arquivo);

            fetch('api/deletar_foto_avaria.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const elemento = document.getElementById(`foto-wrapper-${fotoId}`);
                        // SÓ TENTA REMOVER SE O ELEMENTO EXISTIR (Evita o erro na linha 140)
                        if (elemento) {
                            elemento.style.opacity = "0";
                            setTimeout(() => {
                                elemento.remove();
                                // Se existir a função de atualizar contador, chama com segurança
                                if (typeof atualizarContadorFotos === 'function') {
                                    atualizarContadorFotos();
                                }
                            }, 300);
                        }
                        showToast("Foto removida.", "success");
                    } else {
                        showToast(data.message, "danger");
                    }
                })
                .catch(err => {
                    console.error("Erro na exclusão:", err);
                    showToast("Erro ao processar exclusão no servidor.", "danger");
                });
        };

        // 5. AUXILIARES
        window.validarLimiteFotosEdicao = function(input, jaExistentes) {
            // Conta as fotos atuais no DOM para ser preciso
            const atuaisNoDom = document.querySelectorAll('#galeria-fotos > div').length;
            const limiteDisponivel = 5 - atuaisNoDom;

            if (input.files.length > limiteDisponivel) {
                showToast(`Limite excedido. Você só pode adicionar mais ${limiteDisponivel} foto(s).`, "danger");
                input.value = "";
            }
        };

        function atualizarContadorFotos() {
            const galeria = document.getElementById('galeria-fotos');
            if (!galeria) return;

            const total = galeria.querySelectorAll('.foto-item-container').length; // Use uma classe comum nas divs das fotos
            const contadorEl = document.getElementById('contador-fotos');
            const controlesUpload = document.getElementById('controles-upload');

            if (contadorEl) contadorEl.innerText = `${total} / 5`;
            if (controlesUpload) {
                total >= 5 ? controlesUpload.classList.add('d-none') : controlesUpload.classList.remove('d-none');
            }
        }
    })();
</script>