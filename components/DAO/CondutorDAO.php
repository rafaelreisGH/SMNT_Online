<?php
// components/DAO/CondutorDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class CondutorDAO
{
    private $conn;
    private $table_name = "condutor";

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    private function aplicarEscopoRBAC(string $aliasCondutor = 'c', string $aliasUnidade = 'u'): array
    {
        $sql = "";
        $params = [];
        if (defined('USER_AUTH')) {
            $user = USER_AUTH;
            switch ($user['perfil']) {
                case 'Gestor Local':
                    $sql = " AND {$aliasCondutor}.ubm_id = :rbac_ubm_id";
                    $params[':rbac_ubm_id'] = $user['unidade_id'];
                    break;
                case 'Gestor Regional':
                    $sql = " AND {$aliasUnidade}.comando_regional_id = :rbac_crbm_id";
                    $params[':rbac_crbm_id'] = $user['comando_regional_id'];
                    break;
            }
        }
        return ['sql' => $sql, 'params' => $params];
    }

    public function inserirCondutor(array $data): bool
    {
        $sql = "INSERT INTO " . $this->table_name . " (nome, cpf, numero_cnh, categoria_cnh, data_validade_cnh, anotacao_ear, rg_militar, posto_grad, ubm_id, status) VALUES (:nome, :cpf, :numero_cnh, :categoria_cnh, :data_validade_cnh, :anotacao_ear, :rg_militar, :posto_grad, :ubm_id, :status)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nome', $data['nome'] ?? null);
            $stmt->bindValue(':cpf', $data['cpf'] ?? null);
            $stmt->bindValue(':numero_cnh', $data['numero_cnh'] ?? null);
            $stmt->bindValue(':categoria_cnh', $data['categoria_cnh'] ?? null);
            $stmt->bindValue(':data_validade_cnh', !empty($data['data_validade_cnh']) ? $data['data_validade_cnh'] : null);
            $stmt->bindValue(':anotacao_ear', $data['anotacao_ear'] ?? 0, PDO::PARAM_INT);
            $stmt->bindValue(':rg_militar', $data['rg_militar'] ?? null);
            $stmt->bindValue(':posto_grad', $data['posto_grad'] ?? null);
            $stmt->bindValue(':ubm_id', !empty($data['ubm_id']) ? $data['ubm_id'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':status', $data['status'] ?? 'ativo');
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') throw new Exception("CPF, CNH ou RG Militar já cadastrado.");
            throw new Exception("Erro ao inserir condutor: " . $e->getMessage());
        }
    }

    /**
     * Atualiza os dados de um condutor existente.
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
            $stmt->bindValue(':cpf', $data['cpf'] ?? null);
            $stmt->bindValue(':numero_cnh', $data['numero_cnh'] ?? null);
            $stmt->bindValue(':categoria_cnh', $data['categoria_cnh'] ?? null);
            $stmt->bindValue(':data_validade_cnh', !empty($data['data_validade_cnh']) ? $data['data_validade_cnh'] : null);

            // Tratamento para checkbox (anotacao_ear)
            $stmt->bindValue(':anotacao_ear', isset($data['anotacao_ear']) ? 1 : 0, PDO::PARAM_INT);

            $stmt->bindValue(':rg_militar', $data['rg_militar'] ?? null);
            $stmt->bindValue(':posto_grad', $data['posto_grad'] ?? null);
            $stmt->bindValue(':ubm_id', !empty($data['ubm_id']) ? $data['ubm_id'] : null, PDO::PARAM_INT);
            $stmt->bindValue(':status', $data['status'] ?? 'ativo');
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') throw new Exception("Erro: CPF, CNH ou RG já vinculados a outro condutor.");
            throw new Exception("Erro ao atualizar condutor no banco de dados.");
        }
    }

    public function alterarStatus(int $id, string $status): bool
    {
        $sql = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao alterar status: " . $e->getMessage());
        }
    }

    public function getCondutorById(int $id)
    {
        $sql = "SELECT c.*, u.nome as nome_unidade FROM condutor c LEFT JOIN unidades u ON c.ubm_id = u.id WHERE c.id = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar condutor: " . $e->getMessage());
        }
    }

    public function buscarCondutores(array $filters = []): array
    {
        $rbac = $this->aplicarEscopoRBAC();
        $sql = "SELECT c.*, u.nome AS unidade_lotacao FROM " . $this->table_name . " c LEFT JOIN unidades u ON c.ubm_id = u.id WHERE 1=1 {$rbac['sql']}";
        $params = $rbac['params'];
        if (!empty($filters['nome'])) {
            $sql .= " AND c.nome LIKE :nome";
            $params[':nome'] = '%' . $filters['nome'] . '%';
        }
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar condutores.");
        }
    }

    /**
     * Lista apenas condutores ativos respeitando o RBAC
     */
    public function listarAtivos(): array
    {
        $rbac = $this->aplicarEscopoRBAC(); // Importante para manter a segurança
        $sql = "SELECT c.id, c.nome 
            FROM " . $this->table_name . " c
            LEFT JOIN unidades u ON c.ubm_id = u.id
            WHERE c.status = 'ativo' {$rbac['sql']} 
            ORDER BY c.nome ASC";

        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($rbac['params'] as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro no CondutorDAO (listarAtivos): " . $e->getMessage());
            return [];
        }
    }
}
