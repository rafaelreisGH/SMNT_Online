<?php
// pages/abrir_cautela.php
require_once __DIR__ . '/../api/middleware_auth.php';
require_once __DIR__ . '/../components/DAO/ViaturaDAO.php';
require_once __DIR__ . '/../components/DAO/CondutorDAO.php';
require_once __DIR__ . '/../components/DAO/AcessorioDAO.php';

$vDAO = new ViaturaDAO();
$cDAO = new CondutorDAO();
$aDAO = new AcessorioDAO();

$viaturas = $vDAO->listarTodasParaSelect();
$condutores = $cDAO->listarAtivos();
$universais = $aDAO->listarUniversais();
$opcionais = $aDAO->listarOpcionais();
?>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Abertura de Cautela de Viatura</h1>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form id="formAbrirCautela" novalidate>
                <!-- CARD 1: IDENTIFICAÇÃO -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light text-primary">
                        <h6 class="m-0 font-weight-bold">1. Identificação e Estado de Saída</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Viatura</label>
                                <select class="form-select" name="viatura_id" id="sel_vtr" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($viaturas as $v): ?>
                                        <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['prefixo'] . ' ' . $v['numero']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Condutor (Militar que assume)</label>
                                <select class="form-select" name="condutor_id" required>
                                    <option value="">Selecione o condutor...</option>
                                    <?php foreach ($condutores as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Odômetro de Saída</label>
                                <input type="number" name="odometro_saida" class="form-control" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Nível de Combustível</label>
                                <select class="form-select" name="combustivel_saida" required>
                                    <option value="Vazio">Vazio</option>
                                    <option value="1/4">1/4</option>
                                    <option value="1/2">1/2</option>
                                    <option value="3/4">3/4</option>
                                    <option value="Cheio" selected>Cheio</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Motivo do Deslocamento</label>
                                <input type="text" name="motivo_deslocamento" class="form-control" required placeholder="Ex: Operação Verão">
                            </div>
                            <!-- Área de Avarias Existentes (Atualizada via JS) -->
                            <div class="col-12 mt-3">
                                <label class="form-label small fw-bold text-danger">Danos preexistentes na viatura:</label>
                                <div id="container_avarias_vtr" class="border rounded p-2 bg-light" style="min-height: 40px;">
                                    <span class="text-muted small italic">Selecione uma viatura para carregar o histórico...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: CHECKLIST DE ACESSÓRIOS -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">2. Checklist de Equipamentos (Ativado = Entregue)</h6>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-1 row-cols-md-3 g-2" id="container-checklist">
                            <?php
                            // Buscamos TODOS os acessórios da lista mestra
                            $todosAcessorios = $aDAO->listarTodos();
                            foreach ($todosAcessorios as $a):
                            ?>
                                <div class="col">
                                    <div class="form-check form-switch px-5 border rounded shadow-sm d-flex align-items-center" style="min-height: 50px;">
                                        <input class="form-check-input check-item" type="checkbox" role="switch"
                                            data-id="<?= $a['id'] ?>" id="item_<?= $a['id'] ?>"
                                            <?= ($a['eh_universal'] == 1) ? 'checked' : '' ?>> <!-- Marcado se for universal -->
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
                        <button type="button" class="btn btn-outline-primary w-100 py-2" onclick="enviarFormulario('Pendente')">
                            <i class="fas fa-save me-2"></i>Salvar Rascunho
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success w-100 py-2 fw-bold" onclick="enviarFormulario('Disponível')">
                            <i class="fas fa-share-square me-2"></i>Disponibilizar para Assinatura
                        </button>
                    </div>
                </div>

                <!-- <div id="secao-assinatura-condutor" class="d-none">
                    <div class="card shadow mb-4 border-left-danger bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-danger"><i class="fas fa-file-signature me-2"></i>Assinatura Digital do Condutor</h6>
                            <p class="small text-muted">Condutor, confira os dados acima. Se estiver de acordo, digite sua senha para assumir a cautela.</p>
                            <div class="row g-3 align-items-center">
                                <div class="col-md-6">
                                    <input type="password" name="senha_assinatura" class="form-control border-danger" placeholder="Senha do Condutor">
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">
                                        CONFIRMAR RECEBIMENTO DO VEÍCULO
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('formAbrirCautela');
        const selVtr = document.getElementById('sel_vtr');
        const containerAvarias = document.getElementById('container_avarias_vtr');

        // 1. Busca dados e avarias dinamicamente ao selecionar a viatura
        if (selVtr) {
            selVtr.addEventListener('change', function() {
                const viaturaId = this.value;
                if (!viaturaId) {
                    containerAvarias.innerHTML = '<span class="text-muted small italic">Selecione uma viatura...</span>';
                    return;
                }

                // A. BUSCA ODÔMETRO ATUAL (NOVO)
                fetch(`api/get_dados_viatura.php?id=${viaturaId}`, {
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.quilometragem !== undefined) {
                            const inputOdo = document.querySelector('input[name="odometro_saida"]');
                            if (inputOdo) {
                                inputOdo.value = data.quilometragem;
                                // Opcional: define como 'min' para o HTML não aceitar menor que isso
                                inputOdo.setAttribute('min', data.quilometragem);
                            }
                        }
                    })
                    .catch(err => console.error("Erro ao buscar odômetro:", err));

                // B. BUSCA HISTÓRICO DE AVARIAS (Mantenha sua lógica existente abaixo)
                containerAvarias.innerHTML = '<span class="text-muted small italic"><i class="fas fa-spinner fa-spin me-1"></i> Consultando histórico...</span>';
                fetch(`api/get_avarias_viaturas.php?viatura_id=${viaturaId}`, {
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                        }
                    })
                    .then(r => r.text())
                    .then(html => {
                        containerAvarias.innerHTML = html;
                    });
            });
        }

        // 2. Função Global de Envio (Ajustada para evitar duplicidade de IDs)
        window.enviarFormulario = function(statusFinal) {
            const form = document.getElementById('formAbrirCautela');

            // 1. REABILITA CAMPOS (Para o FormData capturar Vtr, Condutor e Odo)
            const inputsBloqueados = form.querySelectorAll(':disabled');
            inputsBloqueados.forEach(el => el.disabled = false);

            const formData = new FormData(form);

            // 2. TRAVA NOVAMENTE (Mantém o visual do Gestor)
            inputsBloqueados.forEach(el => el.disabled = true);

            // 3. COLETA TODO O CHECKLIST (Obrigatório para gravar o valor 0)
            const checklist = [];
            // Buscamos todos os elementos que possuem a classe 'check-item'
            document.querySelectorAll('.check-item').forEach(el => {
                checklist.push({
                    id: el.dataset.id,
                    status: el.checked ? 1 : 0 // Se desmarcado, envia 0 explicitamente
                });
            });

            // Anexa o checklist e o status do workflow
            formData.append('checklist_json', JSON.stringify(checklist));
            formData.append('status', statusFinal);

            fetch('api/processar_cautela.php' + (statusFinal === 'editar' ? '?acao=editar' : ''), {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('smnt_token')
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.showToast(data.message, 'success');
                        setTimeout(() => loadPage('pages/pesquisar_cautelas.php'), 1200);
                    } else {
                        window.showToast(data.message, 'danger');
                    }
                })
                .catch(err => {
                    console.error("Erro no envio:", err);
                    window.showToast("Falha na comunicação com o servidor.", "danger");
                });
        };

        // 3. Trava visual para assinatura
        window.travarParaAssinatura = function() {
            if (!confirm("Deseja travar os dados e prosseguir para a assinatura?")) return;
            form.querySelectorAll('input, select, textarea').forEach(el => {
                if (el.name !== 'senha_assinatura') el.disabled = true;
            });
            document.getElementById('container-btn-gestor').classList.add('d-none');
            document.getElementById('secao-assinatura-condutor')?.classList.remove('d-none');
            window.showToast("Dados travados para conferência.", "info");
        };

        // 4. Adiciona item extra (Ajustado para evitar duplicidade visual)
        window.adicionarItemChecklist = function() {
            const sel = document.getElementById('sel_opcionais');
            const idSelecionado = sel.value;
            if (!idSelecionado) return;

            // Verifica se o item já existe na lista antes de adicionar o HTML
            const jaExiste = document.querySelector(`.check-item[data-id="${idSelecionado}"]`);
            if (jaExiste) {
                window.showToast("Este item já consta no checklist.", "warning");
                return;
            }

            const html = `
            <div class="col">
                <div class="form-check form-switch px-5 border rounded shadow-sm d-flex align-items-center" style="min-height: 50px;">
                    <input class="form-check-input" type="checkbox" role="switch" data-id="${idSelecionado}" checked>
                    <label class="form-check-label small fw-bold mb-0 text-dark">${sel.options[sel.selectedIndex].text}</label>
                </div>
            </div>`;

            document.getElementById('container-checklist').insertAdjacentHTML('beforeend', html);
            sel.value = "";
        };

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            // Basta chamar a função que já criamos, passando o status 'Ativa'
            window.enviarFormulario('Ativa');
        });
    })();
</script>