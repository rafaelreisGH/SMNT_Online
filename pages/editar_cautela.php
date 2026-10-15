<?php
// pages/editar_cautela.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/CautelaDAO.php';
require_once __DIR__ . '/../components/DAO/ViaturaDAO.php';
require_once __DIR__ . '/../components/DAO/CondutorDAO.php';
require_once __DIR__ . '/../components/DAO/AcessorioDAO.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$dao = new CautelaDAO();
$cautela = $dao->getCautelaById($id);

// 1. Validações de segurança
if (!$cautela) {
    echo "<div class='container-fluid'><div class='alert alert-danger'>Cautela não encontrada.</div></div>";
    exit;
}

if ($cautela['status'] !== 'Pendente') {
    echo "<div class='container-fluid'><div class='alert alert-warning'>Esta cautela está com status '{$cautela['status']}' e não pode mais ser editada.</div></div>";
    exit;
}

// 2. Carregar dados para os selects e checklist
$vDAO = new ViaturaDAO();
$cDAO = new CondutorDAO();
$aceDAO = new AcessorioDAO();

$viaturas = $vDAO->listarTodasParaSelect();
$condutores = $cDAO->listarAtivos();
$universais = $aceDAO->listarUniversais();
$opcionais = $aceDAO->listarOpcionais();
$checklistSalvo = $dao->buscarChecklist($id); // Retorna array [id_acessorio => status, ...]
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Editar Rascunho de Cautela</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form id="formEditarCautela" novalidate>
                <input type="hidden" name="id" value="<?= $cautela['id'] ?>">

                <!-- CARD 1: IDENTIFICAÇÃO -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light text-primary">
                        <h6 class="m-0 font-weight-bold">1. Identificação e Estado de Saída</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Viatura</label>
                                <select class="form-select" name="viatura_id" required>
                                    <?php foreach ($viaturas as $v): ?>
                                        <option value="<?= $v['id'] ?>" <?= $v['id'] == $cautela['viatura_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($v['prefixo'] . ' ' . $v['numero']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Condutor (Militar que assume)</label>
                                <select class="form-select" name="condutor_id" required>
                                    <?php foreach ($condutores as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $cautela['condutor_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Odômetro de Saída</label>
                                <input type="number" name="odometro_saida" class="form-control" required min="0" value="<?= $cautela['odometro_saida'] ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Nível de Combustível</label>
                                <select class="form-select" name="combustivel_saida" required>
                                    <?php
                                    $niveis = ['Vazio', '1/4', '1/2', '3/4', 'Cheio'];
                                    foreach ($niveis as $n) {
                                        $sel = ($cautela['combustivel_saida'] == $n) ? 'selected' : '';
                                        echo "<option value='$n' $sel>$n</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Motivo do Deslocamento</label>
                                <input type="text" name="motivo_deslocamento" class="form-control" value="<?= htmlspecialchars($cautela['motivo_deslocamento'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: CHECKLIST DE ACESSÓRIOS -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">2. Checklist de Equipamentos (Conferência do Rascunho)</h6>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-1 row-cols-md-3 g-2" id="container-checklist">
                            <?php
                            $todosAcessorios = $aceDAO->listarTodos();
                            foreach ($todosAcessorios as $a):
                                // Verifica se este item específico estava marcado como entregue (1) no banco
                                $marcadoNoBanco = (isset($checklistSalvo[$a['id']]) && $checklistSalvo[$a['id']] == 1);
                            ?>
                                <div class="col">
                                    <div class="form-check form-switch px-5 border rounded shadow-sm d-flex align-items-center" style="min-height: 50px;">
                                        <input class="form-check-input check-item" type="checkbox" role="switch"
                                            data-id="<?= $a['id'] ?>" id="item_<?= $a['id'] ?>"
                                            <?= $marcadoNoBanco ? 'checked' : '' ?>>
                                        <label class="form-check-label small fw-bold mb-0 text-dark" for="item_<?= $a['id'] ?>">
                                            <?= htmlspecialchars($a['nome']) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <button type="button" class="btn btn-outline-primary w-100 py-2" onclick="salvarEdicao('Pendente')">
                            <i class="fas fa-save me-2"></i>Apenas Salvar Alterações
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success w-100 py-2 fw-bold" onclick="salvarEdicao('Disponível')">
                            <i class="fas fa-share-square me-2"></i>Salvar e Disponibilizar para Assinatura
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('formEditarCautela');

        // 1. Lógica para carregar itens opcionais que já estavam salvos
        const checklistSalvo = <?= json_encode($checklistSalvo) ?>;
        const opcionais = <?= json_encode($opcionais) ?>;

        opcionais.forEach(opt => {
            // Se o item está no banco (mesmo desmarcado) e não está na lista universal
            if (checklistSalvo.hasOwnProperty(opt.id) && !document.getElementById('item_' + opt.id)) {
                const isChecked = checklistSalvo[opt.id] == 1 ? 'checked' : '';
                const html = `
                    <div class="col">
                        <div class="form-check form-switch p-2 border rounded border-primary bg-white d-flex align-items-center" style="min-height: 50px;">
                            <input class="form-check-input ms-0 me-3 check-item" type="checkbox" role="switch" data-id="${opt.id}" ${isChecked}>
                            <label class="form-check-label small fw-bold mb-0">${opt.nome}</label>
                        </div>
                    </div>`;
                document.getElementById('container-checklist').insertAdjacentHTML('beforeend', html);
            }
        });

        // 2. Função de inclusão manual (caso o gestor queira adicionar algo que não estava antes)
        window.adicionarItemChecklist = function() {
            const sel = document.getElementById('sel_opcionais');
            if (!sel.value || document.querySelector(`.check-item[data-id="${sel.value}"]`)) return;

            const html = `<div class="col"><div class="form-check form-switch p-2 border rounded border-primary d-flex align-items-center" style="min-height: 50px;"><input class="form-check-input ms-0 me-3 check-item" type="checkbox" role="switch" data-id="${sel.value}" checked><label class="form-check-label small fw-bold mb-0 text-dark">${sel.options[sel.selectedIndex].text}</label></div></div>`;
            document.getElementById('container-checklist').insertAdjacentHTML('beforeend', html);
            sel.value = "";
        };

        // 3. Função Única de Salvamento (Chamada pelos botões do HTML)
        window.salvarEdicao = function(statusFinal) {
            const btn = event.currentTarget; // Captura o botão clicado

            // Reabilita temporariamente para captura de dados
            const camposBloqueados = form.querySelectorAll(':disabled');
            camposBloqueados.forEach(el => el.disabled = false);

            const formData = new FormData(form);

            // Bloqueia novamente
            camposBloqueados.forEach(el => el.disabled = true);

            // Coleta checklist completo (1 ou 0)
            const checklist = [];
            document.querySelectorAll('.check-item').forEach(el => {
                checklist.push({
                    id: el.dataset.id,
                    status: el.checked ? 1 : 0
                });
            });

            formData.append('checklist_json', JSON.stringify(checklist));
            formData.append('status', statusFinal);

            btn.disabled = true;
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';

            fetch('api/processar_cautela.php?acao=editar', {
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
                        setTimeout(() => loadPage('pages/pesquisar_cautelas.php'), 1200);
                    } else {
                        showToast(data.message, 'danger');
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                    }
                })
                .catch(err => {
                    console.error("Erro na edição:", err);
                    showToast("Erro de comunicação", "danger");
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                });
        };
    })();
</script>