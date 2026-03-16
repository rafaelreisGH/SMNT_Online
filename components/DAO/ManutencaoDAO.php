<?php
// components/DAO/ManutencaoDAO.php

require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class ManutencaoDAO
{
    private $conn;
    private $table_name = "manutencoes";

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Helper para aplicar as restrições de escopo (RBAC)
     */
    private function aplicarEscopoRBAC(string $aliasViatura = 'v', string $aliasUnidade = 'u'): array
    {
        $sql = "";
        $params = [];
        if (defined('USER_AUTH')) {
            $user = USER_AUTH;
            switch ($user['perfil']) {
                case 'Gestor Local':
                    $sql = " AND v.lotacao_atual_id = :rbac_unidade_id";
                    $params[':rbac_unidade_id'] = $user['unidade_id'];
                    break;
                case 'Gestor Regional':
                    $sql = " AND u.comando_regional_id = :rbac_crbm_id";
                    $params[':rbac_crbm_id'] = $user['comando_regional_id'];
                    break;
            }
        }
        return ['sql' => $sql, 'params' => $params];
    }

    public function inserirManutencao(array $data): bool
    {
        $sql = "INSERT INTO " . $this->table_name . " (
                viatura_id, avaria_id, item_preventiva_id, data_entrada, tipo_manutencao, 
                quilometragem, status, descricao_problema, oficina_id, ordem_de_servico, registrado_por_usuario_id
            ) VALUES (
                :viatura_id, :avaria_id, :item_preventiva_id, :data_entrada, :tipo_manutencao, 
                :quilometragem, :status, :descricao_problema, :oficina_id, :ordem_de_servico, :registrado_por_usuario_id
            )";

        try {
            $stmt = $this->conn->prepare($sql);

            $stmt->bindValue(':viatura_id', $data['viatura_id'], PDO::PARAM_INT);
            $stmt->bindValue(':avaria_id', !empty($data['avaria_id']) ? $data['avaria_id'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':item_preventiva_id', !empty($data['item_preventiva_id']) ? $data['item_preventiva_id'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':data_entrada', !empty($data['data_entrada']) ? $data['data_entrada'] : date('Y-m-d H:i:s'));
            $stmt->bindValue(':tipo_manutencao', $data['tipo_manutencao']);
            $stmt->bindValue(':quilometragem', $data['quilometragem'], PDO::PARAM_INT);
            $stmt->bindValue(':status', $data['status'] ?? 'Pendente');
            $stmt->bindValue(':descricao_problema', $data['descricao_problema'] ?? null);
            $stmt->bindValue(':oficina_id', $data['oficina_id'], PDO::PARAM_INT);
            $stmt->bindValue(':ordem_de_servico', !empty($data['ordem_de_servico']) ? $data['ordem_de_servico'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':registrado_por_usuario_id', defined('USER_AUTH') ? USER_AUTH['id'] : null, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao inserir manutenção: " . $e->getMessage());
        }
    }

    public function buscarManutencoes(array $filters = []): array
    {
        $rbac = $this->aplicarEscopoRBAC();

        $sql = "SELECT
            m.id, m.ordem_de_servico, m.data_entrada, m.data_saida, m.tipo_manutencao, m.custo, m.status,
            v.prefixo as viatura_prefixo, v.numero as viatura_numero, v.placa as viatura_placa,
            o.nome_fantasia as oficina_nome -- CORREÇÃO: era o.nome
        FROM
            " . $this->table_name . " m
        LEFT JOIN
            viaturas v ON m.viatura_id = v.id
        LEFT JOIN
            unidades u ON v.lotacao_atual_id = u.id -- Necessário para o RBAC
        LEFT JOIN
            oficinas o ON m.oficina_id = o.id
        WHERE 1=1 {$rbac['sql']}";

        $params = $rbac['params'];

        // Filtros do formulário
        if (!empty($filters['viatura_id'])) {
            $sql .= " AND m.viatura_id = :v_id";
            $params[':v_id'] = $filters['viatura_id'];
        }
        if (!empty($filters['tipo_manutencao'])) {
            $sql .= " AND m.tipo_manutencao = :tipo";
            $params[':tipo'] = $filters['tipo_manutencao'];
        }
        if (!empty($filters['oficina_nome'])) {
            $sql .= " AND o.nome_fantasia LIKE :oficina"; // CORREÇÃO
            $params[':oficina'] = '%' . $filters['oficina_nome'] . '%';
        }
        // NOVO: Filtro de Status
        if (!empty($filters['status'])) {
            $sql .= " AND m.status = :status";
            $params[':status'] = $filters['status'];
        }

        $sql .= " ORDER BY m.data_entrada DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista todas as oficinas ativas para preencher selects.
     */
    public function listarOficinasAtivas(): array
    {
        $sql = "SELECT id, nome_fantasia FROM oficinas WHERE status = 'ativa' ORDER BY nome_fantasia ASC";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao listar oficinas: " . $e->getMessage());
            return [];
        }
    }

    public function buscarDetalhesPorId(int $id)
    {
        $sql = "SELECT m.*, 
                   v.prefixo, v.numero, v.placa, v.modelo,
                   o.nome_fantasia as oficina_nome,
                   ip.nome as item_preventiva_nome,
                   ra.observacoes_avaria as relato_avaria
            FROM manutencoes m
            JOIN viaturas v ON m.viatura_id = v.id
            LEFT JOIN oficinas o ON m.oficina_id = o.id
            LEFT JOIN itens_preventiva ip ON m.item_preventiva_id = ip.id
            LEFT JOIN registro_avaria ra ON m.avaria_id = ra.id
            WHERE m.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
