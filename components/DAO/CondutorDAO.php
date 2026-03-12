<?php
// ARQUIVO: components/DAO/CondutorDAO.php

require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class CondutorDAO
{
    private $conn;
    private $table_name = "condutor";

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Lista apenas condutores ativos para popular campos de seleção (dropdowns).
     * @return array Lista de condutores ativos.
     */
    public function listarAtivos(): array
    {
        $sql = "SELECT id, nome FROM " . $this->table_name . " WHERE status = 'ativo' ORDER BY nome ASC";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro no CondutorDAO (listarAtivos): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Insere um novo condutor no banco de dados.
     * @param array $data Um array associativo com os dados do condutor.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws Exception Se ocorrer um erro no banco de dados.
     */
    public function inserirCondutor(array $data): bool
    {
        $sql = "INSERT INTO " . $this->table_name . " (
                    nome, cpf, numero_cnh, categoria_cnh, data_validade_cnh, 
                    anotacao_ear, rg_militar, posto_grad, ubm_id, status
                ) VALUES (
                    :nome, :cpf, :numero_cnh, :categoria_cnh, :data_validade_cnh, 
                    :anotacao_ear, :rg_militar, :posto_grad, :ubm_id, :status
                )";

        try {
            $stmt = $this->conn->prepare($sql);

            // Vinculação de parâmetros
            $stmt->bindValue(':nome', $data['nome'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':cpf', $data['cpf'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':numero_cnh', $data['numero_cnh'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':categoria_cnh', $data['categoria_cnh'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':data_validade_cnh', $data['data_validade_cnh'] !== '' ? $data['data_validade_cnh'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':anotacao_ear', $data['anotacao_ear'] ?? 0, PDO::PARAM_INT);
            $stmt->bindValue(':rg_militar', $data['rg_militar'] !== '' ? $data['rg_militar'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':posto_grad', $data['posto_grad'] !== '' ? $data['posto_grad'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':ubm_id', $data['ubm_id'] !== '' ? $data['ubm_id'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':status', $data['status'] ?? 'ativo', PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro de PDO ao inserir condutor: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Verifica se um CPF já existe, opcionalmente excluindo um ID (para edição).
     */
    public function existsByCpf(string $cpf, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE cpf = :cpf";
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
        }
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':cpf', $cpf, PDO::PARAM_STR);
            if ($excludeId !== null) {
                $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao verificar CPF: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Busca condutores com base em um array de filtros.
     */
    public function buscarCondutores(array $filters = []): array
    {
        $sql = "SELECT
                c.id, c.nome, c.cpf, c.numero_cnh, c.categoria_cnh, 
                c.data_validade_cnh, c.anotacao_ear, c.posto_grad, c.status,
                u.nome AS unidade_lotacao
            FROM
                " . $this->table_name . " c
            LEFT JOIN
                unidades u ON c.ubm_id = u.id
            WHERE 1=1";

        $params = [];

        if (!empty($filters['nome'])) {
            $sql .= " AND c.nome LIKE :nome";
            $params[':nome'] = '%' . $filters['nome'] . '%';
        }
        if (!empty($filters['cpf'])) {
            $sql .= " AND c.cpf = :cpf";
            $params[':cpf'] = preg_replace('/\D/', '', $filters['cpf']);
        }
        if (!empty($filters['numero_cnh'])) {
            $sql .= " AND c.numero_cnh LIKE :numero_cnh";
            $params[':numero_cnh'] = '%' . $filters['numero_cnh'] . '%';
        }
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = :status";
            $params[':status'] = $filters['status'];
        }

        $sql .= " ORDER BY c.nome ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar condutores: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Busca um condutor pelo seu ID.
     */
    public function getCondutorById(int $id)
    {
        $query = "SELECT c.*, u.nome as nome_unidade 
              FROM " . $this->table_name . " c
              LEFT JOIN unidades u ON c.ubm_id = u.id
              WHERE c.id = :id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar condutor: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Atualiza os dados de um condutor no banco de dados.
     */
    public function updateCondutor(array $data): bool
    {
        $sql = "UPDATE " . $this->table_name . " SET
                nome = :nome,
                cpf = :cpf,
                numero_cnh = :numero_cnh,
                categoria_cnh = :categoria_cnh,
                data_validade_cnh = :data_validade_cnh,
                anotacao_ear = :anotacao_ear,
                rg_militar = :rg_militar,
                posto_grad = :posto_grad,
                ubm_id = :ubm_id,
                status = :status
            WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);

            $stmt->bindValue(':nome', $data['nome'] ?? null);
            $stmt->bindValue(':cpf', preg_replace('/\D/', '', $data['cpf'] ?? ''));
            $stmt->bindValue(':numero_cnh', $data['numero_cnh'] ?? null);
            $stmt->bindValue(':categoria_cnh', $data['categoria_cnh'] ?? null);
            $stmt->bindValue(':data_validade_cnh', $data['data_validade_cnh'] ?: null);
            $stmt->bindValue(':anotacao_ear', isset($data['anotacao_ear']) ? 1 : 0);
            $stmt->bindValue(':rg_militar', $data['rg_militar'] ?: null);
            $stmt->bindValue(':posto_grad', $data['posto_grad'] ?: null);
            $stmt->bindValue(':ubm_id', $data['ubm_id'] ?: null, PDO::PARAM_INT);
            $stmt->bindValue(':status', $data['status'] ?? 'ativo');
            $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro de PDO ao atualizar condutor: " . $e->getMessage(), 0, $e);
        }
    }
}