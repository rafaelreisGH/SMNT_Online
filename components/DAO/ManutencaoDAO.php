<?php
// ARQUIVO: components/DAO/ManutencaoDAO.php

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
     * Insere um novo registro de manutenção no banco de dados.
     * @param array $data Um array associativo com os dados da manutenção.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws Exception Se ocorrer um erro no banco de dados.
     */
    public function inserirManutencao(array $data): bool
    {
        // CORREÇÃO: Trocado 'oficina_nome' por 'oficina_id'
        $sql = "INSERT INTO " . $this->table_name . " (
                viatura_id, avaria_id, data_entrada, data_saida, tipo_manutencao, 
                descricao_problema, servico_realizado, observacoes, tempo_oficina, 
                custo, oficina_id, registrado_por_usuario_id
            ) VALUES (
                :viatura_id, :avaria_id, :data_entrada, :data_saida, :tipo_manutencao, 
                :descricao_problema, :servico_realizado, :observacoes, :tempo_oficina, 
                :custo, :oficina_id, :registrado_por_usuario_id
            )";

        try {
            $stmt = $this->conn->prepare($sql);

            // ... (outros bindValues permanecem os mesmos) ...
            $stmt->bindValue(':viatura_id', $data['viatura_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':avaria_id', $data['avaria_id'] !== '' ? $data['avaria_id'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':data_entrada', $data['data_entrada'] !== '' ? $data['data_entrada'] : null);
            $stmt->bindValue(':data_saida', $data['data_saida'] !== '' ? $data['data_saida'] : null);
            $stmt->bindValue(':tipo_manutencao', $data['tipo_manutencao'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':descricao_problema', $data['descricao_problema'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':servico_realizado', $data['servico_realizado'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':observacoes', $data['observacoes'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':tempo_oficina', $data['tempo_oficina'] !== '' ? $data['tempo_oficina'] : null);
            $stmt->bindValue(':custo', $data['custo'] !== '' ? $data['custo'] : null);

            // CORREÇÃO: Bind para :oficina_id (que agora está no SQL)
            $stmt->bindValue(':oficina_id', $data['oficina_id'] !== '' ? $data['oficina_id'] : null, PDO::PARAM_INT);

            $stmt->bindValue(':registrado_por_usuario_id', $data['registrado_por_usuario_id'] ?? null, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro de PDO ao inserir manutenção: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Atualiza um registro de manutenção existente.
     * @param array $data Array com os dados, incluindo o 'id' da manutenção.
     * @return bool True em caso de sucesso.
     * @throws Exception
     */
    public function updateManutencao(array $data): bool
    {
        // CORREÇÃO: Trocado 'oficina_nome = :oficina_nome' por 'oficina_id = :oficina_id'
        $sql = "UPDATE " . $this->table_name . " SET
                viatura_id = :viatura_id,
                avaria_id = :avaria_id,
                data_entrada = :data_entrada,
                data_saida = :data_saida,
                tipo_manutencao = :tipo_manutencao,
                descricao_problema = :descricao_problema,
                servico_realizado = :servico_realizado,
                observacoes = :observacoes,
                tempo_oficina = :tempo_oficina,
                custo = :custo,
                oficina_id = :oficina_id,
                registrado_por_usuario_id = :registrado_por_usuario_id
            WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);

            // ... (outros bindValues permanecem os mesmos) ...
            $stmt->bindValue(':viatura_id', $data['viatura_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':avaria_id', $data['avaria_id'] !== '' ? $data['avaria_id'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':data_entrada', $data['data_entrada'] !== '' ? $data['data_entrada'] : null);
            $stmt->bindValue(':data_saida', $data['data_saida'] !== '' ? $data['data_saida'] : null);
            $stmt->bindValue(':tipo_manutencao', $data['tipo_manutencao'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':descricao_problema', $data['descricao_problema'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':servico_realizado', $data['servico_realizado'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':observacoes', $data['observacoes'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':tempo_oficina', $data['tempo_oficina'] !== '' ? $data['tempo_oficina'] : null);
            $stmt->bindValue(':custo', $data['custo'] !== '' ? $data['custo'] : null);

            // CORREÇÃO: Bind para :oficina_id
            $stmt->bindValue(':oficina_id', $data['oficina_id'] !== '' ? $data['oficina_id'] : null, PDO::PARAM_INT);

            $stmt->bindValue(':registrado_por_usuario_id', $data['registrado_por_usuario_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro de PDO ao atualizar manutenção: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Busca um registro de manutenção pelo seu ID.
     * @param int $id O ID da manutenção.
     * @return array|false Retorna os dados da manutenção ou false se não encontrada.
     * @throws Exception
     */
    public function getManutencaoById(int $id)
    {
        $query = "SELECT m.*, v.prefixo as viatura_prefixo, v.placa as viatura_placa,
                     o.nome as oficina_nome -- MUDANÇA: Adicionado nome da oficina
              FROM " . $this->table_name . " m
              LEFT JOIN viaturas v ON m.viatura_id = v.id
              LEFT JOIN oficinas o ON m.oficina_id = o.id -- MUDANÇA: Adicionado JOIN
              WHERE m.id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar manutenção por ID: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Busca manutenções com base em um array de filtros.
     * @param array $filters Filtros como ['viatura_id' => 5, 'tipo_manutencao' => 'Corretiva'].
     * @return array Retorna uma lista de manutenções.
     * @throws Exception
     */

    public function buscarManutencoes(array $filters = []): array
    {
        $sql = "SELECT
                m.id, m.data_entrada, m.data_saida, m.tipo_manutencao, m.custo,
                v.prefixo as viatura_prefixo, v.placa as viatura_placa,
                o.nome as oficina_nome
            FROM
                " . $this->table_name . " m
            LEFT JOIN
                viaturas v ON m.viatura_id = v.id
            LEFT JOIN
                oficinas o ON m.oficina_id = o.id
            WHERE 1=1";

        $params = [];

        // --- Filtros ---
        if (!empty($filters['oficina_nome'])) {
            $sql .= " AND o.nome LIKE :oficina_nome";
            $params[':oficina_nome'] = '%' . $filters['oficina_nome'] . '%';
        }
        if (!empty($filters['viatura_id'])) {
            $sql .= " AND m.viatura_id = :viatura_id";
            $params[':viatura_id'] = $filters['viatura_id'];
        }
        if (!empty($filters['tipo_manutencao'])) {
            $sql .= " AND m.tipo_manutencao = :tipo_manutencao";
            $params[':tipo_manutencao'] = $filters['tipo_manutencao'];
        }
        if (!empty($filters['avaria_id'])) {
            $sql .= " AND m.avaria_id = :avaria_id";
            $params[':avaria_id'] = $filters['avaria_id'];
        }
        if (!empty($filters['data_entrada_min'])) {
            $sql .= " AND m.data_entrada >= :data_entrada_min";
            $params[':data_entrada_min'] = $filters['data_entrada_min'];
        }
        if (!empty($filters['data_entrada_max'])) {
            $sql .= " AND m.data_entrada <= :data_entrada_max";
            $params[':data_entrada_max'] = $filters['data_entrada_max'] . ' 23:59:59';
        }
        if (isset($filters['custo_min']) && $filters['custo_min'] !== '') {
            $sql .= " AND m.custo >= :custo_min";
            $params[':custo_min'] = $filters['custo_min'];
        }
        if (isset($filters['custo_max']) && $filters['custo_max'] !== '') {
            $sql .= " AND m.custo <= :custo_max";
            $params[':custo_max'] = $filters['custo_max'];
        }

        $sql .= " ORDER BY m.data_entrada DESC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar manutenções: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Deleta um registro de manutenção do banco de dados.
     * @param int $id O ID da manutenção a ser deletada.
     * @return bool True em caso de sucesso.
     * @throws Exception
     */
    public function deleteManutencao(int $id): bool
    {
        $sql = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao deletar manutenção: " . $e->getMessage(), 0, $e);
        }
    }
}
