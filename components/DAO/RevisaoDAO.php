<?php
// ARQUIVO: components/DAO/RevisaoDAO.php

require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class RevisaoDAO
{
    private $conn;
    private $table_name = "revisoes_viaturas";

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Insere uma nova revisão (Realizada ou Programada).
     */
    public function inserirRevisao(array $data): bool
    {
        $sql = "INSERT INTO " . $this->table_name . " (
                    viatura_id, data_revisao, tipo_revisao, quilometragem, 
                    itens_substituidos, observacoes, status_revisao, 
                    proxima_revisao_km, proxima_revisao_data
                ) VALUES (
                    :viatura_id, :data_revisao, :tipo_revisao, :quilometragem, 
                    :itens_substituidos, :observacoes, :status_revisao, 
                    :proxima_revisao_km, :proxima_revisao_data
                )";

        try {
            $stmt = $this->conn->prepare($sql);

            $stmt->bindValue(':viatura_id', $data['viatura_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':data_revisao', $data['data_revisao'] !== '' ? $data['data_revisao'] : null);
            $stmt->bindValue(':tipo_revisao', $data['tipo_revisao'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':quilometragem', $data['quilometragem'] !== '' ? $data['quilometragem'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':itens_substituidos', $data['itens_substituidos'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':observacoes', $data['observacoes'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':status_revisao', $data['status_revisao'] ?? 'Realizada', PDO::PARAM_STR);
            $stmt->bindValue(':proxima_revisao_km', $data['proxima_revisao_km'] !== '' ? $data['proxima_revisao_km'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':proxima_revisao_data', $data['proxima_revisao_data'] !== '' ? $data['proxima_revisao_data'] : null);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao inserir revisão: " . $e->getMessage());
        }
    }

    /**
     * Atualiza uma revisão existente.
     */
    public function updateRevisao(array $data): bool
    {
        $sql = "UPDATE " . $this->table_name . " SET
                    viatura_id = :viatura_id,
                    data_revisao = :data_revisao,
                    tipo_revisao = :tipo_revisao,
                    quilometragem = :quilometragem,
                    itens_substituidos = :itens_substituidos,
                    observacoes = :observacoes,
                    status_revisao = :status_revisao,
                    proxima_revisao_km = :proxima_revisao_km,
                    proxima_revisao_data = :proxima_revisao_data
                WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);

            $stmt->bindValue(':viatura_id', $data['viatura_id'] ?? null, PDO::PARAM_INT);
            $stmt->bindValue(':data_revisao', $data['data_revisao'] !== '' ? $data['data_revisao'] : null);
            $stmt->bindValue(':tipo_revisao', $data['tipo_revisao'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':quilometragem', $data['quilometragem'] !== '' ? $data['quilometragem'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':itens_substituidos', $data['itens_substituidos'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':observacoes', $data['observacoes'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':status_revisao', $data['status_revisao'] ?? 'Realizada', PDO::PARAM_STR);
            $stmt->bindValue(':proxima_revisao_km', $data['proxima_revisao_km'] !== '' ? $data['proxima_revisao_km'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':proxima_revisao_data', $data['proxima_revisao_data'] !== '' ? $data['proxima_revisao_data'] : null);
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar revisão: " . $e->getMessage());
        }
    }

    /**
     * Busca revisões com filtros (Viatura, Status, Datas e Tipo).
     */
    public function buscarRevisoes(array $filters = []): array
    {
        // 1. Base da Query
        $sql = "SELECT r.*, v.prefixo, v.placa, v.numero 
            FROM " . $this->table_name . " r
            JOIN viaturas v ON r.viatura_id = v.id
            WHERE 1=1";

        $params = [];

        // 2. Filtro por Viatura
        if (!empty($filters['viatura_id'])) {
            $sql .= " AND r.viatura_id = :viatura_id";
            $params[':viatura_id'] = $filters['viatura_id'];
        }

        // 3. Filtro por Status
        if (!empty($filters['status_revisao'])) {
            $sql .= " AND r.status_revisao = :status_revisao";
            $params[':status_revisao'] = $filters['status_revisao'];
        }

        // 4. Filtro por Tipo de Revisão (Busca parcial com LIKE)
        if (!empty($filters['tipo_revisao'])) {
            $sql .= " AND r.tipo_revisao LIKE :tipo_revisao";
            $params[':tipo_revisao'] = '%' . $filters['tipo_revisao'] . '%';
        }

        // 5. Filtro de Data Início (Maior ou igual)
        if (!empty($filters['data_inicio'])) {
            $sql .= " AND r.data_revisao >= :data_inicio";
            $params[':data_inicio'] = $filters['data_inicio'];
        }

        // 6. Filtro de Data Fim (Menor ou igual)
        if (!empty($filters['data_fim'])) {
            $sql .= " AND r.data_revisao <= :data_fim";
            $params[':data_fim'] = $filters['data_fim'];
        }

        // 7. Ordenação (Mais recentes primeiro)
        $sql .= " ORDER BY r.data_revisao DESC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar revisões: " . $e->getMessage());
        }
    }

    public function deleteRevisao(int $id): bool
    {
        $sql = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao deletar revisão: " . $e->getMessage());
        }
    }
}
