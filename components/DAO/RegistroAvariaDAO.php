<?php
// components/DAO/RegistroAvariaDAO.php
require_once __DIR__ . '/../../components/ConexaoDB/conexao.php';

class RegistroAvariaDAO {
    private $conn;

    public function __construct() {
        $this->conn = Conexao::getConexao();
    }

    public function listarTiposAvaria() {
        $sql = "SELECT id, nome_componente, categoria FROM tipo_avaria ORDER BY categoria, nome_componente";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inserir(array $data) {
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
}