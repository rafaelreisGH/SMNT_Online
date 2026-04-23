<?php
// components/DAO/CombustivelDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class CombustivelDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    public function listarTodos()
    {
        $sql = "SELECT * FROM combustiveis ORDER BY tipo ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inserir($tipo)
    {
        $sql = "INSERT INTO combustiveis (tipo) VALUES (:tipo)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':tipo' => $tipo]);
    }

    public function atualizar($id, $tipo)
    {
        $sql = "UPDATE combustiveis SET tipo = :tipo WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':tipo' => $tipo, ':id' => $id]);
    }

    public function excluir($id)
    {
        // Verifica se há tanques vinculados antes de excluir
        $check = $this->conn->prepare("SELECT COUNT(*) FROM tanques_combustivel WHERE combustivel_id = :id");
        $check->execute([':id' => $id]);
        if ($check->fetchColumn() > 0) {
            throw new Exception("Não é possível excluir: existem viaturas configuradas com este combustível.");
        }

        $sql = "DELETE FROM combustiveis WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
