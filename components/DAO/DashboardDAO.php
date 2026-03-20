<?php
// components/DAO/DashboardDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class DashboardDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    private function aplicarEscopoRBAC()
    {
        $sql = "";
        if (defined('USER_AUTH')) {
            $user = USER_AUTH;
            if ($user['perfil'] === 'Gestor Local') {
                $sql = " AND v.lotacao_atual_id = " . (int)$user['unidade_id'];
            } elseif ($user['perfil'] === 'Gestor Regional') {
                $sql = " AND u.comando_regional_id = " . (int)$user['comando_regional_id'];
            }
        }
        return $sql;
    }

    // 1. Resumo de Status (Cards Principais)
    public function getResumoStatus()
    {
        $rbac = $this->aplicarEscopoRBAC();
        $sql = "SELECT 
                    SUM(CASE WHEN v.status_da_viatura = 'Baixada' THEN 1 ELSE 0 END) as baixadas,
                    SUM(CASE WHEN v.status_da_viatura = 'Operante' THEN 1 ELSE 0 END) as operantes,
                    (SELECT COUNT(*) FROM manutencoes m 
                     JOIN viaturas v2 ON m.viatura_id = v2.id 
                     JOIN unidades u2 ON v2.lotacao_atual_id = u2.id
                     WHERE m.status != 'Concluída' 
                     AND m.status != 'Cancelada' " . str_replace('v.', 'v2.', $rbac) . ") as em_manutencao
                FROM viaturas v
                JOIN unidades u ON v.lotacao_atual_id = u.id
                WHERE 1=1 $rbac";
        return $this->conn->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    // 2. Alertas de Manutenção Preventiva (Baseado nos Planos)
    // Esta query busca viaturas cuja KM atual superou a KM da última manutenção + regra do plano
    public function getAlertasVencidos()
    {
        $rbac = $this->aplicarEscopoRBAC();
        $sql = "SELECT v.prefixo, v.numero, ip.nome as item, 
                       v.quilometragem as km_atual,
                       (m.quilometragem + rm.periodicidade_km) as km_limite
                FROM viaturas v
                JOIN unidades u ON v.lotacao_atual_id = u.id
                JOIN planos_manutencao pm ON v.plano_id = pm.id
                JOIN regras_manutencao rm ON pm.id = rm.plano_id
                JOIN itens_preventiva ip ON rm.item_id = ip.id
                LEFT JOIN manutencoes m ON m.viatura_id = v.id AND m.item_preventiva_id = ip.id
                WHERE (v.quilometragem >= (m.quilometragem + rm.periodicidade_km))
                OR (m.id IS NULL AND v.quilometragem >= rm.periodicidade_km)
                $rbac";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Avarias que ainda não viraram manutenção
    public function getAvariasPendentes()
    {
        $rbac = $this->aplicarEscopoRBAC();
        $sql = "SELECT ra.*, v.prefixo, v.numero, ta.nome_componente
                FROM registro_avaria ra
                JOIN viaturas v ON ra.viatura_id = v.id
                JOIN unidades u ON v.lotacao_atual_id = u.id
                JOIN tipo_avaria ta ON ra.tipo_avaria_id = ta.id
                LEFT JOIN manutencoes m ON m.avaria_id = ra.id
                WHERE m.id IS NULL $rbac
                ORDER BY ra.data_registro ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getManutencoesAndamento()
    {
        $rbac = $this->aplicarEscopoRBAC();
        $sql = "SELECT m.*, v.prefixo, v.numero, o.nome_fantasia as oficina
            FROM manutencoes m
            JOIN viaturas v ON m.viatura_id = v.id
            JOIN unidades u ON v.lotacao_atual_id = u.id
            LEFT JOIN oficinas o ON m.oficina_id = o.id
            WHERE m.status NOT IN ('Concluída', 'Cancelada') 
            $rbac
            ORDER BY m.data_entrada ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTransferenciasEmTransito()
    {
        $sql = "SELECT m.*, v.prefixo, v.numero, 
                   uo.nome as unidade_origem, 
                   ud.nome as unidade_destino
            FROM movimentacoes_lotacao m
            JOIN viaturas v ON m.viatura_id = v.id
            JOIN unidades uo ON m.unidade_origem_id = uo.id
            JOIN unidades ud ON m.unidade_destino_id = ud.id
            WHERE m.status = 'Em Trânsito'";

        $params = [];

        if (defined('USER_AUTH')) {
            $user = USER_AUTH;
            if ($user['perfil'] === 'Gestor Local') {
                // Vê se for origem OU se for destino
                $sql .= " AND (m.unidade_origem_id = :u_id OR m.unidade_destino_id = :u_id)";
                $params[':u_id'] = $user['unidade_id'];
            } elseif ($user['perfil'] === 'Gestor Regional') {
                // Vê se a origem ou destino pertencem ao seu Comando Regional
                $sql .= " AND (uo.comando_regional_id = :crbm OR ud.comando_regional_id = :crbm)";
                $params[':crbm'] = $user['comando_regional_id'];
            }
        }

        $sql .= " ORDER BY m.data_movimentacao DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
