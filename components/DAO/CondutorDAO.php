<?php
// ARQUIVO: components/DAO/CondutorDAO.php

require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class CondutorDAO {
    private $conn;
    private $table_name = "condutor";

    public function __construct() {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Insere um novo condutor no banco de dados.
     * @param array $data Um array associativo com os dados do condutor.
     * @return bool True em caso de sucesso, false em caso de falha.
     * @throws Exception Se ocorrer um erro no banco de dados.
     */
    public function inserirCondutor(array $data): bool {
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
            // Relança uma exceção para ser tratada pela API
            throw new Exception("Erro de PDO ao inserir condutor: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Busca um condutor pelo ID.
     * @param int $id O ID do condutor.
     * @return array|false Retorna os dados do condutor ou false se não encontrado.
     * @throws Exception
     */
    public function getCondutorById(int $id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
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
     * Verifica se um CPF já existe, opcionalmente excluindo um ID (para edição).
     * @param string $cpf
     * @param int|null $excludeId
     * @return bool
     * @throws Exception
     */
    public function existsByCpf(string $cpf, ?int $excludeId = null): bool {
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
    
    // Você pode criar métodos 'existsByNumeroCnh' e 'existsByRgMilitar' seguindo o mesmo modelo acima
    // para uma validação ainda mais robusta na sua API.
}