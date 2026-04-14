<?php
// components/DAO/AcessorioDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class AcessorioDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    // Lista todos os acessórios cadastrados no sistema
    public function listarTodos()
    {
        return $this->conn->query("SELECT * FROM acessorios_viatura ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inserir($nome, $ehUniversal = 0)
    {
        try {
            $stmt = $this->conn->prepare("INSERT INTO acessorios_viatura (nome, eh_universal) VALUES (:nome, :uni)");
            return $stmt->execute([':nome' => $nome, ':uni' => $ehUniversal]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') return "duplicado";
            throw $e;
        }
    }

    public function alterarStatusUniversal($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE acessorios_viatura SET eh_universal = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function listarUniversais()
    {
        return $this->conn->query("SELECT * FROM acessorios_viatura WHERE eh_universal = 1 ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarOpcionais()
    {
        return $this->conn->query("SELECT * FROM acessorios_viatura WHERE eh_universal = 0 ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function excluir($id)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM acessorios_viatura WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            // Se o erro for 23000, significa que o item está vinculado a uma cautela (Integridade)
            if ($e->getCode() == '23000') return "vinculado";
            throw $e;
        }
    }
}
