<?php
// components/DAO/OficinaDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class OficinaDAO
{
    private $conn;
    private $table = "oficinas";

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    public function listarTodas()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY status ASC, nome_fantasia ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inserir(array $data)
    {
        $sql = "INSERT INTO {$this->table} (
                    nome_fantasia, razao_social, cnpj, telefone, email, endereco, status
                ) VALUES (
                    :nome, :razao, :cnpj, :tel, :email, :end, 'ativa'
                )";
        try {
            $stmt = $this->conn->prepare($sql);
            // Limpa CNPJ para salvar apenas os 14 números
            $cnpjLimpo = preg_replace('/\D/', '', $data['cnpj'] ?? '');

            $stmt->bindValue(':nome', $data['nome_fantasia'], PDO::PARAM_STR);
            $stmt->bindValue(':razao', !empty($data['razao_social']) ? $data['razao_social'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':cnpj', !empty($cnpjLimpo) ? $cnpjLimpo : null, PDO::PARAM_STR);
            $stmt->bindValue(':tel', !empty($data['telefone']) ? $data['telefone'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':email', !empty($data['email']) ? $data['email'] : null, PDO::PARAM_STR);
            $stmt->bindValue(':end', !empty($data['endereco']) ? $data['endereco'] : null, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            // Código 23000 é erro de integridade (CNPJ Duplicado)
            if ($e->getCode() == '23000') return "duplicado";
            throw new Exception("Erro ao inserir oficina: " . $e->getMessage());
        }
    }

    public function alterarStatus($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }
}
