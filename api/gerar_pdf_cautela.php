<?php
// api/gerar_pdf_cautela.php
require_once '../vendor/autoload.php';
require_once 'middleware_auth.php';
require_once '../components/DAO/CautelaDAO.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$dao = new CautelaDAO();
$c = $dao->buscarDadosCompletosDocumento($id);

if (!$c || $c['status'] === 'Pendente') {
    die("Acesso negado ou cautela não finalizada.");
}

// === INSERIR ESTE BLOCO DE BUSCA DE DADOS AQUI ===
$db = Conexao::getConexao();
// 1. Busca os itens marcados na cautela
$itens = $dao->buscarChecklist($id);

// 2. Busca as avarias preexistentes (usando a data da cautela)
// Note: Use o método buscarAvariasAtivas que já temos no DAO
$avarias = $dao->buscarAvariasAtivas($c['viatura_id']);

// 3. Busca a lista mestra de nomes de acessórios
$nomesItens = $db->query("SELECT id, nome FROM acessorios_viatura ORDER BY nome ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
// ================================================

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

// Template simples do Termo (Baseado no modelo BEA)
$html = "
<style>
    body { font-family: sans-serif; font-size: 11px; line-height: 1.4; }
    .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 15px; }
    .section { background: #f2f2f2; padding: 5px; font-weight: bold; margin-top: 10px; border-left: 5px solid #d9534f; }
    table { width: 100%; border-collapse: collapse; margin-top: 5px; }
    td { border: 1px solid #ccc; padding: 5px; }
    .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; border-top: 1px solid #eee; }
</style>

<div class='header'>
    <h3>ESTADO DE MATO GROSSO</h3>
    <h4>CORPO DE BOMBEIROS MILITAR - TERMO DE CAUTELA</h4>
</div>

<div class='section'>1. DADOS DO VEÍCULO E CONDUTOR</div>
<table>
    <tr><td><strong>Viatura:</strong> {$c['prefixo']} {$c['numero']}</td><td><strong>Placa:</strong> {$c['placa']}</td></tr>
    <tr><td><strong>Condutor:</strong> {$c['condutor_nome']}</td><td><strong>Unidade:</strong> {$c['unidade_nome']}</td></tr>
</table>

<div class='section'>2. VISTORIA DE SAÍDA</div>
<table>
    <tr><td><strong>Odômetro:</strong> {$c['odometro_saida']} km</td><td><strong>Combustível:</strong> {$c['combustivel_saida']}</td></tr>
    <tr><td colspan='2'><strong>Motivo:</strong> " . ($c['motivo_deslocamento'] ?? 'Não informado') . "</td></tr>
</table>

<div class='section'>3. CHECKLIST DE ACESSÓRIOS</div>
<div style='margin-top:5px;'>";
foreach ($nomesItens as $idAcc => $nomeAcc) {
    $check = (isset($itens[$idAcc]) && $itens[$idAcc] == 1) ? "[X]" : "[  ]";
    $html .= "<span style='display:inline-block; width:33%'>$check $nomeAcc</span>";
}
$html .= "</div>

<div class='section'>4. AVARIAS E DANOS PREEXISTENTES</div>
<div style='border:1px solid #ccc; padding:5px; min-height:40px;'>";
if (empty($avarias)) {
    $html .= "Nenhuma avaria preexistente registrada.";
} else {
    foreach ($avarias as $av) {
        $html .= "• " . htmlspecialchars($av['nome_componente']) . "<br>";
    }
}
$html .= "</div>

<div style='margin-top: 40px; text-align: center; border-top: 1px solid #000; width: 300px; margin-left: auto; margin-right: auto;'>
    <strong>ASSINATURA DIGITAL</strong><br>
    {$c['condutor_nome']}<br>
    <small>Autenticado via senha em: " . date('d/m/Y H:i:s', strtotime($c['assinatura_saida_data'])) . "</small>
</div>

<div class='footer'>SMnt Online - Sistema de Gestão de Manutenção | Impresso em " . date('d/m/Y H:i:s') . "</div>";

$dompdf->loadHtml($html);
$dompdf->render();
// LIMPEZA FINAL: Descarta qualquer erro/aviso que tenha "sujado" a memória
if (ob_get_length()) ob_end_clean();

$dompdf->stream("cautela_{$c['id']}.pdf", ["Attachment" => false]);
exit;
