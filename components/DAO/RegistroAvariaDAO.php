<?php
// components/DAO/RegistroAvariaDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class RegistroAvariaDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = Conexao::getConexao();
    }

    public function listarTiposAvaria()
    {
        $sql = "SELECT id, nome_componente, categoria FROM tipo_avaria ORDER BY categoria, nome_componente";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inserir(array $data)
    {
        $sql = "INSERT INTO registro_avaria (viatura_id, tipo_avaria_id, condutor_id, data_registro, observacoes_avaria) 
                VALUES (:viatura_id, :tipo_avaria_id, :condutor_id, :data_registro, :observacoes)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':viatura_id', $data['viatura_id'], PDO::PARAM_INT);
        $stmt->bindValue(':tipo_avaria_id', $data['tipo_avaria_id'], PDO::PARAM_INT);
        $stmt->bindValue(':condutor_id', $data['condutor_id'], PDO::PARAM_INT);
        $stmt->bindValue(':data_registro', $data['data_registro']);
        $stmt->bindValue(':observacoes', $data['observacoes_avaria'], PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function buscarDetalhesPorId(int $id)
    {
        $sql = "SELECT 
                ra.*, 
                v.prefixo, v.numero, v.placa, v.modelo,
                ta.nome_componente, ta.categoria,
                c.nome AS nome_condutor,
                m.status AS status_manutencao,
                m.id AS manutencao_id
            FROM registro_avaria ra
            JOIN viaturas v ON ra.viatura_id = v.id
            JOIN tipo_avaria ta ON ra.tipo_avaria_id = ta.id
            JOIN condutor c ON ra.condutor_id = c.id
            LEFT JOIN manutencoes m ON m.avaria_id = ra.id
            WHERE ra.id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar detalhes da avaria: " . $e->getMessage());
        }
    }

    public function atualizar(array $data): bool
    {
        $sql = "UPDATE registro_avaria SET 
                viatura_id = :viatura_id, 
                tipo_avaria_id = :tipo_avaria_id, 
                condutor_id = :condutor_id, 
                data_registro = :data_registro, 
                observacoes_avaria = :observacoes 
            WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':viatura_id', $data['viatura_id'], PDO::PARAM_INT);
            $stmt->bindValue(':tipo_avaria_id', $data['tipo_avaria_id'], PDO::PARAM_INT);
            $stmt->bindValue(':condutor_id', $data['condutor_id'], PDO::PARAM_INT);
            $stmt->bindValue(':data_registro', $data['data_registro']);
            $stmt->bindValue(':observacoes', $data['observacoes_avaria'], PDO::PARAM_STR);
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar registro de avaria: " . $e->getMessage());
        }
    }
}
