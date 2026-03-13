<?php
// components/DAO/ItemPreventivaDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class ItemPreventivaDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    public function listarTodos()
    {
        return $this->conn->query("SELECT * FROM itens_preventiva ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inserir($nome)
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO itens_preventiva (nome, status) VALUES (:nome, 'Ativo')");
            return $stmt->execute([':nome' => $nome]);
        } catch (PDOException $e) {
            // Código 23000 no MySQL geralmente indica violação de unicidade (Duplicate entry)
            if ($e->getCode() == '23000') {
                return "duplicado";
            }
            throw $e;
        }
    }

    public function excluir($id)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM itens_preventiva WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            // Erro 23000 é violação de integridade (item sendo usado em outra tabela)
            if ($e->getCode() == '23000') {
                return "vínculo_existente";
            }
            throw $e;
        }
    }

    public function alterarStatus($id, $novoStatus)
    {
        $sql = "UPDATE itens_preventiva SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':status' => $novoStatus, ':id' => $id]);
    }
}
