<?php
// pages/editar_avaria.php
require_once '../components/DAO/ViaturaDAO.php';
require_once '../components/DAO/CondutorDAO.php';
require_once '../components/DAO/RegistroAvariaDAO.php';

$vDAO = new ViaturaDAO();
$cDAO = new CondutorDAO();
$aDAO = new RegistroAvariaDAO();

// --- Lógica para processar a atualização (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => '', 'errors' => []];
    $data = $_POST;

    if (empty($data['viatura_id'])) $response['errors']['viatura_id'] = "Selecione a viatura.";
    if (empty($data['condutor_id'])) $response['errors']['condutor_id'] = "Selecione o condutor.";
    if (empty($data['tipo_avaria_id'])) $response['errors']['tipo_avaria_id'] = "Selecione o componente.";

    if (empty($response['errors'])) {
        try {
            if ($aDAO->atualizar($data)) {
                $response['success'] = true;
                $response['message'] = "Registro de avaria atualizado com sucesso!";
            } else {
                $response['message'] = "Nenhuma alteração detectada nos dados.";
            }
        } catch (Exception $e) {
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
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Formulário de Edição</h6>
                </div>
                <div class="card-body">
                    <form id="formEditarAvaria" novalidate>
                        <input type="hidden" name="id" value="<?= $display_data['id'] ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Viatura Afetada</label>
                                <select class="form-select" name="viatura_id">
                                    <?php foreach ($viaturas as $v): ?>
                                        <option value="<?= $v['id'] ?>" <?= ($v['id'] == $display_data['viatura_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($v['prefixo'] . ' ' . $v['numero']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Condutor Relatante</label>
                                <select class="form-select" name="condutor_id">
                                    <?php foreach ($condutores as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= ($c['id'] == $display_data['condutor_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Componente com Defeito</label>
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
                                            <?= htmlspecialchars($t['nome_componente']) ?>
                                        </option>
                                    <?php endforeach;
                                    echo "</optgroup>"; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Data do Relato</label>
                                <input type="datetime-local" class="form-control" name="data_registro"
                                    value="<?= date('Y-m-d\TH:i', strtotime($display_data['data_registro'])) ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Descrição do Problema</label>
                                <textarea class="form-control" name="observacoes_avaria" rows="4"><?= htmlspecialchars($display_data['observacoes_avaria']) ?></textarea>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-outline-success" id="btnAtualizarAvaria">
                                <i class="fas fa-save me-2"></i>Salvar Alterações
                            </button>

                            <a class="collapse-item" href="../pages/pesquisar_avaria.php">
                                <button type="button" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Voltar para Pesquisa
                                </button>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Container para os Toasts (necessário se o index não tiver um global) -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
</div>

<script>
    // AJUSTE 2: Garantir que o Toast funcione
    function showToast(message, type = 'success') {
        const container = document.querySelector('.toast-container');
        if (!container) return;

        const colorClass = type === 'success' ? 'text-bg-success' : 'text-bg-danger';
        const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle';
        const id = `toast-${Date.now()}`;

        const html = `
            <div id="${id}" class="toast ${colorClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="${icon} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;

        container.insertAdjacentHTML('beforeend', html);
        const toastEl = document.getElementById(id);
        const bsToast = new bootstrap.Toast(toastEl, {
            delay: 4000
        });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }

    // Função para voltar à pesquisa (Adapte o nome da função se o seu SPA usar outro padrão)
    /*
    function carregarPesquisaAvaria() {
        // Se o seu sistema usa uma função global tipo carregarPagina('url'), use-a aqui.
        // Como não tenho o nome da sua função global de navegação, vou simular o clique no menu ou recarga do container:
        if (typeof carregarConteudo === "function") {
            carregarConteudo('pages/pesquisar_avaria.php');
        } else {
            // Fallback: Tenta localizar o link de pesquisa no menu e clica nele, ou carrega via fetch
            const linkPesquisa = document.querySelector('a[onclick*="pesquisar_avaria.php"]');
            if (linkPesquisa) {
                linkPesquisa.click();
            } else {
                window.location.hash = 'pesquisar_avaria'; // Opcional, dependendo do seu roteador
            }
        }
    }
        */

    function initializeEditarAvariaScripts() {
        const form = document.getElementById('formEditarAvaria');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnAtualizarAvaria');
            const formData = new FormData(this);

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Atualizando...';

            fetch('pages/editar_avaria.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        // Opcional: Voltar para a pesquisa automaticamente após 2 segundos
                        // setTimeout(carregarPesquisaAvaria, 2000);
                    } else {
                        showToast(data.message, 'danger');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("Erro de comunicação com o servidor.", "danger");
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save me-2"></i>Salvar Alterações';
                });
        });
    }

    initializeEditarAvariaScripts();
</script>